// JavaScript Document
$(document).ready(function()
{
	//-- read more js
	/*$('.result-description .read-more').click(function()
	{
		$(this).hide();
		$(this).parent('.result-description').css('height','auto');
	});*/
	
	$('.read-more').click(function()
	{		
		$(this).siblings().removeClass('dn');		
		$(this).hide();
	});
	
	$('#titlesearch').click(function(){
		var title = $.trim($('#keyword').val());
		var element = $('#keyword');
		if(title == "")
		{
			SetError(element,'Please enter search text');
			return false;
		}	
		else
		{
			RemoveError(element);
			SendUrl();
		}
	});
	
	$('#locationsearch').click(function(){
		var location	= $.trim($('#location').val());	
		if(location == "")
		{
			SetError($('#location'),'Please enter location');
			return false;
		}
		else
		{
			RemoveError($('#location'));
			SendUrl();
		}
	});
	
	
	//-- category list add/remove js
	$('.filter-section .category-list li a').click(function()
	{	
		if($(this).parent('li').hasClass('active-category'))
		{
			return false;
		}
		else
		{	
			$(this).parent('li').addClass('active-category');
			var thisName=$(this).text();
			$('.add-list ul').append('<li><span>'+thisName+'<a href="javascript:void(0)" class="remove-btn" title="Click here to remove to this category"><i class="fa fa-times"></i></a></span></li>')
			$('.add-list ul li span a.remove-btn').on("click", removeBtnClick);
		}
	});
});

function removeBtnClick()
{
	var thisVal=$(this).parent().text();
	$(this).parents('li').remove();
	console.log($('.filter-section .category-list'));
	$('.filter-section .category-list li.active-category').each(function(){
		var categoryName=$(this).text();
		if(thisVal == categoryName)
		{
			$(this).removeClass('active-category');
		}
	});
}

function SetError(element,ErrMsg)
{
	element.next("span.errorClass").remove();
	element.after("<span class='errorClass'>"+ErrMsg+"</span>");
	element.addClass('error');	
	return false;	
}

function RemoveError(element)
{
	element.next("span.errorClass").remove();
	element.removeClass('error');	
}


