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

/**
 * This function loads the Options mode.
 *
 * @param string Statrix action
 * @uses $statrixActions
 * @uses isUserExcluded()
 */
function loadOptions($statrix_action) {
	global $statrixActions;

	if($_POST) {
		update_option('statrix_collect_anonymous_visitors_data', (int)$_POST['collect_anonymous_visitors_data']);
		update_option('statrix_collect_logged_users_data', (int)$_POST['collect_logged_users_data']);
		update_option('statrix_collect_spiders_data', (int)$_POST['collect_spiders_data']);

		update_option('statrix_collect_admin_menu_data', (int)$_POST['collect_admin_menu_data']);
		update_option('statrix_collect_blog_pages_data', (int)$_POST['collect_blog_pages_data']);
		update_option('statrix_collect_rss_feed_data', (int)$_POST['collect_rss_feed_data']);

        if((int)$_POST['ip2country'] == 0) {
            update_option('statrix_ip2country', 0);
        }

		if(is_array($_POST['excluded_users'])) {
			$_POST['excluded_users'] = implode(',', $_POST['excluded_users']);
		}
		update_option('statrix_excluded_users', $_POST['excluded_users']);
		update_option('statrix_excluded_ips', trim($_POST['excluded_ips']));
        update_option('statrix_excluded_resources', trim($_POST['excluded_resources']));
        update_option('statrix_labeled_ips', trim($_POST['statrix_labeled_ips']));
		update_option('statrix_delete_history', $_POST['delete_history']);
        update_option('statrix_delete_history_option', (int)$_POST['delete_history_option']);
		update_option('statrix_access_level', $_POST['access_level']);

        update_option('statrix_locale', $_POST['locale']);

        statrix_localize(); // TODO: doesn't actually make any difference here
	}
	?>
<div class="wrap">
<h2><?php _e($statrixActions[$statrix_action]['caption'],'statrix'); ?></h2>

<form method="post" onSubmit="selectOptions('excluded_users[]');">
<table class="form-table">
    <tr valign="top">
        <td colspan="2"><b>*<?php echo sprintf(__("Statrix uses the timezone offset, managed in %s", "statrix"), '<a href="' . get_option("home") . '/wp-admin/options-general.php">' . __("Settings") . ' &raquo; ' . __("General") . '</a>'); ?></b></td>
    </tr>
	<tr valign="top">
		<td><strong><?php _e('Collect data about', 'statrix'); ?>:</strong></td>
		<td><input name="collect_anonymous_visitors_data" type="checkbox"
			id="collect_anonymous_visitors_data" value="1"
			<?php checked('1', get_option('statrix_collect_anonymous_visitors_data')); ?> />
			<?php _e('Anonymous visitors', 'statrix') ?><br />
		<input name="collect_logged_users_data" type="checkbox"
			id="collect_logged_users_data" value="1"
			<?php checked('1', get_option('statrix_collect_logged_users_data')); ?> />
			<?php _e('Logged users', 'statrix') ?><br />
		<input name="collect_spiders_data" type="checkbox"
			id="collect_spiders_data" value="1"
			<?php checked('1', get_option('statrix_collect_spiders_data')); ?> />
			<?php _e('Spiders', 'statrix') ?><br />
		</td>
	</tr>
	<tr valign="top">
		<td><strong><?php _e('Collect data from', 'statrix'); ?>:</strong></td>
		<td><input name="collect_admin_menu_data" type="checkbox"
			id="collect_admin_menu_data" value="1"
			<?php checked('1', get_option('statrix_collect_admin_menu_data')); ?> />
			<?php _e('Admin menu', 'statrix') ?><br />
		<input name="collect_blog_pages_data" type="checkbox"
			id="collect_blog_pages_data" value="1"
			<?php checked('1', get_option('statrix_collect_blog_pages_data')); ?> />
			<?php _e('Blog pages', 'statrix') ?><br />
		<input name="collect_rss_feed_data" type="checkbox"
			id="collect_rss_feed_data" value="1"
			<?php checked('1', get_option('statrix_collect_rss_feed_data')); ?> />
			<?php _e('RSS feed', 'statrix') ?><br />
		</td>
	</tr>
    <tr>
        <td valign="top"><strong><?php _e('Show data about', 'statrix'); ?>:</strong></td>
        <td>
            <?php if(get_option('statrix_ip2country')) { ?>
                <input name="ip2country" type="checkbox"
                id="ip2country" value="1"
                <?php checked('1', get_option('statrix_ip2country')); ?> />
                <?php _e('Visitors\' country by IP', 'statrix') ?>
            <?php } else { ?>
                <?php _e('Visitors\' country by IP', 'statrix') ?> - <a href="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=statrix/statrix.php&statrix_action=ip2country_import"><?php _e('TURN ON', 'statrix'); ?></a><br/>
                <div style="background-color: #DFDFDF; width: 800px; padding: 5px;">
                    <i><?php echo sprintf(__('<b>INFO</b>: Turning this option on will initially create %s table into your MySQL, importing the whole <a href="%s" target="_blank">GeoIPCountryWhois</a> database.<br/>After the import, all of your previously collected visitors information will be updated.', 'statrix'), STATRIX_IP2COUNTRY_TABLE, "http://www.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip"); ?></i>
                    <br/><br/>
                    <i><?php echo sprintf(__('<b>WARNING</b>: Please note that turning this option on is a slow process itself, that should <u>not be interrupted</u>.<br/>In order to be completed, Statrix uses <a href="%s" target="_blank">set_time_limit()</a> PHP function, that has <u>no effect</u> when PHP is running in safe mode.<br/>In that case, your import and update will probably fail and you will <u>not</u> be able to use this functionality.', 'statrix'), 'http://php.net/manual/en/function.set-time-limit.php'); ?></i>
                    <br/><br/>
                    <i><?php _e('<b>ERROR</b>: If by some reason the script has been terminated, you will probably see a white screen or an error message.<br/>Don\'t worry, failing to turn this option on is <u>not dangerous</u>, but depends completely of your PHP and Web server configurations.', 'statrix'); ?></i>
                </div>
            <?php } ?>
        </td>
    </tr>
	<tr valign="top">
		<td><strong><?php _e('Exclude users', 'statrix'); ?>:</strong></td>
		<td>
		<table style="margin: 0px; border-spacing: 0px;" cellspacing="0"
			cellpadding="0">
			<tr>
				<td align="left" valign="top"
					style="padding-left: 0px; padding-top: 0px; border-bottom-width: 0px;"><?php _e("Registered users", "statrix"); ?>:<br />
				<select name="registered_users" id="registered_users"
					multiple="multiple" size="20"
					style="width: 320px; height: 195px; border-color: #c6d9e9; margin: 1px; padding: 3px">
					<?php
					global $wpdb;
					$results = $wpdb->query("SELECT ID,user_login FROM {$wpdb->users} ORDER BY user_login");
					foreach($wpdb->last_result as $row) {
						if(!isUserExcluded($row->ID)) {
							echo "<option value=\"{$row->ID}\">{$row->user_login}</option>\n";
						}
					}
					?>
				</select></td>
				<td valign="top"
					style="padding-left: 0px; padding-top: 0px; border-bottom-width: 0px;"><br />
				<input type="button" class="button" name="addUserButton"
					id="addUserButton" value="&gt;&gt;"
					onClick="moveSelectedOptions('registered_users','excluded_users[]');" /><br />
				<br />
				<input type="button" class="button" name="removeUserButton"
					id="removeUserButton" value="&lt;&lt;"
					onClick="moveSelectedOptions('excluded_users[]','registered_users');" />
				</td>
				<td valign="top"
					style="padding-left: 0px; padding-top: 0px; border-bottom-width: 0px;"><?php _e("Excluded users", "statrix"); ?>:<br />
				<select name="excluded_users[]" id="excluded_users[]"
					multiple="multiple" size="20"
					style="width: 320px; height: 195px; border-color: #c6d9e9; margin: 1px; padding: 3px;">
					<?php
					global $wpdb;
					$excludedUsers = (get_option('statrix_excluded_users') ? explode("\n", trim(get_option('statrix_excluded_users'))) : null);
					if(is_array($excludedUsers)) {
						$excludedUsers = implode(',', $excludedUsers);
					}
					$results = $wpdb->query("SELECT ID,user_login FROM {$wpdb->users} WHERE ID IN ($excludedUsers) ORDER BY user_login");
					foreach($wpdb->last_result as $row) {
						echo "<option value=\"{$row->ID}\">{$row->user_login}</option>\n";
					}
					?>
				</select></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr valign="top">
		<td><strong><?php _e('Exclude IPs', 'statrix'); ?>:</strong><br />
		<?php _e('Use <strong>*</strong> for wildcards.', 'statrix'); ?><br />
		<?php _e('Start each entry on a new line.', 'statrix'); ?><br />
		<br />
		<?php _e('Examples', 'statrix'); ?>:<br />
		<strong>&raquo;</strong> 192.168.1.100<br />
		<strong>&raquo;</strong> 192.168.1.*<br />
		<strong>&raquo;</strong> 192.168.*.*<br />
		</td>
		<td><textarea cols="40" rows="10" name="excluded_ips"><?php echo get_option('statrix_excluded_ips'); ?></textarea>
		</td>
	</tr>
    <tr valign="top">
		<td><strong><?php _e('Exclude resources', 'statrix'); ?>:</strong><br />
		<?php _e('Use <strong>*</strong> for wildcards.', 'statrix'); ?><br />
		<?php _e('Start each entry on a new line.', 'statrix'); ?><br />
		<br />
		<?php _e('Examples', 'statrix'); ?>:<br />
		<strong>&raquo;</strong> *favicon.ico<br />
		<strong>&raquo;</strong> *sample-dir*<br />
		</td>
		<td><textarea cols="40" rows="10" name="excluded_resources"><?php echo get_option('statrix_excluded_resources'); ?></textarea>
		</td>
	</tr>
    <tr valign="top">
    		<td><strong><?php _e('Label IPs', 'statrix'); ?>:</strong><br />
    		<?php _e('Use <strong>*</strong> for wildcards.', 'statrix'); ?><br />
            <?php _e('Use <strong>:</strong> to separate IP from it\'s label.', 'statrix'); ?><br />
    		<?php _e('Start each entry on a new line.', 'statrix'); ?><br />
    		<br />
    		<?php _e('Examples', 'statrix'); ?>:<br />
            <strong>&raquo;</strong> 192.168.1.100:home PC<br />
            <strong>&raquo;</strong> 192.168.1.*:home network<br />
            <strong>&raquo;</strong> 8.8.8.8:google<br />
            <strong>&raquo;</strong> 8.8.4.4:google<br />
    		</td>
    		<td><textarea cols="40" rows="10" name="statrix_labeled_ips"><?php echo get_option('statrix_labeled_ips'); ?></textarea>
    		</td>
    </tr>
	<tr valign="top">
		<td><strong><?php _e('Delete history', 'statrix'); ?>:</strong></td>
		<td><select name="delete_history" id="delete_history">
		<?php
		switch(get_option('statrix_delete_history')) {
			case 'week': $selectedWeek = ' selected="selected"'; break;
			case 'month': $selectedMonth = ' selected="selected"'; break;
			case 'months': $selectedMonths = ' selected="selected"'; break;
			case 'year': $selectedYear = ' selected="selected"'; break;
			default: $selectedNever = ' selected="selected"'; break;
		}
		?>
			<option value="week" <?php echo $selectedWeek; ?>><?php _e("Olrder than a week", "statrix"); ?></option>
			<option value="month" <?php echo $selectedMonth; ?>><?php _e("Older than a month", "statrix"); ?></option>
			<option value="months" <?php echo $selectedMonths; ?>><?php _e("Older than 6 months", "statrix"); ?></option>
			<option value="year" <?php echo $selectedYear; ?>><?php _e("Older than a year", "statrix"); ?></option>
			<option value="never" <?php echo $selectedNever; ?>><?php _e("Never", "statrix"); ?></option>
		</select></td>
	</tr>
    <tr valign="top">
        <td>&nbsp;</td>
		<td><input name="delete_history_option" type="checkbox"
			id="delete_history_option" value="1"
			<?php checked('1', get_option('statrix_delete_history_option')); ?> />
			<?php _e('Option for manually deletion', 'statrix') ?>
        <td>
	</tr>
	<tr valign="top">
		<td><strong><?php _e('Share Statrix with', 'statrix'); ?>:</strong></td>
		<td><select name="access_level" id="access_level">
		<?php
		$level = get_option('statrix_access_level');
			if($level == 'zero') {
				$selected0 = ' selected="selected"';
			} elseif($level >= 5 && $level < 8) {
				$selected5 = ' selected="selected"';
			} elseif($level >= 2 && $level < 5) {
				$selected2 = ' selected="selected"';
			} elseif($level == 1) {
				$selected1 = ' selected="selected"';
			}else {
				$selected8 = ' selected="selected"';
			}

		?>
			<option value="8" <?php echo $selected8; ?>><?php _e("Administrators only", "statrix"); ?></option>
			<option value="5" <?php echo $selected5; ?>><?php _e("Administrators, Editors", "statrix"); ?></option>
			<option value="2" <?php echo $selected2; ?>><?php _e("Administrators, Editors, Authors", "statrix"); ?></option>
			<option value="1" <?php echo $selected1; ?>><?php _e("Administrators, Editors, Authors, Contributors", "statrix"); ?></option>
			<option value="zero" <?php echo $selected0; ?>><?php _e("Administrators, Editors, Authors, Contributors, Subscribers", "statrix"); ?></option>
		</select></td>
	</tr>
    <tr valign="top">
        <td><strong><?php _e('Set locale', 'statrix'); ?>:</strong></td>
        <td><select name="locale" id="locale">
        <?php
            $currentLocale = getCurrentStatrixLocale();
            $localesAvailable = getAvailableStatrixLocales();
            foreach ($localesAvailable as $locale) {
                echo "<option value='{$locale}'" . ($locale == $currentLocale ? ' selected="selected"' : '') . ">{$locale}</option>\n";
            }
        ?>
        </select></td>
    </tr>
</table>
<p class="submit"><input type="submit" name="Submit"
	value="<?php _e("Save changes", "statrix"); ?>" /></p>
</form>
</div>
		<?php
}
?>
