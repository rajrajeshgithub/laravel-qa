$(document).ready(function(){
	//for show msg on hower start
	$(document).on('mouseover','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','block');
		});
			
		$(document).on('mouseout','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','none')
		});
		
		$.validator.addMethod('accoutChk',function()
		{
			
			if ($("#accountType:checked").length == 0)
			{
				$("#mycheck").html("(Please Select Account Type)");
				return false;
				
			}else
			{
				return true;
			}
			
		});
	//for show msg on hower end
	$("#form-step2").validate(
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
				bankName			:{required:true},
				routingNumber       :{required:true},
				accountNumber	    :{required:true},
				checkNumber 		:{required:true},
				accountType			:{accoutChk:true},

				
			},
			messages:
			{
				bankName			:{required:"Please enter Bank Name"},
				routingNumber       :{required:"Please enter Routing Number"},
				accountNumber		:{required:"Please enter Account Number"},
				checkNumber    		:{required:"Please enter Check Number"},
			}
		});
		
		
		function validate() 
		{
		if( document.formSection.product.value == "-1" )
		   {
			 alert( "Please select qualification!" );
			 return false;
		   }
		}
		
		
		
		
		
		
		
		
		
		$(document).ready(function(){
			$('input:radio[name="accountType"]').change(function() {
				if ($('input:radio[name="accountType"]').val() == 'Savings'){
						document.getElementById("mycheck").innerHTML = "";
				};
				if ($('input:radio[name="accountType"]').val() == 'Checking'){
						document.getElementById("mycheck").innerHTML = "";
				};
			});
		});
		
		/*html('<span id="error">Please choose gender</span>');*/
		
		$('.submit').click(function(){
			var gender=$('#accountType').val();
				if ($("#accountType:checked").length == 0){
						document.getElementById("mycheck").innerHTML = "Please select Account Type";
					return false;
					}
		});
		
		/*$(function()
		{
			$('#StartDate').datepicker();
			$('#StartDate').datepicker().on('changeDate', function (ev)
			{
				if(ev.viewMode == "days")
				{
					$('#StartDate').datepicker('hide');
					maxDate: jQuery('#StartDate').datepicker("getDate")
	
				}
			});
		})*/
});