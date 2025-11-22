<?php
/**
 * Plugin Name: Gravity Forms Listmonk Connector
 * Description: Minimal Gravity Forms feed add-on to push subscribers into Listmonk.
 * Version: 1.0.0
 * Author: 8am GmbH
* Author URI: https://8am.ch
 * Text Domain: eightam-gf-listmonk
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Tested up to: 6.5
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || die();

define( 'EAGF_LISTMONK_VERSION', '1.0.0' );
define( 'EAGF_LISTMONK_SLUG', 'eightam-gf-listmonk' );
define( 'EAGF_LISTMONK_PLUGIN_FILE', __FILE__ );
define( 'EAGF_LISTMONK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EAGF_LISTMONK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'eagf_listmonk_load_textdomain' );

/**
 * Load plugin textdomain for translations.
 *
 * @return void
 */
function eagf_listmonk_load_textdomain() {
	load_plugin_textdomain(
		'eightam-gf-listmonk',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);
}

add_action( 'gform_loaded', array( 'EAGF_Listmonk_Bootstrap', 'load' ), 5 );

class EAGF_Listmonk_Bootstrap {
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		GFForms::include_feed_addon_framework();

		require_once __DIR__ . '/includes/class-listmonk-client.php';
		require_once __DIR__ . '/includes/class-listmonk-addon.php';

		GFAddOn::register( 'EAGF_Listmonk_AddOn' );
	}
}

/**
 * Helper accessor.
 *
 * @return EAGF_Listmonk_AddOn
 */
function eagf_listmonk() {
	return EAGF_Listmonk_AddOn::get_instance();
}
