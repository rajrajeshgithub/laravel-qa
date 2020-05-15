$(document).ready(function(){
	getStateList(jQuery("#countryAbbr").val(), jQuery("#StateAbbr").val());
	//for show msg on hower start
	$(document).on('mouseover','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','block');
		});
			
		$(document).on('mouseout','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','none')
		});
	//for show msg on hower end
		$("#billing-form").validate(
		{
			
			success: function(element)
			{
				$("#"+element[0].htmlFor).next("b.tooltip").remove();
			},
			errorPlacement: function(error, element)
			{
				element.next("b.tooltip").remove();
				element.after("<b class='tooltip tooltip-top-right'>"+error.text()+"</b>");
			},
			
			rules:
			{
				firstName 			:{required:true},
				lastName	      	:{required:true},
				emailAddress		:{required:true},
				phoneNo	        	:{required:true}
				
			},
			messages:
			{
				firstName    		:{required:"Please enter first Name"},
				lastName			:{required:"Please enter Last Name"},
				emailAddress  		:{required:"Please enter Email Address"},
				phoneNo	    		:{required:"Please enter Phone Number"}
			}
		});
		
		
		$("#payment-detail").validate(
		{
			
			success: function(element)
			{
				$("#"+element[0].htmlFor).next("b.tooltip").remove();
			},
			errorPlacement: function(error, element)
			{
				element.next("b.tooltip").remove();
				element.after("<b class='tooltip tooltip-top-right'>"+error.text()+"</b>");
			},
			
			rules:
			{
				bankName			:{required:true},
				routingNumber       :{required:true},
				accountNumber	    :{required:true},
				checkNumber 		:{required:true},

				
			},
			messages:
			{
				bankName			:{required:"Please enter Bank Name"},
				routingNumber       :{required:"Please enter Routing Number"},
				accountNumber		:{required:"Please enter Account Number"},
				checkNumber    		:{required:"Please enter Check Number"},
			}
		});
		
		
		function validate() 
		{
		if( document.formSection.product.value == "-1" )
		   {
			 alert( "Please select qualification!" );
			 return false;
		   }
		}
		
		jQuery("#phoneNo").mask('(999) 999-9999');
		
		
		
		
		
		
		
		$(document).ready(function(){
			$('input:radio[name="accountType"]').change(function() {
				if ($('input:radio[name="accountType"]').val() == 'Savings'){
						document.getElementById("mycheck").innerHTML = "";
				};
				if ($('input:radio[name="accountType"]').val() == 'Checking'){
						document.getElementById("mycheck").innerHTML = "";
				};
			});
		});
		
		/*html('<span id="error">Please choose gender</span>');*/
		
		$('#submit').click(function(){
			var gender=$('#accountType').val();
				if ($("#accountType:checked").length == 0){
						document.getElementById("mycheck").innerHTML = "Please select Account Type";
					return false;
					}
		});
		
		/*$(function()
		{
			$('#StartDate').datepicker();
			$('#StartDate').datepicker().on('changeDate', function (ev)
			{
				if(ev.viewMode == "days")
				{
					$('#StartDate').datepicker('hide');
					maxDate: jQuery('#StartDate').datepicker("getDate")
	
				}
			});
		})*/
		
		$("#frm_charge").validate( {
			success: function(element) {
				$("#" + element[0].htmlFor).next("b.tooltip").remove();
			},
			errorPlacement: function(error, element) {
				element.next("b.tooltip").remove();
				element.after("<b class='tooltip tooltip-top-right'>" + error.text() + "</b>");
			},
			rules: {
				amountCharge : { required : true, number : true }
			},
			messages: {
				amountCharge : { required : 'Please enter charge amount.', number : 'Please enter only numbers.' }
			}
		});
});
function getStateList(e) {	
    "" != jQuery.trim(e) && jQuery.ajax({
        type: "POST",
        url: SITEURL + "salesubscription/getStateList/" + e + "/" + jQuery("#StateAbbr").val(),
        cache: !1,
        beforeSend: function() {},
        success: function(e) {
            jQuery("#state").attr("disabled", !1).html(e)
        },
        error: function(e) {
            alert(e)
        }
    })
}
