// JavaScript Document
$(document).ready(function()
{
	$('.forget-password').click(function()
	{
		$('.login-section').hide();
		$('.login-heading').hide();
		$('.forget-password-heading').show();
		$('.forget-password-section').show();
	});
	$('.go-back').click(function()
	{
		$('.forget-password-section').hide();
		$('.forget-password-heading').hide();
		$('.login-heading').show();
		$('.login-section').show();
	});
	//-- login validation
	$("#login-form").validate(
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
			email:{required:true,email: true},
			password:{required:true},				
		},
		messages:
		{
			email:{required:"Email Address",email:"Enter the valid  email"},
			password:{required:"Password"},			
		}
	});
	$("#forget-password-form").validate(
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
			email:{required:true,email: true},			
		},
		messages:
		{
			email:{required:"Email Address",email:"Enter the valid  email"},		
		}
	});
});