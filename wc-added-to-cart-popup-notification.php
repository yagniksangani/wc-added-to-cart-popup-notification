<?php
/**
 * Plugin Name:       Added to Cart Popup Notification for WooCommerce
 * Description:       Display a popup notification when a product is added to the WooCommerce cart.
 * Version:           1.0.0
 * Requires at least: 4.4
 * Requires PHP:      7.4
 * Tested up to:      6.5.2
 * Author:            Yagnik Sangani
 * Author URI:        https://profiles.wordpress.org/yagniksangani/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-ac-popup
 * Domain Path:       /languages
 *
 * @package WC_AC_POPUP_NOTIFICATION
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

define( 'WCACN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCACN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCACN_ROOT', dirname( plugin_basename( __FILE__ ) ) );
define( 'WCACN_PLUGIN', plugin_basename( __FILE__ ) );
define( 'WCACN_BASE_FILE', __FILE__ );

if ( ! class_exists( 'Wc_Ac_Popup_Notification_Init' ) ) :

	/**
	 * Class Wc_Ac_Popup_Notification_Init.
	 */
	class Wc_Ac_Popup_Notification_Init {

		/**
		 * Wc_Ac_Popup_Notification_Init constructor.
		 */
		public function __construct() {
			// Use init hook to load up required files.
			add_action( 'init', array( $this, 'required_files' ), 10 );

			// Load languages files.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Register plugin activation hook.
			register_activation_hook( __FILE__, array( $this, 'wcacn_child_plugin_activate' ) );

			// Add plugin settings link.
			add_filter( 'plugin_action_links_' . WCACN_PLUGIN, array( $this, 'wcacn_plugin_settings_link' ) );
		}


		/**
		 * Include additional files as needed.
		 */
		public function required_files() {
			// Load up our files.
			require_once WCACN_PATH . 'admin/class-wc-ac-popup-notification-settings.php';
		}


		/**
		 * Child plugin activate.
		 */
		public function wcacn_child_plugin_activate() {
			// Require parent plugin.
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) ) {

				deactivate_plugins( WCACN_PLUGIN );

				// Stop activation redirect and show error.
				wp_die( 'Sorry, but this plugin requires the "WooCommerce" Plugin to be installed and active. <br><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">&laquo; Return to Plugins</a>' );
			}
		}


		/**
		 * Plugin settings link.
		 *
		 * @param array $links links array.
		 *
		 * @return array
		 */
		public function wcacn_plugin_settings_link( $links ) {
			$settings_link = '<a href="options-general.php?page=wc-popup-notification-settings">Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}


		/**
		 * Load the plugin text domain for translation.
		 *
		 * With the introduction of plugins language packs in WordPress loading the textdomain is slightly more complex.
		 *
		 * We now have 3 steps:
		 *
		 * 1. Check for the language pack in the WordPress core directory
		 * 2. Check for the translation file in the plugin's language directory
		 * 3. Fallback to loading the textdomain the classic way
		 *
		 * @since    1.0.0
		 * @return boolean True if the language file was loaded, false otherwise
		 */
		public function load_plugin_textdomain() {

			// Require parent plugin.
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) ) {
				deactivate_plugins( WCACN_PLUGIN );
			}

			$lang_dir       = trailingslashit( WCACN_ROOT ) . 'languages/';
			$lang_path      = trailingslashit( WCACN_PATH ) . 'languages/';
			$locale         = apply_filters( 'plugin_locale', get_locale(), 'wc-ac-popup' );
			$mofile         = "wc-ac-popup-$locale.mo";
			$glotpress_file = WP_LANG_DIR . '/plugins/wc-ac-popup/' . $mofile;

			// Look for the GlotPress language pack first of all.
			if ( file_exists( $glotpress_file ) ) {
				$language = load_textdomain( 'wc-ac-popup', $glotpress_file );
			} elseif ( file_exists( $lang_path . $mofile ) ) {
				$language = load_textdomain( 'wc-ac-popup', $lang_path . $mofile );
			} else {
				$language = load_plugin_textdomain( 'wc-ac-popup', false, $lang_dir );
			}

			return $language;

		}

	}

	/**
	 * Get Wc_Ac_Popup_Notification_Init running.
	 */
	$wc_ac_popup_notification_init = new Wc_Ac_Popup_Notification_Init();

endif; // class_exists check.

// Include the woocommerce popup notification core code.
require_once WCACN_PATH . 'includes/class-wc-ac-popup-notification-core.php';
