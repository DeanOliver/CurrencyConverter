<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'error_handler.php';


$code = $_POST['code'];
$name = $_POST['name'];
$rate = $_POST['rate'];
$locations = $_POST['countries'];
$method = "put";

/* Variable error checking */
// If the method is not made up of letters and is not equal to 3
if(!ctype_alpha($method)){
   editing_errors(2000, NULL);
}

// Check rate is a number in dec form
/* Regex taken from http://stackoverflow.com/questions/4166813/how-to-validate-decimal-numbers-in-php */
$regex = '/^\s*[+\-]?(?:\d+(?:\.\d*)?|\.\d+)\s*$/';
$valid = preg_match($regex, $rate);
if((!$valid) || ($rate == "")){
   // Amount is not a number
   editing_errors(2100, $method);
}

// Make currency code Upper case to use in XML
$code = strtoupper($code);
// If currency2 is not made up of letters and is not equal to 3
if((!ctype_alpha($code)) || (strlen($code)!=3) || ($code == "")){
   editing_errors(2200, $method);
}

$regex = '/^[a-zA-Z ]+$/';
$valid = preg_match($regex, $name);
// If loactions used are not using the alphabet or is empty
if((!$valid) || ($name == "")){
   editing_errors(2300, $method);
}

$regex = '/^[a-zA-Z ,]+$/';
$valid = preg_match($regex, $locations);
// If loactions used are not using the alphabet or is empty
if((!$valid) || ($locations == "")){
   editing_errors(2300, $method);
}

// Test to see if currency code can be updated
$url = 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote?format=xml';
// Load the YQL XML into a variable
$YQL_xml = simplexml_load_file($url) or editing_errors(2500, $method);
$match = 0;
foreach($YQL_xml->resources->resource as $resource){    
   // Check for new currency code in YQL xml 
   if($resource->field[0] == "USD/".$code){
      $match ++;
   }   
}
// If currency code did not match one in the YQL xml
if($match == 0){
   editing_errors(2400, $method);
}

/* Main code functionality */
// Open XML file
$xml = simplexml_load_file(RATES_XML) or editing_errors(2500, $method);

// Cycle through the rates
foreach($xml->conv as $conv){     
   // Get stored currency code
   $stored_code = (string)$conv->curr->code;

   if($code == $stored_code){
       //This currency already exists
   	 editing_errors(2600, $method);
   }
}

// Add new conversion to the xml file
$conv_xml = $xml->addChild('conv');
$conv_xml->addChild('at', TIME);
$conv_xml->addChild('rate', $rate);
$from_xml = $conv_xml->addChild('curr');
$from_xml->addChild('code', $code);
$from_xml->addChild('name', $name);
$from_xml->addChild('loc', $locations);

// Save new changes
$xml->saveXML(RATES_XML);

// Create the return XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$xml .= '<method type="put">'."\n";
   $xml .= "<at>" . TIME . "</at>"."\n";
   $xml .= "<rate>" . $rate . "</rate>"."\n";
   $xml .= "<curr>\n";
      $xml .= "<code>" . $code . "</code>"."\n";
      $xml .= "<name>" . $name . "</name>"."\n";
      $xml .= "<loc>" . $locations . "</loc>"."\n";
   $xml .= "</curr>\n";
$xml .= "</method>";

// Write over XML file
$fh = fopen(RESPONSE_XML, 'w') or editing_errors(2500, $method);
fwrite($fh, $xml);
fclose($fh);

echo $xml;
return 0;

/*
XCD
East Caribbean Dollar
3.92
Anguila, Antigua and Barbuda, Domainica, Grenada, Montserrat

*/


?>