<?php
	class Fundraisers_Model extends Model
	{
		public 	$F_Camp_ID,$F_Camp_RUID,$P_ErrorCode,$P_ErrorMessage,$P_status,$P_MsgType,$P_ConfirmCode,$P_ConfirmMsg;
		
		public $F_Camp_CP_Email,$F_Camp_CP_Phone,$F_Camp_UrlFriendlyName; // For ProcessFundraiserSetup function
		
		public $F_Camp_Cat_ID,$F_Camp_Title,$F_Camp_ShortDescription,$F_Camp_DonationGoal,$F_Camp_Code,
			   $F_Camp_SpecifiedDate,$F_Camp_DateSpecified,$F_Camp_StartDate,$F_Camp_EndDate,
			   $F_Camp_IsPrivate,$F_Camp_Location_City,$F_Camp_Location_State,$F_Camp_Location_Country,$F_Camp_Location_Logitude,$F_Camp_Location_Latitude,
			   $F_Camp_Duration_Days; // For ProcessFundraiserSetup_1 function
		
		public $F_Camp_DescriptionHTML,$F_Camp_SocialMediaUrl;// For ProcessFundraiserSetup_2 function
		
		public $F_Camp_Chk_AccountNumber,$F_Camp_AccountNumber,$F_Camp_NonChk_AccountNumber,$F_Camp_NonAccountNumber,$F_Camp_Stripe_ConnectedID,$F_Camp_Stripe_Status,$F_Camp_Stripe_Response,$F_Camp_PaymentMode; 
		
		public 	$F_camp_thumbImage,$F_camp_bgImage,$F_Camp_UserBio,$F_Camp_SearchTags,$F_Camp_WebMasterComment,$F_Camp_SalesForceID,$F_Camp_StylingDetails,$F_Camp_MinimumDonationAmount,$F_Camp_Locale,$F_Camp_Deleted,$Video,$Image,$camp_bgImage,$VideoCode;	
		
	
		public $F_Camp_StrripID,$UserId,$F_Camp_NPO_DedCode,$F_Camp_StripeStatus,$F_Camp_NPO_EIN;
		private $F_Camp_CreatedDate,$F_Camp_LastUpdatedDate,$F_camp_ProcessLog;
			
		public $F_FundraiserDetail,$F_Fundraiser_UserId,$F_WhereCondition; //FundraisersDetail	
		
		public $FC_FundraiserId,$FC_CommentId,$FC_CommentContent,$FC_approveStatus,$FC_PageNo,$FC_PageLimit,$FC_TotalRecord;// FundraiserComment
			
		public $Camp_Video_ID,$Camp_Image_ID;
		
		public $F_Comment,$F_UserName;
		
		
		public $DataArray;
		public $LevelID;
		public $Title;
		public $StyleTemplateName;
		public $StyleColorArray;
		public $Camp_StylingDetails;
		public $Camp_Level_ID;
		public function __construct()
		{
			$this->P_status=1;
		}

		public function GetFundraiserDetails($DataArray=array('Camp_ID','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_StylingTemplateName'),$where='')
		{
			EnPException::writeProcessLog('Fundraisers_Model'.__FUNCTION__.'called');
			$Fields=implode(",",$DataArray);
			//echo $where;
			$sql="SELECT $Fields FROM ".TBLPREFIX."campaign ";
			if($where!='')
			{
				$sql.="WHERE 1=1 ".$where;
			}else{
				$sql.="WHERE Camp_Deleted!='1' AND  Camp_ID=".$this->F_Camp_ID;
			}
			//echo $sql; //exit;
			$row = db::get_all($sql);
			return $row;
		}
		
		private function CreateFundraiserArray($fundraiserlist)
		{
			if(isset($fundraiserlist) && count($fundraiserlist) > 0)
			{
				foreach($fundraiserlist as $key=>$val)
				{
					//make a new title in the case of title empty
						if(isset($val['Camp_Title']) && $val['Camp_Title']!='')
						{
							$this->Camp_Title = $val['Camp_Title'];
						}
						else 
						{	
							$this->CampTitle($val['Camp_Level_ID']);
						}	
					//end of code
					$fundraiserlist_array[$val['Camp_ID']] = array('Camp_ID'=>$val['Camp_ID'],'Camp_TeamUserType'=>$val['Camp_TeamUserType'],'Camp_CreatedDate'=>$val['Camp_CreatedDate'],'Camp_Title'=>$this->Camp_Title,'camp_thumbImage'=>$val['camp_thumbImage'],'Camp_RUID'=>$val['Camp_RUID'],'Camp_Status'=>$val['Camp_Status'],'Camp_Level_ID'=>$val['Camp_Level_ID'],'Camp_UrlFriendlyName'=>$val['Camp_UrlFriendlyName'],'Camp_DonationGoal'=>$val['Camp_DonationGoal'],'Camp_DonationReceived'=>$val['Camp_DonationReceived'],'Donationpercentage'=>$val['Donationpercentage'],'Camp_StartDate'=>$val['Camp_StartDate']);
				}
			}
			return $fundraiserlist_array;
		}
		
		public function GetFundraiserUserDetails($DataArray=array('*'),$where='')
		{
				$Fields=implode(",",$DataArray);
				$Sql="SELECT $Fields
				FROM ".TBLPREFIX."campaign C
				LEFT JOIN ".TBLPREFIX."registeredusers RU ON(C.Camp_RUID=RU.RU_ID)				
				 WHERE 1=1 ";
				//echo $Sql.$where;exit;
				if($where!='')
					$Sql .= $where;
				$F_FundraiserDetail = db::get_all($Sql);
				return count($F_FundraiserDetail)>0?$F_FundraiserDetail:NULL;
				//dump($this->F_FundraiserDetail);
			
		}
		
		public function getTitle()
		{
			$sql = "SELECT Camp_Level_Name FROM dns_campaignlevel WHERE Camp_Level_ID=".$this->LevelID;
			$row = db::get_all($sql);
			$this->Title = isset($row[0]['Camp_Level_Name'])?$row[0]['Camp_Level_Name']:'';
			
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
		public function FundraiserInsert($level)
		{
			$data=array("Camp_Level_ID"=>$level,
						"Camp_RUID"=>$this->UserId,
						"Camp_Duration_Days"=>'90',
						"Camp_CreatedDate"=>getDateTime(),
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Status"=>0);
			
			db::insert(TBLPREFIX."campaign", $data);
			$id = db::get_last_id();
			return $id;
		}
		
	
		
		public function ProcessFundraiserSetup()
		{
			if($this->F_Camp_NonAccountNumber<>'' && $this->DataArray['Camp_Status']==5)
			{
				$this->CheckUUIDForNPO();
			}
			if($this->DataArray['Camp_Status']==2)
			{
				$this->ProcessUploadImageStep1();
				if(isset($this->camp_bgImage['name']) && $this->camp_bgImage['name']!='')
				{
					$this->ProcessBackgroundImage();
				}
			}
			
			if($this->P_status == 1)
			{
				db::update(TBLPREFIX.'campaign',$this->DataArray,'Camp_ID='.$this->F_Camp_ID);
				if(db::is_row_affected())
				{
					//$this->UpdateProcessLog();
					$this->setConfirmationMsg('C13000');
					
				}else
				{
					$this->setErrorMsg('000');
				}
			}
			
			
		}
		
		public function UpdateCampaignTemplateName()
		{
			$db_array = array('Camp_StylingTemplateName'=>$this->StyleTemplateName);
			db::update(TBLPREFIX.'campaign',$db_array,'Camp_ID='.$this->F_Camp_ID);
			return TRUE;
		}
		
		public function ProcessUploadImageStep1()
		{
			if(isset($this->Image['name']) && $this->Image['name']!='')
			{
				$objFile=LoadLib('UploadFile');
				$objFile->phyPath=CAMPAIGN_MAIN_IMAGE_DIR;
				$objFile->Uploadfile=$this->Image;
				$objFile->ext=file_ext($this->Image['name']);
				$objFile->customName= strUnique();
				$this->Image['name']=$objFile->customName;				
				$filename = $objFile->customName.'.'.strtolower($objFile->ext);
				
				$Image=$objFile->ProcessUploadFile();				
				
				$objFile->load(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
				$objFile->GetAspectRatio(253);
				$objFile->save(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
				
				$objFile->load(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
				$objFile->crop(0,0,253,253);
				$objFile->save(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
				$DataArray=array("camp_thumbImage"=>$filename);
				db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			
				if(db::is_row_affected())
				{
					$this->setConfirmationMsg('C13005');
				}else
				{
					$this->setErrorMsg('E13011');
				}							
			}
		}
		
		
		public function ProcessBackgroundImage()
		{
			if(isset($this->camp_bgImage['name']) && $this->camp_bgImage['name']!='')
			{
				$objFile=LoadLib('UploadFile');
				$objFile->phyPath=CAMPAIGN_BACKGROUND_IMAGE_DIR;
				$objFile->Uploadfile=$this->camp_bgImage;
				$objFile->ext=file_ext($this->camp_bgImage['name']);
				$objFile->customName= strUnique();
				$this->camp_bgImage['name']=$objFile->customName;				
				$filename = $objFile->customName.'.'.strtolower($objFile->ext);
		
				$Image=$objFile->ProcessUploadFile();
		
		
				$objFile->load(CAMPAIGN_BACKGROUND_IMAGE_DIR.$filename);
				//$objFile->GetAspectRatio(253);
				$objFile->save(CAMPAIGN_BACKGROUND_IMAGE_DIR.$filename);
		
				$objFile->load(CAMPAIGN_BACKGROUND_IMAGE_DIR.$filename);
				//$objFile->crop(0,0,253,253);
				$objFile->save(CAMPAIGN_BACKGROUND_IMAGE_DIR.$filename);
				$DataArray=array("camp_bgImage"=>$filename);
				db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
					
				if(db::is_row_affected())
				{
					$this->setConfirmationMsg('C13005');
				}else
				{
					$this->setErrorMsg('E13011');
				}
					
			}
		}
		
		private function CheckUUIDForNPO()
		{
			$sql="SELECT NUR.Status,NUR.Stripe_ClientID,NUR.Status,ND.NPO_DedCode,NUR.NPOEIN FROM ".TBLPREFIX."npouserrelation as NUR INNER JOIN ".TBLPREFIX."npodetails as ND ON  NUR.NPOID=ND.NPO_ID WHERE NUR.NPOEIN='".$this->F_Camp_NonAccountNumber."' AND NUR.Status ='1'";
			$row = db::get_all($sql);
			
			if(count($row)<>1)
			{
				$this->setErrorMsg('E13016');
			}
			else
			{
				$this->DataArray['Camp_NPO_EIN']=$row[0]['NPOEIN'];
				$this->DataArray['Camp_Stripe_Status']=$row[0]['Status'];
				$this->DataArray['Camp_Stripe_ConnectedID']=$row[0]['Stripe_ClientID'];
				$this->DataArray['Camp_TaxExempt']=$row[0]['NPO_DedCode'];
				
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
		    if($this->P_status == 1 && $this->Image['name']=='')$this->setErrorMsg('E13018');
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
				$objFile->customName=strUnique();
				$this->Image['name']=$objFile->customName;
				$Image=$objFile->ProcessUploadFile();
				$rename=$objFile->customName.'.'.strtolower($objFile->ext);
				
				unset($objFile);
				
				$objFile	= LoadLib('resize_image');
				$ThumbImage	= CAMPAIGN_THUMB_IMAGE_DIR.$rename;
				
				$objFile	= new resize_image(CAMPAIGN_LARGE_IMAGE_DIR.$rename);
				$objFile ->resizeImage(70, 70, 'crop');
				$objFile ->saveImage($ThumbImage, 100);
				db::insert(TBLPREFIX.'campaignimages',array("Camp_Image_CampID"=>$this->F_Camp_ID,"Camp_Image_Name"=>$rename));
				$id = db::get_last_id();				
				if($id=='')
				{
					$this->setErrorMsg('E13009');
				}
				else
				{
					$this->setConfirmationMsg('C13003');
				}
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
		    //if($this->P_status == 1 && $this->Video['name']=='')$this->setErrorMsg('E13008');
		}
		
		private function UploadVideo()
		{
			$sql="SELECT Camp_Video_ID FROM ".TBLPREFIX."campaignvideo ORDER BY Camp_Video_ID DESC LIMIT 1";
			$row = db::get_row($sql);
			$Iname=$row['Camp_Video_ID']+1;
			if($this->Video['name']<>'' || $this->VideoCode<>'')
			{
				$info = new SplFileInfo($this->Video['name']);
				$ext=$info->getExtension();
				$Iname.='.'.$ext;
				if($this->Video['name']=='')
				$Iname='';
				move_uploaded_file($this->Video['tmp_name'],CAMPAIGN_VIDEO_DIR.$Iname);
				db::insert(TBLPREFIX.'campaignvideo',array("Camp_Video_CampID"=>$this->F_Camp_ID,"Camp_Video_File"=>$Iname,"Camp_Video_EmbedCode"=>$this->VideoCode));
				$id = db::get_last_id();				
				if($id=='')
				{
					$this->setErrorMsg('E13010');
				}
				else
				{
					$this->setConfirmationMsg('C13004');
				}
			}
			else
			{
				$this->setErrorMsg('E13019');
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
					//if($row[$i]['Camp_Video_EmbedCode']=='')
					//{
						$row[$i]['video_url']=CheckImage(CAMPAIGN_VIDEO_DIR,CAMPAIGN_VIDEO_URL,'',$row[$i]['Camp_Video_File']);
				//	}
					/*else
					{
						$row[$i]['video_url']=$row[$i]['Camp_Video_EmbedCode'];
					}*/
				}
			
			return $row;
			}
		}
		/*public function UpdateProcessLog()
		{
			
			$sql="SELECT camp_ProcessLog FROM ".TBLPREFIX."campaign WHERE Camp_ID='".$this->F_Camp_ID."'";
			$row = db::get_row($sql);
			$this->F_camp_ProcessLog=$row['camp_ProcessLog'].$this->F_camp_ProcessLog.'#';
			$DataArray=array("camp_ProcessLog"=>$this->F_camp_ProcessLog,"Camp_LastUpdatedDate"=>getDateTime());
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
		
		}*/
		############################### Start Code for FundraisersDetails###################
		/*public function ProcessGetFundraiserDetails()
		{
		 	EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
		    if($this->P_status==1) $this->ValidateParameter();
			if($this->P_status==1) $this->GetFundraiser();
			if($this->P_status==1) $this->GetFundraiserImage();
			if($this->P_status==1) $this->GetFundraiserVideo();
			
		}
		
		private function  ValidateParameter()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status==1 && $this->F_Fundraiser_UserId=='') $this->setErrorMsg('E13000');
			if($this->P_status==1 && $this->F_Camp_ID=='') $this->setErrorMsg('E13000');
			return $this->P_status;
		}
		
		private function GetFundraiser()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_ID,Camp_StylingTemplateName,Camp_Title,camp_thumbImage,camp_bgImage,Camp_UrlFriendlyName,
			Camp_ShortDescription,Camp_Description,Camp_NPO_EIN,Camp_DescriptionHTML,ROUND(Camp_DonationGoal) as Camp_DonationGoal,ROUND(Camp_DonationReceived) as Camp_DonationReceived,Camp_StartDate, Camp_EndDate,Camp_SocialMediaUrl 
			FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$this->F_Camp_ID;
			$F_FundraiserDetail = db::get_all($Sql.$this->F_WhereCondition);
			//dump($F_FundraiserDetail);
			if(count($F_FundraiserDetail)>0)
			{
				if($F_FundraiserDetail[0]['Camp_StartDate']!='0000-00-00')
				{
					$F_FundraiserDetail[0]['Camp_StartDate']=date('m/d/Y',strtotime($F_FundraiserDetail[0]['Camp_StartDate']));
				}
				if($F_FundraiserDetail[0]['Camp_EndDate']!='0000-00-00')
				{
					$F_FundraiserDetail[0]['Camp_EndDate']=date('m/d/Y',strtotime($F_FundraiserDetail[0]['Camp_EndDate']));
				}
				$F_FundraiserDetail[0]['Camp_DonationPrice']=round(($F_FundraiserDetail[0]['Camp_DonationReceived']/$F_FundraiserDetail[0]['Camp_DonationGoal'])*100);
				
				$F_FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($F_FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
				$F_FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_thumbImage']);
				
				$F_FundraiserDetail[0]['Camp_BigImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_bgImage']);
				
				$F_FundraiserDetail=$F_FundraiserDetail[0];
			}else
			{
			   $this->setErrorMsg('E14000');
			}
			$this->F_FundraiserDetail["Details"]=$F_FundraiserDetail;
			return $this->F_Camp_ID;
		}
		
		private function GetFundraiserImage()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_Image_CampID,Camp_Image_Name,Camp_Image_Title,Camp_Image_Type FROM ".TBLPREFIX."campaignimages WHERE Camp_Image_CampID=".$this->F_Camp_ID." AND Camp_Image_ShowOnWebsite='1'";
			$Orderby=" Order by Camp_Image_SortOrder";
			$ImageList = db::get_all($Sql.$Orderby);
			if(count($ImageList)>0)
			{
				for($i=0;$i<count($ImageList);$i++)
				{
					$ImageArray[$i]['Camp_Thumb_Image_Full_Path']=CheckImage(CAMPAIGN_THUMB_IMAGE_DIR,CAMPAIGN_THUMB_IMAGE_URL,NO_IMAGE,$ImageList[$i]['Camp_Image_Name']);
					$ImageArray[$i]['Camp_Large_Image_Full_Path']=CheckImage(CAMPAIGN_LARGE_IMAGE_DIR,CAMPAIGN_LARGE_IMAGE_URL,NO_IMAGE,$ImageList[$i]['Camp_Image_Name']);
				}
				$ImageList=$ImageArray;
			}
			$this->F_FundraiserDetail["Image"]=$ImageList;
			return $this->F_Camp_ID;
		}
		private function GetFundraiserVideo()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_Video_CampID,Camp_Video_Title,Camp_Video_File,Camp_Video_EmbedCode 
			FROM ".TBLPREFIX."campaignvideo 
			WHERE Camp_Video_CampID=".$this->F_Camp_ID." AND Camp_Video_ShowOnWebsite='1'";
			$Orderby=" Order by Camp_Video_SortOrder";
			$VideoList = db::get_all($Sql.$Orderby);
			if(count($VideoList)>0)
			{
				for($i=0;$i<count($VideoList);$i++)
				{
					if($VideoList[$i]['Camp_Video_EmbedCode']=='')
					{
					  if($VideoList[$i]['Camp_Video_File']!='')
					  {
						  $VideoPath=CheckFile(CAMPAIGN_VIDEO_DIR,CAMPAIGN_VIDEO_URL,$VideoList[$i]['Camp_Video_File']);
						  if($VideoPath<>'')
						  {
							  $VideoArray[]=array("Path"=>$VideoPath,"Type"=>1);
						  }
					  }
					}else
					{
						$VideoArray[]=array("Path"=>$VideoList[$i]['Camp_Video_EmbedCode'],"Type"=>2);
					}
				}
				$VideoList=$VideoArray;
				
			}
			$this->F_FundraiserDetail["Video"]=$VideoList;
			return $this->F_Camp_ID;
		}*/
		
		############################### End Code for FundraisersDetails###################
		
		
		//======================================================
		
		
		
		public function UpdateTeamFundraiserBasicDetail($DataArray,$Condition)
		{
			db::update(TBLPREFIX.'campaign',$DataArray,$Condition);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}
			else
			{				
				$this->setErrorMsg('E13011');
			}
		}
		
		public function ProcessFundraiserBasicDetail()
		{
  			if($this->P_status==1) $this->ValidateBasicDetailParameter();
			if($this->P_status == 1) $this->DateSetup();
	 		if($this->P_status==1) $this->UpdateFundraiserBasicDetail();
			if($this->P_status==1)
			{
				if($this->F_Camp_Code!='')
				{
					$DataArray = array("Camp_Title"=>$this->F_Camp_Title,
										"Camp_DescriptionHTML"=>$this->F_Camp_DescriptionHTML,
										"Camp_SalesForceID"=>$this->F_Camp_SalesForceID,
										"Camp_SocialMediaUrl"=>$this->F_Camp_SocialMediaUrl,
										"Camp_UrlFriendlyName"=>$this->F_Camp_UrlFriendlyName,
										"Camp_StartDate"=>$this->F_Camp_StartDate,
										"Camp_IsPrivate"=>$this->F_Camp_IsPrivate,
										"Camp_LastUpdatedDate"=>getDateTime(),
										"Camp_Locale"=>GetUserLocale(),
										"Camp_StylingDetails"=>$this->Camp_StylingDetails);
					$Condition = " Camp_Code='".$this->F_Camp_Code."'";
					//dump($DataArray);
					$this->UpdateTeamFundraiserBasicDetail($DataArray,$Condition);
				}
			}
			if($this->P_status==1) $this->ThumbImageBasicDetail();
			if($this->P_status==1) $this->ProcessBackgroundImage();
		}
		private function DateSetup()
		{		
		   if($this->F_Camp_DateSpecified==2)
		   	  $this->F_Camp_StartDate=ChangeDateFormat($this->F_Camp_SpecifiedDate,"Y-m-d","d-m-Y");
			else
			 $this->F_Camp_StartDate=ChangeDateFormat(getDateTime(0,"m/d/Y"),"Y-m-d","m/d/Y");		
		}
		public function ThumbImageBasicDetail()
		{
			if(isset($this->Image['name']) && $this->Image['name']!='')
				{
					$sql="SELECT camp_thumbImage FROM ".TBLPREFIX."campaign  WHERE Camp_ID='".$this->F_Camp_ID."'" ;
					
					$row = db::get_row($sql);
					unlink($row['camp_thumbImage']);
					$objFile=LoadLib('UploadFile');
					$objFile->phyPath=CAMPAIGN_MAIN_IMAGE_DIR;
					$objFile->Uploadfile=$this->Image;
					$objFile->ext=file_ext($this->Image['name']);
					$objFile->customName= strUnique();
					$this->Image['name']=$objFile->customName;					
					$filename = $objFile->customName.'.'.strtolower($objFile->ext);
					
					$Image=$objFile->ProcessUploadFile();
					
					$objFile->load(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
					$objFile->GetAspectRatio(253);
					$objFile->save(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
					
					$objFile->load(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
					$objFile->crop(0,0,253,253);
					$objFile->save(CAMPAIGN_MAIN_IMAGE_DIR.$filename);
					$DataArray=array("camp_thumbImage"=>$filename);
					db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
				
					if(db::is_row_affected())
					{
						$this->setConfirmationMsg('C13000');
					}
					else
					{
						$this->setErrorMsg('E13011');
					}
				}
		}
		private function ValidateBasicDetailParameter()
		{
			if($this->P_status == 1 && $this->F_Camp_ID=='')$this->setErrorMsg('E13000');
			if($this->P_status == 1 && $this->F_Camp_Cat_ID=='')$this->setErrorMsg('E13002');
			if($this->P_status == 1 && $this->F_Camp_Title=='')$this->setErrorMsg('E13003');
			if($this->P_status == 1 && $this->F_Camp_DonationGoal=='')$this->setErrorMsg('E13004');
			if($this->P_status == 1 && $this->F_Camp_DateSpecified==2 && F_Camp_SpecifiedDate=='')$this->setErrorMsg('E13006');
		}
		private function UpdateFundraiserBasicDetail()
		{
			
			$DataArray=array("Camp_Cat_ID"=>$this->F_Camp_Cat_ID,
						"Camp_Title"=>$this->F_Camp_Title,
						"Camp_DescriptionHTML"=>$this->F_Camp_DescriptionHTML,
						"Camp_SalesForceID"=>$this->F_Camp_SalesForceID,
						"Camp_SocialMediaUrl"=>$this->F_Camp_SocialMediaUrl,
						"Camp_UrlFriendlyName"=>$this->F_Camp_UrlFriendlyName,
						"Camp_ShortDescription"=>$this->F_Camp_ShortDescription,
						"Camp_DonationGoal"=>$this->F_Camp_DonationGoal,
						"Camp_StartDate"=>$this->F_Camp_StartDate,
						"Camp_Location_City"=>$this->F_Camp_Location_City,
						"Camp_Location_State"=>$this->F_Camp_Location_State,
						"Camp_Location_Country"=>$this->F_Camp_Location_Country,
						"Camp_Location_Logitude"=>$this->F_Camp_Location_Logitude,
						"Camp_Location_Latitude"=>$this->F_Camp_Location_Latitude,
						"Camp_IsPrivate"=>$this->F_Camp_IsPrivate,
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Locale"=>GetUserLocale(),
						"Camp_StylingDetails"=>$this->Camp_StylingDetails				
						);
			
			//dump($DataArray);
			
			db::update(TBLPREFIX.'campaign',$DataArray,'Camp_ID='.$this->F_Camp_ID);
			if(db::is_row_affected())
			{
				$this->setConfirmationMsg('C13000');
			}else
			{
				$this->setErrorMsg('E13011');
			}
		}
		//======================================================
		public function ProcessDeleteImage()
		{
			$sql="SELECT Camp_Image_CampID,Camp_Image_ID,Camp_Image_Name,Camp_Image_Title,Camp_Image_Type FROM ".TBLPREFIX."campaignimages WHERE Camp_Image_ID=".$this->Camp_Image_ID."";
	 		$row=db::get_row($sql);
			unlink(CAMPAIGN_THUMB_IMAGE_DIR.$row['Camp_Video_File']);
			unlink(CAMPAIGN_LARGE_IMAGE_DIR.$row['Camp_Video_File']);
			db::delete(TBLPREFIX.'campaignimages', "Camp_Image_ID = '".$this->Camp_Image_ID."'")?$this->setConfirmationMsg('C13002'):$this->setErrorMsg('E13014');
			return $row['Camp_Image_CampID'];
		}
		public function ProcessDeleteVideo()
		{
			$sql="SELECT Camp_Video_ID,Camp_Video_CampID,Camp_Video_Title,Camp_Video_File,Camp_Video_EmbedCode FROM ".TBLPREFIX."campaignvideo WHERE Camp_Video_ID=".$this->Camp_Video_ID."";
	 		$row=db::get_row($sql);
			if($row['Camp_Video_File']<>'')
			{
				unlink(CAMPAIGN_VIDEO_DIR.$row['Camp_Video_File']);
				unlink(CAMPAIGN_VIDEO_DIR.$row['Camp_Video_File']);
			}
			db::delete(TBLPREFIX.'campaignvideo', "Camp_Video_ID = '".$this->Camp_Video_ID."'")?$this->setConfirmationMsg('C13001'):$this->setErrorMsg('E13013');
			return $row['Camp_Video_CampID'];
		}
		
		//----------------- Fundraiser comment section code start here-----------------
		
		public function ProcessFundraiseComment()
		{
			if($this->P_status==1) $this->ValidateParameterComment();
			if($this->P_status==1) $this->InsertComment();
		}
		
		private function ValidateParameterComment()
		{
			if($this->P_status==1 && $this->F_Camp_RUID=='') $this->setErrorMsg('E13015');
			if($this->P_status==1 && $this->F_Camp_ID=='') $this->setErrorMsg('E13000');
			if($this->P_status==1 && $this->F_Comment=='') $this->setErrorMsg('E14001');
		}
		private function InsertComment()
		{
				  $DataArray=array("Camp_Cmt_CampID"=>$this->F_Camp_ID,
				"Camp_Cmt_RUID"=>$this->F_Camp_RUID,
				"Camp_Cmt_UserName"=>$this->F_UserName,
				"Camp_Cmt_Comment"=>$this->F_Comment,
				"Camp_Cmt_CreatedDate"=>getDateTime(),
				);
			db::insert(TBLPREFIX.'campaigncomments',$DataArray);
				$id = db::get_last_id();	
			if($id=='')
			{
				$this->setErrorMsg('E14002');
			}
			else
			{
				$this->setConfirmationMsg('C14000');
			}
		}
		
		public function GetFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$where='';
			//LIMIT
			$limit=' LIMIT '.($this->FC_PageNo-1)*$this->FC_PageLimit.' , '.$this->FC_PageLimit;
			$sql="SELECT Camp_Cmt_ID, Camp_Cmt_RUID, Camp_Cmt_UserName, Camp_Cmt_CampID, Camp_Cmt_Comment, DATE_FORMAT(Camp_Cmt_CreatedDate,'%b, %D %Y') as Camp_Cmt_CreatedDate, Camp_Cmt_LastUpdatedDate, Camp_Cmt_ShowOnWebsite
					FROM ".TBLPREFIX."campaigncomments WHERE Camp_Cmt_CampID='".$this->FC_FundraiserId."'";
			//echo $sql.$where.$limit; //exit;
			
		    if($this->FC_approveStatus==1) $where=" AND Camp_Cmt_ShowOnWebsite='1'";
			
		    $row=db::get_all($sql.$where.$limit);		
			$this->FC_TotalRecord=db::count($sql.$where);
			return $row;
		}
		
		
		//-----UPDATE FUNDRAISER COMMENT SECTION
		public function processUpdateFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status==1) $this->validateFundraiserComment();
			if($this->P_status==1) $this->updateFundraiserComment();
		}

		private function validateFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status == 1 && $this->FC_FundraiserId=='')
			{
				$this->setErrorMsg('E15001');	
			}
			if($this->P_status == 1 && $this->FC_CommentId=='')
			{
				$this->setErrorMsg('E15002');	
			}
			if($this->P_status == 1 && $this->FC_CommentContent=='')
			{
				$this->setErrorMsg('E15003');	
			}
		}
		
		private function updateFundraiserComment()
		{
			$returnStatus=0;
			$data=array('Camp_Cmt_Comment'=>$this->FC_CommentContent);
			$where="Camp_Cmt_ID='".$this->FC_CommentId."' AND Camp_Cmt_RUID='".$this->FC_FundraiserId."'";
			db::update(TBLPREFIX.'campaigncomments',$data,$where);
			if($this->P_status==1) {
				$returnStatus=1;
			}
			echo json_encode($returnStatus);exit;
		}

		//-----DELETE FUNDRAISER COMMENT SECTION
		public function processDeleteFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status==1) $this->validateDeleteFundraiserComment();
			if($this->P_status==1) $this->deleteFundraiserComment();
		}

		public function validateDeleteFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status == 1 && $this->FC_FundraiserId=='')
			{
				$this->setErrorMsg('E15001');	
			}
			if($this->P_status == 1 && $this->FC_CommentId=='')
			{
				$this->setErrorMsg('E15002');	
			}
		}
		
		public function deleteFundraiserComment()
		{
			$where="Camp_Cmt_ID='".$this->FC_CommentId."' AND Camp_Cmt_RUID='".$this->FC_FundraiserId."'";
			$returnStatus=db::delete(TBLPREFIX.'campaigncomments',$where)?1:0;
			echo json_encode($returnStatus);exit;
		}
		
		//APPROVE FUNDRAISER COMMENT SECTION
		public function processApproveFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status==1) $this->validateApproveFundraiserComment();
			if($this->P_status==1) $this->approveFundraiserComment();
		}

		public function validateApproveFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status == 1 && $this->FC_FundraiserId=='')
			{
				$this->setErrorMsg('E15001');	
			}
			if($this->P_status == 1 && $this->FC_CommentId=='')
			{
				$this->setErrorMsg('E15002');	
			}
		}

		public function approveFundraiserComment()
		{
			if($this->FC_approveStatus==0)
				$approveStatus=1;
			else	
				$approveStatus=0;

			$data=array('Camp_Cmt_ShowOnWebsite'=>$approveStatus);
			$where="Camp_Cmt_ID='".$this->FC_CommentId."' AND Camp_Cmt_RUID='".$this->FC_FundraiserId."'";
			$returnStatus=db::update(TBLPREFIX.'campaigncomments',$data,$where)?1:0;
			echo json_encode($returnStatus.'##'.$approveStatus);exit;
		}
		//----------------- Fundraiser comment section code start here-----------------
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
		
		public function SetFundraiserDetails($DataArray,$FundID)
		{
			$temp=0;
			db::update(TBLPREFIX."campaign",$DataArray,"Camp_ID=".$FundID);	
			if(db::is_row_affected())
			$temp=$FundID;
			return $temp;
		}
		
				
		public function getAllStyleColor()
		{
			$sql="SELECT * FROM ".TBLPREFIX."campaigncolorcheme WHERE CCS_StylingTemplateName='".$this->StyleTemplateName."' AND CCS_LevelID=".$this->Camp_Level_ID;
			$this->StyleColorArray = db::get_all($sql);
		}
		
		public function TeamFundraiserCheckUUIDForNPO($EINNumber)
		{
			$sql="SELECT NUR.NPOID,NUR.Status,NUR.Stripe_ClientID,NUR.Status,ND.NPO_DedCode,NUR.NPOEIN FROM ".TBLPREFIX."npouserrelation as NUR INNER JOIN ".TBLPREFIX."npodetails as ND ON  NUR.NPOID=ND.NPO_ID WHERE NUR.NPOEIN='".$EINNumber."' AND NUR.Status ='1'";
			$row = db::get_row($sql);
			
			if($row['NPOID'])
			{
				return $row;
			}
			else
			{
				return false;
			}
		}
		
		public function updateProcessLog($DataArray)
		{
			
			$FieldArray = array("DateTime"=>$DataArray['Date'],
								"ModelName"=>$DataArray['Model'],
								"ControllerName"=>$DataArray['Controller'],
								"UserType"=>$DataArray['UType'],
								"UserName"=>$DataArray['UName'],
								"UserID"=>$DataArray['UID'] = ($DataArray['UID']!='')?$DataArray['UID']:0,
								"RecordID"=>$DataArray['RecordId'] = ($DataArray['RecordId']!='')?$DataArray['RecordId']:0,								
								"SortMessage"=>$DataArray['SMessage'],
								"LongMessage"=>$DataArray['LMessage'],);							
			
			db::insert(TBLPREFIX."processlog",$FieldArray);
			$id = db::get_last_id();
			$id = ($id)?$id:0;
			return $id;
		}
		
		public function setCampaign_DB($DataArray)
		{
			db::insert(TBLPREFIX."campaign",$DataArray);
			$id = db::get_last_id();
			$id = ($id)?$id:0;
			return $id;
		}

	}
?>