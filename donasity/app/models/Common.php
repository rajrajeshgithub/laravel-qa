<?php
	class Common_Model extends Controller
	{
		function __construct()		
		{
				
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
		
		public function getConfig1()
		{
			$sql = "SELECT ConfigID, ConfigKeyword, ConfigCode, ConfigValue FROM ".TBLPREFIX."configuration1 WHERE 1";	
			$sql_res = db::get_all($sql);
			return $sql_res;
		}
		
		public function GetCMSPageList($GroupName,$DataArray=array("CP.internal_url","CP.IsInternalLink","CP.external_url","CP.IsExternalLink","CP.CMSPagesName", "CP.CMSPagesNameINURL", "CP.LoginRequired"))
		{
			//echo $GroupName;
			$Fields	= implode(',',$DataArray);
			$sql = "SELECT $Fields, CP.CMSPagesTitle"._DBLANG_." as CMSPagesTitle
					FROM ".TBLPREFIX."cmspages AS CP INNER JOIN ".TBLPREFIX."cmspagegroup AS CPG ON CP.CMSPageGroupID = CPG.CMSPageGroupID 
					WHERE CPG.Status = '1' AND CP.Status = '1'  
					AND CP.ShowLink = '1' AND CPG.Title='".$GroupName."' ORDER BY SortBy";			
			//echo $sql;exit;
			$res = db::get_all($sql);
			//dump($res);
			if(count($res)>0)
			{
				return $res;
			}
			else
			{	$this->setErrorMsg('E1102');	}
		}
		
		
		public function GetPageCMSDetails($PageKeword = '',$DataArray=array("PMV.PMV_id","PMT.PMT_captionEN","PMT.PMT_captionES","PMT.PMT_contentEN","PMT.PMT_contentES","PMV.PMV_descEN","PMV_descES","PMV.PMV_metaTitleEN","PMV.PMV_metaTitleES","PMV.PMV_metaDescEN","PMV.PMV_metaDescES","PMV.PMV_metaKeywordEN","PMV.PMV_metaKeywordES","PMT.PMT_Keyword"))
		{
			$Fields	= implode(',',$DataArray);
			$sql = "SELECT $Fields 
					FROM ".TBLPREFIX."pagemetavalue AS PMV LEFT JOIN ".TBLPREFIX."pagemetatext AS PMT ON PMV.PMV_id = PMT.PMT_PMVid
					WHERE PMV.PMV_pageKeyword = '".$PageKeword."'";
			//echo $sql;exit; 		
			/*$sql = "SELECT PMV.PMV_id,PMV.PMV_metaTitleEN,PMV.PMV_metaTitleES,PMV.PMV_metaDescEN,PMV.PMV_metaDescES,
					PMV.PMV_metaKeywordEN,PMV.PMV_metaKeywordES,PMV.PMV_pageKeyword,PMV.PMV_pagenameEN,
					PMV.PMV_pagenameES,PMV.PMV_descEN,PMV_descES,
			 		PMT.PMT_captionEN,PMT.PMT_captionES,PMT.PMT_descEN,PMT.PMT_descES,PMT.PMT_contentEN,PMT.PMT_contentES,PMT.PMT_Keyword
					FROM ".TBLPREFIX."pagemetavalue AS PMV LEFT JOIN ".TBLPREFIX."pagemetatext AS PMT ON PMV.PMV_id = PMT.PMT_PMVid
					WHERE PMV.PMV_pageKeyword = '".$PageKeword."'";*/
			//echo $sql;exit; 
			$res = db::get_all($sql);	
			$arrPageDetail = array();
			if(count($res)>0)
			{
				$intId = 0;
				foreach($res as $key => $arrValue)
				{
						$arrPageDetail['caption'.$arrValue['PMT_Keyword']] = $arrValue["PMT_caption"._DBLANG_];
						$arrPageDetail[$arrValue['PMT_Keyword']] = $arrValue["PMT_content"._DBLANG_];
						//$arrPageDetail['pageDescription'.$arrValue['PMT_Keyword']] = $arrValue["PMT_desc"._DBLANG_];	
				}
				$Meta = array();
				//dump($res);
				$Meta = array("MetaTitle"=>$res[0]["PMV_metaTitle"._DBLANG_],
							  "MetaDesc"=>$res[0]["PMV_metaDesc"._DBLANG_],
							  "MetaKeyword"=>$res[0]["PMV_metaKeyword"._DBLANG_]);
							  
				$PageDetail =  array();
				$PageDetail =  array_merge($arrPageDetail,$Meta);
				//dump($PageDetail);
				return $PageDetail;
			}
			else
			{	$this->setErrorMsg('E32001');	}
		}
		
		public function GetCountryListDB($DataArray,$Condition='',$Order='')
		{
			$Where		= " WHERE 1=1";
			$Where  	.=$Condition;
			$Fields		= implode(",",$DataArray);
			$Sql		= "SELECT $Fields FROM ".TBLPREFIX."country";
			$Res		= db::get_all($Sql.$Where.$Order);
			return (count($Res)>0)?$Res:array();	
		}
		
		public function checkEmailDuplicacy($EmailAddress)
		{
			$mailStatus=TRUE;
			if(trim($EmailAddress)<>'')
			{
				$sql = "select RU_EmailID from ".TBLPREFIX."registeredusers where RU_EmailID='".$EmailAddress."'";
				$row = db::get_all($sql);
				
				if(count($row)>0)
				{
					$mailStatus = FALSE;
				} 
			}else{
				$mailStatus = FALSE;
			}
			return $mailStatus;
		}
		
		public function checkGroupCodeDuplicacy($groupCode)
		{
			$Status=TRUE;
			if(trim($groupCode)<>'')
			{
				$sql = "select Camp_Code from ".TBLPREFIX."campaign where Camp_Code='".$groupCode."'";
				$row = db::get_row($sql);
				
				if(count($row['Camp_Code'])>0)
				{
					$Status = FALSE;
				} 
			}else{
				$Status = FALSE;
			}
			return $Status;
		}
		
		public function GetStateName($Abr)
		{
			$Sql	= "SELECT State_Name FROM ".TBLPREFIX."states WHERE State_Country = 'US' AND State_Value='".$Abr."'";
			$Res	= db::get_row($Sql);
			return (isset($Res['State_Name']))?$Res['State_Name']:'';	
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1,$Status=0)
		{
			EnPException::writeProcessLog('setErrorMsg function Call for Erroe Code :: '.$ErrCode);
			$this->P_ErrorCode=$ErrCode;
			$this->P_ErrorMessage=$ErrCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode,$MsgType=2,$Status=1)
		{
			EnPException::writeProcessLog('setConfirmationMsg function Call For Confirmation Code :: '.$ConfirmCode);
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}
	
	
		public	function getTopNavigationArray($lang)
		{
			
			if($lang=="en")
			{
				$MenuArray = array(array("Caption"=>"<span>Donate</span> <span>Now</span>","link"=>"campaign/index/campaigncategorylist","IsChild"=>'0'),array("Caption"=>"<span>Start a</span> <span>Fundraiser</span>","link"=>"start-a-fundraiser.html","IsChild"=>'0'),array("Caption"=>"<span>Non-Profit</span> <span>Registration</span>","link"=>"nonprofit-registration.html","IsChild"=>'0'),array("Caption"=>"<span>About</span> <span>Us</span>","link"=>"about-us.html","IsChild"=>1,
					"child"=>array(array("Caption"=>"Pricing","link"=>"pricing.html"),array("Caption"=>"How it works","link"=>"how-it-works.html"),array("Caption"=>"Who we are","link"=>"who-we-are.html"),array("Caption"=>"Give back","link"=>"giveback-program.html"),array("Caption"=>"Media & Resources","link"=>"media--resources.html"),array("Caption"=>"Testimonials","link"=>"testimonials.html"),array("Caption"=>"FAQs","link"=>"faqs.html"),array("Caption"=>"Contact","link"=>"contact.html"),array("Caption"=>"Terms &amp; conditions","link"=>"terms-and-conditions.html"))));
			}
		
			else if($lang=="es")
			{
					$MenuArray = array(array("Caption"=>"<span>Done</span> <span>Ahora</span>","link"=>"campaign/index/campaigncategorylist","IsChild"=>'0'),array("Caption"=>"<span>Iniciar una recaudación</span><span> de fondos</span>","link"=>"start-a-fundraiser.html","IsChild"=>'0'),array("Caption"=>"<span>Registro Sin</span><span> Fines De Lucro</span>","link"=>"nonprofit-registration.html","IsChild"=>'0'),array("Caption"=>"<span>Sobre</span> <span> Nosotros</span>","link"=>"about-us.html","IsChild"=>'1',
					"child"=>array(array("Caption"=>"Precios","link"=>"pricing.html"),array("Caption"=>"Cómo funciona","link"=>"how-it-works.html"),array("Caption"=>"Quienes somos","link"=>"who-we-are.html"),array("Caption"=>"programa giveback","link"=>"giveback-program.html"),array("Caption"=>"Medios y Recursos","link"=>"media--resources.html"),array("Caption"=>"Testimonios","link"=>"testimonials.html"),array("Caption"=>"Preguntas frecuentes","link"=>"faqs.html"),array("Caption"=>"contacto","link"=>"contact.html"),array("Caption"=>"Términos &amp; condiciones","link"=>"terms-and-conditions.html"))));
			}
			
			return $MenuArray;
			
		}
	

	public function GetUT1LoginImage($UserID)
	{
		$Sql	= "SELECT RU_ProfileImage FROM ".TBLPREFIX."registeredusers WHERE RU_ID=".$UserID;
		$row	= db::get_row($Sql);
		return (isset($row['RU_ProfileImage']))?$row['RU_ProfileImage']:"";	
	}
	
	public function GetUT2LoginImage($UserID)
	{
		$Sql	= "SELECT NPOLogo FROM ".TBLPREFIX."npouserrelation WHERE USERID=".$UserID;
		$row	= db::get_row($Sql);
		return (isset($row['NPOLogo']))?$row['NPOLogo']:"";	
	}
	
}
?>