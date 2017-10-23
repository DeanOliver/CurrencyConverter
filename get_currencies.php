<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$convs = simplexml_load_file('rates.xml') or die("Error: Cannot create object");
$currencies = array();


foreach($convs->conv as $conv){
   
   foreach($conv->curr as $curr){

   	// Cast as a string
    $name = (string)$curr->name;
    $code = (string)$curr->code;
    array_push($currencies, array('code' => $code, 'currency' => $name));  	
   }
}

echo json_encode($currencies);


//Formats simple XML
/* $simpleXml = simplexml_load_file('rates.xml') or die("Error: Cannot create object");

$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($simpleXml->asXML());
$dom->saveXML();
$dom->save('rates.xml');
*/
?>