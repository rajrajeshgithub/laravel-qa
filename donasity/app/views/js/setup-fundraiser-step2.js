// JavaScript Document
$(document).ready(function()
{
	$('#aboutFundraiser').simplyCountable(
	{
    counter:            '#wordCounter',
    countType:          'characters',
    maxCount:           3000,
    strictMax:          true,
    countDirection:     'down'
	});
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
			aboutFundraiser:{required:true}
		},
		messages:
		{
			aboutFundraiser:{required: "Enter About Fundraiser"}			
		}
	});
});