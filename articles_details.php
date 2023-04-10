<?php
    require('includes/application_top.php');

	if(!isset($_GET['article_id']) || !tep_not_null($_GET['article_id']) || !is_numeric($_GET['article_id']) ) {
		tep_redirect(tep_href_link('pages/404'));
	}

    require(DIR_WS_INCLUDES . 'template_top.php');
?>
	<?php
	// Breadcrumbs
	include(DIR_WS_MODULES . 'box_breadcrumbs.php');
	?>
	<!-- Articles Details -->
	<div class="main_content_wide">
		<div class="content_box">
			<h1 style="color:black;"><?php echo $current_page_results['article_name'];?>
			<?php echo get_like_button('article',$current_page_results['article_id'],$current_page_results['likes'], true);?> </h1>
			<div class="box_content_line">
				<div class="details">
					<?php echo article_template($current_page_results['article_description'],$current_page_results['article_pictures']);?>
				</div>
				<div class="details">
					<div class="meta">
						<span class="italic_text"> Added on <?php echo tep_date_long($current_page_results['date_added']);?> </span>
					</div>
					<div class="social_share">
						<div class="pw-widget pw-counter-vertical pw-share-popups" pw: share-popups="true">
							<a class="pw-button-facebook pw-look-native"></a>
							<a class="pw-button-googleplus pw-look-native"></a>
							<a class="pw-button-twitter pw-look-native"></a>
							<a class="pw-button-pinterest pw-look-native"></a>
							<a class="pw-button-post-share"></a>
						</div>
						<script src="http://i.po.st/static/v3/post-widget.js#publisherKey=8dujhcl6qujbr55r8fr2" type="text/javascript"></script>
					</div>
				</div>
			</div>
			<div class="section hide">
				<div class="fb-comments" data-href="<?php echo tep_href_link($current_url); ?>" data-width="100%"></div>
			</div>
		</div>
	</div>

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
