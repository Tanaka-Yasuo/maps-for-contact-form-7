<?php

function maps_for_cf7_plugin_url( $path = '' ) {
        $url = plugins_url( $path, MAPS_FOR_CF7_PLUGIN );

        if ( is_ssl() and 'http:' == substr( $url, 0, 5 ) ) {
                $url = 'https:' . substr( $url, 5 );
        }

        return $url;
}
