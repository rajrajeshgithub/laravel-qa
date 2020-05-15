<?PHP
class Donationpaymentprocess_Controller extends Controller
{
	public $tpl,$P_status=1;
	public $CurrentCsvPosition=0,$TotalRowProcessed=1,$EndCsv=0,$filePath,$fileSize,$dataHeadarr,$dataCsvArr=array(),$CsvLimit=200,$ExpCsvLimit=50,$Log_Key,$filename,$HeaderArr=array();
	public $FieldsArr=array();
	public $CSVFile,$ExportCSVFileName;
	public $LoginUserName;
	public $ExtraIndex,$StartErrorRange,$json_array,$TotalRows;
	public $IPStr;	
	function __construct()
	{
		$this->load_model("Donationpayment","objdonationpayment");
		$this->objdonationpayment = new Donationpayment_Model();
		$this->tpl	= new view;	
		$this->ExportCSVFileName	= EXPORT_CSV_PATH."DonationPaymentListCSV.csv";
		$this->LoginUserName		= getSession('DonasityAdminLoginDetail','admin_fullname');
		$this->IPStr	= str_replace('.','',$_SERVER['REMOTE_ADDR']);
	}
	
	function index($type)
	{
		switch(strtolower($type))
		{
			case "export-donationpayment":
				$this->ExportDonationPaymentData();
				break;
			default:
				//redirect(URL);
				break;	
		}
	}	
	private function GetConstant()
	{
		$this->TotalRowProcessed  = getSession('arrCsv','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsv','CURCSVPOS');
		
		$this->CurrentCsvPosition	= (is_array($this->CurrentCsvPosition))?0:$this->CurrentCsvPosition;
		$this->TotalRowProcessed	= (is_array($this->TotalRowProcessed))?0:$this->TotalRowProcessed;
		
		$this->InitErrorRange();
	}
	public function ExportDonationPaymentData()
	{
		$this->GetExportConstant();
		$NPOsListData	= $this->GetExportList();
		if(count($NPOsListData) == 0)
		{
			$this->setErrorMsg('E6007');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$String	= "";
		if($this->CurrentCsvPosition == 0)$this->CreateCsvFile();
		$fp=fopen($this->ExportCSVFileName, 'a+');
		$i=0;
		foreach($NPOsListData as $val)
		{
			fputcsv($fp,$val);		
			$this->TotalRowProcessed++;
			$i++;
		}
		setSession('arrCsvExp',$this->TotalRowProcessed,'CURCSVPOS');
		setSession('arrCsvExp',$this->TotalRowProcessed,'TOTALROWPROCESSED');		
		fclose($fp);
		$this->ViewRedirectExpCsv();
	}
	
	public function DownloadCSVFile()
	{
		$this->downloadfile($this->ExportCSVFileName,'DonationPaymentListCSV');
	}
	
	private function GetExportConstant()
	{		
		$this->TotalRowProcessed  = getSession('arrCsvExp','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsvExp','CURCSVPOS');
		
		$this->CurrentCsvPosition	= (is_array($this->CurrentCsvPosition) || $this->CurrentCsvPosition=='')?0:$this->CurrentCsvPosition;
		$this->TotalRowProcessed	= (is_array($this->TotalRowProcessed) || $this->TotalRowProcessed == '')?0:$this->TotalRowProcessed;
		
	}
	
	private function CreateCsvFile()
	{
		$fp=fopen($this->ExportCSVFileName, 'w+');
		if($fp)
		{			
			$HeaderArr	= array("PDD ItemCode","PDD NPOEIN","PDD PIItemName","PDD Cost ($)","PDD TransactionFee ($)","PDD SubTotal ($)");
			$StringArray  =  implode(",",$HeaderArr)."\r\n";
			fwrite($fp,$StringArray);
		}
	}
	
	
	private function GetExportList()
	{
		$LimitStr	= " LIMIT ".$this->CurrentCsvPosition.",".$this->ExpCsvLimit;
		$DataArray	= array('PDD.PDD_ItemCode','PDD.PDD_NPOEIN','PDD.PDD_PIItemName','PDD.PDD_Cost','PDD.PDD_TransactionFee','PDD.PDD_SubTotal');
	
		return $this->objdonationpayment->GetDonationPaymentExportList($DataArray,$LimitStr);	
	}
	
	public function ViewRedirectExpCsv()
	{
		$this->TotalRowProcessed  = getSession('arrCsvExp','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsvExp','CURCSVPOS');
		$TotalRows	= $this->objdonationpayment->TotalCountNPOs;
		if($this->CurrentCsvPosition >= $TotalRows)
		{
			$this->P_status=0;
			unsetSession("arrCsvExp");
		}
		$totalper =(int)(($this->CurrentCsvPosition/$TotalRows)*100);
		$tpl = new view;
		$tpl->assign('rowProcessed',$this->TotalRowProcessed);			
		$tpl->assign('totalPer',$totalper);
		$tpl->assign('Pstatus',$this->P_status);
		$tpl->draw("donationpaymentprocess/exportstatus");
	}
	
	public function downloadfile($file,$title)
	{
		$filename	= $file;
		$path=EXPORT_CSV_PATH;
		LoadLib("Download_file");
		if($filename=="") $filename="DonationPaymentListCSV.csv";
		$dFile = new Download_file();
		$dFile->Downloadfile($path,"DonationPaymentListCSV.csv",$title);
	}
	
	private function setErrorMsg($ErrCode,$msg=NULL)
	{
		EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$errParams=array("errCode"=>$ErrCode,
						 "errMsg"=>$msg,
						 "errOriginDetails"=>basename(__FILE__),
						 "errSeverity"=>1,
						 "msgDisplay"=>1,
						 "msgType"=>1);
		//dump($errParams);
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
	
	public function setConfirmation()
	{
		$this->P_status == 1?$this->setConfirmationMsg('C6001'):'';  
		
	}
}
?>