<?php

require_once MAPS_FOR_CF7_PLUGIN_DIR . '/admin/includes/form_list_table.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/admin/includes/post_list_table.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/admin/includes/menu_page.php';

add_action(
	'admin_init',
	function () {
		$options = MAPS_FOR_CF7_Options::get_instance();
		$options->register_setting();
	},
	10, 0
);

add_action(
	'admin_menu',
	function () {
		$options = MAPS_FOR_CF7_Options::get_instance();
		$options->add_options_page();

		$menu_page = MAPS_FOR_CF7_Menu_Page::get_instance();
		$menu_page->add_menu_page();
	},
	9, 0
);

add_action(
	'admin_enqueue_scripts',
	function() {
		wp_enqueue_style( 'maps-for-contact-form-7-admin',
			maps_for_cf7_plugin_url( 'admin/css/styles.css' ),
			array(), MAPS_FOR_CF7_VERSION, 'all'
		);
	},
	10, 1
);


add_filter(
	sprintf( 'plugin_action_links_%s', MAPS_FOR_CF7_PLUGIN_BASENAME ),
	function( $links ) {
		$menu_slug = MAPS_FOR_CF7_Menu_Page::menu_slug;
		$forms_link = sprintf(
			'<a href="admin.php?page=%s">%s</a>',
			$menu_slug,
	       		esc_html( __( 'Contact Forms With Place', 'maps-for-contact-form-7' ) ) );
		array_unshift($links, $forms_link);
		return $links;
	}, 10, 2 );

