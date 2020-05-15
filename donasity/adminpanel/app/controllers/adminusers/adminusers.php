<?php
	class Adminusers_Controller extends Controller
	{
		public $tpl;
		public $DataArray=array();
		public $AdminUserID;
		public $AdminName, $loginDetail = array();
		
		function __construct()
		{
			checkLogin(11);
			$this->load_model("Adminusers","objadm");
			$this->tpl	= new view;	
			$this->showmsg();
			$this->AdminName = getSession('DonasityAdminLoginDetail','admin_fullname');
			$this->loginDetail = getSession('DonasityAdminLoginDetail');
		}	
		
		public function index($action='listing',$AdminID=NULL)
		{
			$this->AdminUserID	= keyDecrypt($AdminID);
			switch(strtolower($action))
			{
				case 'add-admin-user':
					$this->showAddForm();
					break;
				case 'insert-admin-user':
					$this->InsertAdminUser();
					break;
				case 'edit-admin-user':
					$this->showEditForm();
					break;
				case 'update-admin-user':
					$this->UpdateAdminUser();
					break;	
				case "delete-admin-user":
					$this->DeleteAdminUser();
					break;
				default:
					$this->AdminUserList();
					break;	
			}	
		}
		
		private function DeleteAdminUser()
		{
			if($this->objadm->DeleteAdminUserDB($this->AdminUserID))
			{
				$this->setConfirmationMsg('C8003');
			}
			else
			{
				$this->setErrorMsg('E8008');	
			}
			redirect(URL."adminusers");
		}
		
		private function FilterUsers()
		{
			$Condition 		= "";
			$SearchKeyword	= request('post','titlestr',0);
			$Status			= request('post','status',1);
			$Fields			= request('post','searchFields',0);
			
			if(isset($_POST['status']))
			{
				$Condition	= " AND Admin_Status='".$Status."'";
			}
			
			if(trim($SearchKeyword) != '')
			{
				switch($Fields)
				{
					case "Admin_FirstName":
					case "Admin_LastName":
					case "Admin_EmailID":
					$Condition.=" AND ($Fields LIKE '".$SearchKeyword."%')";
					break;
					default:
					$Condition.=" AND (Admin_FirstName LIKE  '".$SearchKeyword."%' OR Admin_LastName LIKE '".$SearchKeyword."%' OR Admin_EmailID LIKE  '".$SearchKeyword."%')";	
				}	
			}
			$this->tpl->assign('searchtitle',$SearchKeyword);
			$this->tpl->assign('status',$Status);
			$this->tpl->assign('Field',$Fields);
			return $Condition;
		}
		
		private function AdminUserList()
		{
			$Condition 	= $this->FilterUsers();
			$Array	= array("Admin_ID","CONCAT(Admin_FirstName,' ',Admin_LastName) AS AdminName","Admin_UserName","Admin_EmailID","Admin_Status");
			$Cond	= " WHERE 1=1 ".$Condition;
			$Order	= " ORDER BY Admin_LastUpdatedDate DESC";
			$List	= $this->objadm->AdminUserListDB($Array,$Cond,$Order);
			$this->tpl->assign('list',$List);
			$this->tpl->draw('adminusers/userList');
		}
		
		private function showAddForm()
		{
			
			$arrModule = getModuleArray();
			$CMSArray	= array('1'=>"CMS1",'2'=>"CMS2",'3'=>"CMS3",'4'=>"CMS4",'5'=>"CMS5",'6'=>"CMS6",'7'=>"CMS7",'8'=>"CMS8",'9'=>"CMS9",'10'=>"CMS10",'11'=>"CMS11",'12'=>"CMS12",
								'13'=>"CMS13");
			$AccessPeriod	= array("0"=>"Unlimited","30"=>30,"60"=>60,"90"=>90);	
			$this->tpl->assign('case','addtadmin');					
			$this->tpl->assign('cms',$CMSArray);
			$this->tpl->assign('accessperiod',$AccessPeriod);
			$this->tpl->assign('arrModule',$arrModule);	
			$this->tpl->draw('adminusers/addForm');
		}
		
		private function showEditForm()
		{
			$this->load_model('Common', 'objCom');
			
			$arrField  = array("Module_ID","Module_ParentID","Module_Style","Module_Desc","Module_Caption","Module_Url");
			$filterWhere = " Where Module_Active='1' ";
			
			$arrModule = $this->objCom->getModuleListDB($arrField, $filterWhere);
			$AdminUserDetails	= $this->AdminUserDetails();
			$AdminUserDetails['Admin_AccessModuleIDs']	= explode(',',$AdminUserDetails['Admin_AccessModuleIDs']);
			$AccessPeriod	= array("0"=>"Unlimited","30"=>30,"60"=>60,"90"=>90);
			$this->tpl->assign('case','editadmin');
			$this->tpl->assign('AdminUserID',$this->AdminUserID);
			$this->tpl->assign('AdminUserDetails',$AdminUserDetails);
			$this->tpl->assign('accessperiod',$AccessPeriod);
			$this->tpl->assign('arrModule',$arrModule);
			$this->tpl->assign('loginDetail', $this->loginDetail);
			$this->tpl->draw('adminusers/addForm');
		}
		
		private function AdminUserDetails()
		{
			$Array	= array("Admin_FirstName,Admin_LastName,Admin_EmailID,Admin_UserName,Admin_Password,Admin_AccessModuleIDs,Admin_AccessPeriod,Admin_LoginWithIP,Admin_AccessIPAddress,
							Admin_Remarks,Admin_Status,Admin_LastUpdatedBy,Admin_AddedDate,Admin_LastUpdatedDate","Admin_Type");
			return $this->objadm->AdminUserDetailDB($Array,$this->AdminUserID);
		}
		
		private function AdminInputData()
		{
			$FirstName			= request('post','firstName',0);
			$LastName			= request('post','lastName',0);	
			$EmailAddress		= request('post','emailAddress',0);	
			$UserName			= request('post','userName',0);	
			$Password			= request('post','password',0);	
			$Password			= PassEnc($Password);
			$IsActive			= request('post','isActive',1);	
			$AdminType			= request('post','admintype',1);
			if($AdminType==1)
			 $Module			= request('post','module',3);
			 elseif($AdminType==2)
			 $Module			= request('post','module',3);
			 elseif($AdminType==3)
			 $Module			= request('post','module',3);
			
			//$Module				= request('post','module',3);
			$Module				= implode(",",$Module);
			$AccessPeriod		= request('post','accessPeriod',1);
			$LoginWithIP		= request('post','loginWithIP',1);
			$LoginIP			= request('post','loginIPAddresses',0);	
			$Remark				= request('post','remark',0);	
			$UpdateBy			= $this->AdminName;
			$AddedDate			= getDateTime();
			$UpdateDate			= getDateTime();
			$this->AdminUserID	= request('post','adminuserid',1);	
			$this->DataArray	= array("Admin_FirstName"=>$FirstName,"Admin_LastName"=>$LastName,"Admin_EmailID"=>$EmailAddress,"Admin_UserName"=>$UserName,
										"Admin_Password"=>$Password,"Admin_AccessModuleIDs"=>$Module,"Admin_AccessPeriod"=>$AccessPeriod,"Admin_LoginWithIP"=>$LoginWithIP,
										"Admin_AccessIPAddress"=>$LoginIP,"Admin_Remarks"=>$Remark,"Admin_Status"=>$IsActive,"Admin_LastUpdatedBy"=>$UpdateBy,
										"Admin_AddedDate"=>$AddedDate,"Admin_LastUpdatedDate"=>$UpdateDate,"Admin_Type"=>$AdminType);
			//dump($this->DataArray);							
			if($this->AdminUserID > 0)unset($this->DataArray['Admin_AddedDate']);
		}
		
		private function ValidateData()
		{
			$DecryptPass		= request('post','password',0);
			if(trim($this->DataArray['Admin_FirstName']) == ''){$this->setErrorMsg('E8002');redirect($_SERVER['HTTP_REFERER']);}
			if(trim($this->DataArray['Admin_LastName']) == ''){$this->setErrorMsg('E8003');redirect($_SERVER['HTTP_REFERER']);}
			if(trim($this->DataArray['Admin_EmailID']) == ''){$this->setErrorMsg('E8004');redirect($_SERVER['HTTP_REFERER']);}
			if(trim($this->DataArray['Admin_UserName']) == ''){$this->setErrorMsg('E8005');redirect($_SERVER['HTTP_REFERER']);}
			if(trim($this->DataArray['Admin_Password']) == ''){$this->setErrorMsg('E8006');redirect($_SERVER['HTTP_REFERER']);}
			if(trim(strlen($DecryptPass)) > 20){$this->setErrorMsg('E8009');redirect($_SERVER['HTTP_REFERER']);}
			if(trim(strlen($DecryptPass)) < 6){$this->setErrorMsg('E80010');redirect($_SERVER['HTTP_REFERER']);}
		}
		
		private function InsertAdminUser()
		{
			$this->AdminInputData();
			$this->ValidateData();
			$this->AdminUserID	= $this->objadm->InsertAdminUserDB($this->DataArray);
			if($this->AdminUserID > 0)
			{
				$this->setConfirmationMsg('C8001');
				redirect(URL."adminusers/index/edit-admin-user/".keyEncrypt($this->AdminUserID));
			}
			else
			{
				$this->setErrorMsg('E8001');
				redirect($_SERVER['HTTP_REFERER']);	
			}
		}
		
		private function UpdateAdminUser()
		{
			$this->AdminInputData();
			$this->ValidateData();
			if($this->objadm->UpdateAdminUserDB($this->DataArray,$this->AdminUserID))
			{
				$this->setConfirmationMsg('C8002');
				redirect($_SERVER['HTTP_REFERER']);
			}
			else
			{
				$this->setErrorMsg('E8007');
				redirect($_SERVER['HTTP_REFERER']);	
			}
		}
		
		private function setErrorMsg($ErrCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$errParams=array("errCode"=>$ErrCode,
							 "errMsg"=>"Custom Exception message",
							 "errOriginDetails"=>basename(__FILE__),
							 "errSeverity"=>1,
							 "msgDisplay"=>1,
							 "msgType"=>1);
			EnPException::setError($errParams);
		}
		
		private function setConfirmationMsg($ConfirmCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$confirmationParams=array("msgCode"=>$ConfirmCode,
										 "msgLog"=>1,									
										 "msgDisplay"=>1,
										 "msgType"=>2);
			$placeholderValues=array("placeValue1");
			EnPException::setConfirmation($confirmationParams, $placeholderValues);
		}
		
		private function showmsg()
		{
			$msgValues=EnPException::getConfirmation(false);			
			$this->tpl->assign('msgValues',$msgValues);
		}
		
		public function CheckEmailAddress()
		{
			$Status			= true;
			$UserID			= request('get','UserID',1);
			$EmailAddress	= request('get','emailAddress',0);
			$Status			= $this->objadm->CheckEmailAddressDB($EmailAddress,$UserID);
			echo json_encode($Status);
			exit;
		}
		
		public function CheckUserName()
		{
			$Status			= true;
			$UserID			= request('get','UserID',1);
			$UserName		= request('get','userName',0);
			$Status			= $this->objadm->CheckUserNameDB($UserName,$UserID);
			echo json_encode($Status);
			exit;	
		}
	}


}

?>