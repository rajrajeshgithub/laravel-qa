<?php
// USAePay PHP Library.
//	v1.7.0
//
// 	Copyright (c) 2001-2015 USAePay
//	For assistance please contact devsupport@usaepay.com
//



/**
 * USAePay Transaction Class
 *
 */
class Usaepay_Model extends Model {
	// Required for all transactions
	public $key;			// Source key
	public $pin;			// Source pin (optional)
	public $amount;		// the entire amount that will be charged to the customers card
							// (including tax, shipping, etc)
	public $invoice;		// invoice number.  must be unique.  limited to 10 digits.  use orderid if you need longer.

	// Required for Level 2 - Commercial Card support
	public $ponum;			// Purchase Order Number
	public $tax;			// Tax
	public $nontaxable;	// Order is non taxable
	public $digitalgoods; //digital goods indicator (ecommerce)

	// Level 3 support
	public $duty;
	public $shipfromzip;

	// Amount details (optional)
	public $tip; 			// Tip
	public $shipping;		// Shipping charge
	public $discount; 	// Discount amount (ie gift certificate or coupon code)
	public $subtotal; 	// if subtotal is set, then
							// subtotal + tip + shipping - discount + tax must equal amount
							// or the transaction will be declined.  If subtotal is left blank
							// then it will be ignored
	public $currency;		// Currency of $amount
	public $allowpartialauth; //set to true if a partial authorization (less than the full $amount)  will be accepted

	// Required Fields for Card Not Present transacitons (Ecommerce)
	public $card;			// card number, no dashes, no spaces
	public $exp;			// expiration date 4 digits no /
	public $cardholder; 	// name of card holder
	public $street;		// street address
	public $zip;			// zip code

	// Fields for Card Present (POS)
	public $magstripe;  	// mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
	public $cardpresent;   // Must be set to true if processing a card present transaction  (Default is false)
	public $termtype;  	// The type of terminal being used:  Optons are  POS - cash register, StandAlone - self service terminal,  Unattended - ie gas pump, Unkown  (Default:  Unknown)
	public $magsupport;  	// Support for mag stripe reader:   yes, no, contactless, unknown  (default is unknown unless magstripe has been sent)
	public $contactless;  	// Magstripe was read with contactless reader:  yes, no  (default is no)
	public $dukpt;			// DUK/PT for PIN Debit
	public $signature;     // Signature Capture data

	// fields required for check transactions
	public $account;		// bank account number
	public $routing;		// bank routing number
	public $ssn;			// social security number
	public $dlnum;			// drivers license number (required if not using ssn)
	public $dlstate;		// drivers license issuing state
	public $checknum;		// Check Number
	public $accounttype;       // Checking or Savings
	public $checkformat;	// Override default check record format
	public $checkimage_front;    // Check front
	public $checkimage_back;		// Check back
	public $auxonus;
	public $epccode;
	public $micr;


	// Fields required for Secure Vault Payments (Direct Pay)
	public $svpbank;		// ID of cardholders bank
	public $svpreturnurl;	// URL that the bank should return the user to when tran is completed
	public $svpcancelurl; 	// URL that the bank should return the user if they cancel



	// Option parameters
	public $origauthcode;	// required if running postauth transaction.
	public $command;		// type of command to run; Possible values are:
						// sale, credit, void, preauth, postauth, check and checkcredit.
						// Default is sale.
	public $orderid;		// Unique order identifier.  This field can be used to reference
						// the order for which this transaction corresponds to. This field
						// can contain up to 64 characters and should be used instead of
						// UMinvoice when orderids longer that 10 digits are needed.
	public $custid;   // Alpha-numeric id that uniquely identifies the customer.
	public $description;	// description of charge
	public $cvv2;			// cvv2 code
	public $custemail;		// customers email address
	public $custreceipt;	// send customer a receipt
	public $custreceiptname;	// name of custom receipt template
	public $ignoreduplicate; // prevent the system from detecting and folding duplicates
	public $ip;			// ip address of remote host
	public $geolocation;	// geo location information for transaciton.  Format lat,long
	public $testmode;		// test transaction but don't process it
	public $usesandbox;    // use sandbox server instead of production
	public $timeout;       // transaction timeout.  defaults to 45 seconds
	public $gatewayurl;   	// url for the gateway
	public $proxyurl;		// proxy server to use (if required by network)
	public $ignoresslcerterrors;  // Bypasses ssl certificate errors.  It is highly recommended that you do not use this option.  Fix your openssl installation instead!
	public $cabundle;      // manually specify location of root ca bundle (useful of root ca is not in default location)
	public $transport;     // manually select transport to use (curl or stream), by default the library will auto select based on what is available
	public $clerk;					// Indicates the clerk/person processing transaction, for reporting purposes. (optional)
	public $terminal;				// Indiactes the terminal used to process transaction, for reporting purposes. (optional)
	public $restaurant_table;	// Indicates the restaurant table, for reporting purposes. (optional)
	public $ticketedevent;  // ID of Event when doing a ticket sale
	public $ifauthexpired;  // controls what happens when capturing an authorization that has expire.  options are 'ignore','error','reauth'.
	public $authexpiredays;  //set the number of days an authorization is valid for.  defaults to merchant account setting.
	public $inventorylocation; // set the warehouse to pull inventory from.  defaults to source key setting.

	// Card Authorization - Verified By Visa and Mastercard SecureCode
	public $cardauth;    	// enable card authentication
	public $pares; 		//

	// Third Party Card Authorization
	public $xid;
	public $cavv;
	public $eci;

	// Customer Database
	public $addcustomer;		//  Save transaction as a recurring transaction:  yes/no
	public $recurring;		// (obsolete,  see the addcustomer)

	public $schedule;		//  How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.  Default is monthly, set to disabled if you don't want recurring billing
	public $numleft; 		//  The number of times to run. Either a number or * for unlimited.  Default is unlimited.
	public $start;			//  When to start the schedule.  Default is tomorrow.  Must be in YYYYMMDD  format.
	public $end;			//  When to stop running transactions. Default is to run forever.  If both end and numleft are specified, transaction will stop when the earliest condition is met.
	public $billamount;	//  Optional recurring billing amount.  If not specified, the amount field will be used for future recurring billing payments
	public $billtax;
	public $billsourcekey;

	// tokenization
	public $savecard;		// create a card token if the sale is approved, returns a cardref variable

	// Manually mark as recurring
	public $isrecurring;

	// Billing Fields
	public $billfname;
	public $billlname;
	public $billcompany;
	public $billstreet;
	public $billstreet2;
	public $billcity;
	public $billstate;
	public $billzip;
	public $billcountry;
	public $billphone;
	public $email;
	public $fax;
	public $website;

	// Shipping Fields
	public $delivery;		// type of delivery method ('ship','pickup','download')
	public $shipfname;
	public $shiplname;
	public $shipcompany;
	public $shipstreet;
	public $shipstreet2;
	public $shipcity;
	public $shipstate;
	public $shipzip;
	public $shipcountry;
	public $shipphone;

	// Custom Fields
	public $custom1;
	public $custom2;
	public $custom3;
	public $custom4;
	public $custom5;
	public $custom6;
	public $custom7;
	public $custom8;
	public $custom9;
	public $custom10;
	public $custom11;
	public $custom12;
	public $custom13;
	public $custom14;
	public $custom15;
	public $custom16;
	public $custom17;
	public $custom18;
	public $custom19;
	public $custom20;


	// Line items  (see addLine)
	public $lineitems;


	public $comments; // Additional transaction details or comments (free form text field supports up to 65,000 chars)

	public $software; // Allows developers to identify their application to the gateway (for troubleshooting purposes)

	// Fraud Profiling
	public $session;

	// response fields
	public $rawresult;		// raw result from gateway
	public $result;		// full result:  Approved, Declined, Error
	public $resultcode; 	// abreviated result code: A D E
	public $authcode;		// authorization code
	public $refnum;		// reference number
	public $batch;		// batch number
	public $avs_result;		// avs result
	public $avs_result_code;		// avs result
	public $avs;  					// obsolete avs result
	public $cvv2_result;		// cvv2 result
	public $cvv2_result_code;		// cvv2 result
	public $vpas_result_code;      // vpas result
	public $isduplicate;      // system identified transaction as a duplicate
	public $convertedamount;  // transaction amount after server has converted it to merchants currency
	public $convertedamountcurrency;  // merchants currency
	public $conversionrate;  // the conversion rate that was used
	public $custnum;  //  gateway assigned customer ref number for recurring billing
	public $authamount; // amount that was authorized
	public $balance;  //remaining balance
	public $cardlevelresult;
	public $procrefnum;
	public $cardref; 		// card number token

	// Cardinal Response Fields
	public $acsurl;	// card auth url
	public $pareq;		// card auth request
	public $cctransid; // cardinal transid

	// Fraud Profiler
	public $profiler_score;
	public $profiler_response;
	public $profiler_reason;

	// Errors Response Feilds
	public $error; 		// error message if result is an error
	public $errorcode; 	// numerical error code
	public $blank;			// blank response
	public $transporterror; 	// transport error

	public $curlerror;
	
	public $usaepay_response_complete , $usaepay_request,$usaepay_response_err,$usaepay_response_filtered;

	function __construct()
	{
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->timeout=45;
		$this->cardpresent=false;
		$this->lineitems = array();
		
		$this->software="USAePay PHP API v" . USAEPAY_VERSION;

		$this->key=USAEPAY_KEY; 				// Your Source Key
		$this->pin=USAEPAY_PIN;							// Source Key Pin
		$this->usesandbox=USAEPAY_SANDBOX;						// Sandbox true/false
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];   							// This allows fraud blocking on the customers ip address 
		$this->testmode=USAEPAY_TESTMODE;    						// Change this to 0 for the transaction to process
		$this->command="check:sale"; 
		$this->ignoresslcerterrors=true;
		//$this->cabundle="c:\windows\curl-ca-bundle.crt";

	}
	
	/**
	 * Add a line item to the transaction
	 *
	 * @param string $sku
	 * @param string $name
	 * @param string $description
	 * @param double $cost
	 * @param string $taxable
	 * @param int $qty
	 * @param string $refnum
	 */
	function addLine($sku, $name='', $description='', $cost='', $qty='', $taxable='', $refnum=0)
	{
		if(is_array($sku))
		{
			$vars = $sku;
			$this->lineitems[] = array(
						'sku' => @$vars['sku'],
						'name' => @$vars['name'],
						'description' => @$vars['description'],
						'cost' => @$vars['cost'],
						'taxable' => @$vars['taxable'],
						'qty' => @$vars['qty'],
						'refnum' => @$vars['refnum'],
						'um' => @$vars['um'],
						'taxrate' => @$vars['taxrate'],
						'taxamount' => @$vars['taxamount'],
						'taxclass' => @$vars['taxclass'],
						'commoditycode' => @$vars['commoditycode'],
						'discountrate' => @$vars['discountrate'],
						'discountamount' => @$vars['discountamount'],
					);

		} else {
			$this->lineitems[] = array(
					'sku' => $sku,
					'name' => $name,
					'description' => $description,
					'cost' => $cost,
					'taxable' => $taxable,
					'qty' => $qty,
					'refnum' => $refnum
				);
		}
	}

	function getLineTotal()
	{
		$total = 0;
		foreach($this->lineitems as $line)
		{
			$total+=(intval($line['cost']*100) * intval($line['qty']));
		}
		return number_format($total/100, 2, '.','');
	}

	/**
	 * Remove all line items
	 *
	 */
	function clearLines()
	{
		$this->lineitems=array();
	}

	/**
	 * Clear all transaction parameters except for the key and pin. Should be run between transactions if not
	 * reinsantiating the Usaepay class
	 *
	 */
	function clearData()
	{
		$map =$this->getFieldMap();
		foreach($map as $apifield => $classfield)
		{
			if($classfield=='software' || $classfield=='key' || $classfield=='pin') continue;
			unset($this->$classfield);
		}

		// Set default values.
		$this->command="sale";
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->cardpresent=false;
		$this->lineitems = array();
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];

	}

	/**
	 * Verify that all required data has been set
	 *
	 * @return string
	 */
	function CheckData()
	{
		if(!$this->key) return "Source Key is required";
		if(in_array(strtolower($this->command), array("cc:capture", "cc:refund", "refund", "check:refund","capture", "creditvoid", 'quicksale', 'quickcredit','refund:adjust','void:release', 'check:void','void')))
		{
			if(!$this->refnum) return "Reference Number is required";
		}else if(in_array(strtolower($this->command), array("svp")))
		{
			if(!$this->svpbank) return "Bank ID is required";
			if(!$this->svpreturnurl) return "Return URL is required";
			if(!$this->svpcancelurl) return "Cancel URL is required";
		}  else {
			if(in_array(strtolower($this->command), array("check:sale","check:credit", "check", "checkcredit","reverseach") )) {
					if(!$this->account) return "Account Number is required";
					if(!$this->routing) return "Routing Number is required";
			} else if (in_array(strtolower($this->command) , array('cash','cash:refund','cash:sale','external:check:sale','external:cc:sale','external:gift:sale'))) {
				// nothing needs to be validated for cash
			} else {
				if(!$this->magstripe &&!preg_match('~^enc://~',$this->card)) {
					if(!$this->card) return "Credit Card Number is required ({$this->command})";
					if(!$this->exp) return "Expiration Date is required";
				}
			}
			if ($this->command !== "cc:save"){
			$this->amount=preg_replace("/[^0-9\.]/","",$this->amount);
			if(!$this->amount) return "Amount is required";
			if(!$this->invoice && !$this->orderid) return "Invoice number or Order ID is required";
			if(!$this->magstripe) {
				//if(!$this->cardholder) return "Cardholder Name is required";
				//if(!$this->street) return "Street Address is required";
				//if(!$this->zip) return "Zipcode is required";
				}
			}
		}

		return 0;
	}

	/**
	 * Send transaction to the USAePay Gateway and parse response
	 *
	 * @return boolean
	 */
	public function Process()
	{
		// check that we have the needed data
		$tmp=$this->CheckData();
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}

		// populate the data
		$map = $this->getFieldMap();
		$data = array();
		foreach($map as $apifield =>$classfield)
		{
			switch($classfield)
			{
				case 'isrecurring':
					if($this->isrecurring) $data['UMisRecurring'] = 'Y';
					break;
				case 'nontaxable':
					if($this->nontaxable) $data['UMnontaxable'] = 'Y';
					break;
				case 'checkimage_front':
				case 'checkimage_back':
					$data[$apifield] = base64_encode($this->$classfield);
					break;
				case 'billsourcekey':
					if($this->billsourcekey) $data['UMbillsourcekey'] = 'yes';
					break;
				case 'cardpresent':
					if($this->cardpresent) $data['UMcardpresent'] = '1';
					break;
				case 'allowpartialauth':
					if($this->allowpartialauth===true) $data['UMallowPartialAuth'] = 'Yes';
					break;
				case 'savecard':
					if($this->savecard) $data['UMsaveCard'] = 'y';
					break;
				default:
					$data[$apifield] = $this->$classfield;
			}
		}

		if(isset($data['UMcheckimagefront']) || isset($data['UMcheckimageback'])) $data['UMcheckimageencoding'] = 'base64';

		// tack on custom fields
		for($i=1; $i<=20; $i++)
		{
			if($this->{"custom$i"}) $data["UMcustom$i"] = $this->{"custom$i"};
		}

		// tack on line level detail
		$c=1;
		if(!is_array($this->lineitems)) $this->lineitems=array();
		foreach($this->lineitems as $lineitem)
		{
			$data["UMline{$c}sku"] = $lineitem['sku'];
			$data["UMline{$c}name"] = $lineitem['name'];
			$data["UMline{$c}description"] = $lineitem['description'];
			$data["UMline{$c}cost"] = $lineitem['cost'];
			$data["UMline{$c}taxable"] = $lineitem['taxable'];
			$data["UMline{$c}qty"] = $lineitem['qty'];
			if($lineitem['refnum'])
				$data["UMline{$c}prodRefNum"] = $lineitem['refnum'];

			//optional level 3 data
			if($lineitem['um']) $data["UMline{$c}um"] = $lineitem['um'];
			if($lineitem['taxrate']) $data["UMline{$c}taxrate"] = $lineitem['taxrate'];
			if($lineitem['taxamount']) $data["UMline{$c}taxamount"] = $lineitem['taxamount'];
			if($lineitem['taxclass']) $data["UMline{$c}taxclass"] = $lineitem['taxclass'];
			if($lineitem['commoditycode']) $data["UMline{$c}commoditycode"] = $lineitem['commoditycode'];
			if($lineitem['discountrate']) $data["UMline{$c}discountrate"] = $lineitem['discountrate'];
			if($lineitem['discountamount']) $data["UMline{$c}discountamount"] = $lineitem['discountamount'];
			$c++;
		}

		// Create hash if pin has been set.
		if(trim($this->pin))
		{
			// generate random seed value
			$seed = microtime(true) . rand();

			// assemble prehash data
			$prehash = $this->command . ":" . trim($this->pin) . ":" . $this->amount . ":" . $this->invoice . ":" . $seed;

			// if sha1 is available,  create sha1 hash,  else use md5
			if(function_exists('sha1')) $hash = 's/' . $seed . '/' . sha1($prehash) . '/n';
			else $hash = 'm/' . $seed . '/' . md5($prehash) . '/n';

			// populate hash value
			$data['UMhash'] = $hash;
		}

		// Tell the gateway what the client side timeout is
		if(@$this->timeout) $data['UMtimeout'] = intval($this->timeout);

		// Figure out URL
		$url = ($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/gate");
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/gate";

		// Post data to Gateway
		$this->usaepay_request=$data;		
		$result=$this->httpPost($url, $data);
		if($result===false) return false;

		// result is in urlencoded format, parse into an array

		parse_str($result,$tmp);
		// dump($result);

		// check to make sure we received the correct fields
		if(!isset($tmp["UMversion"]) || !isset($tmp["UMstatus"]))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error parsing data from card processing gateway.";
			$this->errorcode=10132;
			$this->usaepay_response_err=array("code"=>$this->errorcode,"message"=>$this->error);
			return false;
		}

		// Store results
		$this->result=(isset($tmp["UMstatus"])?$tmp["UMstatus"]:"Error");
		$this->resultcode=(isset($tmp["UMresult"])?$tmp["UMresult"]:"E");
		$this->authcode=(isset($tmp["UMauthCode"])?$tmp["UMauthCode"]:"");
		$this->refnum=(isset($tmp["UMrefNum"])?$tmp["UMrefNum"]:"");
		$this->batch=(isset($tmp["UMbatch"])?$tmp["UMbatch"]:"");
		$this->avs_result=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		$this->avs_result_code=(isset($tmp["UMavsResultCode"])?$tmp["UMavsResultCode"]:"");
		$this->cvv2_result=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");
		$this->cvv2_result_code=(isset($tmp["UMcvv2ResultCode"])?$tmp["UMcvv2ResultCode"]:"");
		$this->vpas_result_code=(isset($tmp["UMvpasResultCode"])?$tmp["UMvpasResultCode"]:"");
		$this->convertedamount=(isset($tmp["UMconvertedAmount"])?$tmp["UMconvertedAmount"]:"");
		$this->convertedamountcurrency=(isset($tmp["UMconvertedAmountCurrency"])?$tmp["UMconvertedAmountCurrency"]:"");
		$this->conversionrate=(isset($tmp["UMconversionRate"])?$tmp["UMconversionRate"]:"");
		$this->error=(isset($tmp["UMerror"])?$tmp["UMerror"]:"");
		$this->errorcode=(isset($tmp["UMerrorcode"])?$tmp["UMerrorcode"]:"10132");
		$this->custnum=(isset($tmp["UMcustnum"])?$tmp["UMcustnum"]:"");
		$this->authamount=(isset($tmp["UMauthAmount"])?$tmp["UMauthAmount"]:"");
		$this->balance=(isset($tmp["UMremainingBalance"])?$tmp["UMremainingBalance"]:"");
		$this->cardlevelresult=(isset($tmp["UMcardLevelResult"])?$tmp["UMcardLevelResult"]:"");
		$this->procrefnum=(isset($tmp["UMprocRefNum"])?$tmp["UMprocRefNum"]:"");
		$this->profiler_score=(isset($tmp["UMprofilerScore"])?$tmp["UMprofilerScore"]:"");
		$this->profiler_response=(isset($tmp["UMprofilerResponse"])?$tmp["UMprofilerResponse"]:"");
		$this->profiler_reason=(isset($tmp["UMprofilerReason"])?$tmp["UMprofilerReason"]:"");
		$this->cardref=(isset($tmp["UMcardRef"])?$tmp["UMcardRef"]:"");

		// Obsolete variable (for backward compatibility) At some point they will no longer be set.
		//$this->avs=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		//$this->cvv2=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");


		if(isset($tmp["UMcctransid"])) $this->cctransid=$tmp["UMcctransid"];
		if(isset($tmp["UMacsurl"])) $this->acsurl=$tmp["UMacsurl"];
		if(isset($tmp["UMpayload"])) $this->pareq=$tmp["UMpayload"];

	$this->setResponseVariables($tmp);
	$this->setErrorVariables($tmp);
		if($this->resultcode == "A") return true;
		return false;

	}

	private function setResponseVariables($tmp)
	{
		$this->usaepay_response_filtered=array("TransactionID"=>$this->refnum,
		"paidStatus"=>$this->result,
		"PaidAmount"=>$this->amount,
		"PayNote"=>"Paid by ACH");
		 $this->usaepay_response_complete=$tmp;
			 
	}

		private function setErrorVariables($tmp)
	{
		$this->usaepay_response_err=array("code"=>$this->errorcode,"message"=>$this->error);
		$this->usaepay_response_complete=$tmp;	 
	}


	function ProcessQuickSale()
	{
		$this->command='quicksale';

		// check that we have the needed data
		$tmp=$this->CheckData();
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}

				// Create hash if pin has been set.
		if(!trim($this->pin))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error='Source key must have pin assigned to run transaction';
			$this->errorcode=999;
			return false;
		}

		// generate random seed value
		$seed = microtime(true) . rand();

		// assemble prehash data
		$prehash = $this->key . $seed . trim($this->pin);

		// hash the data
		$hash = sha1($prehash);


		$data = '<?xml version="1.0" encoding="UTF-8"?>' .
				'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:usaepay" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">' .
				'<SOAP-ENV:Body>' .
				'<ns1:runQuickSale>' .
				'<Token xsi:type="ns1:ueSecurityToken">' .
				'<ClientIP xsi:type="xsd:string">' . $this->ip . '</ClientIP>' .
				'<PinHash xsi:type="ns1:ueHash">' .
				'<HashValue xsi:type="xsd:string">' . $hash . '</HashValue>' .
				'<Seed xsi:type="xsd:string">' . $seed . '</Seed>' .
				'<Type xsi:type="xsd:string">sha1</Type>' .
				'</PinHash>' .
				'<SourceKey xsi:type="xsd:string">' . $this->key . '</SourceKey>' .
				'</Token>' .
				'<RefNum xsi:type="xsd:integer">' . preg_replace('/[^0-9]/','',$this->refnum) . '</RefNum>' .
				'<Details xsi:type="ns1:TransactionDetail">' .
				'<Amount xsi:type="xsd:double">' . $this->xmlentities($this->amount) . '</Amount>' .
				'<Description xsi:type="xsd:string">' . $this->xmlentities($this->description) . '</Description>'.
				'<Discount xsi:type="xsd:double">' . $this->xmlentities($this->discount) . '</Discount>' .
				'<Invoice xsi:type="xsd:string">' . $this->xmlentities($this->invoice) . '</Invoice>' .
				'<NonTax xsi:type="xsd:boolean">' . ($this->nontaxable?'true':'false') . '</NonTax>' .
				'<OrderID xsi:type="xsd:string">' . $this->xmlentities($this->orderid) . '</OrderID>' .
				'<PONum xsi:type="xsd:string">' . $this->xmlentities($this->ponum) . '</PONum>' .
				'<Shipping xsi:type="xsd:double">' . $this->xmlentities($this->shipping) . '</Shipping>' .
				'<Subtotal xsi:type="xsd:double">' . $this->xmlentities($this->subtotal) . '</Subtotal>' .
				'<Tax xsi:type="xsd:double">' . $this->xmlentities($this->tax) . '</Tax>' .
				'<Tip xsi:type="xsd:double">' . $this->xmlentities($this->tip) . '</Tip>' .
				'</Details>' .
				'<AuthOnly xsi:type="xsd:boolean">false</AuthOnly>' .
				'</ns1:runQuickSale>' .
				'</SOAP-ENV:Body>' .
				'</SOAP-ENV:Envelope>';

		// Figure out URL
		$url = ($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/soap/gate/15E7FB61");
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/soap/gate/15E7FB61";

		// Post data to Gateway
		$result=$this->httpPost($url, array('xml'=>$data));


		if($result===false) return false;

		if(preg_match('~<AuthCode[^>]*>(.*)</AuthCode>~', $result, $m)) $this->authcode=$m[1];
		if(preg_match('~<AvsResult[^>]*>(.*)</AvsResult>~', $result, $m)) $this->avs_result=$m[1];
		if(preg_match('~<AvsResultCode[^>]*>(.*)</AvsResultCode>~', $result, $m)) $this->avs_result_code=$m[1];
		if(preg_match('~<BatchRefNum[^>]*>(.*)</BatchRefNum>~', $result, $m)) $this->batch=$m[1];
		if(preg_match('~<CardCodeResult[^>]*>(.*)</CardCodeResult>~', $result, $m)) $this->cvv2_result=$m[1];
		if(preg_match('~<CardCodeResultCode[^>]*>(.*)</CardCodeResultCode>~', $result, $m)) $this->cvv2_result_code=$m[1];
		//if(preg_match('~<CardLevelResult[^>]*>(.*)</CardLevelResult>~', $result, $m)) $this->cardlevel_result=$m[1];
		//if(preg_match('~<CardLevelResultCode[^>]*>(.*)</CardLevelResultCode>~', $result, $m)) $this->cardlevel_result_code=$m[1];
		if(preg_match('~<ConversionRate[^>]*>(.*)</ConversionRate>~', $result, $m)) $this->conversionrate=$m[1];
		if(preg_match('~<ConvertedAmount[^>]*>(.*)</ConvertedAmount>~', $result, $m)) $this->convertedamount=$m[1];
		if(preg_match('~<ConvertedAmountCurrency[^>]*>(.*)</ConvertedAmountCurrency>~', $result, $m)) $this->convertedamountcurrency=$m[1];
		if(preg_match('~<Error[^>]*>(.*)</Error>~', $result, $m)) $this->error=$m[1];
		if(preg_match('~<ErrorCode[^>]*>(.*)</ErrorCode>~', $result, $m)) $this->errorcode=$m[1];
		//if(preg_match('~<isDuplicate[^>]*>(.*)</isDuplicate>~', $result, $m)) $this->isduplicate=$m[1];
		if(preg_match('~<RefNum[^>]*>(.*)</RefNum>~', $result, $m)) $this->refnum=$m[1];
		if(preg_match('~<Result[^>]*>(.*)</Result>~', $result, $m)) $this->result=$m[1];
		if(preg_match('~<ResultCode[^>]*>(.*)</ResultCode>~', $result, $m)) $this->resultcode=$m[1];
		if(preg_match('~<VpasResultCode[^>]*>(.*)</VpasResultCode>~', $result, $m)) $this->vpas_result_code=$m[1];


		// Store results
		if($this->resultcode == "A") return true;
		return false;
	}




	function ProcessQuickCredit()
	{
		$this->command='quickcredit';

		// check that we have the needed data
		$tmp=$this->CheckData();
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}

				// Create hash if pin has been set.
		if(!trim($this->pin))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error='Source key must have pin assigned to run transaction';
			$this->errorcode=999;
			return false;
		}

		// generate random seed value
		$seed = microtime(true) . rand();

		// assemble prehash data
		$prehash = $this->key . $seed . trim($this->pin);

		// hash the data
		$hash = sha1($prehash);

		$data = '<?xml version="1.0" encoding="UTF-8"?>' .
				'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:usaepay" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">' .
				'<SOAP-ENV:Body>' .
				'<ns1:runQuickCredit>' .
				'<Token xsi:type="ns1:ueSecurityToken">' .
				'<ClientIP xsi:type="xsd:string">' . $this->ip . '</ClientIP>' .
				'<PinHash xsi:type="ns1:ueHash">' .
				'<HashValue xsi:type="xsd:string">' . $hash . '</HashValue>' .
				'<Seed xsi:type="xsd:string">' . $seed . '</Seed>' .
				'<Type xsi:type="xsd:string">sha1</Type>' .
				'</PinHash>' .
				'<SourceKey xsi:type="xsd:string">' . $this->key . '</SourceKey>' .
				'</Token>' .
				'<RefNum xsi:type="xsd:integer">' . preg_replace('/[^0-9]/','',$this->refnum) . '</RefNum>' .
				'<Details xsi:type="ns1:TransactionDetail">' .
				'<Amount xsi:type="xsd:double">' . $this->xmlentities($this->amount) . '</Amount>' .
				'<Description xsi:type="xsd:string">' . $this->xmlentities($this->description) . '</Description>'.
				'<Discount xsi:type="xsd:double">' . $this->xmlentities($this->discount) . '</Discount>' .
				'<Invoice xsi:type="xsd:string">' . $this->xmlentities($this->invoice) . '</Invoice>' .
				'<NonTax xsi:type="xsd:boolean">' . ($this->nontaxable?'true':'false') . '</NonTax>' .
				'<OrderID xsi:type="xsd:string">' . $this->xmlentities($this->orderid) . '</OrderID>' .
				'<PONum xsi:type="xsd:string">' . $this->xmlentities($this->ponum) . '</PONum>' .
				'<Shipping xsi:type="xsd:double">' . $this->xmlentities($this->shipping) . '</Shipping>' .
				'<Subtotal xsi:type="xsd:double">' . $this->xmlentities($this->subtotal) . '</Subtotal>' .
				'<Tax xsi:type="xsd:double">' . $this->xmlentities($this->tax) . '</Tax>' .
				'<Tip xsi:type="xsd:double">' . $this->xmlentities($this->tip) . '</Tip>' .
				'</Details>' .
				'<AuthOnly xsi:type="xsd:boolean">false</AuthOnly>' .
				'</ns1:runQuickCredit>' .
				'</SOAP-ENV:Body>' .
				'</SOAP-ENV:Envelope>';

		// Figure out URL
		$url = ($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/soap/gate/15E7FB61");
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/soap/gate/15E7FB61";

		// Post data to Gateway
		$result=$this->httpPost($url, array('xml'=>$data));
		if($result===false) return false;

		if(preg_match('~<AuthCode[^>]*>(.*)</AuthCode>~', $result, $m)) $this->authcode=$m[1];
		if(preg_match('~<AvsResult[^>]*>(.*)</AvsResult>~', $result, $m)) $this->avs_result=$m[1];
		if(preg_match('~<AvsResultCode[^>]*>(.*)</AvsResultCode>~', $result, $m)) $this->avs_result_code=$m[1];
		if(preg_match('~<BatchRefNum[^>]*>(.*)</BatchRefNum>~', $result, $m)) $this->batch=$m[1];
		if(preg_match('~<CardCodeResult[^>]*>(.*)</CardCodeResult>~', $result, $m)) $this->cvv2_result=$m[1];
		if(preg_match('~<CardCodeResultCode[^>]*>(.*)</CardCodeResultCode>~', $result, $m)) $this->cvv2_result_code=$m[1];
		//if(preg_match('~<CardLevelResult[^>]*>(.*)</CardLevelResult>~', $result, $m)) $this->cardlevel_result=$m[1];
		//if(preg_match('~<CardLevelResultCode[^>]*>(.*)</CardLevelResultCode>~', $result, $m)) $this->cardlevel_result_code=$m[1];
		if(preg_match('~<ConversionRate[^>]*>(.*)</ConversionRate>~', $result, $m)) $this->conversionrate=$m[1];
		if(preg_match('~<ConvertedAmount[^>]*>(.*)</ConvertedAmount>~', $result, $m)) $this->convertedamount=$m[1];
		if(preg_match('~<ConvertedAmountCurrency[^>]*>(.*)</ConvertedAmountCurrency>~', $result, $m)) $this->convertedamountcurrency=$m[1];
		if(preg_match('~<Error[^>]*>(.*)</Error>~', $result, $m)) $this->error=$m[1];
		if(preg_match('~<ErrorCode[^>]*>(.*)</ErrorCode>~', $result, $m)) $this->errorcode=$m[1];
		//if(preg_match('~<isDuplicate[^>]*>(.*)</isDuplicate>~', $result, $m)) $this->isduplicate=$m[1];
		if(preg_match('~<RefNum[^>]*>(.*)</RefNum>~', $result, $m)) $this->refnum=$m[1];
		if(preg_match('~<Result[^>]*>(.*)</Result>~', $result, $m)) $this->result=$m[1];
		if(preg_match('~<ResultCode[^>]*>(.*)</ResultCode>~', $result, $m)) $this->resultcode=$m[1];
		if(preg_match('~<VpasResultCode[^>]*>(.*)</VpasResultCode>~', $result, $m)) $this->vpas_result_code=$m[1];


		// Store results
		if($this->resultcode == "A") return true;
		return false;
	}

	function getSession()
	{
		$seed = md5(mt_rand());
		$action = 'getsession';

		// build hash
		$prehash = $action . ":" . $this->pin . ":" . $seed;
		$hash = 's/' . $seed . '/' . sha1($prehash);

		// Figure out URL
		$url = 'https://www.usaepay.com/interface/profiler/';
		if(preg_match('~https://([^/]*)/~i', $this->gatewayurl, $m)) {
			$url = 'https://' . $m[1] . '/interface/profiler/';
		}

		// Look for usesandbox
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/interface/profiler/";

		// Add request paramters
		$url .=  $action . '?SourceKey=' . rawurlencode($this->key) . '&Hash=' . rawurlencode($hash);

		// Make Rest Call
		$output = file_get_contents($url);
		if(!$output) {
			$this->error = "Blank response";
			return false;
		}

		// Parse response
		$xml = simplexml_load_string($output);
		if(!$xml) {
			$this->error = "Unable to parse response";
			return false;
		}

		if($xml->Response!='A') {
			$this->error = $xml->Reason;
			$this->errorcode = $xml->ErrorCode;
			return false;
		}

		// assemble template
		$query ='org_id=' .$xml->OrgID . '&session_id=' . $xml->SessionID;
		$baseurl = "https://h.online-metrix.net/fp/";

		$out = '';
		$out .= '<p style="background:url('.$baseurl.'clear.png?'.$query.'&m=1)"></p>';
		$out .= '<img src="'.$baseurl.'clear.png?'.$query.'&m=2" width="1" height="1" alt="">';
		$out .= '<script src="'.$baseurl.'check.js?'.$query.'" type="text/javascript"></script>';
		$out .= '<object type="application/x-shockwave-flash" data="'.$baseurl.'fp.swf?'.$query.'" width="1" height="1" id="thm_fp">';
		$out .= '	<param name="movie" value="'.$baseurl.'fp.swf?'.$query.'" />';
		$out .= '<div></div></object>';

		return array('sessionid'=>$xml->SessionID, 'orgid'=>$xml->OrgID, 'html'=>$out);
	}

	/**
	 * Verify Proper Installation of USAePay class and required PHP Support
	 *
	 */
	function Test()
	{
		$curl_version=false;
		?>
		<table border=1>
		<tr><th><b>Test</b></th><th><b>Result</b></th></tr>
		<tr><td valign="Top">Checking PHP Version</td>
		<td valign="top"><?php
			if(version_compare(phpversion(),"4.3.0")) {
				?><font color="green">Ok</font><br>
				PHP version <?php echo phpversion()?> on <?php echo PHP_OS?> detected.
				<?php
			} else {
				?><font color="red">Warning</font><br>
				PHP version <?php echo phpversion()?> detected. It is recommended that you
				upgrade to the most recent release of PHP.
				<?php
			}
		?></td></tr>
		<tr><td valign="Top">Checking CURL Extension</td>
		<td valign="top"><?php
			if(function_exists("curl_version")) {
				$tmp=curl_version();
				// PHP 5 returns an array,  version 4 returns a string
				if(is_array($tmp)) {
					$curl_version=$tmp["version"];
					$curl_ssl_version=$tmp["ssl_version"];
				} else {
					$tmp=explode(" ", $tmp);
					foreach($tmp as $piece)
					{
						list($lib,$version)=explode("/",$piece);
						if(stristr($lib,"curl")) $curl_version=$version;
						elseif(stristr($lib,"ssl")) $curl_ssl_version=$version;
					}

				}
				?><font color="green">Ok</font><br>
				Curl Extension (<?php echo $curl_version?>) detected.
				<?php
			} else {
				?><font color="red">Error</font><br>
				Your PHP installation does not include the Curl Extension. You must either enable the curl
				extension in your php.ini file by removing the semi-colon from the line ";extension=php_curl.dll" or recompile
				php with the configuration flag: "--with-curl"
				<?php
			}
		?>
		</td></tr>
		<tr><td valign="Top">Checking CURL SSL Support</td>
		<td valign="top"><?php
			if($curl_ssl_version) {
				?><font color="green">Ok</font><br>
				SSL Version (<?php echo $curl_ssl_version?>) detected.
				<?php
			} else {
				?><font color="red">Error</font><br>
				It appears that your curl installation does not include support for the HTTPS (ssl) protocal
				Proper SSL installation is required to communicate with the USAePay gateway securely.  Please recompile curl
				with SSL support.
				<?php
			}
		?>
		</td></tr>
		<tr><td valign="Top">Checking Communication with the Gateway</td>
		<td valign="top"><?php
			$ssl_failed=true;
			if(!$curl_version) {
				?><font color="red">Error</font><br>
				No curl support
				<?php
			} else {
				$ch = curl_init(($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/secure/gate.php") . "?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
				if(!$ch) {
					?><font color="red">Error</font><br>
					Curl failed to connect to server:  (NULL $ch returned)
					<?php
				} else {

					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);

					$result=curl_exec($ch);
					parse_str($result, $return);
					if($return["UMversion"]) {
						$ssl_failed=false;
						?><font color="green">Ok</font><br>
						Successfully connected to the gateway.  Detected version (<?php echo $return["UMversion"]?>) of the USAePay gateway API.
						<?php
					} else {
						$ch = curl_init(($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/secure/gate.php") . "?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
						if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						$result=curl_exec($ch);
						parse_str($result, $return);
						if($return["UMversion"]) {
							?><font color="red">Warning</font><br>

							Successfully connected to the gateway but could not verify SSL certificate.  This usually indicates that you
							do not have curl set up with the proper root CA certificate.  It is recommended that you install an up to date
							root ca bundle.  As a <b>temporary</b> work around you may disable the ssl cert check by adding the following to your script:<br><br><tt>
							$tran->ignoresslcerterrors=true;<br>
							<br></tt>
							<?php
							$ch = curl_init("https://www.verisign.com");
							if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							$result=curl_exec($ch);
							if(strlen($result)) {
								?>
								SSL certificate for VeriSign was validated sucessfully. This would indicate that you have a root CA bundle installed but that it
								is outdated. See below.
								<?php
							} else {
								?>
								Unable to verify SSL certificate for VeriSign.  This would indicate that you do not have a root CA bundle installed. See below.
								<?php
							}



						} else {
							$ch = curl_init("https://216.133.244.70/secure/gate.php?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
							$result=curl_exec($ch);
							parse_str($result, $return);
							if($return["UMversion"]) {
								?><font color="red">Warning</font><br>

								Successfully connected to the gateway using a static IP but could not connect using hostname. This would indicate
								that you do not have DNS setup correctly.  Please correct your server configuration to include a valid
								DNS server. As a temporary work around you may add the following to your script:<br><br><tt>
								$tran->ignoresslcerterrors=true;<br>
								$tran->gatewayurl="https://216.133.244.70/gate.php";<br>
								<br></tt>
								<?php
							} else {
								?><font color="red">Failed</font><br>
								Unable to establish connection to the USAePay Gateway.  It is possible that you are firewalling
								outbound traffic from your server.  To correct this problem,  you must add an allow entry in
								your firewall for ip <?php echo gethostbyname("www.usaepay.com")?> on port 443;
								<?php

							}

						}

					}
				}
			}
		?>
		</td></tr>
		<?php if($ssl_failed) {?>

		<tr><td valign="Top">Looking for root CA Bundle</td>
		<td valign="top"><?php
		$certpath="";
			if(strstr(PHP_OS,"WIN")!==false) {
				foreach(array('c:\windows\ca-bundle.crt','c:\windows\curl-ca-bundle.crt') as $certpath)
				{
					if(is_file($certpath)) break;
					else unset($certpath);
				}

				if(!$certpath) $certpath="c:\windows\curl-ca-bundle.crt";
			} else {

				foreach(array("/usr/share/curl/curl-ca-bundle.crt","/usr/local/share/curl/curl-ca-bundle.crt","/etc/curl/curl-ca-bundle.crt") as $certpath)
				{
					if(is_file($certpath)) break;
					else unset($certpath);
				}

				if(!$certpath) $certpath="/usr/share/curl/curl-ca-bundle.crt";
			}


			if(is_readable($certpath)) {
				?><font color="green">Ok</font><br>
				A root CA bundle was found in "<?php echo $certpath?>".  Since the above test failed,  its possible that curl is not
				correctly configured to use this bundle.  You can correct the problem by add the following code to your script:<br><br><tt>
				$tran->cabundle='<?php echo $certpath?>';
				</tt>
				<br><br>
				It is also possible that your root CA bundle is out dated. Your root CA bundle was last updated <?php echo date("m/d/y", filemtime($certpath))?>.
				You can download a new file from:
				<a href="https://www.usaepay.com/topics/curl-ca-bundle.crt">https://www.usaepay.com/topics/curl-ca-bundle.crt</a>
				</tt>
				<?php
			} else {
				?><font color="red">Error</font><br>
				Unable to locate your root CA bundle file. You can correct this by downloading a bundle file from:
				<a href="https://www.usaepay.com/topics/curl-ca-bundle.crt">https://www.usaepay.com/topics/curl-ca-bundle.crt</a>
				Once downloaded save this file to: <?php echo $certpath?> You may also need to add the following code:<br><br><tt>
				$tran->cabundle='<?php echo $certpath?>';
				</tt>
				<br><br>
				<?php
			}
		?>
		</td></tr>
		<?php } ?>

		</table>
		<?php
	}

	function getFieldMap()
	{
		return array("UMkey" => 'key',
			"UMcommand" => 'command',
			"UMauthCode" => 'origauthcode',
			"UMcard" => 'card',
			"UMexpir" => 'exp',
			"UMbillamount" => 'billamount',
			"UMamount" => 'amount',
			"UMinvoice" => 'invoice',
			"UMorderid" => 'orderid',
			"UMponum" => 'ponum',
			"UMtax" => 'tax',
			"UMnontaxable" => 'nontaxable',
			"UMtip" => 'tip',
			"UMshipping" => 'shipping',
			"UMdiscount" => 'discount',
			"UMsubtotal" => 'subtotal',
			"UMcurrency" => 'currency',
			"UMname" => 'cardholder',
			"UMstreet" => 'street',
			"UMzip" => 'zip',
			"UMdescription" => 'description',
			"UMcomments" => 'comments',
			"UMcvv2" => 'cvv2',
			"UMip" => 'ip',
			"UMtestmode" => 'testmode',
			"UMcustemail" => 'custemail',
			"UMcustreceipt" => 'custreceipt',
			"UMrouting" => 'routing',
			"UMaccount" => 'account',
			"UMssn" => 'ssn',
			"UMdlstate" => 'dlstate',
			"UMdlnum" => 'dlnum',
			"UMchecknum" => 'checknum',
			"UMaccounttype" => 'accounttype',
			"UMcheckformat" => 'checkformat',
			"UMcheckimagefront" => 'checkimage_front',
			"UMcheckimageback" => 'checkimage_back',
			"UMaddcustomer" => 'addcustomer',
			"UMrecurring" => 'recurring',
			"UMbillamount" => 'billamount',
			"UMbilltax" => 'billtax',
			"UMschedule" => 'schedule',
			"UMnumleft" => 'numleft',
			"UMstart" => 'start',
			"UMexpire" => 'end',
			"UMbillsourcekey" => 'billsourcekey',
			"UMbillfname" => 'billfname',
			"UMbilllname" => 'billlname',
			"UMbillcompany" => 'billcompany',
			"UMbillstreet" => 'billstreet',
			"UMbillstreet2" => 'billstreet2',
			"UMbillcity" => 'billcity',
			"UMbillstate" => 'billstate',
			"UMbillzip" => 'billzip',
			"UMbillcountry" => 'billcountry',
			"UMbillphone" => 'billphone',
			"UMemail" => 'email',
			"UMfax" => 'fax',
			"UMwebsite" => 'website',
			"UMshipfname" => 'shipfname',
			"UMshiplname" => 'shiplname',
			"UMshipcompany" => 'shipcompany',
			"UMshipstreet" => 'shipstreet',
			"UMshipstreet2" => 'shipstreet2',
			"UMshipcity" => 'shipcity',
			"UMshipstate" => 'shipstate',
			"UMshipzip" => 'shipzip',
			"UMshipcountry" => 'shipcountry',
			"UMshipphone" => 'shipphone',
			"UMcardauth" => 'cardauth',
			"UMpares" => 'pares',
			"UMxid" => 'xid',
			"UMcavv" => 'cavv',
			"UMeci" => 'eci',
			"UMcustid" => 'custid',
			"UMcardpresent" => 'cardpresent',
			"UMmagstripe" => 'magstripe',
			"UMdukpt" => 'dukpt',
			"UMtermtype" => 'termtype',
			"UMmagsupport" => 'magsupport',
			"UMcontactless" => 'contactless',
			"UMsignature" => 'signature',
			"UMsoftware" => 'software',
			"UMignoreDuplicate" => 'ignoreduplicate',
			"UMrefNum" => 'refnum',
			'UMauxonus' => 'auxonus',
			'UMepcCode' => 'epccode',
			'UMcustreceiptname' => 'custreceiptname',
			'UMallowPartialAuth' => 'allowpartialauth',
			'UMdigitalGoods' => 'digitalgoods',
			'UMmicr' => 'micr',
			'UMsession' => 'session',
			'UMisRecurring' => 'isrecurring',
			'UMclerk' => 'clerk',
			'UMtranterm' => 'terminal',
			'UMresttable' => 'restaurant_table',
			'UMticketedEvent' => 'ticketedevent',
			'UMifAuthExpired' => 'ifauthexpired',
			'UMauthExpireDays' => 'authexpiredays',
			'UMinventorylocation' => 'inventorylocation',
			'UMduty' => 'duty',
			'UMshipfromzip' => 'shipfromzip',
			'UMsaveCard' => 'savecard',
			'UMlocation' => 'geolocation',

			);
	}
	function buildQuery($data)
	{
		if(function_exists('http_build_query') && ini_get('arg_separator.output')=='&') return http_build_query($data);

		$tmp=array();
		foreach($data as $key=>$val) $tmp[] = rawurlencode($key) . '=' . rawurlencode($val);

		return implode('&', $tmp);

	}

	function httpPost($url, $data)
	{
		// if transport was not specified,  auto select transport
		if(!$this->transport)
		{
			if(function_exists("curl_version")) {
				$this->transport='curl';
			} else if(function_exists('stream_get_wrappers'))  {
				if(in_array('https',stream_get_wrappers())){
					$this->transport='stream';
				}
			}
		}


		// Use selected transport to post request to the gateway
		switch($this->transport)
		{
			case 'curl': return $this->httpPostCurl($url, $data);
			case 'stream': return $this->httpPostPHP($url, $data);
		}

		// No HTTPs libraries found,  return error
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Libary Error: SSL HTTPS support not found";
		$this->errorcode=10130;
		return false;
	}

	function httpPostCurl($url, $data)
	{

		//init the connection
		$ch = curl_init($url);
		if(!is_resource($ch))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Libary Error: Unable to initialize CURL ($ch)";
			$this->errorcode=10131;
			return false;
		}

		// set some options for the connection
		curl_setopt($ch,CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT, ($this->timeout>0?$this->timeout:45));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		// Bypass ssl errors - A VERY BAD IDEA
		if($this->ignoresslcerterrors)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		// apply custom ca bundle location
		if($this->cabundle)
		{
			curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);
		}

		// set proxy
		if($this->proxyurl)
		{
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, $this->proxyurl);
		}

		$soapcall=false;
		if(is_array($data))
		{
			if(array_key_exists('xml',$data)) $soapcall=true;
		}

		if($soapcall)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-type: text/xml;charset=UTF-8",
				"SoapAction: urn:ueSoapServerAction"
			));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data['xml']);

		} else {
			// rawurlencode data
			$data = $this->buildQuery($data);

			// attach the data
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}

		// run the transfer
		$result=curl_exec ($ch);

		//get the result and parse it for the response line.
		if(!strlen($result))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error reading from card processing gateway.";
			$this->errorcode=10132;
			$this->blank=1;
			$this->transporterror=$this->curlerror=curl_error($ch);
			curl_close ($ch);
			return false;
		}
		curl_close ($ch);
		$this->rawresult=$result;

		if($soapcall) {
			return $result;
		}

		if(!$result) {
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Blank response from card processing gateway.";
			$this->errorcode=10132;
			return false;
		}

		// result will be on the last line of the return
		$tmp=explode("\n",$result);
		$result=$tmp[count($tmp)-1];

		return $result;
	}

	function httpPostPHP($url, $data)
	{


		// rawurlencode data
		$data = $this->buildQuery($data);

		// set stream http options
		$options = array(
			'http'=> array(
				'method'=>'POST',
	            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
	                . "Content-Length: " . strlen($data) . "\r\n",
	            'content' => $data,
	            'timeout' => ($this->timeout>0?$this->timeout:45),
	            'user_agent' => 'uePHPLibary v' . USAEPAY_VERSION . ($this->software?'/' . $this->software:'')
	        ),
	        'ssl' => array(
	            'verify_peer' => ($this->ignoresslcerterrors?false:true),
	            'allow_self_signed' => ($this->ignoresslcerterrors?true:false)
			)
		);

		if($this->cabundle) $options['ssl']['cafile'] = $this->cabundle;

		if(trim($this->proxyurl)) $options['http']['proxy'] = $this->proxyurl;


		// create stream context
		$context = stream_context_create($options);

		// post data to gateway
		$fd = fopen($url, 'r', null, $context);
		if(!$fd)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Unable to open connection to gateway";
			$this->errorcode=10132;
			$this->blank=1;
			if(function_exists('error_get_last')) {
				$err=error_get_last();
				$this->transporterror=$err['message'];
			} else if(isset($GLOBALS['php_errormsg'])) {
				$this->transporterror=$GLOBALS['php_errormsg'];
			}
			//curl_close ($ch);
			return false;
		}

		// pull result
		$result = stream_get_contents($fd);

		// check for a blank response
		if(!strlen($result))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error reading from card processing gateway.";
			$this->errorcode=10132;
			$this->blank=1;
			fclose($fd);
			//$this->curlerror=curl_error($ch);
			//curl_close ($ch);
			return false;
		}

		fclose($fd);
		return $result;

	}

	function xmlentities($string)
	{
		$string = preg_replace('/[^a-zA-Z0-9 _\-\.\'\r\n]/e', '_uePhpLibPrivateXMLEntities("$0")', $string);
		return $string;
	}
    
	function _uePhpLibPrivateXMLEntities($num)
	{
	$chars = array(128 => '&#8364;',130 => '&#8218;',131 => '&#402;', 132 => '&#8222;', 133 => '&#8230;', 134 => '&#8224;', 135 => '&#8225;', 136 => '&#710;', 137 => '&#8240;', 138 => '&#352;', 139 => '&#8249;', 140 => '&#338;', 142 => '&#381;', 145 => '&#8216;',146 => '&#8217;',147 => '&#8220;',148 => '&#8221;',149 => '&#8226;',150 => '&#8211;',151 => '&#8212;',152 => '&#732;', 153 => '&#8482;',154 => '&#353;', 155 => '&#8250;',156 => '&#339;', 158 => '&#382;', 159 => '&#376;');
	$num = ord($num);
	return (($num > 127 && $num < 160) ? $chars[$num] : "&#".$num.";" );
	}
		

}

?>
