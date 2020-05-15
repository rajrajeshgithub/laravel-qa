<?php

        require_once LIBRARY_DIR . "Loader.php";

        $loader = Loader::get_instance();
		$loader->init_exception();           // exception handler
        $loader->init_settings();           // load the settings
        $loader->init_db();
        $loader->init_session();
        $loader->init_language('en');           // set the language
        $loader->auth_user();
        $loader->init_theme();              // set theme
        $loader->init_js();
		$loader->init_config();

        


	#--------------------------------
	# Auto Load the Controller
	# init_route set the controller/action/params
	# to load the controller
	#--------------------------------
    $loader->auto_load_controller();




	#--------------------------------
	# Load model
	# load the model and assign the result
	# @params model, action, params, assign_to
	#--------------------------------
	//$loader->load_menu();




	#--------------------------------
	# Assign Layout variables
	#--------------------------------
	$loader->assign('title','donasity');



	#--------------------------------
	# Print the layout
	#--------------------------------
	$loader->draw();


?>
