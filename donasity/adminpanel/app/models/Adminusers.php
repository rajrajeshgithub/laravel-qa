<?php
	class AdminUsers_Model extends Model
	{
		function __construct()
		{
				
		}	
		
		public function InsertAdminUserDB($DataArray)
		{
			db::insert(TBLPREFIX."adminusers",$DataArray);
			return db::get_last_id();
		}
		
		public function AdminUserDetailDB($Array,$AdminUserID)
		{
			$fields	= implode(",",$Array);
			$Where	= " WHERE Admin_ID=".$AdminUserID;	
			$Sql	= "SELECT $fields FROM ".TBLPREFIX."adminusers";
			$Res	= db::get_row($Sql.$Where);
			return (count($Res)>0)?$Res:array();
		}
		
		public function UpdateAdminUserDB($DataArray,$AdminUserID)
		{
			return db::update(TBLPREFIX."adminusers",$DataArray,"Admin_ID=".$AdminUserID);	
		}
		
		public function AdminUserListDB($Array,$Cond,$Order)
		{
			$fields	= implode(",",$Array);
			$Sql	= "SELECT $fields FROM ".TBLPREFIX."adminusers";
			//echo $Sql.$Cond.$Order;exit;
			$Res	= db::get_all($Sql.$Cond.$Order);//dump($Res);//echo $Sql.$Cond.$Order;
			return (count($Res)>0)?$Res:array();
		}
		
		public function DeleteAdminUserDB($AdminUserID)
		{
			return db::delete(TBLPREFIX."adminusers","Admin_ID=".$AdminUserID);	
		}
		
		public function CheckEmailAddressDB($EmailAddress,$UserID)
		{
			$Where	= " WHERE Admin_EmailID='".$EmailAddress."'";
			if($UserID > 0)$Where.=" AND Admin_ID <> ".$UserID;
			$Sql	= "SELECT Admin_ID FROM ".TBLPREFIX."adminusers";
			$Res	= db::get_row($Sql.$Where);
			return ($Res['Admin_ID'] > 0)?false:true;
		}
		
		public function CheckUserNameDB($UserName,$UserID)
		{
			$Where	= " WHERE Admin_UserName='".$UserName."'";
			if($UserID > 0)$Where.=" AND Admin_ID <> ".$UserID;
			$Sql	= "SELECT Admin_ID FROM ".TBLPREFIX."adminusers";
			$Res	= db::get_row($Sql.$Where);
			return ($Res['Admin_ID'] > 0)?false:true;
		}
	}

?>