// JavaScript Document

$(document).ready(function()
{
	//-- login form validation
		$("#login-form").validate(
		{
			success: function(element)
			{
				$("#"+element[0].htmlFor).next("span.errorClass").remove();
			},
			errorPlacement: function(error, element)
			{
				element.next("span.errorClass").remove();
				element.after("<span class='errorClass'>"+error.text()+"</span>");
			},
			rules:
			{
				email:{required:true,email: true},
				password:{required:true}			
			},
			messages:
			{
				email:{required:"Email",email:"Enter the valid  email"},
				password:{required:"Password"}	
			}
		});

		$("#forget-password-form").validate(
		{
			success: function(element)
			{
				$("#"+element[0].htmlFor).next("span.errorClass").remove();
			},
			errorPlacement: function(error, element)
			{
				element.next("span.errorClass").remove();
				element.after("<span class='errorClass'>"+error.text()+"</span>");
			},
			rules:
			{
				emailId:{required:true,email: true}	
			},
			messages:
			{
				emailId:{required:"Email",email:"Enter the valid email"}
			}
		});
		
		//----------
		$('#login-form-section .link-btn').click(function()
		{
			$('#login-form-section').hide();
			$('#forget-password-section').show();
		});

		$('#forget-password-section .link-btn').click(function()
		{
			$('#forget-password-section').hide();
			$('#login-form-section').show();
		});

});