<?php
	class Search_Model extends Controller
	{
		public $PageLimit,$TotalCountNPO,$pageSelectedPage,$TotalCountCampaign;
		
		public $Category,$Title,$Location,$Country,$SearchCategory,$CategoryArr;
		public $CategoryCondition,$NPOCondition;
		
		function __construct()		
		{
			$this->PageLimit	= 20;
			$this->pageSelectedPage=1;
			$this->TotalCountNPO=0;
		}	
		
		public function GetNPOCategoryListDB($DataArray,$Location,$Title)
		{
			$Where		= " WHERE 1=1";
			$Condition	= " AND C.NPOCat_ShowOnWebsite='1' ";
			$Condition	.=" AND (N.NPO_Street LIKE '%".$Location."%' OR N.NPO_City LIKE '%".$Location."%' 
												OR N.NPO_State LIKE '%".$Location."%' OR N.NPO_Zip LIKE '%".$Location."%')";	
			$Condition	.=" AND (N.NPO_Name LIKE '%".$Title."%')";									
			$Where  	.=$Condition;
			$Order		= " ORDER BY C.NPOCat_SortOrder ";
			$Fields		= implode(",",$DataArray);
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."npodetails N
						  left join ".TBLPREFIX."npocategoryrelation npor ON (NPO_CategoryName=NPO_CD)
						  left join ".TBLPREFIX."npocategories C ON (C.NPOCat_ID=npor.NPOCat_ID)";
			$Group		= " GROUP BY C.NPOCat_ID";			  
			$Res		= db::get_all($Sql.$Where.$Group.$Order);//echo $Sql.$Where.$Group.$Order;
			return (count($Res)>0)?$Res:array();
		}
		
		public function GetCategoryListDB($DataArray,$Condition="",$Order="")
		{
			$Where		= " WHERE 1=1";
			$Where  	.=$Condition;
			$Fields		= implode(",",$DataArray);
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."npocategories";
			$Res		= db::get_all($Sql.$Where.$Order);//echo $Sql.$Where.$Order;exit;
			return (count($Res)>0)?$Res:array();
		}
		
		public function GetNPOsDB($DataArray,$Location,$Category,$Title,$pageSelected)
		{
			$this->Category	= $Category;
			$this->Title	= $Title;
			$this->Location	= $Location;
			$this->NPOsFilter();
			
			$Where		= " WHERE (1=1)";
			$Condition	= " AND (N.NPO_Status='1')";
			$Condition	.= $this->NPOCondition;
			$Order		= " ORDER BY N.NPO_CreatedDate";
			
			$Where  	.=$Condition;
			$Fields		= implode(",",$DataArray);
			
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."npodetails N
							LEFT JOIN ".TBLPREFIX."npocategoryrelation RC ON (N.NPO_CD=RC.NPO_CategoryName)";
			$StartIndex	= ($pageSelected-1)*$this->PageLimit;
			$Limit = " LIMIT ".$StartIndex.", ".$this->PageLimit;
			$Res		= db::get_all($Sql.$Where.$Order.$Limit);//echo $Sql.$Where.$Order.$Limit;exit;
		
			/*============================ */
			$sqlCount = "SELECT count(NPO_ID) FROM ".TBLPREFIX."npodetails N 
				   LEFT JOIN ".TBLPREFIX."npocategoryrelation RC ON (RC.NPO_CategoryName=N.NPO_CD)";
			$this->TotalCountNPO = db::countWithSQL($sqlCount.$Where);
			/*============================== */
			//$this->TotalCountNPO	= db::count($Sql.$Where);
			return (count($Res)>0)?$Res:array();
		}
		
		private function NPOsFilter()
		{
			$this->SearchCategory	= $this->Category;
			$this->CategoryArr	= explode("||",$this->Category);//dump(array_filter($Category));
			$this->CategoryCondition	= " AND (N.NPO_Status='1')";
			if($this->Location != "")
			{
				$this->NPOCondition	.= " AND (N.NPO_Street LIKE '%".$this->Location."%' OR N.NPO_City LIKE '%".$this->Location."%'  OR N.NPO_State LIKE '%".$this->Location."%' OR 
										 N.NPO_Zip LIKE '%".$this->Location."%')";		
			}
			
			if($this->CategoryArr)
			{
				$TempArr	= array();
				foreach(array_filter($this->CategoryArr) as $cat)
				{
					if($cat != "")
					{
						$CatID	= $this->GetCatID($cat);
						$TempArr[]	= " RC.NPOCat_ID = '".$CatID."'";
					}
				}	
				if($TempArr)
				{
					$this->NPOCondition	.=	" AND (".implode(" or ",$TempArr).")";
				}
			}
			
			if($this->Title != "")
			{
				$this->NPOCondition	.= " AND (N.NPO_Name LIKE '%".$this->Title."%')";	
			}
		}
		
		private function GetCatID($URLFriendlyName)
		{
			$SQL	= "SELECT NPOCat_ID FROM ".TBLPREFIX."npocategories WHERE NPOCat_URLFriendlyName='".$URLFriendlyName."'";
			$Res	= db::get_row($SQL);
			return (isset($Res['NPOCat_ID']))?$Res['NPOCat_ID']:0;
		}
		
		public function GetCampaignsDB($DataArray,$Location,$Category,$Title,$Country,$pageSelected)
		{
			$this->Category	= $Category;
			$this->Title	= $Title;
			$this->Location	= $Location;
			$this->Country	= $Country;
			$this->CampaignFilter();
			$Where		= " WHERE 1=1";
			$Condition	= " AND C.Camp_Deleted='0' AND C.Camp_IsPrivate='0'";
			$Condition	.= $this->CampaignCondition;
			$Where  	.=$Condition;
			$Fields		= implode(",",$DataArray);
			$Order		= " ORDER BY C.Camp_StartDate";
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."campaign C 
							LEFT JOIN ".TBLPREFIX."npocategories CA ON (C.Camp_Cat_ID=CA.NPOCat_ID)";
			$StartIndex	= ($pageSelected-1)*$this->PageLimit;
			$Limit = " LIMIT ".$StartIndex.", ".$this->PageLimit;				
			$Res		= db::get_all($Sql.$Where.$Order.$Limit);//echo $Sql.$Where.$Order;
			$this->TotalCountCampaign	= db::count($Sql.$Where);
			return (count($Res)>0)?$Res:array();	
		}
		
		private function CampaignFilter()
		{
			$this->SearchCategory	= $this->Category;
			$this->CategoryArr	= explode("||",$this->Category);
			if($this->Location != "")
			{
				$this->CampaignCondition	.= " AND (C.Camp_CP_Address1 LIKE '%".$this->Location."%' OR C.Camp_CP_Address2 LIKE '%".$this->Location."%'  OR 
												C.Camp_CP_City LIKE '%".$this->Location."%' OR C.Camp_CP_State LIKE '%".$this->Location."%' OR C.Camp_CP_ZipCode LIKE '".$this->Location."')";		
			}
			
			if($this->Country != "")
			{
				$this->CampaignCondition	.= " AND (C.Camp_CP_Country = '".$this->Country."') ";	
			}	
			
			if($this->CategoryArr)
			{
				$TempArr	= array();
				foreach(array_filter($this->CategoryArr) as $cat)
				{
					if($cat != "")
					{
						$CatID	= $this->GetCatID($cat);
						$TempArr[]	= " CA.NPOCat_ID = '".$CatID."'";
					}
				}	
				if($TempArr)
				{
					$this->CampaignCondition	.=	" AND (".implode(" or ",$TempArr).")";
				}
			}
			
			if($this->Title != "")
			{
				$this->CampaignCondition	.= " AND (C.Camp_Title LIKE '%".$this->Title."%')";	
			}
		}
		
		
		public function GetCountryListDB($DataArray,$Condition='',$Order='')
		{
			$Where		= " WHERE 1=1";
			$Where  	.=$Condition;
			$Fields		= implode(",",$DataArray);
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."country";
			$Res		= db::get_all($Sql.$Where.$Order);//echo $Sql.$Where.$Order;exit;
			return (count($Res)>0)?$Res:array();	
		}
		
		public function GetLocationDB($SearchStr)
		{
			
			$Sql	= "SELECT DISTINCT NPO_City as result FROM ".TBLPREFIX."npodetails WHERE NPO_City LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_Street as result FROM ".TBLPREFIX."npodetails WHERE NPO_Street LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_State as result FROM ".TBLPREFIX."npodetails WHERE NPO_State LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_Zip as result FROM ".TBLPREFIX."npodetails WHERE NPO_Zip LIKE '".$SearchStr."%'
						LIMIT 0,10";			
			$Res	= db::get_all($Sql);
			return (count($Res)>0)?$Res:array();	
		}
		
		public function GetCampaignLocationDB($SearchStr)
		{
			$Sql	= "SELECT DISTINCT Camp_CP_Address1 as result FROM ".TBLPREFIX."campaign WHERE Camp_CP_Address1 LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT Camp_CP_Address2 as result FROM ".TBLPREFIX."campaign WHERE Camp_CP_Address2 LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT Camp_CP_City as result FROM ".TBLPREFIX."campaign WHERE Camp_CP_City LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT Camp_CP_State as result FROM ".TBLPREFIX."campaign WHERE Camp_CP_State LIKE '".$SearchStr."%'
						LIMIT 0,10";			
			$Res	= db::get_all($Sql);
			return (count($Res)>0)?$Res:array();	
		}
		
		public function IsRegisteredNPO($NPOID)
		{
			$Sql	= "SELECT ID FROM ".TBLPREFIX."npouserrelation WHERE NPOID=".$NPOID;
			$Row	= db::get_row($Sql);	
			return (isset($Row['ID']))?1:0;
		}
	}
?>