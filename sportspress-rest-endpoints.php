<?php

/*
Plugin Name: Sportspress REST Endpoints
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: This plugin exposes the data of your existing Sportspress via REST endpoints.
Version: 0.0.1
Author: mmarcec007
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

add_action( 'rest_api_init', function () {
    require plugin_dir_path( __FILE__ ) . 'includes/VenuesController.php';
    $controller = new VenuesController();
    $controller->register_routes();
} );
