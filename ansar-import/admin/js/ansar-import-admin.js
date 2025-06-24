(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

})(jQuery);
jQuery(document).ready(function ($) {

    // Product Show Method
    jQuery("#product_filter a").click(function (e) {
        e.preventDefault();
        jQuery(this).tab('show');
    });

    // Preview Button 
    jQuery(".btn-preview").click(function () {
        jQuery(".preview-live-btn").addClass('uk-hidden');
        jQuery(".import-priview").removeClass('uk-hidden');
        for (var i = 0; i < ansar_theme_object.length; i++) {
            if (ansar_theme_object[i].id === jQuery(this).data('id')) {
                //console.log(ansar_theme_object[i]);
                jQuery("#theme_preview").attr('src', '');
                jQuery("#theme_preview").attr('src', ansar_theme_object[i].preview_link);
                jQuery(".theme-screenshot").attr('src', ansar_theme_object[i].preview_url);
                //  alert(my_ajax_object.theme_name +'->'+ ansar_theme_object[i].theme_name);
                if (my_ajax_object.theme_name === ansar_theme_object[i].theme_name) {
                    jQuery(".import-priview").attr('data-id', jQuery(this).data('id'));
                    jQuery(".import-priview").removeClass('uk-hidden');
                    jQuery(".preview-buy").addClass('uk-hidden');
                } else {
                    jQuery(".import-priview").addClass('uk-hidden');
                    jQuery(".preview-buy").removeClass('uk-hidden');
                    jQuery(".preview-buy").attr('src', ansar_theme_object[i].pro_link);
                }
            }
        }
        if (jQuery(this).data('live') === 1) {
            jQuery(".import-priview").addClass('uk-hidden');
            jQuery(".preview-live-btn").removeClass('uk-hidden');
        }
        UIkit.modal('#AnsardemoPreview').show();
    });

    jQuery(".preview-desktop").click(function ($) {
        jQuery(".wp-full-overlay-main").removeClass('p-mobile');
        jQuery(".wp-full-overlay-main").removeClass('p-tablet');
    });
    jQuery(".preview-tablet").click(function ($) {
        jQuery(".wp-full-overlay-main").addClass('p-tablet');
        jQuery(".wp-full-overlay-main").removeClass('p-mobile');
    });
    jQuery(".preview-mobile").click(function ($) {
        jQuery(".wp-full-overlay-main").addClass('p-mobile');
        jQuery(".wp-full-overlay-main").removeClass('p-tablet');
    });

    jQuery(".collapse-sidebar").click(function ($) {
        var x = jQuery(this).attr("aria-expanded");
        if (x === "true"){
            jQuery(this).attr("aria-expanded", "false");
            jQuery(".theme-install-overlay").addClass('expanded').removeClass('collapsed');
        } else {
            jQuery(this).attr("aria-expanded", "true");
            jQuery(".theme-install-overlay").addClass('collapsed').removeClass('expanded');
        }
    });


    jQuery(".close-full-overlay").click(function () {

        UIkit.modal('#AnsardemoPreview').hide();
        jQuery("#theme_preview").attr('src', '');
        jQuery(".theme-screenshot").attr('src', '');

    });

    jQuery(".btn-import").click(function () {
        jQuery("#theme_id").val(jQuery(this).data('id'));
        jQuery("#theme_id").attr('tname',jQuery(this).attr('tname'));
        UIkit.modal('#AnsardemoPreview').hide();
        // UIkit.modal('#Confirm').show();
        if(jQuery('.theme').hasClass("focus")){
            jQuery('.theme').removeClass("focus");
        }
        jQuery(this).closest('.theme').addClass("focus");

    });

    // jQuery(".uk-modal-close").click(function () {
    //     if(jQuery('.theme').hasClass("focus")){
    //         jQuery('.theme').removeClass("focus");
    //     }
    // });

    function ansar_model(){
        const modal = document.getElementById('ImportConfirm');
        const openBtn = document.querySelectorAll('.btn-import');
        const closeBtn = document.getElementById('closeConfirm');
        const cancelBtn = document.getElementById('cancelModal');
        
        document.querySelectorAll('.btn-import').forEach(function(el) {
            el.addEventListener('click', function() {
                console.log('yes clicked');
                modal.style.display = 'flex';
            });
        });
        // openBtn.addEventListener('click', () => {
        //     modal.style.display = 'flex';
        // });
    
        [closeBtn, cancelBtn].forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        });
    
        // Close when clicking outside modal content
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    
        // Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                modal.style.display = 'none';
            }
        });
    }
    ansar_model();


    jQuery("#import_data").on("click", function ($) {
        var curr_btn = jQuery(this);
        var theme_id = jQuery("#theme_id").val();
        jQuery('#ImportConfirm').hide();
        jQuery('.btn-import-' + theme_id).addClass('updating-message');
        jQuery('.btn-import-' + theme_id).html("Importing...");
        var customize = jQuery(this).closest(".uk-modal-dialog").find('.import-option-list #import-customizer').prop("checked");
        var widget = jQuery(this).closest(".uk-modal-dialog").find('.import-option-list #import-widgets').prop("checked");
        var content = jQuery(this).closest(".uk-modal-dialog").find('.import-option-list #import-content').prop("checked");
        var data = {
            'action': 'import_action',
            'theme_id': theme_id,
            'customize': customize,
            'widget': widget,
            'content': content,
            'nonce': my_ajax_object.nonce
        };

        if(jQuery("#theme_id").attr('tname')){
            data.theme_name = jQuery("#theme_id").attr('tname');
            console.log(data.theme_name)
        }


        jQuery.ajax({
            type: "POST",
            url: my_ajax_object.ajax_url,
            data: data,
            success: function (data) {
                console.log(data);
                // jQuery(".demo-ansar-container").hide();
                jQuery('.btn-import-' + theme_id).addClass("uk-hidden");
                jQuery('.live-btn-' + theme_id).removeClass("uk-hidden");
                jQuery('#import_data').closest(".uk-modal-dialog").find('.import-option-list #import-content').removeAttr("checked");

            },
            error: function (data) {
                // alert(data);
                alert('Oops! Something went wrong. The demo data could not be imported. Please try again or check your internet connection.');
                console.log(data);
            }

        });
        return false;

    });
    
    var loop = true;  
    let currentPage = 1;
    jQuery(window).scroll(function() {
        var targetElement = jQuery('#ans-infinity-load');  
        if(targetElement.length === 1){
            var distanceToElement = targetElement.offset().top - jQuery(window).scrollTop();
            if (distanceToElement <= 2200 && loop == true) {
                console.log('yes scrolled');
                loop = false;
                currentPage++;
                loadMorePosts(currentPage);
            }
        }
    });

    // Function to infinity load more posts via AJAX
    function loadMorePosts(page) {
        let seed = jQuery('#ans-infinity-load').attr('seed');
        // console.log(type);
        var data = {
            action: 'infinity_load_demos',
            paged: page,
            seed: seed,
        };

        var i = 1;
        jQuery.ajax({
            type: 'POST',
            url: my_ajax_object.ajax_url,
            dataType: 'json',
            data: data,
            success: function (response) {
                if(response['html'] !== "No demos found"){
                    jQuery('.grid-wrap').append(response['html']);
                    loop = true;
                    jQuery(".btn-import").click(function () {
                        jQuery("#theme_id").val(jQuery(this).data('id'));
                        jQuery("#theme_id").attr('tname',jQuery(this).attr('tname'));
                        UIkit.modal('#AnsardemoPreview').hide();
                        // UIkit.modal('#Confirm').show();
                        $('#ImportConfirm').css('display', 'flex');

                        if(jQuery('.theme').hasClass("focus")){
                            jQuery('.theme').removeClass("focus");
                        }
                        jQuery(this).closest('.theme').addClass("focus");
                
                    });
                }else{
                    jQuery('#ans-infinity-load').parent().remove();
                }

                // jQuery('#ans-infinity-load').parent().remove();
                // var div = document.createElement("div");
                // var load_a = document.createElement("a");
                // div.setAttribute("id", "ans-ss");
                // load_a.setAttribute("id", "ans-infinity-load");
                // load_a.innerHTML = "<i class='dashicons dashicons-update spinning'></i>";

                // div.appendChild(load_a);
                // // console.log(div);
                // // console.log(response['html']);
                // if(type == 'gridContent'){
                //     jQuery('#grid').append(response['html']);
                //     if(response['due_post'] > 0){
                //         div.setAttribute("id", "gridContent");
                //         div.setAttribute("paged", response['paged']);
                //         if (typeof cat !== 'undefined') {
                //           div.setAttribute("cat_archive", cat);
                //         }else if (typeof tag !== 'undefined') {
                //           div.setAttribute("tag_archive", tag);
                //         }else if (typeof date !== 'undefined') {
                //           div.setAttribute("date_archive", date);
                //         }
                //         jQuery('#grid').append(div);
                //         loop = true;
                //     }else{
                //         if(i === 1){
                //             console.log('no more post left');
                //             var no_post_div = document.createElement("div");
                //             var no_post = document.createElement("p");
                //             no_post_div.setAttribute("class", "text-center");
                //             no_post.setAttribute("id", "ans-infinity-load-no-post");
                //             no_post.innerHTML = "No More Posts Left";
                //             no_post_div.appendChild(no_post);
                //             jQuery( '#grid').append(no_post_div);
                //             i++;
                //         }
                //     }
                // }else{
                //     jQuery( 'body' ).find('.align_cls').append(response['html']);
                //     if(response['due_post'] > 0){
                //         div.setAttribute("id", "alignContent");
                //         div.setAttribute("paged", response['paged']);
                //         if (typeof cat !== 'undefined') {
                //           div.setAttribute("cat_archive", cat);
                //         }else if (typeof tag !== 'undefined') {
                //           div.setAttribute("tag_archive", tag);
                //         }else if (typeof date !== 'undefined') {
                //           div.setAttribute("date_archive", date);
                //         }
                //         jQuery( 'body' ).find('.align_cls').append(div);
                //         loop = true;
                //     }else{
                //         if(i === 1){
                //             console.log('no more post left');
                //             var no_post_div = document.createElement("div");
                //             var no_post = document.createElement("p");
                //             no_post_div.setAttribute("class", "text-center");
                //             no_post.setAttribute("id", "ans-infinity-load-no-post");
                //             no_post.innerHTML = "No More Posts Left";
                //             no_post_div.appendChild(no_post);
                //             jQuery( 'body' ).find('.align_cls').append(no_post_div);
                //             i++;
                //         }
                //     }
                // }

                // let observer1 = lozad('.lozad', {
                //     threshold: 0.1, // ratio of element convergence
                //     enableAutoReload: true // it will reload the new image when validating attributes changes
                // });
                // observer1.observe();
                
            },
            complete: function() {
                // Sticksy.hardRefreshAll();
            },
            error: function(errorThrown){
                console.log(errorThrown);
            },
        });
    }

});