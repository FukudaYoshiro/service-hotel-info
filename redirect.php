<?php
  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'banner':
      $banner_query = tep_db_query("select banners_url from " . TABLE_BANNERS . " where banners_id = '" . (int)$_GET['goto'] . "'");
      if (tep_db_num_rows($banner_query)) {
        $banner = tep_db_fetch_array($banner_query);
        tep_update_banner_click_count($_GET['goto']);

        tep_redirect($banner['banners_url']);
      }
      break;
  }
  tep_redirect(tep_href_link(FILENAME_DEFAULT));
?>
