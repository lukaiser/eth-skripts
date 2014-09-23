<?php
/**
 * @package ETHSkripts
 */
/*
Plugin Name: ETHSkripts
Plugin URI: http://ethz.ch
Description: Blabla
Version: 0.0.1
Author: Lukas Kaiser
Author URI: http://emperor.ch
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'ETHSkripts_VERSION', '0.0.1' );
define( 'ETHSkripts__MINIMUM_WP_VERSION', '3.0' );
define( 'ETHSkripts__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ETHSkripts__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( PBPrivate__PLUGIN_DIR . 'class.ethskripts.php' );

add_action( 'init', array( 'ETHSkripts', 'init' ) );