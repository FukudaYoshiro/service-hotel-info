<?php
require('includes/application_top.php');
require(DIR_WS_INCLUDES . 'template_top.php');
?>
<?php
// Announcements
include(DIR_WS_MODULES . 'box_announcements.php');
?>
<div class="main_content_wide">
	<?php echo tep_display_banner('static', 7); ?>
	<!-- <img src="images/add_left.png" alt="Center Add" width="660" height="354"> -->
</div>
<?php
// Locations
include(DIR_WS_MODULES . 'box_pubs_locations_zones.php');
// Top 10 Pubs
include(DIR_WS_MODULES . 'box_pubs_most_popular_top.php');
?>
<div class="main_content_wide">
	<?php echo tep_display_banner('static', 8); ?>
	<!-- <img src="images/add_left_2.png" alt="Center Add" width="660" height="297"> -->
</div>

<?php
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>