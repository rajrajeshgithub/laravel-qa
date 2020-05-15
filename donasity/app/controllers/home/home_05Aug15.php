<?php
//require_once(LIBRARY_DIR.'facebook/autoload.php');
	class Home_Controller extends Controller
	{	
		public $tpl;
		
		function __construct()
		{	
			
			$this->tpl=new View;
			//$Meta	= InitMetaDetail($this->tpl,'English_Page_Name');
		}
		
		function index($name='Home Page')
		{
			
			EnPException::writeProcessLog('Home_Controller :: Index Function Call To View All Records');
			InitMetaDetail($this->tpl,'home');
			
			$this->GetCategory();
			$this->tpl->draw('home/index');
		}
		
		private function GetCategory()
		{
			$this->load_model('Campaign','objCamp');
			$DataArray	= array("NPOCat_ID",
								"NPOCat_ParentID",
								"NPOCat_DisplayName_EN",
								"NPOCat_DisplayName_ES",
								"NPOCat_Image_Name",
								"NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_SortOrder","NPOCat_ShowOnWebsite");
			$Condition	= " AND NPOCat_ShowOnWebsite='1'";
			$Category = $this->objCamp->GetCampaignCategory($DataArray,$Condition);
			
			foreach($Category as &$val)
			{
				$val['CampCat_DisplayName']	= $val[_CampCat_DisplayName_];	
				unset($val[_CampCat_DisplayName_]);
			}
			$this->tpl->assign("Category",$Category);
		}
		
		
		function facebookLogin()
		{
			//$objFb = new FacebookRedirectLoginHelper();
			//echo $objFb;exit;	
		}
		
		function generatealphanumeric()
		{
			//================start=================================================
			ini_set('max_execution_time', 3000);
			$TempArr	= array();
			for($i=1;$i<=500000; $i++)
			{
				$TempArr[]	= GenerateUniqueAlphaNumeric(15);
			}
			echo "Total count".count($TempArr);echo "<br/>";
			echo "Total unique count".count(array_unique($TempArr));echo "<br/>";
			dump($TempArr);
			//================end=================================================
		}
	}
?>