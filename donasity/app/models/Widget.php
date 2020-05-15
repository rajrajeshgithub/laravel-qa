<?php
	class Widget_Model extends Model
	{
		public $UniqueKey,$userId,$CharityID,$CharityType,$NPOEIN,$Status,$CreatedDate,$UpdatedDate,$ValidSourceSite;
		public $Pstatus = 1,$widgetId;
		
		public function AddWidget_DB()
		{
			$DataArray = array("W_UniqueKey"=>$this->UniqueKey,"W_RUID"=>$this->userId,"W_CharityID"=>$this->CharityID,"W_CharityType"=>$this->CharityType,"W_NPOEIN"=>$this->NPOEIN,
								"W_Status"=>$this->Status,"W_CreatedDate"=>$this->CreatedDate,"W_UpdatedDate"=>$this->UpdatedDate,"W_ValidSourceSite"=>$this->ValidSourceSite);
								
			db::insert(TBLPREFIX."ut3widgets",$DataArray);		
			$this->widgetId	= db::get_last_id();
			if(isset($this->widgetId))
			{
				$this->Pstatus =1;
				return $this->widgetId;				
			}
			else
			{
				$this->Pstatus =0;
			}
		}
		
		public function getWidgetDetail($DataArray)
		{
			$Fields	= implode(",",$DataArray);	
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."ut3widgets W";
			$Where	= " WHERE 1=1 and W.W_RUID=".$this->userId;//echo $Sql.$Where;exit;
			$Res	= db::get_row($Sql.$Where);
			return $Res;	
		}
		
		public function updateWidget()
		{
			$DataArray = array("W_Status"=>$this->W_Status);
			db::update(TBLPREFIX."ut3widgets",$DataArray,"W_ID=".$this->W_ID);	
				if(db::is_row_affected())
				{
					$this->Pstatus	= 1;					
				}
				else
				{
					$this->Pstatus	= 0;	
				}	
		}
	
	}
	
?>