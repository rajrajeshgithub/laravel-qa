// JavaScript Document
$(document).ready(function()
{
		$("#form-section").validate(
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
				email:{required:true},
				phone:{required:true},
				
			},
			messages:
			{
				fname:{required:"First name"},
				lname:{required:"Last name"},
				email:{required:"Email address"},
				phone:{required:"Phone number"}				
			}
		});
});