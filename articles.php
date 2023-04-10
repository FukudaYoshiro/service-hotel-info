<?php
    require('includes/application_top.php');;

    require(DIR_WS_INCLUDES . 'template_top.php');

	$listing_sql = "select a.article_id, a.article_name, a.article_description, a.article_pictures, a.likes, a.date_added, a.article_url from ".TABLE_ARTICLES." a  where a.status='1' order by date_added DESC ";
?>

	<?php
	// Breadcrumbs
	include(DIR_WS_MODULES . 'box_breadcrumbs.php');
	?>
	<!-- Articles Listing -->
	<div class="main_content_wide">
		<div class="content_box">
			<h1 class="<?php echo $color_scheme; ?>_gradient"> <?php echo STORE_NAME; ?> New Zealand Blog</h1>
			<?php
				include(DIR_WS_MODULES . 'articles_listing.php');
			?>
		</div>
	</div>

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
