<?php
/**
 * Previous page redirect plugin loader
 *
 * @package    WordPress
 * @subpackage Previous page redirect for WooCommerce
 * @since      4.0
 */

if ( ! class_exists( 'PPRedirectLoader' ) ) {

	/**
	 * Plugin loader class
	 */
	class PPRedirectLoader {



		/**
		 * Plugin initialization
		 */
		public function init() {
			add_action( 'init', array( $this, 'do_activate' ) );
			register_activation_hook( PPREDIRECT, array( $this, 'activate' ) );
			register_deactivation_hook( PPREDIRECT, array( $this, 'deactivate' ) );
			add_action( 'activated_plugin', array( $this, 'after_activation' ), 10, 1 );

			add_action( 'before_woocommerce_init', array( $this, 'wc_init' ) );
		}




		/**
		 * Activate plugin functionality
		 */
		public function activate() {
			$this->do_activate();
			flush_rewrite_rules();
		}

		/**
		 * Deactivate plugin functionlity
		 */
		public function deactivate() {
			flush_rewrite_rules();
		}

		/**
		 * Plugin activation process
		 */
		public function do_activate() {

			// check prerequisits.
			if ( ! $this->should_activate() ) {
				return;
			}
			
			load_plugin_textdomain( 'previous-page-redirect-for-woocommerce', false, plugin_basename( dirname( PPREDIRECT ) ) . '/languages' );
			include PPREDIRECT_PATH . 'includes/core-data.php';

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( PPREDIRECT ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'desc_meta' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			include PPREDIRECT_PATH . 'includes/class/admin/class-ppredirectsettings.php';
			include PPREDIRECT_PATH . 'includes/class/class-ppredirect.php';

			$this->ask_feedback();
		}

		/**
		 * If we should activate the plugin
		 */
		public function should_activate() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin           = 'previous-page-redirect-for-woocommerce/previous-page-redirect-for-woocommerce.php';
			$is_base_active   = is_plugin_active( 'woocommerce/woocommerce.php' );
			$is_plugin_active = is_plugin_active( $plugin );

			// if base plugin is active but woocommer is not, skip.
			if ( ! $is_base_active && $is_plugin_active ) {
				deactivate_plugins( $plugin );
				add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );

				return false;
			}

			return true;
		}

		/**
		 * Redirect to plugin settings page after plugin activation
		 *
		 * @param string $plugin plugin name.
		 */
		public function after_activation( $plugin ) {
			if ( plugin_basename( PPREDIRECT ) !== $plugin ) {
				return $plugin;
			}

			wp_safe_redirect( esc_url( admin_url( 'admin.php?page=ppredirect-settings' ) ) );
			die();
		}

		/**
		 * WooCommerce High-Performance Order Storage (HPOS) compatibility enable.
		 */
		public function wc_init() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PPREDIRECT, true );
			}
		}



		/**
		 * Add admin scripts and styles
		 */
		public function admin_scripts() {
			global $ppredirect__;

			if ( ! $this->is_in_scope() ) {
				return;
			}

			// enqueue style.
			wp_register_style( 'ppredirect_admin_style', plugin_dir_url( PPREDIRECT ) . 'assets/admin/admin.css', array(), PPREDIRECT_VER );
			wp_enqueue_style( 'ppredirect_admin_style' );

			wp_enqueue_script( 'ppredirect_admin_script', plugin_dir_url( PPREDIRECT ) . 'assets/admin/admin.js', array( 'jquery' ), PPREDIRECT_VER, true );

			$var = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ajax-nonce' ),
			);

			// apply hook for editing localized variables in admin script.
			$var = apply_filters( 'ppredirect_update_admin_local_val', $var );

			wp_localize_script( 'ppredirect_admin_script', 'ppredirect_admin_data', $var );
		}

		/**
		 * Admin head functionlity : move notices and add menu css
		 */
		public function admin_head() {
			$this->move_admin_notice();
			$this->menu_icon_css();
		}

		/**
		 * Add admin bar menu of the plugin
		 */
		public function admin_menu() {

			// Main menu.
			add_menu_page(
				esc_html__( 'Woo Redirect', 'previous-page-redirect-for-woocommerce' ),
				esc_html__( 'Woo Redirect', 'previous-page-redirect-for-woocommerce' ),
				'manage_options',
				'ppredirect-settings',
				array( $this, 'settings_page' ),
				plugin_dir_url( PPREDIRECT ) . 'assets/images/plugin-icon.svg',
				57
			);

			// settings submenu - settings.
			add_submenu_page(
				'ppredirect-settings',
				esc_html__( 'Woo Redirect', 'previous-page-redirect-for-woocommerce' ),
				'Settings',
				'manage_options',
				'ppredirect-settings'
			);
		}

		/**
		 * Render plugin settings page
		 */
		public function settings_page() {

			// check user capabilities.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// show error/update messages.
			settings_errors( 'wporg_messages' );

			// Display admin html content.
			$settings_class = new PPRedirectSettings();
			$settings_class->settings_page();
		}



		/**
		 * Move admin notices and remove all for displaying them later in the intended position
		 */
		public function move_admin_notice() {
			global $ppredirect__;

			// check scope, without it return.
			if ( ! $this->is_in_scope() ) {
				return;
			}

			// Buffer only the notices.
			ob_start();
			do_action( 'admin_notices' );
			$content = ob_get_contents();
			ob_get_clean();

			// Keep the notices in global $ppredirect__.
			array_push( $ppredirect__['notice'], $content );

			// Remove all admin notices as we don't need to display in it's place.
			remove_all_actions( 'admin_notices' );
		}

		/**
		 * Add admin bar menu css style
		 */
		public function menu_icon_css() {
			?>
			<style>
				#toplevel_page_ppredirect-settings img {
					width: 18px;
					opacity: 1 !important;
				}
				.notice h3{
					margin-top: .5em;
					margin-bottom: 0;
				}
			</style>
			<?php
		}



		/**
		 * Add plugin action links on all plugins page
		 *
		 * @param array $links current plugin action links.
		 */
		public function action_links( $links ) {
			$action_links = array();

			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=ppredirect-settings' ),
				esc_html__( 'Settings', 'previous-page-redirect-for-woocommerce' )
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description meta data on all plugins page
		 *
		 * @param array  $links all meta data.
		 * @param string $file  plugin base file name.
		 */
		public function desc_meta( $links, $file ) {
			global $ppredirect__;

			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( PPREDIRECT ) !== $file ) {
				return $links;
			}

			$row_meta         = array();
			$row_meta['docs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $ppredirect__['urls']['docs'] ),
				esc_html__( 'Documents', 'previous-page-redirect-for-woocommerce' )
			);

			$row_meta['apidocs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $ppredirect__['urls']['support'] ),
				esc_html__( 'Support', 'previous-page-redirect-for-woocommerce' )
			);

			return array_merge( $links, $row_meta );
		}

		/**
		 * Ask user feedback notice in every 15 days
		 */
		public function ask_feedback() {
			if ( isset( $_GET['ppr_nonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['ppr_nonce'] ) ), 'ppredirect_feedback_nonce' ) ) {
				return;
			}

			$task = isset( $_GET['ppredirect_rate'] ) ? sanitize_text_field( wp_unslash( $_GET['ppredirect_rate'] ) ) : '';

			if ( 'done' === $task ) {
				update_option( 'ppredirect_rate', 'done' );
			} elseif ( 'cancel' === $task ) {
				update_option( 'ppredirect_rate', gmdate( 'Y-m-d' ) );
			}

			if ( ! empty( $task ) ) {
				return;
			}

			// show notice to rate us every 15 days.
			if ( $this->if_show_notice( 'ppredirect_rate' ) ) {
				add_action( 'admin_notices', array( $this, 'feedback_notice' ) );
			}
		}

		/**
		 * User feedback notice
		 */
		public function feedback_notice() {
			global $ppredirect__;

			// get current page url.
			$page  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$page .= false !== strpos( $page, '?' ) ? '&' : '?';
			$page .= 'ppr_nonce=' . wp_create_nonce( 'ppredirect_feedback_nonce' ) . '&';

			$plugin = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $ppredirect__['urls']['plugin'] ),
				esc_html( $ppredirect__['name'] )
			);

			$review = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $ppredirect__['urls']['review'] ),
				esc_html__( 'WordPress.org', 'previous-page-redirect-for-woocommerce' )
			);

			?>
			<div class="notice notice-info is-dismissible">
				<h3><?php echo esc_html( $ppredirect__['name'] ); ?></h3>
				<p>
					<?php

					printf(
						// translators: %1$s: plugin name with url, %2$s: plugin review url on WordPress.
						esc_html__( 'Excellent! You\'ve been using %1$s for a while. We\'d appreciate if you kindly rate us on %2$s', 'previous-page-redirect-for-woocommerce' ),
						wp_kses_post( $plugin ),
						wp_kses_post( $review )
					);

					?>
				</p>
				<p>
					<?php

					printf(
						'<a href="%s" class="button-primary">%s</a>&nbsp;',
						esc_url( $ppredirect__['urls']['plugin'] ),
						esc_html__( 'Rate it', 'previous-page-redirect-for-woocommerce' )
					);

					printf(
						'<a href="%sppredirect_rate=done" class="button">%s</a>&nbsp;',
						esc_url( $page ),
						esc_html__( 'Already Did', 'previous-page-redirect-for-woocommerce' )
					);

					printf(
						'<a href="%sppredirect_rate=cancel" class="button">%s</a>',
						esc_url( $page ),
						esc_html__( 'Cancel', 'previous-page-redirect-for-woocommerce' )
					);

					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Check if the 15 days period passed for the notice key or is it done displaying
		 *
		 * @param string $key option meta key to determing the notice type.
		 */
		public function if_show_notice( $key ) {
			global $ppredirect__;

			$value = get_option( $key );

			if ( empty( $value ) ) {
				update_option( $key, gmdate( 'Y-m-d' ) );
				return false;
			}

			// if notice is done displaying forever?
			if ( 'done' === $value ) {
				return false;
			}

			// see if interval period passed.
			$difference  = date_diff( date_create( gmdate( 'Y-m-d' ) ), date_create( $value ) );
			$days_passed = (int) $difference->format( '%d' );

			return $days_passed < $ppredirect__['notice_interval'] ? false : true;
		}

		/**
		 * Display parent plugin missing notice
		 */
		public function wc_missing_notice() {

			$plugin = sprintf(
				'<a href="https://wordpress.org/plugins/previous-page-redirect-for-woocommerce/">%s</a>',
				esc_html__( 'Previous Page Redirect for WooCommerce', 'previous-page-redirect-for-woocommerce' )
			);

			$review = sprintf(
				'<a href="https://wordpress.org/plugins/woocommerce/">%s</a>',
				esc_html__( 'WooCommerce', 'previous-page-redirect-for-woocommerce' )
			);

			?>
			<div class="error">
				<p>
					<?php

						printf(
							// translators: %1$s: plugin name with url, %2$s: plugin review url on WordPress.
							esc_html__( '%1$s plugin has been deactivated. Please acitvate %2$s plugin first.', 'previous-page-redirect-for-woocommerce' ),
							wp_kses_post( $plugin ),
							wp_kses_post( $review )
						);

					?>
				</p>
			</div>
			<?php
		}



		/**
		 * Check if the plugin is in intended scope
		 */
		public function is_in_scope() {
			global $ppredirect__;

			$screen = get_current_screen();

			// check with our plugin screens.
			if ( in_array( $screen->base, $ppredirect__['admin_scopes'], true ) ) {
				return true;
			}

			return false;
		}
	}
}

$prepage_redirect = new PPRedirectLoader();
$prepage_redirect->init();
