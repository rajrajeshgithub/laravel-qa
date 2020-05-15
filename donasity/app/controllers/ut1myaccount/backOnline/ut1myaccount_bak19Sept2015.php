<?php
	class Ut1myaccount_Controller extends Controller
	{		
		
		public $tpl,$LoginUserDetail;
		public $LoginUserId,$CurrentDate;

		function __construct()
		{

			$this->tpl	= new View;
			$this->load_model('UserType1','objutype1');
			$this->objutype1 = new UserType1_Model();
			$this->load_model('Fundraisers','objFund');
			$this->load_model('Ut1_Reporting','objut1report');
			$this->objut1report = new Ut1_Reporting_Model();
			$this->LoginUserDetail	= getSession('Users');
			$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$this->CurrentDate	= getDateTime();
			//$this->verify_user();
			$this->FC_PageLimit=3;
		}
		
		public function verify_user()
		{	
			if(!$this->objutype1->checkLogin(getSession('Users')))
			{
				redirect(URL."ut1/login");
			}
		}
		
		public function index($type='dashboard')
		{

			//echo $type;exit;
			switch($type)
			{
				case 'dashboard':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Dashboard();
					break;
				case 'change-password-form':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->ChangePasswordForm();
					break;
				case 'change-password':
					$this->ChangePassword();
					break;	
				case 'manage-profile':
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Edit();
					break;	
				case 'update':
					$this->Update();
					break;
				case 'donation-list':
					$this->donation_list();
					break;	
				case 'fundarisers-list':
					$this->getFundariserList();
					break;
				default:
					if(!$this->objutype1->checkLogin(getSession('Users')))redirect(URL."ut1");
					$this->Dashboard();
					break;	
			}	
		}	
		
		public function viewall()
		{
		
			$this->objut1report->Type =  request('get','type',0);
			$enID = request('get','id',0);
			$id = keyDecrypt(request('get','id',0));
			$this->objut1report->LoginUserID =  $id;//request('get','id',0);
			//$this->objut1report->LoggedUserID  = request('get','id',0);
			$this->objut1report->Keyword =  request('get','keyword',0);
			
			//get date range in month and year
			$this->objut1report->Month				= request('get','month',3);
			$this->objut1report->Year				= request('get','year',0);
			$this->objut1report->TaxExempted		= request('get','taxable',0);
			if(count($this->objut1report->Month)==0 && $this->objut1report->Year=='')
			{
				$this->objut1report->Month = date('m');
				$this->objut1report->Year  =  date('Y');
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
				if($sortto=="CauseName" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_PIItemName $sortfrom";
					$sortto="CauseName";
				}
				else if($sortto=="Type" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Date" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_DateTime $sortfrom";
					$sortto="Date";
				}
				else if($sortto=="Amount" && $sortfrom!='')
				{
					$this->objut1report->SortOrder="PDD.PDD_Cost $sortfrom";
					$sortto="Date";
				}
				else 
				{
					$this->objut1report->SortOrder =" PDD.PDD_DateTime DESC";
				}
			//end of code
			
			$msgValues=EnPException::getConfirmation();
			unsetSession("confirmnpodetail");
			$this->tpl->assign("msgValues",$msgValues);
			$this->objutype1->GetUserDetails();
			$NpoDetails	= $this->GetNPOProfileDetail();
			$this->load_model('Common','objCommon');
			$arrMetaInfo	= 	$this->objCommon->GetPageCMSDetails('npodashboard');
			$arrMetaInfo["pageheading"]=strtr($arrMetaInfo["pageheading"],array('{{UserName}}' => $UserDetail['user_fullname']));
				
			$UserName		= $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$NPO_EIN         = $NpoDetails['NPO_EIN'];
			$Image			= CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_PERSON_IMAGE,$NpoDetails['NPOLogo']);
			$arrMetaInfo["ut2userdetail"]=strtr($arrMetaInfo["ut2userdetail"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,
					'{{Image}}'=>$Image,'{{NPOName}}'=>$NpoDetails['NPO_Name']));
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
		
			//donation list array
				
			$NPODetails=$this->objutype1->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			$this->objut1report->NPOEIN=$NPODetails["NPOEIN"];
				
			//$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName','ND.NPO_EIN','CONCAT(ND.NPO_Street," ,",ND.NPO_City)NPOAddress'));
			$this->tpl->assign("DonationArray",$DonationArray);
			//end of code
			$montharray = explode(',',$this->objut1report->Month);
			$this->tpl->assign('Month',$montharray);
			$this->tpl->assign('Year',$this->objut1report->Year);
			$this->tpl->assign('UserDetail',$this->objutype1->userDetailsArray);
			$this->tpl->assign('UserName',$UserName);
			$this->tpl->assign('UserID',$enID);
			$this->tpl->assign("keyword",$this->objut1report->Keyword);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut1myaccount/viewall");
		}
		
		public function printdonationlist()
		{
			$this->tpl=new View;
				
				
			//get date range in month and year
			$this->objut1report->PddID	= request('post','PDD_ID',3);
			$Username	= request('post','username',0);
			//end of code
				
			//donation list array
		
			//$NPODetails=$this->objutype1->GetNPODetail(array("NPOEIN")," AND NUR.USERID=".$this->LoginUserId);
			//$this->objut1report->NPOEIN=$NPODetails["NPOEIN"];
		
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_ID','PDD.PDD_Status_Notes','PDD.PDD_RUID','PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(ND.NPO_Street," ",ND.NPO_City," ",ND.NPO_State," ",ND.NPO_Zip)NPOAddress',
'ND.NPO_Name','ND.NPO_EIN'));
			$this->tpl->assign("DonationArray",$DonationArray);
			$this->tpl->assign("PrintedDate",date('m-d-Y'));
			$this->tpl->assign("Username",$Username);
			//end of code
				
			$HTML=$this->tpl->draw('ut1myaccount/printdonation',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}
		
		private function GetNPOProfileDetail()
		{
			$DataArray	= array("NUR.NPOLogo","NUR.NPOConfirmationCode","NUR.NPODescription","N.NPO_Zip","N.NPO_Name","N.NPO_Street","NPO_City","NUR.Status as Stripe_Status","NUR.Stripe_ClientID as Stripe_ClientID","N.NPO_EIN");
			$Res	= $this->objutype1->GetNPOProfileDetail($DataArray,$this->LoginUserId);
			return $Res;
		}
		
		private function getFundariserList()
		{
			$this->load_model('UserType1','objutype1');
			$this->load_model('Common','objCommon');
				
			$this->LoginUserDetail	= getSession('Users');
			$this->objFradraisersList->npoCondition = " WHERE Camp_RUID=".keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$this->load_model('Fundraisers','objFund');
			$this->objFund = new Fundraisers_Model();
			$Wherecondition = " AND Camp_RUID=".keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$fundraiserlist=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage'),$Wherecondition);
			//dump($fundraiserlist);	
				
			$this->objutype1->GetUserDetails();
			$msgValues=EnPException::getConfirmation();
			$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('UT1_DASHBOARD');
			$UserName	= $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$Address1	= $this->objutype1->UserDetailsArray['RU_Address1']." , ";
			$Address1	.= ($this->objutype1->UserDetailsArray['RU_Address1'] != "")?$this->objutype1->UserDetailsArray['RU_Address2'].", ":"";
			$Address2	.= $this->objutype1->UserDetailsArray['RU_City'];
			$Address2	.= ($this->objutype1->UserDetailsArray['RU_ZipCode'] != "")?" - ".$this->objutype1->UserDetailsArray['RU_ZipCode']:"";
			$Image		= CheckImage(UT1PROFILE_MEDIUM_IMAGE_DIR,UT1PROFILE_MEDIUM_IMAGE_URL,NO_PERSON_IMAGE,$this->objutype1->UserDetailsArray['RU_ProfileImage']);
			$arrMetaInfo["userdetails"]=strtr($arrMetaInfo["userdetails"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,'{{Address2}}' => $Address2,
					'{{EmailID}}' => $this->objutype1->UserDetailsArray['RU_EmailID'],'{{Image}}'=>$Image));
			$this->tpl->assign($arrMetaInfo);
			/* ======== Meta Section End ========== */
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->assign("UserDetail",$this->objutype1->UserDetailsArray);
			$this->tpl->assign('fundraiserlistCount',count($fundraiserlist));	
			$this->tpl->assign("fundraiserlist",$fundraiserlist);
			$this->tpl->draw("ut1myaccount/fundraiserlist");
		}
		
		
		private function Dashboard()
		{
			$this->load_model('Common','objCommon');
			$this->objutype1->GetUserDetails();
			$msgValues=EnPException::getConfirmation();
			
			/*$NpoDetails	= $this->GetNPOProfileDetail();
			$Address1		= $NpoDetails['NPO_Street']." , ".$NpoDetails['NPO_City'];
			$Address1		.= ($NpoDetails['NPO_Zip'] != "")?" - ".$NpoDetails['NPO_Zip']:"";
			$NPO_EIN         = $NpoDetails['NPO_EIN'];*/
			
			
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
			if($sortto=="CauseName" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_PIItemName $sortfrom";
				$sortto="CauseName";
			}
			else if($sortto=="Type" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_TaxExempt $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Date" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_DateTime $sortfrom";
				$sortto="Date";
			}
			else if($sortto=="Amount" && $sortfrom!='')
			{
				$this->objut1report->SortOrder="PDD.PDD_Cost $sortfrom";
				$sortto="Date";
			}
			else
			{
				$this->objut1report->SortOrder =" PDD.PDD_DateTime DESC";
			}
			//end of code
			
			
			
			//$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','ND.NPO_EIN','CONCAT(ND.NPO_Street," ,",ND.NPO_City)NPOAddress'));
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('UT1_DASHBOARD');
			$UserName	= $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$UserID         = $this->objutype1->UserDetailsArray['RU_ID'];
			$Address1	= $this->objutype1->UserDetailsArray['RU_Address1']." , ";
			$Address1	.= ($this->objutype1->UserDetailsArray['RU_Address1'] != "")?$this->objutype1->UserDetailsArray['RU_Address2'].", ":"";
			$Address2	.= $this->objutype1->UserDetailsArray['RU_City'];
			$Address2	.= ($this->objutype1->UserDetailsArray['RU_ZipCode'] != "")?" - ".$this->objutype1->UserDetailsArray['RU_ZipCode']:"";
			$Image		= CheckImage(UT1PROFILE_MEDIUM_IMAGE_DIR,UT1PROFILE_MEDIUM_IMAGE_URL,NO_PERSON_IMAGE,$this->objutype1->UserDetailsArray['RU_ProfileImage']);
			$arrMetaInfo["userdetails"]=strtr($arrMetaInfo["userdetails"],array('{{UserName}}' =>$UserName,'{{Address1}}' => $Address1,'{{Address2}}' => $Address2,
										'{{EmailID}}' => $this->objutype1->UserDetailsArray['RU_EmailID'],'{{Image}}'=>$Image));
			$this->tpl->assign($arrMetaInfo);
			/* ======== Meta Section End ========== */
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->assign("UserDetail",$this->objutype1->UserDetailsArray);
			$this->tpl->assign('DonationArrayCount',count($DonationArray));
			$this->tpl->assign("DonationArray",$DonationArray);
			$this->tpl->assign('LoggedUserID',$UserID);
			$this->tpl->assign("sortfrom",$sortfrom);
			$this->tpl->assign("sortto",$sortto);
			$this->tpl->draw("ut1myaccount/dashboard");	
		}
		
		private function ChangePasswordForm()
		{
			$this->load_model('Common','objCommon');
			$msgValues=EnPException::getConfirmation();
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_changepassword'));
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->draw("ut1myaccount/changepassword");
		}
		
		private function ChangePassword()
		{
			$this->objutype1->ExistPassword		= request('post','cpass',0);
			$this->objutype1->Password			= request('post','npass',0);
			$this->objutype1->ConfirmPassword	= request('post','rpass',0);
			$this->objutype1->UpdateDate		= getDateTime();
			$this->objutype1->ChangePasswordDB();
			if($this->objutype1->Pstatus)
			{
				redirect(URL."ut1myaccount");
			}
			else
			{
				redirect(URL."ut1myaccount/change-password-form");	
			}
		}
		
		private function Edit()
		{
			$DataArray	= array('RU.RU_FistName','RU.RU_LastName','RU.RU_ProfileImage','RU.RU_CompanyName','RU.RU_Designation','RU.RU_Phone','RU.RU_Mobile','RU.RU_City','RU.RU_State',
								'RU.RU_ZipCode','RU.RU_Country','RU.RU_Address1','RU.RU_Address2','RU.RU_Gender','RU.RU_DOB','RU.RU_EmailID');
			$this->objutype1->GetUserDetails($DataArray);
			$this->GetCountryList();
			$this->getstates($this->objutype1->UserDetailsArray['RU_Country']);
			$this->load_model('Common','objCommon');
			//$Gender	=  $GLOBALS['gender'];
			$Gender	=  array('Male'=>'Male','Female'=>'Female');//$GLOBALS['gender'];
			$msgValues=EnPException::getConfirmation();
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_ManageProfile'));
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->assign("gender",$Gender);
			$this->tpl->assign("UserDetails",$this->objutype1->UserDetailsArray);
			$this->tpl->draw("ut1myaccount/manageprofile");
		}
		
		private function Update()
		{
				$this->objutype1->FirstName					= request('post','fname',0);
				$this->objutype1->LastName					= request('post','lname',0);
				$this->objutype1->Address1					= request('post','Address1',0);
				$this->objutype1->Address2					= request('post','Address2',0);
				$this->objutype1->City						= request('post','city',0);
				$this->objutype1->Zip						= request('post','zipCode',0);
				$this->objutype1->Country					= request('post','country',0);
				$this->objutype1->State						= request('post','state',0);
				$this->objutype1->PhoneNumber				= request('post','phoneNumber',0);
				$this->objutype1->Mobile					= request('post','altPhoneNumber',0);
				$this->objutype1->CompanyName				= request('post','company',0);
				$this->objutype1->Designation				= request('post','designation',0);
				$this->objutype1->DOB						=  ChangeDateFormat(request('post','dob',0),"Y-m-d","m/d/Y");
				$this->objutype1->Gender					= request('post','gender',0);
				$this->objutype1->ExistProfileImg   		= request('post','existprofileimg',0);
				$this->objutype1->UpdateDate				= getDateTime();
				$this->objutype1->UploadProfileImage		= $_FILES['uploadProfile'];
				
			  	$this->objutype1->UpdateDB();
				redirect(URL."ut1myaccount");
		}
		
		private function GetCountryList()
		{
			$this->load_model('Common','objcommon');
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objcommon->GetCountryListDB($DataArray,$Condition,$Order);
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
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			echo json_encode($StateList);
			exit;
		}
		
		public function donation_list()
		{
			if(!$this->objutype1->checkLogin(getSession('Users')))
				redirect(URL."ut1");
			
			//$_SERVER['QUERY_STRING'];
			$DonationArray	= $this->objut1report->GetDonationDetails(array('PDD.	PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_DonationReciptentType'));
		}
		
		public function FundraiserEdit($CampID='')
		{
			$CampID = keyDecrypt($CampID);
			$this->load_model('Common','objCom');
			$this->objFund = new Fundraisers_Model();
			$msgValues=EnPException::getConfirmation();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign("msgValues",$msgValues);
			$DataArray=array('*','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage');
			$this->objFund->F_Camp_ID=$CampID;
			$FundraiserDetail = $this->objFund->GetFundraiserDetails($DataArray);
			//get fundraiser details
			if(isset($CampID) && $CampID!='')
			{	
				$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
				$this->objut1report->Condition = " AND PDD.PDD_CampID=".$CampID;
				$DonationArray	= $this->objut1report->GetDonationFundDetails(array('PDD.	PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
				//dump($DonationArray);
				$this->tpl->assign("DonationArray",$DonationArray);
				
			}	
			
			//end of code
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			
			$this->tpl->draw("ut1myaccount/fundraiseredit");	
			
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
						$this->objut1report->SortOrder=" PDD.PDD_DateTime DESC ";
						$this->objut1report->Condition = " AND PDD.PDD_CampID=".$FundraiserID;
						$DonationArray	= $this->objut1report->GetDonationFundDetails(array('PDD.	PDD_PIItemName','PDD.PDD_PD_ID','PDD.PDD_DateTime','PDD.PDD_SubTotal','PDD.PDD_Cost','PDD.PDD_TaxExempt','PDD_PIItemType','PDD.PDD_DonationReciptentType','CONCAT(RU.RU_FistName," ",RU.RU_LastName)DonorName'));
						$this->tpl->assign("DonationArray",$DonationArray);
						
					}	
					$this->tpl->assign('UsedDetail',$UsedDetail);
					$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
					/*-----------end code------------*/

					$msgValues=EnPException::getConfirmation();
					$this->tpl->assign("msgValues",$msgValues);
					$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
					$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
					$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
					$this->tpl->assign("fcList",$fundraiserComment);
					$this->tpl->assign("PageNo",1);
					$this->tpl->assign("fcTotalCount",$this->objFund->FC_TotalRecord);
					$this->tpl->assign("FundraiserID",$FundraiserID);
					$this->tpl->draw("ut1myaccount/fundraisercomment");	
			}
			else
			{
				redirect(URL.'ut1myaccount');	
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
			$this->objFund->FC_approveStatus=1;
			
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
				$html.=' <div class="t-gray f-15"><i>Added on '. $res[$i]['Camp_Cmt_CreatedDate'].'</i></div>';
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
		}

		public function deleteFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;

			$returnStatus=$this->objFund->processDeleteFundraiserComment();
		}
		
		public function approveFundraiserComment()
		{
			EnPException::writeProcessLog('FundraisersDetail_Model'.__FUNCTION__.'called');
			$Input=file_get_contents('php://input');
			parse_str($Input);
			
			$this->objFund->FC_FundraiserId		= $fundraiserId;
			$this->objFund->FC_CommentId		= $commentId;
			$this->objFund->FC_approveStatus	= $appStatus;

			$returnStatus=$this->objFund->processApproveFundraiserComment();
		}

		//----------------- Fundraiser comment section code start here-----------------
		
		public function FundraiserBasicDetail($FR_id)
		{
			$this->load_model('Common','objCom');
			$arrMetaInfo	= $this->objCom->GetPageCMSDetails('fundraiser_detail');
			$DataArray=array('*','concat_ws(", ",Camp_Location_City,Camp_Location_State,Camp_Location_Country) as Camp_Location');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			$msgValues=EnPException::getConfirmation();
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw("ut1myaccount/fundraiserbasicdetail");	
		}
		
		public function UpdateFundraiserBasicDetail()
		{
			$this->objFund->F_Camp_Cat_ID=request('post','category',0);
			$FR_id=request('post','FR_id',0);
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->F_Camp_ID=$FR_id;
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
			$this->objFund->F_Camp_DescriptionHTML=request('post','aboutFundraiser',0);
			$this->objFund->F_Camp_SalesForceID=request('post','Camp_SalesForceID',0);
			
			$facebookURL=request('post','facebookURL',0);
			$googleURL=request('post','googleURL',0);
			$linkedinURL=request('post','linkedinURL',0);
			$twitterURL=request('post','twitterURL',0);
			$instagramURL=request('post','instagramURL',0);
			$youtubeURL=request('post','youtubeURL',0);
			$secretURL=request('post','secretURL',0);
			$mailusURL=request('post','mailusURL',0);
			$this->objFund->F_Camp_SocialMediaUrl=json_encode(array("facebook"=>$facebookURL,"g-plus"=>$googleURL,"linkedin"=>$linkedinURL,"twitter"=>$twitterURL,"instagram"=>$instagramURL,"youtube"=>$youtubeURL,"user-secret"=>$secretURL,"a-at"=>$mailusURL));
			$this->objFund->ProcessFundraiserBasicDetail();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut1myaccount/FundraiserBasicDetail/'.keyEncrypt($FR_id));
		}
		
		public function FundraiserPhotoVideo($FR_id)
		{
			$this->load_model('Common','objCom');
			$DataArray=array('*');
			$this->objFund->F_Camp_ID=keyDecrypt($FR_id);
			$FundraiserDetail=$this->objFund->GetFundraiserDetails($DataArray);
			$FundraiserDetail[0]['Camp_StartDate']=ChangeDateFormat($FundraiserDetail[0]['Camp_StartDate'],"d-m-Y","Y-m-d");
			$FundraiserDetail[0]['Camp_SocialMediaUrl']=json_decode($FundraiserDetail[0]['Camp_SocialMediaUrl'],true);
			$CampLevel=$this->objFund->GetCampaignLevelDetail(array('Camp_Level_ID','Camp_Level_CampID','Camp_Level','Camp_Level_Name','Camp_Level_Desc','Camp_Level_DetailJSON')," AND Camp_Level_ID=".$FundraiserDetail[0]['Camp_Level_ID']);
			$FundraiserDetail[0]['Camp_Level_DetailJSON']=$CampLevel[0]['Camp_Level_DetailJSON'];
			$msgValues=EnPException::getConfirmation();
			$ImageList=$this->objFund->CampaignImages();
			$VideoList=$this->objFund->CampaignVideos();
			
			$CampaignCategoryList=$this->objFund->GetNPOCategoryList();
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('UsedDetail',$UsedDetail);
			$this->tpl->assign("msgValues",$msgValues);
			$this->tpl->assign('ImageList',$ImageList);
			$this->tpl->assign('VideoList',$VideoList);
			$this->tpl->assign('FundraiserDetail',$FundraiserDetail[0]);
			$this->tpl->assign('CategoryList',$CampaignCategoryList);
			$this->tpl->assign('categoryname','NPOCat_DisplayName_'._DBLANG_);
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->draw("ut1myaccount/fundraiserphotovideo");	
		}
		
		public function UploadImage()
		{
			
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Image = $_FILES['uploadPhoto'];
			$this->objFund->ProcessUploadImage();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));
		}
			
		public function UploadVideo()
		{
			$this->FR_id = request('post','FR_id',0);
			$this->objFund->VideoCode = $_POST['videoEmbedCode'];
			$this->objFund->F_Camp_ID=$this->FR_id ;
			$this->objFund->Video = $_FILES['uploadVideo'];
			$this->objFund->ProcessUploadVideo();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($this->objFund->F_Camp_ID));
			
		}	
		public function DeleteImage($Image_id)
		{
			$this->objFund->Camp_Image_ID=keyDecrypt($Image_id);
			$FR_id=$this->objFund->ProcessDeleteImage();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));
			
		}
		public function DeleteVideo($Video_id)
		{	
		
			$this->objFund->Camp_Video_ID=keyDecrypt($Video_id);
			$FR_id=$this->objFund->ProcessDeleteVideo();
			if($this->objFund->P_status)
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ConfirmCode);
			else
			$this->SetStatus($this->objFund->P_status,$this->objFund->P_ErrorCode);
			redirect(URL.'ut1myaccount/FundraiserPhotoVideo/'.keyEncrypt($FR_id));
		}
		
		private function SetStatus($Status,$Code)
		{
			if($Status)
			{
				$messageParams=array("msgCode"=>$Code,
												 "msg"=>"Custom Confirmation message",
												 "msgLog"=>0,									
												 "msgDisplay"=>1,
												 "msgType"=>2);
					EnPException::setConfirmation($messageParams);
			}
			else
			{
				$messageParams=array("errCode"=>$Code,
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