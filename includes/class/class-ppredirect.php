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
	class PPRedirect{

		/**
		 * Plugin initialization
		 */
		public static function init(){
			add_action( 'woocommerce_login_form_end', array( __CLASS__, 'add_page_refererer' ) );
			add_action( 'woocommerce_register_form_end', array( __CLASS__, 'add_page_refererer' ) );
			
			add_filter( 'woocommerce_login_redirect', array( __CLASS__, 'login_redirect' ), 10, 2 );
			add_filter( 'logout_redirect', array( __CLASS__, 'logout_redirect' ), 10, 3 );
		}

		/**
		 * Set redirect url after woocommerce successful login
		 *
		 * @param string $default default redirect url.
		 * @param object $user    current user object.
		 *
		 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
		 */
		public static function login_redirect( $default, $user ){
			$saved_url = self::get_settings( 'login_redirect' );

			// check if page referer exists.
			if ( isset($_POST['ppr_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ppr_nonce'] ) ), 'ppr_nonce_ref' ) ) {
				$saved_url = isset( $_POST['pre_page_redirect'] ) ? sanitize_url( wp_unslash( $_POST['pre_page_redirect'] ) ) : '';
			}
			return empty( $saved_url ) ? $default : $saved_url;
		}

		/**
		 * Handle Logout Redirect
		 *
		 * @param string $redirect_to           Redirect destination.
		 * @param string $requested_redirect_to Logout url requested.
		 * @param object $user                  User object.
		 * @return string
		 *
		 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
		 */
		public static function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
			$saved_url = self::get_settings( 'logout_redirect' );
			return empty( $saved_url ) ? $redirect_to : $saved_url;
		}

		/**
		 * Get settings page url
		 *
		 * @param string $action_type Which action is it, either login or logout
		 * @return string
		 */
		private static function get_settings( $action_type ){
			// get admin settings data.
			$data = get_option( 'ppredirect_settings' );
			if( empty ( $data ) || ! isset( $data[ $action_type ] ) || empty( $data[ $action_type ] ) ){
				return '';
			}

			$saved_page = $data[ $action_type ];

			if( 'none' === $saved_page || 'default' === $saved_page && 'logout_redirect' === $action_type ){
				return '';
			}

			if ( 'cart' === $saved_page ) {
				return wc_get_cart_url();
			} elseif ( 'checkout' === $saved_page ) {
				return wc_get_checkout_url();
			} elseif ( 'shop' === $saved_page ) {
				return get_permalink( wc_get_page_id( 'shop' ) );
			} elseif ( 'home' === $saved_page ) {
				return home_url();
			} elseif ( 'tns' === $saved_page ) {
				return get_permalink( wc_terms_and_conditions_page_id() );
			} elseif ( 'custom' === $saved_page ) {
				return isset( $data[ 'custom_' . $action_type ] ) ? esc_url( $data[ 'custom_' . $action_type ] ) : '';
			}
			return '';
		}

		/**
		 * Add referer url in an input field to process after woocommerce account login
		 */
		public static function add_page_refererer(){
			// if no referer found, skip.
			if( !isset( $_SERVER['HTTP_REFERER'] ) ) {
				return;
			}
			?>
			<input
				type="hidden"
				name="pre_page_redirect"
				value="<?php echo esc_url( sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ); ?>">
			<?php
			wp_nonce_field( 'ppr_nonce_ref', 'ppr_nonce' );
		}
	}
}

PPRedirect::init();
