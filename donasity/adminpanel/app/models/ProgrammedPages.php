<?php
 
class ProgrammedPages_Model extends Model {
		
	public $PMV_id, $PMV_metaTitle, $PMV_metaDesc, $PMV_metaKeyword, $PMV_googleAnaltics, $PMV_IsEditableText, $PMV_pageKeyword, $PMV_pagename, $PMV_desc, $PMT_id, $PMT_PMVid, $PMT_serial, $PMT_caption, $PMT_desc, $PMT_content, $limit, $sp, $P_recordCount, $selectedPage, $P_where, $Field, $Criteria, $Search, $P_ErrorMessage, $P_ErrorCode, $P_status, $P_ConfirmCode, $P_ConfirmMsg, $PMT_PMVIdsArr, $PMT_IdsArr, $PMT_CaptionArr, $PMT_ContentsArr, $PMT_DescArr, $OrderBy, $OrderByMethod, $P_sp, $pageLimit, $pageSelectedPage, $TotalCount, $P_selectedPage, $P_limit;

	function __construct() {
		$this->P_status = 1;
		$this->P_sp = 0;
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->OrderByMethod = 'ASC';
	}
	
	public function CheckDuplicacyForPageValue($condition='',$searchField) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: CheckDuplicacyForPageValue function called');
		$sql = "SELECT PMV_pagenameEN FROM " . TBLPREFIX . "pagemetavalue";
		return $row = db :: get_all($sql . $searchField . $condition);
	}
		
	public function UpdateMetaValues_DB($DataArray,$PageID) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: UpdateMetaValues_DB function called');
		db :: update(TBLPREFIX . 'pagemetavalue', $DataArray, "PMV_id = " . $PageID);
		return db :: is_row_affected() ? 1 : 0;
	}
		
	public function MetaValuesInsertMethod_DB($Table, $DataArray) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: MetaValuesInsertMethod_DB function called');
		db :: insert($Table, $DataArray);
		return db :: get_last_id();
	}
		
	public function PageMetaTextInsertMethod_DB($Table, $DataArray) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: PageMetaTextInsertMethod_DB function called');
		db :: insert($Table, $DataArray);
		return db::get_last_id();
	}
		
	public function DeleteAdditionalContent($Where) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: DeleteAdditionalContent function called');
		$Status = 0;
		if(db :: delete(TBLPREFIX . "pagemetatext", $Where))
			$Status = 1;
			
		return $Status;
	}
	
	public function getPageMetaValue($field = array(), $filterparam = array(), $arraySortParam = array()) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: getPageMetaValue function called');
		
		$fieldString = implode(', ', $field);
		$filterString = " Where 1 ";
		
		foreach($filterparam as $key => $row) {
			$cond = '';
			switch($key) {
				default :
				$cond = "$key='$row'";
			}
			$filterString .= " AND ( $cond ) ";
		}
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " limit " . $limit . ", " . $this->pageLimit;
		
		$Sql = "Select $fieldString FROM " . TBLPREFIX . "pagemetavalue PMV";
		if(isset($order) && $order != NULL)
			$sql_res = db :: get_all($Sql . $filterString . $order . $limit);
		else
			$sql_res = db :: get_all($Sql . $filterString . $limit);
			
		$this->TotalCount = db :: count($Sql . $filterString);
		return count($sql_res) > 0 ? $sql_res[0] : array();
	}
		
	public function getPageMetaText($field = array(), $filterparam = array(), $arraySortParam = '') {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: getPageMetaText function called');
		
		$fieldString = implode(', ', $field);
		$filterString = " Where 1 ";
		
		foreach($filterparam as $key => $row) {
			$cond = '';
			switch($key) {
				default :
				$cond = "$key='$row'";
			}
			$filterString .= " AND ( $cond ) ";
		}
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " limit " . $limit . ", " . $this->pageLimit;
		
		$Sql = "Select $fieldString FROM " . TBLPREFIX . "pagemetatext PMT";
			
		if(isset($arraySortParam) && $arraySortParam != NULL)
			$sql_res = db :: get_all($Sql . $filterString . $arraySortParam . $limit);
		else
			$sql_res = db :: get_all($Sql . $filterString . $limit);
		
		$this->TotalCount = db :: count($Sql . $filterString);
		return count($sql_res) > 0 ? $sql_res : array();
	}
		
	public function GetProgrammedPageList() {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: GetProgrammedPageList Function To Get Listing of Meta Pages');
		
		$this->P_where = '';
		
		if($this->Search <> '')
			$this->P_where = " WHERE " . $this->Field . " LIKE '%" . $this->Search . "%' OR PMV_pageKeyword ='" . $this->Search . "'";
		else
			$this->P_where = "WHERE 1 ";
					
		$sql = "SELECT SQL_CACHE SQL_CALC_FOUND_ROWS count(PMT.PMT_PMVid) as PMT_PMVIds, PMT.PMT_PMVid, PMV.PMV_id, PMV.PMV_pagenameEN FROM " . TBLPREFIX . "pagemetavalue PMV LEFT JOIN " . TBLPREFIX . "pagemetatext as PMT on PMT.PMT_PMVid=PMV.PMV_id " . $this->P_where . " group by PMV.PMV_id ";
		
		 $this->P_sp = $this->P_limit * ($this->P_selectedPage - 1);
		 $order = $this->OrderBy == '' ? "Order By PMV.PMV_pagenameEN" : " Order By " . $this->OrderBy;
		 $order = $order . " " . $this->OrderByMethod;
		 $limit = " limit " . $this->P_sp . ", " . $this->P_limit;
		 
		 if(db :: count($sql) > 0) {
			$this->P_recordCount = db :: count($sql);
			$sql_res = db :: get_all($sql . $order . $limit);
		 } else 
			$sql_res = array();
			
		return $sql_res;
	}
		
	public function PageMetaTextUpdate_DB($Table, $Field, $where) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: PageMetaTextUpdate_DB Function To Get Listing of Meta Pages');
		$update = "update $Table SET $Field " . $where;
		db :: query($update);
		return db :: is_row_affected() ? 1 : 0;
	}
		
	private function getfieldName($field) {
		EnPException :: writeProcessLog('ProgrammedPages_Model :: getfieldName function called');
		foreach($field as $key => &$row) {
			switch($row) {
				default :
				$row = "$row";
			}
		}
		return $field;
	}
}
?>