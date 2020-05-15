<?php
	$ch = curl_init("http://dev.donasity.com/adminpanel/recurringtransaction/runTransaction");	
	curl_setopt($ch, CURLOPT_HEADER, 0);	
	curl_exec($ch);		
	curl_close($ch);	
?>