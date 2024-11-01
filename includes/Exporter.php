<?php
/*
 Copyright 2008-2012   ClearCode Ltd.  (email : contacts@clearcode.bg)

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
 * This class implements the data export features.
 */
class Exporter {
	private $filterArray = array();
	private $dataTableCaption;
	private $columnsArray = array();
	private $criteria;

	public function setFilter($filterArray) {
		$this->filterArray = $filterArray;
	}

	public function setDataTableCaption($dataTableCaption) {
		$this->dataTableCaption = $dataTableCaption;
	}

	public function setColumnsArray($columnsArray) {
		$this->columnsArray = $columnsArray;
	}
	
	public function setCriteria($criteria) {
		$this->criteria = $criteria;
	}

	public function getContent() {
		global $wpdb;
		$content = '';
		
		foreach($this->filterArray as $key => $value) {
			$content .= "{$key}, \"{$value}\"\n";
		}
		$content .= "\n\n\n";

		$content .= $this->dataTableCaption . "\n";
		$filter = implode(',', $this->columnsArray);

		$results = $wpdb->query("SELECT " . ($filter ? $filter . ',' : '') . " UNIX_TIMESTAMP(timestamp) AS timestamp FROM " . STATRIX_TABLE . " WHERE " . $this->criteria);
		foreach($wpdb->last_result as $row) {
			$content .= '"' . date("l, M d, Y", $row->timestamp) . '",' .
		'"' . date("H:i:s", $row->timestamp) . '"';

			foreach($this->columnsArray as $column) {
				$content .= ',"' . stripslashes($row->$column) . '"';
			}
			$content .= "\n";
		}

		return $content;
	}
}
?>