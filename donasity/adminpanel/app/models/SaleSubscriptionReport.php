<?php
class SaleSubscriptionReport_Model extends Model {
	public $pageLimit = 20, $pageSelected = 1, $totalRecord = 0, $result = array(), $fromDate, $toDate, $P_status = 1, $isExport = 0, $currentCsvPosition = 0, $totalRowProcessed = 0, $expCsvLimit = 50;
	
	public function __construct() {
		
	}

	// get all list from table
	public function GetSaleSubscriptionDetails($field=array('*'), $filterString='') {
		EnPException :: writeProcessLog('SaleSubscriptionReport_Model :: GetSaleSubscriptionDetails function called');
		
		$fieldString = implode(', ', $field);
		$filterString = " Where 1 " . $filterString;
		
		$sql = "SELECT $fieldString FROM " . TBLPREFIX . "salesubscriptionpaymenttransaction SSPT LEFT JOIN dns_salesubscription SS ON SSPT.SSPT_SSID = SS.SS_ID ";
		//dump($sql . $filterString);
		$this->totalRecord = db :: count($sql . $filterString);
		//dump($this->totalRecord);
		if($this->totalRecord > 0) {
			$limit = $this->isExport == 1 ? $this->SetExportPageLimit() : $this->SetLimit();
			//dump($sql . $filterString . $limit, 0);
			$this->result =	db :: get_all($sql . $filterString . $limit);
		}
		
		return $this->result;
	}
	
	// set page limit
	private function SetLimit() {
		return " LIMIT " . $this->pageLimit * ($this->pageSelected - 1) . "," . $this->pageLimit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		return " LIMIT " . $this->currentCsvPosition . ", " . $this->expCsvLimit;
	}
}

?>