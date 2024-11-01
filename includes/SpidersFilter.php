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

require_once 'Filter.php';

/**
 * This class implements the spiders filter features.
 */
class SpidersFilter extends Filter {
	private $ip;
    private $countryName;
	private $spider;
	private $agent;
	private $visitorsPage;

	public function __construct($defaultPeriod = 'all') {
		$this->ip = (array_key_exists('ip', $_GET) ? $_GET['ip'] : '');
        $this->countryName = (array_key_exists('country_name', $_GET) ? $_GET['country_name'] : '');
		$this->spider = (array_key_exists('spider', $_GET) ? $_GET['spider'] : '');
		$this->agent = (array_key_exists('agent', $_GET) ? $_GET['agent'] : '');
		$this->visitorsPage = (array_key_exists('visitors_page', $_GET) ? $_GET['visitors_page'] : '');

		parent::__construct($defaultPeriod);
	}

	public function getIp() {
		return $this->ip;
	}

    public function getCountryName() {
		return $this->countryName;
	}

	public function getSpider() {
		return $this->spider;
	}

	public function getAgent() {
		return $this->agent;
	}

	public function getVisitorsPage() {
		return $this->visitorsPage;
	}

	protected function setCriteria() {
		$this->criteria .= " AND (timestamp BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}') ";
		$this->criteria .= ($this->ip ? " AND ip LIKE '" . str_replace('*', '%', $this->ip) . "' ": '');
        $this->criteria .= ($this->countryName ? " AND country_name = '{$this->countryName}' ": '');
		$this->criteria .= ($this->spider ? " AND spider = '{$this->spider}' ": '');
		$this->criteria .= ($this->agent ? " AND agent LIKE '" . str_replace('*', '%', $this->agent) . "' ": '');
		$this->criteria .= ($this->visitorsPage ? " AND request_uri LIKE '" . str_replace('*', '%', $this->visitorsPage) . "' ": '');
	}
}

?>