<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'error_handler.php';

$code = $_POST['code'];
$method = "delete";

/* Variable error checking */
// If the method is not made up of letters and is not equal to 3
if(!ctype_alpha($method)){
   editing_errors(2000, NULL);
}

// Make currency code Upper case to use in XML
$code = strtoupper($code);
// If currency2 is not made up of letters and is not equal to 3
if((!ctype_alpha($code)) || (strlen($code)!=3) || ($code == "")){
   editing_errors(2200, $method);
}

/* Main code functionality */
// Open XML file
$xml = simplexml_load_file(RATES_XML) or editing_errors(2500, $method);

// Cycle through the rates
foreach($xml->conv as $conv){     
   // Get stored currency code
   $stored_code = (string)$conv->curr->code;

   if($code == $stored_code){
     // Must create the node as DOM
     // to remove it from the xml
       $conv = dom_import_simplexml($conv);
       $conv->parentNode->removeChild($conv);

       // Save new changes
       $xml->saveXML(RATES_XML);

             // Create the return XML
      $response_xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
      $response_xml .= '<method type="delete">'."\n";
         $response_xml .= "<at>" . TIME . "</at>"."\n";
         $response_xml .= "<code>" . $code . "</code>"."\n";
      $response_xml .= "</method>";

      // Write over XML file
      $fh = fopen(RESPONSE_XML, 'w') or editing_errors(2500, $method);
      fwrite($fh, $response_xml);
      fclose($fh);

      echo $response_xml;
       return 0;
   
   }
}

editing_errors(2400, $method);
return 0;

?>