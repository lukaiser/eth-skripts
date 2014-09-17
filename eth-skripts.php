<?php

/**
 * ETH Skripts
 *
 * @package   ETH_Skripts
 * @author    Lukas Kaiser
 *
 * @wordpress-plugin
 * Plugin Name:       ETH Skripts
 * Description:       A plugin that adds the ETH Skripts style
 * Version:           1.0
 * Author:            Lukas Kaiser
 * Author URI:        http://emperor.ch
 * Text Domain:       eth-skripts
 * Domain Path:       /languages
 */

namespace ETHS;

// If file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die();
}

class Skripts {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     * @var string
     */
    const VERSION = '1.0';

    /**
     * Unique identifier for plugin.
     *
     * @since 1.0.0
     * @var string
     */
    protected $plugin_slug = 'eth-skripts';

    /**
     * Instance of this class.
     *
     * @since 1.0.0
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since 1.0.1
     */
    private function __construct() {
        // Define plugin constants
        if ( ! defined( 'ETHS_PLUGIN_DIR' ) )
            define( 'ETHS_PLUGIN_DIR', __DIR__ . '/' );

        if ( ! defined( 'ETHS_PLUGIN_URL' ) )
            define( 'ETHS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

        // Hook in our pieces
        add_action( 'init', array( &$this, 'registerScriptsAndStyles' ) );
        add_action( 'wp_enqueue_style', array( &$this, 'enqueueChildThemes' ) );
        add_filter( 'allowed_themes', array( &$this, 'filterChildThemes' ), 11 );
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register all scripts and styles
     *
     * @since 1.0.1
     */
    function registerScriptsAndStyles() {
        // Register styles
        register_theme_directory( ETHS_PLUGIN_DIR . 'themes-book' );
        wp_register_style( 'eths-eth-skripts', ETHS_PLUGIN_URL . 'themes-book/ethskripts/style.css', array( 'pressbooks' ), self::VERSION, 'screen' );
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     * @return    Plugin slug variable.
     */
    function getPluginSlug() {
        return $this->plugin_slug;
    }

    /**
     * Queue child theme
     *
     * @since 1.0.0
     */
    function enqueueChildThemes() {
        wp_enqueue_style( 'eths-eth-skripts' );
    }

    /**
     * Pressbooks filters allowed themes, this adds our themes to the list
     *
     * @since 1.0.7
     * @param array $themes
     * @return array
     */
    function filterChildThemes( $themes ) {
        $pbt_themes = array();

        if ( \Pressbooks\Book::isBook() ) {
            $registered_themes = search_theme_directories();

            foreach ( $registered_themes as $key => $val ) {
                if ( $val['theme_root'] == ETHS_PLUGIN_DIR . 'themes-book' ) {
                    $pbt_themes[$key] = 1;
                }
            }
            // add our theme
            $themes = array_merge( $themes, $pbt_themes );

            return $themes;
        } else {
            return $themes;
        }
    }

}

// Prohibit installation if PB is not installed
if ( get_site_option( 'pressbooks-activated' ) ) {
    if ( is_admin() ) {

    } else {
        $pbt = \ETHS\Skripts::get_instance();
    }
}