<?php
/**
 * Plugin Name: Maps for Contact Form 7
 * Plugin URI: https://localohost/
 * Description: add place field on contace-7-form
 * Version: 1.0.2
 * Author: YasuoTanaka
 * Author URI: https://crowdworks.jp/
 * Text Domain: maps-for-contact-form-7
 * Domain Path: /languages/
 */
defined( 'ABSPATH' ) || exit;

define( 'MAPS_FOR_CF7_VERSION', '0.0.1' );

define( 'MAPS_FOR_CF7_TEXT_DOMAIN', 'maps-for-contact-form-7' );

define( 'MAPS_FOR_CF7_PLUGIN', __FILE__ );

define( 'MAPS_FOR_CF7_PLUGIN_BASENAME', plugin_basename( MAPS_FOR_CF7_PLUGIN ) );

define( 'MAPS_FOR_CF7_PLUGIN_NAME', trim( dirname( MAPS_FOR_CF7_PLUGIN_BASENAME ), '/' ) );

define( 'MAPS_FOR_CF7_PLUGIN_DIR', untrailingslashit( dirname( MAPS_FOR_CF7_PLUGIN ) ) );

define( 'MAPS_FOR_CF7_PLUGIN_MODULES_DIR', MAPS_FOR_CF7_PLUGIN_DIR . '/modules' );

if ( ! defined( 'MAPS_FOR_CF7_ADMIN_READ_CAPABILITY' ) ) {
        define( 'MAPS_FOR_CF7_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

if ( ! defined( 'MAPS_FOR_CF7_ADMIN_READ_WRITE_CAPABILITY' ) ) {
        define( 'MAPS_FOR_CF7_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

require_once MAPS_FOR_CF7_PLUGIN_DIR . '/load.php';

