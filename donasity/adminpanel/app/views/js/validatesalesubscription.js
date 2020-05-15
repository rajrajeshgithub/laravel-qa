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
		
		getStateList(jQuery("#countryAbbr").val(), jQuery("#StateAbbr").val());
	
   $('input[type="radio"]').click(function() {
       if($(this).attr('id') == 'reccurring') {
            $('#recurring').show();           
       }

       else {
            $('#recurring').hide();   
       }
   });
   
   $('#service').change(function(){	   
	   	var result = $(this).val().split('|');
		$('#itemId').val(result[0]);
		$('#itemCode').val(result[1]);
		$('#itemName').val(result[2]);
		$('#itemPrice').val(result[3]);
		var qty = $('#itemQty').val();	
		$('#itemAmount').val(qty*result[3]);	
	   });
	   
	 $('#itemQty').blur(function(){
		var qty = $(this).val(); 
		var price = $('#itemPrice').val();
		$('#itemAmount').val(qty*price);
		 });
	 $('#itemPrice').blur(function(){
		var qty = $('#itemQty').val(); 
		var price = $('#itemPrice').val();
		$('#itemAmount').val(qty*price);
		 });
		
		
	//for show msg on hower end
	$("#form-step1").validate(
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
				itemCode      		:{required:true},
				itemName 			:{required:true},
				itemPrice			:{required:true},
				itemQty           	:{required:true},
				itemAmount	    	:{required:true},
				firstName 			:{required:true},
				lastName	      	:{required:true},
				emailAddress		:{required:true},
				phoneNo	        	:{required:true}
				
			},
			messages:
			{
				itemCode             :{required:"Please enter Item Code"},
				itemName			:{required:"Please enter Iten Name"},
				itemPrice			:{required:"Please enter Item Price"},
				itemQty       		:{required:"Please enter Item Qty"},
				itemAmount			:{required:"Please enter Item Amount"},
				firstName    		:{required:"Please enter first Name"},
				lastName			:{required:"Please enter Last Name"},
				emailAddress  		:{required:"Please enter Email Address"},
				phoneNo	    		:{required:"Please enter Phone Number"},
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
		
		jQuery("#phoneNo").mask('(999) 999-9999');
				
		
});

function getStateList(e) {	
    "" != jQuery.trim(e) && jQuery.ajax({
        type: "POST",
        url: SITEURL + "salesubscription/getStateList/" + e + "/" + jQuery("#StateAbbr").val(),
        cache: !1,
        beforeSend: function() {},
        success: function(e) {
            jQuery("#state").attr("disabled", !1).html(e)
        },
        error: function(e) {
            alert(e)
        }
    })
}