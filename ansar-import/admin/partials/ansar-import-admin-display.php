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


$pro_urls = [
    'Newsxo'        => 'https://themeansar.com/themes/newsxo-pro-magazine-blog-wordpress-theme/',
    'Blogier'       => 'https://themeansar.com/themes/blogier-pro-minimal-blog-magazine-wordpress-theme/',
    'NewsPaperup'   => 'https://themeansar.com/themes/newspaperup-pro-magazine-blog-wordpress-theme/',
    'BlogData'      => 'https://themeansar.com/themes/blogdata-pro-minimal-blog-magazine-wordpress-theme/',
    'NewsAir'       => 'https://themeansar.com/themes/newsair-pro/',
    'BlogArise'     => 'https://themeansar.com/themes/blogarise-pro/',
    'Blogus'        => 'https://themeansar.com/themes/blogus-pro/',
    'Fameup'        => 'https://themeansar.com/themes/fameup-pro/',
    'Industryup'    => 'https://themeansar.com/themes/industryup-pro/',
    'Consultco'     => 'https://themeansar.com/themes/consultco-pro/',
    'Agencyup'      => 'https://themeansar.com/themes/agencyup-pro/',
    'Newses'        => 'https://themeansar.com/themes/newses-pro/',
    'Newsup'        => 'https://themeansar.com/themes/newsup-pro/',
    'Consultup'     => 'https://themeansar.com/themes/consultup-pro/',
    'Short'         => 'https://themeansar.com/themes/short-pro/',
    'Yoga'          => 'https://themeansar.com/themes/yoga-wordpress-theme/',
    'Businessup'    => 'https://themeansar.com/themes/businessup-pro/',
    'Transportex'   => 'https://themeansar.com/themes/transportex-pro/',
    'Shopress'      => 'https://themeansar.com/themes/shopress-pro/',
    'Bizwhoop'      => 'https://themeansar.com/themes/bizwhoop-wordpress-theme/',
    'Proficiency'   => 'https://themeansar.com/themes/proficiency-one-page-business-theme/',
    'Ferry'         => 'https://themeansar.com/themes/ferry-woocommerce-wordpress-theme/',
    'Financeup'     => 'https://themeansar.com/themes/financeup/',
    'Shopbiz'       => 'https://themeansar.com/themes/shopbiz/',
];

$doc_urls = [
    'Newsxo'            => 'https://docs.themeansar.com/docs/newsxo-lite/',
    'Newsxo Pro'        => 'https://docs.themeansar.com/docs/newsxo-pro/',
    'Blogier'           => 'https://docs.themeansar.com/docs/blogus-lite/',
    'Blogier Pro'       => 'https://docs.themeansar.com/docs/blogier-pro/',
    'NewsPaperup'       => 'https://docs.themeansar.com/docs/newspaperup-lite/',
    'NewsPaperup Pro'   => 'https://docs.themeansar.com/docs/newspaperup-pro/',
    'BlogData'          => 'https://docs.themeansar.com/docs/blogdata-lite/',
    'BlogData Pro'      => 'https://docs.themeansar.com/docs/blogdata-pro/',
    'NewsAir'           => 'https://docs.themeansar.com/docs/newsair-lite/',
    'NewsAir Pro'       => 'https://docs.themeansar.com/docs/newsair-pro/',
    'BlogArise'         => 'https://docs.themeansar.com/docs/blogarise-lite/',
    'BlogArise Pro'     => 'https://docs.themeansar.com/docs/blogarise-pro/',
    'Blogus'            => 'https://docs.themeansar.com/docs/blogus-lite/',
    'Blogus Pro'        => 'https://docs.themeansar.com/docs/blogus-pro/',
    'Fameup'            => 'https://docs.themeansar.com/docs/fameup-pro/',
    'Fameup Pro'        => 'https://docs.themeansar.com/docs/fameup-pro/',
    'Industryup'        => 'https://docs.themeansar.com/docs/industryup-pro/',
    'Industryup Pro'    => 'https://docs.themeansar.com/docs/industryup-pro/',
    'Consultco'         => 'https://docs.themeansar.com/docs/consultco-pro/',
    'Consultco Pro'     => 'https://docs.themeansar.com/docs/consultco-pro/',
    'Agencyup'          => 'https://docs.themeansar.com/docs/agencyup-lite/',
    'Agencyup Pro'      => 'https://docs.themeansar.com/docs/agencyup-pro/',
    'Newses'            => 'https://docs.themeansar.com/docs/newses-pro/',
    'Newses Pro'        => 'https://docs.themeansar.com/docs/newses-pro/',
    'Newsup'            => 'https://docs.themeansar.com/docs/newsup-lite/',
    'Newsup Pro'        => 'https://docs.themeansar.com/docs/newsup-pro/',
    'Consultup'         => 'https://docs.themeansar.com/docs/consultup-pro/',
    'Consultup Pro'     => 'https://docs.themeansar.com/docs/consultup-pro/',
    'Short'             => 'https://docs.themeansar.com/docs/short-pro/',
    'Short Pro'         => 'https://docs.themeansar.com/docs/short-pro/',
    'Yoga'              => 'https://docs.themeansar.com/docs/yoga-pro/',
    'Yoga Pro'          => 'https://docs.themeansar.com/docs/yoga-pro/',
    'Businessup'        => 'https://docs.themeansar.com/docs/businessup-pro/',
    'Businessup Pro'    => 'https://docs.themeansar.com/docs/businessup-pro/',
    'Transportex'       => 'https://docs.themeansar.com/docs/transportex-pro/',
    'Transportex Pro'   => 'https://docs.themeansar.com/docs/transportex-pro/',
    'Shopress'          => 'https://docs.themeansar.com/docs/shopress-pro/',
    'Shopress Pro'      => 'https://docs.themeansar.com/docs/shopress-pro/',
    'Bizwhoop'          => 'https://docs.themeansar.com/',
    'Bizwhoop Pro'      => 'https://docs.themeansar.com/',
    'Proficiency'       => 'https://docs.themeansar.com/',
    'Proficiency Pro'   => 'https://docs.themeansar.com/',
    'Ferry'             => 'https://docs.themeansar.com/',
    'Ferry Pro'         => 'https://docs.themeansar.com/',
    'Financeup'         => 'https://docs.themeansar.com/docs/financeup-pro/',
    'Financeup Pro'     => 'https://docs.themeansar.com/docs/financeup-pro/',
    'Shopbiz'           => 'https://docs.themeansar.com/docs/shopbiz-pro/',
    'Shopbiz Pro'       => 'https://docs.themeansar.com/docs/shopbiz-pro/',
];

$theme_data = wp_get_theme();
$current_pro_url = '';
$current_doc_url = '';
$parent = $theme_data->parent();

if ( $parent && $theme_data->get('Name') !== 'Blogier' ) {
    $tname = $parent->get('Name');
} else {
    $tname = $theme_data->get('Name');
}
foreach ($pro_urls as $name => $url) {
    if($name == $tname){
        $current_pro_url = $url;
        break;
    }else{
        $current_pro_url = 'https://themeansar.com/themes';
    }
}
foreach ($doc_urls as $name => $url) {
    if($name == $tname){
        $current_doc_url = $url;
        break;
    }else{
        $current_doc_url = 'https://docs.themeansar.com';
    }
}

if ($theme_data->get('Author') != 'themeansar' && $theme_data->get('Author') != 'Themeansar') {
    echo '<h3>' . wp_kses_post(__('Ansar Import - This plugin requires Official <a href="https://themeansar.com/">Theme Ansar</a> Theme to be activated to work.', 'ansar-import')) . '</h3>';

    //Adding @ before will prevent XDebug output
    @trigger_error(esc_html__('Ansar Import - This plugin requires Official Theme Ansar Theme to be activated to work.', 'ansar-import'), E_USER_ERROR);
    wp_die();
}
?>

<div class="wrap">
    <div class="ansar-admin-header">
        <div class="ansar-admin-header-logo">
            <a href="#" class="ansar-logo"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ) . 'image/logo.png'); ?>" alt=""></a>
        </div>
        <div class="ansar-admin-right-area">
            <div class="ansar-version">
                <span><?php echo ANSAR_IMPORT_VERSION; ?></span>
            </div>
            <div class="ansar-doc">
                <a href="<?php echo esc_url($current_doc_url); ?>" target="_blank" tooltip="Docs">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none" style="&#10;    stroke: #505c66;&#10;">
                            <path opacity="0.5" d="M3 10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H13C16.7712 2 18.6569 2 19.8284 3.17157C21 4.34315 21 6.22876 21 10V14C21 17.7712 21 19.6569 19.8284 20.8284C18.6569 22 16.7712 22 13 22H11C7.22876 22 5.34315 22 4.17157 20.8284C3 19.6569 3 17.7712 3 14V10Z" stroke="#1C274C" stroke-width="1.5" style="&#10;    opacity: 1;&#10;    stroke: #505c66;&#10;"/>
                            <path d="M8 12H16" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" style="&#10;    stroke: #505c66;&#10;"/>
                            <path d="M8 8H16" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" style="stroke: #505c66;"/>
                            <path d="M8 16H13" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" style="stroke: #505c66;"/>
                        </svg>
                    </span>
                </a>
            </div>
            <div class="ansar-feature-pro">
                <a href="<?php echo esc_url($current_pro_url); ?>" target="_blank" title="Upgrade to Pro">
                    <span class="ansar-pro-icon"><svg
                            xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24"
                            fill="none" style="fill: #fff;">
                            <path
                                d="M19.6872 14.0931L19.8706 12.3884C19.9684 11.4789 20.033 10.8783 19.9823 10.4999L20 10.5C20.8284 10.5 21.5 9.82843 21.5 9C21.5 8.17157 20.8284 7.5 20 7.5C19.1716 7.5 18.5 8.17157 18.5 9C18.5 9.37466 18.6374 9.71724 18.8645 9.98013C18.5384 10.1814 18.1122 10.606 17.4705 11.2451L17.4705 11.2451C16.9762 11.7375 16.729 11.9837 16.4533 12.0219C16.3005 12.043 16.1449 12.0213 16.0038 11.9592C15.7492 11.847 15.5794 11.5427 15.2399 10.934L13.4505 7.7254C13.241 7.34987 13.0657 7.03557 12.9077 6.78265C13.556 6.45187 14 5.77778 14 5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5C10 5.77778 10.444 6.45187 11.0923 6.78265C10.9343 7.03559 10.759 7.34984 10.5495 7.7254L8.76006 10.934C8.42056 11.5427 8.25081 11.847 7.99621 11.9592C7.85514 12.0213 7.69947 12.043 7.5467 12.0219C7.27097 11.9837 7.02381 11.7375 6.5295 11.2451C5.88787 10.606 5.46156 10.1814 5.13553 9.98012C5.36264 9.71724 5.5 9.37466 5.5 9C5.5 8.17157 4.82843 7.5 4 7.5C3.17157 7.5 2.5 8.17157 2.5 9C2.5 9.82843 3.17157 10.5 4 10.5L4.01771 10.4999C3.96702 10.8783 4.03162 11.4789 4.12945 12.3884L4.3128 14.0931C4.41458 15.0393 4.49921 15.9396 4.60287 16.75H19.3971C19.5008 15.9396 19.5854 15.0393 19.6872 14.0931Z"
                                fill="#1C274C" style="&#10;    fill: #fff;&#10;" />
                            <path
                                d="M10.9121 21H13.0879C15.9239 21 17.3418 21 18.2879 20.1532C18.7009 19.7835 18.9623 19.1172 19.151 18.25H4.84896C5.03765 19.1172 5.29913 19.7835 5.71208 20.1532C6.65817 21 8.07613 21 10.9121 21Z"
                                fill="#1C274C" style="&#10;    fill: #fff;&#10;" />
                        </svg>
                    </span>
                    <span class="ansar-pro-title"><?php esc_html_e( 'Upgrade to Pro', 'ansar-import' ); ?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="ansar-import-wrap">
    <h1 class="ansar-heading"><?php esc_html_e('Ansar Import - One Click Demo Import', 'ansar-import') ?></h1>
    <p class="ansar-desc"><?php esc_html_e('Just Click a Import button and Install a Demo', 'ansar-import') ?></p>
    </div>
</h1>




    <!-- This file should primarily consist of HTML with a little bit of PHP. -->
    <?php
    $cat_data = wp_remote_get(esc_url_raw('https://api.themeansar.com/wp-json/wp/v2/categories?_fields=id,name,slug&post_type=demos&per_page=50&exclude=1'), [ 'timeout' => 15 ]);
    $cat_data_body = wp_remote_retrieve_body($cat_data);
    $all_categories = json_decode($cat_data_body, TRUE);


    //print_r($all_demos);
    $theme_data = wp_get_theme();
    $theme_name = $theme_data->get('Name');
    $theme_slug = $theme_data->get('TextDomain');
    
    $theme_data_api = wp_remote_get(esc_url_raw("https://api.themeansar.com/wp-json/wp/v2/demos/?orderby=menu_order&order=asc&search=%27" . urlencode($theme_name) . "%27&per_page=50"), [ 'timeout' => 15 ]);

    $theme_data_api_body = wp_remote_retrieve_body($theme_data_api);
    $all_demos = json_decode($theme_data_api_body, TRUE);

    $present_cat = array();
    $present_cat = array_values(array_unique($present_cat));

    if (count($all_demos) == 0) {
        wp_die('This theme is not supported yet!');
    }

    //print_r($theme_data_api);
    foreach ($all_demos as $demo) {
        foreach ($demo['categories'] as $in_cat) {
            // echo $in_cat.'<br>';
            array_push($present_cat, $in_cat['id']);
        }
    }

    $present_cat = array_values(array_unique($present_cat));
    ?>

    <hr class="wp-header-end">
    <div class="theme-browser rendered demo-ansar-container">
        <div class="themes wp-clearfix">
            <!-- Filter Controls -->
            <div uk-filter="target: .js-filter">

                <ul class="uk-subnav uk-subnav-pill">
                    <li class="uk-active" uk-filter-control><a href="#">All</a></li>
                    <?php
                    foreach ($all_categories as $category) {

                        if (in_array($category['id'], $present_cat)) {
                            ?>
                            <li uk-filter-control="[data-color*='cat_<?php echo esc_html($category['id']); ?>']"><a href="#"><?php echo esc_attr($category['name']); ?></a></li>

                            <?php
                        }
                    }
                    ?>


                </ul>

                <div class="js-filter grid-wrap">
                    <?php
                    //  print_r($all_demos);
                    foreach ($all_demos as $demo) {  $c = 0; ?>
                        <div class="ansar-inner-box" data-color="<?php
                        foreach ($demo['categories'] as $in_cat) {
                            echo "cat_" . esc_attr($in_cat['id']) . " ";
                        }
                        ?>">
                            <!-- product -->
                            <div class="uk-card theme" style="width: 100%;" tabindex="0">
                                <div class="theme-screenshot">
                                    <?php if ($theme_name != $demo['theme_name']) { ?>
                                        <span class="ribbon pro">
                                            <?php esc_html_e('Pro','ansar-import'); ?>
                                        </span>
                                        <?php } else { ?>
                                        <span class="ribbon">
                                            <?php esc_html_e('Free','ansar-import'); ?>
                                        </span>
                                    <?php } ?>
                                    <img src="<?php echo esc_url($demo['preview_url']); ?>" >
                                </div>
                                <span class="more-details btn-preview" data-id="<?php echo absint($demo['id']); ?>" data-live="<?php  if(get_option( 'ansar_demo_installed' )== $demo['id']){ echo 1; }?>" data-toggle="modal" data-target="#AnsardemoPreview"><?php esc_html_e('Preview','ansar-import'); ?></span>
                                <div class="theme-author"><?php esc_html_e('By Themeansar','ansar-import'); ?> </div>
                                <div class="theme-id-container">
                                    <div class="theme-names-about">
                                        <h2 class="theme-name" id=""><?php echo esc_attr($demo['title']['rendered']); ?></h2>
                                        <?php $lastcat = end($demo['categories']);
                                            foreach ($demo['categories'] as $in_cat) {
                                            if($c == 0){
                                                echo '<ul class="theme-cate">';
                                                $c++;
                                            }
                                            echo '<li class="theme-cate-item">'.$in_cat['name'].'</li>';
                                            
                                            if ($in_cat === $lastcat) {
                                                echo "</ul>";
                                            }
                                        } ?>
                                    </div>
                                    <div class="theme-actions">
                                        <?php if ($theme_name != $demo['theme_name']) {
                                            ?>
                                            <a class="button activate" target="_new" href="<?php echo esc_url($demo['pro_link']); ?>" >
                                                <?php esc_html_e('Buy Now','ansar-import'); ?></a>
                                        <?php } else {
              
                                            ?>
                                            <a class="button activate live-btn-<?php echo absint($demo['id']); ?> <?php  if(get_option( 'ansar_demo_installed' )!= $demo['id']){ echo "uk-hidden"; }?> " target="_new" data-id="<?php echo absint($demo['id']); ?>"  href="<?php echo esc_url(home_url()); ?>">Live Preview</a>
                                            <button type="button" class="<?php  if(get_option( 'ansar_demo_installed' )== $demo['id']){ echo "uk-hidden"; }?> button activate btn-import btn-import-<?php echo absint($demo['id']); ?>" href="#" data-id="<?php echo absint($demo['id']); ?>"><?php esc_html_e('Import','ansar-import'); ?></button>
                                            <?php }  ?>
                                        <a class="button button-primary load-customize hide-if-no-customize btn-preview" data-id="<?php echo absint($demo['id']); ?>" data-toggle="modal" data-target="#AnsardemoPreview" href="#"><?php esc_html_e('Preview','ansar-import'); ?></a>

                                    </div>
                                </div>    
                            </div>
                            <!-- /product -->
                        </div>

                        <?php
                    }
                    ?>


                </ul>

            </div>



            <!-- /product -->


        </div>
    </div>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->




<!-- Modal -->
<div id="AnsardemoPreview" tabindex="-1" class="uk-modal-full" uk-modal>
    <!-- main include -->   
    <div class="theme-install-overlay wp-full-overlay expanded iframe-ready" style="display: block;">
        <div class="wp-full-overlay-sidebar">
            
            <div class="wp-full-overlay-header">
                <button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e('Close', 'ansar-import'); ?></span></button>
                <a class="button activate preview-live-btn uk-hidden" target="_new"  href="<?php echo esc_url(home_url()); ?>"> <?php esc_html_e('Live Preview','ansar-import'); ?></a>
                <button type="button" class="button button-primary import-priview activate btn-import" href="#" data-id="0"><?php esc_html_e('Import', 'ansar-import'); ?></button>
                <a class="button activate preview-buy uk-hidden" target="_new" href="#" ><?php esc_html_e('Buy Now', 'ansar-import'); ?></a>
            </div>

            <div class="wp-full-overlay-sidebar-content">
                <div class="install-theme-info">
                    <h3 class="theme-name"> <?php echo esc_html($theme_data->get('Name')); ?> </h3>
                    <span class="theme-by"><?php esc_html_e('By', 'ansar-import'); ?> <?php echo esc_attr($theme_data->get('Author')); ?> </span>
                    <img class="theme-screenshot" src="" alt="">
                    <div class="theme-details">
                        <div class="theme-version"><?php echo esc_html($theme_data->get('Version')); ?></div>
                        <div class="theme-description"><?php echo esc_html($theme_data->get('Description')); ?></div>
                    </div>
                </div>
            </div>
            <div class="wp-full-overlay-footer">

                <button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="Collapse Sidebar">
                    <span class="collapse-sidebar-arrow"></span>
                    <span class="collapse-sidebar-label"><?php esc_html_e('Collapse', 'ansar-import'); ?></span>
                </button>
                <div class="devices-wrapper">
                    <div class="devices">
                        <button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
                            <span class="screen-reader-text"><?php esc_html_e('Enter desktop preview mode', 'ansar-import'); ?><?php esc_html_e('Collapse', 'ansar-import'); ?></span>
                        </button>
                        <button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
                            <span class="screen-reader-text"><?php esc_html_e('Enter tablet preview mode', 'ansar-import'); ?></span>
                        </button>
                        <button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
                            <span class="screen-reader-text"><?php esc_html_e('Enter mobile preview mode', 'ansar-import'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="wp-full-overlay-main">
            <iframe id="theme_preview" title="Preview"></iframe> 
        </div>
    </div>
    <!-- main include -->   

</div>
<!-- Modal preview  End -->

<div id="ImportConfirm" class="ansar-modal" style="display: none;">
    <div class="ansar-modal-dialog ansar-import-options" id="ansar-import-options">
        <button class="ansar-modal-close-default" type="button" id="closeConfirm">&times;</button>
        
        <div class="ansar-modal-header">
            <h2 class="ansar-modal-title"><?php esc_html_e('Confirmation', 'ansar-import'); ?></h2>
        </div>

        <div class="ansar-modal-body">
            <div class="demo-import-confirm-message"><?php echo sprintf('Importing demo data will ensure that your site will look similar as theme demo. It makes you easy to modify the content instead of creating them from scratch. Also, consider before importing the demo: <ol><li>Importing the demo on the site if you have already added the content is highly discouraged.</li> <li>You need to import demo on fresh WordPress install to exactly replicate the theme demo.</li> <li>It will install the required plugins as well as activate them for installing the required theme demo within your site.</li> <li>Copyright images will get replaced with other placeholder images.</li> <li>None of the posts, pages, attachments or any other data already existing in your site will be deleted or modified.</li> <li>It will take some time to import the theme demo.</li></ol>', 'ansar-import'); ?></div>
        </div>

        <ul class="import-option-list">
            <li class="active">
                <input class="ansar-checkbox" type="checkbox" id="import-customizer" name="import-customizer" checked="checked">
                <label for="import-customizer"><?php esc_html_e('Import Customize Settings', 'ansar-import'); ?></label>
            </li>
            <li class="active">
                <input class="ansar-checkbox" type="checkbox" id="import-widgets" name="import-widgets" checked="checked">
                <label for="import-widgets"><?php esc_html_e('Import Widgets', 'ansar-import'); ?></label>
            </li>
            <li>
                <input class="ansar-checkbox" type="checkbox" id="import-content" name="import-content" checked="checked">
                <label for="import-content"><?php esc_html_e('Import Content', 'ansar-import'); ?></label>
            </li>
        </ul>

        <div class="ansar-modal-footer">
            <form method="post" class="import">
                <input type="hidden" name="theme_id" id="theme_id" value="0">
                <?php wp_nonce_field('ansar_demo_import_nonce'); ?>
                <button type="button" class="ansar-button ansar-button-default" id="cancelModal"><?php esc_html_e('Close', 'ansar-import'); ?></button>
                <button type="button" class="ansar-button ansar-button-primary" id="import_data" ><?php esc_html_e('Confirm', 'ansar-import'); ?></button>
            </form>
        </div>

    </div>
    <div class="ansar-modal-dialog ansar-importing" id="ansar-importing" style="display: none;">
        <div class="ansar-intall-importer">
            <div class="inner">
                <div class="heading">
                    <h4 class="title">Importing Demo Data</h4>
                </div>  
                <div class="ansar-import-menu" id="ansar_import_menu">
                    <div class="progress-tooltip">
                        <span class="progress-tooltip-info" style="left: 0%;">0%</span>
                        <progress class="progress" value="0" max="100">0%</progress>
                    </div>
                    <ul class="ansar-import-tabs" id="ansar_import_tabs">
                        <li class="tab_disabled dashicons" id="demo_file_step">
                            <a href="#">Checking Theme Data Files</a>
                        </li>
                        <li class="tab_disabled dashicons" id="demo_import_step">
                            <a href="#">Importing Theme Data </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="ansar-modal-dialog ansar-import-complete" id="ansar-import-complete"  style="display: none;" >
        <div class="ansar-intall-importer imported">
            <button class="ansar-modal-close-default" type="button" id="importDoneClose">&times;</button>
            <div class="inner">
                <div class="succes-icon"></div>
                <div class="heading">
                    <h4 class="title">🎉 Import Complete Successfully</h4>
                    <p>Your site is now ready. Start exploring and customizing!</p>
                </div> 
                <div class="ansar-import-action">
                <a href="<?php echo esc_url(home_url()); ?>" target="_blank" class="ansar-button">visit your site</a>
                <a href="<?php echo esc_url(admin_url()); ?>" class="ansar-button no-bg">back to dashboard</a>
                </div>
            </div>
      </div>
    </div>
</div>