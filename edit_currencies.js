$(document).ready(function(){

	// When one radio button is clicked deselect the others 
	$("#post_button").click(function(){

	   $('#put_button').attr("checked", false);
	   $('#delete_button').attr("checked", false);

      $('#rate_div').show(1);
      $('#currency_name_div').hide(1);
      $('#countries_div').hide(1);

	});

	$("#put_button").click(function(){

	   $('#post_button').attr("checked", false);
	   $('#delete_button').attr("checked", false);

      $('#rate_div').show(1);
      $('#currency_name_div').show(1);
      $('#countries_div').show(1);

	});

	$("#delete_button").click(function(){

	   $('#put_button').attr("checked", false);
	   $('#post_button').attr("checked", false);

      $('#rate_div').hide(1);
      $('#currency_name_div').hide(1);
      $('#countries_div').hide(1);

	});
   

// Pass across all editing currency data 
$('#submit_button').click(function(){

   // If no radio button selected
   if((! $('#post_button').is(':checked')) && (! $('#put_button').is(':checked'))
                                           && (! $('#delete_button').is(':checked'))){
      alert("Select a method");
      return 0;
   }

   // If POST is selcected
   if($('#post_button').is(":checked")){

      var code = $("#currency_code").val();
      var rate = $("#rate").val();

      // Form validation
      if(code == ""){
      	alert("No Currency code");
      	return 0;
      }
      else if(rate == ""){
      	alert("No conversion rate");
      	return 0;
      }

      // POST details to currPost.php
      $.ajax({   
         type: "POST",
         url: "currPost.php",
         data: {
                code : code,
                rate : rate            
               },
         success: function(response){ 
            $("textarea").val(response);
         }
      });
   }

   // If PUT is selceted
   if($('#put_button').is(":checked")){     

      var code = $("#currency_code").val();
      var name = $("#currency_name").val();
      var rate = $("#rate").val();
      var countries = $("#countries").val();

      // Form validation
      if(code == ""){
      	alert("No Currency Code");
      	return 0;
      }
      else if(name == ""){
      	alert("No Currency Name");
      	return 0;
      }
      else if(rate == ""){
      	alert("No conversion rate");
      	return 0;
      }
      else if(countries == ""){
      	alert("No Countries");
      	return 0;
      }

      // POST details to currPut.php
      $.ajax({   
         type: "POST",
         url: "currPut.php",
         data: {
                code : code,
                name   : name,
                rate     : rate,
                countries : countries            
               },
         success: function(response){ 
            $("textarea").val(response);
         }
      });
   }

   // If DELETE is selected 
   if($('#delete_button').is(":checked")){
  
      var code = $("#currency_code").val();

      // Form validation
      if(code == ""){
      	alert("No Currency Code");
      	return 0;
      }

      // POST details to currDel.php
      $.ajax({   
         type: "POST",
         url: "currDel.php",
         data: {
                code : code            
               },
         success: function(response){ 
            $("textarea").val(response);
         }
      });
   }

});

});