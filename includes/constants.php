<?php
/*
 Copyright 2008-2012  ClearCode Ltd.  (email : contacts@clearcode.bg)

 This file is part of Statrix.

 Statrix is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * @package statrix
 * @author Iva Koleva <iva.koleva@clearcode.bg>
 * @copyright Copyright (c) 2008-2012, ClearCode Ltd.
 */

//CONSTANTS
/**
 * @var string Statrix directory name
 */
define('STATRIX_DIR', 'statrix');

/**
 * @var string Statrix main file name
 */
define('STATRIX_MAIN_FILE', 'statrix.php');

/**
 * @var int Statrix default access level
 */
define('STATRIX_DEFAULT_ACCESS_LEVEL', 8);

/**
 * @var string Statrix table name
 */
(isset($wpdb) ? define('STATRIX_TABLE', $wpdb->prefix . 'statrix') : false);

/**
 * @var string Statrix flags table name
 */
(isset($wpdb) ? define('STATRIX_IP2COUNTRY_TABLE', $wpdb->prefix . 'statrix_ip2country') : false);

/**
 * @var absolute path to the IP-country lib dir
 */
define('STATRIX_IP_COUNTRY_LIB_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . STATRIX_DIR . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'ip2country');

/**
 * @var absolute path to the IP-country CSV file
 */
define('STATRIX_IP_COUNTRY_FILE_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . STATRIX_DIR . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'ip2country' . DIRECTORY_SEPARATOR . 'GeoIPCountryWhois.csv');

/**
 * @var absolute url to the flags image dir
 */
define('STATRIX_FLAGS_IMAGE_DIR', WP_PLUGIN_URL . DIRECTORY_SEPARATOR . STATRIX_DIR . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flags' . DIRECTORY_SEPARATOR . 'png');

/**
 * @var string the key of the Statrix default action in the Statrix actions array
 * @see $statrixActions
 */
define('STATRIX_DEFAULT_ACTION', 'overview');

/**
 * @var int whois timeout in seconds
 */
define('STATRIX_WHOIS_TIMEOUT', 15);


//VARIABLES
/**
 * @var int contains Statrix access level
 */
$accessLevel = null;

/**
 * @var array Statrix actions list
 * @see STATRIX_DEFAULT_ACTION
 */
$statrixActions = array(
'overview' => array('caption' => 'Overview', 'method' => 'loadOverview', 'isSubmenu' => true),
'hits' => array('caption' => 'Hits', 'method' => 'loadHits', 'isSubmenu' => true),
'search_terms' => array('caption' => 'Search Terms', 'method' => 'loadSearchTerms', 'isSubmenu' => true),
'spiders' => array('caption' => 'Spiders', 'method' => 'loadSpiders', 'isSubmenu' => true),
'options' => array('caption' => 'Options', 'method' => 'loadOptions', 'isSubmenu' => true, 'accessLevel' => 8),
'whois' => array('caption' => 'Whois', 'method' => 'loadWhois', 'isSubmenu' => false),
'ip2country_import' => array('caption' => 'IP2country import', 'method' => 'loadIp2country', 'isSubmenu' => false, 'accessLevel' => 8)
);

/**
 * @var array contains week days
 */
$weekdays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

/**
 * @var array contains months
 */
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

/**
 * @var int whois error number
 */
$statrix_whois_errno = "";

/**
 * @var string whois error string
 */
$statrix_whois_errstr = "";

/**
 * @var array contains whois servers
*/
$statrix_whois_servers = array("apnic" => "whois.apnic.net",
							"ripe" => "whois.ripe.net", 
							"lacnic" => "whois.lacnic.net",
							"afrinic" => "whois,afrinic.net",
							"arin" => "whois.arin.net");

?>
