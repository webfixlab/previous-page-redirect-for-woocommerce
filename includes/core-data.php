<?php
/**
 * Plugin data struction
 *
 * @package    WordPress
 * @subpackage Previous page redirect for WooCommerce
 * @since      4.0
 */

global $ppredirect__;

$ppredirect__ = array(
	'name'            => __( 'Previous Page Redirect for WooCommerce', 'previous-page-redirect-for-woocommerce' ),
	'notice'          => array(),
	'notice_interval' => 15, // in days.
);

// admin scopes to allow this plugin.
$ppredirect__['admin_scopes'] = array(
	'toplevel_page_ppredirect-settings',
);

$ppredirect__['urls'] = array(
	'plugin'  => 'https://webfixlab.com/plugins/previous-page-redirect-for-woocommerce/',
	'docs'    => 'https://docs.webfixlab.com/kb/previous-page-redirect-for-woocommerce/',
	'support' => 'https://webfixlab.com/contact/',
	'review'  => 'https://wordpress.org/plugins/previous-page-redirect-for-woocommerce/reviews/?rate=5#new-post',
	'wc'      => 'https://wordpress.org/plugins/woocommerce/',
);

// hook to modify global data variable.
do_action( 'ppredirect_modify_core_data' );
