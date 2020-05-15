<?php
	class Salesubscriptionreport_Controller extends Controller {
	
		public $loginDetails, $tpl, $fromDate, $toDate, $currentDate, $filterParam = '', $adminId = 0;
		//export
		public $totalRowProcessed = 0, $currentCsvPosition = 0, $exportCSVFileName, $P_status, $arrayInputData;
		
		function __construct() {
			checkLogin(32);
			$this->P_status = 1;
			$this->load_model('SaleSubscriptionReport', 'objSaleSubReport');			
			$this->loginDetails = getsession("DonasityAdminLoginDetail");
			$this->tpl = new view;
			$this->adminId = $this->loginDetails['admin_id'];
			$this->exportCSVFileName = EXPORT_CSV_PATH . 'sale_subscription_report_'. $this->adminId .'.csv';
		}
	
		public function index($type='list') {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: index action called with request - ' . $type);
			
			switch(strtolower($type)) {
				case 'list':
					$this->Listing();
				break;
				case 'export' :
					$this->Export();
				break;
				default :
					$this->Listing();
				break;
			}
		}
	
		private function Init() {
			$pageSelected = request('get', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
				
			$this->objSaleSubReport->pageSelected = $pageSelected;
			
			// set filter param
			$fromDate = request('get', 'fromDate', 0);
			$toDate = request('get', 'toDate', 0);
			$dd_status = request('get', 'dd_status', 0);
			$dd_frequency = request('get', 'dd_frequency', 0);
			$searchKeyword = request('get', 'searchKeyword', 0);
			
			$this->arrayInputData  = array(
				'fromDate' => $fromDate,
				'toDate' => $toDate,
				'dd_status' => $dd_status,
				'dd_frequency' => $dd_frequency,
				'dd_frequency' => $dd_frequency,
				'searchKeyword' => $searchKeyword);
				
		}
		
		private function setFilterParam() {
			
			$this->arrayInputData['dd_status'] = $this->arrayInputData['dd_status'] == '' ? 2 : $this->arrayInputData['dd_status'];
			
			$this->arrayInputData['dd_frequency'] = $this->arrayInputData['dd_frequency'] == '' ? 3 : $this->arrayInputData['dd_frequency'];
			
			switch($this->arrayInputData['dd_frequency']) {
				case '1' :
					$this->arrayInputData['fromDate'] = getDateTime(0,'Y-m-d');
					$this->arrayInputData['toDate'] = getDateTime(0,'Y-m-d');
				break;
				case '2' :
					$this->arrayInputData['fromDate'] = getNextDate(7,'-');
					$this->arrayInputData['toDate'] = getDateTime(0,'Y-m-d');
				break;
				case 'all' :
					$this->arrayInputData['fromDate'] = formatDate($this->arrayInputData['fromDate'], 'Y-m-d');
					$this->arrayInputData['toDate'] = formatDate($this->arrayInputData['toDate'], 'Y-m-d');
				break;
				default :
					$this->arrayInputData['fromDate'] = getNextDate(30,'-');
					$this->arrayInputData['toDate'] = getDateTime(0,'Y-m-d');
				break;
			}
			
			$condition = "";
			if($this->arrayInputData['dd_status'] != 'all')
				$condition .= " AND SSPT.SSPT_Status=" . $this->arrayInputData['dd_status'];
				
			if($this->arrayInputData['searchKeyword'] != '')
				$condition .= " AND SS.SS_RefNumber='" . $this->arrayInputData['searchKeyword'] . "'";
				
			$orderBy = " ORDER BY SSPT.SSPT_CreatedDate DESC";
				
			$this->filterParam = " AND DATE_FORMAT(SSPT.SSPT_CreatedDate, '%Y-%m-%d') >= '" . $this->arrayInputData['fromDate'] . "' AND DATE_FORMAT(SSPT.SSPT_CreatedDate, '%Y-%m-%d') <= '" . $this->arrayInputData['toDate'] . "' " . $condition . $orderBy;
			//dump($this->filterParam);
		}
		
		// list all sale subscription detail
		private function Listing() {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: Listing action to view Details');
			$this->Init();
			$this->setFilterParam();
			
			$DataArray = array(
				'SQL_CACHE SSPT.SSPT_ID',
				'SSPT.SSPT_PaymentType',
				'SSPT.SSPT_PaymentAmount',
				'SSPT.SSPT_PaymentGatewayTransactionID',
				'SSPT.SSPT_Status',
				'SSPT.SSPT_CreatedDate',
				'SS.SS_RefNumber',
				'SS.SS_DateTime');
			
			$saleSubsReport = $this->objSaleSubReport->GetSaleSubscriptionDetails($DataArray, $this->filterParam);
			//dump($saleSubsReport);
			$arrayTransStatus = get_setting('TransactionStatus');
			$pagingArr = constructPaging($this->objSaleSubReport->pageSelected, $this->objSaleSubReport->totalRecord, $this->objSaleSubReport->pageLimit);		
			$lastPage = ceil($this->objSaleSubReport->totalRecord / $this->objSaleSubReport->pageLimit);
			//dump($dd_status);
			$this->tpl->assign('totalRecord', $this->objSaleSubReport->totalRecord);
			$this->tpl->assign('saleSubsReport', $saleSubsReport);
			$this->tpl->assign('pagingList', $pagingArr['Pages']);
			$this->tpl->assign('pageSelected', $pagingArr['PageSel']);
			$this->tpl->assign('startRecord', $pagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $pagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $lastPage);
			$this->tpl->assign('pageNumber', $pageSelected);
			$this->tpl->assign('fromDate', $this->arrayInputData['fromDate']);
			$this->tpl->assign('toDate', $this->arrayInputData['toDate']);
			$this->tpl->assign('searchKeyword', $this->arrayInputData['searchKeyword']);
			$this->tpl->assign('dd_status', $this->arrayInputData['dd_status']);
			$this->tpl->assign('dd_frequency', $this->arrayInputData['dd_frequency']);
			$this->tpl->assign('arrayTransStatus', $arrayTransStatus);
			$this->tpl->draw('salesubscriptionreport/listing');
		}
	
		// export report data to csv file
		private function Export() {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: Export action called.');
			
			$this->GetExportConstant();
			$CsvData = $this->GetCsvData();
			
			if(count($CsvData) == 0) {
				$this->SetStatus(0, 'ECSV01');
				redirect(URL . 'salesubscriptionreport');
				return;
			}
			
			$csvHeader = array('Order Ref.', 'Order Date', 'Payment Method', 'Transaction ID', 'Amount($)', 'Status', 'Created on');
			
			if($this->currentCsvPosition == 0)
				$this->CreateCsvFile($csvHeader);
				
			$arrayTransStatus = get_setting('TransactionStatus');
			$fp = fopen($this->exportCSVFileName, 'a+');
			//dump($CsvData,0);
			foreach($CsvData as $val) {
				$val['SS_DateTime'] = formatDate($val['SS_DateTime'], 'm/d/Y');
				if($val['SSPT_PaymentType'] == 'RC')
					$val['SSPT_PaymentType'] = 'Recurring';
				else
					$val['SSPT_PaymentType'] = 'One Time';
				
				$val['SSPT_Status'] = $arrayTransStatus[$val['SSPT_Status']];
					
				$val['SSPT_CreatedDate'] = formatDate($val['SSPT_CreatedDate'], 'm/d/Y');
				
				fputcsv($fp, $val);		
				$this->totalRowProcessed++;
			}
				
			setSession('arrCsvExp', $this->totalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->totalRowProcessed, 'TOTALROWPROCESSED');
			//setSession('arrCsvExp', $this->toDate, 'toDate');
			//setSession('arrCsvExp', $this->fromDate, 'fromDate');
			
			fclose($fp);
			$this->ViewRedirectExpCsv();
		}
		
		// get export constant
		private function GetExportConstant() {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: GetExportConstant action called.');
			$this->objSaleSubReport->isExport = 1;
			
			$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			//$this->toDate = getSession('arrCsvExp', 'toDate');
			//$this->fromDate = getSession('arrCsvExp', 'fromDate');
			//dump($this->CurrentCsvPosition);
			$this->currentCsvPosition = (is_array($this->currentCsvPosition) || $this->currentCsvPosition == '') ? 0 : $this->currentCsvPosition;
			$this->totalRowProcessed = (is_array($this->totalRowProcessed) || $this->totalRowProcessed == '') ? 0 : $this->totalRowProcessed;
			
			$this->objSaleSubReport->currentCsvPosition = $this->currentCsvPosition;
		}
		
		// prepare data from table to export into csv
		private function GetCsvData() {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: GetCsvData action called.');
			
			//$this->Init();
			//$this->setFilterParam();
			
			$DataArray = array(
				'SQL_CACHE SS.SS_RefNumber',
				'SS.SS_DateTime',
				'SSPT.SSPT_PaymentType',
				'SSPT.SSPT_PaymentGatewayTransactionID',
				'SSPT.SSPT_PaymentAmount',
				'SSPT.SSPT_Status',
				'SSPT.SSPT_CreatedDate');
			
			return $this->objSaleSubReport->GetSaleSubscriptionDetails($DataArray, $this->filterParam);
		}
		
		// create csv file
		private function CreateCsvFile($headerArr) {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: CreateCsvFile action called.');
			$fp = fopen($this->exportCSVFileName, 'w+');
			if($fp) {
				$stringArray = implode(",", $headerArr) . "\r\n";
				fwrite($fp, $stringArray);
			}
		}
		
		// export progress bar
		public function ViewRedirectExpCsv() {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: ViewRedirectExpCsv action called.');
			$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$totalRows = $this->objSaleSubReport->totalRecord;
			//dump($this->currentCsvPosition, 0);
			if($this->currentCsvPosition >= $totalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
			
			$totalper = (int)(($this->currentCsvPosition / $totalRows) * 100);
			$this->tpl->assign('rowProcessed', $this->totalRowProcessed);
			$this->tpl->assign('totalPer', $totalper);
			$this->tpl->assign('Pstatus', $this->P_status);
			$this->tpl->assign('currentCsvPosition', $this->currentCsvPosition);
			$this->tpl->assign('totalRows', $totalRows);
			$this->tpl->draw('salesubscriptionreport/exportstatus');
		}
		
		// download csv file
		public function downloadfile($title='sale_subscription_report_') {
			EnPException :: writeProcessLog('Salesubscriptionreport_Controller :: downloadfile action called.');
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
		}
	
		private function SetStatus($Status, $Code, $custom = NULL)  {
			$this->P_Status = $Status;
			$Msg = "Custom Confirmation message";
			if($custom != NULL){
				$Msg = $custom;
				$Code = '000';
			}
			
			if($Status) {							
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"			=> $Msg,
					"msgLog"		=> 0,									
					"msgDisplay"	=> 1,
					"msgType"		=> 2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode" 			=> $Code,
					"errMsg"			=> $Msg,
					"errOriginDetails"	=> basename(__FILE__),
					"errSeverity"		=> 1,
					"msgDisplay"		=> 1,
					"msgType"			=> 1);
				EnPException::setError($messageParams);
			}
		}
	}
?>

