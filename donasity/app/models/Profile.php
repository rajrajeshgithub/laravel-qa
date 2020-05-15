<?php
class Profile_Model extends Model
{
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=20;
		$this->RightpageLimit=3;
		$this->pageSelectedPage=1;
		$this->MemberListTotalRecord=0;
	}
	
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('Donor_Model :: getfieldName function called');
		foreach($field  as $key =>  &$row)
		{
			switch($row)
			{
				default:
				$row="$row";
			}
		}
		return $field;
	}
	
	public function GetRegUserListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('RegUser_Model :: GetRegUserListing function called');
		$field=$this->getfieldName($field);
		$fieldString=implode(' , ',$field);			
		$filterString=" Where 1=1 ";	
		if(count($filterparam)>0)
		{
			foreach($filterparam as  $key => $row)
			{
				$cond="";
				switch($key)
				{
					case 'SearchCondtionLike':
					$cond=" $row ";
					break;
					case 'RU_Status':
					$cond="$key='$row'";
					break;
					case 'RU_Deleted':
					$cond="$key='$row'";
					break;
					default:
					$cond="$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."registeredusers";
		
		$limit 									=	 $this->pageLimit * ($this->pageSelectedPage-1);
		$limit									=	" limit ".$limit.",".$this->pageLimit;
		
		$this->DonorTotalRecord				=	db::count($Sql.$filterString);
		//echo $Sql.$filterString.$limit;exit;
		if($this->DonorTotalRecord>0)
		{
			$sql_res							=	db::get_all($Sql.$filterString.$limit);
		}
		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
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
	
	
	/*public function RegUserInsertMethod_DB($Table,$DataArray)
	{
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}*/
	
	public function UpdateDonorDetail_DB($DataArray,$RID)
	{
		EnPException::writeProcessLog('RegUser_Model :: UpdateDonorDetail_DB function called');
		db::update(TBLPREFIX.'registeredusers',$DataArray,"RU_ID = ".$RID);
		return db::is_row_affected()?1:0;
	}
	
	public function DeleteDonorDetail($Where)
	{
		db::delete(TBLPREFIX."registeredusers",$Where);
		return db::is_row_affected()?1:0;
	}
	
	public function CheckDuplicacyForEmail($condition='',$searchField)
	{
		$sql="SELECT RU_EmailID FROM ".TBLPREFIX."registeredusers";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	public function CheckDuplicacyForUserName($condition='',$searchField)
	{
		$sql="SELECT RU_UserName FROM ".TBLPREFIX."registeredusers";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	public function CheckDuplicacyFacebookID($condition='',$searchField)
	{
		$sql="SELECT RU_FacebookID FROM ".TBLPREFIX."registeredusers";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	function getCountriesList()
	{
		$sql = "SELECT Country_ID,Country_Title,Country_Abbrivation,Country_Active
				FROM ".TBLPREFIX."country ORDER BY Country_Title";
		return db::get_all($sql);
	}
	
	function getStateList($CountryID=NULL)
	{
		$WHERE	= "";
		if($CountryID != NULL)
		{
			$WHERE	= 	"WHERE State_Country='".$CountryID."'";
		}
		$sql = "SELECT State_ID, State_Country,State_Name,State_Value,State_Active,Country_ID
				FROM ".TBLPREFIX."states $WHERE ORDER BY State_Name";
		//echo $sql;exit;		
		return db::get_all($sql);
	}
	
	public function updateProcessLog($DataArray) {
		$FieldArray = array(
			"DateTime"			=> $DataArray['Date'],
			"ModelName"			=> $DataArray['Model'],
			"ControllerName"	=> $DataArray['Controller'],
			"UserType"			=> $DataArray['UType'],
			"UserName"			=> $DataArray['UName'],
			"UserID"			=> $DataArray['UID'] = ($DataArray['UID']!='')?$DataArray['UID']:0,
			"RecordID"			=> $DataArray['RecordId'] = ($DataArray['RecordId']!='')?$DataArray['RecordId']:0,								
			"SortMessage"		=> $DataArray['SMessage'],
			"LongMessage"		=> $DataArray['LMessage']);							
		
		db::insert(TBLPREFIX . "processlog", $FieldArray);
		$id = db::get_last_id();
		$id = ($id) ? $id : 0;
		return $id;
	}
}

?>