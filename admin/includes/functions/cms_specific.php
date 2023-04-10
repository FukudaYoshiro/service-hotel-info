<?php
//IMAGES
//We first check all the images, check for first hand errrors and set up some base parameters
function parse_images($section_name,$imagebasename,$error, $image_status, $check_upload=true) {
	
	global $_POST;
	
	$error_image_message = '';
	// Generating a random key to use in the image title
	$random_image_key = rand(1,100000);
	
	// Upload functionality		
	// Getting the status of the images and calculating the image number
	$image_number = sizeof($image_status);
	
	// We only process the images if everything else is correct
	if ($error == false) {			
		
		// We go through all the possible image fields
		for ($i=0;$i<$image_number;$i++) {			
			// We only process the image if there is something in that field
			if ($image_status[$i] != 'not') {
			
				$upload_image_result = get_image_parameters($section_name,$i,$imagebasename,$random_image_key++);
				$error = $upload_image_result[0];
				$error_image_message = $upload_image_result[1];
				$new_filename[$i] = $upload_image_result[3][$i];
			}
		}
		
		$picture_array = array();
		
		// We gather up all the images for an update
		for ($i=0;$i<$image_number;$i++) {
			if ($new_filename[$i]) {
				$picture_array[]= $new_filename[$i];
			} else if (strlen($_POST['previous_picture'][$i])){
				$picture_array[] = tep_db_prepare_input($_POST['previous_picture'][$i]);
			}
		}
		
		$pictures = implode(',', $picture_array);

		if ((sizeof($picture_array)<1) && ($check_upload == true)) {
			$error = true;
			$error_image_message= 'Please add at least 1 picture';		
		}
	
	}
	
	return array($error, $error_image_message,$pictures);
	
}

//IMAGES
function get_image_parameters($upload_type,$i,$imagebasename,$random_image_key) {
	
	global $_FILES;
	
	$allowedfiletypes = array("jpeg","jpg","png");
	
	switch ($upload_type) {
		
		case 'preview':
			
			$uploadfolder = "../images/preview/";	
						
		break;
		
		case 'pub':
		
			$uploadfolder = "../images/pub_full/";
			
		break;
		
		case 'article':
		
			$uploadfolder = "../images/article_full/";	
		
		break;
			
	}
	
	// We ensure the given image is actually there
	if(empty($_FILES['image']['name'][$i])){
		//We don't actually give out an error here, as image uploading is not required
		/*
		$error = true;
		$error_image_message= 'Seems your image does not have a name.';
		*/
	} else {
		$uploadfilename[$i] = $_FILES['image']['name'][$i];
		// Get the file extension - to add extra protection for checking mime type
		$fileext[$i] = strtolower(substr($uploadfilename[$i],strrpos($uploadfilename[$i],".")+1));
		// Check to see if the file is allowed
		if (!in_array($fileext[$i],$allowedfiletypes)) { 
			$error = true;
			$error_image_message= 'File Type Not allowed.';
		} else {
			// Name - composed of upload folder - user name - a random key and the file extension
			$new_filename[$i] = $imagebasename.'_'.$random_image_key.'.'.$fileext[$i];
			//echo $new_filename[$i];
			$fulluploadfilename = $uploadfolder . $new_filename[$i] ;
			//echo $fulluploadfilename;
			// Move the file to the specified folder
			if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $fulluploadfilename)) {
				// If everything went well, then we create the thumbnails ... we put all the arguments in an array and use the create thumbnail function
				switch ($upload_type) {
	
					case 'preview':
						
						$image_arguments = array('sourcepath'=> $fulluploadfilename,
												 'image_type' => $fileext[$i],
												 'images' =>array ('target' =>array('../images/preview/'.$new_filename[$i]),
																   'width' =>array (110),
																   'height' =>array (110),
																   'crop_type' => array('crop_center')));
						$error_image_message = create_image_thumbnails($image_arguments);
									
					break;
					
					
					
					case 'pub':
					
						$image_arguments = array('sourcepath'=> $fulluploadfilename,
												 'image_type' => $fileext[$i],
												 'images' =>array ('target' =>array('../images/pub_edit/'.$new_filename[$i],
																					'../images/pub_thumb/'.$new_filename[$i],
																					'../images/pub_big/'.$new_filename[$i]),																					
																   'width' =>array (110,
																					107,
																					250),
																   'height' =>array (110,
																					 87,
																					 0),
																   'crop_type' =>array ('top_center',
																					    'crop_center',
																						'not_boxed')));
					break;
					
					case 'article':
					
						$image_arguments = array('sourcepath'=> $fulluploadfilename,
												 'image_type' => $fileext[$i],
												 'images' =>array ('target' =>array('../images/article_edit/'.$new_filename[$i],
																					'../images/article_small/'.$new_filename[$i],
																					'../images/article_medium_small/'.$new_filename[$i],
																					'../images/article_medium/'.$new_filename[$i],
																					'../images/article_big/'.$new_filename[$i]),
																   'width' =>array (110,
																					90,
																					230,
																					300,
																					700),
																   'height' =>array (110,
																					 124,
																					 300,
																					 350,
																					 700),
																   'crop_type' =>array ('top_center',
																					    'crop_center',
																						'boxed',
																						'boxed',
																						'not_boxed')));
						
					break;										
				
				}
								
				$error_image_message = create_image_thumbnails($image_arguments);
				if ($error_image_message) {
					$error = true;
				} else {
					// If we don't have any error messages, then we mark everything ok - this is only usefull for showing th 
					$image_response[$i] = '<img src="'.$image_arguments['images']['target'][0].'">';
				}				
			} else {
				// If we couldn't upload ... throw an error
				$error = true;
				$error_image_message= 'File Could Not Be Uploaded.';						
			}
		}
	}
	
	return array($error, $error_image_message, $image_response, $new_filename);
	
}


function create_image_thumbnails($image_arguments) {
	
	include_once(DIR_WS_CLASSES . 'zebra_image.php');
	$image = new Zebra_Image();
									
	$number_of_images = count($image_arguments['images']['width']);
	
	for ($i=0;$i<$number_of_images;$i++) {	
				
		$image->source_path = $image_arguments['sourcepath'];
		$image->target_path = $image_arguments['images']['target'][$i];
		if (($image_arguments['image_type']=='jpg') || ($image_arguments['image_type']=='jpeg')) {
			$image->jpeg_quality = 100;
		} else {
			// add quality for png
		}
		$image->preserve_aspect_ratio = true;
		//$image->enlarge_smaller_images = true;
		$image->preserve_time = true;
		
		switch ($image_arguments['images']['crop_type'][$i]) {
		
			case 'crop_center':
			
				$crop_type = ZEBRA_IMAGE_CROP_CENTER;
			
			break;
			
			case 'boxed':
			
				$crop_type = ZEBRA_IMAGE_BOXED;
			
			break;
			
			case 'not_boxed':
			
				$crop_type = ZEBRA_IMAGE_NOT_BOXED;
			
			break;
			
			case 'top_center':
			
				$crop_type = ZEBRA_IMAGE_CROP_TOPCENTER;
			
			break;		
		
		}		
					
		// resize the image to exactly 100x100 pixels by using the "crop from center" method
		// (read more in the overview section or in the documentation)
		//  and if there is an error, check what the error is about
		if (!$image->resize($image_arguments['images']['width'][$i], $image_arguments['images']['height'][$i], $crop_type)) {
			
			// if there was an error, let's see what the error is about
			switch ($image->error) {
				case 1:
					$error_image_message= 'Source file could not be found!';
					break;
				case 2:
					$error_image_message= 'Source file is not readable!';
					break;
				case 3:
					$error_image_message= 'Could not write target file!';
					break;
				case 4:
					$error_image_message= 'Unsupported source file format!';
					break;
				case 5:
					$error_image_message= 'Unsupported target file format!';
					break;
				case 6:
					$error_image_message= 'GD library version does not support target file format!';
					break;
				case 7:
					$error_image_message= 'GD library is not installed!';
					break;			
			}	
		}
	}
		
	return $error_image_message;
}

function get_image_upload_container($pictures,$number_of_pictures,$type) {
	
	$pictures = explode (',',$pictures);
	$output = '';
	
	switch ($type) {
		
		case 'pub' :
			
			$folder = 'pub_edit/';
			
		break;	
		
		case 'article' :
			
			$folder = 'article_edit/';
			
		break;	
	
	}
	
	for($i=0;$i<$number_of_pictures;$i++) {
		
		if (strlen(trim($pictures[$i]))) {
			$item_styles = 'display:block;';
		} else {
			$item_styles = 'display:none;';
		}
							
		$output .= '
		<div class="image_upload_container">
			<div class="image_upload_delete" id="image_delete_'.$i.'" style="'.$item_styles.'">
				<a class="image_upload_delete_button" id="image_activate_delete_'.$i.'"><img src="../images/delete_photo.png" alt="delete_photo"/></a>
			</div>
			<div class="image_upload_preview">
				<input type="file" name="image['.$i.']" class="file_input image_activate_preview" id="imagex_'.$i.'"/>
				<span id="imagex_preview_'.$i.'" class="image_upload_hidden_preview" style="'.$item_styles.'"><img src="../images/'.$folder.$pictures[$i].'"/></span>
				<a class="image_upload_link">Upload photo</a>	
				'.tep_draw_hidden_field('previous_picture['.$i.']',$pictures[$i],'id="previous_picture_'.$i.'"').'							
			</div>
		</div>';
	
	}
	
	return $output;
	
}

/* Create clean urls for the guides added by users */      
function strip($string){
		
		$pattern = SEO_REMOVE_ALL_SPEC_CHARS == 'true'									   
										?        "([^[:alnum:]])"
										:        "/[^a-z0-9- ]/i";

		$string = preg_replace('/((&#39))/', '-', strtolower($string)); //remove apostrophe - not caught by above
		$anchor = preg_replace($pattern, '', strtolower($string));
		$pattern = "([[:space:]]|[[:blank:]])";
		$anchor = preg_replace($pattern, '-', $anchor);
		return short_name($anchor); // return the short filtered name 
} 

function expand($set){
		$container = array();
		if ( $this->not_null($set) ){
				if ( $data = @explode(',', $set) ){
						foreach ( $data as $index => $valuepair){
								$p = @explode('=>', $valuepair);
								$container[trim($p[0])] = trim($p[1]);
						}
						return $container;
				} else {
						return 'false';
				}
		} else {
				return 'false';
		}
} 

function short_name($str, $limit=0){
		$container = array();
		$foo = @explode('-', $str);
		foreach($foo as $index => $value){
				switch (true){
						case ( strlen($value) <= $limit ):
								continue;
						default:
								$container[] = $value;
								break;
				}                
		} # end foreach

		$container = ( sizeof($container) > 1 ? implode('-', $container) : (sizeof($container) > 0 ? $container[0] : $str ));
		return $container;
}
?>