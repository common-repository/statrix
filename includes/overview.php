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
 * This function loads the Overview mode.
 *
 * @uses STATRIX_DIR
 * @uses STATRIX_MAIN_FILE
 * @uses STATRIX_TABLE
 * @uses STATRIX_DEFAULT_ACTION
 * @uses $statrixActions
 * @uses getDataFields()
 * @uses getTimeFields()
 * @uses getBlogDomain()
 */
function loadOverview() {
	global $statrixActions;
	global $wpdb;

	?>
<div class="wrap">
<h2><?php _e($statrixActions[STATRIX_DEFAULT_ACTION]['caption'],'statrix'); ?></h2>

<br />
<div id="poststuff" class="dlm">
<div class="postbox close-me dlmbox">
<h3><?php _e('Filter', 'statrix'); ?></h3>
<div class="inside">

<form name="filterHits" id="filterHits" method="get"><?php 
echo '<input name="page" id="page" type="hidden" value="' . STATRIX_DIR . '/' . STATRIX_MAIN_FILE . '">
	<input name="statrix_action" id="statrix_action" type="hidden" value="' . STATRIX_DEFAULT_ACTION . '">
	';
?>

<table>
	<tr>
		<td><strong><?php _e("Today", "statrix"); ?></strong></td>
		<td colspan="2"><input name="period" id="period" type="radio"
			value="today"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], true);"></td>
	</tr>
	<tr>
		<td><strong><?php echo sprintf(__("Last %s days", "statrix"), 7); ?></strong></td>
		<td colspan="2"><input name="period" id="period" type="radio"
			value="lastWeek" checked="checked"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], true);"></td>
	</tr>
	<tr>
		<td><strong><?php echo sprintf(__("Last %s days", "statrix"), 30); ?></strong></td>
		<td colspan="2"><input name="period" id="period" type="radio"
			value="lastMonth"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], true);"></td>
	</tr>
	<tr>
		<td><strong><?php echo sprintf(__("Last %s days", "statrix"), 365); ?></strong></td>
		<td colspan="2"><input name="period" id="period" type="radio"
			value="lastYear"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], true);"></td>
	</tr>
	<tr>
		<td><strong><?php _e("All", "statrix"); ?></strong></td>
		<td colspan="2"><input name="period" id="period" type="radio"
			value="all"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], true);"></td>
	</tr>
	<tr>
		<td valign="top"><strong><?php _e("Custom", "statrix"); ?></strong></td>
		<td valign="top"><input name="period" id="period" type="radio"
			value="custom"
			onChange="toggleStatusOf(['dayFrom','monthFrom','yearFrom','hourFrom','minutesFrom','dayTo','monthTo','yearTo','hourTo','minutesTo'], false);"></td>
		<td>
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td valign="top">&nbsp;&nbsp;<strong>*<?php _e('From', 'statrix'); ?>:</strong></td>
				<td valign="top"><?php printDateFields("dayFrom", "monthFrom", "yearFrom", true); ?>&nbsp;&nbsp;&nbsp;<?php printTimeFields("hourFrom", "minutesFrom", true); ?></td>
			</tr>
			<tr>
				<td valign="top">&nbsp;&nbsp;<strong>*<?php _e('To', 'statrix'); ?>:</strong></td>
				<td valign="top"><?php printDateFields("dayTo", "monthTo", "yearTo", true); ?>&nbsp;&nbsp;&nbsp;<?php printTimeFields("hourTo", "minutesTo", true); ?></td>
			</tr>
			<tr>
				<td colspan="2" align="right"><i>*<?php _e("Required fields (if available).", "statrix"); ?></i></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" align="right"><?php _e("Exclude spiders", "statrix"); ?> <input
			name="exclude_spiders" id="exclude_spiders" type="checkbox" value="1" /></td>
	</tr>
	<tr>
		<td colspan="3" align="right"><?php _e("Exclude RSS feeds", "statrix"); ?> <input
			name="exclude_rss_feeds" id="exclude_rss_feeds" type="checkbox"
			value="1" /></td>
	</tr>
</table>
<input class="button-secondary" type="submit" value="<?php _e("Do filter", "statrix"); ?>" /></form>

</div>
<script type="text/javascript">
		<!--
		jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
		//jQuery('.togbox').click( function() { jQuery(jQuery(this).parent().parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox.close-me').each(function(){
			jQuery(this).addClass("closed");
		});
		//-->
</script></div>
</div>

<br />
<div id="hitsFilterMessage">&nbsp;</div>
<br />
<div><canvas id="hits" height="350" width="1000"></canvas></div>

<br />
<br />
<br />
<br />
<h2><?php _e('Other Statistics','statrix'); ?></h2>
<br />
<table class="widefat">
	<thead>
		<tr>
			<th scope="col" nowrap="nowrap">No.</th>
			<th scope="col"><?php _e("Top page", "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Visits", "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Chart", "statrix"); ?></th>
		</tr>
	</thead>
	<?php
	//$results = $wpdb->query("SELECT COUNT(request_uri) AS count,request_uri FROM " . STATRIX_TABLE . " GROUP BY request_uri ORDER BY COUNT(request_uri) DESC LIMIT 15");
	$results = $wpdb->query("SELECT COUNT(request_uri) AS count,request_uri FROM " . STATRIX_TABLE .
	" WHERE request_uri NOT IN ('" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rdf_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rss_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('rss2_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('atom_url')) .
	"', '" . str_replace("http://" . getBlogDomain(), '',  get_bloginfo('comments_rss2_url')) .
	"') AND request_uri NOT LIKE '%wp-feed.php%'
                              AND request_uri NOT LIKE '%/feed/%'
                              GROUP BY request_uri ORDER BY COUNT(request_uri) DESC LIMIT 15");

	$i = 0;
	foreach($wpdb->last_result as $row) {
		echo "<tr>\n";
		echo "<td>" . ($i + 1) . "</td>";
		echo '<td width="50%"><a href="http://' . getBlogDomain() . stripslashes($row->request_uri) . '">' . stripslashes($row->request_uri) . "</a></td>\n";
		echo "<td eidth=\"50%\">{$row->count}</td>\n";
		if(!$i) {
			echo '<td valign="top" style="padding-right: 0px;" rowspan="' . count($wpdb->last_result) . '"><div><canvas id="pages" height="500" width="500"></canvas></div></td>' . "\n";
		}
		echo "</tr>\n";
		$i++;
	}

	?>
</table>

<br />
<table class="widefat">
	<thead>
		<tr>
			<th scope="col" nowrap="nowrap">No.</th>
			<th scope="col" nowrap="nowrap"><?php _e("Browser" , "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Visits" , "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Chart" , "statrix"); ?></th>
		</tr>
	</thead>
	<?php
	$results = $wpdb->query("SELECT COUNT(browser) AS count,browser FROM " . STATRIX_TABLE . " WHERE browser != '' GROUP BY browser ORDER BY COUNT(browser) DESC");

	$i = 0;
	foreach($wpdb->last_result as $row) {
		echo "<tr>\n";
		echo "<td>" . ($i + 1) . "</td>";
		echo "<td width=\"50%\">{$row->browser}</td>\n";
		echo "<td width=\"50%\">{$row->count}</td>\n";
		if(!$i) {
			echo '<td valign="top" style="padding: 0px;" rowspan="' . count($wpdb->last_result) . '"><div><canvas id="browsers" height="350" width="350"></canvas></div></td>' . "\n";
		}
		echo "</tr>\n";
		$i++;
	}

	?>
</table>
<br />
<table class="widefat">
	<thead>
		<tr>
			<th scope="col" nowrap="nowrap">No.</th>
			<th scope="col" nowrap="nowrap"><?php _e("OS" , "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Visits" , "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Chart" , "statrix"); ?></th>
		</tr>
	</thead>
	<?php
	$results = $wpdb->query("SELECT COUNT(os) AS count,os FROM " . STATRIX_TABLE . " WHERE os != '' GROUP BY os ORDER BY COUNT(os) DESC");

	$i = 0;
	foreach($wpdb->last_result as $row) {
		echo "<tr>\n";
		echo "<td>" . ($i + 1) . "</td>";
		echo "<td width=\"50%\">{$row->os}</td>\n";
		echo "<td width=\"50%\">{$row->count}</td>\n";
		if(!$i) {
			echo '<td valign="top" style="padding: 0px;" rowspan="' . count($wpdb->last_result) . '"><div><canvas id="os" height="350" width="350"></canvas></div></td>' . "\n";
		}
		echo "</tr>\n";
		$i++;
	}

	?>
</table>
</div>
	<?php
}
?>
