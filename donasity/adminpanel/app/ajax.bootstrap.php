<?php
        // include the Loader class
        require_once LIBRARY_DIR . "Loader.php";

        // start the loader
        $loader = Loader::get_instance();

        Loader::configure("controller_extension", AJAX_CONTROLLER_EXTENSION );
        Loader::configure("controller_class_name", AJAX_CONTROLLER_CLASS_NAME );

        // enable the ajax mode
        $loader->ajax_mode();

        $loader->init_settings();
        $loader->init_language('en');
		$loader->init_exception();           // exception handler
        $loader->init_db();
        $loader->auth_user();
		$loader->init_theme();              // set theme
        $loader->init_session();
        $loader->auto_load_controller();
		$loader->init_config();
		
        $loader->ajax_auto_load_controller();

// -- end