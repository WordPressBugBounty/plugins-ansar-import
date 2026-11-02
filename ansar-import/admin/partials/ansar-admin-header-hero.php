<?php
/**
 * Shared Admin Header, Hero Banner, and Search/Filter Controls Partial
 *
 * Expects optional context variables from the parent display partial:
 * - $ansar_import_editor_switcher (array)  Editor switcher items (if empty, switcher & refresh button are omitted).
 * - $ansar_import_active_editor   (string) Currently active editor slug.
 * - $ansar_import_refresh_url     (string) URL to flush demo transients.
 * - $ansar_import_current_doc_url (string) Documentation URL for header button.
 * - $ansar_import_current_pro_url (string) Upgrade to Pro URL for header & hero CTA.
 * - $ansar_import_show_favorites_filter (bool) Whether to include "Favorites" in sort dropdown (default: true).
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ansar_import_header_doc_url        = ! empty( $ansar_import_current_doc_url ) ? $ansar_import_current_doc_url : ( ! empty( $ansar_current_doc_url ) ? $ansar_current_doc_url : ( ! empty( $current_doc_url ) ? $current_doc_url : ( ! empty( $starter_doc_url ) ? $starter_doc_url : 'https://docs.themeansar.com/' ) ) );
$ansar_import_header_pro_url        = ! empty( $ansar_import_current_pro_url ) ? $ansar_import_current_pro_url : ( ! empty( $ansar_current_pro_url ) ? $ansar_current_pro_url : ( ! empty( $current_pro_url ) ? $current_pro_url : 'https://themeansar.com/themes/' ) );
$ansar_import_is_hero_dismissed     = (bool) get_user_meta( get_current_user_id(), 'ansar_dismissed_hero', true );
$ansar_import_show_favorites_filter = isset( $ansar_import_show_favorites_filter ) ? (bool) $ansar_import_show_favorites_filter : ( isset( $ansar_show_favorites_filter ) ? (bool) $ansar_show_favorites_filter : ( isset( $show_favorites_filter ) ? (bool) $show_favorites_filter : true ) );
$ansar_import_show_header           = isset( $ansar_import_show_header ) ? (bool) $ansar_import_show_header : ( isset( $ansar_show_header ) ? (bool) $ansar_show_header : ( isset( $show_header ) ? (bool) $show_header : true ) );
$ansar_import_show_hero             = isset( $ansar_import_show_hero ) ? (bool) $ansar_import_show_hero : ( isset( $ansar_show_hero ) ? (bool) $ansar_show_hero : ( isset( $show_hero ) ? (bool) $show_hero : true ) );
$ansar_import_header_page_label     = ( ( isset( $ansar_page_slug ) && 'ansar-demo-import' === $ansar_page_slug ) || ( isset( $ansar_import_page_slug ) && 'ansar-demo-import' === $ansar_import_page_slug ) )
    ? __( 'Theme Demos', 'ansar-import' )
    : __( 'Starter Sites', 'ansar-import' );
$ansar_import_editor_switcher       = isset( $ansar_import_editor_switcher ) ? $ansar_import_editor_switcher : ( isset( $ansar_editor_switcher ) ? $ansar_editor_switcher : array() );
$ansar_import_active_editor         = isset( $ansar_import_active_editor ) ? $ansar_import_active_editor : ( isset( $ansar_active_editor ) ? $ansar_active_editor : '' );
$ansar_import_refresh_url           = isset( $ansar_import_refresh_url ) ? $ansar_import_refresh_url : ( isset( $ansar_refresh_url ) ? $ansar_refresh_url : '' );
?>
<?php if ( $ansar_import_show_header ) : ?>
<div class="ansar-admin-header" role="banner">
    <div class="ansar-admin-header-logo">
        <a href="https://themeansar.com/" target="_blank" rel="noopener noreferrer" class="ansar-logo" aria-label="<?php esc_attr_e( 'ThemeAnsar', 'ansar-import' ); ?>">
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'image/ansar-icon.svg' ); ?>" alt="<?php esc_attr_e( 'ThemeAnsar', 'ansar-import' ); ?>" width="36" height="36">
        </a>
        <span class="ansar-header-divider" aria-hidden="true"></span>
        <div class="ansar-header-brand-meta">
            <span class="ansar-header-product-badge"><?php echo esc_html( $ansar_import_header_page_label ); ?></span>
        </div>
    </div>
    <div class="ansar-admin-right-area">
        <div class="ansar-version" title="<?php echo esc_attr( sprintf( /* translators: %s: plugin version */ __( 'Ansar Import v%s', 'ansar-import' ), ANSAR_IMPORT_VERSION ) ); ?>">
            <span class="ansar-version-dot" aria-hidden="true"></span>
            <span>v<?php echo esc_html( ANSAR_IMPORT_VERSION ); ?></span>
        </div>
        <div class="ansar-doc">
            <a href="<?php echo esc_url( $ansar_import_header_doc_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Read Documentation', 'ansar-import' ); ?>">
                <span class="ansar-doc-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H13C16.7712 2 18.6569 2 19.8284 3.17157C21 4.34315 21 6.22876 21 10V14C21 17.7712 21 19.6569 19.8284 20.8284C18.6569 22 16.7712 22 13 22H11C7.22876 22 5.34315 22 4.17157 20.8284C3 19.6569 3 17.7712 3 14V10Z" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M8 12H16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        <path d="M8 8H16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        <path d="M8 16H13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="ansar-doc-label"><?php esc_html_e( 'Docs', 'ansar-import' ); ?></span>
            </a>
        </div>
        <div class="ansar-feature-pro">
            <a href="<?php echo esc_url( $ansar_import_header_pro_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Upgrade to Pro', 'ansar-import' ); ?>">
                <span class="ansar-pro-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <path d="M19.6872 14.0931L19.8706 12.3884C19.9684 11.4789 20.033 10.8783 19.9823 10.4999L20 10.5C20.8284 10.5 21.5 9.82843 21.5 9C21.5 8.17157 20.8284 7.5 20 7.5C19.1716 7.5 18.5 8.17157 18.5 9C18.5 9.37466 18.6374 9.71724 18.8645 9.98013C18.5384 10.1814 18.1122 10.606 17.4705 11.2451L17.4705 11.2451C16.9762 11.7375 16.729 11.9837 16.4533 12.0219C16.3005 12.043 16.1449 12.0213 16.0038 11.9592C15.7492 11.847 15.5794 11.5427 15.2399 10.934L13.4505 7.7254C13.241 7.34987 13.0657 7.03557 12.9077 6.78265C13.556 6.45187 14 5.77778 14 5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5C10 5.77778 10.444 6.45187 11.0923 6.78265C10.9343 7.03559 10.759 7.34984 10.5495 7.7254L8.76006 10.934C8.42056 11.5427 8.25081 11.847 7.99621 11.9592C7.85514 12.0213 7.69947 12.043 7.5467 12.0219C7.27097 11.9837 7.02381 11.7375 6.5295 11.2451C5.88787 10.606 5.46156 10.1814 5.13553 9.98012C5.36264 9.71724 5.5 9.37466 5.5 9C5.5 8.17157 4.82843 7.5 4 7.5C3.17157 7.5 2.5 8.17157 2.5 9C2.5 9.82843 3.17157 10.5 4 10.5L4.01771 10.4999C3.96702 10.8783 4.03162 11.4789 4.12945 12.3884L4.3128 14.0931C4.41458 15.0393 4.49921 15.9396 4.60287 16.75H19.3971C19.5008 15.9396 19.5854 15.0393 19.6872 14.0931Z" fill="currentColor" />
                        <path d="M10.9121 21H13.0879C15.9239 21 17.3418 21 18.2879 20.1532C18.7009 19.7835 18.9623 19.1172 19.151 18.25H4.84896C5.03765 19.1172 5.29913 19.7835 5.71208 20.1532C6.65817 21 8.07613 21 10.9121 21Z" fill="currentColor" />
                    </svg>
                </span>
                <span class="ansar-pro-title"><?php esc_html_e( 'Upgrade to Pro', 'ansar-import' ); ?></span>
                <span class="ansar-pro-arrow" aria-hidden="true">↗</span>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="ansar-import-wrap">
    <?php if ( $ansar_import_show_hero && ! $ansar_import_is_hero_dismissed ) : ?>
    <div class="ansar-import-hero">
        <button type="button" class="ansar-hero-dismiss-btn" aria-label="<?php esc_attr_e( 'Dismiss banner', 'ansar-import' ); ?>" title="<?php esc_attr_e( 'Dismiss banner', 'ansar-import' ); ?>">&times;</button>
        <div class="ansar-hero-badge">
            <span class="ansar-badge-icon">⚡</span>
            <span><?php esc_html_e( '1-Click Website Kits', 'ansar-import' ); ?></span>
        </div>
        <h1 class="ansar-heading"><?php echo wp_kses_post( __( 'Build Beautiful Websites in <span class="ansar-heading-gradient">Minutes</span>', 'ansar-import' ) ); ?></h1>
        <p class="ansar-desc"><?php esc_html_e( 'Import complete demo sites with customizer styles, widgets, and content. Fully compatible with Gutenberg Block Editor and Elementor.', 'ansar-import' ); ?></p>

        <div class="ansar-hero-features">
            <span class="ansar-feature-tag">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?php esc_html_e( '1-Click Setup', 'ansar-import' ); ?>
            </span>
            <span class="ansar-feature-tag">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?php esc_html_e( 'Zero Coding Required', 'ansar-import' ); ?>
            </span>
            <span class="ansar-feature-tag">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?php esc_html_e( 'Gutenberg & Elementor', 'ansar-import' ); ?>
            </span>
        </div>

        <div class="ansar-hero-cta">
            <a href="<?php echo esc_url( $ansar_import_header_pro_url ); ?>" target="_blank" class="ansar-pro-upgrade-banner">
                <span class="ansar-pro-crown">👑</span>
                <span class="ansar-pro-cta-text"><?php esc_html_e( 'Unlock All 50+ Pro Starter Sites & Premium Features', 'ansar-import' ); ?></span>
                <span class="ansar-pro-cta-arrow">→</span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div class="ansar-demo-controls">
        <div class="ansar-sort-box">
            <span class="ansar-sort-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="13" y2="6"></line>
                    <line x1="4" y1="12" x2="11" y2="12"></line>
                    <line x1="4" y1="18" x2="9" y2="18"></line>
                    <polyline points="15 15 18 18 21 15"></polyline>
                    <line x1="18" y1="6" x2="18" y2="18"></line>
                </svg>
            </span>
            <select id="ansar-demo-orderby" aria-label="<?php esc_attr_e( 'Sort demos by', 'ansar-import' ); ?>">
                <option value="popular"><?php esc_html_e( 'Sort by: Popular', 'ansar-import' ); ?></option>
                <option value="latest"><?php esc_html_e( 'Sort by: Latest', 'ansar-import' ); ?></option>
                <option value="asc"><?php esc_html_e( 'Sort by: Name (A–Z)', 'ansar-import' ); ?></option>
                <option value="desc"><?php esc_html_e( 'Sort by: Name (Z–A)', 'ansar-import' ); ?></option>
            </select>
            <span class="ansar-select-chevron" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </div>
        <div class="ansar-search-box">
            <span class="ansar-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" id="ansar-demo-search" placeholder="<?php esc_attr_e( 'Search demos by name...', 'ansar-import' ); ?>" autocomplete="off" spellcheck="false">
            <button type="button" id="ansar-search-clear" class="ansar-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'ansar-import' ); ?>" style="display: none;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="ansar-filter-box">
            <span class="ansar-filter-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
            </span>
            <select id="ansar-demo-sort" aria-label="<?php esc_attr_e( 'Filter demos by plan', 'ansar-import' ); ?>">
                <option value="all"><?php esc_html_e( 'All Plans', 'ansar-import' ); ?></option>
                <option value="free"><?php esc_html_e( 'Free Only', 'ansar-import' ); ?></option>
                <option value="pro"><?php esc_html_e( 'Pro Only', 'ansar-import' ); ?></option>
                <?php if ( $ansar_import_show_favorites_filter ) : ?>
                    <option value="favorite"><?php esc_html_e( 'Favorites', 'ansar-import' ); ?></option>
                <?php endif; ?>
            </select>
            <span class="ansar-select-chevron" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </div>
        <?php if ( ! empty( $ansar_import_editor_switcher ) && is_array( $ansar_import_editor_switcher ) ) : ?>
            <div class="ansar-editor-switcher is-selected" data-active-editor="<?php echo esc_attr( $ansar_import_active_editor ); ?>">
                <label class="screen-reader-text" for="ansar-editor-switcher"><?php esc_html_e( 'Select editor type', 'ansar-import' ); ?></label>
                <span class="ansar-editor-switcher-icon" aria-hidden="true" title="<?php esc_attr_e( 'Open Builder Setup Wizard', 'ansar-import' ); ?>"><?php echo isset( $ansar_import_editor_switcher[ $ansar_import_active_editor ]['icon'] ) ? $ansar_import_editor_switcher[ $ansar_import_active_editor ]['icon'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static trusted inline SVG icon. ?></span>
                <select id="ansar-editor-switcher" class="ansar-editor-switcher-select" aria-label="<?php esc_attr_e( 'Select editor type', 'ansar-import' ); ?>">
                    <?php foreach ( $ansar_import_editor_switcher as $ansar_import_editor_key => $ansar_import_editor_data ) : ?>
                        <option
                            value="<?php echo esc_url( $ansar_import_editor_data['url'] ); ?>"
                            data-editor="<?php echo esc_attr( $ansar_import_editor_key ); ?>"
                            data-icon="<?php echo esc_attr( $ansar_import_editor_data['icon'] ); ?>"
                            <?php selected( $ansar_import_active_editor, $ansar_import_editor_key ); ?>
                        >
                            <?php echo esc_html( $ansar_import_editor_data['label'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="ansar-editor-selected-badge" aria-hidden="true" title="<?php esc_attr_e( 'Active Editor', 'ansar-import' ); ?>"></span>
                <span class="ansar-select-chevron" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </div>
        <?php endif; ?>
        <?php if ( ! empty( $ansar_import_refresh_url ) ) : ?>
            <a
                class="ansar-editor-refresh"
                href="<?php echo esc_url( $ansar_import_refresh_url ); ?>"
                aria-label="<?php esc_attr_e( 'Refresh demos', 'ansar-import' ); ?>"
                title="<?php esc_attr_e( 'Refresh demos', 'ansar-import' ); ?>"
            >
                <span class="dashicons dashicons-update" aria-hidden="true"></span>
            </a>
        <?php endif; ?>
    </div>
</div>

