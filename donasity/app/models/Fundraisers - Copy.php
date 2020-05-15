<?php
	class Fundraisers_Model extends Model
	{
		
	public 	$F_Camp_ID,$F_Camp_RUID,$F_Camp_Status,$F_Camp_Level_ID,$P_ErrorCode,$P_ErrorMessage,$P_status,$P_MsgType,$P_ConfirmCode,$P_ConfirmMsg;
	
	public $F_Camp_StylingTemplateName,$F_Camp_CP_FirstName,$F_Camp_CP_LastName,$F_Camp_CP_Address1,
		   $F_Camp_CP_Address2,$F_Camp_CP_City,$F_Camp_CP_State,$F_Camp_CP_Country,$F_Camp_CP_ZipCode,
		   $F_Camp_CP_Email,$F_Camp_CP_Phone,$F_Camp_UrlFriendlyName; // For ProcessFundraiserSetup function
	
	public $F_Camp_Cat_ID,$F_Camp_Title,$F_Camp_ShortDescription,$F_Camp_DonationGoal,
		   $F_Camp_SpecifiedDate,$F_Camp_DateSpecified,$F_Camp_StartDate,$F_Camp_EndDate,
		   $F_Camp_IsPrivate,$F_Camp_Location_City,$F_Camp_Location_State,$F_Camp_Location_Country,$F_Camp_Location_Logitude,$F_Camp_Location_Latitude,
		   $F_Camp_Duration_Days; // For ProcessFundraiserSetup_1 function
	
	public $F_Camp_DescriptionHTML,$F_Camp_SocialMediaUrl;// For ProcessFundraiserSetup_2 function
	
	public $F_Camp_Chk_AccountNumber,$F_Camp_AccountNumber,$F_Camp_NonChk_AccountNumber,$F_Camp_NonAccountNumber,$F_Camp_Stripe_ConnectedID,$F_Camp_Stripe_Status,$F_Camp_Stripe_Response,$F_Camp_PaymentMode; 
	
	
	public 	$F_camp_thumbImage,$F_camp_bgImage,$F_Camp_UserBio,$F_Camp_SearchTags,$F_Camp_WebMasterComment,$F_Camp_NPO_EIN,$F_Camp_SalesForceID,$F_Camp_StylingDetails,$F_Camp_MinimumDonationAmount,$F_Camp_Locale,$F_Camp_Deleted,$Video,$Image,$VideoCode;	
	

	public $F_Camp_StrripID;
		private $F_Camp_CreatedDate,$F_Camp_LastUpdatedDate,$F_camp_ProcessLog;
		
		
		public function __construct()
		{
			$this->P_status=1;
		}
		
		public function GetFundraiserDetails($DataArray=array('Camp_ID','Camp_RUID','Camp_Status','Camp_Level_ID'),$where)
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$Fields=implode(",",$DataArray);
			$sql="SELECT $Fields FROM ".TBLPREFIX."campaign ";
			if($where<>'')
			{
				$sql.="WHERE 1=1 ".$where;
			}else{
				$sql.="WHERE Camp_ID=".$this->F_Camp_ID;
			}
			$row = db::get_all($sql);
			//echo($sql);exit;
			return $row;
		}
		
		public function GetFundraiserDetails1()
		{
	
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT  Camp_ID,".TBLPREFIX."campaignlevel.Camp_Level_ID,Camp_RUID,Camp_Status,Camp_Level_DetailJSON FROM ".TBLPREFIX."campaign LEFT JOIN ".TBLPREFIX."campaignlevel ON ".TBLPREFIX."campaign.Camp_Level_ID=".TBLPREFIX."campaignlevel.Camp_Level_ID WHERE Camp_ID=".$this->F_Camp_ID;
			$row = db::get_all($sql);
			$row[0]['Camp_Level_DetailJSON']=json_decode($row[0]['Camp_Level_DetailJSON'],true);
			return $row;
		}
		
		public function GetCampaignLevelDetail($dataarray=array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON'),$where)
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$Fields=implode(",",$dataarray);
			
			$sql="SELECT ".$Fields." FROM ".TBLPREFIX."campaignlevel WHERE 1=1 ";
			$row = db::get_all($sql.$where);
			$row[0]['Camp_Level_DetailJSON']=json_decode($row[0]['Camp_Level_DetailJSON'],true);
			return $row;
		}
		
		public function GetNPOCategoryList()
		{
				EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT NPOCat_ID,NPOCat_ParentID,NPOCat_DisplayName_"._DBLANG_.",NPOCat_URLFriendlyName,NPOCat_ShowOnWebsite FROM ".TBLPREFIX."npocategories WHERE NPOCat_ShowOnWebsite='1' AND NPOCat_ParentID='0' ORDER BY NPOCat_SortOrder";
	
			$row = db::get_all($sql);
		
			if(count($row)<=0)
			{
				 $this->setErrorMsg("");
			}
			return $row;
		}
		public function FundraiserInsert()
		{
			$data=array("Camp_Level_ID"=>2,
						"Camp_RUID"=>'53',
						"Camp_Duration_Days"=>'90',
						"Camp_Status"=>0);
			
			db::insert(TBLPREFIX."campaign", $data);
			$id = db::get_last_id();
			echo keyEncrypt($id);exit;
		}
		
		public function ProcessFundraiserSetup()
		{
			if($this->P_status == 1) $this->ValidateFundraiserSetupParameter();
			if($this->P_status == 1) $this->UpdateFundraiserSetup();
		}
		private function ValidateFundraiserSetupParameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
			if($this->P_status == 1 && $this->F_Camp_StylingTemplateName=='')$this->setErrorMsg('E13001');
			
		}
		private function UpdateFundraiserSetup()
		{
			
			$DataArray=array("Camp_StylingTemplateName"=>$this->F_Camp_StylingTemplateName,
						"Camp_CP_FirstName"=>$this->F_Camp_CP_FirstName,"Camp_CP_LastName"=>$this->F_Camp_CP_LastName,
						"Camp_CP_Address1"=>$this->F_Camp_CP_Address1,"Camp_CP_Address2"=>$this->F_Camp_CP_Address2,
						"Camp_CP_City"	=>$this->F_Camp_CP_City,"Camp_CP_State"=>$this->F_Camp_CP_State,
						"Camp_CP_Country"=>$this->F_Camp_CP_Country,"Camp_CP_ZipCode"=>$this->F_Camp_CP_ZipCode,
						"Camp_CP_Email"=>$this->F_Camp_CP_Email,"Camp_CP_Phone"=>$this->F_Camp_CP_Phone,
						"Camp_Status"=>$this->F_Camp_Status);
			//dump($DataArray);
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
		}
		public function ProcessFundraiserStep_1()
		{
			if($this->P_status == 1) $this->ValidateFundraiserStep_1Parameter();
			if($this->P_status == 1) $this->DateSetup();
			if($this->P_status == 1) $this->UpdateFundraiserStep_1();
			if($this->P_status == 1) $this->ProcessUploadImageStep1();
			
			if($this->P_status == 1) $this->UpdateProcessLog();
			
		}
		private function ValidateFundraiserStep_1Parameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
			if($this->P_status == 1 && $this->F_Camp_Cat_ID=='')$this->setErrorMsg('E13002');
			if($this->P_status == 1 && $this->F_Camp_Title=='')$this->setErrorMsg('E13003');
			if($this->P_status == 1 && $this->F_Camp_DonationGoal=='')$this->setErrorMsg('E13004');
			if($this->P_status == 1 && $this->F_Camp_DateSpecified==2 && F_Camp_SpecifiedDate=='')$this->setErrorMsg('E13006');

		}
		private function DateSetup()
		{
		  
		   if($this->F_Camp_DateSpecified==2)
		   {
			  $this->F_Camp_StartDate=$this->F_Camp_SpecifiedDate;
			   
		    }
		
		}
		private function UpdateFundraiserStep_1()
		{
			$DataArray=array("Camp_Cat_ID"=>$this->F_Camp_Cat_ID,
						"Camp_Title"=>$this->F_Camp_Title,
						"Camp_UrlFriendlyName"=>$this->F_Camp_UrlFriendlyName,
						"Camp_ShortDescription"=>$this->F_Camp_ShortDescription,
						"Camp_DonationGoal"=>$this->F_Camp_DonationGoal,
						"Camp_StartDate"=>date("Y-m-d", strtotime($this->F_Camp_StartDate)),
						"Camp_EndDate"=>$this->F_Camp_EndDate,
						"Camp_Duration_Days"=>$this->F_Camp_Duration_Days,
						"Camp_Location_City"=>$this->F_Camp_Location_City,
						"Camp_Location_State"=>$this->F_Camp_Location_State,
						"Camp_Location_Country"=>$this->F_Camp_Location_Country,
						"Camp_Location_Logitude"=>$this->F_Camp_Location_Logitude,
						"Camp_Location_Latitude"=>$this->F_Camp_Location_Latitude,
						"Camp_IsPrivate"=>$this->F_Camp_IsPrivate,
						"Camp_Status"=>$this->F_Camp_Status,
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Locale"=>GetUserLocale()
						);
					
			
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
			
		}
			public function ProcessUploadImageStep1()
			{
			
				if(isset($this->Image['name']) && $this->Image['name']!='')
				{
				
				
				$objFile=LoadLib('UploadFile');
				$objFile->phyPath=CAMPAIGN_MAIN_IMAGE_DIR;
				$objFile->Uploadfile=$this->Image;
				$objFile->ext=file_ext($this->Image['name']);
				$objFile->customName=$this->F_Camp_ID;
				$this->Image['name']=$objFile->customName;
				
				$rename=$objFile->customName.'.'.$objFile->ext;
				$filename = $this->F_Camp_ID.'.'.$objFile->ext;
			    $Image=$objFile->ProcessUploadFile();
				$DataArray=array("camp_thumbImage"=>$filename);
				
				db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			
				
				if(db::is_row_affected())
				{
					$this->setConfirmationMsg('C13000');
				}else
				{
					$this->setErrorMsg('E13007');
				}
								
			}
			}
		
		
		
		public function ProcessFundraiserStep_2()
		{
			if($this->P_status == 1) $this->ValidateFundraiserStep_2Parameter();
			if($this->P_status == 1) $this->UpdateFundraiserStep_2();
			$this->F_camp_ProcessLog='step2 copleted';
			if($this->P_status == 1) $this->UpdateProcessLog();
		}
		
		private function ValidateFundraiserStep_2Parameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
		}
		
		private function UpdateFundraiserStep_2()
		{
			$DataArray=array("Camp_DescriptionHTML"=>$this->F_Camp_DescriptionHTML,
						"Camp_SocialMediaUrl"=>$this->F_Camp_SocialMediaUrl,
						"Camp_Status"=>$this->F_Camp_Status,
						"Camp_Locale"=>GetUserLocale(),
						"Camp_LastUpdatedDate"=>getDateTime());
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
		}
		
		public function ProcessFundraiserStep_3()
		{
			
			if($this->P_status == 1) $this->ValidateFundraiserStep_3Parameter();
			
			if($this->P_status == 1) $this->UpdateFundraiserStep_3();
			$this->F_camp_ProcessLog='step3 copleted';
			if($this->P_status == 1) $this->UpdateProcessLog();
		}
		
		private function ValidateFundraiserStep_3Parameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
			
		}
		
		private function UpdateFundraiserStep_3()
		{
			$DataArray=array("Camp_Status"=>$this->F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale());
			
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if($this->VideoCode<>'')
			db::insert(TBLPREFIX.'campaignvideo',array("Camp_Video_CampID"=>$this->F_Camp_ID,"Camp_Video_EmbedCode"=>$this->VideoCode));
			
			if(db::is_row_affected())
			{
			
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
		}
		public function ProcessFundraiserStep_4()
		{
			
			if($this->P_status == 1) $this->ValidateFundraiserStep_4Parameter();
			if($this->P_status == 1) $this->CheckUUIDForNPO();
			if($this->P_status == 1) $this->UpdateFundraiserStep_4();
		}
		private function CheckUUIDForNPO()
		{
			
			$sql="SELECT Stripe_ClientID,Status FROM ".TBLPREFIX."npouserrelation WHERE NPOEIN='".$this->F_Camp_NonAccountNumber."' AND Status ='1'";
			
			$row = db::get_all($sql);
			if(count($row)<>1)
			{
				$this->setErrorMsg('E13007');
			}
			else
			{
				$this->F_Camp_StrripID=$row[0]['Stripe_ClientID'];
			}
		}
		private function ValidateFundraiserStep_4Parameter()
		{
			dump($this->F_Camp_ID);
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
			
			if($this->P_status == 1 && $this->F_Camp_NonAccountNumber=='')$this->setErrorMsg('E13000');
		}
		private function UpdateFundraiserStep_4()
		{
			//Camp_Stripe_Status,Camp_Stripe_ConnectedID
			$DataArray=array("Camp_Status"=>$this->F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale(),'Camp_Stripe_Status'=>'1','Camp_Stripe_ConnectedID'=>$this->F_Camp_StrripID);
			
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
		}
		public function ProcessFundraiserComplete()
		{
			if($this->P_status == 1) $this->ValidateFundraiserCompleteParameter();
			if($this->P_status == 1) $this->UpdateFundraiserComplete();
		}
		private function ValidateFundraiserCompleteParameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
		}
		private function UpdateFundraiserComplete()
		{
			$DataArray=array("Camp_Status"=>$this->F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale());
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13007');
			}
		}
		
	
		public function ProcessUploadImage()
		{
		   if($this->P_status == 1)$this->ValidateImageParameter();
		   if($this->P_status == 1)$this->UploadImage();
		}
		
		private function ValidateImageParameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
		    if($this->P_status == 1 && $this->Image['name']=='')$this->setErrorMsg('E13007');
		}
		private function UploadImage()
		{
			$sql="SELECT Camp_Image_ID FROM ".TBLPREFIX."campaignimages ORDER BY Camp_Image_ID DESC LIMIT 1";
			$row = db::get_row($sql);
			$Iname=$row['Camp_Image_ID']+1;
			
			try
			{
				$objFile=LoadLib('UploadFile');
				$objFile->phyPath=CAMPAIGN_LARGE_IMAGE_DIR;
				$objFile->Uploadfile=$this->Image;
				$objFile->ext=file_ext($this->Image['name']);
				$objFile->customName=$Iname;
				$this->Image['name']=$objFile->customName;
				$Image=$objFile->ProcessUploadFile();
				$rename=$objFile->customName.'.'.$objFile->ext;
				
				
				unset($objFile);
				
				$objFile	= LoadLib('resize_image');
				$ThumbImage	= CAMPAIGN_THUMB_IMAGE_DIR.$rename;
				
				$objFile	= new resize_image(CAMPAIGN_LARGE_IMAGE_DIR.$rename);
				$objFile ->resizeImage(70, 70, 'crop');
				$objFile ->saveImage($ThumbImage, 100);
				db::insert(TBLPREFIX.'campaignimages',array("Camp_Image_CampID"=>$this->F_Camp_ID,"Camp_Image_Name"=>$rename));
								
				unset($objFile);
			}catch(Exception $e){
				$this->setErrorMsg('E13009');
			}
		}
		
		public function ProcessUploadVideo()
		{ 
			if($this->P_status == 1)$this->ValidateVideoParameter();
		    if($this->P_status == 1)$this->UploadVideo();
		}
		
		private function ValidateVideoParameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
		    if($this->P_status == 1 && $this->Video['name']=='')$this->setErrorMsg('E13008');
		}
		
		private function UploadVideo()
		{
			
			$sql="SELECT Camp_Video_ID FROM ".TBLPREFIX."campaignvideo ORDER BY Camp_Video_ID DESC LIMIT 1";
			$row = db::get_row($sql);
			$Iname=$row['Camp_Video_ID']+1;
			try
			{
				
				$info = new SplFileInfo($this->Video['name']);
				$ext=$info->getExtension();
				$Iname.='.'.$ext;
				
				move_uploaded_file($this->Video['tmp_name'],CAMPAIGN_VIDEO_DIR.$Iname);
				db::insert(TBLPREFIX.'campaignvideo',array("Camp_Video_CampID"=>$this->F_Camp_ID,"Camp_Video_File"=>$Iname));
			}catch(Exception $e){
				$this->setErrorMsg('E13010');
			}
		}
		public function CampaignImages()
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT Camp_Image_ID,Camp_Image_CampID,Camp_Image_Name FROM ".TBLPREFIX."campaignimages WHERE Camp_Image_CampID='".$this->F_Camp_ID."'";
			$row = db::get_all($sql);
			if($row>0) 
			{
			for($i=0;$i<count($row);$i++)
			{
				$row[$i]['image_url']=CheckImage(CAMPAIGN_THUMB_IMAGE_DIR,CAMPAIGN_THUMB_IMAGE_URL,'',$row[$i]['Camp_Image_Name']);
			}
			
			return $row;
			}
		}
		public function CampaignVideos()
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$sql="SELECT Camp_Video_ID,Camp_Video_CampID,Camp_Video_Title,Camp_Video_File,Camp_Video_EmbedCode FROM ".TBLPREFIX."campaignvideo WHERE Camp_Video_CampID='".$this->F_Camp_ID."'";
			$row = db::get_all($sql);
			if($row>0) 
			{
			for($i=0;$i<count($row);$i++)
			{
				$row[$i]['video_url']=CheckImage(CAMPAIGN_VIDEO_DIR,CAMPAIGN_VIDEO_URL,'',$row[$i]['Camp_Video_File']);
			}
			
			return $row;
			}
		}
		public function UpdateProcessLog()
		{
			
			$sql="SELECT camp_ProcessLog FROM ".TBLPREFIX."campaign WHERE Camp_Video_CampID='".$this->F_Camp_ID."'";
			$row = db::get_row($sql);
			$this->F_camp_ProcessLog=$row['camp_ProcessLog'].$this->F_camp_ProcessLog.'#';
			$DataArray=array("camp_ProcessLog"=>$this->F_camp_ProcessLog,"Camp_LastUpdatedDate"=>getDateTime());
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
		
		}
		private function setErrorMsg($ErrCode,$MsgType=1,$Status=0)
		{
			EnPException::writeProcessLog('Fundraisers_Model setErrorMsg function Call for Error Code :: '.$ErrCode);
			$this->P_ErrorCode=$ErrCode;
			$this->P_ErrorMessage=$ErrCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}
		private function setConfirmationMsg($ConfirmCode,$MsgType=2,$Status=1)
		{
			EnPException::writeProcessLog('Fundraisers_Model setConfirmationMsg function Call For Confirmation Code :: '.$ConfirmCode);
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}

	}
?>