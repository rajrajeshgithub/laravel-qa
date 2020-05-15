$(document).ready(function(){
	$('input[type=checkbox][name=externalpageLink]').change(function()
	{
		var parentdiv = $(this).parent().parent().parent();
		if($(this).is(':checked'))
		{
			$('#externalid').removeClass('dn');
			$('#internalid').addClass('dn');
			$('#internalpageLink').prop('checked',false);
			
		}
		else
		  $('#externalid').addClass('dn');
	});
	
	
	$('input[type=checkbox][name=internalpageLink]').change(function()
	{
		var parentdiv = $(this).parent().parent().parent();
		if($(this).is(':checked'))
		{
			$('#internalid').removeClass('dn');
			$('#externalid').addClass('dn');
			$('#externalpageLink').prop('checked',false);
		}
		else
		  $('#internalid').addClass('display-none');
	});	
	
	$('input[type=checkbox][name=internalpageLink],input[type=checkbox][name=externalpageLink]').trigger('change');
});