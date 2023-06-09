<?php
	// close session (store variables)
  	tep_session_close();
	
	if (DISPLAY_PAGE_PARSE_TIME == 'true') {
	
		$time_start = explode(' ', PAGE_PARSE_START_TIME);
		$time_end = explode(' ', microtime());
		$parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
		echo '<span class="smallText">Parse Time: ' . $parse_time . 's</span>';
	}

  	if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1) ) {
    	if ( (PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4') ) {
      		tep_gzip_output(GZIP_LEVEL);
    	}
  	}
?>
