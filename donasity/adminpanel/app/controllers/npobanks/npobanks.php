<?php
	class Npobanks_Controller extends Controller
	{
		public  $NPO_EIN,$BankID,$NPO_BD_EIN,$tpl,$sortParam,$filterParam,$FieldArr,$BD_bankName,
				$BD_accName,$BD_accNumber,$BD_accType,$BD_phone,$BD_emailAddress,$BD_preferedPaymentMode,$BD_bankAddress;
 
		public  $pageSelectedPage,$totalRecord,$pageLimit;

		public  $P_ErrorCode,$P_status,$P_ErrorMessage,$P_ConfirmCode,$P_ConfirmMsg,$MsgType;

		function __construct()
		{
			//checkLogin(13);
			$this->load_model('NpoBanks','objBDetails');
			$this->load_model('Common','objCMN');
			$this->LastUpdateBy = getsession("DonasityAdminLoginDetail","admin_fullname");
			$this->P_status=1;		
		}
		
		public function index($type='list-bank', $npoEIN=NULL,$npoContactID='0',$Result=NULL)
		{
			
			if(isset($npoEIN) && $npoEIN!=NULL)
			{
				$this->NPO_EIN	= keyDecrypt($npoEIN);
			}
			if(isset($npoContactID) && $npoContactID!=NULL && $npoContactID>'0')
			{
				$this->BankID = keyDecrypt($npoContactID);
			}
			$this->tpl 			= new view;
			switch(strtolower($type))
			{
				case 'add-bank':
					$this->Insert();				
					break;	
				case 'edit-bank':
					$this->Listing();
					$this->Edit();
					$this->tpl->draw('npobanks/listbanks');
					break;
				case 'update-bank':
					 $this->Update();
					  break;
				case 'list-bank':
					$this->Listing();
					$this->tpl->assign("Result",$Result);
					$this->tpl->draw('npobanks/listbanks');
				break;
				case 'delete-bankdetail':
					$this->DeleteBankDetail();
				break;
			}
			
		}
		
		private function DeleteBankDetail()
		{
			$this->NPO_EIN = request('post','NPO_EIN',0);
			$Npo_ID = request('post','chk',3);
			$Npo_ID = implode(',',$Npo_ID);
			if($Npo_ID!=NULL)
			{
				if($this->objBDetails->DeleteBank_DB($Npo_ID))
				{
					$this->SetStatus(true,'C13003');
				}
				else
				{
					$this->SetStatus(false,'E13009');
				}
			}
			else
			{
				$this->SetStatus(false,'E13010');
			}
			
			redirect(URL."npobanks/index/list-bank/".$this->NPO_EIN);
		}
		
		private function Listing()
		{
			EnPException::writeProcessLog('Npobanks_Controller :: Listing action to view all Listing');
			
			if($this->NPO_EIN!=NULL)
			{
				if(!is_numeric($this->NPO_EIN))
				{
					$this->SetStatus(false,'E2001');
					redirect(URL."home/");
				}
				
				$NpoDataArray       = 	array("NPO_EIN","NPO_Name","NPO_ICO","NPO_ID");
				$Where              = 	array("NPO_EIN"=>$this->NPO_EIN);
				$NpoDetail 			= 	$this->objCMN->GetNPODetail($NpoDataArray,$Where,$this->sortParam);
				//dump($NpoDetail);
					
				$DataArray			=	array("SQL_CACHE NPO_BD_ID as ID","NPO_BD_EIN","NPO_BD_BankName","NPO_BD_BankAddress","NPO_BD_Phone","NPO_BD_EmailAddress","NPO_BD_AccountType","NPO_BD_AccountName","NPO_BD_AccountNumber","NPO_BD_PreferredPaymentMode","NPO_BD_CreatedDate","NPO_BD_LastUpdatedDate","NPO_BD_LastUpdatedBy","NPO_BD_DefaultDetail");
				$this->filterParam	=	array("NPO_BD_EIN"=>$NpoDetail['NPO_EIN']);
				
				$BDList 			= 	$this->objBDetails->GetBankDetailListing($DataArray,$this->filterParam,$this->sortParam);
				//dump($BDList);
			
				
				$PagingArr			=	constructPaging($this->objBDetails->pageSelectedPage,$this->objBDetails->totalRecord,$this->objBDetails->pageLimit);		
				$LastPage 			= 	ceil($this->objBDetails->totalRecord/$this->objBDetails->pageLimit);
				
				$PaymentMode     	= 	$GLOBALS['paymentmode'];
				$accountType		=   $GLOBALS['accounttype'];
				
				$this->tpl->assign("totalRecords",$this->objBDetails->totalRecord);
				$this->tpl->assign("BList",$BDList);
				$this->tpl->assign("PaymentMode",$PaymentMode);
				$this->tpl->assign("AccountType",$accountType);
				$this->tpl->assign("NpoDetail",$NpoDetail);
				$this->tpl->assign("action","add-bank");
				$this->tpl->assign("PagingList",$PagingArr['Pages']);
				$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
				$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
				$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
				$this->tpl->assign("lastPage",$LastPage);
				$this->tpl->assign("CD_EINID",$NpoDetail['NPO_EIN']);
				$this->tpl->assign("NPO_EIN",$this->NPO_EIN);
			}
			else
			{
				redirect(URL."home/");
			}
		}
		
		private function Insert()
		{
			$this->getFormData();
			$this->ValidateFormData();
			//if($this->P_status){$this->CheckDuplicacyForEmail('');}
			
			if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."npobanks");}
			$InsertID	= $this->objBDetails->InsertBankDetail_DB(TBLPREFIX.'npobankdetails',$this->FieldArr);
			
			if($InsertID!=NULL && $InsertID>0)
			{
				$this->SetStatus(true,'C13001');
				redirect(URL."npobanks/index/edit-bank/".keyEncrypt($this->NPO_EIN)."/".keyEncrypt($InsertID));
			}
			else
			{
				$this->SetStatus(false,'E13007');
				redirect(URL."npobanks");
			}	
		}
		
		private function getFormData()
		{
			EnPException::writeProcessLog('Npobanks_Controller :: getFormData action to get all data');
			$this->BankID					= request('post','BD_ID',1);
			$this->NPO_BD_EIN				= request('post','NPO_BD_EIN',1);
			$this->NPO_EIN					= request('post','NPO_EIN',1);
			
			$this->BD_bankName 				= request('post','BD_bankName',0);
			$this->BD_accName 				= request('post','BD_accName',0);
			$this->BD_accNumber 			= request('post','BD_accNumber',0);
			$this->BD_accType 				= request('post','BD_accType',0);
			$this->BD_phone 				= request('post','BD_phone',0);
			$this->BD_emailAddress 			= request('post','BD_emailAddress',0);
			$this->BD_preferedPaymentMode 	= request('post','BD_preferedPaymentMode',0);
			$this->BD_bankAddress 			= request('post','BD_bankAddress',0);
			$this->BD_bankAddress 			= request('post','BD_bankAddress',0);
			$this->BD_DefaultBankingDetail	= request('post','BD_DefaultBDetail','1');
			
			$TodayDate						= getdatetime();
			
			$this->FieldArr					= array("NPO_BD_EIN"=>$this->NPO_BD_EIN,"NPO_BD_BankName"=>$this->BD_bankName,"NPO_BD_BankAddress"=>$this->BD_bankAddress,
													"NPO_BD_Phone"=>$this->BD_phone,"NPO_BD_EmailAddress"=>$this->BD_emailAddress,"NPO_BD_AccountType"=>$this->BD_accType,
									  				"NPO_BD_AccountName"=>$this->BD_accName,"NPO_BD_AccountNumber"=>$this->BD_accNumber,"NPO_BD_PreferredPaymentMode"=>$this->BD_preferedPaymentMode,
													"NPO_BD_CreatedDate"=>$TodayDate,"NPO_BD_LastUpdatedDate"=>$TodayDate,"NPO_BD_LastUpdatedBy"=>$this->LastUpdateBy,"NPO_BD_DefaultDetail"=>$this->BD_DefaultBankingDetail);
		}
		
		private function ValidateFormData()
		{
			if($this->FieldArr['NPO_BD_BankName']==NULL){$this->SetStatus(0,'E13001');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPO_BD_BankAddress']==NULL){$this->SetStatus(0,'E13006');redirect($_SERVER['HTTP_REFERER']);}
			if($this->FieldArr['NPO_BD_EmailAddress']!=NULL)
				if(!filter_var($this->FieldArr['NPO_BD_EmailAddress'], FILTER_VALIDATE_EMAIL)){$this->SetStatus(0,'E13005');redirect($_SERVER['HTTP_REFERER']);}
			
			//elseif($this->FieldArr['NPO_BD_AccountName']==NULL){$this->SetStatus(0,'E13002');redirect($_SERVER['HTTP_REFERER']);}
			//elseif($this->FieldArr['NPO_BD_AccountNumber']==NULL){$this->SetStatus(0,'E13003');redirect($_SERVER['HTTP_REFERER']);}
			//elseif($this->FieldArr['NPO_BD_AccountType']==NULL){$this->SetStatus(0,'E13004');redirect($_SERVER['HTTP_REFERER']);}
			
			
		}
		
		private function CheckDuplicacyForEmail($condition)
		{
			EnPException::writeProcessLog('Npobanks_Controller :: CheckDuplicacyForEmail Function To Check Duplicate Email');
			$KeywordStatus=TRUE;
			if(trim($this->FieldArr['NPO_BD_EmailAddress'])<>'')
			{
				$searchField = " WHERE (NPO_BD_EmailAddress='".$this->FieldArr['NPO_BD_EmailAddress']."')";
				$EmailDetail = $this->objBDetails->CheckDuplicacyForEmail($condition,$searchField);
				if(count($EmailDetail)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E12011');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E12003');
			}
			return $KeywordStatus;	
		}
		
		private function Edit()
		{
			if(!is_numeric($this->NPO_EIN) || !is_numeric($this->BankID))
		  	{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
		  	}
			$DataArray			=	array("SQL_CACHE NPO_BD_ID as ID","NPO_BD_EIN","NPO_BD_BankName","NPO_BD_BankAddress","NPO_BD_Phone","NPO_BD_EmailAddress","NPO_BD_AccountType","NPO_BD_AccountName","NPO_BD_AccountNumber","NPO_BD_PreferredPaymentMode","NPO_BD_CreatedDate","NPO_BD_LastUpdatedDate","NPO_BD_LastUpdatedBy","NPO_BD_DefaultDetail");
												
			$where      		= 	array("NPO_BD_ID"=>$this->BankID);
			
			$BankDetail		= 	$this->objBDetails->GetBankDetailListing($DataArray,$where);
			
			
			$this->tpl->assign("action",'update-bank');
			$this->tpl->assign("ContactID",$this->BankID);
			$this->tpl->assign("BankDetail",$BankDetail[0]);
		}
		
		private function Update()
		{
			EnPException::writeProcessLog('Npobanks_Controller :: Update action to Update bank details ');
			try
			{	
				$this->getFormData();
				$this->ValidateFormData();
				//$Condition = " AND NPO_CD_ID!= ".$this->ContactID;
				//if($this->P_status) $this->CheckDuplicacyForEmail($Condition);
				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."npobanks/index/edit-bank/".keyEncrypt($this->NPO_EIN));}
				
				$Status	=	$this->objBDetails->UpdateBanksDetail_DB($this->FieldArr,$this->BankID);
				
				if($Status)
				{
					$this->SetStatus(true,'C13002');
											
				}
				else
				{
					$this->SetStatus(false,'E13008');
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."npobanks/index/edit-bank/".keyEncrypt($this->NPO_EIN)."/".keyEncrypt($this->BankID));
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Npobanks_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status=0;
			$this->P_ErrorCode.=$ErrCode.",";
			$this->P_ErrorMessage=$ErrCode;
			$this->MsgType=$MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		{
			EnPException::writeProcessLog('Npobanks_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->P_status=1;
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->MsgType=$MsgType;
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