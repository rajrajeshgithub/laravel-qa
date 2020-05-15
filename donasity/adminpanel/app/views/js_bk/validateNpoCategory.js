// JavaScript Document
$(document).ready(function()
{	
	$('.listingmoreBtn i').click(function()
	{
		 $(this).toggleClass("fa-plus fa-minus");
		 $(this).closest('.row').find('.listingmoreSection').slideToggle();
		
	});
	
	var baseUrl = (window.location).href; // You can also use document.URL
	var UrlLastId = baseUrl.substring(baseUrl.lastIndexOf('#') + 1);
	
	if($('#C_NpoCatId').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
		$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
		$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='NpoCategory')
		{
			$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
			$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
			$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
			$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
			
		}
		else
		{
			$('.viewmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
			$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:none' );
			$('.listingmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
			$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:block' );
		}
	}
	
	
	/*$('#addCategory').click(function()
	{
			$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
			$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display-block' );
			window.location(SITEURL+"npocategory#NpoCategory")
			
	})*/
	
	$(".GenerateUrl").click(function()
	{
		var str = $("#C_CategoryNameEn").val();
		str = str.replace(/[^a-z\s]/gi, '');
		str = $.trim(str);				
		str = str.replace(/[_\s]/g, '-');				
		str = str.toLowerCase();
		$("#C_urlFriendlyName").val(str);
			
	})
	
	
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
				C_parentCategory	:{required:true},
				C_categoryCode		:{required:true},
				C_CategoryNameEn	:{required:true},
				<!--C_CategoryNameEs	:{required:true},-->
				C_categoryCode:
				{	
					required: true,
					remote: 
					{
						url:SITEURL+"npocategory/CheckCategoryCode",
						data:
						{
							keyId: function() 
							{
							   return $("#C_NpoCatId").val();
							},
						},
					dataType: 'json',
					async:true,
					cache:false, 
				  }
				},
				
			},
			messages:
			{
				C_parentCategory    :{required:"Please select parent category"},
				C_categoryCode		:{required:"Please enter category code"},
				C_CategoryNameEn	:{required:"Please enter english category name"},
				<!--C_CategoryNameEs   	:{required:"Please enter spanish company name"},-->
				C_categoryCode	:	{required:"Please enter category code",remote: "Category code already in use"},
			}
		});			
});

function GotoPage(pageNo)
{
	$('#pageNumber').val(pageNo);
	$('#formcategory').attr('action', '');
	document.formcategory.submit();
	return false;
}

function Delete_NPOCategory()
{
	var chkValue = [];
	$(':checkbox:checked').each(function(i)
	{
	  chkValue[i] = $(this).val();
	});
	
	if(chkValue!=0)
	{
		if(confirm("Are you sure you want to delete this category detail ?")) 
		{
			document.ListingFormSetion.submit();
		}
	}
	else
	{
		alert("Please select at least one to perform this action?")
		return false;
	}	
}

function Hide_NPOCategory()
{
	var chkValue = [];
	$(':checkbox:checked').each(function(i)
	{
	  chkValue[i] = $(this).val();
	});
	
	if(chkValue!=0)
	{
		if(confirm("Are you sure you want to delete this category detail ?")) 
		{
			$("#ListingFormSetion").attr("action",SITEURL+"npocategory/index/hide-categorydetail");
			document.ListingFormSetion.submit();
		}
	}
	else
	{
		alert("Please select at least one to perform this action?")
		return false;
	}	
}
