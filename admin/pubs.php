<?php
	require('includes/application_top.php');
	$action = (isset($_GET['action']) ? $_GET['action'] : '');

   	if (tep_not_null($action)) {
    	
		$error = false;
		
		switch ($action) {
			
			case 'setflag':
        		
				tep_db_query("update " . TABLE_PUBS . " set status = '".$_GET['flag']."' where pub_id = '" . (int)$_GET['itemID'] . "'");
        		tep_redirect(tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $_GET['itemID']));
        
			break;   
		   
      		case 'delete_pub_confirm':
        		
				if (isset($_POST['pub_id'])) {
				
          			$pub_id = tep_db_prepare_input($_POST['pub_id']);
					
					// Delete the actual Item
          			tep_db_query("delete from " . TABLE_PUBS . " where pub_id = '" . (int)$pub_id . "'");
					// Delete the likes of the item (if this applies)
					tep_db_query("delete from " . TABLE_LIKES . " where item_id = '" . (int)$article_id . "' and section_id='2'");
					
        		}
				
        		tep_redirect(tep_href_link('pubs.php'));
        	
			break;
			
			case 'insert_pub':
     		case 'update_pub':
			
				if (isset($_GET['itemID']))  {
				
					$pub_id = tep_db_prepare_input($_GET['itemID']);
				
				}
					
				// We might add error checking for admins as well later on
								
				$sql_data_array = array('pub_name' => tep_db_prepare_input($_POST['pub_name']),										
										'pub_description' => tep_db_prepare_input($_POST['pub_description']),
										'pub_address' => tep_db_prepare_input($_POST['pub_address']) ,
										'pub_phone' => tep_db_prepare_input($_POST['pub_phone']) ,
										'pub_website' => tep_db_prepare_input($_POST['pub_website']) ,
										'location_id' => tep_db_prepare_input($_POST['location_id']) ,
										'pub_url' => strip(tep_db_prepare_input($_POST['pub_name'])),
										'title_tag' => tep_db_prepare_input($_POST['title_tag']),
										'description_tag' => tep_db_prepare_input($_POST['description_tag']),
										'keywords_tag' => tep_db_prepare_input($_POST['keywords_tag']),
										'got_from'  => tep_db_prepare_input($_POST['got_from']),
										'status' => tep_db_prepare_input($_POST['status']));
										
						
				// Image - Find a way to process images with less code				
				// Parsing the images and getting the results, we send the section name, title and error for creating thumbs - see kaftan specific for more details
				//$parse_image_result = parse_images('pub',tep_db_prepare_input($_POST['pub_name'],$error);
				//$error = $parse_image_result[0];
				//$error_image_message = $parse_image_result[1];	
				//$pictures = $parse_image_result[2];				
				
				$error_image_message = '';
				// Generating a random key to use in the image title
				$random_image_key = rand(1,100000);
				$imagebasename = strip(tep_db_prepare_input($_POST['pub_name']));
				
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
							$upload_image_result = get_image_parameters('pub',$i,$imagebasename,$random_image_key++);
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
			
					if (sizeof($picture_array)<1) {
						$error = true;
						$error_image_message= 'Please add at least 1 picture';		
					}
				
				}	
				
				$sql_data_array['pub_pictures'] = tep_db_prepare_input($pictures);
										
				// Update		
				if ($action == 'update_pub') {
					tep_db_perform(TABLE_PUBS, $sql_data_array, 'update', "pub_id = '" . (int)$pub_id . "'");
				}
				
				// Insert
				if ($action == 'insert_pub') {
					$sql_data_array['date_added'] = 'now()';
					tep_db_perform(TABLE_PUBS, $sql_data_array);
					$pub_id  = tep_db_insert_id();
				}					
				
				tep_redirect(tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $pub_id));			
			
			break;
     
    	}
  
  	}
	
	// Creating a new item
	require(DIR_WS_INCLUDES . 'template_top.php');
	
	if ($action == 'new_pub') {
    
		$parameters = array('pub_name' => '',										
							'pub_description' => '',
							'pub_address' => '' ,
							'pub_phone' => '' ,
							'pub_website' => '' ,
							'location_id' => '' ,
							'title_tag' => '',
							'description_tag' => '',
							'keywords_tag' => '',
							'got_from'  => '',
							'status' => '');

    	$itemInfo = new objectInfo($parameters);

    	if (isset($_GET['itemID']) && empty($_POST)) {
    		$pub_query = tep_db_query("select pub_id, pub_name, pub_description, pub_phone, pub_address, pub_website, location_id, pub_pictures, title_tag, description_tag, keywords_tag, got_from, status, date_added from " . TABLE_PUBS . " where pub_id = '" . (int)$_GET['itemID'] . "'");
			$pub = tep_db_fetch_array($pub_query);
    		$itemInfo->objectInfo($pub);			
						
   	 	}
		
    	$status_array = array();
		$status_query = tep_db_query("select status_id, status_name from ".TABLE_STATUSES." order by status_name");
  		while ($status = tep_db_fetch_array($status_query)) {
			$status_array[] = array('id' => $status['status_id'], 'text' => $status['status_name']);
		}
		
		$location_array = array();
		$location_query = tep_db_query("select l.location_id, l.location_city, lz.location_zone_name, l.location_postcode from ".TABLE_LOCATIONS." l left join ".TABLE_LOCATIONS_ZONES." lz on l.location_zone_id = lz.location_zone_id order by l.location_city");
  		while ($location = tep_db_fetch_array($location_query)) {
			$location_array[] = array('id' => $location['location_id'], 'text' => $location['location_city'].' - '.$location['location_zone_name'] . ' ('.$location['location_postcode'].')' );
		}
					
		$form_action = (isset($_GET['itemID'])) ? 'update_pub' : 'insert_pub';
?>
    	<?php echo tep_draw_form('new_pub', 'pubs.php', tep_get_all_get_params(array('itemID', 'action')) . (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data" class="current_form"'); ?>

    	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      		<tr>
        		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          			<tr>
            			<td class="pageHeading">Add / Edit Pub</td>
          			</tr>
        		</table></td>
			</tr>
			<tr>
        		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      		</tr>
      		<tr>
        		<td><table border="0" cellspacing="0" cellpadding="2">
					<tr>
            			<td class="main" valign="top" colspan="2"><b>SEO and Content</b></td>
          			</tr>
          			<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
		  			<tr>
            			<td class="main" valign="top" width="130">Name:</td>
            			<td class="main"><?php echo tep_draw_input_field('pub_name', $itemInfo->pub_name,'class="long_text"'); ?></td>
          			</tr>
					<tr>
            			<td class="main" valign="top">&nbsp;</td>
            			<td class="main">
							<ul>
								<li style="color:red;">Please note that the URL of the page is automatically created based on the name of the page. Therefore when changing the name of the page, this will also affect SEO.</li>
							</ul>						
						</td>
         			</tr>								  			               
          			<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
            			<td class="main" valign="top">Pub Location:</td>
            			<td class="main"><?php echo tep_draw_pull_down_menu('location_id', $location_array, $itemInfo->location_id,'class="short_select"'); ?></td>
          			</tr> 
          			<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
          			<tr>
            			<td class="main" valign="top">Description:</td>
						<td class="main"><?php echo tep_draw_textarea_field('pub_description', 'soft', '70', '15', $itemInfo->pub_description,'class="ckeditor"'); ?></td>
          			</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  	</tr>  
					<tr>
            			<td class="main" valign="top">Pictures:</td>
            			<td class="main"><?php echo get_image_upload_container($itemInfo->pub_pictures,1,'pub');?></td>
          			</tr> 
              	    <tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  	</tr>
					<tr>
            			<td class="main" valign="top">Page Title:</td>
            			<td class="main"><?php echo tep_draw_textarea_field('title_tag', 'soft', '70', '15', $itemInfo->title_tag,'class="long_textarea"'); ?></td>
         			</tr> 
					<tr>
            			<td class="main" valign="top">&nbsp;</td>
            			<td class="main">
							<ul>
								<li>If left blank, the page title will auto generate based on platform, pub, guide and page name.</li>
								<li style="color:red;">Meta Keywords do not self generate. Google takes into consideration meta descriptions for SEO. Yahoo also takes into consideration meta keywords.</li>
							</ul>						
						</td>
         			</tr>	
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr> 	
					<tr>
            			<td class="main" valign="top">Meta Description:</td>
						<td class="main"><?php echo tep_draw_textarea_field('description_tag', 'soft', '70', '15', $itemInfo->description_tag,'class="long_textarea"'); ?></td>
         			</tr> 	
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
            			<td class="main" valign="top">Meta Keywords:</td>
						<td class="main"><?php echo tep_draw_textarea_field('keywords_tag', 'soft', '70', '15', $itemInfo->keywords_tag,'class="long_textarea"'); ?></td>
         			</tr> 	
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
            			<td class="main" valign="top" colspan="2"><b>General Information</b></td>
          			</tr> 
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
           				<td class="main" valign="top">Pub Address:</td>
            			<td class="main"><?php echo tep_draw_input_field('pub_address', $itemInfo->pub_address,'class="long_text"'); ?></td>
          			</tr>
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
		   			<tr>
           				<td class="main" valign="top">Pub Phone:</td>
            			<td class="main"><?php echo tep_draw_input_field('pub_phone', $itemInfo->pub_phone,'class="long_text"'); ?></td>
          			</tr>
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
           				<td class="main" valign="top">Pub Website:</td>
            			<td class="main"><?php echo tep_draw_input_field('pub_website', $itemInfo->pub_website,'class="long_text"'); ?></td>
          			</tr>
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
					<tr>
           				<td class="main" valign="top">From:</td>
            			<td class="main"><?php echo tep_draw_input_field('got_from', $itemInfo->got_from,'class="long_text"'); ?></td>
          			</tr>
					<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>						
          			<tr>
            			<td class="main" valign="top">Status:</td>
            			<td class="main"><?php echo tep_draw_pull_down_menu('status', $status_array, $itemInfo->status,'class="short_select"'); ?></td>
          			</tr> 			  			               
          			<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>					
        		</table></td>
      		</tr>
      		<tr>
        		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      		</tr>
      		<tr>
        		<td class="smallText" align="right">
				<?php 
				echo tep_draw_hidden_field('date_added', (tep_not_null($itemInfo->date_added) ? $itemInfo->date_added : date('Y-m-d')));
				echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary');
				echo tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) .  (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : ''))); 
				?>
				</td>
      		</tr>
    	</table>
	</form>
	<script type="text/javascript">
	$('#release_date').datepicker({
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
            		<td class="pageHeading" valign="top"><?php echo 'Pubs'; ?></td>
            		<td align="right">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
              				<tr>
                				<td class="smallText" align="right">
									<?php
										echo tep_draw_form('search', 'pubs.php', tep_get_all_get_params(array('itemID', 'action')) . '', 'get');
										echo 'Search: ' . tep_draw_input_field('search','','class="short_text"');
										echo tep_hide_session_id() . '</form>';
									?>
                				</td>
              				</tr>							
							<tr>
								<td class="smallText" align="right" style="line-height:22px;font-size:14px;">
									<?php
									echo '<a href="'.tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')).'filterID=1').'">'; 
									if ((int)$_GET['filterID']==1) {
										echo '<b>No Images</b>';
									} else {
										echo 'No Images';
									}										
									echo '</a>&nbsp;&nbsp;';
									echo '<a href="'.tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')).'filterID=2').'">'; 
									if ((int)$_GET['filterID']==2) {
										echo '<b>Active</b>';
									} else {
										echo 'Active';
									}										
									echo '</a>&nbsp;&nbsp;';
									echo '<a href="'.tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')).'filterID=3').'">'; 
									if ((int)$_GET['filterID']==3) {
										echo '<b>Pending</b>';
									} else {
										echo 'Pending';
									}										
									echo '</a>&nbsp;&nbsp;';
									echo '<a href="'.tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')).'filterID=4').'">'; 
									if ((int)$_GET['filterID']==4) {
										echo '<b>Disabled</b>';
									} else {
										echo 'Disabled';
									}
									?>
								</td>
							</tr>
           			 	</table>
					</td>
          		</tr>
        	</table></td>
      	</tr>
		<tr>
        	<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          		<tr>
            		<td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              			<tr class="dataTableHeadingRow">
                			<td class="dataTableHeadingContent">Pub</td>
                			<td class="dataTableHeadingContent" align="center">Status</td>
                			<td class="dataTableHeadingContent" align="right">Action&nbsp;</td>
              			</tr>
						<?php
						$pubs_count = 0;
						
						if ((isset($_GET['filterID'])) && ($_GET['filterID']>0)) {
							switch ((int)$_GET['filterID']) {
							
								case 1:
									$where = "and p.pub_pictures=''";
								break;
								case 2:
									$where = "and p.status='1'";
								break;
								case 3:
									$where = "and p.status='2'";
								break;
								case 4:
									$where = "and p.status='3'";
								break;
							
							}
						}
						
						if (isset($_GET['search'])) {
							$search = tep_db_prepare_input($_GET['search']);
							$pubs_query_raw ="select p.pub_id, p.pub_name, p.pub_description, p.pub_phone, p.pub_address, p.pub_website, p.location_id, p.pub_pictures, p.title_tag, p.description_tag, p.keywords_tag, p.got_from, p.likes, p.status, p.date_added from " . TABLE_PUBS . " p where p.pub_name like '%" . tep_db_input($search) . "%' order by p.date_added DESC, p.pub_name";
						} else {
							$pubs_query_raw  = "select p.pub_id, p.pub_name, p.pub_description, p.pub_phone, p.pub_address, p.pub_website, p.location_id, p.pub_pictures, p.title_tag, p.description_tag, p.keywords_tag, p.got_from, p.likes, p.status, p.date_added from " . TABLE_PUBS . " p  where p.pub_id>'0' ".$where." order by p.date_added DESC, p.pub_name";
						}
						
						$pubs_split = new splitPageResults($_GET['page'], 50, $pubs_query_raw, $pubs_query_numrows);
   						$pubs_query= tep_db_query($pubs_query_raw);
						
						while ($pubs = tep_db_fetch_array($pubs_query)) {
		
							$pubs_count++;
							$rows++;

							if ( (!isset($_GET['itemID']) || (isset($_GET['itemID']) && ($_GET['itemID'] == $pubs['pub_id']))) && !isset($itemInfo) && (substr($action, 0, 3) != 'new')) {								
																														
								$location_query = tep_db_query("select l.location_city, lz.location_zone_name, l.location_postcode from ".TABLE_LOCATIONS." l left join ".TABLE_LOCATIONS_ZONES." lz on l.location_zone_id = lz.location_zone_id where l.location_id = '" . (int)$pubs['location_id'] . "' order by l.location_city");
								$location = tep_db_fetch_array($location_query);
								
								// Merging all the results in one big array							
								$itemInfo_array = array_merge($pubs, $location);
								$itemInfo = new objectInfo($itemInfo_array);
      						
							}

							if (isset($itemInfo) && is_object($itemInfo) && ($pubs['pub_id'] == $itemInfo->pub_id) ) {
								echo '<tr id="defaultSelected" class="dataTableRowSelected">' . "\n";
							} else {
								echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . '&itemID=' . $pubs['pub_id']) . '\'">' . "\n";
							}
							?>
								<td class="dataTableContent"><?php echo '&nbsp;' . $pubs['pub_name']; ?></td>
								<td class="dataTableContent" align="center">
								<?php
								// Statuses
								if ($pubs['status'] == 1) {
									echo '<b>Active</b>&nbsp;&nbsp;<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $pubs['pub_id']) . '">Pending</a>&nbsp;&nbsp;<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $pubs['pub_id']) . '">Disabled</a>';
								} else if ($pubs['status'] == 2) {
									echo '<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $pubs['pub_id']) . '">Active</a>&nbsp;&nbsp;<b>Pending</b>&nbsp;&nbsp;<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $pubs['pub_id']) . '">Disabled</a>';
								} else if ($pubs['status'] == 3) {
									echo '<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $pubs['pub_id']) . '">Active</a>&nbsp;&nbsp;<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $pubs['pub_id']) . '">Pending</a>&nbsp;&nbsp;<b>Disabled</b>';
								} 
								?>
								</td>
								<td class="dataTableContent" align="right">
								<?php 
								if (isset($itemInfo) && is_object($itemInfo) && ($pubs['pub_id'] == $itemInfo->pub_id)) { 
									echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
								} else { 
									echo '<a href="' . tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $pubs['pub_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
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
									<td class="smallText" valign="top"><?php echo $pubs_split->display_count($pubs_query_numrows, 50, $_GET['page'], 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items)'); ?></td>
									<td class="smallText" align="right"><?php echo $pubs_split->display_links($pubs_query_numrows, 50, 10, $_GET['page'], tep_get_all_get_params(array('page', 'itemID', 'action'))); ?></td>
						  		</tr>
						</table></td>
					  </tr>
						<tr>
                			<td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  				<tr>
                    				<td class="smallText"></td>
                    				<td align="right" class="smallText">
									<?php   echo tep_draw_button('New Pub', 'plus', tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=new_pub')); ?>&nbsp;
									</td>
                  				</tr>
                			</table></td>
              			</tr>
					</table></td>
				<?php
    
				$heading = array();
				$contents = array();
				switch ($action) {
					
					case 'delete_pub':
					
						$heading[] = array('text' => '<strong>Delete pub</strong>');
						$contents = array('form' => tep_draw_form('pubs', 'pubs.php', 'action=delete_pub_confirm') . tep_draw_hidden_field('pub_id', $itemInfo->pub_id));
						$contents[] = array('text' => 'Are you sure you want to delete this item? By deleting this item you will NOT delete anything else related to it (cheats). This is a safety measure to ensure that aditional content will not be lost if a mistake is made.');
						$contents[] = array('text' => '<br /><strong>' . $itemInfo->pub_name . '</strong>');
						$contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->pub_id)));
					
					break;
			
					default:
					
						if ($rows > 0) {
							if (isset($itemInfo) && is_object($itemInfo)) { 
								$heading[] = array('text' => '<strong>' . $itemInfo->pub_name . '</strong>');
								// Add the param for live preview in new window later on
								//$params['newwindow'] = 1;
								$contents[] = array('align' => 'center', 'text' => 
								tep_draw_button('Edit', 'document', tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->pub_id.'&action=new_pub')).
								tep_draw_button('Delete', 'trash', tep_href_link('pubs.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->pub_id . '&action=delete_pub')).
								//tep_draw_button('Preview', 'document', tep_catalog_href_link('pubs_details.php', 'pub_id=' . $itemInfo->pub_id.'&method=preview'),null, $params) . 
								'<br/>');
								$contents[] = array('text' => '<b class="infoBoxContent_section">General Information</b>');
								$contents[] = array('text' => '<b>Date added:</b> ' . tep_date_short($itemInfo->date_added));
								$contents[] = array('text' => '<b>Location:</b> '.$itemInfo->location_city.' - '.$itemInfo->location_zone_name.' - '.$itemInfo->location_postcode );								
								$contents[] = array('text' => '<b>Address:</b> '.$itemInfo->pub_address);
								$contents[] = array('text' => '<b>Phone:</b> '.$itemInfo->pub_phone);
								$contents[] = array('text' => '<b>Website:</b> '.$itemInfo->pub_website);
								$contents[] = array('text' => '<b>From:</b> '.$itemInfo->got_from);
								$contents[] = array('text' => '<b>Likes:</b> '.$itemInfo->likes);																
								$contents[] = array('text' => '<b class="infoBoxContent_section">Content and SEO</b>');
								$contents[] = array('text' => '<b>Page Title:</b> '.$itemInfo->title_tag);
								$contents[] = array('text' => '<b>Meta Description:</b> '.$itemInfo->description_tag);
								$contents[] = array('text' => '<b>Meta Keywords:</b> '.$itemInfo->keywords_tag);
								$contents[] = array('text' => '<b>Content:</b> <br/> '.$itemInfo->pub_description.'<br/>');
								$contents[] = array('text' => '<b>Images:</b> <br/>');
								$pictures = explode (',',$itemInfo->pub_pictures);
								$number_of_pictures = sizeof($pictures);
								for($i=0;$i<$number_of_pictures;$i++) {
									$show_pictures.= '<img src="'.DIR_WS_CATALOG_IMAGES.'/pub_edit/'.$pictures[$i].'">&nbsp;&nbsp;';
								}       
								$contents[] = array('text' => $show_pictures.'<br/><br/>');  
							}
						} else { 
							$heading[] = array('text' => '<strong> No pubs </strong>');
							$contents[] = array('text' => 'No pubs');
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