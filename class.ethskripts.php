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
        add_filter( 'pre_update_option_siteurl' , array( 'ETHSkripts', 'add_https'), 10 );
        add_filter( 'pre_update_option_home' , array( 'ETHSkripts', 'add_https'), 10 );
        add_filter( 'shibboleth_user_role' , array( 'ETHSkripts', 'shibboleth_user_role'), 10 );
        //add login css
        add_action( 'login_enqueue_scripts', function(){wp_enqueue_style( 'login-head', ETHSkripts__PLUGIN_URL.'assets/css/style-login.css', false );} );
        //remove PressBooks redirect
        remove_filter( 'login_redirect', '\PressBooks\Redirect\login', 10 );
        add_filter( 'login_redirect', array( 'ETHSkripts', 'login_redirect'), 10, 3 );
    }

    /**
     *  Initializes admin hooks
     */
    public static function admin_init() {
        add_editor_style( ETHSkripts__PLUGIN_URL.'assets/css/editor.css' );
        add_filter( 'mce_external_plugins', array( 'ETHSkripts', 'addTextbookButtons' ) );
        add_filter( 'mce_buttons_3', array( 'ETHSkripts', 'registerTBButtons' ) );
        static::init_shibboleth_setting();
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
     * Change redirect upon login to 403 if no rights
     *
     * @param string $redirect_to
     * @param string $request_redirect_to
     * @param \WP_User $user
     *
     * @return string
     */
    public static function login_redirect( $redirect_to, $request_redirect_to, $user){
        if ( false === is_a( $user, 'WP_User' ) ) {
            // Unknown user, bail with default
            return $redirect_to;
        }

        if ( is_super_admin( $user->ID ) ) {
            // This is an admin, don't mess
            return $redirect_to;
        }

        $blogs = get_blogs_of_user( $user->ID );
        if ( !array_key_exists( get_current_blog_id(), $blogs ) ) {
            return get_bloginfo('url').'/wp-login.php?redirect_to='.urlencode($redirect_to).'&reauth=1';
        }
        return $redirect_to;
    }

    /**
     * Add https to signup domain
     * @param $default
     * @return mixed
     */
    public static function add_https($default){
        return preg_replace( "/^http:/i", "https:", $default );
    }

    /**
     * Add Shibboleth setting to the privacy menu
     */
    public static function init_shibboleth_setting(){
        register_setting(
            'privacy_settings',
            'shibboleth_subscriber',
            'ETHSkripts::shibboleth_setting_sanitize'
        );

        add_settings_field(
            'shibboleth_subscriber',
            __( 'Who can sign up as a subscriber?', 'ethskript' ),
            'ETHSkripts::shibboleth_setting_callback',
            'privacy_settings',
            'privacy_settings_section'
        );
    }

    /**
     * Output of the option
     * @param $args Arguments
     */
    public static function shibboleth_setting_callback( $args ) {

        $selected = get_option( 'shibboleth_subscriber' );

        $html = '<lable>'.$args[0].'</lable>';
        $html .= '<select name="shibboleth_subscriber" class="shibboleth_subscriber">';
        $html .= '<option value="0"'.($selected == 0 ? ' selected = "selected"' : '').'>'.__( 'Nobody', 'pressbooks' ).'</option>';
        $html .= '<option value="1"'.($selected == 1 ? ' selected = "selected"' : '').'>'.__( 'ETH Users', 'pressbooks' ).'</option>';
        $html .= '<option value="2"'.($selected == 2 ? ' selected = "selected"' : '').'>'.__( 'ETH and UZH Users', 'pressbooks' ).'</option>';
        $html .= '<option value="3"'.($selected == 3 ? ' selected = "selected"' : '').'>'.__( 'SWITCHaai Users', 'pressbooks' ).'</option>';
        $html .= '</select>';
        echo $html;
    }

    /**
     * Callback if the option gets changed
     * @param $input
     * @return mixed
     */
    public static function shibboleth_setting_sanitize($input){
        return absint($input);
    }

    /**
     * Set user role by own criteria
     * @param $default
     */
    public static function shibboleth_user_role($default){
        $values = explode(';', $_SERVER["homeOrganization"]);
        $option = get_option( 'shibboleth_subscriber' );

        if($option == 1){
            if ( in_array("ethz.ch", $values) ) {
                return "subscriber";
            }
        }else if($option == 2){
            if ( in_array("ethz.ch", $values) ) {
                return "subscriber";
            }
            if ( in_array("uzh.ch", $values) ) {
                return "subscriber";
            }
        }else if($option == 3){
            return "subscriber";
        }
        return false;
    }



}