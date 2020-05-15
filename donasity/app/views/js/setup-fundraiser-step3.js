// JavaScript Document
$(document).ready(function()
{
	$('.tab-section .tabOption').click(function()
	{
		$('.tab-section .tabOption').removeClass('selectedTab');
		$(this).addClass('selectedTab');
		var thisHref=$(this).attr('href');
		$('.tab-section .tabInfo').removeClass('open-tab');
		$('.tab-section').find('div'+thisHref).addClass('open-tab');
		return false;
	});
	
	$("#form-section").validate(
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
			uploadImg:{accept:"jpg,png,jpeg,gif"}
		},
		messages:
		{
			uploadImg:{accept: "Only image type jpg/png/jpeg/gif is allowed"}			
		}
	});
	$('#uploadImgbtn').click(function()
	{
		$('#form-section').attr('action', SITEURL+'setup_fundraiser/UploadImage');
		$('#form-section').submit();
	});
	$('#UploadVideobtn').click(function()
	{
		$('#form-section').attr('action', SITEURL+'setup_fundraiser/UploadVideo');
		$('#form-section').submit();
	});
	$('#step3submit').click(function()
	{		
		$('#form-section').attr('action', SITEURL+'setup_fundraiser/Update_fundraiser_step3');
		$('#form-section').submit();
	});
});