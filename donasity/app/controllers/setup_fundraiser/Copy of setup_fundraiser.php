<?php
	class Setup_fundraiser_Controller extends Controller
	{
		private $SuccessUrl,$ErrorUrl;
		private $FR_status,$FR_ErrorCode,$FR_ErrorMsg,$FR_ConfirmCode,$FR_IsLogin,$FR_MsgType;
		public $tpl,$SF_View,$SF_Status,$SF_LoggedInDetail,$FundraiserStyle,$FR_id;
		public function __construct()
		{ 
			$this->load_model("Common","objCom");
			$this->load_model("UserType1","objUT1");
			$this->load_model('Fundraisers','objFund');
			$this->SF_LoggedInDetail=getSession('Users');
		}
		
		public function index($FundId)
		{
			
			$this->FR_id=keyDecrypt($FundId);
			$this->objFund->F_Camp_ID=$this->FR_id;
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
		
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			switch($this->SF_Status)
			{
				case 0 :
						$this->show_step_0();
					break;
				case 1 :
						$this->show_step_1();
					break;
				
				case 2 :
						$this->show_step_2();
					break;
				
				case 3 :	
						$this->show_step_3();
					break;
				
				case 4 :	
						$this->show_step_4();
				break;
				
				case 5 :	
						$this->show_step_complete();
				break;

				default:
					//$this->index();
					break;			
				
			}
			
	
		}
		private function show_step_0()
		{
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$SupportedStype=$FundraiserDetail[0]['Camp_Level_DetailJSON']['Supported_Style'];
			$SupportedStype=explode(',',$FundraiserDetail[0]['Camp_Level_DetailJSON']['Supported_Style']);
		
			$UsedDetail=$this->objUT1->GetUserDetails();
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser');
			$name=$UsedDetail['RU_FistName'].' '.$UsedDetail['RU_LastName'];
			$arrMetaInfo["Key_Premier_Fundraiser"]=strtr($arrMetaInfo["Key_Premier_Fundraiser"],array('{name}' =>$name));
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign($CampLevel[0]);
			$this->tpl->assign('SupportedStype',$SupportedStype);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/index');
		}
		private function show_step_1()
		{
		
		
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step1');
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			
			//dump($FundraiserDetail);
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			$FR_Ddays=$FundraiserDetail[0]['Camp_Level_DetailJSON']['Duration_Days'];
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl=new View;
			$msgValues = EnPException::getConfirmation();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign('FR_DurationDays',$FR_Ddays);
			$this->tpl->assign('msgValues',$msgValues);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/step-1');
		}
		private function show_step_2()
		{
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step2');
			$this->objFund->F_Camp_ID=$this->FR_id;
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$msgValues = EnPException::getConfirmation();
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign('msgValues',$msgValues);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/step-2');
		}
		private function show_step_3()
		{
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step3');
			$msgValues = EnPException::getConfirmation();
			$this->objFund->F_Camp_ID=$this->FR_id;
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$ImageList=$this->objFund->CampaignImages();
			$VideoList=$this->objFund->CampaignVideos();
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('ImageList',$ImageList);
			$this->tpl->assign('VideoList',$VideoList);
			$this->tpl->assign('msgValues',$msgValues);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/step-3');
		}
		private function show_step_4()
		{
			$msgValues = EnPException::getConfirmation();
			$this->objFund->F_Camp_ID=$this->FR_id;
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step4');
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign('FR_DurationDays',$FR_Ddays);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('msgValues',$msgValues);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/step-4');
		}
		private function show_step_complete()
		{
			$msgValues = EnPException::getConfirmation();
			$this->FR_id=$FundId;
			$this->objFund->F_Camp_ID=$FundId;
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$UsedDetail=$this->objUT1->GetUserDetails();
			$name=$UsedDetail['RU_FistName'].' '.$UsedDetail['RU_LastName'];
	
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step_complete');
			$arrMetaInfo["Key_Fundraiser_Step_Complete"]=strtr($arrMetaInfo["Key_Fundraiser_Step_Complete"],array('{name}' =>$name));
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('msgValues',$msgValues);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/complete');
		}
		
		
	
	///////////	
	public function UploadImage()
	{
		$this->FR_id = request('post','FR_id',0);
	 	
		$this->objFund->F_Camp_ID=$this->FR_id ;
		$this->objFund->Image = $_FILES['uploadPhoto'];
		$this->objFund->ProcessUploadImage();
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
		    
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->SetMsg();
		
	}
	//////////
	public function UploadVideo()
	{
		$this->FR_id = request('post','FR_id',0);
		$this->objFund->F_Camp_ID=$this->FR_id ;
		$this->objFund->Video = $_FILES['uploadVideo'];
		$this->objFund->VideoCode = request('post','videoEmbedCode',0);
		$this->objFund->ProcessUploadVideo();
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
		    
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->SetMsg();
		
	}
	
	/////////
		public function Update_fundraiser_setup()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			
			$this->FundraiserStyle = request('post','style2',0);
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$DataArray=array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID','RU.RU_State','RU.RU_Country','RU.RU_Phone');
			$UsedDetail=$this->objUT1->GetUserDetails($DataArray);
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$this->objFund->F_Camp_Status=1;
			$this->objFund->F_Camp_StylingTemplateName=$this->FundraiserStyle;
			$this->objFund->F_Camp_CP_FirstName=$UsedDetail['RU_FistName'];
			$this->objFund->F_Camp_CP_LastName=$UsedDetail['RU_LastName'];
			$this->objFund->F_Camp_CP_Email=$UsedDetail['RU_EmailID'];
			$this->objFund->F_Camp_CP_City=$UsedDetail['RU_City'];
			$this->objFund->F_Camp_CP_ZipCode=$UsedDetail['RU_ZipCode'];
			$this->objFund->F_Camp_CP_Address1=$UsedDetail['RU_Address1'];
			$this->objFund->F_Camp_CP_Address2=$UsedDetail['RU_Address2'];
			$this->objFund->F_Camp_CP_State=$UsedDetail['RU_State'];
			$this->objFund->F_Camp_CP_Country=$UsedDetail['RU_Country'];
			$this->objFund->F_Camp_CP_Phone=$UsedDetail['RU_Phone'];
			
			//RU_State,RU_Country,RU_Phone
			/*IMPORTANT-ValidateFundraiserStep_1Parameter*/
			
			$this->objFund->ProcessFundraiserSetup();
			
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
		    
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='setup_fundraiser/index/'.$this->FR_id;
			
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step1()
		{
			
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$this->objFund->F_Camp_Status=2;
			
			$this->objFund->F_Camp_Cat_ID=request('post','category',0);
			$this->objFund->F_Camp_Title=request('post','title',0);
			$this->objFund->F_Camp_ShortDescription=request('post','subTitle',0);
			
			$this->objFund->F_Camp_DonationGoal=request('post','donation',0);
			$this->objFund->F_Camp_CP_City=request('post','location',0);
			$this->objFund->F_Camp_DateSpecified=request('post','radio1',0);//start date check
			$this->objFund->F_Camp_Duration_Day=request('post','FR_DurationDays',0);
			$this->objFund->F_Camp_SpecifiedDate=request('post','specifiedDate',0);
			
			$this->objFund->F_Camp_IsPrivate=request('post','checkbox',0);
			
			$this->objFund->F_Camp_CP_City=request('post','Camp_Location_City',0);
			$this->objFund->F_Camp_CP_ZipCode=request('post','Camp_Location_State',0);
			$this->objFund->F_Camp_CP_Address1=request('post','Camp_Location_Country',0);
			$this->objFund->F_Camp_CP_Address2==request('post','Camp_Location_Logitude',0);
			$this->objFund->F_Camp_CP_Address2==request('post','Camp_Location_Latitude',0);
		
			$this->objFund->ProcessFundraiserStep_1();
			
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
		    $this->objFund->F_Camp_Status=2;
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='error';
			
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step2()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
		
			$this->objFund->F_Camp_Status=3;
			
			$this->objFund->F_Camp_DescriptionHTML=request('post','aboutFundraiser',0);
			$facebookURL=request('post','facebookURL',0);
			$googleURL=request('post','googleURL',0);
			$linkedinURL=request('post','linkedinURL',0);
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);
			$secretURL=request('post','secretURL',0);
			$mailusURL=request('post','mailusURL',0);
			$this->objFund->F_Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"g-plus"=>$googleURL,"linkedin"=>$linkedinURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL,"user-secret"=>$secretURL,"a-at"=>$mailusURL));

			$this->objFund->ProcessFundraiserStep_2();
			
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='error';
			
			$this->SetMsg();
			
		}
		
		public function Update_fundraiser_step3()
		{
			
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
		
			$this->objFund->F_Camp_Status=4;
			
			$this->objFund->ProcessFundraiserStep_3();
			
			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='error';
			
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step4()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
		
			$this->objFund->F_Camp_Status=5;
	
			$this->objFund->F_Camp_Chk_AccountNumber=request('post','accountNumber',0);
		    $this->objFund->F_Camp_AccountNumber=request('post','StripeACNumber',0);
			$this->objFund->F_Camp_NonChk_AccountNumber=request('post','NPO_ACnumber',0);
			$this->objFund->F_Camp_NonAccountNumber=request('post','UUID',0);
		
			$this->objFund->ProcessFundraiserStep_4();

			$this->FR_status=$this->objFund->P_status;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;

			$this->SuccessUrl='setup_fundraiser/index/'.$this->FR_id;
			$this->ErrorUrl='error';

			$this->SetMsg();
			
		}
		
		public function Update_fundraiser_complete()
		{
			
				
		}
		
		public function FundInsert()
		{
			$a=$this->objFund->FundraiserInsert();
		}
		
		private function SetMsg()
		{
			if($this->FR_status==1)
				{
					$confirmationParams = array(
					"msgCode"	=> $this->FR_ConfirmCode,
					"msg"		=> $this->FR_ErrorMsg,
					"msgLog"	=> 0,									
					"msgDisplay"=> 1,
					"msgType"	=> $this->FR_MsgType);
					EnPException::setConfirmation($confirmationParams);		
					redirect(URL.$this->SuccessUrl);
				}
				else
				{
					$errParams = array(
					"errCode" 	=> $this->FR_ErrorCode,
					"errMsg"	=> $this->FR_ErrorMsg,
					"errOriginDetails"=> basename(__FILE__),
					"errSeverity"=> 1,
					"msgDisplay"=> 1,
					"msgType"	=> $this->FR_MsgType);
					dump($errParams);
					EnPException::setError($errParams);
					redirect(URL.$this->ErrorUrl);			
				}
		}
	}
?>