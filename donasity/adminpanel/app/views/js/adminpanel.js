// JavaScript Document
$(document).ready(function()
{
	//-- sidebar toggle
	$('#menu-trigger a').click(function()
	{
		$(this).parents('#header').toggleClass('sidebar-toggled');
		$('#sidebar').toggleClass('sidebarOpen');
		$('#main').toggleClass('full-content-box');
	});
	//-- prifile information
	$('.profile-info').click(function()
	{
		$(this).parents('.profile-menu').toggleClass('toggled');
		$('#sidebar .profile-menu .main-menu').stop(true,false).slideToggle();
	});
	//-- main menu toggle
	$('.sub-menu a').click(function()
	{
		$(this).parent().toggleClass('toggled');
		$(this).parent().find('ul').stop(true,false).slideToggle();
	});
	//-- active main menu
	$('.main-menu li a').click(function()
	{
		$('.main-menu').find('li').removeClass('active');
		$(this).parent('li').addClass('active');
	});
	//--
	$('.signUser a img').click(function()
	{
		$('#topProfileInfo').stop(true,false).slideToggle('fast');
	});
	//--
	/*var mouse_is_inside = false;
		$('.form_content').click(function(){ 
			mouse_is_inside=true; 
		}, function(){ 
			mouse_is_inside=false; 
		});
	
		$("body").mouseup(function(){ 
			if(! mouse_is_inside) $('.form_wrapper').hide();
		});*/
	//-- form hide/show js
	$('.form-toggle-icon').click(function()
	{
		if($(this).find('i').hasClass('fa-plus-square-o'))
		{
			$(this).find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o')
		}
		else
		{
			$(this).find('i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o')
		}
		
		$(this).parents('#content-box').find('.content-section').stop(true,false).slideToggle();
	});
	
	//-- tabing js
	$('.tab-menu li a').click(function()
	{
		var thisClass=$(this).attr('class');
		$('.tab-menu li').removeClass('active-tab');
		$(this).parent().addClass('active-tab');
		$('.tab-panel .tab-content').removeClass('open-tab');
		$('.tab-panel').find('#'+thisClass).addClass('open-tab');
	});	
	
 });