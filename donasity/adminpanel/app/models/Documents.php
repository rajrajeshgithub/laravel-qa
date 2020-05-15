<?php
class Documents_Model extends Model {
	public $pageLimit, $pageSelectedPage, $totalRecord, $result;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->result = array();
	}

	// get all documents list from table
	public function GetAllDocuments($field=array(), $filterparam=array(),$arraySortParam=array(), $pagenation=NULL) {
		EnPException :: writeProcessLog('Documents_Model :: GetAllDocuments function called');
		
		$field = $this->getfieldName($field);
		$fieldString = implode(' , ', $field);			
		$filterString = " Where 1 ";
		
		if(count($filterparam) > 0) {
			foreach($filterparam as  $key => $row) {
				$cond = '';
				switch($key) {
					default:
					$cond = "$key=$row";
				}
				$filterString .= " AND ( $cond ) ";
			}
		}
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "documents";
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " LIMIT " . $limit . "," . $this->pageLimit;
		$orderBy = " ORDER BY LastUpdatedDate DESC, DocSorting DESC ";
		
		$this->totalRecord = db :: count($Sql . $filterString);
		
		if($this->totalRecord > 0)
			$this->result =	db :: get_all($Sql . $filterString . $orderBy . $limit);
		
		return $this->result;
	}
	
	// insert document data into table and return inserted id
	public function DocumentInsert_DB($DataArray) {
		EnPException :: writeProcessLog('Documents_Model :: DocumentInsert_DB function called');
		//dump($DataArray);
		db :: insert(TBLPREFIX . 'documents', $DataArray);
		return db :: get_last_id();
	}
	
	// update document data in table
	public function DocumentUpdate_DB($DataArray, $Doc_ID) {
		EnPException :: writeProcessLog('Documents_Model :: DocumentUpdate_DB function called for id=' . $Doc_ID);
		db :: update(TBLPREFIX . 'documents', $DataArray, 'DocID=' . $Doc_ID);
		return db :: is_row_affected() ? 1 : 0;
	}
	
	// delete document details from table
	public function DocumentDelete_DB($Doc_ID) {
		EnPException :: writeProcessLog('Documents_Model :: DocumentDelete_DB function called for id=' . $Doc_ID);
		
		return db :: delete(TBLPREFIX . 'documents', 'DocID=' . $Doc_ID);	
	}
	
	// update log process for document
	public function UpdateProcessLog($DataArray) {
		EnPException :: writeProcessLog('Documents_Model :: updateProcessLog function called');
		
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
	
	// get fields name to select from table
	private function getfieldName($field) {
		EnPException :: writeProcessLog('Documents_Model :: getfieldName function called');
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