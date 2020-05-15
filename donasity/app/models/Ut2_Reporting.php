<?php
class Ut2_Reporting_Model extends Model {
	public $DateString = NULL, $Keyword = NULL, $Sort = NULL, $TaxExempted = NULL, $StartDate, $EndDate;
	public $Condition, $SortOrder;
	public $PageSelected, $PageLimit;
	public $ResultArray, $ResultCount;
	public $LoginUserDetails, $LoginUserID;
	public $NPOEIN, $NPOID;
	public $Month, $Year;
	public $PddID;
	public $LoggedUserID, $Type;
	public $NPO_Name;
	public $SortTO, $SortFrom;
	
	function __construct() {
		$this->PageLimit = 100;
		$this->LoginUserDetails	= getSession('Users');
		$this->LoginUserID = keyDecrypt($this->LoginUserDetails['UserType2']['user_id']);
	}
	
	
	public function GetDonationDetails($DataArray,$Where=NULL,$Limit=NULL)
		{			
			$this->ManageFilter();
			$theLimit='';
			if($Limit!=''){$theLimit=$Limit;}
			
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails PDD  INNER JOIN ".TBLPREFIX."registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID) LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
			
			$Where	= " WHERE 1=1 AND PDD.PDD_PIItemType IN ('NPOD','CD') AND PT.PT_PaymentStatus=1";
			if ($this->NPOEIN<>"")
			$Where	.=" AND PDD.PDD_NPOEIN=".$this->NPOEIN;			
					
			if($this->Condition <> "")
			$Where	.=$this->Condition;
			$GroupBY	= " GROUP BY PDD.PDD_ID";
			//echo '<font color="white">'.$Sql.$Where.$GroupBY.$this->SortOrder.$theLimit;exit;   // line 145 which is -----$this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='" . $this->StartDate . "' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '" . $this->EndDate . "')"; ------was commented out to make initial dashboard load show all date range donations, not  merely this month
			$Res	= db::get_all($Sql.$Where.$GroupBY.$this->SortOrder.$theLimit);
			
			$this->ResultArray	=(count($Res) > 0)?$Res:array();
			
			$this->ResultCount = db::countWithSQL($Sql.$Where.$GroupBY.$this->SortOrder);
			return $this->ResultArray;
		}
		
		
	/********** bug where all donations for all NPOs are displayed 
	public function GetDonationDetails($DataArray, $Where = NULL) {
		
		$this->ManageFilter();
		
		$Fields	= implode(",", $DataArray);
		$Sql = "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails PDD INNER JOIN " . TBLPREFIX . "registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID) LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
		if($Where == NULL)			
			$Where = " WHERE 1 AND PDD.PDD_PIItemType IN ('NPOD','CD') AND PT.PT_PaymentStatus=1";
		
		if ($this->NPOEIN <> '' && $Where == NULL)
			$Where .= " AND PDD.PDD_NPOEIN=" . $this->NPOEIN;
		
		if($this->Condition <> '')
			$Where .= $this->Condition;
			
		$GroupBY = " GROUP BY PDD.PDD_ID";
		//dump($Sql . $Where . $GroupBY . $this->SortOrder);
		$Res = db::get_all($Sql . $Where . $GroupBY . $this->SortOrder);
		
		$this->ResultArray =(count($Res) > 0) ? $Res : array();
		
		$this->ResultCount = db::countWithSQL($Sql . $Where . $GroupBY . $this->SortOrder);
		return $this->ResultArray;
	}
	*********************/
	
	public function GetDonationFundDetails($DataArray) {
		
		$Fields	= implode(",", $DataArray);
		$Sql = "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails PDD INNER JOIN " . TBLPREFIX . "registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID) LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
		$Where = " WHERE 1 AND PDD.PDD_PIItemType IN ('CD') AND PT.PT_PaymentStatus=1";
		
		if($this->TaxExempted != '')
			$this->Condition .= " AND (PDD.PDD_TaxExempt = '" . $this->TaxExempted . "')";
		
		if(isset($this->Month)  && isset($this->Year) && $this->Year != '') {
			if(is_array($this->Month))
				$this->Month = implode(",", $this->Month);
				
			$this->Condition .= " AND (MONTH(PDD.PDD_DateTime) IN(" . $this->Month . ") AND YEAR(PDD.PDD_DateTime) = '" . $this->Year . "')";
		} else {
			if($this->StartDate != '' && $this->EndDate != '') {
				$this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='" . $this->StartDate . "' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '" . $this->EndDate . "')";	
			}
		}	
		
		if($this->Keyword != NULL) {
			$this->Condition .= " AND (RU.RU_FistName LIKE '%" . $this->Keyword . "%' OR RU.RU_LastName LIKE '%" . $this->Keyword . "%' OR RU.RU_EmailID LIKE '%" . $this->Keyword . "%')";						 
		}
		
		if($this->Condition <> '')
			$Where .= $this->Condition;
			
		$GroupBY = " GROUP BY PDD.PDD_ID";
		
		if(isset($this->SortOrder) && $this->SortOrder != '')
			$this->SortOrder = " ORDER BY " . $this->SortOrder;
			
		//dump($Sql . $Where . $GroupBY . $this->SortOrder);
		$Res = db::get_all($Sql . $Where . $GroupBY . $this->SortOrder);
		$this->ResultArray =(count($Res) > 0) ? $Res : array();
			
		$this->ResultCount = db::countWithSQL($Sql . $Where . $GroupBY . $this->SortOrder);
		return $this->ResultArray;
	}
	
	public function ManageDateString() {
		if($this->DateString != NULL) {
			$DateString	= explode("||", $this->DateString);
			$this->StartDate = $DateString[0];
			$this->EndDate = $DateString[1];	
		} else {
			$this->StartDate = date('Y-m-01');
			$this->EndDate = date('Y-m-t');		
		}
	}
	
	private function ManageFilter() {
		$this->ManageDateString();
		//for print
		if(isset($this->PddID)) {
			if(is_array($this->PddID))
				$this->PddID =  implode(",",$this->PddID);
				
			$this->Condition .= " AND PDD.PDD_ID IN(" . $this->PddID . ")";
			$this->StartDate = '';
			$this->EndDate = '';
		}
		//end of code
		
		//for view all page
		if(isset($this->Month)  && isset($this->Year) && $this->Year!='') {
			if(is_array($this->Month))
				$this->Month =  implode(",",$this->Month);
				
			$this->Condition .= " AND (MONTH(PDD.PDD_DateTime) IN(" . $this->Month . ") AND YEAR(PDD.PDD_DateTime) = '" . $this->Year . "')";
		} else {
			if($this->StartDate != '' && $this->EndDate != '') {
				// $this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='" . $this->StartDate . "' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '" . $this->EndDate . "')";	
				// this line was making dashboard only show this month's donations, which is not working for folks, want to make it show last 50 not excluding any date
			}
		}	
		
		if($this->Keyword != NULL) {
			$this->Condition .= " AND (RU.RU_FistName LIKE '%" . $this->Keyword . "%' OR RU.RU_LastName LIKE '%" . $this->Keyword . "%' OR RU.RU_EmailID LIKE '%" . $this->Keyword . "%')";						 
		}
		
		if($this->TaxExempted != NULL){
			$this->Condition .= " AND (PDD.PDD_TaxExempt = '" . $this->TaxExempted . "')";			
		}
		
		$this->SortOrder = " ORDER BY " . $this->SortOrder;
	}
	
	private function SetStatus($Status, $Code) {
		if($Status) {
			$messageParams = array(
				"msgCode"	=> $Code,
				"msg"		=> "Custom Confirmation message",
				"msgLog"	=> 0,									
				"msgDisplay"=> 1,
				"msgType"	=> 2);
				EnPException::setConfirmation($messageParams);
		} else {
			$messageParams = array(
				"errCode"	=> $Code,
				"errMsg"	=> "Custom Confirmation message",
				"errOriginDetails" => basename(__FILE__),
				"errSeverity"=> 1,
				"msgDisplay"=> 1,
				"msgType"	=> 1);
				EnPException::setError($messageParams);
		}
	}
}
?>