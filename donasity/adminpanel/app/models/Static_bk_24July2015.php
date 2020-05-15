<?PHP
class Static_Model extends Model
{
	public $Language,$Title,$SortOrder,$Status,$GroupID,$PageID,$P_Status=1; 
	public $ErrorMessage,$ErrorCode,$ConfirmCode,$ConfirmMsg,$MsgType=2;
	public $ErrorCodes='';
	public function InsertGroup($DataArray)
	{
		EnPException::writeProcessLog('Static_Model :: InsertGroup Function');
		db::insert(TBLPREFIX.'cmspagegroup',$DataArray);
		$GroupID = db::get_last_id();
		return $GroupID;
	}
	
	public function UpdateGroup($DataArray,$GroupID)
	{
		EnPException::writeProcessLog('Static_Model :: UpdateGroup Function');
		$data=array('Title'	=> $this->Title,'SortingOrder'=> $this->SortOrder,'Status'=> $this->Status,'Language'=> $this->Language);
		if(db::update(TBLPREFIX.'cmspagegroup',$DataArray,'CMSPageGroupID='.$GroupID))
		{
			return 1;
		}
		else
		{
			return 0;	
		}
	}
	
	public function GetGroup($Array,$Cond='',$Order='')
	{
		$Where	= " WHERE 1=1".$Cond;
		$Fields	= implode(',',$Array);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."cmspagegroup";
		$Res	= db::get_all($Sql.$Where.$Order);
		return (count($Res)>0)?$Res:array();
	}	
	
	public function DeleteGroup($GroupID)
	{
		if(db::delete(TBLPREFIX."cmspagegroup","CMSPageGroupID=".$GroupID))	
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function InsertPage($DataArray)
	{
		db::insert(TBLPREFIX.'cmspages',$DataArray);	
		$PageID	= db::get_last_id();
		return $PageID;
	}
	
	public function GetPages($Array,$Cond='',$Order='')
	{
		$fields	= implode(',',$Array);
		$Where	= " WHERE 1=1 ";
		$Where	= $Where.$Cond;
		$Sql	= "SELECT $fields FROM ".TBLPREFIX."cmspages as CP INNER JOIN ".TBLPREFIX."cmspagegroup AS CPG ON CP.CMSPageGroupID = CPG.CMSPageGroupID";//echo $Sql.$Where.$Order;
		$Res	= db::get_all($Sql.$Where.$Order);	
		return (count($Res)>0)?$Res:array();
	}
	
	public function DeletePage($PageID)
	{
		if(db::delete(TBLPREFIX."cmspages","CMSPagesID=".$PageID))	
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function GetPageDetail($Array,$Condition)
	{
		$fields	= implode(',',$Array);
		$Where	= " WHERE 1=1";
		$Where	= $Where.$Condition;
		$Sql	= "SELECT $fields FROM ".TBLPREFIX."cmspages";
		$row	= db::get_row($Sql.$Where);
		return $row;	
	}
	
	public function UpdatePage($DataArray,$PageID)
	{
		return db::update(TBLPREFIX.'cmspages',$DataArray,"CMSPagesID=".$PageID);
	}

	public function CheckDuplicacyForCMSGroup($condition)
	{
		$Where	= " WHERE 1=1";
		$Where	= $Where.$condition;
		$sql	= "SELECT CMSPageGroupID FROM ".TBLPREFIX."cmspagegroup";	
		$row	= db::get_row($sql.$Where);//echo $sql.$Where;exit;
		return  ($row['CMSPageGroupID'] > 0)?false:true;exit;
	}
	
	public function CheckPageNameDuplicacy($condition)
	{
		$Where	= " WHERE 1=1";
		$Where	= $Where.$condition;
		$sql	= "SELECT CMSPagesID FROM ".TBLPREFIX."cmspages";	
		$row	= db::get_row($sql.$Where);//echo $sql.$Where;exit;
		return  ($row['CMSPagesID'] > 0)?false:true;exit;	
	}
	
	public function CheckURLDuplicacy($condition)
	{
		$Where	= " WHERE 1=1";
		$Where	= $Where.$condition;
		$sql	= "SELECT CMSPagesID FROM ".TBLPREFIX."cmspages";	
		$row	= db::get_row($sql.$Where);//echo $sql.$Where;exit;
		return  ($row['CMSPagesID'] > 0)?false:true;exit;	
	}
	
	private function setErrorMsg($ErrCode,$MsgType=1,$P_Status=0)
	{
		EnPException::writeProcessLog('Static_Model :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->ErrorCode.=$ErrCode.",";
			$this->ErrorMessage=$ErrCode;
			$this->P_Status=$P_Status;
			$this->MsgType=$MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode,$MsgType=2,$P_Status=1)
	{
		EnPException::writeProcessLog('Static_Model :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->ConfirmCode=$ConfirmCode;
			$this->ConfirmMsg=$ConfirmCode;
			$this->P_Status=$P_Status;
			$this->MsgType=$MsgType;
	}	
	
	
}
?>