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
 * This class implements the search terms filter features.
 */
class SearchTermsFilter extends Filter {
	private $ip;
    private $countryName;
	private $searchEngine;
	private $searchTerms;
	private $visitorsPage;
	private $os;
	private $browser;

	public function __construct($defaultPeriod = 'all') {
		$this->ip = (array_key_exists('ip', $_GET) ? $_GET['ip'] : '');
        $this->countryName = (array_key_exists('country_name', $_GET) ? $_GET['country_name'] : '');
		$this->searchEngine = (array_key_exists('search_engine', $_GET) ? $_GET['search_engine'] : '');
		$this->searchTerms = (array_key_exists('search_terms', $_GET) ? $_GET['search_terms'] : '');
		$this->visitorsPage = (array_key_exists('visitors_page', $_GET) ? $_GET['visitors_page'] : '');
		$this->os = (array_key_exists('os', $_GET) ? $_GET['os'] : '');
		$this->browser = (array_key_exists('browser', $_GET) ? $_GET['browser'] : '');

		parent::__construct($defaultPeriod);
	}

	public function getIp() {
		return $this->ip;
	}

    public function getCountryName() {
		return $this->countryName;
	}

	public function getSearchEngine() {
		return $this->searchEngine;
	}

	public function getSearchTerms() {
		return $this->searchTerms;
	}

	public function getVisitorsPage() {
		return $this->visitorsPage;
	}

	public function getOs() {
		return $this->os;
	}

	public function getBrowser() {
		return $this->browser;
	}

	protected function setCriteria() {
		$this->criteria .= " AND (timestamp BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}') ";
		$this->criteria .= ($this->ip ? " AND ip LIKE '" . str_replace('*', '%', $this->ip) . "' ": '');
        $this->criteria .= ($this->countryName ? " AND country_name = '{$this->countryName}' ": '');
		$this->criteria .= ($this->searchEngine ? " AND search_engine = '{$this->searchEngine}' " : '');
		$this->criteria .= ($this->searchTerms ? " AND search_terms LIKE '" . str_replace('*', '%', $this->searchTerms) . "' " : '');
		$this->criteria .= ($this->visitorsPage ? " AND request_uri LIKE '" . str_replace('*', '%', $this->visitorsPage) . "' ": '');
		$this->criteria .= ($this->os ? " AND os = '{$this->os}' ": '');
		$this->criteria .= ($this->browser ? " AND browser = '{$this->browser}' ": '');
	}
}

?>