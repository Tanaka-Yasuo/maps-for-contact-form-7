<?php

/**
 * Registers main scripts and styles.
 */
add_action(
        'wp_enqueue_scripts',
	function () {
		$assets = array();
		$assets = wp_parse_args(
			$assets,
			array(
                        	'src' => map_wpcf7_plugin_url( 'includes/js/place.js' ),
				'dependencies' => array( 'jquery' ),
				'version' => MAPS_FOR_CF7_VERSION,
                        	'in_footer' => ( 'header' !== wpcf7_load_js() ),
                ) );
		wp_register_script(
                        'maps-for-contact-form-7',
                        $assets['src'],
                        $assets['dependencies'],
                        $assets['version'],
                        $assets['in_footer']
                );
		wp_localize_script(
			'maps-for-contact-form-7',
			'mapContactForm7Ajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			) );
		map_wpcf7_enqueue_scripts();

		wp_register_style(
                        'maps-for-contact-form-7',
                        map_wpcf7_plugin_url( 'includes/css/styles.css' ),
                        array(),
                        MAPS_FOR_CF7_VERSION,
                        'all'
                );
                map_wpcf7_enqueue_styles();
	},
        10, 0
);
function map_wpcf7_enqueue_scripts() {
	wp_enqueue_script( 'maps-for-contact-form-7' );
}
function map_wpcf7_enqueue_styles() {
	wp_enqueue_style( 'maps-for-contact-form-7' );
}

