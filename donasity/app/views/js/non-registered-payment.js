// JavaScript Document
$(document).ready(function()
{
	$('.paymentNRNP_popup').magnificPopup({
		type: 'inline'
	});
	
	$('.termsCondition').magnificPopup({
		type: 'inline'
	});
	
	$('.bankaccount').magnificPopup({
		type: 'inline'
	});
	
	$('.terms_of_use_popup').magnificPopup({
		type: 'inline'
	});
	$('.terms_of_service_popup').magnificPopup({
		type: 'inline'
	});
		//-- SignUp form validation
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
			fname:{required:true},
			lname:{required:true},
			streetAddress1:{required:true},
			city:{required:true},
			state:{required:true},
			country:{required:true},
			zipCode:{required:true},
			phoneNumber:{required:true},
			emailAddress:{required:true,email: true},
			bankName:{required:true},
			ddaNumber:{required:true},
			confirmddaNumber:{required:true, equalTo:"#ddaNumber"},
			abaRoutNumber:{required:true},
			checkNumber:{required:true, digits:true}
		},
		messages:
		{
			fname:{required:"First name"},
			lname:{required:"Last name"},
			streetAddress1:{required:"Address"},
			city:{required:"City"},
			state:{required:"State"},
			country:{required:"Select country"},
			zipCode:{required:"Zip code"},
			phoneNumber:{required:"Phone number"},
			emailAddress:{required:"Email Address",email:"Enter the valid  email"},
			bankName:{required:"Bank name"},
			ddaNumber:{required:"DDA number"},
			confirmddaNumber:{required:"Confirm DDA number", equalTo:"DDA number does not match"},
			abaRoutNumber:{required:"ABA routing number"},
			checkNumber:{required:"Check number", digits:"Only digits"}
		}
	});
	
	
	/*==========load state according country=======*/
	$('#country').change(function(){
		$.ajax(
		{
			type:'POST',
			dataType:'json',
			cache :false,
			async:true,
			data:{CountryAB:$(this).val()},
			url:SITEURL+'ut1/getstateajax',
			success:function(data)
			{
				$('#state option').remove();
				for(obj in data)
				{
					var Caption 		= data[obj].State_Name;
					var Captionvalue	= data[obj].State_Value;	
					$('#state').append($('<option>', { 
						value: Captionvalue,
						text : Caption 
					}));
					
				}		
			}
		});	
	});
	/*==========load state according country=======*/
	
	
	/*============== check term condition ==========*/
	$('#billing-form').submit(function(){
		if($("#term").is(':checked')==false)
		{
			alert("If you want to continue, please check term & condition");
			return false;	
		}
		else
		{			
			if ($('#billing-form').valid()) {                   
			   buttnturngray('buttonpay','Processing...','col-xs-12 button-1 large-btn buttonclickedgray');
			} 
			
		}
	});	
	/*============== check term condition ==========*/

});