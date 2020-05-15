<?php
class Changepassword_Model extends Model
{
	public $uid;
	public function __construct()
	{
		$this->uid=getSession('DonasityLoginDetail','user_id');
	}	
	
	public function passchange($DataArray,$cpass)
	{
		
		db::update(TBLPREFIX.'registeredusers',$DataArray,"RU_ID = ".$this->uid." and RU_Password='".$cpass."'");
		return db::is_row_affected()?1:0;
	}
	
	public function passmatch($pass)
	{
		$pass=PassEnc($pass);
		$Sql="SELECT RU_Password FROM ".TBLPREFIX."registeredusers WHERE RU_Password='".$pass."' AND RU_ID=".$this->uid;
		//echo $Sql;exit;  
		$sql_res	=	db::get_row($Sql);
		return $sql_res['RU_Password']==NULL?false:true;
	}
	
	
}
?>