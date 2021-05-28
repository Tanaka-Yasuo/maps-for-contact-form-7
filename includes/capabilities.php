<?php

add_filter(
	'map_meta_cap',
	function( $caps, $cap, $user_id, $args ) {
		$meta_caps = array(
			'maps_for_cf7_read' => MAPS_FOR_CF7_ADMIN_READ_CAPABILITY,
			'maps_for_cf7_delete' => MAPS_FOR_CF7_ADMIN_READ_WRITE_CAPABILITY,
		);
		
		$caps = array_diff( $caps, array_keys( $meta_caps ) );
		
		if ( isset( $meta_caps[$cap] ) ) {
			$caps[] = $meta_caps[$cap];
		}
		
		return $caps;
	}, 10, 4 );

