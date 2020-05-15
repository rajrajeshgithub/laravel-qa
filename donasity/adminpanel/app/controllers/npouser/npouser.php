<?php
class Npouser_Controller extends Controller
{
	public $FieldArr,$sortParam,$Criteria,$firstName,$lastName,$Address1;
	public $Address2,$Country,$State,$City,$ZipCode,$Phone,$EmailId,$Gender,$Dob,$Password;
	public $P_ErrorCode,$tpl,$P_status,$P_ErrorMessage,$MsgType,$P_ConfirmCode,$P_ConfirmMsg;
	
	public $Field, $Search, $Status, $Page_Selected, $OrderBy, $filterParam;
	//export
	public $totalRowProcessed = 0, $currentCsvPosition = 0, $exportFileName;
	
	function __construct()
	{
		checkLogin(18);
		$this->load_model('NpoUser', 'objNpoUser');
		$this->P_status = 1;
		$this->LastUpdateBy = getsession("DonasityAdminLoginDetail", "admin_fullname");
		$this->loginDetails = getsession("DonasityAdminLoginDetail");
		$this->exportFileName = EXPORT_CSV_PATH . "npo_users_" . $this->loginDetails['admin_id'] . ".csv";
	}
	
	public function index($type='list')
	{
		$this->tpl = new view;
		switch(strtolower($type))
		{	
			case 'export-user' :
				$this->ExportUser();
			break;			
			default:
				$this->Listing();
				$this->tpl->draw('npouser/listing');
			break;  
		}
	}
	
	private function Listing()
	{
		EnPException::writeProcessLog('NpoUser_Controller :: Listing action to view all objNpoUser');
		$this->filterParameterLists();

		$DateArray			=	array("NUR.NPOID","NUR.NPOEIN","NUR.USERID","NUR.NPOConfirmationCode","NUR.NPODescription","NUR.NPOLogo","NUR.Stripe_ClientID","NUR.Status as stripeStatus","NUR.Active","RU_Status as registeredStatus",
										"concat(RU_FistName,' ',RU_LastName) as fullName","RU.RU_ID as ID","RU.RU_ProfileImage","RU.RU_EmailID","RU.RU_CompanyName","RU.RU_Designation","NPO_Name");
		
		$this->filterParam['RU_Deleted']='0';
		$this->filterParam['Active']='1';
		$this->filterParam['RU_UserType']='2';
		
		$UsrList 			= 	$this->objNpoUser->GetNPOUserListing($DateArray,$this->filterParam,$this->sortParam);
		//dump($UsrList);
		$PagingArr			=	constructPaging($this->objNpoUser->pageSelectedPage,$this->objNpoUser->NUTotalRecord,$this->objNpoUser->pageLimit);		
		$LastPage 			= 	ceil($this->objNpoUser->NUTotalRecord/$this->objNpoUser->pageLimit);
		
		$this->tpl->assign("UsrList",$UsrList);
		$this->tpl->assign("PagingList",$PagingArr['Pages']);
		$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
		$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
		$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
		$this->tpl->assign("lastPage",$LastPage);
		$this->tpl->assign("Field",$this->Field);
		$this->tpl->assign("status",$this->Status);
		$this->tpl->assign("Criteria",$this->Criteria);
		$this->tpl->assign("Search",stripslashes($this->Search));
		$this->tpl->assign("totalRecords",$this->objNpoUser->NUTotalRecord);
		
	}
	
	private function filterParameterLists()
	{
		$this->Field 			= request('post', 'searchFields', 0);
		$this->Search 			= request('post', 'searchValues', 0);
		$this->Status			= request('post', 'status', 0);
		$this->Page_Selected	= (int)request('post', 'pageNumber', 1);
		$this->OrderBy			= request('post', 'sortBy', 0);
		$pageSelected 			= request('post', 'pageNumber', '1');
		
		$this->objNpoUser->pageSelectedPage	= $pageSelected == 0 ? 1 : $pageSelected;
		
		if($this->Status != NULL)
			$this->filterParam['RU_Status']	= $this->Status;
		
		if($this->Search != NULL)
		{
			switch($this->Field)
			{				
				case "RU_FistName" :
				case "RU_LastName" :
				case "RU_EmailID" :
				case "NPOEIN" :
				case "NPO_Name" :
				case "RU_Status" :
					$this->filterParam['SearchCondtionLike'] = '';
					$this->filterParam['SearchCondtionLike'] .= $this->Field . " LIKE '%" . $this->Search . "%'";
				break;
				default:
					$this->filterParam['SearchCondtionLike'] = '';
					$this->filterParam['SearchCondtionLike'] .= "RU_FistName LIKE '%" . $this->Search . "%'" . " OR RU_LastName LIKE '%" . $this->Search . "%'" . " OR " . " RU_EmailID LIKE '%" . $this->Search . "%' OR " . " NPOEIN LIKE '%" . $this->Search . "%' OR " . " NPO_Name LIKE '%" . $this->Search."%'";
			}
		}
	}
		
	// export npo user data to csv file
	private function ExportUser() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$this->GetExportConstant();
		$CsvData = $this->GetCsvData();
		
		if(count($CsvData) == 0) {
			$this->SetStatus(0, 'ECSV01');
			redirect(URL . 'npouser');
			return;
		}
		
		$csvHeader = array('Name User', 'Npo EIN', 'Npo Name', 'Email', 'Stripe Account ID', 'Stripe Status', 'Status');
		if($this->currentCsvPosition == 0)
			$this->CreateCsvFile($csvHeader);
			
		$fp = fopen($this->exportFileName, 'a+');
		foreach($CsvData as $val) {
			if($val['stripeStatus'] == '1')
				$val['stripeStatus'] = 'Active';
			if($val['stripeStatus'] == '0')
				$val['stripeStatus'] = 'Inactive';
				
			if($val['registeredStatus'] == '1')
				$val['registeredStatus'] = 'Active';
			if($val['registeredStatus'] == '0')
				$val['registeredStatus'] = 'Inactive';
			
			fputcsv($fp, $val);		
			$this->totalRowProcessed++;
		}
		
		setSession('arrCsvExp', $this->totalRowProcessed, 'CURCSVPOS');
		setSession('arrCsvExp', $this->totalRowProcessed, 'TOTALROWPROCESSED');
		
		fclose($fp);
		$this->ViewRedirectExpCsv();
	}
	
	// get export constant
	private function GetExportConstant() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		$this->objNpoUser->isExport = 1;
		
		$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		
		$this->currentCsvPosition = (is_array($this->currentCsvPosition) || $this->currentCsvPosition == '') ? 0 : $this->currentCsvPosition;
		$this->totalRowProcessed = (is_array($this->totalRowProcessed) || $this->totalRowProcessed == '') ? 0 : $this->totalRowProcessed;
		
		$this->objNpoUser->currentCsvPosition = $this->currentCsvPosition;
	}
	
	// prepare data from table to export into csv
	private function GetCsvData() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$this->filterParameterLists();
		
		$DateArray = array(
			"concat(RU_FistName,' ',RU_LastName) as fullName",
			"NUR.NPOEIN",
			"NPO_Name",
			"RU.RU_EmailID",
			"NUR.Stripe_ClientID",
			"NUR.Status as stripeStatus",
			"RU_Status as registeredStatus");
		
		$this->filterParam['RU_Deleted'] = '0';
		$this->filterParam['Active'] = '1';
		$this->filterParam['RU_UserType'] = '2';
		
		return $this->objNpoUser->GetNPOUserListing($DateArray, $this->filterParam);
	}
	
	// create csv file
	private function CreateCsvFile($headerArr) {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$fp = fopen($this->exportFileName, 'w+');
		if($fp) {
			$stringArray = implode(",", $headerArr) . "\r\n";
			fwrite($fp, $stringArray);
		}
	}
	
	// export progress bar
	public function ViewRedirectExpCsv() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		
		$totalRows = $this->objNpoUser->NUTotalRecord;
		if($this->currentCsvPosition >= $totalRows) {
			$this->P_status = 0;
			unsetSession("arrCsvExp");
		}
		
		$totalper = (int)(($this->currentCsvPosition / $totalRows) * 100);
		$this->tpl->assign('rowProcessed', $this->totalRowProcessed);
		$this->tpl->assign('totalPer', $totalper);
		$this->tpl->assign('Pstatus', $this->P_status);
		$this->tpl->draw('npouser/exportstatus');
	}
	
	// download csv file
	public function downloadfile($title='npo_users') {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		LoadLib("Download_file");
		$dFile = new Download_file();
		$dFile->Downloadfile(EXPORT_CSV_PATH, "npo_users_" . $this->loginDetails['admin_id'] . '.csv', $title);
	}
	
	private function SetStatus($Status, $Code) {
		if($Status) {
			$messageParams = array(
				"msgCode"=>$Code,
				"msg"			=> "Custom Confirmation message",
				"msgLog"		=> 0,									
				"msgDisplay"	=> 1,
				"msgType"		=> 2);
			EnPException::setConfirmation($messageParams);
		} else {
			$messageParams = array(
				"errCode" 			=> $Code,
				"errMsg"			=> "Custom Confirmation message",
				"errOriginDetails"	=> basename(__FILE__),
				"errSeverity"		=> 1,
				"msgDisplay"		=> 1,
				"msgType"			=> 1);
			EnPException::setError($messageParams);
		}
	}

}
?>	