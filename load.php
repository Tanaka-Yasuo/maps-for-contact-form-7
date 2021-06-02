<?php

require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/capabilities.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/functions.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/contact-form.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/post.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/taxonomy.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/options.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/shortcode.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/rest-api.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/i10n.php';
require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/select_values.php';

if ( is_admin() ) {
	require_once MAPS_FOR_CF7_PLUGIN_DIR . '/admin/admin.php';
} else {
        require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/controller.php';
}

class MAPS_FOR_CF7 {
	public static function load_modules() {
		self::load_module( 'place' );
	}
	protected static function load_module( $mod ) {
                $dir = MAPS_FOR_CF7_PLUGIN_MODULES_DIR;

                if ( empty( $dir ) or ! is_dir( $dir ) ) {
                        return false;
                }

                $files = array(
                        path_join( $dir, $mod . '/' . $mod . '.php' ),
                        path_join( $dir, $mod . '.php' ),
                );

                foreach ( $files as $file ) {
                        if ( file_exists( $file ) ) {
                                include_once $file;
                                return true;
                        }
                }

                return false;
        }
}

add_action( 'plugins_loaded', function() {
        MAPS_FOR_CF7::load_modules();

        /* Shortcodes */
	add_shortcode( 'maps-for-contact-form-7', function( $atts, $content = null, $code = '' ) {
		$shortcode = new MAPS_FOR_CF7_Shortcode( $atts, $content, $code );
		return $shortcode->html();
	} );
	MAPS_FOR_CF7_Shortcode::add_action();
	maps_for_cf7_load_textdomain();
}, 11, 0 );

add_action( 'init', function() {
	$post = MAPS_FOR_CF7_Post::register_post_type();
	$taxonomy = MAPS_FOR_CF7_Taxonomy::get_instance();
	$taxonomy->register_taxonomies();
	MAPS_FOR_CF7_ContactForm::add_action();
/*
        wpcf7_get_request_uri();
        wpcf7_register_post_types();

        do_action( 'wpcf7_init' );
*/
}, 10, 0 );

register_activation_hook(
        __FILE__,
        function () {
                register_uninstall_hook(
                        __FILE__,
			function() {
				$options = MAPS_FOR_CF7_Options::get_instance();
				$options->delete_option();
			} );
	} );

add_filter( 'pre_update_option_active_plugins', function( $active_plugins, $old_value ) {
	$maps_for_cf7_dirname = basename( dirname( __FILE__ ) );
	$contact_form_7_index = -1;
	$maps_for_cf7_index = -1;

	foreach ( $active_plugins as $index => $path ) {
		$dirname = dirname( $path );
		if ( $dirname == 'contact-form-7' ) {
			$contact_form_7_index = $index;
		}
		if ( $dirname == $maps_for_cf7_dirname ) {
			$maps_for_cf7_index = $index;
			$maps_for_cf7_path = $path;
		}
	}
	if ( $maps_for_cf7_index >= 0
		&& $contact_form_7_index >= 0 ) {
		if ( $maps_for_cf7_index < $contact_form_7_index ) {
		     	array_splice(
				$active_plugins,
				$maps_for_cf7_index, 1 );
			$active_plugins[] = $maps_for_cf7_path;
		}
	}
	return $active_plugins;
}, 10, 2 );
