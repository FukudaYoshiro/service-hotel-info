<?php
// ACTIVITY LOG
function add_to_activity_log($title,$message) {

	$sql_data_array = array('activity_title' => $title,
							'activity_message' => $message,
							'date_added' => 'now()');

	tep_db_perform(TABLE_ACTIVITY, $sql_data_array);

}

// EMAIL
function get_mail_template($message) {

	$email_template = '<div style="width:740px;float:left;font-family: arial;">'.
					   		'<div style="width:740px;float:left;padding-top:10px;padding-bottom: 10px;text-align:center;border-bottom:2px solid #1D2675;">'.
								'<a href="'.tep_href_link(FILENAME_DEFAULT).'"  target="_blank"><img src="'.HTTP_SERVER. DIR_WS_HTTP_CATALOG. DIR_WS_IMAGES.'/logo.png" alt="'.STORE_NAME.' Logo" /></a> <br/>'.
							'</div>'.
							'<div style="width:720px;float:left;margin-top:10px;margin-bottom:10px;text-align:left;padding-left:10px;padding-right:10px;line-height:18px;">'.
								$message.
							'</div>'.
						'</div>';

	return $email_template;

}

//IMAGES
//We first check all the images, check for first hand errrors and set up some base parameters
function parse_images($section_name,$imagebasename,$error, $check_upload=true) {

	global $_POST;

	$error_image_message = '';
	// Generating a random key to use in the image title
	$random_image_key = rand(1,100000);

	// Upload functionality
	// Getting the status of the images and calculating the image number
	$image_status = $_POST['image_status'];
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

// Further checking for errors, setting the directories for upload and image dimensions
function get_image_parameters($upload_type,$i,$imagebasename,$random_image_key) {

	global $_FILES;

	$allowedfiletypes = array("jpeg","jpg","png");

	switch ($upload_type) {

		case 'preview':

			$uploadfolder = "images/preview/";

		break;

		case 'pub':

			$uploadfolder = "images/pub_full/";

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
			$fulluploadfilename = $uploadfolder . $new_filename[$i] ;
			// Move the file to the specified folder
			if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $fulluploadfilename)) {
				// If everything went well, then we create the thumbnails ... we put all the arguments in an array and use the create thumbnail function
				switch ($upload_type) {

					case 'preview':

						$image_arguments = array('sourcepath'=> $fulluploadfilename,
												 'image_type' => $fileext[$i],
												 'images' =>array ('target' =>array('images/preview/'.$new_filename[$i]),
																   'width' =>array (110),
																   'height' =>array (110),
																   'crop_type' => array('crop_center')));
						$error_image_message = create_image_thumbnails($image_arguments);

					break;



					case 'pub':

						$image_arguments = array('sourcepath'=> $fulluploadfilename,
												 'image_type' => $fileext[$i],
												 'images' =>array ('target' =>array('images/pub_edit/'.$new_filename[$i],
																					'images/pub_thumb/'.$new_filename[$i],
																					'images/pub_big/'.$new_filename[$i]),
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

// After the images are uploaded we create the thumbnails with the zebra class
function create_image_thumbnails($image_arguments) {

	include_once(DIR_WS_CLASSES . 'zebra_image.php');
	$image = new Zebra_Image();

	$number_of_images = count($image_arguments['images']['width']);

	for ($i=0;$i<$number_of_images;$i++) {

		$image->source_path = $image_arguments['sourcepath'];
		$image->target_path = $image_arguments['images']['target'][$i];
		if (($image_arguments['image_type']=='jpg') || ($image_arguments['image_type']=='jpeg')) {
			$image->jpeg_quality = 100;
			// echo $image_arguments['image_type']; die();
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

// Loading the image upload container on the form pages
function get_image_upload_container($pictures,$number_of_pictures,$type) {

	$pictures = explode (',',$pictures);
	$output = '';

	switch ($type) {

		case 'pub' :

			$folder = 'pub_edit/';

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
				<a class="image_upload_delete_button" id="image_activate_delete_'.$i.'"><img src="images/delete_photo.png" alt="delete_photo"/></a>
			</div>
			<div class="image_upload_preview">
				<input type="file" name="image['.$i.']" class="file_input image_activate_preview" id="imagex_'.$i.'"/>
				<span id="imagex_preview_'.$i.'" class="image_upload_hidden_preview" style="'.$item_styles.'"><img src="images/'.$folder.$pictures[$i].'"/></span>
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

// Get Platfrom Colors
function get_platform_color($id){

	switch($id) {

		default:
			$color = 'dark_green';
		break;

	}

	return $color;

}

// END PERMISIONS
// TEMPLATES

function article_template($description,$pictures) {

	$pictures = str_replace(' ','%20',$pictures);
	$pictures = explode (',',$pictures);
	$number_of_pictures = sizeof($pictures);

	switch ($number_of_pictures) {

		case 1:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[0].'" alt="Article Picture" class="zoom" /></a>
			</p>
			<p>
				'.$description.'
			</p>';

		break;

		case 2:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[0].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
				<a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[1].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
			</p>
			<p>
				'.$description.'
			</p>';

		break;

		case 3:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>';

		break;

		case 4:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>
			<p>
				<a href="images/article_big/'.$pictures[3].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[3].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
			</p>';

		break;

		case 5:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>
			<p>
				<a href="images/article_big/'.$pictures[3].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[3].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
				<a href="images/article_big/'.$pictures[4].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[4].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
			</p>';

		break;

		case 6:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>
			<p>
				<a href="images/article_big/'.$pictures[3].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[3].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[4].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[4].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[5].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[5].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>';

		break;

		case 7:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>
			<p>
				<a href="images/article_big/'.$pictures[3].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[3].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[4].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[4].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[5].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[5].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				<a href="images/article_big/'.$pictures[6].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[6].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
			</p>';

		break;

		case 8:

			$output ='
			<p>
				<a href="images/article_big/'.$pictures[0].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[0].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[1].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[1].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[2].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[2].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				'.$description.'
			</p>
			<p>
				<a href="images/article_big/'.$pictures[3].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[3].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			    <a href="images/article_big/'.$pictures[4].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[4].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
				<a href="images/article_big/'.$pictures[5].'" rel="lightbox[set]" class="image_container_more"><img src="images/article_medium_small/'.$pictures[5].'" alt="Article Picture" class="zoom" width="230" height="300"/></a>
			</p>
			<p>
				<a href="images/article_big/'.$pictures[6].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[6].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
				<a href="images/article_big/'.$pictures[7].'" rel="lightbox[set]" class="image_container"><img src="images/article_medium/'.$pictures[7].'" alt="Article Picture" class="zoom" width="300" height="350"/></a>
			</p>';

		break;

	}

	return $output;

}

function collection_template($description,$pictures,$where_to_buy,$category_name,$price_range,$tags) {

	$pictures = str_replace(' ','%20',$pictures);
	$pictures = explode (',',$pictures);
	$number_of_pictures = sizeof($pictures);

	if ($where_to_buy) {
		$where_to_buy = '<div class="row grey_row">
							<div class="row_label">
								Where to buy:
							</div>
							<div class="row_content">
								'.$where_to_buy.'
							</div>
						</div>';
	}
	if ($tags) {
		$tags = '<div class="row grey_row">
					<div class="row_label">
						Where to buy:
					</div>
					<div class="row_content">
						'.$tags.'
					</div>
				</div>';
	}

	$output='
		<div class="info_table">
			'.$where_to_buy.'
			<div class="row white_row">
				<div class="row_label">
					Category:
				</div>
				<div class="row_content">
					'.$category_name.'
				</div>
			</div>
			<div class="row grey_row">
				<div class="row_label">
					Price range:
				</div>
				<div class="row_content">
					'.$price_range.'
				</div>
			</div>
			'.$tags.'
		</div>
		<p>'.$description.'</p>
		<p>';
			for($i=0;$i<$number_of_pictures;$i++) {
				$output .= '<a href="images/collection_big/'.$pictures[$i].'" rel="lightbox[set]" class="image_container_more"><img src="images/collection_medium/'.$pictures[$i].'" alt="Collection Picture" class="zoom" width="230" height="300"/></a>';
			}
		$output .=
		'</p>';

	return $output;

}

// LIKE BUTTONS
function get_like_button($type,$id,$likes,$add_fixer = false) {

	if ($add_fixer == true ) {
		$fixer = ' like_button_fixer';
	} else {
		$fixer = ' ';
	}
	/*** Replaced this output with sprite version(remove <img>) ***/
	// $output = '<span class="like_container '.$fixer.'"><span class="like_count"  id="'.$type.'_'. $id .'" >'. $likes.' Likes </span><a class="like_button"  onclick="return like(\''.$type.'\','. $id .');"><img src="images/like_icon.png" alt="Like" title="Like" width="14" height="17"></a></span>';

	$output = '<span class="like_container '.$fixer.'"><span class="like_count"  id="'.$type.'_'. $id .'" >'. $likes.' Likes </span><a class="like_button"  onclick="return like(\''.$type.'\','. $id .');"></a></span>';

	return $output;

}

?>
