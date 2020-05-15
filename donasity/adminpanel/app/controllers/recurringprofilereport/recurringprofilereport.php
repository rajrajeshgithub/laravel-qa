<?php
	class Recurringprofilereport_Controller extends Controller {
		
		public $P_status,$Page_Selected,$ExportCSVFileName;
		//export report parameters
		public $TotalRowProcessed = 0, $CurrentCsvPosition = 0;
		
		function __construct() {
			checkLogin(20);
			$this->load_model('Recurringprofilereport', 'objReports');
			$this->objReports = new Recurringprofilereport_Model();
			$this->P_status = 1;
			$this->ExportCSVFileName = EXPORT_CSV_PATH."recurringprofilereport.csv";
		}
		
		public function index($type='list', $reportsID=NULL) {
			
			$this->tpl = new view;
			switch(strtolower($type)) {
				case 'export-reports' :
					$this->ExportCSVFile();
				break;
				default :
					$this->Listing();
					$this->tpl->draw('recurringprofilereport/reports');
					break;		
			}
		}
		
		//list all recurring profile report
		private function Listing() {
			EnPException :: writeProcessLog('Reports_Controller :: Listing action to view payment Details');
			
			$this->filterParameterLists();
			
			$this->objReports->getReportdata();
			$PagingArr = constructPaging($this->objReports->pageSelectedPage, $this->objReports->totalRecord, $this->objReports->pageLimit);
			$LastPage = ceil($this->objReports->totalRecord / $this->objReports->pageLimit);
			
			
			$this->tpl->assign('totalRecords', $this->objReports->totalRecord);
			$this->tpl->assign('recurringprofilelist', $this->objReports->results);
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			
			/* filter secxtion assinment */
			$this->tpl->assign("RecurringProfiles", $RecurringProfiles);
			if($this->objReports->rp_staus=='')
			{
				$this->tpl->assign("status", '1');
			}
			else
			{
				$this->tpl->assign("status", $this->objReports->rp_staus);
			}
			$this->tpl->assign("cycle", $this->objReports->rp_cycle);
			$this->tpl->assign("keyword", $this->objReports->rp_keyword);
				
			/* end of code */
			
			
		}
		
		private function filterParameterLists() {
			
		$pageSelected = (int)request('get', 'pageNumber', 1);
		$currentstatus = request('get', 'status', 0);
		if($currentstatus=='')
		{
			$this->objReports->rp_staus    = '1';
		}
		else
		{
			$this->objReports->rp_staus    = request('get', 'status', 0);
		}
		$this->objReports->rp_cycle    = request('get', 'cycle', 0);
		$this->objReports->rp_keyword  = request('get', 'keyword', 0);
		$this->objReports->pageSelectedPage	= $pageSelected == 0 ? 1 : $pageSelected;
		}
		
		// export report data to csv file
		public function ExportCSVFile() {
			
			$this->filterParameterLists();
			
			$this->GetExportConstant();
			
			$this->objReports->getReportdata();
			if(count($this->objReports->results) == 0) {
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
			
			foreach($this->objReports->results as $val) {
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
			$this->ViewRedirectExpCsv();		
		}
		
		private function GetExportConstant() {
			$this->objReports->isExport = 1;
			
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			
			$this->CurrentCsvPosition = (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition == '') ? 0 : $this->CurrentCsvPosition;
			$this->TotalRowProcessed = (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '') ? 0 : $this->TotalRowProcessed;
			
			$this->objReports->CurrentCsvPosition = $this->CurrentCsvPosition;
		}
		
		public function ViewRedirectExpCsv() {
			$this->TotalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
			$this->CurrentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
			
			$TotalRows = $this->objReports->totalRecord;
			
			if($this->CurrentCsvPosition >= $TotalRows) {
				$this->P_status = 0;
				unsetSession("arrCsvExp");
			}
			
			$totalper =(int)(($this->CurrentCsvPosition / $TotalRows) * 100);
			$tpl = new view;
			$tpl->assign('rowProcessed', $this->TotalRowProcessed);
			$tpl->assign('totalPer', $totalper);
			$tpl->assign('Pstatus', $this->P_status);
			$tpl->draw("recurringprofilereport/exportstatus");
		}
		
		private function CreateCsvFile() {
			$fp = fopen($this->ExportCSVFileName, 'w+');
			if($fp) {
				$HeaderArr	= array(
					"Cause Name",
					"Start Date",
					"Amount",
					"Cycle",
					"Status"
				);
				$StringArray = implode(",", $HeaderArr)."\r\n";
				fwrite($fp, $StringArray);
			}
		}
		
		public function downloadfile($title='recurringprofilereport') {
			$path = EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$dFile = new Download_file();
			$dFile->Downloadfile($path, "recurringprofilereport.csv", $title);
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