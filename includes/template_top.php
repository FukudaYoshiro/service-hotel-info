<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $current_page['title_tag']; ?></title>
	<?php if ($current_page['description_tag']) { ?>
		<meta name="description" content="<?php echo $current_page['description_tag']; ?>" />
	<?php } ?>
	<?php if ($current_page['keywords_tag']) { ?>
		<meta name="keywords" content="<?php echo $current_page['keywords_tag']; ?>" />
	<?php } ?>
	<?php if ((isset($_GET['category']))) { ?>
		<meta name="robots" content="noindex">
	<?php } ?>
	<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<!--<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700|Roboto+Condensed:400,700' rel='stylesheet' type='text/css'>-->
	<!-- <link rel="stylesheet" type="text/css" href="css/stylesheet1.4.css"/> -->
	<style>
		<?php echo file_get_contents('css/style.min.css');
		?>
	</style>
	<?php if ($PHP_SELF == 'submit_pub.php') { ?>
		<link rel="stylesheet" href="css/redactor.css" />
	<?php } ?>
	<link rel="shortcut icon" href="images/favicon.ico">
	<link href="/apple-touch-icon.png" rel="apple-touch-icon" />
	<link rel="stylesheet" href="css/customize.css" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
	<?php // echo '<pre>';print_r($_SESSION);echo '</pre>';
	?>
	<?php // echo '<pre>';print_r($_POST);echo '</pre>';
	?>

	<!-- Header Filters
<div class="header_filters_container">
	<div class="header_filters">
		<div class="header_filters_holder">

		</div>
	</div>
</div>
 -->
	<!-- Header -->
	<div class="header_container">
		<div class="header">
			<div class="header_logo">
				<a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>"><img src="images/logo.png" alt="Local Pokies NZ Logo" width="268" height="94"></a>
				<div class="motto">Your Local NZ Pokies Directory</div>
			</div>
			<div class="header_banner">
				<?php echo tep_display_banner('static', 6); ?>

			</div>
		</div>
	</div>

	<!-- Navigation -->
	<div class="navigation_container">
		<div class="navigation <?php echo $color_scheme; ?>_gradient">
			<div class="navigation_links">
				<ul>
					<li><a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>">Home</a></li>
					<li><a href="<?php echo tep_href_link('submit_pub.php'); ?>">Add a Pub</a></li>
					<li><a href="<?php echo tep_href_link('articles/'); ?>">Blog </a></li>
					<li><a href="<?php echo tep_href_link('pages/faqs'); ?>">FAQs </a></li>
					<li id="last_link"><a href="<?php echo tep_href_link(FILENAME_CONTACT_US); ?>">Contact Us </a></li>
				</ul>
			</div>
			<div class="search">
				<form name="quickfind" method="get" action="<?php echo tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false); ?>" id="search_form">
					<input class="search_box" name="keywords" id="search_box" type="text" value="Search our site" onFocus="if(this.value=='Search our site')this.value='';" onBlur="if(this.value=='')this.value='Search our site';" />
					<input type="image" class="search_submit" src="images/search_icon.png" alt="search" />
					<?php echo tep_hide_session_id(); ?>
				</form>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="content_container">
		<div class="content">
			<div class="main_content">