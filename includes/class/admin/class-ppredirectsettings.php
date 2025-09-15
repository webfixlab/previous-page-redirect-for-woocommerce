<?php
/**
 * Admin Settings Class
 *
 * @package    WordPress
 * @subpackage Previous page redirect for WooCommerce
 * @since      4.0
 */

if ( ! class_exists( 'PPRedirectSettings' ) ) {

	/**
	 * Swatch admin settings functionlity class
	 */
	class PPRedirectSettings {



		/**
		 * Settings data
		 *
		 * @var array
		 */
		private $data;

		/**
		 * Initialize class and get saved settings data
		 */
		public function __construct() {
			$this->data = get_option( 'ppredirect_settings' );
		}

		/**
		 * Initialize hook of settings class
		 */
		public function init() {
			add_action( 'admin_head', array( $this, 'save_settings' ) );
		}



		/**
		 * Save admin settings
		 */
		public function save_settings() {
			if ( ! isset( $_POST['ppredirect_settings_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ppredirect_settings_nonce'] ) ), 'ppredirect' ) ) {
				return;
			}

			// previous page redirect admin settings data.
			$data = array();

			if ( isset( $_POST['login_redirect'] ) ) {
				$data['login_redirect'] = sanitize_key( wp_unslash( $_POST['login_redirect'] ) );
			}
			if ( isset( $_POST['logout_redirect'] ) ) {
				$data['logout_redirect'] = sanitize_key( wp_unslash( $_POST['logout_redirect'] ) );
			}

			if ( isset( $_POST['custom_login_redirect'] ) ) {
				$data['custom_login_redirect'] = sanitize_url( wp_unslash( $_POST['custom_login_redirect'] ) );
			}
			if ( isset( $_POST['custom_logout_redirect'] ) ) {
				$data['custom_logout_redirect'] = sanitize_url( wp_unslash( $_POST['custom_logout_redirect'] ) );
			}

			update_option( 'ppredirect_settings', $data );
		}



		/**
		 * Display settings page
		 */
		public function settings_page() {
			?>
			<div class="ppredirect-wrap">
				<?php $this->settings_header(); ?>
				<div class="ppredirect-content-wrap">
					<div class="ppredirect-main">
						<form action="" method="POST">
							<?php $this->settings_content(); ?>
						</form>
					</div>
					<div class="ppredirect-side">
						<?php include PPREDIRECT_PATH . 'templates/admin/sidebar.php'; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Settings page header
		 */
		public function settings_header() {
			global $ppredirect__;

			?>
			<div class="ppredirect-heading">
				<?php $this->get_title(); ?>
				<div class="heading-desc">
					<p>
						<a href="<?php echo esc_url( $ppredirect__['urls']['docs'] ); ?>" target="_blank"><?php echo esc_html__( 'Documents', 'previous-page-redirect-for-woocommerce' ); ?></a> | <a href="<?php echo esc_url( $ppredirect__['urls']['support'] ); ?>" target="_blank"><?php echo esc_html__( 'Support', 'previous-page-redirect-for-woocommerce' ); ?></a>
					</p>
				</div>
			</div>
			<div class="ppredirect-notice">
				<?php $this->display_notice(); ?>
			</div>
			<?php
		}

		/**
		 * Settings page content
		 */
		public function settings_content() {
			$login_redirect  = isset( $this->data['login_redirect'] ) ? $this->data['login_redirect'] : '';
			$logout_redirect = isset( $this->data['logout_redirect'] ) ? $this->data['logout_redirect'] : '';

			?>
			<div class="ppredirect-sections">
				<div class="section ppredirect-general">
					<h1><?php echo esc_html__( 'Settings', 'previous-page-redirect-for-woocommerce' ); ?></h1>
					<table class="form-table">
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Login redirect', 'previous-page-redirect-for-woocommerce' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->display_pages_dropdown( 'login_redirect', $login_redirect ); ?>
							</td>
						</tr>
						<tr valign="top" class="custom_login_redirect" style="display:none;">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( '', 'previous-page-redirect-for-woocommerce' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input name="custom_login_redirect" type="text" value="<?php echo isset( $this->data['custom_login_redirect'] ) ? esc_url( $this->data['custom_login_redirect'] ) : ''; ?>">
								<p><?php echo esc_html__( 'Note: External links are not supported', 'previous-page-redirect-for-woocommerce' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Logout redirect', 'previous-page-redirect-for-woocommerce' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->display_pages_dropdown( 'logout_redirect', $logout_redirect ); ?>
							</td>
						</tr>
						<tr valign="top" class="custom_logout_redirect"	style="display:none;">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( '', 'previous-page-redirect-for-woocommerce' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input name="custom_logout_redirect" type="text" value="<?php echo isset( $this->data['custom_logout_redirect'] ) ? esc_url( $this->data['custom_logout_redirect'] ) : ''; ?>">
								<p><?php echo esc_html__( 'Note: External links are not supported', 'previous-page-redirect-for-woocommerce' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="">
				<?php wp_nonce_field( 'ppredirect', 'ppredirect_settings_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html__( 'Save changes', 'previous-page-redirect-for-woocommerce' ); ?>" class="button-primary woocommerce-save-button ppredirect-save">
			</div>
			<?php
		}



		/**
		 * Display settings page title
		 */
		public function get_title() {
			global $ppredirect__;

			$title = sprintf(
				// translators: Placeholder %1$s is plugin name.
				__( '%1$s - Settings', 'previous-page-redirect-for-woocommerce' ),
				esc_html( $ppredirect__['name'] )
			);

			printf( '<h1 class="">%s</h1>', esc_html( $title ) );
		}

		/**
		 * Display navigation tabs
		 */
		public function get_menu() {
			$menu = array(
				'general' => array(
					'label' => __( 'General', 'previous-page-redirect-for-woocommerce' ),
					'icon'  => 'admin-settings',
				),
			);

			foreach ( $menu as $slug => $item ) {
				printf(
					'<a class="nav-tab nav-tab-active" data-target="%s"><span class="dashicons dashicons-%s"></span> %s</a>',
					esc_attr( $slug ),
					esc_attr( $item['icon'] ),
					esc_html( $item['label'] )
				);
			}
		}

		/**
		 * Display settings page options
		 *
		 * @param string $name dropdown field name.
		 * @param string $url  saved redirect url.
		 */
		public function display_pages_dropdown( $name, $url ) {
			$dropdowns = array();
			
			if( 'login_redirect' === $name ){
				$dropdowns['default'] = __( 'Previous page', 'previous-page-redirect-for-woocommerce' );
			}
			$dropdowns['none'] = __( 'Disable', 'previous-page-redirect-for-woocommerce' );
			$dropdowns['home'] = __( 'Home', 'previous-page-redirect-for-woocommerce' );

			// check if the pages exists or set from woocommerce sttings.
			$cart_url = wc_get_cart_url();
			if ( ! empty( $cart_url ) ) {
				$dropdowns['cart'] = __( 'Cart', 'previous-page-redirect-for-woocommerce' );
			}

			$checkout_url = wc_get_checkout_url();
			if ( ! empty( $checkout_url ) ) {
				$dropdowns['checkout'] = __( 'Checkout', 'previous-page-redirect-for-woocommerce' );
			}

			$shop_page = wc_get_page_id( 'shop' );
			if ( ! empty( $shop_page ) ) {
				$dropdowns['shop'] = __( 'Shop', 'previous-page-redirect-for-woocommerce' );
			}

			$tns = wc_terms_and_conditions_page_id();
			if ( ! empty( $tns ) ) {
				$dropdowns['tns'] = __( 'Terms and Conditions', 'previous-page-redirect-for-woocommerce' );
			}

			$dropdowns['custom'] = __( 'Other', 'previous-page-redirect-for-woocommerce' );

			echo sprintf( '<select name="%s" class="redirect-type">', esc_attr( $name ) );

			foreach ( $dropdowns as $value => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $value ),
					$value === $url ? 'selected' : '',
					esc_html( $label )
				);
			}

			echo '</select>';
		}



		/**
		 * Display admin notices and settings form submission notice
		 */
		public function display_notice() {
			global $ppredirect__;

			$allowed_html = wp_kses_allowed_html( 'post' );
			
			if( empty( $allowed_html ) ){
				$allowed_html = array(
					'div'    => array( 'id'    => array(), 'class' => array() ),
					'h3'     => array( 'class' => array() ),
					'p'      => array( 'class' => array() ),
					'a'      => array( 'href'  => array(), 'class' => array() ),
					'strong' => array( 'class' => array() ),
					'button' => array( 'type'  => array(), 'class' => array() ),
					'span'   => array( 'class' => array() )
				);
			}

			$allowed_html[ 'style' ]  = array();
			$allowed_html[ 'script' ] = array();

			// display admin notices.
			if ( isset( $ppredirect__['notice'] ) ) {
				foreach ( $ppredirect__['notice'] as $notice ) {
					echo wp_kses( $notice, $allowed_html );
				}
			}

			// display save settings notice.
			if ( ! isset( $_POST['ppredirect_settings_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ppredirect_settings_nonce'] ) ), 'ppredirect' ) ) {
				return;
			}

			?>
			<div id="message" class="notice notice-success is-dismissible updated">
				<p>
					<?php echo esc_html__( 'Settings saved successfully.', 'previous-page-redirect-for-woocommerce' ); ?>
				</p>
				<button type="button" class="notice-dismiss"></button>
			</div>
			<?php
		}
	}
}

$ppredirect_settings = new PPRedirectSettings();
$ppredirect_settings->init();
