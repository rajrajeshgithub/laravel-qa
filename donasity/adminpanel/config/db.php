<?php

	// default database	
/*	
	$server	  = "mysql";
	$hostname = "devdonasitymysql01.cvdcbz9w09po.us-east-1.rds.amazonaws.com:3306";
    $username = "donasitydba";
	$password = "d0nasity2015";
	$database = "devdonasitymysql";*/


	

	if(strtolower($_SERVER['HTTP_HOST'])=="pdc" || strtolower($_SERVER['HTTP_HOST'])=="localhost" || strtolower($_SERVER['HTTP_HOST'])=="192.168.0.98")
	{
		$server = "mysql";
		$hostname = "localhost";
		$username = "root";
		$password = "P@q2w3efg";
		$database = "donasity_db";

	}	
	if(strtolower($_SERVER['HTTP_HOST'])=="dev.donasity.com")
	{
		$server	  = "mysql";
		$hostname = "devdonasitymysql01.cvdcbz9w09po.us-east-1.rds.amazonaws.com:3306";
		$username = "donasitydba";
		$password = "d0nasity2015";
		$database = "devdonasitymysql";		

	}
	if(strtolower($_SERVER['HTTP_HOST'])=="donasity.com" || strtolower($_SERVER['HTTP_HOST'])=="www.donasity.com")
    {
		$server	  = "mysql";
		$hostname = "prddonasityaurora01-cluster.cluster-cvdcbz9w09po.us-east-1.rds.amazonaws.com:3306";
		$username = "donasitydba";
		$password = "d0nasity2015";
		$database = "prddonasitymysql_01";	
	}


	if( !defined("DB_PREFIX" ) )
		define( "DB_PREFIX", "RAIN_" );
	
?>