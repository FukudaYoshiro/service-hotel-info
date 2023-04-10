<?php
	require('includes/application_top.php');
	
	$error = false;
	
	if ( isset($_POST['formid']) && ($_POST['formid'] == $sessiontoken) && isset($_POST['address']) && (strlen($_POST['address'])<1) ){
	
		$contact_name = tep_db_prepare_input($_POST['contact_us_title']);
		$contact_email = tep_db_prepare_input($_POST['contact_us_email']);
		$contact_comments = tep_db_prepare_input($_POST['contact_us_description']);
		
		$error_title = false;	
		$error_description = false;
		$error_email = false;
		
		if ((strlen($contact_name) < 3))  {
			$error = true;
			$error_title = 'Please enter a valid contact name.';
		}
		
		if ((strlen($contact_email) < 6)) {
			$error = true;
			$error_email = 'Please enter a valid email address.';
		} elseif (tep_validate_email($contact_email) == false) {
			$error = true;
			$error_email = 'Please enter a valid email address.';
		}
		
		if ((strlen($contact_comments) < 20))  {
			$error = true;
			$error_description = 'Your message has to have at least 20 characters.';
		}
	
		if ($error == false ) {
		
			$title = 'Contact from '.STORE_NAME;
			$messsage = get_mail_template($contact_comments);
			tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $title, $messsage, $contact_name , $contact_email);
			add_to_activity_log($title,$message);
			
			$response = array('error' => $error,
							  'error_title' => $error_title,
							  'error_description' => $error_description,
							  'error_email' => $error_email);	
		
		} else {
			
			$response = array('error' => $error,
							  'error_title' => $error_title,
							  'error_description' => $error_description,
							  'error_email' => $error_email);	
			
		}
		
	} else {
		
		// Spam most likely
		$error = true;
		$response = array('error' => $error);
		
	}	
	
	echo json_encode($response);
		
	require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>