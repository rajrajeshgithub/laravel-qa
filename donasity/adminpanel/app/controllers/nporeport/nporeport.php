<?php
	class NpoReport_Controller extends Controller {
		
		public $tpl, $fromDate, $toDate, $currentDate, $filterParam = '', $adminId = 0;
		//export
		public $TotalRowProcessed = 0, $CurrentCsvPosition = 0, $ExportCSVFileName, $P_status = 1;
		
		function __construct() {
			checkLogin(25);
			$this->tpl = new view;
			$this->load_model('NpoReport', 'NpoReport');
			$this->fromDate = date('m/d/Y', strtotime('today - 30 days'));
			$this->toDate = formatDate(getDateTime(), 'm/d/Y');
			$this->currentDate = $this->toDate;
			$loginDetails = getsession("DonasityAdminLoginDetail");
			$this->adminId = $loginDetails['admin_id'];
			$this->ExportCSVFileName = EXPORT_CSV_PATH . 'registered_npo_'. $this->adminId .'.csv';
		}
		
		public function index($type='list') {
			switch(strtolower($type)) {
				case 'list':
					$this->Summary();
				break;
				case 'npo-registered':
					$this->NpoRegistered();
				break;
				case 'export-npo-report' :
					$this->NpoReport->isExport = 1;
					$this->ExportNpoReport();
				break;
				case 'npo-payment-collected':
					$this->NpoPaymentCollected();
				break;
				default :
					$this->Summary();
				break;
			}
		}
		
		// list all
		private function Summary() {
			EnPException :: writeProcessLog('NpoReport_Controller :: Summary action to view npo Details');
			
			$fromDate = request('get', 'fromDate', 0);
			$toDate = request('get', 'toDate', 0);
			
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
			
			$this->filterParam = " WHERE DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')<='$this->toDate' ";
			
			$npoSummary = $this->NpoReport->GetSummary($this->filterParam);
			$this->tpl->assign('npoSummary', $npoSummary);
			$this->tpl->assign('fromDate', $fromDate);
			$this->tpl->assign('toDate', $toDate);
			$this->tpl->draw('nporeport/nporeport');
		}
		
		// npo register user list
		private function NpoRegistered() {
			EnPException :: writeProcessLog('NpoReport_Controller :: NpoRegistered action to view npo Details');
			
			$this->Init();
			$this->SetFilterParam();
			
			$DataArray = array(
				'SQL_CACHE nd.NPO_ID dId',
				'nd.NPO_Name dName',
				'nd.NPO_State sState',
				'ur.RegistrationDate uDate',
				'cr.NPO_CatName	cName');
			
			$NpoReportList = $this->NpoReport->GetAllNpo($DataArray, $this->filterParam);
			//dump($NpoReportList);
			$PagingArr = constructPaging($this->NpoReport->pageSelectedPage, $this->NpoReport->totalRecord, $this->NpoReport->pageLimit);		
			$LastPage = ceil($this->NpoReport->totalRecord / $this->NpoReport->pageLimit);
			
			$this->tpl->assign('totalRecords', $this->NpoReport->totalRecord);
			$this->tpl->assign('NpoReportList', $NpoReportList);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			$this->tpl->assign('pageNumber', $pageSelected);
			$this->tpl->assign('fromDate', $this->arrayInputData['fromDate']);
			$this->tpl->assign('toDate', $this->arrayInputData['toDate']);
			$this->tpl->draw('nporeport/nporegisteredlist');
		}
		
		// npo payment collected
		private function NpoPaymentCollected() {
			EnPException :: writeProcessLog('NpoReport_Controller :: NpoPaymentCollected action to view npo Details');
			
			$this->tpl->draw('nporeport/npopaymentcollectedlist');
		}
		
		// create csv file
		private function CreateCsvFile($headerArr) {
			EnPException :: writeProcessLog('NpoReport_Controller :: CreateCsvFile action called.');
			$fp = fopen($this->ExportCSVFileName, 'w+');
			if($fp) {
				$stringArray = implode(",", $headerArr) . "\r\n";
				fwrite($fp, $stringArray);
			}
		}
		
		// download csv file
		public function downloadfile($title='registered_npo_') {
			EnPException :: writeProcessLog('NpoReport_Controller :: downloadfile action called.');
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
		}
		
		// export report data to csv file
		private function ExportNpoReport() {
			EnPException :: writeProcessLog('NpoReport_Controller :: ExportNpoReport action called.');
			
			$this->GetExportConstant();
			$CsvData = $this->GetCsvData();
			
			if(count($CsvData) == 0) {
				$this->SetStatus(0, 'ECSV01');
				redirect(URL . 'nporeport/index/npo-registered');
				return;
			}
			
			$csvHeader = array('Date', 'NPO Name', 'NPO Category', 'NPO State');
			if($this->CurrentCsvPosition == 0)
				$this->CreateCsvFile($csvHeader);
			
			$fp = fopen($this->ExportCSVFileName, 'a+');
			
			foreach($CsvData as $val) {
				$val['uDate'] = formatDate($val['uDate'], 'm/d/Y');
				$val['cName'] = ExplodeLang($val['cName']);
				fputcsv($fp, $val);		
				$this->TotalRowProcessed++;
			}
			setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
			
			fclose($fp);
			$this->ViewRedirectExpCsv();		
		}
		
		// get export constant
		private function GetExportConstant() {
			//$this->NpoReport->isExport = 1;
			
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$this->fromDate = getSession('arrCsvExp', 'fromDate');
			$this->toDate = getSession('arrCsvExp', 'toDate');
			
			$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
			$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
			
			$this->NpoReport->CurrentCsvPosition = $this->CurrentCsvPosition;
		}
		
		// prepare data from table to export into csv
		private function GetCsvData() {
			
			$this->Init();
			$this->SetFilterParam();
			
			$DataArray = array(
				'SQL_CACHE ur.RegistrationDate uDate',
				'nd.NPO_Name dName',
				'cr.NPO_CatName	cName',
				'nd.NPO_State sState');
			
			return $this->NpoReport->GetAllNpo($DataArray, $this->filterParam);
		}
		
		// export progress bar
		public function ViewRedirectExpCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$this->fromDate = getSession('arrCsvExp', 'fromDate');
			$this->toDate = getSession('arrCsvExp', 'toDate');
			
			$totalRows = $this->NpoReport->totalRecord;
			if($this->CurrentCsvPosition >= $totalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
				unsetSession("filterInput");
			}
			
			$totalper =(int)(($this->CurrentCsvPosition / $totalRows) * 100);
			$this->tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$this->tpl->assign('totalPer', $totalper);
			$this->tpl->assign('Pstatus', $this->P_status);
			$this->tpl->draw('nporeport/exportstatus');
		}
		
		// initialization of values
		private function Init() {
			EnPException :: writeProcessLog('Nporeport_Controller :: Init() called.');
			
			$pageSelected = request('get', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
				
			$this->NpoReport->pageSelected = $pageSelected;
			
			$filterData = unserialize(keyDecrypt(getSession('filterInput')));
			if(is_array($filterData) && count($filterData) > 0) {
				$fromDate = $filterData['fromDate'];
				$toDate = $filterData['toDate'];
			} else {
				$fromDate = request('get', 'fromDate', 0);
				$toDate = request('get', 'toDate', 0);
			}
						
			$this->arrayInputData  = array(
				'fromDate' => $fromDate,
				'toDate' => $toDate);
				
			if(getSession('filterInput') == '' && $this->NpoReport->isExport == 1)
				setSession('filterInput', keyEncrypt(serialize($this->arrayInputData)));
		}
				
		// set filter parameters to appand sql query
		private function SetFilterParam() {
			EnPException :: writeProcessLog('Nporeport_Controller :: Init() called.');
				
			$fromDate = $this->arrayInputData['fromDate'] == '' ? getNextDate(30,'-') : $this->arrayInputData['fromDate'];
			$toDate = $this->arrayInputData['toDate'] == '' ? getDateTime(0,'Y-m-d') : $this->arrayInputData['toDate'];
			$this->arrayInputData['fromDate'] = formatDate($fromDate, 'Y-m-d');
			$this->arrayInputData['toDate'] = formatDate($toDate, 'Y-m-d');
			
			$this->filterParam = " AND DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')>='" . $this->arrayInputData['fromDate'] . "' AND DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')<='" . $this->arrayInputData['toDate'] . "' ";
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