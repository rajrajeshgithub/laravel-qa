// JavaScript Document
// JavaScript Document
$(document).ready(function()
{
	var input = document.getElementById('location');
	  google.maps.event.addDomListener(input, 'keydown', function(e) { 
		if (e.keyCode == 13) { 
			e.preventDefault(); 
		}
	  });
	var autocomplete = new google.maps.places.Autocomplete(self.$('#location')[0], {types: ['geocode']});
	google.maps.event.addListener(autocomplete, 'place_changed', function() {
    var place = autocomplete.getPlace();
	console.log(place);
	var formatted_address={};
	$.each(place.address_components, function (index,object)
	{
		var name = object.types[0];
		if(name=='administrative_area_level_1')
		{
			formatted_address[name] = object.short_name;
		}
		else
		{
			formatted_address[name] = object.long_name;
		}
	});
		
    $('input[name="Camp_Location_Latitude"]').val(place.geometry.location.lat());
    $('input[name="Camp_Location_Logitude"]').val(place.geometry.location.lng());
	$('input[name="Camp_Location_Zip"]').val(formatted_address['postal_code']); 
   	$('input[name="Camp_Location_Country"]').val(formatted_address['country']); 
    $('input[name="Camp_Location_State"]').val(formatted_address['administrative_area_level_1']); 
    $('input[name="Camp_Location_City"]').val(formatted_address['locality']); 
	$("#location").valid();
});
	
	//-- date picker calling js
	
	
	
	$.validator.addMethod('ProperLocation',function(value,element)
	{
		var city=$('#Camp_Location_City').val();
		var state=$('#Camp_Location_State').val();
		var country=$('#Camp_Location_Country').val();
		if(city!="" && state!="" && country!="" )
		{
			return true
		}else
		{
			return false;
		}
		
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
			donation:{required:true, digits:true},
			uploadImg:{accept:"jpg,png,jpeg,gif"},
			facebookURL:{url:true},
			youtubeURL:{url:true},
			instagramURL:{url:true},
			twitterURL:{url:true}
			
		},
		messages:
		{			
			donation:{required:"Donation amount", digits:"Only digits"},
			uploadImg:{accept: "Only image type jpg/png/jpeg/gif is allowed"},
			facebookURL:{url:"Invalid Url"},
			youtubeURL:{url:"Invalid Url"},
			instagramURL:{url:"Invalid Url"},
			twitterURL:{url:"Invalid Url"}
				
		}
		
	});

});