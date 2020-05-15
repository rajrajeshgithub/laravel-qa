<?PHP
class NposProcess_Model extends Model
{
	public $ErrorMessage,$ErrorCode,$ConfirmCode,$ConfirmMsg,$MsgType=2;
	public $ErrorCodes='',$P_Status=1;
	public $TotalCountNPOs;
	
	public function InsertNPOsData($Query)
	{
		db::query($Query);
	}
	
	public function GetNPOsList($DataArray,$LimitStr)
	{
		$fields	= implode(',',$DataArray);
		$Sql	= "SELECT * FROM ".TBLPREFIX."npodetails ORDER BY NPO_ID";
		$Res	= db::get_all($Sql.$LimitStr);
		$this->TotalCountNPOs	= db::count($Sql);
		return (count($Res)>0)?$Res:array();	
	}
	
	private function setErrorMsg($ErrCode,$MsgType=1,$P_Status=0)
	{
		EnPException::writeProcessLog('NPOs_Model :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$this->ErrorCode.=$ErrCode.",";
		$this->ErrorMessage=$ErrCode;
		$this->P_Status=$P_Status;
		$this->MsgType=$MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode,$MsgType=2,$P_Status=1)
	{
		EnPException::writeProcessLog('NPOs_Model :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
		$this->ConfirmCode=$ConfirmCode;
		$this->ConfirmMsg=$ConfirmCode;
		$this->P_Status=$P_Status;
		$this->MsgType=$MsgType;
	}	
}
?>