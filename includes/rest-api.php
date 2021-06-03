<?php
class MAPS_FOR_CF7_Rest {
	const min_lat = 0.1;
	const min_lng = 0.1;

	public static function getmarkerinfos() {
		$arg = json_decode( 
			stripslashes ( rawurldecode( $_GET[ 'query' ] ) )
		);
		$bounds = $arg->bounds;
		$form_id = $arg->form_id;
		$form = $arg->form;

		$contact_forms = WPCF7_ContactForm::find( array(
			'p' => $form_id,
		) );
                $contact_form = $contact_forms[ 0 ];
		$bounds_array = self::divide_bounds( $bounds );

		$posts = self::get_posts(
			$bounds,
			$form_id,
			$form,
			$bounds_array );
		$lat_lng = self::get_lat_lng( $bounds_array );

		$taxonomies = MAPS_FOR_CF7_ContactForm::get_taxonomies(
			$contact_form );
		$key_values = array();
		if ( $lat_lng[ 'lat' ] > self::min_lat
			&& $lat_lng[ 'lng' ] > self::min_lng ) {
			$key_values = self::aggregate_posts_by_lat_lng(
				$key_values,
				$posts,
				$taxonomies,
				$lat_lng );
		} else {
			$key_values = self::aggregate_posts(
				$key_values,
				$posts,
				$taxonomies );
		}
		$markerInfos = [];
		foreach ( $key_values as $key => $value ) {
			$markerInfos[] = $value;
		}
		wp_send_json( $markerInfos );
                return;
	}
	public static function getrank() {
		$arg = json_decode( 
			stripslashes ( rawurldecode( $_GET[ 'query' ] ) )
		);
		$bounds = $arg->bounds;
		$form_id = $arg->form_id;
		$form = $arg->form;

		$contact_forms = WPCF7_ContactForm::find( array(
			'p' => $form_id,
		) );
                $contact_form = $contact_forms[ 0 ];
		$bounds_array = self::divide_bounds( $bounds );

		$posts = self::get_posts(
			$bounds,
			$form_id,
			$form,
			$bounds_array );

		$taxonomies = MAPS_FOR_CF7_ContactForm::get_taxonomies(
			$contact_form );
		$key_values = array();
		$key_values = self::aggregate_posts(
			$key_values,
			$posts,
			$taxonomies );
		$markerInfos = [];
		foreach ( $key_values as $key => $value ) {
			$markerInfos[] = $value;
		}
		usort( $markerInfos, function( $a, $b ) {
			if ( $a[ 'count' ] < $b[ 'count' ] ) {
                        	return 1;
                    	} else if ( $a[ 'count' ] > $b[ 'count' ] ) {
                        	return -1;
                    	}
                    	return 0;
		} );
		$options = MAPS_FOR_CF7_Options::get_instance();
		$settings = $options->get_option();
		$num_ranks = $settings[ MAPS_FOR_CF7_Options::num_ranks ];
		if ( $num_ranks < 0 ) $num_ranks = 1;
	
		while ( count( $markerInfos ) > $num_ranks ) {
			$a = $markerInfos[ $num_ranks - 1 ];
			$b = $markerInfos[ $num_ranks ];

			if ( $a[ 'count' ] != $b[ 'count' ] ) break;
			$num_ranks += 1;
		}
		$markerInfos = array_slice( $markerInfos, 0, $num_ranks );
		wp_send_json( $markerInfos );
                return;
	}
	public static function get_posts(
		$bounds, $form_id, $form, $bounds_array ) {
		$tax_query = [];
		foreach ( $form as $name_value ) {
		     $tax_query = self::add_taxonomy_term( 
			$tax_query,
			$name_value->name,
			$name_value->value );
		}
		if ( count( $tax_query ) > 1 ) {
			$tax_query[ 'relation' ] = 'AND';
		}
		$meta_query = self::get_meta_query( $form_id, $bounds_array );
		$args = array(
			'tax_query' => $tax_query,
			'meta_query' => $meta_query,
			'orderby' => array(
				MAPS_FOR_CF7_Post::meta_key_place_lat => 'ASC',
				MAPS_FOR_CF7_Post::meta_key_place_lng => 'ASC',
			),
		);
		return MAPS_FOR_CF7_Post::find( $args );
	}
	private static function get_lat_lng( $bounds_array ) {
		$lat = $lng = 0;
		foreach ( $bounds_array as $bounds ) {
			$lat += $bounds->north - $bounds->south;
			$lng += $bounds->east - $bounds->west;
		}
		return array(
			'lat' => self::roundup( $lat / 10, self::min_lat ),
			'lng' => self::roundup( $lng / 10, self::min_lng ) );
	}
	private static function aggregate_posts_by_lat_lng(
		$key_values, $posts, $taxonomies, $lat_lng ) {
		foreach ( $posts as $post ) {
			$lat = get_post_meta(
				$post->ID(),
				MAPS_FOR_CF7_Post::meta_key_place_lat,
				true );
			$lng = get_post_meta(
				$post->ID(),
				MAPS_FOR_CF7_Post::meta_key_place_lng,
				true );
			$bounds = self::get_bounds( $lat, $lng, $lat_lng );
			$key_values = self::insert_post_by_bounds(
				$key_values,
				$post,
				$taxonomies,
				$bounds,
				$lat,
				$lng );
		}
		return $key_values;
	}
	private static function aggregate_posts(
		$key_values, $posts, $taxonomies ) {
		foreach ( $posts as $post ) {
			$lat = get_post_meta(
				$post->ID(),
				MAPS_FOR_CF7_Post::meta_key_place_lat,
				true );
			$lng = get_post_meta(
				$post->ID(),
				MAPS_FOR_CF7_Post::meta_key_place_lng,
				true );
			$key_values = self::insert_post(
				$key_values,
				$post,
				$taxonomies,
				$lat,
				$lng );
		}
		return $key_values;
	}
	private static function get_bounds( $lat, $lng, $lat_lng ) {
		return array( 
		    'south' => self::rounddown(
			 $lat,
			 $lat_lng[ 'lat' ] ),
		    'north' => self::roundup(
			 $lat,
			 $lat_lng[ 'lat' ] ),
		    'west' => self::rounddown(
			 $lng,
			 $lat_lng[ 'lng' ] ),
		    'east' => self::roundup(
			 $lng,
			 $lat_lng[ 'lng' ] ),
		);
	}
	private static function insert_post_by_bounds(
		$key_values, $post, $taxonomies, $bounds, $lat, $lng ) {
		$key = $bounds[ 'south' ] . ',' . $bounds[ 'north' ] . 'x'
			. $bounds[ 'west' ] . ',' . $bounds[ 'east' ];
		if ( array_key_exists( $key, $key_values ) ) {
			$value = $key_values[ $key ];
			$count =  $value[ 'count' ];
			$value[ 'lat' ]
				= ( $value[ 'lat' ] * $count + $lat )
				/ ( $count + 1 );
			$value[ 'lng' ]
				= ( $value[ 'lng' ] * $count + $lng )
				/ ( $count + 1 );
		} else {
			$value = array(
				'lat' => $lat,
				'lng' => $lng,
				'name' => '',
				'count' => 0,
				'taxonomies' => array(),
			);
		}
		$key_values[ $key ] = self::statistics(
			$value,
			$post,
			$taxonomies );
		return $key_values;
	}

	private static function insert_post(
		$key_values, $post, $taxonomies, $lat, $lng ) {
		$place_id = get_post_meta(
			$post->ID(),
			MAPS_FOR_CF7_Post::meta_key_place_id,
			true );
		$name = get_post_meta(
			$post->ID(),
			MAPS_FOR_CF7_Post::meta_key_place_name,
			true );
		if ( array_key_exists( $place_id, $key_values ) ) {
			$value = $key_values[ $place_id ];
		} else {
			$value = array(
				'placeId' => $place_id,
				'lat' => $lat,
				'lng' => $lng,
				'name' => rawurldecode( $name ),
				'count' => 0,
				'taxonomies' => array(),
			);
		}
		$key_values[ $place_id ] = self::statistics(
			$value,
			$post,
			$taxonomies );
		return $key_values;
	}
	private static function statistics( $value, $post, $taxonomies ) {
		$value[ 'count' ] += 1;
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post->ID(), $taxonomy ); 
			$name = MAPS_FOR_CF7_Taxonomy::resolve_name( $taxonomy );
			if ( !empty( $terms ) ) {
				if ( array_key_exists(
					$name,
					$value[ 'taxonomies' ] ) ) {
					$tax = $value[ 'taxonomies' ][ $name ];
				} else {
					$tax = array();
				}
				foreach ( $terms as $term ) {
					if  ( !array_key_exists(
						$term->name, $tax ) ) {
						$tax[ $term->name ] = 0;
					}
					$tax[ $term->name ] += 1;
				}
				$value[ 'taxonomies' ][ $name ] = $tax;
			}
		}
		return $value;
	}
	private static function divide_bounds( $bounds ) {
		if ( $bounds->east < $bounds->west ) {
			return array(
				( object ) array( 
					'south' => $bounds->south,
					'west' => $bounds->east,
					'north' => $bounds->north,
					'east' => 0,
				),
				( object ) array(
					'south' => $bounds->south,
					'west' => $bounds->west,
					'north' => $bounds->north,
					'east' => 180,
				),
			);
	     	}
	     	return array( $bounds );
	}
	private static function add_taxonomy_term( $tax_query, $name, $term ) {
		foreach ( $tax_query as &$query ) {
		    	if ( $query[ 'taxonomy' ] == $name ) {
				$query[ 'terms' ][] = $term;
				return $tax_query;
		    	}
		}
		$tax_query[] = array(
			'taxonomy' => $name,
			'field' => 'name',
			'terms' => array( $term ),
		);
		return $tax_query;
	}
	private static function get_meta_query( $form_id, $bounds_array ) {
		$meta_query = array();
		foreach ( $bounds_array as $bounds ) {
			$meta_query[] = array(
				'relation' => 'AND',
				array(
					'key' => MAPS_FOR_CF7_Post::meta_key_form_id,
                                	'value' => $form_id,
				),
				array(
					'key' => MAPS_FOR_CF7_Post::meta_key_place_lat,
					'value' => array(
						$bounds->south,
						$bounds->north ),
					'compare' => 'BETWEEN',
					'type' => 'DECIMAL(17,14)',
				),
				array(
					'key' => MAPS_FOR_CF7_Post::meta_key_place_lng,
					'value' => array(
						$bounds->west,
						$bounds->east ),
					'compare' => 'BETWEEN',
					'type' => 'DECIMAL(17,14)',
				),
			);
		}
		if ( count( $meta_query ) > 1 ) {
			$meta_query[ 'relation' ] = 'OR';
		}
		return $meta_query;
	}
	private static function rounddown( $v, $d ) {
		$n = floor( $v / $d );

		return $n * $d;
	}
	private static function roundup( $v, $d ) {
		$n = floor( $v / $d );

  		if ( $n * $d == $v ) return $v;
  		return ( $n + 1 ) * $d;
	}
}
