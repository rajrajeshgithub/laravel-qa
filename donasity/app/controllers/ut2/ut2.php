<?php
	class Ut2_Controller extends Controller
	{
		public $tpl,$stripeReponse,$arr_pageMetaInfo,$LoginUserDetail;
		public $pStat=1;
		public $UpdatedBy,$UpdateOn,$UpdateLogDetail;
		public $stripe_code,$stripe_clientID,$stripe_OauthResp,$stripe_querystring;
		public $refUrl;
		function __construct()
		{
			$this->stripeResponse = array();
			$this->load_model('UserType2','objutype2');
			$this->load_model('Common','objCommon');
			$this->tpl	= new View;
		}
	
		function index($type='npo-login')
		{
			if(isset($_GET['refurl']))
			{
				$this->refUrl = urldecode($_GET['refurl']);
			}
			switch(strtolower($type))
			{
				case 'code-verification-form':
					$this->redirectLoggedInUsers();	
					$this->VerificationCodeForm();
					break;
				case 'code-verification':
					$this->CodeVerification();
					break;
				case 'registration-form':
					$this->redirectLoggedInUsers();	
					$this->RegistrationForm();
					break;		
				case 'registration':
					$this->Registration();
					break;
				case 'strip-setup':
					$this->StripSetupPage();
					break;	
				case 'verify-stripe-connection':
					$this->VerifyStripConnection();
                    break;	
				case 'confirmation':
					$this->showConfirmation();
				break;
				case 'npo-login':
					$this->npoLogin();
					break;	
				case 'login':
					$this->redirectLoggedInUsers();
					$this->Processlogin();
					break;	
				case 'logout':
					$this->LogOut();
					break;		
				case 'getresponse':				
					$this->GetResponse('werkcd5412');
				break;
				case 'reset':
					$this->Reset();
					break;
				case 'forget-password':
					$this->ForgetPassword();
					break;	
				case 'mail-sucess':
					$this->MailSucess();
					break;	
				case 'mail-fail':
					$this->MailFail();
					break;	
				default:
					$this->npoLogin();
					break;
			}
		}
		
		private function showConfirmation()
		{
			$UserID 					= getSession('UserIdentifier');		
			setSession('UserIdentifier',"");
			$this->objutype2->userId 	= keyDecrypt($UserID);	
			
			if($this->objutype2->userId==""){
				$this->LoginUserDetail	= getSession('Users','UserType2');
				$this->objutype2->userId=keyDecrypt($this->LoginUserDetail['user_id']);		
			}
			if(is_numeric($this->objutype2->userId))
			{				
				
			$DataArray	= array("N.NPO_Name","CONCAT(RU.RU_FistName,' ',RU.RU_LastName) as UserName","NUR.Stripe_ClientID","NUR.Status as Stripe_Status");
			$Condition	= " AND NUR.USERID=".$this->objutype2->userId;
			
			$NPOUserDetail	= $this->objutype2->GetNPODetail($DataArray,$Condition);
			
			
			$arrMetaInfo	=	$this->objCommon->GetPageCMSDetails('nporegconfirm');
			
			$arrMetaInfo["nporegconfirmcontent"]=strtr($arrMetaInfo["nporegconfirmcontent"],array('{{UserName}}' => $NPOUserDetail['UserName'],'{{NPO_Name}}' => $NPOUserDetail['NPO_Name'],'{{stripe_clientID}}' => $NPOUserDetail['Stripe_ClientID']));
			
$arrMetaInfo["messagefailure"]=strtr($arrMetaInfo["messagefailure"],array('{{UserName}}' => $NPOUserDetail['UserName'],'{{NPO_Name}}' => $NPOUserDetail['NPO_Name'],'{{stripe_clientID}}' => $NPOUserDetail['Stripe_ClientID']));
$arrMetaInfo["messagesucess"]=strtr($arrMetaInfo["messagesucess"],array('{{UserName}}' => $NPOUserDetail['UserName'],'{{NPO_Name}}' => $NPOUserDetail['NPO_Name'],'{{stripe_clientID}}' => $NPOUserDetail['Stripe_ClientID']));
			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			
			$this->tpl->assign("stripe_clientID",$NPOUserDetail['Stripe_ClientID']);
			$this->tpl->assign("stripe_status",$NPOUserDetail['Stripe_Status']);
			
			$this->tpl->draw("ut2/confirmation");	
			
			}
		}
		
		public function getStripResponse($code)
		{
		
			
			$token_request_body = array('grant_type' => 'authorization_code','client_id' => STRIPE_ACCOUNT_ID,'code' => $code,'client_secret' => STRIPE_PRIVATE_KEY);
			//dump($token_request_body);
			$req = curl_init('https://connect.stripe.com/oauth/token');
			curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($req, CURLOPT_POST, true );
			curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
			// TO DO: Additional error handling
			$respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
			//echo $req;exit;
			$resp = json_decode(curl_exec($req), true);
			
			curl_close($req);
			//dump($resp);
			return $resp;
		}
		
		private function RegistrationForm()
		{
			$NpoID			= getSession('confirmnpodetail','NPOID');
			$EIN			= getSession('confirmnpodetail','EIN');
			$ConfirmCode	= getSession('confirmnpodetail','ConfirmCode');
			$NPOName		= getSession('confirmnpodetail','NPOName');
			$NPOAddress		= getSession('confirmnpodetail','NPOAddress');
			if(getSession('confirmnpodetail')==NULL)
			{
				redirect(URL.'ut2/code-verification-form');	
			}
			$this->GetCountryList();
			$this->tpl->assign('NPOID',$NpoID);
			$this->tpl->assign('EIN',$EIN);
			$this->tpl->assign('ConfirmCode',$ConfirmCode);
			$this->tpl->assign('NPOName',$NPOName);
			$this->tpl->assign('NPOAddress',$NPOAddress);			
			
			$arrMetaInfo	=	$this->objCommon->GetPageCMSDetails('nporegform');
			
			$arrMetaInfo["pagedescription"]=strtr($arrMetaInfo["pagedescription"],array('{{NPOName}}' => $NPOName,'{{NPOAddress}}' => $NPOAddress));
			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($arrMetaInfo);
			$this->tpl->draw("ut2/registrationform");
		}
		
		private function VerificationCodeForm()
		{			
			unsetSession("confirmnpodetail");
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('nporegaccesscode'));					
			$this->tpl->draw("ut2/codeverificationform");	
		}
		
		
		private function CodeVerification()
		{
			$this->objutype2->UniqueCode	= request('post','uniquecode',0);			
			$this->objutype2->CodeVerificationDB();
			//dump($this->objutype2->NPODetails);
			/*----update process log------*/			
			$userType 	= 'UT2';					
			$userID 	= "";
			$userName	= "";
			$sMessage = "Error in code varification process";
			$lMessage = "Error in code varification process of NPO registration process";
			if($this->objutype2->Pstatus)
			{
				$sMessage = "Code varification processed successfully";
				$lMessage = "Code varification processed successfully with code - ".$this->objutype2->UniqueCode;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->objutype2->NPODetails['NPO_ID'],
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype2));	
				$this->objutype2->updateProcessLog($DataArray);	
				/*-----------------------------*/
			if($this->objutype2->Pstatus)
			{
				$NpoSession	= getSession('confirmnpodetail','NPOID');
				if($NpoSession == "")
				{
					setSession('confirmnpodetail',$this->objutype2->NPODetails['NPO_ID'],'NPOID');
					setSession('confirmnpodetail',$this->objutype2->NPODetails['NPO_EIN'],'EIN');
					setSession('confirmnpodetail',$this->objutype2->UniqueCode,'ConfirmCode');
					setSession('confirmnpodetail',$this->objutype2->NPODetails['NPO_Name'],'NPOName');
					setSession('confirmnpodetail',$this->objutype2->NPODetails['NPO_Street']." ,".$VerificationDetail['NPO_City'],'NPOAddress');
				}
				redirect(URL."ut2/registration-form");	
			}
			else
			{
				redirect(URL."ut2/code-verification-form");
			}
		}
		
		private function Registration()
		{		
			$this->objutype2->Ein					= request('post','ein',1);	
			$this->objutype2->NPOID					= request('post','npoid',1);
			$this->objutype2->ConfirmCode			= request('post','npoconfirmcode',0);
			$this->objutype2->FirstName				= request('post','FirstName',0);	
			$this->objutype2->LastName				= request('post','LastName',0);	
			$this->objutype2->CompanyName			= request('post','Companyname',0);	
			$this->objutype2->Designation			= request('post','Designation',0);	
			$this->objutype2->Address1				= request('post','Address1',0);	
			$this->objutype2->Address2				= request('post','Address2',0);	
			$this->objutype2->City					= request('post','City',0);	
			$this->objutype2->State					= request('post','State',0);	
			$this->objutype2->Country				= request('post','Country',0);	
			$this->objutype2->Zip					= request('post','Zip',0);	
			$this->objutype2->PhoneNumber			= request('post','Phone',0);
			$this->objutype2->Mobile				= request('post','AlternatePhone',0);	
			$this->objutype2->EmailAddress			= request('post','Email',0);	
			$this->objutype2->Password				= request('post','Password',0);	
			$this->objutype2->ConfirmPassword		= request('post','ConfirmPassword',0);				
			$this->objutype2->RegDate				= getDateTime();
			$this->objutype2->UpdateDate			= getDateTime();
			$this->objutype2->LastLoginDate			= getDateTime();
			$this->objutype2->UserIP				= $_SERVER['REMOTE_ADDR'];
			$this->objutype2->UserType				= 2;
			$this->objutype2->Status				= 1;			
			
			$this->objutype2->AddUser();
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->objutype2->userId ;
			$userName	= $this->objutype2->FirstName." ".$this->objutype2->LastName;
			$sMessage = "Error in npo user registration process";
			$lMessage = "Error in npo user registration process";
			if($this->objutype2->userId>0)
			{
				$sMessage = "NPO user registered successfully";
				$lMessage = "NPO user registered successfully with ID =".$userID;	
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
				$this->objutype2->GetUserDetails(array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','NRU.NPOID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID')," AND RU.RU_ID=".$this->objutype2->userId);
				
				$this->objutype2->SetSession();
				setSession("UserIdentifier",keyEncrypt($this->objutype2->userId));
				$this->SendMailForNpoRegister();
				redirect(URL."ut2/strip-setup/");	
			}
			else
			{
					redirect($_SERVER['HTTP_REFERER']);	
			}
		}
		private function SendMailForNpoRegister()
		{
			$this->load_model('Email','objemail');
			$Keyword='UT2nporegister';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$CustomerDetails=$this->objutype2->GetUserDetails(array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','NRU.NPOID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID')," AND RU.RU_ID=".$this->objutype2->userId);
			$UserName=$CustomerDetails['RU_FistName'].' '.$CustomerDetails['RU_LastName'];
			$GetNPOProfileDetail=$this->objutype2->GetNPOProfileDetail(array('NPO_ID','NPO_Name'),$this->objutype2->userId);
			$NPOUserName=$GetNPOProfileDetail['NPO_Name'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('NPOUserName',$NPOUserName);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$CustomerDetails['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$CustomerDetails['RU_EmailID'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
		}
		
		private function StripSetupPage()
		{
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('nporegstripe'));
			$this->tpl->assign('STRIPE_CONNECT_URL',STRIPE_CONNECT_URL);
			$this->tpl->draw("ut2/stripsetuppage");	
		}
		
		private function VerifyStripConnection()
		{
			$UserID = getSession('UserIdentifier');		
			$this->objutype2->userId 	= keyDecrypt($UserID);	

			if($this->objutype2->userId==""){
			$this->LoginUserDetail	= getSession('Users','UserType2');
			$this->objutype2->userId=keyDecrypt($this->LoginUserDetail['user_id']);		
			}
			
			$this->stripe_querystring=$_SERVER['QUERY_STRING'];
			$this->stripe_code =isset($_GET["code"])?$_GET["code"]:"";
			/*----update process log------*/
			$userType 	= 'UT2';					
			$userID 	= $this->objutype2->userId;
			$userName	= $this->LoginUserDetail['user_fullname'];
			$sMessage = "Strip connection failed";
			$lMessage = "Strip connection failed";
			if($this->stripe_code<>"")
			{
				$this->stripe_OauthResp=$this->getStripResponse($this->stripe_code);
				$this->stripe_clientID=$this->stripe_OauthResp["stripe_user_id"];
				
				if($this->stripe_clientID<>"")
				{	
					$sMessage = "Strip connected successfully";
					$lMessage = "Strip connected successfully";				
					$arreyFields = array("Status"=>'1',"Stripe_ClientID"=>$this->stripe_clientID,
										"STRIPE_RESPONSE"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"LastUpdatedDate"=>getDateTime(),
										"Active"=>'1',"Log"=>'CONCAT(Log,",Subcessfully Connected Updated on '.getDateTime().'")');
				}
				else					
				{	
					$sMessage = "Got code but not featched stripe_user_id";
					$lMessage = "Got code ".$this->stripe_code." but not featched stripe_user_id";
					$arreyFields = array("Status"=>'0',"STRIPE_RESPONSE"=>json_encode($this->stripe_querystring).json_encode($this->stripe_OauthResp),"LastUpdatedDate"=>getDateTime(),"Active"=>'0',"Log"=>'CONCAT(Log,",Got Code'.$this->stripe_code.' but stripe_user_id not feteched Updated on '.getDateTime().'")');						
			
				}
				$this->objutype2->UpdateUser($arreyFields);
				$this->ProcessSendMail();
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
				
			/*	echo($this->objutype2->userId.$_SERVER['QUERY_STRING']);
				echo($this->objutype2->userId);
				print_r($arreyFields);
				dump($this->stripe_OauthResp);		*/
				//redirect(URL.'ut2myaccount/confirmation/sucess'); 
				
				redirect(URL.'/ut2myaccount/manage-npo-details'); 
				
			}	                   
			else 
			{
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
			   	redirect(URL.'ut2/confirmation/fail');	
			}			
		}
		
		private function ProcessSendMail()
		{
			$CustomerDetails=$this->objutype2->GetUserDetails(array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','NRU.NPOID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_State','NRU.Status'));
			if($CustomerDetails['Status']==1)
			{
				$GetNPOProfileDetail=$this->objutype2->GetNPOProfileDetail(array('NPO_ID','NPO_EIN','NPO_Name'),$this->objutype2->userId);
				$this->SendMailForNpoStripe($CustomerDetails,$GetNPOProfileDetail);
				$this->SendMailForAdmin($CustomerDetails,$GetNPOProfileDetail);
			}
		}
		
		private function SendMailForNpoStripe($CustomerDetails,$GetNPOProfileDetail)
		{
			$this->load_model('Email','objemail');
			$Keyword="UT2npostripe";
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName=$CustomerDetails['RU_FistName'].' '.$CustomerDetails['RU_LastName'];
			$NPOUserName=$GetNPOProfileDetail['NPO_Name'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('NPOUserName',$NPOUserName);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$CustomerDetails['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$CustomerDetails['RU_EmailID'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
		}
		private function SendMailForAdmin($CustomerDetails,$GetNPOProfileDetail)
		{
			$this->load_model('Email','objemail');
			$Keyword="UT2npostripetoadmin";
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName=$CustomerDetails['RU_FistName'].' '.$CustomerDetails['RU_LastName'];
			$City=$CustomerDetails['RU_City'];
			$State=$CustomerDetails['RU_State'];
			$RU_EmailID=$CustomerDetails['RU_EmailID'];
			$NPOUserName=$GetNPOProfileDetail['NPO_Name'];
			$NPO_EIN=$GetNPOProfileDetail['NPO_EIN'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('EmailID',$RU_EmailID);
			$tpl->assign('NPOUserName',$NPOUserName);
			$tpl->assign('State',$State);
			$tpl->assign('NPO_EIN',$NPO_EIN);
			$tpl->assign('City',$City);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$CustomerDetails['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$GetTemplate['EmailTo'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
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
		
		private function GetCountryList()
		{
			$this->load_model('Common','objcommon');
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objcommon->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		public function getstateajax()
		{
			$CountryAbr	= request('post','CountryAB',0);
			$this->load_model("Common","objcommon");
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			echo json_encode($StateList);
			exit;
		}
		
		public function IsDuplicateEmail()
		{
			$this->load_model("Common","objcommon");
		   $status=false;
		   $EmailAddress = request('get','Email',0);
		   $status = $this->objcommon->checkEmailDuplicacy($EmailAddress);
		   echo json_encode($status);exit;	
		}
		
		//login section start
		public function npoLogin()
		{	
			$this->redirectLoggedInUsers();	
			EnPException::writeProcessLog('Login_Controller :: index action to show admin user login');			
			$this->tpl->assign("Url",URL);			
			$this->tpl->assign("refUrl",$this->refUrl);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('npologin'));
			$this->tpl->draw('ut2/npo-login');	
		}
		
		private function Processlogin()
		{
			$this->objutype2->EmailID	= request('post','emailId',0);
			$this->objutype2->Password	= request('post','password',0);
			$this->objutype2->stayLogin	= request('post','stayLogin',1);
			$this->refUrl				= request('post','refUrl',0);
			$this->objutype2->Processlogin();
			//echo "hello";exit;
			/*----update process log------*/
			$loginDetails = getSession("Users","UserType2");			
			$userType 	= 'UT2';					
			$userID 	= isset($loginDetails['user_id']) ? keyDecrypt($loginDetails['user_id']) : 0;
			$userName	= isset($loginDetails['user_fullname']) ? $loginDetails['user_fullname'] : '';
			$sMessage = "Error in npo user login";
			$lMessage = "Error in npo user login";
			if($this->objutype2->Pstatus)
			{
				$sMessage = "NPO user logged In";
				$lMessage = "NPO user logged In";
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
				/*$refer=getSession("Referer");
				if(trim($refer)<>'')
				{
					redirect(URL.$refer);
				}
				$this->redirectLoggedInUsers();*/
				$this->LoginUserDetail    = getSession('Users');
            	if(KeyDecrypt($this->LoginUserDetail['UserType2']['user_id'])>0)
				{
					$this->load_model('UserType1','objutype1');
					$this->objutype1->logoutUT1();	
					$this->redirectURL(URL."ut2myaccount",true);
				}
			}
			else
			{				
				redirect(URL."ut2/npo-login?refurl=".urlencode($this->refUrl));
			}
		}
		
		private function redirectURL($defaultUrl, $alternateUrl=false)
		{
			if($alternateUrl==true)
			{
				if(!filter_var($this->refUrl, FILTER_VALIDATE_URL)===false)
				{
					redirect($this->refUrl);
				}
				else
					redirect($defaultUrl);
			}
			else
			{
				redirect($defaultUrl);
			}
		}
		
		private function redirectLoggedInUsers()
        {
		    $this->LoginUserDetail = getSession('Users');
            if(count($this->LoginUserDetail['UserType2']) > 0 && KeyDecrypt($this->LoginUserDetail['UserType2']['user_id'])>0)
			{
				$this->load_model('UserType1','objutype1');
				$this->objutype1->logoutUT1();	
            	redirect(URL."ut2myaccount");
			}
		}
		
		//login section end
		
		private function LogOut()
		{
			if($this->objutype2->checkLogin(getSession('Users')))
			{
				$this->objutype2->logoutUT2();				
				$confirmationParams=array("msgCode"=>'C2004',"msgLog"=>1,"msgDisplay"=>1,"msgType"=>2);
				$placeholderValues=array("placeValue1");
				EnPException::setConfirmation($confirmationParams, $placeholderValues);	
			}
			else 
			{
				$messageParams=array("errCode"=>"E9005","errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1, "msgDisplay"=>1,"msgType"=>1);
				EnPException::setError($messageParams);
				
			}
			redirect(URL."ut2/npo-login");	
		}
		
		private function ForgetPassword()
		{
			$this->objutype2->EmailID	= request('post','Email',0);
			$this->objutype2->ForgetPasswordDB();
			
			if($this->objutype2->Pstatus)
			{
				$this->load_model('Email','objemail');
				$Keyword='UT2_ForgotPassword';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'.
	_DBLANG_);
				
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				
				$CustomerDetail=array("Name"=>$this->objutype2->userDetailsArray['Name'],"Email"=>$this->objutype2->EmailID);
				$tpl=new View;
				$tpl->assign('Link',$this->objutype2->Pageurl);
				$tpl->assign('Detail',$CustomerDetail);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				$InsertDataArray=array('FromID'=>$this->objutype2->userDetailsArray['RU_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$this->objutype2->EmailID,
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
				if($Status)
				{
					$this->SetStatus(1,'C2005');
				}else
				{
					$this->SetStatus(0,'E17000');
				}
				redirect(URL."ut2");
			}
			else
			{
				redirect(URL."ut2");
			}
		}
		public function ResetPassword($mailID,$userID,$Datetime)
		{
			//$this->redirectLoggedInUsers();
			date_default_timezone_set('America/New_York');
			$this->objutype2->EmailID  		= keyDecrypt(urldecode($mailID));
			$this->objutype2->userId  		= keyDecrypt(urldecode($userID));
			$this->objutype2->DateTime  	= keyDecrypt(urldecode($Datetime));
			$this->objutype2->VerifyResetPassURL();
			
				$this->load_model('Common','objCommon');
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT2_RESETPASSWORD'));
				$this->tpl->assign("UserID",$this->objutype2->userDetailsArray['RU_ID']);
				$this->tpl->assign("EmailID",$this->objutype2->userDetailsArray['RU_EmailID']);
				$this->tpl->draw("ut2/resetpassword");	
			
			
			/*if($this->objutype2->Pstatus)
			{
				$this->load_model('Common','objCommon');
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT2_RESETPASSWORD'));
				$this->tpl->assign("UserID",$this->objutype2->userDetailsArray['RU_ID']);
				$this->tpl->assign("EmailID",$this->objutype2->userDetailsArray['RU_EmailID']);
				$this->tpl->draw("ut2/resetpassword");	
			}
			else
			{
				redirect(URL."ut2");
			}*/
		}
		private function Reset()
		{
			$this->objutype2->EmailID 				= keyDecrypt(request('post','emailId',0));
			$this->objutype2->userId 				= keyDecrypt(request('post','userId',0));
			$this->objutype2->Password 				= request('post','newPassword',0);
			$this->objutype2->ConfirmPassword 		= request('post','confirmPassword',0);	
			$this->objutype2->ResetDB();
			/*----update process log------*/				
			$userType 	= 'UT2';					
			$userID 	= $this->objutype2->userId;
			$userName	= '';
			$sMessage = "Error in password reset process";
			$lMessage = "Error in password reset process";
			if($this->objutype2->Pstatus)
			{
				$sMessage = "Password has been reset ";
				$lMessage = "Password has been reset with email address - ".$this->objutype2->EmailID;
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
			redirect(URL."ut2");
		}		
		
		
		
	/* =================== */	
	private function MailSucess() {
		$this->load_model('Email', 'objemail');
		$Keyword = 'paymenttransfersucess';
		$where = " Where Keyword='".$Keyword."'";
		
		$DataArray = array(
			'TemplateID',
			'TemplateName',
			'EmailTo',
			'EmailToCc',
			'EmailToBcc',
			'EmailFrom',
			'Subject_'._DBLANG_);
			
		$GetTemplate = $this->objemail->GetTemplateDetail($DataArray, $where);
		$LoginUserDetail = getSession('Users', 'UserType2');
		
		$UserName = $LoginUserDetail['user_fullname'];
		$UserEmail = $LoginUserDetail['user_email'];
		
		$tpl = new View;
		$tpl->assign('UserName', $UserName);
		$HTML = $tpl->draw('email/' . $GetTemplate['TemplateName'], true);
		echo $HTML;
		die();
		$InsertDataArray = array(
			'FromID'	=>keyDecrypt($LoginUserDetail['user_id']),
			'CC'		=>$GetTemplate['EmailToCc'],
			'BCC'		=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],
			'ToAddress'	=>"qualdev.test@gmail.com",
			'Subject'	=>$GetTemplate['Subject_'._DBLANG_],
			'Body'		=>$HTML,
			'Status'	=>'0',
			'SendMode'	=>'1',
			'AddedOn'=>getDateTime());
			
		$id = $this->objemail->InsertEmailDetail($InsertDataArray);
		$Eobj = LoadLib('BulkEmail');
		$Status = $Eobj->sendEmail($id);	
	}
	/* =================== */
	
	/* =================== */	
	private function MailFail() {
		$this->load_model('Email', 'objemail');
		$Keyword = 'paymenttransferfail';
		$where = " Where Keyword='".$Keyword."'";
		
		$DataArray = array(
			'TemplateID',
			'TemplateName',
			'EmailTo',
			'EmailToCc',
			'EmailToBcc',
			'EmailFrom',
			'Subject_'._DBLANG_);
			
		$GetTemplate = $this->objemail->GetTemplateDetail($DataArray, $where);
		$LoginUserDetail = getSession('Users', 'UserType2');
		
		$UserName = $LoginUserDetail['user_fullname'];
		$UserEmail = $LoginUserDetail['user_email'];
		
		$tpl = new View;
		$tpl->assign('UserName', $UserName);
		$HTML = $tpl->draw('email/' . $GetTemplate['TemplateName'], true);
		//echo $HTML;
		//die();
		$InsertDataArray = array(
			'FromID'	=>keyDecrypt($LoginUserDetail['user_id']),
			'CC'		=>$GetTemplate['EmailToCc'],
			'BCC'		=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],
			'ToAddress'	=>"qualdev.test@gmail.com",
			'Subject'	=>$GetTemplate['Subject_'._DBLANG_],
			'Body'		=>$HTML,
			'Status'	=>'0',
			'SendMode'	=>'1',
			'AddedOn'=>getDateTime());
			
		$id = $this->objemail->InsertEmailDetail($InsertDataArray);
		$Eobj = LoadLib('BulkEmail');
		$Status = $Eobj->sendEmail($id);	
	}
	/* =================== */

	}
?>