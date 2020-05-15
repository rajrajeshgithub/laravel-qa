<?php
class Donation_Controller extends Controller
{
		public $Pstatus	= 1;
		private $LoginUserDetail,$LoginUserId;
		private $DonationBasketArray,$UserDetailArray,$NpoDetailArray,$FundariserDetailArray;
		public $OrderID,$OrderDetailIDArray,$InputArray;
		function __construct()
		{
			$this->load_model('Donation','objDonation');
			$this->load_model('UserType1','objutype1');
			$this->load_model('Common','objCommon');
			$this->load_model('Fundraisers','objFund');
			
			$this->LoginUserDetail	= getSession('Users');
			$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
				
			$this->tpl = new view();
		}


		public function verify_user()
		{	
			if($this->objutype1->checkLogin(getSession('Users')))
			{
				$this->LoginUserDetail	= getSession('Users');
				redirect(URL."donation/add_order/".$this->LoginUserDetail['UserType1']['user_id']);
			}
			else
			{
				redirect(URL."donation/user_account");
			}
		}


		public function user_account()
		{
			
			if($this->objutype1->checkLogin(getSession('Users')))
			{
				$this->SetStatus(0,'E12011');
				redirect(URL."donation_checkout");
			}
			else
			{
				$this->tpl->assign("msgValues",EnPException::getConfirmation());
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('donationlogin'));
				$this->tpl->assign("CountryList",$this->objCommon->GetCountryListDB(array("Country_Title","Country_Abbrivation"),""," ORDER BY Country_Title"));
				$this->tpl->draw('donation/login');			
			}
		}


		public function account_login()
		{
			if($this->objutype1->checkLogin(getSession('Users')))
			{
				$this->SetStatus(0,'E12011');
				redirect(URL."donation_checkout");
			}
			else
			{
				$this->objutype1->EmailAddress	= request('post','email',0);
				$this->objutype1->Password		= request('post','password',0);
				$this->objutype1->LoginDB();
				if($this->objutype1->Pstatus)
				{
					$this->LoginUserDetail	= getSession('Users');
					redirect(URL."donation/add_order/".$this->LoginUserDetail['UserType1']['user_id']);
				}
				else
				{
					$this->SetStatus(0,'E12001');
					redirect(URL."donation/user_account");
				}	
			}
		}
	


		public function account_register()
		{
			if($this->objutype1->checkLogin(getSession('Users')))
			{
				$this->SetStatus(0,'E12011');
				redirect(URL."donation_checkout");
			}
			
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
			$this->objutype1->EmailAddress				= request('post','emailAddress',0);
			$this->objutype1->Password					= request('post','signupPassword',0);
			$this->objutype1->ConfirmPassword			= request('post','confirmPassword',0);
			$this->objutype1->RegDate					= getDateTime();
			$this->objutype1->UpdateDate				= getDateTime();
			$this->objutype1->LastLoginDate				= getDateTime();
			$this->objutype1->UserIP					= GetUserLocale();
			$this->objutype1->UserType					= 1;
			$this->objutype1->Status					= 1;
			
					
			if($this->objutype1->RegisterDB() > 0)
			{
				$this->objutype1->SetUserSession();
				redirect(URL."donation/add_order/".keyEncrypt($this->objutype1->UserID));
			}
			else
			{
				redirect(URL."donation/user_account");
			}
		}
	

		public function add_order($UserID="")
		{
			$this->LoginUserDetail	= getSession('Users');
			$this->LoginUserId		= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$UserID=keyDecrypt($UserID);
			
			/*if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
			{
				$this->SetStatus(0,'E12003');
				redirect(URL."donation/error");
			}*/


			
				$this->DonationBasketArray	= getCookie('DonatiobasketArray');
				
				$DataArray	= array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_Address1','RU.RU_Address2','RU.RU_Phone','RU.RU_City','RU.RU_State',
								'RU.RU_ZipCode','RU.RU_Country','RU.RU_EmailID');
				$this->UserDetailArray=$this->objutype1->GetUserDetails($DataArray," AND RU.RU_ID=".$UserID);
				
			
				$this->objDonation->OrderArray=$this->GenerateOrderArray();
				$this->objDonation->OrderDetailArray=$this->GenerateOrderDetailArray();
				$this->OrderID=$this->objDonation->AddDonationOrder();
				$this->OrderDetailIDArray=$this->objDonation->AddDonationOrderDetails();
				if($this->OrderID > 0)
				{
					/*IMPORTANT- on sucessfull order clear cart*/
					setcookie("DonatiobasketArray","",time()-3600,"/"); 
					redirect(URL."donation/process_donation/".keyEncrypt($this->OrderID));	
				}
				else
				{
					$this->SetStatus(0,'E12004');
					redirect(URL."donation_checkout");			
				}
				/*IMPORTANT- ADD CODE TO ROLL BACK TRANSACTION*/
			
					
			
		}
		

		public function process_donation($OrderID,$retryMode=false)
		{
			/*IMPORTANT
			-Put condition to redirect ro Confirmation page only if order has some paid transactions*/

			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");
			
			$this->objDonation->orderID	= keyDecrypt($OrderID);

			if($retryMode=="true")
			{
				
				if($this->objDonation->GetPayableDonation_SUM_COUNT("COUNT(PDD_ID)"," AND PDD_DonationReciptentType='R' AND (PDD_Status Between 1 AND 10)"))
					redirect(URL."donation/registred_charity_payment/".keyEncrypt($this->objDonation->orderID));
				
				elseif($this->objDonation->GetPayableDonation_SUM_COUNT("COUNT(PDD_ID)"," AND PDD_DonationReciptentType='N' AND (PDD_Status Between 1 AND 10)"))
					redirect(URL."donation/non_registred_charity_payment/".keyEncrypt($this->objDonation->orderID));
			}
			
			
			
			
			if($this->objDonation->GetPayableDonation_SUM_COUNT("COUNT(PDD_ID)"," AND PDD_DonationReciptentType='R' AND (PDD_Status = 0)"))
				redirect(URL."donation/registred_charity_payment/".keyEncrypt($this->objDonation->orderID));		
			
			elseif($this->objDonation->GetPayableDonation_SUM_COUNT("COUNT(PDD_ID)"," AND PDD_DonationReciptentType='N' AND (PDD_Status =0)"))
				redirect(URL."donation/non_registred_charity_payment/".keyEncrypt($this->objDonation->orderID));	
			
			//elseif($this->objDonation->GetPayableDonation_SUM_COUNT("COUNT(PDD_ID)"," AND (PDD_Status BETWEEN 11 AND 20)"))
			else 
				redirect(URL."donation/confirmation/".$OrderID);		
			
			
		
		}	



		public function registred_charity_payment($OrderID)
		{
			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");

			/*
			IMPORTANT
			-Put validation based upon payable amount. If zero value then redirect
			-In VIEW credit card Expiry should be auto populated, up to 6 years from current year
			*/
				$this->objDonation->orderID	= keyDecrypt($OrderID);
				
				/*
			IMPORTANT
			SUM(PDD_SubTotal) must inckude Transacfee based uspon status of TransactionFeePaidByUser
			*/
				$PayableAmount_Now=$this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_SubTotal)"," AND PDD_DonationReciptentType='R' AND (PDD_Status BETWEEN 0 AND 10)");
				$PayableAmount_Next=$this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_SubTotal)"," AND PDD_DonationReciptentType='N' AND (PDD_Status BETWEEN 0 AND 10)");
				
				$this->objDonation->GetDonationOrder(array("PD_BillingFirstName","PD_BillingLastName","PD_BillingEmailAddress"));
				
				$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('DONATION_BASKET_STEP2');
				$arrMetaInfo["nextpaymenttext"]=strtr($arrMetaInfo["nextpaymenttext"],array('{{PayableAmount_Next}}' =>$PayableAmount_Next));
				
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($arrMetaInfo);
				$Years 		=	range(getDateTime(0,'Y'),date("Y")+12);
				$this->tpl->assign("Years",$Years);
				$this->tpl->assign("OrderID",$OrderID);
				$this->tpl->assign("PayableAmount_Now",$PayableAmount_Now);
				$this->tpl->assign("PayableAmount_Next",$PayableAmount_Next);
				$this->tpl->assign("UserDetail",$this->objDonation->OrderArray);
				
				$this->tpl->draw("donation/regpayment");
				
			
		}




		public function process_registred_charity_payment($OrderID)
		{
			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");
			
			/*
			IMPORTANT
			-Validate All CC values
			-Validate & Match OrderId in URL & ORDER ID in Form
			-check coutn of payable records if 0 then send to registred_charity_payment_confirmation
			*/	
			 $this->objDonation->orderID	= keyDecrypt($OrderID);
			
			$CardName	= request('post','ccName',0);
			$CardNumber	= request('post','cardNumber',0);
			$CVV		= request('post','sqCode',0);
			$ExpMonth	= request('post','expMonth',0);
			$ExpYear	= request('post','expYear',0);
			$Email		= request('post','emailAddress',0);
			$OrderID	= request('post','orderid',0);
			$this->InputArray	= array('CardName'=>$CardName,'CardNumber'=>$CardNumber,'CVV'=>$CVV,'ExpMonth'=>$ExpMonth,'ExpYear'=>$ExpYear,'Email'=>$Email,'OrderID'=>$OrderID);
			
			$this->load_model('Stripe','ObjStripe');
			
          
			
			$orderArray=$this->objDonation->GetDonationOrder(array("PD_ID","PD_BillingFirstName","PD_BillingLastName","PD_ReferenceNumber","PD_BillingEmailAddress"));

			$orderDetailArray=$this->objDonation->GetDonationOrderDetails(array("PDD_ID","PDD_PD_ID","PDD_RUID","PDD_PIItemName","PDD_ItemCode","PDD_NPOEIN","PDD_CampID","PDD_PIItemType","PDD_Cost","PDD_SubTotal","PDD_TransactionFee",
			"PDD_StripeConnectedID","PDD_DonationReciptentType")," AND PDD_DonationReciptentType='R'  AND (PDD_Status BETWEEN 0 AND 10) ");
			
			foreach($orderDetailArray as $key)
			{
				$PaymentTransactionID=$this->objDonation->SetPaymentTransaction(array("PT_PDID"=>$orderArray["PD_ID"],"PT_RUID"=>$key["PDD_RUID"],"PT_PaymentType"=>"CC","PT_PaymentAmount"=>$key["PDD_SubTotal"],"PT_TransactionFee"=>$key[	
				"PDD_TransactionFee"],"PT_PaymentGatewayName"=>"STRIPE","PT_PaymentStatus"=>0,"PT_PaymentStatus_Notes"=>"New","PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),"PT_LastUpdatedDate"=>getDateTime()));
			
			
				$this->ObjStripe->amount=$key["PDD_SubTotal"];
				$this->ObjStripe->transactionFee=$key["PDD_TransactionFee"];
				$this->ObjStripe->cc_number=	$CardNumber;//"4242424242424242";
				$this->ObjStripe->cc_cvv=	$CVV;
				$this->ObjStripe->cc_exp_month=	$ExpMonth;
				$this->ObjStripe->cc_exp_year=	$ExpYear;
				$this->ObjStripe->cc_name=$CardName;
				$this->ObjStripe->invoice=$orderArray["PD_ReferenceNumber"];
				$this->ObjStripe->receipt_email=$orderArray["PD_BillingEmailAddress"];
				$this->ObjStripe->StripeConnectedAccountID=$key["PDD_StripeConnectedID"];
				$this->ObjStripe->txnDescription=$key["PDD_PIItemName"]." [".$key["PDD_PIItemType"]."]";
			
				if($this->ObjStripe->chargeCredit())
				{
					
					$this->objDonation->SetPaymentTransaction(array("PT_PaidAmount"=>$this->ObjStripe->stripe_response_filtered["PaidAmount"],"PT_PaymentGatewayRequest"=>keyEncrypt(serialize($this->ObjStripe->stripe_request)),
					"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjStripe->stripe_response_complete)),"PT_PaymentGatewayTransactionID"=>$this->ObjStripe->stripe_response_filtered["TransactionID"],"PT_PaymentStatus"=>1,
					"PT_PaymentStatus_Notes"=>"Paid","PT_Comment"=>$this->strip_quote($this->ObjStripe->stripe_response_filtered["PayNote"]),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
								
					$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>11,"PDD_Status_Notes"=>"Paid Sucessfully"),$key["PDD_ID"]);
					$this->objDonation->SetDonationOrder(array("PD_Status"=>11,"PD_LastUpdatedDate"=>getDateTime()),$key["PDD_PD_ID"]);
					
					if ($key["PDD_PIItemType"]=="CD")
					{
						$donationRecived=0;
						$temp=$this->objFund->GetFundraiserDetails(array("Camp_DonationReceived")," AND Camp_ID=".$key["PDD_CampID"]);
						$donationRecived=$temp[0]["Camp_DonationReceived"];
						if($donationRecived<=0) $donationRecived=0;
						$donationRecived=$donationRecived+$key["PDD_Cost"];
						$this->objFund->SetFundraiserDetails(array("Camp_DonationReceived"=>$donationRecived),$key["PDD_CampID"]);
						
						$this->objFund->F_Camp_RUID=$key["PDD_RUID"];
						$this->objFund->F_Camp_ID=$key["PDD_CampID"];
						$this->objFund->F_Comment="Donation $".$key["PDD_Cost"];
						$this->objFund->F_UserName=$orderArray["PD_BillingFirstName"]." ".$orderArray["PD_BillingLastName"];
						$this->objFund->ProcessFundraiseComment();
						
						
					}
					//dump($this->ObjStripe->stripe_response_filtered);
					
				}
				else
				{
					
					
					$this->objDonation->SetPaymentTransaction(array("PT_PaidAmount"=>0,"PT_PaymentStatus"=>2,"PT_PaymentStatus_Notes"=>$this->strip_quote($this->ObjStripe->stripe_response_err["message"]),"PT_PaymentGatewayRequest"=>keyEncrypt(serialize($this
					->ObjStripe->stripe_request)),"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjStripe->stripe_response_complete)),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
					$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>2,"PDD_Status_Notes"=>$this->strip_quote($this->ObjStripe->stripe_response_err["message"])),$key["PDD_ID"]);
					
					//$this->objDonation->SetDonationOrder(array("PD_Status"=0,"PD_LastUpdatedDate"=>getDateTime),$orderDetailArray["PDD_PD_ID"]);
					//dump($this->ObjStripe->stripe_response_err);
					
				}
			}
			redirect(URL."donation/registred_charity_payment_confirmation/".$OrderID);
			
		}


		public function registred_charity_payment_confirmation($OrderID)
		{
			
			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");
			/*IMPORTANT
			-Delete objDonation->ConfirmationArray
			-Delete $this->objDonation->GetPaymentStatus
			-Put check based upon count of OrderDetailArray
			-Spanish coinversion pendign in VIEWS
			-Set iCones in view
			-Based upon count of Failure provide URL to go back to payment page
			-Provide option to skip or disable  redirection to process_donation
			*/
			
				$this->objDonation->orderID	= keyDecrypt($OrderID);//echo $this->objDonation->orderID	;exit;
				
				$PaymentFail_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='R' AND (PDD_Status BETWEEN 1 AND 10)");
				$PaymentSucess_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='R' AND (PDD_Status BETWEEN 11 AND 20)");
				$PayableAmount_Next=$this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_TransactionFee)"," AND PDD_DonationReciptentType='N' AND (PDD_Status BETWEEN 0 AND 10)");
				
			//	if ($PaymentFail_RowCount>0)
			//	SHOW LINK
				
				$this->tpl->assign("msgValues",EnPException::getConfirmation());
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('registerpaymentconfirmation'));
				$OrderDetailArray=$this->objDonation->GetDonationOrderDetails(array('PDD_PIItemName','PDD_Status','PDD_Cost','PDD_Status_Notes')," AND PDD_DonationReciptentType='R' ");
				
				$this->tpl->assign('DataStatus',$OrderDetailArray);
				$this->tpl->assign('EncOrderID',$OrderID);
				$this->tpl->assign('PaymentFail_RowCount',$PaymentFail_RowCount);
				$this->tpl->assign('PayableAmount_Next',$PayableAmount_Next);
				
				
				$this->tpl->draw("donation/regpaymentconfirmation");
			
		}


	public function non_registred_charity_payment($OrderID)
	{
			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");
			
			$this->objDonation->orderID	= keyDecrypt($OrderID);
			
			
			$OrderArray=$this->objDonation->GetDonationOrder(array("PD_BillingFirstName","PD_BillingLastName","PD_BillingEmailAddress","PD_BillingAddress1","PD_BillingAddress2","PD_BillingCity",
			"PD_BillingState","	PD_BillingCountry","PD_BillingZipCode","PD_BillingPhone"));
			
			$PaymentFail_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='N' AND (PDD_Status BETWEEN 1 AND 10)");
			$Sum_TransactionFee=$this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_TransactionFee)"," AND PDD_DonationReciptentType='N' AND (PDD_Status BETWEEN 0 AND 10)");
			$Sum_TotalDonation=$this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_Cost)"," AND PDD_DonationReciptentType='N' AND (PDD_Status BETWEEN 0 AND 10)");
			
			$StateList	= $this->objCommon->getStateList($this->objDonation->OrderArray['PD_BillingCountry']);	
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('DONATION_BASKET_STEP3');
			
			$Registered_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='R'");
			
			$arrMetaInfo["bankaccount_payableamount"]=strtr($arrMetaInfo["bankaccount_payableamount"],array('{{PayableAmount}}' => $Sum_TransactionFee,"{{DonationAmount}}"=>$Sum_TotalDonation));
			
			$arrMetaInfo["termscondion1"]=strtr($arrMetaInfo["termscondion1"],array('{{PayableAmount}}' => $Sum_TransactionFee,"{{DonationAmount}}"=>$Sum_TotalDonation));
			$this->tpl->assign("msgValues",EnPException::getConfirmation());
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);						
			$this->tpl->assign("EncOrderID",$OrderID);
			$this->tpl->assign("UserDetail",$OrderArray);
			$this->tpl->assign("Statelist",$StateList);
			$this->tpl->assign("Sum_TransactionFee",$Sum_TransactionFee);
			$this->tpl->assign('PaymentFail_RowCount',$PaymentFail_RowCount);
			$this->tpl->assign('Registered_RowCount',$Registered_RowCount);
			$this->tpl->assign("CountryList",$this->objCommon->GetCountryListDB(array("Country_Title","Country_Abbrivation"),""," ORDER BY Country_Title"));
			$this->tpl->draw("donation/nonregpayment");	
		
	}
	
	
	
	
		public function process_non_registred_charity_payment()
		{
			/*
			IMPORTANT
			-Match PayableAmount with sum
			-Put Bnak Nam ein notes
			-Set UsaePay Variables
			*/
			
			$OrderID	= request('post','orderid',0);
			$FirstName	= request('post','fname',0);
			$LastName	= request('post','lname',0);
						
			$BankName	=	request('post','bankName',0);
			$AccountNumber	=	request('post','ddaNumber',0);
			$RoutingNumber	=	request('post','abaRoutNumber',0);
			$AccountType	=	request('post','accountType',0);
			$CheckNumber	=	request('post','checkNumber',0);
			$PayableAmount	=	request('post','payableAmount',0);
			
			$this->objDonation->orderID	= keyDecrypt($OrderID);
			
			$orderArray=$this->objDonation->GetDonationOrder(array("PD_ID","PD_RU_ID","PD_ReferenceNumber"));

			
			$orderDetailArray=$this->objDonation->GetDonationOrderDetails(array("PDD_ID","PDD_PD_ID","PDD_RUID","PDD_PIItemName","PDD_ItemCode","PDD_NPOEIN","PDD_CampID","PDD_PIItemType","PDD_SubTotal","PDD_TransactionFee",
			"PDD_StripeConnectedID","PDD_DonationReciptentType")," AND PDD_DonationReciptentType='N'   AND (PDD_Status BETWEEN 0 AND 10)");
		
			$eCheckNotesArray=array("Bank_Name"=>$BankName,"Account_Number"=>$AccountNumber,"Account_Type"=>$AccountType,"Check_Number"=>$CheckNumber,"Routing_Number"=>$RoutingNumber);	

			$PaymentTransactionID=$this->objDonation->SetPaymentTransaction(array("PT_PDID"=>$orderArray["PD_ID"],"PT_RUID"=>$orderArray["PD_RU_ID"],"PT_PaymentType"=>"ACH","PT_PaymentAmount"=>$PayableAmount,"PT_TransactionFee"=>
			$PayableAmount,"PT_PaymentGatewayName"=>"USAEPAY","PT_PaymentStatus"=>1,"PT_PaymentStatus_Notes"=>"New","PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),"PT_LastUpdatedDate"=>getDateTime()));
			
			
			
			$this->load_model('Usaepay','ObjUsaepay');
			$this->ObjUsaepay->amount=$PayableAmount;						// charge amount in dollars
			$this->ObjUsaepay->invoice=$orderArray["PD_ReferenceNumber"];   					// invoice number.  must be unique.
			$this->ObjUsaepay->description="Transaction Fee Payment Only";			// description of charge
			//$this->ObjUsaepay->accountnumber="126543456";			// bank account number
			$this->ObjUsaepay->account=$AccountNumber;					// bank account number
			$this->ObjUsaepay->routing=$RoutingNumber;					// bank routing number
			$this->ObjUsaepay->checknum=$CheckNumber;						// Check Number
			$this->ObjUsaepay->accounttype=$AccountType;       		// Checking or Savings
			$this->ObjUsaepay->billfname=$FirstName;
			$this->ObjUsaepay->billlname=$LastName;
			$this->ObjUsaepay->email=$orderArray["PD_BillingEmailAddress"];
			$this->ObjUsaepay->orderid=$orderArray["PD_ID"];
			
			
			if($this->ObjUsaepay->Process())
			
			{
					$this->objDonation->SetPaymentTransaction(array("PT_PaidAmount"=>$PayableAmount,"PT_PaymentGatewayRequest"=>keyEncrypt(serialize($this->ObjUsaepay->usaepay_request)),"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjUsaepay->usaepay_response_complete)),"PT_PaymentGatewayTransactionID"=>$this->ObjUsaepay->usaepay_response_filtered["TransactionID"],"PT_PaymentStatus"=>1,"PT_PaymentStatus_Notes"=>"Paid","PT_Comment"=>$this->strip_quote($this->ObjUsaepay->usaepay_response_filtered["PayNote"]),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
								
					foreach( $orderDetailArray as $key )
					$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_eCheckStatus"=>1,"PDD_Status"=>12,"PDD_Status_Notes"=>"Transaction Fee Paid Successfully","PDD_eCheckComment"=>serialize($eCheckNotesArray)),$key["PDD_ID"]);
					
					$this->objDonation->SetDonationOrder(array("PD_Status"=>11,"PD_LastUpdatedDate"=>getDateTime()),$key["PDD_PD_ID"]);
					
				//	print_r($this->tran->usaepay_response_filtered);
				//	print_r($this->tran->usaepay_response_complete);
				
				redirect(URL."donation/process_donation/".$OrderID);
			}
			else
			{
				$this->objDonation->SetPaymentTransaction(array("PT_PaidAmount"=>0,"PT_PaymentStatus"=>2,"PT_PaymentStatus_Notes"=>$this->strip_quote($this->ObjUsaepay->error),"PT_PaymentGatewayRequest"=>keyEncrypt(serialize($this->ObjUsaepay->usaepay_request)),"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjUsaepay->usaepay_response_complete)),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
				foreach( $orderDetailArray as $key )
				$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>3,"PDD_Status_Notes"=>$this->strip_quote($this->ObjUsaepay->error)),$key["PDD_ID"]);
				//print_r($this->tran->usaepay_response_filtered);
				//print_r($this->tran->usaepay_response_complete);	
				
				
				$messageParams=array("errCode"=>"000",
									 "errMsg"=>$this->ObjUsaepay->error,
									 "errOriginDetails"=>basename(__FILE__),
									 "errSeverity"=>1,
									 "msgDisplay"=>1,
									 "msgType"=>1);
				EnPException::setError($messageParams);
				
				redirect(URL."donation/non_registred_charity_payment/".$OrderID);
			}
			

			
		}
		
		
		public function error()
		{
				$this->tpl->assign("msgValues",EnPException::getConfirmation());
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('donationerror'));
				$this->tpl->draw('donation/error');			
				/*IMPORTANT - Error page created need to format HTML*/
			
		}

		
		
	
		public function confirmation($OrderID)
		{
			if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
				redirect(URL."donation/error");
				
				
			$this->objDonation->orderID	= keyDecrypt($OrderID);
			$OrderArray	= $this->objDonation->GetDonationOrder(array('PD_BillingFirstName','PD_BillingLastName','PD_BillingEmailAddress','PD_ID','PD_ReferenceNumber'));	
			$OrderDetailTransactionArray	= $this->objDonation->GetDonationOrderTransactionDetails(array('PDD.PDD_ID','PDD.PDD_PD_ID','PDD_PIItemType','PDD.PDD_PIItemName','PDD.PDD_Cost','PDD.PDD_SubTotal','PT.PT_PaymentGatewayTransactionID','PT.PT_PaymentStatus','PT.PT_LastUpdatedDate','PDD.PDD_Status_Notes','PDD.PDD_DonationReciptentType','PDD.PDD_TaxExempt'));
			
			$PaymentFail_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)","  AND (PDD_Status BETWEEN 1 AND 10)");
			$PaymentSucess_RowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)","  AND (PDD_Status BETWEEN 11 AND 20)");
			
			$totaldonation	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_Cost)"," AND (PDD_Status BETWEEN 11 AND 20)");
			$transfeeamt	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_TransactionFee)"," AND (PDD_Status BETWEEN 11 AND 20)");
			$paidamt	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_SubTotal)"," AND (PDD_Status BETWEEN 11 AND 20)");
			
			$RegisteredRowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='R'");
			$NonRegisteredRowCount=$this->objDonation->GetPayableDonation_SUM_COUNT("count(PDD_ID)"," AND PDD_DonationReciptentType='N'");
			
			$username	= $OrderArray['PD_BillingFirstName']." ".$OrderArray['PD_BillingLastName'];
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('DONATION_BASKET_STEP4');
			
			$arrMetaInfo["pagedetail"]=strtr($arrMetaInfo["pagedetail"],array('{{username}}' => $username,'{{email}}'=>$OrderArray['PD_BillingEmailAddress'],'{{OrderReferenceNumber}}'=>$OrderArray['PD_ReferenceNumber']));
			$arrMetaInfo["NoSucessfullPaymentFound"]=strtr($arrMetaInfo["NoSucessfullPaymentFound"],array('{{OrderID}}' => $OrderID));
			$arrMetaInfo["FailPaymentFound"]=strtr($arrMetaInfo["FailPaymentFound"],array('{{OrderID}}' => $OrderID));
			
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->assign('EncOrderID',$OrderID);
			$this->tpl->assign('OrderDetailTransactionArray',$OrderDetailTransactionArray);
			$this->tpl->assign('PaymentFail_RowCount',$PaymentFail_RowCount);
			$this->tpl->assign('PaymentSucess_RowCount',$PaymentSucess_RowCount);
			$this->tpl->assign('totaldonation',$totaldonation);
			$this->tpl->assign('transfeeamt',$transfeeamt);
			$this->tpl->assign('paidamt',$paidamt);
			$this->tpl->assign('RegisteredRowCount',$RegisteredRowCount);
			$this->tpl->assign('NonRegisteredRowCount',$NonRegisteredRowCount);
			$this->tpl->draw("donation/confirmation");
		}
	
	public function Print_Receipt($OrderID,$DonationDetailID)
	{

			//if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
			//	redirect(URL."donation/error");
			$OrderID= keyDecrypt($OrderID);	
			$DonationDetailID	= keyDecrypt($DonationDetailID);
			
			$this->objDonation->orderID	= $OrderID;
			
			$orderArray=$this->objDonation->GetDonationOrder(array("PD_ID","PD_RU_ID","PD_BillingFirstName","PD_BillingLastName","PD_BillingAddress1","PD_BillingAddress2","PD_BillingCity", "PD_BillingState","PD_BillingCountry","PD_BillingZipCode","PD_BillingEmailAddress"));
			
			$OrderDetailsArray	= $this->objDonation->GetDonationOrderTransactionDetails(array('PDD.PDD_ID','PDD_DateTime','PDD_PIItemName','PDD_ItemAttributes','PDD_NPOEIN','PDD_TransactionFee','PDD_SubTotal','PT.PT_PaymentGatewayTransactionID'), " AND PDD.PDD_ID=".$DonationDetailID);
			
			//dump($OrderDetailsArray);
			
			$Donor_Name=$orderArray["PD_BillingFirstName"]." ".$orderArray["PD_BillingLastName"];
			$Donor_Address=$orderArray["PD_BillingAddress1"]." ".$orderArray["PD_BillingAddress2"].", ".$orderArray["PD_BillingCity"].", ".$orderArray["PD_BillingState"]." ".$orderArray["PD_BillingZipCode"];
			$Donor_Email_Address=$orderArray["PD_BillingEmailAddress"];
			$Donation_Date=$OrderDetailsArray[0]["PDD_DateTime"];
			$Donation_Amount=$OrderDetailsArray[0]["PDD_SubTotal"];
			$Donation_Transaction_Fee=$OrderDetailsArray[0]["PDD_TransactionFee"];
			$Donation_Name=$OrderDetailsArray[0]["PDD_PIItemName"];
			$Donation_NPOEIN=$OrderDetailsArray[0]["PDD_NPOEIN"];
			$Donation_Address=$OrderDetailsArray[0]["PDD_ItemAttributes"];
			$Donation_Payment_TransactionID=$OrderDetailsArray[0]["PT_PaymentGatewayTransactionID"];
			
			$Donation_NPOEIN==""?"N/A":$Donation_NPOEIN;
			$Donation_Address==""?"N/A":$Donation_Address;
			
			
			$arrMetaInfo	= $this->objCommon->GetPageCMSDetails('DONATION_PRINT_RECEIPT');
			
			$arrMetaInfo["Donor_Information"]=strtr($arrMetaInfo["Donor_Information"],array('{{Donation_Date}}' => formatDate($Donation_Date,'m/d/Y h:i a'),'{{Donor_Name}}'=>$Donor_Name,'{{Donor_Address}}'=>$Donor_Address,"{{Donor_Email_Address}}"=>
			$Donor_Email_Address,'{{Donation_Amount}}'=>$Donation_Amount,'{{Donation_Transaction_Fee}}'=>$Donation_Transaction_Fee));
			
			$arrMetaInfo["Donation_Information"]=strtr($arrMetaInfo["Donation_Information"],array('{{Donation_Name}}' => $Donation_Name,'{{Donation_NPOEIN}}'=>$Donation_NPOEIN,'{{Donation_Address}}'=>$Donation_Address,
			"{{Donation_Payment_TransactionID}}"=>$Donation_Payment_TransactionID));
			$arrMetaInfo["Message1"]=strtr($arrMetaInfo["Message1"],array('{{Donation_Name}}' => $Donation_Name));
			$arrMetaInfo["Message2"]=strtr($arrMetaInfo["Message2"],array('{{Donation_Name}}' => $Donation_Name));

			$this->tpl->assign($arrMetaInfo);
			$HTML=$this->tpl->draw('donation/donation_reciept_pdf',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
	
	}
	
	public function Print_Confirmation($OrderID)
	{

			//if(!$this->Validate_LogedinUser_And_OrderID($OrderID))
			//	redirect(URL."donation/error");
			$OrderID= keyDecrypt($OrderID);	
			$DonationDetailID	= keyDecrypt($DonationDetailID);
			
			$this->objDonation->orderID	= $OrderID;
	
				
						
			$OrderArray	= $this->objDonation->GetDonationOrder(array('PD_BillingFirstName','PD_BillingLastName','PD_BillingEmailAddress','PD_ID','PD_ReferenceNumber'));	
			$OrderDetailTransactionArray	= $this->objDonation->GetDonationOrderTransactionDetails(array('PDD.PDD_ID','PDD.PDD_PD_ID','PDD_PIItemType','PDD.PDD_PIItemName','PDD.PDD_Cost','PDD.PDD_SubTotal','PT.PT_PaymentGatewayTransactionID','PT.PT_PaymentStatus','PT.PT_LastUpdatedDate','PDD.PDD_Status_Notes','PDD.PDD_DonationReciptentType','PDD.PDD_TaxExempt'));
			
			
			$totaldonation	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_Cost)"," AND (PDD_Status BETWEEN 11 AND 20)");
			$transfeeamt	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_TransactionFee)"," AND (PDD_Status BETWEEN 11 AND 20)");
			$paidamt	= $this->objDonation->GetPayableDonation_SUM_COUNT("SUM(PDD_SubTotal)"," AND (PDD_Status BETWEEN 11 AND 20)");
								
			$username	= $OrderArray['PD_BillingFirstName']." ".$OrderArray['PD_BillingLastName'];
				
			
			$this->tpl->assign('OrderDetailTransactionArray',$OrderDetailTransactionArray);
			$this->tpl->assign('UserName',$username);
			$this->tpl->assign('EmailAddress',$OrderArray["PD_BillingEmailAddress"]);
			$this->tpl->assign('totaldonation',$totaldonation);
			$this->tpl->assign('transfeeamt',$transfeeamt);
			$this->tpl->assign('paidamt',$paidamt);
			
			$this->tpl->assign($arrMetaInfo);
			$HTML=$this->tpl->draw('donation/confirmation_page_pdf',true);
			$DP_Obj=LoadLib('DomPdfGen');
			$DP_Obj->DP_HTML=$HTML;
			$DP_Obj->ProcessPDF();
	
	}
	
	
	public function GetRegisteredPaidAmt($OrderID)
	{
		$orderDetailArray	= $this->objDonation->GetDonationOrderDetails(array("PDD_SubTotal")," AND PDD_DonationReciptentType='R'");
		$Total	= 0;
		if($this->objDonation->OrderArray)
		{
			foreach($this->objDonation->OrderArray as $val)
			{
				$Total	+= $val['PDD_SubTotal'];	
			}
		}
		return $Total;
	}
	


		
		
		
		
		
	

		private function GenerateOrderArray()
		{
				$temp=array (
					"PD_ItemType"=>"D"
					,"PD_ReferenceNumber"=>$this->generateRefNumber()
					,"PD_BillingFirstName"=>$this->UserDetailArray['RU_FistName']
					,"PD_BillingLastName"=>$this->UserDetailArray['RU_LastName']
					,"PD_BillingAddress1"=>$this->UserDetailArray['RU_Address1']
					,"PD_BillingAddress2"=>$this->UserDetailArray['RU_Address2']
					,"PD_BillingCity"=>$this->UserDetailArray['RU_City']
					,"PD_BillingState"=>$this->UserDetailArray['RU_State']
					,"PD_BillingCountry"=>$this->UserDetailArray['RU_Country']
					,"PD_BillingZipCode"=>$this->UserDetailArray['RU_ZipCode']
					,"PD_BillingEmailAddress"=>$this->UserDetailArray['RU_EmailID']
					,"PD_BillingPhone"=>$this->UserDetailArray['RU_Phone']
					,"PD_RU_ID"=>$this->UserDetailArray['RU_ID']
					,"PD_SubTotal"=>$this->DonationBasketArray['calculation']['Total']
					,"PD_TransactionFee"=>$this->DonationBasketArray['calculation']['TransactionFee']
					,"PD_TransactionFeePaidByUser"=>$this->DonationBasketArray['calculation']['TransactionFeePaidByUser']
					,"PD_TotalAmount"=>$this->DonationBasketArray['calculation']['TotalPay']
					,"PD_Status"=>"0"
					,"PD_IP"=>GetUserLocale()
					,"PD_CreatedDate"=>getDateTime()
					,"PD_LastUpdatedDate"=>getDateTime()
					,"PD_CreatedBy"=>$this->UserDetailArray['RU_FistName']
					,"PD_Source"=>"donasity.com"
					,"PD_Deleted"=>0
				
				);
			return $temp;
		}


		private function GenerateOrderDetailArray()
		{
	      	
			$temp;
			$ActualDonationAmount;
			$PayableAmount;
			
			foreach (array('NPOR','NPONR','AMBASSADOR','FUNDARISER') as $key)
			{
				if(isset($this->DonationBasketArray[$key]))
				foreach ($this->DonationBasketArray[$key] as $BasketValue)
				{
					switch($key)
					{ 
						case 'NPOR':
							$this->NpoDetailArray=$this->objDonation->GetNpoDetails("","N.NPO_ID=".$BasketValue['ID']);
							$DonationReciptentType=$this->NpoDetailArray['Stripe_status']==1?"R":"N";
							$PaymentType=$BasketValue['Recurring']==1?"FRP":"OTP";
							$StripeConnectedID=$this->NpoDetailArray['Stripe_ClientID'];
							$NPOEIN=$this->NpoDetailArray['NPO_EIN'];
							break;
					
					
					
						case "NPONR":
							$this->NpoDetailArray=$this->objDonation->GetNpoDetails("","N.NPO_ID=".$BasketValue['ID']);
							$DonationReciptentType=$this->NpoDetailArray['Stripe_status']==1?"R":"N";
							$PaymentType=$BasketValue['Recurring']==1?"FRP":"OTP";
							$StripeConnectedID=$this->NpoDetailArray['Stripe_ClientID'];
							$NPOEIN=$this->NpoDetailArray['NPO_EIN'];
							break;
							
						case "AMBASSADOR":					
							$this->NpoDetailArray=$this->objDonation->GetNpoDetails("","N.NPO_ID=".$BasketValue['ID']);
							$DonationReciptentType=$this->NpoDetailArray['Stripe_status']==1?"R":"N";
							$PaymentType=$BasketValue['Recurring']==1?"FRP":"OTP";
							$StripeConnectedID=$this->NpoDetailArray['Stripe_ClientID'];
							break;
							
						case "FUNDARISER":
							$this->load_model('Fundraisers','objFund');
							$this->FundariserDetailArray=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_Title','Camp_Stripe_ConnectedID','Camp_NPO_EIN',"Camp_TaxExempt","Camp_RUID","Camp_Cat_ID","Camp_Location_City",
							"Camp_Location_State","Camp_Location_Country")," AND Camp_ID=".$BasketValue['ID']);
							
							$DonationReciptentType="R";
							$PaymentType=$BasketValue['Recurring']==1?"FRP":"OTP";
							$StripeConnectedID= $this->FundariserDetailArray[0]['Camp_Stripe_ConnectedID'];
							$NPOEIN=$this->FundariserDetailArray[0]['Camp_NPO_EIN'];
							$CampID=$this->FundariserDetailArray[0]['Camp_ID'];
							
							break;
						default:
							/*IMPORTABT - Delete the ORDER ENTRY & Send to Error*/
							break;
					}
					
						
					if($this->DonationBasketArray['calculation']['TransactionFeePaidByUser']==1)
					{
						$ActualDonationAmount=$BasketValue['Donation_Amount'];
						$PayableAmount=$BasketValue['Donation_Amount']+$BasketValue['Transaction_Fee'];
					}
					else
					{
						$ActualDonationAmount=$BasketValue['Donation_Amount']-$BasketValue['Transaction_Fee'];
						$PayableAmount=$BasketValue['Donation_Amount'];	
					}
					
					
					
					$temp[]=array("PDD_DateTime"=>getDateTime()
					,"PDD_RUID"=>$this->UserDetailArray['RU_ID']
					,"PDD_DonationReciptentType"=>$DonationReciptentType
					,"PDD_StripeConnectedID"=>$StripeConnectedID
					,"PDD_ItemCode"=>$BasketValue['itemcode']
					,"PDD_NPOEIN"=>$NPOEIN
					,"PDD_CampID"=>$CampID
					,"PDD_PIItemType"=>$BasketValue['Product_Type']
					,"PDD_PIItemName"=>$BasketValue['Title']
					,"PDD_PIItemDescription"=>$BasketValue['Category']
					,"PDD_ItemAttributes"=>$BasketValue['City'].", ".$BasketValue['State'].", ".$BasketValue['Zipcode']
					,"PDD_CategoryCode"=>""
					,"PDD_Cost"=>$ActualDonationAmount
					,"PDD_TransactionFee"=>$BasketValue['Transaction_Fee']
					,"PDD_TransactionFeePaidByUser"=>$this->DonationBasketArray['calculation']['TransactionFeePaidByUser']
					,"PDD_TaxExempt"=>$BasketValue['TaxExempt']
					,"PDD_SubTotal"=>$PayableAmount  
					,"PDD_PaymentType"=>$PaymentType
					,"PDD_ReoccuringProfileID"=>""
					,"PDD_PaymentTransactionID"=>""
					,"PDD_Comments"=>""
					,"PDD_Deleted"=>"0"
					,"PDD_eCheckStatus"=>"0"
					,"PDD_eCheckDate"=>""
					,"PDD_eCheckComment"=>""
					,"PDD_Status"=>0
					,"PDD_Status_Notes"=>"New");
					
				
				
				}
			}
			
			return $temp;
		}


	
	public function Ambassador_Donation($uniqueKey)
	{
		$uniqueKey=trim($uniqueKey);
		$type;
		if ($uniqueKey=="") $uniqueKey=0;
		
		$WidgetDetailsArray=$this->objDonation->GetUT3WudgetRecord(array("*")," AND W_UniqueKey='".$uniqueKey."'");
		echo($uniqueKey);
		
		//dump(count($WidgetDetailsArray));
		
		if(count($WidgetDetailsArray)>1 )
		{
			redirect(URL."donation_checkout/index/addcart/".keyEncrypt($WidgetDetailsArray["W_CharityID"])."/".$WidgetDetailsArray["W_CharityType"]);
		}
		else
		{
			$this->SetStatus(0,'E12011');
			redirect(URL."donation_checkout");
		}
		
		
	}
	
	
	
	
	private function generateRefNumber()
	{
		return $this->UniqueRandomNumbersWithinRange(1001,9999,2);
	}
	
			
	private function UniqueRandomNumbersWithinRange($min, $max, $quantity) 
	{
		$numbers = range($min, $max);
		shuffle($numbers);
		return implode("",array_slice($numbers, 0, $quantity));
	}
	
	
	private function Validate_LogedinUser_And_OrderID($OrderID)
	{
		$Status	= false;
		if(trim($OrderID) != "" && $this->LoginUserId!=="" && keyDecrypt($OrderID) > 0 )
		{
			$this->objDonation->orderID	= keyDecrypt($OrderID);
			$OrderArray	= $this->objDonation->GetDonationOrder(array("PD_RU_ID"));
			if($OrderArray['PD_RU_ID'] == $this->LoginUserId)
			{
				$Status	= true;	
			}
		}
		return $Status;
	}
		

	private function SetStatus($Status,$Code)
	{
		if($Status)
		{
			$this->Pstatus	= 1;
			$messageParams=array("msgCode"=>$Code,
											 "msg"=>"Custom Confirmation message",
											 "msgLog"=>0,									
											 "msgDisplay"=>1,
											 "msgType"=>2);
				EnPException::setConfirmation($messageParams);
		}
		else
		{
			$this->Pstatus	= 0;
			$messageParams=array("errCode"=>$Code,
									 "errMsg"=>"Custom Confirmation message",
									 "errOriginDetails"=>basename(__FILE__),
									 "errSeverity"=>1,
									 "msgDisplay"=>1,
									 "msgType"=>1);
				EnPException::setError($messageParams);
		}
	}
	
	private function strip_quote($text)
	{
	$text=str_replace("'","",$text);
	return $text;
	}
}