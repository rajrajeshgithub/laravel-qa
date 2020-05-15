<?php
	class Home_Model extends Model {	
		public $i, $j;
		
		function __construct() {
			$this->i = 0;
			$this->j = 0;
		}	
		
		public function getModuleListDB() {
			EnPException :: writeProcessLog('Home_Model :: getModuleListDB Function For Login');
			
			$this->createArray();
			
			$sql = "SELECT Module_ID, Module_ParentID, Module_Caption, Module_Url FROM " . TBLPREFIX . "modules WHERE 1 AND Module_Active='1' ORDER BY Module_ParentID";
			
			$res = db :: get_all($sql);
			$res = count($res) ? $res : 0;
			$this->helloTest = $res;
			
			dump($this->meargeArray($res));	
			dump($this->newMeargeArray);		
			$arrModuleAll = $this->checkParent();
			//dump($arrModuleAll);
			return $arrModuleAll;	
		}
		
		public function createArray($ParentID=0,$level='1')
		{
			
			$sql = "SELECT Module_ID,Module_ParentID,Module_Caption,Module_Url FROM ".TBLPREFIX."modules WHERE Module_ParentID=".$ParentID."  AND Module_Active='1' ORDER BY Module_ParentID";
			//echo "</br>";
			$res = db::get_all($sql);
			$res = count($res)?$res:0;
			
			//dump($res);
			if($res)
			{
				//$this->arrModule = $res;
				//$this->arrModule[]['hello'];
				foreach($res as $key => $value)
				{
					$this->arrModule[$value['Module_ID']]['Module_ID'] 			= $value['Module_ID'];
					$this->arrModule[$value['Module_ID']]['Module_ParentID'] 	= $value['Module_ParentID'];
					$this->arrModule[$value['Module_ID']]['Module_Caption'] 	= $value['Module_Caption'];
					$this->arrModule[$value['Module_ID']]['Module_Url'] 		= $value['Module_Url'];
					$this->arrModule[$value['Module_ID']]['Depth'] 				= $level;					
					//$this->arrModule[$value['Module_ID']]['Child'] 				= 1;					
					/*if($value['Module_ParentID']==0)
					{	
						$this->arrModule[$i] = $value;
					}
					else
					{
						//echo "hello";exit;					
						$this->arrModule[$value['Module_ID']] = $value;
					}	*/	
					
					$this->createArray($value['Module_ID'],$level+1);
				}
				//dump($res);				
			}
		}
		function meargeArray($arrMearge,$parent_id =0)
		{
			$return = array();
			
				foreach($arrMearge as $key => $value)
				{
					if($value['Module_ParentID']==$parent_id)
					{
						$return[$key] = $value;
						$return[$key]['children'] = $this->meargeArray($arrMearge,$value['Module_ID']);	
						
					}
					
				}			
			
			return $return;			
		}
		
		function newMeargeArray()
		{
			foreach($this->helloTest as $key1 => $value1)
			{
				echo $value['Module_ID']."==".$value1['Module_ParentID']."</br>";
				if($value['Module_ID']==$value1['Module_ParentID'])
				{
					$this->newMeargeArray[$key]['child_array'][$key1] = $value1;
					//dump($this->newMeargeArray[$key]['child_array']);
				}					
			}
		}
		
		
		private function checkParent()
		{
			foreach($this->arrModule as $key => $value)
			{
				$newArrModule[$key] = $value;	
				if($this->isParentFound($this->arrModule,$key))
				{
					$newArrModule[$key]['Child'] = 1;					
				}
				else
				{
					$newArrModule[$key]['Child'] = 0;
				}
				
				$level = "&nbsp;";
				for($i=1;$i<$value['Depth'];$i++)
				{
					$level .= $level; 					
				}
				$newArrModule[$key]['level'] = $level;			
			}			
			return $newArrModule;
		}
		
		private function isParentFound(&$arrModule,$nodeID)
		{
			//echo $nodeID."</br>";
			//dump($this->arrModule);
			$parent = 0;
			$this->nodeOne=NULL;
			foreach($arrModule as $key => $value)
			{			
				if($value['Module_ParentID']==$nodeID)
				{
					$parent=1;	
				}				
			}
			return $parent;	
		}
	}

?>