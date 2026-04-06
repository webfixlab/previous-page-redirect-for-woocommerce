<?php
/**
 * Plugin Name:          Previous Page Redirect for WooCommerce
 * Plugin URI:           https://webfixlab.com/woocommerce-previous-page-redirect
 * Description:          Redirect users to previous page and more after WooCommerce login and logout.
 * Author:               WebFix Lab
 * Author URI:           https://webfixlab.com/
 * Version:              5.0.0
 * Requires at least:    4.9
 * Tested up to:         6.9.4
 * Requires PHP:         7.0
 * Tags:                 woocommerce, redirect, redirection, previous page, referer page
 * WC requires at least: 3.6
 * WC tested up to:      10.6.2
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
define( 'PPREDIRECT_VER', '5.0.0' );
define( 'PPREDIRECT_PATH', plugin_dir_path( PPREDIRECT ) );

require plugin_dir_path( PPREDIRECT ) . 'includes/class/admin/class-ppredirect-loader.php';
