$(document).ready(function(){

      // POST details to get_currencies.php
      $.ajax({   
         type: "POST",
         url: "get_currencies.php",
         data: { },
         success: function(currencies){ 
            currencies = $.parseJSON(currencies);
            for(var i = 0; i < currencies.length; i++){
               $( "#currency1" ).append( "<option value='" + currencies[i]["code"] + "'>" + currencies[i]["currency"] + "</option>" );
               $( "#currency2" ).append( "<option value='" + currencies[i]["code"] + "'>" + currencies[i]["currency"] + "</option>" );

            }
         }
      });

$("#convert_button").click(function(){

   $('#change_currencies').hide(1);
   $('#convert_currency').show(1);   
   $('#convert_button').hide(1);
   $('#action_button').show(1);
});


$("#action_button").click(function(){

   $('#convert_currency').hide(1);
   $('#change_currencies').show(1);   
   $('#action_button').hide(1);
   $('#convert_button').show(1);

});

});

