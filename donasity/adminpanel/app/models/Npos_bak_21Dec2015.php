<?PHP
class Npos_Model extends Model {
	public $ErrorMessage, $ErrorCode, $ConfirmCode, $ConfirmMsg, $MsgType=2;
	public $ErrorCodes='', $P_Status=1;
	public $nposID, $TotalCountNPOs, $pageLimit, $pageSelectedPage;
	
	public function __construct() {
		$this->pageLimit = 100;
		$this->pageSelectedPage = 1;
		$this->TotalCountNPOs = 0;
	}
	
	public function UpdateNPOs_DB($DataArray, $NPOID) {
		return db::update(TBLPREFIX . 'npodetails', $DataArray, "NPO_ID=" . $NPOID);
	}
	
	public function GetNPOsDetailDB($DataArray, $Condition) {
		$Fields	= implode(",", $DataArray);
		$Sql	= "SELECT $Fields FROM " . TBLPREFIX . "npodetails";
		$Res	= db::get_row($Sql . $Condition);
		return ($Res) ? $Res : array();
	}
	
	public function GetDeductionModeDB() {
		$Sql	= "SELECT DISTINCT NPO_DedCode,NPO_DedDescription FROM `dns_npodetails` where NPO_DedCode <> '' and NPO_DedDescription <> ''";
		$row	= db::get_all($Sql);
		return (count($row) > 0) ? $row : array();
	}
	
	public function GetNPOsList_DB($DataArray, $Cond) {
		$fields	= implode(',', $DataArray);
		
		$Sql = "SELECT $fields FROM " . TBLPREFIX . "npodetails N LEFT JOIN " . TBLPREFIX . "npocategoryrelation CR ON (CR.NPO_CategoryName=N.NPO_CD)";
		$Order = " ORDER BY N.NPO_ID";
		$StartIndex	= ($this->pageSelectedPage - 1) * $this->pageLimit;
		$Limit = " LIMIT " . $StartIndex . ", " . $this->pageLimit;
		
		$Res = db::get_all($Sql . $Cond . $Order . $Limit);
		
		$sqlCount = "SELECT count(NPO_ID) FROM " . TBLPREFIX . "npodetails N LEFT JOIN " . TBLPREFIX . "npocategoryrelation CR ON (CR.NPO_CategoryName=N.NPO_CD)";
		
		$this->TotalCountNPOs = db::countWithSQL($sqlCount.$Cond);
		
		return (count($Res) > 0) ? $Res : array();
	}
	
	public function CheckEINDuplicacyDB($EIN, $NPOID) {
		$WHERE	= " WHERE NPO_EIN=" . $EIN;
		if($NPOID > 0)
			$WHERE .= " AND NPO_ID <> " . $NPOID;
			
		$Sql = "SELECT NPO_ID FROM " . TBLPREFIX . "npodetails";
		$Row = db::get_row($Sql . $WHERE);
		return ($Row['NPO_ID'] > 0) ? false : true;
	}
	
	public function GetUserDetailsDB($DataArray, $EIN) {
		$Fields	= implode(",", $DataArray);
		$Sql = "SELECT $Fields FROM " . TBLPREFIX . "npouserrelation NUR LEFT JOIN " . TBLPREFIX . "registeredusers RU ON (NUR.USERID=RU.RU_ID) WHERE NUR.NPOEIN=" . $EIN . " AND NUR.Active='1'";
		$Res = db::get_all($Sql);
		return (count($Res) > 0) ? $Res : array();	
	}
	
	public function GetBankDetailsDB($DataArray, $EIN) {
		$Fields	= implode(",", $DataArray);
		$Sql	= "SELECT $Fields FROM " . TBLPREFIX . "npobankdetails WHERE NPO_BD_EIN=" . $EIN;
		$Res	= db::get_all($Sql);
		return (count($Res) > 0) ? $Res : array();	
	}
	
	public function GetContactDetailsDB($DataArray, $EIN) {
		$Fields	= implode(",", $DataArray);
		$Sql	= "SELECT $Fields FROM " . TBLPREFIX . "npocontactdetails WHERE NPO_CD_EIN=" . $EIN;
		$Res	= db::get_all($Sql);
		return (count($Res) > 0) ? $Res : array();
	}
	
	public function GetCityDB() {
		$Sql = "SELECT DISTINCT NPO_City FROM `dns_npodetails` where NPO_City <> ''";
		$row = db::get_all($Sql);
		return (count($row) > 0) ? $row : array();	
	}
	
	public function InsertNPOsDB($DataArray) {
		db::insert(TBLPREFIX."npodetails", $DataArray);
		return db::get_last_id();
	}
	
	public function GetStates() {
		$Sql = "SELECT State_Name,State_Value FROM " . TBLPREFIX . "states WHERE State_Country='US'";	
		$Res = db::get_all($Sql);
		return (count($Res) > 0) ? $Res : array();
	}
	
	public function GetNPODetail($NPOID) {
		$Sel = "SELECT NPO_EIN,NPO_Name,NPO_State FROM " . TBLPREFIX . "npodetails WHERE NPO_ID=" . $NPOID;
		$Row	= db::get_row($Sel);
		return $Row;	
	}
	
	public function SaveUniqueCode($DataArray, $NPOID) {
		return db::update(TBLPREFIX . "npodetails", $DataArray, "NPO_ID=" . $NPOID);	
	}
	
	public function IsDuplicateCode($UniqueCode) {
		$Sel = "SELECT NPO_ID FROM " . TBLPREFIX . "npodetails WHERE NPO_UniqueCode='" . $UniqueCode . "'";
		$Row = db::get_row($Sel);
		return (isset($Row['NPO_ID'])) ? true : false;	
	}
	
	private function setErrorMsg($ErrCode,$MsgType=1,$P_Status=0) {
		EnPException::writeProcessLog('NPOs_Model :: setErrorMsg Function To Set Error Message => ' . $ErrCode);
		$this->ErrorCode .= $ErrCode . ",";
		$this->ErrorMessage = $ErrCode;
		$this->P_Status = $P_Status;
		$this->MsgType = $MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode,$MsgType=2,$P_Status=1) {
		EnPException::writeProcessLog('NPOs_Model :: setConfirmationMsg Function To Set Confirmation Message => ' . $ConfirmCode);
		$this->ConfirmCode = $ConfirmCode;
		$this->ConfirmMsg = $ConfirmCode;
		$this->P_Status = $P_Status;
		$this->MsgType = $MsgType;
	}
}
?>