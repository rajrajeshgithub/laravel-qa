<?php
	class AmbassadorPortal_Model extends Model
	{
		public $filter, $Keyword, $Month, $Year, $loginUserId, $DateString, $StartDate, $EndDate, $Condition, $result, $SortOrder, $ResultCount, $PddID, $TaxExempted, $Type, $LoggedUserID, $SortTO, $SortFrom, $userDetailsArray;
		public $GroupBY;
		
		function __construct() {
			$this->Month = array();
			$this->Keyword = '';
			$this->Year = '';
			$this->loginUserId = 0;
			$this->Condition = '';
			$this->result = array();
		}	
		
		public function GetDonationDetails($DataArray, $Where=NULL) {
			$this->ManageFilterDonation();
			
			$Fields	= implode(",", $DataArray);
			$Sql	= "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails PDD 
						INNER JOIN " . TBLPREFIX . "registeredusers RU ON(RU.RU_ID = PDD.PDD_RUID) LEFT JOIN " . TBLPREFIX . "paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";			
					
			if($this->Condition <> '')
				$Where .= $this->Condition;
			if($this->GroupBY=='')			
				$this->GroupBY = " GROUP BY PDD.PDD_ID";
			//echo ($Sql . $Where . $this->GroupBY . $this->SortOrder);exit;
			$this->result = db :: get_all($Sql . $Where . $this->GroupBY . $this->SortOrder);
			
			$this->ResultCount = db::countWithSQL($Sql . $Where . $this->GroupBY . $this->SortOrder);
			
			return $this->result;
		}
		
		private function ManageFilterDonation() {
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
			if(isset($this->Month)  && isset($this->Year) && $this->Year != '') {
				if(is_array($this->Month))
					$this->Month =  implode(",", $this->Month);
					
				$this->Condition .= " AND (MONTH(PDD.PDD_DateTime) IN(" . $this->Month . ") AND YEAR(PDD.PDD_DateTime) = '" . $this->Year . "')";
			}
			//end of code
			else {
				
				if($this->StartDate != '' && $this->EndDate != '') {
					$this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='".$this->StartDate . "' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '" . $this->EndDate . "')";	
				}
			}	
			
			if($this->Keyword != NULL) {
				$this->Condition .= " AND (RU.RU_FistName LIKE '%$this->Keyword%' OR RU.RU_LastName LIKE '%$this->Keyword%' OR RU.RU_EmailID LIKE '%$this->Keyword%')";						 
			}
			
			if($this->TaxExempted != NULL)
				$this->Condition .= " AND (PDD.PDD_TaxExempt = '" . $this->TaxExempted . "')";			
			
			$this->SortOrder = " ORDER BY " . $this->SortOrder;
				
			return $this->Condition;
		}
		
		public function ManageDateString() {
			if($this->DateString != NULL) {
				$DateString	= explode("||", $this->DateString);
				$this->StartDate	= $DateString[0];
				$this->EndDate		= $DateString[1];	
			} else {
				$this->StartDate	= '';//date('Y-m-01');
				$this->EndDate		= '';//date('Y-m-t');		
			}
		}
		
		private function ManageFilterDocument() {
		
				
			$this->Condition = " AND D.DocShowOnWebsite='1' AND D.DocUserID = " . $this->loginUserId;
			
			if(count($this->Month) > 0 && $this->Year != '' && $this->Year != 'all') {
				$month = implode(",", $this->Month);
				$this->Condition .= " AND (MONTH(D.CreatedDate) IN(" . $month . ") AND YEAR(D.CreatedDate) = '" . $this->Year . "')";
			} else {
				if($this->StartDate != '' && $this->EndDate != '') {
					//$this->Condition .= " AND (DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >='".$this->StartDate."' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '".$this->EndDate."')";
				}
				if($this->Year == '')
					$this->Condition .= " AND YEAR(D.CreatedDate) = '" . date('Y') . "'";
				else
					$this->Condition .= " AND YEAR(D.CreatedDate) = '" . $this->Year . "'";
			}	
			
			if($this->Keyword != '')
				$this->Condition .= " AND (D.DocTitle LIKE '%$this->Keyword%' OR D.Description LIKE '%$this->Keyword%')";
				
			return $this->Condition;
		}
		
		public function getDocumentDetails($DataArray, $Where=NULL) {
			$Fields	= implode(',', $DataArray);
			$OrderBy = " ORDER BY D.LastUpdatedDate DESC, D.DocSorting DESC";
			$Sql = "SELECT $Fields from " . TBLPREFIX . "documents D WHERE 1 ";
			$Where = $this->ManageFilterDocument();
			//dump($Sql . $Where . $OrderBy);
			$this->result = db :: get_all($Sql . $Where . $OrderBy);
			return $this->result;
		}
		
		private function SetStatus($Status, $Code) {
			if($Status) {
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"=>"Custom Confirmation message",
					"msgLog"=>0,									
					"msgDisplay"=>1,
					"msgType"=>2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode"=>$Code,
					"errMsg"=>"Custom Confirmation message",
					"errOriginDetails"=>basename(__FILE__),
					"errSeverity"=>1,
					"msgDisplay"=>1,
					"msgType"=>1);
				EnPException::setError($messageParams);
			}
		}
	}
?>