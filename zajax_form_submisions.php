<?php

	// Here we process all the forms on the site that are related to creating content or  updating content by users
	
	require('includes/application_top.php');

	$error = false;
	
	$action = tep_db_prepare_input($_POST['action']);
	
	switch($action) {
								
		// Submit a Pub
		case 'pub_create':
		
			// Security Checks
			// Check section permisions, form id and request
			if ( isset($_POST['formid']) && ($_POST['formid'] == $sessiontoken) && isset($_POST['request']) && ($_POST['request'] == 'publish') && isset($_POST['address']) && (strlen($_POST['address'])<1) ){
								
				$pub_name = tep_db_prepare_input($_POST['pub_name']);
				$location_postcode = tep_db_prepare_input($_POST['location_postcode']);
				$pub_description = nl2br($_POST['pub_description']);
				$pub_address = tep_db_prepare_input($_POST['pub_address']);
				$pub_phone = tep_db_prepare_input($_POST['pub_phone']);
				$pub_website = tep_db_prepare_input($_POST['pub_website']);
				$author_email = tep_db_prepare_input($_POST['author_email']);
				
				// We assume there are no errors and we initialize all the error messages as blank
				$error_pub_name = false;	
				$error_location_postcode = false;
				$error_pub_description = false;
				$error_pub_address = false;
				$error_pub_website = false;
				$error_pub_phone = false;
				$error_author_email = false;
				
				// Error Checking		
				// Pub Name
				$pub_name_query = tep_db_query("select pub_name from ".TABLE_PUBS." where pub_name='".$pub_name."' or pub_url = '". strip($pub_name)."'");
				if (tep_db_num_rows($pub_name_query)>0){
					$error = true;
					$error_pub_name = 'It seems we already have this pub in our listings.';	
				} else if ((strlen($pub_name) > 100) || (strlen($pub_name) < 3)) {
					$error = true;
					$error_pub_name = 'Your pub name has to have minimum 3 and maximum 100 characters.';	
				}
				
				$location_postcode_query = tep_db_query("select location_id from ".TABLE_LOCATIONS." where location_postcode='".(int)$location_postcode."'");
				if (tep_db_num_rows($location_postcode_query)<1){
					$error = true;
					$error_location_postcode = 'The postcode you entered is not valid for Australia.';	
				} else {
					$location_id = tep_db_fetch_array($location_postcode_query);				
				}							
				
				// Description
				require_once (DIR_WS_CLASSES . 'library/HTMLPurifier.auto.php');
				$config = HTMLPurifier_Config::createDefault();		
				$purifier = new HTMLPurifier($config);
				$pub_description = $purifier->purify($pub_description);
				if (strlen($pub_description) > 20000) {
					$error = true;
					$error_pub_description = 'The pub description can have maximum 20000 characters.';
				}
								
				// Pub Address
				if (strlen($pub_address) > 100) {
					$error = true;
					$error_pub_address = 'Your pub address can not have more than 100 characters.';
				}
				
				if (strlen($pub_website) > 100) {
					$error = true;
					$error_pub_website = 'Your pub website can not have more than 100 characters.';
				}
				
				if (strlen($pub_phone) > 100) {
					$error = true;
					$error_pub_phone = 'Your pub phone can not have more than 100 characters.';
				}
				
				if (strlen($author_email) > 100) {
					$error = true;
					$error_author_name = 'Your email can not have more than 100 characters.';
				}				
															
				// Image
				// We add an extra variable here since a picture upload is not required
				$check_upload = false;
				// Parsing the images and getting the results, we send the section name, title and error for creating thumbs - see kaftan specific for more details
				$parse_image_result = parse_images('pub',strip($pub_name),$error,$check_upload);
				$error = $parse_image_result[0];
				$error_image_message = $parse_image_result[1];	
				$pictures = $parse_image_result[2];
				// Error Checking End	
						
				//If all is correct and the request is to publish or update we query the info to the database
				if ($error == false) {
					
					$status = DEFAULT_CHEAT_STATUS;
					
					$sql_data_array = array('pub_name' => $pub_name,										
										'pub_description' => $pub_description,
										'pub_address' => $pub_address  ,
										'pub_phone' =>  $pub_phone  ,
										'pub_website' =>  $pub_website  ,
										'pub_pictures' => $pictures ,
										'likes' => 0 ,
										'location_id' => $location_id['location_id'], 
										'pub_url' => strip($pub_name),
										'title_tag' => '',
										'description_tag' => '',
										'keywords_tag' => '',
										'status' => $status,
										'got_from' => $author_email,  
										'date_added' => 'now()');				
				
					tep_db_perform(TABLE_PUBS, $sql_data_array);
					$subsection_id = tep_db_insert_id();
					
					$success_message = '<div class="long_container">
											<p>Thank you for submiting this pub. It will be reviewed shortly by one of our moderators and will be published on the site.<br/><br/>
											We look forward to more interesting pubs from you.<br/><br/></p>
										</div>';
															
					$manage_link = tep_href_link('admin/pubs.php','itemID='.$subsection_id);						
					$status_name_query = tep_db_query("select status_name from ".TABLE_STATUSES." where status_id='".$status."'");
					$status_name = tep_db_fetch_array($status_name_query);
					
					$title = 'A new pub was added on '.STORE_NAME;
					$message = get_mail_template('Hi Administrator,<br/><br/> A new pub was added on '.STORE_NAME.'<br/> Title: '.$subsection_name.'<br/> Content: " '.$subsection_description.' ". <br/> The current status of the pub is: '.$status_name['status_name'].'.<br/><br/>See the the rest details and manage it by following the link: <a href="'.$manage_link.'" target="_blank">MANAGE</a> <br/><br/> Kind Regards,<br/><br/> '.STORE_NAME.' Team');					
					tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $title , $message , STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);	
					tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $title , $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);	
					add_to_activity_log($title,$message);
					
					$response = array(  'error' => $error,
										'error_pub_name' => $error_pub_name,
										'error_location_postcode' => $error_location_postcode,
										'error_pub_description' => $error_pub_description,
										'error_pub_address' => $error_pub_address,
										'error_pub_phone' => $error_pub_phone,
										'error_pub_website' => $error_pub_website,
										'error_author_email' => $error_author_email,
										'error_image' => $error_image_message,
										'success_message' => $success_message);								  
						
					
					
				} else {
				
					$response = array(  'error' => $error,
										'error_pub_name' => $error_pub_name,
										'error_location_postcode' => $error_location_postcode,
										'error_pub_description' => $error_pub_description,
										'error_pub_address' => $error_pub_address,
										'error_pub_phone' => $error_pub_phone,
										'error_pub_website' => $error_pub_website,
										'error_author_email' => $error_author_email,
										'error_image' => $error_image_message,
										'error_image' => $error_image_message);			
										  
				}
				
			} else {
			
				$error = true;				
				$response = array('error' => $error);
			
			}
		
		break;
				
	}
	
	echo json_encode($response);

?>	
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>