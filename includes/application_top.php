<?php
// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// check support for register_globals
if (function_exists('ini_get') && (ini_get('register_globals') == false) && (PHP_VERSION < 4.3)) {
	exit('Server Requirement Error: register_globals is disabled in your PHP configuration. This can be enabled in your php.ini configuration file or in the .htaccess file in your catalog directory. Please use PHP 4.3+ if register_globals cannot be enabled on the server.');
}

// load server configuration parameters
include('includes/configure.php');

// some code to solve compatibility issues
require(DIR_WS_FUNCTIONS . 'compatibility.php');

// set the type of request (secure or not)
$request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
$PHP_SELF = (((strlen(ini_get('cgi.fix_pathinfo')) > 0) && ((bool)ini_get('cgi.fix_pathinfo') == false)) || !isset($HTTP_SERVER_VARS['SCRIPT_NAME'])) ? basename($HTTP_SERVER_VARS['PHP_SELF']) : basename($HTTP_SERVER_VARS['SCRIPT_NAME']);

if ($request_type == 'NONSSL') {
	define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
} else {
	define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
}

// include the list of project filenames
require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
require(DIR_WS_INCLUDES . 'database_tables.php');

// include the database functions
require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
$configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
while ($configuration = tep_db_fetch_array($configuration_query)) {
	define($configuration['cfgKey'], $configuration['cfgValue']);
}

// if gzip_compression is enabled, start to buffer the output
if ((GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && !headers_sent()) {
	if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
		if (PHP_VERSION < '5.4' || PHP_VERSION > '5.4.5') { // see PHP bug 55544
			if (PHP_VERSION >= '4.0.4') {
				ob_start('ob_gzhandler');
			} elseif (PHP_VERSION >= '4.0.1') {
				include(DIR_WS_FUNCTIONS . 'gzip_compression.php');
				ob_start();
				ob_implicit_flush();
			}
		}
	} elseif (function_exists('ini_set')) {
		ini_set('zlib.output_compression_level', GZIP_LEVEL);
	}
}

// define general functions used application-wide
require(DIR_WS_FUNCTIONS . 'general.php');
require(DIR_WS_FUNCTIONS . 'cms_specific.php');
require(DIR_WS_FUNCTIONS . 'html_output.php');

// set the cookie domain
$cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
$cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

// include cache functions if enabled
if (USE_CACHE == 'true') include(DIR_WS_FUNCTIONS . 'cache.php');

// include navigation history class
require(DIR_WS_CLASSES . 'navigation_history.php');

// define how the session functions will be used
require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
tep_session_name('osCsid');
tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
if (function_exists('session_set_cookie_params')) {
	session_set_cookie_params(0, $cookie_path, $cookie_domain);
} elseif (function_exists('ini_set')) {
	ini_set('session.cookie_lifetime', '0');
	ini_set('session.cookie_path', $cookie_path);
	ini_set('session.cookie_domain', $cookie_domain);
}

@ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// set the session ID if it exists
if (isset($_POST[tep_session_name()])) {
	tep_session_id($_POST[tep_session_name()]);
} elseif (($request_type == 'SSL') && isset($_GET[tep_session_name()])) {
	tep_session_id($_GET[tep_session_name()]);
}

// start the session
$session_started = false;
if (SESSION_FORCE_COOKIE_USE == 'True') {
	tep_setcookie('cookie_test', 'please_accept_for_session', time() + 60 * 60 * 24 * 30, $cookie_path, $cookie_domain);
	if (isset($HTTP_COOKIE_VARS['cookie_test'])) {
		tep_session_start();
		$session_started = true;
	}
} elseif (SESSION_BLOCK_SPIDERS == 'True') {
	$user_agent = strtolower(getenv('HTTP_USER_AGENT'));
	$spider_flag = false;
	if (tep_not_null($user_agent)) {
		$spiders = file(DIR_WS_INCLUDES . 'spiders.txt');
		for ($i = 0, $n = sizeof($spiders); $i < $n; $i++) {
			if (tep_not_null($spiders[$i])) {
				if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
					$spider_flag = true;
					break;
				}
			}
		}
	}

	if ($spider_flag == false) {
		tep_session_start();
		$session_started = true;
	}
} else {
	tep_session_start();
	$session_started = true;
}

if (($session_started == true) && (PHP_VERSION >= 4.3) && function_exists('ini_get') && (ini_get('register_globals') == false)) {
	extract($_SESSION, EXTR_OVERWRITE + EXTR_REFS);
}

// initialize a session token
if (!tep_session_is_registered('sessiontoken')) {
	$sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
	tep_session_register('sessiontoken');
}

// set SID once, even if empty
$SID = (defined('SID') ? SID : '');

// verify the ssl_session_id if the feature is enabled
if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true)) {
	$ssl_session_id = getenv('SSL_SESSION_ID');
	if (!tep_session_is_registered('SSL_SESSION_ID')) {
		$SESSION_SSL_ID = $ssl_session_id;
		tep_session_register('SESSION_SSL_ID');
	}

	if ($SESSION_SSL_ID != $ssl_session_id) {
		tep_session_destroy();
		tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
	}
}

// verify the browser user agent if the feature is enabled
if (SESSION_CHECK_USER_AGENT == 'True') {
	$http_user_agent = getenv('HTTP_USER_AGENT');
	if (!tep_session_is_registered('SESSION_USER_AGENT')) {
		$SESSION_USER_AGENT = $http_user_agent;
		tep_session_register('SESSION_USER_AGENT');
	}

	if ($SESSION_USER_AGENT != $http_user_agent) {
		tep_session_destroy();
		tep_redirect(tep_href_link(FILENAME_LOGIN));
	}
}

// verify the IP address if the feature is enabled
if (SESSION_CHECK_IP_ADDRESS == 'True') {
	$ip_address = tep_get_ip_address();
	if (!tep_session_is_registered('SESSION_IP_ADDRESS')) {
		$SESSION_IP_ADDRESS = $ip_address;
		tep_session_register('SESSION_IP_ADDRESS');
	}

	if ($SESSION_IP_ADDRESS != $ip_address) {
		tep_session_destroy();
		tep_redirect(tep_href_link(FILENAME_LOGIN));
	}
}

// include the mail classes
require(DIR_WS_CLASSES . 'mime.php');
require(DIR_WS_CLASSES . 'email.php');

// set the language
if (!tep_session_is_registered('language') || isset($_GET['language'])) {
	if (!tep_session_is_registered('language')) {
		tep_session_register('language');
		tep_session_register('languages_id');
	}

	include(DIR_WS_CLASSES . 'language.php');
	$lng = new language();

	if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
		$lng->set_language($_GET['language']);
	} else {
		$lng->get_browser_language();
	}

	$language = $lng->language['directory'];
	$languages_id = $lng->language['id'];
}

// include the language translations
require(DIR_WS_LANGUAGES . $language . '.php');

// navigation history
if (!tep_session_is_registered('navigation') || !is_object($navigation)) {
	tep_session_register('navigation');
	$navigation = new navigationHistory;
}
$navigation->add_current_page();

// action recorder - We don't use this on the frontend currently
// include('includes/classes/action_recorder.php');

// Maybe add a cookie information page for later ... currently if sessions are not started, nothing is done
/*
	if ($session_started == false) {
    	tep_redirect(tep_href_link(FILENAME_DEFAULT));
  	}
	*/

// include validation functions (right now only email address)
require(DIR_WS_FUNCTIONS . 'validations.php');

// split-page-results
require(DIR_WS_CLASSES . 'split_page_results.php');

// include the breadcrumb class and start the breadcrumb trail - include when used
require(DIR_WS_CLASSES . 'breadcrumb.php');
$breadcrumb = new breadcrumb;
$breadcrumb->add('Home', tep_href_link(FILENAME_DEFAULT));
//

// auto activate and expire banners
require(DIR_WS_FUNCTIONS . 'banner.php');
tep_activate_banners();
tep_expire_banners();

// Aditional constants get defined HERE
define('META_TAG_DESCRIPTION_SITE_NAME', ' - Localpokies.co.nz');


// We create the current URL and set the GET parameters as ID's depending on what page we are on
// Information
if (isset($_GET['info_name'])) {
	$information_url_query = tep_db_query("select information_id, information_url from " . TABLE_INFORMATIONS . " where information_url = '" . tep_db_input($_GET['info_name']) . "'");
	if (tep_db_num_rows($information_url_query)) {
		$information_url = tep_db_fetch_array($information_url_query);
		$_GET['info_id'] = $information_url['information_id'];
		$current_url .= $information_url['information_url'] . '/';
	}
}

if (isset($_GET['article_name'])) {
	$article_url_query = tep_db_query("select article_id, article_url from " . TABLE_ARTICLES . " where article_url = '" . tep_db_input($_GET['article_name']) . "'");
	if (tep_db_num_rows($article_url_query)) {
		$article_url = tep_db_fetch_array($article_url_query);
		$_GET['article_id'] = $article_url['article_id'];
		$current_url .= $article_url['article_url'] . '/';
	}
}
//
$description_title = "";
$description = "";
if (isset($_GET['location_zone_name'])) {
	$description_field = "region";
	$location_zone_url_query = tep_db_query("select location_zone_id, location_zone_url,region,description from " . TABLE_LOCATIONS_ZONES . " where location_zone_url = '" . tep_db_input($_GET['location_zone_name']) . "'");
	if (tep_db_num_rows($location_zone_url_query)) {
		$location_zone_url = tep_db_fetch_array($location_zone_url_query);
		$_GET['location_zone_id'] = $location_zone_url['location_zone_id'];
		$current_location_zone_url = $location_zone_url['location_zone_url'] . '/';
		$current_url .= $location_zone_url['location_zone_url'] . '/';
		$description_title = $location_zone_url['region'];
		$description = $location_zone_url['description'];
	}
}

if (isset($_GET['location_name'])) {
	$description_field = "town";
	$description_name = $_GET['location_name'];
	$location_url_query = tep_db_query("select location_id, location_url, location_city, description from " . TABLE_LOCATIONS . " where location_url = '" . tep_db_input($_GET['location_name']) . "'");
	if (tep_db_num_rows($location_url_query)) {
		$location_url = tep_db_fetch_array($location_url_query);
		$_GET['location_id'] = $location_url['location_id'];
		$current_location_url = $location_url['location_url'] . '/';
		$current_url .= $location_url['location_url'] . '/';
		$description_title = $location_url['location_city'];
		$description = $location_url['description'];
		if (isset($_GET['page'])) {
			$current_url_breadcrumb .= $current_url . 'page' . $_GET['page'];
		}

		// Fix for multiple locations ids
		$location_ids_query = tep_db_query("select location_id from " . TABLE_LOCATIONS . " where location_url = '" . tep_db_input($_GET['location_name']) . "'");
		$found = 0;
		while ($location_ids = tep_db_fetch_array($location_ids_query)) {
			if ($found) {
				$location_query .= " or ";
			}
			$found++;
			$location_query .= "l.location_id = '" . (int)$location_ids['location_id'] . "'";
		}
	}
}

if (isset($_GET['pub_name'])) {
	$pub_url_query = tep_db_query("select pub_id, pub_url from " . TABLE_PUBS . " where pub_url = '" . tep_db_input($_GET['pub_name']) . "'");
	if (tep_db_num_rows($pub_url_query)) {
		$pub_url = tep_db_fetch_array($pub_url_query);
		$_GET['pub_id'] = $pub_url['pub_id'];
		$current_pub_url = $pub_url['pub_url'] . '/';
		$current_url .= $pub_url['pub_url'] . '/';
	}
}

// Homepage Tags are set here. Different Sections meta tags are set up in their respective listing files
$current_page = array();
$current_page['title_tag'] = "LocakPokies.co.nz - Local New Zealand Directory of Pubs and Pokies";
$current_page['description_tag'] = "Your local pub and pokies directory in New Zealand. Find local poker machines to play - Over 600 Kiwi pubs listed - Local Pokies NZ";
$current_page['keywords_tag'] = "pokies, pubs, poker machine, pokie, sports bar, betting, punter, local, search, directory";
// Setting up the default color scheme of the page. It will be modified if a location is set bellow
$color_scheme = 'dark_green';

// Getting the Current Page Information depending on what is set
// Setting up the page title and meta tags
if ((isset($_GET['pub_id']))  && (isset($_GET['location_id'])) && (isset($_GET['location_zone_id']))) {

	$current_page_query = tep_db_query("select p.pub_id, p.pub_name, p.pub_description, p.pub_pictures, p.pub_address, p.pub_phone, p.pub_website, p.title_tag, p.description_tag, p.likes, l.location_city, l.location_postcode, lz.location_zone_name from " . TABLE_PUBS . " p left join " . TABLE_LOCATIONS . " l on p.location_id = l.location_id left join " . TABLE_LOCATIONS_ZONES . " lz on l.location_zone_id = lz.location_zone_id where p.status = '1' and p.pub_id = '" . (int)$_GET['pub_id'] . "' and (" . $location_query . ") and lz.location_zone_id = '" . (int)$_GET['location_zone_id'] . "'");
	if (tep_db_num_rows($current_page_query)) {

		$current_page_results = tep_db_fetch_array($current_page_query);

		if (strlen(trim(strip_tags($current_page_results['title_tag']))) > 3) {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['title_tag']));
		} else {
			$current_page['title_tag'] = $current_page_results['pub_name'] . ' - Pub in ' . ucfirst(strtolower($current_page_results['location_city'])) . ', ' . $current_page_results['location_zone_name'];
		}
		if (strlen(trim(strip_tags($current_page_results['description_tag']))) > 3) {
			$current_page['description_tag'] = trim(strip_tags($current_page_results['description_tag']));
		} else {
			$current_page['description_tag'] =  $current_page_results['pub_name'] . ' - Pub in ' . ucfirst(strtolower($current_page_results['location_city'])) . ', ' . $current_page_results['location_zone_name'] . ". Information, Location, Contact Details and Customer Reviews";
		}
		if (strlen(trim(strip_tags($current_page_results['keywords_tag']))) > 3) {
			$current_page['keywords_tag'] = trim(strip_tags($current_page_results['keywords_tag']));
		} else {
			$current_page['keywords_tag'] = '';
		}

		$breadcrumb->add($current_page_results['location_zone_name'], tep_href_link($current_location_zone_url));
		$breadcrumb->add(ucfirst(strtolower($current_page_results['location_city'])), tep_href_link($current_location_zone_url . $current_location_url));
		$breadcrumb->add($current_page_results['pub_name'], tep_href_link($current_url));
	}
} else if ((isset($_GET['location_id']))  && (isset($_GET['location_zone_id']))) {

	$current_page_query = tep_db_query("select l.location_city, l.location_postcode, lz.location_zone_name from " . TABLE_LOCATIONS . " l left join " . TABLE_LOCATIONS_ZONES . " lz on l.location_zone_id = lz.location_zone_id where l.location_id = '" . (int)$_GET['location_id'] . "' and lz.location_zone_id = '" . (int)$_GET['location_zone_id'] . "'");

	if (tep_db_num_rows($current_page_query)) {

		$current_page_results = tep_db_fetch_array($current_page_query);

		if (strlen(trim(strip_tags($current_page_results['title_tag']))) > 3) {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['title_tag']));
		} else {
			$current_page['title_tag'] = 'Pokies and Pubs in ' . ucfirst(strtolower($current_page_results['location_city']));
		}
		if (strlen(trim(strip_tags($current_page_results['description_tag']))) > 3) {
			$current_page['description_tag'] = trim(strip_tags($current_page_results['description_tag']));
		} else {
			$current_page['description_tag'] = 'List of Pokies and Pubs in ' . ucfirst(strtolower($current_page_results['location_city'])) . ', ' . $current_page_results['location_zone_name'] . " - Where to play Pokies in " . ucfirst(strtolower($current_page_results['location_city']));
		}
		if (strlen(trim(strip_tags($current_page_results['keywords_tag']))) > 3) {
			$current_page['keywords_tag'] = trim(strip_tags($current_page_results['keywords_tag']));
		} else {
			$current_page['keywords_tag'] = '';
		}

		$breadcrumb->add($current_page_results['location_zone_name'], tep_href_link($current_location_zone_url));
		$breadcrumb->add(ucfirst(strtolower($current_page_results['location_city'])), tep_href_link($current_url));

		if (isset($_GET['page'])) {
			$breadcrumb->add('Page ' . $_GET['page'], tep_href_link($current_url_breadcrumb));
		}
	}
} else if (isset($_GET['location_zone_id'])) {

	$current_page_query = tep_db_query("select lz.location_zone_name from " . TABLE_LOCATIONS_ZONES . " lz where lz.location_zone_id = '" . (int)$_GET['location_zone_id'] . "'");

	if (tep_db_num_rows($current_page_query)) {

		$current_page_results = tep_db_fetch_array($current_page_query);

		if (strlen(trim(strip_tags($current_page_results['title_tag']))) > 3) {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['title_tag']));
		} else {
			$current_page['title_tag'] = 'The Best Pubs and Pokies in ' . $current_page_results['location_zone_name'];
		}
		if (strlen(trim(strip_tags($current_page_results['description_tag']))) > 3) {
			$current_page['description_tag'] = trim(strip_tags($current_page_results['description_tag']));
		} else {
			$current_page['description_tag'] = 'Where to play pokies in ' . $current_page_results['location_zone_name'] . ' - ' . STORE_NAME;
		}
		if (strlen(trim(strip_tags($current_page_results['keywords_tag']))) > 3) {
			$current_page['keywords_tag'] = trim(strip_tags($current_page_results['keywords_tag']));
		} else {
			$current_page['keywords_tag'] = '';
		}

		$breadcrumb->add($current_page_results['location_zone_name'], tep_href_link($current_location_zone_url));
	}
} else if (isset($_GET['info_id'])) {
	$current_page_query = tep_db_query("select information_name, information_description, title_tag, description_tag, keywords_tag from " . TABLE_INFORMATIONS . " where information_id = '" . (int)$_GET['info_id'] . "'");
	if (tep_db_num_rows($current_page_query)) {
		$current_page_results = tep_db_fetch_array($current_page_query);
		if (strlen(trim(strip_tags($current_page_results['title_tag']))) > 3) {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['title_tag']));
		} else {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['information_name']));
		}
		if (strlen(trim(strip_tags($current_page_results['description_tag']))) > 3) {
			$current_page['description_tag'] = trim(strip_tags($current_page_results['description_tag']));
		} else {
			$current_page['description_tag'] = $current_page_results['information_description'];
		}
		if (strlen(trim(strip_tags($current_page_results['keywords_tag']))) > 3) {
			$current_page['keywords_tag'] = trim(strip_tags($current_page_results['keywords_tag']));
		} else {
			$current_page['keywords_tag'] = '';
		}
	}
} else if (isset($_GET['article_id'])) {
	$current_page_query = tep_db_query("select a.article_id, a.article_name, a.article_description, a.article_pictures, a.likes, a.date_added, a.title_tag, a.description_tag, a.keywords_tag  from " . TABLE_ARTICLES . " a where a.status='1' and a.article_id='" . (int)$_GET['article_id'] . "'");
	if (tep_db_num_rows($current_page_query)) {
		$current_page_results = tep_db_fetch_array($current_page_query);
		if (strlen(trim(strip_tags($current_page_results['title_tag']))) > 3) {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['title_tag']));
		} else {
			$current_page['title_tag'] = trim(strip_tags($current_page_results['article_name']));
		}
		if (strlen(trim(strip_tags($current_page_results['description_tag']))) > 3) {
			$current_page['description_tag'] = trim(strip_tags($current_page_results['description_tag']));
		} else {
			$current_page['description_tag'] = '';
		}
		if (strlen(trim(strip_tags($current_page_results['keywords_tag']))) > 3) {
			$current_page['keywords_tag'] = trim(strip_tags($current_page_results['keywords_tag']));
		} else {
			$current_page['keywords_tag'] = '';
		}
		$breadcrumb->add('Articles', tep_href_link('articles/'));
		$breadcrumb->add($current_page_results['article_name'], tep_href_link('articles/' . $current_url));
	}
} else if ($PHP_SELF == 'articles.php') {

	$current_url = 'articles/';
	$current_page['title_tag'] = STORE_NAME . ' NZ blog and articles';
	$current_page['description_tag'] = STORE_NAME . ' NZ blog and articles';
	$current_page['keywords_tag'] = 'blog, articles';
	$breadcrumb->add('Articles', tep_href_link('articles/'));
}
