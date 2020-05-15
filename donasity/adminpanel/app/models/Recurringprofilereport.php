<?php
class Recurringprofilereport_Model extends Model {
	
	public $pageLimit, $pageSelectedPage, $totalRecord, $results, $isExport, $CurrentCsvPosition = 0, $TotalRowProcessed = 0, $EndCsv = 0, $filePath, $fileSize, $dataHeadarr, $dataCsvArr=array(), $CsvLimit = 200, $ExpCsvLimit = 50;
	public $rp_staus, $rp_cycle, $rp_keyword, $where = '';
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->results = array();
		$this->where = '';
		$this->isExport = 0;
	}
	
	// get all donation payment details from table
	public function getReportdata($filterparam=array(), $arraySortParam=array(), $pagenation=NULL) {
		EnPException :: writeProcessLog('Reports_Model :: DailyDonation function called');
		
		$DataArray = array('rp.RP_StartDate', 'rp.RP_RUID', 'rp.RP_RecurringCycle', 'rp.RP_RecurringAmount', 'rp.RP_Status', 'rp.RP_RecurringProfileID', 'rp.RP_RecurringCustomerID', 'pdd.PDD_PIItemName');
		$Fields	= implode(",", $DataArray);
			
		$Sql = "SELECT $Fields FROM `" . TBLPREFIX ."recuringprofiles` rp LEFT JOIN `" . TBLPREFIX ."purchasedonationdetails` pdd ON pdd.PDD_ID = rp.RP_PDDID";
		$this->GenerateRTWhere();
		$GroupBY = "";
			
		$this->totalRecord = db::count($Sql . $this->where);
			
		if($this->totalRecord > 0)
		{
			if($this->isExport == 0)
				$limit = $this->SetPageLimit();
			
			if($this->isExport == 1)
				$limit = $this->SetExportPageLimit();
			$this->results = db::get_all($Sql . $this->where . $GroupBY . $limit);
		}	
		return $this->results;
	}
	
	// set where clause
	private function GenerateRTWhere()
	{
		$this->where .= " WHERE RP_Status != 0";
		if($this->rp_staus!='')
		{
			if($this->rp_staus==1)
			{
				$this->where .=" AND (rp.RP_Status>0 AND rp.RP_Status<11)";
			}
			else if($this->rp_staus==2)
			{
				$this->where .=" AND (rp.RP_Status>10 AND rp.RP_Status<21)";
			}
			else if($this->rp_staus==0)
			{
				$this->where .=" AND (rp.RP_Status>0 AND rp.RP_Status<11 OR rp.RP_Status>10 AND rp.RP_Status<21)";
			}
		}
		if(isset($this->rp_cycle) && $this->rp_cycle!='')
		{
			if($this->rp_cycle==1)
			{
				$this->where .=" AND rp.RP_RecurringCycle='Monthly'";
			}
			else if($this->rp_cycle==2)
			{
				$this->where .=" AND rp.RP_RecurringCycle='Quaterly'";
			}
			else if($this->rp_cycle==3)
			{
				$this->where .=" AND rp.RP_RecurringCycle='Half Yearly'";
			}
			else if($this->rp_cycle==4)
			{
				$this->where .=" AND rp.RP_RecurringCycle='Yearly'";
			}
		}
		if(isset($this->rp_keyword) && $this->rp_keyword!='')
		{
			$this->where .=" AND pdd.PDD_PIItemName LIKE '%".$this->rp_keyword."%'";
		}
			
	}
	// set page limit
	private function SetPageLimit() {
		EnPException :: writeProcessLog('Reports_Model :: SetPageLimit function called');
		$limit = '';
		if($this->pageLimit != '' && $this->pageSelectedPage != '') {
			$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
			$limit = " LIMIT " . $limit . ", " . $this->pageLimit;
		}
		return $limit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		EnPException :: writeProcessLog('Reports_Model :: SetExportPageLimit function called');
		$limit = '';
		$limit = " LIMIT " . $this->CurrentCsvPosition . ", " . $this->ExpCsvLimit;
		return $limit;
	}
}
?>