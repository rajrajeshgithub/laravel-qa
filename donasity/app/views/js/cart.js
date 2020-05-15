// JavaScript Document
$(document).ready(function()
{
	$('.transactionfee_popup').magnificPopup({
		type: 'inline'
	});
	
	$('.learnmore_popup').magnificPopup({
		type: 'inline'
	});	
	
	$('.nonRegwhatisthis_popup').magnificPopup({
		type: 'inline'
	});
	$('.commentFundraiser_popup').magnificPopup({
			type:'inline'
	});
	

	$('.reoccurring-btn').click(function()
	{
		$(this).parents('.donors-details').find('.reoccurring-box').toggle();
	});
	//--set time plus and minus js
	var timeVal=0;
	$('.set-time-numbers .plus-btn').click(function()
	{
		timeVal=parseInt($(this).parents('.set-time-numbers').find('.year').val());
		timeVal= (isNaN(timeVal))?0:timeVal;
		timeVal=timeVal+1;
		//$(this).parents('.set-time-numbers').find('#setTime').val(timeVal);
		$(this).parents('.set-time-numbers').find('.year').val(timeVal);
		Updateyear($(this));
	});
	$('.set-time-numbers .minus-btn').click(function()
	{
		timeVal=parseInt($(this).parents('.set-time-numbers').find('.year').val());
		timeVal=timeVal-1;
		timeVal=(timeVal<0)?0:timeVal;
		//$(this).parents('.set-time-numbers').find('#setTime').val(timeVal);
		  $(this).parents('.set-time-numbers').find('.year').val(timeVal);
		  Updateyear($(this));
	});
	
	
	
	$(".commentOpener").click(function(){
		
		$(this).closest('div').next('.cartComment').toggle();
		})
		
	//update amount
	$('.amt').blur(function(){
		var IndexKey	= $(this).attr('id');	
		var Amount		= $(this).val();
		
		/*Call SHOW Function here */
		ShowOverlay();
		$.ajax(
		{
			type:"POST",
			url:SITEURL+"donation_checkout/UpdateAmount",
			data:
				{
					indexkey:IndexKey,
					amount:Amount
				},
			dataType:'JSON',
			beforeSend: function() {
				$('#loadingdiv').css('display','block');
			},
			success:function(data)
			{
				if(data.Status == 1)
				{
					UpdateTotalHtml(data);
					/*Call HIDE Function here */
					HideOverlay();
				}
			}
		})
	});
	//===============end====================
	
	
	//update recurring mode
	$(".mode").click(function(){
		var IndexKey	= $(this).attr('id');	
		var Mode		= $(this).val();
		ShowOverlay();
		$.ajax(
		{
			type:"POST",
			url:SITEURL+"donation_checkout/UpdateRecurringMode",
			data:
				{
					indexkey:IndexKey,
					mode:Mode
				},
			dataType:'JSON',
			success:function(data)
			{
				if(data.Status == 1)
				{
					HideOverlay();
					//window.location.reload();
				}	
			}
		})
		
		
		
		
	});
	
	
	//============end===========================
	
	
	//update year
	/*$(".plus-btn, .minus-btn").blur(function(){
		var IndexKey	= $(this).parent().parent().find('.year').attr('id');	
		var Year		= $(this).parent().parent().find('.year').val();
		
		$.ajax(
		{
			type:"POST",
			url:SITEURL+"cart/UpdateYear",
			data:
				{
					indexkey:IndexKey,
					year:Year
				},
			dataType:'JSON',
			success:function(data)
			{
				if(data.Status == 1)
				{
					//window.location.reload();
				}
			}
		})
	});*/
	//============end===========================
	
	//update include transaction fee
	$('#TransactionFeePaidByUser').click(function(){
		var Include	= 1;
		ShowOverlay();
		if($(this).is(':checked'))
		{
			var Include	= 0;
		}
		$.ajax(
		{
			type:"POST",
			url:SITEURL+"donation_checkout/UpdateIncludeTransactionFee",
			data:
				{
					includeStat:Include
				},
			dataType:'JSON',
			success:function(data)
			{
				if(data.Status == 1)
				{
					UpdateTotalHtml(data);
				}
				HideOverlay();
			}
		})
	});
	//============end===========================
	
	/*============cart validation===========================*/
	$('.continue-donate-btn').click(function(){
		var Status = 1;
		var amoutstatus=1;
		$('.amt').each(function(){
			var tab	= $(this).attr('tabindex');
			element	= $('.'+tab);
			amount	= element.val();
			if(amount == "" || parseFloat(amount) <= 0)
			{
				Status	= 0;
				SetError(element,'Please enter amount');	
			}
			else
			{
				RemoveError(element);
			}
		});
		var amountstring=$('.youpay').text().split('$');
		var totalamount=amountstring[1];
		if(totalamount<20)
		{
			amoutstatus=0;
			alert("Your donations must be greater than or equal to $20");
		}
		if(Status==1 && amoutstatus==1)
		{
			$(this).css({'pointer-events':'none'})
			$(this).addClass('button-2');
			$(this).html('Processing...');
			location.href=SITEURL+"donation/verify_user";	
		}
	});
	/*============end===========================*/
});

function SetError(element,ErrMsg)
{
	element.next("span.errorClass").remove();
	//element.after("<span class='errorClass'>"+ErrMsg+"</span>");
	element.after("<b class='tooltip tooltip-top-right'>"+ErrMsg+"</b>");
	element.addClass('error');	
	return false;	
}

function RemoveError(element)
{
	element.next("span.errorClass").remove();
	element.removeClass('error');	
}

function RemoveItem(IndexKey)
{
	if(confirm("Are you sure, you want to remove this item ?"))
	{
		location.href=SITEURL+"donation_checkout/RemoveItem/"+IndexKey;	
	}	
}

function UpdateTotalHtml(data)
{
	
	$('.NPONRSubTotal').html("$"+number_format(data.NPONRSubTotal_Payable, 2, ".", ","));	
	FUNDARISER_NPORSubTotal_Payable=parseFloat(data.NPORSubTotal_Payable)+parseFloat(data.FUNDARISERSubTotal_Payable);
	$('.NPORSubTotal').html("$"+number_format(FUNDARISER_NPORSubTotal_Payable, 2, ".", ","));
	$('.total').html("$"+number_format(data.Total, 2, ".", ","));
	$('.transfee').html("$"+number_format(data.TransactionFee, 2, ".", ","));
	$('.totaldonation').html("$"+number_format(data.TotalDonation, 2, ".", ","));
	$('.youpay').html("$"+number_format(data.TotalPay, 2, ".", ","));	
}

function Updateyear(obj)
{
	var IndexKey	= obj.parent().parent().find('.year').attr('id');	
	var Year		= obj.parent().parent().find('.year').val();
	ShowOverlay();
	$.ajax(
	{
		type:"POST",
		url:SITEURL+"donation_checkout/UpdateYear",
		data:
			{
				indexkey:IndexKey,
				year:Year
			},
		dataType:'JSON',
		success:function(data)
		{
			if(data.Status == 1)
			{
				//window.location.reload();
				HideOverlay();
			}
		}
	})	
}
function ShowOverlay()
{
	
	/*IMPORTANT - Redefine this function to avpoide use fo FORM HIde*/
	$('.overlay').show();
	//$('#cart-form').hide();
}
function HideOverlay()
{
	$('.overlay').hide();
	//$('#cart-form').show();
}

	function updateComment(objcmt)
	{	
		var IndexKey='';
		var comment='';
		var anonymousName=0;
		var clickIdArray=$("#"+objcmt).attr('id').split("_");         
		comment = $("input[id ^= 'FUNDARISER_"+clickIdArray[1]+"']").val();		  
		if($("input[id ^= 'FUNDARISER1_"+clickIdArray[1]+"']").prop('checked')==true)
		{
			anonymousName = $("input[id ^= 'FUNDARISER1_"+clickIdArray[1]+"']").val();	
		}
		IndexKey = "FUNDARISER|"+clickIdArray[1];			
		/*if($("input[id ^= 'FUNDARISER_"+clickIdArray[1]+"']").val()==$("input[id ^= 'COMMENTHIDE_"+clickIdArray[1]+"']").val())
		{
			return false;
		}*/
		ShowOverlay();
		$.ajax(
		{			
			type:"POST",
			url:SITEURL+"donation_checkout/UpdateAnonymousComment",
			data:
				{
					indexkey:IndexKey,
					KeepAnonymous:anonymousName,
					comment:comment
				},
			dataType:'text',
			beforeSend: function() {
				$('#loadingdiv').css('display','block');
			},
			success:function(data)
			{
				if(data== 1)
				{
					HideOverlay();
				}
			}
		});		
	}
