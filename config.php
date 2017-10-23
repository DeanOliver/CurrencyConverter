<?php
@date_default_timezone_set("GMT");

define('RATES_XML', 'usd_rates.xml');    // Define name of xml file
define('RESPONSE_XML', 'response.xml');  // Define name of xml file
define('MY_BASE', 'GBP');	             // Define my base currency

define('ISO_URL', 'http://www.currency-iso.org/dam/downloads/lists/list_one.xml');	// ISO for countries URL
define('YQL_URL', 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote?format=xml');	// YQL Mod URL
define('YQL_BASE', 'USD');	// Define the YQL base currency

// Get current time in same format as other requests
$time = time();            // Get time in UNIX EPOCH
$time = date('c', $time);  // Change time to ISO8601 standard
define('TIME', $time);     // Define TIME

// Holds all the valid format types
$cur_codes = array("CAD", "CHF", "CNY", "DKK", "EUR", "HKD", "HUF",
				   "INR", "JPY", "MXN", "MYR", "NOK", "NZD", "PHP",
				   "RUB", "SEK", "SGD", "THB", "TRY", "ZAR");
?>