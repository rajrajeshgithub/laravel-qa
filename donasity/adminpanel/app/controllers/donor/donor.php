<?php
	class Donor_Controller extends Controller {
		
		public $tpl, $fromDate, $toDate, $currentDate, $filterParam = '', $adminId = 0, $deleted = '';
		//export
		public $TotalRowProcessed = 0, $CurrentCsvPosition = 0, $ExportCSVFileName, $P_status = 1;
		
		function __construct() {
			//dump(getsession("DonasityAdminLoginDetail"));
			checkLogin(24);
			$this->tpl = new view;
			$this->load_model('Donor', 'donor');
			$this->fromDate = date('m/d/Y', strtotime('today - 30 days'));
			$this->toDate = formatDate(getDateTime(), 'm/d/Y');
			$this->currentDate = $this->toDate;
			$loginDetails = getsession("DonasityAdminLoginDetail");
			$this->adminId = $loginDetails['admin_id'];
			$this->ExportCSVFileName = EXPORT_CSV_PATH . 'registered_donor_'. $this->adminId .'.csv';
		}
		
		public function index($type='list') {
			EnPException :: writeProcessLog('Donor_Controller :: index action called with request - ' . $type);
			
			switch(strtolower($type)) {
				case 'list':
					$this->Summary();
				break;
				case 'donorrecuringsetup':
					//$this->Listing();
					//$this->tpl->draw('donar/donarrecuringsetup');
				break;
				case 'donor-registered':
					$this->DonorRegistered();
				break;
				case 'export-donor-report' :
					$this->ExportDonorReport();
				break;
				default :
					$this->Summary();
				break;		
			}
		}
		
		// list all documents
		private function Summary() {
			EnPException :: writeProcessLog('Donor_Controller :: Summary action to view npo Details');
			
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
			
			//$this->filterParam = " WHERE DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(ur.RegistrationDate,'%Y-%m-%d')<='$this->toDate' ";
			
			//$npoSummary = $this->NpoReport->GetSummary($this->filterParam);
			
			//$this->tpl->assign('npoSummary', $npoSummary);
			$this->tpl->assign('fromDate', $fromDate);
			$this->tpl->assign('toDate', $toDate);
			$this->tpl->draw('donor/donor');
		}
		
		// npo register user list
		private function DonorRegistered() {
			EnPException :: writeProcessLog('Donor_Controller :: DonorRegistered action to view donor Details');
			
			//page selected value
			$pageSelected = request('post', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
				
			$this->donor->pageSelectedPage = $pageSelected;
			
			$fromDate = request('post', 'fromDate', 0);
			$toDate = request('post', 'toDate', 0);
			//dump($fromDate);
			// set filter param
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
			
			$this->filterParam = " AND DATE_FORMAT(ru.RU_RegDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(ru.RU_RegDate,'%Y-%m-%d')<='$this->toDate' ";
			
			$this->deleted = request('post', 'deletedDonor', 0);
			if($this->deleted == 'deleted')
				$this->filterParam .= " AND ru.RU_Deleted = '1' ";
			
			//dump($this->filterParam);
			$DataArray = array(
				'SQL_CACHE ru.RU_FistName fName',
				'ru.RU_LastName lName',
				'ru.RU_RegDate regDate',
				'ru.RU_State uState');
				
			$donorList = $this->donor->GetAllDonor($DataArray, $this->filterParam);
			//dump($DonorList);
			$PagingArr = constructPaging($this->donor->pageSelectedPage, $this->donor->totalRecord, $this->donor->pageLimit);		
			$LastPage = ceil($this->donor->totalRecord / $this->donor->pageLimit);
			
			$this->tpl->assign('totalRecords', $this->donor->totalRecord);
			$this->tpl->assign('donorList', $donorList);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			$this->tpl->assign('pageNumber', $pageSelected);
			$this->tpl->assign('fromDate', $fromDate);
			$this->tpl->assign('toDate', $toDate);
			$this->tpl->assign('deleted', $this->deleted);
			$this->tpl->draw('donor/donorregistered');
		}
		
		// create csv file
		private function CreateCsvFile($headerArr) {
			EnPException :: writeProcessLog('Donor_Controller :: CreateCsvFile action called.');
			$fp = fopen($this->ExportCSVFileName, 'w+');
			if($fp) {
				$stringArray = implode(",", $headerArr) . "\r\n";
				fwrite($fp, $stringArray);
			}
		}
		
		// download csv file
		public function downloadfile($title='registered_donor_') {
			EnPException :: writeProcessLog('Donor_Controller :: downloadfile action called.');
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
		}
		
		// export report data to csv file
		private function ExportDonorReport() {
			EnPException :: writeProcessLog('Donor_Controller :: ExportNpoReport action called.');
					
			$this->GetExportConstant();
			$CsvData = $this->GetCsvData();
			//dump($CsvData);
			if(count($CsvData) == 0) {
				$this->SetStatus(0, 'ECSV01');
				redirect(URL . 'donor/index/donor-registered');
				return;
			}
			
			$csvHeader = array('Date', 'Donor Name', 'Donor State', 'NPO Category');
			if($this->CurrentCsvPosition == 0)
				$this->CreateCsvFile($csvHeader);
				
			$fp = fopen($this->ExportCSVFileName, 'a+');
			foreach($CsvData as $val) {
				$val['regDate'] = formatDate($val['regDate'], 'm/d/Y');
				//$val['cName'] = ExplodeLang($val['cName']);
				fputcsv($fp, $val);		
				$this->TotalRowProcessed++;
			}
			
			setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
			setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
			setSession('arrCsvExp', $this->toDate, 'toDate');
			setSession('arrCsvExp', $this->fromDate, 'fromDate');
			setSession('arrCsvExp', request('post', 'deletedDonor', 0), 'deletedDonor');
			
			fclose($fp);
			$this->ViewRedirectExpCsv();		
		}
		
		// get export constant
		private function GetExportConstant() {
			$this->donor->isExport = 1;
			
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			$this->toDate = getSession('arrCsvExp', 'toDate');
			$this->fromDate = getSession('arrCsvExp', 'fromDate');
			$this->deleted = getSession('arrCsvExp', 'deletedDonor');
			//dump($this->CurrentCsvPosition);
			$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
			$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
			
			$this->donor->CurrentCsvPosition = $this->CurrentCsvPosition;
		}
		
		// prepare data from table to export into csv
		private function GetCsvData() {
			$DataArray = array(
				"SQL_CACHE ru.RU_RegDate regDate",
				"CONCAT(ru.RU_FistName, ' ', ru.RU_LastName) FullName",
				"ru.RU_State uState",
				"ru.RU_ID Npocat");
			
			$fromDate = request('post', 'fromDate', 0);
			$toDate = request('post', 'toDate', 0);
			
			// set filter param
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
			
			$this->filterParam = " AND DATE_FORMAT(ru.RU_RegDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(ru.RU_RegDate,'%Y-%m-%d')<='$this->toDate' ";
			
			$this->deleted = request('post', 'deletedDonor', 0);
			if($this->deleted == 'deleted')
				$this->filterParam .= " AND ru.RU_Deleted = '1' ";
			
			return $this->donor->GetAllDonor($DataArray, $this->filterParam);
		}
		
		// export progress bar
		public function ViewRedirectExpCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			//dump($this->CurrentCsvPosition);
			$totalRows = $this->donor->totalRecord;
			if($this->CurrentCsvPosition >= $totalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
			
			$totalper = (int)(($this->CurrentCsvPosition / $totalRows) * 100);
			$this->tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$this->tpl->assign('totalPer', $totalper);
			$this->tpl->assign('Pstatus', $this->P_status);
			$this->tpl->draw('donor/exportstatus');
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