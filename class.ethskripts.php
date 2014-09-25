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
        add_action( 'wp_loaded', array( 'ETHSkripts', 'add_themes' ) );
        add_action( 'wp_enqueue_scripts', array( 'ETHSkripts', 'load_resources' ) );
        add_filter( 'allowed_themes', array( 'ETHSkripts', 'filterChildThemes' ), 12 );
        add_action( 'wp', array( 'ETHSkripts', 'private_redirect' ) );
    }

    public static function admin_init() {
        add_editor_style( ETHSkripts__PLUGIN_URL.'assets/css/editor.css' );
        add_filter( 'mce_external_plugins', array( 'ETHSkripts', 'addTextbookButtons' ) );
        add_filter( 'mce_buttons_3', array( 'ETHSkripts', 'registerTBButtons' ) );
    }
    public static function admin_menu(){
        add_options_page(__('Discussion'), __('Discussion'), 'manage_options', 'options-discussion.php');
    }

    public static function add_themes() {
        // Register styles
        register_theme_directory( ETHSkripts__PLUGIN_DIR . 'themes-book' );

    }
    public static function load_resources() {
        wp_register_script( 'ga-filedownload.js', ETHSkripts__PLUGIN_URL.'assets/js/ga-filedownload.js', array('jquery'), ETHSkripts_VERSION );
        wp_enqueue_script( 'ga-filedownload.js' );
    }

    public static function option_pbt_other_settings($default){
        $default['pbt_mce-textbook-buttons_active'] = false;
        return($default);
    }

    /**
     * Add the script to the mce array
     *
     * @param array $plugin_array
     * @return array
     */
    public static function addTextbookButtons( $plugin_array ) {

        $plugin_array['textboxbuttons'] = ETHSkripts__PLUGIN_URL.'assets/js/textbox-buttons.js';
        return $plugin_array;
    }

    /**
     * Push our buttons onto the buttons stack in the 3rd mce row
     *
     * @param type $buttons
     */
    public static function registerTBButtons( $buttons ) {

        array_push( $buttons, 'tbformel', 'tbhowto', 'tbdefinition', 'tbbeispiel', 'tbfragen', 'tbverweis', 'tbexkurs' );
        return $buttons;
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


    public static function private_redirect(  ) {
        $metadata = pb_get_book_information();
        if (get_option('blog_public') != '1' && !current_user_can('read')){
            $loginurl = get_bloginfo('url').'/wp-login.php?redirect_to='.get_permalink();
            wp_safe_redirect($loginurl);
        }
    }



}