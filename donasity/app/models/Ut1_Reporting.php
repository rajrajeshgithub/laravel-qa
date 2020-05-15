<?php
	class Ut1_Reporting_Model extends Model {
		public $DateString = NULL, $Keyword = NULL, $Sort = NULL, $TaxExempted = NULL, $StartDate, $EndDate;
		public $Condition, $SortOrder;
		public $PageSelected, $PageLimit;
		public $ResultArray = array(), $ResultCount;
		public $LoginUserDetails, $LoginUserID, $Type;
		public $sortfrom = '', $sortto, $PddID, $Month, $Year;
		
		//form
		public $rp_staus, $rp_cycle, $rp_keyword, $where = '', $UpdatableDataArray = array(), $rpId = 0;
		function __construct() {
			$this->PageLimit = 100;
			$this->LoginUserDetails	= getSession('Users');
		}
		
		public function GetDonationDetails($DataArray) {
			$this->ManageFilter();
			$Fields	= implode(",", $DataArray);
			$Sql = "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails PDD LEFT JOIN " . TBLPREFIX . "npodetails ND ON(PDD.PDD_NPOEIN = ND.NPO_EIN) INNER JOIN " . TBLPREFIX . "registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID) LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
			
			$Where = " WHERE 1 AND PDD.PDD_PIItemType IN ('NPOD','CD') AND PT.PT_PaymentStatus=1 ";
			
			if(isset($this->PaymentType) and $this->PaymentType <> '')
				$Where .= " AND PDD.PDD_PaymentType=".$this->PaymentType;
			
			/// controller that draws /v2.html for all users and also the UT1 dashboard uses this same function, so if userid is needed within query send it via controller
			if ($this->LoginUserID <> '')
				$Where .= " AND PDD.PDD_RUID=" . $this->LoginUserID;	
			
			if($this->Condition <> '')
				$Where .= $this->Condition;
			
			$GroupBY = " GROUP BY PDD.PDD_ID";			
			//echo $this->LoginUserID.'  '.$Sql . $Where . $GroupBY;exit;			
			$Res = db::get_all($Sql . $Where . $GroupBY . $this->SortOrder);	
			$this->ResultArray = (count($Res) > 0) ? $Res : array();
			$this->ResultCount = db::countWithSQL($Sql . $Where . $GroupBY . $this->SortOrder);
			//dump($this->ResultArray);
			return $this->ResultArray;
		}
		
		public function GetDonationFundDetails($DataArray) {
			$Fields	= implode(",", $DataArray);
			$Sql = "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails PDD INNER JOIN " . TBLPREFIX . "registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID)					LEFT JOIN " .TBLPREFIX."campaign C ON(C.Camp_ID=PDD.PDD_CampID)					LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
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
			
			//	
			//$Where = '';
			
			$GroupBY = " GROUP BY PDD.PDD_ID";
			if(isset($this->SortOrder) && $this->SortOrder != '')
				$this->SortOrder = " ORDER BY " . $this->SortOrder;
				
			//echo $Sql . $Where . $GroupBY . $this->SortOrder;exit;
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
					$this->PddID =  implode(",", $this->PddID);
					
				$this->Condition .= " AND PDD.PDD_ID IN(" . $this->PddID . ")";
				$this->StartDate = '';
				$this->EndDate = '';
			}
			//end of code
				
			//for view all page
			if(isset($this->Month) && isset($this->Year) && $this->Year != '') {
				if(is_array($this->Month))
					$this->Month = implode(",", $this->Month);
					
				$this->Condition .= " AND (MONTH(PDD.PDD_DateTime) IN(" . $this->Month . ") AND YEAR(PDD.PDD_DateTime) = '" . $this->Year . "')";
			} else {
				if($this->StartDate != '' && $this->EndDate != '')
					$this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='" . $this->StartDate . "' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '" . $this->EndDate . "')";
			}	
			
			if($this->Keyword != NULL){
				$this->Condition .= " AND (PDD.PDD_NPOEIN LIKE '%" . $this->Keyword . "%' OR PDD.PDD_PIItemName LIKE '%" . $this->Keyword . "%' OR PDD.PDD_PD_ID LIKE '%" . $this->Keyword . "%' OR PT.PT_PaymentGatewayTransactionID LIKE '%" . $this->Keyword . "%')";	
			}
			
			if($this->TaxExempted != NULL)
				$this->Condition .= " AND (PDD.PDD_TaxExempt = '" . $this->TaxExempted . "')";
			
			if(isset($this->SortOrder) && $this->SortOrder != '')
				$this->SortOrder = " ORDER BY " . $this->SortOrder;

		}
		
		public function GetRecurringTrans() {
			
			$DataArray = array('rp.RP_StartDate', 'rp.RP_RUID', 'rp.RP_RecurringCycle', 'rp.RP_RecurringAmount', 'rp.RP_Status', 'rp.RP_RecurringProfileID', 'rp.RP_RecurringCustomerID', 'pdd.PDD_PIItemName');
			$Fields	= implode(",", $DataArray);
			
			$Sql = "SELECT $Fields FROM `" . TBLPREFIX ."recuringprofiles` rp LEFT JOIN `" . TBLPREFIX ."purchasedonationdetails` pdd ON pdd.PDD_ID = rp.RP_PDDID"; 

			$this->GenerateRTWhere();
			$GroupBY = "";
			
			//echo $Sql . $this->where . $GroupBY . $this->SortOrder;exit;
			$this->ResultCount = db::count($Sql . $this->where);
			
			if($this->ResultCount > 0)
				$this->ResultArray = db::get_all($Sql . $this->where . $GroupBY . $this->SortOrder);
			
			return $this->ResultArray;
		}
		
		// set where clause
		private function GenerateRTWhere() 
		{
			$this->LoginUserID = keyDecrypt($this->LoginUserDetails['UserType1']['user_id']);
			$this->where .= " WHERE RP_Status != 0 AND RP_RUID =" . $this->LoginUserID;
			if(isset($this->rp_staus) && $this->rp_staus!='')
			{
				if($this->rp_staus==1)
				{
					$this->where .=" AND (rp.RP_Status>0 AND rp.RP_Status<11)";
				}
				else if($this->rp_staus==2)
				{
					$this->where .=" AND (rp.RP_Status>10 AND rp.RP_Status<21)";
				}
			}
			if(isset($this->rp_cycle) && $this->rp_cycle!='')
			{
				if($this->rp_cycle==1)
				{
					$this->where .=" AND rp.RP_RecurringCycle='Monthly'";
				}
				else if($this->rp_cycle==2)
				{
					$this->where .=" AND rp.RP_RecurringCycle='Quaterly'";
				}
				else if($this->rp_cycle==3)
				{
					$this->where .=" AND rp.RP_RecurringCycle='Half Yearly'";
				}
				else if($this->rp_cycle==4)
				{
					$this->where .=" AND rp.RP_RecurringCycle='Yearly'";
				}
			}
			if(isset($this->rp_keyword) && $this->rp_keyword!='')
			{
				$this->where .=" AND pdd.PDD_PIItemName LIKE '%".$this->rp_keyword."%'";
			}
			
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
		
		public function GetDonorCount() {
			//$Sql = "SELECT PDD_ID FROM ".TBLPREFIX."purchasedonationdetails ";
			
			$Sql = "SELECT PDD_ID FROM " . TBLPREFIX . "purchasedonationdetails PDD LEFT JOIN " . TBLPREFIX . "npodetails ND ON(PDD.PDD_NPOEIN = ND.NPO_EIN) LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
			
			$Where = " WHERE 1 AND PDD.PDD_Status=11 AND PT.PT_PaymentStatus=1 ";			
			$Where .= $this->Condition;	
			//echo $Sql . $Where; exit;
			//$Res = db::get_all($Sql . $Where);
			$TotalDonors		=	db::count($Sql . $Where);		
			return $TotalDonors;
		}
	}
?>