<?php

/*
	Campaign Status Detail
	=========================
	Setup - Front end
	1-10
	6 - Complted Setup 
	
	1 to 6 reserved
	7 to 10 Available 
	-----------------------
	
	Admin Action	
	11-20
	15 - Accepted / Running
	
	11 to 15 reserved
	14, 16 to 20 Available 
	-----------------------
	User Action
	21-30
	21 - Stop by user
	22 to 30 Available
	
	-----------------------
	Team Fundraiser Action
	31-40
	31 - Team Join
	36 - Stop by Captain
	32 to 35 and 37 to 40 Available.
	-----------------------

*/
	class Ut2myaccount_Controller extends Controller
	{		
		public $tpl,$WidgetID;
		public $LoginUserId,$CurrentDate,$LoginUserDetail;
		public $Image,$NPOImagePhysPath,$ExportCSVFileName, $ExportFundraiserCSV;
		public $sortfrom, $sortto, $P_status=1;

		function __construct()
		{
			$this->tpl	= new View;
			$this->load_model('UserType2','objutype2');
			$this->objutype2 = new UserType2_Model();
			$this->load_model('Ut2_Reporting','objut2report');
			$this->load_model('Fundraisers','objFund');
			$this->load_model('Widget','objWidget');
			$this->objut2report = new Ut2_Reporting_Model();
			$this->LoginUserDetail	= getSession('Users','UserType2');	
			/*
			dump($this->LoginUserDetail);
			Array
			(
				[user_id] => TVRZeg==
				[user_firstname] => Jessica
				[user_lastname] => Rabbit
				[user_email] => jessica.rabbit@testnpo.com
				[user_fullname] => Jessica Rabbit
				[user_type] => 2
				[is_login] => 1
			)
			*/
			$this->LoginUserId		= keyDecrypt($this->LoginUserDetail['user_id']);
			$this->CurrentDate		= getDateTime();
			$this->FC_PageLimit=10;
			//unlink(EXPORT_CSV_PATH."ut1_donationlist_".$this->LoginUserId.".csv");
			$this->ExportCSVFileName	= EXPORT_CSV_PATH."ut2_donationlist_".$this->LoginUserId.".csv";
			$this->ExportFundraiserCSV = EXPORT_CSV_PATH . "ut2_fundraiser_" . $this->LoginUserId . ".csv";
			
			if(file_exists($this->ExportCSVFileName))	
				unlink($this->ExportCSVFileName);
				
			if(file_exists($this->ExportFundraiserCSV))	
				unlink($this->ExportFundraiserCSV);
				
			$this->load_model('UserType1','objutype1');
			$this->objutype1->logoutUT1();
		}
		
		public function index($type='dashboard')
		{
			switch($type)
			{
				case 'dashboard':
					if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->Dashboard();
				break;	
				case 'manage-npo-details':
					if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->ManageNpoDetails();
					break;
				case 'updatenpodetails':
					$this->UpdateNPODetails();
					break;					
				case 'manage-profile':	
					if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->ManageProfile();
					break;
				case 'update-profile':
if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->Update();
					break;
				case 'change-password-form':
					if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->ChangePasswordForm();
					break;
				case 'changepassword':
					if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->ChangePassword();
					break;
				case 'fundraisers-list':
if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->getFundraiserList();
					break;
				case 'ambassador-portal':
if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->AmbassadorPortal();
					break;
				case 'add-widget':
if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->addWidget();
					break;
				case 'ambassador-documents':
if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2/npo-login");
					$this->AmbassadorDocuments();
					break;
			}		
		}	
		
		public function updatestyletemplate()
		{
			$postdata = $_POST;
			$this->objFund->F_Camp_ID = keyDecrypt($postdata['Camp_ID']);
			$this->objFund->StyleTemplateName = $postdata['StyleTemplateName'];
			
			$pStatus = 0;
			if($this->objFund->UpdateCampaignTemplateName())
				$pStatus = 1;
			
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in update style template.";
			$lMessage = "Error in update style template of camp id = $this->objFund->F_Camp_ID";
			if($pStatus) {
				$sMessage = "Style template has updated sucessfully.";
				$lMessage = "Style template has updated with camp id = $this->objFund->F_Camp_ID";
			}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$this->objFund->F_Camp_ID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			
			redirect(URL."ut2myaccount/FundraiserBasicDetail/".$postdata['Camp_ID']);
		}
		
		
		
		public function viewall()
		{
			//get date range in month and year				
			$this->objut2report->Type =  request('get','type',0);	
			$id = keyDecrypt(request('get','id',0));
			//$this->objut2report->LoggedUserID =  request('get','id',0);	
			$this->objut2report->LoggedUserID =  request('get','id',0);	
			$this->objut2report->Keyword =  request('get','keyword',0);	
			
			//sorting
			$this->objut2report->SortTO =  request('get','sortto',0);	
			$this->objut2report->SortFrom =  request('get','sortfrom',0);
			if($this->objut2report->SortTO=='' && $this->objut2report->SortFrom=='')
			{
				$this->objut2report->SortTO='';
				$this->objut2report->SortFrom='PDD.PDD_DateTime DESC';
			}
			//end of code
			
			$this->objut2report->Month				= request('get','month',3);
			$this->objut2report->Year				= request('get','year',0);
			$this->objut2report->TaxExempted		= request('get','taxable',0);
			if(count($this->objut2report->Month)==0 && $this->objut2report->Year=='')
			{
				$this->objut2report->Month = date('m'); 	
				$this->objut2report->Year  =  date('Y');
			}
		//end of code
		
			
			
			//sort parameters
			$sortfrom = request('get','sortfrom',0);
			if($sortfrom=='')
			{
				$sortfrom="ASC";
			}
			if(isset($sortfrom) && request('get','sortfrom',0)!='')
			{
				if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
				{
					$sortfrom="DESC";
				}
				else
				{
					$sortfrom="ASC";
				}
			}
			$sortto   = request('get','sortto',0);
			if($sortto=="DonorName" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="RU.RU_FistName $sortfrom";
				$sortto="DonorName";
			}
			else if($sortto=="Type" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Date" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_DateTime $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Amount" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_Cost $sortfrom";
				$sortto="Date";
			}
			else
			{
				$this->objut2report->SortOrder =" PDD.PDD_DateTime DESC";
			}
			//end of code			
			unsetSession("confirmnpodetail");			
			$this->objutype2->GetUserDetails();
			$NpoDetails		= $this->GetNPOProfileDetail();
			
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('ut2_donation_view_all');
			//$arrMetaInfo["pageheading"]=strtr($arrMetaInfo["pageheading"],array('{{UserName}}' => $this->userDetailsArray['user_fullname']));
			
			$UserName		= $this->objutype2->userDetailsArray['RU_FistName']." ".$this->objutype2->userDetailsArray['RU_LastName'];
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$NPO_Name 		= $NpoDetails["NPO_Name"];
			$arrMetaInfo["pageheading"]		= strtr($arrMetaInfo["pageheading"],array('{{npo_name}}'=>$NPO_Name));						
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			//donation list array			
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			
			$this->objut2report->NPOEIN=$NPODetails["NPOEIN"];			
				//$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut2report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_EmailID'));
			
			$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			$montharray = explode(',',$this->objut2report->Month);
			$this->tpl->assign('Month',$montharray);
			$this->tpl->assign('Year',$this->objut2report->Year);
			$this->tpl->assign('UserDetail',$this->objutype2->userDetailsArray);
			$this->tpl->assign('UserName',$UserName);
			$this->tpl->assign('UserID',$this->objut2report->LoggedUserID);			
			$this->tpl->assign("keyword",$this->objut2report->Keyword);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut2myaccount/viewall");	
		}
		
		public function exportdonationlist()
		{
			
			$this->tpl=new View;
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);	
			$this->objut2report->PddID	= request('post','PDD_ID',3);
			$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";					
			$this->objut2report->NPOEIN=$NPODetails["NPOEIN"];	
			$DonationArray	= $this->objut2report->GetDonationDetails(array('PDD.PDD_PIItemName','PDD.PDD_TaxExempt','DATE_FORMAT(PDD.PDD_DateTime,"%m/%d/%Y")as PDD_DateTime','PDD.PDD_Cost','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_FistName','RU.RU_LastName','RU.RU_Phone','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID','CONCAT(RU.RU_Address1," ",RU_Address2)DonorAddress','RU.RU_City','RU.RU_State','RU.RU_ZipCode'));
			//dump($DonationArray);
			if(count($DonationArray) == 0)
			{
				$messageParams=array("errCode"=>'E18000',"errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}			
			$this->CreateCsvFile();
			$fp=fopen($this->ExportCSVFileName, 'a+');
			$i=0;
			foreach($DonationArray as $val)
			{
				if($val['PDD_TaxExempt']==1)
					$val['PDD_TaxExempt'] = 'YES';
				else
					$val['PDD_TaxExempt'] = 'NO';
					
				fputcsv($fp,$val);		
				$i++;
			}			
			$this->downloadfile();			
		}
		
		private function CreateCsvFile()
		{
			$fp=fopen($this->ExportCSVFileName, 'w+');
			if($fp)
			{
			$HeaderArr	= array("Donation To","Tax Exempt","Date of Donation ","Donation Amount","Donor Full Name","Donor First Name","Donor Last Name","Phone","Email","Address","City","State Prov","Zip","Country");
				$StringArray  =  implode(",",$HeaderArr)."\r\n";
				fwrite($fp,$StringArray);
			}
		}
		
		public function downloadfile($title='donationlist')
		{
			$path=EXPORT_CSV_PATH;
			LoadLib("Download_file");
			$filename="donationlist.csv";
			$dFile = new Download_file();
			$dFile->Downloadfile($path,"ut2_donationlist_".$this->LoginUserId.".csv",$title);
		}
		
		public function printdonationlist()
		{
			$this->load_model("common","objCommon");
			$this->tpl=new View;
			
			//get date range in month and year
				$this->objut2report->PddID	= request('post','PDD_ID',3);
				$Username	= request('post','username',0);
			//end of code
			
			//donation list array				
			$NPODetails=$this->objutype2->GetNPODetail(array("NPOEIN","NPO_Name")," AND NUR.USERID=".$this->LoginUserId);
			$this->objut2report->NPOEIN=$NPODetails["NPOEIN"];
			$this->objut2report->NPO_Name = $NPODetails["NPO_Name"];	
			$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
			$npoName = $this->objut2report->NPO_Name;
			$DonationArray	= $this->objut2report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_EmailID','CONCAT(RU.RU_Address1," ",RU.RU_Address2)NPOAddress'));
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('ut2_print_donation');
			$arrMetaInfo["header_donation_statement"]=strtr($arrMetaInfo["header_donation_statement"],array('{{npo_name}}' =>$npoName,'{{print_date}}' => date('m-d-Y')));
			
			$this->tpl->assign("DonationArray",$DonationArray);			
			$this->tpl->assign($arrMetaInfo);
			//end of code
			$this->tpl->assign("Username",$Username);
			$this->tpl->assign("NPO_Name",$npoName);
			$HTML=$this->tpl->draw('ut2myaccount/printdonation',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		private function getFundraiserList()
		{
			$this->load_model('UserType2','objutype2');
			$this->load_model('Common','objCommon');
				
			$this->LoginUserDetail	= getSession('Users');
		
			$Wherecondition = " AND Camp_RUID=".keyDecrypt($this->LoginUserDetail['UserType2']['user_id'])." AND Camp_Deleted!='1'";
			$fundraiserlist = $this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_TeamUserType','Camp_CreatedDate','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_StartDate','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage'),$Wherecondition);
			$fundraiserlist_array = array();
			$fundraiserlist_array = $this->CreateFundraiserArray($fundraiserlist);			
			
			$this->objutype2->GetUserDetails();	
			$NpoDetails	= $this->GetNPOProfileDetail();		
			$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('npodashboard');
			$UserName		= $this->objutype2->userDetailsArray['RU_FistName']." ".$this->objutype2->userDetailsArray['RU_LastName'];
			$UserID         = $this->objutype2->userDetailsArray['RU_ID'];
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$arrMetaInfo["ut2userdetail"]=strtr($arrMetaInfo["ut2userdetail"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,	'{{Image}}'=>$Image,'{{NPOName}}'=>$NpoDetails['NPO_Name']));
			$arrMetaInfo["pageheading"]=strtr($arrMetaInfo["pageheading"],array('{{UserName}}' => $UserName));
			$this->tpl->assign($arrMetaInfo);
			//dump($arrMetaInfo);
			/* ======== Meta Section End ========== */		
				
			$this->tpl->assign("UserDetail",$this->objutype2->userDetailsArray);
			$this->tpl->assign('fundraiserlistCount',count($fundraiserlist_array));	
			$this->tpl->assign('fundraiserlistTeam',$fundraiserlist_array);
			$this->tpl->assign("fundraiserlist",$fundraiserlist_array);
			$this->tpl->draw("ut2myaccount/fundraiserlist");
		}
		
		private function CreateFundraiserArray($fundraiserlist)
		{
			$fundraiserlist_array=array();
			if(isset($fundraiserlist) && count($fundraiserlist) > 0)
			{
				foreach($fundraiserlist as $key=>$val)
				{
					//make a new title in the case of title empty
						if(isset($val['Camp_Title']) && $val['Camp_Title']!='')
						{
							$this->Camp_Title = $val['Camp_Title'];
						}
						else 
						{	
							$this->CampTitle($val['Camp_Level_ID']);
						}	
					//end of code
					$fundraiserlist_array[$val['Camp_ID']] = array('Camp_ID'=>$val['Camp_ID'],'Camp_TeamUserType'=>$val['Camp_TeamUserType'],'Camp_CreatedDate'=>$val['Camp_CreatedDate'],'Camp_Title'=>$this->Camp_Title,'camp_thumbImage'=>$val['camp_thumbImage'],'Camp_RUID'=>$val['Camp_RUID'],'Camp_Status'=>$val['Camp_Status'],'Camp_Level_ID'=>$val['Camp_Level_ID'],'Camp_UrlFriendlyName'=>$val['Camp_UrlFriendlyName'],'Camp_DonationGoal'=>$val['Camp_DonationGoal'],'Camp_DonationReceived'=>$val['Camp_DonationReceived'],'Donationpercentage'=>$val['Donationpercentage'],'Camp_StartDate'=>$val['Camp_StartDate']);
				}
			}
			return $fundraiserlist_array;
		}
		private function CampTitle($levelid)
		{
			$this->objFund->LevelID = $levelid;
			$this->objFund->getTitle();
			$this->Camp_Title = $this->objFund->Title; 
		}
		
		private function ManageNpoDetails()
		{
			unsetSession("confirmnpodetail");
			$Res	= $this->GetNPOProfileDetail();			
			$this->load_model('Common','objCommon');
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ut2_npo_manage_details'));
			$this->tpl->assign('STRIPE_CONNECT_URL',STRIPE_CONNECT_URL);
			$this->tpl->assign("Detail",$Res);						
			$this->tpl->draw('ut2myaccount/managenpodetails');	
		}
		
		private function GetNPOProfileDetail()
		{
			$DataArray	= array("N.NPO_ID","N.NPO_EIN","NUR.NPOLogo","NUR.NPOConfirmationCode","NUR.NPODescription","N.NPO_Zip","N.NPO_Name","N.NPO_Street","NPO_City","NUR.Status as Stripe_Status","NUR.Stripe_ClientID as Stripe_ClientID");
			$Res	= $this->objutype2->GetNPOProfileDetail($DataArray,$this->LoginUserId);
			return $Res;
		}
		
		private function UpdateNPODetails()
		{
			$LogoName		= request('post','logoImg',0);
			$LogoArr		= $_FILES['uploadLogo'];
			$this->Image();
		}
		
		private function Image()
		{
			$Description			= request('post','npoDescription',0);
			$this->Image			= count($_FILES['uploadLogo'])>0?$_FILES['uploadLogo']:'';
			$ImageFile  			= $this->Image;
			$Ext					= file_ext($ImageFile['name']);
			$CustomName	 			= $ImageFile['name'];
			$Image					= strUnique().".".$Ext;
			
			
			$this->NPOImagePhysPath	= NPO_IMAGE_DIR.$Image;			
			
			$ExistLogo		= request('post','existlogo',0);
			if($ExistLogo!=NULL && $ImageFile['name'] != NULL)
			{
				$oldExt				= explode('.',$ExistLogo);
				unlink(NPO_IMAGE_DIR.$ExistLogo);
			}
			
			$DataArray	= array();
			
			if($ImageFile['name'] != "")
			{
				if(move_uploaded_file($ImageFile["tmp_name"],$this->NPOImagePhysPath))
				{
					$DataArray['NPOLogo']	= $Image;
				}
				else
				{
					$this->SetStatus(0,'E5001');	
				}
			}
			
			$DataArray['NPODescription']	= $Description;
			$DataArray['LastUpdatedDate']	= $this->CurrentDate;
			if($this->objutype2->UpdateNpoLogo($DataArray,$this->LoginUserId))
			{
				$this->SetStatus(1,'C5001');	
			}
			else
			{
				$this->SetStatus(0,'E5001');	
			}
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in update NPO details.";
			$lMessage = "Error in update NPO details for user id = $this->LoginUserId";
			if($this->P_status) {
				$sMessage = "NPO detail has updated sucessfully.";
				$lMessage = "NPO detail has updated with user id = $this->LoginUserId";
			}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$userID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objFund));	
			$this->objFund->updateProcessLog($DataArray);	
			redirect(URL."ut2myaccount/dashboard");
		}
		
		private function ManageProfile()
		{
			$DataArray	= array(
				'RU.RU_FistName',
				'RU.RU_LastName',
				'RU.RU_ProfileImage',
				'RU.RU_CompanyName',
				'RU.RU_Designation',
				'RU.RU_Phone',
				'RU.RU_Mobile',
				'RU.RU_City',
				'RU.RU_State',
				'RU.RU_ZipCode',
				'RU.RU_Country',
				'RU.RU_Address1',
				'RU.RU_Address2',
				'RU.RU_Gender',
				'RU.RU_DOB',
				'RU.RU_EmailID');
				
			$this->objutype2->GetUserDetails($DataArray);
			$this->GetCountryList();
			
			$this->getstates($this->objutype2->userDetailsArray['RU_Country']);
			
			$Gender	=  $GLOBALS['gender'];
			
			$this->load_model('Common','objCommon');
			
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ut2_manage_profile'));
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));					
			$this->tpl->assign("gender", $Gender);
			$this->tpl->assign("UserDetails", $this->objutype2->userDetailsArray);
			$this->tpl->draw("ut2myaccount/manageprofile");
		}
		
		private function Update()
		{
			$this->objutype2->FirstName		= request('post','fname',0);
			$this->objutype2->LastName		= request('post','lname',0);
			$this->objutype2->Address1		= request('post','Address1',0);
			$this->objutype2->Address2		= request('post','Address2',0);
			$this->objutype2->City			= request('post','city',0);
			$this->objutype2->Zip			= request('post','zipCode',0);
			$this->objutype2->Country		= request('post','country',0);
			$this->objutype2->State			= request('post','state',0);
			$this->objutype2->PhoneNumber	= request('post','phoneNumber',0);
			$this->objutype2->Mobile		= request('post','altPhoneNumber',0);
			$this->objutype2->CompanyName	= request('post','company',0);
			$this->objutype2->Designation	= request('post','designation',0);
			$this->objutype2->DOB			= ChangeDateFormat(request('post','dob',0),"Y-m-d","m/d/Y");
			$this->objutype2->Gender		= request('post','gender',0);
			$this->objutype2->UpdateDate	= getDateTime();
						
			$this->objutype2->UpdateDB();
			/*----update process log------*/
			$userType 	= 'UT2';
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage 	= "Error in update user details";
			$lMessage 	= "Error in update user details";
			if($this->objutype2->Pstatus)
			{
				$sMessage = "User details updated";
				$lMessage = "User details updated";
			}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$userID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objutype2));	
			$this->objutype2->updateProcessLog($DataArray);	
				/*-----------------------------*/
			
			redirect(URL."ut2myaccount");
		}
		
		private function Dashboard()
		{
			unsetSession("confirmnpodetail");
			//sort parameters
			$sortfrom = request('get','sortfrom',0);
			if($sortfrom=='')
			{
				$sortfrom="ASC";
			}
			if(isset($sortfrom) && request('get','sortfrom',0)!='')
			{
				if($sortfrom=="ASC" && request('get','sortfrom',0)!='')
				{
					$sortfrom="DESC";
				}
				else
				{
					$sortfrom="ASC";
				}
			}
			$sortto   = request('get','sortto',0);
			if($sortto=="DonorName" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="RU.RU_FistName $sortfrom";
				$sortto="DonorName";
			}
			else if($sortto=="Type" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Date" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_DateTime $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Amount" && $sortfrom!='')
			{
				$this->objut2report->SortOrder="PDD.PDD_Cost $sortfrom";
				$sortto="Date";
			}
			else
			{
				$this->objut2report->SortOrder =" PDD.PDD_DateTime DESC";
			}
			//end of code
			
			
			$this->objutype2->GetUserDetails();
			$NpoDetails	= $this->GetNPOProfileDetail();
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('npodashboard');
			
			$UserName		= $this->objutype2->userDetailsArray['RU_FistName']." ".$this->objutype2->userDetailsArray['RU_LastName'];
			$UserID         = $this->objutype2->userDetailsArray['RU_ID'];
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$arrMetaInfo["ut2userdetail"] = strtr($arrMetaInfo["ut2userdetail"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,'{{Image}}'=>$Image,'{{NPOName}}'=>$NpoDetails['NPO_Name']));
			$arrMetaInfo["pageheading"]=strtr($arrMetaInfo["pageheading"],array('{{UserName}}' => $UserName));
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));	
			$this->tpl->assign($arrMetaInfo);

			//donation list array
	
			$NPODetails = $this->objutype2->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);			
			$this->objut2report->NPOEIN = $NPODetails["NPOEIN"];
			
			//$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut2report->GetDonationDetails(array('PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','REPLACE(RU.RU_EmailID,"_DNB","")RU_EmailID'));
			$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			
			$this->tpl->assign('LoggedUserID',$UserID);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->assign('UserDetail',$this->objutype2->userDetailsArray);
			$this->tpl->draw("ut2myaccount/dashboard");	
		}
		
		private function GetCountryList()
		{
			$this->load_model('Common','objcommon');
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList = $this->objcommon->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		public function getstates($CountryAbr)
		{
			$this->load_model("Common","objcommon");
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			$this->tpl->assign("StateList",$StateList);
			return $StateList;
		}
		
		public function getstateajax()
		{
			$CountryAbr	= request('post','CountryAB',0);
			$this->load_model("Common","objcommon");
			$StateList	= '';
			if($CountryAbr != '')
				$StateList	= $this->objcommon->getStateList($CountryAbr);
				
			echo json_encode($StateList);
			exit;
		}
		
		
		public function FundraiserEdit($CampID='') {
			if(!$this->objutype2->checkLogin(getSession('Users')))
				redirect(URL . "ut2/npo-login");
			
			$CampID = keyDecrypt($CampID);
			$this->load_model('Common','objCom');
			
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$DataArray=array('*','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage');
			$this->objFund->F_Camp_ID = $CampID;
			
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('ut1_edit_fundraiser');
			
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);			
			//dump($FundraiserDetail);
			//get fundraiser details
			$DonationArray=array();
			if(isset($CampID) && $CampID!='')
			{	
				$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
				if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
					$this->objut2report->Condition = " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
				else
					$this->objut2report->Condition = " AND PDD.PDD_CampID=".$CampID;
				
				$DonationArray	= $this->objut2report->GetDonationFundDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
				//dump($DonationArray);
				//$this->tpl->assign("DonationArray",$DonationArray);
				
			}	
			
			//end of code			
			$this->tpl->assign("DonationArray", $DonationArray);
			$this->tpl->assign("DonCount",count($DonationArray));
			//$this->tpl->assign('UsedDetail', $UsedDetail);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('FundraiserDetail', $FundraiserDetail[0]);
			$this->tpl->assign('arrBottomMetaInfo', $this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw("ut2myaccount/fundraiseredit");	
			
		}
		
		//----------------- Fundraiser comment section code start here-----------------
		public function FundraiserComment($FundraiserID)
		{
			$this->load_model('Common','objCom');
			$this->objFund = new Fundraisers_Model();

			if(trim($FundraiserID)!='') 
			{
				$FundraiserID = keyDecrypt($FundraiserID);
				$this->objFund->FC_FundraiserId=$FundraiserID;
				$this->objFund->FC_PageNo=1;
				$this->objFund->FC_PageLimit=$this->FC_PageLimit;
				$fundraiserComment=$this->objFund->GetFundraiserComment();
				
					/*----code for get funcraiser detail start code--*/
					$DataArray=array('*','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage');
					$this->objFund->F_Camp_ID=$FundraiserID;
					$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
					
					if(isset($FundraiserID) && $FundraiserID!='')
					{
						$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
						$this->objut2report->Condition = " AND PDD.PDD_CampID=".$FundraiserID;
						$DonationArray	= $this->objut2report->GetDonationFundDetails(array('PDD.	PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
						$this->tpl->assign("DonationArray",$DonationArray);
					}
					//$this->tpl->assign('UsedDetail',$UsedDetail);
					$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
					/*-----------end code------------*/
					
					$this->tpl->assign($this->objCom->GetPageCMSDetails('ut1_edit_fundraiser'));  //ut2_fundraiser_comment
					$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
					$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
					$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
					$this->tpl->assign("fcList",$fundraiserComment);
					$this->tpl->assign("PageNo",1);
					$this->tpl->assign("fcTotalCount",$this->objFund->FC_TotalRecord);
					$this->tpl->assign("FundraiserID",$FundraiserID);
					$this->tpl->draw("ut2myaccount/fundraisercomment");	
			}
			else
			{
				redirect(URL.'ut2myaccount');
			}
		}
		
		//on myaccount page
		public function getFundraiserCommentByAjax()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$input=file_get_contents('php://input');
			parse_str($input);
			$this->objFund->FC_PageNo		= $pageNo;
			$this->objFund->FC_PageLimit	= $this->FC_PageLimit;
			$this->objFund->FC_TotalRecord	= $totalRecord;
			$this->objFund->FC_FundraiserId	= $fundraiserId;
			
			$res=$this->objFund->GetFundraiserComment();
			$returnData=$this->getFundraiserCommentHTML($res);	
			echo $returnData;exit;
		}
		
		public function getFundraiserCommentHTML($res)
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$html='';
			for($i=0;$i<count($res);$i++)
			{
				$html.='<div class="comment-box">';
				$html.='<input type="hidden" name="Camp_Cmt_ID" id="Camp_Cmt_ID" value="'.$res[$i]['Camp_Cmt_ID'].'">';
				$html.='<input type="hidden" name="Camp_Cmt_RUID" id="Camp_Cmt_RUID" value="'.$res[$i]['Camp_Cmt_RUID'].'">';
				$html.='<h3 class="comment-heading">'.$res[$i]['Camp_Cmt_UserName'].'</h3>';
				$html.='<div class="comment-content">'.$res[$i]['Camp_Cmt_Comment'].'</div>';
				$html.='<div class="oh">';
				$html.='<a href="javascript://" class="t-green mr-10 editComment" title="Click here to edit">Edit Comment</a>';
				$html.='<a href="javascript://" class="t-green mr-10 saveComment" title="Click here to save comment">Save Comment</a>';
				if($res[$i]['Camp_Cmt_ShowOnWebsite']==1)
				{
					$title='Click here to unapprove';
					$text='UnApprove';
				}
				else
				{
					$title='Click here to approve';
					$text='Approve';
				}
				$html.='<a href="javascript://" class="t-green mr-10 approveComment" title="'.$title.'" appstatus="'.$res[$i]['Camp_Cmt_ShowOnWebsite'].'">';
				$html.='<span class="approveText">'.$text.'</span>';
				$html.='</a>';
				$html.='<a href="javascript://" class="t-green removeComment" title="Click here to remove">Remove</a>';
				$html.='</div>';
				$html.='</div>';
			}
			return $html;
		}
		//-------------------------------------------------------
		// on comment page
		public function getFundraiserCommentBlockByAjax()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$input=file_get_contents('php://input');
			parse_str($input);
			$this->objFund->FC_PageNo		= $pageNo;
			$this->objFund->FC_PageLimit	= $this->FC_PageLimit;
			$this->objFund->FC_TotalRecord	= $totalRecord;
			$this->objFund->FC_FundraiserId	= $fundraiserId;
			
			$res=$this->objFund->GetFundraiserComment();
			$returnData=$this->getFundraiserCommentBlockHTML($res);	
			echo $returnData;exit;
		}
		
		public function getFundraiserCommentBlockHTML($res)
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$html='';
			for($i=0;$i<count($res);$i++)
			{
				$html.='<div class="comment-box">';
				$html.='<h3 class="comment-heading">'.$res[$i]['Camp_Cmt_UserName'].'</h3>';
				$html.='<div class="comment-content">'.$res[$i]['Camp_Cmt_Comment'].'</div>';
				$html.='</div>';
			}
			return $html;
		}
		
		
		
		public function updateFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;
			$this->objFund->FC_CommentContent	= $commentContent;
			
			$returnStatus=$this->objFund->processUpdateFundraiserComment();
			/*----update process log------*/
				$userType 	= 'UT2';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['user_fullname'];
				$sMessage = "Error in update fundraiser comment";
				$lMessage = "Error in update fundraiser comment";
				if($returnStatus)
				{
					$sMessage = "Fundraiser comment updated";
					$lMessage = "Fundraiser comment updated as ".$commentContent." with id - ".$commentId;
				}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
		}

		public function deleteFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;

			$returnStatus=$this->objFund->processDeleteFundraiserComment();
			/*----update process log------*/
				$userType 	= 'UT2';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['user_fullname'];
				$sMessage = "Error in fundraiser comment deletion";
				$lMessage = "Error in fundraiser comment deletion";
				if($returnStatus)
				{
					$sMessage = "Fundraiser comment deleted";
					$lMessage = "Fundraiser comment deleted with id - ".$commentId;
				}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
		}
		
		public function approveFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.' called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;
			$this->objFund->FC_approveStatus	= $appStatus;

			$returnStatus=$this->objFund->processApproveFundraiserComment();
			/*----update process log------*/
				$userType 	= 'UT2';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['user_fullname'];
				$sMessage = "Error in fundraiser comment status changing";
				$lMessage = "Error in fundraiser comment status changing";
				if($returnStatus)
				{
					$sMessage = "Fundraiser comment status changed";
					$lMessage = "Fundraiser comment status changed as ".$appStatus." with id - ".$commentId;
				}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$fundraiserId,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
		}


		
		public function FundraiserBasicDetail($FR_id)
		{
			$this->load_model('Common','objCom');
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('TeamFundraiserBasicDetail');
			$DataArray=array('*','concat_ws(", ",Camp_Location_City,Camp_Location_State,Camp_Location_Country) as Camp_Location');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray,'');
			if($FundraiserDetail[0]['Camp_TeamUserType']=='T')
				redirect(URL.'ut2myaccount/TeamFundraiserBasicDetail/'.$FR_id);
			$Camp_StylingTemplateName = $FundraiserDetail[0]['Camp_StylingTemplateName'];
			$getCampStyleTemplateColor = unserialize($FundraiserDetail[0]['Camp_StylingDetails']);
			//get color scheme values
			$this->objFund->Camp_Level_ID = $FundraiserDetail[0]['Camp_Level_ID'];
			$this->objFund->StyleTemplateName = $FundraiserDetail[0]['Camp_StylingTemplateName'];
			$this->objFund->getAllStyleColor();
			//end of code
			
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			$CampaignCategoryList = $this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('Camp_StylingTemplateName',$Camp_StylingTemplateName);
			$this->tpl->assign('Camp_Level_ID',$this->objFund->Camp_Level_ID);
			//$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			
			
			if(isset($this->objFund->StyleColorArray) && count($this->objFund->StyleColorArray) > 0)
				$this->tpl->assign('CampaignStyleColor',$this->objFund->StyleColorArray);
			//end of code
			if(isset($getCampStyleTemplateColor) && count($getCampStyleTemplateColor) > 0)
				$this->tpl->assign('CampStyleTemplateColor',$getCampStyleTemplateColor);
				
			$this->tpl->draw("ut2myaccount/fundraiserbasicdetail");	
		}
		
		public function UpdateFundraiserBasicDetail()
		{
			
			$postdata = $_POST;
			$this->objFund->F_Camp_Cat_ID=request('post','category',0);
			$FR_id=request('post','FR_id',0);
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->camp_bgImage = $_FILES['camp_bgImage'];
			$this->objFund->F_Camp_ID=$FR_id;
			$this->objFund->F_Camp_Code = request('post','Camp_Code',0);
			$this->objFund->F_Camp_Title=request('post','title',0);
			$this->objFund->F_Camp_UrlFriendlyName=RemoveSpecialChars($this->objFund->F_Camp_Title);
			$this->objFund->F_Camp_ShortDescription=request('post','subTitle',0);
			
			$this->objFund->F_Camp_DonationGoal=request('post','donation',0);
			
			$this->objFund->F_Camp_DateSpecified=request('post','radio1',0);//start date check
			$this->objFund->F_Camp_Duration_Days=request('post','FR_DurationDays',0);
			$this->objFund->F_Camp_SpecifiedDate=request('post','specifiedDate',0);//start date
			$this->objFund->F_Camp_IsPrivate=request('post','checkbox',0);
			$this->objFund->F_Camp_Location_City=request('post','Camp_Location_City',0);
			$this->objFund->F_Camp_Location_State=request('post','Camp_Location_State',0);
			$this->objFund->F_Camp_Location_Country=request('post','Camp_Location_Country',0);
			$this->objFund->F_Camp_Location_Logitude=request('post','Camp_Location_Logitude',0);
			$this->objFund->F_Camp_Location_Latitude=request('post','Camp_Location_Latitude',0);
			$this->objFund->F_Camp_DescriptionHTML= strip_tags(request('post','aboutFundraiser',0));
			$this->objFund->F_Camp_SalesForceID=request('post','Camp_SalesForceID',0);
			$this->objFund->Camp_StylingDetails=$this->getStylingDetails($postdata);
			
			$facebookURL=request('post','facebookURL',0);			
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);			
			$this->objFund->F_Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL));
			$this->objFund->ProcessFundraiserBasicDetail();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in update fundraiser details";
			$lMessage = "Error in update fundraiser details";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser details updated";
				$lMessage = "Fundraiser details updated";
				
				$Status=$this->SendFundraiserMailAfterUpdateOwnerUT2($FR_id);
				if($Status)
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
				}
				else
				{
					$this->SetStatus(0,'E13017');
				}
				$Status1=$this->SendFundraiserMailAfterUpdateWebmasterUT2($FR_id);
				if($Status1)
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
				}
				else
				{
					$this->SetStatus(0,'E13017');
				}
			}
			else
			{
				$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$FR_id,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/
			redirect(URL.'ut2myaccount/FundraiserBasicDetail/'.keyEncrypt($FR_id));
		}
		
		public function TeamFundraiserBasicDetail($FR_id)
		{
			$this->load_model('Common','objCom');
			$arrMetaInfo = $this->objCom->GetPageCMSDetails('TeamFundraiserBasicDetail');
			//dump($arrMetaInfo);
			$DataArray = array('*','concat_ws(", ",Camp_Location_City,Camp_Location_State,Camp_Location_Country) as Camp_Location');
			$this->objFund->F_Camp_ID = keyDecrypt($FR_id);
			
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				redirect(URL.'ut2myaccount/FundraiserBasicDetail/'.$FR_id);
				
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			
			$CampaignCategoryList = $this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			//$this->tpl->assign('Camp_StylingTemplateName',$Camp_StylingTemplateName);	
			$this->tpl->assign('STRIPE_TEAM_FUNDARISER_CONNECT_URL',STRIPE_TEAM_FUNDARISER_CONNECT_URL);			
			
			//$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));	
							
			$this->tpl->draw('ut2myaccount/teamfundraiserbasicdetail');	
		}
		
		public function UpdateTeamFundraiserBasicDetail()
		{
			$postdata = $_POST;
			//echo "<pre>";print_r($_POST);
			//exit;						
			$Cat_ID=request('post','category',0);
			$FR_id=request('post','FR_id',0);
			$this->objFund->Image = $_FILES['uploadPhoto'];			
			$this->objFund->F_Camp_ID= $FR_id = keyDecrypt($FR_id);
			
			$DataArray=array('Camp_PaymentMode','Camp_Stripe_Status');
			$Condition = " AND Camp_ID=".$FR_id;
			$arrCampDetails = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			// test here ok
			$ShortDescription=request('post','subTitle',0);
			$UrlFriendlyName=request('post','urlFriendlyName',0);
			$DonationGoal=request('post','donation',0);			
			$Status=(request('post','startFund',1))?15:NULL;
			$City=request('post','Camp_Location_City',0);
			$State=request('post','Camp_Location_State',0);
			$Country=request('post','Camp_Location_Country',0);
			$Logitude=request('post','Camp_Location_Logitude',0);
			$Latitude=request('post','Camp_Location_Latitude',0);
			$facebookURL=request('post','facebookURL',0);			
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);			
			$Camp_NonAccountNumber=request('post','UUID',0);
			$Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL));
			
			if($this->P_status == 1 && $FR_id=='')$this->SetStatus(0,'E13000');
			if($this->P_status == 1 && $Cat_ID=='')$this->SetStatus(0,'E13002');			
			if($this->P_status == 1 && $DonationGoal=='')$this->SetStatus(0,'E13004');
			if($this->P_status == 1 && $Status!=NULL && $arrCampDetails[0]['Camp_PaymentMode'] =='INDIVIDUAL-STRIPE-ACCOUNT' && $arrCampDetails[0]['Camp_Stripe_Status']!=1 && $Camp_NonAccountNumber=='')$this->SetStatus(0,'E13020');			
			$DataArray=array("Camp_Cat_ID"=>$Cat_ID,
						"Camp_Status"=>$Status,
						"Camp_ShortDescription"=>$ShortDescription,
						"Camp_DonationGoal"=>$DonationGoal,
						"Camp_SocialMediaUrl"=>$Camp_SocialMediaUrl,						
						"Camp_Location_City"=>$City,
						"Camp_Location_State"=>$State,
						"Camp_Location_Country"=>$Country,
						"Camp_Location_Logitude"=>$Logitude,
						"Camp_Location_Latitude"=>$Latitude,						
						"Camp_LastUpdatedDate"=>getDateTime(),
						"Camp_Locale"=>GetUserLocale()									
						);
			if($Status==NULL)
				unset($DataArray['Camp_Status']);				
			$Condition = " Camp_ID =".$FR_id;			
			if($this->P_status && $Status!=NULL)
			{
				if($arrCampDetails[0]['Camp_PaymentMode'] =='INDIVIDUAL-STRIPE-ACCOUNT' && $arrCampDetails[0]['Camp_Stripe_Status']!=1)
				{
					$arrNPODetails = $this->objFund->TeamFundraiserCheckUUIDForNPO($Camp_NonAccountNumber);

					if($arrNPODetails==false)
					{
						$this->SetStatus(0,'E13016');
					}
					else
					{
						$DataArray['Camp_NPO_EIN'] 				= $arrNPODetails['NPOEIN'];
						$DataArray['Camp_Stripe_Status'] 		= $arrNPODetails['Status'];
						$DataArray['Camp_Stripe_ConnectedID'] 	= $arrNPODetails['Stripe_ClientID'];
						$DataArray['Camp_TaxExempt'] 			= $arrNPODetails['NPO_DedCode'];
						$DataArray['Camp_PaymentMode']			= "NPO-STRIPE-ACCOUNT";
					}
				}				
			}			
			if($this->P_status)
				$this->objFund->UpdateTeamFundraiserBasicDetail($DataArray,$Condition);
			if($this->objFund->P_status)
				$this->objFund->ThumbImageBasicDetail();
				
			if($this->P_status)
			{
				/*-----update process log------*/
				$userType 	= 'UT2';					
				$userID 	= $this->LoginUserId;
				$userName	= $this->LoginUserDetail['user_fullname'];
				$sMessage = "Error in update team fundraiser details";
				$lMessage = "Error in update team fundraiser details";
				if($this->objFund->P_status)
				{
					$sMessage = "Team fundraiser details updated";
					$lMessage = "Team fundraiser details updated";
					
					if($Status==15)
					{
						$this->SendFundraiserMailAfterUpdateOwnerUT2($FR_id);
						$this->SendFundraiserMailAfterUpdateWebmasterUT2($FR_id);	
						
						$msg['EN']="<strong>Congratulations!</strong> Your team fundraiser has been successfully started. <a href='".URL."fundraiser-preview/".keyEncrypt($FR_id)."/".$UrlFriendlyName."' target='_new'>Click here to preview it.</a><br/>To further personalize your fundraiser, add Photos & Videos here: <a href='".URL."ut2myaccount/FundraiserPhotoVideo/{$id}".keyEncrypt($FR_id)."'><strong>Manage Photos & Videos</strong></a>";					
						$msg['ES']="<strong>&iexcl;Felicitaciones!</strong> Su Recaudaci&oacute;n de Fondos de Equipo se ha iniciado correctamente. <a href='".URL."fundraiser-preview/".keyEncrypt($FR_id)."/".$UrlFriendlyName."' target='_new'>Haga Clic aqu&iacute; para ver un avance.</a><br/>Para personalizar a&uacute;n m&aacute;s su recaudaci&oacute;n de fondos, a&ntilde;ada fotos y v&iacute;deos aqu&iacute;: <a href='".URL."ut2myaccount/FundraiserPhotoVideo/{$id}".keyEncrypt($FR_id)."'><strong>Organice Fotos y Videos</strong></a>";
						$this->SetStatus(1,'000',$msg[_DBLANG_]);
					}
					else
					{
						$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);	
					}
				}
				else
				{
					$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
				}
					$DataArray = array(	"UType"=>$userType,
										"UID"=>$userID,
										"UName"=>$userName,
										"RecordId"=>$this->objFund->F_Camp_ID,
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objFund));	
					$this->objFund->updateProcessLog($DataArray);	
					/*-----------------------------*/
			}
			redirect(URL.'ut2myaccount/TeamFundraiserBasicDetail/'.keyEncrypt($FR_id));
		}
		private function getStylingDetails($postdata)
		{
			$color_array='';
			if(isset($postdata['ColorScheme']) && $postdata['ColorScheme']!='')
			{
				$Color_SCHEME_CSS=keyDecrypt($postdata["CSS_Template"]);
				$Color_SCHEME_CSS=strtr($Color_SCHEME_CSS,array('{{Color_Tag1}}' =>$postdata["ColorTag1"],'{{Color_Tag2}}' =>$postdata["ColorTag2"],'{{Color_Tag3}}' =>$postdata["ColorTag3"],'{{Color_Tag4}}' =>$postdata["ColorTag4"],'{{Color_Tag5}}' =>$postdata["ColorTag5"],'{{Color_Tag6}}' =>$postdata["ColorTag6"],'{{Color_Tag7}}' =>$postdata["ColorTag7"]));
				$Color_SCHEME_CSS=keyEncrypt($Color_SCHEME_CSS);
				$color_array = array('ColorScheme'=>$postdata['ColorScheme'],'ColorTag1'=>$postdata['ColorTag1'],'ColorTag2'=>$postdata['ColorTag2'],'ColorTag3'=>$postdata['ColorTag3'],'ColorTag4'=>$postdata['ColorTag4'],'ColorTag5'=>$postdata['ColorTag5'],'ColorTag6'=>$postdata['ColorTag6'],'ColorTag7'=>$postdata['ColorTag7'],'Color_SCHEME_CSS'=>$Color_SCHEME_CSS);
				$color_array = serialize($color_array );
			}
			return $color_array;
				
		}
		private function SendFundraiserMailAfterUpdateOwnerUT2($FR_id)
		{
				$DataArray=array('Camp_ID','Camp_CP_FirstName','Camp_CP_LastName','Camp_CP_Email');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
				
				$uname=$FundraiserDetail[0]['Camp_CP_FirstName'].' '.$FundraiserDetail[0]['Camp_CP_LastName'];
				
				$this->load_model('Email','objemail');
				$Keyword='FundraiserUpdateOwner';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				$link=URL.'contact.html';
				$tpl->assign('link',$link);
				$tpl->assign('uname',$uname);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				
				$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$FundraiserDetail[0]['Camp_CP_Email'],
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
			    
				unset($Eobj);
				return $Status;
				
			
		}
		private function SendFundraiserMailAfterUpdateWebmasterUT2($FR_id)
		{
			
				$this->load_model('UserType2','objUT2');
				$this->load_model('Email','objemail');
				$DataArray=array('*');
				$this->objFund->F_Camp_ID=$FR_id;	
				$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
				$FundraiserDetail[0]['Camp_ThumbImage_Full_Path']=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$FundraiserDetail[0]['camp_thumbImage']);
				if($FundraiserDetail[0]['Camp_NPO_EIN'])
				{
					$DataArray=array('*');
					$where=" AND NPO_EIN = ".$FundraiserDetail[0]['Camp_NPO_EIN'];
					$npoDetail=$this->objUT2->GetNPODetail($DataArray,$where);
				}
				else
				{
					$npoDetail='';
				}
				
				$Keyword='FundraiserUpdateWebmaster';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$tpl=new View;
				//$tpl->assign('Link',$link);
				$tpl->assign('FundraiserDetail',$FundraiserDetail);
				$tpl->assign('npoDetail',$npoDetail);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				
				$InsertDataArray=array('FromID'=>$FundraiserDetail[0]['Camp_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>'qualdev.test@gmail.com',
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Emobj	= LoadLib('BulkEmail');
				$Status1=$Emobj->sendEmail($id);
				
				unset($Eobj);
				
				return $Status1;
		}
		
		public function FundraiserPhotoVideo($FR_id)
		{
			$this->load_model('Common','objCom');
			$DataArray=array('*');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail= $this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];			
			$ImageList=$this->objFund->CampaignImages();
			$VideoList=$this->objFund->CampaignVideos();
			$CampaignCategoryList	= $this->objFund->GetNPOCategoryList();
			$countPhotos 	= $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Photos'];
			$countVideos	= $FundraiserDetail[0]['Camp_Level_DetailJSON']['Number_of_Videos'];
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('ut1_fundraiser_photo_vedio');     //ut2_edit_photo_video
			$arrMetaInfo['text_upload_photos'] = strtr($arrMetaInfo['text_upload_photos'],array("{{number_photos}}"=>$countPhotos));			
			$arrMetaInfo['text_upload_videos'] = strtr($arrMetaInfo['text_upload_videos'],array("{{number_videos}}"=>$countVideos));
			$this->tpl->assign($arrMetaInfo);	
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			//$this->tpl->assign('UsedDetail',$UsedDetail);			
			$this->tpl->assign('ImageList',$ImageList);
			$this->tpl->assign('VideoList',$VideoList);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw("ut2myaccount/fundraiserphotovideo");	
		}
		
				
		private function ChangePasswordForm()
		{
			$this->load_model('Common','objCommon');
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ut2_account_changepassword_form'));					
			$this->tpl->draw("ut2myaccount/changepassword");
		}


		private function ChangePassword()
		{
			$this->objutype2->ExistPassword		= request('post','cpass',0);
			$this->objutype2->Password			= request('post','npass',0);
			$this->objutype2->ConfirmPassword	= request('post','rpass',0);
			$this->objutype2->UpdateDate		= getDateTime();
			$this->objutype2->ChangePasswordDB();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in change password process";
			$lMessage = "Error in change password process";
			if($this->objutype2->Pstatus)
			{
				$sMessage = "Password changed";
				$lMessage = "Password changed";
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype2));	
				$this->objutype2->updateProcessLog($DataArray);	
				/*-----------------------------*/		
			if($this->objutype2->Pstatus)
			{
				$this->SendMail();
				redirect(URL."ut2myaccount");
			}
			else
			{
				redirect(URL."ut2myaccount/change-password-form");	
			}
		}
		private function SendMail()
		{
			$this->load_model('Email','objemail');
			$Keyword='UT2_MyaccountChangePassword';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$LoginUserDetail	= getSession('Users','UserType2');
			$UserName	=$LoginUserDetail['user_fullname'];
			$UserId	=keyDecrypt($LoginUserDetail['user_id']);
			$UserEmail=$LoginUserDetail['user_email'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('URL',URL);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
		}
		
		
		
		public function UploadImage()
		{			
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Image = $_FILES['uploadPhoto'];
			
			$this->objFund->ProcessUploadImage();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in image uploading";
			$lMessage = "Error in image uploading";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser image uploaded";
				$lMessage = "Fundraiser image uploaded";
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->FR_id,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/		
			
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut2myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));
		}
			
		public function UploadVideo()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->VideoCode = $_POST['videoEmbedCode'];
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Video = $_FILES['uploadVideo'];
			$this->objFund->ProcessUploadVideo();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in video uploading";
			$lMessage = "Error in video uploading";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser video uploaded";
				$lMessage = "Fundraiser video uploaded";
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->FR_id,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/	
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut2myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));			
		}
		

		public function DeleteImage($Image_id)
		{
			$this->objFund->Camp_Image_ID=keyDecrypt($Image_id);
			$FR_id=$this->objFund->ProcessDeleteImage();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Error in image deletion";
			$lMessage = "Error in image deletion";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser image deleted";
				$lMessage = "Fundraiser image deleted with Id - ".$this->objFund->Camp_Image_ID;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$FR_id,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/	
			
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut2myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));
			
		}
		
		public function DeleteVideo($Video_id)
		{
			$this->objFund->Camp_Video_ID=keyDecrypt($Video_id);
			$FR_id=$this->objFund->ProcessDeleteVideo();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->LoginUserId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage 	= "Error in video deletion";
			$lMessage 	= "Error in video deletion";
			if($this->objFund->P_status)
			{
				$sMessage = "Fundraiser video deleted";
				$lMessage = "Fundraiser video deleted with Id - ".$this->objFund->Camp_Video_ID;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$FR_id,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/	
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut2myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));
		}
		
		public function getChartData()
		{
			$result = "[['2004',  0],['2005',  0],['2006',  660],['2007',  1030]]";	
			echo $result;
		}
		
		// ambassador action
		private function AmbassadorPortal() {
						
			
			
			$this->load_model('Common','objCommon');
			$this->objWidget->userId = $this->LoginUserId;
			/*------------ get widget detail----------*/
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			//dump($widgetDetials);
			/*---------------get donations details ---------------*/
			$where = "WHERE 1=1 AND PDD.PDD_PIItemType IN ('NPOD3') AND PT.PT_PaymentStatus=1 and PDD.PDD_RUID=".$this->LoginUserId;
			$DataArray = array('PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType',
								'PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','RU.RU_EmailID');
			$DonationArray	= $this->objut2report->GetDonationDetails($DataArray,$where);
			/*---------------*/
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('documentList',$documentList);
			$this->tpl->assign('DonationArray',$DonationArray);
			$this->tpl->assign('widgetDetials',$widgetDetials);
			$this->tpl->draw("ut2myaccount/ambassadorportal");	
		}
		
		private function addWidget()
		{
			$this->insertWidgetDetail();
			if($this->objWidget->Pstatus==1)
			{
				$this->SetStatus(1,'C20000');			
			}	
			else
			{
				$this->SetStatus(0,'E20000');
			}
			redirect(URL."ut2myaccount/ambassador");
			
		}
		
		private function insertWidgetDetail()
		{
			$UniqueID  = GenerateUniqueAlphaNumeric();			
			$NpoDetails	= $this->GetNPOProfileDetail();						
			$this->objWidget->UniqueKey 	= $UniqueID;
			$this->objWidget->userId		= $this->LoginUserId;
			$this->objWidget->CharityID		= $NpoDetails['NPO_ID'];
			$this->objWidget->NPOEIN		= $NpoDetails['NPO_EIN'];
			$this->objWidget->CharityType	= 'NPOR';
			$this->objWidget->Status		= '1';
			$this->objWidget->CreatedDate	= getDateTime();
			$this->objWidget->UpdatedDate	= getDateTime();
			$this->objWidget->ValidSourceSite	= "";
			$this->objWidget->AddWidget_DB();			
			//$UniqueKey,$userId,$CharityID,$CharityType,$NPOEIN,$Status,$CreatedDate,$UpdatedDate,$ValidSourceSite;
		}
		
		// ambassador action
		private function AmbassadorDocuments() 
		{
			$this->load_model("Common","objCommon");
			$this->objWidget->userId = $this->LoginUserId;
			/*------------ get widget detail----------*/
			$DataArray = array("W_ID","W_UniqueKey","W_RUID","W_CharityID","W_CharityType","W_NPOEIN","W_Status");
			$widgetDetials = $this->objWidget->getWidgetDetail($DataArray);
			//dump($widgetDetials);			
			/*----------------- get document details-------------*/
			$DataArray 		= array('D.DocID','D.DocTitle','D.DocName','D.DocSorting','D.DocUserID');
			$Where 			= " AND D.DocShowOnWebsite='1' AND D.DocUserID = ".$this->LoginUserId;
			$documentList 	= $this->objutype2->getDocumentDetails($DataArray,$Where);
			/*---------------*/			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('widgetDetials',$widgetDetials);
			$this->tpl->assign('documentList',$documentList);
			$this->tpl->draw('ut2myaccount/ambasdor-portaldocument');	
		}
		
		// view all donation list of a fundraiser
		public function viewallfundraiser($CampID='') {
			$fund_id = $CampID;
			if(!$this->objutype2->checkLogin(getSession('Users')))
				redirect(URL . "ut2/npo-login");
				
			if($CampID == '')
				redirect(URL . "ut2myaccount/fundraisers-list");
			
			$CampID = keyDecrypt($CampID);
			$this->objFund->F_Camp_ID = $CampID;
			
			$this->FundraiserCredential();
					
			unsetSession("confirmnpodetail");
					
			$this->objutype2->GetUserDetails();
			$NpoDetails	= $this->GetNPOProfileDetail();
			
			$this->load_model('Common', 'objCommon');

			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('ut2_fundraiser_view_all');
			
			$UserName = $this->objutype2->userDetailsArray['RU_FistName'] . " " . $this->objutype2->userDetailsArray['RU_LastName'];
			//dump($this->objutype2->userDetailsArray);
			$Address1 = $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1 .= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$Image = CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$NPO_Name = $NpoDetails["NPO_Name"];
			
			$arrMetaInfo["pageheading"] = strtr($arrMetaInfo["pageheading"], array('{{npo_name}}'=>$NPO_Name));
			$DataArray=array('Camp_TeamUserType','Camp_Code');
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			/*-----check Payment mode------*/
			if($FundraiserDetail[0]['Camp_PaymentMode']!='INDIVIDUAL-STRIPE-ACCOUNT')
				redirect(URL.'ut2myaccount/FundraiserEdit/'.keyEncrypt($CampID));
			/*------*/
			//get fundraiser details			
			$this->objut2report->SortOrder=" PDD.PDD_DateTime DESC ";
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut2report->Condition = " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut2report->Condition = " AND PDD.PDD_CampID=".$CampID;
			
									
			$DonationArray = $this->objut2report->GetDonationFundDetails(
				array('PDD.PDD_ID', 'PDD.PDD_Status_Notes', 'PDD.PDD_RUID', 'PDD.PDD_PIItemName', 'PDD.PDD_PD_ID', 'PDD.PDD_DateTime', 'PDD.PDD_SubTotal', 'PDD.PDD_Cost', 'PDD.PDD_TaxExempt', 'PDD_PIItemType', 'PDD.PDD_DonationReciptentType', 'CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName', 'RU.RU_EmailID'));
				
			//dump($DonationArray);
			//dump(keyEncrypt($this->objutype2->userDetailsArray['RU_ID']));
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign("DonationArray",$DonationArray);
			
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$montharray = explode(',', $this->objut2report->Month);//dump($montharray);
			$this->tpl->assign('Month', $montharray);
			$this->tpl->assign('Year',$this->objut2report->Year);
			$this->tpl->assign('UserDetail',$this->objutype2->userDetailsArray);
			$this->tpl->assign('UserName',$UserName);
			$this->tpl->assign('UserID', keyEncrypt($this->objutype2->userDetailsArray['RU_ID']));			
			$this->tpl->assign("keyword",$this->objut2report->Keyword);
			$this->tpl->assign("sortfrom",$this->sortfrom);
			$this->tpl->assign("sortto",$this->sortto);
			$this->tpl->assign("fund_id",$fund_id);
			$this->tpl->draw("ut2myaccount/viewallfundraiser");	
		}
		
		// get credential for get all donation list of a fundraiser
		private function FundraiserCredential() {
			
			$this->objut2report->Type = request('get', 'type', 0);
			$id = keyDecrypt(request('get', 'id', 0));
			$this->objut2report->LoggedUserID = request('get', 'id', 0);	
			$this->objut2report->Keyword = request('get', 'keyword', 0);	
			
			//sorting
			$this->objut2report->SortTO =  request('get', 'sortto', 0);
			$this->objut2report->SortFrom =  request('get', 'sortfrom', 0);
			if($this->objut2report->SortTO == '' && $this->objut2report->SortFrom == '') {
				$this->objut2report->SortTO = '';
				$this->objut2report->SortFrom = 'PDD.PDD_DateTime DESC';
			}
			
			$this->objut2report->Month = request('get', 'month', 3);
			$this->objut2report->Year = request('get', 'year', 0);
			$this->objut2report->TaxExempted = request('get', 'taxable', 0);
			if(count($this->objut2report->Month) == 0 && $this->objut2report->Year == '') {
				$this->objut2report->Month = date('m');
				$this->objut2report->Year  =  date('Y');
			}
			
			//sort parameters
			$this->sortfrom = request('get', 'sortform', 0);
			//dump($this->sortfrom);
			if($this->sortfrom == '')
				$this->sortfrom = "ASC";
				
			if($this->sortfrom != '') {
				if($this->sortfrom == "ASC")
					$this->sortfrom = "DESC";
				else
					$this->sortfrom = "ASC";
			}
			//dump($this->sortfrom);
			$this->sortto = request('get', 'sortto', 0);
			if($this->sortto == 'DonorName' && $this->sortfrom != '') {
				$this->objut2report->SortOrder = "RU.RU_FistName $this->sortfrom";
				$this->sortto = 'DonorName';
			} else if($this->sortto == 'Type' && $this->sortfrom != '') {
				$this->objut2report->SortOrder = "PDD.PDD_TaxExempt $this->sortfrom";
				$this->sortto = 'Date';
			} else if($this->sortto == 'Date' && $this->sortfrom != '') {
				$this->objut2report->SortOrder = "PDD.PDD_DateTime $this->sortfrom";
				$this->sortto = 'Date';
			} else if($this->sortto == 'Amount' && $this->sortfrom != '') {
				$this->objut2report->SortOrder = "PDD.PDD_Cost $this->sortfrom";
				$this->sortto = 'Date';
			} else
				$this->objut2report->SortOrder = " PDD.PDD_DateTime DESC";
		}
		// print list of fundraiser 
		public function printfundraiserdonationlist() {
			$this->load_model("Common", "objCommon");
			//get date range in month and year
			$this->objut2report->PddID	= request('post', 'PDD_ID', 3);
			$fundId	= keyDecrypt(request('post', 'fund_id', 0));
			$Username	= request('post', 'username', 0);
			//end of code
			
			//fundraiser list array
			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('ut2_print_fundraiser');
			
			$arrMetaInfo["text_top"] = strtr($arrMetaInfo["text_top"], array('{{name}}' =>$Username,'{{date}}' => date('m-d-Y')));
			
			$DataArray = array('Camp_TeamUserType', 'Camp_Code');
			$this->objFund->F_Camp_ID = $fundId;
			$this->objut2report->Month = request('post', 'month', 3);
			$this->objut2report->Year = request('post', 'year', 0);
			$this->objut2report->Keyword = request('post', 'keyword', 0);
			
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			//get fundraiser details			
			$this->objut2report->SortOrder = " PDD.PDD_DateTime DESC ";
			
			if(count($this->objut2report->PddID) > 0) {
				$d_ids = implode(',', $this->objut2report->PddID);
				$this->objut2report->Condition .= " AND PDD.PDD_ID IN($d_ids) ";
			}
			
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut2report->Condition .= " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut2report->Condition .= " AND PDD.PDD_CampID=" . $fundId;

			$FundraiserArray = $this->objut2report->GetDonationFundDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));				
			//dump($FundraiserArray);
			$this->tpl->assign("FundraiserArray", $FundraiserArray);
			$this->tpl->assign("PrintedDate", date('m-d-Y'));
			$this->tpl->assign("Username", $Username);
			$this->tpl->assign($arrMetaInfo);
			//end of code
				
			$HTML = $this->tpl->draw('ut2myaccount/printfundraiser', true);
			$DP_Obj = LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML = $HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		// export fundraser list
		public function exportfundraiserlist() {
			
			$fundId	= keyDecrypt(request('post', 'fund_id', 0));
			$this->objut2report->PddID	= request('post','PDD_ID',3);
			
			$DataArray = array('Camp_TeamUserType', 'Camp_Code');
			$this->objFund->F_Camp_ID = $fundId;
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			//get fundraiser details
			$this->objut2report->Month = request('post', 'month', 3);
			$this->objut2report->Year = request('post', 'year', 0);
			$this->objut2report->Keyword = request('post', 'keyword', 0);
			$this->objut2report->SortOrder = " PDD.PDD_DateTime DESC ";
			
			if(count($this->objut2report->PddID) > 0) {
				$d_ids = implode(',', $this->objut2report->PddID);
				$this->objut2report->Condition .= " AND PDD.PDD_ID IN($d_ids) ";
			}
			
			if($FundraiserDetail[0]['Camp_TeamUserType']=='C')
				$this->objut2report->Condition .= " AND PDD.PDD_CampCode='".$FundraiserDetail[0]['Camp_Code']."'";
			else
				$this->objut2report->Condition .= " AND PDD.PDD_CampID=".$fundId;

			$fund_array = array('CONCAT(RU.RU_FistName," ",RU.RU_LastName) DonorName', 'PDD.PDD_TaxExempt', 'PDD.PDD_DateTime', 'PDD.PDD_Cost');
			
			$FundraiserArray = $this->objut2report->GetDonationFundDetails($fund_array);
			//dump($FundraiserArray);
			if(count($FundraiserArray) == 0) {
				$messageParams = array("errCode"=>'E18000',"errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				redirect($_SERVER['HTTP_REFERER']);
			}
			//dump($FundraiserArray);
			$fp = fopen($this->ExportFundraiserCSV, 'w+');
			if($fp) {
				$HeaderArr = array("Donor Name", "Tax Exempt", "Date", "Amount");
				$StringArray = implode(",", $HeaderArr) . "\r\n";
				fwrite($fp, $StringArray);
			}
			
			$fp = fopen($this->ExportFundraiserCSV, 'a+');
			$i = 0;
			foreach($FundraiserArray as $val) {
				if($val['PDD_TaxExempt'] == 1)
					$val['PDD_TaxExempt'] = 'YES';
				else
					$val['PDD_TaxExempt'] = 'NO';
					
				$val['PDD_DateTime'] = formatDate($val['PDD_DateTime'], 'm/d/Y');
				fputcsv($fp, $val);		
				$i++;
			}
			//$this->downloadfile();
			
			LoadLib("Download_file");			
			$dFile = new Download_file();
			$title = "ut2_fundraiser_".$this->LoginUserId.".csv";
			$dFile->Downloadfile(EXPORT_CSV_PATH, "ut2_fundraiser_".$this->LoginUserId.".csv", $title);
		}
		
		public function updateStatus($FR_id,$Status,$redirect=0)
		{
			$FR_id = keyDecrypt($FR_id);
			$Status = keyDecrypt($Status);
			
			if($Status==15||$Status==21 || $Status==36)
			{
				$DataArray  = array("Camp_Status"=>$Status,"Camp_LastUpdatedDate"=>getDateTime());
				$pStatus = 0;
				if($this->objFund->SetFundraiserDetails($DataArray,$FR_id))
					$pStatus = 1;
					
				/*----update process log------*/	
				$sMessage 	= 'Error in update fundraiser status.';
				$lMessage 	= "Error in update fundraiser($FR_id) status($Status).";
				if($pStatus) {
					$sMessage = 'Fundraiser status has updated sucessfully.';
					$lMessage = "Fundraiser($FR_id) status($Status) has updated sucessfully.";
				}
				$DataArray = array(	
					'UType'			=>'UT2',
					'UID'			=>$this->LoginUserId,
					'UName'			=>$this->LoginUserDetail['user_fullname'],
					'RecordId'		=>$FR_id,
					'SMessage'		=>$sMessage,
					'LMessage'		=>$lMessage,
					'Date'			=>getDateTime(),
					'Controller'	=>get_class()."-".__FUNCTION__,
					'Model'			=>get_class($this->objFund));	
				$this->objFund->updateProcessLog($DataArray);	
				/*-----------------------------*/	
					
				if($redirect)
					redirect(URL.'ut2myaccount/manageTeamFundraisers/'.keyEncrypt($FR_id));
				else
					redirect(URL.'ut2myaccount/FundraiserEdit/'.keyEncrypt($FR_id));
			}
			
		}
		
		public function stopFundraiserMultiple()
		{
			$cap_fund_id = request('post','cap_fund_id',1);
			$arrCamp_ids = request('post','Camp_ID',2);	
			if(is_array($arrCamp_ids) && count($arrCamp_ids))
			{
				//dump($arrCamp_ids);
				$processed =0;
				foreach($arrCamp_ids as $key => $FR_id)
				{
					$sMessage = 'Error in stop fundraiser.';
					$lMessage = "Error in stop fundraiser($FR_id).";
					$pStatus = 0;
					$DataArray = array("Camp_Status"=>36,"Camp_LastUpdatedDate"=>getDateTime());
					
					if($this->objFund->SetFundraiserDetails($DataArray,$FR_id." AND Camp_Status=15")) {
						$processed +=1; 
						$pStatus = 1;
					}
					
					if($pStatus) {
						$sMessage = 'Fundraiser has stoped sucessfully.';
						$lMessage = "Fundraiser($FR_id) has stoped sucessfully.";
					}
					
					$DataArray = array(	
						'UType'			=>'UT2',
						'UID'			=>$this->LoginUserId,
						'UName'			=>$this->LoginUserDetail['user_fullname'],
						'RecordId'		=>$FR_id,
						'SMessage'		=>$sMessage,
						'LMessage'		=>$lMessage,
						'Date'			=>getDateTime(),
						'Controller'	=>get_class()."-".__FUNCTION__,
						'Model'			=>get_class($this->objFund));	
						
					$this->objFund->updateProcessLog($DataArray);
				}
				if($processed>0)
					$this->SetStatus(1, 'C80003');
			}
			redirect(URL.'ut2myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
		}
		
		
		public function manageTeamFundraisers($CampID)
		{
			if(!$this->objutype2->checkLogin(getSession('Users')))redirect(URL."ut2");
			$DecryptCampID = keyDecrypt($CampID);
			$this->objFund->F_Camp_ID = $DecryptCampID;
			$DataArray=array('Camp_ID','Camp_RUID','Camp_Title','Camp_Code','Camp_TeamUserType','Camp_Status','Camp_Level_ID','Camp_StylingTemplateName');
			$arrFundraiserDetails = $this->objFund->GetFundraiserDetails($DataArray);
			$arrFundraiserDetails = $arrFundraiserDetails[0];
			$DataArray=array('C.Camp_ID','C.Camp_RUID','Camp_Title','Camp_ShortDescription','C.Camp_Code','C.Camp_TeamUserType','C.Camp_Status','C.Camp_Level_ID','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID');
			$Condition = " AND C.Camp_Code='".$arrFundraiserDetails['Camp_Code']."' AND C.Camp_TeamUserType='T' AND C.Camp_Status>=11 and C.Camp_Status<=40 and  C.Camp_Deleted!='1'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			//dump($listTeamFundraisers);
			$this->load_model('Common','objCommon');
			$this->tpl = new View;
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('ManageTeamFundraiser'));
			$this->tpl->assign('captainDetails',$arrFundraiserDetails);						
			$this->tpl->assign('listTeamFundraisers',$listTeamFundraisers);
			$this->tpl->draw('ut2myaccount/manageTeamFundraiser');
		}
		
		public function emailTeamFundraiser()
		{
			//dump($_REQUEST,0);
			$cap_fund_id = request('post','cap_fund_id',1);
			$arrCamp_ids = request('post','Camp_ID',2);
			$mailContent = request('post','messageContent',0);
			//dump($arrCamp_ids,0);
			$csCamp_ids = implode(",",$arrCamp_ids);

			$DataArray=array('Camp_Title','Camp_ShortDescription','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID','RU.RU_ID');
			$Condition = " AND C.Camp_ID IN(".$csCamp_ids.") AND C.Camp_TeamUserType='T'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);
			if(count($listTeamFundraisers)>0)
			{
				foreach($listTeamFundraisers as $key => $arrValue)
				{
					if($this->SendMailToTeamMembers($arrValue,$mailContent))
						$cntSuccess +=1; 		
				}	
				if($cntSuccess==count($listTeamFundraisers))
					$this->SetStatus(1,'C80001');
				else
					$this->SetStatus(0,'E25004');
			}
			redirect(URL.'ut2myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
		}
		
		private function SendMailToTeamMembers($teamDetails,$mailContent)
		{
			$this->load_model('Email','objemail');
			$Keyword='manage_team_fundraiser';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			
			$UserName= $teamDetails['UserName'];
			$UserId	= $teamDetails['RU_ID'];
			$UserEmail=$teamDetails['RU_EmailID'];
			$CampTitle = $teamDetails['Camp_Title'];
			$tpl=new View;
			$tpl->assign('UserName',ucwords($UserName));
			$tpl->assign('CampTitle',$CampTitle);
			$tpl->assign('MailContent',$mailContent);
			$tpl->assign('URL',URL);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			return $Status;
		}
		
		public function sendInvitation()
		{			
			//dump($_REQUEST);
			$cap_fund_id = request('post','cap_fund_id',1);
			$csEmail_ids = request('post','emailIDs',0);
			$mailContent = request('post','messageContent',0);
			//dump($arrCamp_ids,0);
			if($csEmail_ids=='')
				redirect(URL.'ut2myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));
				
			$DataArray=array('Camp_ID','Camp_Title','Camp_Code','Camp_ShortDescription','CONCAT(RU.RU_FistName," ",RU.RU_LastName) UserName','RU.RU_EmailID','RU.RU_ID');
			$Condition = " AND C.Camp_ID = ".$cap_fund_id." AND C.Camp_TeamUserType='C'";
			$listTeamFundraisers = $this->objFund->GetFundraiserUserDetails($DataArray,$Condition);			
			if(count($listTeamFundraisers)>0)
			{
				$this->captainFundraiserDetails = $listTeamFundraisers[0];
				//dump($this->captainFundraiserDetails);
				$arrEmail_ids = explode(",",trim($csEmail_ids));
				//dump($arrEmail_ids);
				$invalidEmails='';
				foreach($arrEmail_ids as $key => $strEmailId)
				{
					$arrNameEmail_ids = explode("-",trim($strEmailId));					
					if(is_email(trim($arrNameEmail_ids[0])))
					{
						if($this->sentInvitationEmail(trim($strEmailId),$mailContent))
						{
							$cntSuccess += 1;
						}
						else
						{
							$invalidEmails .= $arrNameEmail_ids[0]."<br/>";			
						}						
					}
					else
					{
						$invalidEmails .= $arrNameEmail_ids[0]."<br/>";	
					}
				}
				
				if($cntSuccess==count($arrEmail_ids))
					$this->SetStatus(1,'C80001');
				else
					$this->SetStatus(0,'000',"Email not sent on below addresses<br/>".$invalidEmails);
			}
			redirect(URL.'ut2myaccount/manageTeamFundraisers/'.keyEncrypt($cap_fund_id));	
		}
		
		private function sentInvitationEmail($receiverEmail,$mailContent)
		{
			$this->load_model('Email','objemail');
			$Keyword='invite_team_fundraiser';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$LoginUserDetail	= getSession('Users','UserType2');
			//dump($LoginUserDetail);
			$arrNameEmail_ids = explode("-",trim($receiverEmail));	
			$UserName	= $arrNameEmail_ids[1];
			$UserId	=keyDecrypt($LoginUserDetail['user_id']);
			$UserEmail=$LoginUserDetail['user_email'];					
			//dump($this->captainFundraiserDetails);
			$CampUrl = "<a href='".URL."fundraiser/".keyEncrypt($this->captainFundraiserDetails['Camp_ID'])."' target='details'>".$this->captainFundraiserDetails['Camp_Title']."</a>";
			$TeamFundreaiserUrl = "<a href='".URL."team_fundraiser' target='details'>Join Fundraiser</a>";
			$CampCode = $this->captainFundraiserDetails['Camp_Code'];
			$tpl=new view;
			$tpl->assign('UserName',ucwords($UserName));
			$tpl->assign('CampTitle',$CampTitle);
			$tpl->assign('CampUrl',$CampUrl);
			$tpl->assign('TeamFundreaiserUrl',$TeamFundreaiserUrl);
			$tpl->assign('CampCode',$CampCode);
			$tpl->assign('MailContent',$mailContent);
			$tpl->assign('URL',URL);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);	
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$UserId,
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$arrNameEmail_ids[0],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			//var_dump($Status);exit;
			return $Status;
		}
		
		public function deleteTeamMember($camp_id,$c_camp_id)
		{
			$dec_camp_id = keyDecrypt($camp_id);
			if($dec_camp_id =='')
				redirect(URL.'ut2myaccount');
				
			$DataArray = array("Camp_Deleted"=>'1');			
			if($this->objFund->SetFundraiserDetails($DataArray,$dec_camp_id))
				$this->SetStatus(1,'C80002');
			else
				$this->SetStatus(0,'E25005');
				
			/*----update process log------*/
			$sMessage = "Error in delete team fundraiser.";
			$lMessage = "Error in delete team fundraiser.";
			if($this->P_status) {
				$sMessage = "Team member deleted successfully.";
				$lMessage = "Team member deleted successfully.";
			}
			
			$userType = $this->LoginUserDetail['UserType2']['user_type'];
			$DataArray = array(	
				"UType"			=> $userType == '1' ? 'UT1' : $userType == '2' ? 'UT2' : '',
				"UID"			=> keyDecrypt($this->LoginUserDetail['UserType2']['user_id']),
				"UName"			=> $this->LoginUserDetail['user_fullname'],
				"RecordId"		=> $dec_camp_id,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objFund));
				
			$this->objFund->updateProcessLog($DataArray);
			
			redirect(URL.'ut2myaccount/manageTeamFundraisers/'.$c_camp_id);	
		}
		
		private function SetStatus($Status, $Code,$msg='') 
		{			
			if($msg=='')
				$msg = "Custom Confirmation message";
			if($Status) {				
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"=>$msg,
					"msgLog"=>0,									
					"msgDisplay"=>1,
					"msgType"=>2);
				EnPException::setConfirmation($messageParams);
				$this->P_status=1;
			} else {
				$messageParams = array(
					"errCode"=>$Code,
					"errMsg"=>$msg,
					"errOriginDetails"=>basename(__FILE__),
					"errSeverity"=>1,
					"msgDisplay"=>1,
					"msgType"=>1);					
				EnPException::setError($messageParams);
				$this->P_status=0;
			}
		}	
	}
?>