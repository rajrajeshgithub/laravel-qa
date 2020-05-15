<?php
class Donor_Model extends Model {
	public $pageLimit, $pageSelectedPage, $totalRecord, $result, $fromDate, $toDate, $P_status = 1, $isExport = 0, $CurrentCsvPosition = 0, $TotalRowProcessed = 0, $EndCsv = 0, $dataHeadarr, $dataCsvArr=array(), $CsvLimit = 200, $ExpCsvLimit = 50;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->result = array();
	}

	// get all donor list from table
	public function GetAllDonor($field=array(), $filterparam='', $arraySortParam=array(), $pagenation=NULL) {
		EnPException :: writeProcessLog('Donor_Model :: GetAllDonor function called');
		
		$fieldString = implode(', ', $field);			
		$filterString = " Where 1 " . $filterparam;
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "registeredusers ru";
		
		$limit = '';
		if($this->isExport == 0)
			$limit = $this->SetLimit();
			
		if($this->isExport == 1)
			$limit = $this->SetExportPageLimit();
			
		$orderBy = " ORDER BY ru.RU_ID DESC ";
		//dump($Sql . $filterString . $orderBy . $limit);
		$this->totalRecord = db :: count($Sql . $filterString);
		
		if($this->totalRecord > 0)
			$this->result =	db :: get_all($Sql . $filterString . $orderBy . $limit);
		
		return $this->result;
	}
	
	// set page limit
	private function SetLimit() {
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " LIMIT " . $limit . "," . $this->pageLimit;
		return $limit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		EnPException :: writeProcessLog('Donor_Model :: SetExportPageLimit function called');
		$limit = '';
		$limit = " LIMIT " . $this->CurrentCsvPosition . ", " . $this->ExpCsvLimit;
		return $limit;
	}
}

?>