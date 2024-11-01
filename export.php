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

require_once 'includes/Exporter.php';
require_once('../../../wp-config.php');
require_once('../../../wp-includes/wp-db.php');
require_once('../../../wp-includes/pluggable.php');
require_once('includes/constants.php');

if(wp_get_current_user()->wp_user_level < STATRIX_DEFAULT_ACCESS_LEVEL) {
	exit;
}

if(!array_key_exists('filename', $_POST) || !array_key_exists('exporter', $_POST)) {
	exit;
}

export($_POST['filename'], unserialize(urldecode($_POST['exporter'])));
exit;

/**
 * This function exports the CSV.
 *
 * @param string filename
 * @param string comma separated values (CSV)
 */
function export($filename, Exporter $exporter) {
	global $wpdb;
	
	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment;filename=\"{$filename}.csv\" ");
	header("Content-Transfer-Encoding: binary ");
	
	echo $exporter->getContent();
}
?>