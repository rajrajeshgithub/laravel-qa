<?php

	class Team_Fundraiser_Controller extends Controller
	{
		public $SF_LoginUserId;
		public $C_ID;
		public $UserDetails;
		public $SF_LoginUserType;
		public $C_GroupCode;
		public function __construct()
		{
			$this->load_model("Common","objCom");
			$this->load_model("UserType1","objUT1");
			$this->load_model("UserType2","objUT2");
			$this->load_model('Fundraisers','objFund');
			$this->arrCampaign = array();
		}
		
		public function index($code='')
		{
			if($code!='')
				$this->C_GroupCode = keyDecrypt($code);
			$this->Validate_LogedinUser_Team_Fundraiser();
			$this->tpl = new view;
			$this->showCodeVerificationForm();
		}
		
		private function showCodeVerificationForm()
		{
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('team_fundraiser');
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('groupCode',$this->C_GroupCode);
			$this->tpl->draw("team_fundraiser/codeverificationform");
		}
		
		public function showCaptainFundraiserPage()
		{
			$this->Validate_LogedinUser_Team_Fundraiser();
			$this->team_fundraiser_verification();
			$DataArray = array("CONCAT(RU_FistName,' ',RU_LastName) as FullName", "C.Camp_ID","Camp_Code","Camp_TeamUserType","Camp_PaymentMode","Camp_StylingTemplateName","Camp_Title","Camp_ShortDescription","camp_thumbImage","camp_bgImage","Camp_UrlFriendlyName","ROUND(Camp_DonationGoal) as Camp_DonationGoal","Camp_Location_City","Camp_Location_State","Camp_Location_Country","Camp_Location_Logitude","Camp_Location_Latitude","ROUND(Camp_DonationReceived) as Camp_DonationReceived","concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),'%') AS Donationpercentage","Camp_StartDate", "Camp_EndDate");
			$Condition = " AND Camp_Code='".$this->C_GroupCode."' AND Camp_Status=15 AND Camp_TeamUserType='C' AND Camp_Deleted!='1'";
			$arrCampaign = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			//dump($arrCampaign);
			$this->tpl = new view;
			$this->tpl->assign('arrCampaign',$arrCampaign[0]);
			$this->tpl->assign('camp_code',$this->camp_code);
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			//$this->tpl->assign($arrMetaInfo);
			$this->tpl->draw("team_fundraiser/previewfundraiser");
		}
		
		private function Validate_LogedinUser_Team_Fundraiser()
		{	
			$this->SF_LoggedInDetail=getSession('Users');		
			
			if($this->objUT1->checkLogin($this->SF_LoggedInDetail)>0)
			{
				$this->SF_LoginUserId	= keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);	
				$this->SF_LoginUserType = 1;
			}
			elseif($this->objUT2->checkLogin($this->SF_LoggedInDetail)>0)
			{
				$this->SF_LoginUserId	= keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);	
				$this->SF_LoginUserType = 2;
			}
			else
			{
				redirect(URL."ut1/?refurl=".urlencode(URL."team_fundraiser/index/".keyEncrypt($this->C_GroupCode)));
			}			
		}
		
		public function checkDuplicateTeamMember()
		{
			$this->Validate_LogedinUser_Team_Fundraiser();
			$arrTeamDetails = $this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_TeamUserType','Camp_Deleted')," AND Camp_Code='".$this->C_GroupCode."' AND (Camp_TeamUserType='T' OR Camp_TeamUserType='C') AND Camp_RUID=".$this->SF_LoginUserId." Order by Camp_TeamUserType DESC");			
			//dump($arrTeamDetails);
			if(count($arrTeamDetails[0]))
			{				
				foreach($arrTeamDetails as $key => $arrValue)
				{
					if($arrValue['Camp_TeamUserType']=='T' && $arrValue['Camp_Deleted']!='1')
					{
						
						if($this->SF_LoginUserType==1){
						$this->SetStatus(1,'C70002');
							redirect(URL.'ut1myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($arrValue['Camp_ID']));
						}elseif($this->SF_LoginUserType==2){
							$this->SetStatus(1,'C70002');
							redirect(URL.'ut2myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($arrValue['Camp_ID']));
						}else{
							$this->SetStatus(0,'E25002');
							redirect(URL.'team_fundraiser');
						} 	
					}
					elseif($arrValue['Camp_TeamUserType']=='C' && $arrValue['Camp_Deleted']!='1')
					{
						if($this->SF_LoginUserType==1){
						$this->SetStatus(1,'C70002');
							redirect(URL.'ut1myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($arrValue['Camp_ID']));
						}elseif($this->SF_LoginUserType==2){
							$this->SetStatus(1,'C70002');
							redirect(URL.'ut2myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($arrValue['Camp_ID']));
						}else{
							$this->SetStatus(0,'E25002');
							redirect(URL.'team_fundraiser');
						} 		
					}	
				}
				
			}
		}
		
		public function team_fundraiser_verification()
		{			
			$this->Validate_LogedinUser_Team_Fundraiser();
			$this->init();
			$DataArray = array("*");
			$Condition = " AND Camp_Code='".$this->C_GroupCode."' AND Camp_Status=15 AND Camp_TeamUserType='C' AND Camp_Deleted!='1'";
			$arrCampaign = $this->objFund->GetFundraiserDetails($DataArray,$Condition);
			
			if(isset($arrCampaign[0]) && count($arrCampaign[0]))
			{
				$this->arrCampaign = $arrCampaign[0];
				$fundraiserOption = json_decode($this->arrCampaign['Camp_TeamFundariserOptions'],true);
				if(strtolower($fundraiserOption['Status'])!='enable')
				{
					$this->SetStatus(0,'E20001');
					redirect(URL."team_fundraiser");
				}
				$this->checkDuplicateTeamMember();				
			}
			else
			{
				$this->SetStatus(0,'E25003');
				redirect(URL.'team_fundraiser');
			}
		}
		
		
		public function joinFundraiser()
		{
			$this->Validate_LogedinUser_Team_Fundraiser();
			$this->team_fundraiser_verification();
			$DataArray	= array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','RU.RU_City','RU.RU_ZipCode','RU.RU_State','RU.RU_Country','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID');
				$Condition	= " AND RU.RU_ID=".$this->SF_LoginUserId;
				$this->UserDetails = $this->objUT1->GetUserDetails($DataArray,$Condition);				
				$DataArray = $this->createDataArray();				
				$this->C_ID = $this->objFund->setCampaign_DB($DataArray);
				if($this->C_ID)
				{
					$this->sendMailCaptain($arrCampaign['Camp_ID']);
					$this->sendMailTeam($this->C_ID);
					/*----update process log------*/
					if($this->SF_LoginUserType==1)		
						$userType = 'UT1';
					if($this->SF_LoginUserType==2)
						$userType = 'UT2';						
					
					$sMessage = "Process to join team fundraiser successful";
					$lMessage = "Process to join team fundraiser successful";
				
					$DataArray = array(	"UType"=>$userType,
										"UID"=>$this->SF_LoginUserId,
										"UName"=>$this->SF_LoggedInDetail['user_fullname'],
										"RecordId"=>$arrValue['Camp_ID'],
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objFund));	
					$this->objFund->updateProcessLog($DataArray);
				
					if($this->SF_LoginUserType==1){						
						$this->SetStatus(1,'C70001');
						redirect(URL.'ut1myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($this->C_ID));
					}elseif($this->SF_LoginUserType==2){
						$this->SetStatus(1,'C70001');
						redirect(URL.'ut2myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($this->C_ID));
					}else{
						$this->SetStatus(0,'E25002');
						redirect(URL.'team_fundraiser');
					}
				}
				$this->SetStatus(0,'E25003');
				redirect(URL.'team_fundraiser');
		}
		
		private function createDataArray()
		{
			$arrFundraiserOption = json_decode($this->arrCampaign['Camp_TeamFundariserOptions'],true);
			$subTeamTitle=$this->UserDetails['RU_FistName'].' '.$this->UserDetails['RU_LastName'];
			$DataArray = array("Camp_Cat_ID" => $this->arrCampaign['Camp_Cat_ID'],
						"Camp_Level_ID" => $this->arrCampaign['Camp_Level_ID'],
						"Camp_Duration_Days" => $this->arrCampaign['Camp_Duration_Days'],
						"Camp_Title" => $this->arrCampaign['Camp_Title'],
						"camp_thumbImage" => $this->arrCampaign['camp_thumbImage'],
						"camp_bgImage" => $this->arrCampaign['camp_bgImage'],
						"Camp_UrlFriendlyName" => $this->arrCampaign['Camp_UrlFriendlyName'],
						"Camp_ShortDescription" => $subTeamTitle,
						"Camp_Description" => $this->arrCampaign['Camp_Description'],
						"Camp_DescriptionHTML" => $this->arrCampaign['Camp_DescriptionHTML'],
						"Camp_DonationGoal" => $this->arrCampaign['Camp_DonationGoal'],
						"Camp_DonationReceived" => 0,
						"Camp_StartDate" => $this->arrCampaign['Camp_StartDate'],
						"Camp_EndDate" => $this->arrCampaign['Camp_EndDate'],
						"Camp_CP_FirstName" => $this->UserDetails['RU_FistName'],
						"Camp_CP_LastName" => $this->UserDetails['RU_LastName'],
						"Camp_CP_Address1" => $this->UserDetails['RU_Address1'],
						"Camp_CP_Address2" => $this->UserDetails['RU_Address2'],
						"Camp_CP_City" => $this->UserDetails['RU_City'],
						"Camp_CP_State" => $this->UserDetails['RU_State'],
						"Camp_CP_Country" => $this->UserDetails['RU_Country'],
						"Camp_CP_ZipCode" => $this->UserDetails['RU_ZipCode'],
						"Camp_CP_Email" => $this->UserDetails['RU_EmailID'],
						"Camp_CP_Phone" => $this->UserDetails['RU_Phone'],
						"Camp_UserBio" => $this->arrCampaign['Camp_UserBio'],
						"Camp_Location_City" => $this->arrCampaign['Camp_Location_City'],
						"Camp_Location_State" => $this->arrCampaign['Camp_Location_State'],
						"Camp_Location_Zip" => $this->arrCampaign['Camp_Location_Zip'],
						"Camp_Location_Country" => $this->arrCampaign['Camp_Location_Country'],
						"Camp_Location_Logitude" => $this->arrCampaign['Camp_Location_Logitude'],
						"Camp_Location_Latitude" => $this->arrCampaign['Camp_Location_Latitude'],
						"Camp_Stripe_Status" => $arrFundraiserOption['PaymentOption']=='INDIVIDUAL-STRIPE-ACCOUNT'?0:$this->arrCampaign['Camp_Stripe_Status'],
						"Camp_Stripe_ConnectedID" => $arrFundraiserOption['PaymentOption']=='INDIVIDUAL-STRIPE-ACCOUNT'?'':$this->arrCampaign['Camp_Stripe_ConnectedID'],
						"Camp_Stripe_Response" => $arrFundraiserOption['PaymentOption']=='INDIVIDUAL-STRIPE-ACCOUNT'?'':$this->arrCampaign['Camp_Stripe_Response'],
						"Camp_PaymentMode" => $arrFundraiserOption['PaymentOption'],
						"Camp_Status" => 31,
						"Camp_SearchTags" => $this->arrCampaign['Camp_SearchTags'],
						"Camp_WebMasterComment" => $this->arrCampaign['Camp_WebMasterComment'],
						"Camp_RUID" =>$this->UserDetails['RU_ID'],
						"Camp_Code" => $this->arrCampaign['Camp_Code'],
						"Camp_TeamUserType" => 'T',
						"Camp_TeamFundariserOptions" => $this->arrCampaign['Camp_TeamFundariserOptions'],
						"Camp_NPO_EIN" => $this->arrCampaign['Camp_NPO_EIN']=='INDIVIDUAL-STRIPE-ACCOUNT'?'':$this->arrCampaign['Camp_NPO_EIN'],
						"Camp_SocialMediaUrl" => $this->arrCampaign['Camp_SocialMediaUrl'],
						"Camp_SalesForceID" => $this->arrCampaign['Camp_SalesForceID'],
						"Camp_TaxExempt" => $this->arrCampaign['Camp_TaxExempt'],
						"Camp_IsPrivate" => $this->arrCampaign['Camp_IsPrivate'],
						"Camp_StylingTemplateName" => $this->arrCampaign['Camp_StylingTemplateName'],
						"Camp_StylingDetails" => $this->arrCampaign['Camp_StylingDetails'],
						"Camp_MinimumDonationAmount" => $this->arrCampaign['Camp_MinimumDonationAmount'],
						"Camp_Tags" => $this->arrCampaign['Camp_Tags'],
						"Camp_CreatedDate" => getDateTime(),
						"Camp_LastUpdatedDate" => getDateTime(),
						"camp_ProcessLog" => 'Added on '.getDateTime,
						"Camp_Locale" => GetUserLocale(),
						"Camp_CustomToggles" => $this->arrCampaign['Camp_CustomToggles'],
						"Camp_DemoUse" => $this->arrCampaign['Camp_DemoUse'],
						"Camp_Deleted" => $this->arrCampaign['Camp_Deleted']
						);
				return $DataArray;
		}
		
		private function sendMailCaptain($FR_id)
		{
		// this will be a future toggle, for now off due to bug and overkill
		//	$DataArray=array('*');
		//	$this->objFund->F_Camp_ID=$FR_id;
			//$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			
			//$uname=$FundraiserDetail[0]['Camp_CP_FirstName'].' '.$FundraiserDetail[0]['Camp_CP_LastName'];
		//	$this->load_model('Email','objemail');
		//$Keyword='joined_team_to_captain';
		//	$where=" Where Keyword='".$Keyword."'";
			//$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
		//	$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//$tpl=new View;			
		//	$tpl->assign('campDetails',$FundraiserDetail[0]);
		//	$tpl->assign('userDetails',$this->SF_LoggedInDetail);
		//	$tpl->assign('uname',$uname);
		//	$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			
		//	$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'], 'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'], 'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],	'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
		//	$id=$this->objemail->InsertEmailDetail($InsertDataArray);
		//	$Eobj	= LoadLib('BulkEmail');
			
		//	$Status=$Eobj->sendEmail($id);
		//	if($Status)
		//	{
			//	$this->FR_status=1;
		//	}
		//	else
			//{
			//	$this->FR_status=0;
		//		$this->FR_ErrorCode='E13017';
		//		$this->FR_ErrorMsg='E13017';
		//	}
		//		unset($Eobj);	
		}
		
		private function sendMailTeam($FR_id)
		{
			$DataArray=array('*');
			$this->objFund->F_Camp_ID=$FR_id;	
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			
			$uname=$FundraiserDetail[0]['Camp_CP_FirstName'].' '.$FundraiserDetail[0]['Camp_CP_LastName'];
			$this->load_model('Email','objemail');
			$Keyword='JoinedTeamFundraiserTeam';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$tpl=new View;
			$tpl->assign('uname',$uname);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			
			$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
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
		
		private function init()
		{
			$this->C_GroupCode = request("post","uniquecode",0);			
			if($this->C_GroupCode=='')
			{
				$this->FR_status = 0;
				$this->FR_ConfirmCode = "000";
				$this->FR_ConfirmMsg = "Please enter group code.";
				redirect(URL."team_fundraiser");
			}
		}
		public function VerifyStripConnection()
		{
			$this->Validate_LogedinUser_Team_Fundraiser();
			$FundId=getSession("TeamFundariserID");
			$this->FR_id=keyDecrypt($FundId);
			setSession("TeamFundariserID",$FundId);
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
										
						//redirect(URL."setup_fundraiser/index/".keyEncrypt($this->FR_id));
					$arreyFields = array("Camp_Status"=>31,"Camp_Stripe_Status"=>1,"Camp_Stripe_ConnectedID"=>$this->stripe_clientID,"Camp_PaymentMode"=>"INDIVIDUAL-STRIPE-ACCOUNT",
										"Camp_Stripe_Response"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"Camp_LastUpdatedDate"=>getDateTime(),
										"camp_ProcessLog"=>'CONCAT(camp_ProcessLog,","Sucessfully Connected to Stripe Updated on.getDateTime()")');
									
					$sMessage = "Sucessfully Connected to Stripe";
					$lMessage = "Sucessfully Connected to Stripe";
				}
				else
				{					
					$arreyFields = array("Camp_Status"=>31,"Camp_Stripe_Status"=>0,"Camp_Stripe_Response"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"Camp_LastUpdatedDate"=>getDateTime(),"camp_ProcessLog"=>'CONCAT(camp_ProcessLog,","Got Code.$this->stripe_code. but stripe_user_id not feteched Updated on .getDateTime()")');						
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
										"RecordId"=>$this->FR_id,
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
			if($this->SF_LoginUserType==1)                   
				redirect(URL."ut1myaccount/TeamFundraiserBasicDetail/".keyEncrypt($this->objFund->F_Camp_ID));	
			elseif($this->SF_LoginUserType==2)                   
				redirect(URL."ut2myaccount/TeamFundraiserBasicDetail/".keyEncrypt($this->objFund->F_Camp_ID));	
			else
				redirect(URL."ut1/?refurl=".urlencode(URL."team_fundraiser"));
		}
		
		public function getStripResponse($code)
		{
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

		private function SetStatus($Status,$Code)
		{
			if($Status)
			{
				$this->Pstatus	= 1;
				$messageParams=array("msgCode"=>$Code,
												 "msg"=>"Custom Confirmation message",
												 "msgLog"=>0,									
												 "msgDisplay"=>1,
												 "msgType"=>2);
					EnPException::setConfirmation($messageParams);
			}
			else
			{
				$this->Pstatus	= 0;
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