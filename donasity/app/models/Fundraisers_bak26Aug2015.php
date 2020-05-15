<?php
	class Fundraisers_Model extends Model
	{
		
	public 	$P_ErrorCode,$P_ErrorMessage,$P_ConfirmCode,$P_ConfirmMsg,$P_status,$P_MsgType;
	
	public 	$F_Camp_ID,$F_Camp_Cat_ID,$F_Camp_Level_ID,$F_Camp_Duration_Days,$F_Camp_Title,$F_camp_thumbImage,$F_camp_bgImage,$F_Camp_UrlFriendlyName,$F_Camp_ShortDescription,$F_Camp_DescriptionHTML,$F_Camp_DonationGoal,$F_Camp_DonationReceived,$F_Camp_StartDate,$F_Camp_EndDate,$F_Camp_CP_FirstName,$F_Camp_CP_LastName,$F_Camp_CP_Address1,$F_Camp_CP_Address2,$F_Camp_CP_City,$F_Camp_CP_State,$F_Camp_CP_Country,$F_Camp_CP_ZipCode,$F_Camp_CP_Email,$F_Camp_CP_Phone,$F_Camp_UserBio,$F_Camp_Location_City,$F_Camp_Location_State,$F_Camp_Location_Country,$F_Camp_Location_Logitude,$F_Camp_Location_Latitude,$F_Camp_Stripe_Status,$F_Camp_Stripe_ConnectedID,$F_Camp_Stripe_Response,$F_Camp_PaymentMode,$F_Camp_Status,$F_Camp_SearchTags,$F_Camp_WebMasterComment,$F_Camp_RUID,$F_Camp_NPO_EIN,$F_Camp_SocialMediaUrl,$F_Camp_SalesForceID,$F_Camp_IsPrivate,$F_Camp_StylingTemplateName,$F_Camp_StylingDetails,$F_Camp_MinimumDonationAmount,$F_Camp_CreatedDate,$F_Camp_LastUpdatedDate,$F_camp_ProcessLog,$F_Camp_Locale,$F_Camp_Deleted;	
		
		public function __construct()
		{
			
		}
		
		public function GetFundraiserDetails()
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT  Camp_ID,Camp_Level_ID,Camp_RUID,Camp_Status FROM ".TBLPREFIX."campaigncampaign WHERE Camp_ID=".$this->F_Camp_ID;
			$row = db::get_all($sql);
			return $row;
		}
		
		public function GetCampaignCategoryList()
		{
				EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT CampCat_ID,CampCat_ParentID,CampCat_DisplayName_"._DBLANG_.",CampCat_UrlFriendlyName,CampCat_ShowOnWebsite FROM ".TBLPREFIX."campaigncategories";
			$row = db::get_all($sql);
			if(count($row)<=0)
			{
				 $this->setErrorMsg("");
			}
			return $row;
		}
		
		
		public function FundraiserInsert()
		{
			$data=array("Camp_Level_ID"=>1,
						"Camp_RUID"=>'53', 
						"Camp_Status"=>0);
			db::insert(TBLPREFIX."campaign", $data);
			$id = db::get_last_id();
			
		}

	}
?>