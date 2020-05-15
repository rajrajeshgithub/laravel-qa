<?PHP
class Donationpayment_Controller extends Controller
{
	public $tpl;
	public $nposID,$InputDataArray=array();
	public $LoginUserName,$startDate,$endDate;
	public $wherCondition;
	public $keyWord;
	
	function __construct()
	{
		$this->load_model("Donationpayment", "objdonationpayment");
		$this->objdonationpayment = new Donationpayment_Model();
		$this->tpl = new view;	
		$this->LoginUserName = getSession('DonasityAdminLoginDetail', 'admin_fullname');
	}
	
	public function index($type="List",$nposID=NULL)
	{
		$this->nposID = ($nposID != NULL) ? keyDecrypt($nposID) : NULL;
		switch(strtolower($type))
		{
			case "export-donationpayment":
				checkLogin(15);
				$this->ExportCSVFile();
				break;
			default:
				checkLogin(14);
				$this->DonationPaymentList();
				break;	
		}
	}
	
	private function FilterData()
	{
		//$startDate = formatDate($this->startDate, 'Y-m-d');
		//$endDate = formatDate($this->endDate, 'Y-m-d');
		
		//$this->startDate = request('get', 'StartDate', 0) ? request('get', 'StartDate', 0) : date('Y-m-1');
		$this->startDate = request('get', 'StartDate', 0) ? request('get', 'StartDate', 0) : date('Y-m-d', strtotime('today - 30 days'));
		
		$this->endDate = request('get', 'EndDate', 0) ? request('get', 'EndDate', 0) : date('Y-m-d');
		
		$this->startDate = formatDate($this->startDate, 'Y-m-d');
		$this->endDate = formatDate($this->endDate, 'Y-m-d');
		
		$this->keyWord = request('get', 'keyword', 0) ? request('get', 'keyword', 0) : '';
		$this->wherCondition = " WHERE DATE_FORMAT(PD.PD_CreatedDate,'%Y-%m-%d') BETWEEN '$this->startDate' AND '$this->endDate'";
		if(trim($this->keyWord) && $this->keyWord!='')
		{
			$this->wherCondition .= " AND (PDD.PDD_ItemCode LIKE '%".$this->keyWord."%') 
									OR (PDD.PDD_NPOEIN LIKE '%".$this->keyWord."%')
									OR (PDD.PDD_PIItemName LIKE '%".$this->keyWord."%')
									OR (PDD.PDD_Cost LIKE '%".$this->keyWord."%')
									OR (PDD.PDD_TransactionFee LIKE '%".$this->keyWord."%')
									OR (PDD.PDD_SubTotal LIKE '%".$this->keyWord."%')
									OR (PDD.PDD_NPOEIN LIKE '%".$this->keyWord."%')";
		}
	}
	
	private function DonationPaymentList()
	{
		$Condition 	= $this->FilterData();
		
		$pageSelected = (int)request('get', 'pageNumber', 1);
		
		$this->objdonationpayment->pageSelectedPage	= ($pageSelected == 0) ? 1 : $pageSelected;
		
		$DataArray	= array('PDD.PDD_ID','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_RUID','PDD.PDD_DonationReciptentType','PDD.PDD_StripeConnectedID','PDD.PDD_ItemCode',
							'PDD.PDD_NPOEIN','PDD.PDD_CampID','PDD.PDD_PIItemType','PDD.PDD_PIItemName','PDD.PDD_PIItemDescription','PDD.PDD_ItemAttributes','PDD.PDD_CategoryCode',
							'PDD.PDD_Cost','PDD.PDD_TransactionFee','PDD.PDD_TransactionFeePaidByUser','PDD.PDD_TaxExempt','PDD.PDD_SubTotal','PDD.PDD_Status','PDD.PDD_Status_Notes',
							'PDD.PDD_PaymentType','PDD.PDD_ReoccuringProfileID','PDD.PDD_PaymentTransactionID','PDD.PDD_Comments','PDD.PDD_Deleted','PDD.PDD_eCheckStatus','PDD.PDD_eCheckDate',
							'PDD.PDD_eCheckComment','PD.PD_ID','PD.PD_ItemType','PD.PD_ReferenceNumber','PD.PD_BillingFirstName','PD.PD_BillingLastName','PD.PD_BillingAddress1',
							'PD.PD_BillingAddress2','PD.PD_BillingCity','PD.PD_BillingState','PD.PD_BillingCountry','PD.PD_BillingZipCode','PD.PD_BillingEmailAddress','PD.PD_BillingPhone',
							'PD.PD_RU_ID','PD.PD_SubTotal','PD.PD_TransactionFee','PD.PD_TransactionFeePaidByUser','PD.PD_TotalAmount','PD.PD_Status','PD.PD_Comment','PD.PD_WebMasterComment',
							'PD.PD_IP','PD.PD_CreatedDate','PD.PD_LastUpdatedDate','PD.PD_CreatedBy','PD.PD_Source','PD.PD_Deleted','PT.PT_ID','PT.PT_PDID','PT.PT_PDDID','PT.PT_RUID',
							'PT.PT_PaymentType','PT.PT_PaymentAmount','PT.PT_PaidAmount','PT.PT_TransactionFee','PT.PT_PaymentGatewayName','PT.PT_PaymentGatewayRequest','PT.PT_PaymentGatewayResponse',
							'PT.PT_PaymentGatewayTransactionID','PT.PT_PaymentStatus','PT.PT_PaymentStatus_Notes','PT.PT_Comment','PT.PT_IP','PT.PT_CreatedDate','PT.PT_LastUpdatedDate');
							
		$DonationPaymentList = $this->objdonationpayment->GetDonationPaymentList_DB($DataArray, $this->wherCondition);
			
		//================= pagination code start =================
		$Page_totalRecords = $this->objdonationpayment->TotalCountNPOs;
		$PagingArr = constructPaging($pageSelected, $Page_totalRecords, $this->objdonationpayment->pageLimit);
		$LastPage = ceil($Page_totalRecords / $this->objdonationpayment->pageLimit);
			
		$this->tpl->assign("pageSelected", $pageSelected);
		$this->tpl->assign("PagingList", $PagingArr['Pages']);
		$this->tpl->assign("PageSelected", $PagingArr['PageSel']);
		$this->tpl->assign("startRecord", $PagingArr['StartPoint']);
		$this->tpl->assign("endRecord", $PagingArr['EndPoint']);
		$this->tpl->assign("lastPage", $LastPage);
		//================= pagination code end ===================
			
		$this->tpl->assign('totalnpos', $this->objdonationpayment->TotalCountNPOs);
		$this->tpl->assign('donationpaymentlist', $DonationPaymentList);
		$this->tpl->assign('StartDate', $this->startDate);
		$this->tpl->assign('EndDate', $this->endDate);
		$this->tpl->assign('keyword', $this->keyWord);
		$this->tpl->draw('donationpayment/listing');
	}
	
	private function ExportCSVFile()
	{
		$this->FilterData();
		setSession('arrCsvExp',serialize($this->wherCondition),'DONATIONPAYMENTCONDITION');
		redirect(URL."donationpaymentprocess/index/export-donationpayment");
	}
	
	private function setErrorMsg($ErrCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$errParams=array("errCode"=>$ErrCode,
						 "errMsg"=>"Custom Exception message",
						 "errOriginDetails"=>basename(__FILE__),
						 "errSeverity"=>1,
						 "msgDisplay"=>1,
						 "msgType"=>1);
		EnPException::setError($errParams);
	}
	
	private function setConfirmationMsg($ConfirmCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
		$confirmationParams=array("msgCode"=>$ConfirmCode,
								  "msgLog"=>1,									
								  "msgDisplay"=>1,
								  "msgType"=>2);
		$placeholderValues=array("placeValue1");
		EnPException::setConfirmation($confirmationParams, $placeholderValues);
	}
}
?>