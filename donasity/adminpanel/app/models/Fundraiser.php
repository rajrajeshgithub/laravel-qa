<?PHP
class Fundraiser_Model extends Model {
	
	public $totalRecord = 0, $pageLimit = 20, $selectedPage = 1, $result = array(), $P_status = 1, $isExport = 0, $CurrentCsvPosition = 0, $TotalRowProcessed = 0, $EndCsv = 0, $dataHeadarr, $dataCsvArr=array(), $CsvLimit = 200, $ExpCsvLimit = 50, $fromDate = '', $toDate = '';
	
	public function __construct() {
		
	}
	
	// get all fundraiser list from table
	public function GetFundraiser($field=array(), $filterParam='', $arraySortParam=array(),  $pagenation=NULL) {
		EnPException :: writeProcessLog('Fundraiser_Model :: GetFundraiser function called.');
		
		$field = implode(",", $field);
		$condition = " WHERE 1 " . $filterParam;
		$limit = $this->SetLimit();
		$sql = "SELECT $field FROM " . TBLPREFIX . "campaign ";
		//dump($sql . $condition . $limit);
		$this->totalRecord = db :: count($sql . $condition);
		
		if($this->totalRecord > 0)
			$this->result =	db :: get_all($sql . $condition . $limit);
		
		return $this->result;
	}
	
	// get 2 week fundraiser from table
	public function Get2WeekFundraiser() {
		EnPException :: writeProcessLog('Fundraiser_Model :: Get2WeekFundraiser function called.');
		
		$sql = "SELECT count(Camp_ID) AS ending2Week FROM `dns_campaign` where Camp_Status = '15' AND Camp_Deleted != '1' AND DATE_FORMAT(Camp_EndDate,'%Y-%m-%d') <= '$this->toDate' AND DATE_FORMAT(Camp_EndDate,'%Y-%m-%d') > DATE_SUB('$this->toDate', INTERVAL 14 DAY) AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d') >= '$this->fromDate' AND DATE_FORMAT(Camp_LastUpdatedDate,'%Y-%m-%d') <= '$this->toDate'";
		
		$this->result =	db :: get_row($sql);
		
		return $this->result;
	}
	
	// get fundraiser list by status from table
	public function GetFundraiserStatus($field=array(), $filterParam='', $arraySortParam=array(),  $pagenation=NULL) {
		EnPException :: writeProcessLog('Fundraiser_Model :: GetFundraiserStatus function called.');
		
		$field = implode(",", $field);
		$condition = " WHERE 1 " . $filterParam;
		
		$limit = $this->isExport == 1 ? '' : $this->SetLimit();
		/*$sql = "SELECT $field FROM ".TBLPREFIX."campaign  C
				   LEFT JOIN ".TBLPREFIX."npocategories CT ON (CT.NPOCat_ID=C.Camp_Cat_ID)
				   LEFT JOIN ".TBLPREFIX."campaignlevel CL ON (CL.Camp_Level_ID=C.Camp_Level_ID)
				   LEFT JOIN ".TBLPREFIX."states S ON (C.Camp_CP_State=S.State_Value AND S.State_Country = 'US')";*/
				   
		$sql = "SELECT $field FROM ".TBLPREFIX."campaign  C
				   LEFT JOIN ".TBLPREFIX."npocategories CT ON (CT.NPOCat_ID=C.Camp_Cat_ID)
				   LEFT JOIN ".TBLPREFIX."campaignlevel CL ON (CL.Camp_Level_ID=C.Camp_Level_ID)";
		//dump($sql . $condition . $limit);
		$this->totalRecord = db :: count($sql . $condition);
		
		if($this->totalRecord > 0)
			$this->result =	db :: get_all($sql . $condition . $limit);
		
		return $this->result;
	}
	
	// get fields name to select from table
	private function GetFields($field) {
		EnPException :: writeProcessLog('Fundraiser_Model :: GetFields function called.');
		foreach($field as $key => &$row) {
			switch($row) {
				default:
				$row = "$row";
			}
		}
		return $field;
	}
	
	// set condition to select from table
	private function SetCondition($filterParam) {
		EnPException :: writeProcessLog('Fundraiser_Model :: SetCondition function called.');
		$filterString = " Where 1 ";
		if(count($filterParam) > 0) {
			foreach($filterParam as  $key => $row) {
				$cond = '';
				switch($key) {
					default:
					$cond = "$key='$row'";
				}
				$filterString .= " AND ( $cond ) ";
			}
		}
		return $filterString;
	}
	
	// set page limit
	private function SetLimit() {
		$limit = '';
		EnpException :: writeProcessLog('Fundraiser_Model :: SetLimit function called.');
		
		$limit = $this->pageLimit * ($this->selectedPage - 1);
		$limit = " LIMIT " . $limit . "," . $this->pageLimit;
		
		return $limit;
	}
}
?>