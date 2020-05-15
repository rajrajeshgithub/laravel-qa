<?php
	class Purchase_Controller extends Controller
	{
		private $LevelID,$ProductID,$ProductCode,$LoginUserId,$PurchaseOrderID,$PurchaseOrderDetailID;
		private $ProductDetailArray,$LoginUserDetail,$UserDetailArray;
		function __construct()
		{
			$this->load_model("Purchase","objPurchase");
			$this->load_model("Common","objCom");	
			$this->load_model('UserType1','objutype1');
			$this->load_model('UserType2','objutype2');
			$this->LoginUserDetail	= getSession('Users');
			$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
		}
		
		public function fundraiser($ProductCode,$PurchaseOrderID="")	
		{
			
				
				$this->ProductCode=keyDecrypt($ProductCode);
				$this->PurchaseOrderID=keyDecrypt($PurchaseOrderID);
				
																	
				$this->Validate_LogedinUser_And_ProductID();
				
				$this->LevelID=$this->objPurchase->GetFundraiserLevel($this->ProductCode);
				$this->tpl 		= new view;
				$this->tpl->assign("msgValues",EnPException::getConfirmation());
				$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCom->GetPageCMSDetails('fundraiserpayment'));
				$this->tpl->assign('productDetails',$this->ProductDetailArray);
				$this->tpl->assign('PurchaseOrderID',$this->PurchaseOrderID);
				$this->tpl->draw('purchase/fundraiser');
				
			
		}
		
		public function Payment($ProductCode,$PurchaseOrderID="")
		{
				
			session_cache_limiter('private_no_expire');
			$this->ProductCode=keyDecrypt($ProductCode);
			$this->PurchaseOrderID=keyDecrypt($PurchaseOrderID);
			
			
			$this->Validate_LogedinUser_And_ProductID();
			$this->LevelID=$this->objPurchase->GetFundraiserLevel($this->ProductCode);
						
			$CardName	= request('post','cardNumber',0);
			$CardNumber	= request('post','cardNumber',0);
			$CVV		= request('post','sqCode',0);
			$ExpMonth	= request('post','expMonth',0);
			$ExpYear	= request('post','expYear',0);
			
			//echo($this->PurchaseOrderID);
			
			if (!is_numeric($this->PurchaseOrderID))
			$this->PurchaseOrderID=$this->add_purchase_order($this->LoginUserId);
			
					
			$PurchaseOrderArray=$this->objPurchase->GetPurchaseOrder(array("PD_ID","PD_BillingFirstName","PD_BillingLastName","PD_ReferenceNumber","PD_BillingEmailAddress"));
			$this->objPurchase->PurchaseOrderID=$this->PurchaseOrderID;
			
			$PurchaseOrderDetailArray=$this->objPurchase->GetPurchaseOrderDetails(array("PDD_ID","PDD_PD_ID","PDD_RUID","PDD_PIItemName","PDD_ItemCode","PDD_PIItemType","PDD_Cost","PDD_SubTotal","PDD_Status"
			),"");
			
			if($PurchaseOrderDetailArray["PDD_Cost"]<=0 or $PurchaseOrderDetailArray["PDD_Status"]==11)
			{
				
				$this->objPurchase->SetPurchaseOrderDetails(array("PDD_Status"=>11,"PDD_Status_Notes"=>"No Payment"),$PurchaseOrderDetailArray["PDD_ID"]);
				$this->objPurchase->SetPurchaseOrder(array("PD_Status"=>11,"PD_LastUpdatedDate"=>getDateTime()),$PurchaseOrderDetailArray["PDD_PD_ID"]);
				
				$this->load_model('Fundraisers','objFund');
				$this->objFund->UserId=$this->LoginUserId;
				$FundraiserID=$this->objFund->FundraiserInsert($this->objPurchase->GetFundraiserLevel($this->ProductCode));
				
				redirect(URL."/setup_fundraiser/index/".keyEncrypt($FundraiserID));
				exit();
			}
			
			
			$PaymentTransactionID=$this->objPurchase->SetPaymentTransaction(array("PT_PDID"=>$PurchaseOrderArray["PD_ID"],"PT_PDDID"=>$PurchaseOrderDetailArray["PDD_ID"],"PT_RUID"=>$PurchaseOrderDetailArray["PDD_RUID"],"PT_PaymentType"=>
			"CC","PT_PaymentAmount"=>$PurchaseOrderDetailArray["PDD_SubTotal"],"PT_PaymentGatewayName"=>"STRIPE","PT_PaymentStatus"=>0,"PT_PaymentStatus_Notes"=>"New","PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),
			"PT_LastUpdatedDate"=>getDateTime()));
			
				$this->load_model('Stripe','ObjStripe');
				$this->ObjStripe->amount=$PurchaseOrderDetailArray["PDD_SubTotal"];
				$this->ObjStripe->cc_number=	$CardNumber;//"4242424242424242";
				$this->ObjStripe->cc_cvv=	$CVV;
				$this->ObjStripe->cc_exp_month=	$ExpMonth;
				$this->ObjStripe->cc_exp_year=	$ExpYear;
				$this->ObjStripe->cc_name=$CardName;
				$this->ObjStripe->invoice=$PurchaseOrderArray["PD_ReferenceNumber"];
				$this->ObjStripe->receipt_email=$PurchaseOrderArray["PD_BillingEmailAddress"];
				$this->ObjStripe->txnDescription=$PurchaseOrderDetailArray["PDD_PIItemName"]." [".$PurchaseOrderDetailArray["PDD_PIItemType"]."]";
				
				if($this->ObjStripe->chargeCreditForPurchase())
				{
					$this->objPurchase->SetPaymentTransaction(array("PT_PaidAmount"=>$this->ObjStripe->stripe_response_filtered["PaidAmount"],"PT_PaymentGatewayRequest"=>keyEncrypt(serialize($this->ObjStripe->stripe_request)),
					"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjStripe->stripe_response_complete)),"PT_PaymentGatewayTransactionID"=>$this->ObjStripe->stripe_response_filtered["TransactionID"],"PT_PaymentStatus"=>1,
					"PT_PaymentStatus_Notes"=>"Paid","PT_Comment"=>$this->strip_quote($this->ObjStripe->stripe_response_filtered["PayNote"]),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
								
					$this->objPurchase->SetPurchaseOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>11,"PDD_Status_Notes"=>"Paid Sucessfully"),$PurchaseOrderDetailArray["PDD_ID"]);
					$this->objPurchase->SetPurchaseOrder(array("PD_Status"=>11,"PD_LastUpdatedDate"=>getDateTime()),$PurchaseOrderDetailArray["PDD_PD_ID"]);
					$this->SetStatus(1,"C16000");
					
					$this->load_model('Fundraisers','objFund');
					$this->objFund->UserId=$this->LoginUserId;
					$FundraiserID=$this->objFund->FundraiserInsert($this->objPurchase->GetFundraiserLevel($this->ProductCode));
					redirect(URL."/setup_fundraiser/index/".keyEncrypt($FundraiserID));
					
					
				}
				else
				{
					$this->objPurchase->SetPaymentTransaction(array("PT_PaidAmount"=>0,"PT_PaymentStatus"=>2,"PT_PaymentStatus_Notes"=>$this->strip_quote($this->ObjStripe->stripe_response_err["message"]),"PT_PaymentGatewayRequest"=>					
					keyEncrypt(serialize($this->ObjStripe->stripe_request)),"PT_PaymentGatewayResponse"=>keyEncrypt(serialize($this->ObjStripe->stripe_response_complete)),"PT_LastUpdatedDate"=>getDateTime()),$PaymentTransactionID);
					
					$this->objPurchase->SetPurchaseOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>2,"PDD_Status_Notes"=>$this->strip_quote($this->ObjStripe->stripe_response_err["message"])),
					$PurchaseOrderDetailArray["PDD_ID"]);
					$this->SetStatus(0,"E16000");
					redirect(URL."/purchase/fundraiser/".keyEncrypt($this->ProductCode)."/".keyEncrypt($this->PurchaseOrderID));
				}
			
			
			
			
		}
		
		
		
		private function add_purchase_order($UserID)
		{

				$DataArray	= array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_Address1','RU.RU_Address2','RU.RU_Phone','RU.RU_City','RU.RU_State',
								'RU.RU_ZipCode','RU.RU_Country','RU.RU_EmailID');
				$this->UserDetailArray=$this->objutype1->GetUserDetails($DataArray," AND RU.RU_ID=".$UserID);
				
				$OrderArray=array (
					"PD_ItemType"=>"P"
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
					,"PD_SubTotal"=>$this->ProductDetailArray['PI_ItemCost']
					,"PD_TransactionFee"=>0
					,"PD_TransactionFeePaidByUser"=>0
					,"PD_TotalAmount"=>$this->ProductDetailArray['PI_ItemCost']
					,"PD_Status"=>"0"
					,"PD_IP"=>GetUserLocale()
					,"PD_CreatedDate"=>getDateTime()
					,"PD_LastUpdatedDate"=>getDateTime()
					,"PD_CreatedBy"=>$this->UserDetailArray['RU_FistName']
					,"PD_Source"=>"donasity.com"
					,"PD_Deleted"=>0
				
				);			
			
				
				$OrderDetailArray=array("PDD_DateTime"=>getDateTime()
					,"PDD_RUID"=>$this->UserDetailArray['RU_ID']
					,"PDD_ItemCode"=>$this->ProductDetailArray['PI_ItemCode']
					,"PDD_PIItemType"=>$this->ProductDetailArray['PI_ItemType']
					,"PDD_PIItemName"=>$this->ProductDetailArray['PI_ItemName_EN']
					,"PDD_PIItemDescription"=>$this->ProductDetailArray['PI_ItemDescription_EN']
					,"PDD_Cost"=>$this->ProductDetailArray['PI_ItemCost']
					,"PDD_SubTotal"=>$this->ProductDetailArray['PI_ItemCost']
					,"PDD_PaymentType"=>"OTP"
					,"PDD_PaymentTransactionID"=>""
					,"PDD_Comments"=>""
					,"PDD_Deleted"=>"0"
					,"PDD_Status"=>0
					,"PDD_Status_Notes"=>"New");
				
				$this->PurchaseOrderID=$this->objPurchase->AddPurchaseOrder($OrderArray);
				$this->objPurchase->PurchaseOrderID=$this->PurchaseOrderID;
				$this->PurchaseOrderDetailID=$this->objPurchase->AddPurchaseOrderDetails($OrderDetailArray);
					
			    return $this->PurchaseOrderID;
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
		
		
	private function Validate_LogedinUser_And_ProductID()
	{	
		$Status	= false;
		
	
		
		if($this->objutype1->checkLogin($this->LoginUserDetail)>0)
				$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);	
		elseif($this->objutype2->checkLogin($this->LoginUserDetail)>0)
				$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType2']['user_id']);	
		else
		{
			redirect(URL."ut1/login/");
		}
		
		
		
		
		if(trim($this->ProductCode) != "" && $this->LoginUserId!=="" )
		{
			$this->ProductDetailArray = $this->objPurchase->GetProductDetail(array('PI_ID','PI_ItemCode','PI_ItemType','PI_ItemName_EN','PI_ItemName_ES','PI_ItemDescription_EN','PI_ItemDescription_ES','PI_ItemCost')," and 
			PI_ItemCode='".$this->ProductCode."'");
			$Status	= true;	

		}
		return $Status;
	}
	
	private function strip_quote($text)
	{
		$text=str_replace("'","",$text);
		return $text;
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
	
}
?>