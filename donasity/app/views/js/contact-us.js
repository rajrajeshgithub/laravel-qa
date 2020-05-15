$(document).ready(function()
{
	$("#contact-form").validate(
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
			name:{required:true},
			email:{required:true,email: true},
			message:{required:true},				
		},
		messages:
		{
			name:{required:"Name"},
			email:{required:"Email Address",email:"Enter the valid  email"},
			message:{required:"Message"},		
		}
	});
});