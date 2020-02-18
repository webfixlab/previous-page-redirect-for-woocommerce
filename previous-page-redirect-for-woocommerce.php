<?php
/*
Plugin Name: Previous Page Redirect for WooCommerce
Plugin URI: https://webfixlab.com/woocommerce-previous-page-redirect
Description: After logged in from WooCommerce My Account page, redirect your customers to the previous page they were in.
Author: WebFix Lab
Author URI: https://webfixlab.com/
Version: 2
Requires at least: 4.4
Tested up to: 5.3.2
WC requires at least: 3.0
WC tested up to: 3.9.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wlpr
*/

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'wlpr_plugin_activation_init' );

function wlpr_plugin_activation_init() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'admin_notices', 'wlpr_woo_dependency_error' );
        deactivate_plugins( 'previous-page-redirect-for-woocommerce/previous-page-redirect-for-woocommerce.php' );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function wlpr_woo_dependency_error(){
    ?><div class="error"><p>Please install and activate <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin first.</p></div><?php
}

// // start global session for saving the referer url
// function wlpr_start_session() {
//     if( !is_user_logged_in() ){
//         if( !session_id() ) {
//             session_start();
//         }
//     }
//     // use DB instead of session. This comes in handy for situation like caching problem.
//         // use db entry when necessary,
//         // remove the db entry just before redirection.
// }
// add_action( 'init', 'wlpr_start_session', 1 );

function wlpr_get_phpsessid(){
    $id = '';
    if( isset( $_COOKIE['PHPSESSID'] ) ){
        $id = $_COOKIE['PHPSESSID'];
    }
    if( !isset( $id ) ){
        $ckis = explode( ';', $_SERVER['HTTP_COOKIE'] );
        foreach( $ckis as $ck ){
            if( strpos( $ck, 'PHPSESSID' ) !== false ){
                $id = str_replace( 'phpsesside', '', sanitize_title( $ck ) );
            }
        }
    }
    return $id;
}
// get  referer url and save it 
function wlpr_redirect_url() {
	$woo_acc_url = esc_url_raw( get_permalink( get_option('woocommerce_myaccount_page_id') ) );
    if( isset( $woo_acc_url ) && $woo_acc_url != '' ){
    	$woo_acc_url_slug = sanitize_title( str_replace( esc_url_raw( home_url() ), '', $woo_acc_url ) );
    	if( isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] != '' ){
    		if( strpos( $_SERVER['HTTP_REFERER'], $woo_acc_url_slug ) === false ){
    		    // only if the url does not contain 'my-account'
    		    // $_SESSION['wlpr_referer_url'] = $_SERVER['HTTP_REFERER'];
                $psid = wlpr_get_phpsessid();
                update_option( 'wlpr_referer_url' . $psid, $_SERVER['HTTP_REFERER'] );
    		}
    	}
    }    
}
add_action( 'template_redirect', 'wlpr_redirect_url' );
function wlpr_login_redirect() {
    //get option from db
    //remove it
    $psid = wlpr_get_phpsessid();
    $ref_url = get_option( 'wlpr_referer_url' . $psid, null );
    if ( $ref_url !== null ) {
        delete_option( 'wlpr_referer_url' . $psid );
        return $ref_url;
    }
}
add_filter( 'woocommerce_login_redirect', 'wlpr_login_redirect', 10, 2 );