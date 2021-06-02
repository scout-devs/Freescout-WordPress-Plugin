<?php
if ( ! function_exists('fsc_conversations_cpt') ) {
	function fsc_conversations_cpt() {
		$form_caps = array();
		$labels = array(
			'name'                => _x( 'Freescout Conversations', 'Post Type General Name', 'freescout-contact' ),
			'singular_name'       => _x( 'Menu Item', 'Post Type Singular Name', 'freescout-contact' ),
			'menu_name'           => __( 'Freescout Conversations', 'freescout-contact' ),
			'parent_item_colon'   => __( 'Parent Menu  Item:', 'freescout-contact' ),
			'all_items'           => __( 'Freescout Conversations', 'freescout-contact' ),
			'view_item'           => __( 'View Menu Item', 'freescout-contact' ),
			'add_new_item'        => __( 'Add New Menu Item', 'freescout-contact' ),
			'add_new'             => __( 'New Menu Item', 'freescout-contact' ),
			'edit_item'           => __( 'Edit Menu Item', 'freescout-contact' ),
			'update_item'         => __( 'Update Menu Item', 'freescout-contact' ),
			'search_items'        => __( 'Search Freescout Conversations', 'freescout-contact' ),
			'not_found'           => __( 'No Freescout Conversations found', 'freescout-contact' ),
			'not_found_in_trash'  => __( 'No Freescout Conversations found in trash', 'freescout-contact' ),
		);
		$args = array(
			'label'               => __( 'fsc_conversations', 'freescout-contact' ),
			'description'         => __( 'Freescout Conversations', 'freescout-contact' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'capabilities'        => $form_caps,
			'public'              => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'		  => 'admin.php?page=fsc_conversations_cpt',	
			'show_in_admin_bar'   => false,
			'menu_position'       => 5,
			'menu_icon'           => plugins_url() . "/img/contact.png",
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'map_meta_cap'        => true,
			'query_var'			  => true,
		);
		register_post_type( 'fsc_conversations', $args );
	}
	add_action( 'init', 'fsc_conversations_cpt', 18 );
}