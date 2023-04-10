<?php
  	require('includes/application_top.php');

  	$error = false;

  	if ( (isset($_GET['keywords']) && empty($_GET['keywords'])) ) {
    	$error = true;
  	} else {   
    	$keywords = '';
    if (isset($_GET['keywords'])) {
    	$keywords = tep_db_prepare_input($_GET['keywords']);
    }	
    if (tep_not_null($keywords)) {
    	if (!tep_parse_search_string($keywords, $search_keywords)) {
        	$error = true;
      	}
    }
	// Saving searches to database for later 
 	$keyword_lookup = tep_db_query("select search_text, search_date from " . TABLE_SEARCH_QUERIES . " where search_text = '" . tep_db_input($keywords) . "'");
  	if (tep_db_num_rows($keyword_lookup) > 0) {
  			tep_db_query("update " . TABLE_SEARCH_QUERIES . " set search_count = search_count+1, search_date = now() where search_text = '" . tep_db_input($keywords) . "'");
  		} else {
  			tep_db_query("insert into " . TABLE_SEARCH_QUERIES . " (search_text, search_date) values ('" . tep_db_input($keywords) . "', now())");
	  	}  
  	}
	  
  	require(DIR_WS_INCLUDES . 'template_top.php');
	
	$where_str ='';
	if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    	$where_str .= " and (";
    		for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      			switch ($search_keywords[$i]) {
        			case '(':
        			case ')':
        			case 'and':
        			case 'or':
          				$where_str .= " " . $search_keywords[$i] . " ";
          			break;
        			default:
          				$keyword = tep_db_prepare_input($search_keywords[$i]);
          				$where_str .= "(p.pub_name like '%" . tep_db_input($keyword) . "%'";
          				$where_str .= ')';
          			break;
      			}
    		}
    	$where_str .= " )";
  	}	
					
	$listing_sql = "select p.pub_id, p.pub_name, p.pub_description, p.pub_pictures, p.pub_address, p.pub_phone, p.pub_website, p.likes, p.pub_url, l.location_city, l.location_postcode, l.location_url, lz.location_zone_name, lz.location_zone_url from ". TABLE_PUBS." p left join ".TABLE_LOCATIONS." l on p.location_id = l.location_id left join ".TABLE_LOCATIONS_ZONES." lz on l.location_zone_id = lz.location_zone_id where p.status='1' ".$where_str." ";
	include(DIR_WS_MODULES . 'search_listing.php');

  
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
	
?>

 