<?PHP
class Npos_Controller extends Controller
{
	public $tpl;
	public $nposID,$InputDataArray=array();
	public $LoginUserName;
	function __construct()
	{
		//ini_set('post_max_size','1024M');
		//phpinfo();
		$this->load_model("Npos","objnpos");	
		$this->tpl	= new view;	
		$this->LoginUserName		= getSession('DonasityAdminLoginDetail','admin_fullname');
	}
	
	public function index($type="List",$nposID=NULL)
	{
		$this->nposID	= ($nposID != NULL)?keyDecrypt($nposID):NULL;
		switch(strtolower($type))
		{
			case "add-npos":
				checkLogin(14);
				$this->AddNPOs();
				break;
			case "insert-npos":
				checkLogin(14);
				$this->InsertNPOs();
				break;
			case "edit-npos":
				checkLogin(14);
				$this->EditNPOs();
				break;	
			case "update-npos":
				checkLogin(14);
				$this->UpdateNPOs();
				break;	
			case "delete-npos":
				checkLogin(14);
				$this->DeleteNPOs();
				break;	
			case "npo-manage":
				checkLogin(15);
				$this->ShowLaunchPad();
				break;
			case "import-csvfile":
			checkLogin(15);
				$this->ImoprtCSVFile();
				break;
			case "export-npos":
				checkLogin(15);
				$this->ExportCSVFile();
				break;
			default:
				checkLogin(14);
				$this->NPOsList();
				break;	
		}
	}
	
	private function ExportCSVFile()
	{
		$Condition 	= $this->FilterData();
		$Condition	= serialize($Condition);
		setSession('arrCsvExp',$Condition,'NPOCONDITION');
		redirect(URL."nposprocess/index/export-nposdata");
	}
	
	private function ShowLaunchPad()
	{
		unsetSession("arrCsvExp");
		$this->tpl = new view;
		$this->tpl->draw('npos/showLaunchPad');
	}
	
	private function ImoprtCSVFile()
	{
		unsetSession("arrCsv");
		$this->showmsg();
		ini_set('max_input_time','30000');
		ini_set('max_execution_time','36000');		
		ini_set('post_max_size','55M');
		//phpinfo();
		$this->tpl->draw('npos/importcsv');
	}
	
	private function AddNPOs()
	{
		$this->showmsg();
		$this->getStateList();	
		$this->getCategoryList();
		$this->tpl->draw("npos/addnpos");	
	}
	
	private function InsertNPOs()
	{
		$this->InputData();	
		$this->ValidateInputData();
		$this->nposID	= $this->objnpos->InsertNPOsDB($this->InputDataArray);
		if($this->nposID > 0)
		{
			$this->setConfirmationMsg('C11001');	
			redirect(URL."npos/index/edit-npos/".keyEncrypt($this->nposID));		
		}
		else
		{
			$this->setErrorMsg('E11001');	
			redirect($_SERVER['HTTP_REFERER']);	
		}
	}
	
	private function ValidateInputData()
	{
		if($this->objnpos->CheckEINDuplicacyDB($this->InputDataArray['NPO_EIN'],$this->nposID) == false)
		{
			$this->setErrorMsg('E6008');
			redirect($_SERVER['HTTP_REFERER']);	
		}
		if(trim($this->InputDataArray['NPO_Name']) == "")
		{
			$this->setErrorMsg('E6009');
			redirect($_SERVER['HTTP_REFERER']);	
		}
	}
	
	private function EditNPOs()
	{
		$NPODetail	= $this->GetNPOsDetail();//dump($NPODetail);
		//$NPODetail['NPO_SubSectionName']	= explode(',',$NPODetail['NPO_SubSectionName']);
		$this->getStateList();	
		$this->getCategoryList();
		$this->GetBankDetails($NPODetail['NPO_EIN']);
		$this->GetContactDetails($NPODetail['NPO_EIN']);
		$this->GetUserDetails($NPODetail['NPO_EIN']);
		$PaymentMode     	= 	$GLOBALS['paymentmode'];
		$this->tpl->assign("paymentmode",$PaymentMode);
		$this->tpl->assign("npodetail",$NPODetail);
		$this->tpl->draw('npos/editnpos');
	}
	
	private function UpdateNPOs()
	{
		$this->InputData();
		if($this->objnpos->UpdateNPOs_DB($this->InputDataArray,$this->nposID))
		{
			$this->setConfirmationMsg('C11002');			
		}
		else
		{
			$this->setErrorMsg('E11002');	
		}
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	private function DeleteNPOs()
	{
			
	}
	
	private function InputData()
	{
			//dump($_POST);
		$this->nposID				= request('post','npoID',1);	
		$NPO_EIN					= request('post','npoein',0);
		$NPO_Name					= request('post','nponame',0);
		$NPO_ICO					= request('post','npoico',0);
		$NPO_Street					= request('post','npostreet',0);
		$NPO_City					= request('post','npocity',0);
		$NPO_State					= request('post','npostate',0);
		$NPO_Zip					= request('post','npozip',0);
		$NPO_SubSectionName			= request('post','nposubsectionname',0);
		/*$NPO_SubSectionName			= request('post','nposubsectionname',3);
		$NPO_SubSectionName			= implode(",",$NPO_SubSectionName);*/
		$NPO_SubSectionDesc			= request('post','nposubsectiondescription',0);
		$NPO_CD						= request('post','npocd',0);
		$NPO_Category				= request('post','npocategory',0);
		$NPO_Irsnteecode2			= request('post','npoirsnteecode2',0);
		$NPO_Affiliation			= request('post','npoaffiliation',0);
		$NPO_AffType				= request('post','npoafftype',0);
		$NPO_AffCodeDesc			= request('post','npoaffiliationcodesdesc',0);
		$NPO_DedCode				= request('post','npodedcode',0);
		$NPO_DedDesc				= request('post','npodeddesc',0);
		$NPO_FoundationCode			= request('post','npofoundationcode',0);
		$NPO_FoundationDesc			= request('post','npofoundationdescription',0);
		$NPO_OrgDesc				= request('post','npoorgdesc',0);
		$NPO_OrgCode				= request('post','npoorgcode',0);
		$NPO_EoStatusCode			= request('post','npoeostatuscode',0);
		$NPO_EoDesc					= request('post','npoeodesc',0);
		$NPO_AssetIncomeCode		= request('post','npoassetincomecode',0);
		$NPO_FilingCode				= request('post','npofilingcode',0);
		$NPO_PfFilingCode			= request('post','npopffilingcode',0);
		$NPO_IRSAssetIncomeCodeDesc	= request('post','npoirsassetincomecodesdesc',0);
		$NPO_IRSFilingCodeDesc		= request('post','npoirsfilingcodedesc',0);
		$NPO_IRSPfFilingCodeDesc	= request('post','npoirspffilingcodedesc',0);
		$NPO_AssetAmt				= request('post','npoassetamt',0);
		$NPO_IncomeAmt				= request('post','npoincomeamt',0);
		$NPO_RevenueAmt				= request('post','revenueamt',0);
		$NPO_Status					= request('post','npostatus',0);
		$NPO_ShowOnWebsite			= request('post','nposhowonweb',1);
		
		$this->InputDataArray	= array("NPO_Name"=>$NPO_Name,"NPO_ICO"=>$NPO_ICO,"NPO_Street"=>$NPO_Street,"NPO_City"=>$NPO_City,"NPO_State"=>$NPO_State,"NPO_Zip"=>$NPO_Zip,
										"NPO_SubSectionName"=>$NPO_SubSectionName,"NPO_SubSectionDesc"=>$NPO_SubSectionDesc,"NPO_CD"=>$NPO_CD,"NPO_Category"=>$NPO_Category,
										"NPO_IRS_NTEECode2Description"=>$NPO_Irsnteecode2,"NPO_Affiliation"=>$NPO_Affiliation,"NPO_AffType"=>$NPO_AffType,
										"NPO_IRS_AffiliationCodesDescription"=>$NPO_AffCodeDesc,"NPO_DedCode"=>$NPO_DedCode,"NPO_DedDescription"=>$NPO_DedDesc,
										"NPO_FoundationCode"=>$NPO_FoundationCode,"NPO_FoundationDescription"=>$NPO_FoundationDesc,"NPO_OrgCode"=>$NPO_OrgCode,
										"NPO_OrgDescription"=>$NPO_OrgDesc,"NPO_EO_StatusCode"=>$NPO_EoStatusCode,"NPO_EO_Description"=>$NPO_EoDesc,
										"NPO_AssetIncomeCode"=>$NPO_AssetIncomeCode,"NPO_IRS_AssetIncomeCodesDescription"=>$NPO_IRSAssetIncomeCodeDesc,
										"NPO_FilingCode"=>$NPO_FilingCode,"NPO_IRS_FilingCodesDescription"=>$NPO_IRSFilingCodeDesc,"NPO_PF_FilingCode"=>$NPO_PfFilingCode,
										"NPO_IRS_PF_FilingCodesDescription"=>$NPO_IRSPfFilingCodeDesc,"NPO_AssetAmt"=>$NPO_AssetAmt,"NPO_IncomeAmt"=>$NPO_IncomeAmt,
										"NPO_RevenueAmt"=>$NPO_RevenueAmt,"NPO_Status"=>$NPO_Status,"NPO_DisplayOnWebsite"=>$NPO_ShowOnWebsite,
										"NPO_LastUpdatedDate"=>getDateTime(),"NPO_LastUpdatedBy"=>$this->LoginUserName,"NPO_EIN"=>$NPO_EIN,"NPO_CreatedDate"=>getDateTime());
		if($this->nposID > 0 )
		{
			unset($this->InputDataArray['NPO_CreatedDate']);
		}
										
	}
	
	private function GetNPOsDetail()
	{
		$DataArray	= array("NPO_ID","NPO_EIN","NPO_Name","NPO_ICO","NPO_Street","NPO_City","NPO_State","NPO_Zip","NPO_SubSectionName","NPO_SubSectionDesc","NPO_CD","NPO_Category",
							"NPO_IRS_NTEECode2Description","NPO_Affiliation","NPO_AffType","NPO_IRS_AffiliationCodesDescription","NPO_DedCode","NPO_DedDescription","NPO_FoundationCode",
							"NPO_FoundationDescription","NPO_OrgCode","NPO_OrgDescription","NPO_EO_StatusCode","NPO_EO_Description","NPO_AssetIncomeCode","NPO_FilingCode",	
							"NPO_IRS_AssetIncomeCodesDescription","NPO_IRS_FilingCodesDescription","NPO_PF_FilingCode","NPO_IRS_PF_FilingCodesDescription","NPO_AssetAmt","NPO_IncomeAmt",
							"NPO_RevenueAmt","NPO_Status","NPO_CreatedDate","NPO_LastUpdatedDate","NPO_LastUpdatedBy","NPO_DisplayOnWebsite","NPO_UniqueCode");
		$Condition	= " WHERE NPO_ID=".$this->nposID;
		return $this->objnpos->GetNPOsDetailDB($DataArray,$Condition);
	}
	
	
	private function GetStateName($StateArr,$StateAbr)
	{
		 $ReturnVal	= "-";
		 foreach($StateArr as $val)
		 {
			if($val['State_Value'] == $StateAbr) 
			{
			  $ReturnVal	= $val['State_Name'];
			  break;
			}	 
		 }	
		 return $ReturnVal;
	}
	
	private function NPOsList()
	{
		$StateArr = $this->getStateList();		
		if($_SERVER['QUERY_STRING'] == NULL){
			$NPOsList	= array();
		}
		else
		{
			$Condition 	= $this->FilterData();	
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$this->objnpos->pageSelectedPage	= ($pageSelected==0)?1:$pageSelected;
			$DataArray	= array('N.NPO_ID','N.NPO_EIN','N.NPO_Name','N.NPO_City','N.NPO_DedCode','N.NPO_Status','N.NPO_State as State_Name','CR.NPO_CatName');
			
			$NPOsList = $this->objnpos->GetNPOsList_DB($DataArray,$Condition);
			foreach($NPOsList as &$val)
			{
				$val['State_Name']	= $this->GetStateName($StateArr,$val['State_Name']);
				$Category			= explode("||",$val['NPO_CatName']);
				$val['NPO_CatName']	= $Category[0];
			}
			
			//================= pagination code start =================
			$Page_totalRecords = $this->objnpos->TotalCountNPOs;		
			$PagingArr = constructPaging($pageSelected, $Page_totalRecords,$this->objnpos->pageLimit);		
			$LastPage = ceil($Page_totalRecords/$this->objnpos->pageLimit);
			
			$this->tpl->assign("pageSelected",$pageSelected);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$LastPage);
			//================= pagination code end ===================
			
		}
			$this->getCategoryList();//get category list
			$DeductionMode	= $GLOBALS['npodeduction'];
			$this->tpl->assign("DeductionMode",$DeductionMode);
			$this->GetFilterString();
			$this->tpl->assign('totalnpos',$this->objnpos->TotalCountNPOs);
			$this->tpl->assign('nposlist', $NPOsList);
			$this->tpl->draw('npos/listing');
	}
	
	private function GetFilterString()
	{
		$NullValue =1;
		$Result	=	$_SERVER['QUERY_STRING'];
		if($Result!=NULL){
			$this->tpl->assign("filterUrl",keyEncrypt($Result));
			$NullValue =0;
		}
		$this->tpl->assign("NullValue",$NullValue);
	}
	
	private function FilterData()
	{
		$Condition			= "";
		$ConditionArr		= array();
		$SearchCategory		= request('get','category',0);
		$SearchState		= request('get','state',0);
		$SearchCity				= request('get','city',0);
		$Status				= "";
		if(isset($_GET['status'])){
		if($_GET['status'] != ""){$Status= request('get','status',1);}
		}
		
		$DeductionMode		= request('get','deductionmode',0);
		$Fields				= request('get','fields',0);
		$CompareCondition	= request('get','condition',1);
		$Keyword			= request('get','keyword',0);
		$CategoryParentID	= request('get','categoryparentid',1);
		
		if($CategoryParentID > 0){
			$this->getSubCategoryList($CategoryParentID);
		}
		
		if($SearchCategory !='' && $SearchCategory!= NULL){$ConditionArr[]	= "CR.NPOCat_ID  = '".$SearchCategory."'";}
		if($SearchState !='' && $SearchState!= NULL){$ConditionArr[]	= "N.NPO_State = '".$SearchState."'";}
		if($SearchCity !='' && $SearchCity!= NULL){$ConditionArr[]	= "N.NPO_City LIKE '%".$SearchCity."%'";}
		if($Status !='' && $Status!= NULL){$ConditionArr[]	= "N.NPO_Status = '".$Status."'";}
		if($DeductionMode !='' && $DeductionMode!= NULL){$ConditionArr[]	= "N.NPO_DedCode = '".$DeductionMode."'";}
		
		if($CompareCondition == 0)
		{
			if(trim($Keyword) != "" && $Fields != "")
			{
				$ConditionArr[]	= $Fields." LIKE '%".$Keyword."%'";
			}
			elseif(trim($Keyword) != "" && $Fields == "")	
			{
				$ConditionArr[]	= "(N.NPO_Name LIKE '%".$Keyword."%' OR N.NPO_EIN LIKE '%".$Keyword."%' OR N.NPO_ICO LIKE '%".$Keyword."%' OR N.NPO_Street LIKE '%".$Keyword."%' 
									OR N.NPO_City LIKE '%".$Keyword."%' OR N.NPO_State LIKE '%".$Keyword."%' OR N.NPO_Zip LIKE '%".$Keyword."%')";	
			}
		}
		elseif($CompareCondition == 1)
		{
			if(trim($Keyword) != "" && $Fields != "")
			{
				$ConditionArr[]	= $Fields." LIKE '".$Keyword."%'";
			}
			elseif(trim($Keyword) != "" && $Fields == "")
			{
				$ConditionArr[]	= "(N.NPO_Name LIKE '".$Keyword."%' OR N.NPO_EIN LIKE '".$Keyword."%' OR N.NPO_ICO LIKE '".$Keyword."' OR N.NPO_Street LIKE '".$Keyword."%' OR 
									N.NPO_City LIKE '".$Keyword."%' OR N.NPO_State LIKE '".$Keyword."%' OR N.NPO_Zip LIKE '".$Keyword."%')";
			}	
		}
		elseif($CompareCondition == 2)
		{
			if(trim($Keyword) != "" && $Fields != "")
			{
				$ConditionArr[]	= $Fields."='".$Keyword."'";
			}
			elseif(trim($Keyword) != "" && $Fields == "")
			{
				$ConditionArr[]	= "(N.NPO_Name='".$Keyword."' OR N.NPO_EIN='".$Keyword."' OR N.NPO_ICO='".$Keyword."' OR N.NPO_Street='".$Keyword."' OR N.NPO_City='".$Keyword."' OR 
									N.NPO_State='".$Keyword."' OR N.NPO_Zip='".$Keyword."')";
			}	
		}
		
		$ConditionStr 	= implode(' AND ',$ConditionArr);
		if($ConditionStr != "")$Condition	= " WHERE ".$ConditionStr;
		//$Condition =" WHERE  N.NPO_EIN=102358595";
		$this->tpl->assign("searchcategory",$SearchCategory);
		$this->tpl->assign("SearchState",$SearchState);
		$this->tpl->assign("Searchcity",$SearchCity);
		$this->tpl->assign("status",$Status);
		$this->tpl->assign("deductionmode",$DeductionMode);
		$this->tpl->assign("fields",$Fields);
		$this->tpl->assign("comparecondition",$CompareCondition);
		$this->tpl->assign("keyword",$Keyword);
		$this->tpl->assign("categoryparentid",$CategoryParentID);
		return $Condition;	
	}
	
	private function getStateList()
	{
		$this->load_model("Common","objcommon");
		$StateList	= $this->objcommon->getStateList('US');			
		$this->tpl->assign("state",$StateList);
		return $StateList;
	}
	
	private function getCategoryList()
	{
		$this->load_model("NpoCategory","objNpoCategory");
		$this->sortParam	=  "";
		$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES",
										  "NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite");
							  
		$NpoCatList 		= 	$this->objNpoCategory->GetNpoCategoryListing($DataArray,array(),$this->sortParam);
		$this->tpl->assign("category",$NpoCatList);
	}
	
	private function GetCity()
	{
		$CityList	= $this->objnpos->GetCityDB();	
		$this->tpl->assign("CityList",$CityList);
	}
	
	public function CheckEINDuplicacy()
	{
		$EIN	= request('get','npoein',0);
		$NPOID	= request('get','NPOID',0);
		$Status	= $this->objnpos->CheckEINDuplicacyDB($EIN,$NPOID);	
		echo json_encode($Status);
		exit;
	}
	
	Private function GetUserDetails($EIN)
	{
		$DataArray	= array("CONCAT(RU.RU_FistName,' ',RU.RU_LastName) as Name","NUR.NPODescription","NUR.NPOLogo","NUR.Stripe_ClientID","NUR.USERID","NUR.Status");
		$UserDetails	= $this->objnpos->GetUserDetailsDB($DataArray,$EIN);
		/*foreach($UserDetails as &$val)
		{
			$len	= strlen($val['NPODescription']);
			$val['NPODescription']	= 	($len > 180)?substr($val['NPODescription'],0,180)."...":$val['NPODescription'];
		}*/
		$this->tpl->assign("UserDetails",$UserDetails);
	}
	
	Private function GetBankDetails($EIN)
	{
		$DataArray	= array("NPO_BD_EIN","NPO_BD_BankName","NPO_BD_BankAddress","NPO_BD_Phone","NPO_BD_EmailAddress","NPO_BD_AccountType","NPO_BD_AccountName","NPO_BD_AccountNumber",
							"NPO_BD_PreferredPaymentMode","NPO_BD_ID","NPO_BD_DefaultDetail");
		$BankDetails	= $this->objnpos->GetBankDetailsDB($DataArray,$EIN);
		$this->tpl->assign("BankDetails",$BankDetails);
	}
	
	Private function GetContactDetails($EIN)
	{
		$DataArray	= array("CONCAT(NPO_CD_FirstName,' ',NPO_CD_LastName) AS Name","CONCAT(NPO_CD_Address1,' ',NPO_CD_Address2) AS Address","NPO_CD_PhoneResidance","NPO_CD_EmailAddress",
							"NPO_CD_CompanyName","NPO_CD_ID","NPO_CD_Mobile","NPO_CD_PhoneOffice","NPO_CD_PrimaryContact");
		$ContactDetails	= $this->objnpos->GetContactDetailsDB($DataArray,$EIN);	
		$this->tpl->assign("ContactDetails",$ContactDetails);					
	}
	
	public function generateuniquecode($NPOID=NULL)
	{
		if($NPOID == NULL){
			$NPOID			= request('post','NPOID',1);
		}
		$NPODetail		= $this->objnpos->GetNPODetail($NPOID);
		$State			= substr(trim($NPODetail['NPO_State']),0,2);		
		$EIN			= substr(trim($NPODetail['NPO_EIN']),0,2);
		$NPOName		= substr(trim($NPODetail['NPO_Name']),0,1);
		if($State=='' || $EIN=='' || $NPOName=='')
		{
			$OutPutArr	= array();
			$OutPutArr['Status']	= $Status;	
			$OutPutArr['Msg']		= E60011;
			echo json_encode($OutPutArr);
			exit;
		}
		
		$RandomNumber	=  $this->UniqueRandomNumbersWithinRange(1001,9999,2);
		
		$UniqueCode		= $State.$EIN."-".$RandomNumber.$NPOName;
		if($this->IsDuplicateCode($UniqueCode))
		{
			$this->generateuniquecode($NPOID);
		}
		else
		{
			$this->SaveUniqueCode($UniqueCode,$NPOID);
		}
	}
	
	private function SaveUniqueCode($UniqueCode,$NPOID)
	{
		$Status	= 0;
		$DataArray	= array('NPO_UniqueCode'=>$UniqueCode);
		if($this->objnpos->SaveUniqueCode($DataArray,$NPOID))
		{
			$Status	= 1;	
		}
		$OutPutArr	= array();
		$OutPutArr['Status']	= $Status;	
		$OutPutArr['Msg']		= E60010;
		if($Status)
		{
			$OutPutArr['Code']	= $UniqueCode;	
			$OutPutArr['Msg']		= C19001;
		}
		echo json_encode($OutPutArr);
		exit;
	}
	
	
	private function UniqueRandomNumbersWithinRange($min, $max, $quantity) 
	{
		$numbers = range($min, $max);
		shuffle($numbers);
		return implode("-",array_slice($numbers, 0, $quantity));
	}
	
	private function IsDuplicateCode($UniqueCode)
	{
		return $this->objnpos->IsDuplicateCode($UniqueCode);	
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
	
	private function showmsg()
	{
		$msgValues=EnPException::getConfirmation(false);			
		$this->tpl->assign('msgValues',$msgValues);
	}
}
?>