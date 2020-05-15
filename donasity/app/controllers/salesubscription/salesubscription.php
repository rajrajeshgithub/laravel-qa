<?php
class Salesubscription_Controller extends Controller {
	
	public $tpl,$SS_Id,$P_Status,$SSPT_Id;
	
	public function __construct() 
	{		
		$this->load_model('Common', 'objCommon');
		$this->load_model('SaleSubscription', 'objSaleSub');
		$this->P_Status = 1;
	}
	
	public function index($secure_param) 
	{
		$secure_param 			= keyDecrypt($secure_param);
		list($SS_Id,$strDate) 	= explode('|',$secure_param);
		$this->SS_Id 			= $SS_Id;
		if(!$this->SS_Id || $this->SS_Id=='')
				redirect(URL.'error');
			
		$FieldArray = array('*','SS_ID','SS_RefNumber','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
							'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City',
							'SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaymentMode',
							'SS_Schedule','SS_SpecialInstruction','SS_EnableRecipt','SS_Status','SS_PaySimplePaymentMethodID','SS_LastUpdatedDate');
		$this->objSaleSub->SS_Id = $this->SS_Id;
		$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);		
		if(strtotime($arraySaleSubDetails['SS_LastUpdatedDate'])!=strtotime($strDate))		
			$this->SetStatus(0,'E90009');
		if($arraySaleSubDetails['SS_Status']==1) /*If process status - Order Added - it update form details else add details*/		
			$this->SetStatus(0,'E26007');
		
		if(!$this->P_Status)
			redirect(URL.'salesubscription/showConfirmation');
		
		$this->tpl = new view;
		$this->tpl->assign('SS_Id',$this->SS_Id);
		$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
		$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
		$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
		$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
		$this->tpl->assign('secure_param',$secure_param);
		$this->tpl->draw('salesubscription/salesubscription');	
	}
	
	public function showConfirmation()
	{
		$this->tpl = new view;
		$this->tpl->assign($this->objCommon->GetPageCMSDetails('payment_details_submission'));
		$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
		$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
		$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
		$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
		$this->tpl->draw('salesubscription/confirmation');	
	}
	
	public function addPaymentDetails()
	{
		//dump($_REQUEST);	
		$SS_Id					= keyDecrypt(request('post','SS_Id',0));
		$this->SS_Id			= $SS_Id;
		$secure_param			= request('post','secure_param',0);
		$bankName 				= request('post','bankName',0);
		$accountType			= request('post','accountType',0);
		$routingNumber			= request('post','routingNumber',0);
		$accountNumber			= request('post','accountNumber',0);
		$checkNumber			= request('post','checkNumber',0);
		$licenceNumber			= request('post','licenceNumber',0);
		$licenceState			= request('post','licenceState',0);
		
		$inputDataArray = array('SS_Id'=>$SS_Id,'bankName'=>$bankName,'accountType'=>$accountType,'routingNumber'=>$routingNumber,'accountNumber'=>$accountNumber,'checkNumber'=>$checkNumber,'licenceNumber'=>$licenceNumber,'licenceState'=>$licenceState);
		//echo $this->SS_Id;exit;
		if(!$this->SS_Id && $this->P_Status)					$this->SetStatus(0,'E26008');
		if(!$this->P_Status)									redirect(URL.'error');
		if(trim($inputDataArray['bankName'])=='' && $this->P_Status) 	$this->SetStatus(0,'E26001');
		if(trim($inputDataArray['accountType'])=='' && $this->P_Status) 	$this->SetStatus(0,'E26002');
		if(trim($inputDataArray['routingNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E26003');
		if(trim($inputDataArray['accountNumber'])=='' && $this->P_Status) $this->SetStatus(0,'E26004');
		if(trim($inputDataArray['checkNumber'])=='' && $this->P_Status) 	$this->SetStatus(0,'E26005');
		
		if(!$this->P_Status)
			redirect(URL.'salesubscription/'.$secure_param);
		
		$FieldArray = array('SS_ID','SS_PaySimpleCustomerID','SS_Status','SS_EnableRecipt','SS_PaySimplePaymentMethodID','SS_FirstName','SS_LastName');
		$this->objSaleSub->SS_Id = $this->SS_Id;
		$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	
		$ProcessStatus = $arraySaleSubDetails['SS_Status'];
		
		if($ProcessStatus==1)
			$this->SetStatus(0,'E26007');
		
		if(!$this->P_Status)
			redirect(URL.'salesubscription/showConfirmation');
			
		$logText = "Updated Bank details - Updated by (Customer): ".$arraySaleSubDetails['SS_FirstName']." ".$arraySaleSubDetails['SS_LastName']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
		$bankDetails = keyEncrypt($inputDataArray['bankName']."|".$inputDataArray['accountType']."|".$inputDataArray['checkNumber']."|XXXX".substr($inputDataArray['accountNumber'],-4));
		$DataArray = array('SS_BankDetails'=>$bankDetails,'SS_CheckNumber'=>$inputDataArray['checkNumber'],'SS_LastUpdatedDate'=>getDateTime(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
		$this->objSaleSub->SS_Id = $this->SS_Id;
		$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
		$this->load_model('Usaepayment','objUSAePay');
		$this->objUSAePay->ArrayPaymentMethod = array('MethodName'=>'ACH',
													'RecordType'=>'PPD',
													'Account'=>$inputDataArray['accountNumber'],
													'AccountType'=>$inputDataArray['accountType'],
													'CheckNumber'=>$inputDataArray['checkNumber'],
													'SecondarySort'=>0,
													'Routing'=>$inputDataArray['routingNumber'],													
													'DriversLicense'=>$inputDataArray['licenceNumber'],
													'DriversLicenseState'=>$inputDataArray['licenceState']
													);
		$this->objUSAePay->Default	= true;
		$this->objUSAePay->CustNum 	= $arraySaleSubDetails['SS_PaySimpleCustomerID'];
		
		if($this->objUSAePay->addCustomerPaymentMethod())
		{
			$PaymentMethodId = $this->objUSAePay->MethodID;	
			$logText = "Updated Payment details - Updated by (Customer): ".$arraySaleSubDetails['SS_FirstName']." ".$arraySaleSubDetails['SS_LastName']." Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
			$DataArray = array('SS_Status'=>11,'SS_PaySimplePaymentMethodID'=>$PaymentMethodId,'SS_LastUpdatedDate'=>getDateTime(),'SS_Locale'=>GetUserLocale(),'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')");
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			
			$this->sendAddPaymentNotificationToCustomer();
			$this->SetStatus(1,'C90002');
			$this->sendAddPaymentNotificationToAdmin();
			$redirection = URL."salesubscription/showConfirmation";	
		}
		else
		{
			$this->SetStatus(0,000,$this->objUSAePay->ErrorMessage);		
			$redirection = URL.'salesubscription/'.$secure_param;								
		}
		
		/*process log*/
		redirect($redirection);
	}
	
	private function sendAddPaymentNotificationToCustomer()
	{
		if($this->SS_Id!='')
		{
			$FieldArray = array('SS_ID','SS_RefNumber','SS_DateTime','SS_ItemCode','SS_ItemName','SS_Amount','SS_FirstName','SS_LastName','SS_StreetAddress1',
								'SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_Website','SS_PaymentMode','SS_Schedule',
								'SS_EmailAddress','SS_PaySimpleCustomerID','SS_Status','SS_BankDetails','SS_SpecialInstruction');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	
		}
		//dump($this->arraySaleSubDetails);
		$uname = $arraySaleSubDetails['SS_FirstName'].' '.$arraySaleSubDetails['SS_LastName'];
		$email_address = $arraySaleSubDetails['SS_EmailAddress'];
		
		$this->load_model('Email','objemail');
		$Keyword='paySimpleAddPaymentNotificationToCustomer';
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
		
	private function sendAddPaymentNotificationToAdmin()
	{
		if($this->SS_Id!='')
		{
			$FieldArray = array('SS_ID','SS_RefNumber','SS_DateTime','SS_ItemCode','SS_ItemName','SS_Amount','SS_FirstName','SS_LastName','SS_StreetAddress1',
								'SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_Website','SS_PaymentMode','SS_Schedule',
								'SS_EmailAddress','SS_PaySimpleCustomerID','SS_Status','SS_BankDetails','SS_SpecialInstruction');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	
		}
		//dump($this->arraySaleSubDetails);
		$uname = $arraySaleSubDetails['SS_FirstName'].' '.$arraySaleSubDetails['SS_LastName'];
		$email_address = $arraySaleSubDetails['SS_EmailAddress'];
		
		$this->load_model('Email','objemail');
		$Keyword='paySimpleAddPaymentNotificationToAdmin';
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
		'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$GetTemplate['EmailTo'],
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
	
	public function showTransactionReceipt($sspt_Id)
	{
		if($sspt_Id=='')
			redirect(URL.'error');
		$this->SSPT_Id = keyDecrypt($sspt_Id);
		$FieldsArray = array('SSPT_ID','SSPT_PaymentType','SSPT_PaymentAmount','SSPT_PaidAmount','SSPT_PaymentGatewayName','SSPT_Status','SSPT_PaymentGatewayTransactionID','SSPT_PaymentStatus_Notes',					
							'SSPT_CreatedDate','SS_RefNumber','SS_DateTime','SS_ItemId','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
							'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country',
							'SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaySimpleCustomerID','SS_PaymentMode','SS_Schedule','SS_TotalCyclesPaid','SS_StartDate',
							'SS_LastOuccringDate','SS_LastOuccringStatus','SS_NextOuccringDate','SS_PaymentStatus','SS_SpecialInstruction','SS_EnableRecipt','SS_Status'
							);
		$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
		$arrSaleTransDetails = $this->objSaleSub->GetSaleTransactionDetails($FieldsArray);
		//dump($arrSaleTransDetails);
		$this->tpl = new View;
		$this->tpl->assign('arrSaleTransDetails',$arrSaleTransDetails);
		$HTML = $this->tpl->draw('salesubscription/TransactionReceipt',true);
		//echo $HTML;exit; 
		$DP_Obj=LoadLib('DomPdfGen');
		//dump($DP_Obj);
		$DP_Obj->DP_HTML = $HTML;
		$DP_Obj->ProcessPDF();
		exit;
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
		//echo $msgValues=EnPException::getConfirmation();exit;
	}
	
}

?>