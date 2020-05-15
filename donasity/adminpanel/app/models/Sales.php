<?php
class Sales_Model extends Model {
	public $pageLimit = 20, $pageSelected = 1, $totalRecord = 0, $result = array(), $fromDate, $toDate, $P_status = 1, $isExport = 0, $currentCsvPosition = 0, $totalRowProcessed = 0, $expCsvLimit = 50;
	
	public function __construct() {
		
	}

	// get all list from table
	public function GetSalesDetails($field=array(), $filterString='') {
		EnPException :: writeProcessLog('Sales_Model :: GetSalesDetails function called');
		
		$fieldString = implode(', ', $field);
		$filterString = " Where 1 " . $filterString;
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "campaign C LEFT JOIN " . TBLPREFIX . "campaignlevel CL ON C.Camp_Level_ID = CL.Camp_Level_CampID LEFT JOIN " . TBLPREFIX . "npocategories NPOCat ON C.Camp_Cat_ID = NPOCat.NPOCat_ID LEFT JOIN " . TBLPREFIX . "registeredusers RU ON C.Camp_RUID = RU.RU_ID";
		//dump($Sql . $filterString);
		$this->totalRecord = db :: count($Sql . $filterString);
		
		if($this->totalRecord > 0) {
			$limit = $this->isExport == 1 ? $this->SetExportPageLimit() : $this->SetLimit();
			$this->result =	db :: get_all($Sql . $filterString . $limit);
		}
		
		return $this->result;
	}
	
	// set page limit
	private function SetLimit() {
		return " LIMIT " . $this->pageLimit * ($this->pageSelected - 1) . "," . $this->pageLimit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		return " LIMIT " . $this->currentCsvPosition . ", " . $this->expCsvLimit;
	}
	
	// update log process
	public function UpdateProcessLog($DataArray) {
		EnPException :: writeProcessLog('Sales_Model :: UpdateProcessLog function called');
		
		$DataArray['UID'] = ($DataArray['UID'] != '') ? $DataArray['UID'] : 0;
		$DataArray['RecordId'] = ($DataArray['RecordId'] != '') ? $DataArray['RecordId'] : 0;
		
		$FieldArray = array(
			"DateTime"			=> $DataArray['Date'],
			"ModelName"			=> $DataArray['Model'],
			"ControllerName"	=> $DataArray['Controller'],
			"UserType"			=> $DataArray['UType'],
			"UserName"			=> $DataArray['UName'],
			"UserID"			=> $DataArray['UID'],
			"RecordID"			=> $DataArray['RecordId'],								
			"SortMessage"		=> $DataArray['SMessage'],
			"LongMessage"		=> $DataArray['LMessage']);							
		
		db :: insert(TBLPREFIX . "processlog", $FieldArray);
		$id = db :: get_last_id();
		$id = ($id) ? $id : 0;
		return $id;
	}
}

?>