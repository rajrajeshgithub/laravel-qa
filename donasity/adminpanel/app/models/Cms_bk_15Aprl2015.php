<?PHP
class Cms_Model extends Model
{
	public $Language,$Title,$SortOrder,$Status,$GroupID,$PageID,$P_Status=1; 
	public $ErrorMessage,$ErrorCode,$ConfirmCode,$ConfirmMsg,$MsgType=2;
	public $ErrorCodes='';
	public function InsertGroup()
	{
		EnPException::writeProcessLog('CMS_Model :: InsertGroup Function call');	
		$this->validateInput();
		if($this->P_Status==1)$this->insert();
		/*db::insert(TBLPREFIX.'cmspagegroup',$DataArray);
		return db::get_last_id();*/
	}
	
	public function UpdateGroup()
	{
		EnPException::writeProcessLog('CMS_Model :: UpdateGroup Function call');	
		$this->validateInput();
		if($this->P_Status==1)$this->editupdategroup();
	}
	
	public function editupdategroup()
	{
		EnPException::writeProcessLog('CMS_Model :: UpdateGroup Function');
		$data=array('Title'	=> $this->Title,'SortingOrder'=> $this->SortOrder,'Status'=> $this->Status,'Language'=> $this->Language);
		if(db::update(TBLPREFIX.'cmspagegroup',$data,'CMSPageGroupID='.$this->GroupID))
		{
			$this->setConfirmationMsg('C1005');
		}
	}
	
	public function insert()
	{
		EnPException::writeProcessLog('CMS_Model :: InsertGroup Function');
		$data=array('Title'	=> $this->Title,'SortingOrder'=> $this->SortOrder,'Status'=> $this->Status,'Language'=> $this->Language);
		db::insert(TBLPREFIX.'cmspagegroup',$data);
		$this->GroupID = db::get_last_id();
		if($this->GroupID>0)$this->setConfirmationMsg('C1001');
	}
	
	
	public function GetGroup($Array,$Cond='',$Order='')
	{
		$Where	= " WHERE 1=1".$Cond;
		$Fields	= implode(',',$Array);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."cmspagegroup";
		$Res	= db::get_all($Sql.$Where.$Order);
		return (count($Res)>0)?$Res:array();
	}	
	
	public function DeleteGroup()
	{
		if(db::delete(TBLPREFIX."cmspagegroup","CMSPageGroupID=".$this->GroupID))	
		{
			$this->setConfirmationMsg('C1004');	
		}
		else
		{
			$this->setErrorMsg('E1004');	
		}
	}
	
	public function InsertPage($DataArray)
	{
		$this->validatePageInput($DataArray);
		if($this->P_Status==1){
		db::insert(TBLPREFIX.'cmspages',$DataArray);	
		$this->PageID	= db::get_last_id();
			if($this->PageID>0)
			{
				$this->setConfirmationMsg('C1002');
			}
			else
			{
				$this->setErrorMsg('E3001');	
			}
		}
	}
	
	public function validateInput()
	{
		EnPException::writeProcessLog('CMS_Model :: validateInput Function call');	
		if(trim($this->Language)=='' && $this->P_Status==1)
		{
			$this->setErrorMsg('E1005');
		}
		if(trim($this->Title)=='' )
		{
			$this->setErrorMsg('E1001');
		}
		
		/*if(trim($this->SortOrder)=='')
		{
			$this->setErrorMsg('E1002');
		}*/
	}
	
	public function validatePageInput($DataArray)
	{
		EnPException::writeProcessLog('CMS_Model :: validateInput Function call');	
		if($DataArray['CMSPageGroupID']=='')
		{
			$this->setErrorMsg('E3006');
		}
		
		if(trim($DataArray['CMSPagesName'])== '' && $this->P_Status==1)
		{
			$this->setErrorMsg('E3003');
		}
		
		if(trim($DataArray['CMSPagesNameINURL'])== '' )
		{
			$this->setErrorMsg('E3004');
		}
		
		if(trim($DataArray['CMSPagesTitle'])== '' )
		{
			$this->setErrorMsg('E3005');
		}
	}
	
	public function GetPages($Array,$Cond='',$Order='')
	{
		$fields	= implode(',',$Array);
		$Where	= " WHERE 1=1 ";
		$Where	= $Where.$Cond;
		$Sql	= "SELECT $fields FROM ".TBLPREFIX."cmspages";//echo $Sql.$Where.$Order;
		$Res	= db::get_all($Sql.$Where.$Order);	
		return (count($Res)>0)?$Res:array();
	}
	
	public function DeletePage()
	{
		if(db::delete(TBLPREFIX."cmspages","CMSPagesID=".$this->PageID))	
		{
			$this->setConfirmationMsg('C1006');	
		}
		else
		{
			$this->setErrorMsg('E3007');	
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
	
	public function UpdatePage($DataArray)
	{
		$this->validatePageInput($DataArray);
		if($this->P_Status==1){
			if(db::update(TBLPREFIX.'cmspages',$DataArray,"CMSPagesID=".$this->PageID))
			{
				$this->setConfirmationMsg('C1003');	
			}
			else
			{
				$this->setErrorMsg('E3008');
			}
		}
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
		EnPException::writeProcessLog('CMS_Model :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->ErrorCode.=$ErrCode.",";
			$this->ErrorMessage=$ErrCode;
			$this->P_Status=$P_Status;
			$this->MsgType=$MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode,$MsgType=2,$P_Status=1)
	{
		EnPException::writeProcessLog('CMS_Model :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->ConfirmCode=$ConfirmCode;
			$this->ConfirmMsg=$ConfirmCode;
			$this->P_Status=$P_Status;
			$this->MsgType=$MsgType;
	}	
	
	
}
?>