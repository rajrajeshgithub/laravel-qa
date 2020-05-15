<?php
	class Salesubscription_Controller extends Controller
	{
		public $loginDetails, $SS_Id, $P_Status,$RU_Id,$UserType;
		public $arraySaleSubDetails,$CustNum,$ProcessStatus;		
		public $PaymentMethod, $arrayInputData = array(), $filterParam;
		
		function __construct()
		{
			
			checkLogin(32);
			$this->load_model('Common', 'objCMN');
			$this->load_model('SaleSubscription', 'objSaleSub');			
			$this->loginDetails = getsession("DonasityAdminLoginDetail");	
			$this->P_Status = 1;
			$this->statusArray = array(1=>'Pending',2=>'Order Added',3=>'Waiting For Payment',4=>'Waiting For Payment Details From Customer', 11=>'Ready To Charge',12=>'Charge Failed',15=>'Activated',16=>'Completed',21=>'Stoped by Administrator');
			//echo strtotime('2016-03-21 02:17:28');		exit;
		}
		
		public function index()
		{
			$this->showList();
		}
		
		public function newOrder($RU_Id=NULL)
		{
			if($RU_Id!=NULL)
			{	
				list($RU_Id,$UserType) = explode("|",$RU_Id);			
				$this->RU_Id = keyDecrypt($RU_Id);
				$this->UserType = keyDecrypt($UserType);
			}
			$this->showAddOrderForm();	
		}
		
		public function editOrder($SS_Id=NULL)
		{
			if($SS_Id!=NULL)
				$this->SS_Id = keyDecrypt($SS_Id);
			
			$this->showAddOrderForm();
		}
			
		public function order($SS_Id=NULL)
		{
			if($SS_Id!=NULL)
				$this->SS_Id = keyDecrypt($SS_Id);
			else
				redirect(URL.'salesubscription/newOrder');	
				
				
			if($this->SS_Id!='')
			{
				$FieldArray = array('SS_ID','SS_Status','SS_PaySimpleCustomerID','SS_PaySimplePaymentMethodID');
				$this->objSaleSub->SS_Id = $this->SS_Id;
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);		
				
				$status = $arraySaleSubDetails['SS_Status'];	
			}
			
			if($status==1)
				redirect(URL.'salesubscription/EditOrder/'.keyEncrypt($this->SS_Id));
			elseif($status>=2 && $status<=10)
				$this->showAddPaymentForm();
			else
				$this->showOrderDetials();
		}
		
		private function showOrderDetials()
		{
			if(!$this->SS_Id || $this->SS_Id=='')
				redirect(URL.'error');
			
			$countriesList		= $this->objCMN->getCountriesList();
			$stateList			= $this->objCMN->getStateList('US');
			$FieldArray = array('*','SS_ID','SS_RefNumber','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
								'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City',
								'SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaymentMode',
								'SS_Schedule','SS_SpecialInstruction','SS_EnableRecipt','SS_Status','SS_PaySimplePaymentMethodID','count(SSPT_ID) as CountSSPT_ID');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSalesubscriptionTransDetails($FieldArray);		
			if($arraySaleSubDetails['SS_Status']<=10)
				redirect(URL.'salesubscription/order/'.$SS_Id);
			$this->tpl = new view;
			$this->tpl->assign('CountryList',$countriesList);
			$this->tpl->assign('StateList', $stateList);
			$this->tpl->assign('statusArray',$this->statusArray);	
			$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$this->tpl->draw('salesubscription/showEditForm');	
		}
		
		private function showList()
		{		
			$this->Init();
			$this->SetFilterParam();
			
			$FieldArray = array('*');			
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionList($FieldArray, $this->filterParam);	
			//dump($arraySaleSubDetails);
			
			
			$statusGroupArray = array('Order Process'=>array(1=>'Pending',2=>'Order Added',3=>'Waiting For Payment',4=>'Waiting For Payment Details From Customer'),'Payment Process'=>array(11=>'Ready To Charge',12=>'Charge Failed',15=>'Activated',16=>'Completed'),'Admin Action'=>array(21=>'Stoped by Administrator'));

			$itemList = $this->objCMN->getProductList();
			
			//================= pagination code start =================
			$Page_totalRecords = $this->objSaleSub->SaleSubListTotalRecord;
			$PagingArr=constructPaging($pageSelected, $Page_totalRecords,$this->objSaleSub->pageLimit);
			$LastPage = ceil($Page_totalRecords / $this->objSaleSub->pageLimit);
			$this->tpl = new view;
			$this->tpl->assign("pageSelected",$pageSelected);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$LastPage);
			$this->tpl->assign('totalrecords',$Page_totalRecords);
			$this->tpl->assign('statusArray',$this->statusArray);	
			$this->tpl->assign('statusGroupArray',$statusGroupArray);		
			$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$this->tpl->assign('itemList',$itemList);
			$this->tpl->assign('fromDate', $this->arrayInputData['fromDate']);
			$this->tpl->assign('toDate', $this->arrayInputData['toDate']);
			$this->tpl->assign('dd_status', $this->arrayInputData['dd_status']);
			$this->tpl->assign('dd_service_type', $this->arrayInputData['dd_service_type']);
			$this->tpl->assign('dd_filter_by', $this->arrayInputData['dd_filter_by']);
			$this->tpl->assign('searchKeyword', $this->arrayInputData['searchKeyword']);
			$this->tpl->assign('payment_mode_rec', $this->arrayInputData['payment_mode_rec']);
			$this->tpl->assign('payment_mode_opt', $this->arrayInputData['payment_mode_opt']);
			$this->tpl->draw('salesubscription/showList');	
		}
		
		private function Init() {
			$pageSelected = request('get', 'pageNumber', 0);
			if($pageSelected == '')
				$pageSelected = 1;
		
				$this->objSaleSub->pageSelectedPage = $pageSelected;
					
				//dump($_POST);
				//$filterData = unserialize(keyDecrypt(getSession('filterInput')));
					
				//if(is_array($filterData) && count($filterData) > 0) {
					//$fromDate = $filterData['fromDate'];
					//$toDate = $filterData['toDate'];
					//$dd_status = $filterData['dd_status'];
					//$dd_frequency = $filterData['dd_frequency'];
					//$searchKeyword = $filterData['searchKeyword'];
				//} else {
					$fromDate = request('get', 'fromDate', 0);
					$toDate = request('get', 'toDate', 0);
					$dd_status = request('get', 'dd_status', 0);
					$dd_service_type = request('get', 'dd_service_type', 0);
					$dd_filter_by = request('get', 'dd_filter_by', 0);
					$searchKeyword = request('get', 'searchKeyword', 0);
					$payment_mode_rec = request('get', 'payment_mode_rec', 0);
					$payment_mode_opt = request('get', 'payment_mode_opt', 0);
				//}
		//dump($payment_mode_opt);
				$this->arrayInputData  = array(
						'fromDate' => $fromDate,
						'toDate' => $toDate,
						'dd_status' => $dd_status,
						'dd_service_type' => $dd_service_type,
						'dd_filter_by' => $dd_filter_by,
						'searchKeyword' => $searchKeyword,
						'payment_mode_rec' => $payment_mode_rec,
						'payment_mode_opt' => $payment_mode_opt,
				);
				//dump($this->arrayInputData['payment_mode']);	
				//if(getSession('filterInput') == '' && $this->objSaleSubReport->isExport == 1)
					//setSession('filterInput', keyEncrypt(serialize($this->arrayInputData)));
		}
		
		// set filter parameters to appand sql query
		private function SetFilterParam() {
			$fromDate = $this->arrayInputData['fromDate'] == '' ? getNextDate(30,'-') : $this->arrayInputData['fromDate'];
			$toDate = $this->arrayInputData['toDate'] == '' ? getDateTime(0,'Y-m-d') : $this->arrayInputData['toDate'];
			$this->arrayInputData['fromDate'] = formatDate($fromDate, 'Y-m-d');
			$this->arrayInputData['toDate'] = formatDate($toDate, 'Y-m-d');
			//dump($this->arrayInputData['dd_service_type']);
			$this->filterParam = "";
			$filterBy = "";
			if($this->arrayInputData['dd_status'] != '')
				$this->filterParam .= " AND SS_Status = '". $this->arrayInputData['dd_status'] ."'";
			
			if($this->arrayInputData['dd_service_type'] != '')
				$this->filterParam .= " AND SS_ItemCode = '". $this->arrayInputData['dd_service_type'] ."'";
			
			if($this->arrayInputData['dd_filter_by'] == '1')	
				$filterBy = "SS_DateTime";
			if($this->arrayInputData['dd_filter_by'] == '2')
				$filterBy = "SS_NextOuccringDate";
			if($filterBy != '' && $this->arrayInputData['fromDate'] != '' && $this->arrayInputData['toDate'] != '')
				$this->filterParam .= " AND DATE_FORMAT(". $filterBy .", '%Y-%m-%d')>='" . $this->arrayInputData['fromDate'] . "' AND DATE_FORMAT(". $filterBy .", '%Y-%m-%d')<='" . $this->arrayInputData['toDate'] . "' ";
			
			if($this->arrayInputData['payment_mode_rec'] == '1' && $this->arrayInputData['payment_mode_opt'] == '1')
				$this->filterParam .= " AND (SS_PaymentMode = 'RC' OR SS_PaymentMode = 'OTP')";
			elseif($this->arrayInputData['payment_mode_rec'] == '1')
				$this->filterParam .= " AND SS_PaymentMode = 'RC'";
			elseif($this->arrayInputData['payment_mode_opt'] == '1')
				$this->filterParam .= " AND SS_PaymentMode = 'OTP'";
			
			if($this->arrayInputData['searchKeyword'] != '')
				$this->filterParam .= " AND (SS_RefNumber LIKE '%" . $this->arrayInputData['searchKeyword'] . "%' OR SS_FirstName LIKE '%" . $this->arrayInputData['searchKeyword'] . "%' OR SS_LastName LIKE '%" . $this->arrayInputData['searchKeyword'] . "%' OR SS_EmailAddress LIKE '%" . $this->arrayInputData['searchKeyword'] . "%' OR SS_PaySimpleCustomerID LIKE '%" . $this->arrayInputData['searchKeyword'] . "%' OR SS_Phone LIKE '%" . $this->arrayInputData['searchKeyword'] . "%')";
		}
		
		private function showAddOrderForm()
		{
			$countriesList		= $this->objCMN->getCountriesList();
			$stateList			= $this->objCMN->getStateList('US');
			$itemList			= $this->objCMN->getProductList();
			
			if(isset($_SESSION['form_data']))
				$sessionData = unserialize(getSession('form_data'));
			
			if($this->RU_Id!=NULL && $this->RU_Id!='')
			{
				if($this->UserType == 1)
				{
					$this->load_model('RegUser','objRegUser');					
					$DataArray = array('RU.RU_Id as RU_Id', 'RU_FistName as firstName','RU_LastName as lastName','RU_CompanyName as organizationName','RU_Address1 as streetAddress1', 'RU_Address2 as streetAddress2',
										'RU_City as City', 'RU_State as state', 'RU_Country as country','RU_Phone as phoneNo', 'RU_EmailID as emailAddress','RU_ZipCode as ZipCode');
					$Condition = " AND RU.RU_Id=".$this->RU_Id;
					$arraySaleSubDetails = $this->objRegUser->GetUserDetails($DataArray, $Condition);

				}
				elseif($this->UserType==2)
				{
					$this->load_model('NpoUser','objNpoUser');					
					$DataArray = array('RU.RU_Id as RU_Id', 'RU_FistName as firstName','RU_LastName as lastName','RU_Address1 as streetAddress1', 'RU_Address2 as streetAddress2',
										'RU_City as City', 'RU_State as state', 'RU_Country as country','RU_Phone as phoneNo', 'RU_EmailID as emailAddress','RU_ZipCode as ZipCode','ND.NPO_EIN as EIN','ND.NPO_Name as organizationName');
					$Condition = " AND RU.RU_Id=".$this->RU_Id;
					$arraySaleSubDetails = $this->objNpoUser->getUserNpoDetails($DataArray, $Condition);
					
				}
					
			}
			
				
			if($this->SS_Id!=NULL && $this->SS_Id!='')
			{
				$FieldArray = array('SS_RUID as RU_Id','SS_ID','SS_ItemCode as itemCode','SS_ItemId as itemId','SS_ItemName as itemName','SS_ItemQuantitiy as itemQty','SS_ItemPrice as itemPrice','SS_Amount as itemAmount',
									'SS_OrganizationName as organizationName','SS_FirstName as firstName','SS_LastName as lastName' ,'SS_StreetAddress1 as streetAddress1','SS_StreetAddress2 as streetAddress2','SS_City as City',
									'SS_State as state','SS_Zipcode as ZipCode','SS_Country as country','SS_Phone as phoneNo','SS_EmailAddress as emailAddress','SS_Website as websiteUrl','SS_EIN as EIN','SS_PaymentMode as paymentMode',
									'SS_Schedule as intervalcnt','SS_SpecialInstruction as referenceNote','SS_AdminNotes as internalNote','SS_EnableRecipt as sentMailReceipt','SS_PaySimpleCustomerID','SS_Status');
				$this->objSaleSub->SS_Id = $this->SS_Id;
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);				
				if($arraySaleSubDetails['SS_Status']>2)
					redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
				
			}
			elseif(count($sessionData)>0)
				$arraySaleSubDetails = $sessionData;
				
			$this->tpl = new view;
			$this->tpl->assign('CountryList',$countriesList);
			$this->tpl->assign('StateList', $stateList);
			$this->tpl->assign('ItemList', $itemList);
			$this->tpl->assign('SS_Id',$this->SS_Id);			
			$this->tpl->assign('ReoccurringInterval',get_setting('ReoccurringInterval'));
			$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$this->tpl->draw('salesubscription/addOrder');
		}
		
		private function showAddPaymentForm()
		{
			if(!$this->SS_Id || $this->SS_Id=='')
				redirect(URL.'error');
			
			$FieldArray = array('*','SS_ID','SS_RefNumber','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
								'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City',
								'SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaymentMode',
								'SS_Schedule','SS_SpecialInstruction','SS_EnableRecipt','SS_Status','SS_PaySimplePaymentMethodID');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);		
			if($arraySaleSubDetails['SS_Status']==1)
				redirect(URL.'salesubscription/editOrder/'.keyEncrypt($this->SS_Id));
			if($arraySaleSubDetails['SS_Status']>10)
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			
			$this->tpl = new view;
			$this->tpl->assign('SS_Id',$this->SS_Id);
			$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$this->tpl->assign('statusArray',$this->statusArray);
			$this->tpl->draw('salesubscription/addPaymentDetails');
		}
		
		
		public function updateOrder()
		{			
			$SS_Id					= request('post','SS_Id',0);
			$SS_Id					= keyDecrypt($SS_Id);
			$this->SS_Id			= $SS_Id;
			
			$firstName				= request('post','firstName',0);
			$lastName				= request('post','lastName',0);
			$emailAddress			= request('post','emailAddress',0);
			$phoneNo				= request('post','phoneNo',0);
			$organizationName		= request('post','organizationName',0);
			$EIN					= request('post','EIN',0);
			$websiteUrl				= request('post','websiteUrl',0);
			$streetAddress1			= request('post','streetAddress1',0);
			$streetAddress2			= request('post','streetAddress2',0);
			$City					= request('post','city',0);
			$ZipCode				= request('post','zipCode',0);
			$country				= request('post','country',0);
			$state					= request('post','state',0);			
			
			$inputDataArray = array('SS_Id'=>$SS_Id,'firstName'=>$firstName,'lastName'=>$lastName,'emailAddress'=>$emailAddress,'phoneNo'=>$phoneNo,'organizationName'=>$organizationName,
			'EIN'=>$EIN,'websiteUrl'=>$websiteUrl,'streetAddress1'=>$streetAddress1,'streetAddress2'=>$streetAddress2,'City'=>$City,'ZipCode'=>$ZipCode,'country'=>$country,'state'=>$state);
			
			if(!$this->SS_Id && $this->P_Status)					$this->SetStatus(0,'E21013');
			if(!$this->P_Status)									redirect(URL.'error');		
			
			if(trim($inputDataArray['firstName'])=='' && $this->P_Status) $this->SetStatus('0','E21008');
			if(trim($inputDataArray['lastName'])=='' && $this->P_Status) $this->SetStatus('0','E21009');
			if(trim($inputDataArray['emailAddress'])=='' && $this->P_Status) $this->SetStatus('0','E21010');
			if(!filter_var($inputDataArray['emailAddress'], FILTER_VALIDATE_EMAIL) && $this->P_Status) $this->SetStatus('0','E21011'); 
			if(trim($inputDataArray['phoneNo'])=='' && $this->P_Status) $this->SetStatus('0','E21012');
			
			if(!$this->P_Status)
			{
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			}
			if($inputDataArray['SS_Id']!='')
			{	
				$logText = "Updated Order Details - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>";			
				$DataArray = array( 'SS_OrganizationName'=>isset($inputDataArray['organizationName'])?$inputDataArray['organizationName']:NULL,
									'SS_FirstName'=>isset($inputDataArray['firstName'])?$inputDataArray['firstName']:NULL,
									'SS_LastName'=>isset($inputDataArray['lastName'])?$inputDataArray['lastName']:NULL,
									'SS_StreetAddress1'=>isset($inputDataArray['streetAddress1'])?$inputDataArray['streetAddress1']:NULL,
									'SS_StreetAddress2'=>isset($inputDataArray['streetAddress2'])?$inputDataArray['streetAddress2']:NULL,
									'SS_City'=>isset($inputDataArray['City'])?$inputDataArray['City']:NULL,
									'SS_State'=>isset($inputDataArray['state'])?$inputDataArray['state']:NULL,
									'SS_Zipcode'=>isset($inputDataArray['ZipCode'])?$inputDataArray['ZipCode']:NULL,
									'SS_Country'=>isset($inputDataArray['country'])?$inputDataArray['country']:NULL,
									'SS_Phone'=>isset($inputDataArray['phoneNo'])?$inputDataArray['phoneNo']:NULL,
									'SS_EmailAddress'=>isset($inputDataArray['emailAddress'])?$inputDataArray['emailAddress']:NULL,
									'SS_Website'=>isset($inputDataArray['websiteUrl'])?$inputDataArray['websiteUrl']:NULL,
									'SS_EIN'=>isset($inputDataArray['EIN'])?$inputDataArray['EIN']:NULL,																												
									'SS_LastUpdatedDate'=>getDateTime(),
									'SS_Locale'=>GetUserLocale(),
									'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
				$this->objSaleSub->SS_Id = $this->SS_Id;
				$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
				if($this->objSaleSub->P_Status)
				{			
					$FieldArray = array('*');
					$this->objSaleSub->SS_Id = $this->SS_Id;					
					$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	/*Get inserted details*/
					
					if($arraySaleSubDetails['SS_ID'] !='' && $arraySaleSubDetails['SS_PaySimpleCustomerID']!='')
					{						
						$this->load_model('Usaepay', 'objUSAePay');
						/*$this->objUSAePay->ArrayCustomerDetails = array('BillingAddress' => array(
																			'FirstName'=>$arraySaleSubDetails['SS_FirstName'],
																			'LastName'=>$arraySaleSubDetails['SS_LastName'],
																			'Company'=>$arraySaleSubDetails['SS_OrganizationName'],
																			'Street'=>$arraySaleSubDetails['SS_StreetAddress1'],
																			'Street2'=>$arraySaleSubDetails['SS_StreetAddress2'],
																			'City'=>$arraySaleSubDetails['SS_City'],
																			'State'=>$arraySaleSubDetails['SS_State'],
																			'Zip'=>$arraySaleSubDetails['SS_Zipcode'],
																			'Country'=>$arraySaleSubDetails['SS_Country'],
																			'Email'=>$arraySaleSubDetails['SS_EmailAddress'],
																			'Phone'=>$arraySaleSubDetails['SS_Phone']																		
																			),
																		'PaymentMethods' => array(
																			'MethodName'=>'XXXXXXXXX',
																			'SecondarySort'=>'X',
																			'Account'=>'XXXXXXXXX',
																			'AccountType'=>'XXXXXXX',
																			'Routing'=>'XXXX',
																			'DriversLicense'=>'XXXX',
																			'DriversLicenseState'=>'XXXX'																		
																		),										
																		'CustomFields' => array(
																								array('Field'=>'ItemName', 'Value'=>$arraySaleSubDetails['SS_ItemName']),
																								array('Field'=>'ItemCode', 'Value'=>$arraySaleSubDetails['SS_ItemCode']),
																								array('Field'=>'ItemPrice', 'Value'=>$arraySaleSubDetails['SS_ItemPrice']),
																								array('Field'=>'Company', 'Value'=>$arraySaleSubDetails['SS_OrganizationName']),
																								array('Field'=>'EIN', 'Value'=>$arraySaleSubDetails['SS_EIN'])
																								),
																		'CustomerID'=>($arraySaleSubDetails['SS_RUID']!='')?'R'.$arraySaleSubDetails['SS_RUID']:$arraySaleSubDetails['SS_RefNumber'],
																		'URL'=>$arraySaleSubDetails['SS_Website'],
																		'Description'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?$arraySaleSubDetails['SS_Schedule']:'One Time Payment',
																		'Amount'=>$arraySaleSubDetails['SS_Amount'],
																		'Tax'=>'0',
																		'Next'=>formatDate($arraySaleSubDetails['SS_NextOuccringDate'],'Y-m-d'),
																		'Notes'=>$arraySaleSubDetails['SS_AdminNotes'],
																		'NumLeft'=>$arraySaleSubDetails['SS_TotalCycles'],
																		'OrderID'=>$arraySaleSubDetails['SS_RefNumber'],
																		'ReceiptNote'=>$arraySaleSubDetails['SS_SpecialInstruction'],
																		'Schedule'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?$arraySaleSubDetails['SS_Schedule']:'',
																		'SendReceipt'=>($arraySaleSubDetails['SS_EnableRecipt']==1)?true:false,
																		'Source'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?'Recurring':'OneTimePayment',
																		'Enabled'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?true:false,
																		'User'=>'donasitytestaccount'
																		);	*/
						$this->objUSAePay->ArrayCustomerDetails = array( 
																		array('Field'=>'FirstName'   , 'Value'=>$arraySaleSubDetails['SS_FirstName']), 
																		array('Field'=>'LastName', 'Value'=>$arraySaleSubDetails['SS_LastName']), 
																		array('Field'=>'Company'    , 'Value'=>$arraySaleSubDetails['SS_OrganizationName']), 
																		array('Field'=>'Address'   , 'Value'=>$arraySaleSubDetails['SS_StreetAddress1']), 
																		array('Field'=>'Address2', 'Value'=>$arraySaleSubDetails['SS_StreetAddress2']),  
																		array('Field'=>'City', 'Value'=>$arraySaleSubDetails['SS_City']),  
																		array('Field'=>'State', 'Value'=>$arraySaleSubDetails['SS_State']),
																		array('Field'=>'Zip', 'Value'=>$arraySaleSubDetails['SS_Zipcode']),
																		array('Field'=>'Country', 'Value'=>$arraySaleSubDetails['SS_Country']),
																		array('Field'=>'Phone', 'Value'=>$arraySaleSubDetails['SS_Phone']),
																		array('Field'=>'Email', 'Value'=>$arraySaleSubDetails['SS_EmailAddress']),																		
																		array('Field'=>'URL', 'Value'=>$arraySaleSubDetails['SS_Website'])
																		); 												
						$this->objUSAePay->CustNum = $arraySaleSubDetails['SS_PaySimpleCustomerID'];
						
						if($this->objUSAePay->quickUpdateCustomer())
						{
							$this->SetStatus(1,'C21006');
							redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));	
						}
						else
						{
							$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);
							redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));		
						}
					}
					else
					{
						$this->SetStatus(0,'E21013');
						redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));	
					}
				}
				else
				{
					$this->SetStatus(0,'E21014');
					redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));	
				}
			}
			else
			{
				$this->SetStatus(0,'E21013');
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));		
			}
				
		}
		
		public function updatePaymentMethod()
		{
			$SS_Id					= keyDecrypt(request('post','SS_Id',0));
			$this->SS_Id			= $SS_Id;
			$Method_Id				= keyDecrypt(request('post','Method_Id',0));		
			$bankName 				= request('post','bankName',0);
			$accountType			= request('post','accountType',0);
			$routingNumber			= request('post','routingNumber',0);
			$accountNumber			= request('post','accountNumber',0);
			$checkNumber			= request('post','checkNumber',0);
			$licenceNumber			= request('post','licenceNumber',0);
			$licenceState			= request('post','licenceState',0);
			
			$inputDataArray = array('SS_Id'=>$SS_Id,'bankName'=>$bankName,'accountType'=>$accountType,'routingNumber'=>$routingNumber,'accountNumber'=>$accountNumber,'checkNumber'=>$checkNumber,'licenceNumber'=>$licenceNumber,'licenceState'=>$licenceState,'methodId'=>$Method_Id);
			
			if(!$this->SS_Id && $this->P_Status)					$this->SetStatus(0,'E21013');
			if(!$inputDataArray['methodId'] && $this->P_Status)		$this->SetStatus(0,'E21013');			
			if(!$this->P_Status) redirect(URL.'error');
			
			if(trim($inputDataArray['bankName'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21015');
			if(trim($inputDataArray['accountType'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21016');
			if(trim($inputDataArray['routingNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E21017');
			if(trim($inputDataArray['accountNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E21018');
			if(trim($inputDataArray['checkNumber'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21019');
			
			if(!$this->P_Status)
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			
			
			$logText = "Updated Payment details - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
			$bankDetails = keyEncryptFront($inputDataArray['bankName']."|".$inputDataArray['accountType']."|".$inputDataArray['checkNumber']."|XXXX".substr($inputDataArray['accountNumber'],-4));			
			$DataArray = array('SS_BankDetails'=>$bankDetails,'SS_CheckNumber'=>$inputDataArray['checkNumber'],'SS_LastUpdatedDate'=>getDateTime(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			if($this->objSaleSub->P_Status)
			{			
				$this->load_model('Usaepay', 'objUSAePay');
				$this->objUSAePay->ArrayPaymentMethod = array('MethodName'=>'ACH',	
															'MethodID'=>$inputDataArray['methodId'],
															'RecordType'=>'PPD',																				
															'Account'=>$inputDataArray['accountNumber'],
															'AccountType'=>$inputDataArray['accountType'],
															'SecondarySort'=>0,/*If set to value greater than 0, use this method as backup in case default fails. Secondary methods will be run in ascending order.*/
															'Routing'=>$inputDataArray['routingNumber'],														
															'DriversLicense'=>$inputDataArray['checkNumber'],
															'DriversLicenseState'=>$inputDataArray['licenceState']
															);
				
				if($this->objUSAePay->updateCustomerPaymentMethod())
				{
					$logText = "Updated After Update Payment Details on USAePay with Method ID ".$inputDataArray['methodId']." - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
					$DataArray = array('SS_Status'=>11,'SS_LastUpdatedDate'=>getDateTime(),'SS_Locale'=>GetUserLocale());
					$this->objSaleSub->SS_Id = $this->SS_Id;
					$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);				
					$this->SetStatus(1,'C21007');
				}
				else
				{
					$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);					
				}
			}
			else
			{
				$this->SetStatus(0,'E21020');		
			}
			
			
			redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));	
		}
		
		public function processAddOrder()
		{
			$this->RU_Id			= request('post','RU_Id',0);

			if($this->RU_Id!='' || $this->RU_Id!=NULL)
			$RU_Id					= keyDecrypt($this->RU_Id);

			$SS_Id					= request('post','SS_Id',0);
			$SS_Id					= keyDecrypt($SS_Id);
			$this->SS_Id			= $SS_Id;
			$itemId					= request('post','itemId',1);
			$itemCode				= request('post','itemCode',0);
			$itemName				= request('post','itemName',0);
			$itemPrice				= request('post','itemPrice',0);
			$itemQty				= request('post','itemQty',1);			
			$itemAmount				= request('post','itemAmount',0);
			$firstName				= request('post','firstName',0);
			$lastName				= request('post','lastName',0);
			$emailAddress			= request('post','emailAddress',0);
			$phoneNo				= request('post','phoneNo',0);
			$organizationName		= request('post','organizationName',0);
			$EIN					= request('post','EIN',0);
			$websiteUrl				= request('post','websiteUrl',0);
			$streetAddress1			= request('post','streetAddress1',0);
			$streetAddress2			= request('post','streetAddress2',0);
			$City					= request('post','city',0);
			$ZipCode				= request('post','zipCode',0);
			$country				= request('post','country',0);
			$state					= request('post','state',0);
			$paymentMode			= request('post','paymentMode',0);
			$interval				= request('post','interval',0);
			$sentMailReceipt		= request('post','sendReceipt',1);
			$referenceNote			= request('post','referenceNote',0);
			$internalNote			= request('post','internalNote',0);
			
			$inputDataArray = array('RU_Id'=>$RU_Id,'SS_Id'=>$SS_Id,'itemId'=>$itemId,'itemCode'=>$itemCode,'itemName'=>$itemName,'itemPrice'=>$itemPrice,'itemQty'=>$itemQty,'itemAmount'=>$itemAmount,
			'firstName'=>$firstName,'lastName'=>$lastName,'emailAddress'=>$emailAddress,'phoneNo'=>$phoneNo,'organizationName'=>$organizationName,'EIN'=>$EIN,'websiteUrl'=>$websiteUrl,
			'streetAddress1'=>$streetAddress1,'streetAddress2'=>$streetAddress2,'City'=>$City,'ZipCode'=>$ZipCode,'country'=>$country,'state'=>$state,'paymentMode'=>$paymentMode,
			'interval'=>$interval,'sentMailReceipt'=>$sentMailReceipt,'referenceNote'=>$referenceNote,'internalNote'=>$internalNote);

			if(trim($inputDataArray['itemCode'])=='' && $this->P_Status) $this->SetStatus(0,'E21003');
			if(trim($inputDataArray['itemName'])=='' && $this->P_Status) $this->SetStatus(0,'E21004');
			if(trim($inputDataArray['itemPrice'])=='' && $this->P_Status) $this->SetStatus(0,'E21005');
			if(trim($inputDataArray['itemQty'])=='' && $this->P_Status) $this->SetStatus(0,'E21006');
			if(trim($inputDataArray['itemAmount'])=='' && $this->P_Status) $this->SetStatus(0,'E21007');
			
			if(trim($inputDataArray['firstName'])=='' && $this->P_Status) $this->SetStatus('0','E21008');
			if(trim($inputDataArray['lastName'])=='' && $this->P_Status) $this->SetStatus('0','E21009');
			if(trim($inputDataArray['emailAddress'])=='' && $this->P_Status) $this->SetStatus('0','E21010');
			if(!filter_var($inputDataArray['emailAddress'], FILTER_VALIDATE_EMAIL) && $this->P_Status) $this->SetStatus('0','E21011'); 
			if(trim($inputDataArray['phoneNo'])=='' && $this->P_Status) $this->SetStatus('0','E21012');
			
			if(!$this->P_Status)
			{	
				setSession('form_data',serialize($inputDataArray));
				redirect(URL.'salesubscription/newOrder');
			}
			
			if($inputDataArray['SS_Id']!='')
			{
				$FieldArray = array('SS_ID','SS_PaySimpleCustomerID','SS_Status');
				
				$this->objSaleSub->SS_Id = $this->SS_Id;
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);
				$ProcessStatus = $arraySaleSubDetails['SS_Status'];
				$SS_PaySimpleCustomerID = $arraySaleSubDetails['SS_PaySimpleCustomerID'];
				if($ProcessStatus==1 || $ProcessStatus==2) /*If process status - Order Added - it update form details else add details*/
				{
					$logText = "Updated - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>";
					$DataArray = array( 'SS_DateTime'=>getDateTime(),
										'SS_ItemId'=>isset($inputDataArray['itemId'])?$inputDataArray['itemId']:NULL,
										'SS_ItemCode'=>isset($inputDataArray['itemCode'])?$inputDataArray['itemCode']:NULL,
										'SS_ItemName'=>isset($inputDataArray['itemName'])?$inputDataArray['itemName']:NULL,
										'SS_ItemQuantitiy'=>isset($inputDataArray['itemQty'])?$inputDataArray['itemQty']:NULL,
										'SS_ItemPrice'=>isset($inputDataArray['itemPrice'])?$inputDataArray['itemPrice']:NULL,
										'SS_Amount'=>isset($inputDataArray['itemAmount'])?$inputDataArray['itemAmount']:NULL,
										'SS_OrganizationName'=>isset($inputDataArray['organizationName'])?$inputDataArray['organizationName']:NULL,
										'SS_FirstName'=>isset($inputDataArray['firstName'])?$inputDataArray['firstName']:NULL,
										'SS_LastName'=>isset($inputDataArray['lastName'])?$inputDataArray['lastName']:NULL,
										'SS_StreetAddress1'=>isset($inputDataArray['streetAddress1'])?$inputDataArray['streetAddress1']:NULL,
										'SS_StreetAddress2'=>isset($inputDataArray['streetAddress2'])?$inputDataArray['streetAddress2']:NULL,
										'SS_City'=>isset($inputDataArray['City'])?$inputDataArray['City']:NULL,
										'SS_State'=>isset($inputDataArray['state'])?$inputDataArray['state']:NULL,
										'SS_Zipcode'=>isset($inputDataArray['ZipCode'])?$inputDataArray['ZipCode']:NULL,
										'SS_Country'=>isset($inputDataArray['country'])?$inputDataArray['country']:NULL,
										'SS_Phone'=>isset($inputDataArray['phoneNo'])?$inputDataArray['phoneNo']:NULL,
										'SS_EmailAddress'=>isset($inputDataArray['emailAddress'])?$inputDataArray['emailAddress']:NULL,
										'SS_Website'=>isset($inputDataArray['websiteUrl'])?$inputDataArray['websiteUrl']:NULL,
										'SS_EIN'=>isset($inputDataArray['EIN'])?$inputDataArray['EIN']:NULL,
										'SS_RUID'=>isset($inputDataArray['RU_Id'])?$inputDataArray['RU_Id']:NULL,
										'SS_PaymentMode'=>isset($inputDataArray['paymentMode'])?$inputDataArray['paymentMode']:NULL,
										'SS_Schedule'=>$inputDataArray['paymentMode']=='RC'?$inputDataArray['interval']:NULL,										
										'SS_StartDate'=>getDateTime(0,'Y-m-d'),																			
										'SS_SpecialInstruction'=>isset($inputDataArray['referenceNote'])?$inputDataArray['referenceNote']:NULL,
										'SS_AdminNotes'=>isset($inputDataArray['internalNote'])?$inputDataArray['internalNote']:NULL,
										'SS_EnableRecipt'=>isset($inputDataArray['sentMailReceipt'])?$inputDataArray['sentMailReceipt']:NULL,																				
										'SS_LastUpdatedDate'=>getDateTime(),
										'SS_Locale'=>GetUserLocale(),
										'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
					$this->objSaleSub->SS_Id = $this->SS_Id;
					$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);							
				}
				else
				{
					redirect(URL.'salesubscription/order/'.keyEncrypt($inputDataArray['SS_Id']));
				}
			}
			else
			{
				$logText = "Added - Added by : ".$this->loginDetails['admin_fullname']." Added on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>";
				$FieldArray = array('SS_RefNumber'=>1,
								'SS_DateTime'=>getDateTime(),
								'SS_ItemId'=>isset($inputDataArray['itemId'])?$inputDataArray['itemId']:NULL,
								'SS_ItemCode'=>isset($inputDataArray['itemCode'])?$inputDataArray['itemCode']:NULL,
								'SS_ItemName'=>isset($inputDataArray['itemName'])?$inputDataArray['itemName']:NULL,
								'SS_ItemQuantitiy'=>isset($inputDataArray['itemQty'])?$inputDataArray['itemQty']:NULL,
								'SS_ItemPrice'=>isset($inputDataArray['itemPrice'])?$inputDataArray['itemPrice']:NULL,
								'SS_Amount'=>isset($inputDataArray['itemAmount'])?$inputDataArray['itemAmount']:NULL,
								'SS_OrganizationName'=>isset($inputDataArray['organizationName'])?$inputDataArray['organizationName']:NULL,
								'SS_FirstName'=>isset($inputDataArray['firstName'])?$inputDataArray['firstName']:NULL,
								'SS_LastName'=>isset($inputDataArray['lastName'])?$inputDataArray['lastName']:NULL,
								'SS_StreetAddress1'=>isset($inputDataArray['streetAddress1'])?$inputDataArray['streetAddress1']:NULL,
								'SS_StreetAddress2'=>isset($inputDataArray['streetAddress2'])?$inputDataArray['streetAddress2']:NULL,
								'SS_City'=>isset($inputDataArray['City'])?$inputDataArray['City']:NULL,
								'SS_State'=>isset($inputDataArray['state'])?$inputDataArray['state']:NULL,
								'SS_Zipcode'=>isset($inputDataArray['ZipCode'])?$inputDataArray['ZipCode']:NULL,
								'SS_Country'=>isset($inputDataArray['country'])?$inputDataArray['country']:NULL,
								'SS_Phone'=>isset($inputDataArray['phoneNo'])?$inputDataArray['phoneNo']:NULL,
								'SS_EmailAddress'=>isset($inputDataArray['emailAddress'])?$inputDataArray['emailAddress']:NULL,
								'SS_Website'=>isset($inputDataArray['websiteUrl'])?$inputDataArray['websiteUrl']:NULL,
								'SS_EIN'=>isset($inputDataArray['EIN'])?$inputDataArray['EIN']:NULL,
								'SS_RUID'=>isset($inputDataArray['RU_Id'])?$inputDataArray['RU_Id']:NULL,
								'SS_PaymentMode'=>isset($inputDataArray['paymentMode'])?$inputDataArray['paymentMode']:NULL,
								'SS_Schedule'=>$inputDataArray['paymentMode']=='RC'?$inputDataArray['interval']:NULL,	
								'SS_TotalCycles'=>'999',
								'SS_StartDate'=>getDateTime(0,'Y-m-d'),															
								'SS_PaymentStatus'=>0,
								'SS_SpecialInstruction'=>isset($inputDataArray['referenceNote'])?$inputDataArray['referenceNote']:NULL,
								'SS_AdminNotes'=>isset($inputDataArray['internalNote'])?$inputDataArray['internalNote']:NULL,
								'SS_EnableRecipt'=>isset($inputDataArray['sentMailReceipt'])?$inputDataArray['sentMailReceipt']:NULL,
								'SS_Status'=>1,
								'SS_CreatedByUserId'=>$this->loginDetails['admin_id'],
								'SS_CreatedDate'=>getDateTime(),
								'SS_LastUpdatedDate'=>getDateTime(),
								'SS_Locale'=>GetUserLocale(),
								'UpdateLog'=>$logText);
				$this->SS_Id = $this->objSaleSub->insertSaleSubscriptionDetails($FieldArray); /*Insert step 1 form details in datbase*/
				
				
			}
			if($this->SS_Id && $SS_PaySimpleCustomerID==NULL)
			{
				unset($_SESSION['form_data']);
				$DataArray = array('SS_RefNumber'=>$this->SS_Id+1000,'SS_LastUpdatedDate'=>getDateTime());
				$this->objSaleSub->SS_Id = $this->SS_Id;	
				$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			
				$FieldArray = array('*');
				$this->objSaleSub->SS_Id = $this->SS_Id;					
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	/*Get inserted details*/
				
				if(isset($arraySaleSubDetails['SS_ID']))
				{							
					$this->load_model('Usaepay', 'objUSAePay');
					$this->objUSAePay->ArrayCustomerDetails = array('BillingAddress' => array(
																		'FirstName'=>$arraySaleSubDetails['SS_FirstName'],
																		'LastName'=>$arraySaleSubDetails['SS_LastName'],
																		'Company'=>$arraySaleSubDetails['SS_OrganizationName'],
																		'Street'=>$arraySaleSubDetails['SS_StreetAddress1'],
																		'Street2'=>$arraySaleSubDetails['SS_StreetAddress2'],
																		'City'=>$arraySaleSubDetails['SS_City'],
																		'State'=>$arraySaleSubDetails['SS_State'],
																		'Zip'=>$arraySaleSubDetails['SS_Zipcode'],
																		'Country'=>$arraySaleSubDetails['SS_Country'],
																		'Email'=>$arraySaleSubDetails['SS_EmailAddress'],
																		'Phone'=>$arraySaleSubDetails['SS_Phone']																		
																		),
																	'CustomFields' => array(
																						array('Field'=>'ItemName', 'Value'=>$arraySaleSubDetails['SS_ItemName']),
																						array('Field'=>'ItemCode', 'Value'=>$arraySaleSubDetails['SS_ItemCode']),
																						array('Field'=>'ItemPrice', 'Value'=>$arraySaleSubDetails['SS_ItemPrice']),
																						array('Field'=>'Company', 'Value'=>$arraySaleSubDetails['SS_OrganizationName']),
																						array('Field'=>'EIN', 'Value'=>$arraySaleSubDetails['SS_EIN'])
																							),
																	'CustomerID'=>($arraySaleSubDetails['SS_RUID']!='')?'R'.$arraySaleSubDetails['SS_RUID']:$arraySaleSubDetails['SS_RefNumber'],
																	'URL'=>$arraySaleSubDetails['SS_Website'],
																	'Description'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?$arraySaleSubDetails['SS_Schedule']:'One Time Payment',
																	'Amount'=>$arraySaleSubDetails['SS_Amount'],
																	'Tax'=>'0',
																	'Next'=>formatDate($arraySaleSubDetails['SS_NextOuccringDate'],'Y-m-d'),
																	'Notes'=>$arraySaleSubDetails['SS_AdminNotes'],
																	'NumLeft'=>$arraySaleSubDetails['SS_TotalCycles'],
																	'OrderID'=>$arraySaleSubDetails['SS_RefNumber'],
																	'ReceiptNote'=>$arraySaleSubDetails['SS_SpecialInstruction'],
																	'Schedule'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?$arraySaleSubDetails['SS_Schedule']:'',
																	'SendReceipt'=>false,/*($arraySaleSubDetails['SS_EnableRecipt']==1)?true:false,*/
																	'Source'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?'Recurring':'OneTimePayment',
																	'Enabled'=>false, /*($arraySaleSubDetails['SS_PaymentMode']=='RC')?true:false,*/
																	'User'=>'donasitytestaccount');	
					
					if($this->objUSAePay->addCustomer())
					{
						$custNum = $this->objUSAePay->CustNum;
						$this->objSaleSub->SS_Id = $this->SS_Id;
						$logText = "Updated After Add Customer details on USAePay with SimplePay Customer# ".$custNum." - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
						$DataArray = array('SS_PaySimpleCustomerID'=>$custNum,'SS_Status'=>2,'SS_LastUpdatedDate'=>getDateTime(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
						$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
						
						$this->SetStatus(1,'C21001');
						redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
					}
					else
					{
						$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);															
						redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));						
					}
				}
				else
				{
					$this->SetStatus(0,'E21013');
					redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
				}
			}
			else if($this->SS_Id && $SS_PaySimpleCustomerID!=NULL)
			{
				$FieldArray = array('*','SS_ID','SS_FirstName','SS_LastName','SS_OrganizationName','SS_StreetAddress1','SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_PaySimpleCustomerID');
				$this->objSaleSub->SS_Id = $this->SS_Id;					
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	/*Get inserted details*/
				$this->load_model('Usaepay', 'objUSAePay');
				$this->objUSAePay->ArrayCustomerDetails = array( 
															array('Field'=>'FirstName'   , 'Value'=>$arraySaleSubDetails['SS_FirstName']), 
															array('Field'=>'LastName', 'Value'=>$arraySaleSubDetails['SS_LastName']), 
															array('Field'=>'Company'    , 'Value'=>$arraySaleSubDetails['SS_OrganizationName']), 
															array('Field'=>'Address'   , 'Value'=>$arraySaleSubDetails['SS_StreetAddress1']), 
															array('Field'=>'Address2', 'Value'=>$arraySaleSubDetails['SS_StreetAddress2']),  
															array('Field'=>'City', 'Value'=>$arraySaleSubDetails['SS_City']),  
															array('Field'=>'State', 'Value'=>$arraySaleSubDetails['SS_State']),
															array('Field'=>'Zip', 'Value'=>$arraySaleSubDetails['SS_Zipcode']),
															array('Field'=>'Country', 'Value'=>$arraySaleSubDetails['SS_Country']),
															array('Field'=>'Phone', 'Value'=>$arraySaleSubDetails['SS_Phone']),
															array('Field'=>'Email', 'Value'=>$arraySaleSubDetails['SS_EmailAddress']),																		
															array('Field'=>'URL', 'Value'=>$arraySaleSubDetails['SS_SpecialInstruction']),
															array('Field'=>'ReceiptNote', 'Value'=>$arraySaleSubDetails['SS_SpecialInstruction']),
															array('Field'=>'Notes', 'Value'=>$arraySaleSubDetails['SS_AdminNotes']),															
															array('Field'=>'Schedule', 'Value'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?$arraySaleSubDetails['SS_Schedule']:false),
															array('Field'=>'SendReceipt', 'Value'=>false),
															array('Field'=>'Source', 'Value'=>($arraySaleSubDetails['SS_PaymentMode']=='RC')?'Recurring':'OneTimePayment'),
															array('Field'=>'Enabled', 'Value'=>false)
															); 															
				
				
				if($arraySaleSubDetails['SS_PaymentMode']=='OTP')
					unset($this->objUSAePay->ArrayCustomerDetails[14]);
				//dump($this->objUSAePay->ArrayCustomerDetails);												
				$this->objUSAePay->CustNum = $arraySaleSubDetails['SS_PaySimpleCustomerID'];
				
				if($this->objUSAePay->quickUpdateCustomer())
				{
					$this->SetStatus(1,'C21006');
					redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));	
				}
				else
				{
					$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);
					redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));		
				}
			}
			else
			{
				$this->SetStatus(0,'E21001');
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			}									
		}
				
		
		
		public function processAddPaymentDetails()
		{			
			$SS_Id					= keyDecrypt(request('post','SS_Id',0));
			$this->SS_Id			= $SS_Id;
			$bankName 				= request('post','bankName',0);
			$accountType			= request('post','accountType',0);
			$routingNumber			= request('post','routingNumber',0);
			$accountNumber			= request('post','accountNumber',0);
			$checkNumber			= request('post','checkNumber',0);
			$licenceNumber			= request('post','licenceNumber',0);
			$licenceState			= request('post','licenceState',0);
			
			$inputDataArray = array('SS_Id'=>$SS_Id,'bankName'=>$bankName,'accountType'=>$accountType,'routingNumber'=>$routingNumber,'accountNumber'=>$accountNumber,'checkNumber'=>$checkNumber,'licenceNumber'=>$licenceNumber,'licenceState'=>$licenceState);
			
			if(!$this->SS_Id && $this->P_Status)					$this->SetStatus(0,'E21013');
			if(!$this->P_Status)									redirect(URL.'error');
			if(trim($inputDataArray['bankName'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21015');
			if(trim($inputDataArray['accountType'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21016');
			if(trim($inputDataArray['routingNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E21017');
			if(trim($inputDataArray['accountNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E21018');
			if(trim($inputDataArray['checkNumber'])=='' && $this->P_Status) 	$this->SetStatus(0,'E21019');
			
			if(!$this->P_Status)
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			
			$FieldArray = array('SS_ID','SS_RefNumber','SS_Status','SS_EnableRecipt','SS_PaySimplePaymentMethodID','SS_DateTime','SS_ItemCode','SS_ItemName','SS_Amount','SS_FirstName','SS_LastName','SS_StreetAddress1',
									'SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_Website','SS_PaymentMode','SS_Schedule',
									'SS_EmailAddress','SS_PaySimpleCustomerID','SS_Status','SS_BankDetails','SS_SpecialInstruction');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	
			$ProcessStatus = $arraySaleSubDetails['SS_Status'];
			if($ProcessStatus==1)
				redirect(URL.'salesubscription/Editorder/'.keyEncrypt($this->SS_Id));
			elseif($ProcessStatus>10)
				redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
			
			
			$logText = "Updated Payment details - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
			$bankDetails = keyEncryptFront($inputDataArray['bankName']."|".$inputDataArray['accountType']."|".$inputDataArray['checkNumber']."|XXXX".substr($inputDataArray['accountNumber'],-4));
			$DataArray = array('SS_BankDetails'=>$bankDetails,'SS_CheckNumber'=>$inputDataArray['checkNumber'],'SS_LastUpdatedDate'=>getDateTime(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			
			
			$this->load_model('Usaepay', 'objUSAePay');
			$this->objUSAePay->ArrayPaymentMethod = array('MethodName'=>'ACH',
														'RecordType'=>'PPD',																					
														'Account'=>$inputDataArray['accountNumber'],
														'AccountType'=>$inputDataArray['accountType'],
														'SecondarySort'=>0,
														'Routing'=>$inputDataArray['routingNumber'],		
														'CheckNumber'=>$inputDataArray['checkNumber'],												
														'DriversLicense'=>$inputDataArray['licenceNumber'],
														'DriversLicenseState'=>$inputDataArray['licenceState']
														);
			$this->objUSAePay->Default	= true;
			$this->objUSAePay->CustNum 	= $arraySaleSubDetails['SS_PaySimpleCustomerID'];
			
			if($this->objUSAePay->addCustomerPaymentMethod())
			{
				$PaymentMethodId = $this->objUSAePay->MethodID;	
				$logText = "Updated After Add Payment Details on USAePay with Method ID ".$PaymentMethodId." - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
				$DataArray = array('SS_Status'=>11,'SS_PaySimplePaymentMethodID'=>$PaymentMethodId,'SS_LastUpdatedDate'=>getDateTime(),'SS_Locale'=>GetUserLocale(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
				$this->objSaleSub->SS_Id = $this->SS_Id;
				$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
				
				//send mail to costomer if allow
				/*if($arraySaleSubDetails['SS_EnableRecipt'])
				{*/
					if($this->sentReceiptMailCustomer($arraySaleSubDetails))
						$this->SetStatus(1,'C21012');
					else
						$this->SetStatus(1,'C21002');
				//}
				//else
				//{
					//$this->SetStatus(1,'C21002');
				//}
				$redirection = URL."salesubscription/order/".keyEncrypt($this->SS_Id);	
			}
			else
			{
				$this->SetStatus(0,000,$this->objUSAePay->ErrorMessage);		
				$redirection = URL.'salesubscription/order/'.keyEncrypt($this->SS_Id);								
			}
			
			redirect($redirection);
		}
		
		private function sentReceiptMailCustomer($arraySaleSubDetails)
		{			
			//dump($this->arraySaleSubDetails);
			$uname = $arraySaleSubDetails['SS_FirstName'].' '.$arraySaleSubDetails['SS_LastName'];
			$email_address = $arraySaleSubDetails['SS_EmailAddress'];
			
			$this->load_model('Email','objemail');
			$Keyword='paySimpleOrderReceiptToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			//dump($HTML);		
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$email_address,
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;
		}
		
		
		public function processSendLinkCustomerPaymentDetails($ss_Id)
		{
			$this->SS_Id = keyDecrypt($ss_Id);
			
			if(trim($this->SS_Id)=='')
				redirect(URL.'error');
			
			$FieldArray = array('SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_PaySimpleCustomerID','SS_Status','SS_Schedule','SS_ItemName','SS_Amount','SS_RefNumber','SS_CreatedDate','SS_LastUpdatedDate');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);
			if($arraySaleSubDetails['SS_Status']<=15 || ($arraySaleSubDetails['SS_Status']>=21 && $arraySaleSubDetails['SS_Status']<=30))
			{
				if(isset($arraySaleSubDetails['SS_ID']))
				{
					$strDate = $arraySaleSubDetails['SS_LastUpdatedDate'];
					
					if($arraySaleSubDetails['SS_Status']>=2 && $arraySaleSubDetails['SS_Status']<=10)
					{
						$strDate = getDateTime();	
					}
					//echo strtotime($strDate);exit;
					$arraySaleSubDetails['SS_LastUpdatedDate'] = $strDate;
					if($this->sendLinkMailCustomerPaymentDetails($arraySaleSubDetails))
					{
						if($arraySaleSubDetails['SS_Status']>=2 && $arraySaleSubDetails['SS_Status']<=10)
						{
							$logText = "Updated details after send payement link to customer - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SS_Status'=>4,'SS_LastUpdatedDate'=>$strDate,'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
							$this->objSaleSub->SS_Id = $this->SS_Id;
							$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
						}
						$this->SetStatus(1,'C21011');
					}
					else
					{
						$this->SetStatus(0,'E21025');	
					}
				}
				else
				{
					$this->SetStatus(0,'E21026');	
				}
			}
			else
			{
				$this->SetStatus(0,'E21026');
			}
			redirect(URL.'salesubscription/order/'.keyEncrypt($this->SS_Id));
		}
		
		private function sendLinkMailCustomerPaymentDetails($saleSubDetails)
		{
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];
			$service_name = $saleSubDetails['SS_ItemName'];
			$charge_amount = $saleSubDetails['SS_Amount'];
			$schedule = $saleSubDetails['SS_Schedule'];
			$order_id = $saleSubDetails['SS_RefNumber'];
			//echo strtotime($saleSubDetails['SS_LastUpdatedDate']);exit;
			$mailParam = keyEncryptFront($saleSubDetails['SS_ID']."|".$saleSubDetails['SS_LastUpdatedDate']);
			$link = "<a href='".FRONTURL."salesubscription/".$mailParam."/' style='color:#abc340;' style='color:#abc340;'/>Add Payment Details</a>";
			$link_url = FRONTURL."salesubscription/".$mailParam;
			$emailId = $saleSubDetails['SS_EmailAddress'];
			$this->load_model('Email','objemail');
			$Keyword='paySimpleAddPaymentToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;			
			$tpl->assign('service_name',$service_name);
			$tpl->assign('schedule',$schedule);			
			$tpl->assign('charge_amount',$charge_amount);
			$tpl->assign('link',$link);
			$tpl->assign('link_url',$link_url);
			$tpl->assign('order_id',$order_id);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			//echo $HTML;
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$emailId,
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;
		}
		
				
		public function Transaction($SS_ID='') 
		{
			if($SS_ID != '') {
				$this->SS_Id = keyDecrypt($SS_ID);
				if($this->SS_Id == '' || $this->SS_Id <= 0)
					redirect(URL.'error');
			} 
			
			$transactions = array();
			$dataArray = array('SSPT_ID','SSPT_PaymentType', 'SSPT_PaymentAmount', 'SSPT_PaymentGatewayName', 'SSPT_PaymentGatewayTransactionID', 'SSPT_Status', 'SSPT_CreatedDate');
			
			$where = "";
			if($SS_ID != '')
				$where .= " AND SSPT_SSID=$this->SS_Id ";
				
			$transactions = $this->objSaleSub->GetTransactions($dataArray, $where);
			//dump($transactions);
			
			$fieldArray = array('*');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$saleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($fieldArray);	
			//dump($saleSubDetails);
			
			$arrayTransStatus = get_setting('TransactionStatus');
			
			$this->tpl = new view;
			//================= pagination code start =================
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$this->objSaleSub->pageSelectedPage = $pageSelected == 0 ? 1 : $pageSelected;
			$Page_totalRecords = $this->objSaleSub->totalRecord;
			$PagingArr = constructPaging($pageSelected, $Page_totalRecords, $this->objSaleSub->pageLimit);
			$lastPage = ceil($Page_totalRecords / $this->objSaleSub->pageLimit);
			$this->tpl->assign("pageSelected", $pageSelected);
			$this->tpl->assign("pagingList", $PagingArr['Pages']);
			$this->tpl->assign("pageSelected", $PagingArr['PageSel']);
			$this->tpl->assign("startRecord", $PagingArr['StartPoint']);
			$this->tpl->assign("endRecord", $PagingArr['EndPoint']);
			$this->tpl->assign("lastPage", $lastPage);
			$this->tpl->assign('totalrecords', $Page_totalRecords);	
			$this->tpl->assign('transactions', $transactions);
			$this->tpl->assign('ss_id', $SS_ID);
			$this->tpl->assign('statusArray',$this->statusArray);
			$this->tpl->assign('arrayTransStatus',$arrayTransStatus);
			$this->tpl->assign('saleSubDetails', $saleSubDetails);
			$this->tpl->draw('salesubscription/transaction');
		}
		
		public function processActive()
		{
			$SS_Id 					= request('post','SS_Id',0);
			$this->SS_Id = keyDecrypt($SS_Id);
			$chargeWithActivation 	= request('post','chargeWithActivation',0); 
			//echo $this->SS_Id;echo "--".$chargeWithActivation;exit;
			$FieldArray = array('SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_PaySimplePaymentMethodID','SS_EnableRecipt','SS_TotalCyclesPaid','SS_ItemName','SS_RefNumber',
					'SS_Schedule','SS_PaySimpleCustomerID','SS_Status','SS_PaymentStatus','SS_PaymentMode','SS_Amount','SS_CheckNumber');
			
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$ArraySaleSubscription = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);
			//dump($ArraySaleSubscription);
			if(isset($ArraySaleSubscription['SS_ID']))
			{
				if($ArraySaleSubscription['SS_Status']==11 || $ArraySaleSubscription['SS_Status']==12)
				{
					if($chargeWithActivation==1)
					{
						$this->load_model('Usaepay', 'objUSAePay');
						$arrayTransStatus = get_setting('TransactionStatus');
					
						$logText = "Added Transaction details - Added by : System , Added on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
						$DataArray = array('SSPT_SSID'=>$ArraySaleSubscription['SS_ID'],
											'SSPT_PaymentType'=>$ArraySaleSubscription['SS_PaymentMode'],
											'SSPT_PaymentAmount'=>$ArraySaleSubscription['SS_Amount'],
											'SSPT_PaidAmount'=>$ArraySaleSubscription['SS_Amount'],
											'SSPT_Status'=>0,
											'SSPT_CreatedDate'=>getDateTime(),
											'SSPT_LastUpdatedDate'=>getDateTime(),
											'SSPT_Locale'=>GetUserLocale(),
											'UpdateLog'=>$logText
											);
							
						$this->SSPT_Id = $this->objSaleSub->insertSaleSubscriptionTransactionDetails($DataArray);
						if($this->SSPT_Id)
						{
							$FieldArray = array('*');
							$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
							$arraySaleSubTransactionDetails = $this->objSaleSub->getSalSubscriptionTransactionDetails($FieldArray);
							//dump($arraySaleSubTransactionDetails);
							$this->objUSAePay->ArrayTransactionDetails = array(
									'Command'=>'Check',
									'IgnoreDuplicate'=>false,
									'CheckData' => array(
											'CheckNumber' =>$ArraySaleSubscription['SS_CheckNumber']),
									'Details'=>array(
											'ClientIP'=>get_ip(),
											'CustReceipt'=>true,
											'PONum' => '', /*Purchase Order Number for commercial card transactions - 25 characters. discussion*/
											'OrderID' =>1000+$this->SSPT_Id, /*Transaction order ID. This field should be used to assign a unique order id to the transaction. The order ID can support 64 characters.*/
											'Description' => ($arraySaleSubTransactionDetails['SSPT_PaymentType']=='RC')?'Recurring Payment':'One Time Payment',
											'Amount'=>$arraySaleSubTransactionDetails['SSPT_PaymentAmount'])
							);
							$this->objUSAePay->CustNum = $ArraySaleSubscription['SS_PaySimpleCustomerID'];
							$this->objUSAePay->MethodID = $ArraySaleSubscription['SS_PaySimplePaymentMethodID'];
							if($this->objUSAePay->runCustomerTransaction())
							{
							//dump($this->objUSAePay->PaySimpleResponse);
							//dump($this->objUSAePay->PaySimpleResponseArray);
								$intStatus = 0;
								if(strtolower($this->objUSAePay->PaySimpleResponseArray['Result'])=='approved')
									$intStatus = 1;
									$logText = "Updated Transaction Details After Create Transaction on USAePay for Status :".$this->objUSAePay->PaySimpleResponseArray['Result']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
									$DataArray = array('SSPT_Status'=>$intStatus,
											'SSPT_PaySimpleStatus'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
											'SSPT_PaymentGatewayName'=>'USAePay',
											'SSPT_PaymentGatewayRequest'=>serialize($this->objUSAePay->ArrayTransactionDetails),
											'SSPT_PaymentGatewayResponse'=>serialize($this->objUSAePay->PaySimpleResponse),
											'SSPT_PaymentGatewayTransactionID'=>$this->objUSAePay->PaySimpleResponseArray['RefNum'],
											'SSPT_PaymentStatus_Notes'=>$this->objUSAePay->PaySimpleResponseArray['Result']." - ".$this->objUSAePay->PaySimpleResponseArray['Error'],
											'SSPT_LastUpdatedDate'=>getDateTime(),
											'SSPT_Locale'=>GetUserLocale(),
											'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
									);
									$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
									$this->objSaleSub->updateSaleSubscriptionTransactionDetails($DataArray);
										
									/**/
									$totalCyclesPaid = $ArraySaleSubscription['SS_TotalCyclesPaid'];
									$ssStatus=12;
									if($intStatus==1)
									{
										$totalCyclesPaid = $ArraySaleSubscription['SS_TotalCyclesPaid']+1;										
										$ssStatus=15;
										if($ArraySaleSubscription['SS_PaymentMode']=='OTP')
											$ssStatus=16;
									}
										$nextOccuringDate = $this->getNextReoccurringDate($ArraySaleSubscription['SS_Schedule']);
										$logText = "Updated Details for Activation After Create Transaction on USAePay for Status :".$this->objUSAePay->PaySimpleResponseArray['Result']."  - Updated by : Admin , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
										$DataArray = array('SS_TotalCyclesPaid'=>$totalCyclesPaid,
												'SS_LastOuccringStatus'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SS_Status'=>$ssStatus,
												'SS_PaymentStatus'=>1,/*Recurring Running*/
												'SS_LastOuccringDate'=>getDateTime(0,'Y-m-d'),
												'SS_NextOuccringDate'=>$nextOccuringDate=='0000-00-00'?NULL:$nextOccuringDate,
												'SS_LastUpdatedDate'=>getDateTime(),
												'SS_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
										);
											
										$this->objSaleSub->SS_Id = $ArraySaleSubscription['SS_ID'];
										$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
										if($this->objSaleSub->P_Status)
										{
											
											if($ArraySaleSubscription['SS_EnableRecipt'])
											{
												/*merge transaction data array in sale sub details*/
												$ArraySaleSubscription['TransactionDetails'] = $arraySaleSubTransactionDetails;
												$ArraySaleSubscription['SS_NextOuccringDate'] = $nextOccuringDate;
												$ArraySaleSubscription['SS_LastOuccringDate'] = getDateTime(0,'Y-m-d');	
												$ArraySaleSubscription['PaymentStatus'] = $this->objUSAePay->PaySimpleResponseArray['Result'];																													
												$this->sentTransactionReceiptToCustomer($ArraySaleSubscription);												
											}
											if($intStatus==1)
												$this->SetStatus(1,'C21015');
											else
												$this->SetStatus(0,'E21032');
										}
										else
										{
											$this->SetStatus(0,'E21028');
										}
							}
							else
							{
								$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);
							}
						}
						else
						{
							$this->SetStatus(0,'E21013');
						}
					}
					else
					{
						$logText = "Updated Details after Activation - Updated by : Admin , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
						$DataArray = array('SS_Status'=>15,
										'SS_NextOuccringDate'=>getDateTime(0,'Y-m-d'),
										'SS_LastUpdatedDate'=>getDateTime(),
										'SS_Locale'=>GetUserLocale(),
										'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
						);
						$this->objSaleSub->SS_Id = $ArraySaleSubscription['SS_ID'];
						$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
						if($this->objSaleSub->P_Status)
						{
							$this->SetStatus(1,'C21013');
						}
						else
						{
							$this->SetStatus(0,'E21028');	
						}
					}
				}
				else
				{
					$this->SetStatus(0,'E21013');
				}
			}
			else
			{
				$this->SetStatus(0,'E21029');
			}			
			redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));			
		}
		
		
		public function sentTransactionReceiptToCustomer($saleSubDetails)
		{
			//dump($saleSubDetails);
			$transactionDetailsArray  = $saleSubDetails['TransactionDetails'];
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];
			$service_name = $saleSubDetails['SS_ItemName'];
			$charge_amount = $transactionDetailsArray['SSPT_PaidAmount'];
			$schedule = $saleSubDetails['SS_Schedule'];
			$order_id = $saleSubDetails['SS_RefNumber'];
			$payment_status = $saleSubDetails['PaymentStatus'];
			
			$link = "<a href='".FRONTURL."salesubscription/showTransactionReceipt/".keyEncryptFront($transactionDetailsArray['SSPT_ID'])."' style='color:#abc340;'/>Show Transaction Receipt</a>";
			$link_url = FRONTURL."salesubscription/showTransactionReceipt/".keyEncryptFront($transactionDetailsArray['SSPT_ID']);
			$emailId = $saleSubDetails['SS_EmailAddress'];
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionReceiptToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl = new View;
			$tpl->assign('service_name',$service_name);
			$tpl->assign('schedule',$schedule);
			$tpl->assign('charge_amount',$charge_amount);
			$tpl->assign('link',$link);
			$tpl->assign('link_url',$link_url);
			$tpl->assign('order_id',$order_id);
			$tpl->assign('uname',$uname);
			$tpl->assign('payment_status',$payment_status);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$emailId,
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;
			}
			unset($Eobj);
			return $this->P_Status;
		}
		
		public function StopRecurringTransation($ss_id)
		{
			$this->SS_Id = keyDecrypt($ss_id);			
			$FieldArray = array('SS_ID','SS_Status','SS_PaymentStatus','SS_PaySimpleCustomerID');
			$Condition = "AND SS_Status=15";
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray,$Condition);
			//dump($arraySaleSubDetails);	
			
			if(isset($arraySaleSubDetails['SS_ID']))
			{
				$this->load_model('Usaepay', 'objUSAePay');
				$this->objUSAePay->CustNum = $arraySaleSubDetails['SS_PaySimpleCustomerID'];
				if($this->objUSAePay->disableCustomer())
				{
					$logText = "Updated details After Stop Recurring on USAePay - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
					$DataArray = array('SS_Status'=>21,/*recurring stoped by administrator*/																												
										'SS_LastUpdatedDate'=>getDateTime(),
										'SS_Locale'=>GetUserLocale(),
										'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
					$this->objSaleSub->SS_Id = $arraySaleSubDetails['SS_ID'];
					$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
					if($this->objSaleSub->P_Status)
					{
						$this->SetStatus(1,'C21008');
					}
					else
					{
						$this->SetStatus(0,'E21021');
					}
				}
				else
				{
					$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);	
				}
			}
						
			redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));		
		}
		
		public function StartRecurringTransation($ss_id)
		{
			$this->SS_Id = keyDecrypt($ss_id);
			
			$FieldArray = array('SS_ID','SS_Status','SS_PaySimpleCustomerID','SS_PaySimplePaymentMethodID');
			$Condition = "AND SS_Status=21";
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray,$Condition);	
			if(isset($arraySaleSubDetails['SS_ID']))
			{
				$this->load_model('Usaepay', 'objUSAePay');
				$this->objUSAePay->CustNum = $arraySaleSubDetails['SS_PaySimpleCustomerID'];
				if($this->objUSAePay->enableCustomer())
				{
					$logText = "Updated details After Start Recurring on USAePay - Updated by (Admin): ".$this->loginDetails['admin_fullname']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
					$DataArray = array('SS_Status'=>15,/*recurring stoped by administrator*/																												
										'SS_NextOuccringDate'=>getDateTime(0,'Y-m-d'),
										'SS_LastUpdatedDate'=>getDateTime(),
										'SS_Locale'=>GetUserLocale(),
										'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
					$this->objSaleSub->SS_Id = $arraySaleSubDetails['SS_ID'];
					$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
					if($this->objSaleSub->P_Status)
					{
						$this->SetStatus(1,'C21009');
					}
					else
					{
						$this->SetStatus(0,'E21021');
					}
				}
				else
				{
					$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);	
				}
			}			
			redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));		
		}
		
		public function chargePayment()
		{
			//dump($_REQUEST);
			$SS_Id					= keyDecrypt(request('post','SS_Id',0));
			$this->SS_Id			= $SS_Id;
			$amountCharge			= request('post','amountCharge',0);
			if($this->P_Status && (trim($amountCharge)<=0 || trim($amountCharge)==''))			
				$this->SetStatus(0,'E21030');
			if($this->P_Status && $this->SS_Id=='')
				$this->SetStatus(0,'E21013');
			if($this->P_Status!=1)
				redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));	
			//echo $this->SS_Id;
			//echo $amountCharge;
			
			$FieldArray = array('SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_PaySimplePaymentMethodID','SS_EnableRecipt','SS_TotalCyclesPaid','SS_ItemName','SS_RefNumber',
					'SS_Schedule','SS_PaySimpleCustomerID','SS_Status','SS_PaymentStatus','SS_PaymentMode','SS_Amount','SS_CheckNumber');
			
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$ArraySaleSubscription = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);
			//dump($ArraySaleSubscription);
			if(isset($ArraySaleSubscription['SS_ID']))
			{
				if($ArraySaleSubscription['SS_Status']>=15)
				{
					$this->load_model('Usaepay', 'objUSAePay');
					$arrayTransStatus = get_setting('TransactionStatus');
			
					$logText = "Added Transaction details - Added by : System , Added on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
					$DataArray = array('SSPT_SSID'=>$ArraySaleSubscription['SS_ID'],
							'SSPT_PaymentType'=>'MNL',
							'SSPT_PaymentAmount'=>$amountCharge,
							'SSPT_PaidAmount'=>$amountCharge,
							'SSPT_Status'=>0,
							'SSPT_CreatedDate'=>getDateTime(),
							'SSPT_LastUpdatedDate'=>getDateTime(),
							'SSPT_Locale'=>GetUserLocale(),
							'UpdateLog'=>$logText
					);
			
					$this->SSPT_Id = $this->objSaleSub->insertSaleSubscriptionTransactionDetails($DataArray);
					if($this->SSPT_Id)
					{
						$FieldArray = array('*');
						$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
						$arraySaleSubTransactionDetails = $this->objSaleSub->getSalSubscriptionTransactionDetails($FieldArray);
						//dump($arraySaleSubTransactionDetails);
						$this->objUSAePay->ArrayTransactionDetails = array(
								'Command'=>'Check',
								'IgnoreDuplicate'=>false,
								'CheckData' => array(
										'CheckNumber' =>$ArraySaleSubscription['SS_CheckNumber']),
								'Details'=>array(
										'ClientIP'=>get_ip(),
										'CustReceipt'=>true,
										'PONum' => '', /*Purchase Order Number for commercial card transactions - 25 characters. discussion*/
										'OrderID' =>1000+$this->SSPT_Id, /*Transaction order ID. This field should be used to assign a unique order id to the transaction. The order ID can support 64 characters.*/
										'Description' => ($arraySaleSubTransactionDetails['SSPT_PaymentType']=='RC')?'Recurring Payment':'One Time Payment',
										'Amount'=>$arraySaleSubTransactionDetails['SSPT_PaymentAmount'])
						);
						$this->objUSAePay->CustNum = $ArraySaleSubscription['SS_PaySimpleCustomerID'];
						$this->objUSAePay->MethodID = $ArraySaleSubscription['SS_PaySimplePaymentMethodID'];
						if($this->objUSAePay->runCustomerTransaction())
						{
							$intStatus = 0;
							if(strtolower($this->objUSAePay->PaySimpleResponseArray['Result'])=='approved')
								$intStatus = 1;
							$logText = "Updated Transaction Details After Create Transaction on USAePay for Status :".$this->objUSAePay->PaySimpleResponseArray['Result']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SSPT_Status'=>$intStatus,
												'SSPT_PaySimpleStatus'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SSPT_PaymentGatewayName'=>'USAePay',
												'SSPT_PaymentGatewayRequest'=>serialize($this->objUSAePay->ArrayTransactionDetails),
												'SSPT_PaymentGatewayResponse'=>serialize($this->objUSAePay->PaySimpleResponse),
												'SSPT_PaymentGatewayTransactionID'=>$this->objUSAePay->PaySimpleResponseArray['RefNum'],
												'SSPT_PaymentStatus_Notes'=>$this->objUSAePay->PaySimpleResponseArray['Result']." - ".$this->objUSAePay->PaySimpleResponseArray['Error'],
												'SSPT_LastUpdatedDate'=>getDateTime(),
												'SSPT_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
												);
							$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
							$this->objSaleSub->updateSaleSubscriptionTransactionDetails($DataArray);
							if($ArraySaleSubscription['SS_EnableRecipt'])
							{
								/*merge transaction data array in sale sub details*/
								$ArraySaleSubscription['TransactionDetails'] = $arraySaleSubTransactionDetails;
								$ArraySaleSubscription['SS_NextOuccringDate'] = $nextOccuringDate;
								$ArraySaleSubscription['SS_LastOuccringDate'] = getDateTime(0,'Y-m-d');	
								$ArraySaleSubscription['PaymentStatus'] = $this->objUSAePay->PaySimpleResponseArray['Result'];																																															
								$this->sentTransactionReceiptToCustomer($ArraySaleSubscription);								
							}
							if($intStatus)
								$this->SetStatus(1,'C21016');
							else
								$this->SetStatus(0,'E21031');
						}	
						else
						{
							$this->SetStatus(0,'000',$this->objUSAePay->ErrorMessage);
						}
					}
					else
					{
						$this->SetStatus(0,'E21013');
					}
				}
				else
				{
					$this->SetStatus(0,'E21013');
				}
			}
			else
			{
				$this->SetStatus(0,'E21013');
			}
			
		redirect(URL."salesubscription/order/".keyEncrypt($this->SS_Id));		
		}
				
		public function getStateList($countryAbbr, $stateAbbr) 
		{
			$html = '<option value="">Select State</option>';
			$stateList = $this->objCMN->getStateList($countryAbbr);		
			if(count($stateList) > 0) 
			{
				for($s = 0; $s < count($stateList); $s++) {
					if(trim($stateAbbr) != '') {
						if($stateList[$s]['State_Value'] == $stateAbbr)
							$sel='selected';
						else
							$sel = '';
					} else 
						$sel = '';
						
					$html .= '<option value="' . $stateList[$s]['State_Value'] . '" ' . $sel . '>' . $stateList[$s]['State_Name'] . '</option>';   
				}
			}
			echo $html;
			exit;
		}
		
		private function getNextReoccurringDate($Interval,$Date=NULL)
		{
			/*YYYY-MM-DD*/
			if($Date==NULL)
				$Date = getDateTime(0,'Y-m-d');
			
			switch(strtolower($Interval))
			{
				case 'daily':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 days'));
				break;	
				case 'weekly':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 week'));
				break;
				case 'monthly':				
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 month'));
				break;
				case 'quarterly':
					$res_date = date('Y-m-d', strtotime($Date. ' + 3 month'));
				break;
				case 'bi-annually':
					$res_date = date('Y-m-d', strtotime($Date. ' + 6 month'));
				break;
				case 'annually':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 year'));
				break;
				default:
					$res_date = $Date;
			}	
			return $res_date;
		}	
		
		private function SetStatus($Status, $Code, $custom=NULL) 
		{
			$this->P_Status = $Status;
			$Msg = "Custom Confirmation message";
			if($custom!=NULL){
				$Msg = $custom;
				$Code = '000';
			}
			
			if($Status) {							
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"			=> $Msg,
					"msgLog"		=> 0,									
					"msgDisplay"	=> 1,
					"msgType"		=> 2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode" 			=> $Code,
					"errMsg"			=> $Msg,
					"errOriginDetails"	=> basename(__FILE__),
					"errSeverity"		=> 1,
					"msgDisplay"		=> 1,
					"msgType"			=> 1);
				EnPException::setError($messageParams);
			}
		}
	}
	
	/*
	
	Step#1
	--Descrive Flow
	ProcessStep1
	-Get Input
	-Validate Input
	-Add to Database
	-Add to Usapaye
	-Get Order Details
	-Update Details
	
	Step#2
	--Make Voids
	Input1
	Input1
	Input1
	Input1
	Input1
	
	CheckInput(Input1);
	AddUSAPAye(Array);
	GetOrderDetails(INPUT);
	
	AddOrderDetails(Array);
	SetOrderDetails(Array);
	
	Step#3
	Write Business Locaic of function 
	
	*/
	
?>

