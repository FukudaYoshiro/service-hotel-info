<?php
	require('includes/application_top.php');
	$action = (isset($_GET['action']) ? $_GET['action'] : '');

   	if (tep_not_null($action)) {
    	
		$error = false;
		
		switch ($action) {
			
			case 'setflag':
        		
				tep_db_query("update " . TABLE_ARTICLES . " set status = '".$_GET['flag']."' where article_id = '" . (int)$_GET['itemID'] . "'");
        		tep_redirect(tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $_GET['itemID']));
        
			break;   
		   
      		case 'delete_article_confirm':
        		
				if (isset($_POST['article_id'])) {
				
          			$article_id = tep_db_prepare_input($_POST['article_id']);
					
					// Delete the actual Item
          			tep_db_query("delete from " . TABLE_ARTICLES . " where article_id = '" . (int)$article_id . "'");
					
					// Delete the likes of the item (if this applies)
					tep_db_query("delete from " . TABLE_LIKES . " where item_id = '" . (int)$article_id . "' and section_id='1'");
					
        		}
				
        		tep_redirect(tep_href_link('articles.php'));
        	
			break;
			
			case 'insert_article':
     		case 'update_article':
			
				if (isset($_GET['itemID']))  {
				
					$article_id = tep_db_prepare_input($_GET['itemID']);
				
				}
					
				// We might add error checking for admins as well later on
				
				
				$sql_data_array = array('article_name' => tep_db_prepare_input($_POST['article_name']),										
										'article_description' => tep_db_prepare_input($_POST['article_description']),
										'article_url' => strip(tep_db_prepare_input($_POST['article_name'])) ,
										'title_tag' => tep_db_prepare_input($_POST['title_tag']),
										'description_tag' => tep_db_prepare_input($_POST['description_tag']),
										'keywords_tag' => tep_db_prepare_input($_POST['keywords_tag']),
										'status' => tep_db_prepare_input($_POST['status']));
				
				
				// Image - Find a way to process images with less code				
				// Parsing the images and getting the results, we send the section name, title and error for creating thumbs - see kaftan specific for more details
				//$parse_image_result = parse_images('article',tep_db_prepare_input($_POST['article_name'],$error);
				//$error = $parse_image_result[0];
				//$error_image_message = $parse_image_result[1];	
				//$pictures = $parse_image_result[2];				
				
				$error_image_message = '';
				// Generating a random key to use in the image title
				$random_image_key = rand(1,100000);
				$imagebasename = tep_db_prepare_input($_POST['article_name']);
				
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
						
							$upload_image_result = get_image_parameters('article',$i,$imagebasename,$random_image_key++);
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
				
				$sql_data_array['article_pictures'] = tep_db_prepare_input($pictures);
										
				// Update		
				if ($action == 'update_article') {
					tep_db_perform(TABLE_ARTICLES, $sql_data_array, 'update', "article_id = '" . (int)$article_id . "'");
				}
				
				// Insert
				if ($action == 'insert_article') {
					$sql_data_array['date_added'] = 'now()';
					tep_db_perform(TABLE_ARTICLES, $sql_data_array);
					$article_id  = tep_db_insert_id();
				}
				
				tep_redirect(tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $article_id));			
			
			break;
     
    	}
  
  	}
	
	// Creating a new item
	require(DIR_WS_INCLUDES . 'template_top.php');
	
	if ($action == 'new_article') {
    
		$parameters = array('article_name' => '',
							'article_description' => '',
							'article_pictures' => '',
							'title_tag' => '',
							'description_tag' => '',
							'keywords_tag' => '',
							'status' => '');

    	$itemInfo = new objectInfo($parameters);

    	if (isset($_GET['itemID']) && empty($_POST)) {
    		$article_query = tep_db_query("select article_id, article_name, article_description, article_pictures, title_tag, description_tag, keywords_tag, status, date_added from " . TABLE_ARTICLES . " where article_id = '" . (int)$_GET['itemID'] . "'");
			$article = tep_db_fetch_array($article_query);
    		$itemInfo->objectInfo($article);			
						
			// Select likes count (if applicable)
			$likes_count_query = tep_db_query("select count(like_id) as total from " . TABLE_LIKES . "  where item_id = '" . (int)$_GET['itemID'] . "' and section_id='1'");
			$likes_count = tep_db_fetch_array($likes_count_query);
						
   	 	}

    	$status_array = array();
		$status_query = tep_db_query("select status_id, status_name from ".TABLE_STATUSES);
  		while ($status = tep_db_fetch_array($status_query)) {
			$status_array[] = array('id' => $status['status_id'], 'text' => $status['status_name']);
		}
					
		$form_action = (isset($_GET['itemID'])) ? 'update_article' : 'insert_article';
?>
    	<?php echo tep_draw_form('new_article', 'articles.php', tep_get_all_get_params(array('itemID', 'action')) . (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data" class="current_form"'); ?>

    	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      		<tr>
        		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          			<tr>
            			<td class="pageHeading">Add / Edit Article</td>
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
            			<td class="main"><?php echo tep_draw_input_field('article_name', $itemInfo->article_name,'class="long_text"'); ?></td>
          			</tr>
          			<tr>
            			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          			</tr>
          			<tr>
            			<td class="main" valign="top">Description:</td>
						<td class="main"><?php echo tep_draw_textarea_field('article_description', 'soft', '70', '15', $itemInfo->article_description,'class="ckeditor"'); ?></td>
          			</tr>
					<tr>
						<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  	</tr>  
					<tr>
            			<td class="main" valign="top">Pictures:</td>
            			<td class="main"><?php echo get_image_upload_container($itemInfo->article_pictures,8,'article');?></td>
          			</tr>  
					<tr>
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
				echo tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) .  (isset($_GET['itemID']) ? '&itemID=' . $_GET['itemID'] : ''))); 
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
            		<td class="pageHeading"><?php echo 'Articles'; ?></td>
            		<td align="right">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
              				<tr>
                				<td class="smallText" align="right">
									<?php
										echo tep_draw_form('search', 'articles.php', tep_get_all_get_params(array('itemID', 'action')) . '', 'get');
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
                			<td class="dataTableHeadingContent">Article</td>
                			<td class="dataTableHeadingContent" align="center">Status</td>
                			<td class="dataTableHeadingContent" align="right">Action&nbsp;</td>
              			</tr>
						<?php
						$articles_count = 0;
						if (isset($_GET['search'])) {
							$search = tep_db_prepare_input($_GET['search']);
							$articles_query_raw ="select article_id, article_name, article_description, article_pictures, likes, title_tag, description_tag, keywords_tag, status, date_added from " . TABLE_ARTICLES . " where article_name like '%" . tep_db_input($search) . "%' order by date_added DESC, article_name";
						} else {
							$articles_query_raw  = "select article_id, article_name, article_description, article_pictures, likes, title_tag, description_tag, keywords_tag, status, date_added from " . TABLE_ARTICLES . "  order by date_added DESC, article_name";
						}
						
						$articles_split = new splitPageResults($_GET['page'], 50, $articles_query_raw, $articles_query_numrows);
   						$articles_query= tep_db_query($articles_query_raw);
						
						while ($articles = tep_db_fetch_array($articles_query)) {
		
							$articles_count++;
							$rows++;

							if ( (!isset($_GET['itemID']) || (isset($_GET['itemID']) && ($_GET['itemID'] == $articles['article_id']))) && !isset($itemInfo) && (substr($action, 0, 3) != 'new')) {								
								
								// Merging all the results in one big array							
								$itemInfo_array = $articles;
								$itemInfo = new objectInfo($itemInfo_array);
      						
							}

							if (isset($itemInfo) && is_object($itemInfo) && ($articles['article_id'] == $itemInfo->article_id) ) {
								echo '<tr id="defaultSelected" class="dataTableRowSelected">' . "\n";
							} else {
								echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . '&itemID=' . $articles['article_id']) . '\'">' . "\n";
							}
							?>
								<td class="dataTableContent"><?php echo '&nbsp;' . $articles['article_name']; ?></td>
								<td class="dataTableContent" align="center">
								<?php
								// Statuses
								if ($articles['status'] == 1) {
									echo '<b>Active</b>&nbsp;&nbsp;<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $articles['article_id']) . '">Pending</a>&nbsp;&nbsp;<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $articles['article_id']) . '">Disabled</a>';
								} else if ($articles['status'] == 2) {
									echo '<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $articles['article_id']) . '">Active</a>&nbsp;&nbsp;<b>Pending</b>&nbsp;&nbsp;<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=3&itemID=' . $articles['article_id']) . '">Disabled</a>';
								} else if ($articles['status'] == 3) {
									echo '<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=1&itemID=' . $articles['article_id']) . '">Active</a>&nbsp;&nbsp;<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=setflag&flag=2&itemID=' . $articles['article_id']) . '">Pending</a>&nbsp;&nbsp;<b>Disabled</b>';
								}	
								?>
								</td>
								<td class="dataTableContent" align="right">
								<?php 
								if (isset($itemInfo) && is_object($itemInfo) && ($articles['article_id'] == $itemInfo->article_id)) { 
									echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
								} else { 
									echo '<a href="' . tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $articles['article_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
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
									<td class="smallText" valign="top"><?php echo $articles_split->display_count($articles_query_numrows, 50, $_GET['page'], 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items)'); ?></td>
									<td class="smallText" align="right"><?php echo $articles_split->display_links($articles_query_numrows, 50, 10, $_GET['page'], tep_get_all_get_params(array('page', 'itemID', 'action'))); ?></td>
						  		</tr>
						</table></td>
					  </tr>
						<tr>
                			<td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  				<tr>
                    				<td class="smallText"></td>
                    				<td align="right" class="smallText">
									<?php   echo tep_draw_button('New Article', 'plus', tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'action=new_article')); ?>&nbsp;
									</td>
                  				</tr>
                			</table></td>
              			</tr>
					</table></td>
				<?php
    
				$heading = array();
				$contents = array();
				switch ($action) {
					
					case 'delete_article':
					
						$heading[] = array('text' => '<strong>Delete article</strong>');
						$contents = array('form' => tep_draw_form('articles', 'articles.php', 'action=delete_article_confirm') . tep_draw_hidden_field('article_id', $itemInfo->article_id));
						$contents[] = array('text' => 'Are you sure you want to delete this item? By deleting this item you will delete any other content that is set as a child for it (comments,likes etc.).');
						$contents[] = array('text' => '<br /><strong>' . $itemInfo->article_name . '</strong>');
						$contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->article_id)));
					
					break;
			
					default:
					
						if ($rows > 0) {
							if (isset($itemInfo) && is_object($itemInfo)) { 
								$heading[] = array('text' => '<strong>' . $itemInfo->article_name . '</strong>');
								// Add the param for live preview in new window later on
								//$params['newwindow'] = 1;
								$contents[] = array('align' => 'center', 'text' => 
								tep_draw_button('Edit', 'document', tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->article_id.'&action=new_article')).
								tep_draw_button('Delete', 'trash', tep_href_link('articles.php', tep_get_all_get_params(array('itemID', 'action')) . 'itemID=' . $itemInfo->article_id . '&action=delete_article')).
								//tep_draw_button('Preview', 'document', tep_catalog_href_link('articles_details.php', 'article_id=' . $itemInfo->article_id.'&method=preview'),null, $params) . 
								'<br/>');
								$contents[] = array('text' => '<b class="infoBoxContent_section">General Information</b>');
								$contents[] = array('text' => '<b>Date added:</b> ' . tep_date_short($itemInfo->date_added));
								$contents[] = array('text' => '<b>Likes:</b> '.$itemInfo->likes );		
								$contents[] = array('text' => '<b class="infoBoxContent_section">Content and SEO</b>');
								$contents[] = array('text' => '<b>Page Title:</b> '.$itemInfo->title_tag);
								$contents[] = array('text' => '<b>Meta Description:</b> '.$itemInfo->description_tag);
								$contents[] = array('text' => '<b>Meta Keywords:</b> '.$itemInfo->keywords_tag);
								$contents[] = array('text' => '<b>Content:</b> <br/> '.$itemInfo->article_description.'<br/>');
								$contents[] = array('text' => '<b>Images:</b> <br/>');
								$pictures = explode (',',$itemInfo->article_pictures);
								$number_of_pictures = sizeof($pictures);
								for($i=0;$i<$number_of_pictures;$i++) {
									$show_pictures.= '<img src="'.DIR_WS_CATALOG_IMAGES.'/article_edit/'.$pictures[$i].'">&nbsp;&nbsp;';
								}       
								$contents[] = array('text' => $show_pictures.'<br/><br/>');  
							}
						} else { 
							$heading[] = array('text' => '<strong> No articles </strong>');
							$contents[] = array('text' => 'No articles');
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