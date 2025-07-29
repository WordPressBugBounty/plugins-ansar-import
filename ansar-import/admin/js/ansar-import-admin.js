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
        if (jQuery('#AnsardemoPreview').length) {
            UIkit.modal('#AnsardemoPreview').hide();
        }
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
        const closeBtn2 = document.getElementById('importDoneClose');
        const cancelBtn = document.getElementById('cancelModal');
        
        document.querySelectorAll('.btn-import').forEach(function(el) {
            el.addEventListener('click', function() {
                modal.style.display = 'flex';
                document.getElementById('ansar-import-options').removeAttribute('style');
            });
        });
        // openBtn.addEventListener('click', () => {
        //     modal.style.display = 'flex';
        // });
    
        [closeBtn, closeBtn2, cancelBtn].forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
                document.getElementById('ansar-import-complete').style.display = 'none';
            });
        });
    
        // // Close when clicking outside modal content
        // modal.addEventListener('click', (e) => {
        //     if (e.target === modal) {
        //         modal.style.display = 'none';
        //     }
        // });
    
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
        // jQuery('#ImportConfirm').hide();
        // jQuery('.btn-import-' + theme_id).addClass('updating-message');
        // jQuery('.btn-import-' + theme_id).html("Importing...");
        var customize = jQuery(this).closest(".ansar-modal-dialog").find('.import-option-list #import-customizer').prop("checked");
        var widget = jQuery(this).closest(".ansar-modal-dialog").find('.import-option-list #import-widgets').prop("checked");
        var content = jQuery(this).closest(".ansar-modal-dialog").find('.import-option-list #import-content').prop("checked");
        var demo_data = {
            'action': 'import_action',
            'theme_id': theme_id,
            'customize': customize,
            'widget': widget,
            'content': content,
            'nonce': my_ajax_object.nonce
        };
        var data = {
            'theme_id': theme_id,
            'customize': customize,
            'widget': widget,
            'content': content,
            'nonce': my_ajax_object.nonce
        };

        if(jQuery("#theme_id").attr('tname')){
            demo_data.theme_name = jQuery("#theme_id").attr('tname');
            demo_data.progress = 'by_theme';
            install_required_theme(demo_data);
        }else{
            demo_data.progress = 'by_file';
            init_demo_data(demo_data);
        }

    });

    function install_required_theme(demo_data){
        data = {
            'action': demo_data.action,
            'theme_id': demo_data.theme_id,
            'customize': demo_data.customize,
            'widget': demo_data.widget,
            'content': demo_data.content,
            'theme_name': demo_data.theme_name,
            'check_import_nonce': my_ajax_object.nonce,
            'step': 'theme_init'
        }
        jQuery.ajax({
            type: "POST", 
            url: my_ajax_object.ajax_url, 
            data: data,
            beforeSend: function(){
                // jQuery("#import-step-data").html("Importing theme data files...");

                // jQuery("#progress-wrapper").removeClass('hide');
                    
                setTimeout(function(){ 
                    var target = 5; 
                    var current = 0;
                    var duration = 1500;

                    jQuery(".progress-tooltip-info").css('left', target + '%');
                    
                    let stepTime = duration / target;

                    let interval = setInterval(function () {
                        
                        jQuery(".progress-tooltip-info").text(current + '%');

                        if (current >= target) {
                            clearInterval(interval);
                        }

                        current++;
                    }, stepTime);

                    jQuery(".progress").attr('value', target);
                    jQuery(".progress").text(target + '%');
                }, 500);
                
                jQuery('.ansar-import-options').hide();
                jQuery('.ansar-importing').show();
                // // jQuery(".progress-value").animate({ width:'5%'});
                // // jQuery("#progress-percentage").html('5%');
                jQuery("#theme_step").addClass('tab_active');
            },
            success: function (response) {
                console.log(response);
                if(response.data.status == 'ok') {
                    
                    setTimeout(function(){ 
                        var target = 35;
                        var current = 5;
                        var duration = 1500;
                        
                        jQuery(".progress-tooltip-info").css('left', target + '%');
                        

                        let stepTime = duration / target;

                        let interval = setInterval(function () {
                            
                                jQuery(".progress-tooltip-info").text(current + '%');

                            if (current >= target) {
                                clearInterval(interval);
                            }

                            current++;
                        }, stepTime);

                        jQuery(".progress").attr('value', target);
                        jQuery(".progress").text(target + '%');
                        
                        $("#theme_step a").text('Required Theme Installed/Activated Successfully');
                        init_demo_data(demo_data);
                    }, 1500);
                } 
                // else {
                //     jQuery("#progress-error-wrapper").removeClass("hide");
                //     jQuery("#import-error-text").html(response.msg);
                // }
            }, 
            error: function (response) {
                console.log(response);
                alert("Something went wrong!, Contact Plugin Author For Further Help");
                console.log("Something went wrong! from : install_required_theme");
            }

        });
    }

    function init_demo_data(demo_data){
        data = {
            'action': demo_data.action,
            'theme_id': demo_data.theme_id,
            'customize': demo_data.customize,
            'widget': demo_data.widget,
            'content': demo_data.content,
            'check_import_nonce': my_ajax_object.nonce,
            'step': 'demo_data_init'
        }
        jQuery.ajax({
            type: "POST", 
            url: my_ajax_object.ajax_url, 
            data: data,
            beforeSend: function(){      
                jQuery('.ansar-import-options').hide();
                jQuery('.ansar-importing').show();
                jQuery("#theme_step").removeClass('tab_active').addClass('tab_inactive');
                jQuery("#demo_file_step").addClass('tab_active');   
                if(demo_data.progress == 'by_file'){
                    setTimeout(function(){ 
                        var target = 15; 
                        var current = 0;
                        var duration = 1500;

                        jQuery(".progress-tooltip-info").css('left', target + '%');
                        
                        let stepTime = duration / target;

                        let interval = setInterval(function () {
                            
                            jQuery(".progress-tooltip-info").text(current + '%');

                            if (current >= target) {
                                clearInterval(interval);
                            }

                            current++;
                        }, stepTime);

                        jQuery(".progress").attr('value', target);
                        jQuery(".progress").text(target + '%');
                    }, 500);
                }
            },
            success: function (response) {
                console.log(response);
                if(response.data.status == 'ok') {
                    if(demo_data.progress == 'by_file'){
                        var target = 60;
                        var current = 15;
                    }else{
                        var target = 65;
                        var current = 35;
                    }
                    var duration = 1500;
                            
                    jQuery(".progress-tooltip-info").css('left', target + '%');

                    let stepTime = duration / target;

                    let interval = setInterval(function () {
                        
                            jQuery(".progress-tooltip-info").text(current + '%');

                        if (current >= target) {
                            clearInterval(interval);
                        }

                        current++;
                    }, stepTime);

                    jQuery(".progress").attr('value', target);
                    jQuery(".progress").text(target + '%');
                    
                    setTimeout(function(){ 
                        import_demo_data(demo_data);
                    }, 100);
                    $("#demo_file_step a").text('Import Data Initialized');
                } 
                // else {
                //     jQuery("#progress-error-wrapper").removeClass("hide");
                //     jQuery("#import-error-text").html(response.msg);
                // }
            }, 
            error: function (response) {
                console.log(response);
                alert("Something went wrong!, Contact Plugin Author For Further Help");
                console.log("Something went wrong! from : init_demo_data");
            }

        });
    }

    function import_demo_data(demo_data){
        data = {
            'action': demo_data.action,
            'theme_id': demo_data.theme_id,
            'customize': demo_data.customize,
            'widget': demo_data.widget,
            'content': demo_data.content,
            'check_import_nonce': my_ajax_object.nonce,
            'step': 'demo_data_import'
        }
        jQuery.ajax({
            type: "POST", 
            url: my_ajax_object.ajax_url, 
            data: data,
            beforeSend: function(){            
                jQuery("#demo_file_step").removeClass('tab_active').addClass('tab_inactive');
                jQuery("#demo_import_step").addClass('tab_active');           
            },
            success: function (response) {
                setTimeout(function(){ 
                    if(demo_data.progress == 'by_file'){
                        var target = 100;
                        var current = 60;
                    }else{
                        var target = 100;
                        var current = 65;
                    }
                    var duration = 1500;

                    jQuery(".progress-tooltip-info").css('left',  target + '%');

                    let stepTime = duration / target;

                    let interval = setInterval(function () {
                        
                            jQuery(".progress-tooltip-info").text(current + '%');

                        if (current >= target) {
                            clearInterval(interval);
                        }

                        current++;
                    }, stepTime);

                    jQuery(".progress").attr('value', target);
                    jQuery(".progress").text( target + '%');
                }, 100);
                jQuery("#demo_import_step").removeClass('tab_active').addClass('tab_inactive');
                setTimeout(function(){ 
                    jQuery(".progress-tooltip-info").css('left', '0%');
                    jQuery(".progress-tooltip-info").text('0%');
                    jQuery(".progress").attr('value', '0');
                    jQuery(".progress").text('0%');
                    jQuery("#theme_step").removeClass('tab_inactive');
                    jQuery("#demo_file_step").removeClass('tab_inactive');
                    jQuery("#demo_import_step").removeClass('tab_inactive');
                    jQuery('.ansar-importing').hide();
                    jQuery('.ansar-import-complete').show();
                }, 2500);
            }, 
            error: function (response) {
                console.log(response);
                alert("Something went wrong!, Contact Plugin Author For Further Help");
                console.log("Something went wrong! from : import_demo_data");
            }

        });
    }
    
    var loop = true;  
    var currentPage = 1;
    jQuery(window).scroll(function() {
        var targetElement = jQuery('#ansar-infinity-load');  
        if(targetElement.length === 1){
            var distanceToElement = targetElement.offset().top - jQuery(window).scrollTop();
            if (distanceToElement <= 2200 && loop == true) {
                loop = false;
                currentPage++;
                loadMorePosts(currentPage);
            }
        }
    });

    // Function to infinity load more posts via AJAX
    function loadMorePosts(page) {
        let seed = jQuery('#ansar-infinity-load').attr('seed');
        var data = {
            action: 'infinity_load_demos',
            paged: page,
            seed: seed,
            'check_import_nonce': my_ajax_object.nonce,
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
                        // UIkit.modal('#Confirm').show();
                        $('#ImportConfirm').css('display', 'flex');
                        $('#ansar-import-options').removeAttr('style');

                        if(jQuery('.theme').hasClass("focus")){
                            jQuery('.theme').removeClass("focus");
                        }
                        jQuery(this).closest('.theme').addClass("focus");
                
                    });
                }else{
                    jQuery('#ansar-infinity-load').parent().remove();
                }
                
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