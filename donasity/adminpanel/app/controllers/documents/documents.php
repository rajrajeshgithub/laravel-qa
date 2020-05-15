<?php
	class Documents_Controller extends Controller {
		
		public $P_status, $lastUpdateBy, $loginDetails, $doc_ID, $filterParam, $sortParam, $docType, $P_ErrorCode, $FieldArr, $DocPhysPath;
		// form
		public $document, $docName, $action, $docTitle, $docSorting, $docShowOnWebsite, $docUserId, $webMasterComment, $description, $docRealName;
		
		function __construct() {
			$this->load_model('Documents', 'objDocs');
			$this->lastUpdateBy = getsession('DonasityAdminLoginDetail', 'admin_fullname');
			$this->P_status = 1;
			$this->loginDetails = getsession('DonasityAdminLoginDetail');	
			$this->doc_ID = 0;
			$this->docUserId = 0;
			$this->filterParam = array();
			$this->sortParam = array();
			//$this->docType = 'pdf';
			$this->docType = array('pdf', 'doc', 'docx', 'png', 'jpg', 'ppt', 'pptx', 'txt', 'rtf', 'gif', 'xls', 'xlsx');
		}
		
		public function index($type='list', $docUser_ID=NULL, $doc_ID=NULL) {
			
			if(isset($doc_ID) && $doc_ID <> NULL)
				$this->doc_ID = keyDecrypt($doc_ID);
			
			if(isset($docUser_ID) && $docUser_ID <> NULL)
				$this->docUserId = $docUser_ID;
			
			$this->tpl = new view;
			
			switch(strtolower($type)) {
				case 'add':
					$this->Insert();				
					break;	
				case 'edit':
					$this->Listing();
					$this->Edit();
					$this->tpl->draw('documents/listdocuments');
					break;
				case 'update':
					$this->Update();
					break;
				case 'list':
					$this->Listing();
					$this->tpl->draw('documents/listdocuments');
				break;
				case 'delete':
				    $this->Delete();
					break;
				default :
					$this->Listing();
					$this->tpl->draw('documents/listdocuments');
					break;		
			}
		}
		
		// list all documents
		private function Listing() {
			EnPException :: writeProcessLog('Documents_Controller :: Listing action to view document Details');
			
			$docUserId = (int)keyDecrypt($this->docUserId);
			
			if($docUserId <= 0) {
				$this->SetStatus(false, 'ED10');
				redirect(URL . 'npouser');
			}
			
			$DataArray = array(
				'SQL_CACHE DocID as ID',
				'WebmasterComment',
				'Description',
				'CreatedDate',
				'DocTitle',
				'DocName',
				'DocRealName',
				'DocSorting',
				'DocUserID',
				'DocShowOnWebsite',
				'CreatedByID',
				'LastUpdatedDate');
			
			$this->filterParam = array("DocUserID"=>$docUserId);
			
			$docList = $this->objDocs->GetAllDocuments($DataArray, $this->filterParam, $this->sortParam);
			
			$PagingArr = constructPaging($this->objDocs->pageSelectedPage, $this->objDocs->totalRecord, $this->objDocs->pageLimit);		
			$LastPage = ceil($this->objDocs->totalRecord / $this->objDocs->pageLimit);
			
			$this->tpl->assign('totalRecords', $this->objDocs->totalRecord);
			$this->tpl->assign('docList', $docList);
			$this->tpl->assign('action', 'add');
			$this->tpl->assign('pagingList', $PagingArr['Pages']);
			$this->tpl->assign('pageSelected', $PagingArr['PageSel']);
			$this->tpl->assign('startRecord', $PagingArr['StartPoint']);
			$this->tpl->assign('endRecord', $PagingArr['EndPoint']);
			$this->tpl->assign('lastPage', $LastPage);
			$this->tpl->assign('docDetail', $docList);
			$this->tpl->assign('doc_user_ID', $this->docUserId);
			$this->tpl->assign("doc_ID", $this->doc_ID);
		}
		
		// process insert data
		private function Insert() {
			$this->getFormData();
			$this->ValidateFormData();
			
			$docUserId = (int)keyDecrypt($this->docUserId);
			
			if($docUserId <= 0) {
				$this->SetStatus(false, 'ED10');
				redirect(URL . 'npouser');
			}
			
			if($this->P_status == 0) {
				$this->SetStatus(false, 'ED06');
				redirect(URL . "documents/index/list/" . keyEncrypt($this->docUserId));
			}
			
			$InsertDocumentId = $this->objDocs->DocumentInsert_DB($this->FieldArr);
			
			if($InsertDocumentId != NULL && $InsertDocumentId > 0) {
				if($_FILES['DocName']['name'] != '') {
					$this->doc_ID = $InsertDocumentId;
					$this->DocumentFile() ? $this->SetStatus(true, 'CD01') : $this->SetStatus(false, 'ED05');
				} else 
					$this->SetStatus(true, 'CD01');
					
				redirect(URL . "documents/index/edit/" . $this->docUserId . '/' . keyEncrypt($InsertDocumentId));
			} else {
				$this->SetStatus(false, 'ED06');
				redirect(URL . "documents/index/list/" . $this->docUserId);
			}
			
			/*----update process log------*/
			$userType 	= 'UT2';
			$userID		= $this->loginDetails['admin_id'];
			$userName	= $this->loginDetails['admin_fullname'];
			$sMessage = "Error in image uploading";
			$lMessage = "Error in image uploading";
			if($InsertDocumentId > 0) {
				$sMessage = "Document file uploaded";
				$lMessage = "Document file uploaded with Id - ".$InsertDocumentId;
			}
			
			$DataArray = array(	
				"UType"			=>$userType,
				"UID"			=>$userID,
				"UName"			=>$userName,
				"RecordId"		=>$InsertImage,
				"SMessage"		=>$sMessage,
				"LMessage"		=>$lMessage,
				"Date"			=>getDateTime(),
				"Controller"	=>get_class()."-".__FUNCTION__,
				"Model"			=>get_class($this->objDocs));
					
			$this->objDocs->UpdateProcessLog($DataArray);
			/*-----------------------------*/	
				
		}
		
		// upload document file
		private function DocumentFile() {
			$this->document = count($_FILES['DocName']) > 0 ? $_FILES['DocName'] : '';
			$DocFile = $this->document;
			
			$Ext = strtolower(file_ext($DocFile['name']));
			$DocFileName = strUnique() . "." . $Ext;
			$this->DocPhysPath = DOCUMENT_PATH . $DocFileName;
			
			$oldDocName = request('post', 'oldDocName', 0);
			
			if($oldDocName != NULL) {
				if(file_exists(DOCUMENT_PATH . $oldDocName))
					unlink(DOCUMENT_PATH . $oldDocName);
			}
			
			move_uploaded_file($DocFile["tmp_name"], $this->DocPhysPath);
			
			$DocField = array("DocName"=>$DocFileName, "DocRealName"=>$this->document['name']);
			$DocStatus = $this->objDocs->DocumentUpdate_DB($DocField, $this->doc_ID);
			return $DocStatus;
		}
		
		// get form data
		private function getFormData() {
			EnPException :: writeProcessLog('Documents_Controller :: getFormData action to get all data');
			
			$this->doc_ID		= request('post', 'doc_ID', 1);
			$this->docUserId	= request('post','doc_user_ID',0);
			$this->action		= request('post', 'action', 0);
			$this->docTitle		= request('post', 'DocTitle', 0);
			$this->docSorting 	= (request('post', 'DocSorting', 1) == 0) ? 999 : request('post', 'DocSorting', 1);
			$this->docShowOnWebsite = request('post', 'DocShowOnWebsite', 1);
			$this->webMasterComment = request('post', 'WebmasterComment', 0);
			$this->description = request('post', 'docDescription', 0);
			
			$this->FieldArr	= array(
				"DocID"=>$this->doc_ID,
				"DocTitle"=>$this->docTitle,
				//"DocName"=>$this->docName,
				"DocSorting"=>$this->docSorting,
				"DocShowOnWebsite"=>$this->docShowOnWebsite,
				"CreatedByID"=>$this->loginDetails['admin_id'],
				"DocUserID"=>keyDecrypt($this->docUserId),
				"WebmasterComment"=>$this->webMasterComment,
				"Description"=>$this->description,
				"LastUpdatedDate"=>getdatetime(),
				"CreatedDate"=>getdatetime());
			
		}
		
		// get a document details
		private function Edit() {
			EnPException :: writeProcessLog('Documents_Controller :: Edit action to get all data');
			if(is_numeric($this->doc_ID) && $this->doc_ID > 0) {
				
				$DataArray = array(
					'SQL_CACHE DocID as ID',
					'WebmasterComment',
					'Description',
					'CreatedDate',
					'LastUpdatedDate',
					'DocTitle',
					'DocName',
					'DocRealName',
					'DocSorting',
					'DocUserID',
					'DocShowOnWebsite',
					'CreatedByID');
											
				$where = array("DocID"=>$this->doc_ID);
				
				$DocDetail = $this->objDocs->GetAllDocuments($DataArray, $where);
				$this->tpl->assign("action", 'update');
				$this->tpl->assign("doc_ID", $this->doc_ID);
				$this->tpl->assign("docDetail", $DocDetail[0]);
			} else {
				$this->SetStatus(false, 'ED05');
				redirect(URL . "documents/index/list/" . $this->docUserId . '/' . keyEncrypt($this->doc_ID));
			}
		}
		
		// update process
		private function Update() {
			EnPException :: writeProcessLog('Documents_Controller :: Update action to update all data');
			$this->getFormData();
			$this->ValidateFormData();
			
			$docUserId = (int)keyDecrypt($this->docUserId);
			
			if($docUserId <= 0) {
				$this->SetStatus(false, 'ED10');
				redirect(URL . 'npouser');
			}
			
			try
			{
				if($this->P_status == 0) {
					$this->SetStatus(false, $this->P_ErrorCode);
					redirect(URL . "documents/index/list/" . $this->docUserId . '/' . keyEncrypt($this->doc_ID));
				}
				
				//if($_FILES['DocName']['name'] == '' || $_FILES['DocName']['error'] > 0) 
					//$this->FieldArr['DocName'] = request('post', 'oldDocName', 0);
				unset($this->FieldArr['CreatedDate']);
				$Status	= $this->objDocs->DocumentUpdate_DB($this->FieldArr, $this->doc_ID);
				
				if($Status) {
					if($_FILES['DocName']['name'] != '' || $_FILES['DocName']['error'] == 0) {
						$this->DocumentFile() ? $this->SetStatus(true, 'CD02') : $this->SetStatus(false, 'ED08');
					} else
						$this->SetStatus(true, 'CD02');
				} else 
					$this->SetStatus(false, 'ED07');
				
				/*----update process log------*/
				$userType 	= 'UT2';					
				$userID		= $this->loginDetails['admin_id'];
				$userName	= $this->loginDetails['admin_fullname'];
				$sMessage = "Error in document details updation";
				$lMessage = "Error in document details updation";
				
				$DataArray = array(	
					"UType"		=> $userType,
					"UID"		=> $userID,
					"UName"		=> $userName,
					"RecordId"	=> $this->CI_ID,
					"SMessage"	=> $sMessage,
					"LMessage"	=> $lMessage,
					"Date"		=> getDateTime(),
					"Controller" => get_class()."-".__FUNCTION__,
					"Model"		=> get_class($this->objCIDetails));	
					
				$this->objDocs->updateProcessLog($DataArray);	
				/*-----------------------------*/	
				
			}
			catch(Exception $e) {
				EnPException::exceptionHandler($e);	
			}
			redirect(URL . "documents/index/edit/" . $this->docUserId . '/' . keyEncrypt($this->doc_ID));
		}
		
		// delete document with its file
		private function Delete() {
			EnPException :: writeProcessLog('Documents_Controller :: Delete action to delete data');
			$docUserId = keyDecrypt($this->docUserId);
			
			if(!is_numeric($docUserId) || !is_numeric($this->doc_ID)) {
				$this->SetStatus(false, 'E2001');
				redirect(URL . "documents/index/edit/" . $this->docUserId . '/' . keyEncrypt($this->doc_ID));
		  	}
			
			$DataArray = array('SQL_CACHE DocID as ID', 'DocName');
			$where = array("DocID"=>$this->doc_ID);
			$DocDetail = $this->objDocs->GetAllDocuments($DataArray, $where);
			
			if($DocDetail[0]['DocName'] != NULL && $DocDetail[0]['ID'] == $this->doc_ID) {
				if(file_exists(DOCUMENT_PATH . $DocDetail[0]['DocName']))
					unlink(DOCUMENT_PATH . $DocDetail[0]['DocName']);
			}
			
			$status = $this->objDocs->DocumentDelete_DB($this->doc_ID);
			
			if($this->P_status == 0) {
				$this->SetStatus(false, $this->P_ErrorCode);
				redirect(URL . "documents/index/edit/" . $this->docUserId . '/' . keyEncrypt($this->doc_ID));
			}
			
			if($this->P_status && $status)
				$this->SetStatus(true, 'CD03');
			else
				$this->SetStatus(false, 'ED09');
				
			redirect(URL . "documents/index/list/" . $this->docUserId);
		}
		
		// validate form data
		private function ValidateFormData() {
			if($this->doc_ID == 0) {
				if($_FILES['DocName']['name'] == NULL) {
					$this->SetStatus(0, 'ED01');
					redirect($_SERVER['HTTP_REFERER']);
				}
				
				if($_FILES['DocName']['name'] != NULL) {
					if(!in_array(file_ext($_FILES['DocName']['name']), $this->docType) ) {
						$this->SetStatus(0, 'ED02');
						redirect($_SERVER['HTTP_REFERER']);
					}
				}
			}
			if($this->FieldArr['DocTitle'] == '') {
				$this->SetStatus(0, 'ED03');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
		
		// set process status
		private function SetStatus($Status, $Code) {
			if($Status) {
				$messageParams = array(
					'msgCode'	=>$Code,
					'msg'		=>'Custom Confirmation message',
					'msgLog'	=>0,									
					'msgDisplay'=>1,
					'msgType'	=>2);
					EnPException :: setConfirmation($messageParams);
			} else {
				$messageParams = array(
					'errCode'	=> $Code,
					'errMsg'	=> 'Custom Confirmation message',
					'errOriginDetails' => basename(__FILE__),
					'errSeverity' => 1,
					'msgDisplay' => 1,
					'msgType'	=> 1);
					EnPException :: setError($messageParams);
			}
		}
	}
?>