<?php
	class Donation_Controller extends Controller {
		
		public $P_status, $reportsID, $filterParam, $Page_Selected, $currentDate, $fromDate, $toDate, $paymentProcessor, $transactionType, $ExportCSVFileName, $RecurringExportCSVFileName, $itemCode, $RemoteCheckExportFile;
		
		//export
		public $TotalRowProcessed = 0, $CurrentCsvPosition = 0;
		
		function __construct() {
			checkLogin(20);
			$this->load_model('Donation', 'objDonation');
			$this->objDonation = new Donation_Model();
			$this->P_status = 1;
			$this->reportsID = 0;
			$this->filterParam = array();			
			$this->fromDate = date('m/d/Y', strtotime('today - 30 days'));
			$this->toDate = formatDate(getDateTime(), 'm/d/Y');
			$this->currentDate = $this->toDate;
			$this->paymentProcessor = '';
			$this->transactionType = '';
			$this->ExportCSVFileName = EXPORT_CSV_PATH."dialydonation.csv";
			$this->RecurringExportCSVFileName = EXPORT_CSV_PATH."recurringprofilereport.csv";
			$this->RemoteCheckExportFile = EXPORT_CSV_PATH."remotely_echeck_donation_report.csv";
		}
		
		public function index($type='list', $reportsID=NULL) {
			//dump($type);
			$this->tpl = new view;			
			switch(strtolower($type)) {
				case 'daily-donation' :
					$this->Listing();
					$this->tpl->draw('donation/reports');
					break;
				case 'export-reports' :
					$this->ExportCSVFile();
					break;
				case 'recurring-profile' :
					$this->RecurringProfileReport();
					$this->tpl->draw('recurringpayment/reports');
					break;
				case 'recurring-export-reports' :
					$this->RecurringExportCSVFile();
					break;
				case 'summary-purchase-donation' :
					$this->DonationSummary();
					$this->tpl->draw('donation/SummaryPurchaseDonation');
					break;
				case 'remotely-created-check-report':
					$this->RemotelyCreatedCheck();
					break;
				case 'echeck-export-reports' :
					$this->RemoteCheckExport();
					break;
				default :
					$this->Listing();
					$this->tpl->draw('donation/reports');
					break;		
			}
		}
		
		private function DonationSummary()
		{
			EnPException :: writeProcessLog('Donation_Controller :: Donation Summary action to view donation summary');
			$this->fromDate = request("get","FromDate",0);
			$this->toDate 	= request("get","ToDate",0);
			$this->fromDate = formatDate($this->fromDate,"Y-m-d");
			$this->toDate	= formatDate($this->toDate,"Y-m-d");
			if($this->fromDate=='' || $this->toDate=='')
			{
				$date = new DateTime("-1 months");
				$this->fromDate = $date->format("Y-m-d");
				$this->toDate = getDateTime(0,'Y-m-d');
			}
			$DataArray = array('sum(PDD_Cost) as totalDonation','PDD_ItemCode');
			$Condition = " and PDD_PIItemType IN('CD','NPOD','CP') and PDD_Status=11 and DATE_FORMAT(PDD_DateTime,'%Y-%m-%d') >='".$this->fromDate."' and DATE_FORMAT(PDD_DateTime,'%Y-%m-%d')<='".$this->toDate."'";
			$GroupBy = " group by PDD_ItemCode";
			$arrDonationDetails = $this->objDonation->getDonationSummary($DataArray,$Condition,$GroupBy);
						
			$arrDonation['Registred NPO Donation'] = array("totalDonation"=>0,"PDD_ItemCode"=>"NPOD1");
			$arrDonation['Non Registered Donation'] = array("totalDonation"=>0,"PDD_ItemCode"=>"NPOD2");
			$arrDonation['Fundariser Donation'] = array("totalDonation"=>0,"PDD_ItemCode"=>"CD1");
			$arrDonation['Ambasador Donation'] = array("totalDonation"=>0,"PDD_ItemCode"=>"NPOD3");
			$arrLavel['THE STANDARD FUNDRAISER'] = array("totalDonation"=>0,"PDD_ItemCode"=>"CP-STANDARD");
			$arrLavel['THE PREMIER FUNDRAISER'] = array("totalDonation"=>0,"PDD_ItemCode"=>"CP-PREMIER");
			$arrLavel['THE PLATINUM FUNDRAISER'] = array("totalDonation"=>0,"PDD_ItemCode"=>"CP-PLATINUM");
			
			foreach($arrDonationDetails as $key => $arrValue)
			{
				if($arrValue['PDD_ItemCode']=='NPOD1')
					$arrDonation['Registred NPO Donation'] = $arrValue;
				elseif($arrValue['PDD_ItemCode']=='NPOD2')
					$arrDonation['Non Registered Donation'] = $arrValue;
				elseif($arrValue['PDD_ItemCode']=='CD1')
					$arrDonation['Fundariser Donation'] = $arrValue;
				elseif($arrValue['PDD_ItemCode']=='NPOD3')
					$arrDonation['Ambasador Donation'] = $arrValue;	
				elseif($arrValue['PDD_ItemCode']=='CP-STANDARD')
					$arrLavel['THE STANDARD FUNDRAISER'] = $arrValue;	
				elseif($arrValue['PDD_ItemCode']=='CP-PREMIER')
					$arrLavel['THE PREMIER FUNDRAISER'] = $arrValue;	
				elseif($arrValue['PDD_ItemCode']=='CP-PLATINUM')	
					$arrLavel['THE PLATINUM FUNDRAISER'] = $arrValue;	
			}
			$this->tpl->assign('fromDate', formatDate($this->fromDate,"m/d/Y"));
			$this->tpl->assign('toDate', formatDate($this->toDate,"m/d/Y"));
			$this->tpl->assign("arrLevel",$arrLavel);
			$this->tpl->assign("arrDonation",$arrDonation);
		}
		
		
		private function RecurringProfileReport()
		{
			EnPException :: writeProcessLog('Donation_Controller :: Listing action to view recurring profile report');
			
			$this->recurringfilterParameterLists();
			
			$DataArray = array('count(pdd.PDD_ReoccuringProfileID) as countProfileIDs','rp.RP_ID','pdd.PDD_ID','ru.RU_ID','CONCAT(ru.RU_FistName," ",ru.RU_LastName)Username','ru.RU_EmailID','pdd.PDD_PIItemName','rp.RP_RecurringCycle','pdd.PDD_ReoccuringProfileID','SUM(pdd.PDD_Cost)AmountPaid','rp.RP_StartDate','rp.RP_EndDate','rp.RP_RUID', 'rp.RP_RecurringCycle', 'rp.RP_RecurringAmount', 'rp.RP_Status', 'rp.RP_RecurringProfileID', 'rp.RP_RecurringCustomerID', 'pdd.PDD_PIItemName');
			
			$this->objDonation->getReportdata($DataArray);
			
			$PagingArr = constructPaging($this->objDonation->pageSelectedPage, $this->objDonation->totalRecord, $this->objDonation->pageLimit);
			$LastPage = ceil($this->objDonation->totalRecord / $this->objDonation->pageLimit);
			//dump($this->objDonation->results);
			$this->tpl->assign('totalRecords', $this->objDonation->totalRecord);
			$this->tpl->assign('recurringprofilelist', $this->objDonation->results);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
				
			/* filter secxtion assinment */
			$this->tpl->assign("RecurringProfiles", $RecurringProfiles);
			if($this->objDonation->rp_staus=='')
			{
				$this->tpl->assign("status", '1');
			}
			else
			{
				$this->tpl->assign("status", $this->objDonation->rp_staus);
			}
			$this->tpl->assign("cycle", $this->objDonation->rp_cycle);
			$this->tpl->assign("keyword", $this->objDonation->rp_keyword);
		}
		
		private function recurringfilterParameterLists() {
				
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$currentstatus = request('get', 'status', 0);
			if($currentstatus=='')
			{
				$this->objDonation->rp_staus    = '1';
			}
			else
			{
				$this->objDonation->rp_staus    = request('get', 'status', 0);
			}
			$this->objDonation->rp_cycle    = request('get', 'cycle', 0);
			$this->objDonation->rp_keyword  = request('get', 'keyword', 0);
			$this->objDonation->pageSelectedPage	= $pageSelected == 0 ? 1 : $pageSelected;
		}
		
		private function RecurringExportCSVFile()
		{
			$this->recurringfilterParameterLists();
				
			//$this->recurringGetExportConstant();
			$this->GetExportConstant();
			$DataArray = array('count(pdd.PDD_ReoccuringProfileID) as countProfileIDs','rp.RP_ID','pdd.PDD_ID','ru.RU_ID','CONCAT(ru.RU_FistName," ",ru.RU_LastName)Username','ru.RU_EmailID','pdd.PDD_PIItemName','rp.RP_RecurringCycle','pdd.PDD_ReoccuringProfileID','SUM(pdd.PDD_Cost)AmountPaid','rp.RP_StartDate','rp.RP_EndDate','rp.RP_RUID', 'rp.RP_RecurringCycle', 'rp.RP_RecurringAmount', 'rp.RP_Status', 'rp.RP_RecurringProfileID', 'rp.RP_RecurringCustomerID', 'pdd.PDD_PIItemName');
			
			$this->objDonation->getReportdata($DataArray);
			
			if(count($this->objDonation->results) == 0) {
				$messageParams = array(
						"errCode" => 'E18000',
						"errMsg" => "Custom Confirmation message",
						"errOriginDetails" => basename(__FILE__),
						"errSeverity" => 1,
						"msgDisplay" => 1,
						"msgType" => 1);
					
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}
				
			$String	= '';
			if($this->CurrentCsvPosition == 0)
				$this->RecurringCreateCsvFile();
			
			$fp = fopen($this->RecurringExportCSVFileName, 'a+');
			//dump($this->objDonation->results);	
			foreach($this->objDonation->results as $val) {
				$Status = '';
			
				if($val['RP_Status']>0 && $val['RP_Status']<11)
				{
					$Status = "Running";
				}
				else if($val['RP_Status']>10 && $val['RP_Status']<21)
				{
					$Status = "Canceled";
				}
				$array_val = array(
						$val['PDD_PIItemName'],
						$val['RP_StartDate'],
						$val['RP_RecurringAmount'],
						$val['RP_RecurringCycle'],
						$Status);
					
				fputcsv($fp, $array_val);
				$this->TotalRowProcessed++;
			}
				
			setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
				
			fclose($fp);
			$this->RecurringViewRedirectExpCsv();
		}
		
		/*private function recurringGetExportConstant() {
			$this->objDonation->isExport = 1;
				
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
				
			$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
			$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
				
			$this->objDonation->CurrentCsvPosition = $this->CurrentCsvPosition;
		}*/
		
		public function RecurringViewRedirectExpCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
				
			$TotalRows = $this->objDonation->totalRecord;
				
			if($this->CurrentCsvPosition >= $TotalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
				
			$totalper =(int)(($this->CurrentCsvPosition / $TotalRows) * 100);
			$tpl = new view;
			$tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$tpl->assign('totalPer', $totalper);
			$tpl->assign('Pstatus', $this->P_status);
			$tpl->draw("recurringpayment/exportstatus");
		}
		
		private function RecurringCreateCsvFile() {
			$fp = fopen($this->RecurringExportCSVFileName, 'w+');
			if($fp) {
				$HeaderArr	= array(
						"Cause Name",
						"Start Date",
						"Amount ($)",
						"Cycle",
						"Status"
				);
				$StringArray = implode(",", $HeaderArr)."\r\n";
				fwrite($fp, $StringArray);
			}
		}
		
		public function recurringdownloadfile($title='recurringprofilereport') {
			$path = EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile($path, "recurringprofilereport.csv", $title);
		}
		
		// list all daily donations
		private function Listing() {
			EnPException :: writeProcessLog('Donation_Controller :: Listing action to view payment Details');			
			$this->filterParameterLists();
			
			$fromDate = request('get', 'FromDate', 0);
			$toDate = request('get', 'ToDate', 0);
			$npoEin = request('get', 'npoEin', 0);
			$this->objDonation->npoEin = $npoEin != '' ? keyDecrypt($npoEin) : '';
			
			$campId = request('get', 'campId', 0);
			$this->objDonation->campId = $campId != '' ? keyDecrypt($campId) : 0;
			
			$paymentType = request('get', 'paymentType', 0);
			$this->objDonation->PaymentType = $paymentType;
			
			/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			if($toDate > $this->currentDate)
				$toDate = $this->currentDate;
			
			$this->objDonation->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->objDonation->toDate = formatDate($toDate, 'Y-m-d');*/
			
			if($fromDate == '' || $toDate == '') {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');
			
			$currentDate = formatDate($this->currentDate, 'Y-m-d');
			if($this->toDate > $currentDate) {
				$this->toDate = $currentDate;
				$toDate = $this->currentDate;
			}
			
			if($this->fromDate > $this->toDate) {
				$this->fromDate = $this->toDate;
				$fromDate = formatDate($this->fromDate, 'm/d/Y');
			}
			
			$this->objDonation->fromDate = $this->fromDate;
			$this->objDonation->toDate = $this->toDate;
			
			$field = array(
				'PDD.PDD_PIItemName as charity',
				'PDD.PDD_Cost',
				'PDD.PDD_TransactionFee',
				'PDD.PDD_TransactionFeePaidByUser',
				'PDD.PDD_SubTotal',
				'PDD.PDD_PaymentType',
				'PDD.PDD_PIItemType',
				'PDD.PDD_DateTime',
				'PT.PT_PaymentType',
				'PT.PT_PaymentGatewayName',
				'PT.PT_PaymentStatus');
			
			$donationList = $this->objDonation->DailyDonation($field, $this->filterParam);	
			//dump($donationList);
			$PagingArr = constructPaging($this->objDonation->pageSelectedPage, $this->objDonation->totalRecord, $this->objDonation->pageLimit);
			$LastPage = ceil($this->objDonation->totalRecord / $this->objDonation->pageLimit);
			if($this->itemCode!='') {
				if($this->itemCode=='NPOD1' || $this->itemCode=='NPOD2' || $this->itemCode=='NPOD3' || $this->itemCode=='CD1')
					$this->transactionType = 1;
				elseif($this->itemCode=='CP-STANDARD' || $this->itemCode=='CP-PREMIER' || $this->itemCode=='CP-PLATINUM')
					$this->transactionType = 2;
			}
			$this->tpl->assign('totalRecords', $this->objDonation->totalRecord);
			$this->tpl->assign('donationList', $donationList);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			$this->tpl->assign('fromDate', $fromDate);
			$this->tpl->assign('toDate', $toDate);
			$this->tpl->assign('itemCode', $this->itemCode);
			$this->tpl->assign('campId', $campId);
			$this->tpl->assign('npoEin', $npoEin);
			$this->tpl->assign('paymentProcessor', $this->paymentProcessor);
			$this->tpl->assign('transactionType', $this->transactionType);
			$this->tpl->assign('paymentType', $paymentType);
		}
		
		private function filterParameterLists() {
			$pageSelected = (int)request('get', 'pageNumber', 1);
			
			$this->paymentProcessor = request('get', 'PaymentProcessor', 0);
			$this->transactionType = request('get', 'TransactionType', 0);
			//dump(request('get','itemCode',0));
			$this->itemCode	= request('get', 'itemCode', 0);
			$this->itemCode = ($this->itemCode != '') ? keyDecrypt($this->itemCode) : '';
			
			$this->objDonation->pageSelectedPage = $pageSelected == 0 ? 1 : $pageSelected;
			$this->objDonation->itemCode	= $this->itemCode;
			$this->objDonation->paymentProcessor = $this->paymentProcessor;
			$this->objDonation->transactionType = $this->transactionType;
		}
		
		// export report data to csv file
		public function ExportCSVFile() {
			
			$this->filterParameterLists();
			
			$fromDate = request('get', 'FromDate', 0);
			$toDate = request('get', 'ToDate', 0);
			$campId = request('get', 'campId', 0);
			$this->objDonation->campId = $campId != '' ? keyDecrypt($campId) : 0;
			
			$npoEin = request('get', 'npoEin', 0);
			$this->objDonation->npoEin = $npoEin != '' ? keyDecrypt($npoEin) : '';
			
			$paymentType = request('get', 'paymentType', 0);
			$this->objDonation->PaymentType = $paymentType;
			
			/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			if($toDate > $this->currentDate)
				$toDate = $this->currentDate;
			
			$this->objDonation->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->objDonation->toDate = formatDate($toDate, 'Y-m-d');*/
			
			if($fromDate == '' || $toDate == '') {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');
			
			$currentDate = formatDate($this->currentDate, 'Y-m-d');
			if($this->toDate > $currentDate) {
				$this->toDate = $currentDate;
				$toDate = $this->currentDate;
			}
			
			if($this->fromDate > $this->toDate) {
				$this->fromDate = $this->toDate;
				$fromDate = formatDate($this->fromDate, 'm/d/Y');
			}
			
			$this->objDonation->fromDate = $this->fromDate;
			$this->objDonation->toDate = $this->toDate;
			
			$this->GetExportConstant();
			
			$field = array(
				'PDD.PDD_PIItemName as charity',
				'PDD.PDD_Cost',
				'PDD.PDD_TransactionFee',
				'PDD.PDD_TransactionFeePaidByUser',
				'PDD.PDD_SubTotal',
				'PDD.PDD_PaymentType',
				'PDD.PDD_PIItemType',
				'PDD.PDD_DateTime',
				'PT.PT_PaymentType',
				'PT.PT_PaymentGatewayName',
				'PT.PT_PaymentStatus');
			
			$DonationArray = $this->objDonation->DailyDonation($field);
			//dump($DonationArray);
			if(count($DonationArray) == 0) {
				$messageParams = array(
					"errCode" => 'E18000',
					"errMsg" => "Custom Confirmation message",
					"errOriginDetails" => basename(__FILE__),
					"errSeverity" => 1,
					"msgDisplay" => 1,
					"msgType" => 1);
					
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}
			
			$String	= '';
			if($this->CurrentCsvPosition == 0)
				$this->CreateCsvFile();
				
			$fp = fopen($this->ExportCSVFileName, 'a+');
			
			foreach($DonationArray as $val) {
				$TransactionType = '';
				$paymentProcessor = '';
				
				if($val['PDD_PIItemType'] == 'NPOD' || $val['PDD_PIItemType'] == 'CD')
					$TransactionType = "Donation";
				elseif($val['PDD_PIItemType'] == 'CP')
					$TransactionType = "Purchase";
				
				if($val['PT_PaymentType'] == 'CC')
					$paymentProcessor = 'Stripe';
				elseif($val['PT_PaymentType'] == 'ACH')
					$paymentProcessor = 'Pay Simple';
					
				$array_val = array(
					formatDate($val['PDD_DateTime'], 'm/d/Y'),
					$val['PDD_SubTotal'],
					$val['PDD_TransactionFee'],
					$val['PDD_Cost'],
					$paymentProcessor,
					$TransactionType,
					$val['charity']);
					
				fputcsv($fp, $array_val);		
				$this->TotalRowProcessed++;
			}
			
			setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
			setSession('arrCsvExp', request('get', 'itemCode', 0), 'itemCode');
			
			fclose($fp);
			$this->ViewRedirectExpCsv();		
		}
		
		private function GetExportConstant() {
			$this->objDonation->isExport = 1;
			
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			
			$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
			$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
			
			$this->objDonation->CurrentCsvPosition = $this->CurrentCsvPosition;
		}
		
		public function ViewRedirectExpCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$itemCode = getSession('arrCsvExp', 'itemCode');
			//dump($itemCode);
			$TotalRows = $this->objDonation->totalRecord;
			
			if($this->CurrentCsvPosition >= $TotalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
			
			$totalper =(int)(($this->CurrentCsvPosition / $TotalRows) * 100);
			$tpl = new view;
			$tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$tpl->assign('totalPer', $totalper);
			$tpl->assign('Pstatus', $this->P_status);
			$tpl->assign('itemCode', $itemCode);
			$tpl->draw("donation/exportstatus");
		}
		
		private function CreateCsvFile() {
			$fp = fopen($this->ExportCSVFileName, 'w+');
			if($fp) {
				$HeaderArr	= array(
					"Date",
					"Paid Amount ($)",
					"Transaction Fee ($)",
					"Amount Received to Donasity ($)",
					"Payment Processor",
					"Transaction Type",
					"Name of Charity/Purchase"
				);
				$StringArray = implode(",", $HeaderArr)."\r\n";
				fwrite($fp, $StringArray);
			}
		}
		
		public function downloadfile($title='dialydonation') {
			$path = EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile($path, "dialydonation.csv", $title);
		}
		
		// donation list of remotely created eCheck
		private function RemotelyCreatedCheck() {
			EnPException :: writeProcessLog('Donation_Controller :: RemotelyCreatedCheck action called');
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$this->objDonation->pageSelectedPage = $pageSelected == 0 ? 1 : $pageSelected;
			
			$eCheckStatus = request('get', 'eCheckStatus', 0);
			
			$fromDate = request('get', 'FromDate', 0);
			$toDate = request('get', 'ToDate', 0);
			
			/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			if($toDate > $this->currentDate)
				$toDate = $this->currentDate;
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');*/
			
			if($fromDate == '' || $toDate == '') {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');
			
			$currentDate = formatDate($this->currentDate, 'Y-m-d');
			if($this->toDate > $currentDate) {
				$this->toDate = $currentDate;
				$toDate = $this->currentDate;
			}
			
			if($this->fromDate > $this->toDate) {
				$this->fromDate = $this->toDate;
				$fromDate = formatDate($this->fromDate, 'm/d/Y');
			}
			
			$field = array(
				'PDD.PDD_DateTime',
				'PDD.PDD_eCheckComment',
				'RU.RU_FistName',
				'RU.RU_LastName',
				'PDD.PDD_SubTotal',
				'PDD.PDD_TransactionFee',
				'PDD.PDD_Cost',
				'PDD.PDD_eCheckStatus');
			
			$strCheckStatus = '';
			if($eCheckStatus != '') {
				switch($eCheckStatus) {
					case '1' :
						$strCheckStatus = " AND PDD.PDD_eCheckStatus = '1'";
					break;
					case '2' :
						$strCheckStatus = " AND PDD.PDD_eCheckStatus = '0'";
					break;
				}
			}	
			
			$filterString = " Where 1 $strCheckStatus AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '$this->toDate' ";
			
			$sortOrder = " ORDER BY PDD.PDD_DateTime DESC ";
				
			$donationList = $this->objDonation->RemotelyCreatedDailyDonation($field, $filterString, $sortOrder);
			
			$PagingArr = constructPaging($this->objDonation->pageSelectedPage, $this->objDonation->totalRecord, $this->objDonation->pageLimit);
			$LastPage = ceil($this->objDonation->totalRecord / $this->objDonation->pageLimit);
			
			$this->tpl->assign('totalRecords', $this->objDonation->totalRecord);
			$this->tpl->assign('donationList', $donationList);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			$this->tpl->assign('fromDate', $fromDate);
			$this->tpl->assign('toDate', $toDate);
			$this->tpl->assign('eCheckStatus', $eCheckStatus);
			$this->tpl->draw('donation/remotelyCreatedCheckReport');	
		}
		
		// export donation list report of remotely created eCheck to csv file
		public function RemoteCheckExport() {
			EnPException :: writeProcessLog('Donation_Controller :: RemoteCheckExport action called');
			
			$eCheckStatus = request('get', 'eCheckStatus', 0);
			$fromDate = request('get', 'FromDate', 0);
			$toDate = request('get', 'ToDate', 0);
			
			/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			if($toDate > $this->currentDate)
				$toDate = $this->currentDate;
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');*/
			
			if($fromDate == '' || $toDate == '') {
				$fromDate = $this->fromDate;
				$toDate = $this->toDate;
			}
			
			$this->fromDate = formatDate($fromDate, 'Y-m-d');
			$this->toDate = formatDate($toDate, 'Y-m-d');
			
			$currentDate = formatDate($this->currentDate, 'Y-m-d');
			if($this->toDate > $currentDate) {
				$this->toDate = $currentDate;
				$toDate = $this->currentDate;
			}
			
			if($this->fromDate > $this->toDate) {
				$this->fromDate = $this->toDate;
				$fromDate = formatDate($this->fromDate, 'm/d/Y');
			}
			
			$field = array(
				'PDD.PDD_DateTime',
				'PDD.PDD_eCheckComment',
				'RU.RU_FistName',
				'RU.RU_LastName',
				'PDD.PDD_SubTotal',
				'PDD.PDD_TransactionFee',
				'PDD.PDD_Cost',
				'PDD.PDD_eCheckStatus');
				
			$strCheckStatus = '';
			if($eCheckStatus != '') {
				switch($eCheckStatus) {
					case '1' :
						$strCheckStatus = " AND PDD.PDD_eCheckStatus = '1'";
					break;
					case '2' :
						$strCheckStatus = " AND PDD.PDD_eCheckStatus = '0'";
					break;
				}
			}
			
			$filterString = " Where 1 $strCheckStatus AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '$this->toDate' ";
			
			$sortOrder = " ORDER BY PDD.PDD_DateTime DESC ";
				
			//$this->recurringGetExportConstant();
			$this->GetExportConstant();
			
			$donationList = $this->objDonation->RemotelyCreatedDailyDonation($field, $filterString, $sortOrder);
			
			if(count($donationList) == 0) {
				$this->SetStatus(0, 'E18000');
				redirect($_SERVER['HTTP_REFERER']);
			}
			
			if($this->CurrentCsvPosition == 0)
				$this->RemoteCheckCreateCsv();
			
			//dump($donationList);
			$fp = fopen($this->RemoteCheckExportFile, 'a+');
			foreach($donationList as $val) {
				$array_val = array(
					formatDate($val['PDD_DateTime'], 'm/d/Y'),
					UnSerializeArray($val['PDD_eCheckComment'], 'Bank_Name'),
					UnSerializeArray($val['PDD_eCheckComment'], 'Account_Number'),
					UnSerializeArray($val['PDD_eCheckComment'], 'Account_Type'),
					$val['RU_FistName'] . ' ' . $val['RU_LastName'],
					$val['PDD_SubTotal'],
					$val['PDD_TransactionFee'],
					$val['PDD_Cost'],
					$val['PDD_eCheckStatus'] == '1' ? 'Clear' : $val['PDD_eCheckStatus'] == '0' ? 'Pending' : '');
					
				fputcsv($fp, $array_val);		
				$this->TotalRowProcessed++;
			}
			
			setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
			
			fclose($fp);
			$this->RemoteCheckRedirectCsv();		
		}
		
		private function RemoteCheckCreateCsv() {
			$fp = fopen($this->RemoteCheckExportFile, 'w+');
			if($fp) {
				$HeaderArr	= array(
					"Date",
					"Bank",
					"Account Number",
					"Account Type",
					"Name User",
					"Paid Amount($)",
					"Transaction Fee($)",
					"Donation Amount($)",
					"eCheck Status"
				);
				$StringArray = implode(",", $HeaderArr)."\r\n";
				fwrite($fp, $StringArray);
			}
		}
		
		public function RemoteCheckRedirectCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			
			$TotalRows = $this->objDonation->totalRecord;
			
			if($this->CurrentCsvPosition >= $TotalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
			
			$totalper =(int)(($this->CurrentCsvPosition / $TotalRows) * 100);
			//$tpl = new view;
			$this->tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$this->tpl->assign('totalPer', $totalper);
			$this->tpl->assign('Pstatus', $this->P_status);
			$this->tpl->draw('donation/remotelyCreatedCheckExportStatus');
		}
		
		public function downloadRemoteCheck($title='remotely_echeck_donation_report') {
			$path = EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile($path, "remotely_echeck_donation_report.csv", $title);
		}
		
		// set process status
		private function SetStatus($Status, $Code) {
			if($Status) {
				$messageParams = array(
					'msgCode'	=>$Code,
					'msg'		=>'Custom Confirmation message',
					'msgLog'	=>0,									
					'msgDisplay'=>1,
					'msgType'	=>2);
				EnPException :: setConfirmation($messageParams);
			} else {
				$messageParams = array(
					'errCode'	=> $Code,
					'errMsg'	=> 'Custom Confirmation message',
					'errOriginDetails' => basename(__FILE__),
					'errSeverity' => 1,
					'msgDisplay' => 1,
					'msgType'	=> 1);
				EnPException :: setError($messageParams);
			}
		}
	}
?>