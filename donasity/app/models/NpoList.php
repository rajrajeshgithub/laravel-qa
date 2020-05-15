<?php
	class NpoList_Model extends Model
	{
		public $npoListArray, $recordsCount,$npoCatetoryArray=array(), $npoFilterArray=array(), $autoLocationArray,$npoCondition,$npoCategoryCondition;
		public $KeywordFilter,$CategoryFilter,$LocationFilter,$SelectedCategoryArray=array();
		public $PageSelected,$N_EIN;
		public $F_Camp_ID;
		function __construct()
		{
			$this->PageLimit=100;
		}
		
		public function GetNPOList($fieldArray, $filters, $keyword='', $activePage='')
		{
			$this->getNPOFilters($filters);
			$this->ManageWhereCondition();	
			$Fields		= implode(",",$fieldArray);
			$sql = "SELECT  $Fields FROM ".TBLPREFIX."npodetails N 
					LEFT JOIN ".TBLPREFIX."npocategoryrelation CR on(N.NPO_CD=CR.NPO_CategoryName)
					LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			
			$StartIndex	= ($this->PageSelected-1)*$this->PageLimit;
			$OrderBy=" ORDER BY IsRegistered DESC , NPO_Name ";
			$Limit = " LIMIT ".$StartIndex.", ".$this->PageLimit;
			//echo $sql.$Where.$Limit;exit;
			$Res	= db::get_all($sql.$this->npoCondition.$OrderBy.$Limit);
			
			$this->npoListArray	= (count($Res)>0)?$Res:array(); /*assign array if data found in db*/
			$this->ManageNPOListArray();
			
			$sqlCount	= "SELECT  count(N.NPO_ID) FROM ".TBLPREFIX."npodetails N 
						   LEFT JOIN ".TBLPREFIX."npocategoryrelation CR on(N.NPO_CD=CR.NPO_CategoryName)
						   LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_ID=NUR.NPOID)";
			$this->recordsCount = db::countWithSQL($sqlCount.$this->npoCondition);//echo $sqlCount.$Where;exit;
		}	
		
		private function ManageNPOListArray()
		{
			foreach($this->npoListArray as &$val)
			{
				$CategoryName	= explode('||',$val['NPO_CatName']);
				$val['NPO_State']	= $this->GetStateName($val['NPO_State']);
				$val['CategoryName']	=   (LANG_ID == 'en')?$CategoryName[0]:$CategoryName[1];
					
				$val['LimitedDescription']  = $this->strLimitData($val['Description']); 
				$val['NextDescription']		= $this->strRemainData($val['Description']);				
			}			
		}
		
		private function strLimitData($string)
		{
			$arrString 	= explode(" ", $string);
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
			$this->npoCondition	= " WHERE 1=1 ";
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
				$this->npoFilterArray['keyword'] = " ((N.NPO_Name LIKE '%".$this->KeywordFilter."%') OR (N.NPO_EIN LIKE '%".$this->KeywordFilter."%'))";		
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
						$CatFilter[]	= " CR.NPOCat_ID=".$catID;	
					}
				}
				$this->npoFilterArray['category'] = " (".implode(" OR ",$CatFilter).")";	
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
						$this->npoFilterArray['location'] = " (N.NPO_Zip LIKE '%".trim($Location[0])."%')";
					}
					else
					{
						$this->npoFilterArray['location'] = " (N.NPO_State LIKE '%".trim($Location[0])."%' OR N.NPO_City LIKE '%".trim($Location[0])."%')";
					}
				}
				
				if(count($Location) == 2)
				{   
					$this->npoFilterArray['location'] = " (N.NPO_City LIKE '%".trim($Location[0])."%' AND N.NPO_State LIKE '%".trim($Location[1])."%') ";
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
		
		public function GetNPOCategoryListDB($DataArray)
		{
			$this->ManageCategoryListFilter();
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
			}
		}
		
		public function GetLocationDB($SearchStr)
		{		
			if(is_numeric($SearchStr) && strlen($SearchStr)>2)
			{
				$Sql	= "SELECT distinct NPO_Zip as result FROM ".TBLPREFIX."npodetails WHERE NPO_Zip LIKE '".$SearchStr."%'LIMIT 0,10";
			}			
			elseif(strlen($SearchStr)==2)
			{
				$Sql	= "SELECT distinct NPO_State as result FROM ".TBLPREFIX."npodetails NPO 							
					   		WHERE NPO_State = '".$SearchStr."'
							ORDER BY NPO_State
							LIMIT 0,20";
			}
			else
			{							
				$Sql	= "SELECT distinct CONCAT(NPO_City,', ',NPO_State) as result FROM ".TBLPREFIX."npodetails NPO 
							LEFT JOIN ".TBLPREFIX."states ST ON(ST.State_Value = NPO.NPO_State)
					   		WHERE NPO_City LIKE '".$SearchStr."%' and ST.State_Country='US'
							ORDER BY NPO.NPO_City,NPO.NPO_State
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
		
		public function GetLogoImage()
		{
			$Sql	= "SELECT * FROM ".TBLPREFIX."npouserrelation 							
					   		WHERE NPOEIN =".$this->N_EIN;
			$Res	= db::get_row($Sql);
			if(isset($Res))
			{
				if($Res['Stripe_ClientID']!='')
				{
					if($Res['NPOLogo']!='')
					{
						$logoImage=CheckImage(NPO_IMAGE_DIR,NPO_IMAGE_URL,NO_IMAGE,$Res['NPOLogo']);
					}	
					else
					{
						//get campaign image
						$sqlquery = "select camp_thumbImage FROM ".TBLPREFIX."campaign WHERE Camp_NPO_EIN=".$this->N_EIN." AND Camp_ID=".$this->F_Camp_ID;
						//echo $sqlquery;exit;
						$Result = db::get_row($sqlquery);
						$logoImage=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$Result['camp_thumbImage']);
					}
				}
				else 
				{
					$sqlquery = "select camp_thumbImage FROM ".TBLPREFIX."campaign WHERE Camp_NPO_EIN=".$this->N_EIN." AND Camp_ID=".$this->F_Camp_ID;
					$Result = db::get_row($sqlquery);
					$logoImage=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$Result['camp_thumbImage']);
				}
			}
			return $logoImage;				
		}
		public function getNPOLogo()
		{
			$sqlquery = "select camp_thumbImage FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$this->F_Camp_ID;
			$Result = db::get_row($sqlquery);
			$logoImage=CheckImage(CAMPAIGN_MAIN_IMAGE_DIR,CAMPAIGN_MAIN_IMAGE_URL,NO_IMAGE,$Result['camp_thumbImage']);
			return $logoImage;
		}
	}
?>