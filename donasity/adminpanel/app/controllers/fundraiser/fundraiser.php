<?PHP
class Fundraiser_Controller extends Controller {
	
	public $tpl, $fundraiserId = 0, $filterParam, $sortParam, $selectedPage, $adminId = 0, $fromDate = '', $toDate = '', $currentDate;
	//export
	public $TotalRowProcessed = 0, $CurrentCsvPosition = 0, $ExportCSVFileName, $ExportFundraiserFile, $P_status = 1, $fund_status = '';
	
	function __construct() {
		checkLogin(23);
		$this->load_model('Fundraiser', 'Fundraiser');
		$this->tpl = new view;
		$loginDetails = getsession("DonasityAdminLoginDetail");
		$this->adminId = $loginDetails['admin_id'];
		$this->ExportCSVFileName = EXPORT_CSV_PATH . 'fundraiser_summary_'. $this->adminId .'.csv';
		$this->ExportFundraiserFile = EXPORT_CSV_PATH . 'fundraiser_details_'. $this->adminId .'.csv';
		/*if(file_exists($this->ExportCSVFileName))		
			unlink($this->ExportCSVFileName);
		if(file_exists($this->ExportFundraiserFile))		
			unlink($this->ExportFundraiserFile);*/
		$this->fromDate = date('m/d/Y', strtotime('today - 30 days'));
		$this->toDate = formatDate(getDateTime(), 'm/d/Y');
		$this->currentDate = $this->toDate;
	}
	
	public function index($type='list', $campStatus='') {
		switch(strtolower($type)) {
			case 'list' :
				$this->Fundraiser();
				break;
			/*case 'view' :
				if($campStatus != '') {
					$campStatus = keyDecrypt($campStatus);
					if($this->FundraiserStatus($campStatus) != '')
						$this->View($campStatus);
					else {
						$this->SetStatus(0, 'E2001');
						redirect(URL.'fundraiser');
					}
				} else {
					$this->SetStatus(0, 'E2001');
					redirect(URL.'fundraiser');
				}
				break;*/
			case 'export-summary' :
				$this->ExportSummary();
				break;
			/*case 'export-fundraiser' :
				$this->ExportFundraiser();
				break;*/
			default :
				$this->Fundraiser();
				break;
		}
	}
	
	// list fundraiser summery
	private function Fundraiser() {
		EnPException :: writeProcessLog('Fundraiser_Controller :: Fundraiser action called.');
		
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
			
		/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
			$fromDate = $this->fromDate;
			$toDate = $this->toDate;
		}
		
		if($toDate > $this->currentDate)
			$toDate = $this->currentDate;
			
		$this->fromDate = formatDate($fromDate, 'Y-m-d');
		$this->toDate = formatDate($toDate, 'Y-m-d');*/
		
		$dataArray = array('count(Camp_Status) AS status_count', 'Camp_Status');
		$filterparam = " AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')<='$this->toDate' AND Camp_Deleted!='1' AND Camp_Status IN(0,1,2,5,6,11,15,21) GROUP BY Camp_Status ";
		
		$fundraisers = $this->Fundraiser->GetFundraiser($dataArray, $filterparam);
		//dump($fundraisers);
		$this->Fundraiser->toDate = $this->toDate;
		$this->Fundraiser->fromDate = $this->fromDate;
		$ending2Week = $this->Fundraiser->Get2WeekFundraiser();
		
		$arrStatus['Initiated'] = array('status_count'=>0, 'Camp_Status'=>'0');
		$arrStatus['Step 1 completed'] = array('status_count'=>0, 'Camp_Status'=>'1');
		$arrStatus['Step 2 completed'] = array('status_count'=>0, 'Camp_Status'=>'2');
		$arrStatus['Step 5 completed'] = array('status_count'=>0, 'Camp_Status'=>'5');
		$arrStatus['Setup completed'] = array('status_count'=>0, 'Camp_Status'=>'6');
		$arrStatus['Waiting for verification'] = array('status_count'=>0, 'Camp_Status'=>'11');
		$arrStatus['Ended'] = array('status_count'=>0, 'Camp_Status'=>'15');
		$arrStatus['Stoped'] = array('status_count'=>0, 'Camp_Status'=>'21');
		
		foreach($fundraisers as $key => $arrValue) {
			if($arrValue['Camp_Status'] == '0')
				$arrStatus['Initiated'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '1')
				$arrStatus['Step 1 completed'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '2')
				$arrStatus['Step 2 completed'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '5')
				$arrStatus['Step 5 completed'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '6')
				$arrStatus['Setup completed'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '11')
				$arrStatus['Waiting for verification'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '15')
				$arrStatus['Ended'] = $arrValue;
			elseif($arrValue['Camp_Status'] == '21')
				$arrStatus['Stoped'] = $arrValue;
		}
		//dump($arrStatus);
		$this->tpl->assign('fundraisers', $arrStatus);
		$this->tpl->assign('ending2Week', $ending2Week['ending2Week']);
		$this->tpl->assign('totalRecords', $this->Fundraiser->totalRecord);
		$this->tpl->assign('fromDate', $fromDate);
		$this->tpl->assign('toDate', $toDate);
		$this->tpl->draw('fundraiser/fundraiser');
	}
	
	// list all 
	public function Report() {
		EnPException :: writeProcessLog('Fundraiser_Controller :: Report action to view fundraiser Details.');
		
		$campStatus = request('get', 'status', 0);
		if($campStatus != '') {
			$campStatus = keyDecrypt($campStatus);
			if($this->FundraiserStatus($campStatus) == '') {
				$this->SetStatus(0, 'E2001');
				redirect(URL.'fundraiser');
				return;
			}
		} else {
			$this->SetStatus(0, 'E2001');
			redirect(URL.'fundraiser');
			return;
		}
		//dump($campStatus);
		
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
		
		
		/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
			$fromDate = $this->fromDate;
			$toDate = $this->toDate;
		}
		
		if($toDate > $this->currentDate)
			$toDate = $this->currentDate;
			
		$this->fromDate = formatDate($fromDate, 'Y-m-d');
		$this->toDate = formatDate($toDate, 'Y-m-d');*/
		
		$hddncampStatus = keyEncrypt($campStatus);
		$statusTitle = $this->FundraiserStatus($campStatus);
		
		$dataArray = array(
			'SQL_CACHE C.Camp_ID AS campId',
			'C.Camp_StartDate AS startDate',
			'C.Camp_EndDate AS endDate',
			'C.Camp_Title AS fundraiser',
			'C.Camp_Status AS f_status',
			'C.Camp_UrlFriendlyName AS url',
			'C.Camp_CP_State AS state',
			'C.Camp_DonationGoal AS goal',
			'C.Camp_LastUpdatedDate AS lastUpdated',
			'CT.NPOCat_DisplayName_EN AS CatName',
			'CL.Camp_Level_Name AS campLevel');
		
		$ending2Week = "";
		if($campStatus == 'eitw') {
			$campStatus = '15';
			$ending2Week = "AND DATE_FORMAT(C.Camp_EndDate,'%Y-%m-%d') <= DATE_FORMAT(NOW(),'%Y-%m-%d') AND DATE_FORMAT(C.Camp_EndDate,'%Y-%m-%d') > DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL 14 DAY)";
		}
			
		$this->filterParam = " AND DATE_FORMAT(C.Camp_LastUpdatedDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(C.Camp_LastUpdatedDate,'%Y-%m-%d')<='$this->toDate' AND C.Camp_Deleted!='1' AND C.Camp_Status = '" . $campStatus . "'" . $ending2Week;
			
		$this->selectedPage = request('get', 'pageNumber', 0) == '' ? 1 : request('get', 'pageNumber', 0);
		$this->Fundraiser->selectedPage = $this->selectedPage;
		
		$fundraiserList = $this->Fundraiser->GetFundraiserStatus($dataArray, $this->filterParam);
		
		$PagingArr = constructPaging($this->Fundraiser->selectedPage, $this->Fundraiser->totalRecord, $this->Fundraiser->pageLimit);
		$lastPage = ceil($this->Fundraiser->totalRecord / $this->Fundraiser->pageLimit);
		$this->tpl->assign('totalRecords', $this->Fundraiser->totalRecord);
		$this->tpl->assign('fundraiserList', $fundraiserList);
		$this->tpl->assign('fundraiserStatus', $statusTitle);
		$this->tpl->assign('hddncampStatus', $hddncampStatus);
		$this->tpl->assign('pagingList', $PagingArr['Pages']);
		$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
		$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
		$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
		$this->tpl->assign('lastPage', $lastPage);
		$this->tpl->assign('pageNumber', $this->selectedPage);
		$this->tpl->assign('pageLimit', $this->Fundraiser->pageLimit);
		$this->tpl->assign('fromDate', $fromDate);
		$this->tpl->assign('toDate', $toDate);
		$this->tpl->draw('fundraiser/listfundraiser');
	}
	
	// fundraiser status
	private function FundraiserStatus($f_status) {
		$fundraiserStatus = '';
		switch($f_status) {
			case '0' :
				$fundraiserStatus = 'Initiated';
				break;
			case '1' :
				$fundraiserStatus = 'Step 1 completed';
				break;
			case '2' :
				$fundraiserStatus = 'Step 2 completed';
				break;
			case '5' :
				$fundraiserStatus = 'Step 5 completed';
				break;
			case '6' :
				$fundraiserStatus = 'Setup completed';
				break;
			case '11' :
				$fundraiserStatus = 'Waiting for verification';
				break;
			case '15' :
				$fundraiserStatus = 'Ended';
				break;
			case '21' :
				$fundraiserStatus = 'Stoped';
				break;
			case 'eitw' :
				$fundraiserStatus = 'Ending in Two Weeks';
				break;
		}
		return $fundraiserStatus;
	}
	
	// export fundraiser summary
	private function ExportSummary() {
		EnPException :: writeProcessLog('Fundraiser_Controller :: ExportSummary action called.');
		
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
		
		
		/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
			$fromDate = $this->fromDate;
			$toDate = $this->toDate;
		}
		
		if($toDate > $this->currentDate)
			$toDate = $this->currentDate;
			
		$this->fromDate = formatDate($fromDate, 'Y-m-d');
		$this->toDate = formatDate($toDate, 'Y-m-d');*/
		
		$dataArray = array('Camp_Status, count(Camp_Status) AS status_count');
		$filterparam = " AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')<='$this->toDate' AND Camp_Deleted!='1' AND Camp_Status IN(0,1,2,5,6,11,15,21) GROUP BY Camp_Status ";
		
		$fundraisers = $this->Fundraiser->GetFundraiser($dataArray, $filterparam);
		$this->Fundraiser->toDate = $this->toDate;
		$this->Fundraiser->fromDate = $this->fromDate;
		$ending2Week = $this->Fundraiser->Get2WeekFundraiser();
		
		$arrStatus['0'] = array('Camp_Status'=>'Initiated','status_count'=>0);
		$arrStatus['1'] = array('Camp_Status'=>'Step 1 completed', 'status_count'=>0);
		$arrStatus['2'] = array('Camp_Status'=>'Step 2 completed', 'status_count'=>0);
		$arrStatus['5'] = array('Camp_Status'=>'Step 5 completed', 'status_count'=>0);
		$arrStatus['6'] = array('Camp_Status'=>'Setup completed', 'status_count'=>0);
		$arrStatus['11'] = array('Camp_Status'=>'Waiting for verification', 'status_count'=>0);
		$arrStatus['15'] = array('Camp_Status'=>'Ended', 'status_count'=>0);
		$arrStatus['21'] = array('Camp_Status'=>'Stoped', 'status_count'=>0);
		$arrStatus['ending2Week'] = array('Camp_Status'=>'Ending in Two Weeks', 'status_count'=>$ending2Week['ending2Week']);
		
		foreach($fundraisers as $key => $arrValue) {
			if($arrValue['Camp_Status'] == '0')
				$arrStatus['0']['status_count'] = $arrValue['status_count'];
			if($arrValue['Camp_Status'] == '1')
				$arrStatus['1']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '2')
				$arrStatus['2']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '5')
				$arrStatus['5']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '6')
				$arrStatus['6']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '11')
				$arrStatus['11']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '15')
				$arrStatus['15']['status_count'] = $arrValue['status_count'];
			elseif($arrValue['Camp_Status'] == '21')
				$arrStatus['21']['status_count'] = $arrValue['status_count'];
		}
		$csvHeader = array("Status", "Count of fundraiser");
		$this->CreateCsvFile($csvHeader);
		$fp = fopen($this->ExportCSVFileName, 'a+');
		foreach($arrStatus as $val) {
			fputcsv($fp, $val);
		}
		
		$this->downloadfile('fundraiser_summary_');
	}
	
	// create csv file
	private function CreateCsvFile($headerArr) {
		EnPException :: writeProcessLog('Fundraiser_Controller :: CreateCsvFile action called.');
		$fp = fopen($this->ExportCSVFileName, 'w+');
		if($fp) {
			$stringArray = implode(",", $headerArr) . "\r\n";
			fwrite($fp, $stringArray);
		}
	}
	
	// create csv file for fundraiser details
	private function CreateFundraiserCsv($headerArr) {
		EnPException :: writeProcessLog('Fundraiser_Controller :: CreateCsvFile action called.');
		$fp = fopen($this->ExportFundraiserFile, 'w+');
		if($fp) {
			$stringArray = implode(",", $headerArr) . "\r\n";
			fwrite($fp, $stringArray);
		}
	}
	
	// download csv file
	public function downloadfile($title='fundraiser_details_') {
		EnPException :: writeProcessLog('Fundraiser_Controller :: downloadfile action called.');
		LoadLib("Download_file");
		$dFile = new Download_file();
		$dFile->Downloadfile(EXPORT_CSV_PATH, $title . $this->adminId . '.csv', $title . $this->adminId);
	}
	
	// export report data to csv file
	public function ExportFundraiser() {
		EnPException :: writeProcessLog('Fundraiser_Controller :: ExportFundraiser action called.');		
		$fund_status = request('get', 'status', 0);
		$this->fund_status = $fund_status;
		
		if($fund_status == '' || $this->FundraiserStatus(keyDecrypt($fund_status)) == '') {
			$this->SetStatus(0, 'ECSV01');
			redirect(URL . 'fundraiser');
			return;
		}
				
		$this->GetExportConstant();
		
		$CsvDataByStatus = $this->GetCsvData(keyDecrypt($fund_status));
		
		if(count($CsvDataByStatus) == 0) {
			$this->SetStatus(0, 'ECSV01');
			redirect(URL . 'fundraiser/Report?status=' . $fund_status . '&from=$this->fromDate&to=$this->toDate');
			return;
		}
		
		$csvHeader = array('Date', 'Fundraiser', 'Start Date', 'End Date', 'State', 'Goal ($)', 'Category', 'Level');
		if($this->CurrentCsvPosition == 0)
			$this->CreateFundraiserCsv($csvHeader);
			
		$fp = fopen($this->ExportFundraiserFile, 'a+');
		//dump($CsvDataByStatus);
		foreach($CsvDataByStatus as $val) {
			$val['lastUpdated'] = formatDate($val['lastUpdated'], 'm/d/Y');
			if($val['lastUpdated'] == '00/00/0000')
				$val['lastUpdated'] = '';
			
			if($val['startDate'] != '0000-00-00')
				$val['startDate'] = formatDate($val['startDate'], 'm/d/Y');
			else
				$val['startDate'] = '';
				
			if($val['endDate'] != '0000-00-00')
				$val['endDate'] = formatDate($val['endDate'], 'm/d/Y');
			else
				$val['endDate'] = '';
				
			fputcsv($fp, $val);
			$this->TotalRowProcessed++;
		}
		
		setSession('arrCsvExp', $this->TotalRowProcessed, 'CURCSVPOS');
		setSession('arrCsvExp', $this->TotalRowProcessed, 'TOTALROWPROCESSED');
		setSession('arrCsvExp', $this->fund_status, 'fund_status');
		
		fclose($fp);
		$this->ViewRedirectExpCsv();		
	}
	
	// get export constant
	private function GetExportConstant() {
		$this->Fundraiser->isExport = 1;
		
		$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		
		$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
		$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
		
		$this->Fundraiser->CurrentCsvPosition = $this->CurrentCsvPosition;
	}
	
	// prepare data from table to export into csv
	private function GetCsvData($campStatus) {
		
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
		
		
		/*if($fromDate == '' || $toDate == '' || $fromDate >= $toDate) {
			$fromDate = $this->fromDate;
			$toDate = $this->toDate;
		}
		
		if($toDate > $this->currentDate)
			$toDate = $this->currentDate;
			
		$this->fromDate = formatDate($fromDate, 'Y-m-d');
		$this->toDate = formatDate($toDate, 'Y-m-d');*/
		
		$dataArray = array(
			'C.Camp_LastUpdatedDate AS lastUpdated',
			'C.Camp_Title AS fundraiser',
			'C.Camp_StartDate AS startDate',
			'C.Camp_EndDate AS endDate',
			'C.Camp_CP_State AS state',
			'C.Camp_DonationGoal AS goal',
			'CT.NPOCat_DisplayName_EN AS CatName',
			'CL.Camp_Level_Name AS campLevel');
		
		$ending2Week = "";
		if($campStatus == 'eitw') {
			$campStatus = '15';
			$ending2Week = " AND DATE_FORMAT(C.Camp_EndDate,'%Y-%m-%d') <= DATE_FORMAT(NOW(),'%Y-%m-%d') AND DATE_FORMAT(C.Camp_EndDate,'%Y-%m-%d') > DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL 14 DAY) ";
		}
			
		$this->filterParam = " AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')>='$this->fromDate' AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d')<='$this->toDate' AND C.Camp_Deleted!='1' AND C.Camp_Status = '" . $campStatus . "'" . $ending2Week;
		
		return $this->Fundraiser->GetFundraiserStatus($dataArray, $this->filterParam);
	}
	
	// export progress bar
	public function ViewRedirectExpCsv() {
		$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		$this->fund_status = getSession('arrCsvExp', 'fund_status');
		
		$totalRows = $this->Fundraiser->totalRecord;
		if($this->CurrentCsvPosition >= $totalRows) {
			$this->P_status = 0;
			unsetSession("arrCsvExp");
		}
		
		$totalper =(int)(($this->CurrentCsvPosition / $totalRows) * 100);
		$this->tpl->assign('rowProcessed', $this->TotalRowProcessed);
		$this->tpl->assign('totalPer', $totalper);
		$this->tpl->assign('Pstatus', $this->P_status);
		$this->tpl->assign('fund_status', $this->fund_status);
		$this->tpl->assign('fromDate', $this->fromDate);
		$this->tpl->assign('toDate', $this->toDate);
		$this->tpl->draw('fundraiser/exportstatus');
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