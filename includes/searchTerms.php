<?php
/*
 Copyright 2008-2012  ClearCode Ltd. (email : contacts@clearcode.bg)

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

require_once 'SearchTermsFilter.php';
require_once 'Exporter.php';

/**
 * This function loads the Spiders mode.
 *
 * @param string Statrix action
 * @uses $statrixActions
 * @uses printPages()
 */
function loadSearchTerms($statrix_action) {
	global $wpdb;
	global $statrixActions;
	global $weekdays;

	if($_POST) {
        if(array_key_exists('deleteSearchTerms', $_POST)) {
            $searchTermsForDeletion = implode(',', array_keys($_POST['deleteSearchTerms']));
            if($searchTermsForDeletion) {
			    $wpdb->query("DELETE FROM " . STATRIX_TABLE . " WHERE id IN ({$searchTermsForDeletion})");
            }
        } else {
		    update_option('statrix_search_terms_per_page', (int)$_POST['searchTermsPerPage']);
        }
	}

	$filter = new SearchTermsFilter();
	$sort = getSort($statrix_action);
	$order = getOrder($statrix_action, $sort);
	$limit = get_option('statrix_search_terms_per_page');
	$offset = getOffset($limit);
	?>

<div class="wrap">
<h2><?php _e($statrixActions[$statrix_action]['caption'],'statrix'); ?></h2>
<br />

<div id="poststuff" class="dlm">
<div class="postbox close-me dlmbox">
<h3><?php _e('Filter', 'statrix'); ?></h3>
<div class="inside">

<form name="filterHits" id="filterHits" method="get"><?php 
echo '<input name="page" id="page" type="hidden" value="' . STATRIX_DIR . '/' . STATRIX_MAIN_FILE . '">
	<input name="statrix_action" id="statrix_action" type="hidden" value="' . $statrix_action . '">
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
			value="lastWeek"
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
			value="all" checked="checked"
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
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('IP', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><input name="ip" id="ip" type="text"
			size="39" maxlength="39"><br />
		<i><?php _e("Wildcards allowed (e.g. 192.168.*.*).", "statrix"); ?></i><br />
		<br />
		</td>
	</tr>
    <tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('Country name', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><select name="country_name" id="country_name">
			<option value=""><?php _e("Not Specified", "statrix"); ?></option>
			<?php
			global $wpdb;
			$wpdb->query("SELECT country_name FROM " . STATRIX_TABLE . " WHERE country_name!='' AND country_code!='' AND search_engine!='' GROUP BY country_name ORDER BY country_name");
			foreach($wpdb->last_result as $row) {
				echo "<option value=\"{$row->country_name}\">{$row->country_name}</option>\n";
			}
			?>
		</select></td>
	</tr>
	<tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('Search engine', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><select name="search_engine"
			id="search_engine">
			<option value=""><?php _e("Not Specified", "statrix"); ?></option>
			<?php
			global $wpdb;
			$results = $wpdb->query("SELECT search_engine FROM " . STATRIX_TABLE . " WHERE search_engine != '' GROUP BY search_engine ORDER BY search_engine");
			foreach($wpdb->last_result as $row) {
				echo "<option value=\"{$row->search_engine}\">{$row->search_engine}</option>\n";
			}
			?>
		</select></td>
	</tr>
	<tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('Search terms', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><input name="search_terms"
			id="search_terms" type="text" size="100"><br />
		<i><?php _e("Wildcards allowed (e.g. blog*).", "statrix"); ?></i><br />
		<br />
		</td>
	</tr>
	<tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('Page', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><input name="visitors_page"
			id="visitors_page" type="text" size="100"><br />
		<i><?php _e("Wildcards allowed (e.g. /wp-admin/admin.php?page=*).", "statrix"); ?></i><br />
		<br />
		</td>
	</tr>
	<tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('OS', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><select name="os" id="os">
			<option value=""><?php _e("Not Specified", "statrix"); ?></option>
			<?php
			global $wpdb;
			$results = $wpdb->query("SELECT os FROM " . STATRIX_TABLE . " WHERE os != '' GROUP BY os ORDER BY os");
			foreach($wpdb->last_result as $row) {
				echo "<option value=\"{$row->os}\">{$row->os}</option>\n";
			}
			?>
		</select></td>
	</tr>
	<tr>
		<td valign="top" align="right">&nbsp;&nbsp;<strong><?php _e('Browser', 'statrix'); ?>:</strong></td>
		<td valign="top" colspan="2"><select name="browser" id="browser">
			<option value=""><?php _e("Not Specified", "statrix"); ?></option>
			<?php
			global $wpdb;
			$results = $wpdb->query("SELECT browser FROM " . STATRIX_TABLE . " WHERE browser != '' GROUP BY browser ORDER BY browser");
			foreach($wpdb->last_result as $row) {
				echo "<option value=\"{$row->browser}\">{$row->browser}</option>\n";
			}
			?>
		</select></td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
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

			<?php

			?>

<table align="left" width="70%">
	<tr>
		<td>
		<div id="hitsFilterMessage">
		<table>
		<?php
		print "<tr><td align=\"left\"><b>" . __("From", "statrix") . ":</b></td><td align=\"left\">" . $filter->getDateFrom() . "</td></tr>\n";
		print "<tr><td align=\"left\"><b>" . __("To", "statrix") . ":</b></td><td align=\"left\">" . $filter->getDateTo() . "</td></tr>\n";
		print ($filter->getIp() ? "<tr><td align=\"left\"><b>" . __("IP", "statrix") . ":</b></td><td align=\"left\">" . $filter->getIp() . "</td></tr>\n" : '');
        print ($filter->getCountryName() ? "<tr><td align=\"left\"><b>" . __("Country name", "statrix") . ":</b></td><td align=\"left\">" . __($filter->getCountryName(), "statrix") . "</td></tr>\n" : '');
		print ($filter->getSearchEngine() ? "<tr><td align=\"left\"><b>" . __("Search engine", "statrix") . ":</b></td><td align=\"left\">" . $filter->getSearchEngine() . "</td></tr>\n" : '');
		print ($filter->getSearchTerms() ? "<tr><td align=\"left\"><b>" . __("Search terms", "statrix") . ":</b></td><td align=\"left\">" . $filter->getSearchTerms() . "</td></tr>\n" : '');
		print ($filter->getVisitorsPage() ? "<tr><td align=\"left\"><b>" . __("Page", "statrix") . ":</b></td><td align=\"left\">" . $filter->getVisitorsPage() . "</td></tr>\n" : '');
		print ($filter->getOs() ? "<tr><td align=\"left\"><b>" . __("OS", "statrix") . ":</b></td><td align=\"left\">" . $filter->getOs() . "</td></tr>\n" : '');
		print ($filter->getBrowser() ? "<tr><td align=\"left\"><b>" . __("Browser", "statrix") . ":</b></td><td align=\"left\">" . $filter->getBrowser() . "</td></tr>\n" : '');
		?>
		</table>
		<br />
		</div>
		</td>
	</tr>
</table>

<form name="searchTermsPerPageForm" id="searchTermsPerPageForm"
	method="post">
<table align="right">
	<tr>
		<td align="right"><?php _e("Total search terms", "statrix"); ?>:</td>
		<td align="left"><b> <?php
		global $wpdb;

		$results = $wpdb->query("SELECT COUNT(1) AS search_terms_count FROM " . STATRIX_TABLE . " WHERE search_terms != '' " . $filter->getCriteria());
		$searchTermsCount = $wpdb->last_result[0]->search_terms_count;
		echo $searchTermsCount;
		?> </b></td>
	</tr>
	<tr>
		<td align="right"><?php _e("Search terms per page", "statrix"); ?>:</td>
		<td align="left"><select name="searchTermsPerPage"
			id="searchTermsPerPage"
			onchange="submitForm('searchTermsPerPageForm');">
			<?php

			switch(get_option('statrix_search_terms_per_page')) {
				case 10: $selected10 = ' selected="selected"'; break;
				case 20: $selected20 = ' selected="selected"'; break;
				case 50: $selected50 = ' selected="selected"'; break;
				default: $selected20 = ' selected="selected"'; break;
			}

			?>
			<option value="10" <?php echo $selected10; ?>>10</option>
			<option value="20" <?php echo $selected20; ?>>20</option>
			<option value="50" <?php echo $selected50; ?>>50</option>
		</select></td>
	</tr>
</table>
</form>

<?php if(get_option("statrix_delete_history_option")) { ?>
<form name="deleteSearchTermsForm" id="deleteSearchTermsForm" method="post">
<?php } ?>
<table width="100%" class="widefat">
	<thead>
		<tr>
            <?php if(get_option("statrix_delete_history_option")) { ?>
            <th scope="col"><input type="checkbox" name="selectAll" id="selectAll" style="margin: 0;" onclick="toggleBoxesStartingWith('deleteSearchTerms', document.getElementById('selectAll').checked);"/></th>
            <?php } ?>
			<th scope="col" nowrap="nowrap"><?php _e("Date", "statrix"); ?> <?php buildSortLink($statrix_action, 'date', 'desc') ?>
			<?php buildSortLink($statrix_action, 'date', 'asc') ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Time", "statrix"); ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("IP", "statrix"); ?> <?php buildSortLink($statrix_action, 'ip', 'desc') ?>
			<?php buildSortLink($statrix_action, 'ip', 'asc') ?></th>
            <?php if(get_option("statrix_ip2country")) { ?>
            <th scope="col" nowrap="nowrap"><?php _e("Country name", "statrix"); ?> <?php buildSortLink($statrix_action, 'country_name', 'desc') ?>
			<?php buildSortLink($statrix_action, 'country_name', 'asc') ?></th>
            <?php } ?>
			<th scope="col" nowrap="nowrap"><?php _e("Search engine", "statrix"); ?> <?php buildSortLink($statrix_action, 'search_engine', 'desc') ?>
			<?php buildSortLink($statrix_action, 'search_engine', 'asc') ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Search terms", "statrix"); ?> <?php buildSortLink($statrix_action, 'search_terms', 'desc') ?>
			<?php buildSortLink($statrix_action, 'search_terms', 'asc') ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Page", "statrix"); ?> <?php buildSortLink($statrix_action, 'page', 'desc') ?>
			<?php buildSortLink($statrix_action, 'page', 'asc') ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("OS", "statrix"); ?> <?php buildSortLink($statrix_action, 'os', 'desc') ?>
			<?php buildSortLink($statrix_action, 'os', 'asc') ?></th>
			<th scope="col" nowrap="nowrap"><?php _e("Browser", "statrix"); ?> <?php buildSortLink($statrix_action, 'browser', 'desc') ?>
			<?php buildSortLink($statrix_action, 'browser', 'asc') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	global $wpdb;

	$results = $wpdb->query("SELECT " . (get_option("statrix_delete_history_option") ? "id," : "") . "ip," . (get_option("statrix_ip2country") ? "country_code,country_name," : "") . "search_engine,search_terms,request_uri,os,browser,timestamp FROM " . STATRIX_TABLE . " WHERE search_terms != '' " . $filter->getCriteria() . ($sort ? " ORDER BY $sort $order " : " ") .
	" LIMIT $limit OFFSET $offset");

	foreach($wpdb->last_result as $row) {
        $ipLabel = getIpLabel($row->ip);
		echo "<tr valign=\"top\"\n>";
        echo get_option("statrix_delete_history_option") ? "<td><input type=\"checkbox\" name=\"deleteSearchTerms[{$row->id}]\" id=\"deleteSearchTerms[{$row->id}]\" /></td>" : "";
		echo "<td nowrap=\"nowrap\">" . date("l, M d, Y", strtotime($row->timestamp))  . "</td>\n";
		echo "<td nowrap=\"nowrap\">" . date("H:i:s", strtotime($row->timestamp))  . "</td>\n";
		echo "<td nowrap=\"nowrap\"><a href=\"?page=" . STATRIX_DIR . "/" . STATRIX_MAIN_FILE . "&amp;statrix_action=whois&amp;statrix_query={$row->ip}\">{$row->ip}</a>" . ($ipLabel ? " ({$ipLabel})" : "") . "</td>\n";
        echo get_option("statrix_ip2country") ? "<td nowrap=\"nowrap\">" . ($row->country_code ? "<img src=\"" . STATRIX_FLAGS_IMAGE_DIR . DIRECTORY_SEPARATOR . strtolower($row->country_code) . ".png\"/> " : "") . __($row->country_name, "statrix") . "</td>\n" : "";
		echo "<td nowrap=\"nowrap\">{$row->search_engine}</td>\n";
		echo '<td nowrap="nowrap">' . stripslashes($row->search_terms) . "</td>\n";
		echo '<td><a href="http://' . getBlogDomain() . stripslashes($row->request_uri) . '">' . stripslashes($row->request_uri) . "</a></td>\n";
		echo "<td nowrap=\"nowrap\">{$row->os}</td>\n";
		echo "<td nowrap=\"nowrap\">{$row->browser}</td>\n";
		echo "</tr>\n";
	}
	?>
	</tbody>
</table>

<?php if(get_option("statrix_delete_history_option")) { ?>
<input class="button-secondary" type="submit" value="<?php _e("Delete selected", "statrix"); ?>" onclick="return confirmAction('<?php _e("Are you sure you want to delete the selected items?", "statrix"); ?>');"/>
<?php } ?>
</form>

	<?php
	printPages($searchTermsCount, $limit);

	$exporter = new Exporter();
	$exporter->setFilter(array('From' => $filter->getDateFrom(),
									'To' => $filter->getDateTo(),
									'IP' => $filter->getIp(),
                                    'Country name' => $filter->getCountryName(),
									'Search Engine' => $filter->getSearchEngine(),
									'Search Terms' => $filter->getSearchTerms(),
									'Page' => $filter->getVisitorsPage(),
									'OS' => $filter->getOs(),
									'Browser' => $filter->getBrowser()));
	$exporter->setDataTableCaption('Date, Time, IP, Country code, Country name, Search Engine, Search Terms, Page, OS, Browser');
	$exporter->setColumnsArray(array('ip', 'country_code', 'country_name', 'search_engine', 'search_terms', 'request_uri', 'os', 'browser'));
	$exporter->setCriteria(" search_terms != '' " . $filter->getCriteria() . ($sort ? " ORDER BY $sort $order " : " "));
	
	printExport("{$statrixActions[$statrix_action]['caption']}_" . date("d-m-Y_H:i:s"), $exporter);
	?></div>
	<?php
}
?>