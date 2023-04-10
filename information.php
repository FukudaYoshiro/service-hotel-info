<?php
	require('includes/application_top.php');
	
	// Security measure
	if(!isset($_GET['info_id']) || !tep_not_null($_GET['info_id']) || !is_numeric($_GET['info_id']) ) {
		tep_redirect(tep_href_link('pages/404'));
	}
		
	require(DIR_WS_INCLUDES . 'template_top.php');
	
?>	
	<div class="main_content_wide">
		<div class="content_box">
			<h1 class="<?php echo $color_scheme; ?>_gradient"><?php echo $current_page_results['information_name']; ?></h1>
			<div class="box_content_line">
				<?php echo $current_page_results['information_description']; ?>
			</div>
		</div>
	</div>
	
<?php 
	require(DIR_WS_INCLUDES . 'template_bottom.php');
 	require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>