<?php
/**
 * Plugin Name:       Ansar Import - Starter Sites & Template Library
 * Plugin URI:        https://themeansar.com/
 * Description:       One-click Starter Sites, Demo Importer, and In-Editor Section & Page Template Library for WordPress Block Editor (Gutenberg) & Elementor.
 * Version:           2.1.3
 * Author:            Themeansar
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author URI:        https://themeansar.com/
 * Text Domain:       ansar-import
 * Tested up to:      7.1
 * Domain Path:       /languages
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */
// If this file is called directly, abort.
if (!defined('WPINC')) { die; }

define('ANSAR_IMPORT_VERSION', '2.1.4');
define('ANSAR_IMPORT_FILE', __FILE__);
define('ANSAR_IMPORT_DIR_PATH', plugin_dir_path(ANSAR_IMPORT_FILE));
define('ANSAR_IMPORT_DIR_URL', plugin_dir_url(ANSAR_IMPORT_FILE));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ansar-import-activator.php
 */
function ansar_import_activate() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ansar-import-activator.php';
    Ansar_Import_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ansar-import-deactivator.php
 */
function ansar_import_deactivate() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ansar-import-deactivator.php';
    Ansar_Import_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'ansar_import_activate');
register_deactivation_hook(__FILE__, 'ansar_import_deactivate');

if ( defined( 'WP_SANDBOX_SCRAPING' ) && WP_SANDBOX_SCRAPING ) {
    ansar_import_activate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 * Note: WXR parsers and ANSAR_IMPORT_WP_Import are loaded on demand during demo_data_import.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ansar-import.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function ansar_import_run() {

    $plugin = new Ansar_Import();
    $plugin->run();
}

ansar_import_run();