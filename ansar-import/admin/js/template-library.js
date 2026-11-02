( function( wp ) {
    'use strict';

    if (
        ! wp ||
        ! wp.plugins ||
        ! wp.element ||
        ! wp.components ||
        ! wp.blocks ||
        ! wp.data
    ) {
        return;
    }

    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var useEffect = wp.element.useEffect;
    var useMemo = wp.element.useMemo;
    var useRef = wp.element.useRef;
    var useState = wp.element.useState;
    var Modal = wp.components.Modal;
    var registerPlugin = wp.plugins.registerPlugin;
    var __ = wp.i18n.__;

    var data = window.taAnsarImportTemplateLibraryData || {};
    var cache = {
        sections: null,
        pages: null
    };
    var pendingRequests = {};
    var cacheTtl = Number( data.cacheTtl || 86400 ) * 1000;
    var templatesLimit = Math.max( 1, Number( data.templatesLimit || 1000 ) );
    var requestPageSize = Math.max( 1, Number( data.requestPageSize || 100 ) );
    var renderBatchSize = Math.max( 1, Number( data.renderBatchSize || 24 ) );
    var searchDelay = Math.max( 0, Number( data.searchDelay || 250 ) );

    function getCacheKey( type ) {
        return ( data.cacheKey || 'ta_ansar_import_template_library' ) + '_' + type;
    }

    function getFavoritesKey() {
        return ( data.cacheKey || 'ta_ansar_import_template_library' ) + '_favorites';
    }

    function readFavoriteTemplates() {
        var stored;
        var favorites = {};

        try {
            stored = window.localStorage.getItem( getFavoritesKey() );
        } catch ( error ) {
            return favorites;
        }

        if ( ! stored ) {
            return favorites;
        }

        try {
            stored = JSON.parse( stored );
        } catch ( error ) {
            return favorites;
        }

        if ( ! Array.isArray( stored ) ) {
            return favorites;
        }

        stored.forEach( function( templateId ) {
            favorites[ String( templateId ) ] = true;
        } );

        return favorites;
    }

    function writeFavoriteTemplates( favorites ) {
        try {
            window.localStorage.setItem(
                getFavoritesKey(),
                JSON.stringify( Object.keys( favorites ).filter( function( templateId ) {
                    return favorites[ templateId ];
                } ) )
            );
        } catch ( error ) {
            // Ignore storage failures; favorites remain for the current session.
        }
    }

    function readStoredTemplates( type ) {
        var stored;

        try {
            stored = window.localStorage.getItem( getCacheKey( type ) );
        } catch ( error ) {
            return null;
        }

        if ( ! stored ) {
            return null;
        }

        try {
            stored = JSON.parse( stored );
        } catch ( error ) {
            return null;
        }

        if ( ! stored.createdAt || Date.now() - stored.createdAt > cacheTtl ) {
            clearTemplateCache( type );
            return null;
        }

        if ( ! stored.payload || ! Array.isArray( stored.payload.templates ) ) {
            return null;
        }

        return stored.payload;
    }

    function writeStoredTemplates( type, payload ) {
        try {
            window.localStorage.setItem(
                getCacheKey( type ),
                JSON.stringify( {
                    createdAt: Date.now(),
                    payload: payload
                } )
            );
        } catch ( error ) {
            // Browsers can reject large payloads or private-mode storage writes.
        }
    }

    function clearTemplateCache( type ) {
        var types = type ? [ type ] : [ 'sections', 'pages' ];

        types.forEach( function( cacheType ) {
            cache[ cacheType ] = null;

            try {
                window.localStorage.removeItem( getCacheKey( cacheType ) );
            } catch ( error ) {
                // Ignore storage failures; in-memory cache was already cleared.
            }
        } );

        if ( ! type ) {
            clearLegacyTemplateCache();
        }
    }

    function clearLegacyTemplateCache() {
        var prefixes = [
            'ansar_import_template_library_',
            'ta_ansar_import_template_library_'
        ];
        var keys = [];
        var index;

        try {
            for ( index = 0; index < window.localStorage.length; index++ ) {
                keys.push( window.localStorage.key( index ) );
            }

            keys.forEach( function( key ) {
                if ( prefixes.some( function( prefix ) {
                    return key && key.indexOf( prefix ) === 0;
                } ) ) {
                    window.localStorage.removeItem( key );
                }
            } );
        } catch ( error ) {
            // Ignore storage failures; active in-memory cache was already cleared.
        }
    }

    function getEndpoint( type, page ) {
        var endpoint = type === 'pages' ? data.pagesEndpoint : data.sectionsEndpoint;
        var perPage = Math.min( requestPageSize, templatesLimit );

        if ( ! endpoint || ! perPage ) {
            return endpoint;
        }

        try {
            endpoint = new URL( endpoint, window.location.origin );

            if ( ! endpoint.searchParams.has( 'per_page' ) ) {
                endpoint.searchParams.set( 'per_page', String( perPage ) );
            }

            if ( ! endpoint.searchParams.has( 'page' ) ) {
                endpoint.searchParams.set( 'page', String( page ) );
            }

            return endpoint.toString();
        } catch ( error ) {
            return endpoint + ( endpoint.indexOf( '?' ) === -1 ? '?' : '&' ) +
                'per_page=' + encodeURIComponent( perPage ) +
                '&page=' + encodeURIComponent( page );
        }
    }

    function getTemplateId( template ) {
        return String( template.ID || template.id || template.slug || template.post_title || Math.random() );
    }

    function buildSeenIds( payload ) {
        var seenTemplateIds = {};
        var seenCategoryIds = {};

        ( payload.templates || [] ).forEach( function( template ) {
            seenTemplateIds[ getTemplateId( template ) ] = true;
        } );

        ( payload.categories || [] ).forEach( function( category ) {
            seenCategoryIds[ String( category.id || category.slug || category.name ) ] = true;
        } );

        return {
            templates: seenTemplateIds,
            categories: seenCategoryIds
        };
    }

    function mergeTemplatePayload( aggregate, payload, seenTemplateIds, seenCategoryIds ) {
        var categories = payload.categories || [];
        var templates = payload.templates || [];
        var added = 0;

        categories.forEach( function( category ) {
            var categoryId = String( category.id || category.slug || category.name );

            if ( ! seenCategoryIds[ categoryId ] ) {
                seenCategoryIds[ categoryId ] = true;
                aggregate.categories.push( category );
            }
        } );

        templates.forEach( function( template ) {
            var templateId = getTemplateId( template );

            if ( ! seenTemplateIds[ templateId ] && aggregate.templates.length < templatesLimit ) {
                seenTemplateIds[ templateId ] = true;
                aggregate.templates.push( template );
                added++;
            }
        } );

        return added;
    }

    function fetchTemplatePage( type, page ) {
        return window.fetch( getEndpoint( type, page ) )
            .then( function( response ) {
                if ( ! response.ok ) {
                    throw new Error( 'Template request failed.' );
                }

                return response.json().then( function( payload ) {
                    return {
                        payload: payload,
                        totalPages: Number( response.headers.get( 'X-WP-TotalPages' ) || 0 )
                    };
                } );
            } );
    }

    function fetchTemplates( type ) {
        if ( cache[ type ] ) {
            return Promise.resolve( cache[ type ] );
        }

        cache[ type ] = readStoredTemplates( type );

        if ( cache[ type ] ) {
            return Promise.resolve( cache[ type ] );
        }

        if ( pendingRequests[ type ] ) {
            return pendingRequests[ type ];
        }

        pendingRequests[ type ] = fetchTemplatePage( type, 1 )
            .then( function( result ) {
                var payload = result.payload || {};
                var templates = payload.templates || [];
                var aggregate = {
                    categories: [],
                    templates: [],
                    nextPage: 2,
                    hasMore: false
                };

                mergeTemplatePayload( aggregate, payload, {}, {} );

                aggregate.hasMore = aggregate.templates.length < templatesLimit && (
                    ( result.totalPages && result.totalPages > 1 ) ||
                    ( ! result.totalPages && templates.length >= Math.min( requestPageSize, templatesLimit ) )
                );

                cache[ type ] = aggregate;
                writeStoredTemplates( type, cache[ type ] );

                return cache[ type ];
            } )
            .finally( function() {
                pendingRequests[ type ] = null;
            } );

        return pendingRequests[ type ];
    }

    function fetchMoreTemplates( type ) {
        var payload = cache[ type ];
        var requestKey = type + '_more';
        var seen;

        if ( ! payload || ! payload.hasMore || payload.templates.length >= templatesLimit ) {
            return Promise.resolve( payload );
        }

        if ( pendingRequests[ requestKey ] ) {
            return pendingRequests[ requestKey ];
        }

        seen = buildSeenIds( payload );

        pendingRequests[ requestKey ] = fetchTemplatePage( type, payload.nextPage || 2 )
            .then( function( result ) {
                var responsePayload = result.payload || {};
                var templates = responsePayload.templates || [];
                var added = mergeTemplatePayload( payload, responsePayload, seen.templates, seen.categories );
                var hasMoreHeaderPages = result.totalPages && payload.nextPage < result.totalPages;
                var hasPossibleNextPage = templates.length >= Math.min( requestPageSize, templatesLimit );

                payload.nextPage = ( payload.nextPage || 2 ) + 1;
                payload.hasMore = payload.templates.length < templatesLimit &&
                    added > 0 &&
                    ( hasMoreHeaderPages || ( ! result.totalPages && hasPossibleNextPage ) );

                cache[ type ] = payload;
                writeStoredTemplates( type, payload );

                return payload;
            } )
            .finally( function() {
                pendingRequests[ requestKey ] = null;
            } );

        return pendingRequests[ requestKey ];
    }

    function getMetaValue( meta, key ) {
        if ( ! meta || ! meta[ key ] ) {
            return '';
        }

        return Array.isArray( meta[ key ] ) ? meta[ key ][0] : meta[ key ];
    }

    function normalizeTemplateContent( payload ) {
        if ( Array.isArray( payload ) && payload[0] && payload[0].content ) {
            return payload[0].content;
        }

        if ( payload && payload.content ) {
            return payload.content;
        }

        if ( typeof payload === 'string' ) {
            return payload;
        }

        return '';
    }

    function getCategoryIcon( category ) {
        var key = String( ( category && ( category.slug || category.name ) ) || 'all' ).toLowerCase();
        var iconMap = [
            [ 'all', 'dashicons-screenoptions' ],
            [ 'hero', 'dashicons-cover-image' ],
            [ 'header', 'dashicons-align-wide' ],
            [ 'footer', 'dashicons-editor-kitchensink' ],
            [ 'about', 'dashicons-info' ],
            [ 'service', 'dashicons-admin-tools' ],
            [ 'feature', 'dashicons-star-filled' ],
            [ 'blog', 'dashicons-welcome-write-blog' ],
            [ 'post', 'dashicons-admin-post' ],
            [ 'news', 'dashicons-megaphone' ],
            [ 'contact', 'dashicons-email-alt' ],
            [ 'team', 'dashicons-groups' ],
            [ 'testimonial', 'dashicons-format-quote' ],
            [ 'pricing', 'dashicons-money-alt' ],
            [ 'portfolio', 'dashicons-portfolio' ],
            [ 'gallery', 'dashicons-format-gallery' ],
            [ 'slider', 'dashicons-images-alt2' ],
            [ 'cta', 'dashicons-share-alt2' ],
            [ 'faq', 'dashicons-editor-help' ],
            [ 'search', 'dashicons-search' ],
            [ 'shop', 'dashicons-cart' ]
        ];
        var matched = iconMap.find( function( item ) {
            return key.indexOf( item[0] ) !== -1;
        } );

        return matched ? matched[1] : 'dashicons-layout';
    }

    function categoryLabel( label, icon, count ) {
        return [
            el( 'span', { key: 'icon', className: 'ansar-tl-cat-icon dashicons ' + icon, 'aria-hidden': true } ),
            el( 'span', { key: 'label', className: 'ansar-tl-cat-label' }, label ),
            el( 'span', { key: 'count', className: 'ansar-tl-cat-count' }, count )
        ];
    }

    function getCategoryCounts( templates, templateType, favorites ) {
        var counts = {
            all: 0
        };

        templates.forEach( function( template ) {
            var meta = template.post_meta || {};
            var templatePlan = getMetaValue( meta, 'template_type' );
            var templateId = getTemplateId( template );
            var isFav = !! ( favorites && favorites[ templateId ] );

            var matchesType = true;
            if ( templateType === 'favorites' ) {
                matchesType = isFav;
            } else if ( templateType && templateType !== 'freepro' ) {
                matchesType = templatePlan === templateType;
            }

            if ( matchesType ) {
                counts.all = ( counts.all || 0 ) + 1;
                Object.keys( template.taxonomies || {} ).forEach( function( slug ) {
                    counts[ slug ] = ( counts[ slug ] || 0 ) + 1;
                } );
            }
        } );

        return counts;
    }

    function getTemplateCategoryName( template ) {
        var taxonomies = template.taxonomies || {};
        var keys = Object.keys( taxonomies );
        var firstTaxonomy;

        if ( ! keys.length ) {
            return __( 'Template', 'ansar-import' );
        }

        firstTaxonomy = taxonomies[ keys[0] ];

        if ( Array.isArray( firstTaxonomy ) && firstTaxonomy[0] ) {
            return firstTaxonomy[0].name || firstTaxonomy[0].slug || keys[0];
        }

        if ( firstTaxonomy && typeof firstTaxonomy === 'object' ) {
            return firstTaxonomy.name || firstTaxonomy.slug || keys[0];
        }

        return keys[0].replace( /-/g, ' ' );
    }

    function TemplateGrid( props ) {
        var type = props.type;
        var templateType = props.templateType;
        var templateSearch = props.templateSearch;
        var cacheVersion = props.cacheVersion;
        var favorites = props.favorites;
        var pluginStatus = props.pluginStatus;
        var onToggleFavorite = props.onToggleFavorite;
        var onPreparePlugin = props.onPreparePlugin;
        var onInsert = props.onInsert;
        var state = useState( [] );
        var categories = state[0];
        var setCategories = state[1];
        var templateState = useState( [] );
        var templates = templateState[0];
        var setTemplates = templateState[1];
        var activeCatState = useState( 'all' );
        var activeCat = activeCatState[0];
        var setActiveCat = activeCatState[1];
        var loadingState = useState( true );
        var loading = loadingState[0];
        var setLoading = loadingState[1];
        var loadingMoreState = useState( false );
        var loadingMore = loadingMoreState[0];
        var setLoadingMore = loadingMoreState[1];
        var hasMoreState = useState( false );
        var hasMore = hasMoreState[0];
        var setHasMore = hasMoreState[1];
        var errorState = useState( '' );
        var error = errorState[0];
        var setError = errorState[1];
        var previewState = useState( null );
        var previewUrl = previewState[0];
        var setPreviewUrl = previewState[1];
        var iframeState = useState( true );
        var iframeLoading = iframeState[0];
        var setIframeLoading = iframeState[1];
        var widthState = useState( '100%' );
        var previewWidth = widthState[0];
        var setPreviewWidth = widthState[1];
        var modeState = useState( 'Desktop' );
        var previewMode = modeState[0];
        var setPreviewMode = modeState[1];
        var visibleCountState = useState( renderBatchSize );
        var visibleCount = visibleCountState[0];
        var setVisibleCount = visibleCountState[1];
        var sentinelRef = useRef( null );

        useEffect( function() {
            var mounted = true;

            setLoading( true );
            setLoadingMore( false );
            setHasMore( false );
            setError( '' );
            setVisibleCount( renderBatchSize );

            fetchTemplates( type )
                .then( function( payload ) {
                    if ( ! mounted ) {
                        return;
                    }

                    setCategories( payload.categories );
                    setTemplates( payload.templates );
                    setHasMore( !! payload.hasMore );
                } )
                .catch( function( fetchError ) {
                    if ( ! mounted ) {
                        return;
                    }

                    setError( fetchError.message || __( 'Unable to load templates.', 'ansar-import' ) );
                } )
                .finally( function() {
                    if ( mounted ) {
                        setLoading( false );
                    }
                } );

            return function() {
                mounted = false;
            };
        }, [ type, cacheVersion ] );

        useEffect( function() {
            setVisibleCount( renderBatchSize );
        }, [ activeCat, templateType, templateSearch, favorites ] );

        var filteredTemplates = useMemo( function() {
            return templates.filter( matchesTemplate );
        }, [ templates, activeCat, templateType, templateSearch, favorites ] );
        var categoryCounts = useMemo( function() {
            return getCategoryCounts( templates, templateType, favorites );
        }, [ templates, templateType, favorites ] );

        useEffect( function() {
            var sentinel = sentinelRef.current;
            var root = document.querySelector( '.ansar-tl-main' );
            var observer;

            if ( ! sentinel || loading || loadingMore || error || ( visibleCount >= filteredTemplates.length && ! hasMore ) ) {
                return;
            }

            function showNextBatch() {
                if ( visibleCount < filteredTemplates.length ) {
                    setVisibleCount( function( currentCount ) {
                        return Math.min( currentCount + renderBatchSize, filteredTemplates.length );
                    } );
                    return;
                }

                if ( ! hasMore ) {
                    return;
                }

                setLoadingMore( true );
                fetchMoreTemplates( type )
                    .then( function( payload ) {
                        if ( ! payload ) {
                            return;
                        }

                        setCategories( payload.categories );
                        setTemplates( payload.templates );
                        setHasMore( !! payload.hasMore );
                    } )
                    .catch( function() {
                        setHasMore( false );
                    } )
                    .finally( function() {
                        setLoadingMore( false );
                    } );
            }

            if ( 'IntersectionObserver' in window ) {
                observer = new IntersectionObserver(
                    function( entries ) {
                        if ( entries[0] && entries[0].isIntersecting ) {
                            showNextBatch();
                        }
                    },
                    {
                        root: root || null,
                        rootMargin: '600px 0px',
                        threshold: 0
                    }
                );
                observer.observe( sentinel );

                return function() {
                    observer.disconnect();
                };
            }

            function onScroll() {
                var rect = sentinel.getBoundingClientRect();

                if ( rect.top <= window.innerHeight + 600 ) {
                    showNextBatch();
                }
            }

            window.addEventListener( 'scroll', onScroll, { passive: true } );
            onScroll();

            return function() {
                window.removeEventListener( 'scroll', onScroll );
            };
        }, [ type, loading, loadingMore, error, hasMore, filteredTemplates.length, visibleCount, activeCat, templateType, templateSearch, favorites ] );

        function matchesTemplate( template ) {
            var meta = template.post_meta || {};
            var templatePlan = getMetaValue( meta, 'template_type' );
            var title = ( template.post_title || '' ).toLowerCase();
            var templateId = getTemplateId( template );
            var isFavorite = !! ( favorites && favorites[ templateId ] );

            var matchesCategory = activeCat === 'all' || (
                template.taxonomies &&
                Object.prototype.hasOwnProperty.call( template.taxonomies, activeCat )
            );

            var matchesType = true;
            if ( templateType === 'favorites' ) {
                matchesType = isFavorite;
            } else if ( templateType === 'freepro' ) {
                matchesType = true;
            } else {
                matchesType = templatePlan === templateType;
            }

            var matchesSearch = ! templateSearch || title.indexOf( templateSearch.toLowerCase() ) !== -1;

            return matchesCategory && matchesType && matchesSearch;
        }

        function insertFromUrl( jsonUrl ) {
            if ( ! jsonUrl ) {
                return;
            }

            window.fetch( jsonUrl )
                .then( function( response ) {
                    if ( ! response.ok ) {
                        throw new Error( 'Template file request failed.' );
                    }

                    return response.json();
                } )
                .then( function( payload ) {
                    var content = normalizeTemplateContent( payload );

                    if ( content ) {
                        onInsert( content );
                    }
                } )
                .catch( function() {
                    window.alert( __( 'Unable to insert this template.', 'ansar-import' ) );
                } );
        }

        function responsiveButton( mode, icon, width ) {
            return el(
                'button',
                {
                    type: 'button',
                    className: 'ansar-tl-resp-btn' + ( previewMode === mode ? ' ansar-tl-is-active' : '' ),
                    'aria-label': mode,
                    onClick: function() {
                        setPreviewWidth( width );
                        setPreviewMode( mode );
                    }
                },
                el( 'span', { className: 'dashicons ' + icon, 'aria-hidden': true } )
            );
        }

        var visibleTemplates = filteredTemplates.slice( 0, visibleCount );
        var remainingTemplates = filteredTemplates.length - visibleTemplates.length;

        if ( loading ) {
            return el( 'div', { className: 'ansar-tl-loader' } );
        }

        return el(
            Fragment,
            null,
            el(
                'aside',
                { className: 'ansar-tl-sidebar' },
                el(
                    'ul',
                    { className: 'ansar-tl-nav ansar-tl-cat-group' },
                    el(
                        'li',
                        {
                            className: 'ansar-tl-cat ' + ( activeCat === 'all' ? 'ansar-tl-is-active' : '' ),
                            onClick: function() {
                                setActiveCat( 'all' );
                            }
                        },
                        categoryLabel( __( 'All', 'ansar-import' ), getCategoryIcon( { slug: 'all' } ), categoryCounts.all || 0 )
                    ),
                    categories.map( function( category ) {
                        return el(
                            'li',
                            {
                                key: category.id || category.slug,
                                className: 'ansar-tl-cat ' + ( activeCat === category.slug ? 'ansar-tl-is-active' : '' ),
                                onClick: function() {
                                    setActiveCat( category.slug );
                                }
                            },
                            categoryLabel( category.name, getCategoryIcon( category ), categoryCounts[ category.slug ] || 0 )
                        );
                    } )
                )
            ),
            el(
                'main',
                { className: 'ansar-tl-main' },
                error ? el( 'p', { className: 'ansar-tl-message' }, error ) : null,
                ! error && filteredTemplates.length === 0 && ! hasMore && ! loadingMore ? (
                    templateType === 'favorites' ? el(
                        'div',
                        { className: 'ansar-tl-empty-state ansar-tl-empty-favorites' },
                        el(
                            'div',
                            { className: 'ansar-tl-empty-icon-box' },
                            el(
                                'svg',
                                {
                                    className: 'ansar-tl-empty-heart-svg',
                                    viewBox: '0 0 24 24',
                                    width: 32,
                                    height: 32,
                                    fill: 'none',
                                    stroke: '#f43f5e',
                                    strokeWidth: 2,
                                    strokeLinecap: 'round',
                                    strokeLinejoin: 'round',
                                    'aria-hidden': true
                                },
                                el( 'path', {
                                    d: 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'
                                } )
                            )
                        ),
                        el( 'h3', { className: 'ansar-tl-empty-title' }, __( 'No Favorite Templates Yet', 'ansar-import' ) ),
                        el( 'p', { className: 'ansar-tl-empty-desc' }, __( 'Click the heart icon on any pattern or page card to save it to your favorites for quick access.', 'ansar-import' ) )
                    ) : el( 'p', { className: 'ansar-tl-message' }, __( 'No templates available.', 'ansar-import' ) )
                ) : null,
                ! error && filteredTemplates.length === 0 && hasMore && ! loadingMore ? el( 'p', { className: 'ansar-tl-message' }, __( 'No loaded templates match this filter.', 'ansar-import' ) ) : null,
                ! error && filteredTemplates.length > 0 ? el(
                    'div',
                    { className: 'ansar-tl-results-count' },
                    __( 'Showing', 'ansar-import' ) + ' ' + visibleTemplates.length + ' / ' + filteredTemplates.length
                ) : null,
                el(
                    'div',
                    { className: 'ansar-tl-cards' },
                    visibleTemplates.map( function( template ) {
                        var meta = template.post_meta || {};
                        var isPro = getMetaValue( meta, 'template_type' ) === 'pro';
                        var imageUrl = getMetaValue( meta, 'template_img_url' );
                        var preview = getMetaValue( meta, 'preview_link' );
                        var jsonUrl = getMetaValue( meta, 'json_file_url' );
                        var proUrl = getMetaValue( meta, 'pro_link' ) || data.proUrl || '#';
                        var categoryName = getTemplateCategoryName( template );
                        var templateId = getTemplateId( template );
                        var isFavorite = !! favorites[ templateId ];
                        var freePlugin = pluginStatus.free || {};
                        var proPlugin = pluginStatus.pro || {};
                        var pluginType = isPro || proPlugin.installed ? 'pro' : 'free';
                        var requiredPlugin = pluginStatus[ pluginType ] || {};
                        var isPluginReady = isPro ? !! proPlugin.active : !! ( freePlugin.active || proPlugin.active );
                        var isPreparingPlugin = !! requiredPlugin.loading;
                        var isProMissing = isPro && ! requiredPlugin.installed;
                        var shouldImportAfterPrepare = ! requiredPlugin.installed;

                        function importTemplate() {
                            insertFromUrl( jsonUrl );
                        }

                        function preparePluginAction() {
                            if ( isPreparingPlugin ) {
                                return;
                            }

                            onPreparePlugin( pluginType )
                                .then( function( status ) {
                                    if ( shouldImportAfterPrepare && status && status.active ) {
                                        insertFromUrl( jsonUrl );
                                    }
                                } );
                        }

                        function getActionLabel() {
                            if ( isPreparingPlugin ) {
                                return __( 'Preparing...', 'ansar-import' );
                            }

                            if ( isPluginReady ) {
                                return __( 'Import', 'ansar-import' );
                            }

                            if ( isProMissing ) {
                                return __( 'Unlock Pro', 'ansar-import' );
                            }

                            if ( requiredPlugin.installed ) {
                                return __( 'Activate', 'ansar-import' );
                            }

                            return __( 'Install', 'ansar-import' );
                        }

                        return el(
                            'div',
                            {
                                key: templateId,
                                className: 'ansar-tl-card ' + ( isPro ? 'ansar-tl-is-pro' : 'ansar-tl-is-free' ) + ( isFavorite ? ' ansar-tl-card-is-favorite' : '' )
                            },
                            el(
                                'div',
                                { className: 'ansar-tl-img' },
                                el(
                                    'button',
                                    {
                                        type: 'button',
                                        className: 'ansar-tl-favorite-btn' + ( isFavorite ? ' ansar-tl-is-favorite' : '' ),
                                        'aria-label': isFavorite ? __( 'Remove from favorites', 'ansar-import' ) : __( 'Add to favorites', 'ansar-import' ),
                                        'aria-pressed': isFavorite,
                                        title: isFavorite ? __( 'Remove from favorites', 'ansar-import' ) : __( 'Add to favorites', 'ansar-import' ),
                                        onClick: function( event ) {
                                            if ( event ) {
                                                if ( event.stopPropagation ) { event.stopPropagation(); }
                                                if ( event.preventDefault ) { event.preventDefault(); }
                                            }
                                            onToggleFavorite( templateId );
                                        }
                                    },
                                    el(
                                        'svg',
                                        {
                                            className: 'ansar-tl-favorite-heart',
                                            viewBox: '0 0 24 24',
                                            width: 15,
                                            height: 15,
                                            fill: isFavorite ? '#f43f5e' : 'none',
                                            stroke: isFavorite ? '#f43f5e' : 'currentColor',
                                            strokeWidth: 2,
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            'aria-hidden': true
                                        },
                                        el( 'path', {
                                            d: 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'
                                        } )
                                    )
                                ),
                                el(
                                    'span',
                                    {
                                        className: 'ansar-tl-template-badge',
                                        'aria-label': isPro ? __( 'Pro', 'ansar-import' ) : __( 'Free', 'ansar-import' )
                                    }
                                ),
                                imageUrl ? el( 'img', { src: imageUrl, alt: template.post_title || '', loading: 'lazy', decoding: 'async' } ) : null,
                                preview ? el(
                                    'button',
                                    {
                                        type: 'button',
                                        className: 'ansar-tl-preview-btn',
                                        onClick: function() {
                                            setPreviewUrl( preview );
                                            setIframeLoading( true );
                                        }
                                    },
                                    el( 'span', { className: 'ansar-tl-preview' }, __( 'Preview', 'ansar-import' ) )
                                ) : null
                            ),
                            el(
                                'div',
                                { className: 'ansar-tl-card-body' },
                                el(
                                    'div',
                                    { className: 'ansar-tl-card-info' },
                                    el( 'h3', null, template.post_title || __( 'Untitled', 'ansar-import' ) )
                                ),
                                el(
                                    'div',
                                    { className: 'ansar-tl-card-actions' },
                                    el(
                                        'div',
                                        { className: 'ansar-tl-card-meta' },
                                        el( 'span', null, el( 'span', { className: 'dashicons dashicons-admin-appearance', 'aria-hidden': true } ), categoryName )
                                    ),
                                    isProMissing ? el(
                                        'a',
                                        {
                                            href: proUrl,
                                            target: '_blank',
                                            rel: 'noopener noreferrer',
                                            className: 'ansar-tl-pro-btn'
                                        },
                                        getActionLabel()
                                    ) : el(
                                        'button',
                                        {
                                            type: 'button',
                                            className: 'ansar-tl-insert-btn',
                                            disabled: isPreparingPlugin,
                                            onClick: function() {
                                                if ( isPluginReady ) {
                                                    importTemplate();
                                                    return;
                                                }

                                                preparePluginAction();
                                            }
                                        },
                                        getActionLabel()
                                    )
                                )
                            )
                        );
                    } )
                ),
                remainingTemplates > 0 || ( hasMore && filteredTemplates.length > 0 ) || loadingMore ? el(
                    'div',
                    { className: 'ansar-tl-infinite-scroll-sentinel', ref: sentinelRef },
                    el( 'span', null, loadingMore || hasMore ? __( 'Loading more templates...', 'ansar-import' ) : '' )
                ) : null
            ),
            previewUrl ? el(
                'div',
                { className: 'ansar-tl-model-main ansar-tl-model-open' },
                el(
                    'div',
                    { className: 'ansar-tl-model-inner' },
                    el(
                        'div',
                        { className: 'ansar-tl-model-header' },
                        el(
                            'div',
                            { className: 'ansar-tl-responsive-controls' },
                            responsiveButton( 'Desktop', 'dashicons-desktop', '100%' ),
                            responsiveButton( 'Tablet', 'dashicons-tablet', '768px' ),
                            responsiveButton( 'Mobile', 'dashicons-smartphone', '375px' )
                        ),
                        el(
                            'button',
                            {
                                type: 'button',
                                className: 'ansar-tl-preview-close-btn',
                                onClick: function() {
                                    setPreviewUrl( null );
                                }
                            },
                            'x'
                        )
                    ),
                    el(
                        'div',
                        { className: 'ansar-tl-model-wrap' },
                        iframeLoading ? el( 'div', { className: 'ansar-tl-loader' } ) : null,
                        el(
                            'div',
                            { className: 'ansar-tl-popup-content-wrap' },
                            el(
                                'iframe',
                                {
                                    className: 'ansar-tl-popup-iframe',
                                    src: previewUrl,
                                    title: __( 'Template preview', 'ansar-import' ),
                                    style: { width: previewWidth, height: '90vh', border: 'none' },
                                    onLoad: function() {
                                        setIframeLoading( false );
                                    }
                                }
                            )
                        )
                    )
                ),
                el(
                    'div',
                    {
                        className: 'ansar-tl-bg-overlay',
                        onClick: function() {
                            setPreviewUrl( null );
                        }
                    }
                )
            ) : null
        );
    }

    function TemplateLibrary() {
        var modalState = useState( false );
        var isModalOpen = modalState[0];
        var setIsModalOpen = modalState[1];
        var tabState = useState( 'sections' );
        var activeTab = tabState[0];
        var setActiveTab = tabState[1];
        var typeState = useState( 'freepro' );
        var templateType = typeState[0];
        var setTemplateType = typeState[1];
        var searchState = useState( '' );
        var templateSearch = searchState[0];
        var setTemplateSearch = searchState[1];
        var searchInputState = useState( '' );
        var searchInput = searchInputState[0];
        var setSearchInput = searchInputState[1];
        var cacheVersionState = useState( 0 );
        var cacheVersion = cacheVersionState[0];
        var setCacheVersion = cacheVersionState[1];
        var refreshingState = useState( false );
        var isRefreshing = refreshingState[0];
        var setIsRefreshing = refreshingState[1];
        var favoritesState = useState( readFavoriteTemplates );
        var favorites = favoritesState[0];
        var setFavorites = favoritesState[1];
        var favoriteCount = useMemo( function() {
            return Object.keys( favorites || {} ).filter( function( id ) {
                return favorites[ id ];
            } ).length;
        }, [ favorites ] );
        var pluginStatusState = useState( data.plugins || {} );
        var pluginStatus = pluginStatusState[0];
        var setPluginStatus = pluginStatusState[1];

        useEffect( function() {
            var observer;
            var timeout;
            var rafId = null;
            var button;

            function addTemplateButton() {
                rafId = null;
                if ( document.getElementById( 'ansar-template-library-btn' ) ) {
                    return;
                }

                var toolbar = document.querySelector( '.edit-post-header__toolbar' ) ||
                    document.querySelector( '.editor-header__toolbar' ) ||
                    document.querySelector( '.edit-site-header-toolbar' );

                if ( ! toolbar ) {
                    return;
                }

                var menuSvg = '<svg class="ansar-tlb-svg" width="16" height="16" viewBox="0 0 22 22" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">' +
                    '<rect width="22" height="22" rx="11" fill="url(#ansar_tlb_paint0_linear)"/>' +
                    '<path d="M18.132 13.2823C18.3581 13.3743 18.5059 13.594 18.5059 13.8381L18.506 15.4488C18.506 15.8766 18.071 16.167 17.6759 16.003L4.55032 10.5534C4.32636 10.4604 4.18039 10.2418 4.18039 9.99929L4.18039 8.49983C4.18039 8.07386 4.61187 7.78355 5.00645 7.94403L18.132 13.2823Z" fill="#ffffff"/>' +
                    '<g filter="url(#ansar_tlb_filter0_d)">' +
                    '<path d="M12.2326 3.56802C12.1393 3.34476 11.921 3.19941 11.679 3.19941L10.1098 3.19939C9.68133 3.19939 9.39094 3.63548 9.55616 4.03077L15.0517 17.1791C15.145 17.4024 15.3633 17.5477 15.6053 17.5477L17.1745 17.5477C17.6029 17.5477 17.8933 17.1116 17.7281 16.7163L12.2326 3.56802Z" fill="#ffffff"/>' +
                    '</g>' +
                    '<g filter="url(#ansar_tlb_filter1_d)">' +
                    '<path d="M9.05115 3.56277C9.1464 3.34373 9.36251 3.20205 9.60136 3.20205L11.1626 3.20204C11.5948 3.20203 11.8852 3.64505 11.7129 4.04133L5.99484 17.1896C5.89959 17.4087 5.68348 17.5504 5.44463 17.5504L3.88338 17.5504C3.45125 17.5504 3.16082 17.1073 3.33315 16.7111L9.05115 3.56277Z" fill="#ffffff"/>' +
                    '</g>' +
                    '<defs>' +
                    '<filter id="ansar_tlb_filter0_d" x="8.50928" y="2.19922" width="12.2661" height="18.3486" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">' +
                    '<feFlood flood-opacity="0" result="BackgroundImageFix"/>' +
                    '<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>' +
                    '<feOffset dx="1" dy="1"/>' +
                    '<feGaussianBlur stdDeviation="1"/>' +
                    '<feComposite in2="hardAlpha" operator="out"/>' +
                    '<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.3 0"/>' +
                    '<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>' +
                    '<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>' +
                    '</filter>' +
                    '<filter id="ansar_tlb_filter1_d" x="2.28271" y="2.20215" width="12.4805" height="18.3486" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">' +
                    '<feFlood flood-opacity="0" result="BackgroundImageFix"/>' +
                    '<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>' +
                    '<feOffset dx="1" dy="1"/>' +
                    '<feGaussianBlur stdDeviation="1"/>' +
                    '<feComposite in2="hardAlpha" operator="out"/>' +
                    '<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.3 0"/>' +
                    '<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>' +
                    '<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>' +
                    '</filter>' +
                    '<linearGradient id="ansar_tlb_paint0_linear" x1="22" y1="11" x2="0" y2="11" gradientUnits="userSpaceOnUse">' +
                    '<stop stop-color="#0F5938"/>' +
                    '<stop offset="1" stop-color="#3FA34D"/>' +
                    '</linearGradient>' +
                    '</defs>' +
                    '</svg>';

                button = document.createElement( 'button' );
                button.id = 'ansar-template-library-btn';
                button.type = 'button';
                button.className = 'components-button is-secondary ansar-template-library-btn';
                button.setAttribute( 'aria-label', __( 'Open Template Library', 'ansar-import' ) );
                button.innerHTML = '<span class="ansar-tlb-icon" aria-hidden="true">' + menuSvg + '</span><span class="ansar-tlb-text">' + __( 'Template Library', 'ansar-import' ) + '</span>';
                button.style.marginLeft = '8px';
                button.addEventListener( 'click', function() {
                    setIsModalOpen( true );
                } );

                if ( toolbar.firstChild ) {
                    toolbar.insertBefore( button, toolbar.firstChild.nextSibling );
                } else {
                    toolbar.appendChild( button );
                }
            }

            observer = new window.MutationObserver( function() {
                if ( document.getElementById( 'ansar-template-library-btn' ) || rafId ) {
                    return;
                }
                rafId = window.requestAnimationFrame( addTemplateButton );
            } );
            observer.observe( document.body, { childList: true, subtree: true } );
            timeout = window.setTimeout( addTemplateButton, 500 );

            return function() {
                window.clearTimeout( timeout );
                if ( rafId ) {
                    window.cancelAnimationFrame( rafId );
                }
                observer.disconnect();

                if ( button && button.parentNode ) {
                    button.parentNode.removeChild( button );
                }
            };
        }, [] );

        useEffect( function() {
            var timeout = window.setTimeout( function() {
                setTemplateSearch( searchInput.trim() );
            }, searchDelay );

            return function() {
                window.clearTimeout( timeout );
            };
        }, [ searchInput ] );

        function insertTemplate( content ) {
            var blocks = wp.blocks.parse( content );

            if ( blocks.length ) {
                wp.data.dispatch( 'core/block-editor' ).insertBlocks( blocks );
                setIsModalOpen( false );
            }
        }

        function toggleFavorite( templateId ) {
            setFavorites( function( currentFavorites ) {
                var nextFavorites = Object.assign( {}, currentFavorites );

                if ( nextFavorites[ templateId ] ) {
                    delete nextFavorites[ templateId ];
                } else {
                    nextFavorites[ templateId ] = true;
                }

                writeFavoriteTemplates( nextFavorites );

                return nextFavorites;
            } );
        }

        function preparePlugin( pluginType ) {
            var formData = new window.FormData();

            setPluginStatus( function( currentStatus ) {
                var nextStatus = Object.assign( {}, currentStatus );

                nextStatus[ pluginType ] = Object.assign( {}, nextStatus[ pluginType ] || {}, {
                    loading: true
                } );

                return nextStatus;
            } );

            formData.append( 'action', 'ansar_template_library_prepare_plugin' );
            formData.append( 'nonce', data.nonce || '' );
            formData.append( 'plugin_type', pluginType );

            return window.fetch(
                data.ajaxUrl || window.ajaxurl,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                }
            )
                .then( function( response ) {
                    if ( ! response.ok ) {
                        throw new Error( __( 'Plugin request failed.', 'ansar-import' ) );
                    }

                    return response.json();
                } )
                .then( function( response ) {
                    var status = response.data || {};

                    if ( ! response.success ) {
                        throw new Error( status.message || __( 'Unable to prepare required plugin.', 'ansar-import' ) );
                    }

                    setPluginStatus( function( currentStatus ) {
                        var nextStatus = Object.assign( {}, currentStatus );

                        nextStatus[ pluginType ] = Object.assign( {}, status, {
                            loading: false
                        } );

                        return nextStatus;
                    } );

                    return status;
                } )
                .catch( function( error ) {
                    setPluginStatus( function( currentStatus ) {
                        var nextStatus = Object.assign( {}, currentStatus );

                        nextStatus[ pluginType ] = Object.assign( {}, nextStatus[ pluginType ] || {}, {
                            loading: false
                        } );

                        return nextStatus;
                    } );

                    window.alert( error.message || __( 'Unable to prepare required plugin.', 'ansar-import' ) );

                    return null;
                } );
        }

        return el(
            Fragment,
            null,
            isModalOpen ? el(
                Modal,
                {
                    title: __( 'Template Library', 'ansar-import' ),
                    onRequestClose: function() {
                        setIsModalOpen( false );
                    },
                    shouldCloseOnClickOutside: false,
                    className: 'ansar-template-library-fullscreen',
                    __experimentalHideHeader: true
                },
                el(
                    'div',
                    { className: 'ansar-template-library-content' },
                    el(
                        'div',
                        { className: 'ansar-tl-header' },
                        el(
                            'div',
                            { className: 'ansar-tl-header-left' },
                            el(
                                'div',
                                { className: 'ansar-tl-logo' },
                                data.logoUrl ? el( 'img', { src: data.logoUrl, alt: '' } ) : null
                            ),
                            el( 'h1', { className: 'ansar-tl-title' }, __( 'Template Library', 'ansar-import' ) )
                        ),
                        el(
                            'nav',
                            { className: 'ansar-tl-nav-menu', 'aria-label': __( 'Template library', 'ansar-import' ) },
                            [ 'sections', 'pages' ].map( function( tab ) {
                                return el(
                                    'button',
                                    {
                                        key: tab,
                                        type: 'button',
                                        className: 'ansar-tl-tab ' + ( activeTab === tab ? 'ansar-tl-is-active' : '' ),
                                        onClick: function() {
                                            setActiveTab( tab );
                                            setTemplateType( 'freepro' );
                                        }
                                    },
                                    el( 'span', { className: 'dashicons ' + ( tab === 'sections' ? 'dashicons-layout' : 'dashicons-media-document' ), 'aria-hidden': true } ),
                                    tab === 'sections' ? __( 'Patterns', 'ansar-import' ) : __( 'Pages', 'ansar-import' )
                                );
                            } ),
                            el(
                                'div',
                                { className: 'ansar-tl-type-filter', role: 'group', 'aria-label': __( 'Template type', 'ansar-import' ) },
                                [
                                    [ 'freepro', __( 'Free/Pro', 'ansar-import' ) ],
                                    [ 'free', __( 'Free', 'ansar-import' ) ],
                                    [ 'pro', __( 'Pro', 'ansar-import' ) ],
                                    [ 'favorites', __( 'Favorites', 'ansar-import' ) ]
                                ].map( function( item ) {
                                    var isFav = item[0] === 'favorites';
                                    return el(
                                        'button',
                                        {
                                            key: item[0],
                                            type: 'button',
                                            className: 'ansar-tl-type-filter-btn' + ( templateType === item[0] ? ' ansar-tl-is-active' : '' ) + ( isFav ? ' ansar-tl-type-filter-fav' : '' ),
                                            'aria-pressed': templateType === item[0],
                                            onClick: function() {
                                                setTemplateType( item[0] );
                                            }
                                        },
                                        isFav ? el( 'span', { className: 'dashicons dashicons-heart', 'aria-hidden': true } ) : null,
                                        item[1],
                                        isFav && favoriteCount > 0 ? el( 'span', { className: 'ansar-tl-fav-count-pill' }, favoriteCount ) : null
                                    );
                                } )
                            )
                        ),
                        el(
                            'div',
                            { className: 'ansar-tl-header-right' },
                            el(
                                'a',
                                {
                                    className: 'ansar-tl-header-action-icon',
                                    href: data.helpUrl || '#',
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    title: __( 'Help Center', 'ansar-import' ),
                                    'aria-label': __( 'Help Center', 'ansar-import' )
                                },
                                el( 'span', { className: 'dashicons dashicons-sos', 'aria-hidden': true } )
                            ),
                            el(
                                'a',
                                {
                                    className: 'ansar-tl-header-action-icon',
                                    href: data.supportUrl || '#',
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    title: __( 'Contact Support', 'ansar-import' ),
                                    'aria-label': __( 'Contact Support', 'ansar-import' )
                                },
                                el( 'span', { className: 'dashicons dashicons-format-chat', 'aria-hidden': true } )
                            ),
                            el(
                                'a',
                                {
                                    className: 'ansar-tl-header-action-icon',
                                    href: data.tutorialsUrl || '#',
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    title: __( 'Video Tutorials', 'ansar-import' ),
                                    'aria-label': __( 'Video Tutorials', 'ansar-import' )
                                },
                                el( 'span', { className: 'dashicons dashicons-video-alt3', 'aria-hidden': true } )
                            ),
	                            el(
	                                'button',
	                                {
	                                    type: 'button',
	                                    className: 'ansar-tl-cache-btn' + ( isRefreshing ? ' ansar-tl-is-refreshing' : '' ),
	                                    title: isRefreshing ? __( 'Refreshing templates', 'ansar-import' ) : __( 'Refresh template cache', 'ansar-import' ),
	                                    'aria-label': isRefreshing ? __( 'Refreshing templates', 'ansar-import' ) : __( 'Refresh template cache', 'ansar-import' ),
	                                    'aria-busy': isRefreshing,
	                                    disabled: isRefreshing,
	                                    onClick: function() {
                                            setIsRefreshing( true );
	                                        clearTemplateCache();
	                                        setCacheVersion( function( value ) {
	                                            return value + 1;
	                                        } );
                                            window.setTimeout( function() {
                                                setIsRefreshing( false );
                                            }, 900 );
	                                    }
	                                },
                                el( 'span', { className: 'dashicons dashicons-update', 'aria-hidden': true } )
                            ),
                            el(
                                'input',
                                {
                                    type: 'search',
                                    className: 'ansar-tl-search',
                                    value: searchInput,
                                    onChange: function( event ) {
                                        setSearchInput( event.target.value );
                                    },
                                    placeholder: __( 'Search layouts, patterns, widgets...', 'ansar-import' )
                                }
                            ),
                            el(
                                'button',
                                {
                                    type: 'button',
                                    className: 'ansar-tl-close-btn',
                                    onClick: function() {
                                        setIsModalOpen( false );
                                    }
                                },
                                'x'
                            )
                        )
                    ),
                    el(
                        'div',
                        { className: 'ansar-tl-container' },
                        el(
                            TemplateGrid,
                            {
                                type: activeTab,
                                templateType: templateType,
                                templateSearch: templateSearch,
                                cacheVersion: cacheVersion,
                                favorites: favorites,
                                pluginStatus: pluginStatus,
                                onToggleFavorite: toggleFavorite,
                                onPreparePlugin: preparePlugin,
                                onInsert: insertTemplate
                            }
                        )
                    )
                )
            ) : null
        );
    }

    registerPlugin( 'ansar-template-library', {
        render: TemplateLibrary
    } );
}( window.wp ) );
