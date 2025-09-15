<?php
/**
 * Plugin Name:          Previous Page Redirect for WooCommerce
 * Plugin URI:           https://webfixlab.com/woocommerce-previous-page-redirect
 * Description:          Set the redirect for both after login and logout to the previous page, homepage, cart, checkout, shop, or any custom page of your choice.
 * Author:               WebFix Lab
 * Author URI:           https://webfixlab.com/
 * Version:              4.1.1
 * Requires at least:    4.9
 * Tested up to:         6.8.2
 * Requires PHP:         7.0
 * Tags:                 woocommerce, redirect, redirection, previous page, referer page
 * WC requires at least: 3.6
 * WC tested up to:      10.1.2
 * License:              GPL2
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:     woocommerce
 * Text Domain:          previous-page-redirect-for-woocommerce
 * Domain Path:          /languages
 *
 * @package              Previous page redirect for WooCommerce
 */

defined( 'ABSPATH' ) || exit;

define( 'PPREDIRECT', __FILE__ );
define( 'PPREDIRECT_VER', '4.1.1' );
define( 'PPREDIRECT_PATH', plugin_dir_path( PPREDIRECT ) );

require plugin_dir_path( PPREDIRECT ) . 'includes/class/admin/class-ppredirect-loader.php';
