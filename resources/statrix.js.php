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

require_once('../../../../wp-config.php');
require_once('../../../../wp-includes/wp-db.php');
require_once('../includes/constants.php');

if(array_key_exists('statrix_action', $_GET)) {
	$statrix_action = ($_GET['statrix_action'] ? $_GET['statrix_action'] : STATRIX_DEFAULT_ACTION);
} else {
	$statrix_action = STATRIX_DEFAULT_ACTION;
}

$match = false;
foreach($statrixActions as $actionName => $actionData) {
	if($statrix_action == $actionName) {
		$match = true;
	}
}
$statrix_action = ($match ? $statrix_action : STATRIX_DEFAULT_ACTION);

switch($statrix_action) {
	case 'overview':
		printHitsLine();
		printPagesBar();
		printBrowsersPie();
		printOsPie();
		break;
}

//DINAMIC FUNCTIONS
/**
 * This function prints the hits for time period, specified (or not) by filter.
 *
 * @uses STATRIX_TABLE
 * @uses $weekdays
 * @uses drawChart()
 */
function printHitsLine() {
	global $wpdb;
	global $weekdays;

	if(($_GET['period'] != 'today' && $_GET['period'] != 'lastWeek' && $_GET['period'] != 'lastMonth' && $_GET['period'] != 'lastYear' && $_GET['period'] != 'all' && $_GET['period'] != 'custom') ||
	($_GET['period'] == 'custom' && (!$_GET['dayFrom'] || !$_GET['monthFrom'] || !$_GET['yearFrom'] || !$_GET['hourFrom'] || !$_GET['minutesFrom'] ||
	!$_GET['dayTo'] || !$_GET['monthTo'] || !$_GET['yearTo'] || !$_GET['hourTo'] || !$_GET['minutesTo']))) {
		//default settings
		$_GET['period'] = 'lastWeek';
	}
	
	switch($_GET['period']) {
		case 'today':
			$timestampFrom = strtotime("today 0 hours 0 minutes 0 seconds", current_time('timestamp'));
			$timestampTo = strtotime("today 23 hours 59 minutes 59 seconds", current_time('timestamp'));
			$dateFrom = date("Y-m-d H:i:s", $timestampFrom);
			$dateTo = date("Y-m-d H:i:s", $timestampTo);
			break;
		case 'lastWeek':
			$timestampFrom = strtotime("-6 days", current_time('timestamp'));
			$timestampTo = strtotime("today 23 hours 59 minutes 59 seconds", current_time('timestamp'));
			$dateFrom = date("Y-m-d 00:00:00", $timestampFrom);
			$timestampFrom = strtotime($dateFrom, current_time('timestamp'));
			$dateTo = date("Y-m-d H:i:s", $timestampTo);
			break;
		case 'lastMonth':
			$timestampFrom = strtotime("-29 days", current_time('timestamp'));
			$timestampTo = strtotime("today 23 hours 59 minutes 59 seconds", current_time('timestamp'));
			$dateFrom = date("Y-m-d H:i:s", $timestampFrom);
			$timestampFrom = strtotime($dateFrom, current_time('timestamp'));
			$dateTo = date("Y-m-d H:i:s", $timestampTo);
			break;
		case 'lastYear':
			$timestampFrom = strtotime("-355 days", current_time('timestamp'));
			$timestampTo = strtotime("today 23 hours 59 minutes 59 seconds", current_time('timestamp'));
			$dateFrom = date("Y-m-d H:i:s", $timestampFrom);
			$timestampFrom = strtotime($dateFrom, current_time('timestamp'));
			$dateTo = date("Y-m-d H:i:s", $timestampTo);
			break;
		case 'all':
			$timestampFrom = 0;
			$timestampTo = strtotime("today 23 hours 59 minutes 59 seconds", current_time('timestamp')); // replace "now" because of possible timezone shifts
			$dateFrom = date("Y-m-d H:i:s", $timestampFrom);
			$dateTo = date("Y-m-d H:i:s", $timestampTo);
			break;
		case 'custom':
			$dateFrom = addslashes("{$_GET[yearFrom]}-{$_GET[monthFrom]}-{$_GET[dayFrom]} {$_GET[hourFrom]}:{$_GET[minutesFrom]}:00");
			$dateTo = addslashes("{$_GET[yearTo]}-{$_GET[monthTo]}-{$_GET[dayTo]} {$_GET[hourTo]}:{$_GET[minutesTo]}:59");
			$timestampFrom = strtotime($dateFrom, current_time('timestamp'));
			$timestampTo = strtotime($dateTo, current_time('timestamp'));
			break;
	}

	$difference = $timestampTo - $timestampFrom;
	if($difference < 0) {
		$tmp = $timestampFrom;
		$timestampFrom = $timestampTo;
		$timestampTo = $tmp;
		$tmp = $dateFrom;
		$dateFrom = $dateTo;
		$dateTo = $tmp;
		$difference = $timestampTo - $timestampFrom;
	}

	$criteria = array();
	($_GET['exclude_spiders'] == 1) ? $criteria[] = "spider = ''" : false;
	($_GET['exclude_rss_feeds'] == 1) ? $criteria[] = "rss = ''" : false;

	//date period checking
	$timePeriod = '';
	$innerSelectItems = array();
	if($difference <= 60*60*24) { //day - hours
		$timePeriod = 'aDay';
		$innerSelectItems[] = 'HOUR(timestamp) AS hour';
		$innerSelectItems[] = 'COUNT(timestamp) AS count';
		$criteria[] = "timestamp BETWEEN '{$dateFrom}' AND '{$dateTo}'";
		$innerGroupBy = 'HOUR(timestamp)';
		$innerOrderBy = 'timestamp DESC';
		$innerLimit = 24;
		$orderBy = 'hour ASC';
	} elseif($difference <= 60*60*24*14) {//2 weeks - days
		$timePeriod = 'twoWeeks';
		$innerSelectItems[] = 'DAY(timestamp) AS day';
		$innerSelectItems[] = 'WEEKDAY(timestamp) AS weekday';
		$innerSelectItems[] = 'COUNT(timestamp) AS count';
		$innerSelectItems[] = 'timestamp';
		$criteria[] = "timestamp BETWEEN '{$dateFrom}' AND '{$dateTo}'";
		$innerGroupBy = 'DAY(timestamp)';
		$innerOrderBy = 'timestamp DESC';
		$innerLimit = 14;
		$orderBy = 'timestamp ASC';
	} elseif($difference <= 60*60*24*31*3) { //3 months - weeks
		$timePeriod = 'threeMonths';
		$innerSelectItems[] = 'WEEK(timestamp,1) AS week';
		$innerSelectItems[] = 'COUNT(timestamp) AS count';
		$criteria[] = "timestamp BETWEEN '{$dateFrom}' AND '{$dateTo}'";
		$innerGroupBy = 'WEEK(timestamp,1)';
		$innerOrderBy = 'timestamp DESC';
		$innerLimit = 12;
		$orderBy = 'week ASC';
	} elseif($difference <= 60*60*24*365) { //1 year - months
		$timePeriod = 'aYear';
		$innerSelectItems[] = 'MONTHNAME(timestamp) AS month';
		$innerSelectItems[] = 'COUNT(timestamp) AS count';
		$innerSelectItems[] = 'timestamp';
		$criteria[] = "timestamp BETWEEN '{$dateFrom}' AND '{$dateTo}'";
		$innerGroupBy = 'MONTHNAME(timestamp)';
		$innerOrderBy = 'timestamp DESC';
		$innerLimit = 12;
		$orderBy = 'timestamp ASC';
	} else { //n years - years
		$timePeriod = 'nYears';
		$innerSelectItems[] = 'YEAR(timestamp) AS year';
		$innerSelectItems[] = 'COUNT(timestamp) AS count';
		$criteria[] = "timestamp BETWEEN '{$dateFrom}' AND '{$dateTo}'";
		$innerGroupBy = 'YEAR(timestamp)';
		$innerOrderBy = 'timestamp DESC';
		$orderBy = 'year ASC';
	}

	//writing filter criterias
	echo "window.onload = function() {
    document.getElementById('hitsFilterMessage').innerHTML = '<b>" . __("From", "statrix") . ":</b> {$dateFrom}'
		+ '&nbsp;&nbsp;&nbsp;<b>" . __("To", "statrix") . ":</b> {$dateTo}'
		+'";
	($_GET['exclude_spiders'] == 1) ? print '&nbsp;&nbsp;&nbsp;' . __("Spiders excluded", "statrix") . ';' : false;
	($_GET['exclude_rss_feeds'] == 1) ? print '&nbsp;&nbsp;&nbsp;' . __("RSS feeds excluded", "statrix") . ';' : false;
	echo  "';
}\n\n";	


	$criteria = ($criteria ? " WHERE " . implode($criteria, ' AND ') : "");
	$innerSelectItems = ' SELECT ' . implode($innerSelectItems, ',');
	$innerGroupBy = " GROUP BY {$innerGroupBy} ";
	$innerOrderBy = " ORDER BY {$innerOrderBy} ";
	$innerLimit = ($innerLimit ? " LIMIT {$innerLimit} " : '');
	$orderBy = " ORDER BY {$orderBy} ";

	$sql = "SELECT * FROM ({$innerSelectItems} FROM " . STATRIX_TABLE . " {$criteria} {$innerGroupBy} {$innerOrderBy} {$innerLimit}) a {$orderBy}";
	//$sql1 = "SELECT * FROM ({$innerSelectItems} FROM " . STATRIX_TABLE . ($criteria ? " $criteria AND rss != '' " : " WHERE rss != '' ") . " {$innerGroupBy} {$innerOrderBy} {$innerLimit}) a {$orderBy}";
	//$sql2 = "SELECT * FROM ({$innerSelectItems} FROM " . STATRIX_TABLE . ($criteria ? " $criteria AND rss = '' AND spider = '' " : " WHERE rss = '' AND spider = '' ") . " {$innerGroupBy} {$innerOrderBy} {$innerLimit}) a {$orderBy}";
	//$sql3 = "SELECT * FROM ({$innerSelectItems} FROM " . STATRIX_TABLE . ($criteria ? " $criteria AND spider != '' " : " WHERE spider != '' ") . " {$innerGroupBy} {$innerOrderBy} {$innerLimit}) a {$orderBy}";
	//echo $sql;
	$results = $wpdb->query($sql);

	$datasets = array();
	$labels = array();
	foreach($wpdb->last_result as $row) {
		$datasets[0][] = $row->count;

		switch($timePeriod) {
			case 'aDay': $labels[0][] = "{$row->hour}" . __("h", "statrix"); break;
			case 'twoWeeks': $labels[0][] = "{$row->day} " . __($weekdays[$row->weekday], "statrix"); break;
			case 'threeMonths': $labels[0][] = __("Week", "statrix") . " {$row->week}"; break;
			case 'aYear': $labels[0][] = __("{$row->month}", "statrix"); break;
			case 'nYears': $labels[0][] = "{$row->year}"; break;
			default: $labels[0][] = "{$row->day} {$row->month}"; break;
		}
	}
	drawChart('hits', 'line', $datasets, $labels);
}

/**
 * This function prints the top fifteen pages.
 *
 * @uses STATRIX_TABLE
 * @uses getBlogDomain()
 * @uses drawChart()
 */
function printPagesBar() {
	global $wpdb;

	$results = $wpdb->query("SELECT COUNT(request_uri) AS count,request_uri FROM " . STATRIX_TABLE .
	" WHERE request_uri NOT IN ('" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rdf_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rss_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rss2_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('atom_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('comments_rss2_url')) .
	"') AND request_uri NOT LIKE '%wp-feed.php%'
                              AND request_uri NOT LIKE '%/feed/%'
                              GROUP BY request_uri ORDER BY COUNT(request_uri) DESC LIMIT 15");

	$datasets = array();
	$labels = array();
	$i = 1;
	foreach($wpdb->last_result as $row) {
		$datasets[0][] = $row->count;
		$labels[0][] = $i; //$row->request_uri;
		$i++;
	}
	drawChart('pages', 'bar', $datasets, $labels);
}

/**
 * This function prints the browsers pie chart.
 *
 * @uses STATRIX_TABLE
 * @uses drawChart()
 */
function printBrowsersPie() {
	global $wpdb;

	$results = $wpdb->query("SELECT COUNT(browser) AS count,browser FROM " . STATRIX_TABLE . " WHERE browser != '' GROUP BY browser ORDER BY count DESC");

	$datasets = array();
	$labels = array();
	$i = 1;
	foreach($wpdb->last_result as $row) {
		$datasets[0][] = $row->count;
		$labels[0][] = $i; //$row->browser;
		$i++;
	}
	drawChart('browsers', 'pie', $datasets, $labels);
}

/**
 * This function prints the os pie chart.
 *
 * @uses STATRIX_TABLE
 * @uses drawChart()
 */
function printOsPie() {
	global $wpdb;

	$results = $wpdb->query("SELECT COUNT(os) AS count,os FROM " . STATRIX_TABLE . " WHERE os != '' GROUP BY os ORDER BY count DESC");

	$datasets = array();
	$labels = array();
	$i = 1;
	foreach($wpdb->last_result as $row) {
		$datasets[0][] = $row->count;
		$labels[0][] = $i; //$row->os;
		$i++;
	}
	drawChart('os', 'pie', $datasets, $labels);
}

/**
 * This functions draws a chart.
 *
 * @param string HTML element ID
 * @param string chart type (bar, line, pie)
 * @param array chart's datasets
 * @param array chart's labels
 */
function drawChart($element, $type, $datasets, $labels) {
	if(($type != 'bar' && $type != 'line' && $type != 'pie') || !is_array($datasets) || !is_array($labels)) {
		return false;
	}
	(!$datasets[0]) ? $datasets[0][] = 0 : false;
	(!$labels[0]) ? $labels[0][] = 0 : false;

	echo "\n//$element element chart";
	foreach($labels as $num => $label) {
		echo "\nvar {$element}{$num}_options = {\n";
		if($type == 'pie') {
			echo "\"pieRadius\": 0.4,";
		} elseif($type == 'bar') {
			echo "\"padding\": {left: 45, right: 0, top: 10, bottom: 15},";
		} elseif($type == 'line') {
			echo "\"padding\": {left: 45, right: 40, top: 10, bottom: 10},";
		}
		echo "\"IECanvasHTC\": \"../lib/plotkit/iecanvas.htc\",";
		echo "\"xTicks\": [";
		$i = 0;
		foreach($label as $key => $value) {
			if($i) { echo ", "; }
			echo "{v:{$key}, label:\"{$value}\"}";
			$i++;
		}
		echo "]\n};";
	}

	echo "\nfunction draw" . ucfirst($element) . "() {
    var layout = new PlotKit.Layout(\"{$type}\", {$element}{$num}_options);\n";

	foreach($datasets as $num => $dataset) {
		echo "    layout.addDataset(\"{$element}{$num}\", [";
		$i = 0;
		foreach($dataset as $key => $value) {
			if($i) { echo ", "; };
			echo "[$key, $value]";
			$i++;
		}
		echo "]);\n";
	}

	echo "
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement(\"{$element}\");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, {$element}{$num}_options);
    plotter.render();
}	
MochiKit.DOM.addLoadEvent(draw" . ucfirst($element) . ");\n\n";
}

?>

//STATIC FUNCTIONS 
function submitForm(formId) {
	document.getElementById(formId).submit(); 
} 

function toggleStatusOf(elementIdArray, disabled) { 
	for (var i in elementIdArray) { 
		document.getElementById(elementIdArray[i]).disabled = disabled; 
	} 
}

function uncheckBoxes(elementIdArray) {
    for (var i in elementIdArray) {
		document.getElementById(elementIdArray[i]).checked = false;
	}
}

function confirmAction(message) {
    return confirm(message);
}

function toggleBoxesStartingWith(startString, checked) {
    var inputs = document.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].name.indexOf(startString) == 0) {
            inputs[i].checked = checked;
        }
    }
}

function moveSelectedOptions(moveFromSelect, moveToSelect) {
	var fromSelect = document.getElementById(moveFromSelect);
	var toSelect = document.getElementById(moveToSelect);
	
	for(i = 0; i < fromSelect.length; i++) {
		if(fromSelect.options[i].selected) {
			var option = document.createElement('option');
			option.text = fromSelect.options[i].text;
			option.value = fromSelect.options[i].value;
		
			try {
   				toSelect.add(option, null); // standards compliant; doesn't work in IE
  			}
  			catch(ex) {
    			toSelect.add(option); // IE only
  			}
			fromSelect.remove(i);
			i--;
		}
	}
	sortOptions(fromSelect);
	sortOptions(toSelect);
}

function compareOptionText(a,b) {
	return a.text != b.text ? a.text < b.text ? -1 : 1 : 0;
}

function sortOptions(list) {
	var items = list.options.length;
	var tmpArray = new Array(items);
	
	for (i = 0; i < items; i++ ) {
		tmpArray[i] = new Option(list.options[i].text, list.options[i].value);
	}
	tmpArray.sort(compareOptionText);
	for (i = 0; i < items; i++ ) {
		list.options[i] = new Option(tmpArray[i].text, tmpArray[i].value);
	}
}

function selectOptions(list) {
	var select = document.getElementById(list);
	for(i = 0; i < select.length; i++) {
		select.options[i].selected = true;
	}
}