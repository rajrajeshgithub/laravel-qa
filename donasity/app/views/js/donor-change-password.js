// JavaScript Document
$(document).ready(function()
{
	/* ========= strong password validation for registration page ===== */
	
	$.validator.addMethod('IsStrongPassword', function (value, element)
	{
		var Pass=$('#npass').val();
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
			/*if ( Pass.match(/[@]/) == null)
			{
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
	
	
	
	
	
	//-- login Change password form user type 1
	$("#change-password-form").validate(
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
			cpass:{required:true, minlength:6,maxlength:20},
			npass:{required:true, minlength:6,maxlength:20, IsStrongPassword:true},
			rpass:{required:true, minlength:6,maxlength:20, equalTo:"#npass"}
		},
		messages:
		{
			cpass:{required:"Please enter current password", minlength:"Atleast 6 characters required",maxlength:"Password maximum limit reached"},
			npass:{required:"Please enter new password", minlength:"Atleast 6 characters required",maxlength:"Password maximum limit reached" ,IsStrongPassword:"Password not strong"},
			rpass:{required:"Please retype password", minlength:"Atleast 6 characters required",maxlength:"Password maximum limit reached", equalTo:"Password not match"}		
		}
	});
	
	
});