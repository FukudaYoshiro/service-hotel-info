<?php
	require('includes/application_top.php');

	$error = false;

	$action = tep_db_prepare_input($_POST['action']);

	switch($action) {

		// Create the comment form
		case 'like':

			$section_name = tep_db_prepare_input($_POST['section_name']);
			$item_id = tep_db_prepare_input($_POST['item_id']);

			// Get the section ID
			$section_id_query = tep_db_query("select section_id from ". TABLE_SECTIONS ." where section_name = '" . $section_name . "'");

			// Check if this section actually exists and. If yes then we proceed
			if(tep_db_num_rows($section_id_query) > 0) {

				if (!isset($_SESSION['liked'][$section_name][$item_id])) {

					$sql_data_array = array('section_id' => (int)$section_id['section_id'],
											'item_id' => (int)$item_id,
											'date_added' => 'now()');
					tep_db_perform(TABLE_LIKES, $sql_data_array);
					tep_db_query("update " . $section_name . "s set likes = likes + 1 where ".$section_name."_id = '" . (int)$item_id . "'");

					$_SESSION['liked'][$section_name][$item_id] = 1;

					$total_votes_query = tep_db_query("select likes from " . $section_name . "s where ".$section_name."_id='".(int)$item_id."'");
					$total_votes = tep_db_fetch_array($total_votes_query);

					$response = array('error' => $error,
									  'total_votes' => $total_votes['likes']);

				} else {

					$error = true;
					$response = array('error' => $error);

				}

			}

		break;

	}

	echo json_encode($response);

?>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
