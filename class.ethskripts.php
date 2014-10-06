<?php
/**
 * The Main Plugin Class
 * • Das ETH Design
 * • Änderungen am Editor (Buttons für Boxen)
 * • Das Google Analytics Tracking von Downloads
 * • Die Umleitung auf die Loginseite bei Privaten Büchern
 * • Macht die Optionen von Diskussionen wieder zugänglich (Pressbooks versteckt die)
 * Created by PhpStorm.
 * User: lukas
 * Date: 23.09.14
 * Time: 13:16
 */

class ETHSkripts {

    /**
     * @var bool if plugin is initiated
     */
    private static $initiated = false;

    /**
     * init hook handler
     */
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
        add_filter( 'option_home' , array( 'ILAnnotations', 'add_https') );
        add_filter( 'option_siteurl' , array( 'ILAnnotations', 'add_https') );
        add_filter( 'wpmu_validate_blog_signup' , array( 'ILAnnotations', 'wpmu_validate_blog_signup') );
    }

    /**
     *  Initializes admin hooks
     */
    public static function admin_init() {
        add_editor_style( ETHSkripts__PLUGIN_URL.'assets/css/editor.css' );
        add_filter( 'mce_external_plugins', array( 'ETHSkripts', 'addTextbookButtons' ) );
        add_filter( 'mce_buttons_3', array( 'ETHSkripts', 'registerTBButtons' ) );
    }

    /**
     * Adds the Discussion menu point back to menu
     */
    public static function admin_menu(){
        add_options_page(__('Discussion'), __('Discussion'), 'manage_options', 'options-discussion.php');
    }

    /**
     * Adds the themes in the theme folder to Wordpress
     */
    public static function add_themes() {
        // Register styles
        register_theme_directory( ETHSkripts__PLUGIN_DIR . 'themes-book' );

    }

    /**
     * Adds the Javascript that tracks the file downloads to the page
     */
    public static function load_resources() {
        wp_register_script( 'ga-filedownload.js', ETHSkripts__PLUGIN_URL.'assets/js/ga-filedownload.js', array('jquery'), ETHSkripts_VERSION );
        wp_enqueue_script( 'ga-filedownload.js' );
    }

    /**
     * Deactivates editor buttons added by pressbook-textbook
     * @param $default
     * @return mixed
     */
    public static function option_pbt_other_settings($default){
        $default['pbt_mce-textbook-buttons_active'] = false;
        return($default);
    }

    /**
     * Add the box buttons script to the mce array
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


    /**
     * Redirect to the Login Page if accessing a private book
     */
    public static function private_redirect(  ) {
        $metadata = pb_get_book_information();
        if (get_option('blog_public') != '1' && !current_user_can('read')){
            $pageURL = 'http';
            if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            $loginurl = get_bloginfo('url').'/wp-login.php?redirect_to='.urlencode($pageURL);
            wp_safe_redirect($loginurl);
        }
    }

    /**
     * Add https to siteurl and home option
     * @param $original
     * @return mixed
     */
    public static function add_https($original){
        echo("GOGOGO: ".$original);
        return preg_replace("/^http:/i", "https:", $original);
    }

    /**
     * Add https to signup domain
     * @param $default
     * @return mixed
     */
    public static function wpmu_validate_blog_signup($default){
        $default["domain"] = preg_replace( "/^http:/i", "https:", $default["domain"] );
        return $default;
    }



}