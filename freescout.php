<?php
/**
* Plugin Name: Freescout
* Description: Integrates Freescout features with WordPress
* Version: 1.0.0
* Author: ExpressTech
* Text Domain: freescout
*/

define('FSC_VERSION', '1.0.0');
// Paths
define('FSC_PATH', plugin_dir_path( __FILE__ ));
define('FSC_DIRNAME', basename( plugin_basename( FSC_PATH ) ));
define('FSC_PLUGIN_URL', plugins_url() . '/' . FSC_DIRNAME);

//api constants
//define('FSC_API_URL', 'https://help-staging.expresstech.io/');
//define('FSC_API_KEY', 'ff0d9fcceea7f65ffb15cf5f9fe30416');

//includes
require_once( 'cpt/fsc.php' );
require_once( 'inc/functions.php' );
require_once( 'inc/admin.php' );


///////////////////////////////////////////////////////////////////////////////
// Languages
function fsc_plugin_setup() {
	load_plugin_textdomain('freescout-contact', false, dirname(plugin_basename(__FILE__)) . '/languages/');
} 
add_action('plugins_loaded', 'fsc_plugin_setup');


//enqueue scripts
function fsc_enqueue_scripts() {
	wp_enqueue_script('jquery');

	if (is_admin()) {
		wp_register_script( 
			'fsc_datatables',
			'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js',
			array('jquery'),
			FSC_VERSION,
			TRUE
		);
		wp_enqueue_script('fsc_datatables');

		wp_register_style( 
			'fsc_datatables_styles', 
			'https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css',
			array(), 
			FSC_VERSION, 
			'all' 
		);
		wp_enqueue_style( 'fsc_datatables_styles');

		wp_register_script( 'fsc_admin_script', FSC_PLUGIN_URL . '/assets/admin/script.js' , '', true, '1.0' );
		wp_enqueue_script('fsc_admin_script');

		wp_register_style( 'fsc_admin_style', FSC_PLUGIN_URL . '/assets/admin/style.css' , array(), FSC_VERSION, 'all' );
		wp_enqueue_style('fsc_admin_style');
	

		wp_localize_script( 'fsc_admin_script', 'fsc_script_ajax_object',
			array( 
				'ajax_url'   => admin_url( 'admin-ajax.php' )
			)
		);  
		

  }

  
}
add_action('init', 'fsc_enqueue_scripts');

//plugin activation hook
register_activation_hook( __FILE__, 'fsc_plugin_activate' );
function fsc_plugin_activate(){
	update_option('fsc_enabled',1);
}