<?php
	class Ambassadorportal_Controller extends Controller
	{
		public $tpl, $LoginUserId, $LoginUserDetail, $ExportCSVFileName, $year, $month, $keyword, $donationArray;
		public $startDate, $endDate; 
		//ambassador
		public $organizationsName, $einNumber, $contactName, $billingAddress, $zipCode, $contactNumber, $emailAddress, $currently_registered, $linked_payment, $contact_time, $contact_time_str, $Pstatus;

		public function __construct() {
			$this->LoginUserDetail	= getSession('Users','UserType2');
			$this->LoginUserId		= keyDecrypt($this->LoginUserDetail['user_id']);						
			$this->tpl = new view;	
			$this->load_model('Widget','objWidget');
			$this->load_model('Common','objCommon');
			$this->load_model('UserType2','objutype2');
			$this->load_model('AmbassadorPortal','objAmbassador');	
			if(file_exists(EXPORT_CSV_PATH."ambassador_donationlist_".$this->LoginUserId.".csv"))		
				unlink(EXPORT_CSV_PATH."ambassador_donationlist_".$this->LoginUserId.".csv");
			$this->ExportCSVFileName	= EXPORT_CSV_PATH."ambassador_donationlist_".$this->LoginUserId.".csv";
			$this->year = '';
			$this->month = array();
			$this->keyword = '';
			$this->strChartData = '';//"['Date','Amount']";
			$this->contact_time = array();
			$this->contact_time_str = '';
			$this->Pstatus = 1;
			
		}	
		
		public function index($type="dashboard")
		{
			switch(strtolower($type))
			{
				case 'documents':
					$this->checkAbassadorLogin();
					$this->getDocuments();
				break;
				case 'add-widget':
					$this->checkAbassadorLogin();
					$this->addWidget();
					break;
				case 'view-widget':
					$this->checkAbassadorLogin();
					$this->viewdonatenowbutton();
					break;
				case 'disable-widget':
					$this->checkAbassadorLogin();
					$this->updateWidget();
					break;
				case 'request_form':
					$this->requestForm();
					break;
				case 'request-form':
					$this->requestForm();
					break;
				case 'donation-chart':
					$this->dashdonationchart();
					break;
				case 'ambassador_request':
					$this->ambassador_request();
					break;
				case 'ambassador-request':
					$this->ambassador_request();
					break;
				case 'confirm-request':
					$this->confirmRequest();
					break;
				default:
					$this->checkAbassadorLogin();
					$this->dashboard();	
					break;
			}	
		}
		
		private function checkAbassadorLogin()
		{
			if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
			//if($this->LoginUserDetail['user_ambassador']!=1) redirect(URL."ut2myaccount");	
		}
		
		public function dashboard()
		{
			//dump($_GET);
			$this->objWidget->userId = $this->LoginUserId;
			/*------------ get widget detail----------*/
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			//dump($widgetDetials);	
			$NpoDetails	= $this->GetNPOProfileDetail();	
			$this->endDate = getDateTime(0,'Y-m-d');
			$this->startDate = date('Y-m-d', strtotime("-30 days"));
			$m = getDateTime(0,'m');
			if(isset($_GET['month']) && $_GET['month']!='' && $_GET['month']>0)
			{
				$m = $_GET['month'];
				$this->startDate = getDateTime(0,'Y-'.$m.'-01');
				$this->endDate = getDateTime(0,'Y-'.$m.'-t');
			}
			//echo $this->endDate."==".$this->startDate;exit;
			$where = "WHERE 1=1 AND PDD.PDD_ItemCode IN ('NPOD3') AND PT.PT_PaymentStatus=1 AND PDD.PDD_NPOEIN=".$NpoDetails["NPO_EIN"]." AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='".$this->startDate."' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '".$this->endDate."')";
			$DataArray = array('DATE_FORMAT(PDD.PDD_DateTime,"%Y-%c-%d") as PDD_DateTime','PDD.PDD_SubTotal','SUM(PDD.PDD_Cost) as PDD_Cost');
			$this->objAmbassador->SortOrder = " PDD.PDD_DateTime DESC";
			$this->objAmbassador->GroupBY = " GROUP BY PDD_DateTime";
			$this->donationArray	= $this->objAmbassador->GetDonationDetails($DataArray,$where);
			//dump($this->donationArray);
			$this->CreateJSONChartData();	
			$this->tpl->assign('month',$m);		
			$this->tpl->assign('chartData',"[ ".$this->strChartData." ]");
			$this->tpl->assign('donationArray',$this->donationArray);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials',$widgetDetials);
			if($widgetDetials['W_Status']==1)$Status=0;else $Status=1;
			$this->tpl->assign('Status',$Status);
			$this->tpl->draw('ambassador/dashboardclosed');	
		}
		
		public function dashdonationchart()
		{
			//dump($_GET);
			$this->objWidget->userId = $this->LoginUserId;
			/*------------ get widget detail----------*/
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			//dump($widgetDetials);	
			$NpoDetails	= $this->GetNPOProfileDetail();	
			$this->endDate = getDateTime(0,'Y-m-d');
			$this->startDate = date('Y-m-d', strtotime("-30 days"));
			if(isset($_GET['month']) && $_GET['month']!='' && $_GET['month']>0)
			{
				$m = $_GET['month'];
				$this->startDate = getDateTime(0,'Y-'.$m.'-01');
				$this->endDate = getDateTime(0,'Y-'.$m.'-t');
			}
			//echo $this->endDate."==".$this->startDate;exit;
			$where = "WHERE 1=1 AND PDD.PDD_ItemCode IN ('NPOD3') AND PT.PT_PaymentStatus=1 AND PDD.PDD_NPOEIN=".$NpoDetails["NPO_EIN"]." AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='".$this->startDate."' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '".$this->endDate."')";
			$DataArray = array('DATE_FORMAT(PDD.PDD_DateTime,"%Y-%c-%d") as PDD_DateTime','PDD.PDD_SubTotal','SUM(PDD.PDD_Cost) as PDD_Cost');
			$this->objAmbassador->SortOrder = " PDD.PDD_DateTime DESC";
			$this->objAmbassador->GroupBY = " GROUP BY PDD_DateTime";
			$this->donationArray	= $this->objAmbassador->GetDonationDetails($DataArray,$where);
			//dump($this->donationArray);
			$this->CreateJSONChartData();	
			$this->tpl->assign('month',$m);		
			$this->tpl->assign('chartData',"[ ".$this->strChartData." ]");
			$this->tpl->assign('donationArray',$this->donationArray);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials',$widgetDetials);
			if($widgetDetials['W_Status']==1)$Status=0;else $Status=1;
			$this->tpl->assign('Status',$Status);
			$this->tpl->draw('ambassador/donationchart');	
		}
		
		public function viewdonatenowbutton()
		{
			//var_dump();
			$this->objWidget->userId = $this->LoginUserId;
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			$NpoDetails	= $this->GetNPOProfileDetail();	
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials',$widgetDetials);
			if($widgetDetials['W_Status']==1)$Status=0;else $Status=1;
			$this->tpl->assign('Status',$Status);
			$this->tpl->draw('ambassador/donatenowbutton');	
		}
		
		public function donatenowpage()
		{
			$this->objWidget->userId = $this->LoginUserId;
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status","W_AmbassModules");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			$NpoDetails	= $this->GetNPOProfileDetail();
			
			$widgetidx=$widgetDetials['W_ID'];
			$DataArray = array("DN_pgID");
			$Condition=" DN_widgetid='$widgetidx' LIMIT 1";
			$LastPStyleMadeID=$this->objWidget->getPayPageStyle($DataArray,$Condition);
			$currPStyleArray = array();

			if($LastPStyleMadeID['DN_pgID'] !=NULL && $LastPStyleMadeID['DN_pgID']  > 0){
				$myStyleid=$LastPStyleMadeID['DN_pgID'];
				$DataArray = array("DN_pgID","DN_color1","DN_color2","DN_color3","DN_color4","DN_color5","DN_color6","DN_text1","DN_stylename","DN_retrnurl","DN_widgetid");
				$Condition=" DN_pgID='$myStyleid'";
				$currPStyleArray=$this->objWidget->getPayPageStyle($DataArray,$Condition);
			}
			
			// detect if this page style module is active for this widget
			$DataArray = array("W.W_Status","W.W_AmbassModules");
			$this->objWidget->WidgetID=$widgetidx;
			$widgetModuleString=$this->objWidget->getWidgetDetailByID($DataArray);
			$typeModulDesired='styling';
			$pStyleActivated=$this->chekAmbWidgetModules($widgetModuleString,$typeModulDesired);

			$this->tpl->assign('PStyleActive',$pStyleActivated);
			$this->tpl->assign('CurrentPStyleArray',$currPStyleArray);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials',$widgetDetials);
			if($widgetDetials['W_Status']==1)$Status=0;else $Status=1;
			$this->tpl->assign('Status',$Status);
			$this->tpl->draw('ambassador/donatenowpage');
		}
		
		// ambassador request form
		private function requestForm() {
			$dataArray = array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','CONCAT(RU_FistName," ",RU_LastName) as Name','RU.RU_Mobile','RU.RU_CompanyName','RU_ZipCode','RU.RU_CompanyName','RU.RU_ProfileImage','NRU.NPOID','NRU.NPOEIN','RU.RU_City','RU.RU_State','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID','RU.RU_AllowAmbassador');
			$this->objutype2->GetUserDetails($dataArray);			
			//dump($this->objutype2->userDetailsArray);
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ambassador_request_form'));
			$this->tpl->assign('userDetail',$this->objutype2->userDetailsArray);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw('ambassador/requestForm');	
		}
		
		// process ambassador request form
		public function ambassador_request() {
			$this->organizationsName = request('post', 'organizationsName', 0);
			$this->einNumber = request('post', 'einNumber', 0);
			$this->contactName = request('post', 'contactName', 0);
			$this->billingAddress = request('post', 'billingAddress', 0);
			$this->zipCode = request('post', 'zipCode', 0);
			$this->contactNumber = request('post', 'contactNumber', 0);
			$this->emailAddress = request('post', 'emailAddress', 0);
			$this->currently_registered = request('post', 'currently_registered', 0);
			$this->linked_payment = request('post', 'linked_payment', 0);
			$this->contact_time = request('post', 'contact_time', 3);			
			
			$this->currently_registered = $this->currently_registered == '1' ? 'Yes' : 'No';
			$this->linked_payment = $this->linked_payment == '1' ? 'Yes' : 'No';
			
			foreach($this->contact_time as $ct) {
				$this->contact_time_str .= $ct . ', ';
			}
			$this->contact_time_str = rtrim($this->contact_time_str, ', ');
			
			$this->ValidateAmbassadorRequest();
			
			if($this->Pstatus == 1) {
				if($this->SendMailForRequest()) {
					redirect(URL . 'ambassadorportal/index/confirm-request');
				} else {
					$this->SetStatus(0, 'E17000');
					redirect(URL . 'ambassadorportal/index/request-form');
				}
			} else
				redirect($_SERVER['HTTP_REFERER']);
		}
		
		//ambassador request send email
		private function SendMailForRequest() {
			$this->load_model('Email', 'objemail');
			$Keyword = 'AmbassadorRequest';
			
			$where = " Where Keyword='" . $Keyword . "'";
			
			$DataArray = array(
				'TemplateID',
				'TemplateName',
				'EmailTo',
				'EmailToCc',
				'EmailToBcc',
				'EmailFrom',
				'Subject_'._DBLANG_);
				
			$GetTemplate = $this->objemail->GetTemplateDetail($DataArray, $where);
			$this->tpl->assign('organizationsName', $this->organizationsName);
			$this->tpl->assign('einNumber', $this->einNumber);
			$this->tpl->assign('contactName', $this->contactName);
			$this->tpl->assign('billingAddress', $this->billingAddress);
			$this->tpl->assign('zipCode', $this->zipCode);
			$this->tpl->assign('contactNumber', $this->contactNumber);
			$this->tpl->assign('emailAddress', $this->emailAddress);
			$this->tpl->assign('currently_registered', $this->currently_registered);
			$this->tpl->assign('linked_payment', $this->linked_payment);
			$this->tpl->assign('contact_time', $this->contact_time_str);
			
			$HTML = $this->tpl->draw('email/' . $GetTemplate['TemplateName'], true);
			
			$InsertDataArray = array(
				'FromID'		=>$this->emailAddress,
				'CC'			=>$GetTemplate['EmailToCc'],
				'BCC'			=>$GetTemplate['EmailToBcc'],
				'FromAddress'	=>$GetTemplate['EmailFrom'],
				'ToAddress'		=>$GetTemplate['EmailTo'],
				'Subject'		=>$GetTemplate['Subject_'._DBLANG_],
				'Body'			=>$HTML,
				'Status'		=>'0',
				'SendMode'		=>'1',
				'AddedOn'		=>getDateTime());
				
			$id = $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status = $Eobj->sendEmail($id);
			return $Status;
		}
		
		//ambassador request confirmation
		private function confirmRequest() {
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ambassador_request_confirmation'));
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			//$this->tpl->assign('contactName', $this->contactName);
			$this->tpl->draw('ambassador/requestConfirmation');
		}
		
		//ambassador request form validation
		private function ValidateAmbassadorRequest() {
			if(trim($this->organizationsName) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR01');
				$this->Pstatus = 0;
			}
			
			if(trim($this->einNumber) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR02');
				$this->Pstatus = 0;
			}
			
			if(trim($this->contactName) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR03');
				$this->Pstatus = 0;
			}
			
			if(trim($this->billingAddress) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR04');
				$this->Pstatus = 0;
			}
			
			if(trim($this->zipCode) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR05');
				$this->Pstatus = 0;
			}
			
			if(trim($this->contactNumber) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR06');
				$this->Pstatus = 0;
			}
			
			if(trim($this->emailAddress) == '' && $this->Pstatus == 1) {
				$this->SetStatus(0, 'EAR07');
				$this->Pstatus = 0;
			}
			
			if(trim($this->emailAddress) != '' && $this->Pstatus == 1 && !filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL)) {
				$this->SetStatus(0, 'EAR08');
				$this->Pstatus = 0;
			}
		}
		
		private function CreateJSONChartData()
		{
		
			if(count($this->donationArray>0))
			{				
				foreach($this->donationArray as $key => $value)
				{
					$displayDate = formatDate($value['PDD_DateTime'],'M-d');
					if($key==0)
						$this->strChartData .= "['$displayDate', ".$value['PDD_Cost']."]";
					else
						$this->strChartData .= ", ['$displayDate', ".$value['PDD_Cost']."]";	
				}	
				//echo $this->strChartData;exit;			
			}
		}
		
		private function addWidget()
		{
			$this->insertWidgetDetail();
			if($this->objWidget->Pstatus==1)
			{
				$this->SetStatus(1,'C20000');			
			}	
			else
			{
				$this->SetStatus(0,'E20000');
			}
			redirect(URL."ambassadorportal");
			
		}
		
		public function updateWidget($wID,$Status)
		{
			$this->widgetId 		= keyDecrypt($wID);
			$this->widgetStatus 	= keyDecrypt($Status);
			if(!is_numeric($this->widgetId) && !is_numeric($this->widgetStatus)) redirect(URL."ambassadorportal");			
			//echo $this->widgetId." -- ".$this->widgetStatus;exit;
			$this->LoginUserDetail	= getSession('Users','UserType2');
			$this->LoginUserId		= keyDecrypt($this->LoginUserDetail['user_id']);	
			$this->objWidget->W_ID 	= $this->widgetId;
			$this->objWidget->W_Status = $this->widgetStatus;
			$this->objWidget->updateWidget();
			if($this->objWidget->Pstatus==1)
				if($this->widgetStatus==1)
					$this->SetStatus(1,"C20002");
				else	
					$this->SetStatus(1,"C20001");
			else
				$this->SetStatus(0,"E20001");
			
			redirect(URL."ambassadorportal");
		}
		
		
		
		private function insertWidgetDetail()
		{
			$UniqueID  = GenerateUniqueAlphaNumeric();			
			$NpoDetails	= $this->GetNPOProfileDetail();	
			if(count($NpoDetails)<1)
			{
				redirect(URL."ambassadorportal");	
			}
			$this->objWidget->UniqueKey 	= $UniqueID;
			$this->objWidget->userId		= $this->LoginUserId;
			$this->objWidget->CharityID		= $NpoDetails['NPO_ID'];
			$this->objWidget->NPOEIN		= $NpoDetails['NPO_EIN'];
			$this->objWidget->CharityType	= 'NPOR';
			$this->objWidget->Status		= '1';
			$this->objWidget->CreatedDate	= getDateTime();
			$this->objWidget->UpdatedDate	= getDateTime();
			$this->objWidget->ValidSourceSite	= "";
			$this->objWidget->AddWidget_DB();	
			
			/*----update process log------*/
			$sMessage = "Error in add widget.";
			$lMessage = "Error in add widget(id=$this->objWidget->widgetId).";
			if($this->objWidget->Pstatus) {
				$sMessage = "Widget detail has been added successfully.";
				$lMessage = "Widget detail (id=$this->objWidget->widgetId) has been added successfully.";
			}
			
			$DataArray = array(	
				"UType"			=> 'UT2',
				"UID"			=> $this->LoginUserId,
				"UName"			=> $this->LoginUserDetail['user_fullname'],
				"RecordId"		=> $this->objWidget->widgetId,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objRegUser));
				
			$this->objRegUser->updateProcessLog($DataArray);	
			/*-----------------------------*/	
			//$UniqueKey,$userId,$CharityID,$CharityType,$NPOEIN,$Status,$CreatedDate,$UpdatedDate,$ValidSourceSite;
		}
				
		/*private function AmbassadorPortal() 
		{				
			$this->objWidget->userId = $this->LoginUserId;*/
			/*------------ get widget detail----------*/
			/*$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);*/
			//dump($widgetDetials);
			/*---------------get donations details ---------------*/
			/*$where = "WHERE 1=1 AND PDD.PDD_PIItemType IN ('NPOD3') AND PT.PT_PaymentStatus=1 and PDD.PDD_RUID=".$this->LoginUserId;
			$DataArray = array('PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType',
								'PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_EmailID');
			$DonationArray	= $this->objut2report->GetDonationDetails($DataArray,$where);*/
			/*---------------*/
			/*$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('documentList',$documentList);
			$this->tpl->assign('DonationArray',$DonationArray);
			$this->tpl->assign('widgetDetials',$widgetDetials);
			$this->tpl->draw("ut2myaccount/ambassadorportal");	
		}*/
		
		public function getDocuments() {
			$this->objWidget->userId = $this->LoginUserId;
			$this->objAmbassador->loginUserId = $this->LoginUserId;
			
			/*------------ get widget detail----------*/
			$DataArray = array("W_ID", "W_UniqueKey", "W_RUID", "W_CharityID", "W_CharityType", "W_NPOEIN", "W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			
			/*----------------- get document details-------------*/
			if(request('get', 'filter', 0) != '')
				$this->Filter();
				
			$DataArray = array('D.DocID', 'D.DocTitle', 'D.DocName', 'D.DocSorting', 'D.DocUserID', 'D.WebmasterComment', 'D.CreatedDate', 'D.DocRealName', 'D.Description');
			$documentList = $this->objAmbassador->getDocumentDetails($DataArray);
			
			/*---------------*/			
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials', $widgetDetials);
			$this->tpl->assign('documentList', $documentList);
			$year = $this->year == '' ? date('Y') : $this->year;
			$this->tpl->assign('year', $year);
			$this->tpl->assign('month', $this->month);
			$this->tpl->assign('keyword', $this->keyword);
			$this->tpl->draw('ambassador/documents');	
		}
		
		private function Filter() {
			$this->keyword = request('get', 'keyword', 0);
			$this->month = request('get', 'month', 3);
			$this->year = request('get', 'year', 0);
			$this->objAmbassador->Keyword	= $this->keyword;
			$this->objAmbassador->Month		= $this->month;
			$this->objAmbassador->Year		= $this->year;
		}
		
		public function viewall()
		{			
			//dump($_REQUEST);	
			//get date range in month and year				
			$this->objAmbassador->Type =  request('get', 'type', 0);	
			$id = keyDecrypt(request('get', 'id', 0));
			//$this->objut2report->LoggedUserID =  request('get','id',0);	
			$this->objAmbassador->LoggedUserID = request('get', 'id', 0);	
			$this->objAmbassador->Keyword = request('get', 'keyword', 0);	
			
			//sorting
			$this->objAmbassador->SortTO =  request('get', 'sortto', 0);	
			$this->objAmbassador->SortFrom =  request('get', 'sortfrom', 0);
			if($this->objAmbassador->SortTO == '' && $this->objAmbassador->SortFrom == '') {
				$this->objAmbassador->SortTO = '';
				$this->objAmbassador->SortFrom = 'PDD.PDD_DateTime DESC';
			}
			//end of code
			
			$this->objAmbassador->Month = request('get', 'month', 3);
			$this->objAmbassador->Year = request('get', 'year', 0);
			$this->objAmbassador->TaxExempted = request('get', 'taxable', 0);
			if(count($this->objAmbassador->Month)==0 && $this->objAmbassador->Year == '') {
				$this->objAmbassador->Month = date('m'); 	
				$this->objAmbassador->Year = date('Y');
			}
			//end of code			
			//sort parameters
			$sortfrom = request('get', 'sortfrom', 0);
			if($sortfrom == '') {
				$sortfrom="ASC";
			}
			if(isset($sortfrom) && request('get', 'sortfrom', 0) != '') {
				if($sortfrom == "ASC" && request('get', 'sortfrom', 0) != '')
					$sortfrom = "DESC";
				else
					$sortfrom = "ASC";
			}
			$sortto = request('get', 'sortto', 0);
			if($sortto == "DonorName" && $sortfrom != '') {
				$this->objAmbassador->SortOrder = "RU.RU_FistName $sortfrom";
				$sortto = "DonorName";
			} else if($sortto == "Type" && $sortfrom != '') {
				$this->objAmbassador->SortOrder = "PDD.PDD_TaxExempt $sortfrom";
				$sortto = "Date";
			} else if($sortto == "Date" && $sortfrom != '') {
				$this->objAmbassador->SortOrder = "PDD.PDD_DateTime $sortfrom";
				$sortto = "Date";
			} else if($sortto == "Amount" && $sortfrom != '') {
				$this->objAmbassador->SortOrder = "PDD.PDD_Cost $sortfrom";
				$sortto = "Date";
			} else 
				$this->objAmbassador->SortOrder = " PDD.PDD_DateTime DESC";
				
			//end of code			
			unsetSession("confirmnpodetail");			
			$this->objutype2->GetUserDetails();
			$NpoDetails		= $this->GetNPOProfileDetail();
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('ut2_donation_view_all');
			//$arrMetaInfo["pageheading"]=strtr($arrMetaInfo["pageheading"],array('{{UserName}}' => $this->userDetailsArray['user_fullname']));
			
								
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			//$this->tpl->assign($arrMetaInfo);
			//donation list array			
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);			
			//$this->objut2report->NPOEIN=$NPODetails["NPOEIN"];			
				//$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
				$where = "WHERE 1=1 AND PDD.PDD_ItemCode IN ('NPOD3') AND PT.PT_PaymentStatus=1 AND PDD.PDD_NPOEIN=".$NPODetails["NPOEIN"];
				$DataArray = array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID','PDD.PDD_DnbVersion');
				$DonationArray	= $this->objAmbassador->GetDonationDetails($DataArray,$where);
				$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			$montharray = explode(',',$this->objAmbassador->Month);
			$this->tpl->assign('Month',$montharray);
			$this->tpl->assign('Year',$this->objAmbassador->Year);
			$this->tpl->assign('UserDetail',$this->objAmbassador->userDetailsArray);
			$this->tpl->assign('UserName', isset($this->LoginUserDetail['user_fullname']) ? $this->LoginUserDetail['user_fullname'] : '');
			$this->tpl->assign('UserID',$this->objAmbassador->LoggedUserID);			
			$this->tpl->assign("keyword",$this->objAmbassador->Keyword);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ambassador/viewall");	
		}
		
		private function GetNPOProfileDetail()
		{
			$DataArray	= array("N.NPO_ID","N.NPO_EIN","NUR.NPOLogo","NUR.NPOConfirmationCode","NUR.NPODescription","N.NPO_Zip","N.NPO_Name","N.NPO_Street","NPO_City","NUR.Status as Stripe_Status","NUR.Stripe_ClientID as Stripe_ClientID");
			$Res	= $this->objutype2->GetNPOProfileDetail($DataArray,$this->LoginUserId);
			return $Res;
		}
		
		public function exportdonationlist()
		{		
			//echo "hello";exit;	
			$this->tpl=new View;
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);	
			$this->objAmbassador->PddID	= request('post','PDD_ID',3);
			$this->objAmbassador->SortOrder=" PDD.PDD_DateTime DESC ";					
			$this->objAmbassador->NPOEIN=$NPODetails["NPOEIN"];	
			$where = "WHERE 1=1 AND PDD.PDD_ItemCode IN ('NPOD3') AND PT.PT_PaymentStatus=1 AND PDD.PDD_NPOEIN=".$NPODetails["NPOEIN"];
			$DataArray = array('PDD.PDD_PIItemName','PDD.PDD_TaxExempt','DATE_FORMAT(PDD.PDD_DateTime,"%m/%d/%Y")as PDD_DateTime','PDD.PDD_Cost','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_FistName','RU.RU_LastName','RU.RU_Phone','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID','CONCAT(RU.RU_Address1," ",RU_Address2)DonorAddress','RU.RU_City','RU.RU_State','RU.RU_ZipCode');
			$DonationArray	= $this->objAmbassador->GetDonationDetails($DataArray,$where);
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
		
		private function CreateCsvFile()
		{
			$fp=fopen($this->ExportCSVFileName, 'w+');
			if($fp)
			{
				$HeaderArr	= array("Donation To","Tax Exempt","Date of Donation ","Donation Amount","Donor Full Name","Donor First Name","Donor Last Name","Phone","Email","Address","City","State Prov","Zip","Country");
				$StringArray  =  implode(",",$HeaderArr)."\r\n";
				fwrite($fp,$StringArray);
			}
		}
		
		public function downloadfile($title='donationlist')
		{
			$path=EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$filename="donationlist.csv";
			$dFile = new Download_file();
			$dFile->Downloadfile($path,"ambassador_donationlist_".$this->LoginUserId.".csv",$title);
		}
		
		public function printdonationlist()
		{
			$this->load_model("common","objCommon");
			$this->tpl=new View;
			
			//get date range in month and year
				$this->objAmbassador->PddID	= request('post','PDD_ID',3);
				$Username	= request('post','username',0);
			//end of code
			
			//donation list array				
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN","NPO_Name")," AND NUR.USERID=".$this->LoginUserId);
			$this->objAmbassador->NPOEIN=$NPODetails["NPOEIN"];
			$this->objAmbassador->NPO_Name = $NPODetails["NPO_Name"];	
			$this->objAmbassador->SortOrder=" PDD.PDD_DateTime DESC ";
			$npoName = $this->objAmbassador->NPO_Name;
			$where = "WHERE 1=1 AND PDD.PDD_ItemCode IN ('NPOD3') AND PT.PT_PaymentStatus=1 AND PDD.PDD_NPOEIN=".$NPODetails["NPOEIN"];
			$DataArray = array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID','CONCAT(RU.RU_Address1," ",RU.RU_Address2)NPOAddress');
			$DonationArray	= $this->objAmbassador->GetDonationDetails($DataArray,$where);
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('ut2_print_donation');
			$arrMetaInfo["header_donation_statement"]=strtr($arrMetaInfo["header_donation_statement"],array('{{npo_name}}' =>$npoName,'{{print_date}}' => date('m-d-Y')));
			
			$this->tpl->assign("DonationArray",$DonationArray);			
			$this->tpl->assign($arrMetaInfo);
			//end of code
			$this->tpl->assign("Username",$Username);
			$this->tpl->assign("NPO_Name",$npoName);
			$HTML=$this->tpl->draw('ambassador/printdonation',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		private function SetStatus($Status, $Code) {
			if($Status) {
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"=>"Custom Confirmation message",
					"msgLog"=>0,									
					"msgDisplay"=>1,
					"msgType"=>2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode"=>$Code,
					"errMsg"=>"Custom Confirmation message",
					"errOriginDetails"=>basename(__FILE__),
					"errSeverity"=>1,
					"msgDisplay"=>1,
					"msgType"=>1);
				EnPException::setError($messageParams);
			}
		}
		
		public function paypagestyles()
		{			
			$widgetIdNum	= request('post','widnum',0); 
			$loadedpstyle	= request('post','CurrentLoadedPStyle',0);			
			$colorchoice1	= request('post','FinlColorTag1',0);
			$colorchoice2	= request('post','FinlColorTag2',0);
			$colorchoice3	= request('post','FinlColorTag3',0);			
			$colorchoice4	= request('post','FinlColorTag4',0);
			$colorchoice5	= request('post','FinlColorTag5',0);
			$colorchoice6	= request('post','FinlColorTag6',0);
			$headertxt		= request('post','dntextx',0);
			$successurlpg	= request('post','tyurl',0);
			
			$colorchoice1	= str_replace('#','',$colorchoice1);
			$colorchoice2	= str_replace('#','',$colorchoice2);
			$colorchoice3	= str_replace('#','',$colorchoice3);			
			$colorchoice4	= str_replace('#','',$colorchoice4);
			$colorchoice5	= str_replace('#','',$colorchoice5);
			$colorchoice6	= str_replace('#','',$colorchoice6);			

			// clear default colors out, skip color 4
			if($colorchoice1=='ffffff' || $colorchoice1=='fff'){$colorchoice1='';}
			if($colorchoice2=='dddddd' || $colorchoice2=='ddd'){$colorchoice2='';}
			if($colorchoice3=='ffffff' || $colorchoice3=='fff'){$colorchoice3='';}
			if($colorchoice5=='abc345'){$colorchoice5=='';}
			if($colorchoice6=='dddddd' || $colorchoice6=='ddd'){$colorchoice6='';}

			$this->PStyleFormDataRay=array("wid"=>$widgetIdNum,"psid"=>$loadedpstyle,"cl1"=>$colorchoice1,"cl2"=>$colorchoice2,"cl3"=>$colorchoice3,"cl4"=>$colorchoice4,"cl5"=>$colorchoice5,"cl6"=>$colorchoice6,"tx1"=>$headertxt,"urx"=>$successurlpg);
			$this->objWidget->pStyleArray=$this->GenerateStyleArray($this->PStyleFormDataRay);	
			$CurrentStyleRowId=$this->objWidget->storWidgetPayPage($loadedpstyle);
			redirect(URL."ambassadorportal/donatenowpage");	
							
		}

		private function GenerateStyleArray($PStyleFormDataRay)
		{
			$temp=array (
				"DN_color1"=>$this->PStyleFormDataRay['cl1']
				,"DN_color2"=>$this->PStyleFormDataRay['cl2']
				,"DN_color3"=>$this->PStyleFormDataRay['cl3']
				,"DN_color4"=>$this->PStyleFormDataRay['cl4']
				,"DN_color5"=>$this->PStyleFormDataRay['cl5']
				,"DN_color6"=>$this->PStyleFormDataRay['cl6']
				,"DN_text1"=>$this->PStyleFormDataRay['tx1']
				,"DN_stylename"=>"My Saved Style"
				,"DN_retrnurl"=>$this->PStyleFormDataRay['urx']	
				,"DN_widgetid"=>$this->PStyleFormDataRay['wid']					
			);
			return $temp;
		}

		private function chekAmbWidgetModules($widgetModuleString,$typeModulDesired){
			// module 1 is the page styling turned on 
			$resultx='9';
			$numtosearchfor='';
			if($typeModulDesired=='styling'){
				$numtosearchfor='1';
			}
			if($widgetModuleString!='' && $widgetModuleString !='0' && $numtosearchfor!=''){				
				$moduleNumRay=explode(',',$widgetModuleString['W_AmbassModules']);
				if (in_array($numtosearchfor,$moduleNumRay)){
					$resultx=1;					
				}				
			}
			return $resultx;
		}		
	}

?>