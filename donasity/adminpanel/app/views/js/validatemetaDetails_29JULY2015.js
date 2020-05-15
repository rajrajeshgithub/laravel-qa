// JavaScript Document

$(document).ready(function()
{	
	AddEditor($('#rowcount').val());
	AddEditor2($('#rowcount').val());
	
	$(document).on('mouseover','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','block');
	});
		
	$(document).on('mouseout','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','none')
	});
		
	/*$('button[type=button][name=buttonenglish]').click(function(){
		$('#english').show();
		$('#spanish').hide();
		
	})	
	
	$('button[type=button][name=buttonspanish]').click(function(){
		$('#english').hide();
		$('#spanish').show();
		
	})	*/
	
	/*$('.tabSection .tabs').click(function()
	{
		var tabId = $(this).attr('id');
		$('.tabSection').find('.activeTab').removeClass('activeTab');
		$(this).addClass('activeTab');
		$('.tabContainer .tabBox').addClass('display-none');
		$('.tabContainer .'+tabId+'Section').removeClass('display-none');
	})*/
	
	$('.tab-section a').click(function()
	{
		$('.tab-section a').removeClass('selectedTab');
		$(this).addClass('selectedTab');
		var thisHref=$(this).attr('href');
		$('.tab-section .tabInfo').removeClass('open-tab');
		$('.tab-section').find('div'+thisHref).addClass('open-tab');
		return false;
	});
	
	//To show hide defalut content in Additional Page meta Details
	$('#button-2-addMore').bind('click',function()
	{
		var value=$('#addDetail ul').length;
		if(value>0)
		{
			$('#contentMessage').hide();
		}
	});
	
	$('button[type=button][name=addMore]').click(function(){
		Body		= CreateHtml();
		$('.metaSection').after(Body);
		AddEditor($('#rowcount').val());
		AddEditor2($('#rowcount').val());
		/*if($('.metaSection :last').hasClass('display-none'))
		{
			var EmptyHtml = $('.metaSection :last');
			EmptyHtml.removeClass('display-none');
			EmptyHtml.prev().remove();
		}
		else
		{
			var EmptyHtml = $('.metaSection :last').clone();
			$(EmptyHtml).find('textarea').val('');
			$(EmptyHtml).find(':text').val('');
			$(EmptyHtml).find('#metaPageContentId').val('');
			$('.metaSection :last').after(EmptyHtml)
		}*/
	});
	
	
	$('.metaSection .removeIcon').on('click',function(){
		if($('.metaSection').length>1 && typeof $(this).attr('rid')=="undefined" && confirm("Are you sure, you want to remove this detail ?"))
			$(this).parent().parent().parent().remove()
		else
		{
			if(typeof $(this).attr('rid')!="undefined")
			{
				var ridvalue  =  $(this).attr('rid');
				ridvalue      =  ridvalue.split(",")
				if(ridvalue.length==2)
				confirmDeleteMetaContent($.trim(ridvalue[0]),$.trim(ridvalue[1]));
			}
		}
	});
	
	
	
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
					pageName:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"programmedpages/CheckPageName",
							data: 
						{
							keyId: function() 
							{
							   return $("#keyId").val();
							},
						},	
						dataType: 'json',
						async:true,
						cache:false, 
					  }
					},
					pageNameSpanish:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"programmedpages/CheckPageName",
							data: 
						{
							keyId: function() 
							{
							   return $("#keyId").val();
							},
						},	
						dataType: 'json',
						async:true,
						cache:false, 
					  }
					},
					/*pageKeyword:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"metadetail/CheckPageKeyword",
							data: 
						{
							keyId: function() 
							{
							   return $("#keyId").val();
							},
						},	
						dataType: 'json',
						async:true,
						cache:false, 
					  }
					},*/
					
					
			},
			messages:
			{
				pageName		: {required:"Please enter page name",remote: "Page name already in use"},
				pageNameSpanish	: {required:"Please enter page name",remote: "Page name already in use"},
				pageKeyword		: {required:"Please enter page keyword",remote: "Page keyword already in use"}
			},
		});
		
		// For Adding more Additional Information
		var count=2;
		$("#button-2-addMore").click(function()
		{	
			//alert(count);
			var id= $("#keyId").val();
			//var Cid= $("#metaContentId").val();
			html='<ul>';
			html+='<li class="dataRow">';
			html+='<input type="hidden" name="metaPageValuesId[]" value="'+id+'" />';
			html+='<input type="hidden" name="metaPageContentId[]" value="" />';
			html+='<label>Caption</label><input type="text" name="caption[]" id="caption'+count+'" />';
			html+='</li>';
			html+='<li class="dataRow">';
			html+='<label>Detail</label><textarea name="detail[]" id="detail'+count+'" rows="6"></textarea>';
			html+='</li>';
			html+='<li class="dataRow">';
			html+='<label>Description</label><textarea name="description[]" id="description'+count+'" rows="6"></textarea>';
			html+='</li>';
			//html+='<li class="dataRow">';
			html+='<input type="button" name="button4-Remove" id="button4-Remove'+count+'" class="button4 manageButton" value="Remove" />';
			//html+='</li>';
			html+='</ul>';
			$('#addDetail').append(html);
			$('#button4-Remove'+count).bind('click',removemetaDetail);
			count++;
		})
		
});
function confirmDeleteMetaPages(PageId)
{
	if(confirm("Are you sure you want to delete this Meta page ?")) 
	{
		window.location = SITEURL+'programmedpages/delete/'+PageId;
	} else {
		return false;
	}
}

function DeleteMetaContent(Id,PageId)
{
	if(Id!='' && PageId!='')
	{
		if(confirm("Are you sure you want to delete this Meta Content ?"))
		{
			location.href = SITEURL+"programmedpages/deleteAdditionalContent/"+Id+"/"+PageId;
		}
		else 
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}
function removemetaDetail()
{
	if(confirm("Are you sure, you want to remove this detail ?"))
	{
		$(this).parent().remove();
		var value1=$('#addDetail ul').length-1;
		if(value1==0)
		{
			$('#contentMessage').show();
		}
	}
}

function CreateHtml()
{
	var id= $("#keyId").val();
	idcount	= RowCount();
	Str	= '<input type="hidden" name="metaPageValuesId[]" value="'+id+'" /><input type="hidden" name="metaPageContentId[]" value="" /><div class="metaSection"><div class="userfrmDiv overflow-hidden"><div class="form-group productfrmDiv editorPanel pull-left clear-none"><label class="form-label editorCaption" for="base_sku">Caption:</label><input type="text" class="form-control pull-left" id="caption" name="caption[]" placeholder="Caption" ></div><div class="form-group productfrmDiv detailSection1 pull-left clear-none"><label class="form-label" for="default_item_code">Caption Spanish:</label><textarea id="captionspanish " name="captionspanish[]" placeholder="Caption Spanish" ></textarea></div></div><div class="userfrmDiv overflow-hidden"><div class="pull-left ml-10 detailSection"><div class="form-group editorPanel"><label class="form-label" for="base_sku">Page Meta Text Or Html:</label><textarea class="form-control pull-left" id="metadetail'+idcount+'" name="metadetail[]" placeholder="Page Meta Text Or Html" ></textarea></div></div></div><div class="userfrmDiv overflow-hidden"><div class="pull-left ml-10 detailSection"><div class="form-group editorPanel"><label class="form-label" for="base_sku">Page Meta Text Or Html Spanish:</label><textarea class="form-control pull-left" id="metadetailspanish'+idcount+'" name="metadetailspanish[]" placeholder="Page Meta Text Or Html Spanish" ></textarea></div></div></div><div class="userfrmDiv overflow-hidden"><div class="form-group productfrmDiv detailSection1 pull-left clear-none"><label class="form-label" for="base_sku">Description:</label><input type="text" class="form-control pull-left" id="description" name="description[]" placeholder="Description" ></div><div class="form-group productfrmDiv detailSection1 pull-left clear-none"><label class="form-label" for="default_item_code">Description Spanish:</label><textarea class="form-control pull-left" id="descriptionspanish'+idcount+'" name="descriptionspanish[]" placeholder="Description Spanish" ></textarea></div></div><div class="col-lg-2 cursor-pointer margin-top-35" title="Click to remove"><span class="removeIcon"></span></div></div>';
	
	Str='<div class="metaSection">';
     Str+= '<div class="userfrmDiv overflow-hidden">';
      Str+=  '<div class="form-group productfrmDiv detailSection1 pull-left clear-none">';
       Str+=   '<label class="form-label" for="base_sku">Caption:</label>';
       Str+=   '<input type="text" class="form-control pull-left" id="caption" name="caption[]" placeholder="Caption" >';
       Str+=   '</div>';
       Str+=   '<div class="form-group productfrmDiv detailSection1 pull-left clear-none">';
       Str+=   '<label class="form-label" for="default_item_code">Caption Spanish:</label>';
        Str+=   '<input type="text" class="form-control pull-left" id="captionspanish " name="captionspanish[]" placeholder="Caption Spanish" >';
         Str+=   '</div>';
          Str+=   '</div>';
         Str+=   '<div class="userfrmDiv overflow-hidden">';
           Str+=   '<div class="pull-left ml-10 detailSection">';
                	Str+=   '<div class="form-group editorPanel">';
                    	Str+=   '<label class="form-label editorCaption" for="base_sku">Page Meta Text Or Html:</label>';
                    	Str+=   '<textarea class="form-control pull-left " id="metadetail'+idcount+'" name="metadetail1[]" placeholder="Page Meta Text Or Html" ></textarea>';
                    Str+=   '</div>';
                Str+=   '</div>';
				Str+=   '<div class="pull-left ml-10 detailSection">';
                	Str+=   '<div class="form-group editorPanel">';
                    	Str+=   '<label class="form-label editorCaption" for="base_sku">Page Meta Text Or Html Spanish:</label>';
                    	Str+=   '<textarea id="metadetailspanish'+idcount+'" class="form-control pull-left" name="metadetailspanish1[]" placeholder="Page Meta Text Or Html Spanish" ></textarea>';
                    Str+=   '</div>';
                Str+=   '</div>';
           Str+=   ' </div>';
            Str+=   '<div class="userfrmDiv overflow-hidden">';
               Str+=   ' <div class="form-group productfrmDiv detailSection1 pull-left clear-none">';
                    Str+=   '<label class="form-label" for="base_sku">Description:</label>';
                    Str+=   '<input type="text" class="form-control pull-left" id="description" name="description[]" placeholder="Description" >';
                Str+=   '</div>';
                Str+=   '<div class="form-group productfrmDiv detailSection1 pull-left clear-none">';
                 Str+=   ' <label class="form-label" for="default_item_code">Description Spanish:</label>';
                  Str+=   '<textarea class="form-control pull-left" id="descriptionspanish " name="descriptionspanish[]" placeholder="Description Spanish" ></textarea>';
                Str+=   '</div>';
            Str+=   '</div>';
        Str+=   '</div>';
	
	//alert(Str);
return Str;
}

function RowCount()
{
	rowcount	= $('#rowcount').val();
	idcount		= parseInt(rowcount)+1;
	$('#rowcount').val(idcount);
	return idcount;
}

function AddEditor(rowcount)
{
	for(i=1;i<=rowcount;i++)
	{
		id ="textarea#metadetail"+i;
		
		tinymce.init(
		{
			selector: "textarea#metadetail"+i,
			theme: "modern",
			height: 120,
			
			plugins: [
				"advlist autolink lists link image charmap print preview hr anchor pagebreak",
				"searchreplace wordcount visualblocks visualchars code fullscreen",
				"insertdatetime media nonbreaking save table contextmenu directionality",
				"emoticons template paste textcolor"
			],
			toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
			
		   image_advtab: true,
		   toolbar_items_size: 'small',
		   extended_valid_elements : "span[class]",
		   convert_urls: false,
		   style_formats: [
				{title: 'Bold text', inline: 'b'},
				{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
				{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
				{title: 'Example 1', inline: 'span', classes: 'example1'},
				{title: 'Example 2', inline: 'span', classes: 'example2'},
				{title: 'Table styles'},
				{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
			]
 		}); 
	}
}

function AddEditor2(rowcount)
{
	for(i=1;i<=rowcount;i++)
	{
		tinymce.init(
		{
			selector: "textarea#metadetailspanish"+i,
			theme: "modern",
			height: 120,
			
			plugins: [
				"advlist autolink lists link image charmap print preview hr anchor pagebreak",
				"searchreplace wordcount visualblocks visualchars code fullscreen",
				"insertdatetime media nonbreaking save table contextmenu directionality",
				"emoticons template paste textcolor"
			],
			toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
			
		   image_advtab: true,
		   toolbar_items_size: 'small',
		   extended_valid_elements : "span[class]",
		   convert_urls: false,
		   style_formats: [
				{title: 'Bold text', inline: 'b'},
				{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
				{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
				{title: 'Example 1', inline: 'span', classes: 'example1'},
				{title: 'Example 2', inline: 'span', classes: 'example2'},
				{title: 'Table styles'},
				{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
			]
 		});  
	}
}