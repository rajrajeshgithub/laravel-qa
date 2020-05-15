jQuery(function()
{
	jQuery("#addMorePageMetaDiv").on("click", function() {
		var id= $("#keyId").val();
		var e = "";
		e += '<div class="additionalPageMetaDiv">',
		e += '<div class="form-group">', 
		e +='<input type="hidden" name="metaPageValuesId[]" value="'+id+'" />';
		e +='<input type="hidden" name="metaPageContentId[]" value="" />';
		e += '<label class="form-label">Caption:</label>', 
		e += '<input type="text" class="form-control" id="caption" name="caption[]" placeholder="Enter Caption">', 
		e += "</div>",
		e += '<div class="form-group">', 
		e += '<label class="form-label">Description:</label>', 
		e += '<textarea name="description[]" class="form-control" id="description" rows="6" placeholder="Meta Description"></textarea>',
		e += "</div>", 
		e += '<div class="form-group">',
		e += '<label class="form-label">Detail:</label>', 
		e += '<textarea name="detail[]" class="form-control" id="detail" rows="6" placeholder="Meta Detail"></textarea>', 
		e += "</div>", 
		e += '<div class="form-group">', 
		e += '<label class="form-label">&nbsp;</label>', 
		e += '<button type="button" name="delete" id="removeDivBtn" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i>&nbsp;Delete</button>', 
		e += "</div>", 
		e += "</div>", 
		jQuery(".additionalPageMetaSection").append(e)
	}),
	jQuery(".additionalPageMetaSection").on("click", '#removeDivBtn', function() {
		jQuery(this).closest(".additionalPageMetaDiv").remove()
	});
})