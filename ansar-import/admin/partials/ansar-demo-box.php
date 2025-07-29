<div class="ansar-inner-box" data-color="<?php
        foreach ($demo['categories'] as $in_cat) {
            echo "cat_" . esc_attr($in_cat['id']) . " ";
        }
    ?>">
    <!-- product -->
    <div class="uk-card theme" style="width: 100%;" tabindex="0">
        <div class="theme-screenshot">
            <?php if ((strpos($demo['theme_name'], 'pro') !== false) || (strpos($demo['theme_name'], 'Pro') !== false) || (strpos($demo['theme_name'], 'PRO') !== false)) { ?>
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
        <a href="<?php echo esc_url($demo['preview_link']); ?>" target="_blank">
            <span class="more-details" data-id="<?php echo absint($demo['id']); ?>"><?php esc_html_e('Preview','ansar-import'); ?></span>
        </a>
        <div class="theme-author"><?php esc_html_e('By Themeansar','ansar-import'); ?> </div>
        <div class="theme-id-container">
            <div class="theme-names-about">
                <h2 class="theme-name" id=""><?php echo esc_attr($demo['title']['rendered']); ?></h2>
                <?php $lastcat = end($demo['categories']);
                    $c = 0;
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

                <?php if ((strpos($demo['theme_name'], 'pro') !== false) || (strpos($demo['theme_name'], 'Pro') !== false) || (strpos($demo['theme_name'], 'PRO') !== false)) { ?>
                    <a class="button activate" target="_new" href="<?php echo esc_url($demo['pro_link']); ?>" >
                        <?php esc_html_e('Buy Now','ansar-import'); ?>
                    </a>
                <?php } else { ?>
                    <a class="button activate live-btn-<?php echo absint($demo['id']); ?> uk-hidden " target="_new" data-id="<?php echo absint($demo['id']); ?>"  href="<?php echo esc_url(home_url()); ?>">Live Preview</a>
                    <button type="button" class="button activate btn-import btn-import-<?php echo absint($demo['id']); ?>" href="#" data-id="<?php echo absint($demo['id']); ?>" tname="<?php echo esc_attr(strtolower(str_replace(' ', '-', $demo['theme_name']))); ?>">
                        <?php esc_html_e('Import','ansar-import'); ?>
                    </button>
                <?php }  ?>
                <a class="button button-primary load-customize hide-if-no-customize" href="<?php echo esc_url($demo['preview_link']); ?>" target="_blank">
                    <?php esc_html_e('Preview','ansar-import'); ?>
                </a>

            </div>
        </div>    
    </div>
    <!-- /product -->
</div>