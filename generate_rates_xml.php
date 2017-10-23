<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
@date_default_timezone_set("GMT");

require_once 'config.php';
require_once 'error_handler.php';


   
   // Load the YQL XML into a variable
   $YQL_xml = simplexml_load_file(YQL_URL) or conversion_errors(1400, $format);
   $xname = '//field[@name ="name"]';
   $xprice = '//field[@name ="price"]';
   $xtime = '//field[@name ="utctime"]';  
   $resource = $YQL_xml->resources->resource;


$new_xml = new SimpleXMLElement('<convs/>');
  
// Start my xml file with my base rate
// for loop the size of the xml file
for($i = 0; $i < sizeof($resource->xpath($xname)); $i++){
     
   // hold the code for this loop
   $code = $resource->xpath($xname)[$i];
   $code = substr($code, -3);

   // Get my base rate compared to the YQL base currency 
   if($code == MY_BASE){
      
      $GBP_rate = $resource->xpath($xprice)[$i]; // price
      // Get the $ to the £
      // 1 / base rate to get $ to the £ to 6 DP
      $GBP_rate = bcdiv(1, $GBP_rate, 6);
      $time = $resource->xpath($xtime)[$i]; // utctime 
      // Add new conversion to the xml file
      $conv_xml = $new_xml->addChild('conv');
      $conv_xml->addChild('at', $time);
      $conv_xml->addChild('rate', "1.000000");
      $from_xml = $conv_xml->addChild('curr');
      $from_xml->addChild('code', $code);
      $from_xml->addChild('name', "");
      $from_xml->addChild('loc', ""); 
      $new_xml->saveXML(RATES_XML);    
   }
}

for($i = 0; $i < sizeof($resource->xpath($xname)); $i++){
   $code = $resource->xpath($xname)[$i];    // Get codes from xml
   $code = substr($code, -3);               // Get last 3 characters of the string
   $time =  $resource->xpath($xtime)[$i];   // Get time from xml
   $rate =  $resource->xpath($xprice)[$i];  // Get rate from xml

   if(in_array($code, $cur_codes)){     
      // Convert the rates to the £
      // Inverse of rate1 * rate 2 to 6 DP
      $rate = bcmul($GBP_rate, $rate, 6);

      // Add new conversion to the xml file
      $conv_xml = $new_xml->addChild('conv');
      $conv_xml->addChild('at', $time);
      $conv_xml->addChild('rate', $rate);
      $from_xml = $conv_xml->addChild('curr');
      $from_xml->addChild('code', $code);
      $from_xml->addChild('name', "");
      $from_xml->addChild('loc', "");
   }
   // If code is GBP
   if($code == MY_BASE){
      $code = YQL_BASE; // Set code to USD
      $rate = bcdiv(1, $GBP_rate, 6); // Get $ to the £

      // Add new conversion to the xml file
      $conv_xml = $new_xml->addChild('conv');
      $conv_xml->addChild('at', $time);
      $conv_xml->addChild('rate', $rate);
      $from_xml = $conv_xml->addChild('curr');
      $from_xml->addChild('code', $code);
      $from_xml->addChild('name', "");
      $from_xml->addChild('loc', "");
   }
}
$new_xml->asXML(RATES_XML); //Save xml

$iso_xml = simplexml_load_file(ISO_URL);
$convs = simplexml_load_file(RATES_XML);
// Cycle through the rates
foreach($convs->conv as $conv){     
   // Get stored currency code
   $code = (string)$conv->curr->code;

   // For each code find a country that uses it
   foreach ($iso_xml->CcyTbl->CcyNtry as $CcyNtry) {
      // Get a currency code match
      if($code == $CcyNtry->Ccy){
         // Update XML with the new rate and date
         $conv->curr->name = $CcyNtry->CcyNm; // Add currency name to the xml
         $locations = ""; // Create a empty string to hold all locations
         // Loop through again to get all country names that use the currency
         foreach ($iso_xml->CcyTbl->CcyNtry as $CcyNtry) {  
            if($code == $CcyNtry->Ccy){
              // Get country name
              $cnty_name = $CcyNtry->CtryNm;
              // First letter upper case, the rest lower
              $cnty_name = ucwords(strtolower($cnty_name));
              // push it into the string
              $locations .= $cnty_name . ", ";
            }        
         }
         // Cut last space and comma off
         $locations = substr($locations, 0, -2); 
         // Store locations in xml
         $conv->curr->loc = $locations;
         // Save XML
         $convs->asXML(RATES_XML);

      }  
   }
}

echo "File created";
?> 