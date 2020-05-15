// JavaScript Document

$(document).ready(function()
{	
	$(document).on('mouseover','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','block');
	});
		
	$(document).on('mouseout','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','none')
	});
		
	
		
	
	/*$('.viewmoreBtn i').click(function()
	{
		 $(this).toggleClass("fa-plus fa-minus");
		 $(this).closest('.row').find('.viewmorSection').slideToggle();
		
	});*/
	
	/*$('.tabSection .tabs').click(function()
	{
		var tabId = $(this).attr('id');
		$('.tabSection').find('.activeTab').removeClass('activeTab');
		$(this).addClass('activeTab');
		$('.tabContainer .tabBox').addClass('dn');
		$('.tabContainer .'+tabId+'Section').removeClass('display-none');
	})*/
	
	$('.tab-section a').click(function()
	{
		$('.tab-section a').removeClass('selectedTab');
		$(this).addClass('selectedTab');
		var thisHref=$(this).attr('href');
		$('.tab-section .tabInfo').removeClass('open-tab');
		$('.tab-section').find('div'+thisHref).addClass('open-tab');
		return false;
	});
	
	$("#formSection").validate(
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
				templateName	:{required:true},
				receiver		:{email:true},
				receiverCc		:{email:true},
				receiverBcc		:{email:true},
				sender			:{required:true,email:true},
			},
			messages:
			{
					templateName	:	{required:"Please enter template name"},
					receiver		:	{email:"Please enter valid email"},
					receiverCc		:	{email:"Please enter valid email"},
					receiverBcc		:	{email:"Please enter valid email"},
					sender			:	{required:"Please enter sender email id",email:"Please enter valid email"}
			}
		});			
});

