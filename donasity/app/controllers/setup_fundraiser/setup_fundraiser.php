<?php
	class Setup_fundraiser_Controller extends Controller
	{
		private $SuccessUrl,$ErrorUrl,$SF_LoginUserId;
		private $FR_status,$FR_ErrorCode,$FR_ErrorMsg,$FR_ConfirmCode,$FR_IsLogin,$FR_MsgType,$FR_ConfirmMsg;
		public $tpl,$SF_View,$SF_Status,$SF_LoggedInDetail,$FundraiserStyle,$FR_id;
		public $F_FundId,$F_CampCode;
		public $stripe_querystring,$stripe_code,$stripe_OauthResp,$stripe_clientID;
		public function __construct()
		{	
			$this->load_model("Common","objCom");			
			$this->load_model("UserType1","objUT1");
			$this->load_model("UserType2","objUT2");
			$this->load_model('Fundraisers','objFund');
			$this->objFund = new Fundraisers_Model();			
		}
		
		public function verify_user()
		{	
			$this->SF_LoggedInDetail=getSession('Users');			
			
			if($this->objUT1->checkLogin($this->SF_LoggedInDetail)>0)
				$this->SF_LoginUserId	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);	
			elseif($this->objUT2->checkLogin($this->SF_LoggedInDetail)>0)
				$this->SF_LoginUserId	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);	
			else
			{
				redirect(URL."ut1/login");
			}
		}
		public function index($FundId)
		{
			$this->verify_user();
			if($FundId=='')
			{
				redirect(URL.'error');
			}
			
			$this->FR_id=keyDecrypt($FundId);
			setSession("FundariserID",$FundId);
			
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
				
				case 5:
					
						$this->show_step_5();
				break;
				
				case 6 :	
						
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
			
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			//$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			$FR_Ddays=$FundraiserDetail[0]['Camp_Level_DetailJSON']['Duration_Days'];
			$Camp_StylingTemplateName = $FundraiserDetail[0]['Camp_StylingTemplateName'];
			$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl=new View;			
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);
			$this->tpl->assign('FR_DurationDays',$FR_Ddays);			
			$this->tpl->assign('Camp_StylingTemplateName',$Camp_StylingTemplateName);			
			//$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('teamFundraiser',($enabledTeamFundraiser=='Yes')?1:0);
			$this->tpl->draw('setup_fundraiser/step-1');
		}
		private function show_step_2()
		{
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step2');
			$this->objFund->F_Camp_ID=$this->FR_id;
			
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];
			
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);			
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('teamFundraiser',($enabledTeamFundraiser=='Yes')?1:0);
			$this->tpl->draw('setup_fundraiser/step-2');
		}
		
		private function show_step_3()
		{
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step3');			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");						
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];			
			$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];
			
			$noPhotos = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Photos'];
			$arrMetaInfo["text_upload_photos"]=strtr($arrMetaInfo["text_upload_photos"],array('{{number_photos}}' =>$noPhotos));
			$noVedios = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Videos'];
			$arrMetaInfo["instruction_upload_vedios"]=strtr($arrMetaInfo["instruction_upload_vedios"],array('{{number_vedios}}' =>$noVedios));
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
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('teamFundraiser',($enabledTeamFundraiser=='Yes')?1:0);
			$this->tpl->draw('setup_fundraiser/step-3');
		}
		private function show_step_4()
		{			
			$this->objFund->F_Camp_ID=$this->FR_id;
			
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step4');
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];
			
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);			
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign($arrMetaInfo);			
			$this->tpl->assign('STRIPE_FUNDARISER_CONNECT_URL',STRIPE_FUNDARISER_CONNECT_URL);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('teamFundraiser',($enabledTeamFundraiser=='Yes')?1:0);
			$this->tpl->draw('setup_fundraiser/step-4');
		}
		
		public function show_step_5()
		{
			$this->objFund->F_Camp_ID=$this->FR_id;
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('setup_fundraiser_step5');
			//dump($arrMetaInfo);
			$DataArray=array('Camp_ID','Camp_Level_ID','Camp_RUID','Camp_Status'," '' as  Camp_Level_DetailJSON");
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];
			//if($enabledTeamFundraiser!='Yes')
			//redirect(URL."setup_fundraiser/index/".keyEncrypt($this->FR_id));
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',$this->FR_id);			
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign($arrMetaInfo);			
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('teamFundraiser',$enabledTeamFundraiser);
			$this->tpl->draw('setup_fundraiser/step-5');
		}
		 public function VerifyStripConnection()
		{
			$this->verify_user();
			$FundId=getSession("FundariserID");
			$this->FR_id=keyDecrypt($FundId);
			setSession("FundariserID",$FundId);
			$this->objFund->F_Camp_ID=$this->FR_id;	
			$this->stripe_querystring=$_SERVER['QUERY_STRING'];
			$this->stripe_code =isset($_GET["code"])?$_GET["code"]:"";
			if($this->stripe_code<>"")
			{
				$this->stripe_OauthResp=$this->getStripResponse($this->stripe_code);
				$this->stripe_clientID=$this->stripe_OauthResp["stripe_user_id"];
				
				if($this->stripe_clientID<>"")								
				{				
					$FundraiserDetail=$this->objFund->GetFundraiserDetails();
					$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
					$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
					$enabledTeamFundraiser = $FundraiserDetail[0]['Camp_Level_DetailJSON']['Team_Fundraiser'];
					if($enabledTeamFundraiser=='Yes')
						$campStatus = 5;
					else
						$campStatus = 6;
						//redirect(URL."setup_fundraiser/index/".keyEncrypt($this->FR_id));
					$arreyFields = array("Camp_Status"=>$campStatus,"Camp_Stripe_Status"=>1,"Camp_Stripe_ConnectedID"=>$this->stripe_clientID,"Camp_PaymentMode"=>"INDIVIDUAL-STRIPE-ACCOUNT",
										"Camp_Stripe_Response"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"Camp_LastUpdatedDate"=>getDateTime(),
										"camp_ProcessLog"=>'CONCAT(camp_ProcessLog,","Sucessfully Connected to Stripe Updated on.getDateTime()")');
				
					$this->EmailOnStripeSuccess($this->FR_id);//for strip Email
					$sMessage = "Sucessfully Connected to Stripe";
					$lMessage = "Sucessfully Connected to Stripe";
				}
				else
				{					
					$arreyFields = array("Camp_Status"=>'4',"Camp_Stripe_Status"=>0,"Camp_Stripe_Response"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"Camp_LastUpdatedDate"=>getDateTime(),"camp_ProcessLog"=>'CONCAT(camp_ProcessLog,","Got Code.$this->stripe_code. but stripe_user_id not feteched Updated on .getDateTime()")');						
					$sMessage = "Error in strip verification";
					$lMessage = "Got Code".$this->stripe_code." but stripe_user_id not feteched";
			
				}
				
				$this->objFund->SetFundraiserDetails($arreyFields,$this->objFund->F_Camp_ID);
				/*----update process log------*/			
				if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
					$userType 	= 'UT1';
					$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
					$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
				}else{
					$userType 	= 'UT2';
					$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
					$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
				}				
					$DataArray = array(	"UType"=>$userType,
										"UID"=>$userID,
										"UName"=>$userName,
										"RecordId"=>$this->objFund->F_Camp_ID,
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
				
				//echo($this->objFund->F_Camp_ID.$_SERVER['QUERY_STRING']);
				//print_r($arreyFields);
				//dump($this->stripe_OauthResp);		
				//redirect(URL.'ut2myaccount/confirmation/sucess'); 
				//	redirect(URL.'/ut2myaccount/manage-npo-details'); 
				
			}	                   
			redirect(URL."/setup_fundraiser/index/".keyEncrypt($this->objFund->F_Camp_ID));	
		}
		private function EmailOnStripeSuccess($FR_id)
		{
				$this->verify_user();
				$DataArray=array('Camp_ID','Camp_CP_FirstName','Camp_CP_LastName','Camp_CP_Email');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails();
			
				$uname=$FundraiserDetail['Camp_CP_FirstName'].' '.$FundraiserDetail['Camp_CP_LastName'];
				
				$this->load_model('Email','objemail');
				$Keyword='StripeSuccess';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				$tpl->assign('Link',$link);
				$tpl->assign('uname',$uname);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				$InsertDataArray=array('FromID'=>$this->objutype1->UserDetailsArray['RU_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail['Camp_CP_Email'],
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
					if($Status)
					{
						$this->FR_status=1;
					}
					else
					{
						$this->FR_status=0;
					}
		}
		
		
		private function show_step_complete()
		{
					
			$this->objFund->F_Camp_ID=$this->FR_id;
			$DataArray=array('*');
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$UsedDetail=$this->objUT1->GetUserDetails();			
			$name=$UsedDetail['RU_FistName'].' '.$UsedDetail['RU_LastName'];
			$SFundraiserDetail[0]['Camp_UrlFriendlyName']=RemoveSpecialChars($FundraiserDetail[0]['Camp_UrlFriendlyName']);
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('setup_fundraiser_step_complete');
			
			$arrMetaInfo["text_Fundraiser_Step_Complete"]=strtr($arrMetaInfo["text_Fundraiser_Step_Complete"],array('{name}' =>$name,'{fundraiser_title}' =>$FundraiserDetail[0]['Camp_Title']));

	
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			
			$this->SF_Status=$FundraiserDetail[0]['Camp_Status'];
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl=new View;
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('FR_id',keyEncrypt($this->FR_id));
			$this->tpl->assign($arrMetaInfo);			
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail);			
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('setup_fundraiser/complete');
		}
		
		public function UploadImage()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->ProcessUploadImage();
				/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in image uploading";
			$lMessage = "Error in image uploading";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser image uploaded";
				$lMessage = "Fundraiser image uploaded";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
			
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->SetMsg();
			
		}
		public function UploadVideo()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Video = $_FILES['uploadVideo'];
			
			$this->objFund->VideoCode = $_POST['videoEmbedCode'];
			
			$this->objFund->ProcessUploadVideo();
				/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in video uploading";
			$lMessage = "Error in video uploading";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser video uploaded";
				$lMessage = "Fundraiser video uploaded";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
			
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->SetMsg();
			
		}
		
		
		public function Update_fundraiser_setup()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			if(trim($this->SF_LoggedInDetail['UserType1']['user_id'])!='')
			{
				$this->objUT1->UserID=keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$DataArray=array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID','RU.RU_State','RU.RU_Country','RU.RU_Phone');
				$UsedDetail=$this->objUT1->GetUserDetails($DataArray);
			}elseif($this->SF_LoggedInDetail['UserType2']['user_id']!=''){
				$this->objUT2->userId=keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$DataArray=array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID','RU.RU_State','RU.RU_Country','RU.RU_Phone');
				$UsedDetail=$this->objUT2->GetUserDetails($DataArray);
			}
				$this->FundraiserStyle = request('post','style2',0);
				$DataArray=array("Camp_StylingTemplateName"=>$this->FundraiserStyle,
						"Camp_CP_FirstName"=>$UsedDetail['RU_FistName'],
						"Camp_CP_LastName"=>$UsedDetail['RU_LastName'],
						"Camp_CP_Address1"=>$UsedDetail['RU_Address1'],
						"Camp_CP_Address2"=>$UsedDetail['RU_Address2'],
						"Camp_CP_City"	=>$UsedDetail['RU_City'],
						"Camp_CP_State"=>$UsedDetail['RU_State'],
						"Camp_CP_Country"=>$UsedDetail['RU_Country'],
						"Camp_CP_ZipCode"=>$UsedDetail['RU_ZipCode'],
						"Camp_CP_Email"=>$UsedDetail['RU_EmailID'],
						"Camp_CP_Phone"=>$UsedDetail['RU_Phone'],						
						"Camp_Status"=>1);
		
			$this->objFund->DataArray=$DataArray;
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 0";
			$lMessage = "Error in Fundraiser Step 0";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser Step 0 completed";
				$lMessage = "Fundraiser Step 0 completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
		    
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			
			$this->SetMsg();
		}
		
		private function createGroupCode($campTitle, $stateAbrivation)
		{
			$strNumber = numberUnique();
			$iniTitle = strtoupper(substr(trim($campTitle),0,1));
			$sufState = strtoupper(substr(trim($stateAbrivation),0,2));
			if(trim($sufState)=='')
				$sufState = 'US';
			return  $iniTitle.$strNumber.$sufState;
		}
		
		public function Update_fundraiser_step1()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
					
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->camp_bgImage = $_FILES['camp_bgImage'];
			$this->objUT1->UserID=$this->SF_LoggedInDetail['UserType1']['user_id'];
			$UsedDetail=$this->objUT1->GetUserDetails();
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$F_Camp_Status=2;
			
			$F_Camp_Cat_ID=request('post','category',0);
			$F_Camp_Title=request('post','title',0);
			$F_Camp_UrlFriendlyName=RemoveSpecialChars($F_Camp_Title);
			$F_Camp_ShortDescription=request('post','subTitle',0);
			
			$F_Camp_DonationGoal=request('post','donation',0);
			$F_Camp_CP_City=request('post','location',0);
			$F_Camp_DateSpecified=request('post','radio1',0);//start date check
			$F_Camp_Duration_Days=request('post','FR_DurationDays',0);
			$F_Camp_SpecifiedDate=request('post','specifiedDate',0);
			
			$F_Camp_IsPrivate=request('post','checkbox',0);
			
			$F_Camp_Location_City=request('post','Camp_Location_City',0);
			$F_Camp_Location_State=request('post','Camp_Location_State',0);
			$F_Camp_Location_Country=request('post','Camp_Location_Country',0);
			$F_Camp_Location_Logitude=request('post','Camp_Location_Logitude',0);
			$F_Camp_Location_Latitude=request('post','Camp_Location_Latitude',0);
			
			if($F_Camp_DateSpecified==2)
		   	  $F_Camp_StartDate=ChangeDateFormat($F_Camp_SpecifiedDate,"Y-m-d","d-m-Y");
			else
			 $F_Camp_StartDate=ChangeDateFormat(getDateTime(0,"m/d/Y"),"Y-m-d","m/d/Y");  
			$DataArray=array("Camp_Cat_ID"=>$F_Camp_Cat_ID,
						"Camp_Title"=>$F_Camp_Title,
						"Camp_UrlFriendlyName"=>$F_Camp_UrlFriendlyName,
						"Camp_ShortDescription"=>$F_Camp_ShortDescription,
						"Camp_DonationGoal"=>$F_Camp_DonationGoal,
						"Camp_StartDate"=>$F_Camp_StartDate,
						"Camp_Duration_Days"=>$F_Camp_Duration_Days,
						"Camp_Location_City"=>$F_Camp_Location_City,
						"Camp_Location_State"=>$F_Camp_Location_State,
						"Camp_Location_Country"=>$F_Camp_Location_Country,
						"Camp_Location_Logitude"=>$F_Camp_Location_Logitude,
						"Camp_Location_Latitude"=>$F_Camp_Location_Latitude,
						"Camp_IsPrivate"=>$F_Camp_IsPrivate,
						"Camp_Status"=>$F_Camp_Status,											
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Locale"=>GetUserLocale()
						);
			$this->objFund->DataArray=$DataArray;
			$this->F_camp_ProcessLog='step1 completed';
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 1";
			$lMessage = "Error in Fundraiser Step 1";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser Step 1 completed";
				$lMessage = "Fundraiser Step 1 completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
		    $this->objFund->F_Camp_Status=2;
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step2() {
			$this->verify_user();
			$this->FR_id = request('post', 'FR_id', 0);
			$this->objFund->F_Camp_ID = $this->FR_id;
		
			$F_Camp_Status = 3;
			//dump($_REQUEST,1);
			$F_Camp_DescriptionHTML = request('post', 'aboutFundraiser', 0);
			$F_Camp_DescriptionHTML  = strip_tags($F_Camp_DescriptionHTML);
			
			$F_Camp_SalesForceID = request('post', 'Camp_SalesForceID', 0);
			
			$facebookURL	= request('post', 'facebookURL', 0);
			$twitterURL		= request('post', 'twitterURL', 0);
			$instagramURL	= request('post', 'instagramURL', 0);
			$youtubeURL		= request('post', 'youtubeURL', 0);
			
			$F_Camp_SocialMediaUrl = json_encode(array(
				"facebook"	=> $facebookURL,
				"twitter"	=> $twitterURL,
				"instagram"	=> $instagramURL,
				"youtube"	=> $youtubeURL));

			$DataArray = array(
				"Camp_DescriptionHTML"	=> $F_Camp_DescriptionHTML,
				"Camp_SalesForceID"		=> $F_Camp_SalesForceID,
				"Camp_SocialMediaUrl"	=> $F_Camp_SocialMediaUrl,
				"Camp_Status"			=> $F_Camp_Status,
				"Camp_Locale"			=> GetUserLocale(),
				"Camp_LastUpdatedDate"	=> getDateTime());
							
			$this->F_camp_ProcessLog = 'step2 completed';
			$this->objFund->DataArray = $DataArray;
			
			$this->objFund->ProcessFundraiserSetup();
			
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 2";
			$lMessage = "Error in Fundraiser Step 2";
			if($this->FR_status == 1) {
				$sMessage = "Fundraiser Step 2 completed";
				$lMessage = "Fundraiser Step 2 completed";
			}
			
			$DataArray = array(	
				"UType"			=> $userType,
				"UID"			=> $userID,
				"UName"			=> $userName,
				"RecordId"		=> $this->objFund->F_Camp_ID,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objFund));
								
			$this->objFund->updateProcessLog($DataArray);
			
			/*-----------------------------*/
			$this->FR_ErrorCode = $this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg = $this->objFund->P_ErrorMessage;
			$this->FR_MsgType = $this->objFund->P_MsgType;
			$this->FR_ConfirmCode = $this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg = $this->objFund->P_ConfirmMsg;
			$this->SuccessUrl = 'setup_fundraiser/index/' . keyEncrypt($this->FR_id);
			$this->ErrorUrl = 'setup_fundraiser/index/' . keyEncrypt($this->FR_id);
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step3()
		{	
			$this->verify_user();		
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$F_Camp_Status=4;
			$DataArray=array("Camp_Status"=>$F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale());
			
			$this->objFund->DataArray=$DataArray;
			$this->F_camp_ProcessLog='step3 completed';
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 3";
			$lMessage = "Error in Fundraiser Step 3";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser Step 3 completed";
				$lMessage = "Fundraiser Step 3 completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/			
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			
			$this->SetMsg();
		}
		
		public function Update_fundraiser_step4()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			$this->EnabledTeamFundraiser = request('post','teamFundraiser',1);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			
			if($this->EnabledTeamFundraiser)
				$F_Camp_Status=5;
			else
				$F_Camp_Status=6;
			
			$F_Camp_Chk_AccountNumber = request('post','accountNumber',0);
		    $F_Camp_AccountNumber = request('post','StripeACNumber',0);
			$F_Camp_NonChk_AccountNumber = request('post','NPO_ACnumber',0);
			$this->objFund->F_Camp_NonAccountNumber = request('post','UUID',0);
			$DataArray = array("Camp_Status"=>$F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale(),'Camp_Stripe_Status'=>'','Camp_Stripe_ConnectedID'=>'','Camp_TaxExempt'=>'',"Camp_NPO_EIN"=>'',"Camp_PaymentMode"=>"NPO-STRIPE-ACCOUNT");
			$this->F_camp_ProcessLog='step4 completed';
			$this->objFund->DataArray=$DataArray;
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 4";
			$lMessage = "Error in Fundraiser Step 4";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser Step 4 completed";
				$lMessage = "Fundraiser Step 4 completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
		
			//send email npo donor use to npo user	
			if($this->FR_status==1)
			{
				$this->MailOnFundRaiserApprovelDonor($this->FR_id);
				$this->MailOnFundRaiserApprovelNPO($this->FR_id);
				$this->MailOnSetupCompleteOwner($this->FR_id);
				$this->MailOnSetupCompleteWebMaster($this->FR_id);
			}
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);

			$this->SetMsg();
			
		}
		
		public function Update_fundraiser_step5()
		{
			$this->verify_user();
			$this->FR_id = request('post','FR_id',0);
			$CampCode = request('post','uniqueCode',0);
			$teamFundraiser = request('post','team_fundraiser',1);
			$stripeInherit = request('post','inherit_stripe',1);
			$teamFundType = ($teamFundraiser==1 && isset($teamFundraiser))?"C":NULL;
			$CampCode	= ($teamFundraiser==1 && isset($teamFundraiser))?$CampCode:NULL;
			$paymentOption = ($stripeInherit==1)?"CAPTAIN-STRIPE-ACCOUNT":"INDIVIDUAL-STRIPE-ACCOUNT";
			$optionJSON = json_encode(array("Status"=>"Enable","PaymentOption"=>$paymentOption,"Notes"=>"","RestrictedFields"=>""));					
			
			$this->objFund->F_Camp_ID=$this->FR_id;
			$F_Camp_Status=6;
			$this->objFund->F_Camp_NonAccountNumber=request('post','UUID',0);
			$DataArray =array("Camp_Status"=>$F_Camp_Status,
							"Camp_LastUpdatedDate"=>getDateTime(),
							"Camp_Locale"=>GetUserLocale(),
							"Camp_TeamUserType"=>$teamFundType,
							"Camp_Code"=>$CampCode,
							"Camp_TeamFundariserOptions"=>$optionJSON);
			//dump($DataArray);
			$this->F_camp_ProcessLog='step5 completed';
			$this->objFund->DataArray=$DataArray;
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}	
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser Step 5";
			$lMessage = "Error in Fundraiser Step 5";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser Step 5 completed";
				$lMessage = "Fundraiser Step 5 completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
		
			//send email npo donor use to npo user				
			$this->SuccessUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);
			$this->ErrorUrl='setup_fundraiser/index/'.keyEncrypt($this->FR_id);

			$this->SetMsg();
			
		}
		
		public function generateuniquecode()
		{
			//dump($_REQUEST);
			
			$DataArray=array('Camp_Title','Camp_CP_State');
			$this->objFund->F_Camp_ID = request('post','Camp_ID',1);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail = $FundraiserDetail[0];
			$groupCode = $this->returnUniqueGroupCode($FundraiserDetail['Camp_Title'],$FundraiserDetail['Camp_CP_State']);
			echo json_encode($groupCode);exit;	
		}
		
		public function returnUniqueGroupCode($title,$state)
		{
			$CampCode = $this->createGroupCode($title,$state); 
			
			if($this->objCom->checkGroupCodeDuplicacy($CampCode))
			{
				return $CampCode;
			}
			else
			{
				$this->returnUniqueGroupCode($title,$state)	;				
			}	
		}
		
		public function IsDuplicateCode()
		{
		   $status=false;
		   $CampCode = request('get','uniqueCode',0);
		   $status = $this->objCom->checkGroupCodeDuplicacy($CampCode);
		   echo json_encode($status);exit;	
		}
		private function MailOnSetupCompleteOwner($FR_id)
		{
					$DataArray=array('*');
					$this->objFund->F_Camp_ID=$FR_id;	
					$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
					
					$uname=$FundraiserDetail[0]['Camp_CP_FirstName'].' '.$FundraiserDetail[0]['Camp_CP_LastName'];
					$this->load_model('Email','objemail');
					$Keyword='SetupCompleteOwner';
					$where=" Where Keyword='".$Keyword."'";
					$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
					$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
					$tpl=new View;
					$link=URL.'fundraiser/'.keyEncrypt($FR_id).'/'.RemoveSpecialChars($FundraiserDetail[0]['Camp_UrlFriendlyName']);
					$tpl->assign('link',$link);
					$tpl->assign('uname',$uname);
					$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
					
					$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
					'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
					'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],
					'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
	'SendMode'=>'1','AddedOn'=>getDateTime());
					$id=$this->objemail->InsertEmailDetail($InsertDataArray);
					$Eobj	= LoadLib('BulkEmail');
					
					$Status=$Eobj->sendEmail($id);
					if($Status)
					{
						$this->FR_status=1;
					}
					else
					{
						$this->FR_status=0;
						$this->FR_ErrorCode='E13017';
						$this->FR_ErrorMsg='E13017';
					}
				unset($Eobj);
		}
		private function MailOnSetupCompleteWebMaster($FR_id)
		{
			$DataArray=array('*');
			$this->objFund->F_Camp_ID=$FR_id;	
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$FundraiserDetail[0]['camp_thumbImage']);
		
			$this->load_model("UserType2","objUserT2");
			$DataArray=array('*');
			$cond=" AND NPO_EIN=".$FundraiserDetail[0]['Camp_NPO_EIN'];
			$NpoDetail=$this->objUserT2->GetNPODetail($DataArray,$cond);
			
			$uname=$NpoDetail['RU_FistName'].' '.$NpoDetail['RU_LastName'];
			$this->load_model('Email','objemail');
			$Keyword='SetupCompleteWbmaster';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$tpl=new View;
			$link=URL.'adminpanel/campaign/index/edit/'.keyEncryptadmin($FR_id);
			//$link=URL.'adminpanel/'.keyEncrypt($FR_id).'/FundDetail';
			$tpl->assign('link',$link);
			$tpl->assign('FundraiserDetail',$FundraiserDetail);
			$tpl->assign('npoDetail',$NpoDetail);
			$tpl->assign('uname',$uname);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			
			$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>'qualdev.test@gmail.com',
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->FR_status=1;
			}
			else
			{
				$this->FR_status=0;
				$this->FR_ErrorCode='E13017';
				$this->FR_ErrorMsg='E13017';
			}
		unset($Eobj);
		}
		private function MailOnFundRaiserApprovelDonor($FR_id)
		{
				
				$DataArray=array('*');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
				
				$this->load_model('Email','objemail');
				$Keyword='FundraiserApprovelDonor';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				$link=URL.'faqs.html';
				$tpl->assign('link',$link);
				$tpl->assign('uname',$uname);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				
				$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
					if($Status)
					{
						$this->FR_status=1;
						
					}
					else
					{
						$this->FR_status=0;
						$this->FR_ErrorCode='E13017';
						$this->FR_ErrorMsg='E13017';
					}
					unset($Eobj);
			
		}
			private function MailOnFundRaiserApprovelNPO($FR_id)
			{
					
					$DataArray=array('*');
					$this->objFund->F_Camp_ID=$FR_id;	
					$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
					$this->load_model("UserType2","objUserT2");
					$DataArray=array('*');
					$cond=" AND NPO_EIN=".$FundraiserDetail[0]['Camp_NPO_EIN'];
					$NpoDetail=$this->objUserT2->GetNPODetail($DataArray,$cond);
					$uname=$NpoDetail['RU_FistName'].' '.$NpoDetail['RU_LastName'];
					$this->load_model('Email','objemail');
					$Keyword='FundraiserApprovelNPO';
					$where=" Where Keyword='".$Keyword."'";
					$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
					$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
					$tpl=new View;
					
					$link=URL.'fundraiser/'.keyEncrypt($FR_id).'/'.RemoveSpecialChars($FundraiserDetail[0]['Camp_UrlFriendlyName']);
					$tpl->assign('link',$link);
					$tpl->assign('link',$link);
					$tpl->assign('uname',$uname);
					$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
					$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
					'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
					'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$NpoDetail['RU_EmailID'],
					'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
	'SendMode'=>'1','AddedOn'=>getDateTime());
					$id=$this->objemail->InsertEmailDetail($InsertDataArray);
					$Eobj	= LoadLib('BulkEmail');
					$Status=$Eobj->sendEmail($id);
					if($Status)
					{
						$this->FR_status=1;
					}
					else
					{
						$this->FR_status=0;
						$this->FR_ErrorCode='E13017';
						$this->FR_ErrorMsg='E13017';
					}
				unset($Eobj);
			}
		
		public function Update_fundraiser_complete($FR_id)
		{
			$this->verify_user();
			$this->FR_id =keyDecrypt($FR_id);
			$F_Camp_Status=6;
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$DataArray=array("Camp_Status"=>$F_Camp_Status,"Camp_LastUpdatedDate"=>getDateTime(),"Camp_Locale"=>GetUserLocale());
			
			$this->F_camp_ProcessLog='step5 completed';
			$this->objFund->ProcessFundraiserSetup();
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}
			
			$this->FR_status=$this->objFund->P_status;
			$sMessage = "Error in Fundraiser completion";
			$lMessage = "Error in Fundraiser completion";
			if($this->FR_status==1)
			{
				$sMessage = "Fundraiser setup completed";
				$lMessage = "Fundraiser setup completed";
			}			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->F_Camp_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			$this->FR_ErrorCode=$this->objFund->P_ErrorCode;
			$this->FR_ErrorMsg=$this->objFund->P_ErrorMessage;
			$this->FR_MsgType=$this->objFund->P_MsgType;
			$this->FR_ConfirmCode=$this->objFund->P_ConfirmCode;
			$this->FR_ConfirmMsg=$this->objFund->P_ConfirmMsg;
			
			$this->SuccessUrl='fundraiserdetail/index/'.keyEncrypt($this->FR_id).'/FundDetail';
			$this->ErrorUrl='fundraiserdetail/index/'.keyEncrypt($this->FR_id).'/FundDetail';
			$this->SetMsg();
		}
		
		public function FundInsert($level=1)
		{	
			$this->verify_user();		
			$this->objFund->UserId=$this->SF_LoginUserId;
			$a=$this->objFund->FundraiserInsert($level);
			
			/*----update process log------*/			
			if(isset($this->SF_LoggedInDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			}else{
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$userName	= $this->SF_LoggedInDetail['UserType2']['user_fullname'];
			}
			
			$sMessage = "Error in Fundraiser level insert.";
			$lMessage = "Error in Fundraiser level insert id=$a.";
			if($a) {
				$sMessage = "Fundraiser has inserted successfully.";
				$lMessage = "Fundraiser(id=$a) has inserted successfully.";
			}
						
			$DataArray = array(	
				"UType"			=>$userType,
				"UID"			=>$userID,
				"UName"			=>$userName,
				"RecordId"		=>$this->objFund->F_Camp_ID,
				"SMessage"		=>$sMessage,
				"LMessage"		=>$lMessage,
				"Date"			=>getDateTime(),
				"Controller"	=>get_class()."-".__FUNCTION__,
				"Model"			=>get_class($this->objFund));
				
			$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			
			redirect(URL."/setup_fundraiser/index/".keyEncrypt($a));	
		}
		
		
		private function SetMsg()
		{
				if($this->FR_status==1)
				{
					$confirmationParams = array(
					"msgCode"	=> $this->FR_ConfirmCode,
					"msg"		=> $this->FR_ConfirmMsg,
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
					
					EnPException::setError($errParams);
					redirect(URL.$this->ErrorUrl);			
				}
		}
		
		
		public function getStripResponse($code)
		{		
			$this->verify_user();
			$token_request_body = array('grant_type' => 'authorization_code','client_id' =>STRIPE_ACCOUNT_ID,'code' => $code,'client_secret' => STRIPE_PRIVATE_KEY);
			//dump($token_request_body);
			$req = curl_init('https://connect.stripe.com/oauth/token');
			curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($req, CURLOPT_POST, true );
			curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
			// TO DO: Additional error handling
			$respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
			//echo $req;exit;
			$resp = json_decode(curl_exec($req), true);
			
			curl_close($req);
			//dump($resp);
			return $resp;
		}
	}
?>