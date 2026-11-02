(function () {
    'use strict';

    /**
     * Pure Vanilla JS Admin Controller for Ansar Import
     * Zero jQuery / Zero UIkit dependency
     */

    // =========================================================================
    // Helper Utilities
    // =========================================================================

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function debounce(fn, delay) {
        var timer = null;
        return function () {
            var context = this;
            var args = arguments;
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function () {
                timer = null;
                fn.apply(context, args);
            }, delay);
        };
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Lightweight POST AJAX helper using native fetch + URLSearchParams
     */
    function postAjax(data, options) {
        options = options || {};
        var ajaxUrl = (typeof my_ajax_object !== 'undefined' && my_ajax_object.ajax_url)
            ? my_ajax_object.ajax_url
            : window.ajaxurl;

        if (!ajaxUrl) {
            return Promise.reject(new Error('AJAX URL is not defined.'));
        }

        var params = new URLSearchParams();
        Object.keys(data || {}).forEach(function (key) {
            if (data[key] !== undefined && data[key] !== null) {
                params.append(key, String(data[key]));
            }
        });

        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var timeoutId = null;

        if (controller && options.timeout && options.timeout > 0) {
            timeoutId = setTimeout(function () {
                controller.abort();
            }, options.timeout);
        }

        return window.fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: params.toString(),
            signal: controller ? controller.signal : undefined
        })
            .then(function (response) {
                return response.text().then(function (text) {
                    var parsed = null;
                    try {
                        parsed = text ? JSON.parse(text) : null;
                    } catch (e) {
                        parsed = null;
                    }
                    if (!response.ok) {
                        var err = new Error('HTTP ' + response.status);
                        err.responseJSON = parsed;
                        err.status = response.status;
                        throw err;
                    }
                    return parsed;
                });
            })
            .finally(function () {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
            });
    }

    // =========================================================================
    // Lightweight Modal Shim (Replaces UIkit.modal)
    // =========================================================================

    function getModalController(selectorOrEl) {
        var el = typeof selectorOrEl === 'string'
            ? document.querySelector(selectorOrEl)
            : selectorOrEl;

        return {
            show: function () {
                if (!el) return;
                el.classList.add('uk-open');
                el.style.display = 'block';
                el.removeAttribute('hidden');
            },
            hide: function () {
                if (!el) return;
                el.classList.remove('uk-open');
                el.style.display = 'none';
            }
        };
    }

    if (typeof window.UIkit === 'undefined') {
        window.UIkit = {
            modal: getModalController
        };
    } else if (typeof window.UIkit.modal !== 'function') {
        window.UIkit.modal = getModalController;
    }

    // =========================================================================
    // Main Initialization
    // =========================================================================

    onReady(function () {
        var ajaxObj = typeof my_ajax_object !== 'undefined' ? my_ajax_object : null;

        // Build O(1) lookup map for ansar_theme_object demos
        var demoMap = new Map();
        if (typeof ansar_theme_object !== 'undefined' && Array.isArray(ansar_theme_object)) {
            for (var i = 0; i < ansar_theme_object.length; i++) {
                var item = ansar_theme_object[i];
                if (item && item.id != null) {
                    demoMap.set(String(item.id), item);
                }
            }
        }

        // ---------------------------------------------------------------------
        // Editor Switcher State & Change Handler (Instant Client-Side Switching)
        // ---------------------------------------------------------------------
        var editorSwitcher = document.getElementById('ansar-editor-switcher');
        var activeEditorFilter = '';
        if (editorSwitcher && editorSwitcher.selectedIndex >= 0) {
            var initialEditorOpt = editorSwitcher.options[editorSwitcher.selectedIndex];
            activeEditorFilter = initialEditorOpt ? (initialEditorOpt.getAttribute('data-editor') || '') : '';
        }

        function switchActiveEditor(chosenEditor, shouldAnimate) {
            if (!chosenEditor) return;
            activeEditorFilter = chosenEditor;

            var iconHtml = '';
            if (editorSwitcher) {
                var switcherWrap = editorSwitcher.closest('.ansar-editor-switcher');
                if (switcherWrap) {
                    switcherWrap.setAttribute('data-active-editor', chosenEditor);
                    switcherWrap.classList.add('is-selected');
                }
                for (var idx = 0; idx < editorSwitcher.options.length; idx++) {
                    var opt = editorSwitcher.options[idx];
                    var isMatch = opt.getAttribute('data-editor') === chosenEditor;
                    opt.selected = isMatch;
                    if (isMatch) {
                        opt.setAttribute('selected', 'selected');
                        editorSwitcher.selectedIndex = idx;
                        iconHtml = opt.getAttribute('data-icon') || '';
                    } else {
                        opt.removeAttribute('selected');
                    }
                }
            }

            if (iconHtml) {
                document.querySelectorAll('.ansar-editor-switcher-icon, .ansar-empty-editor-state-icon').forEach(function (iconEl) {
                    iconEl.innerHTML = iconHtml;
                });
            }

            // Sync Refresh button URL with new active editor
            document.querySelectorAll('.ansar-editor-refresh').forEach(function (refreshEl) {
                var href = refreshEl.getAttribute('href');
                if (href) {
                    try {
                        var refreshUrl = new URL(href, window.location.origin);
                        refreshUrl.searchParams.set('editor', chosenEditor);
                        refreshEl.setAttribute('href', refreshUrl.toString());
                    } catch (e) {}
                }
            });

            // Sync Onboarding Modal builder cards state
            document.querySelectorAll('.ansar-builder-card').forEach(function (cardEl) {
                var cardEditor = cardEl.getAttribute('data-editor');
                var isSelected = cardEditor === chosenEditor;
                cardEl.classList.toggle('active-selected', isSelected);
                var btn = cardEl.querySelector('.ansar-btn-choose-builder');
                if (btn && !cardEl.classList.contains('ansar-builder-disabled')) {
                    btn.textContent = isSelected ? 'Selected' : 'Choose Builder';
                }
            });

            // Update URL without reloading
            if (window.history && window.history.replaceState) {
                try {
                    var nextUrl = new URL(window.location.href);
                    nextUrl.searchParams.set('editor', chosenEditor);
                    nextUrl.searchParams.delete('onboarding');
                    window.history.replaceState({}, document.title, nextUrl.toString());
                } catch (e) {}
            }

            // Persist preferred editor in user meta in the background
            if (ajaxObj) {
                postAjax({
                    action: 'ansar_save_preferred_editor',
                    editor: chosenEditor,
                    nonce: ajaxObj.nonce
                }).catch(function () {});
            }

            if (typeof performDemoFilter === 'function') {
                performDemoFilter(shouldAnimate !== false);
            }
        }

        if (editorSwitcher) {
            editorSwitcher.addEventListener('change', function () {
                var selected = this.options[this.selectedIndex];
                var chosenEditor = selected ? (selected.getAttribute('data-editor') || '') : '';
                if (chosenEditor) {
                    switchActiveEditor(chosenEditor, true);
                } else if (this.value) {
                    window.location.href = this.value;
                }
            });
        }

        // ---------------------------------------------------------------------
        // Step 1 Onboarding: Editor Selection Wizard Logic
        // ---------------------------------------------------------------------
        var onboardingModal = document.getElementById('AnsarEditorOnboarding');

        function cleanOnboardingParam() {
            if (window.history && window.history.replaceState) {
                var cleanUrl = new URL(window.location.href);
                if (cleanUrl.searchParams.has('onboarding')) {
                    cleanUrl.searchParams.delete('onboarding');
                    window.history.replaceState({}, document.title, cleanUrl.toString());
                }
            }
        }

        if (onboardingModal) {
            var urlParams = new URLSearchParams(window.location.search);
            var forceOnboarding = urlParams.get('onboarding') === '1' || urlParams.get('onboarding') === 'true';
            var hasExplicitEditorParam = urlParams.has('editor');

            if (forceOnboarding && !hasExplicitEditorParam) {
                onboardingModal.style.display = 'flex';
                cleanOnboardingParam();
            }
        }

        // ---------------------------------------------------------------------
        // Rotating Tips & Progress Bar Controller
        // ---------------------------------------------------------------------
        var ansarImportTips = [
            'You can customize site colors, fonts, and headers anytime under Appearance → Customize.',
            'All demo content is built to work seamlessly with Gutenberg Blocks or Elementor.',
            'Demo placeholder images can be easily replaced directly from your Media Library.',
            'You can configure header, sidebar, and footer widgets under Appearance → Widgets.',
            'Every starter site design is 100% mobile-responsive and optimized for speed.',
            'Need help? Comprehensive video tutorials and guides are available at docs.themeansar.com.'
        ];
        var ansarTipInterval = null;
        var ansarTipTimeouts = [];
        var ansarCurrentTipIndex = 0;
        var tipElement = document.getElementById('ansar-rotating-tip');

        function clearTipTimeouts() {
            while (ansarTipTimeouts.length) {
                clearTimeout(ansarTipTimeouts.pop());
            }
        }

        function startRotatingTips() {
            stopRotatingTips();
            ansarCurrentTipIndex = 0;
            if (!tipElement) {
                tipElement = document.getElementById('ansar-rotating-tip');
            }
            if (!tipElement) return;

            tipElement.classList.remove('fade-out', 'fade-in');
            tipElement.innerHTML = '<span>' + ansarImportTips[0] + '</span>';

            ansarTipInterval = setInterval(function () {
                if (!tipElement) return;
                tipElement.classList.add('fade-out');

                var t1 = setTimeout(function () {
                    ansarCurrentTipIndex = (ansarCurrentTipIndex + 1) % ansarImportTips.length;
                    tipElement.innerHTML = '<span>' + ansarImportTips[ansarCurrentTipIndex] + '</span>';
                    tipElement.classList.remove('fade-out');
                    tipElement.classList.add('fade-in');

                    var t2 = setTimeout(function () {
                        tipElement.classList.remove('fade-in');
                    }, 350);
                    ansarTipTimeouts.push(t2);
                }, 350);
                ansarTipTimeouts.push(t1);
            }, 4000);
        }

        function stopRotatingTips() {
            if (ansarTipInterval) {
                clearInterval(ansarTipInterval);
                ansarTipInterval = null;
            }
            clearTipTimeouts();
        }

        // Single unified progress animator (prevents overlapping setInterval races)
        var progressRafId = null;
        var progressTimeoutId = null;
        var currentProgressVal = 0;
        var progressTooltipNodes = null;
        var progressBarNodes = null;

        function getProgressNodes() {
            if (!progressTooltipNodes || !progressTooltipNodes.length) {
                progressTooltipNodes = document.querySelectorAll('.progress-tooltip-info');
            }
            if (!progressBarNodes || !progressBarNodes.length) {
                progressBarNodes = document.querySelectorAll('.progress');
            }
        }

        function stopProgressAnimation() {
            if (progressRafId) {
                cancelAnimationFrame(progressRafId);
                progressRafId = null;
            }
            if (progressTimeoutId) {
                clearTimeout(progressTimeoutId);
                progressTimeoutId = null;
            }
        }

        function setProgressImmediate(value) {
            stopProgressAnimation();
            currentProgressVal = value;
            getProgressNodes();
            var pct = value + '%';
            progressTooltipNodes.forEach(function (el) {
                el.style.left = pct;
                el.textContent = pct;
            });
            progressBarNodes.forEach(function (el) {
                el.setAttribute('value', String(value));
                el.value = value;
                el.textContent = pct;
            });
        }

        function animateProgress(from, target, duration, delayMs) {
            stopProgressAnimation();

            var run = function () {
                getProgressNodes();
                var startVal = typeof from === 'number' ? from : currentProgressVal;
                var targetPct = target + '%';

                progressTooltipNodes.forEach(function (el) {
                    el.style.left = targetPct;
                });
                progressBarNodes.forEach(function (el) {
                    el.setAttribute('value', String(target));
                    el.value = target;
                    el.textContent = targetPct;
                });

                var startTime = performance.now();
                var lastRenderedInt = -1;

                function tick(now) {
                    var elapsed = now - startTime;
                    var ratio = duration > 0 ? Math.min(1, elapsed / duration) : 1;
                    var currentInt = Math.round(startVal + (target - startVal) * ratio);

                    if (currentInt !== lastRenderedInt) {
                        lastRenderedInt = currentInt;
                        currentProgressVal = currentInt;
                        var text = currentInt + '%';
                        progressTooltipNodes.forEach(function (el) {
                            el.textContent = text;
                        });
                    }

                    if (ratio < 1) {
                        progressRafId = requestAnimationFrame(tick);
                    } else {
                        progressRafId = null;
                    }
                }

                progressRafId = requestAnimationFrame(tick);
            };

            if (delayMs && delayMs > 0) {
                progressTimeoutId = setTimeout(run, delayMs);
            } else {
                run();
            }
        }

        // ---------------------------------------------------------------------
        // Modal & Import Step Helpers
        // ---------------------------------------------------------------------
        var importConfirmModal = document.getElementById('ImportConfirm');
        var completeBox = document.getElementById('ansar-import-complete');
        var errorBox = document.getElementById('ansar-import-error');
        var importingBox = document.getElementById('ansar-importing');
        var optionsBox = document.getElementById('ansar-import-options');
        var themeIdInput = document.getElementById('theme_id');
        var themeStepEl = document.getElementById('theme_step');
        var pluginStepEl = document.getElementById('plugin_step');
        var demoFileStepEl = document.getElementById('demo_file_step');
        var demoImportStepEl = document.getElementById('demo_import_step');
        var currentRetryCallback = null;

        function setStepLinkText(stepEl, text) {
            if (!stepEl) return;
            var link = stepEl.querySelector('a');
            if (link) {
                link.textContent = text;
            }
        }

        function closeImportModal() {
            stopRotatingTips();
            stopProgressAnimation();
            if (importConfirmModal) importConfirmModal.style.display = 'none';
            if (completeBox) completeBox.style.display = 'none';
            if (errorBox) errorBox.style.display = 'none';
            if (importingBox) importingBox.style.display = 'none';
            if (optionsBox) optionsBox.style.display = 'block';
        }

        function showImportError(errorMessage, retryCallback) {
            stopRotatingTips();
            stopProgressAnimation();
            if (importingBox) importingBox.style.display = 'none';
            if (optionsBox) optionsBox.style.display = 'none';
            if (completeBox) completeBox.style.display = 'none';

            var defaultMsg = 'The server took longer than expected to process the demo files or a temporary network interruption occurred.';
            var displayMsg = (typeof errorMessage === 'string' && errorMessage.trim().length > 0)
                ? errorMessage
                : defaultMsg;

            var errorDesc = document.getElementById('ansar-error-desc');
            if (errorDesc) {
                errorDesc.textContent = displayMsg;
            }
            if (errorBox) {
                errorBox.style.display = 'block';
            }

            currentRetryCallback = typeof retryCallback === 'function' ? retryCallback : null;
        }

        function renderPluginBadges(plugins) {
            var listEl = document.getElementById('ansar-modal-plugins-list');
            if (!listEl) return;

            if (!plugins || !plugins.length) {
                var emptyMsg = (ajaxObj && ajaxObj.i18n && ajaxObj.i18n.no_plugins_needed)
                    ? ajaxObj.i18n.no_plugins_needed
                    : 'No extra plugins required for this demo.';
                listEl.innerHTML = '<span class="ansar-plugin-chip ansar-chip-ready"><span class="ansar-plugin-name">' +
                    escapeHtml(emptyMsg) +
                    '</span><span class="ansar-plugin-badge ansar-badge-active">Ready</span></span>';
                return;
            }

            var html = '';
            for (var i = 0; i < plugins.length; i++) {
                var item = plugins[i];
                var badgeClass = 'ansar-badge-installable';
                var badgeText = 'Will Install';

                if (item.status === 'active') {
                    badgeClass = 'ansar-badge-active';
                    badgeText = 'Active';
                } else if (item.status === 'inactive') {
                    badgeClass = 'ansar-badge-installed';
                    badgeText = 'Installed';
                } else if (item.plugin_type === 'pro') {
                    badgeClass = 'ansar-badge-pro';
                    badgeText = 'Pro Required';
                }

                html += '<span class="ansar-plugin-chip">' +
                    '<span class="ansar-plugin-name">' + escapeHtml(item.plugin_name || item.plugin_slug) + '</span>' +
                    '<span class="ansar-plugin-badge ' + badgeClass + '">' + badgeText + '</span>' +
                    '</span>';
            }
            listEl.innerHTML = html;
        }

        function loadModalRequiredPlugins(demoId) {
            var listEl = document.getElementById('ansar-modal-plugins-list');
            if (!listEl) return;

            var key = String(demoId);
            var matchedDemo = demoMap.get(key) || null;

            // Instant 0ms path: use pre-computed required_plugins from ansar_theme_object
            if (matchedDemo && Array.isArray(matchedDemo.required_plugins)) {
                renderPluginBadges(matchedDemo.required_plugins);
                return;
            }

            var loadingText = (ajaxObj && ajaxObj.i18n && ajaxObj.i18n.loading_plugins)
                ? ajaxObj.i18n.loading_plugins
                : 'Checking required plugins...';
            listEl.innerHTML = '<span class="ansar-plugin-loading-placeholder"><span class="dashicons dashicons-update spinning"></span><span>' +
                escapeHtml(loadingText) + '</span></span>';

            postAjax({
                action: 'ansar_get_demo_plugins_status',
                demo_id: demoId,
                demo_data: matchedDemo ? JSON.stringify(matchedDemo) : '',
                nonce: ajaxObj ? ajaxObj.nonce : ''
            })
                .then(function (res) {
                    if (res && res.success && res.data && Array.isArray(res.data.plugins)) {
                        if (matchedDemo) {
                            matchedDemo.required_plugins = res.data.plugins;
                        } else {
                            demoMap.set(key, { id: demoId, required_plugins: res.data.plugins });
                        }
                        renderPluginBadges(res.data.plugins);
                    } else {
                        renderPluginBadges([]);
                    }
                })
                .catch(function () {
                    renderPluginBadges([]);
                });
        }

        // ---------------------------------------------------------------------
        // Multi-Step Import Pipeline (Theme -> Plugins -> Data Init -> Data Import)
        // ---------------------------------------------------------------------
        function isRequiredThemeAlreadyActive(themeSlug) {
            if (!themeSlug || !ajaxObj) return false;
            var target = String(themeSlug).trim().toLowerCase();
            var activeSlug = ajaxObj.theme_slug ? String(ajaxObj.theme_slug).trim().toLowerCase() : '';
            var activeName = ajaxObj.theme_name ? String(ajaxObj.theme_name).trim().toLowerCase() : '';
            var parentSlug = ajaxObj.parent_slug ? String(ajaxObj.parent_slug).trim().toLowerCase() : '';
            var parentName = ajaxObj.parent_name ? String(ajaxObj.parent_name).trim().toLowerCase() : '';
            return target === activeSlug || target === activeName || (parentSlug && target === parentSlug) || (parentName && target === parentName);
        }

        function areRequiredPluginsAlreadyActive(themeId) {
            var matched = demoMap.get(String(themeId));
            if (!matched || !Array.isArray(matched.required_plugins)) {
                return false;
            }
            for (var i = 0; i < matched.required_plugins.length; i++) {
                if (matched.required_plugins[i].status !== 'active') {
                    return false;
                }
            }
            return true;
        }

        function markCachedPluginsActive(themeId) {
            var matched = demoMap.get(String(themeId));
            if (!matched || !Array.isArray(matched.required_plugins)) return;
            for (var i = 0; i < matched.required_plugins.length; i++) {
                matched.required_plugins[i].status = 'active';
            }
        }

        function installRequiredTheme(demoData) {
            if (optionsBox) optionsBox.style.display = 'none';
            if (importingBox) importingBox.style.display = 'block';
            startRotatingTips();

            // Fast path: skip theme_init AJAX roundtrip if required theme is already active
            if (isRequiredThemeAlreadyActive(demoData.theme_name)) {
                setStepLinkText(themeStepEl, 'Required Theme Installed/Activated Successfully');
                if (themeStepEl) {
                    themeStepEl.classList.remove('tab_active');
                    themeStepEl.classList.add('tab_inactive');
                }
                animateProgress(0, 25, 400);
                installRequiredPlugins(demoData);
                return;
            }

            if (themeStepEl) {
                themeStepEl.classList.add('tab_active');
            }
            animateProgress(0, 5, 800, 100);

            postAjax({
                action: demoData.action,
                theme_id: demoData.theme_id,
                customize: demoData.customize,
                widget: demoData.widget,
                content: demoData.content,
                theme_name: demoData.theme_name,
                check_import_nonce: ajaxObj ? ajaxObj.nonce : '',
                step: 'theme_init'
            })
                .then(function (response) {
                    if (response && response.success && response.data && response.data.status === 'ok') {
                        if (ajaxObj && demoData.theme_name) {
                            ajaxObj.theme_slug = String(demoData.theme_name).trim().toLowerCase();
                        }
                        animateProgress(5, 25, 800);
                        setStepLinkText(themeStepEl, 'Required Theme Installed/Activated Successfully');
                        if (themeStepEl) {
                            themeStepEl.classList.remove('tab_active');
                            themeStepEl.classList.add('tab_inactive');
                        }
                        installRequiredPlugins(demoData);
                    } else {
                        var errMsg = (response && response.data && response.data.msg)
                            ? response.data.msg
                            : (response && response.data ? response.data : 'Failed to install or activate required theme.');
                        showImportError(errMsg, function () {
                            installRequiredTheme(demoData);
                        });
                    }
                })
                .catch(function (err) {
                    var errDetail = (err && err.responseJSON && err.responseJSON.data)
                        ? err.responseJSON.data
                        : 'Server connection failed while installing theme. Please check your internet or retry.';
                    showImportError(errDetail, function () {
                        installRequiredTheme(demoData);
                    });
                });
        }

        function installRequiredPlugins(demoData) {
            if (optionsBox) optionsBox.style.display = 'none';
            if (importingBox) importingBox.style.display = 'block';
            startRotatingTips();

            if (themeStepEl) {
                themeStepEl.classList.remove('tab_active');
                themeStepEl.classList.add('tab_inactive');
            }

            var target = demoData.progress === 'by_theme' ? 45 : 25;
            var current = demoData.progress === 'by_theme' ? 25 : 5;

            // Fast path: skip install_plugins AJAX roundtrip if all required plugins are already active
            if (areRequiredPluginsAlreadyActive(demoData.theme_id)) {
                setStepLinkText(pluginStepEl, 'Required Plugins Installed/Activated');
                if (pluginStepEl) {
                    pluginStepEl.classList.remove('tab_active');
                    pluginStepEl.classList.add('tab_inactive');
                }
                animateProgress(current, target, 400);
                initDemoData(demoData);
                return;
            }

            if (pluginStepEl) {
                pluginStepEl.classList.add('tab_active');
                setStepLinkText(pluginStepEl, 'Installing Required Plugins...');
            }

            animateProgress(current, target, 1200);

            postAjax({
                action: demoData.action,
                theme_id: demoData.theme_id,
                check_import_nonce: ajaxObj ? ajaxObj.nonce : '',
                step: 'install_plugins'
            })
                .then(function (response) {
                    if (response && response.success && response.data && response.data.status === 'ok') {
                        markCachedPluginsActive(demoData.theme_id);
                        setStepLinkText(pluginStepEl, 'Required Plugins Installed/Activated');
                        if (pluginStepEl) {
                            pluginStepEl.classList.remove('tab_active');
                            pluginStepEl.classList.add('tab_inactive');
                        }
                        initDemoData(demoData);
                    } else {
                        var errMsg = (response && response.data && response.data.msg)
                            ? response.data.msg
                            : 'Failed to install required plugins.';
                        showImportError(errMsg, function () {
                            installRequiredPlugins(demoData);
                        });
                    }
                })
                .catch(function (err) {
                    var errDetail = (err && err.responseJSON && err.responseJSON.data && err.responseJSON.data.msg)
                        ? err.responseJSON.data.msg
                        : 'Server connection failed while installing required plugins. Please retry.';
                    showImportError(errDetail, function () {
                        installRequiredPlugins(demoData);
                    });
                });
        }

        function initDemoData(demoData) {
            if (optionsBox) optionsBox.style.display = 'none';
            if (importingBox) importingBox.style.display = 'block';
            startRotatingTips();

            if (themeStepEl) {
                themeStepEl.classList.remove('tab_active');
                themeStepEl.classList.add('tab_inactive');
            }
            if (pluginStepEl) {
                pluginStepEl.classList.remove('tab_active');
                pluginStepEl.classList.add('tab_inactive');
            }
            if (demoFileStepEl) {
                demoFileStepEl.classList.add('tab_active');
            }

            if (demoData.progress === 'by_file') {
                animateProgress(0, 15, 800, 100);
            }

            postAjax({
                action: demoData.action,
                theme_id: demoData.theme_id,
                customize: demoData.customize,
                widget: demoData.widget,
                content: demoData.content,
                check_import_nonce: ajaxObj ? ajaxObj.nonce : '',
                step: 'demo_data_init'
            })
                .then(function (response) {
                    if (response && response.success && response.data && response.data.status === 'ok') {
                        var target = demoData.progress === 'by_file' ? 60 : 65;
                        var current = demoData.progress === 'by_file' ? 15 : 35;
                        animateProgress(current, target, 1000);

                        setStepLinkText(demoFileStepEl, 'Import Data Initialized');
                        importDemoData(demoData);
                    } else {
                        var errMsg = 'Failed to download demo data files from server.';
                        if (response && response.data) {
                            if (typeof response.data === 'string') {
                                errMsg = response.data;
                            } else if (response.data.msg) {
                                errMsg = response.data.msg;
                            }
                        }
                        showImportError(errMsg, function () {
                            initDemoData(demoData);
                        });
                    }
                })
                .catch(function (err) {
                    var errDetail = 'Server timeout while downloading demo files. Check PHP max_execution_time.';
                    if (err && err.responseJSON && err.responseJSON.data) {
                        errDetail = typeof err.responseJSON.data === 'string'
                            ? err.responseJSON.data
                            : (err.responseJSON.data.msg || errDetail);
                    }
                    showImportError(errDetail, function () {
                        initDemoData(demoData);
                    });
                });
        }

        function importDemoData(demoData) {
            if (demoFileStepEl) {
                demoFileStepEl.classList.remove('tab_active');
                demoFileStepEl.classList.add('tab_inactive');
            }
            if (demoImportStepEl) {
                demoImportStepEl.classList.add('tab_active');
            }

            postAjax({
                action: demoData.action,
                theme_id: demoData.theme_id,
                customize: demoData.customize,
                widget: demoData.widget,
                content: demoData.content,
                check_import_nonce: ajaxObj ? ajaxObj.nonce : '',
                step: 'demo_data_import'
            })
                .then(function (response) {
                    if (response && response.success && response.data && response.data.status === 'ok') {
                        var current = demoData.progress === 'by_file' ? 60 : 65;
                        animateProgress(current, 100, 600, 50);

                        if (demoImportStepEl) {
                            demoImportStepEl.classList.remove('tab_active');
                            demoImportStepEl.classList.add('tab_inactive');
                        }

                        setTimeout(function () {
                            stopRotatingTips();
                            setProgressImmediate(0);
                            if (themeStepEl) themeStepEl.classList.remove('tab_inactive');
                            if (pluginStepEl) pluginStepEl.classList.remove('tab_inactive');
                            if (demoFileStepEl) demoFileStepEl.classList.remove('tab_inactive');
                            if (demoImportStepEl) demoImportStepEl.classList.remove('tab_inactive');
                            if (importingBox) importingBox.style.display = 'none';
                            if (completeBox) completeBox.style.display = 'block';
                        }, 700);
                    } else {
                        var errMsg = 'Error writing demo content and customizer settings.';
                        if (response && response.data) {
                            if (typeof response.data === 'string') {
                                errMsg = response.data;
                            } else if (response.data.msg) {
                                errMsg = response.data.msg;
                            }
                        }
                        showImportError(errMsg, function () {
                            initDemoData(demoData);
                        });
                    }
                })
                .catch(function (err) {
                    var errDetail = 'Database timeout or execution limit reached during import. Please retry.';
                    if (err && err.responseJSON && err.responseJSON.data) {
                        errDetail = typeof err.responseJSON.data === 'string'
                            ? err.responseJSON.data
                            : (err.responseJSON.data.msg || errDetail);
                    }
                    showImportError(errDetail, function () {
                        initDemoData(demoData);
                    });
                });
        }

        function triggerStartImport(triggerBtn) {
            var dialog = triggerBtn ? triggerBtn.closest('.ansar-modal-dialog') : document;
            var customizeInput = dialog ? dialog.querySelector('.import-option-list #import-customizer') : null;
            var widgetInput = dialog ? dialog.querySelector('.import-option-list #import-widgets') : null;
            var contentInput = dialog ? dialog.querySelector('.import-option-list #import-content') : null;

            var demoData = {
                action: 'import_action',
                theme_id: themeIdInput ? themeIdInput.value : '',
                customize: customizeInput ? customizeInput.checked : true,
                widget: widgetInput ? widgetInput.checked : true,
                content: contentInput ? contentInput.checked : true,
                nonce: ajaxObj ? ajaxObj.nonce : ''
            };

            var templateType = themeIdInput ? (themeIdInput.getAttribute('data-template-type') || '') : '';
            var tname = themeIdInput ? (themeIdInput.getAttribute('tname') || '') : '';
            var isBlockTheme = Boolean((ajaxObj && ajaxObj.is_block_theme) || templateType === 'block-editor' || templateType === 'gutenberg');

            if (!isBlockTheme && tname && (templateType === 'elementor' || templateType === 'theme' || templateType === 'classic' || templateType === '')) {
                if (themeStepEl) themeStepEl.style.display = '';
                demoData.theme_name = tname;
                demoData.progress = 'by_theme';
                installRequiredTheme(demoData);
            } else {
                if (themeStepEl) themeStepEl.style.display = 'none';
                demoData.progress = 'by_file';
                installRequiredPlugins(demoData);
            }
        }

        // ---------------------------------------------------------------------
        // Favorites & Unified Demo Filtering (Category Pills + Search + Sort)
        // ---------------------------------------------------------------------
        var activeCategoryFilter = ''; // e.g., "cat_12" or "" for All

        function getDemoFavorites() {
            try {
                var stored = window.localStorage.getItem('ansar_demo_favorites');
                var parsed = stored ? JSON.parse(stored) : [];
                return Array.isArray(parsed) ? parsed.map(String) : [];
            } catch (e) {
                return [];
            }
        }

        function saveDemoFavorites(favsArray) {
            try {
                window.localStorage.setItem('ansar_demo_favorites', JSON.stringify(favsArray));
            } catch (e) {}
        }

        var nextCardInitialOrder = 1;
        function getCardMeta(box) {
            if (!box._ansarMeta) {
                var titleEl = box.querySelector('.theme-name');
                var favBtnEl = box.querySelector('.ansar-card-favorite-btn');
                var importBtnEl = box.querySelector('.btn-import');
                var rawId = (favBtnEl && favBtnEl.getAttribute('data-id')) || (importBtnEl && importBtnEl.getAttribute('data-id')) || '0';
                var isPro = Boolean(box.querySelector('.ribbon.pro'));
                var colorAttr = box.getAttribute('data-color') || '';
                var editorAttr = box.getAttribute('data-editor') || 'default-theme';
                var isFirstDemo = box.hasAttribute('data-first-demo');
                box._ansarMeta = {
                    id: parseInt(rawId, 10) || 0,
                    initialOrder: isFirstDemo ? 0 : nextCardInitialOrder++,
                    title: titleEl ? (titleEl.textContent || '').trim().toLowerCase() : '',
                    isPro: isPro,
                    editor: editorAttr,
                    colorTokens: colorAttr.trim().split(/\s+/).filter(Boolean)
                };
            }
            return box._ansarMeta;
        }

        function updateFavoriteButtonState(btn, box, isFav) {
            var label = isFav ? 'Remove from favorites' : 'Add to favorites';
            btn.classList.toggle('is-favorite', isFav);
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            if (box) {
                box.classList.toggle('is-favorite', isFav);
            }
        }

        function syncDemoFavorites(rootNode) {
            var favSet = new Set(getDemoFavorites());
            var scope = rootNode || document;
            scope.querySelectorAll('.ansar-card-favorite-btn').forEach(function (btn) {
                var id = String(btn.getAttribute('data-id') || '');
                var box = btn.closest('.ansar-inner-box');
                updateFavoriteButtonState(btn, box, favSet.has(id));
            });
        }

        syncDemoFavorites();

        var searchInput = document.getElementById('ansar-demo-search');
        var clearBtn = document.getElementById('ansar-search-clear');
        var sortSelect = document.getElementById('ansar-demo-sort');
        var orderBySelect = document.getElementById('ansar-demo-orderby');

        function performDemoFilter(shouldAnimate) {
            var searchTerm = searchInput ? (searchInput.value || '').toLowerCase().trim() : '';
            var sortValue = sortSelect ? (sortSelect.value || 'all') : 'all';
            var orderByValue = orderBySelect ? (orderBySelect.value || 'popular') : 'popular';

            if (clearBtn) {
                clearBtn.style.display = searchTerm.length > 0 ? '' : 'none';
            }

            var boxes = document.querySelectorAll('.ansar-inner-box');

            // Compute total demos and available category tokens for the active editor
            var editorTotalCount = 0;
            var editorCategorySet = new Set();
            for (var eIdx = 0; eIdx < boxes.length; eIdx++) {
                var eMeta = getCardMeta(boxes[eIdx]);
                if (!activeEditorFilter || eMeta.editor === activeEditorFilter) {
                    editorTotalCount++;
                    for (var cIdx = 0; cIdx < eMeta.colorTokens.length; cIdx++) {
                        editorCategorySet.add(eMeta.colorTokens[cIdx]);
                    }
                }
            }

            // Reset activeCategoryFilter if the newly selected editor doesn't have that category
            if (activeCategoryFilter && !editorCategorySet.has(activeCategoryFilter)) {
                activeCategoryFilter = '';
            }

            // Sync category pill visibility and active state for the active editor
            document.querySelectorAll('.uk-subnav.uk-subnav-pill').forEach(function (nav) {
                nav.style.display = editorTotalCount === 0 ? 'none' : '';
                nav.querySelectorAll('li[uk-filter-control]').forEach(function (li) {
                    var rule = li.getAttribute('uk-filter-control') || '';
                    var m = rule.match(/cat_\d+/);
                    if (!m) {
                        li.style.display = '';
                        li.classList.toggle('uk-active', activeCategoryFilter === '');
                    } else {
                        var catToken = m[0];
                        var hasCat = editorCategorySet.has(catToken);
                        li.style.display = hasCat ? '' : 'none';
                        li.classList.toggle('uk-active', activeCategoryFilter === catToken);
                    }
                });
            });

            // Toggle empty editor state when an editor has 0 total demos
            document.querySelectorAll('.ansar-empty-editor-state').forEach(function (emptyEl) {
                emptyEl.style.display = editorTotalCount === 0 ? '' : 'none';
            });

            var canAnimate = shouldAnimate !== false &&
                typeof Element !== 'undefined' &&
                typeof Element.prototype.animate === 'function' &&
                !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

            var viewportH = window.innerHeight || 800;
            var firstRects = canAnimate ? new Map() : null;
            var wasVisibleSet = canAnimate ? new Set() : null;

            if (canAnimate) {
                for (var p = 0; p < boxes.length; p++) {
                    var b = boxes[p];
                    if (b._ansarFilterAnim) {
                        b._ansarFilterAnim.cancel();
                        b._ansarFilterAnim = null;
                    }
                    if (b.style.display !== 'none') {
                        wasVisibleSet.add(b);
                        var r = b.getBoundingClientRect();
                        if (r.bottom >= -200 && r.top <= viewportH + 400) {
                            firstRects.set(b, r);
                        }
                    }
                }
            }

            var visibleCount = 0;
            var matchedBoxes = [];

            for (var i = 0; i < boxes.length; i++) {
                var box = boxes[i];
                var meta = getCardMeta(box);
                var isFav = box.classList.contains('is-favorite');

                var matchEditor = !activeEditorFilter || meta.editor === activeEditorFilter;
                var matchCategory = !activeCategoryFilter || meta.colorTokens.indexOf(activeCategoryFilter) !== -1;
                var matchSearch = searchTerm === '' || meta.title.indexOf(searchTerm) !== -1;
                var matchSort = true;

                if (sortValue === 'free' && meta.isPro) {
                    matchSort = false;
                } else if (sortValue === 'pro' && !meta.isPro) {
                    matchSort = false;
                } else if (sortValue === 'favorite' && !isFav) {
                    matchSort = false;
                }

                if (matchEditor && matchCategory && matchSearch && matchSort) {
                    if (box.style.display === 'none') {
                        box.style.display = '';
                    }
                    visibleCount++;
                    matchedBoxes.push(box);
                } else if (box.style.display !== 'none') {
                    box.style.display = 'none';
                }
            }

            if (matchedBoxes.length > 0) {
                var sortedBoxes = matchedBoxes.slice().sort(function (a, b) {
                    var aMeta = getCardMeta(a);
                    var bMeta = getCardMeta(b);
                    if (orderByValue === 'latest') {
                        if (bMeta.id !== aMeta.id) {
                            return bMeta.id - aMeta.id;
                        }
                        return aMeta.initialOrder - bMeta.initialOrder;
                    }
                    if (orderByValue === 'asc') {
                        var cmpAsc = aMeta.title.localeCompare(bMeta.title);
                        return cmpAsc !== 0 ? cmpAsc : (aMeta.initialOrder - bMeta.initialOrder);
                    }
                    if (orderByValue === 'desc') {
                        var cmpDesc = bMeta.title.localeCompare(aMeta.title);
                        return cmpDesc !== 0 ? cmpDesc : (aMeta.initialOrder - bMeta.initialOrder);
                    }
                    return aMeta.initialOrder - bMeta.initialOrder;
                });

                for (var sIdx = 0; sIdx < sortedBoxes.length; sIdx++) {
                    sortedBoxes[sIdx].style.order = String(sIdx + 1);
                }
            }

            if (canAnimate && matchedBoxes.length > 0) {
                for (var k = 0; k < matchedBoxes.length; k++) {
                    var card = matchedBoxes[k];
                    var lastRect = card.getBoundingClientRect();
                    if (lastRect.bottom < -200 || lastRect.top > viewportH + 400) {
                        continue;
                    }

                    var prevRect = firstRects.get(card);
                    if (prevRect) {
                        var dx = prevRect.left - lastRect.left;
                        var dy = prevRect.top - lastRect.top;
                        if (Math.abs(dx) > 1 || Math.abs(dy) > 1) {
                            card._ansarFilterAnim = card.animate([
                                { transform: 'translate3d(' + dx + 'px, ' + dy + 'px, 0)' },
                                { transform: 'translate3d(0, 0, 0)' }
                            ], {
                                duration: 280,
                                easing: 'cubic-bezier(0.22, 1, 0.36, 1)'
                            });
                        }
                    } else if (!wasVisibleSet.has(card)) {
                        card._ansarFilterAnim = card.animate([
                            { opacity: 0, transform: 'scale(0.95) translate3d(0, 10px, 0)' },
                            { opacity: 1, transform: 'scale(1) translate3d(0, 0, 0)' }
                        ], {
                            duration: 260,
                            easing: 'cubic-bezier(0.22, 1, 0.36, 1)'
                        });
                    }
                }
            }

            var existingNoResults = document.getElementById('ansar-no-search-results');
            if (existingNoResults) {
                existingNoResults.remove();
            }

            if (visibleCount === 0 && editorTotalCount > 0) {
                var container = document.querySelector('.grid-wrap') || document.querySelector('.theme-browser .themes');
                if (container) {
                    var emptyHtml = '<div id="ansar-no-search-results" class="ansar-no-results-card">' +
                        '<div class="ansar-no-results-icon">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<circle cx="11" cy="11" r="8"></circle>' +
                                '<line x1="21" y1="21" x2="16.65" y2="16.65"></line>' +
                                '<line x1="8" y1="11" x2="14" y2="11"></line>' +
                            '</svg>' +
                        '</div>' +
                        '<h3>No matching demos found</h3>' +
                        '<p>We couldn\'t find any demo matching your search and filter criteria.</p>' +
                        '<button type="button" class="ansar-no-results-reset" id="ansar-reset-filters">' +
                            '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                                '<polyline points="1 4 1 10 7 10"></polyline>' +
                                '<path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>' +
                            '</svg>' +
                            'Reset Filters' +
                        '</button>' +
                    '</div>';
                    container.insertAdjacentHTML('beforeend', emptyHtml);
                }
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', debounce(function () {
                performDemoFilter(true);
            }, 150));
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                performDemoFilter(true);
            });
        }
        if (orderBySelect) {
            orderBySelect.addEventListener('change', function () {
                performDemoFilter(true);
            });
        }

        // ---------------------------------------------------------------------
        // Infinite Scroll via IntersectionObserver (Zero Scroll Reflows)
        // ---------------------------------------------------------------------
        var infinitySentinel = document.getElementById('ansar-infinity-load');
        var isLoadingMore = false;
        var hasMoreDemos = true;
        var currentPage = 1;
        var infinityObserver = null;

        function loadMorePosts(page) {
            if (!infinitySentinel || isLoadingMore || !hasMoreDemos) return;
            isLoadingMore = true;

            var seed = infinitySentinel.getAttribute('seed') || '';
            postAjax({
                action: 'infinity_load_demos',
                paged: page,
                seed: seed,
                editor: activeEditorFilter || 'default-theme',
                check_import_nonce: ajaxObj ? ajaxObj.nonce : ''
            })
                .then(function (response) {
                    if (response && response.html && response.html !== 'No demos found') {
                        var gridWrap = document.querySelector('.grid-wrap');
                        if (gridWrap) {
                            var temp = document.createElement('div');
                            temp.innerHTML = response.html;
                            syncDemoFavorites(temp);
                            while (temp.firstElementChild) {
                                gridWrap.appendChild(temp.firstElementChild);
                            }
                            performDemoFilter(false);
                        }
                        isLoadingMore = false;
                    } else {
                        hasMoreDemos = false;
                        if (infinityObserver) {
                            infinityObserver.disconnect();
                        }
                        if (infinitySentinel.parentElement) {
                            infinitySentinel.parentElement.remove();
                        }
                    }
                })
                .catch(function (err) {
                    console.error(err);
                    isLoadingMore = false;
                });
        }

        if (infinitySentinel) {
            if ('IntersectionObserver' in window) {
                infinityObserver = new IntersectionObserver(function (entries) {
                    if (entries[0] && entries[0].isIntersecting && !isLoadingMore && hasMoreDemos) {
                        currentPage++;
                        loadMorePosts(currentPage);
                    }
                }, {
                    rootMargin: '2200px 0px',
                    threshold: 0
                });
                infinityObserver.observe(infinitySentinel);
            } else {
                var onScrollFallback = debounce(function () {
                    if (!infinitySentinel || isLoadingMore || !hasMoreDemos) return;
                    var rect = infinitySentinel.getBoundingClientRect();
                    if (rect.top <= window.innerHeight + 2200) {
                        currentPage++;
                        loadMorePosts(currentPage);
                    }
                }, 100);
                window.addEventListener('scroll', onScrollFallback, { passive: true });
            }
        }

        // ---------------------------------------------------------------------
        // Single Delegated Click Handler for All Admin Interactive Elements
        // ---------------------------------------------------------------------
        document.addEventListener('click', function (e) {
            var target = e.target;
            if (!target || typeof target.closest !== 'function') return;

            // 1. Reopen Onboarding Modal
            var reopenBtn = target.closest('.ansar-btn-reopen-onboarding, #ansar-btn-reopen-onboarding, .ansar-btn-setup-wizard, .ansar-editor-switcher-icon');
            if (reopenBtn && onboardingModal) {
                e.preventDefault();
                onboardingModal.style.display = 'flex';
                return;
            }

            // 2. Close Onboarding Modal
            if (target.closest('#closeOnboardingModal') || target === onboardingModal) {
                if (onboardingModal) {
                    onboardingModal.style.display = 'none';
                    cleanOnboardingParam();
                }
                return;
            }

            // 3. Builder Card Selection in Onboarding Modal
            var builderTrigger = target.closest('.ansar-builder-card, .ansar-btn-choose-builder');
            if (builderTrigger) {
                var card = builderTrigger.closest('.ansar-builder-card');
                if (!card || card.classList.contains('ansar-builder-disabled')) {
                    return;
                }
                var chosenEditor = card.getAttribute('data-editor');
                if (!chosenEditor) return;

                if (editorSwitcher) {
                    switchActiveEditor(chosenEditor, true);
                    if (onboardingModal) {
                        onboardingModal.style.display = 'none';
                    }
                    cleanOnboardingParam();
                    return;
                }

                card.classList.add('active-selected');
                var chooseBtn = card.querySelector('.ansar-btn-choose-builder');
                if (chooseBtn) {
                    chooseBtn.textContent = 'Loading...';
                }

                var navigateToChosenEditor = function () {
                    var currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('editor', chosenEditor);
                    currentUrl.searchParams.delete('onboarding');
                    window.location.href = currentUrl.toString();
                };

                if (ajaxObj) {
                    postAjax({
                        action: 'ansar_save_preferred_editor',
                        editor: chosenEditor,
                        nonce: ajaxObj.nonce
                    }, { timeout: 2500 }).finally(navigateToChosenEditor);
                } else {
                    navigateToChosenEditor();
                }
                return;
            }

            // 4. Dismiss Hero Banner
            var dismissHeroBtn = target.closest('.ansar-hero-dismiss-btn');
            if (dismissHeroBtn) {
                e.preventDefault();
                var hero = dismissHeroBtn.closest('.ansar-import-hero');
                if (hero) {
                    if (typeof hero.animate === 'function') {
                        var anim = hero.animate([
                            { height: hero.offsetHeight + 'px', opacity: 1 },
                            { height: '0px', opacity: 0, marginTop: '0px', marginBottom: '0px', paddingTop: '0px', paddingBottom: '0px' }
                        ], { duration: 250, easing: 'ease' });
                        anim.onfinish = function () {
                            hero.remove();
                        };
                    } else {
                        hero.remove();
                    }
                }
                if (ajaxObj) {
                    postAjax({
                        action: 'ansar_dismiss_hero',
                        nonce: ajaxObj.nonce
                    }).catch(function () {});
                }
                return;
            }

            // 5. Refresh Button Spin State
            var refreshBtn = target.closest('.ansar-editor-refresh');
            if (refreshBtn) {
                refreshBtn.classList.add('ansar-is-refreshing');
                return;
            }

            // 6. Category Pill Filter ([uk-filter-control])
            var filterLi = target.closest('[uk-filter-control]');
            if (filterLi) {
                e.preventDefault();
                var filterRule = filterLi.getAttribute('uk-filter-control') || '';
                var catMatch = filterRule.match(/cat_\d+/);
                var nextCategory = catMatch ? catMatch[0] : '';
                if (filterLi.classList.contains('uk-active') && activeCategoryFilter === nextCategory) {
                    return;
                }
                var navList = filterLi.closest('.uk-subnav');
                if (navList) {
                    navList.querySelectorAll('li').forEach(function (li) {
                        li.classList.toggle('uk-active', li === filterLi);
                    });
                }
                activeCategoryFilter = nextCategory;
                performDemoFilter(true);
                return;
            }

            // 7. Favorite Card Button Toggle
            var favBtn = target.closest('.ansar-card-favorite-btn');
            if (favBtn) {
                e.preventDefault();
                e.stopPropagation();

                var favId = String(favBtn.getAttribute('data-id') || '');
                var favBox = favBtn.closest('.ansar-inner-box');
                var favs = getDemoFavorites();
                var idx = favs.indexOf(favId);
                var isNowFav = idx === -1;

                if (isNowFav) {
                    favs.push(favId);
                } else {
                    favs.splice(idx, 1);
                }

                updateFavoriteButtonState(favBtn, favBox, isNowFav);
                saveDemoFavorites(favs);

                if (sortSelect && sortSelect.value === 'favorite') {
                    performDemoFilter();
                }
                return;
            }

            // 8. Clear Search Button
            if (target.closest('#ansar-search-clear')) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                performDemoFilter();
                return;
            }

            // 9. Reset All Filters Button
            if (target.closest('#ansar-reset-filters')) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'all';
                if (orderBySelect) orderBySelect.value = 'popular';
                activeCategoryFilter = '';
                document.querySelectorAll('.uk-subnav li[uk-filter-control]').forEach(function (li, index) {
                    li.classList.toggle('uk-active', index === 0);
                });
                performDemoFilter();
                return;
            }

            // 10. Preview Modal Open (.btn-preview)
            var previewBtn = target.closest('.btn-preview');
            if (previewBtn) {
                e.preventDefault();
                var clickedDemoId = previewBtn.getAttribute('data-id') || '';
                var isLive = previewBtn.getAttribute('data-live') === '1';

                var liveBtns = document.querySelectorAll('.preview-live-btn');
                var importPreviewBtns = document.querySelectorAll('.import-priview');
                var buyBtns = document.querySelectorAll('.preview-buy');
                var previewIframe = document.getElementById('theme_preview');

                liveBtns.forEach(function (el) { el.classList.add('uk-hidden'); });
                importPreviewBtns.forEach(function (el) { el.classList.remove('uk-hidden'); });

                var demoObj = demoMap.get(String(clickedDemoId));
                if (demoObj) {
                    if (previewIframe) {
                        previewIframe.setAttribute('src', demoObj.preview_link || '');
                    }
                    var slugName = demoObj.theme_name ? String(demoObj.theme_name).toLowerCase().replace(/\s+/g, '-') : '';
                    importPreviewBtns.forEach(function (el) {
                        el.setAttribute('data-id', String(clickedDemoId));
                        if (slugName) el.setAttribute('tname', slugName);
                        if (demoObj.template_type) el.setAttribute('data-template-type', demoObj.template_type);
                    });

                    if (ajaxObj && ajaxObj.theme_name === demoObj.theme_name) {
                        importPreviewBtns.forEach(function (el) { el.classList.remove('uk-hidden'); });
                        buyBtns.forEach(function (el) { el.classList.add('uk-hidden'); });
                    } else {
                        importPreviewBtns.forEach(function (el) { el.classList.add('uk-hidden'); });
                        buyBtns.forEach(function (el) {
                            el.classList.remove('uk-hidden');
                            el.setAttribute('href', demoObj.pro_link || '#');
                            el.setAttribute('src', demoObj.pro_link || '');
                        });
                    }
                }

                if (isLive) {
                    importPreviewBtns.forEach(function (el) { el.classList.add('uk-hidden'); });
                    liveBtns.forEach(function (el) { el.classList.remove('uk-hidden'); });
                }

                getModalController('#AnsardemoPreview').show();
                return;
            }

            // 11. Preview Modal Device Switchers & Sidebar Collapse & Close
            if (target.closest('.preview-desktop')) {
                document.querySelectorAll('.wp-full-overlay-main').forEach(function (el) {
                    el.classList.remove('p-mobile', 'p-tablet');
                });
                return;
            }
            if (target.closest('.preview-tablet')) {
                document.querySelectorAll('.wp-full-overlay-main').forEach(function (el) {
                    el.classList.add('p-tablet');
                    el.classList.remove('p-mobile');
                });
                return;
            }
            if (target.closest('.preview-mobile')) {
                document.querySelectorAll('.wp-full-overlay-main').forEach(function (el) {
                    el.classList.add('p-mobile');
                    el.classList.remove('p-tablet');
                });
                return;
            }

            var collapseBtn = target.closest('.collapse-sidebar');
            if (collapseBtn) {
                var expanded = collapseBtn.getAttribute('aria-expanded') === 'true';
                collapseBtn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                document.querySelectorAll('.theme-install-overlay').forEach(function (el) {
                    el.classList.toggle('collapsed', !expanded);
                    el.classList.toggle('expanded', expanded);
                });
                return;
            }

            if (target.closest('.close-full-overlay')) {
                getModalController('#AnsardemoPreview').hide();
                var iframeEl = document.getElementById('theme_preview');
                if (iframeEl) iframeEl.setAttribute('src', '');
                document.querySelectorAll('#AnsardemoPreview .theme-screenshot').forEach(function (el) {
                    el.setAttribute('src', '');
                });
                return;
            }

            // 12. Single Unified .btn-import Handler (Replaces 3 duplicate handlers)
            var importBtn = target.closest('.btn-import');
            if (importBtn) {
                e.preventDefault();
                var rawId = importBtn.getAttribute('data-id') || '';
                var rawTname = importBtn.getAttribute('tname') || '';
                var rawTemplateType = importBtn.getAttribute('data-template-type') || '';

                if (themeIdInput) {
                    if (rawId) themeIdInput.value = rawId;
                    if (rawTname) {
                        themeIdInput.setAttribute('tname', rawTname);
                    } else {
                        themeIdInput.removeAttribute('tname');
                    }
                    if (rawTemplateType) {
                        themeIdInput.setAttribute('data-template-type', rawTemplateType);
                    } else {
                        themeIdInput.removeAttribute('data-template-type');
                    }
                }

                // Close preview modal if open
                getModalController('#AnsardemoPreview').hide();

                // Highlight focused card
                document.querySelectorAll('.theme.focus').forEach(function (el) {
                    el.classList.remove('focus');
                });
                var parentCard = importBtn.closest('.theme');
                if (parentCard) {
                    parentCard.classList.add('focus');
                }

                // Update dynamic documentation links
                var docUrl = 'https://docs.themeansar.com/';
                if (rawTemplateType === 'block-editor' || rawTemplateType === 'gutenberg') {
                    docUrl = 'https://docs.themeansar.com/docs/ansar-blocks/';
                } else if (rawTemplateType === 'elementor') {
                    docUrl = 'https://docs.themeansar.com/docs/ansar-elements/';
                } else if (ajaxObj && ajaxObj.doc_url) {
                    docUrl = ajaxObj.doc_url;
                }
                document.querySelectorAll('#ansar-modal-doc-link, #ansar-error-doc-link, .ansar-doc-link').forEach(function (link) {
                    link.setAttribute('href', docUrl);
                });

                loadModalRequiredPlugins(rawId);

                if (importConfirmModal) importConfirmModal.style.display = 'flex';
                if (optionsBox) optionsBox.removeAttribute('style');
                if (completeBox) completeBox.style.display = 'none';
                if (errorBox) errorBox.style.display = 'none';
                if (importingBox) importingBox.style.display = 'none';
                return;
            }

            // 13. Confirm & Start Import Button (#import_data)
            var startImportBtn = target.closest('#import_data');
            if (startImportBtn) {
                e.preventDefault();
                triggerStartImport(startImportBtn);
                return;
            }

            // 14. Retry Import Button (#ansar-error-retry-btn)
            if (target.closest('#ansar-error-retry-btn')) {
                e.preventDefault();
                if (errorBox) errorBox.style.display = 'none';
                if (typeof currentRetryCallback === 'function') {
                    currentRetryCallback();
                } else {
                    triggerStartImport(document.getElementById('import_data'));
                }
                return;
            }

            // 15. Toggle Import Option Card when clicking card padding (.import-option-list li)
            var optionCard = target.closest('.import-option-list li');
            if (optionCard && !target.closest('input[type="checkbox"], label')) {
                var cb = optionCard.querySelector('input[type="checkbox"]');
                if (cb) {
                    cb.checked = !cb.checked;
                    optionCard.classList.toggle('active', Boolean(cb.checked));
                }
                return;
            }

            // 16. Close Import Modal Buttons or Backdrop Click (when not actively importing)
            if (
                target.closest('#closeConfirm, #importDoneClose, #cancelModal, #importErrorClose, #ansar-error-close-btn') ||
                (target === importConfirmModal && (!importingBox || importingBox.style.display === 'none'))
            ) {
                e.preventDefault();
                closeImportModal();
            }
        });

        // Delegated change handler for Import Option checkboxes (.import-option-list li)
        document.addEventListener('change', function (e) {
            var target = e.target;
            if (!target || typeof target.matches !== 'function') return;
            if (target.matches('.import-option-list li input[type="checkbox"]')) {
                var parentLi = target.closest('li');
                if (parentLi) {
                    parentLi.classList.toggle('active', Boolean(target.checked));
                }
            }
        });

        // Escape key handler for modals
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (importConfirmModal && importConfirmModal.style.display === 'flex' && (!importingBox || importingBox.style.display === 'none')) {
                closeImportModal();
            }
            if (onboardingModal && onboardingModal.style.display === 'flex') {
                onboardingModal.style.display = 'none';
                cleanOnboardingParam();
            }
            var previewModal = document.getElementById('AnsardemoPreview');
            if (previewModal && (previewModal.classList.contains('uk-open') || previewModal.style.display === 'block')) {
                getModalController(previewModal).hide();
            }
        });
    });
})();
