<?php
class Logout_Controller extends Controller
{
	public function index()
	{
		unsetSession("DonasityAdminLoginDetail");
		setcookie("DonasityAdminLoginDetail","",time()-3600,"/"); 
		header("Location:".URL.'login');
	}
}
?>