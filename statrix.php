<?php
/*
 Plugin Name: Statrix
 Plugin URI: http://wordpress.org/extend/plugins/statrix/
 Description: Generates real-time statistics for your blog. You can browse, filter and export detailed data about visitors, spiders, search terms and engines.
 Version: 1.3
 Author: Iva Koleva, ClearCode Ltd.
 Author URI: http://clearcode.bg/
 */

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

//PHP VERSION CHECK
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    echo 'Your PHP version is ' . PHP_VERSION . ". Statrix requires PHP version 5.0 or greater (tested down to PHP v5.0.3).\n";
    exit;
}

//INCLUDES
require_once("includes/constants.php");
require_once("includes/resourceFunctions.php");
require_once("includes/overview.php");
require_once("includes/hits.php");
require_once("includes/searchTerms.php");
require_once("includes/spiders.php");
require_once("includes/options.php");
require_once("includes/whois.php");
require_once("includes/ip2countryImport.php");

//ACTIONS
add_action('admin_menu', 'addToolMenu');
add_action('admin_head', 'addJavaScript');
get_option('statrix_collect_admin_menu_data') ? add_action('admin_menu', 'collectData') : false;
get_option('statrix_collect_blog_pages_data') ? add_action('wp_head', 'collectData') : false;
if (get_option('statrix_collect_rss_feed_data')) {
    add_action('atom_head', 'collectData');
    add_action('rdf_header', 'collectData');
    add_action('rss_head', 'collectData');
    add_action('rss2_head', 'collectData');
}


/* PREPARE TOOL */
prepareTool();
/* END PREPARE TOOL */


//FUNCTIONS
/**
 * This function prepares the Statrix environment for proper work. (Necessary because the $wpdb->get_var() method.)
 *
 * @uses STATRIX_DEFAULT_ACCESS_LEVEL
 * @uses STATRIX_TABLE
 * @uses $accessLevel
 * @uses statrix_localize()
 */
function prepareTool() {
    global $wpdb;
    global $wp_db_version;
    global $accessLevel;

    //access level
    $accessLevel = get_option('statrix_access_level');
    if (!$accessLevel) {
        $accessLevel = STATRIX_DEFAULT_ACCESS_LEVEL;
        add_option('statrix_access_level', $accessLevel, 'Statrix access level required.', 'yes');
    }
    $accessLevel = ($accessLevel == 'zero' ? 0 : $accessLevel);

    //add default options
    !get_option('statrix_collect_anonymous_visitors_data') ? add_option('statrix_collect_anonymous_visitors_data', 1, 'Collect data about anonymous visitors.', 'yes') : false;
    !get_option('statrix_collect_logged_users_data') ? add_option('statrix_collect_logged_users_data', 1, 'Collect data about logged users.', 'yes') : false;
    !get_option('statrix_collect_spiders_data') ? add_option('statrix_collect_spiders_data', 1, 'Collect data about spiders.', 'yes') : false;
    !get_option('statrix_collect_blog_pages_data') ? add_option('statrix_collect_blog_pages_data', 1, 'Collect data from blog pages.', 'yes') : false;
    !get_option('statrix_collect_rss_feed_data') ? add_option('statrix_collect_rss_feed_data', 1, 'Collect data from RSS feed.', 'yes') : false;
    !get_option('statrix_collect_admin_menu_data') ? add_option('statrix_collect_admin_menu_data', 1, 'Collect data from admin menu.', 'yes') : false;
    !get_option('statrix_excluded_users') ? add_option('statrix_excluded_users', '', 'Excluded users from data collecting.', 'yes') : false;
    !get_option('statrix_excluded_ips') ? add_option('statrix_excluded_ips', '', 'Excluded IPs from data collecting.', 'yes') : false;
    !get_option('statrix_excluded_resources') ? add_option('statrix_excluded_resources', '', 'Excluded resources from data collecting.', 'yes') : false;
    !get_option('statrix_labeled_ips') ? add_option('statrix_labeled_ips', '', 'Labeled IPs.', 'yes') : false;
    !get_option('statrix_delete_history') ? add_option('statrix_delete_history', 'never', 'Delete history.', 'yes') : false;
    !get_option('statrix_delete_history_option') ? add_option('statrix_delete_history_option', 1, 'Delete history option.', 'yes') : false;
    !get_option('statrix_hits_per_page') ? add_option('statrix_hits_per_page', 20, 'Show hits per page.', 'yes') : false;
    !get_option('statrix_search_terms_per_page') ? add_option('statrix_search_terms_per_page', 20, 'Show search terms per page.', 'yes') : false;
    !get_option('statrix_spiders_per_page') ? add_option('statrix_spiders_per_page', 20, 'Show spiders per page.', 'yes') : false;
    !get_option('statrix_locale') ? add_option('statrix_locale', (in_array(get_locale(), getAvailableStatrixLocales()) ? get_locale() : 'en_US'), 'Statrix localization.', 'yes') : false;
    !get_option('statrix_ip2country') ? add_option('statrix_ip2country', 0, 'Statrix IP2country.', 'yes') : false;
    !get_option('statrix_ip2country_initialized') ? add_option('statrix_ip2country_initialized', 0, 'Statrix IP2country initialized.', 'yes') : false;

    //check for table existence
    if ($wpdb->get_var('SHOW TABLES LIKE "' . STATRIX_TABLE . '"') != STATRIX_TABLE) {
        $sql = "CREATE TABLE " . STATRIX_TABLE . " (
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
						timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						ip VARCHAR(39) NOT NULL,
						request_uri TEXT NOT NULL,
						agent TEXT,
						referer TEXT,
						os TEXT,
						browser TEXT,
						rss VARCHAR(12),
						spider TEXT,
						search_engine TEXT,
						search_terms TEXT,
						user_login VARCHAR(60)
						)";

        if ($wp_db_version >= 5540) {
            $page = 'wp-admin/includes/upgrade.php';
        } else {
            $page = 'wp-admin/upgrade-functions.php';
        }
        require_once(ABSPATH . $page);
        dbDelta($sql);
    }

    statrix_localize();
}

/**
 * This function localizes the plugin, using the current locale settings.
 *
 * @return void
 */
function statrix_localize() {
    $pluginDir = basename(dirname(__FILE__));
    $languageDir = ABSPATH . "wp-content/plugins/{$pluginDir}/language/";
    $currentLocale = getCurrentStatrixLocale();
    if (is_file($languageDir . 'statrix-' . $currentLocale . '.mo')) {
        load_textdomain("statrix", $languageDir . 'statrix-' . $currentLocale . '.mo');
    } else {
        die("Language (.mo) file doesn't exist or accessible");
    }
}

/**
 * This function searches the language plugin dir for available locales.
 *
 * @return array
 */
function getAvailableStatrixLocales() {
    $locales = array();
    $matches = array();
    $pluginDir = basename(dirname(__FILE__));
    $languageDir = ABSPATH . "wp-content/plugins/{$pluginDir}/language/";
    $handler = opendir($languageDir);

    while ($file = readdir($handler)) {
        if ($file != "." && $file != "..") {
            $pattern = "/^statrix\-([a-z]{2,}_[A-Z]{2,})\.(mo|txt)$/";
            if (preg_match($pattern, (string)$file, $matches)) {
                array_push($locales, $matches[1]);
            }
        }
    }

    closedir($handler);
    sort($locales);

    return $locales;
}

function getCurrentStatrixLocale() {
    return get_option('statrix_locale');
}

/**
 * This function builds the Statrix plugin menu and submenus.
 *
 * @uses STATRIX_MAIN_FILE
 * @uses $statrixActions
 * @uses $accessLevel
 */
function addToolMenu() {
    global $statrixActions;
    global $accessLevel;

    //add menus
    add_menu_page('Statrix', 'Statrix', $accessLevel, __FILE__, 'loadTool');

    foreach ($statrixActions as $actionName => $actionData) {
        $actionAccessLevel = ($actionData['accessLevel'] ? ($actionData['accessLevel'] == 'zero' ? 0 : $actionData['accessLevel']) : $accessLevel);

        if ($actionData['isSubmenu']) {
            if ($actionName == STATRIX_DEFAULT_ACTION) {
                add_submenu_page(__FILE__, __($actionData['caption'], "statrix"), __($actionData['caption'], "statrix"), $actionAccessLevel, __FILE__, 'loadTool');
            } else {
                add_submenu_page(__FILE__, __($actionData['caption'], "statrix"), __($actionData['caption'], "statrix"), $actionAccessLevel, __FILE__ . '&statrix_action=' . $actionName, 'loadTool');
            }
        }
    }
}

/**
 * This function loads the specific tool, depending on the statrix_action GET parameter.
 *
 * @uses STATRIX_DEFAULT_ACTION
 * @uses $statrixActions
 * @uses getStatrixAction()
 */
function loadTool() {
    global $statrixActions;

    $statrixActionsTmp = $statrixActions; //FIX for the old PHP (do not ask).
    foreach ($statrixActionsTmp as $actionName => $actionData) {
        if ($actionName == getStatrixAction()) {
            $actionData['method']($actionName);
            return;
        }
    }
    $statrixActions[STATRIX_DEFAULT_ACTION]['method']();
}

/**
 * This functon adds javascript files in admin header.
 *
 * @uses STATRIX_DIR
 * @uses STATRIX_DEFAULT_ACTION
 * @uses getStatrixAction()
 */
function addJavaScript() {
    if (getStatrixAction() == STATRIX_DEFAULT_ACTION) {
        echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/libs/mochikit/MochiKit.js'></script>";
        echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/libs/plotkit/Base.js'></script>";
        echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/libs/plotkit/Layout.js'></script>";
        echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/libs/plotkit/Canvas.js'></script>";
        echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/libs/plotkit/SweetCanvas.js'></script>";
    }
    echo "\n<script type='text/javascript' src='" . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . "/resources/statrix.js.php?statrix_action=" . getStatrixAction();
    if (getStatrixAction() == STATRIX_DEFAULT_ACTION && $_GET) {
        foreach ($_GET as $key => $value) {
            echo "&{$key}={$value}";
        }
    }
    echo "'></script>\n";
}

/**
 * This function collects the visitor's data and inserts it in the database.
 *
 * @uses STATRIX_TABLE
 * @uses getRequestUri()
 * @uses isUserExcluded()
 * @uses isIpExcluded()
 * @uses isResourceExcluded()
 * @uses getOs()
 * @uses getBrowser()
 * @uses getRssType()
 * @uses getSpider()
 * @uses getSearchEngine()
 * @uses getSearchTerms()
 * @uses deleteHistory()
 */
function collectData() {
    global $wpdb;
    global $userdata;

    get_currentuserinfo();
    $ip = $_SERVER['REMOTE_ADDR'];
    $requestUri = addslashes($_SERVER['REQUEST_URI']);
    $agent = addslashes((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
    $referer = addslashes((isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));

    if ((int)get_option('statrix_collect_logged_users_data') >= (int)is_user_logged_in() && !isUserExcluded($userdata->ID) && !isIpExcluded($ip) && !isResourceExcluded($requestUri)) {
        $sql = "INSERT INTO " . STATRIX_TABLE . "
		(timestamp, ip, request_uri, agent, referer, os, browser, rss, spider, search_engine, search_terms, user_login)
		VALUES('" . current_time('mysql') . "', '$ip', '$requestUri', '$agent', '$referer', '" .
               getOs($agent) . "', '" . getBrowser($agent) . "', '" . getRssType($requestUri) . "', '" . getSpider($agent) . "', '" .
               getSearchEngine($referer) . "', '" . addslashes(getSearchTerms($referer)) . "', '{$userdata->user_login}') ";

        $wpdb->query($sql);

        if(get_option('statrix_ip2country_initialized')) {
            $lastid = $wpdb->insert_id;
            $result = $wpdb->get_results("SELECT country_code,country_name FROM " . STATRIX_IP2COUNTRY_TABLE . " WHERE INET_ATON('{$ip}') BETWEEN begin_ip_num AND end_ip_num");
            if($result[0]->country_code && $result[0]->country_name) {
                $wpdb->update(STATRIX_TABLE, array('country_code' => $result[0]->country_code,'country_name' => $result[0]->country_name) , array('id' => $lastid), array( '%s', '%s' ), array( '%s' ));
            }
        }
    }

    deleteHistory();
}

/**
 * This function checks if the user is excluded from data collecting.
 *
 * @param int user ID
 * @return bool returns true if the user is excluded from data collecting
 */
function isUserExcluded($userId) {
    $excludedUsers = (get_option('statrix_excluded_users') ? explode(',', trim(get_option('statrix_excluded_users'))) : null);
    if (is_array($excludedUsers)) {
        if (array_search($userId, $excludedUsers) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * This function checks if the IP address is excluded from data collecting.
 *
 * @param string user IP address
 * @return bool return true if the IP is excluded from data collecting
 */
function isIpExcluded($ip) {
    $ip = explode('.', $ip);
    $excludedIps = (get_option('statrix_excluded_ips') ? explode("\n", get_option('statrix_excluded_ips')) : null);

    if (is_array($excludedIps)) {
        foreach ($excludedIps as $excludedIp) {
            $excludedIp = explode('.', trim($excludedIp));
            if (count($ip) == count($excludedIp)) {
                $match = true;
                for ($i = 0; $i < count($ip); $i++) {
                    if ($excludedIp[$i] != '*' && $ip[$i] != $excludedIp[$i]) {
                        $match = false;
                        break;
                    }
                }
            } else {
                $match = false;
            }
            if ($match) {
                return true;
            }
        }
    }
    return false;
}

/**
 * This function checks if the resource is excluded from data collecting.
 *
 * @param string resource
 * @return bool return true if the resource is excluded from data collecting
 */
function isResourceExcluded($resource) {
    $excludedResources = (get_option('statrix_excluded_resources') ? explode("\n", get_option('statrix_excluded_resources')) : null);

    $matches = null;
    if (is_array($excludedResources)) {
        foreach ($excludedResources as $excludedResource) {
            $resourcePattern = '/' . str_replace('*', '.*', str_replace('/', '\/', $excludedResource)) . '/';
            preg_match($resourcePattern, $resource, $matches);
            if ($matches == true) {
                return true;
            }
        }
    }
    return false;
}

/**
 * This function gets the IP address label if set.
 *
 * @param string user IP address
 * @return string return label if the IP label is found
 */
function getIpLabel($ip) {
    $ip = explode('.', $ip);
    $labeledIpsArray = (get_option('statrix_labeled_ips') ? explode("\n", get_option('statrix_labeled_ips')) : null);
    rsort($labeledIpsArray); // pull the exact IP patters first, so they can be used with higher priority
    $labeledIps = array();
    foreach($labeledIpsArray as $labeledIp) {
        $labeledIp = explode(":", $labeledIp);
        $labeledIps[$labeledIp[0]] = $labeledIp[1];
    }

    foreach ($labeledIps as $labeledIp=>$label) {
        $labeledIp = explode('.', trim($labeledIp));
        if (count($ip) == count($labeledIp)) {
            $match = true;
            for ($i = 0; $i < count($ip); $i++) {
                if ($labeledIp[$i] != '*' && $ip[$i] != $labeledIp[$i]) {
                    $match = false;
                    break;
                }
            }
        } else {
            $match = false;
        }
        if ($match) {
            return $label;
        }
    }

    return null;
}

/**
 * This function deletes the history by settings from Options mode.
 *
 * @uses STATRIX_TABLE
 */
function deleteHistory() {
    global $wpdb;
    $deletePeriod = get_option('statrix_delete_history');
    $timestamp = '';

    switch ($deletePeriod) {
        case 'week':
            $timestamp = strtotime("-1 week");
            break;
        case 'month':
            $timestamp = strtotime("-1 month");
            break;
        case 'months':
            $timestamp = strtotime("-6 months");
            break;
        case 'year':
            $timestamp = strtotime("-1 year");
            break;
        default:
            // do nothing
            break;
    }

    if ($timestamp) {
        $timestamp = date("Y-m-d H:i:s", $timestamp);
        $result = $wpdb->query("DELETE FROM " . STATRIX_TABLE . " WHERE timestamp < '{$timestamp}'");
    }
}

/**
 * This function returns the statrix action.
 *
 * @return string Statrix action
 * @uses STATRIX_DEAULT_ACTION
 * @uses $statrixActions
 */
function getStatrixAction() {
    global $statrixActions;

    if (array_key_exists('statrix_action', $_GET)) {
        foreach ($statrixActions as $actionName => $actionData) {
            if ($_GET['statrix_action'] == $actionName) {
                return $actionName;
            }
        }
    }
    return STATRIX_DEFAULT_ACTION;
}

/**
 * This function returns the blog domain from the Settings panel data.
 *
 * @return string blog domain
 */
function getBlogDomain() {
    //untrailingslashit(get_bloginfo('wpurl'))
    $domain = str_replace('http://', '', get_option('home'));
    return str_replace(strstr($domain, '/'), '', $domain);
}

/**
 * This function prints the paging elements
 *
 * @param int count of the items for paging
 * @param int limit of the items for paging
 * @param array additional arguments for building the links
 * @uses STATRIX_DIR
 */
function printPages($itemsCount, $itemsLimit, $additionalArgs = array()) {
    $pages = ceil($itemsCount / $itemsLimit);

    $_GET['statrix_page'] = ((int)$_GET['statrix_page'] ? (int)$_GET['statrix_page'] : 1);

    echo "<table align=\"left\">\n<tr nowrap=\"nowrap\">\n<td nowrap=\"nowrap\">\n" . __("Pages", "statrix") . ":&nbsp;&nbsp;";

    if ($_GET['statrix_page'] - 1 > 0) { //left arrow
        echo '<a href="?';
        $i = 0;
        foreach ($_GET as $key => $value) {
            if ($key != 'statrix_page') {
                $value = urlencode($value);
                print ($i != 0 ? '&amp;' : '') . "{$key}={$value}";
                $i++;
            }
        }
        foreach ($additionalArgs as $key => $value) {
            echo "&amp;{$key}={$value}";
        }
        echo "&amp;statrix_page=" . ($_GET['statrix_page'] - 1) . "\">&larr;</a>&nbsp;&nbsp;";
    }

    print "<b>" . ($_GET['statrix_page'] ? $_GET['statrix_page'] : 1) . "</b> " . __("from", "statrix") . " " . ($pages < 1 ? 1 : $pages) . "&nbsp;&nbsp;";

    if ($_GET['statrix_page'] + 1 <= $pages) { //right arrow
        echo '<a href="?';
        $i = 0;
        foreach ($_GET as $key => $value) {
            if ($key != 'statrix_page') {
                $value = urlencode($value);
                print ($i != 0 ? '&amp;' : '') . "{$key}={$value}";
                $i++;
            }
        }
        foreach ($additionalArgs as $key => $value) {
            $value = urlencode($value);
            echo "&amp;{$key}={$value}";
        }
        echo "&amp;statrix_page=" . ($_GET['statrix_page'] + 1) . "\">&rarr;</a>&nbsp;&nbsp;";
    }

    echo '
</td>
<td nowrap="nowrap">
<form method="get"><input name="page" id="page" type="hidden"
	value="' . STATRIX_DIR . '/">';

    foreach ($_GET as $key => $value) {
        if ($key != 'statrix_page') {
            echo "<input name=\"{$key}\" id=\"{$key}\" type=\"hidden\" value=\"{$value}\">";
        }
    }
    foreach ($additionalArgs as $key => $value) {
        echo "<input name=\"{$key}\" id=\"{$key}\" type=\"hidden\" value=\"{$value}\">";
    }

    echo '
	<input name="statrix_page" id="statrix_page" type="text"
	size="' . strlen($pages) . '"
	maxlength="' . strlen($pages) . '"> <input
	class="button-secondary" type="submit" value="' . __("Go", "statrix") . '"></form>
</td>
</tr>
</table>';
}


/**
 * This function prints date select fields.
 *
 * @param string HTML day element ID
 * @param string HTML month element ID
 * @param string HTML year element ID
 * @return string built date select fields
 * @uses STATRIX_TABLE
 * @uses $months
 */
function printDateFields($dayFieldName, $monthFieldName, $yearFieldName, $isDisabled = false) {
    global $wpdb;
    global $months;

    $dateFields = '';
    //days
    $dateFields .= "<select name=\"{$dayFieldName}\" id=\"{$dayFieldName}\"" . ($isDisabled ? " disabled=\"disabled\"" : null) . ">
		     <option value=\"\">- " . __("Day", "statrix") . " -</option>\n";
    for ($i = 1; $i <= 31; $i++) {
        $dateFields .= sprintf("<option value=\"%02d\">%02d</option>\n", $i, $i);
    }
    $dateFields .= "</select>";

    //months
    $dateFields .= ".<select name=\"{$monthFieldName}\" id=\"{$monthFieldName}\"" . ($isDisabled ? " disabled=\"disabled\"" : null) . ">
		      <option value=\"\">- " . __("Month", "statrix") . " -</option>\n";
    foreach ($months as $key => $value) {
        $dateFields .= sprintf("<option value=\"%02d\">%s</option>\n", $key + 1, __($value, "statrix"));
    }
    $dateFields .= "</select>";

    //years
    $dateFields .= ".<select name=\"{$yearFieldName}\" id=\"{$yearFieldName}\"" . ($isDisabled ? " disabled=\"disabled\"" : null) . ">
                      <option value=\"\">- " . __("Year", "statrix") . " -</option>\n";
    $results = $wpdb->query("SELECT DISTINCT YEAR(timestamp) AS year FROM " . STATRIX_TABLE . " ORDER BY YEAR(timestamp) DESC");
    foreach ($wpdb->last_result as $row) {
        $dateFields .= "<option value=\"{$row->year}\">{$row->year}</option>\n";
    }
    $dateFields .= "</select>\n";

    echo $dateFields;
}

/**
 * This function prints time select fields.
 *
 * @param string HTML hour element ID
 * @param string HTML minutes element ID
 * @return string built time select fields
 */
function printTimeFields($hourField, $minutesField, $isDisabled = false) {
    $timeFields = '';
    //hours
    $timeFields .= "<select name=\"{$hourField}\" id=\"{$hourField}\"" . ($isDisabled ? " disabled=\"disabled\"" : null) . ">
			     <option value=\"\">- " . __("Hour", "statrix") . " -</option>\n";
    for ($i = 0; $i <= 23; $i++) {
        $timeFields .= sprintf("<option value=\"%02d\">%02d</option>\n", $i, $i);
    }
    $timeFields .= "</select>";

    //minutes
    $timeFields .= ":<select name=\"{$minutesField}\" id=\"{$minutesField}\"" . ($isDisabled ? " disabled=\"disabled\"" : null) . ">
                     <option value=\"\">- " . __("Minutes", "statrix") . " -</option>\n";
    for ($i = 0; $i <= 60; $i++) {
        $timeFields .= sprintf("<option value=\"%02d\">%02d</option>\n", $i, $i);
    }
    $timeFields .= "</select>\n";

    echo $timeFields;
}

/**
 * This function returns the sort criteria.
 *
 * @param string Statrix action
 * @return string sort criteria
 */
function getSort($statrixAction) {
    $sort = '';
    if (array_key_exists('statrix_sort', $_GET)) {
        switch ($_GET['statrix_sort']) {
            case 'date':
                $sort = 'timestamp';
                break;
            case 'ip':
                $sort = 'ip';
                break;
            case 'country_name':
                $sort = 'country_name';
                break;
            case 'username':
                $sort = ($statrixAction == 'hits' ? 'user_login' : '');
                break;
            case 'page':
                $sort = 'request_uri';
                break;
            case 'referer':
                $sort = ($statrixAction == 'hits' ? 'referer' : '');
                break;
            case 'os':
                $sort = ($statrixAction == 'hits' || $statrixAction == 'search_terms' ? 'os' : '');
                break;
            case 'browser':
                $sort = ($statrixAction == 'hits' || $statrixAction == 'search_terms' ? 'browser' : '');
                break;
            case 'rss':
                $sort = ($statrixAction == 'hits' ? 'rss' : '');
                break;
            case 'spider':
                $sort = ($statrixAction == 'spiders' ? 'spider' : '');
                break;
            case 'agent':
                $sort = ($statrixAction == 'spiders' ? 'agent' : '');
                break;
            case 'search_engine':
                $sort = ($statrixAction == 'search_terms' ? 'search_engine' : '');
                break;
            case 'search_terms':
                $sort = ($statrixAction == 'search_terms' ? 'search_terms' : '');
                break;
            default:
                $sort = 'timestamp';
                break;
        }
    }
    $sort = ($sort ? $sort : 'timestamp');
    return $sort;
}

/**
 * This function returns the order criteria.
 *
 * @param string Statrix action
 * @param string sort criteria
 * @return string order criteria
 */
function getOrder($statrixAction, $sort) {
    if (array_key_exists('statrix_order', $_GET)) {
        switch ($_GET['statrix_order']) {
            case 'asc' :
                $order = 'ASC';
                break;
            case 'desc' :
                $order = 'DESC';
                break;
            default:
                $order = ($sort == 'timestamp' ? 'DESC' : 'ASC');
                break;
        }
    } else {
        $order = ($sort == 'timestamp' ? 'DESC' : ' ASC');
    }
    return $order;
}

/**
 * This function returns the offset criteria.
 *
 * @param int items for page
 * @return int offset criteria
 */
function getOffset($limit) {
    if (array_key_exists('statrix_page', $_GET)) {
        $page = ((int)$_GET['statrix_page'] ? (int)$_GET['statrix_page'] : 1);
    } else {
        $page = 1;
    }
    $offset = ($page - 1) * $limit;
    return $offset;
}

/**
 * This function builds the sort link
 *
 * @param string Statrix action
 * @param string sort criteria
 * @param string order criteria
 * @uses STATRIX_DIR
 */
function buildSortLink($statrixAction, $statrixSort, $statrixOrder) {
    print '<a href="?';
    $i = 0;
    foreach ($_GET as $key => $value) {
        if ($key != 'statrix_action' && $key != 'statrix_sort' && $key != 'statrix_order') {
            print ($i != 0 ? '&amp;' : '') . "{$key}={$value}";
            $i++;
        }
    }
    print "&amp;statrix_action={$statrixAction}&amp;statrix_sort={$statrixSort}&amp;statrix_order={$statrixOrder}\">";
    print '<img alt="" src="' . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR .
          '/resources/' . ($statrixOrder == 'asc' ? 'down_arrow' : 'up_arrow') . '.png" /></a>';
}

/**
 * This function gets the whois information about the specified domain or IP address.
 *
 * @param string IP address
 * @return string whois response
 * @uses STATRIX_WHOIS_TIMEOUT
 * @uses $statrix_whois_servers
 * @uses $statrix_whois_errno
 * @uses $statrix_whois_errstr
 * @uses getWhoisServer()
 */
function statrixWhois($query) {
    global $statrix_whois_servers;
    global $statrix_whois_errno, $statrix_whois_errstr;

    $server = getWhoisServer($query);
    if (!$server || $server == 'UNALLOCATED') {
        return "Unknown AS number or IP network.\n";
    }
    foreach ($statrix_whois_servers as $name => $address) {
        if ($server == $name) {
            $server = $address;
            break;
        }
    }

    $f = fsockopen($server, 43, $statrix_whois_errno, $statrix_whois_errstr, STATRIX_WHOIS_TIMEOUT);
    if (!$f) {
        return false;
    }
    fwrite($f, $query . "\r\n");

    $response = '';
    while (!feof($f)) {
        $response .= fgets($f, 1024);
    }

    fclose($f);
    return $response;
}

/**
 * This function determines the whois server.
 *
 * @param string IP address
 * @return string whois server or null if none has been found
 * @uses STATRIX_DIR
 * @todo IPv6 implementation (resources/ip6List.stx)
 */
function getWhoisServer($ip) {
    $fp = fopen(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . "/resources/ipList.stx", "r");

    while (!feof($fp)) {
        $line = trim(fgets($fp));

        if (substr($line, 0, 1) == '#') {
            continue;
        } else {
            $line = trim(str_replace(strstr($line, '#'), '', $line));
        }

        if (strlen($line) == 0) {
            continue;
        }

        $line = str_replace("\t", ' ', $line);
        while (strpos($line, '  ') !== false) {
            $line = str_replace('  ', ' ', $line);
        }

        $lineTokens = explode(' ', $line);
        $networkTokens = explode("/", $lineTokens[0]);

        $netmask = pow(2, 32) - pow(2, 32 - $networkTokens[1]);
        $network = ip2long($networkTokens[0]);
        $address = ip2long($ip);

        if (($network & $netmask) == ($address & $netmask)) {
            return $lineTokens[1];
        }
    }
    return null;
}

/**
 * This function prints the export button.
 *
 * @param string output filename
 * @param Exporter exporter
 * @uses STATRIX_DIR
 */
function printExport($filename, Exporter $exporter) {
    $export = '<form action="' . get_option('home') . "/" . PLUGINDIR . "/" . STATRIX_DIR . '/export.php" method="post">
	<table align="right">
	<tr><td align="right"><input class="button-secondary" type="submit" value="' . __("Export", "statrix") . '"></td></tr>
	</table>';

    $export .= '<input name="filename" id="filename" type="hidden" value="' . $filename . '">
	<input name="exporter" id="exporter" type="hidden" value="' . urlencode(serialize($exporter)) . '">
	</form><br/><br/>';

    echo $export;
}
?>
