<?PHP
class Donationpayment_Model extends Model
{
	public $ErrorMessage,$ErrorCode,$ConfirmCode,$ConfirmMsg,$MsgType=2;
	public $ErrorCodes='',$P_Status=1;
	public $nposID,$TotalCountNPOs,$pageLimit,$pageSelectedPage;
	
	public function __construct()
	{
		$this->pageLimit=100;
		$this->pageSelectedPage=1;
		$this->TotalCountNPOs=0;
	}
	
	public function GetDonationPaymentList_DB($DataArray,$Cond)
	{
		$fields	= implode(',',$DataArray);
		
		$Sql	= "SELECT  $fields FROM ".TBLPREFIX."purchasedonationdetails PDD
				   LEFT JOIN ".TBLPREFIX."purchasedonation PD ON (PDD.PDD_PD_ID = PD.PD_ID)
				   LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PT.PT_ID = PDD.PDD_PaymentTransactionID)";
		$Order	= "";
		$StartIndex	= ($this->pageSelectedPage - 1) * $this->pageLimit;
		$Limit = " LIMIT " . $StartIndex . ", " . $this->pageLimit;
		
		$Res = db::get_all($Sql.$Cond.$Order.$Limit);
		//dump($Sql.$Cond.$Order.$Limit);
		$sqlCount = "SELECT count(PDD_ID) FROM ".TBLPREFIX."purchasedonationdetails PDD
					 LEFT JOIN ".TBLPREFIX."purchasedonation PD ON (PDD.PDD_PD_ID = PD.PD_ID)
				   	LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PT.PT_ID = PDD.PDD_PaymentTransactionID)";
		
		$this->TotalCountNPOs = db::countWithSQL($sqlCount.$Cond);
		return (count($Res)>0)?$Res:array();
	}
	
	
	public function GetDonationPaymentExportList($DataArray,$LimitStr)
	{
		$Condition	= getSession('arrCsvExp','DONATIONPAYMENTCONDITION');
		$Condition	= unserialize($Condition);
		$fields	= implode(',',$DataArray);
		$Sql	= "SELECT  $fields FROM ".TBLPREFIX."purchasedonationdetails PDD
				   LEFT JOIN ".TBLPREFIX."purchasedonation PD ON (PDD.PDD_PD_ID = PD.PD_ID)
				   LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PT.PT_ID = PDD.PDD_PaymentTransactionID)";
		$Order	= " ";
		$Res	= db::get_all($Sql.$Condition.$Order);
		$this->TotalCountNPOs	= db::count($Sql.$Condition);
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