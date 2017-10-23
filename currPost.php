<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'error_handler.php';

$sent_code = $_POST['code'];
$new_rate = $_POST['rate'];
$method = "post";

/* Variable error checking */
// If the method is not made up of letters
if(!ctype_alpha($method)){
   editing_errors(2000, NULL);
}

// Check rate is a number in dec form
/* Regex taken from http://stackoverflow.com/questions/4166813/how-to-validate-decimal-numbers-in-php */
$regex = '/^\s*[+\-]?(?:\d+(?:\.\d*)?|\.\d+)\s*$/';
$valid = preg_match($regex, $new_rate);
if((!$valid) || ($new_rate == "")){
   // Amount is not a number
   editing_errors(2100, $method);
}

// Make currency code Upper case to use in XML
$sent_code = strtoupper($sent_code);
// If currency2 is not made up of letters and is not equal to 3
if((!ctype_alpha($sent_code)) || (strlen($sent_code)!=3) || ($sent_code == "")){
   editing_errors(2200, $method);
}

/* Main code functionality */
// Open XML file
$convs = simplexml_load_file(RATES_XML) or editing_errors(2500, $method);

// Cycle through the rates
foreach($convs->conv as $conv){     
   // Get stored currency code
   $stored_code = (string)$conv->curr->code;

   if($sent_code == $stored_code){
      
      // Store the old rate
      $old_rate = (string) $conv->rate;
      
      // Update XML with the new rate and date
      $conv->at = TIME;
      $conv->rate = $new_rate;
      // Save XML
      $convs->asXML(RATES_XML);


      $locations = $conv->curr->loc;
      $cur_name = $conv->curr->name;

      // Create the return XML
      $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
      $xml .= '<method type="post">'."\n";
         $xml .= "<at>" . TIME . "</at>"."\n";
         $xml .= "<previous>\n";
            $xml .= "<rate>" . $old_rate . "</rate>"."\n";
            $xml .= "<curr>\n";
               $xml .= "<code>" . $sent_code . "</code>"."\n";
               $xml .= "<name>" . $cur_name . "</name>"."\n";
               $xml .= "<loc>" . $locations . "</loc>"."\n";
            $xml .= "</curr>\n";
         $xml .= "</previous>\n";
         $xml .= "<new>\n";
            $xml .= "<rate>" . $new_rate . "</rate>"."\n";
            $xml .= "<curr>\n";
               $xml .= "<code>" . $sent_code . "</code>"."\n";
               $xml .= "<name>" . $cur_name . "</name>"."\n";
               $xml .= "<loc>" . $locations . "</loc>"."\n";
            $xml .= "</curr>\n";
         $xml .= "</new>\n";
      $xml .= "</method>";

      // Write over XML file
      $fh = fopen(RESPONSE_XML, 'w') or editing_errors(2500, $method);
      fwrite($fh, $xml);
      fclose($fh);

      echo $xml;
      return 0;
   }
}

editing_errors(2400, $method);
return 0;

?>