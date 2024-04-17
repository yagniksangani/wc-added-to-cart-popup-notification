<?php
/**
 * Wc_Ac_Popup_Notification_Settings class for backend settings page & other code functions.
 *
 * @package WC_AC_POPUP_NOTIFICATION
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wc_Ac_Popup_Notification_Settings' ) ) :

	/**
	 * Wc_Ac_Popup_Notification_Settings class for the backend settings.
	 */
	class Wc_Ac_Popup_Notification_Settings {

		/**
		 * Holds the values to be used in the fields callbacks.
		 *
		 * @var options.
		 */
		private $options;

		/**
		 * Instance variable.
		 *
		 * @var instance.
		 */
		private static $instance = null;

		/**
		 * Wc_Ac_Popup_Notification_Settings - instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Wc_Ac_Popup_Notification_Settings - constructor.
		 */
		public function __construct() {
			$this->hooks();     // register hooks to make the custom post type do things.
		}


		/**
		 * Add all the hook inside the this private method.
		 */
		private function hooks() {

			// Enqueue scripts - backend.
			add_action( 'admin_enqueue_scripts', array( $this, 'wcacn_admin_enqueue_scripts_backend' ) );

			// Register a settings for a plugin.
			add_action( 'admin_init', array( $this, 'wc_ac_popup_notification_settings_page_init' ) );

			// Creating an options page.
			add_action( 'admin_menu', array( $this, 'wc_ac_popup_notification_add_plugin_page' ) );
		}


		/**
		 * Enqueue the scripts for backend.
		 */
		public function wcacn_admin_enqueue_scripts_backend() {
			wp_enqueue_style( 'wcacn-admin-style', WCACN_URL . 'assets/css/wcacn-backend-admin-style.css', array(), '1.0' );
		}


		/**
		 * Add options page.
		 */
		public function wc_ac_popup_notification_add_plugin_page() {
			// This page will be under "Settings".
			add_options_page(
				__( 'WC Notification Settings', 'wc-ac-popup' ),
				__( 'WC Notification Settings', 'wc-ac-popup' ),
				'manage_options',
				'wc-popup-notification-settings',
				array( $this, 'wc_popup_notification_create_admin_page' )
			);
		}


		/**
		 * Options page callback.
		 */
		public function wc_popup_notification_create_admin_page() {
			// Set class property.
			$this->options = get_option( 'wcacn_option' );
			?>
			<div class="wrap">
				<h1><?php echo esc_html( __( 'WooCommerce Notification Settings', 'wc-ac-popup' ) ); ?></h1>
				<form class="wcac_popup_settings_form" method="post" action="options.php">
				<?php
					// This prints out all hidden setting fields.
					settings_fields( 'wcacn_option_group' );
					do_settings_sections( 'wc-ac-popup-notification' );
					submit_button();
				?>
				</form>
			</div>
			<?php
		}


		/**
		 * Register and add settings.
		 */
		public function wc_ac_popup_notification_settings_page_init() {
			register_setting(
				'wcacn_option_group', // Option group.
				'wcacn_option', // Option name.
			);

			add_settings_section(
				'setting_section_fields', // ID.
				'', // Title.
				array( $this, 'wcacn_print_section_info' ), // Callback.
				'wc-ac-popup-notification' // Page.
			);

			add_settings_field(
				'wcacn_layout_option', // ID.
				__( 'Layout', 'wc-ac-popup' ), // Title.
				array( $this, 'wcacn_section_layout_option_callback' ), // Callback.
				'wc-ac-popup-notification', // Page.
				'setting_section_fields' // Section.
			);

			add_settings_field(
				'wcacn_position_option', // ID.
				__( 'Position', 'wc-ac-popup' ), // Title.
				array( $this, 'wcacn_section_position_option_callback' ), // Callback.
				'wc-ac-popup-notification', // Page.
				'setting_section_fields' // Section.
			);

			add_settings_field(
				'wcacn_close_after', // ID.
				__( 'Close Aftter (Seconds)', 'wc-ac-popup' ), // Title.
				array( $this, 'wcacn_section_close_after_callback' ), // Callback.
				'wc-ac-popup-notification', // Page.
				'setting_section_fields' // Section.
			);

			add_settings_field(
				'wcacn_display_condition', // ID.
				__( 'Display Condition', 'wc-ac-popup' ), // Title.
				array( $this, 'wcacn_section_display_condition_callback' ), // Callback.
				'wc-ac-popup-notification', // Page.
				'setting_section_fields' // Section.
			);
		}


		/**
		 * Print the Section text.
		 */
		public function wcacn_print_section_info() {
			$html = '<p class="wcacn_instruction_notes">Set the below settings for WooCommerce added to cart popup notification.</p>';
			echo $html; // phpcs:ignore
		}


		/**
		 * Get the settings option array and print one of its values.
		 */
		public function wcacn_section_layout_option_callback() {
			$layout = isset( $this->options['wcacn_layout_option'] ) ? esc_attr( $this->options['wcacn_layout_option'] ) : '';
			?>
				<input type="radio" class="wcacn-layout-field" id="wcacn_layout_option_layout1" name="wcacn_option[wcacn_layout_option]" value="layout1" <?php echo ( 'layout1' === $layout ) ? 'checked' : ''; ?>/><label for="wcacn_layout_option_layout1"><?php echo esc_html( __( 'Product image within the content on the left side', 'wc-ac-popup' ) ); ?></label>
				<br><br>
				<img src="<?php echo esc_url( WCACN_URL . '/assets/images/layout-1.png' ); ?>" width="100px">
				<br><br>
				<input type="radio" class="wcacn-layout-field" id="wcacn_layout_option_layout2" name="wcacn_option[wcacn_layout_option]" value="layout2" <?php echo ( 'layout2' === $layout ) ? 'checked' : ''; ?>/><label for="wcacn_layout_option_layout2"><?php echo esc_html( __( 'Product image as a background', 'wc-ac-popup' ) ); ?></label>
				<br><br>
				<img src="<?php echo esc_url( WCACN_URL . '/assets/images/layout-2.png' ); ?>" width="100px">
			<?php
		}


		/**
		 * Get the settings option array and print one of its values.
		 */
		public function wcacn_section_position_option_callback() {
			$position = isset( $this->options['wcacn_position_option'] ) ? esc_attr( $this->options['wcacn_position_option'] ) : '';
			?>
				<input type="radio" class="wcacn-position-field" id="wcacn_position_option_top" name="wcacn_option[wcacn_position_option]" value="top" <?php echo ( 'top' === $position ) ? 'checked' : ''; ?>/><label for="wcacn_position_option_top"><?php echo esc_html( __( 'Top', 'wc-ac-popup' ) ); ?></label>
				<br><br>
				<input type="radio" class="wcacn-position-field" id="wcacn_position_option_bottom" name="wcacn_option[wcacn_position_option]" value="bottom" <?php echo ( 'bottom' === $position ) ? 'checked' : ''; ?>/><label for="wcacn_position_option_bottom"><?php echo esc_html( __( 'Bottom', 'wc-ac-popup' ) ); ?></label>
				<br><br>
				<small class="wcacn_instruction_notes"><strong><?php echo esc_html( __( 'Note: Top right corner or bottom right corner on the screen.', 'wc-ac-popup' ) ); ?><strong></small>
			<?php
		}


		/**
		 * Get the settings option array and print one of its values.
		 */
		public function wcacn_section_close_after_callback() {
			printf(
				'<input type="number" class="wcacn-close-after-field" id="wcacn_close_after" name="wcacn_option[wcacn_close_after]" value="%s" />',
				isset( $this->options['wcacn_close_after'] ) ? esc_attr( $this->options['wcacn_close_after'] ) : '',
			);
		}


		/**
		 * Get the settings option array and print one of its values.
		 */
		public function wcacn_section_display_condition_callback() {
			$display_pages = isset( $this->options['wcacn_display_condition'] ) ? $this->options['wcacn_display_condition'] : array();

			$select_options = array(
				'all-pages'       => __( 'All Pages', 'wc-ac-popup' ),
				'shop'            => __( 'Shop Archive', 'wc-ac-popup' ),
				'shop-categories' => __( 'Shop Archive Categories', 'wc-ac-popup' ),
				'shop-tags'       => __( 'Shop Archive Tags', 'wc-ac-popup' ),
				'shop-attributes' => __( 'Shop Archive Product Attributes', 'wc-ac-popup' ),
				'single-products' => __( 'Single Products', 'wc-ac-popup' ),
			);
			?>
				<select id="wcacn_display_condition" class="wcacn-display-condition-field" name="wcacn_option[wcacn_display_condition][]" size="6" multiple>
					<?php
					foreach ( $select_options as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( in_array( $key, $display_pages, true ) ) ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
			<?php
		}

	}

	/**
	 * Get Wc_Ac_Popup_Notification_Settings running.
	 */
	$wc_ac_popup_notification_settings = new Wc_Ac_Popup_Notification_Settings();

endif; // class_exists check.
