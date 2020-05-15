// JavaScript Document
$(document).ready(function()
{
	/*popup terms*/
	$('.terms_of_use_popup').magnificPopup({
		type: 'inline'
	});
	$('.terms_of_service_popup').magnificPopup({
		type: 'inline'
	});
	/*-------*/
	//-- SignUp form validation
	//
	//{
	$("#payment-form").validate(
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
			ccName:{required:true},
			cardNumber:{required:true},
			sqCode:{required:true},
			expMonth:{required:true},
			expYear:{required:true},
			emailAddress:{required:true,email: true},
		},
		messages:
		{
			ccName:{required:"Name on card"},
			cardNumber:{required:"Card Number"},
			sqCode:{required:"Security Code"},
			expMonth:{required:"Select Month"},
			expYear:{required:"Select Year"},
			emailAddress:{required:"Email Address",email:"Enter a valid  email"},			
		}
	});
	$('#payment-form').on('submit', function () { 
        if ($('#payment-form').valid()) {                   
           buttnturngray('buttonpay','Processing...','col-xs-12 button-1 large-btn buttonclickedgray');
        } 
	
	
	
    });

	
	
});
