<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 23.09.14
 * Time: 13:16
 */

class ETHSkripts {

    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
            self::$initiated = true;
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks() {
        add_action('admin_init', array('ETHSkripts', 'admin_init'));
        add_action('admin_menu', array('ETHSkripts', 'admin_menu'), 2);
        add_action( 'wp_loaded', array( 'ETHSkripts', 'registerScriptsAndStyles' ) );
        add_filter( 'allowed_themes', array( 'ETHSkripts', 'filterChildThemes' ), 12 );
    }

    public static function admin_init() {

    }
    public static function admin_menu(){
        add_options_page(__('Discussion'), __('Discussion'), 'manage_options', 'options-discussion.php');
    }

    public static function registerScriptsAndStyles() {
        // Register styles
        register_theme_directory( ETHSkripts__PLUGIN_DIR . 'themes-book' );
    }


    /**
     * Pressbooks filters allowed themes, this adds our themes to the list
     *
     * @since 1.0.7
     * @param array $themes
     * @return array
     */
    public static function filterChildThemes( $themes ) {
        $pbt_themes = array();

        if ( \Pressbooks\Book::isBook() ) {
            $registered_themes = search_theme_directories();

            foreach ( $registered_themes as $key => $val ) {
                if ( $val['theme_root'] == ETHSkripts__PLUGIN_DIR . 'themes-book' ) {
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