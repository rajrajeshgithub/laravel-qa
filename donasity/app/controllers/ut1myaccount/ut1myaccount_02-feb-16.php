<?php
/*
	Campaign Status Detail
	=========================
	Setup - Front end
	1-10
	6 - Complted Setup 
	
	1 to 6 reserved
	7 to 10 Available 
	-----------------------
	
	Admin Action	
	11-20
	15 - Accepted / Running
	
	11 to 15 reserved
	14, 16 to 20 Available 
	-----------------------
	User Action
	21-30
	21 - Stop by user
	22 to 30 Available
	
	-----------------------
	Team Fundraiser Action
	31-40
	31 - Team Join
	36 - Stop by Captain
	32 to 35 and 37 to 40 Available.
	-----------------------

*/
	class Ut1myaccount_Controller extends Controller
	{
		public $tpl,$LoginUserDetail;
		public $LoginUserId,$CurrentDate;
		public $Camp_Title,$ExportCSVFileName,$ExportRecurringCSVFileName,$ExportFundraiserCSV, $pStatus = 1, $sortfrom, $sortto,$captainFundraiserDetails;
		
		// cc var
		public $ccName, $cardNumber, $sqCode, $expMonth, $expYear, $emailAddress;
		
		function __construct()
		{
			$this->P_status = 1;
			$this->tpl	= new View;
			$this->load_model('UserType1','objutype1');
			$this->objutype1 = new UserType1_Model();
			$this->load_model('Fundraisers','objFund');
			$this->load_model('Ut1_Reporting','objut1report');
			$this->objut1report = new Ut1_Reporting_Model();
			$this->LoginUserDetail	= getSession('Users');
			//dump($this->LoginUserDetail['UserType1']['user_type']);
			$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$this->CurrentDate	= getDateTime();
			//$this->verify_user();
			$this->FC_PageLimit=3;
			$this->ExportFundraiserCSV = EXPORT_CSV_PATH . "ut1_fundraiser_" . $this->LoginUserId . ".csv";
			
			if(file_exists(EXPORT_CSV_PATH."ut1_donationlist_".$this->LoginUserId.".csv"))
				unlink(EXPORT_CSV_PATH."ut1_donationlist_".$this->LoginUserId.".csv");
				
			if(file_exists(EXPORT_CSV_PATH."ut1_recurring_transaction_list_".$this->LoginUserId.".csv"))	
				unlink(EXPORT_CSV_PATH."ut1_recurring_transaction_list_".$this->LoginUserId.".csv");
				
			if(file_exists($this->ExportFundraiserCSV))	
				unlink($this->ExportFundraiserCSV);
				
			$this->ExportCSVFileName = EXPORT_CSV_PATH."ut1_donationlist_".$this->LoginUserId.".csv";
			$this->ExportRecurringCSVFileName = EXPORT_CSV_PATH."ut1_recurring_transaction_list_".$this->LoginUserId.".csv";
			
			/*$this->load_model('UserType2', 'objutype2');
			$this->objutype2->logoutUT2();*/
		}
		
		public function verify_user()
		{	
			if(!$this->objutype1->checkLogin(getSession('Users')))
			{
				redirect(URL."ut1/login");
			}
		}
		
		public function index($type='dashboard')
		{
			//echo $type;exit;
			switch($type)
			{				
				case 'dashboard':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Dashboard();
					break;				
				case 'change-password-form':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->ChangePasswordForm();
					break;
				case 'change-password':
					$this->ChangePassword();
					break;	
				case 'manage-profile':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Edit();
					break;	
				case 'update':
					$this->Update();
					break;
				case 'donation-list':
					$this->donation_list();
					break;	
				case 'fundarisers-list':
					$this->getFundariserList();
					break;				
				default:
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Dashboard();
					break;	
			}	
		}	
		
		public function TeamFundraiserBasicDetail($FR_id)
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
			$this->load_model('Common', 'objCom');
			//$arrMetaInfo = $this->objCom->GetPageCMSDetails('fundraiser_detail');
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('TeamFundraiserBasicDetail');
			
			$DataArray = array('*', 'concat_ws(", ",Camp_Location_City,Camp_Location_State,Camp_Location_Country) as Camp_Location');
			$this->objFund->F_Camp_ID = keyDecrypt($FR_id);
			
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			
			if($FundraiserDetail[0]['Camp_TeamUserType'] == 'C')
				redirect(URL.'ut1myaccount/FundraiserBasicDetail/'.$FR_id);
			
			setSession("TeamFundariserID",$FR_id);
			$FundraiserDetail[0]['Camp_SocialMediaUrl'] = json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'], true);
			
			$CampaignCategoryList = $this->objFund->GetNPOCategoryList();
			
			$this->tpl->assign('arrBottomInfo', $this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('Camp_StylingTemplateName', $Camp_StylingTemplateName);			
			$this->tpl->assign('STRIPE_TEAM_FUNDARISER_CONNECT_URL',STRIPE_TEAM_FUNDARISER_CONNECT_URL);
			//$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail', $FundraiserDetail[0]);
			$this->tpl->assign('CategoryList', $CampaignCategoryList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('categoryname', 'NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo', $this->objCom->GetPageCMSDetails(BOTTOM_META));	
							
			$this->tpl->draw('ut1myaccount/teamfundraiserbasicdetail');	
		}
		
		public function updatestyletemplate()
		{
			$postdata = $_POST;
			$this->objFund->F_Camp_ID = keyDecrypt($postdata['Camp_ID']);
			$this->objFund->StyleTemplateName = $postdata['StyleTemplateName'];
			
			$pStatus = 0;
			if($this->objFund->UpdateCampaignTemplateName())
				$pStatus = 1;
			
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in update style template.";
			$lMessage = "Error in update style template of camp id = $this->objFund->F_Camp_ID";
			if($pStatus) {
				$sMessage = "Style template has updated sucessfully.";
				$lMessage = "Style template has updated with camp id = $this->objFund->F_Camp_ID";
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
			
			redirect(URL."ut1myaccount/FundraiserBasicDetail/".$postdata['Camp_ID']);
		}
		
		public function viewall()
		{
			$this->objut1report->Type =  request('get','type',0);
			$enID = request('get','id',0);
			$id = keyDecrypt(request('get','id',0));
			$this->objut1report->LoginUserID =  $id;//request('get','id',0);
			//$this->objut1report->LoggedUserID  = request('get','id',0);
			$this->objut1report->Keyword =  request('get','keyword',0);
			
			//get date range in month and year
			$this->objut1report->Month				= request('get','month',3);
			$this->objut1report->Year				= request('get','year',0);
			$this->objut1report->TaxExempted		= request('get','taxable',0);
			if(count($this->objut1report->Month)==0 && $this->objut1report->Year=='')
			{
				$this->objut1report->Month = date('m');
				$this->objut1report->Year  =  date('Y');
			}
			//end of code
				
			//sort parameters
				$sortfrom = request('get','sortfrom',0);
				if($sortfrom=='')
				{
					$sortfrom="ASC";
				}
				if(isset($sortfrom) && request('get','sortfrom',0)!='')
				{
					if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
					{
						$sortfrom="DESC";
					}
					else
					{
						$sortfrom="ASC";
					}
				}
				$sortto   = request('get','sortto',0);
				if($sortto=="CauseName" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_PIItemName $sortfrom";
					$sortto="CauseName";
				}
				else if($sortto=="Type" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Date" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_DateTime $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Amount" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_Cost $sortfrom";
					$sortto="Date";
				}
				else 
				{
					$this->objut1report->SortOrder =" PDD.PDD_DateTime DESC";
				}
			//end of code
			
			
			unsetSession("confirmnpodetail");			
			$this->objutype1->GetUserDetails();
			$NpoDetails	= $this->GetNPOProfileDetail();
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('u1donation_view_all');
			$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
		
			//donation list array
				
			$NPODetails=$this->objutype1->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			$this->objut1report->NPOEIN=$NPODetails["NPOEIN"];
				
			//$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','ND.NPO_EIN','CONCAT(ND.NPO_Street," ,",ND.NPO_City)NPOAddress'));
			$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			$montharray = explode(',',$this->objut1report->Month);
			$this->tpl->assign('Month',$montharray);
			$this->tpl->assign('Year',$this->objut1report->Year);
			$this->tpl->assign('UserDetail',$this->objutype1->userDetailsArray);
			$this->tpl->assign('UserName',$UserName);
			$this->tpl->assign('UserID',$enID);
			$this->tpl->assign("keyword",$this->objut1report->Keyword);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut1myaccount/viewall");
		}
		
		public function exportdonationlist()
		{
			$this->tpl=new View;
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_PIItemName','ND.NPO_EIN','PDD.PDD_TaxExempt','DATE_FORMAT(PDD.PDD_DateTime,"%m/%d/%Y")as PDD_DateTime','PDD.PDD_Cost','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
			
			if(count($DonationArray) == 0)
			{
				$messageParams=array("errCode"=>'E18000',"errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}			
			$this->CreateCsvFile();
			$fp=fopen($this->ExportCSVFileName, 'a+');
			$i=0;
			foreach($DonationArray as $val)
			{
				if($val['PDD_TaxExempt']==1)
					$val['PDD_TaxExempt'] = 'YES';
				else
					$val['PDD_TaxExempt'] = 'NO';
					
				fputcsv($fp,$val);		
				$i++;
			}
			$this->downloadfile();			
		}
		
		
		public function exportRecurringTransactionList()
		{
			$this->tpl=new View;
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$this->objut1report->PaymentType = "'FRP'";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_PIItemName','ND.NPO_EIN','PDD.PDD_TaxExempt','DATE_FORMAT(PDD.PDD_DateTime,"%m/%d/%Y")as PDD_DateTime','PDD.PDD_Cost','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
			
			if(count($DonationArray) == 0)
			{
				$messageParams=array("errCode"=>'E18000',"errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}			
			$this->CreateCsvFileRecurring();
			$fp=fopen($this->ExportRecurringCSVFileName, 'a+');
			
			$i=0;
			foreach($DonationArray as $val)
			{
				if($val['PDD_TaxExempt']==1)
					$val['PDD_TaxExempt'] = 'YES';
				else
					$val['PDD_TaxExempt'] = 'NO';
					
				fputcsv($fp,$val);		
				$i++;
			}			
			$this->downloadfileRecurring();			
		}
		
		private function CreateCsvFile()
		{
			$fp=fopen($this->ExportCSVFileName, 'w+');
			if($fp)
			{
				$HeaderArr	= array("Cause Name","EIN","Tax Exempt","Date of Donation ","Donation Amount","Donation By");
				$StringArray  =  implode(",",$HeaderArr)."\r\n";
				fwrite($fp,$StringArray);
			}
		}
		
		private function CreateCsvFileRecurring()
		{
			$fp=fopen($this->ExportRecurringCSVFileName, 'w+');			
			if($fp)
			{
				$HeaderArr	= array("Cause Name","EIN","Tax Exempt","Date of Donation ","Donation Amount","Donation By");
				$StringArray  =  implode(",",$HeaderArr)."\r\n";
				fwrite($fp,$StringArray);
			}	
		}
		
		public function downloadfile($title='donation_list')
		{
			$path=EXPORT_CSV_PATH;
			LoadLib("Download_file");			
			$dFile = new Download_file();
			$dFile->Downloadfile($path,"ut1_donationlist_".$this->LoginUserId.".csv",$title);
		}
		
		public function downloadfileRecurring($title='recurring_transaction_list')
		{
			$path=EXPORT_CSV_PATH;
			LoadLib("Download_file");			
			$dFile = new Download_file();
			$dFile->Downloadfile($path,"ut1_recurring_transaction_list_".$this->LoginUserId.".csv",$title);
		}
		
		public function printdonationlist()
		{
			$this->tpl=new View;
				
			$this->load_model("Common","objCommon");
			//get date range in month and year
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			$Username	= request('post','username',0);
			//end of code
				
			//donation list array
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('ut1_print_donation');
			$arrMetaInfo["text_top"]=strtr($arrMetaInfo["text_top"],array('{{name}}' =>$Username,'{{date}}' => date('m-d-Y')));
			//$NPODetails=$this->objutype1->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			//$this->objut1report->NPOEIN=$NPODetails["NPOEIN"];
		
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(ND.NPO_Street," ",ND.NPO_City," ",ND.NPO_State," ",ND.NPO_Zip)NPOAddress','ND.NPO_Name','ND.NPO_EIN'));
			$this->tpl->assign("DonationArray",$DonationArray);
			$this->tpl->assign("PrintedDate",date('m-d-Y'));
			$this->tpl->assign("Username",$Username);
			$this->tpl->assign($arrMetaInfo);
			//end of code
				
			$HTML=$this->tpl->draw('ut1myaccount/printdonation',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		// print list of fundraiser 
		public function printfundraiserdonationlist() {
			$this->load_model("Common", "objCommon");
			//get date range in month and year
			$this->objut1report->PddID	= request('post', 'PDD_ID', 3);
			$fundId	= keyDecrypt(request('post', 'fund_id', 0));
			$Username	= request('post','username',0);
			//end of code
			
			//fundraiser list array
			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('ut1_print_fundraiser');
			$arrMetaInfo["text_top"] = strtr($arrMetaInfo["text_top"], array('{{name}}' =>$Username,'{{date}}' => date('m-d-Y')));
			
			$DataArray = array('Camp_TeamUserType', 'Camp_Code');
			$this->objFund->F_Camp_ID = $fundId;
			$this->objut1report->Month = request('post', 'month', 3);
			$this->objut1report->Year = request('post', 'year', 0);
			$this->objut1report->Keyword = request('post', 'keyword', 0);
			
			if(count($this->objut1report->PddID) > 0) {
				$d_ids = implode(',', $this->objut1report->PddID);
				$this->objut1report->Condition .= " AND PDD.PDD_ID IN($d_ids) ";
			}
			
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			//get fundraiser details			
			$this->objut1report->SortOrder = " PDD.PDD_DateTime DESC ";
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut1report->Condition .= " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut1report->Condition .= " AND PDD.PDD_CampID=".$fundId;

			$FundraiserArray = $this->objut1report->GetDonationFundDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));				
			//dump($FundraiserArray);
			$this->tpl->assign("FundraiserArray", $FundraiserArray);
			$this->tpl->assign("PrintedDate", date('m-d-Y'));
			$this->tpl->assign("Username", $Username);
			$this->tpl->assign($arrMetaInfo);
			//end of code
				
			$HTML = $this->tpl->draw('ut1myaccount/printfundraiser', true);
			$DP_Obj = LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML = $HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		// export fundraser list
		public function exportfundraiserlist() {
			
			$fundId	= keyDecrypt(request('post', 'fund_id', 0));
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			
			$DataArray = array('Camp_TeamUserType', 'Camp_Code');
			$this->objFund->F_Camp_ID = $fundId;
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			//get fundraiser details
			$this->objut1report->Month = request('post', 'month', 3);
			$this->objut1report->Year = request('post', 'year', 0);
			$this->objut1report->Keyword = request('post', 'keyword', 0);
			$this->objut1report->SortOrder = " PDD.PDD_DateTime DESC ";
			
			if(count($this->objut1report->PddID) > 0) {
				$d_ids = implode(',', $this->objut1report->PddID);
				$this->objut1report->Condition .= " AND PDD.PDD_ID IN($d_ids) ";
			}
			
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut1report->Condition .= " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut1report->Condition .= " AND PDD.PDD_CampID=".$fundId;

			$fund_array = array('CONCAT(RU.RU_FistName," ",RU.RU_LastName) DonorName', 'PDD.PDD_TaxExempt', 'PDD.PDD_DateTime', 'PDD.PDD_Cost');
			/*array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName');*/
			
			$FundraiserArray = $this->objut1report->GetDonationFundDetails($fund_array);
			//dump($FundraiserArray);
			if(count($FundraiserArray) == 0) {
				$messageParams = array("errCode"=>'E18000',"errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}
			//dump($FundraiserArray);
			$fp = fopen($this->ExportFundraiserCSV, 'w+');
			if($fp) {
				$HeaderArr = array("Donor Name", "Tax Exempt", "Date", "Amount");
				$StringArray = implode(",", $HeaderArr) . "\r\n";
				fwrite($fp, $StringArray);
			}
			
			$fp = fopen($this->ExportFundraiserCSV, 'a+');
			$i = 0;
			foreach($FundraiserArray as $val) {
				if($val['PDD_TaxExempt'] == 1)
					$val['PDD_TaxExempt'] = 'YES';
				else
					$val['PDD_TaxExempt'] = 'NO';
					
				$val['PDD_DateTime'] = formatDate($val['PDD_DateTime'], 'm/d/Y');
				fputcsv($fp, $val);		
				$i++;
			}
			//$this->downloadfile();
			
			LoadLib("Download_file");			
			$dFile = new Download_file();
			$title = "ut1_fundraiser_".$this->LoginUserId.".csv";
			$dFile->Downloadfile(EXPORT_CSV_PATH, "ut1_fundraiser_".$this->LoginUserId.".csv", $title);
		}
		
		public function printRecurringTransactionList()
		{
			$this->tpl=new View;
				
			$this->load_model("Common","objCommon");
			//get date range in month and year
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			$Username	= request('post','username',0);
			//end of code
				
			//donation list array
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('ut1_print_donation');
			$arrMetaInfo["text_top"]=strtr($arrMetaInfo["text_top"],array('{{name}}' =>$Username,'{{date}}' => date('m-d-Y')));
			//$NPODetails=$this->objutype1->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			//$this->objut1report->NPOEIN=$NPODetails["NPOEIN"];
		
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$this->objut1report->PaymentType = "'FRP'";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(ND.NPO_Street," ",ND.NPO_City," ",ND.NPO_State," ",ND.NPO_Zip)NPOAddress','ND.NPO_Name','ND.NPO_EIN'));
			$this->tpl->assign("DonationArray",$DonationArray);
			$this->tpl->assign("PrintedDate",date('m-d-Y'));
			$this->tpl->assign("Username",$Username);
			$this->tpl->assign($arrMetaInfo);
			//end of code
				
			$HTML=$this->tpl->draw('ut1myaccount/printTransaction',true);
			
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		private function GetNPOProfileDetail()
		{
			$DataArray	= array("NUR.NPOLogo","NUR.NPOConfirmationCode","NUR.NPODescription","N.NPO_Zip","N.NPO_Name","N.NPO_Street","NPO_City","NUR.Status as Stripe_Status","NUR.Stripe_ClientID as Stripe_ClientID","N.NPO_EIN");
			$Res	= $this->objutype1->GetNPOProfileDetail($DataArray,$this->LoginUserId);
			return $Res;
		}
		
		private function getFundariserList()
		{
			$this->load_model('UserType1','objutype1');
			$this->load_model('Common','objCommon');
				
			$this->LoginUserDetail	= getSession('Users');
		
			$this->load_model('Fundraisers','objFund');
			$this->objFund = new Fundraisers_Model();
			$Wherecondition = " AND Camp_RUID=".keyDecrypt($this->LoginUserDetail['UserType1']['user_id'])." AND Camp_Deleted!='1' AND Camp_TeamUserType NOT IN('T','C')";
			$fundraiserlist = $this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_TeamUserType','Camp_CreatedDate','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_StartDate','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage'),$Wherecondition);
			$fundraiserlist_array = $this->CreateFundraiserArray($fundraiserlist);
			
			
			$Wherecondition = " AND Camp_RUID=".keyDecrypt($this->LoginUserDetail['UserType1']['user_id'])." AND Camp_Deleted!='1' AND Camp_TeamUserType IN('T','C')";
			$fundraiserlistTeam=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_TeamUserType','Camp_CreatedDate','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_StartDate','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage'),$Wherecondition);
			//dump($fundraiserlistTeam);
			$fundraiserlist_arrayTeam = $this->CreateFundraiserArray($fundraiserlistTeam);
			//$fundraiserlist_array = array();
			
			//dump($fundraiserlist_arrayTeam);
			
			//dump($fundraiserlist);	
				
			$this->objutype1->GetUserDetails();			
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('ut1_dashboard');
			$UserName	= $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$Address1	= $this->objutype1->UserDetailsArray['RU_Address1']."  ";
			$Address1	.= ($this->objutype1->UserDetailsArray['RU_Address2'] != "")?$this->objutype1->UserDetailsArray['RU_Address2']." ":"";
			$Address2	= $this->objutype1->UserDetailsArray['RU_City'];
			$Address2	.= ($this->objutype1->UserDetailsArray['RU_ZipCode'] != "")?" - ".$this->objutype1->UserDetailsArray['RU_ZipCode']:"";
			$Image		= CheckImage(UT1PROFILE_MEDIUM_IMAGE_DIR,UT1PROFILE_MEDIUM_IMAGE_URL,NO_PERSON_IMAGE,$this->objutype1->UserDetailsArray['RU_ProfileImage']);
			$arrMetaInfo["userdetails"]=strtr($arrMetaInfo["userdetails"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,'{{Address2}}' => $Address2,
					'{{EmailID}}' => $this->objutype1->UserDetailsArray['RU_EmailID'],'{{Image}}'=>$Image));
			$this->tpl->assign($arrMetaInfo);
			/* ======== Meta Section End ========== */		
				
			$this->tpl->assign("UserDetail",$this->objutype1->UserDetailsArray);
			$this->tpl->assign('fundraiserlistCount',count($fundraiserlist_array));	
			$this->tpl->assign('fundraiserlistTeam',$fundraiserlist_arrayTeam);
			$this->tpl->assign("fundraiserlist",$fundraiserlist_array);
			$this->tpl->draw("ut1myaccount/fundraiserlist");
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
		
		private function CampTitle($levelid)
		{
			$this->objFund->LevelID = $levelid;
			$this->objFund->getTitle();
			$this->Camp_Title = $this->objFund->Title; 
		}
		
		private function Dashboard()
		{
			$this->load_model('Common','objCommon');
			$this->objutype1->GetUserDetails();
			
			/*$NpoDetails	= $this->GetNPOProfileDetail();
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$NPO_EIN         = $NpoDetails['NPO_EIN'];*/
			
			//sort parameters
			$sortfrom = request('get','sortfrom',0);
			if($sortfrom=='')
			{
				$sortfrom="ASC";
			}
			if(isset($sortfrom) && request('get','sortfrom',0)!='')
			{
				if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
				{
					$sortfrom="DESC";
				}
				else
				{
					$sortfrom="ASC";
				}
			}
			$sortto   = request('get','sortto',0);
			if($sortto=="CauseName" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_PIItemName $sortfrom";
				$sortto="CauseName";
			}
			else if($sortto=="Type" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Date" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_DateTime $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Amount" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_Cost $sortfrom";
				$sortto="Date";
			}
			else
			{
				$this->objut1report->SortOrder =" PDD.PDD_DateTime DESC";
			}
			//end of code
			
			
			
			//$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','ND.NPO_EIN','CONCAT(ND.NPO_Street," ,",ND.NPO_City)NPOAddress'));
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('ut1_dashboard');

			$UserName	= $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$UserID         = $this->objutype1->UserDetailsArray['RU_ID'];
			$Address1	= $this->objutype1->UserDetailsArray['RU_Address1']."  ";
			$Address1	.= ($this->objutype1->UserDetailsArray['RU_Address2'] != "")?$this->objutype1->UserDetailsArray['RU_Address2']." ":"";
			$Address2	= $this->objutype1->UserDetailsArray['RU_City'];
			$Address2	.= ($this->objutype1->UserDetailsArray['RU_ZipCode'] != "")?" - ".$this->objutype1->UserDetailsArray['RU_ZipCode']:"";
			$Image		= CheckImage(UT1PROFILE_MEDIUM_IMAGE_DIR,UT1PROFILE_MEDIUM_IMAGE_URL,NO_PERSON_IMAGE,$this->objutype1->UserDetailsArray['RU_ProfileImage']);
			$arrMetaInfo["userdetails"]=strtr($arrMetaInfo["userdetails"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,'{{Address2}}' => $Address2,
										'{{EmailID}}' => $this->objutype1->UserDetailsArray['RU_EmailID'],'{{Image}}'=>$Image));
			$this->tpl->assign($arrMetaInfo);
			/* ======== Meta Section End ========== */			
			$this->tpl->assign("UserDetail",$this->objutype1->UserDetailsArray);
			$this->tpl->assign('DonationArrayCount',count($DonationArray));
			$this->tpl->assign("DonationArray",$DonationArray);
			$this->tpl->assign('LoggedUserID',$UserID);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut1myaccount/dashboard");	
		}
		
		private function ChangePasswordForm()
		{
			$this->load_model('Common','objCommon');
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_changepassword'));			
			$this->tpl->draw("ut1myaccount/changepassword");
		}
		
		private function ChangePassword()
		{
			$this->objutype1->ExistPassword		= request('post','cpass',0);
			$this->objutype1->Password			= request('post','npass',0);
			$this->objutype1->ConfirmPassword	= request('post','rpass',0);
			$this->objutype1->UpdateDate		= getDateTime();
			$this->objutype1->ChangePasswordDB();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in change password process";
			$lMessage = "Error in change password process";
			if($this->objutype1->Pstatus)
			{
				$sMessage = "Password changed";
				$lMessage = "Password changed";
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype1));	
				$this->objutype1->updateProcessLog($DataArray);	
				/*-----------------------------*/
			if($this->objutype1->Pstatus)
			{
			    $this->SendMailForChangePassword();
				redirect(URL."ut1myaccount");
			}
			else
			{
				redirect(URL."ut1myaccount/change-password-form");	
			}
		}
	    
		private function SendMailForChangePassword()
		{
			$this->load_model('Email','objemail');
			$Keyword='UT1_MyaccountChangePassword';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$LoginUserDetail	= getSession('Users','UserType1');
			$UserName	=$LoginUserDetail['user_fullname'];
			$UserId	=keyDecrypt($LoginUserDetail['user_id']);
			$UserEmail=$LoginUserDetail['user_email'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('URL',URL);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
		}
		
		private function Edit()
		{
			$DataArray	= array('RU.RU_FistName','RU.RU_LastName','RU.RU_ProfileImage','RU.RU_CompanyName','RU.RU_Designation','RU.RU_Phone','RU.RU_Mobile','RU.RU_City','RU.RU_State',
								'RU.RU_ZipCode','RU.RU_Country','RU.RU_Address1','RU.RU_Address2','RU.RU_Gender','RU.RU_DOB','RU.RU_EmailID');
			$this->objutype1->GetUserDetails($DataArray);
			$this->GetCountryList();
			$this->getstates($this->objutype1->UserDetailsArray['RU_Country']);
			$this->load_model('Common','objCommon');
			$Gender	=  $GLOBALS['gender'];
			//$Gender	=  array('Male'=>'Male','Female'=>'Female');//$GLOBALS['gender'];			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_ManageProfile'));			
			$this->tpl->assign("gender",$Gender);
			$this->tpl->assign("UserDetails",$this->objutype1->UserDetailsArray);
			$this->tpl->draw("ut1myaccount/manageprofile");
		}
		
		private function Update()
		{
				$this->objutype1->FirstName					= request('post','fname',0);
				$this->objutype1->LastName					= request('post','lname',0);
				$this->objutype1->Address1					= request('post','Address1',0);
				$this->objutype1->Address2					= request('post','Address2',0);
				$this->objutype1->City						= request('post','city',0);
				$this->objutype1->Zip						= request('post','zipCode',0);
				$this->objutype1->Country					= request('post','country',0);
				$this->objutype1->State						= request('post','state',0);
				$this->objutype1->PhoneNumber				= request('post','phoneNumber',0);
				$this->objutype1->Mobile					= request('post','altPhoneNumber',0);
				$this->objutype1->CompanyName				= request('post','company',0);
				$this->objutype1->Designation				= request('post','designation',0);
				$this->objutype1->DOB						=  ChangeDateFormat(request('post','dob',0),"Y-m-d","m/d/Y");
				$this->objutype1->Gender					= request('post','gender',0);
				$this->objutype1->ExistProfileImg   		= request('post','existprofileimg',0);
				$this->objutype1->UpdateDate				= getDateTime();
				$this->objutype1->UploadProfileImage		= $_FILES['uploadProfile'];
				
			  	$this->objutype1->UpdateDB();
				/*----update process log------*/
				$userType 	= 'UT1';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
				$sMessage = "Error in update user details";
				$lMessage = "Error in update user details";
				if($this->objutype1->Pstatus)
				{
					$sMessage = "User details updated";
					$lMessage = "User details updated";
				}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype1));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
				redirect(URL."ut1myaccount");
		}
		
		private function GetCountryList()
		{
			$this->load_model('Common','objcommon');
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objcommon->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		public function getstates($CountryAbr)
		{
			$this->load_model("Common","objcommon");
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			$this->tpl->assign("StateList",$StateList);
			return $StateList;
		}

		public function getstateajax()
		{
			$CountryAbr	= request('post','CountryAB',0);
			$this->load_model("Common","objcommon");
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			echo json_encode($StateList);
			exit;
		}
		
		public function donation_list()
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))
				redirect(URL."ut1");
			
			//$_SERVER['QUERY_STRING'];
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_DonationReciptentType'));
		}
		
		public function FundraiserEdit($CampID='')
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
			$CampID = keyDecrypt($CampID);
			$this->load_model('Common','objCom');
			$this->objFund = new Fundraisers_Model();
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('ut1_edit_fundraiser');
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$DataArray=array('*','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage');
			$this->objFund->F_Camp_ID=$CampID;
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
					
			//get fundraiser details
			if(isset($CampID) && $CampID!='')
			{
				$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
				if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
					$this->objut1report->Condition = " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
				else
					$this->objut1report->Condition = " AND PDD.PDD_CampID=".$CampID;
					
					//echo $this->objut1report->Condition;exit;
				$DonationArray	= $this->objut1report->GetDonationFundDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','C.Camp_PaymentMode','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_EmailID'));
				//dump($DonationArray);
				$this->tpl->assign("DonationArray",$DonationArray);
			}
			
			//end of code
			
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			
			$this->tpl->draw("ut1myaccount/fundraiseredit");	
			
		}
		
		//----------------- Fundraiser comment section code start here-----------------
		public function FundraiserComment($FundraiserID)
		{
			$this->load_model('Common','objCom');
			$this->objFund = new Fundraisers_Model();

			if(trim($FundraiserID)!='') 
			{
				$FundraiserID = keyDecrypt($FundraiserID);
				$this->objFund->FC_FundraiserId=$FundraiserID;
				$this->objFund->FC_PageNo=1;
				$this->objFund->FC_PageLimit=$this->FC_PageLimit;
				$fundraiserComment=$this->objFund->GetFundraiserComment();
					/*----code for get funcraiser detail start code--*/
					$DataArray=array('*','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage');
					$this->objFund->F_Camp_ID=$FundraiserID;
					$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
					
					if(isset($FundraiserID) && $FundraiserID!='')
					{	
						$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
						$this->objut1report->Condition = " AND PDD.PDD_CampID=".$FundraiserID;
						$DonationArray	= $this->objut1report->GetDonationFundDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
						$this->tpl->assign("DonationArray",$DonationArray);
						
					}	
					$this->tpl->assign('UsedDetail',$UsedDetail);
					$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
					/*-----------end code------------*/
					$arrMetaInfo	= $this->objCom->GetPageCMSDetails('ut1_edit_fundraiser');
					
					$this->tpl->assign($arrMetaInfo);
					$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
					$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
					$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
					$this->tpl->assign("fcList",$fundraiserComment);
					$this->tpl->assign("PageNo",1);
					$this->tpl->assign("fcTotalCount",$this->objFund->FC_TotalRecord);
					$this->tpl->assign("FundraiserID",$FundraiserID);
					$this->tpl->draw("ut1myaccount/fundraisercomment");	
			}
			else
			{
				redirect(URL.'ut1myaccount');	
			}
		}
		
		//on myaccount page
		public function getFundraiserCommentByAjax()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$input=file_get_contents('php://input');
			parse_str($input);
			$this->objFund->FC_PageNo		= $pageNo;
			$this->objFund->FC_PageLimit	= $this->FC_PageLimit;
			$this->objFund->FC_TotalRecord	= $totalRecord;
			$this->objFund->FC_FundraiserId	= $fundraiserId;
			
			$res=$this->objFund->GetFundraiserComment();
			$returnData=$this->getFundraiserCommentHTML($res);	
			echo $returnData;exit;
		}
		
		public function getFundraiserCommentHTML($res)
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$html='';
			for($i=0;$i<count($res);$i++)
			{
				$html.='<div class="comment-box">';
				$html.='<input type="hidden" name="Camp_Cmt_ID" id="Camp_Cmt_ID" value="'.$res[$i]['Camp_Cmt_ID'].'">';
				$html.='<input type="hidden" name="Camp_Cmt_RUID" id="Camp_Cmt_RUID" value="'.$res[$i]['Camp_Cmt_RUID'].'">';
				$html.='<h3 class="comment-heading">'.$res[$i]['Camp_Cmt_UserName'].'</h3>';
				$html.='<div class="comment-content">'.$res[$i]['Camp_Cmt_Comment'].'</div>';
				$html.='<div class="oh">';
				$html.='<a href="javascript://" class="t-green mr-10 editComment" title="Click here to edit">Edit Comment</a>';
				$html.='<a href="javascript://" class="t-green mr-10 saveComment" title="Click here to save comment">Save Comment</a>';
				if($res[$i]['Camp_Cmt_ShowOnWebsite']==1)
				{
					$title='Click here to unapprove';
					$text='UnApprove';
				}
				else
				{
					$title='Click here to approve';
					$text='Approve';
				}
				$html.='<a href="javascript://" class="t-green mr-10 approveComment" title="'.$title.'" appstatus="'.$res[$i]['Camp_Cmt_ShowOnWebsite'].'">';
				$html.='<span class="approveText">'.$text.'</span>';
				$html.='</a>';
				$html.='<a href="javascript://" class="t-green removeComment" title="Click here to remove">Remove</a>';
				$html.='</div>';
				$html.='</div>';
			}
			return $html;
		}
		//-------------------------------------------------------
		// on comment page
		public function getFundraiserCommentBlockByAjax()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$input=file_get_contents('php://input');
			parse_str($input);
			$this->objFund->FC_PageNo		= $pageNo;
			$this->objFund->FC_PageLimit	= $this->FC_PageLimit;
			$this->objFund->FC_TotalRecord	= $totalRecord;
			$this->objFund->FC_FundraiserId	= $fundraiserId;
			$this->objFund->FC_approveStatus=1;
			
			$res=$this->objFund->GetFundraiserComment();
			$returnData=$this->getFundraiserCommentBlockHTML($res);	
			echo $returnData;exit;
		}
		
		public function getFundraiserCommentBlockHTML($res)
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$html='';
			for($i=0;$i<count($res);$i++)
			{
				$html.='<div class="comment-box">';
				$html.='<h3 class="comment-heading">'.$res[$i]['Camp_Cmt_UserName'].'</h3>';
				$html.='<div class="comment-content">'.$res[$i]['Camp_Cmt_Comment'].'</div>';
				$html.=' <div class="t-gray f-15"><i>Added on '. $res[$i]['Camp_Cmt_CreatedDate'].'</i></div>';
				$html.='</div>';
			}
			return $html;
		}
		
		public function updateFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;
			$this->objFund->FC_CommentContent	= $commentContent;
			
			$returnStatus=$this->objFund->processUpdateFundraiserComment();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in update fundraiser comment";
			$lMessage = "Error in update fundraiser comment";
			if($returnStatus)
			{
				$sMessage = "Fundraiser comment updated";
				$lMessage = "Fundraiser comment updated as".$commentContent." with comment id -".$commentId;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class().'-'.__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/		
			
		}

		public function deleteFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;

			$returnStatus=$this->objFund->processDeleteFundraiserComment();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in deletion fundraiser comment";
			$lMessage = "Error in deletion fundraiser comment";
			if($returnStatus)
			{
				$sMessage = "Fundraiser comment deleted";
				$lMessage = "Fundraiser comment deleted with comment id -".$commentId;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
			
			
		}
		
		public function approveFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;
			$this->objFund->FC_approveStatus	= $appStatus;

			$returnStatus=$this->objFund->processApproveFundraiserComment();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in update fundraiser comment status";
			$lMessage = "Error in update fundraiser comment status";
			if($returnStatus)
			{
				$sMessage = "Fundraiser comment status changed";
				$lMessage = "Fundraiser comment status changed as ".$appStatus." with comment id -".$commentId;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/			
		}

		//----------------- Fundraiser comment section code start here-----------------
		
		public function FundraiserBasicDetail($FR_id)
		{
			$this->load_model('Common','objCom');
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('fundraiser_detail');
			$DataArray=array('*','concat_ws(", ",Camp_Location_City,Camp_Location_State,Camp_Location_Country) as Camp_Location');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			if($FundraiserDetail[0]['Camp_TeamUserType']=='T')
				redirect(URL.'ut1myaccount/TeamFundraiserBasicDetail/'.$FR_id);
			$Camp_StylingTemplateName = $FundraiserDetail[0]['Camp_StylingTemplateName'];
			$getCampStyleTemplateColor = unserialize($FundraiserDetail[0]['Camp_StylingDetails']);
			//get color scheme values
				$this->objFund->Camp_Level_ID = $FundraiserDetail[0]['Camp_Level_ID'];
				$this->objFund->StyleTemplateName = $FundraiserDetail[0]['Camp_StylingTemplateName'];
				$this->objFund->getAllStyleColor();
			//end of code
			
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('Camp_StylingTemplateName',$Camp_StylingTemplateName);
			$this->tpl->assign('Camp_Level_ID',$this->objFund->Camp_Level_ID);
			
			//$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			//color scheme variable assign
				if(isset($this->objFund->StyleColorArray) && count($this->objFund->StyleColorArray) > 0)$this->tpl->assign('CampaignStyleColor',$this->objFund->StyleColorArray);
			//end of code
			
			if(isset($getCampStyleTemplateColor) && count($getCampStyleTemplateColor) > 0)$this->tpl->assign('CampStyleTemplateColor',$getCampStyleTemplateColor);
				
			$this->tpl->draw("ut1myaccount/fundraiserbasicdetail");	
		}
		
		public function UpdateTeamFundraiserBasicDetail()
		{
			$postdata = $_POST;
			//echo "<pre>";print_r($_POST);exit;
			$Cat_ID=request('post','category',0);
			$FR_id=request('post','FR_id',0);
			$this->objFund->Image = $_FILES['uploadPhoto'];			
			$this->objFund->F_Camp_ID= $FR_id = keyDecrypt($FR_id);
			$arrCampDetails = $this->objFund->GetFundraiserUserDetails(array("Camp_PaymentMode","Camp_Stripe_Status")," AND Camp_ID=".$FR_id);
			
			$ShortDescription=request('post','subTitle',0);
			$UrlFriendlyName = request('post','urlFriendlyName',0);
			$DonationGoal=request('post','donation',0);			
			$Status = (request('post','startFund',1))?15:NULL;
			$City=request('post','Camp_Location_City',0);
			$State=request('post','Camp_Location_State',0);
			$Country=request('post','Camp_Location_Country',0);
			$Logitude=request('post','Camp_Location_Logitude',0);
			$Latitude=request('post','Camp_Location_Latitude',0);
			$facebookURL=request('post','facebookURL',0);			
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);			
			$Camp_NonAccountNumber = request('post','UUID',0);
			$Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL));
			if($this->P_status == 1 && $FR_id=='')$this->SetStatus(0,'E13000');
			if($this->P_status == 1 && $Cat_ID=='')$this->SetStatus(0,'E13002');			
			if($this->P_status == 1 && $DonationGoal=='')$this->SetStatus(0,'E13004');
			if($this->P_status == 1 && $Status!=NULL && $arrCampDetails[0]['Camp_PaymentMode'] =='INDIVIDUAL-STRIPE-ACCOUNT' && $arrCampDetails[0]['Camp_Stripe_Status']!=1 && $Camp_NonAccountNumber=='')$this->SetStatus(0,'E13020');
			$DataArray=array("Camp_Cat_ID"=>$Cat_ID,
						"Camp_Status"=>$Status,
						"Camp_ShortDescription"=>$ShortDescription,
						"Camp_DonationGoal"=>$DonationGoal,
						"Camp_SocialMediaUrl"=>$Camp_SocialMediaUrl,						
						"Camp_Location_City"=>$City,
						"Camp_Location_State"=>$State,
						"Camp_Location_Country"=>$Country,
						"Camp_Location_Logitude"=>$Logitude,
						"Camp_Location_Latitude"=>$Latitude,						
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Locale"=>GetUserLocale()									
						);
			if($Status==NULL)
				unset($DataArray['Camp_Status']);
			$Condition = " Camp_ID =".$FR_id;
			if($this->P_status && $Status!=NULL)
			{
				if($arrCampDetails[0]['Camp_PaymentMode'] =='INDIVIDUAL-STRIPE-ACCOUNT' && $arrCampDetails[0]['Camp_Stripe_Status']!=1)
				{
					$arrNPODetails = $this->objFund->TeamFundraiserCheckUUIDForNPO($Camp_NonAccountNumber);

					if($arrNPODetails==false)
					{
						$this->SetStatus(0,'E13016');
					}
					else
					{
						$DataArray['Camp_NPO_EIN'] 				= $arrNPODetails['NPOEIN'];
						$DataArray['Camp_Stripe_Status'] 		= $arrNPODetails['Status'];
						$DataArray['Camp_Stripe_ConnectedID'] 	= $arrNPODetails['Stripe_ClientID'];
						$DataArray['Camp_TaxExempt'] 			= $arrNPODetails['NPO_DedCode'];
						$DataArray['Camp_PaymentMode']			= "NPO-STRIPE-ACCOUNT";
					}
				}
			}
			if($this->P_status)
					$this->objFund->UpdateTeamFundraiserBasicDetail($DataArray,$Condition);
					
			if($this->objFund->P_status)
				$this->objFund->ThumbImageBasicDetail();
			if($this->P_status)
			{
				/*-----update process log------*/
				$userType 	= 'UT1';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
				$sMessage = "Error in update team fundraiser details";
				$lMessage = "Error in update team fundraiser details";
				if($this->objFund->P_status)
				{
					$sMessage = "Team fundraiser details updated";
					$lMessage = "Team fundraiser details updated";
					
					if($Status==15)
					{
						$this->SendFundraiserMailAfterUpdateOwner($FR_id);
					$this->SendFundraiserMailAfterUpdateWebmaster($FR_id);
						$msg="<strong>Congratulation!</strong> Your team fundraiser has been successfully started. <a href='".URL."fundraiser-preview/".keyEncrypt($FR_id)."/".$UrlFriendlyName."' target='_new'>Click here to preview it.</a><br/>To further personalize your fundraiser. You can add Photos & Videos into it. <a href='".URL."ut1myaccount/FundraiserPhotoVideo/{$id}".keyEncrypt($FR_id)."'><strong>Manage Photos & Videos</strong></a>";
						$this->SetStatus(1,'000',$msg);
					}
					else
					{
						$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);	
					}
					
					
					
				}
				else
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
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
			}
			redirect(URL.'ut1myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($FR_id));
		}
		
		public function UpdateFundraiserBasicDetail()
		{
			$postdata = $_POST;
			//echo "<pre>";print_r($_POST);exit;			
			$this->objFund->F_Camp_Cat_ID=request('post','category',0);
			$FR_id=request('post','FR_id',0);
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->camp_bgImage = $_FILES['camp_bgImage'];
			$this->objFund->F_Camp_ID=$FR_id;
			$this->objFund->F_Camp_Code = request('post','Camp_Code',0);
			$this->objFund->F_Camp_Title=request('post','title',0);
			$this->objFund->F_Camp_UrlFriendlyName=RemoveSpecialChars($this->objFund->F_Camp_Title);
			$this->objFund->F_Camp_ShortDescription=request('post','subTitle',0);
			
			$this->objFund->F_Camp_DonationGoal=request('post','donation',0);
			
			$this->objFund->F_Camp_DateSpecified=request('post','radio1',0);//start date check
			$this->objFund->F_Camp_Duration_Days=request('post','FR_DurationDays',0);
			$this->objFund->F_Camp_SpecifiedDate=request('post','specifiedDate',0);//start date
			$this->objFund->F_Camp_IsPrivate=request('post','checkbox',0);
			$this->objFund->F_Camp_Location_City=request('post','Camp_Location_City',0);
			$this->objFund->F_Camp_Location_State=request('post','Camp_Location_State',0);
			$this->objFund->F_Camp_Location_Country=request('post','Camp_Location_Country',0);
			$this->objFund->F_Camp_Location_Logitude=request('post','Camp_Location_Logitude',0);
			$this->objFund->F_Camp_Location_Latitude=request('post','Camp_Location_Latitude',0);
			$this->objFund->F_Camp_DescriptionHTML= strip_tags(request('post','aboutFundraiser',0));
			$this->objFund->F_Camp_SalesForceID=request('post','Camp_SalesForceID',0);
			$this->objFund->Camp_StylingDetails=$this->getStylingDetails($postdata);
			
			$facebookURL=request('post','facebookURL',0);			
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);			
			$this->objFund->F_Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL));
			$this->objFund->ProcessFundraiserBasicDetail();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in update fundraiser details";
			$lMessage = "Error in update fundraiser details";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser details updated";
				$lMessage = "Fundraiser details updated";			
				
				$Status=$this->SendFundraiserMailAfterUpdateOwner($FR_id);
				if($Status)
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
				}
				else
				{
					$this->SetStatus(0,'E13017');
				}
				$Status1=$this->SendFundraiserMailAfterUpdateWebmaster($FR_id);
				if($Status1)
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
				}
				else
				{
					$this->SetStatus(0,'E13017');
				}
			}
			else
			{
				$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
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
			redirect(URL.'ut1myaccount/FundraiserBasicDetail/'.keyEncrypt($FR_id));
		}
				
		private function getStylingDetails($postdata)
		{
			$color_array='';
			if(isset($postdata['ColorScheme']) && $postdata['ColorScheme']!='')
			{	
				$Color_SCHEME_CSS=keyDecrypt($postdata["CSS_Template"]);
				$Color_SCHEME_CSS=strtr($Color_SCHEME_CSS,array('{{Color_Tag1}}' =>$postdata["ColorTag1"],'{{Color_Tag2}}' =>$postdata["ColorTag2"],'{{Color_Tag3}}' =>$postdata["ColorTag3"],'{{Color_Tag4}}' =>$postdata["ColorTag4"],'{{Color_Tag5}}' =>$postdata["ColorTag5"],'{{Color_Tag6}}' =>$postdata["ColorTag6"],'{{Color_Tag7}}' =>$postdata["ColorTag7"]));
				$Color_SCHEME_CSS=keyEncrypt($Color_SCHEME_CSS);
				$color_array = array('ColorScheme'=>$postdata['ColorScheme'],'ColorTag1'=>$postdata['ColorTag1'],'ColorTag2'=>$postdata['ColorTag2'],'ColorTag3'=>$postdata['ColorTag3'],'ColorTag4'=>$postdata['ColorTag4'],'ColorTag5'=>$postdata['ColorTag5'],'ColorTag6'=>$postdata['ColorTag6'],'ColorTag7'=>$postdata['ColorTag7'],'Color_SCHEME_CSS'=>$Color_SCHEME_CSS);
				$color_array = serialize($color_array );
			}
			return $color_array;
			
		}
		
		private function SendFundraiserMailAfterUpdateOwner($FR_id)
		{
				$DataArray=array('Camp_ID','Camp_CP_FirstName','Camp_CP_LastName','Camp_CP_Email');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			
				$uname=$FundraiserDetail[0]['Camp_CP_FirstName'].' '.$FundraiserDetail[0]['Camp_CP_LastName'];
				
				$this->load_model('Email','objemail');
				$Keyword='FundraiserUpdateOwner';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				$link=URL.'contact.html';
				$tpl->assign('link',$link);
				$tpl->assign('uname',$uname);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				
				$InsertDataArray=array('FromID'=>$FundraiserDetail['Camp_RUID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
				unset($Eobj);
				return $Status;
				
			
		}
		
		private function SendFundraiserMailAfterUpdateWebmaster($FR_id)
		{
			
				$this->load_model('UserType2','objUT2');
				$DataArray=array('*');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
				$FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$FundraiserDetail[0]['camp_thumbImage']);
				if($FundraiserDetail[0]['Camp_NPO_EIN'])
				{
					$DataArray=array('*');
					$where=" AND NPO_EIN = ".$FundraiserDetail[0]['Camp_NPO_EIN'];
					$npoDetail=$this->objUT2->GetNPODetail($DataArray,$where);
				}
				else
				{
					$npoDetail='';
				}
				
				$this->load_model('Email','objemail');
				$Keyword='FundraiserUpdateWebmaster';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				$tpl->assign('Link',$link);
				$tpl->assign('FundraiserDetail',$FundraiserDetail);
				$tpl->assign('npoDetail',$npoDetail);
				
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				
				$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_RUID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>'qualdev.test@gmail.com',
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
				unset($Eobj);
				return $Status;
		}
		
		public function FundraiserPhotoVideo($FR_id)
		{
			$this->load_model('Common','objCom');
			$DataArray=array('*');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];			
			$ImageList		= $this->objFund->CampaignImages();
			$VideoList		= $this->objFund->CampaignVideos();
			$numberPhoto 	= $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Photos']; 
			$numberVideo	= $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Videos'];
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('ut1_fundraiser_photo_vedio');
			$arrMetaInfo["text_upload_photos"]=strtr($arrMetaInfo["text_upload_photos"],array('{{number_photos}}' =>$numberPhoto));
			$arrMetaInfo["text_upload_videos"]=strtr($arrMetaInfo["text_upload_videos"],array('{{number_videos}}' =>$numberVideo));
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('UsedDetail',$UsedDetail);			
			$this->tpl->assign('ImageList',$ImageList);
			$this->tpl->assign('VideoList',$VideoList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw("ut1myaccount/fundraiserphotovideo");	
		}
		
		public function UploadImage()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->ProcessUploadImage();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in uploading fundraiser image";
			$lMessage = "Error in uploading fundraiser image";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser image added";
				$lMessage = "Fundraiser image added";
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
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));
		}
			
		public function UploadVideo()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->VideoCode = $_POST['videoEmbedCode'];
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Video = $_FILES['uploadVideo'];
			$this->objFund->ProcessUploadVideo();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in uploading fundraiser video";
			$lMessage = "Error in uploading fundraiser video";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser video added";
				$lMessage = "Fundraiser video added";
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
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));
			
		}
		
		public function DeleteImage($Image_id)
		{
			$this->objFund->Camp_Image_ID=keyDecrypt($Image_id);
			$FR_id=$this->objFund->ProcessDeleteImage();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in deletion fundraiser image";
			$lMessage = "Error in deletion fundraiser image";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser image deleted";
				$lMessage = "Fundraiser image deleted with id - ".$this->objFund->Camp_Image_ID;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->Camp_Image_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
			
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));
			
		}
		
		public function DeleteVideo($Video_id)
		{
			$this->objFund->Camp_Video_ID=keyDecrypt($Video_id);
			$FR_id=$this->objFund->ProcessDeleteVideo();
			if($this->objFund->P_status)
				$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
				$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['UserType1']['user_fullname'];
			$sMessage = "Error in deletion fundraiser video";
			$lMessage = "Error in deletion fundraiser video";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser video deleted";
				$lMessage = "Fundraiser video deleted with id - ".$this->objFund->Camp_Video_ID;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objFund->Camp_Video_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
			/*-----------------------------*/
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));			
		}
		
		public function RecurringTransactionList()
		{
			$this->objut1report->Type =  request('get','type',0);
			$enID = request('get','id',0);
			$id = keyDecrypt(request('get','id',0));
			$this->objut1report->LoginUserID =  $id;//request('get','id',0);
			//$this->objut1report->LoggedUserID  = request('get','id',0);
			$this->objut1report->Keyword =  request('get','keyword',0);
			
			//get date range in month and year
			$this->objut1report->Month				= request('get','month',3);
			$this->objut1report->Year				= request('get','year',0);
			$this->objut1report->TaxExempted		= request('get','taxable',0);
			if(count($this->objut1report->Month)==0 && $this->objut1report->Year=='')
			{
				$this->objut1report->Month = date('m');
				$this->objut1report->Year  =  date('Y');
			}
			//end of code
				
			//sort parameters
				$sortfrom = request('get','sortfrom',0);
				if($sortfrom=='')
				{
					$sortfrom="ASC";
				}
				if(isset($sortfrom) && request('get','sortfrom',0)!='')
				{
					if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
					{
						$sortfrom="DESC";
					}
					else
					{
						$sortfrom="ASC";
					}
				}
				$sortto   = request('get','sortto',0);
				if($sortto=="CauseName" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_PIItemName $sortfrom";
					$sortto="CauseName";
				}
				else if($sortto=="Type" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Date" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_DateTime $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Amount" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_Cost $sortfrom";
					$sortto="Date";
				}
				else 
				{
					$this->objut1report->SortOrder =" PDD.PDD_DateTime DESC";
				}
			//end of code
			
			
			unsetSession("confirmnpodetail");			
			$this->objutype1->GetUserDetails();
			
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('u1donation_view_all');
					
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
							
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$this->objut1report->PaymentType = "'FRP'";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','ND.NPO_EIN','CONCAT(ND.NPO_Street," ,",ND.NPO_City)NPOAddress'));
			$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			$montharray = explode(',',$this->objut1report->Month);
			$this->tpl->assign('Month',$montharray);
			$this->tpl->assign('Year',$this->objut1report->Year);
			$this->tpl->assign('UserDetail',$this->objutype1->userDetailsArray);
			$this->tpl->assign('UserName',$UserName);
			$this->tpl->assign('UserID',$enID);
			$this->tpl->assign("keyword",$this->objut1report->Keyword);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut1myaccount/recurringTransaction");
		}
		
		// list recurring profiles details
		public function RecurringProfiles() {
			if(!$this->objutype1->checkLogin(getSession('Users'))) 
				redirect(URL."ut1");
				
			$this->setfilterParameterLists();
			
			//sort parameters
			$sortfrom = request('get','sortfrom',0);
			if($sortfrom=='')
			{
				$sortfrom="ASC";
			}
			if(isset($sortfrom) && request('get','sortfrom',0)!='')
			{
				if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
				{
					$sortfrom="DESC";
				}
				else
				{
					$sortfrom="ASC";
				}
			}
			$sortto   = request('get','sortto',0);
			if($sortto=="CauseName" && $sortfrom!='')
			{
				$this->objut1report->SortOrder=" ORDER BY pdd.PDD_PIItemName $sortfrom";
				$sortto="CauseName";
			}
			else if($sortto=="Date" && $sortfrom!='')
			{
				$this->objut1report->SortOrder=" ORDER BY pdd.PDD_DateTime $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Amount" && $sortfrom!='')
			{
				$this->objut1report->SortOrder=" ORDER BY pdd.PDD_Cost $sortfrom";
				$sortto="Amount";
			}
			else
			{
				$this->objut1report->SortOrder =" ORDER BY rp.RP_StartDate DESC";
			}
			//end of code
			
			
			
			$RecurringProfiles = array();
			$RecurringProfiles = $this->objut1report->GetRecurringTrans();			
			//dump($RecurringProfiles);
			
			/* filter secxtion assinment */ 
			$this->tpl->assign("RecurringProfiles", $RecurringProfiles);
			$this->tpl->assign("status", $this->objut1report->rp_staus);
			$this->tpl->assign("cycle", $this->objut1report->rp_cycle);
			$this->tpl->assign("keyword", $this->objut1report->rp_keyword);
			
			/* end of code */ 
			
			/* sorting parameter section */ 
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			/* end of code */ 
			
			/* meta details  */ 
			
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('u1donation_RecurringProfiles');
			//$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('image', '');
			$this->tpl->assign('is_login', '');
			$this->tpl->assign($arrMetaInfo);
			/* end of code */ 
			$this->tpl->draw("ut1myaccount/RecurringProfiles");	
		}
		
		private function setfilterParameterLists() {
			$this->objut1report->rp_staus    = request('get', 'status', 0);
			$this->objut1report->rp_cycle    = request('get', 'cycle', 0);
			$this->objut1report->rp_keyword  = request('get', 'keyword', 0);
		}
		
		public function CancelRecurringTrans($customerID, $subscriptionID) {
			if(!$this->objutype1->checkLogin(getSession('Users'))) 
				redirect(URL."ut1");
				
			if($customerID == '' || $subscriptionID == '') {
				$this->SetStatus(0, 'ERT01');
				redirect(URL . 'ut1myaccount/RecurringProfiles');
			}
			
			$this->tpl->assign('customerID', $customerID);
			$this->tpl->assign('subscriptionID', $subscriptionID);
			
			/* meta details  */ 
			$NpoDetails = array();
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('u1donation_RecurringProfiles');
			//$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE, $NpoDetails['NPOLogo']);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			//$this->tpl->assign('image', $Image);
			$this->tpl->assign($arrMetaInfo);
			/* end of code */ 
			
			$this->tpl->draw("ut1myaccount/CancelRecurringTrans");	
		}
		
		// cancel recurring
		public function CancelRecurring() {
			if(!$this->objutype1->checkLogin(getSession('Users'))) 
				redirect(URL."ut1");
				
			$customerID = request('post', 'customerID', 0);
			$subscriptionID = request('post', 'subscriptionID', 0);
			$comment = request('post', 'comment', 0);
			
			if($customerID == '' || $subscriptionID == '') {
				$this->SetStatus(0, 'ERT01');
				redirect(URL . 'ut1myaccount/RecurringProfiles');
			}
			
			$customerID = keyDecrypt($customerID);
			$subscriptionID = keyDecrypt($subscriptionID);
			
			$this->load_model('Donation', 'objDonation');
			$DataArray = array('RP_ID','RP_RecurringCycle','RP_PDDID');
			$Condition = " RP_RecurringProfileID='" . $subscriptionID . "' AND RP_RecurringCustomerID='" . $customerID . "'";
			
			$arrRecurringDetails = $this->objDonation->GetRecurringDetails($DataArray, $Condition);
			
			if($arrRecurringDetails['RP_ID'] != '' && $arrRecurringDetails['RP_ID'] > 0) {
				$DataArray = array('PDD_PIItemName','PDD_Cost');
				$Condition = " AND PDD_ID=".$arrRecurringDetails['RP_PDDID'];
				$arrDonationDetials = $this->objDonation->GetDonationOrderDetails($DataArray,$Condition);
				$arrDonationDetials = $arrDonationDetials[0];
				$arrDonationDetials['RecurringCycle'] = $arrRecurringDetails['RP_RecurringCycle'];
				$this->load_model("Stripe", "objStripe");
				$this->objStripe->customerID = $customerID;
				$this->objStripe->subscriptionID = $subscriptionID;
				
				if($this->objStripe->cancelRecurring()) {
					if($this->objDonation->updateRecurringDetails(array('RP_Status'=>11, 'RP_RecurringStopComment'=>$comment), $arrRecurringDetails['RP_ID'])) {
						$this->EmailCancelRecurring($arrDonationDetials);
						$this->SetStatus(1, 'CRT01');
					}
					else
						$this->SetStatus(0, 'ERT02');
				} else  {
					$messageParams = array(
						"errCode"=>'000',
						"errMsg"=>$this->objStripe->stripe_response_err["message"],
						"errOriginDetails"=>basename(__FILE__),
						"errSeverity"=>1,
						"msgDisplay"=>1,
						"msgType"=>1);
						
					EnPException::setError($messageParams);
				}
				
				$sMessage = "Error in cancel recurring.";
				$lMessage = "Error in cancel recurring.";
				if($this->pStatus) {
					$sMessage = "Recurring has canceled successfully.";
					$lMessage = "Recurring has canceled successfully.";
				}
				$userType = $this->LoginUserDetail['UserType1']['user_type'];
				$DataArray = array(	
					"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
					"UID"			=> keyDecrypt($this->LoginUserDetail['UserType1']['user_id']),
					"UName"			=> $this->LoginUserDetail['UserType1']['user_fullname'],
					"RecordId"		=> $customerID,
					"SMessage"		=> $sMessage,
					"LMessage"		=> $lMessage,
					"Date"			=> getDateTime(),
					"Controller"	=> get_class()."-".__FUNCTION__,
					"Model"			=> get_class($this->objFund));
					
				$this->objFund->updateProcessLog($DataArray);
				
			} else 
				$this->SetStatus(0, 'ERT02');
			
			redirect(URL . 'ut1myaccount/RecurringProfiles');
		}
		
		//
		private function EmailCancelRecurring($arrDonationDetials) {
			$this->load_model('Email', 'objemail');
			$Keyword = 'cancelrecurringpayment';
			$where = " Where Keyword='" . $Keyword . "'";
			
			$DataArray = array(
				'TemplateID', 'TemplateName', 'EmailTo', 'EmailToCc', 'EmailToBcc', 'EmailFrom', 'Subject_'._DBLANG_);
				
			$GetTemplate = $this->objemail->GetTemplateDetail($DataArray, $where);
			//$LoginUserDetail = getSession('Users', 'UserType1');
			
			$tpl = new View;
			$tpl->assign('UserName', $this->LoginUserDetail['UserType1']['user_fullname']);
			$tpl->assign('DonationDetails',$arrDonationDetials);
			$HTML = $tpl->draw('email/' . $GetTemplate['TemplateName'],true);
			//echo $HTML; exit;
			$InsertDataArray = array(
				'FromID'=>$UserId,
				'CC'=>$GetTemplate['EmailToCc'],
				'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],
				'ToAddress'=>$this->LoginUserDetail['UserType1']['user_email'],
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],
				'Body'=>$HTML,'Status'=>'0',
				'SendMode'=>'1',
				'AddedOn'=>getDateTime());
				
			$id = $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj = LoadLib('BulkEmail');
			
			return $Eobj->sendEmail($id);
		}
		
		// change credit card details
		public function ChangeCreditCard($customerID, $subscriptionID) {
			if(!$this->objutype1->checkLogin(getSession('Users'))) 
				redirect(URL."ut1");
				
			$yearArr = range(date('Y'), date('Y') + 12);
			
			/* meta details  */ 
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('u1donation_RecurringProfiles');
			//$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('yearArr', $yearArr);
			$this->tpl->assign('customerID', $customerID);
			$this->tpl->assign('subscriptionID', $subscriptionID);
			$this->tpl->draw("ut1myaccount/ChangeCreditCard");	
		}
		
		public function UpdateCreditCard() {
			if(!$this->objutype1->checkLogin(getSession('Users'))) 
				redirect(URL."ut1");
				
			$customerID = request('post', 'customerID', 0);
			$subscriptionID = request('post', 'subscriptionID', 0);
			
			if($customerID == '' || $subscriptionID == '') {
				$this->SetStatus(0, 'ERT01');
				redirect(URL . 'ut1myaccount/RecurringProfiles');
			} 
			
			$this->ccName = request('post', 'ccName', 0);
			$this->cardNumber = request('post', 'cardNumber', 0);
			$this->sqCode = request('post', 'sqCode', 0);
			$this->expMonth = request('post', 'expMonth', 0);
			$this->expYear = request('post', 'expYear', 0);
			
			$this->ValidateCrediCard();
			
			if($this->pStatus === 1) {
				
				$customerID = keyDecrypt($customerID);
				$subscriptionID = keyDecrypt($subscriptionID);
			
				$this->load_model('Donation', 'objDonation');
				$DataArray = array('RP_ID');
				$Condition = " RP_RecurringProfileID='" . $subscriptionID . "' AND RP_RecurringCustomerID='" . $customerID . "'";
			
				$arrRecurringDetails = $this->objDonation->GetRecurringDetails($DataArray, $Condition);
				
				if($arrRecurringDetails['RP_ID'] != '' && $arrRecurringDetails['RP_ID'] > 0) {
					$this->load_model("Stripe", "objStripe");
					$this->objStripe->customerID = $customerID;
					$this->objStripe->subscriptionID = $subscriptionID;
					$this->objStripe->cc_exp_month = $this->expMonth;
					$this->objStripe->cc_exp_year = $this->expYear;
					$this->objStripe->cc_number = $this->cardNumber;
					$this->objStripe->cc_cvv = $this->sqCode;
					$this->objStripe->cc_name = $this->ccName;
					
					$pStatus = 0;
					if($this->objStripe->ChangeCreditCard()) {
						if($this->objStripe->stripe_response_filtered['status'] == 'active')
							$this->SetStatus(1, 'CRT02');
						else
							$this->SetStatus(1, 'CRT03');
							
						$pStatus = 1;
					} else {
						$messageParams = array(
							"errCode"=>'000',
							"errMsg"=>$this->objStripe->stripe_response_err["message"],
							"errOriginDetails"=>basename(__FILE__),
							"errSeverity"=>1,
							"msgDisplay"=>1,
							"msgType"=>1);
							
						EnPException::setError($messageParams);
					}
					
					$sMessage = "Error in update credit card details.";
					$lMessage = "Error in update credit card details.";
					if($pStatus) {
						$sMessage = "Credit card details has updated successfully.";
						$lMessage = "Credit card details has updated successfully.";
					}
					$userType = $this->LoginUserDetail['UserType1']['user_type'];
					$DataArray = array(	
						"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
						"UID"			=> keyDecrypt($this->LoginUserDetail['UserType1']['user_id']),
						"UName"			=> $this->LoginUserDetail['UserType1']['user_fullname'],
						"RecordId"		=> $customerID,
						"SMessage"		=> $sMessage,
						"LMessage"		=> $lMessage,
						"Date"			=> getDateTime(),
						"Controller"	=> get_class()."-".__FUNCTION__,
						"Model"			=> get_class($this->objFund));
						
					$this->objFund->updateProcessLog($DataArray);
					
				} else 
					$this->SetStatus(0, 'ECCC08');
			} //else
			
			redirect(URL . 'ut1myaccount/RecurringProfiles');
			
		}
		
		public function updateStatus($FR_id,$Status,$redirect=0)
		{
			$FR_id = keyDecrypt($FR_id);
			$Status = keyDecrypt($Status);			
			if($Status==15||$Status==21 || $Status==36)
			{
				$pStatus = 0;
				$sMessage = "Error in stop fundraiser.";
				$lMessage = "Error in stop fundraiser.";
				$userType = $this->LoginUserDetail['UserType1']['user_type'];
				
				$DataArray = array("Camp_Status"=>$Status,"Camp_LastUpdatedDate"=>getDateTime());
				if($this->objFund->SetFundraiserDetails($DataArray,$FR_id))
					$pStatus = 1;
					
				if($pStatus) {
					$sMessage = "Fundraiser has stoped successfully.";
					$lMessage = "Fundraiser has stoped successfully.";
				}
				$DataArray = array(	
					"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
					"UID"			=> keyDecrypt($this->LoginUserDetail['UserType1']['user_id']),
					"UName"			=> $this->LoginUserDetail['UserType1']['user_fullname'],
					"RecordId"		=> $FR_id,
					"SMessage"		=> $sMessage,
					"LMessage"		=> $lMessage,
					"Date"			=> getDateTime(),
					"Controller"	=> get_class()."-".__FUNCTION__,
					"Model"			=> get_class($this->objFund));
					
				$this->objFund->updateProcessLog($DataArray);
					
				if($redirect)
					redirect(URL.'ut1myaccount/manageTeamFundraisers/'.keyEncrypt($FR_id));
				else				
					redirect(URL.'ut1myaccount/FundraiserEdit/'.keyEncrypt($FR_id));
			}
			
		}
		
		public function stopFundraiserMultiple()
		{
			$cap_fund_id = request('post','cap_fund_id',1);
			$arrCamp_ids = request('post','Camp_ID',2);	
			if(is_array($arrCamp_ids) && count($arrCamp_ids))
			{
				//dump($arrCamp_ids);
				$processed =0;
				$userType = $this->LoginUserDetail['UserType1']['user_type'];
				foreach($arrCamp_ids as $key => $FR_id)
				{
					$pStatus = 0;
					$sMessage = "Error in stop multiple fundraiser.";
					$lMessage = "Error in stop multiple fundraiser.";
					$DataArray  = array("Camp_Status"=>36,"Camp_LastUpdatedDate"=>getDateTime());
					if($this->objFund->SetFundraiserDetails($DataArray,$FR_id." AND Camp_Status=15")) {
						$processed += 1; 
						$pStatus = 1;
					}
					
					if($pStatus) {
						$sMessage = "Multiple fundraiser has stoped successfully.";
						$lMessage = "Multiple fundraiser has stoped successfully.";
					}
					$DataArray = array(	
						"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
						"UID"			=> keyDecrypt($this->LoginUserDetail['UserType1']['user_id']),
						"UName"			=> $this->LoginUserDetail['UserType1']['user_fullname'],
						"RecordId"		=> $FR_id,
						"SMessage"		=> $sMessage,
						"LMessage"		=> $lMessage,
						"Date"			=> getDateTime(),
						"Controller"	=> get_class()."-".__FUNCTION__,
						"Model"			=> get_class($this->objFund));
						
					$this->objFund->updateProcessLog($DataArray);
				}
				if($processed>0)
					$this->SetStatus(1, 'C80003');
				
			}
			redirect(URL.'ut1myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
		}
		
		// validate credit card details
		private function ValidateCrediCard() {
			if(trim($this->ccName) == '' && $this->pStatus == 1) {
				$this->SetStatus(0, 'ECCC01');
				$this->pStatus = 0;
			}
				
			if(trim($this->cardNumber) == '' && $this->pStatus == 1) {
				$this->SetStatus(0, 'ECCC02');
				$this->pStatus = 0;
			}
			
			if(trim($this->sqCode) == '' && $this->pStatus == 1) {
				$this->SetStatus(0, 'ECCC03');
				$this->pStatus = 0;
			}
				
			if(trim($this->expMonth) == '' && $this->pStatus == 1) {
				$this->SetStatus(0, 'ECCC04');
				$this->pStatus = 0;
			}
			
			if(trim($this->expYear) == '' && $this->pStatus == 1) {
				$this->SetStatus(0, 'ECCC05');
				$this->pStatus = 0;
			}				
			
		}
		
		// view all donation list of a fundraiser
		public function viewallfundraiser($CampID='') 
		{
			$fund_id = $CampID;
			if(!$this->objutype1->checkLogin(getSession('Users')))
				redirect(URL . "ut1/npo-login");
				
			if($CampID == '')
				redirect(URL . "ut1myaccount/fundarisers-list");
			
			$CampID = keyDecrypt($CampID);
			$this->objFund->F_Camp_ID = $CampID;
			
			$this->FundraiserCredential();
			
			unsetSession("confirmnpodetail");
					
			$this->objutype1->GetUserDetails();
			
			
			$this->load_model('Common', 'objCommon');
			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('ut1fundraiser_view_all');			
			$UserName = $this->objutype1->UserDetailsArray['RU_FistName'] . " " . $this->objutype1->UserDetailsArray['RU_LastName'];			
			$arrMetaInfo["pageheading"] = strtr($arrMetaInfo["pageheading"], array('{{npo_name}}'=>$NPO_Name));					
			$DataArray=array('Camp_TeamUserType','Camp_Code');
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			/*-----check Payment mode------*/
			if($FundraiserDetail[0]['Camp_PaymentMode']!='INDIVIDUAL-STRIPE-ACCOUNT')
				redirect(URL.'ut1myaccount/FundraiserEdit/'.keyEncrypt($CampID));
			/*------*/
			//get fundraiser details			
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut1report->Condition = " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut1report->Condition = " AND PDD.PDD_CampID=".$CampID;
				

			$DonationArray = $this->objut1report->GetDonationFundDetails(array('PDD.PDD_ID', 'PDD.PDD_Status_Notes', 'PDD.PDD_RUID','C.Camp_PaymentMode','PDD.PDD_PIItemName', 'PDD.PDD_PD_ID', 'PDD.PDD_DateTime', 'PDD.PDD_SubTotal', 'PDD.PDD_Cost', 'PDD.PDD_TaxExempt', 'PDD_PIItemType', 'PDD.PDD_DonationReciptentType', 'CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName', 'RU.RU_EmailID'));
			
			//dump($DonationArray);
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign("DonationArray", $DonationArray);
			
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$montharray = explode(',', $this->objut1report->Month);//dump($montharray);
			$this->tpl->assign('Month', $montharray);
			$this->tpl->assign('Year', $this->objut1report->Year);
			$this->tpl->assign('UserDetail', $this->objutype1->userDetailsArray);
			$this->tpl->assign('UserName', $UserName);
			$this->tpl->assign('UserID', keyEncrypt($this->objutype1->userDetailsArray['RU_ID']));			
			$this->tpl->assign("keyword", $this->objut1report->Keyword);
			$this->tpl->assign("sortfrom", $this->sortfrom);
			$this->tpl->assign("sortto", $this->sortto);
			$this->tpl->assign("fund_id", $fund_id);
			$this->tpl->draw("ut1myaccount/viewallfundraiser");	
		}
		
		// get credential for get all donation list of a fundraiser
		private function FundraiserCredential() {
			
			$this->objut1report->Type = request('get', 'type', 0);
			$id = keyDecrypt(request('get', 'id', 0));
			$this->objut1report->LoggedUserID = request('get', 'id', 0);	
			$this->objut1report->Keyword = request('get', 'keyword', 0);
			
			//sorting
			$this->objut1report->SortTO =  request('get', 'sortto', 0);
			$this->objut1report->SortFrom =  request('get', 'sortfrom', 0);
			if($this->objut1report->SortTO == '' && $this->objut1report->SortFrom == '') {
				$this->objut1report->SortTO = '';
				$this->objut1report->SortFrom = 'PDD.PDD_DateTime DESC';
			}
			
			$this->objut1report->Month = request('get', 'month', 3);
			$this->objut1report->Year = request('get', 'year', 0);
			$this->objut1report->TaxExempted = request('get', 'taxable', 0);
			if(count($this->objut1report->Month) == 0 && $this->objut1report->Year == '') {
				$this->objut1report->Month = date('m');
				$this->objut1report->Year  =  date('Y');
			}
			
			//sort parameters
			$this->sortfrom = request('get', 'sortform', 0);
			//dump($this->sortfrom);
			if($this->sortfrom == '')
				$this->sortfrom = "ASC";
				
			if($this->sortfrom != '') {
				//if($this->sortfrom == "ASC" && request('get', 'sortfrom', 0) != '')
				if($this->sortfrom == "ASC")
					$this->sortfrom = "DESC";
				else
					$this->sortfrom = "ASC";
			}
			
			$this->sortto = request('get', 'sortto', 0);//dump($this->sortfrom);
			if($this->sortto == 'CauseName' && $this->sortfrom != '') {
				$this->objut1report->SortOrder = "RU.RU_FistName $this->sortfrom";
				$this->sortto = 'CauseName';
			} else if($this->sortto == 'Type' && $this->sortfrom != '') {
				$this->objut1report->SortOrder = "PDD.PDD_TaxExempt $this->sortfrom";
				$this->sortto = 'Date';
			} else if($this->sortto == 'Date' && $this->sortfrom != '') {
				$this->objut1report->SortOrder = "PDD.PDD_DateTime $this->sortfrom";
				$this->sortto = 'Date';
			} else if($this->sortto == 'Amount' && $this->sortfrom != '') {
				$this->objut1report->SortOrder = "PDD.PDD_Cost $this->sortfrom";
				$this->sortto = 'Date';
			} else
				$this->objut1report->SortOrder = " PDD.PDD_DateTime DESC";
				
			//dump($this->objut1report->SortOrder);
		}
		
		public function manageTeamFundraisers($CampID)
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
			
			$DecryptCampID = keyDecrypt($CampID);
			$this->objFund->F_Camp_ID = $DecryptCampID;
			$DataArray=array('Camp_ID','Camp_RUID','Camp_Title','Camp_Code','Camp_TeamUserType','Camp_Status','Camp_Level_ID','Camp_StylingTemplateName');
			$arrFundraiserDetails = $this->objFund->GetFundraiserDetails($DataArray);
			$arrFundraiserDetails = $arrFundraiserDetails[0];
			$DataArray=array('C.Camp_ID','C.Camp_RUID','Camp_Title','Camp_ShortDescription','C.Camp_Code','C.Camp_TeamUserType','C.Camp_Status','C.Camp_Level_ID','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID');
			$Condition = " AND C.Camp_Code='".$arrFundraiserDetails['Camp_Code']."' AND C.Camp_TeamUserType='T' AND C.Camp_Status>=11 and C.Camp_Status<=40 and  C.Camp_Deleted!='1'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			$this->load_model('Common','objCommon');
			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('ManageTeamFundraiser');	
			//dump($arrFundraiserDetails);			
			//dump($listTeamFundraisers);			
			$this->tpl = new View;
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('captainDetails',$arrFundraiserDetails);						
			$this->tpl->assign('listTeamFundraisers',$listTeamFundraisers);
			$this->tpl->assign('Camp_ID',$DecryptCampID);
			$this->tpl->draw('ut1myaccount/manageTeamFundraiser');
		}
		
		public function emailTeamFundraiser()
		{
			//dump($_REQUEST,0);
			$cap_fund_id = request('post','cap_fund_id',1);
			$arrCamp_ids = request('post','Camp_ID',2);
			$mailContent = request('post','messageContent',0);
			//dump($arrCamp_ids,0);
			$csCamp_ids = implode(",",$arrCamp_ids);

			$DataArray=array('Camp_Title','Camp_ShortDescription','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID','RU.RU_ID');
			$Condition = " AND C.Camp_ID IN(".$csCamp_ids.") AND C.Camp_TeamUserType='T'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			if(count($listTeamFundraisers)>0)
			{
				foreach($listTeamFundraisers as $key => $arrValue)
				{
					if($this->SendMailToTeamMembers($arrValue,$mailContent))
						$cntSuccess +=1; 		
				}	
				if($cntSuccess==count($listTeamFundraisers))
					$this->SetStatus(1,'C80001');
				else
					$this->SetStatus(0,'E25004');
			}
			redirect(URL.'ut1myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
		}
		
		private function SendMailToTeamMembers($teamDetails,$mailContent)
		{
			$this->load_model('Email','objemail');
			$Keyword='manage_team_fundraiser';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			
			$UserName= $teamDetails['UserName'];
			$UserId	= $teamDetails['RU_ID'];
			$UserEmail=$teamDetails['RU_EmailID'];
			$CampTitle = $teamDetails['Camp_Title'];
			$tpl=new View;
			$tpl->assign('UserName',ucwords($UserName));
			$tpl->assign('CampTitle',$CampTitle);
			$tpl->assign('MailContent',$mailContent);
			$tpl->assign('URL',URL);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			return $Status;
		}
		
		public function sendInvitation()
		{			
			//dump($_REQUEST);
			$cap_fund_id = request('post','cap_fund_id',1);
			$csEmail_ids = request('post','emailIDs',0);
			$mailContent = request('post','messageContent',0);
			//dump($arrCamp_ids,0);
			if($csEmail_ids=='')
				redirect(URL.'ut1myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
			$DataArray=array('Camp_ID','Camp_Title','Camp_Code','Camp_ShortDescription','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID','RU.RU_ID');
			$Condition = " AND C.Camp_ID = ".$cap_fund_id." AND C.Camp_TeamUserType='C'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);			
			if(count($listTeamFundraisers)>0)
			{
				$this->captainFundraiserDetails = $listTeamFundraisers[0];
				//dump($this->captainFundraiserDetails);
				$arrEmail_ids = explode(",",trim($csEmail_ids));
				//dump($arrEmail_ids);
				$invalidEmails='';
				foreach($arrEmail_ids as $key => $strEmailId)
				{
					$arrNameEmail_ids = explode("-",trim($strEmailId));					
					if(is_email(trim($arrNameEmail_ids[0])))
					{
						if($this->sentInvitationEmail(trim($strEmailId),$mailContent))
						{
							$cntSuccess += 1;
						}
						else
						{
							$invalidEmails .= $arrNameEmail_ids[0]."<br/>";			
						}						
					}
					else
					{
						$invalidEmails .= $arrNameEmail_ids[0]."<br/>";	
					}
				}
				
				if($cntSuccess==count($arrEmail_ids))
					$this->SetStatus(1,'C80001');
				else
					$this->SetStatus(0,'000',"Email not sent on below addresses<br/>".$invalidEmails);
			}
			redirect(URL.'ut1myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));	
		}
		
		private function sentInvitationEmail($receiverEmail,$mailContent)
		{
			$this->load_model('Email','objemail');
			$Keyword = 'invite_team_fundraiser';
			$where = " Where Keyword='" . $Keyword . "'";
			$DataArray = array('TemplateID', 'TemplateName', 'EmailTo', 'EmailToCc', 'EmailToBcc', 'EmailFrom', 'Subject_'._DBLANG_);
			
			$GetTemplate = $this->objemail->GetTemplateDetail($DataArray,$where);
			
			$LoginUserDetail = getSession('Users','UserType1');
			
			$arrNameEmail_ids = explode("-", trim($receiverEmail));	
			$UserName = $arrNameEmail_ids[1];
			$UserId	= keyDecrypt($LoginUserDetail['user_id']);
			$UserEmail = $LoginUserDetail['user_email'];					
			//dump($this->captainFundraiserDetails);
			$CampUrl = "<a href='".URL."fundraiser/".keyEncrypt($this->captainFundraiserDetails['Camp_ID'])."' target='details'>".$this->captainFundraiserDetails['Camp_Title']."</a>";
			$TeamFundreaiserUrl = "<a href='".URL."team_fundraiser/index/".keyEncrypt($this->captainFundraiserDetails['Camp_Code'])."' target='details'>Join Fundraiser</a>";
			
			$CampCode = $this->captainFundraiserDetails['Camp_Code'];
			$tpl = new view;
			$tpl->assign('UserName', ucwords($UserName));
			$tpl->assign('CampTitle', $CampTitle);
			$tpl->assign('CampUrl', $CampUrl);
			$tpl->assign('TeamFundreaiserUrl', $TeamFundreaiserUrl);
			$tpl->assign('CampCode', $CampCode);
			$tpl->assign('MailContent', $mailContent);
			$tpl->assign('URL', URL);
			$HTML = $tpl->draw('email/'.$GetTemplate['TemplateName'],true);	
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$arrNameEmail_ids[0],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id = $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			//var_dump($Status);exit;
			return $Status;
		}
		
		public function deleteTeamMember($camp_id,$c_camp_id)
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))
				redirect(URL."ut1");
			
			$dec_camp_id = keyDecrypt($camp_id);
			if($dec_camp_id == '')
				redirect(URL.'ut1myaccount');
			
			$DataArray = array("Camp_Deleted"=>'1');			
			if($this->objFund->SetFundraiserDetails($DataArray,$dec_camp_id))
				$this->SetStatus(1,'C80002');
			else
				$this->SetStatus(0,'E25005');
				
			/*----update process log------*/
			$sMessage = "Error in delete team fundraiser.";
			$lMessage = "Error in delete team fundraiser.";
			if($this->P_status) {
				$sMessage = "Team member deleted successfully.";
				$lMessage = "Team member deleted successfully.";
			}
			
			$userType = $this->LoginUserDetail['UserType1']['user_type'];
			
			$DataArray = array(	
				"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
				"UID"			=> keyDecrypt($this->LoginUserDetail['UserType1']['user_id']),
				"UName"			=> $this->LoginUserDetail['UserType1']['user_fullname'],
				"RecordId"		=> $dec_camp_id,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objFund));
				
			$this->objFund->updateProcessLog($DataArray);
			
			redirect(URL.'ut1myaccount/manageTeamFundraisers/'.$c_camp_id);	
		}
		
		public function stopFundraiserScheduler()
		{
			$DataArray=array('Camp_ID','Camp_EndDate','Camp_Status');
			$Condition = " AND Camp_Deleted!='1' AND Camp_EndDate<='".getDateTime(0,'Y-m-d')."'";
			$arrCampDetails = $this->objFund->GetFundraiserDetails($DataArray,$Condition);	
			//dump($arrCampDetails);
			if(is_array($arrCampDetails) && count($arrCampDetails))
			{
				foreach($arrCampDetails as $key => $arrValue)
				{
					$Pstatus=0;
					$DataArray  = array("Camp_Status"=>16,"Camp_LastUpdatedDate"=>getDateTime());
					if($this->objFund->SetFundraiserDetails($DataArray,$arrValue['Camp_ID']))
					{
						$Pstatus = 1;
					}
					/*----update process log------*/
					$userType 	= 'UT1 and UT2';
					$sMessage = "Error in stop fundraiser";
					$lMessage = "Error in stop fundraiser";
					if($Pstatus)
					{
						$sMessage = "Fundriaser stoped by system successfully";
						$lMessage = "Fundriaser stoped by system successfully";
					}
					$DataArray = array(	"UType"=>'',
										"UID"=>0,
										"UName"=>'',
										"RecordId"=>$arrValue['Camp_ID'],
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objFund));	
					$this->objFund->updateProcessLog($DataArray);
				}
			}			
		}
		
		
		private function SetStatus($Status, $Code,$msg='') {
			
			if($msg=='')
				$msg = "Custom Confirmation message";
			if($Status) {				
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"=>$msg,
					"msgLog"=>0,									
					"msgDisplay"=>1,
					"msgType"=>2);
				EnPException::setConfirmation($messageParams);
				$this->P_status=1;
			} else {
				$messageParams = array(
					"errCode"=>$Code,
					"errMsg"=>$msg,
					"errOriginDetails"=>basename(__FILE__),
					"errSeverity"=>1,
					"msgDisplay"=>1,
					"msgType"=>1);					
				EnPException::setError($messageParams);
				$this->P_status=0;
			}
		}
	}
?>