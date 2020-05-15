<?php
	class Bottom_Controller extends Controller
	{
		public function index()
		{	
			$tpl = new View;		
		    $tpl->draw("bottom/bottom" );
			
		}
	}
?>