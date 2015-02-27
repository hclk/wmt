<?php
	/**
	 *  PHP class for downloading CSV files from Google Webmaster Tools.
	 *
	 *  This class does NOT require the Zend gdata package be installed
	 *  in order to run.
	 *
	 *  Copyright 2012 eyecatchUp UG. All Rights Reserved.
	 *
	 *  Licensed under the Apache License, Version 2.0 (the "License");
	 *  you may not use this file except in compliance with the License.
	 *  You may obtain a copy of the License at
	 *
	 *     http://www.apache.org/licenses/LICENSE-2.0
	 *
	 *  Unless required by applicable law or agreed to in writing, software
	 *  distributed under the License is distributed on an "AS IS" BASIS,
	 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 *  See the License for the specific language governing permissions and
	 *  limitations under the License.
	 *
	 *  @author: Stephan Schmitz <eyecatchup@gmail.com>
	 *  @link:   https://code.google.com/p/php-webmaster-tools-downloads/
	 */

	 class GWTdata
	 {
		const HOST = "https://www.google.com";

		public $_language, $_tables, $_daterange, $_downloaded;
		private $_auth, $_logged_in;

		public function __construct()
		{
			$this->_language = "en";
			//$this->_tables = array("TOP_PAGES", "TOP_QUERIES"); removed to just do top queries
			$this->_tables = array("TOP_QUERIES");
			$this->_daterange = array("","");
			$this->_downloaded = array();
			$this->_auth = false;
			$this->_logged_in = false;
		}

		/**
		 *  Sets content language.
		 *
		 *  @param $str     String   Valid ISO 639-1 language code, supported by Google.
		 */
			public function SetLanguage($str)
			{
				$this->_language = $str;
			}

		/**
		 *  Sets features that should be downloaded.
		 *
		 *  @param $arr     Array   Array containing values TOP_PAGES, TOP_QUERIES, or both.
		 */
			public function SetTables($arr)
			{
				if(is_array($arr) && !empty($arr) && sizeof($arr) <= 2) {
					$this->_tables = array();
					for($i=0; $i < sizeof($arr); $i++) {
						if($arr[$i] == "TOP_PAGES" || $arr[$i] =="TOP_QUERIES") {
							array_push($this->_tables, $arr[$i]);
						} else { throw new Exception("Invalid argument given."); }
					}
				} else { throw new Exception("Invalid argument given."); }
			}

		/**
		 *  Sets daterange for download data.
		 *
		 *  @param $arr     Array   Array containing two ISO 8601 formatted date strings.
		 */
			public function SetDaterange($arr)
			{
				if(is_array($arr) && !empty($arr) && sizeof($arr) == 2) {
					if(self::IsISO8601($arr[0]) === true &&
					  self::IsISO8601($arr[1]) === true) {
						$this->_daterange = array(str_replace("-", "", $arr[0]),
						  str_replace("-", "", $arr[1]));
						return true;
					} else { throw new Exception("Invalid argument given."); }
				} else { throw new Exception("Invalid argument given."); }
			}

		/**
		 *  Returns array of downloaded filenames.
		 *
		 *  @return  Array   Array of filenames that have been written to disk.
		 */
			public function GetDownloadedFiles()
			{
				return $this->_downloaded;
			}

		/**
		 *  Checks if client has logged into their Google account yet.
		 *
		 *  @return Boolean  Returns true if logged in, or false if not.
		 */
			private function IsLoggedIn()
			{
				return $this->_logged_in;
			}

		/**
		 *  Attempts to log into the specified Google account.
		 *
		 *  @param $email  String   User's Google email address.
		 *  @param $pwd    String   Password for Google account.
		 *  @return Boolean  Returns true when Authentication was successful,
		 *                   else false.
		 */
			public function LogIn($email, $pwd)
			{
				$url = self::HOST . "/accounts/ClientLogin";
				$postRequest = array(
					'accountType' => 'HOSTED_OR_GOOGLE',
					'Email' => $email,
					'Passwd' => $pwd,
					'service' => "sitemaps",
					'source' => "Google-WMTdownloadscript-0.1-php"
				);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
				$output = curl_exec($ch);
				$info = curl_getinfo($ch);
				curl_close($ch);
				if($info['http_code'] == 200) {
					preg_match('/Auth=(.*)/', $output, $match);
					if(isset($match[1])) {
						$this->_auth = $match[1];
						$this->_logged_in = true;
						return true;
					} else { return false; }
				} else { return false; }
			}

		/**
		 *  Attempts authenticated GET Request.
		 *
		 *  @param $url    String   URL for the GET request.
		 *  @return Mixed  Curl result as String,
		 *                 or false (Boolean) when Authentication fails.
		 */
			public function GetData($url)
			{
				if(self::IsLoggedIn() === true) {
					$url = self::HOST . $url;
					$head = array("Authorization: GoogleLogin auth=".$this->_auth,
						"GData-Version: 2");
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_ENCODING, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
					$result = curl_exec($ch);
					$info = curl_getinfo($ch);
					curl_close($ch);
					return ($info['http_code']!=200) ? false : $result;
				} else { return false; }
			}

		/**
		 *  Gets all available sites from Google Webmaster Tools account.
		 *
		 *  @return Mixed  Array with all site URLs registered in GWT account,
		 *                 or false (Boolean) if request failed.
		 */
			public function GetSites()
			{
				if(self::IsLoggedIn() === true) {
					$feed = self::GetData("/webmasters/tools/feeds/sites/");
					if($feed !== false) {
						$sites = array();
						$doc = new DOMDocument();
						$doc->loadXML($feed);
						foreach ($doc->getElementsByTagName('entry') as $node) {
							array_push($sites,
							  $node->getElementsByTagName('title')->item(0)->nodeValue);
						}
						return $sites;
					} else { return false; }
				} else { return false; }
			}

		/**
		 *  Gets the download links for an available site
		 *  from the Google Webmaster Tools account.
		 *
		 *  @param $url    String   Site URL registered in GWT.
		 *  @return Mixed  Array with keys TOP_PAGES and TOP_QUERIES,
		 *                 or false (Boolean) when Authentication fails.
		 */
			public function GetDownloadUrls($url)
			{
				if(self::IsLoggedIn() === true) {
					$_url = sprintf("/webmasters/tools/downloads-list?hl=%s&siteUrl=%s",
					  $this->_language,
					  urlencode($url));
					$downloadList = self::GetData($_url);
					return json_decode($downloadList, true);
				} else { return false; }
			}

		/**
		 *  Downloads the file based on the given URL.
		 *
		 *  @param $site    String   Site URL available in GWT Account.
		 *  @param $savepath  String   Optional path to save CSV to (no trailing slash!).
		 */
			public function DownloadCSV($site, $savepath=".", $uisite)
			{
				if(self::IsLoggedIn() === true) {
					$downloadUrls = self::GetDownloadUrls($site);
					$filename = parse_url($site, PHP_URL_HOST) ."-". date("Ymd-His"); 
					$tables = $this->_tables;
					foreach($tables as $table) {
						$finalName = "$savepath/$table-$uisite-" . $this->_daterange[0] . ".csv";
						$finalUrl = $downloadUrls[$table] ."&prop=ALL&db=%s&de=%s&more=true";
						$finalUrl = sprintf($finalUrl, $this->_daterange[0], $this->_daterange[1]);
						$data = self::GetData($finalUrl);
						if(file_put_contents($finalName, utf8_decode($data))) {
							array_push($this->_downloaded, realpath($finalName));
						}
					}
				} else { return false; }
			}

		/**
		 *  Validates ISO 8601 date format.
		 *
		 *  @param $str      String   Valid ISO 8601 date string (eg. 2012-01-01).
		 *  @return  Boolean   Returns true if string has valid format, else false.
		 */
			private function IsISO8601($str)
			{
				$stamp = strtotime($str);
				return (is_numeric($stamp) && checkdate(date('m', $stamp),
					  date('d', $stamp), date('Y', $stamp))) ? true : false;
			}
	 }
?>