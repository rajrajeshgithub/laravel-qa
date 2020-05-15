<?php
	class FundraisersDetail_Model extends Model
	{
		
	public 	$P_ErrorCode,$P_ErrorMessage,$P_ConfirmCode,$P_ConfirmMsg,$P_status,$P_MsgType;
	
	public $F_Camp_ID,$F_Camp_RUID,$F_FundraiserDetail,$F_WhereCondition,$F_Comment,$F_UserName,$F_Camp_Status;
	public $NPO_Name;
		
		public function __construct()
		{
			$this->P_status=1;
		}
		
		public function ProcessGetFundraiserDetails()
		{
		 	EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
		    if($this->P_status==1) $this->ValidateParameter();
			if($this->P_status==1) $this->GetFundraiser();
			if($this->P_status==1) $this->GetTeamFundraisers();	
			if($this->P_status==1) $this->GetFundraiserImage();
			if($this->P_status==1) $this->GetFundraiserVideo();
		}
		
		private function  ValidateParameter()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			if($this->P_status==1 && $this->F_Camp_Status==2 && $this->F_Camp_RUID=='') $this->setErrorMsg('E13015');
			if($this->P_status==1 && $this->F_Camp_ID=='') $this->setErrorMsg('E13000');
			return $this->P_status;
		}
		
		public function GetFundraiser()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_ID,Camp_Code,Camp_TeamUserType,Camp_PaymentMode,Camp_StylingTemplateName,Camp_Title,camp_thumbImage,camp_bgImage,Camp_UrlFriendlyName,Camp_ShortDescription,Camp_Description,Camp_NPO_EIN,Camp_DescriptionHTML,ROUND(Camp_DonationGoal) as Camp_DonationGoal,ROUND(Camp_DonationReceived) as Camp_DonationReceived,Camp_StartDate, Camp_EndDate,Camp_Status,Camp_SocialMediaUrl,Camp_StylingDetails,Camp_RUID,Camp_CustomToggles FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$this->F_Camp_ID;
			
			//echo $Sql.$this->F_WhereCondition; exit;
			
			
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
				
				$F_FundraiserDetail[0]['Camp_CustomToggles']=json_decode($F_FundraiserDetail[0]['Camp_CustomToggles'],true);
				
				$F_FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_thumbImage']);
				
				$F_FundraiserDetail[0]['Camp_BigImage_Full_Path']=CheckImage(CAMPAIGN_BACKGROUND_IMAGE_DIR,CAMPAIGN_BACKGROUND_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_bgImage']);
				
				
				$F_FundraiserDetail[0]['NPO_Name'] = $F_FundraiserDetail[0]['Camp_NPO_EIN'] != '' ? $this->getNPOname($F_FundraiserDetail[0]['Camp_NPO_EIN']) : '';
				$F_FundraiserDetail=$F_FundraiserDetail[0];
			}else
			{
			   $this->setErrorMsg('E14000');
			}
			$this->F_FundraiserDetail["Details"]=$F_FundraiserDetail;
			
			return $this->F_Camp_ID;
		}
		
		
		private function getNPOname($EIN_No)
		{
			$Sql = "SELECT NPO_Name FROM dns_npodetails WHERE NPO_EIN=".$EIN_No;
			$row = db::get_all($Sql);
			return isset($row[0]['NPO_Name']) ? $row[0]['NPO_Name'] : '';
		}
		
		private function GetTeamFundraisers()
		{
			if($this->F_FundraiserDetail['Details']['Camp_Code']!='')
			{
				$OrderBy=' ORDER by Camp_ID ASC';
				//$OrderBy=' ORDER by C.Camp_ShortDescription ASC';
				$Sql="SELECT NPOCat_URLFriendlyName, CONCAT(RU_FistName,' ',RU_LastName) as FullName, C.Camp_ID,Camp_Code,Camp_TeamUserType,Camp_PaymentMode,
						Camp_StylingTemplateName,Camp_Title,Camp_ShortDescription,camp_thumbImage,camp_bgImage,Camp_UrlFriendlyName,ROUND(Camp_DonationGoal) as Camp_DonationGoal,
						ROUND(Camp_DonationReceived) as Camp_DonationReceived,concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),'%') AS Donationpercentage,Camp_StartDate, Camp_EndDate,Camp_CustomToggles 
				FROM ".TBLPREFIX."campaign C
				LEFT JOIN ".TBLPREFIX."registeredusers RU ON(C.Camp_RUID=RU.RU_ID)
				LEFT JOIN ".TBLPREFIX."npocategories NC ON(NC.NPOCat_ID = C.Camp_Cat_ID)
				 WHERE Camp_Code='".$this->F_FundraiserDetail['Details']['Camp_Code']."' AND C.Camp_Status='15' AND C.Camp_Deleted!='1' AND C.Camp_IsPrivate!='1' AND C.Camp_ID!='".$this->F_Camp_ID."' ".$OrderBy." LIMIT 0,21 ";
				//echo $Sql;exit;
				
				$F_FundraiserDetail = db::get_all($Sql);

				foreach($F_FundraiserDetail as $key => $arrValue)
				{
					if($arrValue['Camp_TeamUserType']=='C')
					{
						$arrCaptain = $arrValue;
						$this->F_FundraiserDetail['CaptainDetails'] = $arrValue;
					}
					elseif($arrValue['Camp_TeamUserType']=='T')
					{
						$teamIdentity = $arrValue['FullName'];
						if($arrValue['Camp_ShortDescription'] != ''){$teamIdentity = $arrValue['Camp_ShortDescription'];}
						$teamIdentityuncut=$teamIdentity;
						$teamIdentity = $this->StringOnSpaceTruncate($teamIdentity,22);
						if($teamIdentityuncut!=$teamIdentity){$teamIdentity.='&hellip;';}
						$arrValue['Camp_TeamIdentity'] = $teamIdentity;
						$this->F_FundraiserDetail['TeamDetails'][] = $arrValue;
						//asort($this->F_FundraiserDetail['TeamDetails'][]);
					}
				}
			
				$this->F_FundraiserDetail['TeamDetails'] = (count($F_FundraiserDetail) > 0) ? isset($this->F_FundraiserDetail['TeamDetails']) ? $this->F_FundraiserDetail['TeamDetails'] : NULL : NULL;
				//array_push($this->F_FundraiserDetail['TeamDetails'][],$arrCaptain);
			}
		}
		
		public function GetTeamFundraisersPaginate()
		{
			$OrderBy=' ORDER by C.Camp_ID ASC';
			//$OrderBy=' ORDER by C.Camp_ShortDescription ASC';
			
			$where=" WHERE C.Camp_Code='".$this->PT_TeamCode."' AND C.Camp_TeamUserType='T' AND C.Camp_Status=15 AND C.Camp_Deleted!='1' AND C.Camp_IsPrivate!='1' ";
			
			$xx=($this->PT_PageChosen-1)*10;
			if($this->PT_TeamMobile=='2'){$limit=' LIMIT '.$xx.',10';}
			else{$limit=' LIMIT '.($this->PT_PageChosen-1)*$this->PT_TeamsPerPanel.','.$this->PT_TeamsPerPanel;}			
			
			
			$Sql="SELECT NPOCat_URLFriendlyName, CONCAT(RU_FistName,' ',RU_LastName) as FullName, C.Camp_ID, Camp_Code,Camp_TeamUserType, Camp_PaymentMode, Camp_StylingTemplateName,Camp_Title, Camp_ShortDescription, camp_thumbImage,camp_bgImage, Camp_UrlFriendlyName,ROUND(Camp_DonationGoal) as Camp_DonationGoal, ROUND(Camp_DonationReceived) as Camp_DonationReceived,concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),'%') AS Donationpercentage,Camp_StartDate, Camp_EndDate, Camp_CustomToggles FROM ".TBLPREFIX."campaign C LEFT JOIN ".TBLPREFIX."registeredusers RU ON(C.Camp_RUID=RU.RU_ID) LEFT JOIN ".TBLPREFIX."npocategories NC ON(NC.NPOCat_ID = C.Camp_Cat_ID)";
			
			//  do not add  Camp_DemoUse!='1'  because if team captn is demo, and sales is demo-ing atm, do list teams, this query is only for pagination, not home page search	
			//echo $Sql.$where.$limit.$orderby; 	
			$row=db::get_all($Sql.$where.$orderby.$limit);	
			//dump($row);exit;	
			return $row;			
		}
		
		private function StringOnSpaceTruncate($string=NULL,$desiredlength=NULL){
			$stringchopped=$string;
			if($string!=NULL && $desiredlength!=NULL){
				if (strlen($string) > $desiredlength) 
				{
					$string = wordwrap($string, $desiredlength);					
					$string = substr($string, 0, strpos($string, "\n"));					
					$stringchopped=$string;
				}
			}
			return $stringchopped;
		}
		
		private function GetFundraiserImage()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_Image_CampID,Camp_Image_Name,Camp_Image_Title,Camp_Image_Type FROM ".TBLPREFIX."campaignimages WHERE Camp_Image_CampID=".$this->F_Camp_ID." AND Camp_Image_ShowOnWebsite='1'";
			$Orderby=" Order by Camp_Image_SortOrder";
			$ImageList = db::get_all($Sql.$Orderby);
			//dump($ImageList);
			if(count($ImageList)>0)
			{
				for($i=0;$i<count($ImageList);$i++)
				{
					$ImageArray[$i]['Camp_Thumb_Image_Full_Path']=CheckImage(CAMPAIGN_THUMB_IMAGE_DIR,CAMPAIGN_THUMB_IMAGE_URL,NO_IMAGE,$ImageList[$i]['Camp_Image_Name']);
					$ImageArray[$i]['Camp_Large_Image_Full_Path']=CheckImage(CAMPAIGN_LARGE_IMAGE_DIR,CAMPAIGN_LARGE_IMAGE_URL,NO_IMAGE,$ImageList[$i]['Camp_Image_Name']);
				}
				$ImageList=$ImageArray;
				//dump($ImageList);
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
			$VideoArray = array();
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
							  $VideoArray[]=array("Path"=>$VideoPath,"Type"=>1,"Thmb"=>'');
						  }
					  }
					}else
					{						
						$thumbPath='';
						$vidThumb=$VideoList[$i]['Camp_Video_EmbedCode'];
						$vidray=explode('"',$vidThumb);
						$resultelems = count($vidray);						
						if (strpos('x'.$vidThumb,'youtube.com') !== false) 		// youtube exists there
						{
							if($resultelems>4)
							{
								$YTvidcodeRay=explode('/',$vidray[5]);
								$resultelems2 = count($YTvidcodeRay);
								if($resultelems2>3)
								{
									if($YTvidcodeRay[2]=='www.youtube.com')
									{
										$YTvidcode=$YTvidcodeRay[4];
										$thumbPath='http://i.ytimg.com/vi/'.$YTvidcode.'/default.jpg';
									}
									
								}	
							}
						}
						$VideoArray[]=array("Path"=>$VideoList[$i]['Camp_Video_EmbedCode'],"Type"=>2,"Thmb"=>$thumbPath);
					}
				}
				$VideoList=$VideoArray;
				
			}
			$this->F_FundraiserDetail["Video"]=$VideoList;
			return $this->F_Camp_ID;
		}
		
	
		public function GetFundraiserDetailForPrint()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Sql="SELECT Camp_ID,Camp_StylingTemplateName,Camp_Title,camp_thumbImage,camp_bgImage,Camp_UrlFriendlyName,
			Camp_ShortDescription,Camp_Description,Camp_NPO_EIN,Camp_DescriptionHTML,ROUND(Camp_DonationGoal) as Camp_DonationGoal,ROUND(Camp_DonationReceived) as Camp_DonationReceived,Camp_StartDate, Camp_EndDate,Camp_SocialMediaUrl,Camp_PaymentMode
			FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$this->F_Camp_ID;
			$F_FundraiserDetail = db::get_all($Sql.$this->F_WhereCondition);
			
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
			
				$F_FundraiserDetail[0]['camp_thumbImage']=$F_FundraiserDetail[0]['camp_thumbImage'];
				$F_FundraiserDetail[0]['camp_bgImage']=$F_FundraiserDetail[0]['camp_bgImage'];
				
				$F_FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($F_FundraiserDetail[0]['Camp_SocialMediaUrl'],true);				
				
				$F_FundraiserDetail[0]['Camp_CustomToggles'] = isset($F_FundraiserDetail[0]['Camp_CustomToggles']) ? json_decode($F_FundraiserDetail[0]['Camp_CustomToggles'],true) : '';				
				
				$F_FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_thumbImage']);
			
				$F_FundraiserDetail[0]['Camp_BigImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$F_FundraiserDetail[0]['camp_bgImage']);
			
				$F_FundraiserDetail=$F_FundraiserDetail[0];
			}else
			{
				$this->setErrorMsg('E14000');
			}
			
			$this->F_FundraiserDetail = $F_FundraiserDetail;
			$this->NPO_Name = $F_FundraiserDetail['Camp_NPO_EIN'] != '' ? $this->getNPOname($F_FundraiserDetail['Camp_NPO_EIN']) : '';
			//echo $F_FundraiserDetail[0]['Camp_NPO_EIN'];exit;
			//echo $this->NPO_Name;exit;
			return $this->F_Camp_ID;
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