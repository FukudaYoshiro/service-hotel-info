<?php
	require('includes/application_top.php');
	
	require(DIR_WS_INCLUDES . 'template_top.php');
	
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
    	<tr>
        	<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          		<tr>
            		<td class="pageHeading"><?php echo 'Keyword Search Report'; ?></td>
            		<td align="right">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
              				<tr>
                				<td class="smallText" align="right">
									<?php
										echo tep_draw_form('search', 'report_keywords.php', tep_get_all_get_params(array('itemID', 'action')) . '', 'get');
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
                			<td class="dataTableHeadingContent">Keyword</td>
                			<td class="dataTableHeadingContent" align="center">Number of Searches</td>
                			<td class="dataTableHeadingContent" align="center">Date Last Searched</td>
              			</tr>
						<?php
						$reported_items_count = 0;
						if (isset($_GET['search'])) {
							$search = tep_db_prepare_input($_GET['search']);
							$reported_items_query_raw ="select search_id, search_text, search_count, search_date from " . TABLE_SEARCH_QUERIES . " where search_text like '%" . tep_db_input($search) . "%' order by search_count DESC, search_date";
						} else {
							$reported_items_query_raw  = "select search_text, search_count, search_date from " . TABLE_SEARCH_QUERIES . " order by search_count DESC, search_date";
						}
						
						$reported_items_split = new splitPageResults($_GET['page'], 50, $reported_items_query_raw, $reported_items_query_numrows);
   						$reported_items_query= tep_db_query($reported_items_query_raw);
						
						while ($reported_items = tep_db_fetch_array($reported_items_query)) {
		
							$reported_items_count++;
							$rows++;

							if ( (!isset($_GET['itemID']) || (isset($_GET['itemID']) && ($_GET['itemID'] == $reported_items['search_id']))) && !isset($itemInfo) && (substr($action, 0, 3) != 'new')) {				
																
								// Merging all the results in one big array							
								$itemInfo_array = array_merge($reported_items);
								$itemInfo = new objectInfo($itemInfo_array);
      						
							}

							if (isset($itemInfo) && is_object($itemInfo) && ($reported_items['search_id'] == $itemInfo->search_id) ) {
								echo '<tr class="dataTableRow">' . "\n";
							} else {
								echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
							}
							?>
								<td class="dataTableContent"><?php echo '&nbsp;' . $reported_items['search_text']; ?></td>
								<td class="dataTableContent" align="center"><?php echo '&nbsp;' . $reported_items['search_count']; ?></td>
								<td class="dataTableContent" align="center"><?php echo '&nbsp;' . $reported_items['search_date']; ?></td>
							</tr>
							<?php
    					}
						?>
						<tr>
							<td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
						  		<tr>
									<td class="smallText" valign="top"><?php echo $reported_items_split->display_count($reported_items_query_numrows, 50, $_GET['page'], 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items)'); ?></td>
									<td class="smallText" align="right"><?php echo $reported_items_split->display_links($reported_items_query_numrows, 50, 10, $_GET['page'], tep_get_all_get_params(array('page', 'itemID', 'action'))); ?></td>
						  		</tr>
						</table></td>
					  </tr>
					</table></td>
				</tr>
			</table></td>
		</tr>
	</table>

<?php
  	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>