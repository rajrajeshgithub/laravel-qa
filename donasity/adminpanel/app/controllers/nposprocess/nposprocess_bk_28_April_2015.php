<?PHP
class NposProcess_Controller extends Controller
{
	public $tpl,$P_status=1;
	public $CurrentCsvPosition=0,$TotalRowProcessed=1,$EndCsv=0,$filePath,$fileSize,$dataHeadarr,$dataCsvArr=array(),$CsvLimit=200,$ExpCsvLimit=50,$Log_Key,$filename,$HeaderArr=array();
	public $FieldsArr=array();
	public $CSVFile,$ExportCSVFileName;
	public $LoginUserName;
	public $CategoryLimitPointer;
	
	function __construct()
	{
		$this->load_model("NposProcess","objnposprocess");	
		$this->tpl	= new view;	
		$this->ExportCSVFileName	= EXPORT_CSV_PATH."NPOsCSV.csv";
		$this->LoginUserName		= getSession('DonasityAdminLoginDetail','admin_fullname');
		$this->CategoryLimitPointer = $this->objnposprocess->ExistNpostDetailRecordCount();
	}
	
	function index($type,$nposID=NULL)
	{
		$this->nposID	= keyDecrypt($nposID);
		switch(strtolower($type))
		{
			case "upload-nposcsv":
				$this->UploadNPOscsv();
				break;
			case "import-csvdata":
				$this->ImportCSVData();
				break;	
			case "export-nposdata":
				$this->ExportNPOsData();
				break;
			default:
				redirect(URL.'npos/index/npo-manage');
				break;	
		}
	}
	
		
	public function InputData()
	{
		if(isset($_FILES['csvFile']))
		{
			$this->CSVFile	= $_FILES['csvFile'];
		}
	}
	
	public function ValidateInputCsv()
	{
		EnPException::writeProcessLog(' ImportCsv_Model :: ValidateInputCsv function Call');
		if($this->P_status == 1)
		{
			if(count($this->CSVFile) ==0 )
			{
				$this->setErrorMsg('E6001');
				redirect(URL."npos/index/import-csvfile");
			}
			else if(!validateExtension($this->CSVFile,array("csv")))
			{
				$this->setErrorMsg('E6001');
				redirect(URL."npos/index/import-csvfile");
			}
		}
	}
	
	private function UploadNPOscsv()
	{
		$this->showmsg();
		if($this->P_status)$this->InputData();
		if($this->P_status)$this->ValidateInputCsv();
		if($this->P_status)$this->UploadcsvFile();
	}
	
	private function UploadcsvFile()
	{
		EnPException::writeProcessLog(' ImportCsv_Model :: UploadcsvFile function Call');
		if($this->P_status == 1)
		{
			$uploadFile = move_uploaded_file($this->CSVFile['tmp_name'],IMPORT_CSV_PATH.$this->CSVFile['name']);
			if($uploadFile)
			{
				setSession('arrCsv',$this->CSVFile['name'],'CURCSVFILENAME');
				redirect(URL."nposprocess/index/import-csvdata");
				exit;	
			}
			else
			{
				$this->setErrorMsg('E6002');
				redirect(URL."nposprocess/index/import-csvfile");
			}
		}
	}
	
	private function ImportCSVData()
	{
		if($this->P_status == 1)
		{
			$this->GetConstant();
			$this->CreateFilePath();
			if(file_exists($this->filePath))
			{
				ini_set('auto_detect_line_endings',TRUE);
				
				$handle=fopen($this->filePath,"r");
				if($handle)
				{
					if($this->fileSize>0)
					{
						$i=1;$this->dataCsvArr=array();
						while(!feof($handle))
						{
							fseek($handle,$this->CurrentCsvPosition);
							if($this->CurrentCsvPosition == 0)
							{
								$this->HeaderArr = fgetcsv($handle);								
								$this->ValidateHeader();
							}
							else
							{
								$Row	= fgetcsv($handle);
								$this->dataCsvArr[]	= $Row;
								if($Row)
								{
									$i++;
									$this->TotalRowProcessed++;
								}
							}
							$this->CurrentCsvPosition=ftell($handle);
							
							if($i == $this->CsvLimit)
							{
								break;	
							}
						}
						$this->dataCsvArr = array_filter($this->dataCsvArr);
						setSession('arrCsv',$this->CurrentCsvPosition,'CURCSVPOS');
						setSession('arrCsv',$this->TotalRowProcessed,'TOTALROWPROCESSED');
						fclose($handle);
						$this->ViewRedirectCsv();						
					}
				}
				else
				{
					$this->setErrorMsg('E6001');	
				}
			}
			else
			{
				$this->setErrorMsg('E6003');		
			}
		}	
	}
	
	private function GetConstant()
	{		
		$this->TotalRowProcessed  = getSession('arrCsv','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsv','CURCSVPOS');
		
		$this->CurrentCsvPosition	= (is_array($this->CurrentCsvPosition))?0:$this->CurrentCsvPosition;
		$this->TotalRowProcessed	= (is_array($this->TotalRowProcessed))?0:$this->TotalRowProcessed;
	}
	
	private function CreateFilePath()
	{
		$this->filename	= getSession('arrCsv','CURCSVFILENAME');
		$this->filePath = IMPORT_CSV_PATH.$this->filename;
		$this->fileSize= filesize($this->filePath);
	}
	
	public function ViewRedirectCsv()
	{
		$this->TotalRowProcessed  = getSession('arrCsv','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsv','CURCSVPOS');
		if($this->CurrentCsvPosition >= $this->fileSize-1)$this->P_status=0;
		$totalper =(int)(($this->CurrentCsvPosition/$this->fileSize)*100);
		$this->NPOsDataInsert();
		$this->NPOsCategoryInsert();
		$tpl = new view;
		$tpl->assign('rowProcessed',$this->TotalRowProcessed);
		$tpl->assign('totalPer',$totalper);
		$tpl->assign('Pstatus',$this->P_status);
		$tpl->draw("nposprocess/importstatus");
	}
	
		
	
	public function NPOsDataInsert()
	{
		$NPOsCreateDate		= getDateTime();
		$NPOLastUpdateDate	= getDateTime();
		$NPO_LastUpdatedBy	= $this->LoginUserName;
		$Array		= array();		
		$FieldsArr	= getSession('arrCsv','FIELDS');
		
		$Fields		= implode(",",$FieldsArr);
		
		$FieldStr	= "INSERT INTO ".TBLPREFIX."npodetails($Fields,NPO_CreatedDate,NPO_LastUpdatedDate,NPO_LastUpdatedBy)VALUES";								
		$Value		= "";
		
		foreach($this->dataCsvArr as $val)
		{	
			$arrValue = array();
			$Value .= "(";
			foreach($val as $key =>$arrDetail)
			{		
				$arrValue[] = "'".addslashes(trim($arrDetail))."'";																		
			}
			array_push($arrValue,"'".$NPOsCreateDate."','".$NPOLastUpdateDate."','".$NPO_LastUpdatedBy."')");	
			$Value .= implode(",",$arrValue);
			$Value .=",";
			
		}
		
		$Value = substr($Value,0,strrpos($Value,",",-1));		
		$Value .= " ON DUPLICATE KEY UPDATE ";
		foreach($FieldsArr as $keyNew => $valueNew)
		{
			$arrField[] =$valueNew."= VALUES(".$valueNew.")"; 			
		}
		$Value .= implode(",",$arrField);
		$Query	= $FieldStr.$Value;		
		//echo "<pre>";print_r($Query);exit;
		$this->objnposprocess->InsertNPOsData($Query);
		unset($this->dataCsvArr);
	}
	
	private function ValidateHeader()
	{				
		$Array		= array("ID"=>"NPO_ID",
							"EIN"=>"NPO_EIN",
							"NAME"=>"NPO_Name",
							"ICO"=>"NPO_ICO",
							"STREET"=>"NPO_Street",
							"CITY"=>"NPO_City",
							"STATE"=>"NPO_State",
							"ZIP"=>"NPO_Zip",
							"Subsection Name"=>"NPO_SubSectionName",
							"Subsection Description"=>"NPO_SubSectionDesc",
							"NTEE_CD"=>"NPO_CD",
							"Category"=>"NPO_Category",
							"IRS_NTEE_Code2_Description"=>"NPO_IRS_NTEECode2Description",
							"AFFILIATION"=>"NPO_Affiliation",
							"Aff_Type"=>"NPO_AffType",
							"IRS_Affiliation_Codes_Description"=>"NPO_IRS_AffiliationCodesDescription",
							"Ded_Code"=>"NPO_DedCode",
							"Ded_Description"=>"NPO_DedDescription",
							"Foundation_Code"=>"NPO_FoundationCode",
							"Foundation_Description"=>"NPO_FoundationDescription",
							"Org_Code"=>"NPO_OrgCode",
							"Org_Description"=>"NPO_OrgDescription",
							"EO_Status_Code"=>"NPO_EO_StatusCode",
							"EO_Description"=>"NPO_EO_Description",
							"Asset_Income_Code"=>"NPO_AssetIncomeCode",
							"IRS_Asset_Income_Codes_Description"=>"NPO_IRS_AssetIncomeCodesDescription",
							"Filing_Code"=>"NPO_FilingCode",
							"IRS_Filing_Codes_Description"=>"NPO_IRS_FilingCodesDescription",
							"PF_Filing_Code"=>"NPO_PF_FilingCode",
							"IRS_PF_Filing_Codes_Description"=>"NPO_IRS_PF_FilingCodesDescription",
							"ASSET_AMT"=>"NPO_AssetAmt",
							"INCOME_AMT"=>"NPO_IncomeAmt",
							"REVENUE_AMT"=>"NPO_RevenueAmt");			
			
			//dump($this->HeaderArr);				
		foreach($this->HeaderArr as $key => $val)
		{
			foreach($Array as $key1 => $ArrVal)
			{
				if(!in_array($key1,$this->HeaderArr))
				{
					//$this->FieldsArr[$val]	=	$Array[$val];	
					$this->setErrorMsg('000',"Invalid header '$key1'");redirect($_SERVER['HTTP_REFERER']);	
				}
				else
				{
					$this->FieldsArr[$val]	=	$Array[$val];	
				}
			}			
		}
		//dump($this->FieldsArr);
		setSession('arrCsv',$this->FieldsArr,'FIELDS');
	}
	
	private function NPOsCategoryInsert()
	{
		$Category	= $this->GetDistinctCategory();
		if(count($Category) > 0)
		{
			$QueryStr	= "INSERT INTO ".TBLPREFIX."npocategories(NPOCat_DisplayName_EN,NPOCat_DisplayName_ES,NPOCat_CodeName,NPOCat_URLFriendlyName)VALUES";	
			$Array		= array();
			foreach($Category as $Cat)
			{
				if($Cat['NPO_Category'] != ""){
					$Cat['NPO_Category'] = str_replace(",","\,",$Cat['NPO_Category']);
				$Array[]	= "('".addslashes($Cat['NPO_Category'])."','".addslashes($Cat['NPO_Category'])."','".addslashes($Cat['NPO_Category'])."','".addslashes($Cat['NPO_Category'])."')";	
				}
			}
			$QueryStr.=implode(",",$Array);
			$QueryStr .= " ON DUPLICATE KEY UPDATE NPOCat_DisplayName_EN=VALUES(NPOCat_DisplayName_EN),NPOCat_DisplayName_ES=VALUES(NPOCat_DisplayName_ES),
							NPOCat_CodeName=VALUES(NPOCat_CodeName),NPOCat_URLFriendlyName=VALUES(NPOCat_URLFriendlyName)";
			$this->objnposprocess->NPOsCategoryInsertDB($QueryStr);
		}
	}
	
	private function GetDistinctCategory()
	{
		$LimitPointer	= getSession('arrCsv','LIMITPOINTER');
		if(!is_array($LimitPointer))$this->CategoryLimitPointer=$LimitPointer;
		$Limit	= " LIMIT ".$this->CategoryLimitPointer.",".$this->CsvLimit;	
		$Category	= $this->objnposprocess->GetDistinctCategoryDB($Limit);
		$this->CategoryLimitPointer+=$this->CsvLimit;
		setSession('arrCsv',$this->CategoryLimitPointer,'LIMITPOINTER');
		return $Category;
	}
	
	public function ExportNPOsData()
	{
		$this->GetExportConstant();
		$NPOsListData	= $this->GetNPOsList();
		if(count($NPOsListData) == 0)
		{
			$this->setErrorMsg('E6007');
			redirect(URL."npos");	
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
		$this->downloadfile($this->ExportCSVFileName,'NPOsLIST');
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
			$HeaderArr	= array("ID","EIN","NAME","ICO","STREET","CITY","STATE","ZIP","Subsection Name","Subsection Description","NTEE_CD","Category","IRS_NTEE_Code2_Description",
								"AFFILIATION","Aff_Type","IRS_Affiliation_Codes_Description","Ded_Code","Ded_Description","Foundation_Code","Foundation_Description","Org_Code",
								"Org_Description","EO_Status_Code","EO_Description","Asset_Income_Code","IRS_Asset_Income_Codes_Description","Filing_Code","IRS_Filing_Codes_Description",
								"PF_Filing_Code","IRS_PF_Filing_Codes_Description","ASSET_AMT","INCOME_AMT","REVENUE_AMT");
			$StringArray  =  implode(",",$HeaderArr)."\r\n";
			fwrite($fp,$StringArray);
		}
	}
	
	private function GetNPOsList()
	{
		$LimitStr	= " LIMIT ".$this->CurrentCsvPosition.",".$this->ExpCsvLimit;
		$DataArray	= array("NPO_ID","NPO_EIN","NPO_Name","NPO_ICO","NPO_Street","NPO_City","NPO_State","NPO_Zip","NPO_SubSectionName","NPO_SubSectionDesc","NPO_CD","NPO_Category",
							"NPO_IRS_NTEECode2Description","NPO_Affiliation","NPO_AffType","NPO_IRS_AffiliationCodesDescription","NPO_DedCode","NPO_DedDescription","NPO_FoundationCode",
							"NPO_FoundationDescription","NPO_OrgCode","NPO_OrgDescription","NPO_EO_StatusCode","NPO_EO_Description","NPO_AssetIncomeCode",
							"NPO_IRS_AssetIncomeCodesDescription","NPO_FilingCode","NPO_IRS_FilingCodesDescription","NPO_PF_FilingCode","NPO_IRS_PF_FilingCodesDescription","NPO_AssetAmt",
							"NPO_IncomeAmt","NPO_RevenueAmt");
		return $this->objnposprocess->GetNPOsList($DataArray,$LimitStr);	
	}
	
	public function ViewRedirectExpCsv()
	{
		$this->TotalRowProcessed  = getSession('arrCsvExp','TOTALROWPROCESSED');
		$this->CurrentCsvPosition = getSession('arrCsvExp','CURCSVPOS');
		$TotalRows	= $this->objnposprocess->TotalCountNPOs;
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
		$tpl->draw("nposprocess/exportstatus");
	}
	
	public function downloadfile($file,$title)
	{
		$filename	= $file;
		$path=EXPORT_CSV_PATH;
		LoadLib("Download_file");
		if($filename=="") $filename="NPOslist.csv";
		$dFile = new Download_file();
		$dFile->Downloadfile($path,"NPOsCSV.csv",$title);
	}
	
	public function downloadsamplecsv()
	{
		$path=SAMPLE_CSV_PATH;
		LoadLib("Download_file");
		$dFile = new Download_file();
		$dFile->Downloadfile($path,"sample.csv","sample");
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
	
	private function showmsg()
	{
		$msgValues=EnPException::getConfirmation(false);			
		$this->tpl->assign('msgValues',$msgValues);
	}
}
?>