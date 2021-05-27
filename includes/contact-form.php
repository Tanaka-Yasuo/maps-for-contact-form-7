<?php

class MAPS_FOR_CF7_ContactForm {
	private static $instance;

        private function __construct() {
        }
        public static function get_instance() {
                if ( empty( self::$instance ) ) {
                        self::$instance = new self;
                }

                return self::$instance;
        }
	public static function in_form_ids( $form_ids, $contact_form ) {
                foreach ( $form_ids as $form_id ) {
                        if ( $contact_form->id() == $form_id ) {
                                return true;
                        }
                }
                return false;
        }
	public static function add_action() {
		add_action(
			'wpcf7_submit',
			array( __CLASS__, 'wpcf7_submit' ),
			10, 2 );
	}
	public static function wpcf7_submit( $contact_form, $result ) {
		if ( !empty( $result['status'] )
		and ! in_array( $result['status'], array( 'mail_sent', 'mail_failed' ) ) ) {
			return;
		}
		$options = MAPS_FOR_CF7_Options::get_instance();

                $settings = $options->get_option();
		$form_ids = $settings[ MAPS_FOR_CF7_Options::form_ids ];

		if ( !self::in_form_ids( $form_ids,  $contact_form) ) {
			return;
		}
		$tags = $contact_form->scan_form_tags();
		if ( !self::has_place( $tags ) ) return;

		$submission = WPCF7_Submission::get_instance();
		$posted_data = $submission->get_posted_data();

		$taxonomy = MAPS_FOR_CF7_Taxonomy::get_instance();
		$taxonomies = array();
		foreach( $posted_data as $name => $values ) {
			$tag = self::get_tag( $tags, $name );

			switch ( $tag[ 'basetype' ] ) {
			case 'radio':
				$taxonomy_name = $taxonomy->get_name(
					$contact_form->id(),
					$tag );
				$terms = array();
				foreach( $values as $value ) {
					$terms[] = $value;
				}
				$taxonomies[] = array(
					'name' => $taxonomy_name,
					'terms' => $terms );
				break;
			case 'place':
				$place = explode( ',', $values[ 0 ] );
				break;
			default:
				continue 2;
			}
		}
		MAPS_FOR_CF7_Post::insert_post(
			$contact_form,
			$place,
			$posted_data,
			$taxonomies );
	}
	public static function get_taxonomies( $contact_form ) {
		$taxonomy = MAPS_FOR_CF7_Taxonomy::get_instance();
		$taxonomies = array();
		$tags = $contact_form->scan_form_tags();
		foreach( $tags as $tag ) {
			switch ( $tag[ 'basetype' ] ) {
			case 'radio':
				$taxonomies[] = $taxonomy->get_name(
					$contact_form->id(),
					$tag );
				break;
			default:
				break;
			}
		}
		return $taxonomies;
	}
	public static function has_place( $tags ) {
		foreach ( $tags as $tag ) {
			$basetype = $tag[ 'basetype' ];

			if ( $basetype == 'place' ) {
				return true;
			}
		}
		return false;
	}
	public static function get_tag( $tags, $name ) {
		foreach ( $tags as $tag ) {
			if ( $tag[ 'name' ] == $name ) return $tag;
		}
		return null;
	}
}

