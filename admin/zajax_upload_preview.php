<?php
	require('includes/application_top.php');
	
	$error = false;
		
	$error_image_message = '';
	// Generating a random key to use in the image title
	$random_image_key = rand(1,100000);
	$imagebasename = 'preview_image';
	
	// Upload functionality
	
	// Getting the status of the images and calculating the image number
	$image_number = $_POST['image_number'];
	$i = $image_number;
		
	$upload_image_result = get_image_parameters('preview',$i,$imagebasename,$random_image_key++);
	$error = $upload_image_result[0];
	$error_image_message = $upload_image_result[1];
	$image_response[$i] = $upload_image_result[2][$i];
	$filename[$i] = $upload_image_result[3][$i];
		
	//$_FILES['image']['tmp_name'][$i];
	//$_FILES['image']['type'][$i];
	//$_FILES['image']['size'][$i];
	//echo'<pre>';print_r($_FILES);echo '</pre>';
		
	// Sending the response via JSON for page update
	$response = array('error' => $error,
					  'error_image_message' => $error_image_message,
					  'image_response' => $image_response,
					  'filename' => $filename);	
	
	echo json_encode($response);
	
?>	
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>