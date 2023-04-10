<?php
    require('includes/application_top.php');

	// Security measure
	if(!isset($_GET['location_id']) || !tep_not_null($_GET['location_id']) || !is_numeric($_GET['location_id']) ) {
		tep_redirect(tep_href_link('pages/404'));
	}
	if(!isset($_GET['pub_id']) || !tep_not_null($_GET['pub_id']) || !is_numeric($_GET['pub_id']) ) {
		tep_redirect(tep_href_link('pages/404'));
	}

    require(DIR_WS_INCLUDES . 'template_top.php');
?>

	<?php
	// Breadcrumbs
	include(DIR_WS_MODULES . 'box_breadcrumbs.php');
	?>

	<div class="main_content_wide">
		<div class="content_box" itemscope itemtype="http://schema.org/Organization">
			<h1 class="<?php echo $color_scheme; ?>_gradient"><span itemprop="name"><?php echo $current_page_results['pub_name']; ?></span>
			<?php echo get_like_button('pub', $current_page_results['pub_id'] , $current_page_results['likes'], true);?></h1>
			<div class="item_details_info_container">
                <div class="item_details_image">
                    <?php if ($current_page_results['pub_pictures']) { ?>
                    <img src="images/pub_big/<?php echo $current_page_results['pub_pictures']; ?>" width="250" alt="<?php echo $current_page_results['pub_name']; ?>">
                    <?php } else { ?>
                    <img src="images/no_image.png" width="250" alt="No Image">
                    <?php } ?>
                </div>
				<div class="item_details_meta">
					<div class="item_details_info_meta">
						<ul>
							<?php if (strlen($current_page_results['pub_address'])) { ?>
							<li class="address_icon"><b>Address:</b> <span itemprop="address"><?php echo $current_page_results['pub_address']; ?> </span></li>
							<?php } ?>
							<?php if (strlen($current_page_results['pub_phone'])) { ?>
							<li class="phone_icon"><b>Phone:</b> <span itemprop="telephone"><?php echo $current_page_results['pub_phone']; ?></span> </li>
							<?php } ?>
							<?php if (strlen($current_page_results['pub_website'])) { ?>
							<li class="website_icon"><b>Website:</b> <span itemprop="url"><?php echo $current_page_results['pub_website']; ?></span> </li>
							<?php } ?>
						</ul>
					</div>
					<div class="item_details_info_meta">
						<div class="pw-widget pw-counter-vertical pw-share-popups" pw: share-popups="true">
							<a class="pw-button-facebook pw-look-native"></a>
							<a class="pw-button-googleplus pw-look-native"></a>
							<a class="pw-button-twitter pw-look-native"></a>
							<a class="pw-button-pinterest pw-look-native"></a>
							<a class="pw-button-post-share"></a>
						</div>
						<script src="http://i.po.st/static/v3/post-widget.js#publisherKey=8dujhcl6qujbr55r8fr2" type="text/javascript"></script>
					</div>
					<?php if (strlen($current_page_results['pub_description'])) { ?>
					<div class="item_details_info_meta">
						<?php
							echo $current_page_results['pub_description'];
						?>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="section hide">
				<div class="fb-comments" data-href="<?php echo tep_href_link($current_url); ?>" data-width="640"></div>
			</div>
		</div>
	</div>

	<?php
	// Pubs from a similar location
	include(DIR_WS_MODULES . 'box_pubs_similar_location.php');
	?>

	<?php
	// Tracking number of visits on this page
	tep_db_query("update " . TABLE_PUBS . " set number_of_visits = number_of_visits + 1 where pub_id = '" . (int)$_GET['pub_id'] . "'");
	?>

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
