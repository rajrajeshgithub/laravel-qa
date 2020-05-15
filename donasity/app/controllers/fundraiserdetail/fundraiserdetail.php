<?php
	class fundraiserdetail_Controller extends Controller
	{
		public $SF_LoggedInDetail, $F_FundId, $F_URL, $F_Status, $F_CommentList, $F_UserType, $F_FriendlyUrl;

		public function __construct()
		{
			$this->load_model("Common", "objCom");
			$this->load_model('UserType1', 'objUT1');
			$this->load_model('UserType2', 'objUT2');
			$this->load_model('FundraisersDetail', 'objFund');
			$this->load_model('Fundraisers', 'objF');
			$this->load_model('FundraisersList', 'objFundList');	
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
					$this->objFund->F_WhereCondition = " AND Camp_Status=15 AND Camp_Deleted!='1' AND Camp_StartDate<='" . getDateTime(0, 'Y-m-d') . "' AND Camp_EndDate>='" . getDateTime(0, 'Y-m-d') . "'";
				 } else {					
					
					$UType1 = 0;
					$UType2 = 0;
					if(isset($this->SF_LoggedInDetail['UserType1']['user_id'])){
						$UType1=keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);	
						$this->F_UserType = 1;
						$this->objFund->F_Camp_RUID = $UType1;				
					}
					if(isset($this->SF_LoggedInDetail['UserType2']['user_id'])){
						$UType2=keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
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

		private function ShowTemplateV2() 
		{
			// identify user and login status
			$loginStatus = 1;
			if(!$this->objUT1->checkLogin(getSession('Users')))
				$loginStatus = 0;
			
			$this->Surferid=0;
			$UType1=0;
			$UType2=0;			
			if(isset($this->SF_LoggedInDetail['UserType1']['user_id'])){
				$UType1=keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']);	
				$this->F_UserType=1;
				$this->Surferid=$UType1;		
			}
			if(isset($this->SF_LoggedInDetail['UserType2']['user_id'])){
				$UType2=keyDecrypt($this->SF_LoggedInDetail['UserType2']['user_id']);
				$this->F_UserType=2;
				$this->Surferid=$UType2;						
			}	
			$this->load_model('Ut1_Reporting', 'objut1report');// only use getdonationdetails from UT1, so userType is Generic in this model's function 
			$this->load_model('NpoList', 'objNplist');
			
			// work with fundraiser details
			$fundraiserDetails = $this->objFund->F_FundraiserDetail;			
			$this->objNplist->N_EIN = $this->objFund->F_FundraiserDetail['Details']['Camp_NPO_EIN'];
			$this->objNplist->F_Camp_ID = $this->objFund->F_Camp_ID;
			$LogoImage = $this->objFund->F_FundraiserDetail['Details']['Camp_ThumbImage_Full_Path']; // always show uploaded logo, per JAN2016 meeting			
			$npoName = $fundraiserDetails['Details']['NPO_Name'];
			$campTogglesRay='';				
			if(isset($fundraiserDetails['Details']['Camp_CustomToggles']) && $fundraiserDetails['Details']['Camp_CustomToggles']!=''){$campTogglesRay = explode(',',$fundraiserDetails['Details']['Camp_CustomToggles']);}		
			$this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal'] = number_format($this->objFund->F_FundraiserDetail['Details']['Camp_DonationGoal']);
			
			// V2 Team pagination, if necessary. This is only used for starting panel, a different function is used to update panel
			$Page_totalRecords=1;
			$LastPage 		=1;
			$pageSelected	=1;
			$PagingList		=1;
			$PageSelected	=1;
			$startRecord	=1;
			$endRecord		=1;
			$totalrecord	=1;
			if(isset($this->objFund->F_FundraiserDetail['Details']['Camp_TeamUserType']))
			{
				if($this->objFund->F_FundraiserDetail['Details']['Camp_TeamUserType']=='C')
				{
					$campcodex=$this->objFund->F_FundraiserDetail['Details']['Camp_Code'];
					$this->objut1report->Condition = " AND PDD.PDD_CampCode='".$campcodex."'";
					$TotalDonors = $this->objut1report->GetDonorCount();
				
					$campTeamPageLimit=20;
					if(isset($this->objFund->F_FundraiserDetail['Details']['Camp_CustomToggles']['pgt']) && $this->objFund->F_FundraiserDetail['Details']['Camp_CustomToggles']['pgt']!='')
						$campTeamPageLimit=$this->objFund->F_FundraiserDetail['Details']['Camp_CustomToggles']['pgt'];
					
					$this->filterInput['teamcode']=$this->objFund->F_FundraiserDetail['Details']['Camp_Code'];
					$DataArray = array('C.Camp_ID','C.Camp_Title','C.camp_thumbImage','C.Camp_DescriptionHTML','C.Camp_ShortDescription','Camp_TeamUserType','CC.NPOCat_DisplayName_'._DBLANG_.'','CC.NPOCat_URLFriendlyName','C.Camp_CP_City','C.Camp_CP_State','C.Camp_CP_Country','C.Camp_CP_ZipCode','C.Camp_PaymentMode','C.Camp_NPO_EIN');
					$this->objFundList->PageSelected=1;
					$this->objFundList->PageLimit=$campTeamPageLimit;
					$this->objFundList->GetNPOList($DataArray, $this->filterInput);
					$Page_totalRecords = ($this->objFundList->recordsCount - 1);
					$PagingArr = constructPaging(1, $Page_totalRecords,$this->objFundList->PageLimit,3,9);

					$LastPage 		=ceil($Page_totalRecords / $this->objFundList->PageLimit);
					$pageSelected	=$pageSelected;
					$PagingList		=$PagingArr['Pages'];
					$PageSelected	=$PagingArr['PageSel'];
					$startRecord	=$PagingArr['StartPoint'];
					$endRecord		=$PagingArr['EndPoint'];
					$totalrecord	=$Page_totalRecords;
			
				}
				elseif($this->objFund->F_FundraiserDetail['Details']['Camp_TeamUserType']=='T')
				{
					$this->objut1report->Condition = " AND PDD.PDD_CampID=" . keyDecrypt($this->F_FundId);
					$TotalDonors = $this->objut1report->GetDonorCount();
				}
				else{					
					$this->objut1report->Condition = " AND PDD.PDD_CampID=" . keyDecrypt($this->F_FundId);
					$TotalDonors = $this->objut1report->GetDonorCount();
				}
			}
			else{
				$this->objut1report->Condition = " AND PDD.PDD_CampID=" . keyDecrypt($this->F_FundId);
				$TotalDonors = $this->objut1report->GetDonorCount();				
			}
			
			// V2 styling
			$Color_SCHEME_CSS = $this->objFund->F_FundraiserDetail["Details"]["Camp_StylingDetails"];
			$Color_SCHEME_CSS_Array = unserialize($Color_SCHEME_CSS);
			$Color_SCHEME_CSS = keyDecrypt($Color_SCHEME_CSS_Array["Color_SCHEME_CSS"]);
			
			//CMS info
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('v2_fundraiser_detail');			

			//dump($arrMetaInfo);
			
			$tpl = new View;				
			$tpl->assign("pageSelected", $pageSelected);
			$tpl->assign("PagingList", $PagingList);
			$tpl->assign("PageSelected", $PageSelected);
			$tpl->assign("startRecord", $startRecord);
			$tpl->assign("endRecord", $endRecord);
			$tpl->assign("totalrecord", $totalrecord);
			$tpl->assign("lastPage", $LastPage);
			$tpl->assign("Color_SCHEME_CSS", $Color_SCHEME_CSS);
			$tpl->assign('arrBottomInfo', $this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$tpl->assign('MenuArray', $this->objCom->getTopNavigationArray(LANG_ID));
			$tpl->assign($arrMetaInfo);
			$tpl->assign("campTogglesRay", $campTogglesRay);
			$tpl->assign("benefitsNPO", $npoName);
			$tpl->assign("QRCODEURL", $this->F_URL);
			$tpl->assign("Status", $this->F_Status);			
			$tpl->assign("CommentList", $this->F_CommentList);
			$tpl->assign('TotalRecords', $this->objF->FC_TotalRecord);
			$tpl->assign('PageNumber', '1');
			$tpl->assign('FundraiseID', $this->objFund->F_Camp_ID);
			$tpl->assign('F_FundraiserDetail', $fundraiserDetails);
			$tpl->assign('TotalDonor', $TotalDonors);			
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
			
			// looks like creating a comment is limited to utype1, so no test needed here
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
			$this->objF->F_Camp_RUID = keyDecrypt($this->SF_LoggedInDetail['UserType1']['user_id']); // only utype 1 can create a comment
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
		
		public function pageChangeTeamPanel()
		{
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->PT_PageChosen		= $itmx;
			$this->objFund->PT_CurrentPageNum	= $pgnm;
			$this->objFund->PT_TotalTeams		= $ttem;
			$this->objFund->PT_TeamsPerPanel	= $perp;
			$this->objFund->PT_TeamsPerPanelMob	= $prpm;
			$this->objFund->PT_TeamCategoryName	= $tcat;
			$this->objFund->PT_TeamSearchTerm	= $tsch;
			$this->objFund->PT_TeamMobile		= $tmob;
			$this->objFund->PT_TeamCode			= keyDecrypt($tjin);
			
			$res=$this->objFund->GetTeamFundraisersPaginate();
			$returnData=$this->GetTeamFundraisersHTML($res,$tmob);	
			echo $returnData;exit;					
		}
		
		public function GetTeamFundraisersHTML($res,$tmob=1)
		{
			$html='';
			
			
			for($i=0;$i<count($res);$i++)
			{
				$dongoal=$res[$i]['Camp_DonationGoal'];
				$dongoalnum=number_format($dongoal);
				$campidencrypted=keyEncrypt($res[$i]['Camp_ID']);
				
				if($tmob==2){
					$html.='<div class="col-xs-6 p-5 pb-15 panelteamcontentm"><div class="text-center"><a href=\''.URL.'fundraiser/'.$campidencrypted.'/'.$res[$i]['Camp_UrlFriendlyName'].'\'><img src=\''.URL.'read_write/campaignimages/main/'.$res[$i]['camp_thumbImage'].'\' class="img-responsive" alt="'.$res[$i]['Camp_UrlFriendlyName'].'"></a></div><div class="bgd-w" style="height:100px;"><span class="p-5 f-14 db t-blue text-bold"><a href=\''.URL.'fundraiser/'.$campidencrypted.'/'.$res[$i]['Camp_UrlFriendlyName'].'\'>'.$res[$i]['Camp_ShortDescription'].'</a></span><div class="progress m-5"><div class="progress-bar" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100" style="width:'.$res[$i]['Donationpercentage'].';"></div></div><span class="p-5 f-12 text-bold t-blue pb-5 db">'.$res[$i]['Donationpercentage'].' raised of $'.$dongoalnum.'</span></div></div>';
				}
				else{
					$html.='<div class="teamdiv-fiveacross panelteamcontent"><div class="text-center"><a href=\''.URL.'fundraiser/'.$campidencrypted.'/'.$res[$i]['Camp_UrlFriendlyName'].'\'><img src=\''.URL.'read_write/campaignimages/main/'.$res[$i]['camp_thumbImage'].'\' class="img-responsive" alt="'.$res[$i]['Camp_UrlFriendlyName'].'"></a></div><div class="teamidentity-goalcontainer"><div class="f-11 db t-blue text-bold teamidentity-name"><a href=\''.URL.'fundraiser/'.$campidencrypted.'/'.$res[$i]['Camp_UrlFriendlyName'].'\'>'.$res[$i]['Camp_ShortDescription'].'</a></div><div class="progress m-5 teamdivprogress"><div class="progress-bar" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100" style="height:12px!important; width:'.$res[$i]['Donationpercentage'].';"></div></div><span class="pl-5 pt-1 f-12 text-bold t-blue pb-4 db">'.$res[$i]['Donationpercentage'].' raised of $'.$dongoalnum.'</span></div></div>';
				}				
			}
			if(count($res)<1){
				$html.='<div class="teamdiv-fiveacross panelteamcontent"><div class="text-center"></div><div class="teamidentity-goalcontainer"><div class="f-11 db t-blue text-bold teamidentity-name"></div><div class="progress m-5 teamdivprogress"></div></div></div>';				
			}
			return $html;			
			
		}
	}
?>