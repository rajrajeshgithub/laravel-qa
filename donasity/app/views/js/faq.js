$(document).ready(function()
{	//-- search validation
	$("#search-form").validate(
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
			searching:{required:true},				
		},
		messages:
		{
			searching:{required:"Search"},		
		}
	});
	//--
	$('.faq a').on('click',function() {
		if($(this).find('i').hasClass('fa-chevron-circle-down')) {	
			$('.panel-title i').removeClass('fa-chevron-circle-up').stop(true,true).addClass('fa-chevron-circle-down');
			$(this).find('i').removeClass('fa-chevron-circle-down').stop(true,true).addClass('fa-chevron-circle-up');
		}
		else {		
			$(this).find('i').removeClass('fa-chevron-circle-up').stop(true,true).addClass('fa-chevron-circle-down');
		}
	});
});