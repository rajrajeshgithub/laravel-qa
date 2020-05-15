// JavaScript Document
$(document).ready(function()
{
	/* ========= strong password validation for registration page ===== */
	
	$.validator.addMethod('IsStrongPassword', function (value, element)
	{
		var Pass=$('#Password').val();
		if(Pass!="")
		{
			var Status	= 1;
			//validate character
			if ( Pass.match(/[A-z]/) == null) {
			Status = 0;
			} 
			//validate capital letter
			if ( Pass.match(/[A-Z]/) == null ) {
				Status = 0;
			} 
			
			//validate number
			if ( Pass.match(/\d/) == null) {
				Status = 0;
			}
			 /*if ( Pass.match(/[@]/) == null){
				Status = 0;	 
			}*/
			if(Status == 0)
			{
				return false;	
			}
			else
			{
				return true;	
			}
		}
		else
		{
			return true;	
		}
		
	});
	/* =============================== End ========================== */
	//-- SignUp form validation
	$("#NPORegistrationForm").validate(
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
			FirstName:{required:true},
			LastName:{required:true},
			Companyname:{required:true},
			//einNumber:{required:true, digits:true},
			//category:{required:true},
			Address1:{required:true},
			City:{required:true},
			State:{required:true},
			Country:{required:true},			
			Zip:{required:true, digits:true},
			Phone:{required:true},
			Email:
				{
					required:true,
					email: true,
					remote: 
					{
						url:SITEURL+"ut2/IsDuplicateEmail",
						dataType:'json',
						async:true,						
					}
				},
			Password:{required:true,IsStrongPassword:true},
			ConfirmPassword:{required:true, equalTo:"#Password"}		
		},
		messages:
		{
			FirstName:{required:"First Name"},
			LastName:{required:"Last Name"},
			Companyname:{required:"Company Name"},
			//einNumber:{required:"EIN Number"},
			//category:{required:"Select Category"},
			Address1:{required:"Mailing Address", email:"Invalid Email"},
			City:{required:"City"},
			State:{required:"State"},
			Country:{required:"Select Country"},
			Zip:{required:"Zip Code", digits:"Only Digits"},
			Phone:{required:"Phone Number"},
			Email:{required:"Email Address", email:"Invalid Email",remote:"Email already in use"},
			Password:{required:"Enter Password",IsStrongPassword:"Password is not strong"},
			ConfirmPassword:{required:"Confirm Password", equalTo:"Password does not match"},		
		}
	});
	
	
	
});