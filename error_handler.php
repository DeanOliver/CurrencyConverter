<?php
require_once 'config.php';

function conversion_errors($error_code, $format){


   switch($error_code){
      case 1000: $message = "Currency type not recognized"; break;
	   case 1100: $message = "Required parameter is missing"; break;
	   case 1200: $message = "Parameter not recognized"; break;
	   case 1300: $message = "Currency amount must be a decimal number"; break;
	   case 1400: $message = "Error in service"; break;
	     defualt: $message = "Error in service"; break;
   }

   // Create the return XML
   $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
   $xml = "<conv>\n";
      $xml .= "<error>\n";
         $xml .= "<code>" . $error_code . "</code>"."\n";
         $xml .= "<message>" . $message . "</message>"."\n";
      $xml .= "</error>\n";
   $xml .= "</conv>";

   // Write over XML file
   $fh = fopen(RESPONSE_XML, 'w');
   fwrite($fh, $xml);
   fclose($fh);

   if($format == "xml"){
   	header("Content-type: text/xml");
      echo $xml;
      exit;
   }
   if($format == "json"){
   	// Load as simple xml so we can convert it to json
   	$fileForJson = simplexml_load_file(RESPONSE_XML) or die("Error: Cannot create object");
      echo json_encode($fileForJson);
      exit;
   }
   else{
   	// Load as simple xml so we can convert it to json
   	$fileForJson = simplexml_load_file(RESPONSE_XML) or die("Error: Cannot create object");
      echo json_encode($fileForJson);
      exit;
   }
}

function editing_errors($error_code, $method){
require_once 'config.php';

   switch($error_code){
      case 2000: $message = "Method not recognized or is missing"; break;
	   case 2100: $message = "Rate in wrong format or is missing"; break;
	   case 2200: $message = "Currency code in wrong format or is missing"; break;
	   case 2300: $message = "Country name in wrong format or is missing"; break;
	   case 2400: $message = "Currency code not found for update"; break;
	   case 2500: $message = "Error in service"; break;
      case 2600: $message = "Currency already exists"; break;
	     defualt: $message = "Error in service"; break;
   }

   // Create the return XML
   $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
   $xml .= '<method type="' . $method . '">'."\n";
      $xml .= "<error>\n";
         $xml .= "<code>" . $error_code . "</code>"."\n";
         $xml .= "<message>" . $message . "</message>"."\n";
      $xml .= "</error>\n";
   $xml .= "</method>";

   // Write over XML file
   $fh = fopen(RESPONSE_XML, 'w');
   fwrite($fh, $xml);
   fclose($fh);

   echo $xml;
   exit;

}
?>