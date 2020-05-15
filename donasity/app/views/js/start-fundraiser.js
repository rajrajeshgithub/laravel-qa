$(document).ready(function()
{
	$('.how-it-works a').click(function()
	{
		$('.how-it-works a').toggleClass('content-collapse');
		$('.how-it-works-content').stop(true,false).slideToggle();
	});
	//-- package container height js
	packageContainerHeight();
	$(window).resize(function()
	{
		packageContainerHeight();
	});
});
function packageContainerHeight()
{
	var pkgHeight=0;
	$('.package-content .package-container').each(function()
	{
		var thisHeight=$(this).innerHeight();
		if(thisHeight > pkgHeight)
		{
			pkgHeight=thisHeight;
		}
	});
	if(window.innerWidth > 767)
		$('.package-content .package-column').css('height',pkgHeight);	
	else
		$('.package-content .package-column').css('height','');
}
