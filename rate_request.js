$(document).ready(function(){

// Calculate amount when the first currancy is changed
$('#currency1').change(function(){

      var currency1 = $("#currency1").val();
      var currency2 = $("#currency2").val();
      var amount = $("#amount1").val();
      var format = "";

      // GET details to index.php
      $.ajax({   
         type: "GET",
         url: "index.php",
         data: {
                amnt   : amount,
                from   : currency1,
                to     : currency2,
                format : format            
               },
         success: function(rates){
            rates = $.parseJSON(rates);
            var new_amount2 = rates["to"]["amnt"]; 
            $( "#amount2" ).val(new_amount2);
         }
      });
   });

// Calculate amount when the second currancy is changed
$('#currency2').change(function(){

      var currency1 = $("#currency1").val();
      var currency2 = $("#currency2").val();
      var amount = $("#amount2").val();
      var format = "";

      // GET details to index.php
      $.ajax({   
         type: "GET",
         url: "index.php",
         data: {
                amnt   : amount,
                from   : currency2,
                to     : currency1,
                format : format            
               },
         success: function(rates){
            rates = $.parseJSON(rates);
        
            var new_amount2 = rates["to"]["amnt"]; 
            $( "#amount1" ).val(new_amount2);
         }
      });
   });

// Calculate the new amount when the first amount is changed 
$('#amount1').change(function(){

      var currency1 = $("#currency1").val();
      var currency2 = $("#currency2").val();
      var amount = $("#amount1").val();
      var format = "";

      // GET details to index.php
      $.ajax({   
         type: "GET",
         url: "index.php",
         data: {
                amnt   : amount,
                from   : currency1,
                to     : currency2,
                format : format            
               },
         success: function(rates){
            rates = $.parseJSON(rates);
        
            var new_amount2 = rates["to"]["amnt"]; 
            $( "#amount2" ).val(new_amount2);
         }
      });
   });

// Calculate the new amount when the second amount is changed 
$('#amount2').change(function(){

      var currency1 = $("#currency1").val();
      var currency2 = $("#currency2").val();
      var amount = $("#amount2").val();
      var format = "";

      // GET details to index.php
      $.ajax({   
         type: "GET",
         url: "index.php",
         data: {
                amnt   : amount,
                from   : currency2,
                to     : currency1,
                format : format            
               },
         success: function(rates){
            rates = $.parseJSON(rates);
        
            var new_amount2 = rates["to"]["amnt"]; 
            $( "#amount1" ).val(new_amount2);
         }
      });
   });
 });