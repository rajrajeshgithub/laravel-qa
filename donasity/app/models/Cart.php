<?php
	class Cart_Model extends Model
	{	
		public function GetNpoDetails($DataArray,$Param=NULL)
		{
			$Where	= " WHERE 1=1";
			if($Param != NULL)
			{
				$Where.=$Param;	
			}
			$Fields	= implode(',',$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npodetails N
					   LEFT JOIN ".TBLPREFIX."npocategoryrelation NCR ON (NCR.NPO_CategoryName=N.NPO_CD)
					   LEFT JOIN ".TBLPREFIX."npouserrelation NU ON (N.NPO_ID=NU.NPOID)";
			$Res	= db::get_row($Sql.$Where);//echo $Sql.$Where;exit;		   
			return $Res;
		}	
	}
?>