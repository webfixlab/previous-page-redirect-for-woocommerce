<?php
/**
 * Frontend previous page redirect class
 *
 * @package    WordPress
 * @subpackage Previous page redirect for WooCommerce
 * @since      4.0
 */

if (!class_exists('PPRedirect')) {

	/**
	 * Activate plugin and add redirect functionlity
	 */
	class PPRedirect
	{



		/**
		 * Plugin initialization
		 */
		public function init(){
			add_action( 'woocommerce_login_form_end', array( $this, 'add_ref' ) );
			add_action( 'woocommerce_register_form_end', array( $this, 'add_ref' ) );

			add_filter( 'woocommerce_login_redirect', array( $this, 'login_redirect' ), 10, 2 );
			add_filter( 'woocommerce_logout_default_redirect_url', array( $this, 'logout_redirect' ), 10, 1 );
		}



		/**
		 * Set redirect url after woocommerce successful login
		 *
		 * @param string $default default redirect url.
		 * @param object $user    current user object.
		 *
		 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
		 */
		public function login_redirect( $default, $user ){
			return $this->prepare_redirect( $default, 'login_redirect' );
		}

		/**
		 * Set redirect url after woocommerce successful logout
		 *
		 * @param string $default default redirect url.
		 */
		public function logout_redirect( $default ){
			global $wp_query, $wp;

			// get referer page url.
			$referer_url = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$redirect = $this->update_url( 'logout_redirect', $referer_url );

			if ( isset( $wp->query_vars['customer-logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'customer-logout' ) ) {
				return ! empty( $redirect ) ? $redirect : $default;
			}

			return $default;
		}

		/**
		 * Prepare redirect url
		 *
		 * @param string $default default page url.
		 * @param string $name    field name.
		 */
		public function prepare_redirect($default, $name)
		{
			if (!isset($_POST) || !isset($_POST['pre_page_redirect'])) {
				return $default;
			}

			if (!isset($_POST['ppr_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['ppr_nonce'])), 'ppr_nonce_ref')) {
				return $default;
			}

			$previous_page = isset($_POST['pre_page_redirect']) ? sanitize_url(wp_unslash($_POST['pre_page_redirect'])) : '';

			// update url based on settings.
			$url = $this->update_url($name, $previous_page);
			if (empty($url)) {
				return $default;
			}

			return $url;
		}

		/**
		 * Update redirect url based on admin settings
		 *
		 * @param string $name          field name.
		 * @param string $previous_page previous page url.
		 */
		public function update_url( $name, $previous_page ){
			// get admin settings data.
			$data = get_option( 'ppredirect_settings' );

			// get redirect type.
			$val = isset( $data[ $name ] ) && ! empty( $data[ $name ] ) ? $data[ $name ] : 'default';

			if( 'default' === $val && 'logout_redirect' === $name ){
				$val = 'none';
			}

			if( 'none' === $val ) {
				return '';
			}

			// new url based on settings.
			$url = '';

			if ('cart' === $val) {
				$url = wc_get_cart_url();
			} elseif ('checkout' === $val) {
				$url = wc_get_checkout_url();
			} elseif ('shop' === $val) {
				$url = get_permalink( wc_get_page_id( 'shop' ) );
			} elseif ('home' === $val) {
				$url = home_url();
			} elseif ('tns' === $val) {
				$url = get_permalink( wc_terms_and_conditions_page_id() );
			} elseif ('custom' === $val) {
				$url = isset( $data[ 'custom_' . $name ] ) ? esc_url( $data[ 'custom_' . $name ] ) : '';
			}

			if( empty( $url ) ){
				return $previous_page;
			}

			return $url;
		}
		


		/**
		 * Add referer url in an input field to process after woocommerce account login
		 */
		public function add_ref(){
			global $post;

			// if no referer found, skip.
			if( !isset( $_SERVER['HTTP_REFERER'] ) ) {
				return;
			}

			// get referer page url.
			$referer_url = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

			if( empty( $referer_url ) ) {
				return;
			}

			echo wp_kses(
				sprintf( '<input type="hidden" name="pre_page_redirect" value="%s">', esc_url( $referer_url ) ),
				array(
					'input' => array(
						'type'  => array(),
						'name'  => array(),
						'value' => array()
					)
				)
			);

			wp_nonce_field( 'ppr_nonce_ref', 'ppr_nonce' );
		}
	}
}

$prepage_redirect = new PPRedirect();
$prepage_redirect->init();
