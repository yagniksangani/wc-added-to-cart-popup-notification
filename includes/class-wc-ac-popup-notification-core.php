<?php
/**
 * Wc_Ac_Popup_Notification_Core class frontend code functions.
 *
 * @package WC_AC_POPUP_NOTIFICATION
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Wc_Ac_Popup_Notification_Init' ) ) :

	/**
	 * Adds Wc_Ac_Popup_Notification_Core widget.
	 */
	class Wc_Ac_Popup_Notification_Core {

		/**
		 * Holds the values to be used in the fields callbacks.
		 *
		 * @var options.
		 */
		private $options;

		/**
		 * Hold the action value.
		 *
		 * @var action.
		 */
		public $action = null;

		/**
		 * Instance variable.
		 *
		 * @var instance.
		 */
		private static $instance = null;

		/**
		 * Wc_Ac_Popup_Notification_Core - instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Wc_Ac_Popup_Notification_Core - constructor.
		 */
		public function __construct() {
			$this->hooks();     // register hooks to make the custom post type do things.
		}


		/**
		 * Add all the hook inside the this private method.
		 */
		private function hooks() {
			// Enqueue scripts - frontend.
			add_action( 'wp_enqueue_scripts', array( $this, 'wcacn_public_enqueue_scripts_frontend' ) );

			// Add code in footer.
			add_action( 'wp_footer', array( $this, 'wcacn_get_popup_markup' ) );

			// Prevent cart redirection.
			add_filter( 'pre_option_woocommerce_cart_redirect_after_add', array( $this, 'wcacn_prevent_cart_redirect' ), 10, 1 );

			// Add to cart ajax actions.
			add_action( 'wc_ajax_wcacn_cp_add_to_cart', array( $this, 'wcacn_cp_add_to_cart' ) );
			add_action( 'wc_ajax_wcacn_cp_update_cart', array( $this, 'wcacn_cp_update_cart' ) );

			// Set last added cart item key.
			add_action( 'woocommerce_add_to_cart', array( $this, 'wcacn_set_last_added_cart_item_key' ), 10, 6 );

			// Add to cart fragments.
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'wcacn_add_to_cart_fragments' ) );
		}


		/**
		 * Enqueue the scripts for frontend.
		 */
		public function wcacn_public_enqueue_scripts_frontend() {
			wp_enqueue_script( 'wcacn-public-script', WCACN_URL . 'assets/js/wcacn-frontend-public-script.js', array(), '1.0', true );
			wp_enqueue_style( 'wcacn-public-style', WCACN_URL . 'assets/css/wcacn-frontend-public-style.css', array(), '1.0' );

			wp_localize_script(
				'wcacn-public-script',
				'wcacn_cp_localize',
				array(
					'adminurl'    => admin_url() . 'admin-ajax.php',
					'homeurl'     => get_bloginfo( 'url' ),
					'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'reset_cart'  => true,
					'closeafter'  => self::wcacnn_get_close_after_setting(),
				)
			);
		}


		/**
		 * Add to cart fragments.
		 *
		 * @param array $fragments fragments array.
		 *
		 * @return array
		 */
		public function wcacn_add_to_cart_fragments( $fragments ) {
			$cart_content = $this->wcacn_get_cart_content();

			// Cart content.
			$fragments['div.wcacn-cp-content'] = '<div class="wcacn-cp-content">' . $cart_content . '</div>';

			return $fragments;
		}


		/**
		 * Get cart content.
		 *
		 * @return void
		 */
		public function wcacn_get_cart_content() {

			// Get last cart item key.
			$cart_item_key = get_option( 'wcacn_popup_added_cart_item_key' );

			if ( ! $cart_item_key ) {
				return;
			}

			// Remove from the database.
			delete_option( 'wcacn_popup_added_cart_item_key' );

			$args = array(
				'cart_item_key' => $cart_item_key,
				'action'        => $this->action,
			);

			$layout = self::wcacnn_get_layout_setting();

			ob_start();

			if ( 'layout1' === $layout ) {
				wc_get_template( 'wcacn-popup-layout1-content.php', $args, '', WCACN_PATH . 'templates/' );
			} elseif ( 'layout2' === $layout ) {
				wc_get_template( 'wcacn-popup-layout2-content.php', $args, '', WCACN_PATH . 'templates/' );
			}

			return ob_get_clean();
		}


		/**
		 * Get the selected notification layout.
		 *
		 * @return string
		 */
		public function wcacnn_get_layout_setting() {
			$wcacn_option = get_option( 'wcacn_option' );
			$layout       = $wcacn_option['wcacn_layout_option'];
			if ( empty( $layout ) ) {
				$layout = 'layout1';
			}
			return $layout;
		}

		/**
		 * Get the selected notification position.
		 *
		 * @return string
		 */
		public function wcacnn_get_position_setting() {
			$wcacn_option = get_option( 'wcacn_option' );
			$position     = $wcacn_option['wcacn_position_option'];
			if ( empty( $position ) ) {
				$position = 'top';
			}
			return apply_filters( 'wcacn_filter_popup_notification_position', $position );
		}

		/**
		 * Get the close after seconds.
		 *
		 * @return string
		 */
		public function wcacnn_get_close_after_setting() {
			$wcacn_option = get_option( 'wcacn_option' );
			$closeafter   = $wcacn_option['wcacn_close_after'];

			return apply_filters( 'wcacn_filter_popup_notification_close_after_seconds', $closeafter );
		}

		/**
		 * Get the display condition pages.
		 *
		 * @return array
		 */
		public function wcacnn_get_display_condition_setting() {
			$wcacn_option  = get_option( 'wcacn_option' );
			$display_pages = $wcacn_option['wcacn_display_condition'];

			return apply_filters( 'wcacn_filter_popup_notification_display_condition', $display_pages );
		}


		/**
		 * Add code in footer.
		 */
		public function wcacn_get_popup_markup() {
			if ( is_cart() || is_checkout() ) {
				return;
			}

			$proceed       = 0; // flag for display condition pages.
			$display_pages = self::wcacnn_get_display_condition_setting();

			if ( ! empty( $display_pages ) ) {
				if ( in_array( 'all-pages', $display_pages, true ) ) {
					$proceed = 1;
				} elseif ( in_array( 'shop', $display_pages, true ) && is_shop() ) {
					$proceed = 1;
				} elseif ( in_array( 'shop-categories', $display_pages, true ) && is_product_category() ) {
					$proceed = 1;
				} elseif ( in_array( 'shop-tags', $display_pages, true ) && is_product_tag() ) {
					$proceed = 1;
				} elseif ( in_array( 'shop-attributes', $display_pages, true ) && self::wcacn_is_wc_attribute() ) {
					$proceed = 1;
				} elseif ( in_array( 'single-products', $display_pages, true ) && is_product() ) {
					$proceed = 1;
				}
			} else {
				$proceed = 1;
			}

			if ( 0 === $proceed ) {
				return;
			}

			$position = self::wcacnn_get_position_setting();

			$args = array(
				'position' => $position,
			);

			wc_get_template( 'wcacn-popup-template.php', $args, '', WCACN_PATH . 'templates/' );
		}


		/**
		 * Prevent add to cart redirection.
		 *
		 * @param  string $value value.
		 * @return string
		 */
		public function wcacn_prevent_cart_redirect( $value ) {
			if ( ! is_admin() ) {
				return 'no';
			}

			return $value;
		}


		/**
		 * Function for `woocommerce_add_to_cart` action-hook.
		 *
		 * @param string  $cart_id          ID of the item in the cart.
		 * @param integer $product_id       ID of the product added to the cart.
		 * @param integer $request_quantity Quantity of the item added to the cart.
		 * @param integer $variation_id     Variation ID of the product added to the cart.
		 * @param array   $variation        Array of variation data.
		 * @param array   $cart_item_data   Array of other cart item data.
		 *
		 * @return void
		 */
		public function wcacn_set_last_added_cart_item_key( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
			$this->action = 'add';
			update_option( 'wcacn_popup_added_cart_item_key', $cart_id );
		}


		/**
		 * Add to cart ajax on single product page.
		 *
		 * @return void
		 */
		public function wcacn_cp_add_to_cart() {
			global $woocommerce,$wcacn_cp_gl_qtyen_value,$wcacn_cp_gl_ibtne_value;

			if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'wcacn_cp_add_to_cart' || ! isset( $_POST['add-to-cart'] ) ) { // phpcs:ignore
				die();
			}

			// get woocommerce error notice.
			$error = wc_get_notices( 'error' );
			$html  = '';

			if ( $error ) {
				// print notice.
				ob_start();
				foreach ( $error as $value ) {
					wc_print_notice( $value['notice'], 'error' );
				}

				$js_data = array(
					'error' => ob_get_clean(),
				);

				wc_clear_notices(); // clear other notice.
				wp_send_json( $js_data );
			} else {
				// trigger action for added to cart in ajax.
				do_action( 'woocommerce_ajax_added_to_cart', intval( $_POST['add-to-cart'] ) ); // phpcs:ignore

				wc_clear_notices(); // clear other notice.
				WC_AJAX::get_refreshed_fragments();
			}

			die();
		}


		/**
		 * Check if product attribute page.
		 *
		 * @return bool
		 */
		public function wcacn_is_wc_attribute() {
			if ( is_tax() && function_exists( 'taxonomy_is_product_attribute' ) ) {
				$tax_obj = get_queried_object();
				return taxonomy_is_product_attribute( $tax_obj->taxonomy );
			}
			return false;
		}
	}


	/**
	 * Get Wc_Ac_Popup_Notification_Core running.
	 */
	$wc_ac_popup_notification_core = new Wc_Ac_Popup_Notification_Core();

endif; // class_exists check.
