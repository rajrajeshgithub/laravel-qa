<?php
class NpoUser_Model extends Model {
	
	public $pageLimit, $RightpageLimit, $pageSelectedPage, $MemberListTotalRecord, $NUTotalRecord, $isExport = 0, $currentCsvPosition = 0, $totalRowProcessed = 0, $expCsvLimit = 50, $totalRecord = 0;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->RightpageLimit = 3;
		$this->pageSelectedPage = 1;
		$this->MemberListTotalRecord = 0;
	}
	
	public function GetNPOUserListing($field = array(), $filterparam = array(), $arraySortParam = array(), $Pagenation = NULL) {
		EnPException::writeProcessLog('NpoUser_Model :: GetNPOUserListing function called');
		
		$field = $this->getfieldName($field);
		
		$fieldString = implode(' , ', $field);
		$filterString = " Where 1 ";
		
		if(count($filterparam) > 0) {
			foreach($filterparam as $key => $row) {
				$cond = '';
				switch($key) {
					case 'SearchCondtionLike' :
						$cond = " $row ";
					break;
					case 'RU_Status' :
						$cond = "$key='$row'";
					break;
					case 'RU_Deleted' :
						$cond = "$key='$row'";
					break;
					case 'Active' :
						$cond = "$key='$row'";
					break;
					default :
						$cond = "$key=$row";
					break;
				}
				$filterString .= " AND ( $cond ) ";
			}
		}
		
		$Sql = "Select $fieldString from " . TBLPREFIX . "npouserrelation NUR left join " . TBLPREFIX . "registeredusers RU ON NUR.USERID=RU.RU_ID LEFT JOIN " . TBLPREFIX . "npodetails ND ON ND.NPO_ID = NUR.NPOID";
		
		//$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		//$limit = " limit " . $limit . ", " . $this->pageLimit;
		//dump($Sql . $filterString . $limit);
		$this->NUTotalRecord = db :: count($Sql . $filterString);
		$sql_res = array();
		
		if($this->NUTotalRecord > 0) {
			$limit = $this->isExport == 1 ? $this->SetExportPageLimit() : $this->SetLimit();
			$sql_res = db :: get_all($Sql . $filterString . $limit);
		}
		
		if(!count($sql_res)) 
			$sql_res = array();
		
		return $sql_res;
	}
	
	// set page limit
	private function SetLimit() {
		return " LIMIT " . $this->pageLimit * ($this->pageSelectedPage - 1) . "," . $this->pageLimit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		return " LIMIT " . $this->currentCsvPosition . ", " . $this->expCsvLimit;
	}
	
	private function getfieldName($field) {
		EnPException :: writeProcessLog('NpoUser_Model :: getfieldName function called');
		
		foreach($field as $key => &$row) {
			switch($row) {
				default :
				$row = "$row";
			}
		}
		return $field;
	}
	
	public function getUserNpoDetails($field,$condition='')
	{
		$fieldString = implode(' , ', $field);
		$Sql = "Select $fieldString from " . TBLPREFIX . "registeredusers RU left join " . TBLPREFIX . "npouserrelation NUR ON NUR.USERID=RU.RU_ID LEFT JOIN " . TBLPREFIX . "npodetails ND ON ND.NPO_ID = NUR.NPOID where 1=1 ";	
		
		if($condition!='')
		$Sql  .= $condition;
		
		$res = db::get_row($Sql);
		$res = count($res>0)?$res:array();
		return $res;
	}
}
?>