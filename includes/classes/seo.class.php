<?php
define('USE_SEO_REDIRECT_DEBUG', 'false');

class SEO_DataBase{
        /**
         * Database host (localhost, IP based, etc)
        * @var string
         */
        var $host;
        /**
         * Database user
        * @var string
         */
        var $user;
        /**
         * Database name
        * @var string
         */
        var $db;
        /**
         * Database password
        * @var string
         */
        var $pass;
        /**
         * Database link
        * @var resource
         */
        var $link_id;

/**
 * MySQL_DataBase class constructor 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $host
 * @param string $user
 * @param string $db
 * @param string $pass  
 */        
        function SEO_DataBase($host, $user, $db, $pass){
                $this->host = $host;
                $this->user = $user;
                $this->db = $db;
                $this->pass = $pass;                
                $this->ConnectDB();
                $this->SelectDB();
        } # end function

/**
 * Function to connect to MySQL 
 * @author Bobby Easland 
 * @version 1.1
 */        
        function ConnectDB(){
                $this->link_id = mysql_connect($this->host, $this->user, $this->pass);
        } # end function
        
/**
 * Function to select the database
 * @author Bobby Easland 
 * @version 1.0
 * @return resoource 
 */        
        function SelectDB(){
                return mysql_select_db($this->db);
        } # end function
        
/**
 * Function to perform queries
 * @author Bobby Easland 
 * @version 1.0
 * @param string $query SQL statement
 * @return resource 
 */        
        function Query($query){
                $result = @mysql_query($query, $this->link_id);
                return $result;
        } # end function
        
/**
 * Function to fetch array
 * @author Bobby Easland 
 * @version 1.0
 * @param resource $resource_id
 * @param string $type MYSQL_BOTH or MYSQL_ASSOC
 * @return array 
 */        
        function FetchArray($resource_id, $type = MYSQL_BOTH){
             if ($resource_id)
             {
                $result = mysql_fetch_array($resource_id, $type);
                return $result;
             }
             return false;
        } # end function
        
/**
 * Function to fetch the number of rows
 * @author Bobby Easland 
 * @version 1.0
 * @param resource $resource_id
 * @return mixed  
 */        
        function NumRows($resource_id){
                return @mysql_num_rows($resource_id);
        } # end function

/**
 * Function to fetch the last insertID
 * @author Bobby Easland 
 * @version 1.0
 * @return integer  
 */        
        function InsertID() {
                return mysql_insert_id();
        }
        
/**
 * Function to free the resource
 * @author Bobby Easland 
 * @version 1.0
 * @param resource $resource_id
 * @return boolean
 */        
        function Free($resource_id){
                return @mysql_free_result($resource_id);
        } # end function

/**
 * Function to add slashes
 * @author Bobby Easland 
 * @version 1.0
 * @param string $data
 * @return string 
 */        
        function Slashes($data){
                return addslashes($data);
        } # end function

/**
 * Function to perform DB inserts and updates - abstracted from osCommerce-MS-2.2 project
 * @author Bobby Easland 
 * @version 1.0
 * @param string $table Database table
 * @param array $data Associative array of columns / values
 * @param string $action insert or update
 * @param string $parameters
 * @return resource
 */        
        function DBPerform($table, $data, $action = 'insert', $parameters = '') {
                reset($data);
                if ($action == 'insert') {
                  $query = 'INSERT INTO `' . $table . '` (';
                  while (list($columns, ) = each($data)) {
                        $query .= '`' . $columns . '`, ';
                  }
                  $query = substr($query, 0, -2) . ') values (';
                  reset($data);
                  while (list(, $value) = each($data)) {
                        switch ((string)$value) {
                          case 'now()':
                                $query .= 'now(), ';
                                break;
                          case 'null':
                                $query .= 'null, ';
                                break;
                          default:
                                $query .= "'" . $this->Slashes($value) . "', ";
                                break;
                        }
                  }
                  $query = substr($query, 0, -2) . ')';
                } elseif ($action == 'update') {
                  $query = 'UPDATE `' . $table . '` SET ';
                  while (list($columns, $value) = each($data)) {
                        switch ((string)$value) {
                          case 'now()':
                                $query .= '`' .$columns . '`=now(), ';
                                break;
                          case 'null':
                                $query .= '`' .$columns .= '`=null, ';
                                break;
                          default:
                                $query .= '`' .$columns . "`='" . $this->Slashes($value) . "', ";
                                break;
                        }
                  }
                  $query = substr($query, 0, -2) . ' WHERE ' . $parameters;
                }
                return $this->Query($query);
        } # end function        
} # end class

/**
 * Ultimate SEO URLs Installer and Configuration Class
 *
 * Ultimate SEO URLs installer and configuration class offers a modular 
 * and easy to manage method of configuration.  The class enables the base
 * class to be configured and installed on the fly without the hassle of 
 * calling additional scripts or executing SQL.
 * @package Ultimate-SEO-URLs
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.1
 * @link http://www.oscommerce-freelancers.com/ osCommerce-Freelancers
 * @copyright Copyright 2005, Bobby Easland 
 * @author Bobby Easland 
 */
class SEO_URL_INSTALLER{        
        /**
         * The default_config array has all the default settings which should be all that is needed to make the base class work.
        * @var array
         */
        var $default_config;
        /**
         * Database object
        * @var object
         */
        var $DB;
        /**
         * $attributes array holds information about this instance
        * @var array
         */
        var $attributes;
        
/**
 * SEO_URL_INSTALLER class constructor 
 * @author Bobby Easland 
 * @version 1.1
 */        
        function SEO_URL_INSTALLER(){
                
                $this->attributes = array();
                
                $x = 0;
                $this->default_config = array();
                $this->default_config['SEO_ENABLED'] = array('DEFAULT' => 'true',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Enable SEO URLs?', 'SEO_ENABLED', 'true', 'Enable the SEO URLs?  This is a global setting and will turn them off completely.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");
                $x++;
                $this->default_config['SEO_URLS_FILTER_SHORT_WORDS'] = array('DEFAULT' => '3',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Filter Short Words', 'SEO_URLS_FILTER_SHORT_WORDS', '3', 'This setting will filter words less than or equal to the value from the URL.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, NULL)");
                $x++;
                $this->default_config['SEO_URLS_USE_W3C_VALID'] = array('DEFAULT' => 'true',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Output W3C valid URLs (parameter string)?', 'SEO_URLS_USE_W3C_VALID', 'true', 'This setting will output W3C valid URLs.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");
                $x++;
                $this->default_config['USE_SEO_CACHE_GLOBAL'] = array('DEFAULT' => 'true',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Enable SEO cache to save queries?', 'USE_SEO_CACHE_GLOBAL', 'true', 'This is a global setting and will turn off caching completely.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");               
                $x++;
                $this->default_config['USE_SEO_REDIRECT'] = array('DEFAULT' => 'true',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Enable automatic redirects?', 'USE_SEO_REDIRECT', 'true', 'This will activate the automatic redirect code and send 301 headers for old to new URLs.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");               
                $x++;
                $this->default_config['USE_SEO_PERFORMANCE_CHECK'] = array('DEFAULT' => 'false',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Enable permormance checker?', 'USE_SEO_PERFORMANCE_CHECK', 'false', 'This will cause the code to track all database queries so that its affect on the speed of the page can be determined. Enabling it will cause a small speed loss.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");
                $x++;
                $this->default_config['SEO_REWRITE_TYPE'] = array('DEFAULT' => 'Rewrite',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Choose URL Rewrite Type', 'SEO_REWRITE_TYPE', 'Rewrite', 'Choose which SEO URL format to use.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''Rewrite''),')");
                $x++;
                $this->default_config['SEO_CHAR_CONVERT_SET'] = array('DEFAULT' => '',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Enter special character conversions', 'SEO_CHAR_CONVERT_SET', '', 'This setting will convert characters.<br><br>The format <b>MUST</b> be in the form: <b>char=>conv,char2=>conv2</b>', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, NULL)");
                $x++;
                $this->default_config['SEO_REMOVE_ALL_SPEC_CHARS'] = array('DEFAULT' => 'false',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Remove all non-alphanumeric characters?', 'SEO_REMOVE_ALL_SPEC_CHARS', 'false', 'This will remove all non-letters and non-numbers.  This should be handy to remove all special characters with 1 setting.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'tep_cfg_select_option(array(''true'', ''false''),')");
                $x++;
                $this->default_config['SEO_URLS_CACHE_RESET'] = array('DEFAULT' => 'false',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Reset SEO URLs Cache', 'SEO_URLS_CACHE_RESET', 'false', 'This will reset the cache data for SEO', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), 'tep_reset_cache_data_seo_urls', 'tep_cfg_select_option(array(''reset'', ''false''),')");
                $x++;
                $this->default_config['SEO_URLS_UNINSTALL'] = array('DEFAULT' => 'false',
                                      'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES (NULL, 'Uninstall Ultimate SEO', 'SEO_URLS_DB_UNINSTALL', 'false', 'This will delete all of the entries in the configuration table for SEO', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), 'tep_reset_cache_data_seo_urls', 'tep_cfg_select_option(array(''uninstall'', ''false''),')");
                $this->init();
        } # end class constructor
        
/**
 * Initializer - if there are settings not defined the default config will be used and database settings installed. 
 * @author Bobby Easland 
 * @version 1.1
 */        
        function init(){
                foreach( $this->default_config as $key => $value ){
                        $container[] = defined($key) ? 'true' : 'false';
                } # end foreach
                $this->attributes['IS_DEFINED'] = in_array('false', $container) ? false : true;

                switch(true){
                        case ( !$this->attributes['IS_DEFINED'] ):
                                $this->eval_defaults();
                                $this->DB = new SEO_DataBase(DB_SERVER, DB_SERVER_USERNAME, DB_DATABASE, DB_SERVER_PASSWORD);
                                $sql = "SELECT configuration_key, configuration_value  
                                                FROM " . TABLE_CONFIGURATION . " 
                                                WHERE configuration_key LIKE 'SEO%' OR configuration_key LIKE 'USE_SEO%'";
                                $result = $this->DB->Query($sql);
                                $num_rows = $this->DB->NumRows($result);
                                $this->DB->Free($result);                
                                $this->attributes['IS_INSTALLED'] = (sizeof($container) == $num_rows) ? true : false;
                                if ( !$this->attributes['IS_INSTALLED'] ){
                                        $this->install_settings(); 
                                }
                                break;
                        default:
                                $this->attributes['IS_INSTALLED'] = true;
                                break;
                } # end switch
        } # end function
        
/**
 * This function evaluates the default serrings into defined constants 
 * @author Bobby Easland 
 * @version 1.0
 */        
        function eval_defaults(){
                foreach( $this->default_config as $key => $value ){
                    if (! defined($key))
                        define($key, $value['DEFAULT']);
                } # end foreach
        } # end function
        
/**
 * This function removes the database settings (configuration and cache)
 * @author Bobby Easland
 * @version 1.0
 */
        function uninstall_settings(){
                $cfgId_query = "SELECT configuration_group_id as ID FROM `".TABLE_CONFIGURATION_GROUP."` WHERE onfiguration_group_title = 'SEO URLs'";
                $cfgID = $this->DB->FetchArray( $this->DB->Query($cfgId_query) );
                $this->DB->Query("DELETE FROM `".TABLE_CONFIGURATION_GROUP."` WHERE `configuration_group_title` = 'SEO URLs'");
                $this->DB->Query("DELETE FROM `".TABLE_CONFIGURATION."` WHERE configuration_group_id = '" . $cfgID['ID'] . "' OR configuration_key LIKE 'SEO_%' OR configuration_key LIKE 'USE_SEO_%'");
            $this->DB->Query("DROP TABLE IF EXISTS `cache`");
        } # end function
        
/**
 * This function installs the database settings
 * @author Bobby Easland 
 * @version 1.0
 */        
        function install_settings(){
                $this->uninstall_settings();
                $sort_order_query = "SELECT MAX(sort_order) as max_sort FROM `".TABLE_CONFIGURATION_GROUP."`";
                $sort = $this->DB->FetchArray( $this->DB->Query($sort_order_query) );
                $next_sort = $sort['max_sort'] + 1;
                $insert_group = "INSERT INTO `".TABLE_CONFIGURATION_GROUP."` VALUES (NULL, 'SEO URLs', 'Options for Ultimate SEO URLs by Chemo', '".$next_sort."', '1')";
                $this->DB->Query($insert_group);
                $group_id = $this->DB->InsertID();

                foreach ($this->default_config as $key => $value){
                        $sql = str_replace('GROUP_INSERT_ID', $group_id, $value['QUERY']);
                        $this->DB->Query($sql);
                }

                $insert_cache_table = "CREATE TABLE `cache` (
                  `cache_id` varchar(32) NOT NULL default '',
                  `cache_language_id` tinyint(1) NOT NULL default '0',
                  `cache_name` varchar(255) NOT NULL default '',
                  `cache_data` mediumtext NOT NULL,
                  `cache_global` tinyint(1) NOT NULL default '1',
                  `cache_gzip` tinyint(1) NOT NULL default '1',
                  `cache_method` varchar(20) NOT NULL default 'RETURN',
                  `cache_date` datetime NOT NULL,
                  `cache_expires` datetime NOT NULL,
                  PRIMARY KEY  (`cache_id`,`cache_language_id`),
                  KEY `cache_id` (`cache_id`),
                  KEY `cache_language_id` (`cache_language_id`),
                  KEY `cache_global` (`cache_global`)
                ) TYPE=MyISAM;";
                $this->DB->Query($insert_cache_table);
        } # end function        
} # end class

/**
 * Ultimate SEO URLs Base Class
 *
 * Ultimate SEO URLs offers search engine optimized URLS for osCommerce
 * based applications. Other features include optimized performance and 
 * automatic redirect script.
 * @package Ultimate-SEO-URLs
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 2.1
 * @link http://www.oscommerce-freelancers.com/ osCommerce-Freelancers
 * @copyright Copyright 2005, Bobby Easland 
 * @author Bobby Easland 
 */
class SEO_URL{
        /**
         * $cache is the per page data array that contains all of the previously stripped titles
        * @var array
         */
        var $cache;
        /**
         * $languages_id contains the language_id for this instance
        * @var integer
         */
        var $languages_id;
        /**
         * $attributes array contains all the required settings for class
        * @var array
         */
        var $attributes;
        /**
         * $base_url is the NONSSL URL for site
        * @var string
         */
        var $base_url;
        /**
         * $base_url_ssl is the secure URL for the site
        * @var string
         */
        var $base_url_ssl;
        /**
         * $performance array contains evaluation metric data
        * @var array
         */
        var $performance;
        /**
         * $timestamp simply holds the temp variable for time calculations
        * @var float
         */
        var $timestamp;
        /**
         * $reg_anchors holds the anchors used by the .htaccess rewrites
        * @var array
         */
        var $reg_anchors;
        /**
         * $cache_query is the resource_id used for database cache logic
        * @var resource
         */
        var $cache_query;
        /**
         * $cache_file is the basename of the cache database entry
        * @var string
         */
        var $cache_file;
        /**
         * $data array contains all records retrieved from database cache
        * @var array
         */
        var $data;
        /**
         * $need_redirect determines whether the URL needs to be redirected
        * @var boolean
         */
        var $need_redirect;
        /**
         * $is_seopage holds value as to whether page is in allowed SEO pages
        * @var boolean
         */
        var $is_seopage;
        /**
         * $uri contains the $_SERVER['REQUEST_URI'] value
        * @var string
         */
        var $uri;
        /**
         * $real_uri contains the $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'] value
        * @var string
         */
        var $real_uri;
        /**
         * $uri_parsed contains the parsed uri value array
        * @var array
         */
        var $uri_parsed;
        /**
         * $path_info contains the getenv('PATH_INFO') value
        * @var string
         */
        var $path_info;
        /**
         * $DB is the database object
        * @var object
         */
        var $DB;
        /**
         * $installer is the installer object
        * @var object
         */
        var $installer;

/**
 * SEO_URL class constructor
 * @author Bobby Easland
 * @version 1.1
 * @param integer $languages_id
 */
        function SEO_URL($languages_id){
            global $session_started, $SID;

                $this->installer = new SEO_URL_INSTALLER;

                $this->DB = new SEO_DataBase(DB_SERVER, DB_SERVER_USERNAME, DB_DATABASE, DB_SERVER_PASSWORD);

                $this->languages_id = (int)$languages_id;

                $this->data = array();
                $this->turnOffBrokenUrls(); // Turn off experimental oscommerce search engine friendly urls

//ojp FILENAME_LINKS
                $seo_pages = array(FILENAME_DEFAULT,
                                   FILENAME_PRODUCT_INFO);
                                   if ( defined('FILENAME_INFORMATION') ) $seo_pages[] = FILENAME_INFORMATION;
								   if ( defined('FILENAME_PLATFORMS') ) $seo_pages[] = FILENAME_PLATFORMS;
								   if ( defined('FILENAME_GAMES') ) $seo_pages[] = FILENAME_GAMES;
								   if ( defined('FILENAME_GUIDES') ) $seo_pages[] = FILENAME_GUIDES;
                                   

//ojp USE_SEO_CACHE_LINKS
                $this->attributes = array('PHP_VERSION' => PHP_VERSION,
                                          'SESSION_STARTED' => $session_started,
                                          'SID' => $SID,
                                          'SEO_ENABLED' => defined('SEO_ENABLED') ? SEO_ENABLED : 'false',                                          
                                          'SEO_URLS_USE_W3C_VALID' => defined('SEO_URLS_USE_W3C_VALID') ? SEO_URLS_USE_W3C_VALID : 'true',
                                          'USE_SEO_CACHE_GLOBAL' => defined('USE_SEO_CACHE_GLOBAL') ? USE_SEO_CACHE_GLOBAL : 'false',                             
                                          'USE_SEO_REDIRECT' => defined('USE_SEO_REDIRECT') ? USE_SEO_REDIRECT : 'false',
										  'USE_SEO_PERFORMANCE_CHECK' => defined('USE_SEO_PERFORMANCE_CHECK') ? USE_SEO_PERFORMANCE_CHECK : 'false',
                                          'SEO_REWRITE_TYPE' => defined('SEO_REWRITE_TYPE') ? SEO_REWRITE_TYPE : 'false',
                                          'SEO_URLS_FILTER_SHORT_WORDS' => defined('SEO_URLS_FILTER_SHORT_WORDS') ? SEO_URLS_FILTER_SHORT_WORDS : 'false',
                                          'SEO_CHAR_CONVERT_SET' => defined('SEO_CHAR_CONVERT_SET') ? $this->expand(SEO_CHAR_CONVERT_SET) : 'false',
                                          'SEO_REMOVE_ALL_SPEC_CHARS' => defined('SEO_REMOVE_ALL_SPEC_CHARS') ? SEO_REMOVE_ALL_SPEC_CHARS : 'false',
                                          'SEO_PAGES' => $seo_pages,
                                          'SEO_INSTALLER' => $this->installer->attributes
                                                                  );

                $this->base_url = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
                $this->base_url_ssl = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
                $this->cache = array();
                $this->timestamp = 0;

                $this->reg_anchors = array('info_id' => '-i-',
                                           );

                $this->performance = array('NUMBER_URLS_GENERATED' => 0,
                                           'NUMBER_QUERIES' => 0,
                                           'CACHE_QUERY_SAVINGS' => 0,
                                           'NUMBER_STANDARD_URLS_GENERATED' => 0,
                                           'TOTAL_CACHED_PER_PAGE_RECORDS' => 0,
                                           'TOTAL_TIME' => 0,
                                           'TIME_PER_URL' => 0,
                                           'QUERIES' => array()
                                          );
//ojp generate_link_cache

                if ($this->attributes['SEO_ENABLED'] == 'true' && $this->attributes['USE_SEO_REDIRECT'] == 'true'){
                        $this->check_redirect();
                } # end if
        } # end constructor

/**
 * Function to return SEO URL link SEO'd with stock generattion for error fallback
 * @author Bobby Easland
 * @version 1.0
 * @param string $page Base script for URL
 * @param string $parameters URL parameters
 * @param string $connection NONSSL/SSL
 * @param boolean $add_session_id Switch to add osCsid
 * @return string Formed href link
 */
        function href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true){
                // Some sites have hardcoded &amp;
                $parameters = str_replace('&amp;', '&', $parameters);
                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') {
                   $this->start($this->timestamp);
                   $this->performance['NUMBER_URLS_GENERATED']++;
                }
         
                if ( !in_array($page, $this->attributes['SEO_PAGES']) || $this->attributes['SEO_ENABLED'] == 'false' ) {
                   return $this->stock_href_link($page, $parameters, $connection, $add_session_id);
                }

                $link = $connection == 'NONSSL' ? $this->base_url : $this->base_url_ssl;
                $separator = '?';
               
                if ($this->not_null($parameters)) {
                  $link .= $this->parse_parameters($page, $parameters, $separator);
                } else {
                  $link .= $page;
                }
                $link = $this->add_sid($link, $add_session_id, $connection, $separator); 
                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') {
                  $this->stop($this->timestamp, $time);
                  $this->performance['TOTAL_TIME'] += $time;
                }
                  
                switch($this->attributes['SEO_URLS_USE_W3C_VALID']){
                        case ('true'):
                                if (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')){
                                        return $link;
                                } else {
                                    //    return mb_convert_encoding($link, 'UTF-8', mb_detect_encoding($link));        
                                         return htmlspecialchars(utf8_encode($link));
                                }
                                break;
                        case ('false'):
                                return $link;
                                break;
                }
        } # end function

/**
 * Stock function, fallback use 
 */        
  function stock_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID;
    if (!$this->not_null($page)) {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
    }
        if ($page == '/') $page = '';
    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
    }
    if ($this->not_null($parameters)) {
      $link .= $page . '?' . $this->output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }
    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if ($this->not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = $this->SessionName() . '=' . $this->SessionID();
        }
      }
    }
    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);
      $separator = '?';
    }
        switch(true){
                case (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')):
                        $page_cache = true;
                        $return = $link . $separator . '<osCsid>';
                        break;
                case (isset($_sid)):
                        $page_cache = false;
                        $return = $link . $separator . tep_output_string($_sid);
                        break;
                default:
                        $page_cache = false;
                        $return = $link;
                        break;
        } # end switch
        if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['NUMBER_STANDARD_URLS_GENERATED']++;
        $this->cache['STANDARD_URLS'][] = $link;
        if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') {
          $time = 0;
          $this->stop($this->timestamp, $time);
          $this->performance['TOTAL_TIME'] += $time;
        }
        switch(true){
                case ($this->attributes['SEO_URLS_USE_W3C_VALID'] == 'true' && !$page_cache):
                        return htmlspecialchars(utf8_encode($return));
                        break;
                default:
                        return $return;
                        break;
        }# end swtich
  } # end default tep_href function

/**
 * Function to append session ID if needed 
 * @author Bobby Easland 
 * @version 1.2
 * @param string $link 
 * @param boolean $add_session_id
 * @param string $connection
 * @param string $separator
 * @return string
 */        
        function add_sid( $link, $add_session_id, $connection, $separator ){
                global $request_type; // global variable
                if ( ($add_session_id) && ($this->attributes['SESSION_STARTED']) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
                  if ($this->not_null($this->attributes['SID'])) {
                        $_sid = $this->attributes['SID'];
                  } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
                        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
                          $_sid = $this->SessionName() . '=' . $this->SessionID();
                        }
                  }
                } 
                switch(true){
                        case (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')):
                                $return = $link . $separator . '<osCsid>';
                                break;
                        case (isset($_sid) && $this->not_null($_sid)):
                                $return = $link . $separator . tep_output_string($_sid);
                                break;
                        default:
                                $return = $link;
                                break;
                } # end switch
                return $return;
        } # end function
        
/**
 * SFunction to parse the parameters into an SEO URL 
 * @author Bobby Easland 
 * @version 1.2
 * @param string $page
 * @param string $params
 * @param string $separator NOTE: passed by reference
 * @return string 
 */        
        function parse_parameters($page, $params, &$separator){
                $p = @explode('&', $params);				
                krsort($p);		
                $container = array();
                foreach ($p as $index => $valuepair){
                        $p2 = @explode('=', $valuepair); 
                        switch ($p2[0]){                                                                                              
                                case 'info_id': //Information Pages
                                     switch(true){
                                             case ($page == FILENAME_INFORMATION):
                                                     $url = $this->make_url($page, $this->get_information_name($p2[1]), $p2[0], $p2[1], '.html');
                                                     break;
                                             default: 
                                                     $container[$p2[0]] = $p2[1];
                                                     break;
                                     } # end switch
                                     break;				
                           		    
                       		     default:
                                        if( isset($p2[1]) ) $container[$p2[0]] = $p2[1]; 
                                        break;
                        } # end switch
                } # end foreach $p
                $url = isset($url) ? $url : $page;
                if ( sizeof($container) > 0 ){
                        if ( $imploded_params = $this->implode_assoc($container) ){
                                $url .= $separator . $this->output_string( $imploded_params );
                                $separator = '&';
                        }
                }
				

                return $url;
        } # end function

/**
 * Function to return the generated SEO URL         
 * @author Bobby Easland 
 * @version 1.0
 * @param string $page
 * @param string $string Stripped, formed anchor
 * @param string $anchor_type Parameter type (products_id, cPath, etc.)
 * @param integer $id
 * @param string $extension Default = .html
 * @param string $separator NOTE: passed by reference -- NOTE: not used so removed
 * @return string
 */        
        function make_url($page, $string, $anchor_type, $id, $extension = '.html'){
                // Right now there is but one rewrite method since cName was dropped
                // In the future there will be additional methods here in the switch
                switch ( $this->attributes['SEO_REWRITE_TYPE'] ){
                        case 'Rewrite':
                                return $string.'/';
								//return $string . $this->reg_anchors[$anchor_type] . $id . $extension;
                                break;
                        default:
                                break;
                } # end switch
        } # end function
		
		// Get Site Content
/**
 * Function to get the informatin name. Use evaluated cache, per page cache, or database query in that order of precedent.
 * @author Bobby Easland 
 * @version 1.1
 * @param integer $iID
 * @return string
 */        
        function get_information_name($iID){
                switch(true){
                        case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('INFO_NAME_' . $iID)):
                                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['CACHE_QUERY_SAVINGS']++;
                                $return = constant('INFO_NAME_' . $iID);
                                $this->cache['INFO'][$iID] = $return;
                                break;
                        case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['INFO'][$iID])):
                                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['CACHE_QUERY_SAVINGS']++;
                                $return = $this->cache['INFO'][$iID];
                                break;
                        default:
                                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['NUMBER_QUERIES']++;
                                $sql = "SELECT information_title as iName 
                                               FROM information 
                                               WHERE information_id='".(int)$iID."' 
                                               AND language_id='".(int)$this->languages_id."' 
                                               LIMIT 1";
                                $result = $this->DB->FetchArray( $this->DB->Query( $sql ) );
                                $iName = $this->strip( $result['iName'] );
                                $this->cache['INFO'][$iID] = $iName;
                                if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['QUERIES']['INFO'][] = $sql;
                                $return = $iName;
                                break;                                                                
                } # end switch                
                return $return;
        } # end function
        
/**
 * Function to check if a value is NULL 
 * @author Bobby Easland as abstracted from osCommerce-MS2.2 
 * @version 1.0
 * @param mixed $value
 * @return boolean
 */        
        function not_null($value) {
                if (is_array($value)) {
                        if (sizeof($value) > 0) {
                                return true;
                        } else {
                                return false;
                        }
                } else {
                        if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
                                return true;
                        } else {
                                return false;
                        }
                }
        } # end function

/**
 * Function to check if the products_id contains an attribute 
 * @author Bobby Easland 
 * @version 1.1
 * @param integer $pID
 * @return boolean
 */        
        function is_attribute_string($pID){
                if ( is_numeric(strpos($pID, '{')) ){
                        return true;
                } else {
                        return false;
                }
        } # end function

/**
 * Function to check if the params contains a products_id 
 * @author Bobby Easland 
 * @version 1.1
 * @param string $params
 * @return boolean
 */        
        function is_product_string($params){
                if ( is_numeric(strpos('products_id', $params)) ){
                        return true;
                } else {
                        return false;
                }
        } # end function

/**
 * Function to check if cPath is in the parameter string  
 * @author Bobby Easland 
 * @version 1.0
 * @param string $params
 * @return boolean
 */        
        function is_cPath_string($params){
                if ( preg_match('/cPath/i', $params) ){
                        return true;
                } else {
                        return false;
                }
        } # end function

/**
 * Function used to output class profile
 * @author Bobby Easland 
 * @version 1.0
 */        
        function profile(){
                $this->calculate_performance();
                $this->PrintArray($this->attributes, 'Class Attributes');
                $this->PrintArray($this->cache, 'Cached Data');
        } # end function

/**
 * Function used to calculate and output the performance metrics of the class
 * @author Bobby Easland 
 * @version 1.0
 * @return mixed Output of performance data wrapped in HTML pre tags
 */        
        function calculate_performance(){
                foreach ($this->cache as $type){
                        if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['TOTAL_CACHED_PER_PAGE_RECORDS'] += sizeof($type);                        
                }
                $this->performance['TIME_PER_URL'] = $this->performance['TOTAL_TIME'] / $this->performance['NUMBER_URLS_GENERATED'];
                return $this->PrintArray($this->performance, 'Performance Data');
        } # end function
        
/**
 * Function to strip the string of punctuation and white space 
 * @author Bobby Easland 
 * @version 1.1
 * @param string $string
 * @return string Stripped text. Removes all non-alphanumeric characters.
 */        
        function strip($string){
                if ( is_array($this->attributes['SEO_CHAR_CONVERT_SET']) ) $string = strtr($string, $this->attributes['SEO_CHAR_CONVERT_SET']);
          
                $pattern = $this->attributes['SEO_REMOVE_ALL_SPEC_CHARS'] == 'true'
//                                                ?        "([^[:alnum:]])+"
  //                                              :        "([[:punct:]])+";
                                               
                                                ?        "([^[:alnum:]])"
                                                :        "/[^a-z0-9- ]/i";

                $string = preg_replace('/((&#39))/', '-', strtolower($string)); //remove apostrophe - not caught by above
           		 //$anchor = preg_replace($pattern, '', mb_convert_case($string, MB_CASE_LOWER, "utf-8"));
                $anchor = preg_replace($pattern, '', strtolower($string));
                $pattern = "([[:space:]]|[[:blank:]])";
                $anchor = preg_replace($pattern, '-', $anchor);
                return $this->short_name($anchor); // return the short filtered name 
        } # end function

/**
 * Function to expand the SEO_CONVERT_SET group 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $set
 * @return mixed
 */        
        function expand($set){
                $container = array();
                if ( $this->not_null($set) ){
                        if ( $data = @explode(',', $set) ){
                                foreach ( $data as $index => $valuepair){
                                        $p = @explode('=>', $valuepair);
                                        $container[trim($p[0])] = trim($p[1]);
                                }
                                return $container;
                        } else {
                                return 'false';
                        }
                } else {
                        return 'false';
                }
        } # end function
/**
 * Function to return the short word filtered string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $str
 * @param integer $limit
 * @return string Short word filtered
 */        
        function short_name($str, $limit=3){
                $container = array();
                if ( $this->attributes['SEO_URLS_FILTER_SHORT_WORDS'] != 'false' ) $limit = (int)$this->attributes['SEO_URLS_FILTER_SHORT_WORDS'];
                $foo = @explode('-', $str);
                foreach($foo as $index => $value){
                        switch (true){
                                case ( strlen($value) <= $limit ):
                                        continue;
                                default:
                                        $container[] = $value;
                                        break;
                        }                
                } # end foreach

                $container = ( sizeof($container) > 1 ? implode('-', $container) : (sizeof($container) > 0 ? $container[0] : $str ));
                return $container;
        }
        
/**
 * Function to implode an associative array 
 * @author Bobby Easland 
 * @version 1.0
 * @param array $array Associative data array
 * @param string $inner_glue
 * @param string $outer_glue
 * @return string
 */        
        function implode_assoc($array, $inner_glue='=', $outer_glue='&') {
                $output = array();
                foreach( $array as $key => $item ){
                        if ( $this->not_null($key) && $this->not_null($item) ){
                                $output[] = $key . $inner_glue . $item;
                        }
                } # end foreach        
                return @implode($outer_glue, $output);
        }

/**
 * Function to print an array within pre tags, debug use 
 * @author Bobby Easland 
 * @version 1.0
 * @param mixed $array
 */        
        function PrintArray($array, $heading = ''){
                echo '<fieldset style="border-style:solid; border-width:1px;">' . "\n";
                echo '<legend style="background-color:#FFFFCC; border-style:solid; border-width:1px;">' . $heading . '</legend>' . "\n";
                echo '<pre style="text-align:left;">' . "\n";
                print_r($array);
                echo '</pre>' . "\n";
                echo '</fieldset><br/>' . "\n";
        } # end function

/**
 * Function to start time for performance metric 
 * @author Bobby Easland 
 * @version 1.0
 * @param float $start_time
 */        
        function start(&$start_time){
                $start_time = explode(' ', microtime());
        }
        
/**
 * Function to stop time for performance metric 
 * @author Bobby Easland 
 * @version 1.0
 * @param float $start
 * @param float $time NOTE: passed by reference
 */        
        function stop($start, &$time){
                $end = explode(' ', microtime());
                $time = number_format( array_sum($end) - array_sum($start), 8, '.', '' );
        }

/**
 * Function to translate a string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $data String to be translated
 * @param array $parse Array of tarnslation variables
 * @return string
 */        
        function parse_input_field_data($data, $parse) {
                return strtr(trim($data), $parse);
        }
        
/**
 * Function to output a translated or sanitized string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $sting String to be output
 * @param mixed $translate Array of translation characters
 * @param boolean $protected Switch for htemlspecialchars processing
 * @return string
 */        
        function output_string($string, $translate = false, $protected = false) {
                if ($protected == true) {
                  return htmlspecialchars($string);
                } else {
                  if ($translate == false) {
                        return $this->parse_input_field_data($string, array('"' => '&quot;'));
                  } else {
                        return $this->parse_input_field_data($string, $translate);
                  }
                }
        }

/**
 * Function to return the session ID 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $sessid
 * @return string
 */        
        function SessionID($sessid = '') {
                if (!empty($sessid)) {
                  return session_id($sessid);
                } else {
                  return session_id();
                }
        }
        
/**
 * Function to return the session name 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name
 * @return string
 */        
        function SessionName($name = '') {
                if (!empty($name)) {
                  return session_name($name);
                } else {
                  return session_name();
                }
        }

/**
 * Function to convert time for cache methods 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $expires
 * @return string
 */        
        function convert_time($expires){ //expires date interval must be spelled out and NOT abbreviated !!
                $expires = explode('/', $expires);
                switch( strtolower($expires[1]) ){ 
                        case 'seconds':
                                $expires = mktime( @date("H"), @date("i"), @date("s")+(int)$expires[0], @date("m"), @date("d"), @date("Y") );
                                break;
                        case 'minutes':
                                $expires = mktime( @date("H"), @date("i")+(int)$expires[0], @date("s"), @date("m"), @date("d"), @date("Y") );
                                break;
                        case 'hours':
                                $expires = mktime( @date("H")+(int)$expires[0], @date("i"), @date("s"), @date("m"), @date("d"), @date("Y") );
                                break;
                        case 'days':
                                $expires = mktime( @date("H"), @date("i"), @date("s"), @date("m"), @date("d")+(int)$expires[0], @date("Y") );
                                break;
                        case 'months':
                                $expires = mktime( @date("H"), @date("i"), @date("s"), @date("m")+(int)$expires[0], @date("d"), @date("Y") );
                                break;
                        case 'years':
                                $expires = mktime( @date("H"), @date("i"), @date("s"), @date("m"), @date("d"), @date("Y")+(int)$expires[0] );
                                break;
                        default: // if something fudged up then default to 1 month
                                $expires = mktime( @date("H"), @date("i"), @date("s"), @date("m")+1, @date("d"), @date("Y") );
                                break;
                } # end switch( strtolower($expires[1]) )
                return @date("Y-m-d H:i:s", $expires);
        } # end function convert_time()

/**
 * Function to check if the cache is in the database and expired  
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name
 * @param boolean $is_cached NOTE: passed by reference
 * @param boolean $is_expired NOTE: passed by reference
 */        
        function is_cached($name, &$is_cached, &$is_expired){ // NOTE: $is_cached and $is_expired is passed by reference !!
                $this->cache_query = $this->DB->Query("SELECT cache_expires FROM cache WHERE cache_id='".md5($name)."' AND cache_language_id='".(int)$this->languages_id."' LIMIT 1");
                $is_cached = ( $this->DB->NumRows($this->cache_query ) > 0 ? true : false );
                if ($is_cached){ 
                        $check = $this->DB->FetchArray($this->cache_query);
                        $is_expired = ( $check['cache_expires'] <= @date("Y-m-d H:i:s") ? true : false );
                        unset($check);
                }
                $this->DB->Free($this->cache_query);
        }# end function is_cached()
         
/**
 * Function to initialize the redirect logic
 * @author Bobby Easland 
 * @version 1.1
 */        
        function check_redirect(){
                $this->need_redirect = false; 
                $this->path_info = is_numeric(strpos(ltrim(getenv('PATH_INFO'), '/') , '/')) ? ltrim(getenv('PATH_INFO'), '/') : NULL;
                $this->uri = ltrim( basename($_SERVER['REQUEST_URI']), '/' );
                $this->real_uri = ltrim( basename($_SERVER['SCRIPT_NAME']) . '?' . $_SERVER['QUERY_STRING'], '/' );
                $this->uri_parsed = $this->not_null( $this->path_info )
                                                                ?        parse_url(basename($_SERVER['SCRIPT_NAME']) . '?' . $this->parse_path($this->path_info) )
                                                                :        parse_url(basename($_SERVER['REQUEST_URI']));                        
                $this->attributes['SEO_REDIRECT']['PATH_INFO'] = $this->path_info;                        
                $this->attributes['SEO_REDIRECT']['URI'] = $this->uri;
                $this->attributes['SEO_REDIRECT']['REAL_URI'] = $this->real_uri;                        
                $this->attributes['SEO_REDIRECT']['URI_PARSED'] = $this->uri_parsed;    
				
				//echo '<pre>'; print_r($this->attributes['SEO_REDIRECT']); echo '</pre>';
				
				//echo '- check_redirect() - '. $_SERVER['QUERY_STRING'] .' - the query string <br/>';

 
                /**** redirect child path to full path - i.e., -c-3782.html to -c-28_3782.html, when applicable ****/
                if (strpos($this->attributes['SEO_REDIRECT']['URI_PARSED']['path'], '.html') !== FALSE) {
                    $u1 = $this->attributes['SEO_REDIRECT']['URI_PARSED']['path'];
					
					//echo '- check_redirect() 2- '.$u1.' - uri parsed path <br/>';
               		
                    if (($pStart = strpos($u1, "-c-")) !== FALSE) {         //start isolating the ID - only for categories
						//echo '- check_redirect() 2.1- '.$u1.' - check if it goes on <br/>';
                       if (($pStop = strpos($u1, ".html")) !== FALSE) {
                          $path = substr($u1, $pStart, $pStop);             //will be something like -c-34.html
                          if (($pStart = strpos($path, "-")) !== FALSE) {   //isolate to the number
                              if (($pStop = strpos($path, ".html")) !== FALSE) {
                                  /**** GET THE ID's AND PATH's ****/
                                  $actualID = substr($path, $pStart + 3, $pStop - 3); //will be something like 34
                                  $fullID = $this->get_full_cPath($actualID, $actualID); //will be something like 34 or 34_35
                                  $actualPath = $actualID . '.html';        //save a few instructions
                                  
                                  /**** REPLACE THE PARTIAL ID IN THE URL's WITH THE FULL ONE ****/
                                  $idPos = strpos($this->attributes['SEO_REDIRECT']['REAL_URI'], $actualID);            
                                  $this->attributes['SEO_REDIRECT']['REAL_URI'] = substr_replace($this->attributes['SEO_REDIRECT']['REAL_URI'], $fullID, $idPos, strlen($idPos));
                                  $idPos = strpos($this->attributes['SEO_REDIRECT']['URI'], $actualID);  
                                  $this->attributes['SEO_REDIRECT']['URI'] = substr_replace($this->attributes['SEO_REDIRECT']['URI'], $fullID, $idPos, strlen($idPos));
                                  
                                  if (strpos($this->attributes['SEO_REDIRECT']['URI_PARSED']['path'], '-c-'.$actualPath) !== FALSE) { //this is the actual url
                                      if ($fullID != $actualID && strpos($fullID.'.html', $actualPath) !== FALSE) { //enteed url is child of full path
                                          $url = $this->make_url($page, $this->get_category_name($actualID), 'cPath', $fullID, '.html');
                                          $this->uri_parsed['path'] = $url; //reset the url
                                          $this->need_redirect = true; 
                                          $this->is_seopage = true;  
                                          if ( $this->need_redirect && $this->is_seopage && $this->attributes['USE_SEO_REDIRECT'] == 'true') $this->do_redirect(); 
                                      }
                                  }  
                              }  
                          }
                       }
                    }
                }
        
        
                /**** redirect for special case of cat ID = 0 ****/
                if (strpos($this->attributes['SEO_REDIRECT']['URI_PARSED']['path'], '.html') !== FALSE) {
                    $u1 = $this->attributes['SEO_REDIRECT']['URI_PARSED']['path'];
               		
					
					//echo '- check_redirect() 3- '.$u1.' - uri parsed path <br/>';
					
                    if (($pStart = strpos($u1, "-c-")) !== FALSE) {         //start isolating the ID - only for categories
						echo '- check_redirect() 3.1- '.$u1.' - check if it goes on <br/>';
                       if (($pStop = strpos($u1, ".html")) !== FALSE) {
                          $path = substr($u1, $pStart, $pStop + 5);             //will be something like -c-34.html

                          if (($pStart = strpos($path, "-")) !== FALSE) {   //isolate to the number
                              if (($pStop = strpos($path, ".html")) !== FALSE) {
                              
                                  /**** GET THE ID's AND PATH's ****/
                                  $actualID = substr($path, $pStart + 3, $pStop - 3); //will be something like 34
                                  if ($actaulID == 0) {
                                      $actualPath = $actualID . '.html';        //save a few instructions
                                      
                                      /**** REPLACE THE PARTIAL ID IN THE URL's WITH THE FULL ONE ****/
                                      $this->attributes['SEO_REDIRECT']['REAL_URI'] = 'index.php';
                                      $this->attributes['SEO_REDIRECT']['URI'] = '';
                                      
                                      if (strpos($this->attributes['SEO_REDIRECT']['URI_PARSED']['path'], '-c-'.$actualPath) !== FALSE) { //this is the actual url
                                          if (0 == $actualID && strpos($actualID.'.html', $actualPath) !== FALSE) { //enteed url is child of full path
                                              $url = 'index.php';
                                              $this->uri_parsed['path'] = $url; //reset the url
                                              $this->need_redirect = true; 
                                              $this->is_seopage = true;  
                                              if ( $this->need_redirect && $this->is_seopage && $this->attributes['USE_SEO_REDIRECT'] == 'true') {
                                                  header("HTTP/1.0 404 not found");
                                                  header("Location: $url"); // redirect...bye bye  
                                              } 
                                          }
                                      }  
                                  }
                              }  
                          }
                       }
                    }
                }           

                      
                $this->need_redirect(); 
                $this->check_seo_page();  
                if ( $this->need_redirect && $this->is_seopage && $this->attributes['USE_SEO_REDIRECT'] == 'true') $this->do_redirect();                        
        } # end function
        
        function turnOffBrokenUrls(){
          if( defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS == 'true' ){
            $sql = "
            UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = 'false'
            WHERE configuration_key = 'SEARCH_ENGINE_FRIENDLY_URLS'";
            $this->DB->Query($sql);
          }
        }
        
/**
 * Function to check if the URL needs to be redirected 
 * @author Bobby Easland 
 * @version 1.2
 */        
        function need_redirect(){ 
        global $SID;     

                foreach( $this->reg_anchors as $param => $value){
                        $pattern[] = $param;
                }
				
				//echo '<pre>'; print_r($pattern); echo '</pre>';
				
                switch(true){
                        case ($this->is_attribute_string($this->uri)):
                                $this->need_redirect = false;
								//echo '- 1'.'<br/>';
                                break;
                        case ($this->uri != $this->real_uri && !$this->not_null($this->path_info)):
                                //echo'- need redirect uri - '. $this->uri.'<br/>';
								 //echo '- need redirect real uri - '.$this->real_uri.'<br/>';
								 // echo '- need redirect path info - '.$this->path_info.'<br/>';
								
								
								if (($pStart = strpos($this->uri_parsed['path'], "-p-")) !== FALSE) {
								
									//echo '- 11'.'<br/>';
                                    if (($pStop = strpos($this->uri_parsed['path'], ".html")) !== FALSE) {

                                       $forceRedirect = $this->VerifyLink($pStop, $pStart); //remove things that shouldn't be there
                                                                        
                                       if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['NUMBER_QUERIES']++;
                                       $pID = substr($this->uri_parsed['path'], $pStart + 3, -(strlen($this->uri_parsed['path']) - $pStop));
                                       $sqlCmd = $this->attributes['USE_SEO_HEADER_TAGS'] == 'true' ? 'products_head_title_tag as pName' : 'products_name as pName';
                                       $sql = "SELECT " . $sqlCmd . "
                                             FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                             WHERE products_id='".(int)$pID."'
                                             AND language_id='".(int)$this->languages_id."'
                                             LIMIT 1";
                                       $result = $this->DB->FetchArray( $this->DB->Query( $sql ) );

                                       $cName = '';
                                       if ($this->attributes['SEO_ADD_CPATH_TO_PRODUCT_URLS'] == 'true') {
                                          $cName = $this->get_all_category_parents($pID, $cName);
                                          $cName = str_replace(" ", "-", $cName) . '-';
                                       }

                                       $pName = $cName . $this->strip( $result['pName'] );
                                       if ($forceRedirect || ($pName !== substr($this->uri_parsed['path'], 0, $pStart))) {
                                         $this->uri_parsed['path'] = $pName . "-p-" . $pID . ".html";
                                         $this->need_redirect = true;
                                         $this->do_redirect();
                                       }
                                    }
									
                                } 
                                                                
                                else if (($pStart = strpos($this->uri_parsed['path'], "-c-")) !== FALSE) {
									//echo '- 10'.'<br/>';
                                    if (($pStop = strpos($this->uri_parsed['path'], ".html")) !== FALSE) {

                                       $forceRedirect = $this->VerifyLink($pStop, $pStart); //remove things that shouldn't be there
                                       $cID = substr($this->uri_parsed['path'], $pStart + 3, -(strlen($this->uri_parsed['path']) - $pStop));

                                       if ($this->attributes['SEO_ADD_CAT_PARENT'] != 'true') {
                                          if (strpos($cID, "_") !== FALSE) { //test for sub-category
                                            $parts = explode("_", $cID);
                                            $cID = $parts[count($parts) - 1];
                                          }

                                          if ($this->attributes['USE_SEO_PERFORMANCE_CHECK'] == 'true') $this->performance['NUMBER_QUERIES']++;
                                          $sqlCmd = $this->attributes['USE_SEO_HEADER_TAGS'] == 'true' ? 'LOWER(categories_htc_title_tag) as cName' : 'LOWER(categories_name) as cName';
                                          $sql = "SELECT " . $sqlCmd . "
                                              FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                              WHERE categories_id='".(int)$cID."'
                                              AND language_id='".(int)$this->languages_id."'
                                              LIMIT 1";
                                          $result = $this->DB->FetchArray( $this->DB->Query( $sql ) );
                                          $cName = $result['cName'];
                                      } else {
                                          $cID = $this->get_full_cPath($cID, $single_cID); // full cPath needed for uniformity
                                          $sqlCmd = $this->attributes['USE_SEO_HEADER_TAGS'] == 'true' ? 'LOWER(categories_htc_title_tag) as cName' : 'LOWER(categories_name) as cName';
                                          $sql = "SELECT " . $sqlCmd . "
                                              FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                              WHERE categories_id='".(int)$single_cID."'
                                              AND language_id='".(int)$this->languages_id."'
                                              LIMIT 1";
                                          $result = $this->DB->FetchArray( $this->DB->Query( $sql ) );
                                          $cName = $result['cName'];
                                          if ($this->attributes['SEO_ADD_CAT_PARENT'] == 'true') $cName = $this->get_all_category_names($single_cID, $cName );
                                       }
                                       $cName = $this->strip( $cName);

                                       if ($forceRedirect || ($cName !== substr($this->uri_parsed['path'], 0, $pStart))) {
                                         $this->uri_parsed['path'] = $cName . "-c-" . $cID . ".html";
                                         $this->need_redirect = true;
                                         $this->do_redirect();
                                       }
                                    }
									
									//echo '- 77'.'<br/>';
                                }
								//echo '- 88'.'<br/>';
                                $this->need_redirect = false;
                                break;
                        case (is_numeric(strpos($this->uri, '.htm'))):
                                $this->need_redirect = false;
								//echo '- 3'.'<br/>';
                                break;
                        case (@preg_match("/(".@implode('|', $pattern).")/i", $this->uri)):
                                $this->need_redirect = true;
								//echo '- 4'.'<br/>';
                                break;
                        case (@preg_match("/(".@implode('|', $pattern).")/i", $this->path_info)):
                                $this->need_redirect = true;
								//echo '- 5'.'<br/>';
                                break;
                        default:
                                break;
                } # end switch
                $this->attributes['SEO_REDIRECT']['NEED_REDIRECT'] = $this->need_redirect ? 'true' : 'false';
        } # end function set_seopage

        
/**
 * Function to check if the url is valid
 * @author Jack York 
 * @version 1.1
 */        
   function VerifyLink(&$pStop, $pStart) {
      $r1 = $this->base_url.$this->uri_parsed['path'];
      $p1 = strpos($_SERVER['REQUEST_URI'], $this->attributes['SEO_REDIRECT']['URI_PARSED']['path']);
      $r2 = substr($_SERVER['REQUEST_URI'], 0, $p1);
	  
	  //echo '- verify_link()'.$pStop. ' - '.$pStop.'<br/>';
	  
      if (strpos($r1, $r2) === FALSE) {
         return true;
      }
                               
      /*** begin check for characters at end of string before .html ***/
      $endStr = substr($this->uri_parsed['path'], $pStart + 3, $pStop - $pStart - 3);
      if (! preg_match("/^([0-9_]+)$/", $endStr)) {
         $parts = explode("_",$endStr);
         for ($p = 0; $p < count($parts); ++$p) {
             $parts[$p] = (int)$parts[$p];
         }
         $newStr = implode("_", $parts);
         $this->uri_parsed['path'] = str_replace($endStr, $newStr, $this->uri_parsed['path']);
         $pStop = strpos($this->uri_parsed['path'], ".html"); //recalculate the end
         return true;
      }
      
      return false;                                   
   }                                         

/**
 * Function to check if it's a valid redirect page
 * @author Bobby Easland 
 * @version 1.1
 */        
        function check_seo_page(){
				
				//echo '- check_seo_page()<br/>';
                switch (true){
                        case (@in_array($this->uri_parsed['path'], $this->attributes['SEO_PAGES'])):
								echo $this->uri_parsed['path'];
								print_r($this->attributes['SEO_PAGES']);
								echo '- checkseo true'.'<br/>';	
                                $this->is_seopage = true;
                                break;
                        case ($this->attributes['SEO_ENABLED'] == 'false'):							
                        default:
                                $this->is_seopage = false;
								//echo '- checkseo false'.'<br/>';
                                break;
                } # end switch
                $this->attributes['SEO_REDIRECT']['IS_SEOPAGE'] = $this->is_seopage ? 'true' : 'false';
				//echo '- check_seo_page()'.$this->attributes['SEO_REDIRECT']['IS_SEOPAGE'].'<br/>';
        } # end function check_seo_page
        
/**
 * Function to parse the path for old SEF URLs 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $path_info
 * @return array
 */        
        function parse_path($path_info){ 
		
				//echo '- parse_patht()'.$path_info.'<br/>';
		
                $tmp = @explode('/', $path_info);                 
                if ( sizeof($tmp) > 2 ){
                        $container = array();                                
                        for ($i=0, $n=sizeof($tmp); $i<$n; $i++) {
                                $container[] = $tmp[$i] . '=' . $tmp[$i+1]; 
                                $i++; 
                        }
                        return @implode('&', $container);                        
                } else { 
                        return @implode('=', $tmp);
                }            
				                  
        } # end function parse_path
        
/**
 * Function to perform redirect 
 * @author Bobby Easland 
 * @version 1.0
 */        
        function do_redirect(){
                $p = @explode('&', $this->uri_parsed['query']);
				
				//echo '- do redirect()'.'<br/>';;
				
                foreach( $p as $index => $value ){                                                        
                        $tmp = @explode('=', $value);
                                switch($tmp[0]){
                                        case 'products_id':
                                                if ( $this->is_attribute_string($tmp[1]) ){
                                                        $pieces = @explode('{', $tmp[1]);                                                        
                                                        $params[] = (tep_not_null($tmp[0]) ? $tmp[0] . '=' . $pieces[0] : '');
                                                } else {
                                                        $params[] = (tep_not_null($tmp[0]) ? $tmp[0] . '=' . $tmp[1] : '');
                                                }
                                                break;
                                        default:
                                                $params[] = (tep_not_null($tmp[0]) ? $tmp[0] . '=' . $tmp[1] : '');
                                                break;                                                
                                }
                } # end foreach( $params as $var => $value )
                $params = ( sizeof($params) > 1 ? implode('&', $params) : $params[0] );                
                $url = $this->href_link($this->uri_parsed['path'], $params, 'NONSSL', false);
									
                switch(true){
                        case (defined('USE_SEO_REDIRECT_DEBUG') && USE_SEO_REDIRECT_DEBUG == 'true'):
                                $this->attributes['SEO_REDIRECT']['REDIRECT_URL'] = $url;
                                break;
                        case ($this->attributes['USE_SEO_REDIRECT'] == 'true'):
                                header("HTTP/1.0 301 Moved Permanently");
                                $url = str_replace('&amp;', '&', $url);
                                header("Location: $url"); // redirect...bye bye                
                                break;
                        default:
                                $this->attributes['SEO_REDIRECT']['REDIRECT_URL'] = $url;
                                break;
                } # end switch
        } # end function do_redirect        
} # end class
?>
