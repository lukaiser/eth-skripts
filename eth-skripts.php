<?php
/**
 * @package ETHSkripts
 */
/*
Plugin Name: ETHSkripts
Plugin URI: https://github.com/lukaiser/eth-skripts
Description: Modifizierungen von Wordpress für diesen spezifischen Server
    • Das ETH Design
    • Änderungen am Editor (Buttons für Boxen)
    • Google Analytics Tracking von Downloads
    • Die Umleitung auf die Loginseite bei Privaten Büchern
    • Macht die Optionen von Diskussionen wieder zugänglich (Pressbooks versteckt die)
Version: 0.9.0
Author: Lukas Kaiser
Author URI: http://emperor.ch
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'ETHSkripts_VERSION', '0.9.0' );
define( 'ETHSkripts__MINIMUM_WP_VERSION', '3.0' );
define( 'ETHSkripts__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ETHSkripts__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ETHSkripts__PLUGIN_DIR . 'class.ethskripts.php' );
//Test if Pressbooks and Pressbooks-textbook is installed
if ( get_site_option( 'pressbooks-activated' ) && get_site_option( 'pressbooks-textbook-activated' ) ) {
    //init hocks
    add_action( 'init', array( 'ETHSkripts', 'init' ) );
    //option_pbt_other_settings hook (needs to be registered before the init hook)
    add_filter('option_pbt_other_settings', array( 'ETHSkripts', 'option_pbt_other_settings' ) );
}