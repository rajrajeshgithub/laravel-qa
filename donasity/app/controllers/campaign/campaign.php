<?php
	class Campaign_Controller extends Controller
	{		
			   
		public $P_ErrorCode,$P_status,$P_ErrorMessage,$MsgType,$P_ConfirmCode,$P_ConfirmMsg;
		public $SF_LoggedInDetail,$F_FundId;
		
		function __construct()
		{
			$this->load_model('Campaign','objCamp');
			$this->load_model('Common','objCMN');
			$this->load_model('UserType1','objUT1');
			$this->load_model('FundraisersDetail','objFund');
			$this->SF_LoggedInDetail=getSession('Users');
			$this->P_status=1;
		}
		
		
		public function index($type='detail',$Campaign_ID=1)
		{
			$this->CI_CID = $Campaign_ID;
			
			$this->tpl = new view();
			switch(strtolower($type))
			{
				case "campaigncategorylist":
					$this->GetCampaignCategory();
					$this->tpl->draw("donationcategory/index");
					break;
				case "campaignlist":
					$this->GetCampaignsList();
					break;
				case 'edit':
					$this->Edit();
					break;
				case 'update':
					$this->Update();
					break;
				default:
					$this->CampaignDetail();
					$this->tpl->draw("campaign/index");
					break;
			}
		}
		
		private function Edit()
		{
			$DataArray	= array("Camp_ID","Camp_Cat_ID","Camp_Level_ID","Camp_Title","Camp_UrlFriendlyName","Camp_ShortDescription","Camp_DescriptionHTML","Camp_DonationGoal",
								"Camp_DonationReceived","Camp_StartDate","Camp_EndDate","Camp_CP_FirstName","Camp_CP_LastName","Camp_CP_Address1","Camp_CP_Address2","Camp_CP_City",		
								"Camp_CP_State","Camp_CP_Country","Camp_CP_ZipCode","Camp_CP_Email","Camp_CP_Phone","Camp_UserBio","Camp_BankName","Camp_BankAddress","Camp_AccountType",	
								"Camp_AccountName","Camp_AccountNumber","Camp_PreferredPaymentMode","Camp_BankEmail","Camp_BankPhone","Camp_Status","Camp_SearchTags","Camp_WebMasterComment",
								"Camp_RUID","Camp_NPO_EIN","Camp_SocialMediaUrl","Camp_SalesForceID","Camp_IsPrivate","Camp_StylingTemplateName","Camp_StylingDetails",
								"Camp_MinimumDonationAmount","Camp_Deleted","Camp_CreatedDate","Camp_LastUpdatedDate","Camp_LastUpdatedUserIP");
			$WhereCondition = array("Camp_ID"=>$this->CI_CID);					
			$CampaignDetail	= $this->objCamp->GetCampaignDetail('campaign',$DataArray,$WhereCondition);
			
			$this->tpl->assign("CampaignDetail",$CampaignDetail[0]);
			$this->tpl->draw("campaign/editcampaign");
		}
		
		private function Update()
		{
			$this->InputData();	
			$pStatus = 0;
			if($this->objCamp->CampaignUpdateDB($this->InputDataArray,$this->CI_CID))
			{
				$this->setConfirmationMsg('C15002');
				$pStatus = 1;
			}
			else
			{
				$this->setErrorMsg('E15002');
			}
			
			/*----update process log------*/
			$userType 	= '';
			$userID 	= 0;
			$userName	= '';		
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}
			if(isset($this->SF_LoggedInDetail['UserType2']['is_login'])) {
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}
			
			$sMessage = "Error in update campaign.";
			$lMessage = "Error in update campaign(id=$this->CI_CID).";
			if($pStatus) {
				$sMessage = "Campaign detail has updated successfully.";
				$lMessage = "Campaign detail (id=$this->CI_CID) has updated successfully.";
			}
			
			$DataArray = array(	
				"UType"			=> $userType,
				"UID"			=> $userID,
				"UName"			=> $userName,
				"RecordId"		=> $this->CI_CID,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objRegUser));
				
			$this->objRegUser->updateProcessLog($DataArray);	
			/*-----------------------------*/
			
			redirect($_SERVER['HTTP_REFERER']);
		}
		
		private function InputData()
		{
			$this->CI_CID		= request('post','campaignid',0);
			$Title				= request('post','title',0);
			$UserFriendlyName	= request('post','userfriendlyname',0);
			$ShortDesc			= request('post','shortdesc',0);
			$LongDescHTML		= request('post','longdeschtml',0);
			$DonationRced		= request('post','donationrecd',0);
			$DonationGoal		= request('post','donationgoal',0);
			$MinDonationAmt		= request('post','minimumdonationamt',0);
			$Level				= request('post','level',0);
			$StartDate			= request('post','startdate',0);
			$StartDate			= date('Y-m-d',strtotime($StartDate));
			$EndDate			= request('post','enddate',0);
			$EndDate			= date('Y-m-d',strtotime($EndDate));
			$FirstName			= request('post','firstname',0);
			$LastName			= request('post','lastname',0);
			$Address1			= request('post','address1',0);
			$Address2			= request('post','address2',0);
			$City				= request('post','city',0);
			$Country			= request('post','country',0);
			$Zip				= request('post','zip',0);
			$State				= request('post','state',0);
			$Email				= request('post','email',0);
			$Phone				= request('post','phone',0);
			$UserBio			= request('post','userbio',0);
			$BankName			= request('post','bankname',0);
			$BankAddress		= request('post','bankaddress',0);
			$AccountType		= request('post','accounttype',0);
			$AccountName		= request('post','accountname',0);
			$AccountNumber		= request('post','accountnumber',0);
			$PaymentMode		= request('post','preferedpaymentmode',0);
			$BankEmail			= request('post','bankemail',0);
			$BankPhone			= request('post','bankphone',0);
			$SearchTags			= request('post','searchtags',0);
			$NPOEIN				= request('post','npoein',0);
			$SocialMediaUrl		= request('post','socialmediaurl',0);
			$SalesForceID		= request('post','salesforceid',0);
			$IsPrivate			= request('post','privatecampaign',0);
			$Status				= request('post','status',0);
			$CategoryID			= request('post','category',0);
			$StylingDetail		= request('post','stylingdetails',0);
			$StylingTempName	= request('post','stylingtemplatename',0);
			$WebMasterComment	= request('post','webmastercomment',0);
			
			$this->InputDataArray	= array("Camp_Cat_ID"=>$CategoryID,"Camp_Level_ID"=>$Level,"Camp_Title"=>$Title,"Camp_UrlFriendlyName"=>$UserFriendlyName,
											"Camp_ShortDescription"=>$ShortDesc,"Camp_DescriptionHTML"=>$LongDescHTML,"Camp_DonationGoal"=>$DonationGoal,
											"Camp_DonationReceived"=>$DonationRced,"Camp_StartDate"=>$StartDate,"Camp_EndDate"=>$EndDate,"Camp_CP_FirstName"=>$FirstName,
											"Camp_CP_LastName"=>$LastName,"Camp_CP_Address1"=>$Address1,"Camp_CP_Address2"=>$Address2,"Camp_CP_City"=>$City,"Camp_CP_State"=>$State,
											"Camp_CP_Country"=>$Country,"Camp_CP_ZipCode"=>$Zip,"Camp_CP_Email"=>$Email,"Camp_CP_Phone"=>$Phone,"Camp_UserBio"=>$UserBio,
											"Camp_BankName"=>$BankName,"Camp_BankAddress"=>$BankAddress,"Camp_AccountType"=>$AccountType,"Camp_AccountName"=>$AccountName,
											"Camp_AccountNumber"=>$AccountNumber,"Camp_PreferredPaymentMode"=>$PaymentMode,"Camp_BankEmail"=>$BankEmail,"Camp_BankPhone"=>$BankPhone,
											"Camp_Status"=>$Status,"Camp_SearchTags"=>$SearchTags,"Camp_WebMasterComment"=>$WebMasterComment,"Camp_RUID"=>"0","Camp_NPO_EIN"=>$NPOEIN,
											"Camp_SocialMediaUrl"=>$SocialMediaUrl,"Camp_SalesForceID"=>$SalesForceID,"Camp_IsPrivate"=>$IsPrivate,
											"Camp_StylingTemplateName"=>$StylingTempName,"Camp_StylingDetails"=>$StylingDetail,"Camp_MinimumDonationAmount"=>$MinDonationAmt,
											"Camp_CreatedDate"=>getDateTime(),"Camp_LastUpdatedDate"=>getDateTime(),"Camp_LastUpdatedUserIP"=>$this->UserIP);
			if($this->CI_CID > 0 )
			{
				unset($this->InputDataArray['Camp_CreatedDate']);	
			}							
		}
		
		
		private function CampaignDetail()
		{
			EnPException::writeProcessLog('Campaign_Controller :: CampaignDetail fuction is used get all data');
			
			$campaignArray		= array("C.Camp_ID","C.Camp_Title","C.Camp_StartDate","C.Camp_EndDate","C.Camp_CP_City",
											"C.Camp_CP_Email","C.Camp_NPO_EIN","I.Camp_Image_Name","I.Camp_Image_Title","Camp_UserBio");
			$campaignfilter		= array("Camp_ID"=>1,"Camp_Deleted"=>'0',"Camp_IsPrivate"=>'0',"Camp_Status"=>'1');
			
			$GetCampaignDetail	= $this->objCamp->GetCampaign($campaignArray,$campaignfilter);
		   // dump($GetCampaignDetail);
			if($GetCampaignDetail<>NULL)
			{
				$this->GetImageDetail();
				
				$this->GetCommentDetail();
				
				$this->GetVideoDetail();
			}
			$this->tpl->assign($GetCampaignDetail);
			
			$this->tpl->assign('arrBottomInfo',$this->objCMN->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCMN->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCMN->GetPageCMSDetails(BOTTOM_META));
			//$this->tpl->assign($this->objCMN->GetPageCMSDetails('donationcategory'));
		}
		
		private function GetImageDetail()
		{
			$imageArray			=	array("Camp_Image_Name","Camp_Image_Title","Camp_Image_Type","Camp_Image_SortOrder");
			$imageParam			=	array("Camp_Image_CampID"=>$this->CI_CID,"Camp_Image_ShowOnWebsite"=>'1');
			$CImagesDetail 		= 	$this->objCamp->GetCampaignDetail('campaignimages',$imageArray,$imageParam);
			$this->tpl->assign('ImageDetail',$CImagesDetail);
		}
		
		
		private function GetCommentDetail()
		{
			$commentArray		=	array('Camp_Cmt_UserName','Camp_Cmt_Comment','Camp_Cmt_CreatedDate','Camp_Cmt_LastUpdatedDate');
			$commentParam		=	array("Camp_Cmt_CampID"=>$this->CI_CID,"Camp_Cmt_ShowOnWebsite"=>'1');
			$CCommentDetail 	= 	$this->objCamp->GetCampaignDetail('campaigncomments',$commentArray,$commentParam);
			//dump($CCommentDetail);
			$this->tpl->assign('CommentDetail',$CCommentDetail);
		}
		
		private function GetVideoDetail()
		{
			$videoDataArray		=	array("Camp_Video_Title","Camp_Video_EmbedCode","Camp_Video_SortOrder");
			$videoParam			=	array("Camp_Video_CampID"=>$this->CI_CID,"Camp_Video_ShowOnWebsite"=>'1');
			$CVideoDetail 		= 	$this->objCamp->GetCampaignDetail('campaignvideo',$videoDataArray,$videoParam);
			$this->tpl->assign('VideoDetail',$CVideoDetail);
		}

		private function GetCampaignCategory()
		{
			$DataArray	= array("NPOCat_ID",
								"NPOCat_ParentID",
								"NPOCat_DisplayName_EN",
								"NPOCat_DisplayName_ES",
								"NPOCat_Image_Name",
								"NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_SortOrder","NPOCat_ShowOnWebsite");
			$Condition	= " AND NPOCat_ShowOnWebsite='1' ";
			$Category = $this->objCamp->GetCampaignCategory($DataArray,$Condition);
			//dump(LANG_ID);
			//dump($Category);
			/*foreach($Category as &$val)
			{
				$val['CampCat_DisplayName']	= $val["CampCat_DisplayName_"._DBLANG_];	
				//unset($val["CampCat_DisplayName_"._DBLANG_]);
			}*/
			
			$this->tpl->assign("Category",$Category);
			
			
			$this->tpl->assign('arrBottomInfo',$this->objCMN->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCMN->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCMN->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCMN->GetPageCMSDetails('donationcategory'));
		}
		
		private function GetCampaignsList()
		{
			$Condition 	= " AND Camp_Status='1' AND Camp_Deleted='0' AND Camp_IsPrivate='0'";
			$DataArray	= array("Camp_ID","Camp_Title","Camp_DonationGoal","Camp_DonationReceived","Camp_MinimumDonationAmount");	
			$Campains	= $this->objCamp->GetCampaignsList($DataArray,$Condition);
			$Newest		= array();
			$Endest		= array();
			$Popular	= array();
			foreach($Campains as $val)
			{
				$FourthPart	= round($val['Camp_DonationGoal']/4);
				if($val['Camp_DonationReceived'] <= $FourthPart)
				{
					$Newest[]	= $val;	
				}
				else
				{
					$Endest[]	= $val;	
				}	
			}
			
			foreach($this->GetPopularCampaigns() as $popularval)
			{
				$Popular[]	= $popularval;
			}
			$Campaigns	= array('newest'=>$Newest,'endest'=>$Endest,'popular'=>$Popular);
			//dump($Campaigns);
		}
		
		private function GetPopularCampaigns()
		{
			$Condition 	= " AND Camp_Status='1' AND Camp_Deleted='0' AND Camp_IsPrivate='0'";
			$DataArray	= array("Camp_ID","Camp_Title","Camp_DonationGoal","Camp_DonationReceived","Camp_MinimumDonationAmount");	
			$PoularCampains	= $this->objCamp->GetPopularCampaigns($DataArray,$Condition);
			return $PoularCampains;
		}
		
	   
		  private function setErrorMsg($ErrCode,$MsgType=1)
		  {
				EnPException::writeProcessLog('Campaign_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
				$this->P_status=0;
				$this->P_ErrorCode.=$ErrCode.",";
				$this->P_ErrorMessage=$ErrCode;
				$this->MsgType=$MsgType;
		  }
		  
		  private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		  {
				EnPException::writeProcessLog('Registration_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
				$this->P_status=1;
				$this->P_ConfirmCode=$ConfirmCode;
				$this->P_ConfirmMsg=$ConfirmCode;
				$this->MsgType=$MsgType;
		  }
		  
		  private function SetStatus($Status,$Code)
		  {
			   if($Status)
			   {
						$messageParams=array("msgCode"=>$Code,
													 "msg"=>"Custom Confirmation message",
													 "msgLog"=>0,									
													 "msgDisplay"=>1,
													 "msgType"=>2);
						EnPException::setConfirmation($messageParams);
			   }
			   else
			   {
					$messageParams=array("errCode"=>$Code,
											 "errMsg"=>"Custom Confirmation message",
											 "errOriginDetails"=>basename(__FILE__),
											 "errSeverity"=>1,
											 "msgDisplay"=>1,
											 "msgType"=>1);
						EnPException::setError($messageParams);
				}
		  }
		  
		
	}
?>