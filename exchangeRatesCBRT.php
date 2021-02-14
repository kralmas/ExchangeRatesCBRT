<?PHP // Ş-UTF8, 14.02.2021
// Mehmet Alper Şen

if (!class_exists('exchangeRatesCBRT')) {
	class exchangeRatesCBRT {
		private $errors = array();
		private $curDate = null;
		private $maxGoBack = 10;

		const _CLP_URL = "http://www.tcmb.gov.tr/kurlar/{_YEAR}{_MONTH}/{_DAY}{_MONTH}{_YEAR}.xml";
		const _CLP_VERSION = array(
			"major" => "1",
			"minor" => "0",
			"build" => "1",
			"status" => "beta", 
			"date" => "2021-02-14"
		);

		public function __construct($options=array()) {
			return $this->setOptions($options);
		}

		public function getClassVersion() {
			return $this::_CLP_VERSION;
		}

		public function setOptions($options=array()) {
			if (isset($options["curDate"])) { $this->curDate = $options["curDate"]; }
			if (isset($options["maxGoBack"])) { $this->maxGoBack = $options["maxGoBack"]; }

			if ($this->curDate == null) {
				$this->curDate = date("Y-m-d");
			}
		}

		private function loadCurrency($options=array()) {
			$currency = "ALL";
			$goBackDate = $this->curDate;
			$currencyList = array();
			$currencyCodeList = array();

			if (is_array($options)) {
				if (array_key_exists("currency", $options) AND !empty($options["currency"])) {
					$currency = $options["currency"];
				}
				if (array_key_exists("date", $options) AND !empty($options["date"])) {
					$goBackDate = $options["date"];
				}
				if (array_key_exists("goBackDate", $options) AND !empty($options["goBackDate"])) {
					$goBackDate = $options["goBackDate"];
				}
			}

			$date2time = strtotime($goBackDate);
			if ($date2time !== false) {
				$y = date('Y', $date2time);
				$m = date('m', $date2time);
				$d = date('d', $date2time);

				$find = array("{_DAY}", "{_MONTH}", "{_YEAR}");
				$replace = array($d, $m, $y);
				$url = str_replace($find, $replace, $this::_CLP_URL);

				$html = @file_get_contents($url);
				if ($html !== false) {
					libxml_use_internal_errors(true);
					$xml = simplexml_load_string($html);
					if ($xml !== false) {
						if (array_key_exists("Currency", $xml)) {
							$currency_count = count($xml->Currency);
							for ($i=0; $i < $currency_count; $i++) {
								if (isset($xml->Currency[$i])) {
									$code = $xml->Currency[$i]->attributes()['Kod']->__toString();
									if ($currency == "ALL" OR $currency == $code) {
										if (!empty($xml->Currency[$i]->BanknoteBuying)) {
											$currencyList[$code] = array(
												'name' => $xml->Currency[$i]->CurrencyName->__toString(),
												'buy' => $xml->Currency[$i]->BanknoteBuying->__toString(),
												'sell' => $xml->Currency[$i]->BanknoteSelling->__toString()
											);
											$currencyCodeList[] = $code;
										}
										else if (!empty($xml->Currency[$i]->ForexBuying)) {
											$currencyList[$code] = array(
												'name' => $xml->Currency[$i]->CurrencyName->__toString(),
												'buy' => $xml->Currency[$i]->ForexBuying->__toString(),
												'sell' => $xml->Currency[$i]->ForexSelling->__toString()
											);
											$currencyCodeList[] = $code;
										}
									}
								}
							}
						}

						$currencyListTotal = count($currencyList);
						if ($currencyListTotal > 0) {
							return array('status' => true, 'date' => $goBackDate, 'list' => $currencyList, 'code_list' => $currencyCodeList);
						}
					}
					else {
					    $errorOutput = "Failed loading XML"."\n";
					    foreach(libxml_get_errors() as $error) {
					        $errorOutput .= "\t".$error->message;
					    }
					    $this->errors[] = $errorOutput;
					}
				}
				else {
					// There may be no pages on some special days such as weekends and public holidays.
					//$this->errors[] = "CBRT URL is return false. Please check URL format.";
				}
			}
			else {
				$this->errors[] = $goBackDate." is not a acceptable date. Please use YYYY-MM-DD format.";
			}

			return array('status' => false, 'date' => $goBackDate, 'list' => $currencyList, 'code_list' => $currencyCodeList);
		}

		public function getCurrency($options=array()) {
			$currencyList = array();
			$currencyCodeList = array();

			$curDate = $this->curDate;
			$maxGoBack = $this->maxGoBack;
			$goBack = $this->maxGoBack;
			$goBackDate = $curDate;

			if (is_array($options)) {
				if (array_key_exists("date", $options) AND !empty($options["date"])) {
					$curDate = $options["date"];
					$goBackDate = $options["date"];
				}
				if (array_key_exists("maxGoBack", $options) AND is_int($options["maxGoBack"])) {
					$maxGoBack = $options["maxGoBack"];
					$goBack = $maxGoBack;
				}
				if (array_key_exists("goBack", $options) AND is_int($options["goBack"])) {
					$goBack = $options["goBack"];
				}
				if (array_key_exists("goBackDate", $options) AND !empty($options["goBackDate"])) {
					$goBackDate = $options["goBackDate"];
				}
			}

			$x = $this->loadCurrency($options);

			if (is_array($x) AND array_key_exists("status", $x) AND $x["status"] == true) {
				return $x;
			}
			elseif ($goBack > 0) {
				// if there is no page, we get the rates of the previous day
				$date2time = strtotime($goBackDate);
				if ($date2time !== false) {
					$options["goBack"] = $goBack - 1;
					$options["goBackDate"] = date('Y-m-d', strtotime('-1 day', $date2time));
					return $this->getCurrency($options);
				}
			}

			return array('status' => false, 'date' => $curDate, 'list' => $currencyList, 'code_list' => $currencyCodeList);
		}

		public function getErrorCount() {
			return count($this->errors);
		}

		public function getErrorMessages($option="last") {
			$total_errors = $this->getErrorCount();
			if ($total_errors > 0) {
				if ($option == "last") {
					return $this->errors[($total_errors-1)];
				}
				elseif ($option == "all") {
					return $this->errors;
				}
			}
			return false;
		}
	}
}