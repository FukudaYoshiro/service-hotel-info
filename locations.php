<?php
require('includes/application_top.php');

// Security measure
if (!isset($_GET['location_zone_id']) || !tep_not_null($_GET['location_zone_id']) || !is_numeric($_GET['location_zone_id'])) {
	tep_redirect(tep_href_link('pages/404'));
}

require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php
echo "location";
// Breadcrumbs
include(DIR_WS_MODULES . 'box_breadcrumbs.php');
//description
include(DIR_WS_MODULES . 'box_description.php');

if (isset($_GET['location_id'])) {
	// Pubs listings
	include(DIR_WS_MODULES . 'pubs_listing.php');
} else {
	// Location Listings
	include(DIR_WS_MODULES . 'locations_listing.php');
}
// Locations
include(DIR_WS_MODULES . 'box_pubs_locations_zones.php');
?>

<?php
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>