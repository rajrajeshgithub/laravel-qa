<?php
	class Search_Model extends Model
	{
		public $keyword, $npoListArray, $recordsCount, $filterInput, $npoCatetoryArray, $npoFilterArray=array(), $autoLocationArray,$status;
		public $PageSelected;
		function __construct()
		{
			$this->PageLimit=100;
		}
		
		public function GetNPOList($fieldArray, $filters, $keyword, $activePage)
		{
			$FilterString	= $this->getNPOFilters($filters);
			$Where			= " WHERE 1=1 ";
			if($FilterString <> "")
			$Where			.= " ANd ".$FilterString;
			
			$Fields		= implode(",",$fieldArray);
			$sql = "SELECT  $Fields FROM ".TBLPREFIX."npodetails N 
					LEFT JOIN ".TBLPREFIX."npocategoryrelation NR on(N.NPO_CD=NR.NPO_CategoryName)
					LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			
			$StartIndex	= ($this->PageSelected-1)*$this->PageLimit;
			$Limit = " LIMIT ".$StartIndex.", ".$this->PageLimit;//echo $sql.$Where.$Limit;exit;
			$Res	= db::get_all($sql.$Where.$Limit);
			$this->npoListArray	= (count($Res)>0)?$Res:array(); /*assign array if data found in db*/
			
			$sqlCount	= "SELECT  count(N.NPO_ID) FROM ".TBLPREFIX."npodetails N 
						   LEFT JOIN ".TBLPREFIX."npocategoryrelation NR on(N.NPO_CD=NR.NPO_CategoryName)
						   LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			$this->recordsCount = db::countWithSQL($sqlCount.$Where);//echo $sqlCount.$Where;exit;
			return $status;	/* 0 or 1   - if return 1 than we use furher process function*/ 
		}	
		
		function setNPOCategories()
		{
			$this->npoCategoryArray; /*assign arrray if category data found in database*/
			return $status; /* 0 or 1*/	
		}
		
		function getNPOFilters($filters)
		{	
		
			if(isset($filters['category']) &&  $filters['category']<>'')
			{
				$this->npoCatetoryArray = explode("||",$filters['category']);
				$CatFilter	= array();
				foreach($this->npoCatetoryArray as $catID)
				{
					if($catID <> ""){
						$CatFilter[]	= " NR.NPOCat_ID=".$catID;	
					}
				}
				$this->npoFilterArray[] = "(".implode(" OR ",$CatFilter).")";	
			}
			if(isset($filters['keyword']) &&  $filters['keyword']<>'')
			{
				$this->npoFilterArray[] = " (N.NPO_Name LIKE '%".$filters['keyword']."%') OR (N.NPO_EIN LIKE '%".$filters['keyword']."%')";	
			}
			
			
			
			if(isset($filters['location']) &&  $filters['location']<>'')
			{
				//echo $filters['location'];
				$Location	= explode(",",$filters['location']);
				$arrLocation = explode("-",$Location[1]);
				
				//dump($Location);
				if(count($Location) == 1)
				{
					$arrLocation = explode("-",strpos($Location[1],'-',1));
					if(count($arrLocation)==2)
					{
						$this->npoFilterArray[] = " ( N.NPO_State LIKE '%".$arrLocation[0]."%' OR N.NPO_Zip LIKE '%".$arrLocation[1]."%')";			
					}
					else
					{
						$this->npoFilterArray[] = " ( N.NPO_State LIKE '%".$arrLocation[0]."%')";		
					}
				}
				elseif(count($Location)==2)
				{
					$this->npoFilterArray[] = " (N.NPO_City LIKE '%".$Location[0]."%' OR  N.NPO_State LIKE '%".$Location[1]."%')";	
				}
				
				/*$this->npoFilterArray[] = " ( N.NPO_Street LIKE '%".$filters['location']."%' OR N.NPO_City LIKE '%".$filters['location']."%'  OR 
											N.NPO_State LIKE '%".$filters['location']."%' OR N.NPO_Zip LIKE '%".$filters['location']."%')";	*/
			}
			return implode(" AND ",$this->npoFilterArray);/*assign all search filters*/	
		}
		
		private function RemoveSpecialChar($Str)
		{
			$Array	= array('-');
			return str_replace($Array,"",strpos($Str,'-',1));
		}
		
		
		function getAutoSuggestion($keyword)
		{
			$this->autoLocationArray;
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
		
		
		public function GetNPOCategoryListDB($DataArray,$Filter)
		{
			$Where		= " WHERE 1=1";
			if(isset($Filter['keyword']) && $Filter['keyword'] <> "")
			{
				$Condition	.=" AND (N.NPO_Name LIKE '%".$Filter['keyword']."%') OR (N.NPO_EIN LIKE '%".$Filter['keyword']."%')";	
			}
			if(isset($Filter['location']) && $Filter['location'] <> "")
			{
				$Condition	.=" AND (N.NPO_Street LIKE '%".$Filter['location']."%' OR N.NPO_City LIKE '%".$Filter['location']."%' OR N.NPO_State LIKE '%".$Filter['location']."%' 
								OR N.NPO_Zip LIKE '%".$Filter['location']."%')";	
			}
			
			$Where  	.=$Condition;
			$Order		= " ORDER BY CR.NPO_CatName ";
			$Fields		= implode(",",$DataArray);
			
			$Sql		= "SELECT  $Fields FROM ".TBLPREFIX."npodetails N 
						   LEFT JOIN ".TBLPREFIX."npocategoryrelation CR on(N.NPO_CD=CR.NPO_CategoryName)
						   LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			$Group		= " GROUP BY CR.NPO_CatName";			  
			$Res		= db::get_all($Sql.$Where.$Group.$Order);//echo $Sql.$Where.$Group.$Order;exit;
			return (count($Res)>0)?$Res:array();
		}
		
		public function GetLocationDB($SearchStr)
		{
			
			/*$Sql	= "SELECT DISTINCT NPO_City as result FROM ".TBLPREFIX."npodetails WHERE NPO_City LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_Street as result FROM ".TBLPREFIX."npodetails WHERE NPO_Street LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_State as result FROM ".TBLPREFIX."npodetails WHERE NPO_State LIKE '".$SearchStr."%'
						UNION ALL
						SELECT DISTINCT NPO_Zip as result FROM ".TBLPREFIX."npodetails WHERE NPO_Zip LIKE '".$SearchStr."%'
						LIMIT 0,10";*/	
			
			if(is_numeric($SearchStr) && strlen($SearchStr)>2)
			{
				$Sql	= "SELECT distinct CONCAT(NPO_State,'-',NPO_Zip) as result FROM ".TBLPREFIX."npodetails WHERE NPO_Zip LIKE '".$SearchStr."%'LIMIT 0,10";
			}			
			elseif(strlen($SearchStr)==2)
			{
				/*$Sql	= "SELECT CONCAT(NPO_Street,',',NPO_City) as result FROM ".TBLPREFIX."npodetails 
					   		WHERE NPO_Street LIKE '".$SearchStr."%' OR  NPO_City LIKE '".$SearchStr."%' 
							LIMIT 0,10";*/
				$Sql	= "SELECT distinct NPO_State as result FROM ".TBLPREFIX."npodetails NPO 
							
					   		WHERE NPO_State = '".$SearchStr."'
							ORDER BY NPO_State
							LIMIT 0,20";
									
			}
			else
			{
				//$Sql	= "SELECT NPO_Street as result FROM ".TBLPREFIX."npodetails WHERE NPO_Street LIKE '".$SearchStr."%' LIMIT 0,10";
							
				$Sql	= "SELECT distinct CONCAT(NPO_City,',',NPO_State) as result FROM ".TBLPREFIX."npodetails NPO 
							LEFT JOIN ".TBLPREFIX."states ST ON(ST.State_Value = NPO.NPO_State)
					   		WHERE NPO_City LIKE '".$SearchStr."%' 
							ORDER BY NPO.NPO_City,NPO.NPO_State
							LIMIT 0,20";
			}
			//echo $Sql;exit;
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
		
		
	}
?>