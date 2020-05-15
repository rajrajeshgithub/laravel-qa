<?php
class CompaignVideo_Model extends Model {
	public $pageLimit, $pageSelectedPage, $totalRecord, $Result;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->Result = array();
	}
	
	private function getfieldName($field) {
		EnPException::writeProcessLog('CompaignImages_Model :: getfieldName function called');
		foreach($field  as $key =>  &$row) {
			switch($row) {
				default:
				$row = "$row";
			}
		}
		return $field;
	}

	public function GetCompaignVideoListing($field=array(), $filterparam=array(), $arraySortParam=array(), $Pagenation=NULL) {
		
		EnPException::writeProcessLog('CompaignVideos_Model :: GetCompaignVideoListing function called');
		
		$field = $this->getfieldName($field);
		$fieldString = implode(' , ',$field);
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
		
		$Sql = "Select $fieldString from " . TBLPREFIX . "campaignvideo";
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " limit " . $limit . ", " . $this->pageLimit;
				
		$this->totalRecord = db::count($Sql . $filterString);
		if($this->totalRecord > 0) 
			$this->Result = db::get_all($Sql . $filterString . $limit);
		
		/*if(!count($sql_res)) {
			$sql_res = array();
			return $sql_res;
		}*/
		return $this->Result;
	}
	
	public function CVideoInsert_DB($DataArray) {
		EnPException::writeProcessLog('CompaignImages_Model :: CVideoInsert_DB function called');
		db :: insert(TBLPREFIX . 'campaignvideo', $DataArray);
		return db :: get_last_id();
	}
	
	public function UpdateCVideoInsert($DataArray, $CV_ID) {
		EnPException :: writeProcessLog('CompaignImages_Model :: UpdateCVideoInsert function called for id=' . $CV_ID);
		
		db :: update(TBLPREFIX . 'campaignvideo', $DataArray, "Camp_Video_ID = " . $CV_ID);
		return db :: is_row_affected() ? 1 : 0;
	}
	
	public function UpdateCVideo_DB($DataArray, $CV_ID) {
		EnPException :: writeProcessLog('CompaignImages_Model :: UpdateCImage_DB function called for id=' . $CV_ID);
		db :: update(TBLPREFIX . 'campaignvideo', $DataArray, "Camp_Video_ID = " . $CV_ID);
		return db::is_row_affected() ? 1 : 0;
	}
	
	public function DeleteVideo_DB($ComID) {
		EnPException :: writeProcessLog('CompaignImages_Model :: DeleteVideo_DB function called for id=' . $ComID);
		return db :: delete(TBLPREFIX . "campaignvideo", "Camp_Video_ID=". $ComID);
	}
	
	public function GetFileName_DB($field, $filterparam) {
		EnPException :: writeProcessLog('CompaignImages_Model :: GetFileName_DB function called');
		
		$field = $this->getfieldName($field);
		$fieldString = implode(' , ',$field);
		$filterString = " Where 1 ";
		
		if(count($filterparam) > 0) {
			foreach($filterparam as $key => $row) {
				$cond = '';
				switch($key) {
					default:
					$cond = "$key=$row";
				}
				$filterString .= " AND ( $cond ) ";
			}
		}
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "campaignvideo $filterString";
		return db::get_row($Sql);
	}
	public function updateProcessLog($DataArray)
	{
		
		$FieldArray = array("DateTime"=>$DataArray['Date'],
							"ModelName"=>$DataArray['Model'],
							"ControllerName"=>$DataArray['Controller'],
							"UserType"=>$DataArray['UType'],
							"UserName"=>$DataArray['UName'],
							"UserID"=>$DataArray['UID'] = ($DataArray['UID']!='')?$DataArray['UID']:0,
							"RecordID"=>$DataArray['RecordId'] = ($DataArray['RecordId']!='')?$DataArray['RecordId']:0,								
							"SortMessage"=>$DataArray['SMessage'],
							"LongMessage"=>$DataArray['LMessage'],);							
		
		db::insert(TBLPREFIX."processlog",$FieldArray);
		$id = db::get_last_id();
		$id = ($id)?$id:0;
		return $id;
	}
}
?>