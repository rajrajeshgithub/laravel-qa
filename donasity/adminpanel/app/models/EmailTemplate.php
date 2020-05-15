<?php
class EmailTemplate_Model extends Model {
	public $ETemplateTotalRecord, $pageLimit, $RightpageLimit, $pageSelectedPage, $MemberListTotalRecord;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->RightpageLimit = 3;
		$this->pageSelectedPage = 1;
		$this->MemberListTotalRecord = 0;
	}
	
	public function GetTemplateListing($field = array(), $filterparam = array(), $arraySortParam = array(), $Pagenation = NULL) {
		EnPException :: writeProcessLog('EmailTemplate_Model :: GetTemplateListing function called');
		
		$field = $this->getfieldName($field);
		$fieldString = implode(' , ', $field);			
		$filterString = " Where 1 ";
		if(isset($filterparam)) {
			foreach($filterparam as $key => $row) {
				$cond = '';
				switch($key) {
					case 'SearchCondtionLike' :
						$cond = " $row ";
					break;
					default:
						$cond = "$key=$row";
					break;
				}
				if(trim($cond) != '')
					$filterString .= " AND ( $cond ) ";
				else
					$filterString .= "";
			}
		}
		
		$Sql = "Select $fieldString from " . TBLPREFIX . "emailtemplate";
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " limit " . $limit . ", " . $this->pageLimit;
		
		$this->ETemplateTotalRecord = db :: count($Sql . $filterString);
		
		$sql_res = array();
		if($this->ETemplateTotalRecord > 0)
			$sql_res = db :: get_all($Sql . $filterString . $limit);
		
		if(!count($sql_res)) 
			$sql_res = array();
			
		return $sql_res;
	}
	
	public function UpdateMetaDetail_DB($DataArray, $TID) {
		EnPException :: writeProcessLog('EmailTemplate_Model :: UpdateEmail_DB function called');
		
		db :: update(TBLPREFIX . 'emailtemplate', $DataArray, "TemplateID = " . $TID);
		return db :: is_row_affected() ? 1 : 0;
	}
	
	public function CheckDuplicacyForPageValue($searchField, $condition = '') {
		EnPException :: writeProcessLog('EmailTemplate_Model :: CheckDuplicacyForPageValue function called');
		
		$sql = "SELECT TemplateName FROM " . TBLPREFIX . "emailtemplate";
		return $row = db :: get_all($sql . $searchField . $condition);
	}
	
	private function getfieldName($field) {
		EnPException::writeProcessLog('Member_Model :: getfieldName function called');
		
		foreach($field as $key => &$row) {
			switch($row) {
				default :
					$row = "$row";
				break;
			}
		}
		return $field;
	}
}
?>