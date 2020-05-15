// JavaScript Document

$(document).ready(function()
{
	$('.forgot-password').click(function()
	{
		$('#login-form').hide();
		$('#password-recovery-form').show();
	});
	$('.close-window').click(function()
	{
		$('#password-recovery-form').hide();
		$('#login-form').show();
	});
});


	$(document).ready(function()
	{
		// Validation for login form
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
				userName:{required: true},
				password:{required: true,minlength: 6,maxlength: 20}
			},
			messages:
			{
				userName:{required: 'Please enter your username'},
				password:{required: 'Please enter your password'}
			},					
		});
		
		// Validation for recovery form
		$("#password-recovery-form").validate(
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
				fpEmailAddress:{required: true,email: true}
			},
			messages:
			{
				fpEmailAddress:{required: 'Please enter your email address',email: 'Please enter a VALID email address'}
			},
		});
	});			
