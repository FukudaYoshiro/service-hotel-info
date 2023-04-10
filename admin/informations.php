<?php
	require('includes/application_top.php');
	$action = (isset($_GET['action']) ? $_GET['action'] : '');

   	if (tep_not_null($action)) {
    	
		$error = false;
		
		switch ($action) {
			
			case 'setflag':
        		
				tep_db_query("update " . TABLE_INFORMATIONS . " set status = '".$_GET['flag']."' where information_id = '" . (int)$_GET['itemID'] . "'");
        		tep_redirect(tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $_GET['itemID']));
        
			break;   
		   
      		case 'delete_information_confirm':
        		
				if (isset($_POST['information_id'])) {
				
          			$information_id = tep_db_prepare_input($_POST['information_id']);
					
					// Delete the actual Item
          			tep_db_query("delete from " . TABLE_INFORMATIONS . " where information_id = '" . (int)$information_id . "'");
					
        		}
				
        		tep_redirect(tep_href_link('informations.php'));
        	
			break;
			
			case 'insert_information':
     		case 'update_information':
			
				if (isset($_GET['itemID']))  {
				
					$information_id = tep_db_prepare_input($_GET['itemID']);
				
				}
					
				// We might add error checking for admins as well later on
				
				
				$sql_data_array = array('information_name' => tep_db_prepare_input($_POST['information_name']),										
										'information_description' => tep_db_prepare_input($_POST['information_description']),
										'information_url' => strip(tep_db_prepare_input($_POST['information_name'])),
										'title_tag' => tep_db_prepare_input($_POST['title_tag']),
										'description_tag' => tep_db_prepare_input($_POST['description_tag']),
										'keywords_tag' => tep_db_prepare_input($_POST['keywords_tag']),
										'status' => tep_db_prepare_input($_POST['status']),
										'featured' => tep_db_prepare_input($_POST['featured']));
																	
				// Update		
				if ($action == 'update_information') {
					tep_db_perform(TABLE_INFORMATIONS, $sql_data_array, 'update', "information_id = '" . (int)$information_id . "'");
				}
				
				// Insert
				if ($action == 'insert_information') {
					$sql_data_array['date_added'] = 'now()';
					tep_db_perform(TABLE_INFORMATIONS, $sql_data_array);
					$information_id  = tep_db_insert_id();
				}
				
				tep_redirect(tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $information_id));			
			
			break;
     
    	}
  
  	}
	
	// Creating a new item
	require(DIR_WS_INCLUDES . 'template_top.php');
	
	if ($action == 'new_information') {
    
		$parameters = array('information_name' => '',
							'information_description' => '',
							'title_tag' => '',
							'description_tag' => '',
							'keywords_tag' => '',
							'status' => '',
							'featured' => '');

    	$itemInfo = new objectInfo($parameters);

    	if (isset($_GET['itemID']) && empty($_POST)) {
    		$information_query = tep_db_query("select information_id, information_name, information_description, title_tag, description_tag, keywords_tag, status, featured, date_added from " . TABLE_INFORMATIONS . " where information_id = '" . (int)$_GET['itemID'] . "'");
			$information = tep_db_fetch_array($information_query);
    		$itemInfo->objectInfo($information);			
						
   	 	}

    	$status_array = array();
		$status_query = tep_db_query("select status_id, status_name from ".TABLE_STATUSES);
  		while ($status = tep_db_fetch_array($status_query)) {
			$status_array[] = array('id' => $status['status_id'], 'text' => $status['status_name']);
		}
					
		$form_action = (isset($_GET['itemID'])) ? 'update_information' : 'insert_information';
?>
    	<?php echo tep_draw_form('new_information', 'informations.php', tep_get_all_get_params(array('itemID', 'action')) . (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data" class="current_form"'); ?>

    	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      		<tr>
        		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          			<tr>
            			<td class="pageHeading">Add / Edit Information</td>
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
            			<td class="main"><?php echo tep_draw_input_field('information_name', $itemInfo->information_name,'class="long_text"'); ?></td>
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
            			<td class="main" valign="top">Description:</td>
						<td class="main"><?php echo tep_draw_textarea_field('information_description', 'soft', '70', '15', $itemInfo->information_description,'class="ckeditor"'); ?></td>
          			</tr>  
              	    <tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  	</tr>
					<tr>
            			<td class="main" valign="top">Page Title:</td>
            			<td class="main"><?php echo tep_draw_textarea_field('title_tag', 'soft', '70', '15', $itemInfo->title_tag,'class="long_textarea"'); ?></td>
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
				echo tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) .  (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : ''))); 
				?>
				</td>
      		</tr>
    	</table>
	</form>
<?php
	} else {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
    	<tr>
        	<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          		<tr>
            		<td class="pageHeading"><?php echo 'Informations'; ?></td>
            		<td align="right">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
              				<tr>
                				<td class="smallText" align="right">
									<?php
										echo tep_draw_form('search', 'informations.php', tep_get_all_get_params(array('itemID', 'action')) . '', 'get');
										echo 'Search: ' . tep_draw_input_field('search','','class="short_text"');
										echo tep_hide_session_id() . '</form>';
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
                			<td class="dataTableHeadingContent">Information</td>
                			<td class="dataTableHeadingContent" align="center">Status</td>
                			<td class="dataTableHeadingContent" align="right">Action&nbsp;</td>
              			</tr>
						<?php
						$informations_count = 0;
						if (isset($_GET['search'])) {
							$search = tep_db_prepare_input($_GET['search']);
							$informations_query_raw ="select information_id, information_name, information_description, title_tag, description_tag, keywords_tag, featured, status, date_added from " . TABLE_INFORMATIONS . " where information_name like '%" . tep_db_input($search) . "%' order by date_added DESC, information_name";
						} else {
							$informations_query_raw  = "select information_id, information_name, information_description, title_tag, description_tag, keywords_tag, featured, status, date_added from " . TABLE_INFORMATIONS . "  order by date_added DESC, information_name";
						}
						
						$informations_split = new splitPageResults($_GET['page'], 50, $informations_query_raw, $informations_query_numrows);
   						$informations_query= tep_db_query($informations_query_raw);
						
						while ($informations = tep_db_fetch_array($informations_query)) {
		
							$informations_count++;
							$rows++;

							if ( (!isset($_GET['itemID']) || (isset($_GET['itemID']) && ($_GET['itemID'] == $informations['information_id']))) && !isset($itemInfo) && (substr($action, 0, 3) != 'new')) {								
																								
								// Merging all the results in one big array							
								$itemInfo_array = array_merge($informations);
								$itemInfo = new objectInfo($itemInfo_array);
      						
							}

							if (isset($itemInfo) && is_object($itemInfo) && ($informations['information_id'] == $itemInfo->information_id) ) {
								echo '<tr id="defaultSelected" class="dataTableRowSelected">' . "\n";
							} else {
								echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . '&itemID=' . $informations['information_id']) . '\'">' . "\n";
							}
							?>
								<td class="dataTableContent"><?php echo '&nbsp;' . $informations['information_name']; ?></td>
								<td class="dataTableContent" align="center">
								<?php
								// Statuses
								if ($informations['status'] == 1) {
									echo '<b>Active</b>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $informations['information_id']) . '">Pending</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $informations['information_id']) . '">Disabled</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=4&itemID=' . $informations['information_id']) . '">Deleted</a>';
								} else if ($informations['status'] == 2) {
									echo '<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $informations['information_id']) . '">Active</a>&nbsp;&nbsp;<b>Pending</b>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $informations['information_id']) . '">Disabled</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=4&itemID=' . $informations['information_id']) . '">Deleted</a>';
								} else if ($informations['status'] == 3) {
									echo '<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $informations['information_id']) . '">Active</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $informations['information_id']) . '">Pending</a>&nbsp;&nbsp;<b>Disabled</b>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=4&itemID=' . $informations['information_id']) . '">Deleted</a>';
								} else if ($informations['status'] == 4) {
									echo '<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $informations['information_id']) . '">Active</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $informations['information_id']) . '">Pending</a>&nbsp;&nbsp;<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $informations['information_id']) . '">Disabled</a>&nbsp;&nbsp;<b>Deleted</b>';
								}		
								?>
								</td>
								<td class="dataTableContent" align="right">
								<?php 
								if (isset($itemInfo) && is_object($itemInfo) && ($informations['information_id'] == $itemInfo->information_id)) { 
									echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
								} else { 
									echo '<a href="' . tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $informations['information_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
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
									<td class="smallText" valign="top"><?php echo $informations_split->display_count($informations_query_numrows, 50, $_GET['page'], 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items)'); ?></td>
									<td class="smallText" align="right"><?php echo $informations_split->display_links($informations_query_numrows, 50, 10, $_GET['page'], tep_get_all_get_params(array('page', 'itemID', 'action'))); ?></td>
						  		</tr>
						</table></td>
					  </tr>
						<tr>
                			<td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  				<tr>
                    				<td class="smallText"></td>
                    				<td align="right" class="smallText">
									<?php   echo tep_draw_button('New Information', 'plus', tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=new_information')); ?>&nbsp;
									</td>
                  				</tr>
                			</table></td>
              			</tr>
					</table></td>
				<?php
    
				$heading = array();
				$contents = array();
				switch ($action) {
					
					case 'delete_information':
					
						$heading[] = array('text' => '<strong>Delete information</strong>');
						$contents = array('form' => tep_draw_form('informations', 'informations.php', 'action=delete_information_confirm') . tep_draw_hidden_field('information_id', $itemInfo->information_id));
						$contents[] = array('text' => 'Are you sure you want to delete this item? By deleting this item you will delete any other content that is set as a child for it');
						$contents[] = array('text' => '<br /><strong>' . $itemInfo->information_name . '</strong>');
						$contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->information_id)));
					
					break;
			
					default:
					
						if ($rows > 0) {
							if (isset($itemInfo) && is_object($itemInfo)) { 
								$heading[] = array('text' => '<strong>' . $itemInfo->information_name . '</strong>');
								// Add the param for live preview in new window later on
								//$params['newwindow'] = 1;
								$contents[] = array('align' => 'center', 'text' => 
								tep_draw_button('Edit', 'document', tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->information_id.'&action=new_information')).
								tep_draw_button('Delete', 'trash', tep_href_link('informations.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->information_id . '&action=delete_information')).
								//tep_draw_button('Preview', 'document', tep_catalog_href_link('informations_details.php', 'information_id=' . $itemInfo->information_id.'&method=preview'),null, $params) . 
								'<br/>');
								$contents[] = array('text' => '<b class="infoBoxContent_section">General Information</b>');
								$contents[] = array('text' => '<b>Date added:</b> ' . tep_date_short($itemInfo->date_added));
								$contents[] = array('text' => '<b>Page ID:</b> ' . $itemInfo->information_id);								
								$contents[] = array('text' => '<b class="infoBoxContent_section">Content and SEO</b>');
								$contents[] = array('text' => '<b>Page Title:</b> '.$itemInfo->title_tag);
								$contents[] = array('text' => '<b>Meta Description:</b> '.$itemInfo->description_tag);
								$contents[] = array('text' => '<b>Meta Keywords:</b> '.$itemInfo->keywords_tag);
								$contents[] = array('text' => '<b>Content:</b> <br/> '.$itemInfo->information_description.'<br/>');
							}
						} else { 
							$heading[] = array('text' => '<strong> No informations </strong>');
							$contents[] = array('text' => 'No informations');
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