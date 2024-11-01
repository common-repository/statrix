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
 * This function loads the IP2country mode.
 *
 * @param string Statrix action
 * @uses $statrixActions
 * @uses statrixWhois()
 */
function loadIp2country($statrix_action) {
	global $wpdb, $wp_db_version;
    global $statrixActions;

    $message = "";
    $sprintfArgs = STATRIX_IP2COUNTRY_TABLE; //default value
    $success = null;

    //if something goes wrong end the script has been terminated without completing all the operations needed
    update_option('statrix_ip2country', 0);

    //check if we need to reimport - if the data in the file is different comparing to the database
    if($wpdb->get_var('SHOW TABLES LIKE "' . STATRIX_IP2COUNTRY_TABLE . '"') == STATRIX_IP2COUNTRY_TABLE) {
        $itemsCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM " . STATRIX_IP2COUNTRY_TABLE));

        $f = fopen(STATRIX_IP_COUNTRY_FILE_PATH, "r");
        $linesCount = 0;
        while (!feof($f)) {
            $line = fgets($f);
            if ($line !== false) {
                $linesCount++;
            }
        }
        fclose($f);

        if($itemsCount != $linesCount + 5) {
            $wpdb->query("DROP TABLE " . STATRIX_IP2COUNTRY_TABLE);
            $message = "Re-import needed. The old data table (%s) has been dropped.<br/>";
        } else {
            $message = "No re-import needed.";
        }
    }

    //check for flags table existence
    if ($wpdb->get_var('SHOW TABLES LIKE "' . STATRIX_IP2COUNTRY_TABLE . '"') != STATRIX_IP2COUNTRY_TABLE) {
        $sql = "CREATE TABLE " . STATRIX_IP2COUNTRY_TABLE ."  (
                        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        begin_ip VARCHAR(20),
                        end_ip VARCHAR(20),
                        begin_ip_num INT(11) UNSIGNED,
                        end_ip_num INT(11) UNSIGNED,
                        country_code VARCHAR(3),
                        country_name VARCHAR(150)
                        )";

        if ($wp_db_version >= 5540) {
            $page = 'wp-admin/includes/upgrade.php';
        } else {
            $page = 'wp-admin/upgrade-functions.php';
        }
        require_once(ABSPATH . $page);
        dbDelta($sql);

        //create indexes
        $wpdb->query("CREATE INDEX begin_ip_num_index ON " . STATRIX_IP2COUNTRY_TABLE . "(begin_ip_num)");
        $wpdb->query("CREATE INDEX end_ip_num_index ON " . STATRIX_IP2COUNTRY_TABLE . "(end_ip_num)");

        importPrivateIPs(); // import the private IP addresses

        $f = fopen(STATRIX_IP_COUNTRY_FILE_PATH, "r");
        if($f) {
            set_time_limit(0);
            while (!feof($f)) {
                $line = fgets($f);
                if ($line !== false) {
                    $columns = explode(',', str_replace('"', '', trim($line)));
                    $wpdb->insert( STATRIX_IP2COUNTRY_TABLE, array(
                                    'begin_ip' => $columns[0],
                                    'end_ip' => $columns[1],
                                    'begin_ip_num' => $columns[2],
                                    'end_ip_num' => $columns[3],
                                    'country_code' => $columns[4],
                                    'country_name' => $columns[5]
                                ), array( '%s', '%s', '%d', '%d', '%s', '%s'));
                }
            }
            fclose($f);
            $message .= $message ? "Re-import finished successfully." : "Import finished successfully.";

            //if we are here, so everything went ok - without any interruption
            $success = true;
        } else {
            $message = $message ? "Re-import failed. Cannot locate file %s" : "Import failed. Cannot locate file %s";
            $sprintfArgs = STATRIX_IP_COUNTRY_FILE_PATH;
            $success = false;
        }
    }

    /*$wpdb->query("ALTER TABLE " . STATRIX_TABLE . " DROP COLUMN country_code");
    $wpdb->query("ALTER TABLE " . STATRIX_TABLE . " DROP COLUMN country_name");*/

    //check if country-related fields in STATRIX_TABLE exist
    if($success !== false &&
            $wpdb->get_var("SELECT COUNT(1) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . STATRIX_TABLE . "' AND COLUMN_NAME = 'country_code' AND TABLE_SCHEMA='" . DB_NAME . "'") == 0 &&
            $wpdb->get_var("SELECT COUNT(1) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . STATRIX_TABLE . "' AND COLUMN_NAME = 'country_name' AND TABLE_SCHEMA='" . DB_NAME . "'") == 0) {
        $wpdb->query("ALTER TABLE " . STATRIX_TABLE . " ADD country_code VARCHAR(3) AFTER ip");
        $wpdb->query("ALTER TABLE " . STATRIX_TABLE . " ADD country_name VARCHAR(150) AFTER country_code");

        set_time_limit(0);

        $wpdb->query("SELECT ip FROM " . STATRIX_TABLE . " GROUP BY ip ORDER BY timestamp");
        foreach($wpdb->last_result as $row) {
            $result = $wpdb->get_results("SELECT country_code,country_name FROM " . STATRIX_IP2COUNTRY_TABLE . " WHERE INET_ATON('{$row->ip}') BETWEEN begin_ip_num AND end_ip_num");
            $wpdb->update(STATRIX_TABLE, array('country_code' => $result[0]->country_code,'country_name' => $result[0]->country_name) , array('ip' => $row->ip), array( '%s', '%s' ), array( '%s' ));
        }
    }

    if($success !== false) {
        update_option('statrix_ip2country', 1);
        update_option('statrix_ip2country_initialized', 1);
    }
?>
<div class="wrap">
<h2><?php _e($statrixActions[$statrix_action]['caption'],'statrix'); ?></h2>
<p><?php echo sprintf(__($message, "statrix"), $sprintfArgs); ?></p>
<p><?php _e(get_option("statrix_ip2country") ? "The IP2country option is now on." : "The IP2country option is now off.", "statrix"); ?></p>
<p><a href="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=statrix/statrix.php&statrix_action=options"><?php _e("Go back to Statrix options.", "statrix"); //TODO a href ?></a></p>
<?php
}

function importPrivateIPs() {
    global $wpdb;

    $addresses = array(
        array(
            'begin_ip' => '10.0.0.0',
            'end_ip' => '10.255.255.255',
            'begin_ip_num' => 167772160,
            'end_ip_num' => 184549375,
            'country_code' => 'PRV',
            'country_name' => 'Private'
        ),
        array(
            'begin_ip' => '172.16.0.0',
            'end_ip' => '172.31.255.255',
            'begin_ip_num' => 2886729728,
            'end_ip_num' => 2887778303,
            'country_code' => 'PRV',
            'country_name' => 'Private'
        ),
        array(
            'begin_ip' => '192.168.0.0',
            'end_ip' => '192.168.255.255',
            'begin_ip_num' => 3232235520,
            'end_ip_num' => 3232301055,
            'country_code' => 'PRV',
            'country_name' => 'Private'
        ),
        array(
            'begin_ip' => '169.254.0.0',
            'end_ip' => '169.254.255.255',
            'begin_ip_num' => 2851995648,
            'end_ip_num' => 2852061183,
            'country_code' => 'LLC',
            'country_name' => 'Link local'
        ),
        array(
            'begin_ip' => '127.0.0.0',
            'end_ip' => '127.255.255.255',
            'begin_ip_num' => 2130706432,
            'end_ip_num' => 2147483647,
            'country_code' => 'LPB',
            'country_name' => 'Loopback'
        )
    );

    foreach ($addresses as $address) {
         $wpdb->insert( STATRIX_IP2COUNTRY_TABLE, array(
                                'begin_ip' => $address['begin_ip'],
                                'end_ip' => $address['end_ip'],
                                'begin_ip_num' => $address['begin_ip_num'],
                                'end_ip_num' => $address['end_ip_num'],
                                'country_code' => $address['country_code'],
                                'country_name' => $address['country_name']
                            ), array( '%s', '%s', '%d', '%d', '%s', '%s'));
    }
}
?>
