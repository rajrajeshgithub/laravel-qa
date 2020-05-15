// JavaScript Document
var approve_status='';
var TotalRecord;
var TotalCommentDiv;
jQuery(document).ready(function()
{
	TotalRecord = jQuery("#TotalRecord").val();
	TotalCommentDiv = jQuery(".comment-box").length;
	if(TotalRecord == TotalCommentDiv)jQuery('#loadMore').hide();
	jQuery('#loadMore').on('click',function() {
		var pageNo		= parseInt($("#PageNo").val())+1;
		var totalRecord	= $("#TotalRecord").val();
		var fundraiserId= $("#FundraiserID").val();
		var dataArr={};
		dataArr['pageNo']		= pageNo;
		dataArr['totalRecord']	= totalRecord;
		dataArr['fundraiserId']	= fundraiserId;
		$.ajax(
		{
			type:'POST',	
			cache:false,
			url:SITEURL+'ut1myaccount/getFundraiserCommentByAjax',
			data:dataArr,
			contentType: "application/html", 
        	dataType: "html", 
			beforeSend:function()
			{
			},
			success:function(res)
			{
				$("#PageNo").val(pageNo);
				$('.loadMoreDiv').before(res);
				jQuery('.comment-box').fadeIn('slow');
				if($('.comment-box').length==totalRecord) {
					$('.loadMoreDiv').hide();
				}
			},
			error:function(errMsg)
			{
				alert(errMsg)
			}
		})
	});

	//jQuery('.comment-section .comment-box .editComment').on('click',function() {
	jQuery(document.body).on('click', '.editComment' ,function(){		
		jQuery(this).hide();
		jQuery(this).parents('.comment-box').find('.saveComment').show();
		var thisComment=jQuery(this).parents('.comment-box').find('.comment-content').text().trim();
		jQuery(this).parents('.comment-box').find('.comment-content').text(" ").append("<div class='edit-content-box form-group'><textarea name='commentContent' id='commentContent' class='edit-input form-control' placeholder='Comment'></textarea></div>")
		jQuery(this).parents('.comment-box').find('.edit-input').val(thisComment);
	});
	
	//jQuery('.saveComment').on('click',function() {
	jQuery(document.body).on('click', '.saveComment' ,function(){
		//$("#comment-form").submit();		
		var $this=jQuery(this);
		var commentId		= $this.closest('.comment-box').find('#Camp_Cmt_ID').val();
		var fundraiserId	= $this.closest('.comment-box').find('#Camp_Cmt_RUID').val();
		var commentContent	= $this.closest('.comment-box').find('#commentContent').val();
		if(commentContent!='')
		{
			var dataArr={};
			dataArr['commentId']		= commentId;
			dataArr['fundraiserId']		= fundraiserId;
			dataArr['commentContent']	= commentContent;
			jQuery.ajax( {
				type:'POST',
				data:dataArr,	
				url:SITEURL+'ut1myaccount/updateFundraiserComment',
				cache:false,
				beforeSend:function() {
					$this.closest('.comment-box').find('#commentContent').attr('readonly',true);
				},
				success:function(res) {
					$this.closest('.comment-box').find('#commentContent').attr('readonly',false);
					var msgHTML=getMsgHtml(res,'saveComment');
					if(jQuery('.fundraiser-section').find('#msgSection').length==0) {
						jQuery('.fundraiser-section').prepend(msgHTML);
					}else {
						jQuery('.fundraiser-section').find('#msgSection').html(msgHTML);
					}
					if(res==1) {
						$this.hide();
						$this.closest('.comment-box').find('.editComment').show();
						$this.closest('.comment-box').find('.comment-content').text(commentContent);
					}	
				},
				error:function(errMsg){alert(errMsg)},
			})
		}
		else
		{
			jQuery(this).parents('.comment-box').find('#commentContent').addClass('error');
			return false;	
		}
	})

	//jQuery('.approveComment').on('click',function() {
	jQuery(document.body).on('click', '.approveComment' ,function(){	
		var $this=jQuery(this);
		var commentId		= $this.closest('.comment-box').find('#Camp_Cmt_ID').val();
		var fundraiserId	= $this.closest('.comment-box').find('#Camp_Cmt_RUID').val();
		var appStatus		= $this.attr('appStatus');
		var commentContent	= $this.closest('.comment-box').find('#commentContent').val();
		var dataArr={};
		dataArr['commentId']		= commentId;
		dataArr['fundraiserId']		= fundraiserId;
		dataArr['appStatus']		= appStatus;
		
		if(appStatus==1)
			approve_status=0;
		else	
			approve_status=1;
		jQuery.ajax( {
			type:'POST',
			data:dataArr,
			url:SITEURL+'ut1myaccount/approveFundraiserComment',
			cache:false,
			beforeSend:function(){},
			success:function(res) {
				var resArr=JSON.parse(res);
				resArr=resArr.split('##');
				var msgHTML=getMsgHtml(resArr[0],'approveComment');
				if(jQuery('.fundraiser-section').find('#msgSection').length==0) {
					jQuery('.fundraiser-section').prepend(msgHTML);
				}else {
					jQuery('.fundraiser-section').find('#msgSection').html(msgHTML);
				}
				$this.attr('appStatus',resArr[1]);
				if(resArr[1]==1) {
					$this.find('.approveText').text('UnApprove');		
				}
				else {
					$this.find('.approveText').text('Approve');		
				}	
			},
			error:function(errMsg){alert(errMsg)},
		})
	})

	//jQuery('.removeComment').on('click',function() {
		jQuery(document.body).on('click', '.removeComment' ,function(){	
		if(confirm('Are to sure to delete this comment...?')) {
			var $this=jQuery(this);
			var commentId		= $this.closest('.comment-box').find('#Camp_Cmt_ID').val();
			var fundraiserId	= $this.closest('.comment-box').find('#Camp_Cmt_RUID').val();
			var dataArr={};
				dataArr['commentId']	= commentId;
				dataArr['fundraiserId']	= fundraiserId;
			jQuery.ajax( {
				type:'POST',
				data:dataArr,
				url:SITEURL+'ut1myaccount/deleteFundraiserComment',
				cache:false,
				beforeSend:function(){},
				success:function(res) {
					var msgHTML=getMsgHtml(res,'removeComment');
					if(jQuery('.fundraiser-section').find('#msgSection').length==0) {
						jQuery('.fundraiser-section').prepend(msgHTML);
					}else {
						jQuery('.fundraiser-section').find('#msgSection').html(msgHTML);
					}
					if(res==1) {
						$this.closest('.comment-box').remove();
					}	
				},
				error:function(errMsg){alert(errMsg)},
			})
		}
	})
})

function getMsgHtml(resStatus,type) {
	var msgHTML='';
	switch(type) {
		case 'saveComment': {
			if(resStatus==1) {
				var msgText='<div class="alert alert-success text-center"><span>Fundraiser comment updated successfully</span></div>';
			}
			else {
				var msgText='<div class="alert alert-danger text-center"><span>Unable to update fundraiser comment</span></div>';
			}
			break;	
		}
		case 'approveComment': {
			if(resStatus==1) {
				if(approve_status==1) {
					var msgText='<div class="alert alert-success text-center"><span>Fundraiser comment Approved successfully</span></div>';
				} else {
					var msgText='<div class="alert alert-success text-center"><span>Fundraiser comment UnApproved successfully</span></div>';
				}
			}
			else {
				var msgText='<div class="alert alert-danger text-center"><span>Unable to approve fundraiser comment</span></div>';
			}
			break;	
		}
		case 'removeComment': {
			if(resStatus==1) {
				var msgText='<div class="alert alert-success text-center"><span>Fundraiser comment removed successfully</span></div>';
			}
			else {
				var msgText='<div class="alert alert-danger text-center"><span>Unable to remove fundraiser comment</span></div>';
			}
			break;	
		}
	}
	msgHTML+='<div class="container"><div id="msgSection" class="mt-10">';
	msgHTML+=msgText;
	msgHTML+='</div></div>';
	return msgHTML;
}