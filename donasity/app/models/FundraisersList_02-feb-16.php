<?php
	class FundraisersList_Model extends Model
	{
		public $npoListArray, $recordsCount,$npoCatetoryArray=array(), $npoFilterArray=array(), $autoLocationArray,$npoCondition,$npoCategoryCondition;
		public $KeywordFilter,$CategoryFilter,$LocationFilter,$SelectedCategoryArray=array();
		public $PageSelected;
		function __construct()
		{
			$this->PageLimit=100;
		}
		
		public function GetNPOList($fieldArray, $filters, $keyword='', $activePage='')
		{
			$this->getNPOFilters($filters);
			$this->ManageWhereCondition();
			$Fields		= implode(",",$fieldArray);
			$sql = "SELECT  $Fields FROM ".TBLPREFIX."campaign C 
					INNER JOIN ".TBLPREFIX."npocategories CC on(C.Camp_Cat_ID=CC.NPOCat_ID)";
			$StartIndex	= ($this->PageSelected-1)*$this->PageLimit;
			$Limit = " LIMIT ".$StartIndex.", ".$this->PageLimit;
			//echo $sql.$this->npoCondition.$Limit;exit;
			$Res	= db::get_all($sql.$this->npoCondition.$Limit);
			$this->npoListArray	= (count($Res)>0)?$Res:array(); /*assign array if data found in db*/
			$this->ManageNPOListArray();
			
			$sqlCount	= "SELECT  count(C.Camp_ID) FROM ".TBLPREFIX."campaign C 
						  INNER JOIN ".TBLPREFIX."npocategories CC on(C.Camp_Cat_ID=CC.NPOCat_ID)";
			$this->recordsCount = db::countWithSQL($sqlCount.$this->npoCondition);//echo $sqlCount.$Where;exit;
		}	
		
		private function ManageNPOListArray()
		{
			foreach($this->npoListArray as &$val)
			{
				$CategoryName	= explode('||',$val['NPO_CatName']);
				$val['NPO_State']	= $this->GetStateName($val['NPO_State']);
				$val['CategoryName']	=   (LANG_ID == 'en')?$CategoryName[0]:$CategoryName[1];				
				
				$val['LimitedDescription']  = $this->strLimitData($val['Camp_DescriptionHTML']); 
				$val['NextDescription']		= $this->strRemainData($val['Camp_DescriptionHTML']);
			}
		}
		
		private function strLimitData($string)
		{
			$arrString 	= explode(" ", $string);
			//dump($arrString);
			$countArr 	= count($arrString);
			if($countArr>0)
			{
				$arrSliced  = array_slice($arrString,0,30);
				$string = implode(" ",$arrSliced);
				if($countArr>30)				
					$string = $string." <a href='javascript://' class='read-more'> "._READ_MORE_."</a>";
			}			
			return $string;	
		}
		
		private function strRemainData($string)
		{
			$arrString 	= explode(" ", $string);
			$countArr 	= count($arrString);
			if($countArr>0)
			{
				$arrSliced  = array_slice($arrString,30,$countArr);
				$string = implode(" ",$arrSliced);
			}			
			return $string;		
		}
		
		
		
		function getNPOFilters($Filter)
		{	
			if(isset($Filter['keyword']) && $Filter['keyword'] != NULL)
				$this->KeywordFilter	= $Filter['keyword'];
				
			if(isset($Filter['category']) && $Filter['category'] != NULL)
				$this->CategoryFilter	= $Filter['category'];
				
			if(isset($Filter['location']) && $Filter['location'] != NULL)
				$this->LocationFilter	= $Filter['location'];		
		}
		
		private function ManageWhereCondition()
		{
			$this->npoCondition	= " WHERE 1=1 AND Camp_Status=15 AND Camp_IsPrivate!='1' AND Camp_Deleted!='1' AND Camp_EndDate>='".getDateTime(0,'Y-m-d')."'";
			$this->ManageKeywordFilter();
			$this->ManageCategoryFilter();
			$this->ManageLocationFilter();
			if(count($this->npoFilterArray) > 0)
				$this->npoCondition	.= " AND ".implode(" AND ",$this->npoFilterArray);	
		}
		
		private function ManageKeywordFilter()
		{
			if(isset($this->KeywordFilter) && $this->KeywordFilter != "")
			{
				$this->npoFilterArray['keyword'] = " ((C.Camp_Title LIKE '%".$this->KeywordFilter."%'))";		
			}
		}
		
		private function ManageCategoryFilter()
		{
			if(isset($this->CategoryFilter) && $this->CategoryFilter != "")
			{
				$this->SelectedCategoryArray = explode("||",$this->CategoryFilter);
				$CatFilter	= array();
				foreach($this->SelectedCategoryArray as $catID)
				{
					if($catID <> ""){
						$CatFilter[]	= " CC.NPOCat_ID=".$catID;	
					}
				}
				$this->npoFilterArray['category'] = " (".implode(" OR ",$CatFilter).")";	
			}
		}
		
		private function getCategoryDetails()
		{
			if(isset($this->CategoryFilter) && $this->CategoryFilter != "")
			{
				$this->SelectedCategoryArray = explode("||",$this->CategoryFilter);
			}
			
		}
		private function ManageLocationFilter()
		{
			if(isset($this->LocationFilter) && $this->LocationFilter != "")
			{
				$Location	= explode(",",$this->LocationFilter);	
				if(count($Location) == 1)
				{
					if(is_numeric($this->RemoveSpecialChar($Location[0])))
					{
						$this->npoFilterArray['location'] = " (C.Camp_Location_Zip LIKE '%".trim($Location[0])."%')";
					}
					else
					{
						$this->npoFilterArray['location'] = " (C.Camp_Location_State LIKE '%".trim($Location[0])."%' OR C.Camp_Location_City LIKE '%".trim($Location[0])."%')";
					}
				}
				
				if(count($Location) == 2)
				{   
					$this->npoFilterArray['location'] = " (C.Camp_Location_City LIKE '%".trim($Location[0])."%' AND C.Camp_Location_State LIKE '%".trim($Location[1])."%') ";
				}
			}
		}
		
		private function RemoveSpecialChar($Str)
		{
			$Array	= array('-');
			return str_replace($Array,"",strpos($Str,'-',1));
		}
		
		private function ManageCategoryListFilter()
		{
			$this->npoCategoryCondition	= " WHERE 1=1 ";
			$CategoryFilterArr	= $this->npoFilterArray;
			unset($CategoryFilterArr['category']);
			if(count($CategoryFilterArr) > 0)
				$this->npoCategoryCondition	.= " AND ".implode(" AND ",$CategoryFilterArr);
		}
		
		public function GetNPOCategoryListDB($DataArray= array())
		{
			//'CR.NPO_CatName as DisplayName','CR.NPOCat_ID','count(CR.NPOCat_ID) cnt'
			
			$Sql="SELECT C.Camp_Title,NC.NPOCat_DisplayName_"._DBLANG_." as DisplayName,count(NC.NPOCat_ID) cnt,NC.NPOCat_ID,NC.NPOCat_URLFriendlyName 
				 FROM dns_npocategories NC INNER JOIN dns_campaign C ON(NC.NPOCat_ID = C.Camp_Cat_ID AND C.Camp_Status=15 AND C.Camp_IsPrivate!='1')
			group by NC.NPOCat_DisplayName_"._DBLANG_."";
			$this->npoCatetoryArray = db::get_all($Sql);
			//echo $Sql;exit;
			/*$this->ManageCategoryListFilter();
			$Order		= " ORDER BY CR.NPO_CatName ";
			$Fields		= implode(",",$DataArray);
			
			$Sql		= "SELECT  $Fields FROM ".TBLPREFIX."npodetails N 
						   LEFT JOIN ".TBLPREFIX."npocategoryrelation CR on(N.NPO_CD=CR.NPO_CategoryName)
						   LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			$Group		= " GROUP BY CR.NPO_CatName";
			//echo $Sql.$this->npoCategoryCondition.$Group.$Order;exit;
			$this->npoCatetoryArray	= db::get_all($Sql.$this->npoCategoryCondition.$Group.$Order);
			$this->npoCatetoryArray	= array_filter($this->npoCatetoryArray);
			foreach($this->npoCatetoryArray as $key => &$cat)
			{
				$CategoryDisplayName	= explode("||",$cat['DisplayName']);
				$cat['DisplayName']	= (LANG_ID == "en")?$CategoryDisplayName[0]:$CategoryDisplayName[1];
				if($cat['DisplayName'] == "")
				{
					unset($this->npoCatetoryArray[$key]);	
				}
			}*/
		}
		
		public function GetLocationDB($SearchStr)
		{		
			if(is_numeric($SearchStr) && strlen($SearchStr)>2)
			{
				$Sql	= "SELECT distinct Camp_Location_Zip as result FROM ".TBLPREFIX."campaign WHERE Camp_Location_Zip LIKE '".$SearchStr."%'LIMIT 0,10";
			}			
			elseif(strlen($SearchStr)==2)
			{
				$Sql	= "SELECT distinct Camp_Location_State as result FROM ".TBLPREFIX."campaign NPO 							
					   		WHERE Camp_Location_State = '".$SearchStr."'
							ORDER BY Camp_Location_State
							LIMIT 0,20";
			}
			else
			{							
				$Sql	= "SELECT distinct CONCAT(Camp_Location_City,', ',Camp_Location_State) as result FROM ".TBLPREFIX."campaign NPO 
							LEFT JOIN ".TBLPREFIX."states ST ON(ST.State_Value = NPO.Camp_Location_State)
					   		WHERE Camp_Location_City LIKE '".$SearchStr."%' and ST.State_Country='US'
							ORDER BY NPO.Camp_Location_City,NPO.Camp_Location_State
							LIMIT 0,20";
			}
			//echo $Sql;exit;
			$this->autoLocationArray	= db::get_all($Sql);
		}	
		
		public function GetStateName($Abr)
		{
			$Sql	= "SELECT State_Name FROM ".TBLPREFIX."states WHERE State_Country = 'US' AND State_Value='".$Abr."'";
			$Res	= db::get_row($Sql);
			return (isset($Res['State_Name']))?$Res['State_Name']:'';	
		}	
	}
?>