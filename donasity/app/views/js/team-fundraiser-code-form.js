// JavaScript Document
$(document).ready(function()
{
	$("#CodeVerificationForm").validate(
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
			uniquecode:{required:true},			
		},
		messages:
		{
			uniquecode:{required:"Enter your access code"}		
		}
	});
});