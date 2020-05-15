<?php
	class Email_Controller extends Controller
	{		
		function __construct() {
			$this->load_model('Email', 'objemail');
			$this->load_model('UserType1', 'objcust');
		}
		
		public function index() {
			$Keyword = 'Template1';
			$where = " Where Keyword='" . $Keyword . "'";
			$DataArray = array('TemplateID', 'TemplateName', 'EmailTo', 'EmailToCc', 'EmailToBcc', 'EmailFrom', 'Subject_' . _DBLANG_);
			$EmailDetail = $this->objemail->GetTemplateDetail($DataArray, $where);
			$Data = array('RU.RU_ID', 'RU.RU_FistName', 'RU.RU_LastName', 'RU.RU_EmailID', 'RU.RU_ProfileImage', 'RU.RU_City', 'RU.RU_ZipCode', 'RU.RU_Address1', 'RU.RU_Address2', 'RU.RU_EmailID');
			$CustDetail = $this->objcust->GetUserDetails($Data);
			$tpl = new View;
			$tpl->assign('Detail', $CustDetail);
			$HTML = $tpl->draw('email/' . $EmailDetail['TemplateName'], true);
			$InsertDataArray = array(
				'FromID'		=>$CustDetail['RU_ID'], 
				'CC'			=>$EmailDetail['EmailToCc'], 
				'BCC'			=>$EmailDetail['EmailToBcc'], 
				'FromAddress'	=>$EmailDetail['EmailTo'],
				'ToAddress'		=>$CustDetail['RU_EmailID'],
				'Subject'		=>$EmailDetail['Subject_'. _DBLANG_],
				'Body'			=>$HTML,
				'Status'		=>'0',
				'SendMode'		=>'1',
				'AddedOn'		=>getDateTime());
				
			$id = $this->objemail->InsertEmailDetail($InsertDataArray);
			echo 'Completed'; 
			exit;
		}	
	}
?>