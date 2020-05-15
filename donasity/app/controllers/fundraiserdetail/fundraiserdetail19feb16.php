<?php
	class fundraiserdetail_Controller extends Controller
	{
		public $SF_LoggedInDetail, $F_FundId, $F_URL, $F_Status, $F_CommentList, $F_UserType, $F_FriendlyUrl;

		public function __construct()
		{
			$this->load_model("Common", "objCom");
			$this->load_model('UserType1', 'objUT1');
			$this->load_model('FundraisersDetail', 'objFund');
			$this->load_model('Fundraisers', 'objF');
			$this->SF_LoggedInDetail = getSession('Users');
		}
		public function verify_user()
		{	
			/*if(!$this->objUT1->checkLogin(getSession('Users')))
			{
				redirect(URL."ut1/login");
			}*/
		}
		
		
		public function PrintFundraiserDetails($FundId) {
			$this->tpl = new View;
			$detailURL = URL . "fundraiser/" . $FundId;
			$FundId = keyDecrypt($FundId);
			if($FundId != '') {
				$this->load_model("Common","objCommon");
				
				$arrMetaInfo = $this->objCommon->GetPageCMSDetails('print_fundraiser_details');
				
				$QRCODEURL = 'https://chart.googleapis.com/chart?chs=130x130&cht=qr&chl='.urlencode($detailURL).'&choe=UTF-8';
				$this->objFund->F_Camp_ID = $FundId;
				
				$this->objFund->GetFundraiserDetailForPrint();
				
				$NpoName = $this->objFund->NPO_Name;
				$arrMetaInfo["text_bottom_blue"] = strtr($arrMetaInfo["text_bottom_blue"], array('{{npo_name}}'=>$NpoName));
				$this->objFund->F_FundraiserDetail['Camp_DonationGoal'] = number_format($this->objFund->F_FundraiserDetail['Camp_DonationGoal']);
				$this->tpl->assign($arrMetaInfo);
				$this->tpl->assign("FundraiserDetail", $this->objFund->F_FundraiserDetail);
				$this->tpl->assign("QRCODEURL", $QRCODEURL);
				$this->tpl->assign("NpoName", $NpoName);
				$HTML=$this->tpl->draw('fundraiserDetail/printfundraiserdetail', true);
				$DP_Obj=LoadLib('DomPdfGen');
				$DP_Obj->DP_HTML = $HTML;
				$DP_Obj->ProcessPDF();
				exit;
			}
		}
		
		public function FundraiserDetail($FundId,$FriendlyUrl,$Status)
		{
			$this->F_FundId = $FundId;
			$this->objFund->F_Camp_ID = keyDecrypt($FundId);
			$this->F_Status = $Status;
			$this->F_FriendlyUrl = $FriendlyUrl;
			$this->objFund->F_Camp_Status = $this->F_Status;
			//dump($this->F_Status);
			$this->F_URL='https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.urlencode(URL.$_SERVER['REQUEST_URI']).'&choe=UTF-8';
			try
			{
				 if($this->F_Status == 1) {
					$this->objFund->F_WhereCondition = " AND Camp_Status=15 AND Camp_StartDate<='" . getDateTime(0, 'Y-m-d') . "' AND Camp_EndDate>='" . getDateTime(0, 'Y-m-d') . "'";
				 } else {
					$UType1 = isset($this->SF_LoggedInDetail['UserType1']['user_id']) ? keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']) : '';
					$UType2 = isset($this->SF_LoggedInDetail['UserType2']['user_id']) ? keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']) : '';
					$UType1 = ($UType1 != '') ? $UType1 : 0;
					$UType2 = ($UType2 != '') ? $UType2 : 0;
					if($UType1 != 0) {
						$this->F_UserType = 1;
						$this->objFund->F_Camp_RUID = $UType1;
					} else {
						$this->F_UserType = 2;
						$this->objFund->F_Camp_RUID = $UType2;
					}
					
				  	//$this->objFund->F_WhereCondition = " AND Camp_Status<=15 AND (Camp_RUID=" . $UType1 . " OR Camp_RUID=" . $UType2 . ")";  kills preview for stopped FR where status 21
					$this->objFund->F_WhereCondition = " AND Camp_Status > 0 AND (Camp_RUID=" . $UType1 . " OR Camp_RUID=" . $UType2 . ")";
				 }
				 
				$this->objFund->ProcessGetFundraiserDetails();
				
				$this->objF->FC_FundraiserId = $this->objFund->F_Camp_ID;
				$this->objF->FC_PageNo = 1;
				$this->objF->FC_PageLimit = 10;
				$this->objF->FC_approveStatus = 1;
				$this->F_CommentList = $this->objF->GetFundraiserComment();
				  
				if($this->objFund->P_status == 1) {
					$F_FundraiserDetail = $this->objFund->F_FundraiserDetail;
					if(strtolower($F_FundraiserDetail['Details']['Camp_StylingTemplateName']) == 'v1')
						$this->ShowTemplateV1();
					else
						$this->ShowTemplateV2();
				} else {
					$arrMetaInfo = $this->objCom->GetPageCMSDetails('fundraiser_detail');					
					$tpl = new View;
					$tpl->assign('arrBottomInfo', $this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
					$tpl->assign('MenuArray', $this->objCom->getTopNavigationArray(LANG_ID));
					$tpl->assign($arrMetaInfo);
					$tpl->assign('ErrorMessage', $arrMetaInfo['errormesage']);
					$tpl->assign('arrBottomMetaInfo', $this->objCom->GetPageCMSDetails(BOTTOM_META));
					$tpl->draw('error/error');
				}
			}
			catch(Exception $e)
			{
			
			}
		}
		
		private function ShowTemplateV1() {
			$loginStatus = 1;
			if(!$this->objUT1->checkLogin(getSession('Users'))) {
				$loginStatus = 0;
			}
			
			$Color_SCHEME_CSS = $this->objFund->F_FundraiserDetail["Details"]["Camp_StylingDetails"];
			$Color_SCHEME_CSS_Array = unserialize($Color_SCHEME_CSS);
			$Color_SCHEME_CSS = keyDecrypt($Color_SCHEME_CSS_Array["Color_SCHEME_CSS"]);
			$fundraiserDetails = $this->objFund->F_FundraiserDetail;
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('v1_fundraiser_details');
			$npoName = $fundraiserDetails['Details']['NPO_Name'];
			$arrMetaInfo["text_fundraiser_benefits"] = strtr($arrMetaInfo["text_fundraiser_benefits"],array('{{npo_name}}'=>$npoName));
			
			$tpl = new View;
			$tpl->assign('arrBottomInfo', $this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$tpl->assign('MenuArray', $this->objCom->getTopNavigationArray(LANG_ID));
			$tpl->assign($arrMetaInfo);
			$tpl->assign("Color_SCHEME_CSS", $Color_SCHEME_CSS);
			$tpl->assign("QRCODEURL", $this->F_URL);
			$tpl->assign("Status", $this->F_Status);
			$tpl->assign("CommentList", $this->F_CommentList);
			$tpl->assign('TotalRecords', $this->objF->FC_TotalRecord);
			$tpl->assign('PageNumber', '1');
			$tpl->assign('FundraiseID', $this->objFund->F_Camp_ID);
			$this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal'] = number_format($this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal']);
			$tpl->assign('F_FundraiserDetail', $fundraiserDetails);
			$tpl->assign('EncFundID', $this->F_FundId);
			$tpl->assign('FriendlyUrl', $this->F_FriendlyUrl);
			$tpl->assign('UserType', $this->F_UserType);
			$tpl->assign("loginStatus", $loginStatus);
			$tpl->assign('arrBottomMetaInfo', $this->objCom->GetPageCMSDetails(BOTTOM_META));
			$tpl->draw('fundraiserDetail/v1');
		}
		
		private function ShowTemplateV2() {
			$loginStatus = 1;
			if(!$this->objUT1->checkLogin(getSession('Users')))
				$loginStatus = 0;
			
			$Color_SCHEME_CSS = $this->objFund->F_FundraiserDetail["Details"]["Camp_StylingDetails"];
			$Color_SCHEME_CSS_Array = unserialize($Color_SCHEME_CSS);
			$Color_SCHEME_CSS = keyDecrypt($Color_SCHEME_CSS_Array["Color_SCHEME_CSS"]);
			
			$this->load_model('Ut1_Reporting', 'objut1report');
			$this->load_model('NpoList', 'objNplist');
			$this->objNplist->N_EIN = $this->objFund->F_FundraiserDetail['Details']['Camp_NPO_EIN'];
			$this->objNplist->F_Camp_ID = $this->objFund->F_Camp_ID;
			//if($this->objFund->F_FundraiserDetail['Details']['Camp_NPO_EIN'] != '')   new Jan 2016 decision - under no circumstances is NPO logo to replace uploaded FR logo
				//$LogoImage = $this->objNplist->GetLogoImage();
			//else
			$LogoImage = $this->objFund->F_FundraiserDetail['Details']['Camp_ThumbImage_Full_Path'];
			
			$this->Surferid = 0;
			$UType1 = '';
			$UType2 = '';
			if(isset($this->SF_LoggedInDetail['UserType1']['user_id'])) 
				$UType1 = keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
				
			if(isset($this->SF_LoggedInDetail['UserType2']['user_id'])) 
				$UType2 = keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				
			$UType1 = ($UType1 != '') ? $UType1 : 0;
			$UType2 = ($UType2 != '') ? $UType2 : 0;
			if($UType1 != 0) {
				$this->F_UserType = 1;
				$this->Surferid = $UType1;
			} else {
				$this->F_UserType = 2;
				$this->Surferid = $UType2;
			}	
				
			$fundraiserDetails = $this->objFund->F_FundraiserDetail;				
			$this->objut1report->Condition = " AND PDD_PD_ID=" . keyDecrypt($this->F_FundId);
			
			$TotalDonor = $this->objut1report->GetDonationDetails(array("count('PDD.PDD_PD_ID') as TotalDonor"));
			$total_donor = 0;
			if(count($TotalDonor) > 0) {
				if($TotalDonor[0]['TotalDonor'] != '')
					$total_donor = $TotalDonor[0]['TotalDonor'];
			}
			
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('v2_fundraiser_detail');
			$npoName = $fundraiserDetails['Details']['NPO_Name'];
			$arrMetaInfo["text_fundraiser_benefits"] = strtr($arrMetaInfo["text_fundraiser_benefits"],array('{{npo_name}}'=>$npoName));
			$arrMetaInfo["text_special_thanks_greater_7"] = strtr($arrMetaInfo["text_special_thanks_greater_7"], array('{{total_donor}}'=>$npoName));
			$campTogglesRay = explode(',',$fundraiserDetails['Details']['Camp_CustomToggles']);
			
			//dump($fundraiserDetails);
			$tpl = new View;
			$tpl->assign("Color_SCHEME_CSS", $Color_SCHEME_CSS);
			$tpl->assign('arrBottomInfo', $this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$tpl->assign('MenuArray', $this->objCom->getTopNavigationArray(LANG_ID));
			$tpl->assign($arrMetaInfo);
			$tpl->assign("campTogglesRay", $campTogglesRay);
			$tpl->assign("QRCODEURL", $this->F_URL);
			$tpl->assign("Status", $this->F_Status);
			$this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal'] = number_format($this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal']);
			$tpl->assign("CommentList", $this->F_CommentList);
			$tpl->assign('TotalRecords', $this->objF->FC_TotalRecord);
			$tpl->assign('PageNumber', '1');
			$tpl->assign('FundraiseID', $this->objFund->F_Camp_ID);
			$tpl->assign('F_FundraiserDetail', $fundraiserDetails);
			$tpl->assign('TotalDonor', $total_donor);
			$tpl->assign('LogoImage', $LogoImage);
			$tpl->assign('EncFundID', $this->F_FundId);
			$tpl->assign('FriendlyUrl', $this->F_FriendlyUrl);
			$tpl->assign('UserType', $this->F_UserType);
			$tpl->assign('SurferID',$this->Surferid);			
			$tpl->assign("loginStatus", $loginStatus);
			$tpl->assign('arrBottomMetaInfo', $this->objCom->GetPageCMSDetails(BOTTOM_META));
			$tpl->draw('fundraiserDetail/v2');
		}
		
		public function FundraiserCommentPost()
		{			 
			$CampIDencryptd		= request('post','campId',0);
			$this->objF->F_Camp_ID=keyDecrypt($CampIDencryptd);
			$this->objF->F_Comment=request('post','comment',0);
			$this->objF->F_Camp_RUID =keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
			$this->objF->F_UserName =$this->SF_LoggedInDetail['UserType1']['user_fullname'];
			$this->objF->ProcessFundraiseComment();
			
			 /*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->objF->F_Camp_RUID;
			$userName	= $this->objF->F_UserName;
			$sMessage = "Error in fundraiser comment addition";
			$lMessage = "Error in fundraiser comment addition";
			if($this->objF->P_status)
			{
				$sMessage = "Fundraiser comment added";
				$lMessage = "Fundraiser comment added";	
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objF));	
				$this->objF->updateProcessLog($DataArray);	
				/*-----------------------------*/
			if($this->objF->P_status=='1'){				
				redirect(URL.'fundraiser/'.$CampIDencryptd.'#comt');
			}
			else{
				redirect(URL.'fundraiser/'.$CampIDencryptd.'#comr');
			}
		}
		
		public function FundraiserComment() {
			$DataArr = file_get_contents('php://input');
			parse_str($DataArr);
			$this->objF->F_Camp_ID = keyDecrypt($campId);
			$this->objF->F_Comment = $comment;
			$this->objF->F_Camp_RUID = keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);
			$this->objF->F_UserName = $this->SF_LoggedInDetail['UserType1']['user_fullname'];
			
			$this->objF->ProcessFundraiseComment();
			
			 /*----update process log------*/
			$userType = 'UT1';					
			$userID = $this->objF->F_Camp_RUID;
			$userName = $this->objF->F_UserName;
			$sMessage = "Error in fundraiser comment addition";
			$lMessage = "Error in fundraiser comment addition";
			if($this->objF->P_status) {
				$sMessage = "Fundraiser comment added";
				$lMessage = "Fundraiser comment added";	
			}
			
			$DataArray = array(	
				"UType"		=>$userType,
				"UID"		=>$userID,
				"UName"		=>$userName,
				"RecordId"	=>$userID,
				"SMessage"	=>$sMessage,
				"LMessage"	=>$lMessage,
				"Date"		=>getDateTime(),
				"Controller"=>get_class()."-".__FUNCTION__,
				"Model"		=>get_class($this->objF));
				
			$this->objF->updateProcessLog($DataArray);	
				/*-----------------------------*/
			echo $this->objF->P_status;
			exit;
		}
		

		
	}
?>