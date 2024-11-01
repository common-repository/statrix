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

/**
 * This function returns the OS name.
 *
 * @param string user agent
 * @return string OS name
 * @uses STATRIX_DIR
 */
function getOs($agent) {
	$agent = str_replace(' ', '', $agent);
	$lines = file(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . '/resources/os.stx');

	foreach($lines as $line => $os) {
		list($name, $id) = explode("|", $os);
		if(strpos($agent, $id) === FALSE) {
			continue;
		}
		return $name;
	}
	return null;
}

/**
 * This function returns the browser name.
 *
 * @param string user agent
 * @return string browser name
 * @uses STATRIX_DIR
 */
function getBrowser($agent){
	$agent = str_replace(' ', '', $agent);
	$lines = file(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . '/resources/browser.stx');

	foreach($lines as $line => $browser) {
		list($name, $id) = explode("|", $browser);
		if(strpos($agent, $id) === FALSE) {
			continue;
		}
		return $name;
	}
	return null;
}

/**
 * This function returns the search engine name.
 *
 * @param string user referer
 * @return string search engine name
 * @uses STATRIX_DIR
 */
function getSearchEngine($referer) {
	$lines = file(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . '/resources/searchEngine.stx');

	foreach($lines as $searchEngine) {
		list($name, $url, $queryVar) = explode("|", $searchEngine);
		if(strstr($referer, $url)) {
			return $name;
		}
	}
	return null;
}

/**
 * This function returns the search engine terms.
 *
 * @params string user referer
 * @return string user search terms
 * @uses STATRIX_DIR
 */
function getSearchTerms($referer) {
	$lines = file(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . '/resources/searchEngine.stx');

	foreach($lines as $searchEngine) {
		list($name, $url, $queryVar) = explode("|", $searchEngine);
		if($name == getSearchEngine($referer)) {
			$terms = substr((strstr($referer, "?{$queryVar}") ? strstr($referer, "?{$queryVar}") : strstr($referer, "&{$queryVar}")), strlen($queryVar) + 2) ;
			$terms = urldecode(str_replace(strstr($terms, '&'), '', $terms));
			return $terms;
		}
	}
	return null;
}

/**
 * This function returns the spider name.
 *
 * @param string user agent
 * @return string spider name
 * @uses STATRIX_DIR
 */
function getSpider($agent) {
	$agent = str_replace(' ', '', $agent);
	$lines = file(ABSPATH . PLUGINDIR . '/' . STATRIX_DIR . '/resources/spider.stx');

	foreach($lines as $line => $spider) {
		list($name, $id) = explode("|", $spider);
		if(strpos($agent, $id) === FALSE) {
			continue;
		}
		return $name;
	}
	return null;

}

/**
 * This function returns the rss feed type.
 *
 * @param string user request URI
 * @return string rss feed type
 * @uses STATRIX_DIR
 * @uses getBlogDomain()
 */
function getRssType($uri) {
	$uri = "http://". getBlogDomain() . $uri;

	if($uri == get_bloginfo('rdf_url')) {
		return 'RDF';
	}
	if($uri == get_bloginfo('rss_url')) {
		return 'RSS';
	}
	if($uri == get_bloginfo('rss2_url')) {
		return 'RSS2';
	}
	if($uri == get_bloginfo('atom_url')) {
		return 'ATOM';
	}
	if($uri == get_bloginfo('comments_rss2_url')) {
		return 'COMMENT RSS2';
	}
	if(stristr($uri, 'wp-feed.php')) {
		return 'RSS2';
	}
	if(stristr($uri, '/feed/')) {
		return 'RSS2';
	}
	return null;
}
?>
