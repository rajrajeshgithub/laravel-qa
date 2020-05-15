<?PHP
class NposProcess_Model extends Model {
	public $ErrorMessage, $ErrorCode, $ConfirmCode, $ConfirmMsg, $MsgType=2;
	public $ErrorCodes='', $P_Status=1, $TotalCountNPOs;
	
	public function InsertNPOsData($Query) {
		db::query($Query);
	}
	
	/*public function GetNPOsList($DataArray,$LimitStr)
	{
		$Condition	= getSession('arrCsvExp','NPOCONDITION');
		$Condition	= unserialize($Condition);
		$fields	= implode(',',$DataArray);
		$Sql	= "SELECT $fields FROM ".TBLPREFIX."npodetails N
				   LEFT JOIN ".TBLPREFIX."npocategoryrelation CR ON (CR.NPO_CategoryName=N.NPO_SubSectionName)
				   LEFT JOIN ".TBLPREFIX."npocategories C ON (C.NPOCat_ID=CR.NPOCat_ID)"; 
		$Order	= " ORDER BY N.NPO_ID";//echo $Sql.$Condition.$Order.$LimitStr;exit;
		//$Res	= db::get_all($Sql.$Condition.$Order.$LimitStr);
		$Res	= db::get_all($Sql.$Condition.$Order);
		$this->TotalCountNPOs	= db::count($Sql.$Condition);
		return (count($Res)>0)?$Res:array();	
	}*/
	
	public function GetNPOsList($DataArray, $LimitStr) {
		$Condition = getSession('arrCsvExp', 'NPOCONDITION');
		if($Condition != NULL && !is_array($Condition))
			$Condition = unserialize($Condition);
		else
			$Condition = '';
		
		$fields	= implode(',', $DataArray);
		$Sql = "SELECT $fields FROM " . TBLPREFIX . "npodetails N LEFT JOIN " . TBLPREFIX . "npocategoryrelation CR ON (CR.NPO_CategoryName=N.NPO_CD)"; 
		$Order = " ORDER BY N.NPO_ID";
		//dump($Sql . $Condition . $Order . $LimitStr, 0);
		$Res = db::get_all($Sql . $Condition . $Order . $LimitStr);
		
		$this->TotalCountNPOs = db::count($Sql . $Condition);
		return (count($Res) > 0) ? $Res : array();	
	}
	
	public function GetDistinctCategoryDB() {
		$Sql = "SELECT DISTINCT NPO_SubSectionName FROM " . TBLPREFIX . "npodetails";
		$Res = db::get_all($Sql);
		return (count($Res) > 0) ? $Res : array();
	}
	
	public function NPOsCategoryInsertDB($QueryStr) {
		db::query($QueryStr);	
	}
	
	public function ExistNpostDetailRecordCount() {
		$Sql = "SELECT NPO_ID FROM " . TBLPREFIX . "npodetails";
		return db::count($Sql);
	}
	
	private function setErrorMsg($ErrCode, $MsgType=1, $P_Status=0) {
		EnPException::writeProcessLog('NPOs_Model :: setErrorMsg Function To Set Error Message => ' . $ErrCode);
		$this->ErrorCode .= $ErrCode . ",";
		$this->ErrorMessage = $ErrCode;
		$this->P_Status = $P_Status;
		$this->MsgType = $MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode, $MsgType=2, $P_Status=1) {
		EnPException::writeProcessLog('NPOs_Model :: setConfirmationMsg Function To Set Confirmation Message => ' . $ConfirmCode);
		$this->ConfirmCode = $ConfirmCode;
		$this->ConfirmMsg = $ConfirmCode;
		$this->P_Status = $P_Status;
		$this->MsgType = $MsgType;
	}
}
?>