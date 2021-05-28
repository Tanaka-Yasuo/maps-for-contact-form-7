<?php

class MAPS_FOR_CF7_Taxonomy {
	private static $instance;

	private function __construct() {
	}
	public static function get_instance() {
                if ( empty( self::$instance ) ) {
                        self::$instance = new self;
                }

                return self::$instance;
        }
	public static function get_name( $id, $tag ) {
		return ( $id . '-' . $tag[ 'name' ] . '-maps-for-cf7' );
	}
	public static function resolve_name( $taxonomy ) {
		$matches = array();
		$pattern = "/\d+-([\w-]+)-maps-for-cf7/";
		if ( preg_match( $pattern, $taxonomy, $matches ) == 1 ) {
			return $matches[ 1 ];
		}
		return $taxonomy;
	}
	public function register_taxonomies() {
		if ( !class_exists( 'WPCF7_FormTagsManager' ) ) {
			return;
		}
		$manager = WPCF7_FormTagsManager::get_instance();
		$options = MAPS_FOR_CF7_Options::get_instance();

		$settings = $options->get_option();
		$form_ids = $settings[ MAPS_FOR_CF7_Options::form_ids ];

		$contact_forms = WPCF7_ContactForm::find();
		foreach ( $contact_forms as $contact_form ) {
			if ( !MAPS_FOR_CF7_ContactForm::in_form_ids(
				$form_ids,
				$contact_form ) ) {
				continue;
			}
			$content = $manager->normalize(
				$contact_form->prop( 'form' ) );
			$tags = $manager->scan( $content );
			$this->register_taxonomies_from_tags(
				$contact_form->id(), $tags );
		}
	}
	private function register_taxonomies_from_tags( $id, $tags ) {
		foreach ( $tags as $tag ) {
			switch ( $tag[ 'basetype' ] ) {
			case 'radio':
				break;
			default:
				continue 2;
			}
			if ( empty( $tag[ 'name' ] ) ) continue;
			
			$name = $this->get_name( $id , $tag );
			register_taxonomy(
				$name,
				MAPS_FOR_CF7_Post::post_type,
				array(
					'hierarchical'      => false,
					'show_ui'           => false,
					'show_in_nav_menus' => false,
					'public'            => false,
					'label'             => $name
				)
			);
			foreach ( $tag[ 'raw_values' ] as $raw_value ) {
				wp_insert_term(
					$raw_value,
					$name );
			}
		}
	}
}

