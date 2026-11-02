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

$ansar_import_all_demos_unfiltered = Ansar_Import_Admin::get_cached_demos( 'starter' );
extract( Ansar_Import_Admin::get_editor_context( 'ansar-starter-sites', $ansar_import_all_demos_unfiltered ) );
$ansar_import_is_embedded_view = isset( $show_header ) && false === $show_header;
?>
<div class="<?php echo esc_attr( $ansar_import_is_embedded_view ? 'ansar-content-wrapper ansar-embedded-wrapper' : 'wrap ansar-content-wrapper' ); ?>">
<?php
include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-admin-header-hero.php';

if ( empty( $ansar_import_all_demos_unfiltered ) ) { ?>
    <div class="ansar-empty-editor-state" style="margin-top: 50px;">
        <h2><?php esc_html_e( 'Unable to connect to the demo library', 'ansar-import' ); ?></h2>
        <p><?php esc_html_e( 'We could not fetch the demos from the server. Please check your internet connection, or try refreshing the page.', 'ansar-import' ); ?></p>
        <a href="<?php echo esc_url( $ansar_refresh_url ); ?>" class="button button-primary" style="margin-top: 20px;"><?php esc_html_e( 'Try Again', 'ansar-import' ); ?></a>
        <?php
        $ansar_import_api_err = Ansar_Import_Admin::get_last_api_error();
        if ( current_user_can( 'manage_options' ) && ! empty( $ansar_import_api_err ) ) : ?>
            <div style="margin: 25px auto 0; max-width: 650px; text-align: left; background: #fff; border-left: 4px solid #dc3232; padding: 15px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px;">
                <h4 style="margin: 0 0 8px; color: #dc3232;"><?php esc_html_e( 'Server Diagnostic Details (Admins Only):', 'ansar-import' ); ?></h4>
                <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Error Message:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( $ansar_import_api_err['message'] ); ?></code></p>
                <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Requested URL:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( $ansar_import_api_err['url'] ); ?></code></p>
                <?php if ( ! empty( $ansar_import_api_err['code'] ) ) : ?>
                    <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Status Code:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( (string) $ansar_import_api_err['code'] ); ?></code></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </div> <!-- close wrap -->
<?php return; }

$ansar_import_all_categories = Ansar_Import_Admin::get_cached_categories( 'starter' );
if ( ! is_array( $ansar_import_all_categories ) ) {
    $ansar_import_all_categories = array();
}

// Fallback: If categories endpoint is empty or failed, extract categories from the loaded demos.
if ( empty( $ansar_import_all_categories ) && ! empty( $ansar_import_all_demos_unfiltered ) ) {
    $ansar_import_found_cats = array();
    foreach ( $ansar_import_all_demos_unfiltered as $ansar_import_raw_demo ) {
        if ( ! empty( $ansar_import_raw_demo['categories'] ) && is_array( $ansar_import_raw_demo['categories'] ) ) {
            foreach ( $ansar_import_raw_demo['categories'] as $ansar_import_cat ) {
                if ( isset( $ansar_import_cat['id'], $ansar_import_cat['name'] ) && ! isset( $ansar_import_found_cats[ $ansar_import_cat['id'] ] ) ) {
                    $ansar_import_found_cats[ $ansar_import_cat['id'] ] = array(
                        'id'   => $ansar_import_cat['id'],
                        'name' => $ansar_import_cat['name'],
                        'slug' => isset( $ansar_import_cat['slug'] ) ? $ansar_import_cat['slug'] : sanitize_title( $ansar_import_cat['name'] ),
                    );
                }
            }
        }
    }
    $ansar_import_all_categories = array_values( $ansar_import_found_cats );
}

$ansar_import_all_demos      = Ansar_Import_Admin::filter_demos_by_editor( $ansar_import_all_demos_unfiltered, $ansar_active_editor );
$ansar_no_demos = ( count( $ansar_import_all_demos ) === 0 );
$ansar_import_present_cat    = array();

foreach ( $ansar_import_all_demos as $ansar_import_demo ) {
    if ( ! empty( $ansar_import_demo['categories'] ) && is_array( $ansar_import_demo['categories'] ) ) {
        foreach ( $ansar_import_demo['categories'] as $ansar_import_in_cat ) {
            if ( isset( $ansar_import_in_cat['id'] ) ) {
                $ansar_import_present_cat[] = $ansar_import_in_cat['id'];
            }
        }
    }
}
$ansar_import_present_cat = array_values( array_unique( $ansar_import_present_cat ) );
?>

<hr class="wp-header-end">
<div class="theme-browser rendered demo-ansar-container">
    <div class="themes wp-clearfix">
        <div uk-filter="target: .js-filter">
            <!-- Filter Controls -->
            <ul class="uk-subnav uk-subnav-pill">
                <li class="uk-active" uk-filter-control><a href="#">All</a></li>
                <?php foreach ( $ansar_import_all_categories as $ansar_import_category ) {
                    if ( 'uncategorized' === $ansar_import_category['slug'] ) {
                        continue;
                    }
                    $ansar_import_cat_is_hidden = ( ! empty( $ansar_import_present_cat ) && ! in_array( $ansar_import_category['id'], $ansar_import_present_cat, false ) );
                    ?>
                    <li uk-filter-control="[data-color*='cat_<?php echo esc_attr( $ansar_import_category['id'] ); ?>']"<?php if ( $ansar_import_cat_is_hidden ) : ?> style="display: none;"<?php endif; ?>><a href="#"><?php echo esc_html( $ansar_import_category['name'] ); ?></a></li>
                <?php } ?>
            </ul>
            <!-- / Filter Controls -->

            <div class="js-filter grid-wrap">

                <div class="ansar-empty-editor-state"<?php if ( ! $ansar_no_demos ) : ?> style="display: none;"<?php endif; ?>>
                    <span class="ansar-empty-editor-state-icon" aria-hidden="true"><?php echo isset( $ansar_editor_switcher[ $ansar_active_editor ] ) ? $ansar_editor_switcher[ $ansar_active_editor ]['icon'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static trusted inline SVG icon. ?></span>
                    <h2><?php esc_html_e( 'No demos available yet', 'ansar-import' ); ?></h2>
                    <p><?php esc_html_e( 'Try another editor type from the switcher above.', 'ansar-import' ); ?></p>
                </div>

                <?php
                $ansar_import_context = 'starter';
                foreach ( $ansar_import_all_demos_unfiltered as $ansar_import_demo ) {
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
