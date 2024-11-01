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
 * This class implements the filter features.
 */
class Filter {
	protected $dateFrom;
	protected $dateTo;
	protected $criteria = '';

	public function __construct($defaultPeriod = 'all') {
		$this->setTimePeriod($defaultPeriod);
		$this->setCriteria();
	}

	public function getDateFrom() {
		return $this->dateFrom;
	}

	public function getDateTo() {
		return $this->dateTo;
	}

	public function getCriteria() {
		return $this->criteria;
	}

	protected function setCriteria() {
		$this->criteria .= " AND (timestamp BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}') ";
	}

	protected function setTimePeriod($defaultPeriod) {
		if(($_GET['period'] != 'today' && $_GET['period'] != 'lastWeek' && $_GET['period'] != 'lastMonth' && $_GET['period'] != 'lastYear' && $_GET['period'] != 'all' && $_GET['period'] != 'custom') ||
		($_GET['period'] == 'custom' && (!$_GET['dayFrom'] || !$_GET['monthFrom'] || !$_GET['yearFrom'] || !$_GET['hourFrom'] || !$_GET['minutesFrom'] ||
		!$_GET['dayTo'] || !$_GET['monthTo'] || !$_GET['yearTo'] || !$_GET['hourTo'] || !$_GET['minutesTo']))) {
			//default settings
			$_GET['period'] = $defaultPeriod;
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

		if($timestampTo - $timestampFrom >= 0) {
			$this->dateFrom = $dateFrom;
			$this->dateTo = $dateTo;
		} else {
			$this->dateFrom = $dateTo;
			$this->dateTo = $dateFrom;
		}
	}

}
?>