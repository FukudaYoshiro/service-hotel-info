<?php
	if (tep_session_is_registered('admin')) {
		$cl_box_groups = array();
	
		include(DIR_WS_BOXES . 'configuration.php');
		include(DIR_WS_BOXES . 'sections.php');
		include(DIR_WS_BOXES . 'reports.php');
		include(DIR_WS_BOXES . 'tools.php');
		//include(DIR_WS_BOXES . 'modules.php');
?>

	<div id="adminAppMenu">

	<?php
    	foreach ($cl_box_groups as $groups) {
      	echo '<h3><a href="#">' . $groups['heading'] . '</a></h3>' .
        	   '<div><ul>';

			foreach ($groups['apps'] as $app) {
				echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
			}
	
			echo '</ul></div>';
		}
?>

	</div>

	<script type="text/javascript">
	$('#adminAppMenu').accordion({
  		autoHeight: false,
  		icons: {
    		'header': 'ui-icon-plus',
    		'headerSelected': 'ui-icon-minus'
  		}

	<?php
    $counter = 0;
    foreach ($cl_box_groups as $groups) {
      	foreach ($groups['apps'] as $app) {
        	if ($app['code'] == $PHP_SELF) {
          		echo ',active: ' . $counter;
          		break;
        	}
      	}
      	$counter++;
   	}
	?>
	});
	</script>

<?php
	}
?>
