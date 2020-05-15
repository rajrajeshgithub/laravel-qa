<?php
class NpoReport_Model extends Model {
	public $pageLimit, $pageSelectedPage, $totalRecord, $result, $fromDate, $toDate, $P_status = 1, $isExport = 0, $CurrentCsvPosition = 0, $TotalRowProcessed = 0, $EndCsv = 0, $dataHeadarr, $dataCsvArr=array(), $CsvLimit = 200, $ExpCsvLimit = 50;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->result = array();
	}
	
	public function GetSummary($filterString='') {
		EnPException :: writeProcessLog('NpoReport_Model :: GetSummary function called');
		
		$sql = "SELECT COUNT(nd.NPO_ID) registredNpo FROM " . TBLPREFIX . "npodetails nd INNER JOIN " . TBLPREFIX . "npouserrelation ur ON nd.NPO_ID = ur.NPOID AND ur.Active = '1'";
		//dump($sql . $filterString);
		$this->result =	db :: get_row($sql . $filterString);
		return $this->result;
	}

	// get all npo list from table
	public function GetAllNpo($field=array(), $filterparam='', $arraySortParam=array(), $pagenation=NULL) {
		EnPException :: writeProcessLog('NpoReport_Model :: GetAllNpo function called');
		
		//$field = $this->getfieldName($field);
		$fieldString = implode(', ', $field);			
		$filterString = " Where 1 " . $filterparam;
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "npodetails nd INNER JOIN " . TBLPREFIX . "npouserrelation ur ON nd.NPO_ID = ur.NPOID AND ur.Active = '1' LEFT JOIN " . TBLPREFIX . "npocategoryrelation cr ON nd.NPO_CD = cr.NPO_CategoryName";
		
		$limit = $this->isExport == 1 ? '' : $this->SetLimit();
		
		$limit = "";
		if($this->isExport == 0)
			$limit = $this->SetLimit();
			
		if($this->isExport == 1)
			$limit = $this->SetExportPageLimit();
		
		$orderBy = " ORDER BY ur.RegistrationDate DESC ";
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
		EnPException :: writeProcessLog('NpoReport_Model :: SetExportPageLimit function called');
		$limit = '';
		$limit = " LIMIT " . $this->CurrentCsvPosition . ", " . $this->ExpCsvLimit;
		return $limit;
	}
	
	// get fields name to select from table
	private function getfieldName($field) {
		EnPException :: writeProcessLog('NpoReport_Model :: getfieldName function called');
		foreach($field as $key => &$row) {
			switch($row) {
				default:
				$row = "$row";
			}
		}
		return $field;
	}
}

?>