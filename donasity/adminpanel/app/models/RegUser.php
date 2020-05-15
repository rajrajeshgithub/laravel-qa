<?php
class RegUser_Model extends Model {
	
	public $pageLimit, $RightpageLimit, $pageSelectedPage, $MemberListTotalRecord, $DonorTotalRecord, $isExport = 0, $currentCsvPosition = 0, $totalRowProcessed = 0, $expCsvLimit = 50, $totalRecord = 0;
	
	public function __construct() {
		$this->pageLimit = 20;
		$this->RightpageLimit = 3;
		$this->pageSelectedPage = 1;
		$this->MemberListTotalRecord = 0;
	}
	
	public function GetRegUserListing($field = array(), $filterparam = array(), $arraySortParam = array(), $Pagenation = NULL) {
		EnPException :: writeProcessLog('RegUser_Model :: GetRegUserListing function called');
		$field = $this->getfieldName($field);
		$fieldString = implode(' , ', $field);			
		$filterString = " Where 1 ";	
		
		if(count($filterparam) > 0) {
			foreach($filterparam as $key => $row) {
				$cond = '';
				switch($key) {
					case 'SearchCondtionLike' : 
						$cond = " $row ";
					break;
					case 'RU_Status':
						$cond = "$key='$row'";
					break;
					case 'RU_Deleted':
						$cond = "$key='$row'";
					break;
					default:
						$cond="$key=$row";
					break;
				}
				$filterString .= " AND ( $cond ) ";
			}
		}
		
		$Sql = "Select $fieldString from " . TBLPREFIX . "registeredusers RU LEFT JOIN " . TBLPREFIX . "states S ON (RU.RU_State=S.State_Value AND RU_Country=State_Country AND RU_State <> '' AND RU_Country <> '')";
				
		//$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		//$limit = " limit " . $limit . "," . $this->pageLimit;
		//dump($Sql . $filterString);
		$this->DonorTotalRecord = db :: count($Sql . $filterString);
		
		$sql_res = array();
		if($this->DonorTotalRecord > 0) {
			$limit = $this->isExport == 1 ? $this->SetExportPageLimit() : $this->SetLimit();
			$sql_res = db :: get_all($Sql . $filterString . $limit);
		}
		
		if(!count($sql_res)) 
			$sql_res = array(); 
			
		return $sql_res;
	}
	
	// set page limit
	private function SetLimit() {
		return " LIMIT " . $this->pageLimit * ($this->pageSelectedPage - 1) . "," . $this->pageLimit;
	}
	
	// set csv export page limit
	private function SetExportPageLimit() {
		return " LIMIT " . $this->currentCsvPosition . ", " . $this->expCsvLimit;
	}
	
	/*public function Test_DB($Table,$DataArray)
	{
		
		$str = "Hello";
		echo md5($str,true);exit;
		$hash = hash_file('crc32b', '123456');
		echo $hash;exit;
		$array = unpack('N', pack('H*', $hash));
		$crc32 = $array[1];
		dump($crc32);
		/*$passwordFromPost = '1234567';
		$hash 	=	hash('ripemd160', '1234567');
		if($hash==='d8913df37b24c97f28f840114d05bd110dbb2e44')
		{
			echo "password Verify";
		}
		exit;*/
		/*if (password_verify($passwordFromPost, $hashed)) {
				echo 'Password is valid!';
			} else {
				echo 'Invalid password.';
			}
		exit;
		$sql = "SELECT * FROM dns_registeredusers WHERE RU_Password='$hashed'";
		echo $sql;exit; 
		
		//echo $hashed;exit;
		$DataArray = array("RU_FistName" => 'test1',"RU_LastName" => 'test1',"RU_CompanyName" => 'Test1',"RU_EmailID" => 'test1@gmail.com',"RU_Password" => $hash);
		
   		db::insert('dns_registeredusers',$DataArray);
		//return db::get_last_id();
	}*/
	
	
	public function RegUserInsertMethod_DB($Table, $DataArray) {
		EnPException :: writeProcessLog('RegUser_Model :: RegUserInsertMethod_DB function called');
		db :: insert($Table, $DataArray);
		return db :: get_last_id();
	}
	
	public function UpdateDonorDetail_DB($DataArray, $RID) {
		EnPException :: writeProcessLog('RegUser_Model :: UpdateDonorDetail_DB function called');
		db :: update(TBLPREFIX . 'registeredusers', $DataArray, "RU_ID = " . $RID);
		return db :: is_row_affected() ? 1 : 0;
	}
	
	public function DeleteDonorDetail($Where) {
		EnPException :: writeProcessLog('RegUser_Model :: DeleteDonorDetail function called');
		db :: delete(TBLPREFIX . "registeredusers", $Where);
		return db :: is_row_affected() ? 1 : 0;
	}
	
	public function CheckDuplicacyForEmail($condition = '', $searchField) {
		EnPException :: writeProcessLog('RegUser_Model :: CheckDuplicacyForEmail function called');
		$sql = "SELECT RU_EmailID FROM " . TBLPREFIX . "registeredusers";
		return $row = db :: get_all($sql . $searchField . $condition);
	}
	
	public function CheckDuplicacyForUserName($condition='',$searchField) {
		EnPException :: writeProcessLog('RegUser_Model :: CheckDuplicacyForUserName function called');
		$sql = "SELECT RU_UserName FROM " . TBLPREFIX . "registeredusers";
		return $row = db :: get_all($sql . $searchField . $condition);
	}
	
	public function CheckDuplicacyFacebookID($condition = '', $searchField) {
		EnPException :: writeProcessLog('RegUser_Model :: CheckDuplicacyForUserName function called');
		$sql = "SELECT RU_FacebookID FROM " . TBLPREFIX . "registeredusers";
		return $row = db :: get_all($sql . $searchField . $condition);
	}
	
	private function getfieldName($field) {
		EnPException :: writeProcessLog('RegUser_Model :: getfieldName function called');
		foreach($field as $key => &$row) {
			switch($row) {
				default:
				$row = "$row";
			}
		}
		return $field;
	}
	public function GetUserDetails($field,$condition='')
	{
		$fieldString = implode(' , ', $field);
		$Sql = "Select $fieldString from " . TBLPREFIX . "registeredusers RU where 1=1 ";	
		
		if($condition!='')
		$Sql  .= $condition;
		
		$res = db::get_row($Sql);
		$res = count($res>0)?$res:array();
		return $res;	
	}
}
?>