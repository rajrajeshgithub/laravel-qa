<link type="text/css" rel="stylesheet" href='{#RESOURCES#}css/bootstrap-datepicker.css'>
<script type="text/javascript" src='{#RESOURCES#}js/bootstrap-datepicker.js'></script>
<script type="text/javascript" src='{#URL#}app/tinymce/tinymce.min.js'></script>
<script type="text/javascript" src='{#RESOURCES#}js/campaign_edit.js'></script>
<script type="text/javascript" src='{#RESOURCES#}js/jquery.simplyCountable.js'></script>
<script type="text/javascript" src='{#RESOURCES#}js/jquery.colorbox.js'></script>
<link href='{#RESOURCES#}css/jquery.colorbox.css' type="text/css" rel="stylesheet" />
<link href='{#RESOURCES#}css/magnific-popup.css' type="text/css" rel="stylesheet" />


<ul class="breadcrumb">
	<li title="Dashboard"><a href='{#URL#}home/'>Dashboard</a></li>
    <li title="Campaign"><a href='{#URL#}campaign/'>Campaign - List</a></li>
    <li title="Campaign List">Edit</li>
</ul>
<div class="oh">
	<div class="col col-5">
    	<h1 class="heading-1">Campaign - Edit</h1>
    </div>
    <div class="col col-7 text-right mt-15 pull-right">                   
                  <a href='{#URL#}campaign' class="button-2" title="View Campaign list"><i class="fa fa-list"></i>&nbsp;View List</a>
                  <a href='{#URL#}campaignimages/index/list-images/{$CampaignResultArray.Camp_ID|keyEncrypt}' class="button-2" title="Images"><i class="fa fa-file-image-o"></i>&nbsp;Images</a>
                  <a href='{#URL#}campaignvideo/index/list-videos/{$CampaignResultArray.Camp_ID|keyEncrypt}' class="button-2" title="Video"><i class="fa fa-video-camera"></i>&nbsp;Videos</a>
                  <a href='{#URL#}campaigncomment/index/list/{$CampaignResultArray.Camp_ID|keyEncrypt}' class="button-2" title="Comments"><i class="fa fa-comment"></i>&nbsp;Comments</a>
                  <a href='javascript://' class="button-2" title="Donation"><i class="fa fa-money"></i>&nbsp;Donation</a>
            </div>
</div>
{include="common/message"}
<div class="col col-6 br-1">
    <div id="preview-content-box" class="igs-form">
        
        <div class="preview-content-section">
            <div class="row">
                	<div class="col mb-10">
                            <div class="user-image">
                                <img class="img-responsive" src='{function="CheckImage(UT1PROFILE_THUMB_IMAGE_DIR,UT1PROFILE_THUMB_IMAGE_URL,NO_PERSON_IMAGE,$CampaignResultArray.camp_thumbImage)"}' width="100" alt="Image">
                            </div>
					</div> 
                    <div class="col col-9">
                    	<label class="label text-bold f-16 t-blue">{$CampaignResultArray.Camp_Title|ucwords}</label>
                        <label class="label text-bold">{$CampaignResultArray.NPOCat_DisplayName_EN}</label>
                        <label class="label "><i class="fa fa-map-marker"></i>{if="$CampaignResultArray.Camp_Location_City==NULL&&$CampaignResultArray.Camp_Location_State==NULL&&$CampaignResultArray.Camp_Location_Country==NULL&&$CampaignResultArray.Camp_Location_Logitude==NULL&&$CampaignResultArray.Camp_Location_Latitude==NULL"}NA
                        {else} {if condition="$CampaignResultArray.Camp_Location_City==NULL"}{else}{$CampaignResultArray.Camp_Location_City},{/if} {if="$CampaignResultArray.Camp_Location_State==NULL"}{else}{$CampaignResultArray.Camp_Location_State},{/if} {if="$CampaignResultArray.Camp_Location_Country==NULL"}{else}{$CampaignResultArray.Camp_Location_Country},{/if} {if="$CampaignResultArray.Camp_Location_Logitude==NULL"}{else}{$CampaignResultArray.Camp_Location_Logitude},{/if} {if="$CampaignResultArray.Camp_Location_Latitude==NULL"}{else}{$CampaignResultArray.Camp_Location_Latitude}{/if}{/if}</label>
                        
                        <label class="label "><strong>{$CampaignResultArray.Camp_DonationReceived}</strong> Raised of <strong>{$CampaignResultArray.Camp_DonationGoal}</strong>
                    	</label>
                	</div>
                	{if="$CampaignResultArray.Camp_DescriptionHTML!=NULL"}
                        <div class="col col-12">
                            <label class="label text-bold">Description</label>
                            <label class="label">{if="CampaignResultArray.Camp_DescriptionHTML==NULL"}NO Description Available{else}{$CampaignResultArray.Camp_DescriptionHTML}{/if}</label>
                        </div>
                    {/if}
                        <div class="col col-6">
                            <label class="label text-bold">Duration</label>
                            <label class="label">{$CampaignResultArray.Camp_StartDate|formatdate:'m/d/Y'} - {if="$CampaignResultArray.Camp_EndDate==NULL"}NA
                        {else}
                        {$CampaignResultArray.Camp_EndDate|formatdate:'m/d/Y'}
                        {/if}</label>
                        </div>
                        <div class="col col-3">
                            <label class="label text-bold">Level</label>
                            <label class="label">{$CampaignResultArray.Camp_Level_Name}</label>
                        </div>
                        <div class="col col-3">
                            <label class="label text-bold">Is Private</label>
                            <label class="label">{if condition="$CampaignResultArray.Camp_IsPrivate==1"}Yes{else}No{/if}</label>
                        </div>
        	</div>        
    	</div>
	</div>        
        <div id="preview-content-box" class="igs-form">
            <div class="preview-section-heading">User Details</div>
            <div class="preview-content-section">
                <div class="row">            
                         <div class="col col-4">
                            <label class="label text-bold">Person Name</label>
                            <label class="label">{$CampaignResultArray.Camp_CP_FirstName} {$CampaignResultArray.Camp_CP_LastName}</label>
                         </div>
                         <div class="col col-8">
                            <label class="label text-bold">Email Address</label>
                            <label class="label">{$CampaignResultArray.Camp_CP_Email}</label>
                         </div>
                         <div class="col col-12">   
                            <label class="label text-bold">Address</label>
                            <label class="label">{$CampaignResultArray.Camp_CP_Address1} {$CampaignResultArray.Camp_CP_Address2}</label> 
                         </div>
                         <div class="col col-4">   
                            <label class="label text-bold">City</label>
                            <label class="label">{if="$CampaignResultArray.Camp_City==NULL"}NA
                        {else}
                        {$CampaignResultArray.Camp_City}
                        {/if}</label>
                         </div>
                         <div class="col col-4">   
                            <label class="label text-bold">Zip Code</label>
                            <label class="label">{$CampaignResultArray.Camp_CP_ZipCode}</label>
                         </div>
                         <div class="col col-4">   
                            <label class="label text-bold">State</label>
                            <label class="label ">{if="$CampaignResultArray.Camp_State==NULL"}NA
                        {else}
                        {$CampaignResultArray.Camp_State}
                        {/if}</label>
                         </div>
                         <div class="col col-4">   
                            <label class="label text-bold">Country</label>
                            <label class="label">{if="$CampaignResultArray.Camp_Country==NULL"}NA
                        {else}
                        {$CampaignResultArray.Camp_Country}
                        {/if}</label>
                         </div>
                         <div class="col col-8">   
                            <label class="label text-bold">Phone Number</label>
                            <label class="label">{if="$CampaignResultArray.Camp_Phone==NULL"}NA
                        {else}
                        {$CampaignResultArray.Camp_Phone}
                        {/if}</label>
                         </div>
                         <div class="col col-12">
                         	<label class="label"><span class="text-bold">Social Link</span></label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->facebook==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->g-plus==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->linkedin==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->twitter==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->instagram==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->youtube==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->user-secret==NULL&&$CampaignResultArray.Camp_SocialMediaUrl->a-at==NULL"} <label class="label">NA</label>{else}
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->facebook==NULL"}{else}<label class="label pull-left mr-10"><i class="fa fa-facebook-square"></i>  {$CampaignResultArray.Camp_SocialMediaUrl->facebook}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->g-plus==NULL"}{else}<label class="label pull-left mr-10"><i class="fa fa-google-plus-square"></i> {$CampaignResultArray.Camp_SocialMediaUrl->g-plus}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->linkedin==NULL"}{else}<label class="label pull-left mr-10"><i class="fa fa-linkedin-square "></i> {$CampaignResultArray.Camp_SocialMediaUrl->linkedin}}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->twitter==Null"}{else}<label class="label pull-left mr-10"><i class="fa fa-twitter-square"></i> {$CampaignResultArray.Camp_SocialMediaUrl->twitter} {/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->instagram==NULL"}{else}<label class="label pull-left mr-10"><i class="fa fa-instagram-square"></i> {$CampaignResultArray.Camp_SocialMediaUrl->instagram}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->youtube==NULL"}{else}<label class="label pull-left mr-10"><i class="fa fa-youtube-square"></i> {$CampaignResultArray.Camp_SocialMediaUrl->youtube}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->user-secret==NULL"}{else}<label class="label pull-left mr-10">{$CampaignResultArray.Camp_SocialMediaUrl->user-secret}{/if}</label>
                            {if="$CampaignResultArray.Camp_SocialMediaUrl->a-at==NULL"}{else}{<label class="label pull-left mr-10">{$CampaignResultArray.Camp_SocialMediaUrl->a-at}{/if}{/if}</label>
                            
                         </div>
                 </div> 
            </div>        
        </div>
        <div id="preview-content-box" class="igs-form">
            <div class="preview-section-heading">Images</div>
            <div class="preview-content-section">
                <div class="row">{if condition="$images==NULL"}<div class="col col-12"><label class="label">No Image Available</label></div>{else}
                            {if condition="$images!=''"}
                            	{loop="$images"}
                            		 <div class="col col-2 mb-25">
                                		<a href='{function="CheckImage(CAMPAIGN_THUMB_IMAGE_DIR,CAMPAIGN_THUMB_IMAGE_URL,NO_PERSON_IMAGE,$value.Camp_Image_Name)"}' class="listingImage"><img class="img-responsive" src='{function="CheckImage(CAMPAIGN_THUMB_IMAGE_DIR,CAMPAIGN_THUMB_IMAGE_URL,NO_PERSON_IMAGE,$value.Camp_Image_Name)"}'alt="Image"width="80">
                           			  </a></div>
                            	{/loop}
                            {/if}{/if}
            	</div>    
            </div>        
        </div>
        <div id="preview-content-box" class="igs-form">
            <div class="preview-section-heading">Video</div>
            <div class="preview-content-section">
                <div class="row">
                {if condition="$videos==NULL"}<div class="col col-12"><label class="label">No Video Available</label></div>{else}
                {if condition="$videos!=''"}
                            	{loop="$videos"}
									{if condition="$value.Camp_Video_EmbedCode!=''"}
                            <div class="col col-2 mb-25">
                                <a class="popup-modal" href='#video'><img src='{#RESOURCES#}img/videothumb.jpg' class="img-responsive" height="50" width="150"></a>
                            </div>
                            <div id="video" class="white-popup-block mfp-hide text-center">{$value.Camp_Video_EmbedCode}</div>
                                    {/if}            		 
   									   
                                 {/loop}
                 {/if}
                 
                 {if condition="$videos!=''"}
                            	{loop="$videos"}
									{if condition="$value.Camp_Video_File!=''"} 
                                    <div class="col col-2 mb-25">
                               		 <a class="popup-modal" href='#video1'><img src='{#RESOURCES#}img/videothumb.jpg' class="img-responsive" height="50" width="150"></a></div>                           	
            	                <div id="video1" class="white-popup-block mfp-hide text-center"><video width="100%" height="315" controls><source src='{#CAMPAIGN_VIDEO_URL#}{$value.Camp_Video_File}' type="video/mp4"></video></div>
                                    {/if}            		 
   									   
                                 {/loop}
                 {/if}{/if}
                </div>            
            </div>     
        </div>
        
	</div>
    <div class="col col-6">
		<div class="row">
        	<div id="content-box">
            	<div class="section-heading">Edit - Basic Details <a href='javascript://' class="form-toggle-icon"><i class="fa fa-plus-square-o"></i></a></div>
                	<div class="content-section dn-ni">
                        <form method="post" autocomplete="off" name="frm_basicdetails" id="frm_basicdetails" action='{#URL#}campaign/index/update-basic' enctype="multipart/form-data">
                        	<input type="hidden" name="campID" id="campID" value="{$CampaignResultArray.Camp_ID|keyEncrypt}" />
                            <fieldset>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Title</label>
                                        <input type="text" placeholder="Title" name="Title" id="Title" value='{$CampaignResultArray[Camp_Title]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    
                                    <label class="label pull-left">Url Friendly Name</label>
                                    <label class="dib GenerateUrl pl-10"><a href='javascript://' class="link"><li class="fa fa-magic">&nbsp;</li>Generate</a></label>
                                        <input type="text" id="UrlFriendlyName" placeholder="Url Friendly Name" name="UrlFriendlyName"  value='{$CampaignResultArray[Camp_UrlFriendlyName]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="label">Upload image</label>
                                    <label for="file" class="input input-file">
                                        <div class="button"><input type="file" id="thumbImage" name="thumbImage" onchange="this.parentNode.nextSibling.value = this.value">Browse</div><input name="file" id="file" type="text"><div class="note"><strong>Note :</strong> Please Upload JPG/PNG image format only. </div>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Sort Description</label>
                                        <input type="text" placeholder="Sort Description" name="ShortDescription" id="ShortDescription" value='{$CampaignResultArray[Camp_ShortDescription]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Long Description</label>
                                        <div class="textarea">
                                        
                                            <textarea name="DescriptionHTML" id="DescriptionHTML"  rows="5"  >{$CampaignResultArray[Camp_DescriptionHTML]}</textarea>
                                        </div>
                                        <div class="oh mt-5 f-12 text-bold">Remaining Characters&nbsp;<span id="wordCounter"></span></div>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Donation Goal Amount</label>
                                        <input type="text" placeholder="Donation Goal Amount" name="DonationGoal" id="DonationGoal" value='{$CampaignResultArray[Camp_DonationGoal]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Achieved Amount</label>
                                        <input type="text" placeholder="Achieved Amount" name="DonationReceived" id="DonationReceived" value='{$CampaignResultArray[Camp_DonationReceived]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="input">
                                    <label class="label">Minimun Donation Amount</label>
                                        <input type="text" placeholder="Minimun Donation Amount" name="MinimumDonationAmount" id="MinimumDonationAmount" value='{$CampaignResultArray[Camp_MinimumDonationAmount]}'>
                                    </label>
                                </section>
                                <section class="col col-12">
                                   <label class="checkbox ">
		                                <input type="checkbox" name="IsPrivate" value="1" {if condition="$CampaignResultArray[Camp_IsPrivate]==1"}checked{/if}><i></i>Yes, this is private fundraiser.</label>
                                </section>
                                <div class="form-footer">
                                            <button type="Submit" title="save" class="button-2"><i class="fa fa-edit"></i>&nbsp;Save</button>
                                </div>
                                        
                        	</fieldset>
                        </form>                    
                	</div>                
                </div>
            </div>
            <div class="row">
                <div id="content-box">
                    <div class="section-heading">Duration Details <a href='javascript://' class="form-toggle-icon"><i class="fa fa-plus-square-o"></i></a></div>
                        <div class="content-section dn-ni">
                            <form method="post" autocomplete="off" name="frm_durationdetails" id="frm_durationdetails" action='{#URL#}campaign/index/update-duration'>
                            <input type="hidden" name="campID" id="campID" value="{$CampaignResultArray.Camp_ID|keyEncrypt}" />
                                <fieldset>
                                    <section class="col col-6">
                                        <label class="label col col-12">Start Date</label>
                                        <div class="col col-12 input-append date" id="StartDate" data-date="" data-date-format="mm/dd/yyyy" data-date-viewmode="years">
                                         <label class="input">
                                            <i class="icon-append fa fa-calendar add-on"></i>
                                            <input type="text" class="form-control" name="StartDate" id="StartDate" value='{$CampaignResultArray[Camp_StartDate]|formatDate:"m/d/Y"}'>
                                         </label>
                                    </section>
                                    <section class="col col-6">
                                        <label class="label col col-12">End Date</label>
                                        <div class="col col-12 input-append date" id="EndDate" data-date="" data-date-format="mm/dd/yyyy" data-date-viewmode="years">
                                         <label class="input">
                                            <i class="icon-append fa fa-calendar add-on"></i>
                                            <input type="text" class="form-control" name="EndDate" id="EndDate" value='{$CampaignResultArray[Camp_EndDate]|formatDate:"m/d/Y"}'>
                                         </label>
                                    </section>     
                                    <div class="form-footer">
                                                <button type="Submit" title="save" class="button-2"><i class="fa fa-edit"></i>&nbsp;Save</button>
                                    </div>
                                            
                                </fieldset>
                            </form>                    
                        </div>                
                    </div>
                </div>
                <div class="row">
                <div id="content-box">
                    <div class="section-heading">Change Level<a href='javascript://' class="form-toggle-icon"><i class="fa fa-plus-square-o"></i></a></div>
                        <div class="content-section dn-ni">
                            <form method="post" autocomplete="off" name="frm_leveldetails" id="frm_leveldetails" action='{#URL#}campaign/index/update-level'>
                            <input type="hidden" name="campID" id="campID" value="{$CampaignResultArray.Camp_ID|keyEncrypt}" />
                                <fieldset>              
                                			<section class="col col-3">
                                            	<label class="checkbox ">
		                                        	&nbsp;
		                                        </label>
                                                <div>
				                                	<label class="label f-11"><strong>Supported Style :</strong></label>
				                                	<label class="label f-11"><strong>Number of Photos :</strong></label>
				                                	<label class="label f-11"><strong>Number of Videos :</strong></label>
				                                	<label class="label f-11"><strong>Color Customization :</strong></label>
				                                	<label class="label f-11"><strong>Color Themes :</strong></label>
				                                	<label class="label f-11"><strong>Duration Days :</strong></label>
												</div>
                                            </section>
                                               
                                    {if condition="$level!=''"}
                                    	{loop="$level"}
	                                    	{$checked=''}
	                                    	{if="$CampaignResultArray[Camp_Level_ID]==$value.Camp_Level_ID"}
	                                    		{$checked=checked}
	                                    	{/if}
                                    		<section class="col col-3">
		                                    	<label class="checkbox ">
		                                        	<input type="radio" name="Level_ID" value='{$value.Camp_Level_ID}' {$checked}><i></i>{$value.Camp_Level_Name}
		                                        </label>
		                                        <div>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Supported_Style}</label>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Number_of_Photos}</label>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Number_of_Videos}</label>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Allow_Color_Customization}</label>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Color_Themes}</label>
				                                	<label class="label f-11">{$value.Camp_Level_DetailJSON->Duration_Days}</label>
												</div>                                        
				                                        
		                                    </section>
			                                
			                               
                                    	{/loop}
                                    {/if}
                                    
                                    <div class="col col-12">
                                        <div class="form-footer">
                                                    <button type="Submit" title="save" class="button-2"><i class="fa fa-edit"></i>&nbsp;Save</button>
                                        </div>
                                    </div>    
                                            
                                </fieldset>
                            </form>                    
                        </div>                
                    </div>
                </div>
                <div class="row">
                <div id="content-box">
                    <div class="section-heading">Change Status<a href='javascript://' class="form-toggle-icon"><i class="fa fa-minus-square-o"></i></a></div>
                        <div class="content-section">
                            <form  method="post" autocomplete="off" name="frm_statusdetails" id="frm_statusdetails" action='{#URL#}campaign/index/update-status'>
                            <input type="hidden" name="campID" id="campID" value="{$CampaignResultArray.Camp_ID|keyEncrypt}" />
                                <fieldset>
                                
                                    <section class="col col-12">
                                    <label class="label">Status</label>
                                    <label class="select">
                                        <select name="Status" id="Status">
                                        {if condition="$CampaignStatus!=''"}
                                        	{loop="$CampaignStatus"}
                                        	{$select=''}
                                        	{if condition="$CampaignResultArray[Camp_Status]==$key"}
                                        		{$select='selected'}
                                        	{/if}
                                        		<option value='{$key}' {$select}>{$value}</option>
                                        	{/loop}
                                        {/if}
                                        </select>
                                        <i></i>
                                    </label>
                                </section>
                                <section class="col col-12">
                                    <label class="label">Web Master Comment</label>
                                        <div class="textarea">	
                                            <textarea id="metaDescription" name="WebMasterComment"  rows="5" class="form-control">{$CampaignResultArray[Camp_WebMasterComment]}</textarea>
                                        </div>    
                                </section>    
                                    <div class="form-footer">
                                                <button type="Submit" title="save" class="button-2"><i class="fa fa-edit"></i>&nbsp;Save</button>
                                    </div>
                                            
                                </fieldset>
                            </form>                    
                        </div>                
                    </div>
                </div>
                
    		</div>        
    </div>
    
<script type="text/javascript" src='{#RESOURCES#}js/jquery.magnific-popup.js'></script>
<script type="text/javascript">
	$('.iframe-popup').magnificPopup({
		type: 'iframe',
	});
	$('.popup-modal').magnificPopup({
		type: 'inline',
		return:false
	});
</script>
    
        
    