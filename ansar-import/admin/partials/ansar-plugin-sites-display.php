<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themeansar.com/
 * @since      1.0.0
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
<?php
include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-admin-header-hero.php';

$ansar_import_theme_data = wp_get_theme();
$ansar_import_theme_name = $ansar_import_theme_data->get( 'Name' );
$ansar_import_theme_slug = $ansar_import_theme_data->get( 'TextDomain' );

$ansar_import_all_demos = Ansar_Import_Admin::get_cached_demos( 'plugin', $ansar_import_theme_name );

if ( ! is_array( $ansar_import_all_demos ) || count( $ansar_import_all_demos ) === 0 ) {
    ?>
    <div class="ansar-error-notice" style="text-align: center; padding: 50px; background: #fff; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php esc_html_e( 'Oops! Demos could not be loaded.', 'ansar-import' ); ?></h2>
        <p><?php esc_html_e( 'We are having trouble connecting to the demo server. Please check your internet connection or try again later.', 'ansar-import' ); ?></p>
        <button onclick="window.location.reload();" class="button button-primary"><?php esc_html_e( 'Try Again', 'ansar-import' ); ?></button>
    </div>
    </div> <!-- Close .wrap -->
    <?php
    return; // Stop rendering
}

$ansar_import_first_match_found = false;
?>

<hr class="wp-header-end">
<div class="theme-browser rendered demo-ansar-container">
    <div class="themes wp-clearfix">
        <div uk-filter="target: .js-filter">

            <div class="js-filter grid-wrap">

                <?php
                $ansar_import_context = 'plugin';
                foreach ( $ansar_import_all_demos as $ansar_import_demo ) {
                    include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-demo-box.php';
                }
                ?>

            </div>
        </div>
    </div>

</div>

<!-- Modal preview  End -->

<?php
require plugin_dir_path( __FILE__ ) . 'ansar-import-modals.php';
?>