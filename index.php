<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
@date_default_timezone_set("GMT");

require_once 'config.php';
require_once 'error_handler.php';

/* Variable error checking */
// Check format first so we can display all other errors in the specified format
if(!isset($_GET['format']))
   conversion_errors(1100, 'xml');

$format = $_GET['format']; // Response type
$format = strtolower($format); // Make lower case 

// Holds all the valid format types
$format_types = array("xml", "json", "");

if(!in_array($format, $format_types)){
   // Format not recognised
   conversion_errors(1200, 'xml');  
}

// Check and define the rest fo the variables
if((!isset($_GET['from'])) || (!isset($_GET['to'])) || (!isset($_GET['amnt']))){
   conversion_errors(1100, $format);
}

$cur1 = $_GET['from'];     // Currency we are converting from
$cur2 = $_GET['to'];       // Currency being converted to
$amount = $_GET['amnt'];   // Amount that is be converted
$cur_match = 0;            //Counts currency matches

// Make currency code Upper case to compare vs XML
$cur1 = strtoupper($cur1);
$cur2 = strtoupper($cur2);

//Check parameters are not empty
if(($cur1 == "") || ($cur2 == "") || ($amount == "")){
   // If parameters are missing
   conversion_errors(1100, $format); 
}

// If currency1 is not made up of letters and is not equal to 3
if((!ctype_alpha($cur1)) || (strlen($cur1)!=3)){
   conversion_errors(1000, $format);
}
// If currency2 is not made up of letters and is not equal to 3
if((!ctype_alpha($cur2)) || (strlen($cur2)!=3)){
   conversion_errors(1000, $format);
}

// Check amount is a number in dec form
/* Regex taken from http://stackoverflow.com/questions/4166813/how-to-validate-decimal-numbers-in-php */
$regex = '/^\s*[+\-]?(?:\d+(?:\.\d*)?|\.\d+)\s*$/';
$valid = preg_match($regex, $amount);
if(!$valid){
   // Amount is not a number
   conversion_errors(1300, $format);
}

/* Main code functionality*/
// Cur1 & cur2 are the same proceed
if($cur1 == $cur2){
  calculate_data($format, $amount, $cur1, $cur2);
  send_data($format);
  return 0;
}

// Is cur 1 or 2 the same as the base currency
if(($cur1 == MY_BASE) || ($cur2 == MY_BASE)){
   // Currency match
   $cur_match ++;
}

// Check rates.xml to see if the exchange
// is on record or if it needs updating.
$convs = simplexml_load_file(RATES_XML) or conversion_errors(1400, $format);

// Get current time
$current_time = time();

// Cycle through the rates
foreach($convs->conv as $conv){
   // Get currency codes that are not the base code
   $stored_code = (string)$conv->curr->code;

    // Check for code match
    if(($cur1 == $stored_code) && ($cur1 != MY_BASE)){
      // +1 when currency is matched
      $cur_match ++;    
      //Get stored time
      $stored_time = (string)$conv->at;
      // Get Epoch time to calculate difference
      $stored_time = strtotime($stored_time);
      // Calculate the hours between last update
      $diff = (($current_time - $stored_time) / (60 * 60));

      // Check if date is over 10 hour old
      if($diff > 10){
        // Update the exchange.xml
        update_xml($cur1, $format);
      }
    }
    
    // Check for code match
    if(($cur2 == $stored_code) && ($cur2 != MY_BASE)){      
      // +1 when currency is matched
      $cur_match ++;    
      //Get stored time
      $stored_time = (string)$conv->at;
      // Get Epoch time to calculate difference
      $stored_time = strtotime($stored_time);
      // Calculate the hours between last update
      $diff = (($current_time - $stored_time) / (60 * 60));

      // Check if date is over 10 hour old
      if($diff > 10){
        // Update the exchange.xml
        update_xml($cur2, $format);
      }     
    }

    //Both currencies are found
    if($cur_match == 2){          
       // Calculate & return data
       calculate_data($format, $amount, $cur1, $cur2);
       send_data($format);
       return 0;
    }
}
// If both currency codes are not found
conversion_errors(1000, $format);
return 0;     


/* Update rates.xml with the new currency compared to the GBP */
function update_xml($to, $format){
   
   $rate_count =0;
   
   // Load the YQL XML into a variable
   $YQL_xml = simplexml_load_file(YQL_URL) or conversion_errors(1400, $format);
   $xname  = '//field[@name ="name"]';    //XPath to field where name=name
   $xprice = '//field[@name ="price"]';   //XPath to field where name=price
   $xtime  = '//field[@name ="utctime"]'; //XPath to field where name=nameutctime
   $resource = $YQL_xml->resources->resource;

   // for loop the size of the xml file
   for($i = 0; $i < sizeof($resource->xpath($xname)); $i++){
     
      // hold the code for this loop
      $code = $resource->xpath($xname)[$i];
      // Take last three characters of the string
      $code = substr($code, -3); 

      // Get my base rate compared to the YQL base currency 
      if($code == MY_BASE){
         $GBP_rate = $resource->xpath($xprice)[$i]; // price
         $time = $resource->xpath($xtime)[$i]; // utctime
         $rate_count ++;      
      }
      // Get specified currency compared to the dollar
      if($code == $to){ 
       $rate = $resource->xpath($xprice)[$i]; // price 
       $rate_count ++;             
      }

      // If both rates are found break.
      if($rate_count == 2){
         break;
      }
   }

  // If currency is USD
  if($to == YQL_BASE){
     // Take GBP conversion and inverse it for USD
     $rate = bcdiv(1, $GBP_rate, 6);
  }
  else{
   // 1 / base rate to get $ to the £ to 6 DP
   $GBP_rate = bcdiv(1, $GBP_rate, 6);
   // Inverse of rate1 * rate 2 to 6 DP
   $rate = bcmul($GBP_rate, $rate, 6);
  }

   // Open XML file
   $convs = simplexml_load_file(RATES_XML) or conversion_errors(1400, $format);

   // Cycle through the rates
   foreach($convs->conv as $conv){     
      $cur_code = (string)$conv->curr->code;

      if($to == $cur_code){
         // Update XML with new date/time & rate
         $conv->at   = $time;
         $conv->rate = $rate;

         // Save XML
         $convs->asXML(RATES_XML);
      }

   }
}

/* Calculates the conversion */
function calculate_data($format, $amount, $cur1, $cur2){
    
   $cur_match = 0; // Used to count currency matches
   $cur1_rate; // Holds the rate for currency1
   $cur2_rate; // Holds the rate for currency2


   // Cur1 & cur2 are the same proceed
   if($cur1 == $cur2){
      // currencies are the same so rate is 1
      $rate = 1;

      create_exchange_xml($rate, $cur1, $cur2, $amount, $format);
      return 0;
   }
   // Is cur 1 or 2 the same as the base currency
   // save time and set rate to 1
   if($cur1 == MY_BASE){
      // Currency match
      $cur_match ++;
      // Store first currency conversion rate
      $cur1_rate = 1;
   }
   else if($cur2 == MY_BASE){
      // Currency match
      $cur_match ++;
      // Store second currency conversion rate
      $cur2_rate = 1;    
   }

   // Open XML file
   $convs = simplexml_load_file(RATES_XML) or conversion_errors(1400, $format);

   // Cycle through all the rates
   foreach($convs->conv as $conv){
      // Get currency codes that are not the base code
      $code = (string)$conv->curr->code;
      // Get the stored rate
      $stored_rate = (string)$conv->rate;

      // Check for code match
      if(($cur1 == $code) && ($cur1 != MY_BASE)){ 
         // Currency match
         $cur_match ++; 
         // Store first currency conversion rate
         $cur1_rate = $stored_rate;
         // Both currencies found
         if($cur_match == 2){
            // Calculate the rate between the two currencies
            // (base / current rate 1) * (current rate 2)
            // Base / rate1 to 6 DP
            $rate = bcdiv(1, $cur1_rate, 6);
            // Inverse of rate1 * rate 2 to 6 DP
            $rate = bcmul($rate, $cur2_rate, 6);

            create_exchange_xml($rate, $cur1, $cur2, $amount, $format);
            return 0;
         }
      }

      // Check for code match
      if(($cur2 == $code) && ($cur2 != MY_BASE)){ 
         // Currency match
         $cur_match ++; 
         // Store first currency conversion rate
         $cur2_rate = $stored_rate;
         // Both currencies found
         if($cur_match == 2){
            // Calculate the rate between the two currencies
            // (base / current rate 1) * (current rate 2)
            // Base / rate1 to 6 DP
            $rate = bcdiv(1, $cur1_rate, 6);
            // Inverse of rate1 * rate 2 to 6 DP
            $rate = bcmul($rate, $cur2_rate, 6);

            create_exchange_xml($rate, $cur1, $cur2, $amount, $format);
            return 0;
         }
      }
   }
   // If both currency codes are not found
   conversion_errors(1000, $format);
   return 0; 
}

/* Create the XML for the current currency conversion */
function create_exchange_xml($rate, $from, $to, $amount, $format){

   // Open XML file
   $convs = simplexml_load_file(RATES_XML) or conversion_errors(1400, $format);

   // Cycle through all the rates
   foreach($convs->conv as $conv){

     $code = (string)$conv->curr->code;

     if($from == $code){

        // Get currency name
        $from_name = (string)$conv->curr->name;
        // Get locations of currency
        $from_loc = (string)$conv->curr->loc;
     }
     
     if($to == $code){
       // Get currency name
       $to_name = (string)$conv->curr->name;
       // Get locations of currency
       $to_loc = (string)$conv->curr->loc;
     }
   }


      $conv_amount = bcmul($rate, $amount, 2);

      // Create the return XML
      $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
      $xml .= "<conv>\n";
         $xml .= "<at>" . TIME . "</at>"."\n";
         $xml .= "<rate>" . $rate . "</rate>"."\n";
         $xml .= "<from>\n";
            $xml .= "<code>" . $from . "</code>"."\n";
            $xml .= "<name>" . $from_name . "</name>"."\n";
            $xml .= "<loc>" . $from_loc . "</loc>"."\n";
            $xml .= "<amt>" . $amount . "</amt>"."\n";
         $xml .= "</from>\n";
         $xml .= "<to>\n";
            $xml .= "<code>" . $to . "</code>"."\n";
            $xml .= "<name>" . $to_name . "</name>"."\n";
            $xml .= "<loc>" . $to_loc . "</loc>"."\n";
            $xml .= "<amnt>" . $conv_amount . "</amnt>"."\n";
         $xml .= "</to>\n";
      $xml .= "</conv>";

      // Write over XML file
      $fh = fopen(RESPONSE_XML, 'w');
      fwrite($fh, $xml);
      fclose($fh);
}

/* Send the data in the specified format */
function send_data($format){

// Load file so that it can be converted to json and displayed        
$fileForJson = simplexml_load_file(RESPONSE_XML) or conversion_errors(1400, $format);
// Load file so that it can be displayed in xml
$file = file_get_contents(RESPONSE_XML) or conversion_errors(1400, $format);

        // Decide which format to send
        if($format == "xml"){ 
           header("Content-type: text/xml");
           echo $file;
           return 0;
        }
        else if($format == "json"){
           echo json_encode($fileForJson);           
           return 0;
        }
        else if($format == ""){ // if just using main page
           echo json_encode($fileForJson);
           return 0;
        }
        else{
           return 0;
        }

}
?> 