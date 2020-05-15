// JavaScript Document
$(document).ready(function()
{
		//-- SignUp form validation
	$("#signUp-form").validate(
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
			firstName:{required:true},
			lastName:{required:true},
			mobile:{required:true, digits:true},
			email:{required:true,email: true},
			cardNo:{required:true, digits:true},
			scurityCode:{required:true},
			state:{required:true},
			zipCode:{required:true, digits:true},
			month:{required:true},
			year:{required:true}				
		},
		messages:
		{
			firstName:{required:"First Name"},
			lastName:{required:"Last Name"},
			mobile:{required:"Mobile Number", digits:"Only digits"},
			email:{required:"Email Address",email:"Enter the valid  email"},
			cardNo:{required:"Card Number", digits:"Only digits"},
			scurityCode:{required:"Scurity Code"},
			state:{required:"Select state"},
			zipCode:{required:"Zip Code", digits:"Only digits"},
			month:{required:"Select Month"},
			year:{required:"Select Year"}			
		}
	});

});