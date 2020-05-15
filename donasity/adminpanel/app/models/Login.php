<?php 
class Login_Model extends Model {
	
	public $adminDetail = array(), $L_username, $L_password, $L_LastAccessDate, $L_userid, $L_ErrorCode, $L_ErrorMessage, $L_ConfirmCode, $L_ConfirmMsg, $L_status = 1, $IPExist = 0;
	
	public function processLogin_DB($fields, $condition, $orderby) {
		EnPException :: writeProcessLog('Login_Model :: processLogin Function For Login');
		
		$getFields = implode(',', $fields);
		$sql = "SELECT $getFields FROM " . TBLPREFIX . "adminusers where 1 $condition $orderby";
		$res = db :: get_row($sql);
		if(!count($res))
			$res = array();
		return $res;
	}
	
	public function UpdateLoginDate($DataArray, $AdminUserID) {
		EnPException :: writeProcessLog('Login_Model :: UpdateLoginDate Function For Login');
		
		return db :: update(TBLPREFIX . 'adminusers', $DataArray, 'Admin_ID=' . $AdminUserID);
	}
		
	public function ResetPassword_DB($fields, $condition, $orderby) {
		EnPException :: writeProcessLog('Login_Model :: ResetPassword_DB Function For Login');
		
		$getFields = implode(',', $fields);
		$sql = "SELECT $getFields FROM " . TBLPREFIX . "adminusers where 1 $condition $orderby";
		
		$res = db :: get_all($sql);
		return $res;
	}
	
	public function UpdatePassword($DataArray, $where) {
		EnPException :: writeProcessLog('Login_Model :: ResetPassword_DB Function For Login');
		db :: update(TBLPREFIX . 'adminusers', $DataArray, $where);
		return db :: is_row_affected() ? 1 : 0;
	}
}
?>