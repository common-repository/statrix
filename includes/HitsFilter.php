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

require_once 'Filter.php';

/**
 * This class implements the hits filter features.
 */
class HitsFilter extends Filter {
	private $ip;
    private $countryName;
	private $username;
	private $visitorsPage;
    private $externalReferer;
    private $nonEmptyReferer;
	private $emptyReferer;
	private $referer;
	private $os;
	private $browser;
	private $rss;

	public function __construct($defaultPeriod = 'all') {
		$this->ip = (array_key_exists('ip', $_GET) ? $_GET['ip'] : '');
        $this->countryName = (array_key_exists('country_name', $_GET) ? $_GET['country_name'] : '');
		$this->username = (array_key_exists('username', $_GET) ? $_GET['username'] : '');
		$this->visitorsPage = (array_key_exists('visitors_page', $_GET) ? $_GET['visitors_page'] : '');
		$this->externalReferer = (array_key_exists('external_referer', $_GET) ? $_GET['external_referer'] : '');
        $this->nonEmptyReferer = (array_key_exists('non_empty_referer', $_GET) ? $_GET['non_empty_referer'] : '');
        $this->emptyReferer = (array_key_exists('empty_referer', $_GET) ? $_GET['empty_referer'] : '');
		$this->referer = (array_key_exists('referer', $_GET) ? $_GET['referer'] : '');
		$this->os = (array_key_exists('os', $_GET) ? $_GET['os'] : '');
		$this->browser = (array_key_exists('browser', $_GET) ? $_GET['browser'] : '');
		$this->rss = (array_key_exists('rss', $_GET) ? $_GET['rss'] : '');

		parent::__construct($defaultPeriod);
	}

	public function getIp() {
		return $this->ip;
	}

    public function getCountryName() {
		return $this->countryName;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getVisitorsPage() {
		return $this->visitorsPage;
	}

    public function getExternalReferer() {
		return $this->externalReferer;
	}
	
	public function getNonEmptyReferer() {
		return $this->nonEmptyReferer;
	}

    public function getEmptyReferer() {
		return $this->emptyReferer;
	}

	public function getReferer() {
		return $this->referer;
	}
	
	public function getOs() {
		return $this->os;
	}
	
	public function getBrowser() {
		return $this->browser;
	}
	
	public function getRss() {
		return $this->rss;
	}
	
	protected function setCriteria() {
		$this->criteria .= " AND (timestamp BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}') ";
		$this->criteria .= ($this->ip ? " AND ip LIKE '" . str_replace('*', '%', $this->ip) . "' ": '');
        $this->criteria .= ($this->countryName ? " AND country_name = '{$this->countryName}' ": '');
		$this->criteria .= ($this->username ? ($this->username == 'Anonymous' ? "AND user_login = '' " : " AND user_login = '{$this->username}' ") : '');
		$this->criteria .= ($this->visitorsPage ? " AND request_uri LIKE '" . str_replace('*', '%', $this->visitorsPage) . "' ": '');

        if($this->emptyReferer) {
            $this->criteria .= " AND referer = '' ";
        } elseif ($this->nonEmptyReferer) {
            $this->criteria .= " AND referer != '' ";
        } else {
            if($this->referer) {
                $this->criteria .= " AND referer LIKE '" . str_replace('*', '%', $this->referer) . "' ";
            }
            if($this->externalReferer) {
                $startsWith = get_option("siteurl");
                $this->criteria .= " AND referer != '' AND referer NOT LIKE '{$startsWith}" . ($this->referer ? str_replace('*', '%', $this->referer) : '%') . "' ";
            }
        }

        $this->criteria .= ($this->os ? " AND os = '{$this->os}' ": '');
		$this->criteria .= ($this->browser ? " AND browser = '{$this->browser}' ": '');
		$this->criteria .= ($this->rss ? " AND rss = '{$this->rss}' ": '');
	}
}

?>