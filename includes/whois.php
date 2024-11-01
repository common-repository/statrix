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
 * This function loads the Visitors mode.
 *
 * @param string Statrix action
 * @uses $statrixActions
 * @uses statrixWhois()
 */
function loadWhois($statrix_action) {
	global $statrixActions;

	$query = (array_key_exists('statrix_query', $_GET) ? $_GET['statrix_query'] : "");
	?>

<div class="wrap">
<h2><?php _e($statrixActions[$statrix_action]['caption'],'statrix'); ?></h2>
<table width="100%" class="widefat">
	<thead>
		<tr>
			<th><?php echo $query; ?></th>
		</tr>
	</thead>
	<tr>
		<td>hostname:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gethostbyaddr($query); ?></td>
	</tr>
	<tr>
		<td><?php
		if(array_key_exists('statrix_query', $_GET)) {
			echo '<pre>' . statrixWhois($query) . '</pre>';
		}
		?></td>
	</tr>
</table>
</div>
		<?php
}
?>
