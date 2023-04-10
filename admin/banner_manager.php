<?php
	require('includes/application_top.php');
	
	// Add more statistics and integrate the normal image uploaded from the rest of the site
	
	
	$action = (isset($_GET['action']) ? $_GET['action'] : '');
	
	$banner_extension = tep_banner_image_extension();

   	if (tep_not_null($action)) {
    	
		$error = false;
		
		switch ($action) {
			
			case 'setflag':
        		
				tep_db_query("update " . TABLE_BANNERS . " set status = '".$_GET['flag']."' where banners_id = '" . (int)$_GET['itemID'] . "'");
        		tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $_GET['itemID']));
        
			break;   
		   
      		case 'delete_banners_confirm':
        		
				$banners_id = tep_db_prepare_input($_POST['banners_id']);
				
				if (isset($_POST['banners_id'])) {
				
          			if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
					
						$banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
					  	$banner = tep_db_fetch_array($banner_query);
			
					  	if (is_file(DIR_FS_CATALOG_IMAGES .'banners/'. $banner['banners_image'])) {
							
							if (tep_is_writable(DIR_FS_CATALOG_IMAGES .'banners/'. $banner['banners_image'])) {
						  		unlink(DIR_FS_CATALOG_IMAGES .'banners/'. $banner['banners_image']);
							} else {
						  		$messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
							}
					  	
						} else {
					
							$messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
					  	
						}
						
					}
			
					tep_db_query("delete from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
					
					tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");
			
					if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
						
						if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
							if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
						  		unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
							}
					  	}
			
					  	if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
							if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
						  		unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
							}
					  	}
			
					  	if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
							if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
						  		unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
							}
					  	}
			
					  	if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
							if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
						  		unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
							}
					  	}
					}
			
					$messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');					
        		
				}
				
        		tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER));
        	
			break;
			
			case 'insert_banners':
     		case 'update_banners':
			
				if (isset($_GET['itemID']))  {
				
					$banners_id = tep_db_prepare_input($_GET['itemID']);
				
				}
				
				$banners_title = tep_db_prepare_input($_POST['banners_title']);
				$banners_url = tep_db_prepare_input($_POST['banners_url']);
				$new_banners_group = tep_db_prepare_input($_POST['new_banners_group']);
				$banners_group = (empty($new_banners_group)) ? tep_db_prepare_input($_POST['banners_group']) : $new_banners_group;
				$banners_html_text = tep_db_prepare_input($_POST['banners_html_text']);
				$banners_image_local = tep_db_prepare_input($_POST['banners_image_local']);
				$banners_image_target = tep_db_prepare_input($_POST['banners_image_target']);
				$db_image_location = '';
				$expires_date = tep_db_prepare_input($_POST['expires_date']);
				$expires_impressions = tep_db_prepare_input($_POST['expires_impressions']);
				$date_scheduled = tep_db_prepare_input($_POST['date_scheduled']);
		
				$banner_error = false;
				if (empty($banners_title)) {
				  	$messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
				  	$banner_error = true;
				}
		
				if (empty($banners_group)) {
				  	$messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
				  	$banner_error = true;
				}
		
				if (empty($banners_html_text)) {
				  	if (empty($banners_image_local)) {
						$banners_image = new upload('banners_image');
						$banners_image->set_destination(DIR_FS_CATALOG_IMAGES . 'banners/');
						if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
					  		$banner_error = true;
						}
				  	}
				}
		
				if ($banner_error == false) {
				  	
					$db_image_location = (tep_not_null($banners_image_local)) ? $banners_image_local : 'banners/' . $banners_image->filename;
					
				  	$sql_data_array = array('banners_title' => $banners_title,
											'banners_url' => $banners_url,
											'banners_image' => $db_image_location,
											'banners_group' => $banners_group,
											'banners_html_text' => $banners_html_text,
											'expires_date' => 'null',
											'expires_impressions' => 0,
											'date_scheduled' => 'null');
		
				  	if ($action == 'insert_banners') {
						$insert_sql_data = array('date_added' => 'now()',
											 	 'status' => '1');
		
						$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
		
						tep_db_perform(TABLE_BANNERS, $sql_data_array);
		
						$banners_id = tep_db_insert_id();
		
						$messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
				  	
					} elseif ($action == 'update_banners') {
					
						tep_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");
		
						$messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
				  	}
		
				  	if (tep_not_null($expires_date)) {
						
						$expires_date = substr($expires_date, 0, 4) . substr($expires_date, 5, 2) . substr($expires_date, 8, 2);
		
						tep_db_query("update " . TABLE_BANNERS . " set expires_date = '" . tep_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
				  	} elseif (tep_not_null($expires_impressions)) {
						
						tep_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . tep_db_input($expires_impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
				  
				  	}
		
				  	if (tep_not_null($date_scheduled)) {
						
						$date_scheduled = substr($date_scheduled, 0, 4) . substr($date_scheduled, 5, 2) . substr($date_scheduled, 8, 2);
		
						tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . tep_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
				  	
					}
		
				  	tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $_GET['itemID']));
				
				} else {
				  	
					$action = 'new';
				
				}
		
				// Image - Find a way to process images with less code				
				// Parsing the images and getting the results, we send the section name, title and error for creating thumbs - see kaftan specific for more details
				//$parse_image_result = parse_images('banners',tep_db_prepare_input($_POST['banners_name'],$error);
				//$error = $parse_image_result[0];
				//$error_image_message = $parse_image_result[1];	
				//$pictures = $parse_image_result[2];				
				
				/*
				
				$error_image_message = '';
				// Generating a random key to use in the image title
				$random_image_key = rand(1,100000);
				$imagebasename = tep_db_prepare_input($_POST['banners_name']);
				
				// Upload functionality		
				// Getting the status of the images and calculating the image number
				$image_number = sizeof($_POST['previous_picture']);
				for ($i=0;$i<$image_number;$i++) {		
					if ($_POST['previous_picture'][$i]) {
						$image_status[$i] = $i;
					} else {
						$image_status[$i] = 'not';
					}				
				}
				
				// We only process the images if everything else is correct
				if ($error == false) {			
					
					// We go through all the possible image fields
					for ($i=0;$i<$image_number;$i++) {			
						// We only process the image if there is something in that field
						if ($image_status[$i] !== 'not') {
						
							$upload_image_result = get_image_parameters('banners',$i,$imagebasename,$random_image_key++);
							$error = $upload_image_result[0];
							$error_image_message = $upload_image_result[1];
							$new_filename[$i] = $upload_image_result[3][$i];
							//echo '<pre>'; print_r($upload_image_result); echo '</pre>';			
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
			
					if (sizeof($picture_array)<1) {
						$error = true;
						$error_image_message= 'Please add at least 1 picture';		
					}
				
				}	
				
				$sql_data_array['banners_pictures'] = tep_db_prepare_input($pictures);
				
				*/						
			
			break;
     
    	}
  
  	}
	
	// check if the graphs directory exists
  	$dir_ok = false;
  	if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    	if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      		if (tep_is_writable(DIR_WS_IMAGES . 'graphs')) {
        		$dir_ok = true;
      		} else {
        		$messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      		}
    	} else {
      		$messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    	}
  	}
	
	// Creating a new item
	require(DIR_WS_INCLUDES . 'template_top.php');
	
	if ($action == 'new_banners') {
    
		$parameters = array('expires_date' => '',
							'date_scheduled' => '',
							'banners_title' => '',
							'banners_url' => '',
							'banners_group' => '',
							'banners_image' => '',
							'banners_html_text' => '',
							'expires_impressions' => '');

    	$itemInfo = new objectInfo($parameters);

    	if (isset($_GET['itemID']) && empty($_POST)) {
    		$banners_query = tep_db_query("select banners_title, banners_url, banners_image, banners_group, banners_html_text, status, date_format(date_scheduled, '%Y/%m/%d') as date_scheduled, date_format(expires_date, '%Y/%m/%d') as expires_date, expires_impressions, date_status_change from " . TABLE_BANNERS . " where banners_id = '" . (int)$_GET['itemID'] . "'");
			$banners = tep_db_fetch_array($banners_query);
    		$itemInfo->objectInfo($banners);			
						
   	 	}

    	$groups_array = array();
    	$groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS . " order by banners_group");
    	while ($groups = tep_db_fetch_array($groups_query)) {
      		$groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
    	}
					
		$form_action = (isset($_GET['itemID'])) ? 'update_banners' : 'insert_banners';
?>
    	<?php echo tep_draw_form('new_banners', FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data" class="current_form"'); ?>

    	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      		<tr>
        		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          			<tr>
            			<td class="pageHeading">Add / Edit Banner</td>
          			</tr>
        		</table></td>
			</tr>
			<tr>
        		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      		</tr>
      		<tr>
        		<td><table border="0" cellspacing="0" cellpadding="2">
					<!--
					<tr>
            			<td class="main" valign="top">Pictures:</td>
            			<td class="main">--><?php //echo get_image_upload_container($itemInfo->article_pictures,8,'article');?><!--</td>
          			</tr>  
					<tr>
              	    <tr>
						<td colspan="2">-->	<?php //echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?><!--</td>
				  	</tr> 
					-->					
					<tr>
						<td class="main" width="130"><?php echo TEXT_BANNERS_TITLE; ?></td>
						<td class="main"><?php echo tep_draw_input_field('banners_title', $itemInfo->banners_title, 'class="long_text"', true); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_BANNERS_URL; ?></td>
						<td class="main"><?php echo tep_draw_input_field('banners_url', $itemInfo->banners_url, 'class="long_text"'); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
						<td class="main">
						<?php 
						echo tep_draw_pull_down_menu('banners_group', $groups_array, $itemInfo->banners_group,'class="short_select"') . TEXT_BANNERS_NEW_GROUP ;
						echo '<br/><br/>';
						echo tep_draw_input_field('new_banners_group', '', 'class="long_text"', ((sizeof($groups_array) > 0) ? false : true)); 
						?>
						</td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
						<td class="main">
						<?php 
						echo 
						tep_draw_file_field('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br />' 
						. DIR_FS_CATALOG_IMAGES . tep_draw_input_field('banners_image_local', (isset($itemInfo->banners_image) ? $itemInfo->banners_image : ''),' class="long_text" style="width:300px;"'); 
						?>
						</td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td valign="top" class="main"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
						<td class="main"><?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '5', $itemInfo->banners_html_text,'class="long_textarea"'); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?></td>
						<td class="main"><?php echo tep_draw_input_field('date_scheduled', $itemInfo->date_scheduled, 'id="date_scheduled" class="long_text"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td valign="top" class="main"><?php echo TEXT_BANNERS_EXPIRES_ON; ?></td>
						<td class="main">
						<?php 
						echo tep_draw_input_field('expires_date', $itemInfo->expires_date, 'id="expires_date"  class="long_text"') . ' <small>(YYYY-MM-DD)</small>' . TEXT_BANNERS_OR_AT . '<br /><br />' . tep_draw_input_field('expires_impressions', $itemInfo->expires_impressions, 'maxlength="7" size="7"  class="long_text"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
					</tr>						
        		</table></td>
      		</tr>
      		<tr>
        		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      		</tr>
      		<tr>
				<td class="main"><?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?></td>
        		<td class="smallText" align="right" valign="top">
				<?php 
				echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary');
				echo tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) .  (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : ''))); 
				?>
				</td>
      		</tr>
    	</table>
	</form>
	<script type="text/javascript">
	$('#date_scheduled').datepicker({
	  dateFormat: 'yy-mm-dd'
	});
	$('#expires_date').datepicker({
	  dateFormat: 'yy-mm-dd'
	});
	</script>
<?php
	} else {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
    	<tr>
        	<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          		<tr>
            		<td class="pageHeading"><?php echo 'Banners'; ?></td>
            		<td align="right">&nbsp;
						
					</td>
          		</tr>
        	</table></td>
      	</tr>
		<tr>
        	<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          		<tr>
            		<td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              			<tr class="dataTableHeadingRow">
							<td class="dataTableHeadingContent">Banner</td>
							<td class="dataTableHeadingContent" align="right">Group</td>
							<td class="dataTableHeadingContent" align="right">Display/Clicks</td>
							<td class="dataTableHeadingContent" align="right">Status</td>
							<td class="dataTableHeadingContent" align="right">Action&nbsp;</td>
              			</tr>
						<?php
						$banners_count = 0;
						$banners_query_raw  = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added from " . TABLE_BANNERS . " order by banners_title, banners_group";
						
						$banners_split = new splitPageResults($_GET['page'], 50, $banners_query_raw, $banners_query_numrows);
   						$banners_query= tep_db_query($banners_query_raw);
						
						while ($banners = tep_db_fetch_array($banners_query)) {
		
							$banners_count++;
							$rows++;
							
							// Banner Info Query
							$info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners['banners_id'] . "'");
							$info = tep_db_fetch_array($info_query);
							
							if ( (!isset($_GET['itemID']) || (isset($_GET['itemID']) && ($_GET['itemID'] == $banners['banners_id']))) && !isset($itemInfo) && (substr($action, 0, 3) != 'new')) {									
								
								// Merging all the results in one big array							
								$itemInfo_array = array_merge($banners, $info);
								$itemInfo = new objectInfo($itemInfo_array);
      						
							}
							
							$banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
      						$banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';

							if (isset($itemInfo) && is_object($itemInfo) && ($banners['banners_id'] == $itemInfo->banners_id) ) {
								echo '<tr id="defaultSelected" class="dataTableRowSelected">' . "\n";
							} else {
								echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . '&itemID=' . $banners['banners_id']) . '\'">' . "\n";
							}
							?>
								<td class="dataTableContent"><?php echo '&nbsp;' . $banners['banners_title']; ?></td>
								<td class="dataTableContent" align="right"><?php echo $banners['banners_group']; ?></td>
								<td class="dataTableContent" align="right"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
								<td class="dataTableContent" align="center">
								<?php
								// Statuses
								if ($banners['status'] == 1) {
									echo '<b>Active</b>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=0&itemID=' . $banners['banners_id']) . '">Disabled</a>';
								} else if ($banners['status'] == 0) {
									echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $banners['banners_id']) . '">Active</a>&nbsp;&nbsp;<b>Disabled</b>';
								} 		
								?>
								</td>
								<td class="dataTableContent" align="right">
								<?php 
								echo tep_image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS) . '</a>&nbsp;'; 
								if (isset($itemInfo) && is_object($itemInfo) && ($banners['banners_id'] == $itemInfo->banners_id)) { 
									echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
								} else { 
									echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . '&itemID=' . $banners['banners_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
								} 
								?>&nbsp;
								</td>
							</tr>
							<?php
    					}
						?>
						<tr>
							<td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
						  		<tr>
									<td class="smallText" valign="top"><?php echo $banners_split->display_count($banners_query_numrows, 50, $_GET['page'], 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items)'); ?></td>
									<td class="smallText" align="right"><?php echo $banners_split->display_links($banners_query_numrows, 50, 10, $_GET['page'], tep_get_all_get_params(array('page', 'itemID', 'action'))); ?></td>
						  		</tr>
						</table></td>
					  </tr>
						<tr>
                			<td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  				<tr>
                    				<td class="smallText"></td>
                    				<td align="right" class="smallText">
									<?php   echo tep_draw_button('New Banner', 'plus', tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'action=new_banners')); ?>&nbsp;
									</td>
                  				</tr>
                			</table></td>
              			</tr>
					</table></td>
				<?php
    
				$heading = array();
				$contents = array();
				switch ($action) {
					
					case 'delete_banners':
					
						$heading[] = array('text' => '<strong>Delete banners</strong>');
						$contents = array('form' => tep_draw_form('banners', FILENAME_BANNER_MANAGER, 'action=delete_banners_confirm') . tep_draw_hidden_field('banners_id', $itemInfo->banners_id));
						$contents[] = array('text' => 'Are you sure you want to delete this item? By deleting this item you will delete any other content that is set as a child for it (comments,likes etc.).');
						$contents[] = array('text' => '<br /><strong>' . $itemInfo->banners_title . '</strong>');
						$contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->banners_id)));
					
					break;
			
					default:
					
						if ($rows > 0) {
							if (isset($itemInfo) && is_object($itemInfo)) { 
								$heading[] = array('text' => '<strong>' . $itemInfo->banners_title . '</strong>');
								// Add the param for live preview in new window later on
								//$params['newwindow'] = 1;
								$contents[] = array('align' => 'center', 'text' => 
								tep_draw_button('Edit', 'document', tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->banners_id.'&action=new_banners')).
								tep_draw_button('Delete', 'trash', tep_href_link(FILENAME_BANNER_MANAGER, tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->banners_id . '&action=delete_banners')).
								//tep_draw_button('Preview', 'document', tep_catalog_href_link('banners_details.php', 'banners_id=' . $itemInfo->banners_id.'&method=preview'),null, $params) . 
								'<br/>');
								$contents[] = array('text' => '<b class="infoBoxContent_section">General Information</b>');
								$contents[] = array('text' => '<b>Date added:</b> ' . tep_date_short($itemInfo->date_added));
								
								if ($itemInfo->expires_date) {
								  	$contents[] = array('text' => '<b>' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, tep_date_short($itemInfo->expires_date)).'</b>');
								} elseif ($itemInfo->expires_impressions) {
								  	$contents[] = array('text' => '<b>' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $itemInfo->expires_impressions).'</b>');
								}
						
								if ($itemInfo->date_status_change) {
									$contents[] = array('text' => '<b>' . sprintf(TEXT_BANNERS_STATUS_CHANGE, tep_date_short($itemInfo->date_status_change)).'</b>');
								}
								
								if ($itemInfo->date_scheduled) {
									$contents[] = array('text' => '<b>' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, tep_date_short($itemInfo->date_scheduled)).'</b>');
								}		
								
								if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
								  	$banner_id = $itemInfo->banners_id;
								  	$days = '3';
								  	include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
								  	$contents[] = array('align' => 'center', 'text' => '<br />' . tep_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
								} else {
								  	include(DIR_WS_FUNCTIONS . 'html_graphs.php');
								  	$contents[] = array('align' => 'center', 'text' => '<br />' . tep_banner_graph_infoBox($itemInfo->banners_id, '3'));
								}
						
								$contents[] = array('text' => tep_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . tep_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);
								
								/*
								$contents[] = array('text' => '<b>Images:</b> <br/>');
								$pictures = explode (',',$itemInfo->banners_pictures);
								$number_of_pictures = sizeof($pictures);
								for($i=0;$i<$number_of_pictures;$i++) {
									$show_pictures.= '<img src="'.DIR_WS_CATALOG_IMAGES.'/banners_edit/'.$pictures[$i].'">&nbsp;&nbsp;';
								}       
								$contents[] = array('text' => $show_pictures.'<br/><br/>');  
								*/
							}
						} else { 
							$heading[] = array('text' => '<strong> No banners </strong>');
							$contents[] = array('text' => 'No banners');
						}
					
					break;
				
				}
	
				if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
			
					echo '<td width="35%" valign="top" class="infoBox">' . "\n";
					$box = new box;
					echo $box->infoBox($heading, $contents);
					echo '</td>' . "\n";
				
				}
			}
	
			?>
				</tr>
			</table></td>
		</tr>
	</table>

<?php
  	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>