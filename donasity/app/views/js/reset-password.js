// JavaScript Document
$(document).ready(function()
{
	
	//-- login validation
	$("#resetForm").validate(
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
			newPassword:{required:true, minlength:6,maxlength:20, IsStrongPassword:true},	
			confirmPassword:{required:true, equalTo:"#newPassword"}			
		},
		messages:
		{
			newPassword:{required:"Password", minlength:"Atleast 6 characters required",maxlength:"Password maximum limit reached" ,IsStrongPassword:"Password not strong"},
			confirmPassword:{required:"Confirm Password", equalTo:"Password does not match"}			
		}
	});
	
	
	
	
	$.validator.addMethod('IsStrongPassword', function (value, element)
	{
		var Pass=$('#newPassword').val();
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
	
	
	
});