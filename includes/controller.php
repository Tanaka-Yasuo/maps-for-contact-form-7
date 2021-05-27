<?php

/**
 * Registers main scripts and styles.
 */
add_action(
        'wp_enqueue_scripts',
	function () {
		$options = MAPS_FOR_CF7_Options::get_instance();
                $settings = $options->get_option();
                $api_key = $settings[ MAPS_FOR_CF7_Options::api_key ];
                $language = $settings[ MAPS_FOR_CF7_Options::language ];
                $region = $settings[ MAPS_FOR_CF7_Options::region ];

		$assets = array();
                $assets = wp_parse_args(
                        $assets,
                        array(
                                'src' => "https://maps.googleapis.com/maps/api/js?key={$api_key}&language={$language}&region={$region}&libraries=places&callback=maps_for_contact_form_7_initialize",
                                'dependencies' => array(),
                                'version' => MAPS_FOR_CF7_VERSION,
                                'in_footer' => ( 'header' !== wpcf7_load_js() ),
                        ) );
                wp_register_script(
                        'maps-for-contact-form-7-places',
                        $assets['src'],
                        $assets['dependencies'],
                        $assets['version'],
                        $assets['in_footer']
                );
		add_action( 'script_loader_tag', function($tag, $handle){
                        if ($handle === 'maps-for-contact-form-7-places') {
                                return str_replace(' src=', " async defer src=", $tag);
                        }
                        return $tag;
                }, 10, 2 );
                wp_enqueue_script( 'maps-for-contact-form-7-places' );
		
		$assets = array();
                $assets = wp_parse_args(
                        $assets,
                        array(
                                'src' => map_wpcf7_plugin_url( 'includes/js/maps.js' ),
                                'dependencies' => array( 'jquery' ),
                                'version' => MAPS_FOR_CF7_VERSION,
                                'in_footer' => ( 'header' !== wpcf7_load_js() ),
                        ) );
                wp_register_script(
                        'maps-for-contact-form-7-maps',
                        $assets['src'],
                        $assets['dependencies'],
                        $assets['version'],
                        $assets['in_footer']
                );
		wp_localize_script(
                        'maps-for-contact-form-7-maps',
                        'mapsForContactForm7ShortcodeAjax',
                        array(
                                'url' => admin_url( 'admin-ajax.php' )
                        ) );
                wp_enqueue_script( 'maps-for-contact-form-7-maps' );

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

