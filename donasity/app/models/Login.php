<?php 
class Login_Model extends Model
{
	
	public function __construct()
	{
		
	}
	
	
	
	public function ProcessUserDetailFromFB()
		{
			$this->SetSessionValue();
			$this->GetUserDetails();
		}
		
		private function SetSessionValue()
		{
			$app_id ='1650437265169437';
			$app_secret='d8194943f21d5a2f43213c3c81be4842';
			$redirect_url='http://dev.donosity.com/login/FacebookThroughRedirect';	

			$helper = new FacebookRedirectLoginHelper($redirect_url,$app_id, $app_secret);
			FacebookSession::setDefaultApplication($app_id, $app_secret);
			dump($_SESSION);
			if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) 
			{
				$this->session = new FacebookSession( $_SESSION['fb_token'] );
			try {
					if ( !$this->session->validate() ) {
						$this->session = null;
					}
				} catch ( Exception $e ) {
					$this->session = null;
				}
			}
			if ( !isset( $this->session ) || $this->session === null )
			{
				try
				{
					$this->session = $helper->getSessionFromRedirect();
				}
				catch(FacebookRequestException $ex)
				{}
				catch( Exception $ex )
				{}
			}
			if(isset($this->session))
			{ 
				$_SESSION['fb_token'] = $this->session->getToken();
				
			}else
			{
				$this->setErrorMsg('E5028');
			}
		
		}
		private function GetUserDetails()
		{
		  $request= (new FacebookRequest($this->session, 'GET', '/me' ));
		  $response=$request->execute();
		  $object = $response->getGraphObject();
		  $graph_user = $response->getGraphObject(GraphUser::className());
		  $this->FCustomerDetail['FacebookID']	=  $graph_user->getId();
		  $this->FCustomerDetail['FirstName']	= $graph_user->getFirstName();
		  $this->FCustomerDetail['LastName']	=$graph_user->getLastName();
		  $this->FCustomerDetail['Email'] 		= $graph_user->getProperty('email');
		}
		
	
	public function processLogin_DB($fields,$condition,$orderby)
	{
		EnPException::writeProcessLog('Login_Model :: processLogin Function For Login');
		$getFields = implode(',',$fields);
		$sql = "SELECT $getFields FROM ".TBLPREFIX."registeredusers RU
 				LEFT JOIN ".TBLPREFIX."npouserrelation NRU ON (RU.RU_ID=NRU.USERID)
				where 1=1 $condition $orderby";
		//echo $sql;exit;
		$res  = db::get_row($sql);
		if(!count($res))
		{
			$res = array();
		}
		return $res;
	}
	
	public function UpdateLoginDate($DataArray,$UserID)
	{
		return db::update(TBLPREFIX.'registeredusers',$DataArray,'Admin_ID='.$UserID);
	}
	
	
	public function ResetPassword_DB($fields,$condition,$orderby)
	{
		EnPException::writeProcessLog('Login_Model :: processLogin Function For Login');
		$getFields = implode(',',$fields);
		$sql = "SELECT $getFields FROM ".TBLPREFIX."registeredusers where 1=1 $condition $orderby";
		//echo $sql;exit;
		$res  = db::get_all($sql);
		return $res;

	}
	
	public function UpdatePassword($DataArray,$where)
	{
		db::update(TBLPREFIX.'registeredusers',$DataArray,$where);
		return db::is_row_affected()?1:0;
	}
}
?>