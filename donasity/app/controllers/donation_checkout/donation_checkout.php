<?php
	class Donation_checkout_Controller extends Controller
	{
		public $tpl, $Ref_ID, $ItemCode;
		public $ID, $Donor, $CartArray = array();
		
		function __construct()
		{
			$this->load_model('Cart', 'objCart');
			$this->load_model('Common', 'objCommon');
			$this->tpl = new view();
		}
		
		public function index($type = 'donation_checkout', $ID = NULL, $Donor = 'NPOR', $ItemCode = NULL) {
			$this->ID		= ($ID != NULL) ? keyDecrypt($ID) : 0;
			$this->Donor	= $Donor;
			$this->ItemCode = $ItemCode;
			switch(strtolower($type))
			{
				case "addcart":
					$this->AddCart();
					break;
				case "regpayment":
					$this->RegisteredPayment();
					break;	
				case "nonregpayment":
					$this->NonRegisteredPayment();
					break;
				case "receipt":
					$this->Receipt();
					break;				
				default:
					$this->Cart();
					break;		
			}
		}
		
		private function AddCart() {
			$FundRaiserArray = array();
			$NPORArray = array();
			$NPONRArray = array();
			
			if($this->Donor == 'FUNDARISER') {
				$this->load_model('Fundraisers', 'objFund');
				$Detail = $this->objFund->GetFundraiserDetails(array('Camp_ID', 'Camp_Title', 'Camp_Stripe_ConnectedID', 'Camp_NPO_EIN', "Camp_TaxExempt", "Camp_RUID", "Camp_Cat_ID", "Camp_Location_City", "Camp_Location_State", "Camp_Location_Country"), ' AND 1=1 AND Camp_ID=' . $this->ID);
				//dump($Detail);
				$FundRaiserArray = array(
					'ID'		=> $this->ID,
					'Title'		=> $Detail[0]['Camp_Title'],
					'Recurring'	=> 0,
					'Recurring_Mode'=> '',
					'Recurring_Year'=> '',
					'Donation_Amount'=> '',
					'Transaction_Fee'=> '',
					'Payable'	=> 0,
					'Product_Type'=> 'CD',
					'itemcode'	=> 'CD1',
					'City'		=> $Detail[0]['Camp_Location_City'],
					'Category'	=> $Detail[0]['Camp_Cat_ID'],
					'Zipcode'	=> "",
					'State'		=> $Detail[0]['Camp_Location_State'],
					'Connected_UserID'=> $Detail[0]['Camp_RUID'],
					'Connected_Stripe_AccountID' => $Detail[0]['Camp_Stripe_ConnectedID'],
					"TaxExempt"	=> ($Detail[0]['Camp_TaxExempt']==1) ? 1 : 0,
					'Comment'	=> "",
					'KeepAnonymous'=> 0);
			} elseif($this->Donor == 'NPOR') {
				if($this->ItemCode == NULL) 
					$this->ItemCode = 'NPOD1';
					
				$Detail	= $this->GetNpoDetails($this->ID);
				$NPORArray = array(
					'ID'				=>$this->ID,
					'Title'				=>$Detail['NPO_Name'],
					'Recurring'			=>0,
					'Recurring_Mode'	=>'',
					'Recurring_Year'	=>'',
					'Donation_Amount'	=>'',
					'Transaction_Fee'	=>'',
					'Payable'			=>0,
					'Product_Type'		=>'NPOD',
					'itemcode'			=>$this->ItemCode,
					'City'				=>$Detail['NPO_City'],
					'Category'			=>$Detail['category'],
					'Zipcode'			=>$Detail['NPO_Zip'],
					'State'				=>$Detail['NPO_State'],
					'Connected_UserID'	=>$Detail['USERID'],
					'Connected_Stripe_AccountID' => $Detail['Stripe_ClientID'],
					"TaxExempt"			=>($Detail['NPO_DedCode'] == 1) ? 1 : 0);	
			} elseif($this->Donor == 'NPONR') {
				$Detail	= $this->GetNpoDetails($this->ID);	
				$NPONRArray	= array(
					'ID'				=> $this->ID,
					'Title'				=> $Detail['NPO_Name'],
					'Recurring'			=> 0,
					'Recurring_Mode'	=> '',
					'Recurring_Year'	=> '',
					'Donation_Amount'	=> '',
					'Payable'			=> 0,
					'Transaction_Fee'	=> '',
					'Product_Type'		=> 'NPOD',
					'itemcode'			=> 'NPOD2',
					'City'				=> $Detail['NPO_City'],
					'Category'			=> $Detail['category'],
					'Zipcode'			=> $Detail['NPO_Zip'],
					'State'				=> $Detail['NPO_State'],
					'Connected_UserID'	=> $Detail['USERID'],
					'Connected_Stripe_AccountID' => $Detail['Stripe_ClientID'],
					"TaxExempt"			=> ($Detail['NPO_DedCode'] == 1) ? 1 : 0);	
			}
			
			$Cart = getCookie('DonatiobasketArray');
			if($Cart == "")
			{
				if(count($FundRaiserArray) > 0){
					$this->CartArray['FUNDARISER'][] = $FundRaiserArray;
				}
				if(count($NPORArray) > 0){
					$this->CartArray['NPOR'][]	= $NPORArray;
				}
				if(count($NPONRArray) > 0){
					$this->CartArray['NPONR'][]	= $NPONRArray;
				}
				
				$this->CartArray['calculation']	= array(
					'Total'				=>0,
					'TransactionFeePaidByUser'	=>1,
					'TransactionRate'	=>TRANSACTION_FEE,
					'TransactionFee'	=>0,
					'TotalDonation'		=>0,
					'TotalPay'			=>0);
					
				set_Cookie("DonatiobasketArray", $this->CartArray);
			} else {
				$this->CartArray = getCookie('DonatiobasketArray');
				if(isset($FundRaiserArray))
				{
					if(count($FundRaiserArray) > 0){
						if(isset($this->CartArray['FUNDARISER'])) {
							if(!$this->IsAlreadyExist($this->ID, $this->CartArray['FUNDARISER']))
								$this->CartArray['FUNDARISER'][] = $FundRaiserArray;
						} else 
							$this->CartArray['FUNDARISER'][] = $FundRaiserArray;
					}
				}
				
				if(isset($NPORArray)) {
					if(count($NPORArray) > 0){
						if(isset($this->CartArray['NPOR'])) {
							if(!$this->IsAlreadyExist($this->ID, $this->CartArray['NPOR'])){
								$this->CartArray['NPOR'][] = $NPORArray;
							}
						} else 
							$this->CartArray['NPOR'][]	= $NPORArray;
					}
				}
				
				if(isset($NPONRArray)) {
					if(count($NPONRArray) > 0) {
						if(isset($this->CartArray['NPONR'])) {
							if(!$this->IsAlreadyExist($this->ID, $this->CartArray['NPONR']))
								$this->CartArray['NPONR'][]	= $NPONRArray;
						} else
							$this->CartArray['NPONR'][]	= $NPONRArray;
					}
				}
				
				$this->UpdateCalculation();
				
				set_Cookie("DonatiobasketArray", $this->CartArray);
			}			
			redirect(URL . "donation_checkout");	
		}
		
		private function getComment() {
			if(isset($this->CartArray['FUNDARISER'])) {
				foreach($this->CartArray['FUNDARISER'] as &$val) {
					
				}
			}
		}
		
		private function UpdateCalculation() {
			$total = 0;
			
			if(isset($this->CartArray['FUNDARISER'])) {
				foreach($this->CartArray['FUNDARISER'] as &$val) {
					if($val['Donation_Amount'] > 0) {
						$val['Transaction_Fee']	= $val['Donation_Amount'] * (TRANSACTION_FEE / 100);
						$val['Payable'] = $val['Donation_Amount'];
						if($this->CartArray['calculation']['TransactionFeePaidByUser'] == 1)
							$val['Payable'] = $val['Donation_Amount'] + $val['Transaction_Fee'];
						
						$total += $val['Donation_Amount'];
					}
				}
			}
			if(isset($this->CartArray['NPOR'])) {
				foreach($this->CartArray['NPOR'] as &$val) {
					if($val['Donation_Amount'] > 0) {
						$val['Transaction_Fee']	= $val['Donation_Amount'] * (TRANSACTION_FEE / 100);
						$total += $val['Donation_Amount'];
						$val['Payable'] = $val['Donation_Amount'];
						if($this->CartArray['calculation']['TransactionFeePaidByUser'] == 1)
							$val['Payable'] = $val['Donation_Amount'] + $val['Transaction_Fee'];
					}
				}
			}
			
			if(isset($this->CartArray['NPONR'])) {
				foreach($this->CartArray['NPONR'] as &$val) {
					if($val['Donation_Amount'] > 0) {
						$val['Transaction_Fee']	= $val['Donation_Amount'] * (TRANSACTION_FEE / 100);
						$total += $val['Donation_Amount'];
						$val['Payable'] = $val['Donation_Amount'];
						if($this->CartArray['calculation']['TransactionFeePaidByUser'] == 1)
							$val['Payable'] = $val['Donation_Amount'] + $val['Transaction_Fee'];
					}
				}
			}
			
			$this->CartArray['calculation']['Total'] = $total;
			$TransFee = $total * ($this->CartArray['calculation']['TransactionRate'] / 100);
			$this->CartArray['calculation']['TransactionFee'] = $TransFee;
			if($this->CartArray['calculation']['TransactionFeePaidByUser']) {
				$GrandTotal	= $total + $TransFee;
				$this->CartArray['calculation']['TotalPay'] = $GrandTotal;
				$this->CartArray['calculation']['TotalDonation'] = $total;
			} else {
				$GrandTotal	= $total - $TransFee;
				$this->CartArray['calculation']['TotalPay'] = $total;	
				$this->CartArray['calculation']['TotalDonation'] = $GrandTotal;
			}
		}
		
		private function Cart() {
			$msgValues = EnPException::getConfirmation();
			$cartCount = 0;
			$continue_to_donate_url = getSession("continue_to_donate_url");
			if(trim($continue_to_donate_url) == "") 
				$continue_to_donate_url = "/campaign/index/campaigncategorylist";
			
			$this->CartArray = getCookie('DonatiobasketArray');

			foreach(array("NPOR", "NPONR", "FUNDARISER") as $key)
				if(isset($this->CartArray[$key])) 
					$cartCount = $cartCount + count($this->CartArray[$key]);

			$this->tpl->assign('CartList', $this->CartArray);
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('donationbasket'));
			$this->tpl->assign("continue_to_donate_url", $continue_to_donate_url);
			$this->tpl->assign("cartCount", $cartCount);
			$this->tpl->assign("msgValues", $msgValues);
			$this->tpl->draw('donation_checkout/cart');	
			dump(getCookie('DonatiobasketArray'));
			
		}
		
		private function IsAlreadyExist($ID, $Array) {
			$Status	= 0;
			if(isset($Array)) {
				foreach($Array as $value) {
					if(array_search($ID, $value)) {
						$Status	= 1;
						break;	
					} else
						$Status = 0;
				}
			}
			return $Status;
		}
		
		private function GetNpoDetails($NPOID) {
			$Param = " AND N.NPO_ID=" . $NPOID;
			$DataArray = array('N.NPO_Name','N.NPO_City','NCR.NPO_CatName as category','N.NPO_Zip','N.NPO_State','NU.USERID','NU.Stripe_ClientID','N.NPO_DedCode','NU.Status as Stripe_status');
			
			$NPODetail = $this->objCart->GetNpoDetails($DataArray, $Param);	
			$NPODetail['State_Name'] = $this->objCommon->GetStateName($NPODetail['NPO_State']);
			$CategoryName = explode('||', $NPODetail['category']);
			$NPODetail['category'] = (LANG_ID == 'en') ? $CategoryName[0] : $CategoryName[1];	
			return $NPODetail;
		}
		
		public function RemoveItem($IndexKey) {
			$Param = explode("|", $IndexKey);
			$this->CartArray = getCookie('DonatiobasketArray');
			unset($this->CartArray[$Param[0]][$Param[1]]);
			if(count($this->CartArray[$Param[0]]) == 0)
				unset($this->CartArray[$Param[0]]);
				
			$this->UpdateCalculation();
			
			if(isset($this->CartArray['NPOR']) && isset($this->CartArray['NPONR']) && isset($this->CartArray['FUNDARISER'])) {
				if(count($this->CartArray['NPOR']) == 0 && count($this->CartArray['NPONR']) == 0 && count($this->CartArray['FUNDARISER']) == 0)
					unset($this->CartArray);
			}
			
			set_Cookie('DonatiobasketArray', $this->CartArray);
			redirect(URL . 'donation_checkout');
		}
		
		public function UpdateAmount() {
			$IndexKey = request('post', 'indexkey', 0);
			$Amount	= request('post', 'amount', 1);
			$Param = explode("|", $IndexKey);
			$this->CartArray = getCookie('DonatiobasketArray');
			$this->CartArray[$Param[0]][$Param[1]]['Donation_Amount'] = $Amount;
			$this->UpdateCalculation();
			set_Cookie('DonatiobasketArray', $this->CartArray);
			$ReturArr = $this->GetUpdatedAmt();
			echo json_encode($ReturArr);
			exit;
		}
				
		public function UpdateAnonymousComment() {
			$IndexKey			= request('post', 'indexkey', 0);
			$KeepAnonymous		= request('post', 'KeepAnonymous', 0);
			$Comment			= request('post', 'comment', 0);
			$Param				= explode("|", $IndexKey);
			$this->CartArray	= getCookie('DonatiobasketArray');									
			$this->CartArray[$Param[0]][$Param[1]]['KeepAnonymous']	= $KeepAnonymous;
			$this->CartArray[$Param[0]][$Param[1]]['Comment']		= $Comment;

			set_Cookie('DonatiobasketArray', $this->CartArray);
			$this->CartArray	= getCookie('DonatiobasketArray');		
			//dump($this->CartArray);
			echo $Status = 1;					
			exit;
		}
		
		private function GetUpdatedAmt() {
			$NPORSubTotal = 0;
			$NPONRSubTotal = 0;
			$FUNDARISERSubTotal	= 0;
			$NPORSubTotal_Payable = 0;
			$NPONRSubTotal_Payable = 0;
			$FUNDARISERSubTotal_Payable	= 0;
			
			if(isset($this->CartArray['FUNDARISER']))
			foreach($this->CartArray['FUNDARISER'] as $val)
			{
				$FUNDARISERSubTotal+=$val['Donation_Amount'];	
				$FUNDARISERSubTotal_Payable+=$val['Payable'];	
			}
			if(isset($this->CartArray['NPOR']))
			foreach($this->CartArray['NPOR'] as $val)
			{
				$NPORSubTotal+=$val['Donation_Amount'];	
				$NPORSubTotal_Payable+=$val['Payable'];	

			}
			if(isset($this->CartArray['NPONR']))
			foreach($this->CartArray['NPONR'] as $val)
			{
				$NPONRSubTotal+=$val['Donation_Amount'];
				$NPONRSubTotal_Payable+=$val['Payable'];	
			}
			
			return array('NPORSubTotal'=>$NPORSubTotal,'NPONRSubTotal'=>$NPONRSubTotal,'FUNDARISERSubTotal'=>$FUNDARISERSubTotal,
			'NPORSubTotal_Payable'=>$NPORSubTotal_Payable,'NPONRSubTotal_Payable'=>$NPONRSubTotal_Payable,'FUNDARISERSubTotal_Payable'=>$FUNDARISERSubTotal_Payable,
			'Total'=>$this->CartArray['calculation']['Total'],'TransactionFee'=>$this->CartArray['calculation']['TransactionFee'],'TotalDonation'=>$this->CartArray['calculation']['TotalDonation'],
						  'TotalPay'=>$this->CartArray['calculation']['TotalPay'],'Status'=>1);
		
		}
		
		public function UpdateRecurringMode()
		{
			$IndexKey	= request('post','indexkey',0);
			$Mode		= request('post','mode',0);
			$Param	= explode("|",$IndexKey);
			
			$this->CartArray	= getCookie('DonatiobasketArray');
			$this->CartArray[$Param[0]][$Param[1]]['Recurring_Mode']	= $Mode;
			$this->CartArray[$Param[0]][$Param[1]]['Recurring']	= 1;
			$this->UpdateCalculation();
			set_Cookie('DonatiobasketArray',$this->CartArray);
			$ReturArr	= array('Status'=>1);
			echo json_encode($ReturArr);
			exit;
		}
	
		
		public function UpdateYear()
		{
			$IndexKey	= request('post','indexkey',0);
			$Year		= request('post','year',1);
			$Param	= explode("|",$IndexKey);
			$this->CartArray	= getCookie('DonatiobasketArray');
			$this->CartArray[$Param[0]][$Param[1]]['Recurring_Year']	= $Year;
			$this->UpdateCalculation();
			set_Cookie('DonatiobasketArray',$this->CartArray);
			$ReturArr	= array('Status'=>1);
			echo json_encode($ReturArr);
			exit;
		}
		
		public function UpdateIncludeTransactionFee()
		{
			$IncludeStatus	= request('post','includeStat',1);
			$this->CartArray	= getCookie('DonatiobasketArray');
			$this->CartArray['calculation']['TransactionFeePaidByUser']	= $IncludeStatus;
			$this->UpdateCalculation();
			set_Cookie('DonatiobasketArray',$this->CartArray);
			$ReturArr	= $this->GetUpdatedAmt();
			echo json_encode($ReturArr);
			exit;
		}
	}
?>