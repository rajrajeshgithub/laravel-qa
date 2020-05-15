$(document).ready(function()
{	
	//-- package container height js
	var pkgHeight=0;
	$('.pricing-content .package-container').each(function()
	{
		var thisHeight=$(this).innerHeight();
		if(thisHeight > pkgHeight)
		{
			pkgHeight=thisHeight;
		}
	});
	$('.pricing-content').find('.package-container').css('height',pkgHeight);
	
	
	$('.keyword').keypress(function(event) {
		if (event.which == 13) {
			SendSearchUrl();
		 }
	});
	
	//-- search validation
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

	//-- donation categories text align js
	
  		categoriesNamePos();
	
	
	$(window).resize(function(){
		categoriesNamePos();
	});
});
function categoriesNamePos()
{
	
	$('.donation-categories-box').each(function()
	{
		$(this).find("img").one("load", function() {
		  var img = $(this);
			var boxHeight = img.height();
			var nameHeight = img.parent().find('.donation-categories-name').height();
			var namePos = (boxHeight-nameHeight)/2;
			img.parent().find('.donation-categories-name').css({"top":namePos});
		}).each(function() {
		  if(this.complete) $(this).load();
		});
		
	
		/*$(this).find("img").on("load", function()
		{
			
			var img = $(this);
			var boxHeight = img.height();
			var nameHeight = img.parent().find('.donation-categories-name').height();
			var namePos = (boxHeight-nameHeight)/2;
			img.parent().find('.donation-categories-name').css({"top":namePos});
		});*/
	});
}

function validateDonateNow()
{
	var title = $.trim($('.keyword').val());
	var element = $('.keyword');
	if(title == "")
	{
		SetError(element,'Please enter search text');
		//return false;
	}	
	else
	{
		RemoveError(element);
		return true;
	}	
}

function SendSearchUrl()
{
	if(validateDonateNow()){
		var Keyword		= $('.keyword').val();
		
		url	= SITEURL+"non-profits-search";
		
		if(Keyword != "")
		{
			url	= url+"?filter[keyword]="+Keyword;
		}
		window.location.href=url;
	}
}