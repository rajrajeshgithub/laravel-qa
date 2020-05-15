// JavaScript Document
$(document).ready(function()
{
	// METAL
	jQuery("#Metal").autocomplete(
	{
		source:SITEURL+"product/getMetalList/",
		selectFirst: true,
		select: function( event, ui )
		{
			jQuery("#Metal").val(ui.item.value);
		}
	});

	// GROUP
	jQuery("#productGroup").autocomplete(
	{
		source:SITEURL+"product/getGroupList/",
		selectFirst: true,
		select: function( event, ui )
		{
			jQuery("#productGroup").val(ui.item.value);
		}
	});

	// GEMS STONE
	jQuery("#gemsStone").autocomplete(
	{
		source:SITEURL+"product/getGemsStoneList/",
		selectFirst: true,
		select: function( event, ui )
		{
			jQuery("#gemsStone").val(ui.item.value);
		}
	});
	jQuery("#gemsStone2").autocomplete(
	{
		source:SITEURL+"product/getGemsStoneList2/",
		selectFirst: true,
		select: function( event, ui )
		{
			jQuery("#gemsStone2").val(ui.item.value);
		}
	});
})