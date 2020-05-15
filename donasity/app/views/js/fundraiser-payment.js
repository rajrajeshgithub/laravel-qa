// JavaScript Document
$(document).ready(function()
{
	$('.termsofuse_popup').magnificPopup({
		type: 'inline'
	});
	
	$('.termsofservice_popup').magnificPopup({
		type: 'inline'	
	});
		//-- SignUp form validation
	$("#billing-form").validate(
	{
		success: function(element)
		{
			$("#"+element[0].htmlFor).next("b.tooltip").remove();
		},
		errorPlacement: function(errorr, element)
		{
			element.next("b.tooltip").remove();
			element.after("<b class='tooltip tooltip-top-right'>"+errorr.text()+"</b>");
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
			cardNumber:{required:"Card number"},
			sqCode:{required:"Scurity code"},
			expMonth:{required:"Select month"},
			expYear:{required:"Select year"},
			emailAddress:{required:"Email address",email:"Enter the valid  email"},			
		}
	});
});