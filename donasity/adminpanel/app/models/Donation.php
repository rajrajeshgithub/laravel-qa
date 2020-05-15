<?php
class Donation_Model extends Model {
	
	public $pageLimit, $pageSelectedPage, $totalRecord, $results, $where, $fromDate, $toDate, $paymentProcessor, $transactionType, $isExport, $CurrentCsvPosition = 0, $TotalRowProcessed = 0, $EndCsv = 0, $filePath, $fileSize, $dataHeadarr, $dataCsvArr=array(), $CsvLimit = 200, $ExpCsvLimit = 50;
	public $itemCode, $campId = 0, $npoEin = '', $PaymentType = '';
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->pageSelectedPage = 1;
		$this->results = array();
		$this->where = '';
		$this->fromDate = '';
		$this->toDate = '';
		$this->paymentProcessor = '';
		$this->transactionType = '';
		$this->isExport = 0;
	}
	
	
	/* donation summary */
	public function getDonationSummary($DataArray=array(),$Condition='',$GroupBY='')
	{
		EnPException :: writeProcessLog('Donation_Model :: getDonationSummary function called');		
		$Fields	= implode(",", $DataArray);
		$Condition= " WHERE 1=1 ".$Condition;
		$Sql = "SELECT $Fields FROM `" . TBLPREFIX ."purchasedonationdetails` pdd";
		$this->totalRecord = db::count($Sql.$Condition.$GroupBY);		
		//echo $Sql . $Condition . $GroupBY . $limit;exit;		
		if($this->totalRecord > 0)
		{
			$this->results = db::get_all($Sql.$Condition.$GroupBY);
		}
		return $this->results;
	}
	
	public function GenerateWhereForDonation()
	{
		$this->where .= " WHERE pdd.PDD_Status=11 AND pt.PT_PaymentStatus=1 AND pdd.PDD_PIItemType IN('NPOD','CD')";
	}
	
	/* end of code */
	
	
	/* recurring profile report function */
	// get all donation payment details from table
	public function getReportdata($DataArray) {
		EnPException :: writeProcessLog('Donation_Model :: getReportdata function called');
	
		/*$DataArray = array('count(pdd.PDD_ReoccuringProfileID) as countProfileIDs','rp.RP_ID','pdd.PDD_ID','ru.RU_ID','CONCAT(ru.RU_FistName," ",ru.RU_LastName)Username','ru.RU_EmailID','pdd.PDD_PIItemName','rp.RP_RecurringCycle','pdd.PDD_ReoccuringProfileID','SUM(pdd.PDD_Cost)AmountPaid','rp.RP_StartDate','rp.RP_EndDate','rp.RP_RUID', 'rp.RP_RecurringCycle', 'rp.RP_RecurringAmount', 'rp.RP_Status', 'rp.RP_RecurringProfileID', 'rp.RP_RecurringCustomerID', 'pdd.PDD_PIItemName');*/
		$Fields	= implode(",", $DataArray);
			
		$Sql = "SELECT $Fields FROM " . TBLPREFIX . "purchasedonationdetails pdd LEFT JOIN " . TBLPREFIX ."recuringprofiles rp ON pdd.PDD_ID = rp.RP_PDDID
			   LEFT JOIN `" . TBLPREFIX . "registeredusers` ru ON(ru.RU_ID = pdd.PDD_RUID)";
			  
		$this->GenerateRTWhere();
		$GroupBY = " GROUP BY pdd.PDD_ReoccuringProfileID";
		//dump($Sql . $this->where . $GroupBY);
		$this->totalRecord = db::count($Sql . $this->where .$GroupBY);
			
		
		//echo $Sql . $this->where . $GroupBY . $limit;exit;
		
		if($this->totalRecord > 0)
		{
			if($this->isExport == 0)
				$limit = $this->SetPageLimit();
				
			if($this->isExport == 1)
				$limit = $this->SetExportPageLimit();
				
			$this->results = db::get_all($Sql . $this->where . $GroupBY . $limit);
		}
		return $this->results;
	}
	
	// set where clause
	private function GenerateRTWhere()
	{
		$this->where .= " WHERE rp.RP_Status != 0 AND pdd.PDD_Status=11";
		if($this->rp_staus!='')
		{
			if($this->rp_staus==1)
			{
				$this->where .=" AND (rp.RP_Status>0 AND rp.RP_Status<11)";
			}
			else if($this->rp_staus==2)
			{
				$this->where .=" AND (rp.RP_Status>10 AND rp.RP_Status<21)";
			}
			else if($this->rp_staus==0)
			{
				$this->where .=" AND (rp.RP_Status>0 AND rp.RP_Status<11 OR rp.RP_Status>10 AND rp.RP_Status<21)";
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
	/* end of code */ 
		
	// get all donation payment details from table
	public function DailyDonation($field=array(), $filterparam=array(), $arraySortParam=array(), $pagenation=NULL) {
		EnPException :: writeProcessLog('Donation_Model :: DailyDonation function called');
		
		/*$field = array(
			'PDD.PDD_PIItemName as charity',
			'PDD.PDD_Cost',
			'PDD.PDD_TransactionFee',
			'PDD.PDD_TransactionFeePaidByUser',
			'PDD.PDD_SubTotal',
			'PDD.PDD_PaymentType',
			'PDD.PDD_PIItemType',
			'PDD.PDD_DateTime',
			'PT.PT_PaymentType',
			'PT.PT_PaymentGatewayName',
			'PT.PT_PaymentStatus');*/
		
		//$field = $this->getfieldName($field);
		
		$fieldString = implode(' , ', $field);			
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "purchasedonationdetails AS PDD LEFT JOIN " . TBLPREFIX . "paymenttransection AS PT ON (PDD.PDD_PaymentTransactionID = PT.PT_ID)";
		
		$filterString = $this->GenerateWhere(); 
		//dump($Sql . $filterString . $this->SetOrderBy() . $limit);
		
		$this->totalRecord = db :: count($Sql . $filterString);
		if($this->totalRecord > 0) {
			if($this->isExport == 0)
				$limit = $this->SetPageLimit();
				
			if($this->isExport == 1)
				$limit = $this->SetExportPageLimit();
				
			//dump($Sql . $filterString . $this->SetOrderBy() . $limit);
			$this->results = db :: get_all($Sql . $filterString . $this->SetOrderBy() . $limit);
		}
		return $this->results;
	}
	
	// get all donation payment details of remotely created check from table
	public function RemotelyCreatedDailyDonation($field=array(), $filterString='', $sortOrder='') {
		EnPException :: writeProcessLog('Donation_Model :: RemotelyCreatedDailyDonation function called');
		
		$fieldString = implode(' , ', $field);			
		
		$Sql = "SELECT $fieldString FROM " . TBLPREFIX . "purchasedonationdetails AS PDD LEFT JOIN " . TBLPREFIX . "paymenttransection AS PT ON (PDD.PDD_PaymentTransactionID = PT.PT_ID) LEFT JOIN " . TBLPREFIX . "registeredusers RU ON (RU.RU_ID = PDD.PDD_RUID)";
		
		//dump($Sql . $filterString . $sortOrder);
		
		$this->totalRecord = db :: count($Sql . $filterString);
		
		if($this->totalRecord > 0) {
			if($this->isExport == 0)
				$limit = $this->SetPageLimit();
				
			if($this->isExport == 1)
				$limit = $this->SetExportPageLimit();
				
			//dump($Sql . $filterString . $sortOrder . $limit);
			$this->results = db :: get_all($Sql . $filterString . $sortOrder . $limit);
		}
		return $this->results;
	}
	
	// get fields name to select from table
	private function getfieldName($field) {
		EnPException :: writeProcessLog('Donation_Model :: getfieldName function called');
		foreach($field as $key => &$row) {
			switch($row) {
				default:
				$row = "$row";
			}
		}
		return $field;
	}
	
	// set page limit
	private function SetPageLimit() {
		EnPException :: writeProcessLog('Donation_Model :: SetPageLimit function called');
		$limit = '';
		if($this->pageLimit != '' && $this->pageSelectedPage != '') {
			$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
			$limit = " LIMIT " . $limit . ", " . $this->pageLimit;
		}
		return $limit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		EnPException :: writeProcessLog('Donation_Model :: SetExportPageLimit function called');
		$limit = '';
		$limit = " LIMIT " . $this->CurrentCsvPosition . ", " . $this->ExpCsvLimit;
		return $limit;
	}
	
	// set order by
	private function SetOrderBy() {
		EnPException :: writeProcessLog('Donation_Model :: SetOrderBy function called');
		$orderBy = " ORDER BY PT.PT_LastUpdatedDate DESC, PDD.PDD_ID DESC ";
		return $orderBy;
	}
	
	// generate where clause
	private function GenerateWhere() {
		EnPException :: writeProcessLog('Donation_Model :: GenerateWhere function called');
		
		$this->where .= " Where 1 AND PDD_Status=11";
		
		// date filter
		if($this->fromDate != '' && $this->toDate != '') {
			$this->fromDate = date('Y-m-d', strtotime($this->fromDate));
			$this->toDate = date('Y-m-d', strtotime($this->toDate));
			
			if($this->toDate >= $this->fromDate)
				$this->where .= " AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(PDD.PDD_DateTime,'%Y-%m-%d') <= '$this->toDate' ";
		}
		
		// payment processor filter
		if($this->paymentProcessor == '1')
			$this->where .= " AND PT.PT_PaymentGatewayName='STRIPE' ";
			
		if($this->paymentProcessor == '2')
			$this->where .= " AND PT.PT_PaymentGatewayName='USAEPAY' ";
			
		// trasaction type filter
		if($this->transactionType == '1')
			$this->where .= " AND (PDD.PDD_PIItemType = 'CD' OR PDD.PDD_PIItemType = 'NPOD') ";
			
		if($this->transactionType == '2')
			$this->where .= " AND PDD.PDD_PIItemType = 'CP' ";
			
		if($this->itemCode != '')
			$this->where .= " AND PDD.PDD_ItemCode='".$this->itemCode."'";
		
		// get donation details of a campign
		if($this->campId > 0)
			$this->where .= " AND PDD.PDD_CampID='" . $this->campId . "'";
			
		// get donation details by npoEin
		if($this->npoEin != '')
			$this->where .= " AND PDD.PDD_NPOEIN='" . $this->npoEin . "'";
			
		// get donation details by payment type
		if($this->PaymentType != '') {
			switch($this->PaymentType) {
				case '1' :
					$paymentType = 'OTP';
				break;
				case '2' :
					$paymentType = 'FRP';
				break;
				case '3' :
					$paymentType = 'NRP';
				break;
				default :
					$paymentType = 'OTP';
				break;
			}
			$this->where .= " AND PDD.PDD_PaymentType = '$paymentType' ";
		}
		
		return $this->where;
	}
}

?>