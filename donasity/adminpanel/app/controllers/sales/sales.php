<?php
	class Sales_Controller extends Controller {
		
		public $tpl, $fromDate, $toDate, $currentDate, $filterParam = '', $adminId = 0;
		//export
		public $totalRowProcessed = 0, $currentCsvPosition = 0, $exportCSVFileName, $P_status = 1, $arrayInputData = array();
		
		function __construct() {
			checkLogin(30);
			$this->tpl = new view;
			$this->load_model('Sales', 'sales');
			//$this->fromDate = date('m/d/Y', strtotime('today - 30 days'));
			//$this->toDate = formatDate(getDateTime(), 'm/d/Y');
			//$this->currentDate = $this->toDate;
			$loginDetails = getsession("DonasityAdminLoginDetail");
			$this->adminId = $loginDetails['admin_id'];
			$this->exportCSVFileName = EXPORT_CSV_PATH . 'sales_report_'. $this->adminId .'.csv';
		}
		
		public function index($type='list') {
			EnPException :: writeProcessLog('Sales_Controller :: index action called with request - ' . $type);
			
			switch(strtolower($type)) {
				case 'list':
					$this->Listing();
				break;
				case 'export-sales' :
					$this->sales->isExport = 1;
					$this->ExportSales();
				break;
				default :
					$this->Listing();
				break;
			}
		}
		
		// list all sales detail
		private function Listing() {
			EnPException :: writeProcessLog('Sales_Controller :: Listing action to view Details');
			
			$this->Init();
			$this->SetFilterParam();
			
			/*//page selected value
			$pageSelected = request('get', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
				
			$this->sales->pageSelected = $pageSelected;
			
			//$sales_id = request('get', 'sales_id', 0);
			$sales_id = str_replace(array("'", "\"", "&quot;"), "", request('get', 'sales_id', 0));
			
			// set filter param
			$fromDate = request('get', 'fromDate', 0);
			$toDate = request('get', 'toDate', 0);
			
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
			}*/
			
			/*$orderBy = " ORDER BY C.Camp_ID DESC";
			$salesStr = '';
			if($sales_id != '')
				$salesStr = ' AND C.Camp_SalesForceID = "'.$sales_id.'" ';
				
			$this->filterParam = " AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') <= '$this->toDate' AND C.Camp_SalesForceID IS NOT NULL AND C.Camp_SalesForceID != '' AND C.Camp_Deleted != '1' " . $salesStr . $orderBy;*/
			
			$DataArray = array(
				'SQL_CACHE C.Camp_CreatedDate fundDate',
				'C.Camp_SalesForceID salesId',
				'C.Camp_Title fundraiser',
				'C.Camp_Status campStatus',
				'C.Camp_ID campId',
  				'C.Camp_UrlFriendlyName url',
				'CL.Camp_Level_Name fundLevel',
				'C.Camp_Location_State fundState',
				'NPOCat.NPOCat_DisplayName_EN category',
				'RU.RU_AllowAmbassador isAmbassador');
			
			$salesList = $this->sales->GetSalesDetails($DataArray, $this->filterParam);
			
			$pagingArr = constructPaging($this->sales->pageSelected, $this->sales->totalRecord, $this->sales->pageLimit);		
			$lastPage = ceil($this->sales->totalRecord / $this->sales->pageLimit);
			
			$this->tpl->assign('totalRecord', $this->sales->totalRecord);
			$this->tpl->assign('salesList', $salesList);
			$this->tpl->assign('pagingList', $pagingArr['Pages']);
			$this->tpl->assign('pageSelected', $pagingArr['PageSel']);
			$this->tpl->assign('startRecord', $pagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $pagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $lastPage);
			$this->tpl->assign('pageNumber', $pageSelected);
			$this->tpl->assign('fromDate', $this->arrayInputData['fromDate']);
			$this->tpl->assign('toDate', $this->arrayInputData['toDate']);
			$this->tpl->assign('sales_id', $this->arrayInputData['sales_id']);
			$this->tpl->draw('sales/sales_list');
		}
		
		// export report data to csv file
		private function ExportSales() {
			EnPException :: writeProcessLog('Sales_Controller :: ExportSales action called.');
			
			$this->GetExportConstant();
			$CsvData = $this->GetCsvData();
			
			if(count($CsvData) == 0) {
				$this->SetStatus(0, 'ECSV01');
				redirect(URL . 'sales/index/list');
				return;
			}
			
			$csvHeader = array('Date', 'Sales ID', 'Fundraiser', 'Level', 'Fundraiser State', 'Fundraiser Category', 'Ambassador');
			if($this->currentCsvPosition == 0)
				$this->CreateCsvFile($csvHeader);
				
			$fp = fopen($this->exportCSVFileName, 'a+');
			foreach($CsvData as $val) {
				$val['fundDate'] = formatDate($val['fundDate'], 'm/d/Y');
				if($val['isAmbassador'] == '1')
					$val['isAmbassador'] = 'Yes';
				if($val['isAmbassador'] == '0')
					$val['isAmbassador'] = 'No';
				
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
			EnPException :: writeProcessLog('Sales_Controller :: GetExportConstant action called.');
			//$this->sales->isExport = 1;
			
			$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			//$this->toDate = getSession('arrCsvExp', 'toDate');
			//$this->fromDate = getSession('arrCsvExp', 'fromDate');
			//dump($this->CurrentCsvPosition);
			$this->currentCsvPosition = (is_array($this->currentCsvPosition) || $this->currentCsvPosition == '') ? 0 : $this->currentCsvPosition;
			$this->totalRowProcessed = (is_array($this->totalRowProcessed) || $this->totalRowProcessed == '') ? 0 : $this->totalRowProcessed;
			
			$this->sales->currentCsvPosition = $this->currentCsvPosition;
		}
		
		// prepare data from table to export into csv
		private function GetCsvData() {
			EnPException :: writeProcessLog('Sales_Controller :: GetCsvData action called.');
			$DataArray = array(
				'SQL_CACHE C.Camp_CreatedDate fundDate',
				'C.Camp_SalesForceID salesId',
				'C.Camp_Title fundraiser',
				'CL.Camp_Level_Name fundLevel',
				'C.Camp_Location_State fundState',
				'NPOCat.NPOCat_DisplayName_EN category',
				'RU.RU_AllowAmbassador isAmbassador');
				
			$this->Init();
			$this->SetFilterParam();
			
			/*$fromDate = request('get', 'fromDate', 0);
			$toDate = request('get', 'toDate', 0);
			$sales_id = request('get', 'sales_id', 0);
			
			// set filter param
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
			
			$salesStr = '';
			if($sales_id != '')
				$salesStr = " AND C.Camp_SalesForceID = '$sales_id' ";
				
			$orderBy = " ORDER BY C.Camp_ID DESC";
			
			$this->filterParam = " AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') <= '$this->toDate' AND C.Camp_SalesForceID IS NOT NULL AND C.Camp_SalesForceID != '' AND C.Camp_Deleted = '0' " . $salesStr . $orderBy;*/
			
			return $this->sales->GetSalesDetails($DataArray, $this->filterParam);
		}
		
		// create csv file
		private function CreateCsvFile($headerArr) {
			EnPException :: writeProcessLog('Sales_Controller :: CreateCsvFile action called.');
			$fp = fopen($this->exportCSVFileName, 'w+');
			if($fp) {
				$stringArray = implode(",", $headerArr) . "\r\n";
				fwrite($fp, $stringArray);
			}
		}
		
		// export progress bar
		public function ViewRedirectExpCsv() {
			EnPException :: writeProcessLog('Sales_Controller :: ViewRedirectExpCsv action called.');
			$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$totalRows = $this->sales->totalRecord;
			if($this->currentCsvPosition >= $totalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
				unsetSession("filterInput");
			}
			
			$totalper = (int)(($this->currentCsvPosition / $totalRows) * 100);
			$this->tpl->assign('rowProcessed', $this->totalRowProcessed);
			$this->tpl->assign('totalPer', $totalper);
			$this->tpl->assign('Pstatus', $this->P_status);
			$this->tpl->draw('sales/exportstatus');
		}
		
		// download csv file
		public function downloadfile($title='sales_report_') {
			EnPException :: writeProcessLog('Sales_Controller :: downloadfile action called.');
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
		}
		
		// download csv file
		public function downloadfilereport($title='sale_subscription_report_') {
			EnPException :: writeProcessLog('Sales_Controller :: downloadfile action called.');
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
		}
		
		// initialization of values
		private function Init() {
			EnPException :: writeProcessLog('Sales_Controller :: Init() called.');
			
			$pageSelected = request('get', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
				
			$this->sales->pageSelected = $pageSelected;
			
			$filterData = unserialize(keyDecrypt(getSession('filterInput')));
			if(is_array($filterData) && count($filterData) > 0) {
				$fromDate = $filterData['fromDate'];
				$toDate = $filterData['toDate'];
				$sales_id = $filterData['sales_id'];
			} else {
				$fromDate = request('get', 'fromDate', 0);
				$toDate = request('get', 'toDate', 0);
				$sales_id = request('get', 'sales_id', 0);
			}
						
			$this->arrayInputData  = array(
				'fromDate' => $fromDate,
				'toDate' => $toDate,
				'sales_id' => $sales_id);
				
			if(getSession('filterInput') == '' && $this->sales->isExport == 1)
				setSession('filterInput', keyEncrypt(serialize($this->arrayInputData)));
		}
		
		
		// set filter parameters to appand sql query
		private function SetFilterParam() {
			EnPException :: writeProcessLog('Sales_Controller :: Init() called.');
				
			$fromDate = $this->arrayInputData['fromDate'] == '' ? getNextDate(30,'-') : $this->arrayInputData['fromDate'];
			$toDate = $this->arrayInputData['toDate'] == '' ? getDateTime(0,'Y-m-d') : $this->arrayInputData['toDate'];
			$this->arrayInputData['fromDate'] = formatDate($fromDate, 'Y-m-d');
			$this->arrayInputData['toDate'] = formatDate($toDate, 'Y-m-d');
						
			$condition = "";
			$orderBy = " ORDER BY C.Camp_ID DESC";
			
			if($this->arrayInputData['sales_id'] != '')
				$condition = " AND C.Camp_SalesForceID = '" . $this->arrayInputData['sales_id'] . "'";
				
			$this->filterParam = " AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') >= '" . $this->arrayInputData['fromDate'] . "' AND DATE_FORMAT(C.Camp_CreatedDate, '%Y-%m-%d') <= '" . $this->arrayInputData['toDate'] . "' AND C.Camp_SalesForceID IS NOT NULL AND C.Camp_SalesForceID != '' AND C.Camp_Deleted != '1' " . $condition . $orderBy;
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