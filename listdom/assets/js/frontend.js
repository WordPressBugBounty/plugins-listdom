// Requests Object for Skins
let listdomPageHistoryCache = window.location.href;

function lsdNormalizeQueryKeys(keys) {
    if (Array.isArray(keys)) return keys.filter(key => typeof key === 'string' && key.length);
    if (typeof keys === 'string' && keys.length) return [keys];

    return [];
}

function lsdClearQueryKeys(searchParams, keys) {
    lsdNormalizeQueryKeys(keys).forEach(function (key) {
        searchParams.delete(key);
    });
}

// Listdom PAGE HISTORY PLUGIN
function ListdomPageHistory() {
    this.push = function (url, update = true, clearKeys = []) {
        listdomPageHistoryCache = this.apply(listdomPageHistoryCache, url, clearKeys);

        if (update) {
            try {
                history.pushState(
                    {search: 1},
                    "Search Results",
                    listdomPageHistoryCache
                );
            } catch (err) {
            }
        }
    };

    this.apply = function (source_qs, new_qs, clearKeys = []) {
        source_qs = decodeURI(source_qs);
        new_qs = decodeURI(new_qs);

        if (new_qs.substring(0, 1) === "?") new_qs = new_qs.substring(1);
        let url = new URL(source_qs);
        let new_qs_sp = new URLSearchParams(new_qs);

        lsdClearQueryKeys(url.searchParams, clearKeys);

        new_qs_sp.forEach(function (value, key) {
            url.searchParams.delete(key);
        });

        new_qs_sp.forEach(function (value, key) {
            url.searchParams.append(key, value);
        });

        return url.toString();
    };
}

function lsdShouldUpdateAddressBar(id) {
    if (
        typeof lsd_update_page_address !== "undefined" &&
        typeof lsd_update_page_address[id] !== "undefined"
    )
        return lsd_update_page_address[id];
    return true;
}

function lsdResetTimelineCarousel($skin) {
    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;
    const $timeline = ($skin && $skin.length) ? $skin.find('.lsd-timeline-items') : $();

    if (!$timeline.length) return;

    const hasOwlCarousel = typeof $.fn !== 'undefined' && typeof $.fn.owlCarousel === 'function';
    if (hasOwlCarousel && $timeline.hasClass('owl-loaded')) {
        $timeline.trigger('destroy.owl.carousel');
    }

    $timeline.removeClass('owl-loaded owl-hidden owl-drag owl-grab');

    const $stageOuter = $timeline.children('.owl-stage-outer');
    if ($stageOuter.length) $stageOuter.children().unwrap();

    const $stage = $timeline.children('.owl-stage');
    if ($stage.length) $stage.children().unwrap();
}

// Requests Object for Skins
let listdomRequests = {};

// Listdom REQUEST PLUGIN
function ListdomRequest(id, settings) {
    this.id = id;
    this.settings = settings;

    this.get = function (request, atts, clearKeys = []) {
        // Get Cached Request
        let cached = listdomRequests[this.id];
        if (!cached) cached = "?";

        if (request === "") {
            let url = new URL(window.location.href);
            request = url.search ? url.search.substring(1) : "";
        }

        // Render new Request
        let newParameters = atts + "&" + request;
        let rendered = this.apply(cached, newParameters, clearKeys);

        // Push to Object
        listdomRequests[this.id] = rendered;

        // Return Rendered Parameters
        return rendered;
    };

    this.apply = function (source_qs, new_qs, clearKeys = []) {
        source_qs = decodeURI(source_qs);
        new_qs = decodeURI(new_qs);

        let source_qs_sp = new URLSearchParams(source_qs);
        let new_qs_sp = new URLSearchParams(new_qs);

        lsdClearQueryKeys(source_qs_sp, clearKeys);

        // Remove New Keys from Source Query String
        new_qs_sp.forEach(function (value, key) {
            source_qs_sp.delete(key);
        });

        // Add New Query Strings
        new_qs_sp.forEach(function (value, key) {
            source_qs_sp.append(key, value);
        });

        return source_qs_sp.toString();
    };
}

// Skin Maps
let listdomSkinMaps = {};

// Listdom MAPS PLUGIN
function ListdomMaps(id) {
    this.id = id;

    this.set = function (map) {
        // Push to Object
        listdomSkinMaps[this.id] = map;
    };

    this.get = function () {
        // Get Map
        if (typeof listdomSkinMaps[this.id] !== "undefined")
            return listdomSkinMaps[this.id];
        return false;
    };

    this.load = function (objects) {
        // Get Map
        let map = this.get();

        // Map Not Found
        if (!map) return false;

        map.load(objects);
    };

    this.openListing = function (listingId) {
        let map = this.get();

        if (!map || typeof map.openListing !== "function") return false;

        return map.openListing(listingId);
    };
}

// Listdom DETAILS PLUGIN
function ListdomDetails(id, link, settings) {
    this.id = id;
    this.link = link;
    this.settings = settings;
    this.body = jQuery('body');

    this.lightbox = function () {
        jQuery.featherlight({
            iframe: this.link,
            iframeWidth: 1200,
            iframeHeight: jQuery(window).height() * 0.9,
        });
    };

    this.get = function (selector) {
        return jQuery(selector);
    }

    this.getOverlay = function () {
        // Overlay
        let $overlay = this.get('.lsd-panel-overlay');

        if (!$overlay.length) {
            this.body.append('<div class="lsd-panel-overlay"></div>');

            // Overlay
            $overlay = this.get('.lsd-panel-overlay');
        }

        return $overlay;
    }

    this.getPanel = function (name) {
        // Panel
        let $panel = this.get('.' + name);
        let created = false;

        if (!$panel.length) {
            this.body.append('<div class="lsd-panel ' + name + '"></div>');

            // Panel
            $panel = this.get('.' + name);
            created = true;
        }

        return [$panel, created];
    }

    this.panel = function ($panel, created) {
        // Add Iframe
        $panel.html(`
            <div class="lsd-panel-close"><i class="lsd-icon fa fa-window-close"></i></div>
            <iframe src="${this.link}"></iframe>
        `);

        // Open Panel
        if (created) setTimeout(() => $panel.addClass('lsd-panel-open'), 10);
        else $panel.addClass('lsd-panel-open');

        // Overlay
        const $overlay = this.getOverlay();
        $overlay.addClass('lsd-active');

        // Not Scrollable
        this.body.addClass('lsd-not-scrollable');

        // Close Panel by Icon
        jQuery('.lsd-panel-close i').on('click', () => this.closePanel());

        // Close Panel by Overlay
        $overlay.off('click').on('click', () => this.closePanel());

        // Close Panel by Key
        jQuery(document).off('keydown').on('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closePanel();
            }
        });
    }

    this.rightPanel = function () {
        // Panel
        let [$panel, created] = this.getPanel('lsd-right-panel');

        this.panel($panel, created);
    };

    this.leftPanel = function () {
        // Panel
        let [$panel, created] = this.getPanel('lsd-left-panel');

        this.panel($panel, created);
    };

    this.bottomPanel = function () {
        // Panel
        let [$panel, created] = this.getPanel('lsd-bottom-panel');

        this.panel($panel, created);
    };

    this.closePanel = function () {
        // Overlay
        const $overlay = this.getOverlay();
        $overlay.removeClass('lsd-active');

        // Panel
        const $panel = this.get('.lsd-panel');
        $panel.removeClass('lsd-panel-open');

        // Scrollable
        this.body.removeClass('lsd-not-scrollable');
    }
}

// Listdom LIST SKIN PLUGIN
(function ($) {
    $.fn.listdomListSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        // Set the listener
        setListeners();

        function setListeners() {
            // Load More
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find($(":selected")).data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_list_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $list_wrapper.removeClass('lsd-loading');
                        $button.removeClass("lsd-load-more-loading");
                        $wrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Loading Classes
                        $button
                            .removeClass("lsd-util-hide")
                            .removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Loading Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_list_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom GRID SKIN PLUGIN
(function ($) {
    $.fn.listdomGridSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        // Set the listeners
        setListeners();

        function setListeners() {
            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li"
                    ).removeClass("lsd-active");
                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i"
                    ).remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find(":selected").data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_grid_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $list_wrapper.removeClass('lsd-loading');
                        $button.removeClass("lsd-load-more-loading");
                        $wrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Loading Classes
                        $button
                            .removeClass("lsd-util-hide")
                            .removeClass("lsd-load-more-loading");

                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Loading Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_grid_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper").removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass("lsd-util-hide");

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom TABLE SKIN PLUGIN
(function ($) {
    $.fn.listdomTableSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        let columnsConfig = settings.columns || null;
        if (columnsConfig && typeof columnsConfig === 'string') {
            try {
                columnsConfig = JSON.parse(columnsConfig);
            } catch (e) {
                columnsConfig = null;
            }
        }

        let currentDevice = null;
        let resizeTimer = null;

        // Set the listeners
        setListeners();

        applyColumns(true);

        $(window).on('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                applyColumns();
            }, 150);
        });

        function setListeners() {
            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li"
                    ).removeClass("lsd-active");
                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i"
                    ).remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find(":selected").data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function detectDevice() {
            const width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            if (width <= 568) return 'mobile';
            if (width <= 992) return 'tablet';
            return 'desktop';
        }

        function applyColumns(force = false) {
            if (!columnsConfig || !columnsConfig.desktop) return;

            const device = detectDevice();
            if (!force && device === currentDevice) return;
            currentDevice = device;

            const baseConfig = columnsConfig.desktop || { columns: [] };
            const baseColumns = Array.isArray(baseConfig.columns) ? baseConfig.columns : [];

            let deviceConfig = columnsConfig[device] || {};
            const inherit = device !== 'desktop' && (typeof deviceConfig.inherit === 'undefined' || parseInt(deviceConfig.inherit));
            if (inherit) deviceConfig = baseConfig;

            const deviceColumns = Array.isArray(deviceConfig.columns) && deviceConfig.columns.length
                ? deviceConfig.columns
                : baseColumns;

            const baseLabels = {};
            baseColumns.forEach((col) => {
                if (!col || !col.key) return;
                if (typeof col.label !== 'undefined') baseLabels[col.key] = col.label;
            });

            const deviceLabels = {};
            deviceColumns.forEach((col) => {
                if (!col || !col.key) return;
                if (typeof col.label !== 'undefined') deviceLabels[col.key] = col.label;
            });

            const unionKeys = [];
            const addKeys = (columns) => {
                columns.forEach((col) => {
                    if (!col || !col.key) return;
                    if (!unionKeys.includes(col.key)) unionKeys.push(col.key);
                });
            };

            addKeys(baseColumns);
            addKeys(deviceColumns);

            const baseEnabled = {};
            const baseWidth = {};
            baseColumns.forEach((col) => {
                if (!col || !col.key) return;
                baseEnabled[col.key] = parseInt(col.enabled) ? 1 : 0;
                if (typeof col.width !== 'undefined' && col.width !== '') {
                    baseWidth[col.key] = parseInt(col.width, 10);
                }
            });

            const deviceEnabled = {};
            const deviceWidth = {};
            deviceColumns.forEach((col) => {
                if (!col || !col.key) return;
                if (typeof col.enabled !== 'undefined' && col.enabled !== '') deviceEnabled[col.key] = parseInt(col.enabled) ? 1 : 0;
                if (typeof col.width !== 'undefined' && col.width !== '') deviceWidth[col.key] = parseInt(col.width, 10);
            });

            const desiredOrder = [];
            deviceColumns.forEach((col) => {
                if (!col || !col.key) return;
                if (!desiredOrder.includes(col.key)) desiredOrder.push(col.key);
            });
            unionKeys.forEach((key) => {
                if (!desiredOrder.includes(key)) desiredOrder.push(key);
            });

            const $table = $("#lsd_skin" + settings.id + " .lsd-listing-table");
            if (!$table.length) return;

            const $headRow = $table.find('thead tr').first();
            const $bodyRows = $table.find('tbody tr');

            const reorderCells = ($row, selector) => {
                const cells = {};
                $row.find(selector).each(function () {
                    const $cell = $(this);
                    const key = $cell.data('column');
                    if (!key) return;
                    cells[key] = $cell;
                });

                desiredOrder.forEach((key) => {
                    if (cells[key]) $row.append(cells[key]);
                });
            };

            reorderCells($headRow, 'th');
            $bodyRows.each(function () {
                reorderCells($(this), 'td');
            });

            $headRow.find('th').each(function () {
                const $cell = $(this);
                const key = $cell.data('column');
                if (!key) return;

                if (Object.prototype.hasOwnProperty.call(deviceLabels, key)) $cell.text(deviceLabels[key]);
                else if (Object.prototype.hasOwnProperty.call(baseLabels, key)) $cell.text(baseLabels[key]);
            });

            const updateVisibility = ($row, selector) => {
                $row.find(selector).each(function () {
                    const $cell = $(this);
                    const key = $cell.data('column');
                    if (!key) return;

                    const enabled = typeof deviceEnabled[key] !== 'undefined'
                        ? deviceEnabled[key]
                        : (device === 'desktop'
                            ? (baseEnabled[key] || 0)
                            : (typeof baseEnabled[key] !== 'undefined' ? baseEnabled[key] : 0));

                    if (enabled) $cell.removeClass('lsd-table-column-hidden');
                    else $cell.addClass('lsd-table-column-hidden');

                    let width = '';
                    if (typeof deviceWidth[key] !== 'undefined') width = deviceWidth[key];
                    else if (typeof baseWidth[key] !== 'undefined') width = baseWidth[key];

                    width = parseInt(width, 10);
                    if (!width || width <= 0) width = 150;

                    $cell.css('width', width + 'px');
                });
            };

            updateVisibility($headRow, 'th');
            $bodyRows.each(function () {
                updateVisibility($(this), 'td');
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_table_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $button.removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');
                        $wrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Button Classes
                        $button
                            .removeClass("lsd-util-hide")
                            .removeClass("lsd-load-more-loading");

                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                        applyColumns(true);
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_table_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $(
                            "#lsd_skin" + settings.id + " .lsd-listing-wrapper"
                        ).html(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                        applyColumns(true);
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom Accordion SKIN PLUGIN
(function ($) {
    $.fn.listdomAccordionSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        // Set the listener
        setListeners();

        function setListeners() {
            // Load More
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find($(":selected")).data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });

            $("#lsd_skin" + settings.id + " .lsd-accordion-body").hide();

            $(document)
            .off('click.listdomAccordion' + settings.id, "#lsd_skin" + settings.id + ' .lsd-accordion-header')
            .on('click.listdomAccordion' + settings.id, "#lsd_skin" + settings.id + ' .lsd-accordion-header', function() {
                const $header = $(this);
                const $body = $header.next('.lsd-accordion-body');

                $('.lsd-accordion-body').not($body).slideUp();
                $('.lsd-accordion-header').not($header).find('.accordion-arrow').removeClass('rotated');

                // Toggle current body and initialize slider when shown
                $body.stop(true, true).slideToggle(400, function() {
                    if ($body.is(':visible') && typeof listdom_image_slider === 'function') {
                        listdom_image_slider();
                    }
                });

                $header.find('.accordion-arrow').toggleClass('rotated');
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_accordion_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $list_wrapper.removeClass('lsd-loading');
                        $button.removeClass("lsd-load-more-loading");
                        $wrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Loading Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Loading Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_accordion_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom GALLERY SKIN PLUGIN
(function ($) {
    $.fn.listdomGallerySkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
                masonry: 0,
                rtl: false,
                duration: 400,
            },
            options
        );

        const $wrapper = $("#lsd_skin" + settings.id);
        let masonry = null;
        if (parseInt(settings.masonry)) {
            masonry = $("#lsd_skin" + settings.id + " .lsd-listing-wrapper");
        }

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        setListeners();
        listdom_enable_listing_container_clicks($wrapper);

        function setListeners() {
            if (masonry) {
                masonry.isotope({
                    filter: "*",
                    transitionDuration: settings.duration,
                    originLeft: !settings.rtl,
                    masonry: {
                        gutter: 0
                    }
                });
            }

            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on("click", function () {
                    loadMore();
                });
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on("click", function () {
                let $option = $(this);
                let orderby = $option.data("orderby");
                let order = $option.data("order");

                $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                $option.addClass("lsd-active");

                if (order === "DESC") {
                    $option.data("order", "ASC");
                    $option.append('<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>');
                } else {
                    $option.data("order", "DESC");
                    $option.append('<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>');
                }

                sort(orderby, order);
            });

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on("change", function () {
                let $select = $(this);
                let orderby = $select.val();
                let order = $select.find(":selected").data("order");

                sort(orderby, order);
            });

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page) {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev')) {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $('.lsd-list-wrapper').addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function loadMore(append = true, page = null) {
            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $loadMoreWrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_gallery_load_more&" + req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Button Classes
                        $button.removeClass("lsd-load-more-loading");
                        $loadMoreWrapper.addClass("lsd-util-hide");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');
                    } else {
                        // Adjust Button Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        listdom_enable_listing_container_clicks($wrapper);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $button.removeClass("lsd-load-more-loading");
                            $loadMoreWrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();

                        if (masonry) masonry.isotope('reloadItems').isotope();
                    }
                },
                error: function () {
                    $('.lsd-list-wrapper').removeClass('lsd-loading');
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $('.lsd-list-wrapper').addClass('lsd-loading');

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_gallery_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    ) +
                    "&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);

                    // Update Pagination
                    $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                    // Update the Next Page
                    $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                    // Release Lock of Infinite Scroll
                    settings.infinite_locked = false;

                    // Loading Style
                    $('.lsd-list-wrapper').removeClass('lsd-loading');

                    // Trigger
                    listdom_onload();

                    if (masonry) masonry.isotope('reloadItems').isotope();
                },
                error: function () {
                    $('.lsd-list-wrapper').removeClass('lsd-loading');
                },
            });
        }
    };
})(jQuery);

// Listdom Timeline SKIN PLUGIN
(function ($) {
    $.fn.listdomTimelineSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                horizontal: 0,
                autoplay: 0,
                columns: 1,
                vertical_alignment: "zigzag",
                horizontal_alignment: "zigzag",
                style: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $wrapper.find('.lsd-list-wrapper');
        let $items_wrapper = $wrapper.find('.lsd-timeline-items');
        const isHorizontal = parseInt(settings.horizontal) === 1;
        const autoplay = parseInt(settings.autoplay) === 1;
        const parsedColumns = parseInt(settings.columns, 10);
        const parsedLimit = parseInt(settings.limit, 10);
        const normalizedLimit = Math.max(1, isNaN(parsedLimit) ? 1 : parsedLimit);
        const columns = Math.max(1, isNaN(parsedColumns) ? normalizedLimit : parsedColumns);
        settings.limit = normalizedLimit;
        const horizontalAlignment = (settings.horizontal_alignment || 'zigzag').toString().toLowerCase();

        // Set the listener
        setListeners();
        rebuildHorizontalCarousel(true);
        if (isHorizontal) {
            $(window).on('resize orientationchange', function () {
                window.requestAnimationFrame(syncHorizontalTimeline);
            });
        }
        listdom_image_slider();

        $(window).on('lsd-search-success', function (event) {
            const detail = event.originalEvent ? event.originalEvent.detail : event.detail;
            if (!detail || parseInt(detail.shortcode) !== parseInt(settings.id)) return;

            rebuildHorizontalCarousel(true);
        });

        $(document).on('listdom:onload', function () {
            rebuildHorizontalCarousel();
        });

        function setListeners() {
            // Load More
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find($(":selected")).data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function setItemsWrapper() {
            const $currentWrapper = $wrapper.find('.lsd-timeline-items');
            if ($currentWrapper.length) $items_wrapper = $currentWrapper;
            return $items_wrapper;
        }

        function rebuildHorizontalCarousel(forceSync = false) {
            const $currentWrapper = setItemsWrapper();
            if (!isHorizontal || !$items_wrapper.length) return;

            const needsInit = !$items_wrapper.hasClass('owl-loaded');
            if (needsInit) initCarousel();

            if (!needsInit || forceSync) {
                window.requestAnimationFrame(syncHorizontalTimeline);
            }
        }

        function syncHorizontalTimeline() {
            const $currentWrapper = setItemsWrapper();
            if (!isHorizontal || !$items_wrapper.length) return;

            const alignmentClasses = [
                'lsd-timeline-align-top',
                'lsd-timeline-align-bottom',
                'lsd-timeline-align-zigzag',
            ];

            $items_wrapper.removeClass(alignmentClasses.join(' '));

            let wrapperAlignment = 'lsd-timeline-align-zigzag';
            if (horizontalAlignment === 'top') wrapperAlignment = 'lsd-timeline-align-top';
            else if (horizontalAlignment === 'bottom') wrapperAlignment = 'lsd-timeline-align-bottom';

            $items_wrapper.addClass(wrapperAlignment);
            updateHorizontalPadding();
        }

        function updateHorizontalPadding() {
            const $currentWrapper = setItemsWrapper();
            const $stageOuter = $items_wrapper.closest('.lsd-listing-wrapper').find('.owl-stage-outer');
            if (!$stageOuter.length) return;

            const measureHeight = function (selector) {
                let maxHeight = 0;
                $items_wrapper.find(selector).each(function () {
                    const height = $(this).outerHeight(true);
                    if (!isNaN(height)) maxHeight = Math.max(maxHeight, height);
                });
                return maxHeight;
            };

            const topHeight = measureHeight('.lsd-timeline-top');
            const bottomHeight = measureHeight('.lsd-timeline-bottom');
            const buffer = 80;

            const topPadding = Math.ceil(topHeight + buffer);
            const bottomPadding = Math.ceil(bottomHeight + buffer);

            $stageOuter.css({
                'padding-top': topPadding + 'px',
                'padding-bottom': bottomPadding + 'px',
            });
        }

        function initCarousel() {
            const $currentWrapper = setItemsWrapper();
            if (!isHorizontal || !$items_wrapper.length || $items_wrapper.hasClass('owl-loaded')) return;
            if (typeof $.fn === 'undefined' || typeof $.fn.owlCarousel === 'undefined') return;

            const responsive = {
                0: { items: Math.min(columns, 1) },
                480: { items: Math.min(columns, 2) },
                768: { items: Math.min(columns, 2) },
                1024: { items: Math.min(columns, 3) },
                1280: { items: columns },
            };

            $items_wrapper.owlCarousel({
                items: columns,
                loop: false,
                autoplay: autoplay,
                autoplayHoverPause: true,
                dots: true,
                nav: false,
                margin: 10,
                responsiveClass: true,
                autoHeight: false,
                responsive: responsive,
            });

            window.requestAnimationFrame(syncHorizontalTimeline);
        }

        function renderListings(html, append = true) {
            const $content = $(html);
            listdom_mount_fragment_assets($content);
            const $items = $content
            .filter('.lsd-timeline-item')
            .add($content.find('.lsd-timeline-item'));

            setItemsWrapper();
            const hasOwl = isHorizontal && $items_wrapper.hasClass('owl-loaded');

            // Replace mode
            if (!append) {
                if (hasOwl) {
                    $items_wrapper
                    .trigger('replace.owl.carousel', [$items])
                    .trigger('refresh.owl.carousel');
                } else {
                    $items_wrapper.html($items);
                    if (isHorizontal) initCarousel();
                }

                if (isHorizontal) requestAnimationFrame(syncHorizontalTimeline);
                listdom_onload();
                return;
            }

            // Append mode
            if (hasOwl) {
                $items.each(function () {
                    $items_wrapper.trigger('add.owl.carousel', [$(this)]);
                });
                $items_wrapper.trigger('refresh.owl.carousel');
            } else {
                $items_wrapper.append($items);
                if (isHorizontal) initCarousel();
            }

            if (isHorizontal) requestAnimationFrame(syncHorizontalTimeline);
            listdom_onload();
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $loadMoreWrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_timeline_load_more&" +
                    req.get(
                        "page=" + next_page + "&limit=" + settings.limit,
                        settings.atts
                    ),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $list_wrapper.removeClass('lsd-loading');
                        $button.removeClass("lsd-load-more-loading");
                        $loadMoreWrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Loading Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');

                        console.log(response);
                        // Append Items
                        renderListings(response.html, append);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Loading Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $loadMoreWrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_timeline_sort&" +
                    req.get(
                        "orderby=" +
                            orderby +
                            "&order=" +
                            order +
                            "&limit=" +
                            settings.limit,
                        settings.atts
                    ) +
                    "&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        renderListings(response.html, false);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom Mosaic SKIN PLUGIN
(function ($) {
    $.fn.listdomMosaicSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        // Set the listener
        setListeners();

        function setListeners() {
            // Load More
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find($(":selected")).data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_mosaic_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Loading Classes
                        $list_wrapper.removeClass('lsd-loading');
                        $button.removeClass("lsd-load-more-loading");
                        $wrapper.addClass("lsd-util-hide");
                    } else {
                        // Adjust Loading Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Loading Classes
                            $list_wrapper.removeClass('lsd-loading');
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_mosaic_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom SIDE SKIN PLUGIN
(function ($) {
    $.fn.listdomSideSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                single_listing_style: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Wrapper
        const $wrapper = $("#lsd_skin" + settings.id);

        // Current Listing
        let currentListing;

        // Body
        const $body = $('body');

        // Set the listeners
        setListeners();

        function setListeners() {
            // Load More
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $wrapper.find($("#lsd_skin" + settings.id + " .lsd-side-listings")).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").removeClass("lsd-active");
                    $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i").remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append('<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>');
                    } else {
                        $option.data("order", "DESC");
                        $option.append('<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>');
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find($(":selected")).data("order");

                    sort(orderby, order);
                }
            );

            // Close Icon
            $("#lsd_skin" + settings.id + " .lsd-side-details-close").off('click').on('click', function () {
                let $details = $wrapper.find(jQuery("#lsd_skin" + settings.id + " .lsd-side-details"));

                $details.removeClass('lsd-display');
                $body.removeClass('lsd-small-not-scrollable');
            });

            // Listing Pages
            singlePages();

            // After Search
            $(window).on('lsd-search-success', () => {
                currentListing = null;
                singlePages();
            });

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $('.lsd-list-wrapper').addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function singlePages() {
            let $wrapper = $("#lsd_skin" + settings.id);

            // Single Listing
            $wrapper.find($(".lsd-listing"))
            .off('click')
            .on('click', function (e) {
                e.preventDefault();

                // Next Listing
                const next = jQuery(this).next();
                if (next.length) {
                    // Load Next Listing
                    loadSingle(next, true);

                    const twoNext = next.next();
                    if (twoNext.length) {
                        // Load Two Next Listing
                        loadSingle(twoNext, true);
                    }
                }

                // Load Next Listing
                loadSingle(jQuery(this), false);
            });

            // Load First Listing Automatically
            if (!currentListing && window.innerWidth > 1024) jQuery("#lsd_skin" + settings.id + " .lsd-listing:first").trigger('click');
        }

        function loadSingle(element, in_background) {
            if (typeof in_background === 'undefined') in_background = false;

            let $details = $wrapper.find(jQuery("#lsd_skin" + settings.id + " .lsd-side-details"));
            let $iframe_wrapper = $details.find(jQuery(".lsd-side-details-iframe"));

            // Listing ID
            const id = element.data('listing-id');

            // Iframe HTML ID
            const html_id = `lsd_${settings.id}_listing_raw_iframe_${id}`;

            // Listing URL
            let url = element.attr('href');
            if (!url) url = element.data('url');

            // Add Raw to the URL
            if (url.includes('?')) url += '&raw&lsd-side=1&lsd-style=' + settings.single_listing_style;
            else url += '?raw&lsd-side=1&lsd-style=' + settings.single_listing_style;

            let html_class = '';

            if (in_background) {
                let $iframe = jQuery(`#${html_id}`);
                if ($iframe.length) {
                    return;
                }

                html_class = 'lsd-util-hide';
            } else {
                // Don't load Current Listing Again
                if (id && currentListing === id && window.innerWidth > 1024) return;

                // Set Current Listing
                currentListing = id;

                // Hide all Iframes
                jQuery("#lsd_skin" + settings.id + " .lsd-listing-raw-iframe").addClass('lsd-util-hide');

                let $iframe = jQuery(`#${html_id}`);
                if ($iframe.length) {
                    $iframe.removeClass('lsd-util-hide');

                    // Show Details
                    $details.addClass('lsd-display');
                    $body.addClass('lsd-small-not-scrollable');

                    return;
                }
            }

            // New Content
            $iframe_wrapper.append(`<iframe
                class="lsd-listing-raw-iframe ${html_class}"
                id="${html_id}"
                src="${url}"
            ></iframe>`);

            // Show Details
            $details.addClass('lsd-display');
            $body.addClass('lsd-small-not-scrollable');
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $loadMore = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_side_load_more&" + req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Button Classes
                        $button.removeClass("lsd-load-more-loading");
                        $loadMore.addClass("lsd-util-hide");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');
                    } else {
                        // Adjust Button Classes
                        $button.removeClass("lsd-util-hide").removeClass("lsd-load-more-loading");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $button.removeClass("lsd-load-more-loading");
                            $loadMore.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Listing Pages
                        singlePages();

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                },
            });
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_side_sort&" + req.get("orderby=" + orderby + "&order=" + order, settings.atts)+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper").removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass("lsd-util-hide");

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Clear Current Listing
                        currentListing = null;

                        // Listing Pages
                        singlePages();

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom LIST + GRID SKIN PLUGIN
(function ($) {
    $.fn.listdomListGridSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
                view: "grid",
                columns: 3,
            },
            options
        );

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Set the listeners
        setListeners();

        function setListeners() {
            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            $("#lsd_skin" + settings.id + " .lsd-view-switcher-buttons li").on(
                "click",
                function () {
                    let view = $(this).data("view");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-switcher-buttons li"
                    ).removeClass("lsd-active lsd-color-m-txt");
                    $(this).addClass("lsd-active lsd-color-m-txt");

                    switchView(view);
                }
            );

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li"
                    ).removeClass("lsd-active");
                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i"
                    ).remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find(":selected").data("order");

                    sort(orderby, order);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $('.lsd-list-wrapper').addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sync
            $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request, '', clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $loadMoreWrapper = $(
                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
            );

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_listgrid_load_more&" +
                    req.get(
                        "page=" + next_page + "&view=" + $wrapper.data("view"),
                        settings.atts
                    ),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Button Classes
                        $button.removeClass("lsd-load-more-loading");
                        $loadMoreWrapper.addClass("lsd-util-hide");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');
                    } else {
                        // Adjust Button Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $button.removeClass("lsd-load-more-loading");
                            $loadMoreWrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                    $('.lsd-list-wrapper').removeClass('lsd-loading');
                },
            });
        }

        function switchView(view) {
            // Do nothing if the view is currently active
            if ($wrapper.data("view") === view) return;

            if (view === "grid") {
                $("#lsd_skin" + settings.id + " .lsd-listgrid-view-listings-wrapper")
                .removeClass("lsd-viewstyle-list")
                .addClass("lsd-viewstyle-grid");
                $("#lsd_skin" + settings.id + " .lsd-listing-wrapper > .lsd-row > div")
                .removeClass("lsd-col-12")
                .addClass("lsd-col-" + 12 / settings.columns);
            } else {
                $("#lsd_skin" + settings.id + " .lsd-listgrid-view-listings-wrapper")
                .removeClass("lsd-viewstyle-grid")
                .addClass("lsd-viewstyle-list");
                $("#lsd_skin" + settings.id + " .lsd-listing-wrapper > .lsd-row > div")
                .removeClass("lsd-col-" + 12 / settings.columns)
                .addClass("lsd-col-12");
            }

            // Update the view
            $wrapper.data("view", view);

            // Slider
            listdom_image_slider();
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);
            let view = $wrapper.data("view");

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_listgrid_sort&" +
                    req.get(
                        "orderby=" + orderby + "&order=" + order + "&view=" + view,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom HALF MAP SKIN PLUGIN
(function ($) {
    $.fn.listdomHalfMapSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                load_more: 0,
                infinite_scroll: 0,
                infinite_locked: false,
                ajax_url: "",
                next_page: 2,
                atts: "",
                limit: 300,
                nonce: "",
                view: "grid",
                columns: 3,
            },
            options
        );

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Set the listeners
        setListeners();

        function setListeners() {
            if ($(window).width() >= 768) {
                const selector =
                    "." +
                    $(".lsd-halfmap-view-map-section-wrapper")
                    .children("div")
                    .attr("class");

                const offset = $(selector).offset();
                const offset_top = offset.top;

                $(selector).css("height", $(window).height());
                $(selector + " > div").css("height", $(window).height());
                $(selector).css("top", offset_top);
                $(selector).addClass("lsd-listing-map-fixed");

                if ($(window).scrollTop() > 0) {
                    if ($(window).scrollTop() > offset_top) $(selector).css("top", 0);
                    else $(selector).css("top", $(window).scrollTop());
                }

                $(window).on("scroll", function () {
                    const scroll_top = $(window).scrollTop();

                    if (scroll_top === 0) $(selector).css("top", offset_top);
                    else if (scroll_top <= offset_top)
                        $(selector).css("top", offset_top - scroll_top);
                    else $(selector).css("top", 0);
                });
            }

            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            $("#lsd_skin" + settings.id + " .lsd-view-switcher-buttons li").on(
                "click",
                function () {
                    let view = $(this).data("view");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-switcher-buttons li"
                    ).removeClass("lsd-active lsd-color-m-txt");
                    $(this).addClass("lsd-active lsd-color-m-txt");

                    switchView(view);
                }
            );

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $('.lsd-list-wrapper').addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            // Sortbar
            $("#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li").on(
                "click",
                function () {
                    let $option = $(this);
                    let orderby = $option.data("orderby");
                    let order = $option.data("order");

                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li"
                    ).removeClass("lsd-active");
                    $(
                        "#lsd_skin" + settings.id + " .lsd-view-sortbar-wrapper li i"
                    ).remove();

                    $option.addClass("lsd-active");

                    if (order === "DESC") {
                        $option.data("order", "ASC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-down" aria-hidden="true"></i>'
                        );
                    } else {
                        $option.data("order", "DESC");
                        $option.append(
                            '<i class="lsd-icon fas fa-sort-amount-up" aria-hidden="true"></i>'
                        );
                    }

                    sort(orderby, order);
                }
            );

            // Sort Dropdown
            $("#lsd_skin" + settings.id + " .lsd-sortbar-dropdown select").on(
                "change",
                function () {
                    let $select = $(this);
                    let orderby = $select.val();
                    let order = $select.find(":selected").data("order");

                    sort(orderby, order);
                }
            );
        }

        function loadMore(append = true, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $loadMoreWrapper = $(
                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
            );

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $("#lsd_skin" + settings.id).data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_halfmap_load_more&" +
                    req.get(
                        "page=" + next_page + "&view=" + $wrapper.data("view"),
                        settings.atts
                    ),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Button Classes
                        $button.removeClass("lsd-load-more-loading");
                        $loadMoreWrapper.addClass("lsd-util-hide");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');
                    } else {
                        // Adjust Button Classes
                        $button
                        .removeClass("lsd-util-hide")
                        .removeClass("lsd-load-more-loading");
                        $('.lsd-list-wrapper').removeClass('lsd-loading');

                        // Append Items
                        if (!append) $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(response.html);
                        else $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").append(response.html);

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $button.removeClass("lsd-load-more-loading");
                            $loadMoreWrapper.addClass("lsd-util-hide");
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                    $('.lsd-list-wrapper').removeClass('lsd-loading');
                },
            });
        }

        function switchView(view) {
            // Do nothing if the view is currently active
            if ($wrapper.data("view") === view) return;

            if (view === "grid") {
                $("#lsd_skin" + settings.id + " .lsd-halfmap-view-listings-wrapper")
                .removeClass("lsd-viewstyle-list")
                .addClass("lsd-viewstyle-grid");
                $("#lsd_skin" + settings.id + " .lsd-listing-wrapper > .lsd-row > div")
                .removeClass("lsd-col-12")
                .addClass("lsd-col-" + 12 / settings.columns);
            } else {
                $("#lsd_skin" + settings.id + " .lsd-halfmap-view-listings-wrapper")
                .removeClass("lsd-viewstyle-grid")
                .addClass("lsd-viewstyle-list");
                $("#lsd_skin" + settings.id + " .lsd-listing-wrapper > .lsd-row > div")
                .removeClass("lsd-col-" + 12 / settings.columns)
                .addClass("lsd-col-12");
            }

            // Update the view
            $wrapper.data("view", view);

            if (typeof listdom_image_slider === "function") {
                listdom_image_slider();
            }
        }

        function sort(orderby, order) {
            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_halfmap_sort&" +
                    req.get(
                        "&orderby=" + orderby + "&order=" + order,
                        settings.atts
                    )+"&page=1",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        // Display New Items
                        $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                            response.html
                        );

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (parseInt(settings.load_more) && response.total > response.count) {
                            // Show Load More
                            $(
                                "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
                            ).removeClass("lsd-util-hide");
                            $("#lsd_skin" + settings.id + " .lsd-load-more").removeClass(
                                "lsd-util-hide"
                            );

                            // Update the Next Page
                            $("#lsd_skin" + settings.id).data("next-page", 2);
                        }

                        // Update Seed
                        if (typeof response.seed != "undefined" && response.seed)
                            settings.atts += "&atts[seed]=" + response.seed;

                        // Trigger
                        listdom_onload();
                    }

                    // Loading Style
                    $wrapper.fadeTo(200, 1);
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom CAROUSEL SKIN PLUGIN
(function ($) {
    $.fn.listdomCarouselSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                ajax_url: "",
                atts: "",
                nonce: "",
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                dots: true,
                nav: true,
                navText: [
                    '<i class="lsd-icon fa fa-chevron-right" aria-hidden="true"></i>',
                    '<i class="lsd-icon fa fa-chevron-left" aria-hidden="true"></i>',
                ],
                responsiveClass: false,
                responsive: {},
            },
            options
        );

        // Set the listeners
        setListeners();

        function setListeners() {
            const $carousel = $(".lsd-skin-" + settings.id + "-carousel");

            $carousel.on(
                "initialized.owl.carousel changed.owl.carousel",
                function () {
                    if (typeof listdom_image_slider === "function") listdom_image_slider();
                    if (typeof listdom_trigger_compare_modal === "function") listdom_trigger_compare_modal();
                    if (typeof listdom_trigger_compare === "function") listdom_trigger_compare();
                }
            );

            $carousel.owlCarousel({
                items: settings.items,
                loop: parseInt(settings.loop) === 1,
                autoplay: settings.autoplay,
                autoplayHoverPause: settings.autoplayHoverPause,
                dots: settings.dots,
                nav: settings.nav,
                navText: settings.navText,
                responsiveClass: settings.responsiveClass,
                responsive: settings.responsive,
            });
        }
    };
})(jQuery);

// Listdom SLIDER SKIN PLUGIN
(function ($) {
    $.fn.listdomGallerySlider = function (options) {
        // Default Options
        let settings = $.extend(
            {
                ajax_url: "",
                atts: "",
                nonce: "",
                loop: false,
                autoplay: true,
                autoplayHoverPause: true,
                dots: true,
                nav: false,
                items: 1,
                autoHeight: true
            },
            options
        );

        // Set the listeners
        setListeners();

        function setListeners() {
            // Initialize the main slider
            let mainSlider = $(".lsd-gallery-slider").owlCarousel({
                items: settings.items,
                loop: settings.loop,
                autoplay: settings.autoplay,
                autoplayHoverPause: settings.autoplayHoverPause,
                dots: settings.dots,
                nav: settings.nav,
                autoHeight: settings.autoHeight,
            });

            // Synchronization Callback
            mainSlider.on("changed.owl.carousel", function (event) {
                syncThumbnails(event.item.index);
            });

            // Sync Thumbnails
            function syncThumbnails(index) {
                $(".lsd-gallery-slider-thumbs").trigger('to.owl.carousel', [index, 300, true]);
            }
        }
    };

    $.fn.listdomGallerySliderThumbnail = function (options) {
        // Default Options
        let settings = $.extend(
            {
                loop: false,
                autoplay: false,
                autoplayHoverPause: false,
                dots: false,
                nav: true,
                mouseDrag: true,
                touchDrag: true,
                items: 4,
            },
            options
        );

        // Set the listeners
        setListeners();

        function setListeners() {
            // Initialize the thumbnail slider
            let thumbSlider = $(".lsd-gallery-slider-thumbs").owlCarousel({
                items: settings.items,
                loop: parseInt(settings.loop) === 1,
                autoplay: settings.autoplay,
                autoplayHoverPause: settings.autoplayHoverPause,
                dots: settings.dots,
                nav: settings.nav,
                stagePadding: settings.stagePadding,
                mouseDrag: settings.mouseDrag,
                touchDrag: settings.touchDrag,
            });

            // Add click event listener for thumbnails
            thumbSlider.on("click", ".owl-item", function () {
                const index = $(this).index();
                $(".lsd-gallery-slider").trigger('to.owl.carousel', [index, 300, true]);
            });

            // Add synchronization callback
            thumbSlider.on("changed.owl.carousel", function (event) {
                $(".lsd-gallery-slider").trigger('to.owl.carousel', [event.item.index, 300, true]);
            });
        }
    };
})(jQuery);

// Listdom SLIDER SKIN PLUGIN
(function ($) {
    $.fn.listdomSliderSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                ajax_url: "",
                atts: "",
                nonce: "",
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                dots: false,
                nav: true,
            },
            options
        );

        // Set the listeners
        setListeners();

        function setListeners() {
            $(".lsd-skin-" + settings.id + "-slider").owlCarousel({
                items: settings.items,
                loop: parseInt(settings.loop) === 1,
                autoplay: settings.autoplay,
                autoplayHoverPause: settings.autoplayHoverPause,
                dots: settings.dots,
                nav: settings.nav,
                navText: [
                    '<i class="lsd-icon fa fa-chevron-left" aria-hidden="true"></i>',
                    '<i class="lsd-icon fa fa-chevron-right" aria-hidden="true"></i>',
                ],
                autoHeight: true,
            });
        }
    };
})(jQuery);

// Listdom MASONRY SKIN PLUGIN
(function ($) {
    $.fn.listdomMasonrySkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                ajax_url: "",
                atts: "",
                duration: 400,
                load_more: 0,
                infinite_scroll: 0,
                next_page: 2,
                limit: 300,
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Masonry Wrapper
        let masonry = $("#lsd_skin" + settings.id + " .lsd-listing-wrapper");

        // List Wrapper
        const $list_wrapper = $('.lsd-list-wrapper');

        // Set the listeners
        setListeners();

        function bindFilterClicks() {
            $("#lsd_skin" + settings.id)
            .off("click", ".lsd-masonry-filters a")
            .on("click", ".lsd-masonry-filters a", function () {
                let e = $(this);
                let f = e.attr("data-filter");

                masonry.isotope({
                    filter: f,
                });

                if (e.hasClass("lsd-selected")) return false;

                e.closest(".lsd-masonry-filters")
                .find(".lsd-selected")
                .removeClass("lsd-selected");
                e.addClass("lsd-selected");

                return false;
            });
        }

        function setListeners() {
            masonry.isotope({
                filter: "*",
                transitionDuration: settings.duration,
                originLeft: !settings.rtl,
            });

            // Set load more listener
            if (parseInt(settings.load_more)) {
                $("#lsd_skin" + settings.id + " .lsd-load-more").on(
                    "click",
                    function () {
                        loadMore();
                    }
                );
            }

            // Infinite Scroll
            if (parseInt(settings.infinite_scroll)) {
                $(window).on("scroll", function () {
                    let $target = $("#lsd_skin" + settings.id + " .lsd-load-more");

                    let hT = $target.offset().top,
                        hH = $target.outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();

                    if (wS + 100 > hT + hH - wH && !settings.infinite_locked) {
                        settings.infinite_locked = true;
                        loadMore();
                    }
                });
            }

            // Numeric Pagination Click Event
            $(document).on("click", "#lsd_skin" + settings.id + " .lsd-numeric-pagination a", function (e) {
                e.preventDefault();

                let $button = $(this);
                let page = $button.data("page");

                if (!page)
                {
                    page = $("#lsd_skin" + settings.id).data("next-page");

                    // Prev Page
                    if ($button.hasClass('prev'))
                    {
                        page = parseInt(page) - 2;
                        if (page < 1) page = 1;
                    }
                }

                // Add loading class
                $list_wrapper.addClass('lsd-loading');

                let newUrl = new URL(window.location);
                newUrl.searchParams.set('paged', page);
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);

                loadMore(false, parseInt(page));
            });

            bindFilterClicks();

            // After Search
            $(window).on('lsd-search-success', (event) => {
                const detail = event.originalEvent ? event.originalEvent.detail : event.detail;
                if (!detail || parseInt(detail.shortcode) !== parseInt(settings.id)) return;

                masonry.isotope('reloadItems').isotope();
            });

            $('body').on('lsd-sync', function (e, data = {}) {
                const {id, request, clearKeys = []} = data;
                if (parseInt(id) !== parseInt(settings.id)) return;

                const requestString = typeof request === "string" ? request : "";
                const attsString = typeof settings.atts === "string" ? settings.atts : "";

                req.get(requestString, attsString, clearKeys);
                $("#lsd_skin" + settings.id).data("next-page", 1);
                $list_wrapper.addClass('lsd-loading');

                loadMore(false);
            });
        }

        function loadMore(append, page = null) {
            if (typeof append === 'undefined') append = true;

            // Get button and wrapper
            let $skin = $("#lsd_skin" + settings.id);
            let $button = $("#lsd_skin" + settings.id + " .lsd-load-more");
            let $wrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

            // Add loading Class
            $button.addClass("lsd-load-more-loading");

            // Next Page
            let next_page = page || $skin.data("next-page");

            let newUrl = new URL(window.location);
            newUrl.searchParams.set('paged', next_page);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_masonry_load_more&" +
                    req.get("page=" + next_page, settings.atts),
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0 && append) {
                        // Adjust Button Classes
                        $button.removeClass("lsd-load-more-loading");
                        $wrapper.addClass("lsd-util-hide");
                        $list_wrapper.removeClass('lsd-loading');
                    } else {
                        // Adjust Button Classes
                        $button
                            .removeClass("lsd-util-hide")
                            .removeClass("lsd-load-more-loading");
                        $list_wrapper.removeClass('lsd-loading');

                        // Append Items
                        if (!append) {
                            // Replace listings and filters (initial load)
                            $skin.find(".lsd-listing-wrapper").html(response.html);
                            $skin.find(".lsd-masonry-filters").replaceWith(response.filters);

                            bindFilterClicks();
                        } else {
                            // Append listings
                            $skin.find(".lsd-listing-wrapper").append(response.html);

                            // Append new filters, avoiding duplicates
                            let $existingFilters = $skin.find(".lsd-masonry-filters");
                            let $newFilters = $(response.filters).find("a[data-filter]");

                            $newFilters.each(function () {
                                let filter = $(this);
                                let filterValue = filter.attr("data-filter");
                                // Only append if this filter doesn't already exist
                                if ($existingFilters.find('a[data-filter="' + filterValue + '"]').length === 0) {
                                    $existingFilters.append(filter);
                                }
                            });
                        }

                        // Update Pagination
                        $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper").replaceWith(response.pagination);

                        if (response.total <= next_page * settings.limit) {
                            // Adjust Button Classes
                            $button.removeClass("lsd-load-more-loading");
                            $wrapper.addClass("lsd-util-hide");
                            $list_wrapper.removeClass('lsd-loading');
                        }

                        // Update the Next Page
                        $("#lsd_skin" + settings.id).data("next-page", response.next_page);

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;

                        // Trigger
                        listdom_onload();

                        masonry.isotope('reloadItems').isotope();
                    }
                },
                error: function () {
                },
            });
        }
    };
})(jQuery);

// Listdom SINGLEMAP SKIN PLUGIN
(function ($) {
    $.fn.listdomSinglemapSkin = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                sidebar: 0,
                ajax_url: "",
                atts: "",
                nonce: "",
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.atts);

        // Single Map Wrapper
        const $wrapper = $("#lsd_skin" + settings.id);

        // Sidebar Wrapper
        const $sidebar = $wrapper.find($('.lsd-map-sidebar-wrapper'));

        // Set the listeners
        setListeners();

        function setListeners() {
            $wrapper.find($('.lsd-map-sidebar-toggle')).on('click', function () {
                $sidebar.toggleClass('lsd-map-sidebar-open');
            });
        }
    };
})(jQuery);

// Listdom Leaflet PLUGIN
(function ($) {
    $.fn.listdomLeaflet = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                latitude: 0,
                longitude: 0,
                zoom: 14,
                icon: "../img/m-01.png",
                clustering: false,
                clustering_images: "",
                richmarker: "",
                objects: {},
                styles: "",
                mapcontrols: {},
                fill_color: "#1e90ff",
                fill_opacity: 0.3,
                stroke_color: "#1e74c7",
                stroke_opacity: 0.6,
                stroke_weight: 1,
                mapsearch: false,
                autoGPS: false,
                display_infowindow: true,
                infowindow_trigger: "click",
                geo_request: false,
                mousewheel_zoom: false,
                gps_zoom: {
                    zl: 13,
                    current: 7,
                },
                gps: false,
                access_token: "",
                layers: [],
            },
            options
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.args + (settings.args && settings.atts ? "&" : "") + settings.atts);

        // Load More Wrapper
        let $loadMoreWrapper = $("#lsd_skin" + settings.id + " .lsd-load-more-wrapper");

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        let mapsearchFreez = false;

        // Disable clustering if undefined
        if (settings.clustering && typeof L.markerClusterGroup === "undefined")
        {
            settings.clustering = false;
        }

        let canvas = this;
        let DOM = canvas[0];
        let updateBounds = true;

        // Map Options
        let mapOptions = {
            scrollWheelZoom: settings.mousewheel_zoom,
            dragging: !L.Browser.mobile,
        };

        // Restrict Bounds
        if (settings.max_bounds && settings.max_bounds.ne && settings.max_bounds.sw)
        {
            if (
                settings.max_bounds.ne.lat !== "" &&
                settings.max_bounds.ne.lng !== "" &&
                settings.max_bounds.sw.lat !== "" &&
                settings.max_bounds.sw.lng !== "" &&
                settings.max_bounds.ne.lat != null &&
                settings.max_bounds.ne.lng != null &&
                settings.max_bounds.sw.lat != null &&
                settings.max_bounds.sw.lng != null
            ) {
                mapOptions.maxBounds = L.latLngBounds(
                    L.latLng(settings.max_bounds.sw.lat, settings.max_bounds.sw.lng),
                    L.latLng(settings.max_bounds.ne.lat, settings.max_bounds.ne.lng)
                );

                mapOptions.minZoom = 3;
                mapOptions.maxZoom = 18;
            }
        }

        // Init map
        let map = L.map(DOM, mapOptions).setView(
            [settings.latitude, settings.longitude],
            settings.zoom
        );

        let clustering;

        // Load Clustering
        if (settings.clustering) {
            clustering = L.markerClusterGroup({
                chunkedLoading: true,
                spiderfyOnMaxZoom: true,
            });
        }

        // Tile Server
        if (typeof settings.tileserver === "function") {
            settings.tileserver(map);
        }
        // Mapbox Tile Server
        else if (settings.access_token) {
            L.tileLayer(
                "https://api.mapbox.com/styles/v1/mapbox/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}",
                {
                    attribution:
                        'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: "streets-v9",
                    accessToken: settings.access_token,
                }
            ).addTo(map);
        }
        // OSM (OpenStreetMaps)
        else {
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution:
                    'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
                maxZoom: 18,
            }).addTo(map);
        }

        // Popup Options
        let popupOptions = {
            minWidth: 300,
            maxWidth: 600,
            className: "listdom-leaflet-popup",
        };

        const infowindowTrigger = settings.infowindow_trigger === "hover" ? "hover" : "click";

        // Extend Shapes
        extend();

        // Loaded Objects
        let loadedObjects = [];
        let loadedObjectsById = {};
        let openListingTimer = null;

        // Bounds
        let bounds = L.latLngBounds();

        // Load Layers
        loadLayers(settings.layers);

        // Load Objects
        loadObjects(settings.objects);

        // Init Map Search
        if (settings.mapsearch) mapsearch();

        // Init Auto GPS
        if (settings.autoGPS && !settings.geo_request) autoGPS();

        // Sync
        $('body').on('lsd-sync', function (e, { id, request, clearKeys = [] }) {
            if (id !== parseInt(settings.id)) return;
            mapsearch_request(request, clearKeys);
        });

        function loadObjects(objects) {
            let f = 0;
            let dataObject;

            const $sidebar = $('#lsd_skin' + settings.id + ' .lsd-map-sidebar-listings');
            if ($sidebar.length) $sidebar.html('');

            // If clustering enabled, clear previous cluster layers (safety)
            if (settings.clustering && clustering) clustering.clearLayers();

            for (let i in objects) {
                f++;
                dataObject = objects[i];

                if (dataObject.type === "marker") loadMarker(dataObject);
                else if (dataObject.type === "circle") loadCircle(dataObject);
                else if (dataObject.type === "polygon") loadPolygon(dataObject);
                else if (dataObject.type === "polyline") loadPolyline(dataObject);
                else if (dataObject.type === "rectangle") loadRectangle(dataObject);

                if (dataObject.card && $sidebar.length) {
                    $sidebar.append(dataObject.card);
                }
            }

            // Fit the map to the boundaries
            if (updateBounds && (f > 1 || (f === 1 && dataObject.type !== "marker")))
            {
                map.fitBounds(bounds);
            } else if (updateBounds && f === 1 && dataObject.type === "marker") {
                map.setView([dataObject.latitude, dataObject.longitude], settings.zoom);
            }

            // Apply Clustering
            if (settings.clustering && clustering && !map.hasLayer(clustering)) map.addLayer(clustering);

            // Mobile Sidebar
            if ($sidebar.length && !$sidebar.is(':empty') && $(window).width() <= 1024) {
                // Load More Button
                const $loadmore = $('.lsd-map-sidebar-loadmore .lsd-loadmore-button');

                // Display First Cards
                displayCards();

                // Load More
                $loadmore
                .off('click')
                .on('click', displayCards);

                function displayCards() {
                    const limit = $sidebar.data('limit');
                    const selector = '.lsd-map-card-wrapper:hidden';

                    const $all = $sidebar.find($(selector));
                    $all.slice(0, limit).removeClass('lsd-tablet-hidden');

                    const remaining = $sidebar.find($(selector)).length;

                    if (remaining === 0) $loadmore.parent().addClass('lsd-util-hide');
                    else if (remaining > 0) $loadmore.parent().removeClass('lsd-util-hide');
                }
            }
        }

        function loadMarker(markerData) {
            // HTML Marker
            let icon = L.divIcon({
                className: "lsd-marker",
                html: markerData.marker,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
            });

            let marker = L.marker([markerData.latitude, markerData.longitude], {
                icon: icon,
            });

            marker.listing_id = markerData.id;
            marker.is_marker = true;
            marker.lsd_infowindow = markerData.infowindow;

            if (settings.display_infowindow) {
                // InfoWindow
                if (markerData.onclick === "infowindow") {
                    marker.bindPopup(markerData.infowindow, popupOptions);
                    marker.on("popupopen", listdom_bricks_infowindow_onload);

                    if (infowindowTrigger === "hover") {
                        marker.on("mouseover", function () {
                            marker.openPopup();
                        });

                        marker.on("mouseout", function (event) {
                            const icon = marker.getElement();
                            if (icon && event && event.originalEvent) {
                                const relatedTarget = event.originalEvent.relatedTarget;
                                if (relatedTarget && icon.contains(relatedTarget)) return;
                            }

                            const popup = marker.getPopup();
                            if (popup && popup.getElement() && event && event.originalEvent) {
                                const relatedTarget = event.originalEvent.relatedTarget;
                                if (relatedTarget && popup.getElement().contains(relatedTarget)) return;
                            }

                            marker.closePopup();
                        });

                        marker.on("popupopen", function (event) {
                            if (marker._lsdHoverPopupBound) return;
                            marker._lsdHoverPopupBound = true;

                            const popupElement = event.popup.getElement();
                            if (!popupElement) return;

                            popupElement.addEventListener("mouseleave", function () {
                                marker.closePopup();
                            });
                        });
                    }
                }
                // Redirect
                else if (markerData.onclick === "redirect") {
                    marker.on("click", function () {
                        window.location = markerData.link;
                    });
                } else if (markerData.onclick === "lightbox") {
                    marker.on("click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            markerData.id,
                            markerData.raw,
                            settings
                        ).lightbox();
                    });
                }
            }

            // Add to clustering or map
            if (settings.clustering && clustering) clustering.addLayer(marker);
            else marker.addTo(map);

            // Extend the bounds to include each marker's position
            bounds.extend([markerData.latitude, markerData.longitude]);

            // Add to Loaded Objects
            registerLoadedObject(marker);
        }

        function loadCircle(shapeData) {
            let shape = L.circle([shapeData.center.lat, shapeData.center.lng], {
                color: shapeData.stroke_color,
                weight: shapeData.stroke_weight,
                opacity: shapeData.stroke_opacity,
                fill: true,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
                radius: shapeData.radius,
            });

            shape.listing_id = shapeData.id;
            shape.is_marker = false;
            shape.lsd_infowindow = shapeData.infowindow;

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
                    shape.on("popupopen", listdom_bricks_infowindow_onload);
                }
                // Redirect
                else if (shapeData.onclick === "redirect") {
                    shape.on("click", function () {
                        window.location = shapeData.link;
                    });
                } else if (shapeData.onclick === "lightbox") {
                    shape.on("click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(shapeData.id, shapeData.raw, settings).lightbox();
                    });
                }
            }

            // Add to clustering or map
            if (settings.clustering && clustering) clustering.addLayer(shape);
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadPolygon(shapeData) {
            let shape = new L.PolygonClusterable(shapeData.boundaries, {
                color: shapeData.stroke_color,
                weight: shapeData.stroke_weight,
                opacity: shapeData.stroke_opacity,
                fill: true,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
            });

            shape.listing_id = shapeData.id;
            shape.is_marker = false;
            shape.lsd_infowindow = shapeData.infowindow;

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
                    shape.on("popupopen", listdom_bricks_infowindow_onload);
                }
                // Redirect
                else if (shapeData.onclick === "redirect") {
                    shape.on("click", function () {
                        window.location = shapeData.link;
                    });
                } else if (shapeData.onclick === "lightbox") {
                    shape.on("click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(shapeData.id, shapeData.raw, settings).lightbox();
                    });
                }
            }

            // Add to clustering or map
            if (settings.clustering && clustering) clustering.addLayer(shape);
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadPolyline(shapeData) {
            let points = [];

            for (let p in shapeData.boundaries) {
                let point = shapeData.boundaries[p];
                points.push(new L.LatLng(point.lat, point.lng));
            }

            let shape = new L.PolylineClusterable(points, {
                color: shapeData.stroke_color,
                weight: shapeData.stroke_weight,
                opacity: shapeData.stroke_opacity,
                smoothFactor: 1,
            });

            shape.listing_id = shapeData.id;
            shape.is_marker = false;
            shape.lsd_infowindow = shapeData.infowindow;

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
                    shape.on("popupopen", listdom_bricks_infowindow_onload);
                }
                // Redirect
                else if (shapeData.onclick === "redirect") {
                    shape.on("click", function () {
                        window.location = shapeData.link;
                    });
                } else if (shapeData.onclick === "lightbox") {
                    shape.on("click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(shapeData.id, shapeData.raw, settings).lightbox();
                    });
                }
            }

            // Add to clustering or map
            if (settings.clustering && clustering) clustering.addLayer(shape);
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadRectangle(shapeData) {
            let points = [
                [shapeData.north, shapeData.west],
                [shapeData.north, shapeData.east],
                [shapeData.south, shapeData.east],
                [shapeData.south, shapeData.west],
            ];

            let shape = new L.RectangleClusterable(points, {
                color: shapeData.stroke_color,
                weight: shapeData.stroke_weight,
                opacity: shapeData.stroke_opacity,
                fill: true,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
            });

            shape.listing_id = shapeData.id;
            shape.is_marker = false;
            shape.lsd_infowindow = shapeData.infowindow;

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
                    shape.on("popupopen", listdom_bricks_infowindow_onload);
                }
                // Redirect
                else if (shapeData.onclick === "redirect") {
                    shape.on("click", function () {
                        window.location = shapeData.link;
                    });
                } else if (shapeData.onclick === "lightbox") {
                    shape.on("click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(shapeData.id, shapeData.raw, settings).lightbox();
                    });
                }
            }

            // Add to clustering or map
            if (settings.clustering && clustering) clustering.addLayer(shape);
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function removeObjects(objects) {
            for (let i in objects) {
                let object = objects[i];

                if (settings.clustering && clustering && clustering.hasLayer(object)) clustering.removeLayer(object);
                else if (map.hasLayer(object)) map.removeLayer(object);
            }
        }

        function reloadObjects(objects) {
            // Remove Existing Objects
            removeObjects(loadedObjects);
            loadedObjects = [];
            loadedObjectsById = {};

            // Add New Objects
            loadObjects(objects);
        }

        function registerLoadedObject(object) {
            loadedObjects.push(object);

            if (typeof object.listing_id !== "undefined") {
                loadedObjectsById[String(object.listing_id)] = object;
            }
        }

        function freezeMapsearchTemporarily() {
            mapsearchFreez = true;

            if (openListingTimer) clearTimeout(openListingTimer);
            openListingTimer = setTimeout(function () {
                mapsearchFreez = false;
            }, 2000);
        }

        function isolateMarker(object) {
            if (!object || !object.is_marker || !object.getLatLng || typeof object.getLatLng !== "function") return;

            const latLng = object.getLatLng();
            const maxZoom = typeof map.getMaxZoom === "function" && map.getMaxZoom() ? map.getMaxZoom() : 18;
            let zoom = typeof map.getZoom === "function" ? map.getZoom() : 0;

            while (zoom < maxZoom) {
                const visibleMarkers = loadedObjects.filter(function (item) {
                    return item && item.is_marker && item.getLatLng && map.getBounds().contains(item.getLatLng());
                }).length;

                if (visibleMarkers <= 1) break;

                zoom += 1;
                map.setView(latLng, zoom, {
                    animate: false,
                });
            }
        }

        function openObjectPopup(object) {
            if (!object || !object.lsd_infowindow) return false;

            freezeMapsearchTemporarily();

            if (!object.getPopup || !object.getPopup()) {
                object.bindPopup(object.lsd_infowindow, popupOptions);
                object.on("popupopen", listdom_bricks_infowindow_onload);
            }

            if (object.getBounds && typeof object.getBounds === "function") {
                map.fitBounds(object.getBounds());
            } else if (object.getLatLng && typeof object.getLatLng === "function") {
                const latLng = object.getLatLng();
                const targetZoom = parseInt(settings.zoom, 10);

                if (!isNaN(targetZoom) && map.getZoom() < targetZoom) map.setView(latLng, targetZoom);
                else map.panTo(latLng);
            }

            if (settings.clustering && clustering && typeof clustering.zoomToShowLayer === "function") {
                clustering.zoomToShowLayer(object, function () {
                    if (object.getLatLng && typeof object.getLatLng === "function") {
                        const latLng = object.getLatLng();
                        const targetZoom = parseInt(settings.zoom, 10);

                        if (!isNaN(targetZoom) && map.getZoom() < targetZoom) map.setView(latLng, targetZoom);
                    }

                    isolateMarker(object);
                    object.openPopup();
                });
            } else {
                isolateMarker(object);
                object.openPopup();
            }

            return true;
        }

        function extend() {
            // Extend Rectangle
            L.RectangleClusterable = L.Rectangle.extend({
                _originalInitialize: L.Rectangle.prototype.initialize,
                initialize: function (bounds, options) {
                    this._originalInitialize(bounds, options);
                    this._latlng = this.getBounds().getCenter();
                },
                getLatLng: function () {
                    return this._latlng;
                },
                setLatLng: function () { },
            });

            // Extend Polygon
            L.PolygonClusterable = L.Polygon.extend({
                _originalInitialize: L.Polygon.prototype.initialize,
                initialize: function (bounds, options) {
                    this._originalInitialize(bounds, options);
                    this._latlng = this.getBounds().getCenter();
                },
                getLatLng: function () {
                    return this._latlng;
                },
                setLatLng: function () { },
            });

            // Extend Polyline
            L.PolylineClusterable = L.Polyline.extend({
                _originalInitialize: L.Polyline.prototype.initialize,
                initialize: function (bounds, options) {
                    this._originalInitialize(bounds, options);
                    this._latlng = this.getBounds().getCenter();
                },
                getLatLng: function () {
                    return this._latlng;
                },
                setLatLng: function () { },
            });
        }

        function mapsearch() {
            map.on('moveend', function () {
                if (!mapsearchFreez) {
                    mapsearch_boundary();
                }
            });
        }

        function mapsearch_boundary() {
            // Calculating Bounds
            let bounds = map.getBounds();
            let ne = bounds.getNorthEast();
            let sw = bounds.getSouthWest();

            let lat_max = ne.lat;
            let lat_min = sw.lat;
            let lng_min = sw.lng;
            let lng_max = ne.lng;

            // Min/Max values for Longitude
            if (lng_min > lng_max) {
                lng_min = -180;
                lng_max = 180;
            }

            // Min/Max values for Latitude
            if (lat_min > lat_max) {
                lat_max = 90;
                lat_min = -90;
            }

            // Trigger Event
            $("body").trigger("lsd-mapsearch", {
                ne: {
                    lat: lat_max,
                    lng: lng_max,
                },
                sw: {
                    lat: lat_min,
                    lng: lng_min,
                },
            });

            // Boundary Parameters
            let request =
                "sf[min_latitude]=" + lat_min +
                "&sf[max_latitude]=" + lat_max +
                "&sf[min_longitude]=" + lng_min +
                "&sf[max_longitude]=" + lng_max;

            mapsearch_request(request);
        }

        function mapsearch_request(request, clearKeys = []) {
            // Freez the Map Search
            mapsearchFreez = true;

            // Page Reset
            request += "&page=1";

            // View
            request +=
                "&view=" +
                (typeof $wrapper.data("view") === "undefined"
                    ? ""
                    : $wrapper.data("view"));

            // Push to History
            new ListdomPageHistory().push(
                "?" + request,
                lsdShouldUpdateAddressBar(settings.id),
                clearKeys
            );

            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            // Pagination Wrapper
            const $pagination = $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper");

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_ajax_search&" + req.get(request, settings.args, clearKeys),
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Don't Update Boundary
                    updateBounds = false;

                    // Reload Objects
                    reloadObjects(response.objects);

                    // Release the Map Search
                    setTimeout(function () {
                        mapsearchFreez = false;
                    }, 1000);

                    // Update Listings
                    const $skin = $("#lsd_skin" + settings.id);
                    lsdResetTimelineCarousel($skin);
                    $skin.find('.lsd-listing-wrapper').html(response.listings);

                    // Update Pagination
                    if($pagination.length) $pagination.replaceWith(response.pagination);
                    else $("#lsd_skin" + settings.id + " .lsd-list-wrapper").append(response.pagination);

                    // Hide or Show Load More
                    if (response.count === 0 || response.total <= response.count)
                        $loadMoreWrapper.addClass("lsd-util-hide");
                    else if (response.total > response.count)
                        $loadMoreWrapper.removeClass("lsd-util-hide");

                    // Loading Style
                    $wrapper.fadeTo(200, 1);

                    // Update the Next Page
                    $("#lsd_skin" + settings.id).data('next-page', response.next_page);

                    // Search Success Event
                    response.shortcode = settings.id;
                    window.dispatchEvent(new CustomEvent('lsd-search-success', {
                        detail: response
                    }));

                    // Trigger
                    listdom_onload();

                    // Trigger Connected Shortcodes Sync
                    if (typeof settings.connected_shortcodes !== 'undefined') {
                        for (const i in settings.connected_shortcodes) {
                            const shortcode_id = settings.connected_shortcodes[i];
                            $('body').trigger('lsd-sync', {
                                id: shortcode_id,
                                request: request,
                                clearKeys: clearKeys
                            });
                        }
                    }
                },
                error: function () { },
            });
        }

        function loadLayers(layers) {
            for (let i in layers) {
                let layer = layers[i];

                if (layer.type === "KML") omnivore.kml(layer.src).addTo(map);
                else if (layer.type === "GPX") omnivore.gpx(layer.src).addTo(map);
            }
        }

        function autoGPS() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    map.setView(
                        [position.coords.latitude, position.coords.longitude],
                        map.getZoom() <= settings.gps_zoom.current
                            ? settings.gps_zoom.zl
                            : map.getZoom()
                    );
                });
            }
        }

        return {
            id: settings.id,
            element: DOM,
            load: function (objects) {
                reloadObjects(objects);
            },
            openListing: function (listingId) {
                const object = loadedObjectsById[String(listingId)];
                if (!object) return false;

                return openObjectPopup(object);
            },
        };
    };
})(jQuery);

// Listdom GOOGLE MAPS PLUGIN
(function ($) {
    $.fn.listdomGoogleMaps = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                latitude: 0,
                longitude: 0,
                zoom: 14,
                icon: "../img/m-01.png",
                clustering: false,
                clustering_images: "",
                richmarker: "",
                objects: {},
                styles: "",
                mapcontrols: {},
                fill_color: "#1e90ff",
                fill_opacity: 0.3,
                stroke_color: "#1e74c7",
                stroke_opacity: 0.6,
                stroke_weight: 1,
                mapsearch: false,
                autoGPS: false,
                display_infowindow: true,
                infowindow_trigger: "click",
                geo_request: false,
                max_bounds: {},
                mousewheel_zoom: false,
                gps_zoom: {
                    zl: 13,
                    current: 7,
                },
                gps: false,
                gplaces: false,
                direction: {
                    status: false,
                },
                layers: [],
            },
            options
        );

        // Load More Wrapper
        let $loadMoreWrapper = $(
            "#lsd_skin" + settings.id + " .lsd-load-more-wrapper"
        );

        // Wrapper
        let $wrapper = $("#lsd_skin" + settings.id);

        // Head of Page
        let $head = $("head");

        // Load Rich Marker
        $head.append(
            '<script type="text/javascript" src="' +
            settings.richmarker +
            '"></script>'
        );

        // Load InfoBox
        $head.append(
            '<script type="text/javascript" src="' + settings.infobox + '"></script>'
        );

        // Listdom Request Plugin
        let req = new ListdomRequest(settings.id, settings);
        req.get("", settings.args + (settings.args && settings.atts ? "&" : "") + settings.atts);

        // Create the options
        let bounds = new google.maps.LatLngBounds();
        let center = new google.maps.LatLng(settings.latitude, settings.longitude);

        let canvas = this;
        let DOM = canvas[0];
        let updateBounds = true;

        // Google Maps Options
        let mapOptions = $.extend(
            {
                scrollwheel: settings.mousewheel_zoom,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: center,
                zoom: settings.zoom,
                styles: settings.styles,
            },
            getMapControlOptions()
        );

        // Restrict Bounds
        if (
            settings.max_bounds &&
            settings.max_bounds.ne &&
            settings.max_bounds.sw
        ) {
            if (
                settings.max_bounds.ne.lat !== "" &&
                settings.max_bounds.ne.lng !== "" &&
                settings.max_bounds.sw.lat !== "" &&
                settings.max_bounds.sw.lng !== ""
            ) {
                mapOptions.restriction = {
                    latLngBounds: {
                        north: settings.max_bounds.ne.lat,
                        south: settings.max_bounds.sw.lat,
                        west: settings.max_bounds.sw.lng,
                        east: settings.max_bounds.ne.lng,
                    },
                    strictBounds: true,
                };

                // Disable Clustering
                settings.clustering = false;
            }
        }

        // Init map
        let map = new google.maps.Map(DOM, mapOptions);

        const infowindowTrigger = settings.infowindow_trigger === "hover" ? "hover" : "click";
        let infowindowHover = false;
        let infowindowCloseTimer = null;

        // Init Infowindow
        let infowindow = new InfoBox({
            alignBottom: true,
        });

        // Loaded Objects
        let loadedObjects = [];
        let loadedObjectsById = {};
        let openListingTimer = null;
        let markerCluster;

        // Load Layers
        loadLayers(settings.layers);

        // Load Objects
        loadObjects(settings.objects);

        // Load Clustering
        if (settings.clustering) {
            $head.append(
                '<script type="text/javascript" src="' +
                settings.clustering +
                '"></script>'
            );

            markerCluster = new MarkerClusterer(map, loadedObjects, {
                imagePath: settings.clustering_images,
            });
        }

        // Init Map Search
        if (settings.mapsearch) mapsearch();

        // Init GPS
        if (settings.mapcontrols.gps) gps();

        // Init Auto GPS
        if (settings.autoGPS && !settings.geo_request) autoGPS();

        // Init Draw Search
        if (settings.mapcontrols.draw && hasGoogleDrawing()) drawsearch();

        // Init Google Places
        if (settings.gplaces) gplaces();

        // Init Direction
        if (settings.direction.status) direction();

        // Sync
        $('body').on('lsd-sync', function (e, {id, request, clearKeys = []}) {
            if (id !== parseInt(settings.id)) return;
            mapsearch_request(request, clearKeys);
        });

        function loadObjects(objects) {
            let f = 0;
            let dataObject;

            const $sidebar = $('#lsd_skin' + settings.id + ' .lsd-map-sidebar-listings');
            if ($sidebar.length) $sidebar.html('');

            for (let i in objects) {
                f++;
                dataObject = objects[i];

                if (dataObject.type === "marker") loadMarker(dataObject);
                else if (dataObject.type === "circle") loadCircle(dataObject);
                else if (dataObject.type === "polygon") loadPolygon(dataObject);
                else if (dataObject.type === "polyline") loadPolyline(dataObject);
                else if (dataObject.type === "rectangle") loadRectangle(dataObject);

                if (dataObject.card && $sidebar.length) {
                    $sidebar.append(dataObject.card);
                }
            }

            // Fit the map to the boundaries
            if (
                updateBounds &&
                (f > 1 || (f === 1 && dataObject.type !== "marker"))
            ) {
                map.fitBounds(bounds);
            } else if (updateBounds && f === 1 && dataObject.type === "marker") {
                map.setCenter(
                    new google.maps.LatLng(dataObject.latitude, dataObject.longitude)
                );
                map.setZoom(settings.zoom);
            }

            // Mobile Sidebar
            if ($sidebar.length && !$sidebar.is(':empty') && $(window).width() <= 1024) {
                // Load More Button
                const $loadmore = $('.lsd-map-sidebar-loadmore .lsd-loadmore-button');

                // Display First Cards
                displayCards();

                // Load More
                $loadmore
                    .off('click')
                    .on('click', displayCards);

                function displayCards() {
                    const limit = $sidebar.data('limit');
                    const selector = '.lsd-map-card-wrapper:hidden';

                    const $all = $sidebar.find($(selector));
                    $all.slice(0, limit).removeClass('lsd-tablet-hidden');

                    const remaining = $sidebar.find($(selector)).length;

                    if (remaining === 0) $loadmore.parent().addClass('lsd-util-hide');
                    else if (remaining > 0) $loadmore.parent().removeClass('lsd-util-hide');
                }
            }
        }

        function loadMarker(markerData) {
            let marker = new RichMarker({
                position: new google.maps.LatLng(
                    markerData.latitude,
                    markerData.longitude
                ),
                map: map,
                content: markerData.marker,
                infowindow: markerData.infowindow,
                lsd_onclick: markerData.onclick,
                lsd_link: markerData.link,
                raw_link: markerData.raw,
                lsd: markerData.lsd,
                listing_id: markerData.id,
                shadow: "none",
            });

            marker.lsd_infowindow = markerData.infowindow;
            marker.is_marker = true;

            // Marker Info-Window
            if (settings.display_infowindow) {
                const cancelInfowindowClose = function () {
                    if (infowindowCloseTimer) {
                        clearTimeout(infowindowCloseTimer);
                        infowindowCloseTimer = null;
                    }
                };

                const scheduleInfowindowClose = function () {
                    cancelInfowindowClose();
                    infowindowCloseTimer = setTimeout(function () {
                        if (!infowindowHover) infowindow.close();
                    }, 150);
                };

                const bindInfowindowHover = function () {
                    const infoBoxElement = DOM.querySelector(".infoBox");
                    if (!infoBoxElement || infoBoxElement._lsdHoverBound) return;

                    infoBoxElement._lsdHoverBound = true;
                    infoBoxElement.addEventListener("mouseenter", function () {
                        infowindowHover = true;
                        cancelInfowindowClose();
                    });

                    infoBoxElement.addEventListener("mouseleave", function () {
                        infowindowHover = false;
                        scheduleInfowindowClose();
                    });
                };

                const openMarkerInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);

                    // Infowindow Offset
                    if (typeof marker.lsd.y_offset !== 'undefined')
                        infowindow.setOptions({
                            pixelOffset: new google.maps.Size(
                                marker.lsd.x_offset,
                                marker.lsd.y_offset
                            ),
                        });
                    else
                        infowindow.setOptions({
                            pixelOffset: new google.maps.Size(0, -35),
                        });

                    infowindow.open(map, this);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);

                    if (infowindowTrigger === "hover") {
                        infowindowHover = true;
                        cancelInfowindowClose();
                        google.maps.event.addListenerOnce(infowindow, "domready", bindInfowindowHover);
                    }
                };

                if (marker.lsd_onclick === 'infowindow') {
                    if (infowindowTrigger === 'hover') {
                        google.maps.event.addListener(marker, 'mouseover', function () {
                            infowindowHover = true;
                            cancelInfowindowClose();
                            openMarkerInfowindow.call(this);
                        });
                        google.maps.event.addListener(marker, 'mouseout', function () {
                            infowindowHover = false;
                            scheduleInfowindowClose();
                        });
                        google.maps.event.addListener(marker, 'click', openMarkerInfowindow);
                    } else {
                        google.maps.event.addListener(marker, 'click', openMarkerInfowindow);
                    }
                } else if (marker.lsd_onclick === 'redirect') {
                    google.maps.event.addListener(marker, 'click', function () {
                        window.location = this.lsd_link;
                    });
                } else if (marker.lsd_onclick === 'lightbox') {
                    google.maps.event.addListener(marker, 'click', function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            this.listing_id,
                            this.raw_link,
                            settings
                        ).lightbox();
                    });
                }
            }

            if (markerData.infowindow) {
                marker._lsdOpenInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(marker.infowindow);

                    if (typeof marker.lsd.y_offset !== 'undefined') {
                        infowindow.setOptions({
                            pixelOffset: new google.maps.Size(
                                marker.lsd.x_offset,
                                marker.lsd.y_offset
                            ),
                        });
                    } else {
                        infowindow.setOptions({
                            pixelOffset: new google.maps.Size(0, -35),
                        });
                    }

                    infowindow.open(map, marker);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };
            }

            // Extend the bounds to include each marker's position
            bounds.extend(marker.position);

            // Add to Loaded Objects
            registerLoadedObject(marker);
        }

        function loadCircle(shapeData) {
            let shape = new google.maps.Circle({
                map: map,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
                strokeOpacity: shapeData.stroke_opacity,
                strokeColor: shapeData.stroke_color,
                strokeWeight: shapeData.stroke_weight,
                clickable: true,
                center: shapeData.center,
                radius: shapeData.radius,
                infowindow: shapeData.infowindow,
                lsd_onclick: shapeData.onclick,
                lsd_link: shapeData.link,
                raw_link: shapeData.raw,
                listing_id: shapeData.id,
            });

            shape.lsd_infowindow = shapeData.infowindow;
            shape.is_marker = false;

            shape.getPosition = function () {
                return shape.getCenter();
            };

            // Shape Info-Window
            if (settings.display_infowindow) {
                const openShapeInfowindow = function (event) {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event && event.latLng ? event.latLng : this.getPosition());
                    infowindow.open(map, this);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };


                if (shape.lsd_onclick === "infowindow") {
                    google.maps.event.addListener(shape, "click", openShapeInfowindow);
                } else if (shape.lsd_onclick === "redirect") {
                    google.maps.event.addListener(shape, "click", function () {
                        window.location = this.lsd_link;
                    });
                } else if (shape.lsd_onclick === "lightbox") {
                    google.maps.event.addListener(shape, "click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            this.listing_id,
                            this.raw_link,
                            settings
                        ).lightbox();
                    });
                }
            }

            if (shapeData.infowindow) {
                shape._lsdOpenInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(shape.infowindow);
                    infowindow.setPosition(shape.getPosition());
                    infowindow.open(map, shape);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };
            }

            // Extend the bounds to include each shape's position
            bounds.union(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadPolygon(shapeData) {
            let shape = new google.maps.Polygon({
                map: map,
                paths: shapeData.boundaries,
                strokeOpacity: shapeData.stroke_opacity,
                strokeColor: shapeData.stroke_color,
                strokeWeight: shapeData.stroke_weight,
                clickable: true,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
                infowindow: shapeData.infowindow,
                lsd_onclick: shapeData.onclick,
                lsd_link: shapeData.link,
                raw_link: shapeData.raw,
                listing_id: shapeData.id,
            });

            shape.lsd_infowindow = shapeData.infowindow;
            shape.is_marker = false;

            let lastPath;
            let lastCenter;
            shape.getPosition = function () {
                let path = this.getPath();
                if (lastPath === path) return lastCenter;

                lastPath = path;
                let bounds = new google.maps.LatLngBounds();
                path.forEach(function (latlng) {
                    bounds.extend(latlng);
                });

                lastCenter = bounds.getCenter();
                return lastCenter;
            };

            // Shape Info-Window
            if (settings.display_infowindow) {
                const openShapeInfowindow = function (event) {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event && event.latLng ? event.latLng : this.getPosition());
                    infowindow.open(map, this);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };


                if (shape.lsd_onclick === "infowindow") {
                    google.maps.event.addListener(shape, "click", openShapeInfowindow);
                } else if (shape.lsd_onclick === "redirect") {
                    google.maps.event.addListener(shape, "click", function () {
                        window.location = this.lsd_link;
                    });
                } else if (shape.lsd_onclick === "lightbox") {
                    google.maps.event.addListener(shape, "click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            this.listing_id,
                            this.raw_link,
                            settings
                        ).lightbox();
                    });
                }
            }

            if (shapeData.infowindow) {
                shape._lsdOpenInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(shape.infowindow);
                    infowindow.setPosition(shape.getPosition());
                    infowindow.open(map, shape);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };
            }

            // Extend the bounds to include each shape's position
            shape.getPaths().forEach(function (path) {
                let points = path.getArray();
                for (let p in points) bounds.extend(points[p]);
            });

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadPolyline(shapeData) {
            let shape = new google.maps.Polyline({
                map: map,
                path: shapeData.boundaries,
                strokeOpacity: shapeData.stroke_opacity,
                strokeColor: shapeData.stroke_color,
                strokeWeight: shapeData.stroke_weight,
                clickable: true,
                infowindow: shapeData.infowindow,
                lsd_onclick: shapeData.onclick,
                lsd_link: shapeData.link,
                raw_link: shapeData.raw,
                listing_id: shapeData.id,
            });

            shape.lsd_infowindow = shapeData.infowindow;
            shape.is_marker = false;

            // Shape Info-Window
            if (settings.display_infowindow) {
                const openShapeInfowindow = function (event) {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event && event.latLng ? event.latLng : this.getPosition());
                    infowindow.open(map, this);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };


                if (shape.lsd_onclick === "infowindow") {
                    google.maps.event.addListener(shape, "click", openShapeInfowindow);
                } else if (shape.lsd_onclick === "redirect") {
                    google.maps.event.addListener(shape, "click", function () {
                        window.location = this.lsd_link;
                    });
                } else if (shape.lsd_onclick === "lightbox") {
                    google.maps.event.addListener(shape, "click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            this.listing_id,
                            this.raw_link,
                            settings
                        ).lightbox();
                    });
                }
            }

            // Extend the bounds to include each shape's position
            let path = shape.getPath();

            let slat, blat, slng, blng;

            slat = blat = path.getAt(0).lat();
            slng = blng = path.getAt(0).lng();

            for (let i = 1; i < path.getLength(); i++) {
                let e = path.getAt(i);
                slat = slat < e.lat() ? slat : e.lat();
                blat = blat > e.lat() ? blat : e.lat();
                slng = slng < e.lng() ? slng : e.lng();
                blng = blng > e.lng() ? blng : e.lng();
            }

            bounds.extend(new google.maps.LatLng(slat, slng));
            bounds.extend(new google.maps.LatLng(blat, blng));

            shape.getPosition = function () {
                return new google.maps.LatLng((slat + blat) / 2, (slng + blng) / 2);
            };

            if (shapeData.infowindow) {
                shape._lsdOpenInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(shape.infowindow);
                    infowindow.setPosition(shape.getPosition());
                    infowindow.open(map, shape);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };
            }

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function loadRectangle(shapeData) {
            let shape = new google.maps.Rectangle({
                map: map,
                strokeOpacity: shapeData.stroke_opacity,
                strokeColor: shapeData.stroke_color,
                strokeWeight: shapeData.stroke_weight,
                clickable: true,
                fillColor: shapeData.fill_color,
                fillOpacity: shapeData.fill_opacity,
                bounds: {
                    north: shapeData.north,
                    south: shapeData.south,
                    east: shapeData.east,
                    west: shapeData.west,
                },
                infowindow: shapeData.infowindow,
                lsd_onclick: shapeData.onclick,
                lsd_link: shapeData.link,
                raw_link: shapeData.raw,
                listing_id: shapeData.id,
            });

            shape.lsd_infowindow = shapeData.infowindow;
            shape.is_marker = false;

            shape.getPosition = function () {
                return shape.getBounds().getCenter();
            };

            // Shape Info-Window
            if (settings.display_infowindow) {
                const openShapeInfowindow = function (event) {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event && event.latLng ? event.latLng : this.getPosition());
                    infowindow.open(map, this);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };


                if (shape.lsd_onclick === "infowindow") {
                    google.maps.event.addListener(shape, "click", openShapeInfowindow);
                } else if (shape.lsd_onclick === "redirect") {
                    google.maps.event.addListener(shape, "click", function () {
                        window.location = this.lsd_link;
                    });
                } else if (shape.lsd_onclick === "lightbox") {
                    google.maps.event.addListener(shape, "click", function () {
                        // Listdom Details Plugin
                        new ListdomDetails(
                            this.listing_id,
                            this.raw_link,
                            settings
                        ).lightbox();
                    });
                }
            }

            if (shapeData.infowindow) {
                shape._lsdOpenInfowindow = function () {
                    infowindow.close();
                    infowindow.setContent(shape.infowindow);
                    infowindow.setPosition(shape.getPosition());
                    infowindow.open(map, shape);
                    google.maps.event.addListenerOnce(infowindow, "domready", listdom_bricks_infowindow_onload);
                };
            }

            // Extend the bounds to include each shape's position
            bounds.union(shape.getBounds());

            // Add to Loaded Objects
            registerLoadedObject(shape);
        }

        function removeObjects(objects) {
            for (let i in objects) {
                let object = objects[i];
                object.setMap(null);
            }
        }

        function reloadObjects(objects) {
            // Remove Existing Objects
            removeObjects(loadedObjects);
            loadedObjects = [];
            loadedObjectsById = {};

            // Empty Bounds
            bounds = new google.maps.LatLngBounds();

            // Add New Objects
            loadObjects(objects);

            // Redraw Clustering
            if (settings.clustering && markerCluster) {
                markerCluster.clearMarkers();
                markerCluster.addMarkers(loadedObjects, false);
                markerCluster.redraw();
            }
        }

        function registerLoadedObject(object) {
            loadedObjects.push(object);

            if (typeof object.listing_id !== "undefined") {
                loadedObjectsById[String(object.listing_id)] = object;
            }
        }

        function freezeMapsearchTemporarily() {
            mapsearchFreez = true;

            if (openListingTimer) clearTimeout(openListingTimer);
            openListingTimer = setTimeout(function () {
                mapsearchFreez = false;
            }, 2000);
        }

        function isolateMarker(object, callback) {
            if (!object || !object.is_marker || !object.position) {
                callback();
                return;
            }

            const bounds = map.getBounds();
            const zoom = typeof map.getZoom === "function" ? map.getZoom() : 0;
            const maxZoom = typeof map.maxZoom === "number" ? map.maxZoom : 21;

            if (!bounds) {
                callback();
                return;
            }

            const visibleMarkers = loadedObjects.filter(function (item) {
                return item && item.is_marker && item.position && bounds.contains(item.position);
            }).length;

            if (visibleMarkers <= 1 || zoom >= maxZoom) {
                callback();
                return;
            }

            google.maps.event.addListenerOnce(map, "idle", function () {
                isolateMarker(object, callback);
            });

            map.setZoom(zoom + 1);
        }

        function openListingInfowindow(object) {
            if (!object || typeof object._lsdOpenInfowindow !== "function") return false;

            freezeMapsearchTemporarily();

            const position = typeof object.getPosition === "function" ? object.getPosition() : null;
            const targetZoom = parseInt(settings.zoom, 10);

            if (position) {
                if (!isNaN(targetZoom) && map.getZoom() < targetZoom) map.setZoom(targetZoom);
                map.panTo(position);
            }

            google.maps.event.addListenerOnce(map, "idle", function () {
                isolateMarker(object, function () {
                    object._lsdOpenInfowindow();
                });
            });
            return true;
        }

        function getMapControlPosition(lsdPosition) {
            let position;

            if (lsdPosition === "TOP_LEFT")
                position = google.maps.ControlPosition.TOP_LEFT;
            else if (lsdPosition === "TOP_CENTER")
                position = google.maps.ControlPosition.TOP_CENTER;
            else if (lsdPosition === "TOP_RIGHT")
                position = google.maps.ControlPosition.TOP_RIGHT;
            else if (lsdPosition === "RIGHT_TOP")
                position = google.maps.ControlPosition.RIGHT_TOP;
            else if (lsdPosition === "RIGHT_CENTER")
                position = google.maps.ControlPosition.RIGHT_CENTER;
            else if (lsdPosition === "RIGHT_BOTTOM")
                position = google.maps.ControlPosition.RIGHT_BOTTOM;
            else if (lsdPosition === "LEFT_TOP")
                position = google.maps.ControlPosition.LEFT_TOP;
            else if (lsdPosition === "LEFT_CENTER")
                position = google.maps.ControlPosition.LEFT_CENTER;
            else if (lsdPosition === "LEFT_BOTTOM")
                position = google.maps.ControlPosition.LEFT_BOTTOM;
            else if (lsdPosition === "BOTTOM_RIGHT")
                position = google.maps.ControlPosition.BOTTOM_RIGHT;
            else if (lsdPosition === "BOTTOM_CENTER")
                position = google.maps.ControlPosition.BOTTOM_CENTER;
            else if (lsdPosition === "BOTTOM_LEFT")
                position = google.maps.ControlPosition.BOTTOM_LEFT;

            return position;
        }

        function getMapControlOptions() {
            let options = {};

            // Zoom Control
            if (settings.mapcontrols.zoom === 0) options.zoomControl = false;
            else {
                options.zoomControl = true;
                options.zoomControlOptions = {
                    position: getMapControlPosition(settings.mapcontrols.zoom),
                };
            }

            // Map Type Control
            if (settings.mapcontrols.maptype === 0) options.mapTypeControl = false;
            else {
                options.mapTypeControl = true;
                options.mapTypeControlOptions = {
                    position: getMapControlPosition(settings.mapcontrols.maptype),
                };
            }

            // Street View Control
            if (settings.mapcontrols.streetview === 0)
                options.streetViewControl = false;
            else {
                options.streetViewControl = true;
                options.streetViewControlOptions = {
                    position: getMapControlPosition(settings.mapcontrols.streetview),
                };
            }

            // Scale Control
            options.scaleControl = settings.mapcontrols.scale !== 0;

            // Fullscreen Control
            options.fullscreenControl = settings.mapcontrols.fullscreen !== 0;

            // Camera Control
            options.cameraControl = typeof settings.mapcontrols.camera !== 'undefined' && settings.mapcontrols.camera !== 0;

            return options;
        }

        function gplaces() {
            let request = {
                location: map.getCenter(),
                radius: 1000,
            };

            let service = new google.maps.places.PlacesService(map);
            service.search(request, function (results, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    for (let i = 0; i < results.length; i++) gplaces_marker(results[i]);
                }
            });
        }

        function gplaces_marker(place) {
            let geoPoint = place.geometry.location;
            let image = new google.maps.MarkerImage(
                place.icon,
                new google.maps.Size(51, 51),
                new google.maps.Point(0, 0),
                new google.maps.Point(17, 34),
                new google.maps.Size(25, 25)
            );

            let marker = new google.maps.Marker({
                map: map,
                icon: image,
                title: place.name,
                position: geoPoint,
            });

            // Extend the Bounds
            bounds.extend(geoPoint);

            google.maps.event.addListener(marker, "click", function () {
                infowindow.setContent(
                    '<div class="lsd-gplaces-infowindow"><a href="https://www.google.com/maps/place/?q=place_id:' +
                    place.id +
                    '" target="_blank">' +
                    place.name +
                    "</a>" +
                    (typeof place.plus_code !== "undefined" &&
                    typeof place.plus_code.compound_code !== "undefined"
                        ? "<p>" + place.plus_code.compound_code + "</p>"
                        : "") +
                    "</div>"
                );
                infowindow.setOptions({pixelOffset: new google.maps.Size(0, -35)});
                infowindow.open(map, this);
            });
        }

        function direction() {
            let directionsDisplay;
            let directionsService;
            let start_marker;
            let end_marker;

            // Elements
            let $form = $("#lsd_direction_form" + settings.id);
            let $message = $form.data('message');
            let $gps = $("#lsd_direction_gps" + settings.id);
            let $address = $("#lsd_direction_address" + settings.id);
            let $reset = $("#lsd_direction_reset" + settings.id);
            let $latitude = $("#lsd_direction_latitude" + settings.id);
            let $longitude = $("#lsd_direction_longitude" + settings.id);
            let $gpsWrapper = $gps.closest('.lsd-direction-position-wrapper');

            // Disable native validation so we can allow GPS-only submissions
            $form.attr("novalidate", "novalidate");

            $form.on("submit", function (event) {
                event.preventDefault();

                let dest = new google.maps.LatLng(
                    settings.direction.destination.latitude,
                    settings.direction.destination.longitude
                );

                let latitude = $latitude.val();
                let longitude = $longitude.val();
                let addressValue = ($address.val() || "").trim();
                let hasCoordinates = latitude && longitude;
                let hasAddress = addressValue.length > 0;

                // Require either a typed address or coordinates from GPS
                if (!hasAddress && !hasCoordinates) {
                    if ($address[0] && typeof $address[0].reportValidity === "function") {
                        $address[0].reportValidity();
                    } else {
                        $address.focus();
                    }

                    return;
                }

                // Start Point By Address
                let from = hasCoordinates
                    ? new google.maps.LatLng(latitude, longitude)
                    : addressValue;


                // Reset The Direction
                if (typeof directionsDisplay !== "undefined") {
                    directionsDisplay.setMap(null);

                    if (start_marker) start_marker.setMap(null);
                    if (end_marker) end_marker.setMap(null);
                }

                // Fade Google Maps Canvas
                $(canvas).fadeTo(300, 0.4);

                directionsService = new google.maps.DirectionsService();
                directionsDisplay = new google.maps.DirectionsRenderer({
                    suppressMarkers: true,
                });

                directionsService.route(
                    {
                        origin: from,
                        destination: dest,
                        travelMode: google.maps.DirectionsTravelMode.DRIVING,
                    },
                    function (response, status) {
                        // No result or error
                        if (status !== google.maps.DirectionsStatus.OK || !response.routes || !response.routes.length)
                        {
                            listdom_toastify($message, 'lsd-warning');

                            // Fade Google Maps Canvas back
                            $(canvas).fadeTo(300, 1);

                            return;
                        }

                        if (status === google.maps.DirectionsStatus.OK) {
                            directionsDisplay.setDirections(response);
                            directionsDisplay.setMap(map);

                            let leg = response.routes[0].legs[0];
                            start_marker = new google.maps.Marker({
                                position: leg.start_location,
                                map: map,
                                icon: settings.direction.start_marker,
                            });

                            end_marker = new google.maps.Marker({
                                position: leg.end_location,
                                map: map,
                                icon: settings.direction.end_marker,
                            });
                        }

                        // Fade Google Maps Canvas
                        $(canvas).fadeTo(300, 1);
                    }
                );

                // Show Reset Button
                $reset.removeClass("lsd-util-hide");
                $gpsWrapper.addClass("lsd-util-hide");
            });

            $reset.on("click", function () {
                $address.val("");
                $latitude.val("");
                $longitude.val("");

                // Reset The Direction
                if (
                    typeof directionsDisplay !== "undefined" &&
                    typeof start_marker !== "undefined" &&
                    typeof end_marker !== "undefined"
                ) {
                    directionsDisplay.setMap(null);
                    start_marker.setMap(null);
                    end_marker.setMap(null);
                }

                // Hide Reset Button and show GPS wrapper
                $reset.addClass("lsd-util-hide");
                $gpsWrapper.removeClass("lsd-util-hide");
            });

            $address.on("input", function() {
                if ($address.val()) {
                    $latitude.val("");
                    $longitude.val("");
                } else {
                    $reset.addClass("lsd-util-hide");
                    $gpsWrapper.removeClass("lsd-util-hide");
                }
            });

            $gps.on("click", function () {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        let lat = position.coords.latitude;
                        let lng = position.coords.longitude;

                        $latitude.val(lat);
                        $longitude.val(lng);

                        // Create Geocoder instance
                        let geocoder = new google.maps.Geocoder();
                        let latlng = new google.maps.LatLng(lat, lng);

                        geocoder.geocode({ location: latlng }, function(results, status) {
                            if (status === google.maps.GeocoderStatus.OK) {
                                if (results[0]) $address.val(results[0].formatted_address);
                            }

                            $form.submit();
                        });
                    });
                }
            });
        }

        function gps() {
            let gpsWrapper = document.createElement("div");
            gpsWrapper.className = "lsd-gps";

            let gpsButton = document.createElement("button");
            gpsButton.style.backgroundColor = "rgb(255, 255, 255)";
            gpsButton.style.border = "none";
            gpsButton.style.outline = "none";
            gpsButton.style.width = "40px";
            gpsButton.style.height = "40px";
            gpsButton.style.borderRadius = "0";
            gpsButton.style.boxShadow = "rgba(0, 0, 0, 0.3) 0px 1px 4px -1px";
            gpsButton.style.cursor = "pointer";
            gpsButton.style.marginTop = "10px";
            gpsButton.style.marginRight = "10px";
            gpsButton.style.marginBottom = "10px";
            gpsButton.style.marginLeft = "10px";
            gpsButton.style.padding = "0px";
            gpsButton.title = "Your Location";
            gpsWrapper.appendChild(gpsButton);

            let gpsInner = document.createElement("div");
            gpsInner.style.margin = "0 auto";
            gpsInner.style.width = "18px";
            gpsInner.style.height = "18px";
            gpsInner.style.backgroundImage =
                "url(https://maps.gstatic.com/tactile/mylocation/mylocation-sprite-1x.png)";
            gpsInner.style.backgroundSize = "180px 18px";
            gpsInner.style.backgroundPosition = "0px 0px";
            gpsInner.style.backgroundRepeat = "no-repeat";
            gpsInner.id = "lsd_gps_button_inner" + settings.id;
            gpsButton.appendChild(gpsInner);

            google.maps.event.addListener(map, "dragstart", function () {
                $("#lsd_gps_button_inner" + settings.id).css(
                    "background-position",
                    "0px 0px"
                );
            });

            gpsButton.addEventListener("click", function () {
                let imgX = "0";
                let gpsAnimation = setInterval(function () {
                    if (imgX === "-18") imgX = "0";
                    else imgX = "-18";

                    $("#lsd_gps_button_inner" + settings.id).css(
                        "background-position",
                        imgX + "px 0px"
                    );
                }, 500);

                // Clear the Interval after 10 seconds
                setTimeout(function () {
                    if (gpsAnimation) {
                        clearInterval(gpsAnimation);
                        $("#lsd_gps_button_inner" + settings.id).css(
                            "background-position",
                            "0px 0px"
                        );
                    }
                }, 10000);

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        // Set the Map Center
                        map.setCenter(
                            new google.maps.LatLng(
                                position.coords.latitude,
                                position.coords.longitude
                            )
                        );

                        // Set the Zoom Level
                        if (map.getZoom() <= settings.gps_zoom.current)
                            map.setZoom(settings.gps_zoom.zl);

                        clearInterval(gpsAnimation);
                        $("#lsd_gps_button_inner" + settings.id).css(
                            "background-position",
                            "-144px 0px"
                        );
                    });
                } else {
                    clearInterval(gpsAnimation);
                    $("#lsd_gps_button_inner" + settings.id).css(
                        "background-position",
                        "0px 0px"
                    );
                }
            });

            gpsWrapper.index = 1;
            map.controls[getMapControlPosition(settings.mapcontrols.gps)].push(
                gpsWrapper
            );
        }

        function autoGPS() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    let GPSInterval = setInterval(function () {
                        if (!mapsearchFreez) {
                            // Set the Map Center
                            map.setCenter(
                                new google.maps.LatLng(
                                    position.coords.latitude,
                                    position.coords.longitude
                                )
                            );

                            // Set the Zoom Level
                            if (map.getZoom() <= settings.gps_zoom.current)
                                map.setZoom(settings.gps_zoom.zl);

                            // Clear The Loop
                            clearInterval(GPSInterval);
                        }
                    }, 300);
                });
            }
        }

        let loadedOverlays = [];
        let drawing = false;

        function hasGoogleDrawing() {
            return (
                typeof google !== "undefined" &&
                typeof google.maps !== "undefined" &&
                typeof google.maps.drawing !== "undefined" &&
                typeof google.maps.drawing.DrawingManager === "function"
            );
        }

        function drawsearch() {
            if (!hasGoogleDrawing()) return;

            let drawManager;
            try {
                drawManager = new google.maps.drawing.DrawingManager({
                    drawingControl: true,
                    drawingControlOptions: {
                        position: getMapControlPosition(settings.mapcontrols.draw),
                        drawingModes: [
                            "polygon",
                            "circle",
                            "rectangle",
                        ],
                    },
                    polygonOptions: {
                        strokeColor: settings.stroke_color,
                        strokeOpacity: settings.stroke_opacity,
                        strokeWeight: settings.stroke_weight,
                        editable: true,
                        draggable: false,
                        fillColor: settings.fill_color,
                        fillOpacity: settings.fill_opacity,
                        clickable: false,
                    },
                    rectangleOptions: {
                        strokeColor: settings.stroke_color,
                        strokeOpacity: settings.stroke_opacity,
                        strokeWeight: settings.stroke_weight,
                        editable: true,
                        draggable: false,
                        fillColor: settings.fill_color,
                        fillOpacity: settings.fill_opacity,
                        clickable: false,
                    },
                    circleOptions: {
                        strokeColor: settings.stroke_color,
                        strokeOpacity: settings.stroke_opacity,
                        strokeWeight: settings.stroke_weight,
                        editable: true,
                        draggable: false,
                        fillColor: settings.fill_color,
                        fillOpacity: settings.fill_opacity,
                        clickable: false,
                    },
                    map: map,
                });
            } catch (error) {
                return;
            }

            google.maps.event.addListener(
                drawManager,
                "overlaycomplete",
                function (event) {
                    // Set Draw Flag
                    drawing = true;

                    // Reset Draw Tool
                    drawManager.setOptions({drawingMode: null});

                    // Delete Other Overlays
                    drawsearch_set_overlays(event.overlay);

                    // Set Overlay Listeners
                    drawsearch_set_overlay_listeners(event.type, event.overlay);

                    // Extend Bounds
                    drawsearch_extend_bounds(
                        event.type,
                        event.overlay,
                        function (type, overlay) {
                            // Do a Search
                            drawsearch_boundary(type, overlay);
                        }
                    );
                }
            );
        }

        function drawsearch_delete_overlays() {
            for (let i = 0; i < loadedOverlays.length; i++)
                loadedOverlays[i].setMap(null);
        }

        function drawsearch_set_overlays(overlay) {
            // Remove Existing Overlays
            drawsearch_delete_overlays();

            // Add New Overlay
            loadedOverlays = [];
            loadedOverlays.push(overlay);
        }

        function drawsearch_set_overlay_listeners(type, overlay) {
            // POLYGON
            if (type === "polygon") {
                overlay.getPaths().forEach(function (path) {
                    google.maps.event.addListener(path, "insert_at", function () {
                        // Set Draw Flag
                        drawing = true;

                        // Extend Bounds
                        drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                            // Do a Search
                            drawsearch_boundary(type, overlay);
                        });
                    });

                    google.maps.event.addListener(path, "remove_at", function () {
                        // Set Draw Flag
                        drawing = true;

                        // Extend Bounds
                        drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                            // Do a Search
                            drawsearch_boundary(type, overlay);
                        });
                    });

                    google.maps.event.addListener(path, "set_at", function () {
                        // Set Draw Flag
                        drawing = true;

                        // Extend Bounds
                        drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                            // Do a Search
                            drawsearch_boundary(type, overlay);
                        });
                    });
                });
            }
            // Circle
            else if (type === "circle") {
                google.maps.event.addListener(overlay, "radius_changed", function () {
                    // Set Draw Flag
                    drawing = true;

                    // Extend Bounds
                    drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                        // Do a Search
                        drawsearch_boundary(type, overlay);
                    });
                });

                google.maps.event.addListener(overlay, "center_changed", function () {
                    // Set Draw Flag
                    drawing = true;

                    // Extend Bounds
                    drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                        // Do a Search
                        drawsearch_boundary(type, overlay);
                    });
                });
            }
            // Rectangle
            else if (type === "rectangle") {
                google.maps.event.addListener(overlay, "bounds_changed", function () {
                    // Set Draw Flag
                    drawing = true;

                    // Extend Bounds
                    drawsearch_extend_bounds(type, overlay, function (type, overlay) {
                        // Do a Search
                        drawsearch_boundary(type, overlay);
                    });
                });
            }
        }

        function drawsearch_extend_bounds(type, overlay, callback) {
            bounds = new google.maps.LatLngBounds();
            let mapsearch_freez = true;

            if (type === "polygon") {
                overlay.getPaths().forEach(function (path) {
                    let points = path.getArray();
                    for (let b in points) bounds.extend(points[b]);
                });
            } else if (type === "circle") {
                bounds.union(overlay.getBounds());
            } else if (type === "rectangle") {
                bounds.union(overlay.getBounds());
            }

            map.fitBounds(bounds);
            setTimeout(function () {
                mapsearch_freez = false;

                // Call Callback Function
                if (typeof callback === "function") callback(type, overlay);
            }, 500);
        }

        function drawsearch_boundary(type, overlay) {
            let request;

            if (type === "polygon") {
                let paths = [];

                overlay.getPaths().forEach(function (path) {
                    let points = path.getArray();
                    for (let b in points) {
                        paths.push(
                            new google.maps.LatLng(points[b].lat(), points[b].lng())
                        );
                    }
                });

                // Boundary Parameters
                request = "sf[shape]=polygon&sf[polygon]=" + paths.toString();
            } else if (type === "circle") {
                let radius = overlay.getRadius();
                let center = overlay.getCenter();

                let latitude = center.lat();
                let longitude = center.lng();

                // Boundary Parameters
                request =
                    "sf[shape]=circle&sf[circle_latitude]=" +
                    latitude +
                    "&sf[circle_longitude]=" +
                    longitude +
                    "&sf[circle_radius]=" +
                    radius;
            } else if (type === "rectangle") {
                // Calculating Bounds
                let bounds = overlay.getBounds();
                let ne = bounds.getNorthEast();
                let sw = bounds.getSouthWest();

                let lat_max = ne.lat();
                let lat_min = sw.lat();
                let lng_min = sw.lng();
                let lng_max = ne.lng();

                // Boundary Parameters
                request =
                    "sf[shape]=rectangle&sf[rect_min_latitude]=" +
                    lat_min +
                    "&sf[rect_max_latitude]=" +
                    lat_max +
                    "&sf[rect_min_longitude]=" +
                    lng_min +
                    "&sf[rect_max_longitude]=" +
                    lng_max;
            }

            // Send Search Request
            mapsearch_request(request);
        }

        let mapsearchFreez = true;
        let firstRun = true;

        function mapsearch() {
            // Boundary Search
            google.maps.event.addListener(map, "idle", function () {
                /**
                 * Idle event triggered after drawing an overlay,
                 * so we're going to disable the map search
                 * because a draw search happened already!
                 */
                if (drawing) {
                    drawing = false;
                    mapsearchFreez = true;
                }

                if (!mapsearchFreez) {
                    drawsearch_delete_overlays();
                    mapsearch_boundary();
                } else if (firstRun) {
                    // Release the Map Search
                    firstRun = false;
                    mapsearchFreez = false;
                }
            });
        }

        function mapsearch_boundary() {
            // Calculating Bounds
            let bounds = map.getBounds();
            let ne = bounds.getNorthEast();
            let sw = bounds.getSouthWest();

            let lat_max = ne.lat();
            let lat_min = sw.lat();
            let lng_min = sw.lng();
            let lng_max = ne.lng();

            // Min/Max values for Longitude
            if (lng_min > lng_max) {
                lng_min = -180;
                lng_max = 180;
            }

            // Min/Max values for Latitude
            if (lat_min > lat_max) {
                lat_max = 90;
                lat_min = -90;
            }

            // Trigger Event
            $("body").trigger("lsd-mapsearch", {
                ne: {
                    lat: lat_max,
                    lng: lng_max,
                },
                sw: {
                    lat: lat_min,
                    lng: lng_min,
                },
            });

            // Boundary Parameters
            let request =
                "sf[min_latitude]=" +
                lat_min +
                "&sf[max_latitude]=" +
                lat_max +
                "&sf[min_longitude]=" +
                lng_min +
                "&sf[max_longitude]=" +
                lng_max;
            mapsearch_request(request);
        }

        function mapsearch_request(request, clearKeys = []) {
            // Freez the Map Search
            mapsearchFreez = true;

            // Page Reset
            request += "&page=1";

            // View
            request +=
                "&view=" +
                (typeof $wrapper.data("view") === "undefined"
                    ? ""
                    : $wrapper.data("view"));

            // Push to History
            new ListdomPageHistory().push(
                "?" + request,
                lsdShouldUpdateAddressBar(settings.id),
                clearKeys
            );

            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            // Pagination Wrapper
            const $pagination = $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper");

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_ajax_search&" + req.get(request, settings.args, clearKeys),
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Don't Update Boundary
                    updateBounds = false;

                    // Reload Objects
                    reloadObjects(response.objects);

                    // Release the Map Search
                    setTimeout(function () {
                        mapsearchFreez = false;
                    }, 1000);

                    // Update Listings
                    const $skin = $("#lsd_skin" + settings.id);
                    lsdResetTimelineCarousel($skin);
                    $skin.find('.lsd-listing-wrapper').html(response.listings);

                    // Update Pagination
                    if($pagination.length) $pagination.replaceWith(response.pagination);
                    else $("#lsd_skin" + settings.id + " .lsd-list-wrapper").append(response.pagination);

                    // Hide or Show Load More
                    if (response.count === 0 || response.total <= response.count)
                        $loadMoreWrapper.addClass("lsd-util-hide");
                    else if (response.total > response.count)
                        $loadMoreWrapper.removeClass("lsd-util-hide");

                    // Loading Style
                    $wrapper.fadeTo(200, 1);

                    // Update the Next Page
                    $("#lsd_skin" + settings.id).data('next-page', response.next_page);

                    // Search Success Event
                    response.shortcode = settings.id;
                    window.dispatchEvent(new CustomEvent('lsd-search-success', {
                        detail: response
                    }));

                    // Trigger
                    listdom_onload();

                    // Trigger Connected Shortcodes Sync
                    if (typeof settings.connected_shortcodes !== 'undefined') {
                        for (const i in settings.connected_shortcodes) {
                            const shortcode_id = settings.connected_shortcodes[i];
                            $('body').trigger('lsd-sync', {
                                id: shortcode_id,
                                request: request,
                                clearKeys: clearKeys
                            });
                        }
                    }
                },
                error: function () {
                },
            });
        }

        function loadLayers(layers) {
            for (let i in layers) {
                // Layer Data
                let layer = layers[i];

                if (layer.type === "KML") {
                    // Add Layer
                    new google.maps.KmlLayer(layer.src, {
                        map: map,
                        preserveViewport: true,
                    });
                } else if (layer.type === "GPX") {
                    $.ajax({
                        type: "GET",
                        url: layer.src,
                        dataType: "XML",
                        success: function (xml) {
                            let points = [];
                            $(xml)
                            .find("trkpt")
                            .each(function () {
                                let lat = $(this).attr("lat");
                                let lon = $(this).attr("lon");
                                let p = new google.maps.LatLng(lat, lon);

                                points.push(p);
                            });

                            let poly = new google.maps.Polyline({
                                path: points,
                                strokeOpacity: settings.stroke_opacity,
                                strokeColor: settings.stroke_color,
                                strokeWeight: settings.stroke_weight,
                            });

                            poly.setMap(map);
                        },
                    });
                }
            }
        }

        return {
            id: settings.id,
            element: DOM,
            load: function (objects) {
                // Freez the Map Search
                mapsearchFreez = true;

                reloadObjects(objects);

                // Release the Map Search
                setTimeout(function () {
                    mapsearchFreez = false;
                }, 1000);
            },
            openListing: function (listingId) {
                const object = loadedObjectsById[String(listingId)];
                if (!object) return false;

                return openListingInfowindow(object);
            },
        };
    };
})(jQuery);

// Listdom SEARCH FORM PLUGIN
(function ($) {
    $.fn.listdomSearchForm = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                id: 0,
                shortcode: "",
                ajax: 0,
                sf: {},
                select2: {},
                nonce: ""
            },
            options
        );

        let $container = $(".lsd-search-" + settings.id);
        let $form = $container.find($("form:visible"));

        const isCompareModal = $container.closest('#lsdaddcmp-add-modal').length > 0;
        if (isCompareModal && !parseInt(settings.ajax, 10)) settings.ajax = 1;

        setListeners();

        function setListeners() {
            const isiOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            const bindSearchInputClearButtons = function () {
                const toggleClearButton = function ($input) {
                    const inputId = $input.attr('id');
                    if (!inputId) return;

                    const hasValue = (($input.val() || '').toString().trim() !== '');
                    $form.find(`.lsd-search-input-clear[data-for="${inputId}"]`).toggleClass('lsd-util-hide', !hasValue);
                };

                $form.find('.lsd-search-input-clear').off('click.lsdSearchClear').on('click.lsdSearchClear', function (e) {
                    e.preventDefault();

                    const inputId = ($(this).data('for') || '').toString();
                    if (!inputId) return;

                    const $input = $form.find(`#${inputId}`);
                    if (!$input.length) return;

                    const input = $input.get(0);

                    if (input && input._flatpickr)
                    {
                        const instance = input._flatpickr;

                        instance.clear();
                        instance.close();

                        input.value = '';
                        if (instance.altInput) instance.altInput.value = '';

                        $(input).trigger('input').trigger('change');
                    }
                    else
                    {
                        $input.val('').trigger('change').trigger('input').focus();
                    }
                });

                $form.find('.lsd-search-input-clear-wrap input').off('input.lsdSearchClear change.lsdSearchClear').on('input.lsdSearchClear change.lsdSearchClear', function () {
                    toggleClearButton($(this));
                }).each(function () {
                    toggleClearButton($(this));
                });
            };

            const setNativeSelectState = function ($select) {
                if ($select.prop('multiple')) return;

                const value = $select.val();
                const isEmpty = Array.isArray(value) ? value.filter(Boolean).length === 0 : !value;

                $select.toggleClass('lsd-native-select-empty', isEmpty);
            };

            $container
            .find('select[data-enhanced=1]')
            .each(function () {
                const $select = $(this);

                $select.off('.lsdNativeSelectFallback');

                if (isiOS) {
                    if ($select.data('select2')) $select.select2('destroy');

                    $select.addClass('lsd-native-select-fallback');
                    setNativeSelectState($select);
                    $select.on('change.lsdNativeSelectFallback', function () {
                        setNativeSelectState($(this));
                    });
                    return;
                }

                $select.removeClass('lsd-native-select-fallback lsd-native-select-empty');

                $select.select2({
                    allowClear: true,
                    placeholder: $select.attr('placeholder'),
                    minimumResultsForSearch: 0,
                    shouldFocusInput: () => false,
                    "language": {
                        "noResults": () => settings.select2.noResults
                    },
                });
            });

            initRadiusFields();
            bindSearchInputClearButtons();

            // More Options
            $container
            .find($(".lsd-search-row-more-options"))
            .off("click")
            .on("click", function () {
                const $toggle = $(this);
                const type = $(this).data('type');
                const width = $(this).data('width');
                const target = $(this).data('for');

                // Popup
                if (type === 'popup')
                {
                    const popupId = $toggle.attr('data-popup-id');
                    let $wrapper = popupId ? $("#" + popupId) : $();

                    if (!$wrapper.length)
                    {
                        const $search = $toggle.closest(".lsd-search");
                        const $device = $toggle.closest($container).find($(target)).first();
                        const $more = $device.find($(".lsd-search-included-in-more")).first();

                        if ($more.length)
                        {
                            const uniqueId = "lsd-search-popup-" + Date.now() + "-" + Math.floor(Math.random() * 1000);
                            const searchClasses = ($search.attr("class") || "").trim();
                            const $popupWrapper = $("<div>", {id: uniqueId})
                                .addClass("lsd-popup-wrapper lsd-modal lsd-search-modal");

                            if (searchClasses) $popupWrapper.addClass(searchClasses);

                            $popupWrapper.append($("<div>").addClass("lsd-modal-content"));

                            $more.wrapAll($popupWrapper);

                            $wrapper = $("#" + uniqueId);
                            $toggle.attr("data-popup-id", uniqueId);

                            const $stackTargets = $wrapper
                                .parents(".lsd-search-bar-sticky, .lsd-search-bar-wrapper")
                                .add($wrapper.closest(".lsd-search"));

                            $wrapper.data("lsdSearchPopupStackTargets", $stackTargets);
                            $wrapper
                                .off("listdom:modal:opened.lsdSearchPopup listdom:modal:closed.lsdSearchPopup")
                                .on("listdom:modal:opened.lsdSearchPopup", function ()
                                {
                                    const $targets = $(this).data("lsdSearchPopupStackTargets");
                                    if ($targets && $targets.length) $targets.addClass("lsd-search-popup-active");
                                })
                                .on("listdom:modal:closed.lsdSearchPopup", function ()
                                {
                                    const $targets = $(this).data("lsdSearchPopupStackTargets");
                                    if ($targets && $targets.length) $targets.removeClass("lsd-search-popup-active");
                                });

                            $wrapper.find(".lsd-modal-content")
                                .css("width", width + "vw")
                                .prepend('<a href="#" class="lsd-modal-close">&times;</a>');

                            if (typeof window.lsdInitFlatpickr === 'function') window.lsdInitFlatpickr($wrapper);

                            $more.fadeIn();
                        }
                    }

                    const $stackTargets = $wrapper.data("lsdSearchPopupStackTargets");
                    if ($stackTargets && $stackTargets.length) $stackTargets.addClass("lsd-search-popup-active");

                    if (typeof ListdomModal !== 'undefined') ListdomModal.open($wrapper);
                    else $wrapper.fadeIn();
                }
                // Slide
                else
                {
                    if ($(this).children("span").children("i").hasClass("fa-plus"))
                    {
                        $(this)
                            .children("span")
                            .children("i")
                            .removeClass("fa-plus")
                            .addClass("fa-minus");
                    }
                    else
                    {
                        $(this)
                            .children("span")
                            .children("i")
                            .removeClass("fa-minus")
                            .addClass("fa-plus");
                    }

                    $(this)
                        .parent()
                        .children()
                        .next(".lsd-search-included-in-more")
                        .slideToggle();
                }
            });

            // True False
            $container.find($('.lsd-true-false-search')).each(function () {
                let checkbox = $(this).find('.lsd-search-checkbox-input');

                toggleHiddenInputs($(this));

                $(document).on("change", checkbox, function () {
                    toggleHiddenInputs($(this));
                });
            });

            // Range Slider
            $container.find('.lsd-range-slider-search').each(function () {
                let rangeSlider = this;
                let $rangeSlider = $(rangeSlider);

                let dataMin = parseFloat($rangeSlider.data("min"));
                let dataMax = parseFloat($rangeSlider.data("max"));
                let dataDefault = $rangeSlider.data("default") !== '' ? parseFloat($rangeSlider.data("default")) : dataMin;
                let dataMaxDefault = $rangeSlider.data("max-default") !== '' ? parseFloat($rangeSlider.data("max-default")) : dataMax;
                let dataStep = parseFloat($rangeSlider.data("step"));

                let rangeInputMin = $rangeSlider.parent().find('.lsd-range-min-value');
                let rangeInputMax = $rangeSlider.parent().find('.lsd-range-max-value');
                let minDisplay = $rangeSlider.parent().find('.lsd-range-min');
                let maxDisplay = $rangeSlider.parent().find('.lsd-range-max');

                noUiSlider.create(rangeSlider, {
                    start: [dataDefault, dataMaxDefault],
                    connect: true,
                    step: dataStep,
                    range: {
                        'min': dataMin,
                        'max': dataMax
                    },
                    format: {
                        to: value => parseFloat(value).toFixed(0),
                        from: value => parseFloat(value)
                    }
                });

                rangeSlider.noUiSlider.on('update', function (values) {
                    let min = values[0];
                    let max = values[1];

                    minDisplay.text(min);
                    maxDisplay.text(max);
                });

                rangeSlider.noUiSlider.on('change', function (values) {
                    rangeInputMin.val(values[0]).trigger('change');
                    rangeInputMax.val(values[1]).trigger('change');
                });
            });

            // Hierarchical Dropdowns
            $container.find($(".lsd-hierarchical-dropdowns")).each(function () {
                let $wrapper = $(this);
                let id = $wrapper.data("id");
                let name = $wrapper.data("name");
                let taxonomy = $wrapper.data("for");
                let hide_empty = $wrapper.data("hide-empty");
                let max_levels = $wrapper.data("max-levels");
                let level_status = $wrapper.data("level-status");

                $wrapper.find("select, .select2").each(function () {
                    let $dropdown = $(this);

                    // Add loading spinner to each select dropdown
                    let $loadingSpinner = $('<div class="lsd-loading-spinner lsd-util-hide"><i class="fas fa-spinner fa-spin"></i></div>');

                    if ($dropdown.data('select2')) $dropdown.next('.select2-container').after($loadingSpinner);
                    else $dropdown.after($loadingSpinner);

                    $dropdown.on("change select2:clear", function () {
                        let value = $dropdown.val();
                        let level = parseInt($dropdown.data("level"));

                        if (level > 1 && !value) $dropdown.attr("name", "");
                        else $dropdown.attr("name", name);

                        for (let l = level + 1; l <= max_levels; l++) {
                            let $next = $wrapper.find("#" + id + "_" + l);
                            if ($next.length) {
                                $next.val("");
                                $next.hide().trigger("change");
                                $next.find("option").remove();
                            }
                        }

                        // Already Max Level
                        if (level >= max_levels) return;

                        // Next Level Doesn't Exist
                        if (!$wrapper.has("#" + id + "_" + (level + 1))) return;

                        // Next Level Dropdown
                        let $next_level = $wrapper.find("#" + id + "_" + (level + 1));

                        // Set No Value
                        $next_level.val("");
                        $next_level.hide().trigger("change");

                        if (value) {
                            // Remove All Options
                            $next_level.find($("option")).remove();
                            $loadingSpinner.removeClass('lsd-util-hide');

                            $.ajax({
                                url: settings.ajax_url,
                                data:
                                    "action=lsd_hierarchical_terms&taxonomy=" +
                                    taxonomy +
                                    "&parent=" +
                                    value +
                                    "&hide_empty=" +
                                    hide_empty +
                                    "&_wpnonce=" +
                                    settings.nonce,
                                dataType: "json",
                                type: "post",
                                success: function (response) {
                                    let options =
                                        '<option value="">' +
                                        $dropdown.nextAll("select").first().attr("placeholder") +
                                        "</option>";
                                    response.items.forEach(function (item) {
                                        options +=
                                            '<option class="lsd-option lsd-parent-' +
                                            item.parent +
                                            '" value="' +
                                            item.id +
                                            '">' +
                                            item.name +
                                            "</option>";
                                    });

                                    // Add Options
                                    $next_level.html(options);

                                    // Show Dropdown
                                    if (response.found) {
                                        $next_level.show();

                                        if ($next_level.data('select2')) $next_level.next('.select2-container').show();
                                    }
                                    // Hide Dropdown
                                    else {
                                        $next_level.show();
                                        $next_level.empty();
                                        $next_level.append('<option value="">No Results</option>');

                                        if ($next_level.data('select2')) $next_level.next('.select2-container').show();
                                    }

                                    // Trigger
                                    $next_level.trigger("change");

                                    // Hide the loading spinner when the AJAX request finishes
                                    $loadingSpinner.addClass('lsd-util-hide');
                                },
                                error: function () {
                                },
                            });
                        }
                    });
                });

                // Trigger on Load
                $wrapper.find("select").each(function () {
                    let $dropdown = $(this);

                    let value = $dropdown.val();
                    let level = parseInt($dropdown.data("level"));

                    if (level >= 2) {
                        $dropdown.empty();
                        $dropdown.append('<option value="">No Results</option>');
                    }

                    // Check if the level status is 0, and set visibility accordingly
                    if (level_status === 'all') return;

                    // Skip First Level
                    if (level === 1) return;

                    if (!value) $dropdown.attr("name", "");
                    else $dropdown.attr("name", name);

                    // Previous Level Dropdown
                    let $prev_level = $wrapper.find("#" + id + "_" + (level - 1));
                    let prev_value = $prev_level.val();

                    // Show Dropdown
                    let hasValidOptions = $dropdown.find("option.lsd-parent-" + prev_value).length > 0;

                    if (hasValidOptions) {
                        $dropdown.show();

                        if ($dropdown.data('select2')) {
                            $dropdown.next('.select2-container').show();
                            $dropdown.trigger('change.select2');
                        } else if ($dropdown.hasClass("use-select2")) {
                            $dropdown.select2();
                        }
                    } else {
                        $dropdown.hide();

                        // Hide the Select2 container if it's active
                        if ($dropdown.data('select2')) {
                            $dropdown.next('.select2-container').hide();
                        }
                    }
                });
            });

            // Attach Clear All button handler
            $form.on("click", ".lsd-search-clear-all", function (e) {
                e.preventDefault();

                // Clear inputs
                $form.find("input[type=text], input[type=search], input[type=email], input[type=url], input[type=tel], input[type=number], input[type=date], input[type=time], input[type=datetime-local]").val("").trigger("input");
                $form.find('input[type=hidden]:not([data-lsd-persistent="1"])').val("");

                // Clear checkboxes and radios
                $form.find("input[type=checkbox], input[type=radio]").prop("checked", false);

                // Reset Select2
                $form.find("select").each(function () {
                    $(this).val('').trigger('change');
                });

                // Reset noUiSlider range sliders
                $form.find('.lsd-range-slider-search').each(function () {
                    const $slider = $(this)[0];
                    const $el = $(this);
                    const defaultMin = $el.data("default") !== '' ? parseFloat($el.data("default")) : parseFloat($el.data("min"));
                    const defaultMax = $el.data("max-default") !== '' ? parseFloat($el.data("max-default")) : parseFloat($el.data("max"));

                    if ($slider.noUiSlider) {
                        $slider.noUiSlider.set([defaultMin, defaultMax]);
                    }

                    // Clear hidden inputs for range
                    $el.parent().find('.lsd-range-min-value, .lsd-range-max-value').val('');
                });

                // Reset hierarchical dropdowns
                $form.find('.lsd-hierarchical-dropdowns select').each(function () {
                    $(this).val('').trigger('change');
                });

                // Reset hidden inputs on true/false checkboxes
                $form.find(".lsd-true-false-search").each(function () {
                    const $checkbox = $(this).find(".lsd-search-checkbox-input");
                    const $input = $(this).find(".lsd-search-checkbox-hidden");

                    $checkbox.prop("checked", false).val(0);
                    $input.prop("disabled", false);
                });

                if (settings.ajax) {
                    search();
                } else {
                    $form.trigger("submit");
                }
            });

            // AJAX
            if (
                (settings.ajax && settings.shortcode)
                || (typeof settings.connected_shortcodes !== 'undefined' && settings.connected_shortcodes.length)
            ) {
                ajax();
            }
        }

        function getManagedQueryKeys() {
            let keys = ['page', 'paged'];

            $form.find(':input[name]').each(function () {
                let name = $(this).attr('name');
                if (typeof name === 'string' && name.length) keys.push(name);
            });

            return [...new Set(keys)];
        }

        function initRadiusFields() {
            const $fields = $container.find('.lsd-radius-search-field');
            if (!$fields.length) return;

            const getRadiusDropdownController = function ($field, $address) {
                const $popup = $field.find('.lsd-radius-search-address-popup').first();
                const loadingText =
                    ($popup.length && $popup.data('loading-text')) || '';

                if (typeof window.lsdGetAddressDropdownController === 'function') {
                    const controller = window.lsdGetAddressDropdownController($field, {
                        dropdown: $popup,
                        trigger: $address,
                        storeKey: 'lsdRadiusDropdownController',
                        dropdownOptions: { loadingText: loadingText },
                    });

                    if (controller) return controller;
                }

                return {
                    $element: $popup,
                    reset: function () { },
                    populate: function () { },
                    markSelected: function () { },
                    isUpdating: function () { return false; },
                    selectItem: function () { },
                    hide: function () { },
                    showLoading: function () { },
                    show: function () { },
                };
            };

            const getGeocoder = (function () {
                let geocoder = null;
                return function () {
                    if (geocoder) return geocoder;
                    if (
                        typeof google === 'undefined' ||
                        typeof google.maps === 'undefined' ||
                        typeof google.maps.Geocoder !== 'function'
                    ) return null;

                    geocoder = new google.maps.Geocoder();
                    return geocoder;
                };
            })();

            const extractLatLng = function (location) {
                if (!location) return { latitude: '', longitude: '' };

                const latFn = typeof location.lat === 'function' ? location.lat : null;
                const lngFn = typeof location.lng === 'function' ? location.lng : null;

                return {
                    latitude: latFn ? latFn.call(location) : location.lat || '',
                    longitude: lngFn ? lngFn.call(location) : location.lng || ''
                };
            };

            const resolveAddressFromCoordinates = function (lat, lng, provider, callback) {
                const latNum = parseFloat(lat);
                const lngNum = parseFloat(lng);

                if (isNaN(latNum) || isNaN(lngNum)) {
                    callback('');
                    return;
                }

                const fallback = function () {
                    jQuery
                    .getJSON('https://nominatim.openstreetmap.org/reverse', {
                        format: 'json',
                        lat: latNum,
                        lon: lngNum
                    })
                    .done(function (data) {
                        const formatted =
                            (data && data.display_name) ||
                            (data && data.address && data.address.road) ||
                            '';
                        callback(formatted || '');
                    })
                    .fail(function () { callback(''); });
                };

                if (provider === 'googlemap') {
                    const geocoder = getGeocoder();
                    if (geocoder) {
                        geocoder.geocode({ location: { lat: latNum, lng: lngNum } }, function (results, status) {
                            if (status === 'OK' && results && results.length) {
                                const result = results[0];
                                const formatted =
                                    result.formatted_address ||
                                    result.formattedAddress ||
                                    (result.name ? result.name : '');
                                callback(formatted);
                                return;
                            }
                            fallback();
                        });
                        return;
                    }
                }

                fallback();
            };

            const resolveCoordinatesFromAddress = function (address, provider, callback) {
                if (!address) {
                    callback(null);
                    return;
                }

                const fallback = function () {
                    jQuery
                    .getJSON('https://nominatim.openstreetmap.org/search', {
                        format: 'json',
                        limit: 1,
                        q: address
                    })
                    .done(function (data) {
                        if (Array.isArray(data) && data.length) {
                            const item = data[0];
                            callback({
                                latitude: item.lat || '',
                                longitude: item.lon || '',
                                formatted: item.display_name || address
                            });
                            return;
                        }
                        callback(null);
                    })
                    .fail(function () { callback(null); });
                };

                if (provider === 'googlemap') {
                    const geocoder = getGeocoder();
                    if (geocoder) {
                        geocoder.geocode({ address: address }, function (results, status) {
                            if (status === 'OK' && results && results.length) {
                                const result = results[0];
                                const coordinates = extractLatLng(result.geometry && result.geometry.location);
                                const formatted =
                                    result.formatted_address ||
                                    result.formattedAddress ||
                                    (result.name ? result.name : address);

                                callback({
                                    latitude: coordinates.latitude,
                                    longitude: coordinates.longitude,
                                    formatted: formatted
                                });
                                return;
                            }
                            fallback();
                        });
                        return;
                    }
                }

                fallback();
            };

            // ───────────────────────────────────────────────
            // Attach these helpers globally to reuse in setupRadiusAutocomplete
            // ───────────────────────────────────────────────
            window.lsdRadiusShared = {
                getRadiusDropdownController,
                extractLatLng,
                getGeocoder,
                resolveAddressFromCoordinates,
                resolveCoordinatesFromAddress,
            };

            // Initialize core field logic
            $fields.each(function () {
                const $field = $(this);
                const $address = $field.find('.lsd-radius-search-address');
                const $latitude = $field.find('.lsd-radius-search-latitude');
                const $longitude = $field.find('.lsd-radius-search-longitude');
                const $locate = $field.find('.lsd-radius-search-locate');

                if (!$address.length) return;

                const autocompleteEnabled = !!$address.data('autocomplete');

                const dropdown = getRadiusDropdownController($field, $address);
                dropdown.markSelected($address.val(), { lat: $latitude.val(), lon: $longitude.val() });

                const rawProvider = ($address.data('map-provider') || '').toString().toLowerCase();
                const provider = rawProvider === 'googlemap' ? 'googlemap' : 'leaflet';

                $field.data('lsd-map-provider', provider);
                const gpsLabel = $address.data('gps-label');
                const messages = {
                    unsupported: $locate.data('error-unsupported'),
                    denied: $locate.data('error-denied'),
                    failed: $locate.data('error-failed')
                };

                let googleServices = null;
                let googlePredictionRequest = null;
                let googlePlacesElement = null;
                let applyGooglePlace = null;

                const initialAddress = ($address.val() || '').toString().trim();
                if (initialAddress && !$latitude.val() && !$longitude.val()) {
                    resolveCoordinatesFromAddress(initialAddress, provider, function (result) {
                        if (!result || !result.latitude || !result.longitude) return;

                        $latitude.val(result.latitude);
                        $longitude.val(result.longitude);
                        $address.data('lsd-autocomplete-selected', true);

                        if (autocompleteEnabled)
                        {
                            const formatted = result.formatted || initialAddress;
                            if (formatted) $address.val(formatted);
                        }

                        dropdown.markSelected($address.val(), {
                            lat: result.latitude,
                            lon: result.longitude
                        });
                    });
                }

                const ensureGoogleServices = function () {
                    if (typeof window.lsdAddressAutocompleteSources === 'undefined') return null;

                    if (!googlePlacesElement) googlePlacesElement = document.createElement('div');

                    googleServices = window.lsdAddressAutocompleteSources.ensureGoogleServices({
                        services: googleServices || {},
                        placesElement: googlePlacesElement
                    }) || googleServices;

                    if (googleServices) googleServices.placesElement = googlePlacesElement;

                    return googleServices;
                };

                $address.off('input.lsd-radius-address').on('input.lsd-radius-address', function () {
                    dropdown.reset();
                    $address.data('lsd-autocomplete-selected', false);
                    $latitude.val('');
                    $longitude.val('');
                });

                $address.on('change', function () {
                    if ($address.data('lsd-autocomplete-selected')) return;
                    $latitude.val('');
                    $longitude.val('');

                    const value = $address.val();
                    if (!value || !value.length) return;

                    const requestKey = Date.now();
                    $address.data('lsd-geocode-request', requestKey);

                    resolveCoordinatesFromAddress(value, provider, function (result) {
                        if ($address.data('lsd-geocode-request') !== requestKey) return;
                        $address.removeData('lsd-geocode-request');

                        if (!result || !result.latitude || !result.longitude) {
                            return;
                        }

                        $latitude.val(result.latitude);
                        $longitude.val(result.longitude);
                        $address.data('lsd-autocomplete-selected', true);

                        if (autocompleteEnabled && result.formatted)
                        {
                            $address.val(result.formatted);
                        }

                        dropdown.markSelected($address.val(), {
                            lat: result.latitude,
                            lon: result.longitude
                        });

                        $address.trigger('change');
                    });
                });

                if (dropdown.$element.length) {
                    dropdown.$element
                    .off('lsd-autocomplete-select.lsd-radius-dropdown')
                    .on('lsd-autocomplete-select.lsd-radius-dropdown', function (event, item) {
                        if (dropdown.isUpdating()) return;

                        const hasItem = item && typeof item === 'object';
                        const optionValue = hasItem ? item.value || item.label || '' : '';
                        const optionLabel = hasItem ? item.label || item.value || '' : '';

                        if (!optionValue) {
                            $address.val('');
                            $latitude.val('');
                            $longitude.val('');
                            $address.data('lsd-autocomplete-selected', false);
                            $address.trigger('change');
                            return;
                        }

                        $address.val(optionLabel || optionValue);

                        const optionLat = hasItem && item.lat ? item.lat : '';
                        const optionLon = hasItem && item.lon ? item.lon : '';
                        const optionPlaceId = hasItem && item.placeId ? item.placeId : '';

                        if (optionLat) $latitude.val(optionLat);
                        if (optionLon) $longitude.val(optionLon);

                        if (optionLat && optionLon) {
                            $address.data('lsd-autocomplete-selected', true);
                            dropdown.markSelected(optionValue, {
                                lat: optionLat,
                                lon: optionLon,
                                placeId: optionPlaceId
                            });
                            $address.trigger('change');
                            return;
                        }

                        $address.data('lsd-autocomplete-selected', false);

                        if (provider === 'googlemap' && optionPlaceId) {
                            const services = ensureGoogleServices();
                            if (
                                services &&
                                services.places &&
                                typeof services.places.getDetails === 'function'
                            ) {
                                dropdown.hide();

                                const request = {
                                    placeId: optionPlaceId,
                                    fields: ['geometry', 'formatted_address', 'name', 'place_id']
                                };

                                if (
                                    typeof google !== 'undefined' &&
                                    typeof google.maps !== 'undefined' &&
                                    typeof google.maps.places !== 'undefined' &&
                                    typeof google.maps.places.AutocompleteSessionToken === 'function' &&
                                    services.sessionToken
                                ) {
                                    request.sessionToken = services.sessionToken;
                                }

                                const statusSource = services.statusEnum
                                    ? services.statusEnum
                                    : (typeof google !== 'undefined' &&
                                        typeof google.maps !== 'undefined' &&
                                        typeof google.maps.places !== 'undefined' &&
                                        google.maps.places.PlacesServiceStatus)
                                        ? google.maps.places.PlacesServiceStatus
                                        : {};

                                services.places.getDetails(request, function (result, status) {
                                    if (status === statusSource.OK && result) {
                                        if (typeof applyGooglePlace === 'function') {
                                            applyGooglePlace(result);
                                            return;
                                        }
                                    }

                                    $address.data('lsd-autocomplete-selected', false);
                                    dropdown.markSelected(optionValue, {placeId: optionPlaceId});
                                    $address.trigger('change');
                                });

                                return;
                            }

                            dropdown.markSelected(optionValue, {placeId: optionPlaceId});
                        }

                        $address.trigger('change');
                    });
                }

                if ($locate.length && typeof window.lsdBindLocateControl === 'function') {
                    window.lsdBindLocateControl($locate, {
                        messages: messages,
                        expectsFinalize: true,
                        setLoading: function (loading) {
                            $locate.toggleClass('lsd-radius-search-locate--loading', !!loading);
                        },
                        showError: function (message) {
                            if (message) showRadiusError(message, true);
                        },
                        onDenied: function () {
                            $latitude.val('');
                            $longitude.val('');
                        },
                        onError: function () {
                            $latitude.val('');
                            $longitude.val('');
                        },
                        onSuccess: function (lat, lng, done) {
                            $latitude.val(lat);
                            $longitude.val(lng);
                            $address.data('lsd-autocomplete-selected', true);

                            resolveAddressFromCoordinates(
                                lat,
                                lng,
                                provider,
                                function (resolvedAddress) {
                                    if (resolvedAddress) $address.val(resolvedAddress);
                                    else if (!$address.val()) $address.val(gpsLabel);

                                    dropdown.markSelected($address.val(), {
                                        lat: $latitude.val(),
                                        lon: $longitude.val()
                                    });

                                    $address.trigger('change');
                                    done();
                                }
                            );
                        }
                    });
                }
            });

            setupRadiusAutocomplete($fields);
        }

        function setupRadiusAutocomplete($fields) {
            if (!$fields.length) return;
            if (!window.lsdAddressAutocompleteSources || !window.lsdGetAddressDropdownController) return;

            $fields.each(function () {
                const $field = $(this);
                if ($field.data('lsd-autocomplete-initialized')) return;

                const $address = $field.find('.lsd-radius-search-address');
                const $latitude = $field.find('.lsd-radius-search-latitude');
                const $longitude = $field.find('.lsd-radius-search-longitude');
                if (!$address.length) return;

                const autocompleteEnabled = !!$address.data('autocomplete');
                if (!autocompleteEnabled) {
                    $field.data('lsd-autocomplete-initialized', true);
                    return;
                }

                const dropdown = window.lsdGetAddressDropdownController($field, {
                    dropdown: $field.find('.lsd-radius-search-address-popup').first(),
                    trigger: $address,
                    storeKey: 'lsdRadiusDropdownController'
                });

                const providerRaw = ($address.data('map-provider') || '').toString().toLowerCase();
                const hasGoogleAutocomplete = !!$address.data('autocomplete');
                const provider = (providerRaw === 'googlemap' && hasGoogleAutocomplete) ? 'googlemap' : 'openstreetmap';

                let currentRequest = null;

                $address.off('input.lsd-autocomplete').on('input.lsd-autocomplete', function () {
                    const term = $address.val();

                    dropdown.reset();
                    $address.data('lsd-autocomplete-selected', false);

                    if (!term || term.length < 3) return;

                    dropdown.showLoading();

                    if (currentRequest && typeof currentRequest.cancel === 'function') {
                        currentRequest.cancel();
                    }

                    currentRequest = window.lsdAddressAutocompleteSources.fetch(provider, term, { minLength: 3 });

                    if (!currentRequest || !currentRequest.promise) return;

                    currentRequest.promise
                    .done(function (response) {
                        if ($address.val() !== term) return; // ignore outdated responses

                        const items = response && response.items ? response.items : [];
                        if (!items.length) {
                            dropdown.reset();
                            return;
                        }

                        dropdown.populate(items);
                    })
                    .fail(function () {
                        dropdown.reset();
                    });
                });

                // Handle selection
                dropdown.$element
                .off('lsd-autocomplete-select.lsd-radius-dropdown')
                .on('lsd-autocomplete-select.lsd-radius-dropdown', function (event, item) {
                    if (!item) {
                        $address.val('');
                        $latitude.val('');
                        $longitude.val('');
                        $address.data('lsd-autocomplete-selected', false);
                        $address.trigger('change');
                        return;
                    }

                    $address.val(item.label || item.value || '');

                    if (item.lat && item.lon) {
                        $latitude.val(item.lat);
                        $longitude.val(item.lon);
                        $address.data('lsd-autocomplete-selected', true);
                    } else {
                        $latitude.val('');
                        $longitude.val('');
                        $address.data('lsd-autocomplete-selected', false);
                    }

                    $address.trigger('change');
                });

                $field.data('lsd-autocomplete-initialized', true);
            });
        }

        function showRadiusError(message, preferNative) {
            if (!message) return;

            new ListdomToast(message, {type: 'lsd-error'});
        }

        function ajax() {
            // On The Fly
            if (settings.ajax === 2) {
                $form.on("change", ":input", function (e) {
                    e.preventDefault();
                    search();
                });

                $form.on("paste", ":input", function (e) {
                    setTimeout(() => {
                        search();
                    }, 50);
                });
            }

            const closeSearchPopups = function ()
            {
                $('.lsd-popup-wrapper').each(function ()
                {
                    const $popup = $(this);
                    const $stackTargets = $popup.data("lsdSearchPopupStackTargets");

                    if ($stackTargets && $stackTargets.length) $stackTargets.removeClass("lsd-search-popup-active");

                    $popup.fadeOut();
                });
            };

            // On Submit
            $form.on("submit", function (e) {
                e.preventDefault();
                search();

                closeSearchPopups();
            });
        }

        function search() {
            // Listdom Request Plugin
            let req = new ListdomRequest(settings.shortcode, settings);
            let clearKeys = getManagedQueryKeys();

            let $skin = $("#lsd_skin" + settings.shortcode);
            let $wrapper = $("#lsd_skin" + settings.shortcode + " .lsd-listing-wrapper");
            let $loadMoreWrapper = $("#lsd_skin" + settings.shortcode + " .lsd-load-more-wrapper");
            const $pagination = $skin.find($(".lsd-numeric-pagination-wrapper"));

            // Add loading Class
            $wrapper.addClass("lsd-loading");

            // Push to History
            new ListdomPageHistory().push(
                "?" + $form.serialize(),
                lsdShouldUpdateAddressBar(settings.id),
                clearKeys
            );

            // Trigger Connected Shortcodes Sync
            if (typeof settings.connected_shortcodes !== 'undefined' && settings.connected_shortcodes.length) {
                for (const i in settings.connected_shortcodes) {
                    const shortcode_id = settings.connected_shortcodes[i];
                    $('body').trigger('lsd-sync', {
                        id: shortcode_id,
                        request: $form.serialize() + "&page=1",
                        clearKeys: clearKeys
                    });
                }
                // Search
            } else {
                $.ajax({
                    url: settings.ajax_url,
                    data:
                        "action=lsd_ajax_search&" +
                        req.get($form.serialize() + "&page=1&view=" + $skin.data('view'), "", clearKeys),
                    dataType: "json",
                    type: "post",
                    success: function (response) {
                        // Remove Loading Class
                        $wrapper.removeClass("lsd-loading");

                        lsdResetTimelineCarousel($skin);

                        // Display Items
                        $wrapper.html(response.listings);

                        // Masonry Filters
                        if ($skin.hasClass("lsd-masonry-view-wrapper")) {
                            if (typeof response.filters !== "undefined") {
                                const $filtersWrapper = $skin.find(".lsd-masonry-filters");

                                if ($filtersWrapper.length) {
                                    if (response.filters) $filtersWrapper.replaceWith(response.filters);
                                    else $filtersWrapper.remove();
                                } else if (response.filters) {
                                    $skin.find(".lsd-list-wrapper").prepend(response.filters);
                                }
                            }
                        }

                        // Update Pagination
                        if ($pagination.length) $pagination.replaceWith(response.pagination);
                        else $skin.find($('.lsd-list-wrapper')).append(response.pagination);

                        // Hide Load More
                        if (response.count === 0 || response.total <= response.count)
                            $loadMoreWrapper.addClass("lsd-util-hide");
                        else if (response.total > response.count)
                            $loadMoreWrapper.removeClass("lsd-util-hide");

                        // Update the Next Page
                        $wrapper.data("next-page", response.next_page);
                        $skin.data("next-page", response.next_page);

                        // Map Objects
                        new ListdomMaps(settings.shortcode).load(response.objects);

                        // Search Success Event
                        response.shortcode = settings.shortcode;
                        window.dispatchEvent(new CustomEvent('lsd-search-success', {
                            detail: response
                        }));

                        // Trigger
                        listdom_onload();
                    },
                    error: function (err) {
                    }
                });
            }
        }

        function toggleHiddenInputs($element) {
            const $checkbox = $element.find(".lsd-search-checkbox-input");
            const $hiddenInput = $element.find(".lsd-search-checkbox-hidden");

            if ($checkbox.is(":checked")) {
                $hiddenInput.prop("disabled", true);
                $checkbox.val(1);
            } else {
                $hiddenInput.prop("disabled", false);
                $checkbox.val(0);
            }
        }
    };
})(jQuery);

(function () {
    window.lsdDashboardApplySidebar = function ($dashboards)
    {
        if (typeof jQuery === 'undefined') return;

        const $ = jQuery;
        $dashboards = $dashboards && $dashboards.length ? $dashboards : $('.lsd-dashboard');

        const dropdownNamespace = '.lsdDashboardDropdown';

        function closeAllDropdowns()
        {
            $('.lsd-dashboard-menu-more.lsd-open').each(function ()
            {
                const $item = $(this);
                const $trigger = $item.find('.lsd-dashboard-menu-more-trigger').first();

                $item.removeClass('lsd-open');
                if ($trigger.length) $trigger.attr('aria-expanded', 'false');
            });
        }

        function bindDropdown($container)
        {
            const $dropdownItems = $container.find('.lsd-dashboard-menu-more');
            if (!$dropdownItems.length) return;

            $dropdownItems.each(function ()
            {
                const $item = $(this);
                const $trigger = $item.find('.lsd-dashboard-menu-more-trigger').first();
                const $links = $item.find('.lsd-dashboard-menu-more-list a');

                if ($trigger.length)
                {
                    $trigger.off('click' + dropdownNamespace).on('click' + dropdownNamespace, function (event)
                    {
                        event.preventDefault();
                        event.stopPropagation();

                        const isOpen = $item.hasClass('lsd-open');

                        closeAllDropdowns();

                        if (!isOpen)
                        {
                            $item.addClass('lsd-open');
                            $trigger.attr('aria-expanded', 'true');
                        }
                    });

                    if ($item.hasClass('lsd-open')) $trigger.attr('aria-expanded', 'true');
                    else $trigger.attr('aria-expanded', 'false');
                }

                $links.off('click' + dropdownNamespace).on('click' + dropdownNamespace, function ()
                {
                    const $parent = $(this).closest('.lsd-dashboard-menu-more');
                    if (!$parent.length) return;

                    const $toggle = $parent.find('.lsd-dashboard-menu-more-trigger').first();
                    $parent.removeClass('lsd-open');
                    if ($toggle.length) $toggle.attr('aria-expanded', 'false');
                });
            });

            $(document).off('click' + dropdownNamespace).on('click' + dropdownNamespace, function (event)
            {
                if ($(event.target).closest('.lsd-dashboard-menu-more').length) return;
                closeAllDropdowns();
            });

            $(document).off('keydown' + dropdownNamespace).on('keydown' + dropdownNamespace, function (event)
            {
                if (event.key === 'Escape' || event.key === 'Esc') closeAllDropdowns();
            });
        }

        function destroyDropdown($container)
        {
            const $dropdownItems = $container.find('.lsd-dashboard-menu-more');
            if (!$dropdownItems.length) return;

            $dropdownItems.each(function ()
            {
                const $item = $(this);
                const $trigger = $item.find('.lsd-dashboard-menu-more-trigger').first();
                const $links = $item.find('.lsd-dashboard-menu-more-list a');

                $item.removeClass('lsd-open');
                if ($trigger.length)
                {
                    $trigger.attr('aria-expanded', 'false');
                    $trigger.off('click' + dropdownNamespace);
                }

                $links.off('click' + dropdownNamespace);
            });
        }

        function initMenuCarousel($container)
        {
            if (typeof $.fn === 'undefined' || typeof $.fn.owlCarousel === 'undefined') return;

            const $carousels = $container.find('.lsd-dashboard-menus-carousel');
            if (!$carousels.length) return;

            $carousels.each(function ()
            {
                const $carousel = $(this);
                let itemCount = $carousel.data('lsdMenuCount');

                if (!itemCount)
                {
                    itemCount = $carousel.find('> li').length;
                    $carousel.data('lsdMenuCount', itemCount);
                }

                if (!itemCount) return;

                let gap = 20;

                try
                {
                    const computed = window.getComputedStyle(this);
                    if (computed)
                    {
                        const parsed = parseFloat(computed.getPropertyValue('--listdom-gap'));
                        if (!isNaN(parsed)) gap = parsed;
                    }
                }
                catch (error)
                {
                    // Keep default gap value when computed styles are unavailable.
                }

                const responsive = {
                    0: { items: Math.min(itemCount, 1) },
                    480: { items: Math.min(itemCount, 3) },
                    768: { items: Math.min(itemCount, 4) },
                    1024: { items: Math.min(itemCount, 5) },
                    1280: { items: Math.min(itemCount, 7) },
                };

                if ($carousel.hasClass('owl-loaded'))
                {
                    $carousel.trigger('refresh.owl.carousel');
                    return;
                }

                $carousel.owlCarousel({
                    items: Math.min(itemCount, 7),
                    loop: false,
                    autoplay: false,
                    autoplayHoverPause: true,
                    dots: false,
                    nav: true,
                    margin: gap,
                    responsiveClass: true,
                    responsive: responsive,
                });
            });
        }

        $dashboards.each(function ()
        {
            const $dashboard = $(this);
            const $container = $dashboard.find('.lsd-dashboard-menu-container').first();
            if (!$container.length) return;

            const sidebarStatus = $container.data('sidebar-status') || $dashboard.data('sidebar-status') || 'default';
            const horizontalMode = $container.data('horizontal-mode') || $dashboard.data('horizontal-mode') || 'default';
            const $toggle = $container.find('.lsd-dashboard-menu-toggle-trigger');

            function queueTransitionReady()
            {
                if ($dashboard.hasClass('lsd-dashboard-sidebar-transition-ready')) return;

                const enable = function ()
                {
                    $dashboard.addClass('lsd-dashboard-sidebar-transition-ready');
                };

                if (typeof window !== 'undefined' && typeof window.requestAnimationFrame === 'function')
                    window.requestAnimationFrame(() => {
                        setTimeout(enable, 50);
                    });
                else setTimeout(enable, 0);
            }

            function applyState(mode)
            {
                const isHorizontal = mode === 'horizontal';
                const isCompact = mode === 'compact';

                $dashboard.toggleClass('lsd-dashboard-sidebar-horizontal', isHorizontal);
                $dashboard.toggleClass('lsd-dashboard-sidebar-horizontal-carousel', isHorizontal && horizontalMode === 'carousel');
                $dashboard.toggleClass('lsd-dashboard-sidebar-vertical', !isHorizontal);
                $dashboard.toggleClass('lsd-dashboard-sidebar-compact', !isHorizontal && isCompact);
                $dashboard.toggleClass('lsd-dashboard-sidebar-default', !isHorizontal && !isCompact);

                $dashboard.data('sidebar-active-mode', mode);
            }

            function updateToggle(mode)
            {
                if (!$toggle.length) return;

                const isExpanded = mode !== 'compact';
                $toggle.attr('aria-expanded', isExpanded ? 'true' : 'false');

                const $icon = $toggle.find('.lsd-fe-icon').first();
                if ($icon.length)
                {
                    $icon.toggleClass('fa-long-arrow-left', isExpanded);
                    $icon.toggleClass('fa-long-arrow-right', !isExpanded);
                }
            }

            if (sidebarStatus === 'horizontal')
            {
                applyState('horizontal');
                updateToggle('horizontal');
                destroyDropdown($container);
                if (horizontalMode === 'dropdown') bindDropdown($container);
                else if (horizontalMode === 'carousel') initMenuCarousel($container);
                queueTransitionReady();
                return;
            }

            destroyDropdown($container);

            const toggleInitialMode = $toggle.length ? ($toggle.data('initial-mode') === 'compact' ? 'compact' : 'default') : 'default';
            let activeMode = sidebarStatus === 'compact' ? toggleInitialMode : 'default';
            applyState(activeMode);
            updateToggle(activeMode);
            queueTransitionReady();

            if ($toggle.length)
            {
                $toggle.off('click.listdomDashboard').on('click.listdomDashboard', function (event)
                {
                    event.preventDefault();
                    activeMode = activeMode === 'compact' ? 'default' : 'compact';
                    applyState(activeMode);
                    updateToggle(activeMode);
                    queueTransitionReady();
                });
            }
        });
    };

    if (typeof jQuery !== 'undefined')
    {
        jQuery(function ($)
        {
            window.lsdDashboardApplySidebar($('.lsd-dashboard'));
        });
    }
})();

// Listdom DASHBOARD PLUGIN
(function ($) {
    $.fn.listdomDashboard = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                ajax_url: 0,
            },
            options
        );

        let $dashboard = $("#lsd_dashboard");
        window.lsdDashboardApplySidebar($dashboard);
        setListeners();

        function setListeners() {
            $dashboard
            .find($(".lsd-dashboard-delete"))
            .off("click")
            .on("click", function () {
                remove($(this));
            });
        }

        function remove($btn) {
            let confirm = $btn.data("confirm");
            if (confirm === 0) {
                $btn.data("confirm", 1);
                $btn.addClass("lsd-need-confirm");

                setTimeout(function () {
                    $btn.data("confirm", 0);
                    $btn.removeClass("lsd-need-confirm");
                }, 10000);

                return;
            }

            // Loading Style
            $dashboard.fadeTo(200, 0.7);

            let id = $btn.data("id");

            $.ajax({
                url: settings.ajax_url,
                data:
                    "action=lsd_dashboard_listing_delete&id=" +
                    id +
                    "&_lsdnonce=" +
                    settings.nonce,
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.success === 1) {
                        let $listing = $("#lsd_dashboard_listing_" + id);
                        $listing.remove();
                    }

                    // Loading Style
                    $dashboard.fadeTo(200, 1);
                },
                error: function () {
                    // Loading Style
                    $dashboard.fadeTo(200, 1);
                },
            });
        }
    };
})(jQuery);

// Listdom DASHBOARD FORM PLUGIN
(function ($) {
    $.fn.listdomDashboardForm = function (options) {
        // Default Options
        const settings = $.extend(
            {
                // These are the defaults.
                ajax_url: 0,
            },
            options
        );

        let $dashboard = $("#lsd_dashboard");
        let $form = $("#lsd_dashboard_form");
        window.lsdDashboardApplySidebar($dashboard);
        let $featured_image_input = $("#lsd_featured_image");
        let $featured_image_upload = $("#lsd_featured_image_file");
        let $gallery_upload = $("#lsd_listing_gallery_uploader");
        let $featured_image_preview = $("#lsd_dashboard_featured_image_preview");
        let $featured_image_container = $("#lsd_dashboard_featured_image_placeholder");
        let $featured_image_remove = $("#lsd_featured_image_remove_button");
        let $multiline_select = $(".lsd-select-multiple");
        let ajax = false;
        const aspectRatioTolerance = 0.05;

        function parseAspectRatio(value) {
            if (!value || typeof value !== "string") return null;

            const trimmed = value.trim();
            if (!trimmed || trimmed === "none") return null;

            const parts = trimmed.split(":");
            if (parts.length !== 2) return null;

            const width = parseFloat(parts[0]);
            const height = parseFloat(parts[1]);

            if (!width || !height || Number.isNaN(width) || Number.isNaN(height)) return null;

            return width / height;
        }

        function isAspectRatioAllowed(width, height, ratio, tolerance) {
            if (!ratio || !width || !height) return true;
            const actual = width / height;
            return Math.abs(actual - ratio) / ratio <= tolerance;
        }

        function getImageDimensions(file) {
            return new Promise(function (resolve, reject) {
                if (!file || !file.type || file.type.indexOf("image") !== 0) {
                    reject(new Error("invalid_file"));
                    return;
                }

                const image = new Image();
                const urlCreator = window.URL || window.webkitURL;
                const objectUrl = urlCreator ? urlCreator.createObjectURL(file) : null;

                image.onload = function () {
                    const width = image.naturalWidth || image.width;
                    const height = image.naturalHeight || image.height;
                    if (objectUrl && urlCreator) urlCreator.revokeObjectURL(objectUrl);

                    if (!width || !height) {
                        reject(new Error("invalid_dimensions"));
                        return;
                    }

                    resolve({ width: width, height: height });
                };

                image.onerror = function () {
                    if (objectUrl && urlCreator) urlCreator.revokeObjectURL(objectUrl);
                    reject(new Error("load_error"));
                };

                if (objectUrl) image.src = objectUrl;
                else reject(new Error("no_url"));
            });
        }

        function fallbackGalleryPlaceholder(context) {
            let $containers;

            if (typeof context !== "undefined" && context !== null) {
                $containers = $(context);

                if ($containers.is(".lsd-listing-gallery-container")) {
                    // Already the container
                } else {
                    $containers = $containers.closest(".lsd-listing-gallery-container");
                }
            } else {
                $containers = $(".lsd-listing-gallery-container");
            }

            if (!$containers.length) return;

            $containers.each(function () {
                const $container = $(this);
                const $list = $container.find(".lsd-listing-gallery");
                const $placeholder = $container.find(".lsd-gallery-placeholder");
                const $removeButton = $container.find(".lsd-remove-gallery-button");
                const $addMoreButton = $container.find(".lsd-gallery-add-more-button");

                if (!$list.length || !$placeholder.length) return;

                const hasItems = $list.find("li").length > 0;

                if (hasItems) {
                    $list.removeClass("lsd-util-hide");
                    $placeholder.addClass("lsd-util-hide");
                    $removeButton.removeClass("lsd-util-hide");
                    $addMoreButton.removeClass("lsd-util-hide");
                } else {
                    $list.addClass("lsd-util-hide");
                    $placeholder.removeClass("lsd-util-hide");
                    $removeButton.addClass("lsd-util-hide");
                    $addMoreButton.addClass("lsd-util-hide");
                }
            });
        }

        if (typeof window.lsdUpdateGalleryPlaceholder !== "function") {
            window.lsdUpdateGalleryPlaceholder = fallbackGalleryPlaceholder;
        }

        function updateGalleryPlaceholder(context) {
            if (typeof window.lsdUpdateGalleryPlaceholder === "function") {
                window.lsdUpdateGalleryPlaceholder(context);
            }
        }

        function bindGalleryRemoveHandler($lists) {
            if (!$lists || !$lists.length) return;

            $lists
                .off("click.lsdGalleryRemove", ".lsd-remove-gallery-single-button")
                .on("click.lsdGalleryRemove", ".lsd-remove-gallery-single-button", function (event) {
                    event.preventDefault();

                    const $item = $(this).closest("li");
                    if (!$item.length) return;

                    const $list = $item.closest(".lsd-listing-gallery");

                    $item.remove();
                    updateGalleryPlaceholder($list);
                });
        }

        updateGalleryPlaceholder();
        bindGalleryRemoveHandler($(".lsd-listing-gallery"));

        function syncDashboardEditors() {
            if (
                typeof window.tinymce !== "undefined" &&
                window.tinymce &&
                typeof window.tinymce.triggerSave === "function"
            ) {
                window.tinymce.triggerSave();
                return;
            }

            if (
                typeof window.tinyMCE !== "undefined" &&
                window.tinyMCE &&
                typeof window.tinyMCE.triggerSave === "function"
            ) {
                window.tinyMCE.triggerSave();
            }
        }

        function clearFieldMessages() {
            const $featuredAlert = $("#lsd_listing_featured_image_message");
            if ($featuredAlert.length) $featuredAlert.html("");

            if ($form.length) {
                $form.find('.lsd-attribute-image').each(function () {
                    const $wrapper = $(this);
                    $wrapper.removeClass('lsd-attribute-error');
                    $wrapper.find('.lsd-imagepicker-image-placeholder').removeClass('lsd-attribute-error');
                    $wrapper.find('.lsd-attribute-image-message').html('');
                });

                $form.find('.lsd-attribute-file').each(function () {
                    const $wrapper = $(this);
                    $wrapper.removeClass('lsd-attribute-error');
                    $wrapper.find('.lsd-imagepicker-image-placeholder').removeClass('lsd-attribute-error');
                    $wrapper.find('.lsd-attribute-file-message').html('');
                });
            }
        }

        function applyFieldMessages(data) {
            clearFieldMessages();
            if (!data) return;

            const $featuredAlert = $("#lsd_listing_featured_image_message");
            if (data.featured_image && $featuredAlert.length) {
                if (typeof listdom_alertify === 'function') $featuredAlert.html(listdom_alertify(data.featured_image, "lsd-error"));
                else $featuredAlert.text(data.featured_image);

                setTimeout(function () {
                    $featuredAlert.html("");
                }, 9000);
            }

            if (data.attribute_images && typeof data.attribute_images === 'object') {
                Object.keys(data.attribute_images).forEach(function (slug) {
                    const message = data.attribute_images[slug];
                    const $input = $form.find('[name="lsd[attributes][' + slug + ']"]');
                    if (!$input.length) return;

                    const $wrapper = $input.closest('.lsd-attribute-image');
                    const $placeholder = $('#' + $input.attr('id') + '_img');
                    const $alert = $wrapper.find('.lsd-attribute-image-message');

                    if ($alert.length) {
                        if (typeof listdom_alertify === 'function') $alert.html(listdom_alertify(message, 'lsd-error'));
                        else $alert.text(message);
                    }

                    $wrapper.addClass('lsd-attribute-error');
                    if ($placeholder.length) $placeholder.addClass('lsd-attribute-error');

                    setTimeout(function () {
                        if ($alert.length) $alert.html('');
                    }, 9000);
                });
            }

            if (data.attribute_files && typeof data.attribute_files === 'object') {
                Object.keys(data.attribute_files).forEach(function (slug) {
                    const message = data.attribute_files[slug];
                    const $input = $form.find('[name="lsd[attributes][' + slug + ']"]');
                    if (!$input.length) return;

                    const $wrapper = $input.closest('.lsd-attribute-file');
                    const $placeholder = $('#' + $input.attr('id') + '_img');
                    const $alert = $wrapper.find('.lsd-attribute-file-message');

                    if ($alert.length) {
                        if (typeof listdom_alertify === 'function') $alert.html(listdom_alertify(message, 'lsd-error'));
                        else $alert.text(message);
                    }

                    $wrapper.addClass('lsd-attribute-error');
                    if ($placeholder.length) $placeholder.addClass('lsd-attribute-error');

                    setTimeout(function () {
                        if ($alert.length) $alert.html('');
                    }, 9000);
                });
            }
        }

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                save();
            });

            $featured_image_upload.off("change").on("change", function () {
                featured_image_upload();
            });

            $featured_image_remove.off("click").on("click", function () {
                featured_image_remove();
            });

            $gallery_upload.off("change").on("change", function () {
                gallery_upload();
            });

            $multiline_select.select2();
        }

        function save() {
            // Message
            const $message = $("#lsd_dashboard_form_message");

            // Hide the Message
            $message.html("");

            // Validate required attributes
            let isValid = true;

            // Validate required checkbox groups
            $('.lsd-attribute-checkbox[data-required="1"]:visible', $form).each(function () {
                const $group = $(this);
                const $checkboxes = $group.find('input[type="checkbox"]');
                const isChecked = $checkboxes.is(':checked');
                const requiredMessage = $group.data('required-message');

                if (!isChecked) {
                    isValid = false;
                    $group.addClass('lsd-attribute-error');
                    if ($group.find('.lsd-attribute-error-msg').length === 0) {
                        $group.append('<div class="lsd-attribute-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                    }
                } else {
                    $group.removeClass('lsd-attribute-error');
                    $group.find('.lsd-attribute-error-msg').remove();
                }
            });

            // Validate required radio groups
            $('.lsd-attribute-radio[data-required="1"]:visible', $form).each(function () {
                const $group = $(this);
                const $radios = $group.find('input[type="radio"]');
                const isSelected = $radios.is(':checked');
                const requiredMessage = $group.data('required-message');

                if (!isSelected) {
                    isValid = false;
                    $group.addClass('lsd-attribute-error');
                    if ($group.find('.lsd-attribute-error-msg').length === 0) {
                        $group.append('<div class="lsd-attribute-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                    }
                } else {
                    $group.removeClass('lsd-attribute-error');
                    $group.find('.lsd-attribute-error-msg').remove();
                }
            });

            // Validate required image fields
            $('.lsd-attribute-image[data-required="1"]:visible', $form).each(function () {
                const $this = $(this);
                const $input = $(this).find('input[type=hidden]');
                const value = $input.val();
                const requiredMessage = $this.data('required-message');
                const $placeholder = $('#' + $input.attr('id') + '_img');

                if (!value) {
                    isValid = false;
                    $placeholder.addClass('lsd-attribute-error');
                    if ($placeholder.next('.lsd-attribute-error-msg').length === 0) {
                        $placeholder.after('<div class="lsd-attribute-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                    }
                } else {
                    $placeholder.removeClass('lsd-attribute-error');
                    $placeholder.next('.lsd-attribute-error-msg').remove();
                }
            });

            // Validate required file fields
            $('.lsd-attribute-file[data-required="1"]:visible', $form).each(function () {
                const $this = $(this);
                const $input = $(this).find('input[type=hidden]');
                const value = $input.val();
                const requiredMessage = $this.data('required-message') || 'Please select a file.';
                const $placeholder = $('#' + $input.attr('id') + '_img');

                if (!value) {
                    isValid = false;
                    $placeholder.addClass('lsd-attribute-error');
                    if ($placeholder.next('.lsd-attribute-error-msg').length === 0) {
                        $placeholder.after('<div class="lsd-attribute-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                    }
                } else {
                    $placeholder.removeClass('lsd-attribute-error');
                    $placeholder.next('.lsd-attribute-error-msg').remove();
                }
            });

            if (!isValid) {
                const $firstError = $('.lsd-attribute-error:visible:first');
                if ($firstError.length) {
                    $('html, body').animate(
                        {
                            scrollTop: $firstError.offset().top - 100,
                        },
                        300
                    );
                }
                return;
            }

            clearFieldMessages();

            // Add loading Class to the form
            $form.addClass("lsd-loading");

            // Loading Style
            $dashboard.fadeTo(200, 0.7);

            // Sync TinyMCE editor content without affecting scroll position
            syncDashboardEditors();

            // Abort previous request
            if (ajax) ajax.abort();

            const data = $form.serialize();
            ajax = $.ajax({
                type: "POST",
                url: settings.ajax_url,
                data: data,
                dataType: "JSON",
                success: function (response) {
                    // Remove the Loading Class from the Form
                    $form.removeClass("lsd-loading");

                    if (response.success === 1) {
                        // Show the message
                        $message.html(listdom_alertify(response.message, "lsd-success"));

                        // Set the event id
                        $("#lsd_dashboard_id").val(response.data.id);

                        // Labelize Addon
                        const $labelize = $("#lsd_labelize_button");
                        if ($labelize.length) {
                            // Set Listing ID
                            $labelize.data("id", response.data.id);

                            // Hide Message
                            $(".lsd-labelize-metabox .lsd-labelize-message").addClass(
                                "lsd-util-hide"
                            );

                            // Show Button
                            $labelize.removeClass("lsd-util-hide");
                        }
                    } else {
                        // Show the message
                        $message.html(listdom_alertify(response.message, "lsd-error"));
                    }

                    // Reset Recaptcha
                    typeof grecaptcha !== 'undefined' && grecaptcha.reset();

                    // Loading Style
                    $dashboard.fadeTo(200, 1);
                },
                error: function () {
                    // Remove the Loading Class from the Form
                    $form.removeClass("lsd-loading");

                    // Loading Style
                    $dashboard.fadeTo(200, 1);
                },
            });
        }

        function featured_image_upload() {
            // Alert
            let $alert = $("#lsd_listing_featured_image_message");

            // Wrapper
            let $wrapper = $(".lsd-dashboard-featured-image");

            const file = $featured_image_upload.prop("files")[0];
            if (!file) return;

            const ratioLabel = ($featured_image_upload.data("aspectRatio") || "").toString().trim();
            const ratioValue = parseAspectRatio(ratioLabel);

            const aspectMessage =
                ($featured_image_upload.data("aspectMessage") || "").toString().trim() ||
                (ratioLabel ? "Please upload an image with an aspect ratio close to " + ratioLabel + "." : "");

            if (ratioValue) {
                getImageDimensions(file)
                    .then(function (dimensions) {
                        if (
                            !isAspectRatioAllowed(
                                dimensions.width,
                                dimensions.height,
                                ratioValue,
                                aspectRatioTolerance
                            )
                        ) {
                            const message = aspectMessage || "The image does not match the required aspect ratio.";
                            $featured_image_upload.val("");
                            $alert.html(listdom_alertify(message, "lsd-error"));
                            setTimeout(function () {
                                $alert.html("");
                            }, 9000);
                            return;
                        }

                        featured_image_upload_request(file, $wrapper, $alert);
                    })
                    .catch(function () {
                        const message = aspectMessage || "Unable to validate the image aspect ratio.";
                        $featured_image_upload.val("");
                        $alert.html(listdom_alertify(message, "lsd-error"));
                        setTimeout(function () {
                            $alert.html("");
                        }, 9000);
                    });

                return;
            }

            featured_image_upload_request(file, $wrapper, $alert);
        }

        function featured_image_upload_request(file, $wrapper, $alert) {
            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_listing_upload_featured_image");
            fd.append("_wpnonce", settings.nonce);
            fd.append("file", file);

            // Empty Alert
            $alert.html("");

            $.ajax({
                url: settings.ajax_url,
                type: "POST",
                data: fd,
                dataType: "json",
                processData: false,
                contentType: false,
            }).done(function (response) {
                // Loading Style
                $wrapper.removeClass("lsd-loading");

                if (response.success) {
                    const $emptyState = $featured_image_container.find('.lsd-image-placeholder-empty');

                    $featured_image_input.val(response.data.attachment_id);
                    $featured_image_upload.val("");
                    $featured_image_preview.html('<img src="' + response.data.url + '" alt="">').removeClass('lsd-util-hide');
                    $featured_image_container.addClass('lsd-image-placeholder-has-image');
                    $emptyState.addClass('lsd-util-hide');
                    $featured_image_remove.removeClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    const $emptyState = $featured_image_container.find('.lsd-image-placeholder-empty');

                    $featured_image_input.val('');
                    $featured_image_upload.val("");
                    $featured_image_preview.html('').addClass('lsd-util-hide');
                    $featured_image_container.removeClass('lsd-image-placeholder-has-image');
                    $emptyState.removeClass('lsd-util-hide');
                    $featured_image_remove.addClass('lsd-util-hide');

                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 9000);
            });
        }

        function featured_image_remove() {
            const $emptyState = $featured_image_container.find('.lsd-image-placeholder-empty');

            $featured_image_input.val("");
            $featured_image_preview.html("").addClass('lsd-util-hide');
            $featured_image_container.removeClass('lsd-image-placeholder-has-image');
            $emptyState.removeClass('lsd-util-hide');
            $featured_image_remove.addClass("lsd-util-hide");
        }

        function gallery_upload() {
            // Alert
            let $alert = $("#lsd_listing_gallery_uploader_message");

            let $target = $($gallery_upload.data("for"));
            let $wrapper = $target.closest(".lsd-listing-gallery-container");
            if (!$wrapper.length) $wrapper = $(".lsd-listing-gallery-container");
            let name = $gallery_upload.data("name");
            let files = $gallery_upload.prop("files");
            let ins = files.length;

            bindGalleryRemoveHandler($target);

            // Empty Alert
            $alert.html("");

            let maxImages = parseInt($gallery_upload.data("max-images"), 10);
            if (isNaN(maxImages)) maxImages = 0;

            let currentCount = $target.find("li").length;

            if (maxImages > 0) {
                let remaining = maxImages - currentCount;

                if (remaining <= 0) {
                    let message = $gallery_upload.data("max-images-message");
                    if (!message) message = "You can upload up to " + maxImages + " gallery images.";
                    else message = message.replace("%s", maxImages);

                    $gallery_upload.val("");
                    $alert.html(listdom_alertify(message, "lsd-error"));

                    setTimeout(function () {
                        $alert.html("");
                    }, 60000);

                    return;
                }

                if (ins > remaining) {
                    let message = $gallery_upload.data("max-images-remaining-message");
                    if (!message) message = "You can only upload " + remaining + " more image(s).";
                    else message = message.replace("%s", remaining);

                    $gallery_upload.val("");
                    $alert.html(listdom_alertify(message, "lsd-error"));

                    setTimeout(function () {
                        $alert.html("");
                    }, 60000);

                    return;
                }
            }

            const ratioLabel = ($gallery_upload.data("aspectRatio") || $wrapper.data("aspectRatio") || "")
                .toString()
                .trim();
            const ratioValue = parseAspectRatio(ratioLabel);
            const aspectMessage =
                ($gallery_upload.data("aspectMessage") || $wrapper.data("aspectMessage") || "").toString().trim() ||
                (ratioLabel ? "Please upload images with an aspect ratio close to " + ratioLabel + "." : "");

            if (ratioValue && ins > 0) {
                validateGalleryAspectRatio(files, ratioValue, aspectMessage, $alert, $wrapper, $target, name);
                return;
            }

            gallery_upload_request($wrapper, $target, name, files, currentCount, $alert);
        }

        function validateGalleryAspectRatio(files, ratioValue, aspectMessage, $alert, $wrapper, $target, name) {
            const fileList = Array.from(files);
            let index = 0;

            const checkNext = function () {
                if (index >= fileList.length) {
                    gallery_upload_request($wrapper, $target, name, files, $target.find("li").length, $alert);
                    return;
                }

                const file = fileList[index];
                index += 1;

                getImageDimensions(file)
                    .then(function (dimensions) {
                        if (!isAspectRatioAllowed(dimensions.width, dimensions.height, ratioValue, aspectRatioTolerance)) {
                            const message = aspectMessage || "One or more images do not match the required aspect ratio.";
                            $gallery_upload.val("");
                            $alert.html(listdom_alertify(message, "lsd-error"));
                            setTimeout(function () {
                                $alert.html("");
                            }, 60000);
                            return;
                        }

                        checkNext();
                    })
                    .catch(function () {
                        const message = aspectMessage || "Unable to validate the image aspect ratio.";
                        $gallery_upload.val("");
                        $alert.html(listdom_alertify(message, "lsd-error"));
                        setTimeout(function () {
                            $alert.html("");
                        }, 60000);
                    });
            };

            checkNext();
        }

        function gallery_upload_request($wrapper, $target, name, files, currentCount, $alert) {
            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_listing_upload_gallery");
            fd.append("_wpnonce", settings.nonce);

            // Append Images
            fd.append("existing_count", currentCount);

            $target.find('input[name="' + name + '"]').each(function () {
                const id = $(this).val();
                if (id) fd.append("existing_ids[]", id);
            });

            for (let x = 0; x < files.length; x++) fd.append("files[]", files[x]);

            $.ajax({
                url: settings.ajax_url,
                type: "POST",
                data: fd,
                dataType: "json",
                processData: false,
                contentType: false,
            }).done(function (response) {
                // Loading Style
                $wrapper.removeClass("lsd-loading");

                const hasData = Array.isArray(response.data) && response.data.length > 0;

                if (hasData) {
                    response.data.map(function (attachment) {
                        $target.append(
                            '<li data-id="' + attachment.id + '"><input type="hidden" name="' + name + '" value="' + attachment.id + '"><img src="' + attachment.url + '" alt=""><div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div></li>'
                        );
                    });

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                }

                if (!response.success || !hasData) {
                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                updateGalleryPlaceholder($target);

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 60000);
            });
        }
    };
})(jQuery);

// Listdom Dashboard Additional Categories
(function ($) {
    $.fn.AdditionalCategories = function(options)
    {
        const defaults = {post_id: '', children_only: '0', ajax_url: '', nonce: ''};

        return this.each(function()
        {
            const $wrapper = $(this);
            if (!$wrapper.length) return;

            const settings = $.extend({}, defaults, options || {});
            const childrenOnly = settings.children_only === '1';
            const ajaxUrl = settings.ajax_url || '';
            const nonce = settings.nonce || '';
            const postId = parseInt(settings.post_id, 10) || 0;
            const namespace = '.lsdAddCat';

            const $form = $wrapper.closest('form');
            const $target = $wrapper.find('.lsd-additional-categories');
            const requiresPrimary = $wrapper.data('requires-primary') || '';

            let request = null;
            let refreshTimer = null;

            const initSelect2 = function($elements)
            {
                if (!$elements || !$elements.length || typeof $.fn.select2 !== 'function') return;

                $elements.each(function()
                {
                    const $element = $(this);
                    const options = {width: '100%'};
                    const placeholder = $element.data('placeholder');

                    if (placeholder) options.placeholder = placeholder;

                    const $dropdownParent = $element.closest('.lsd-additional-categories-wrapper');
                    if ($dropdownParent.length) options.dropdownParent = $dropdownParent;

                    $element.select2(options);
                });
            };

            const currentPrimaryId = function()
            {
                const $select = $form.find('#lsd_listing_category');
                if ($select.length)
                {
                    const val = parseInt($select.val(), 10);
                    return isNaN(val) ? 0 : val;
                }

                const $checked = $form.find('input[name="lsd[listing_category][]"]:checked');
                if ($checked.length)
                {
                    const val = parseInt($checked.first().val(), 10);
                    return isNaN(val) ? 0 : val;
                }

                return 0;
            };

            const collectSelected = function()
            {
                const selected = [];
                const $dropdown = $target.find('select.lsd-fd-taxonomies-dropdown');
                if ($dropdown.length)
                {
                    const values = $dropdown.val();
                    const normalized = Array.isArray(values) ? values : (typeof values === 'undefined' || values === null ? [] : [values]);

                    normalized.forEach(function(value)
                    {
                        const id = parseInt(value, 10);
                        if (id) selected.push(id);
                    });

                    return selected;
                }

                $target.find('input[type="checkbox"]:checked').each(function()
                {
                    const id = parseInt($(this).val(), 10);
                    if (id) selected.push(id);
                });

                return selected;
            };

            const showPrimaryMessage = function()
            {
                if (requiresPrimary)
                {
                    const markup = $('<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"></p>').text(requiresPrimary).prop('outerHTML');
                    $target.html(markup);
                }
                else
                {
                    $target.empty();
                }
            };

            const refresh = function()
            {
                if (!childrenOnly) return;

                const primaryId = currentPrimaryId();

                if (!primaryId)
                {
                    if (request) request.abort();
                    $wrapper.removeClass('lsd-loading');
                    showPrimaryMessage();
                    return;
                }

                const selected = collectSelected();

                if (request) request.abort();
                if (!ajaxUrl || !nonce) return;

                $wrapper.addClass('lsd-loading');

                request = $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'lsd_refresh_additional_categories',
                        nonce: nonce,
                        post_id: postId,
                        primary_id: primaryId,
                        selected: selected
                    },
                    success: function(response)
                    {
                        const html = response && response.data && typeof response.data.html !== 'undefined'
                            ? response.data.html
                            : (response && typeof response.html !== 'undefined' ? response.html : null);

                        if (html === null || typeof html === 'undefined') return;

                        $target.html(html);
                        initSelect2($target.find('.lsd-select-multiple'));
                    },
                    complete: function()
                    {
                        request = null;
                        $wrapper.removeClass('lsd-loading');
                    }
                });
            };

            const scheduleRefresh = function()
            {
                if (refreshTimer) clearTimeout(refreshTimer);

                refreshTimer = setTimeout(function()
                {
                    refreshTimer = null;
                    refresh();
                }, 0);
            };

            const bindPrimaryHandlers = function()
            {
                const $primarySelect = $form.find('#lsd_listing_category');
                if ($primarySelect.length)
                {
                    $primarySelect
                    .off('change' + namespace + ' select2:select' + namespace + ' select2:unselect' + namespace + ' select2:clear' + namespace)
                    .on('change' + namespace, scheduleRefresh)
                    .on('select2:select' + namespace + ' select2:unselect' + namespace + ' select2:clear' + namespace, scheduleRefresh);
                }

                const $primaryCheckboxes = $form.find('input[name="lsd[listing_category][]"]');
                if ($primaryCheckboxes.length)
                {
                    $primaryCheckboxes.off('change' + namespace).on('change' + namespace, scheduleRefresh);
                }
            };

            if (childrenOnly)
            {
                bindPrimaryHandlers();
                if (currentPrimaryId()) refresh();
                else showPrimaryMessage();
            }

            const instance = {refresh: refresh};
            $wrapper.data('lsdAdditionalCategories', instance);
        });
    };
})(jQuery);

// Listdom DASHBOARD NEW TAX FORM PLUGIN
(function ($) {
    $.fn.listdomDashboardTaxForm = function (options) {
        return this.each(function () {
            const $form = $(this);
            const $wrapper = $form.closest('.lsd-new-tax-wrapper');
            const tax = $wrapper.data("tax");

            const settings = $.extend(
                {
                    ajax_url: 0,
                    nonce: '',
                },
                options
            );

            let ajax = false;

            setListeners();

            function setListeners()
            {
                $form.find(".lsd_add_term_btn").off("click").on("click", function (e) {
                    e.preventDefault();
                    save('detailed');
                });

                $form.find(".lsd_add_express_term_btn").off("click").on("click", function (e) {
                    e.preventDefault();
                    save('express');
                });

                $form.find('.lsd_express_term_name').off('keydown').on('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        save('express');
                    }
                });
            }

            function save(mode)
            {
                const $button = $form.find('button[type=submit]');
                const $message = $form.find("#lsd_new_term_message_" + tax);
                $message.html("");

                let term_name = '';
                if (mode === 'express') term_name = $form.find('.lsd_express_term_name').val();
                else term_name = $form.find('.lsd_detailed_term_name').val();

                if (!term_name) {
                    $message.html(listdom_alertify("Term name is required.", "lsd-error"));
                    return;
                }

                if (ajax) ajax.abort();

                const originalHTML = $button.html();
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                let data = {
                    action: 'lsd_dashboard_new_term',
                    mode: mode,
                    _wpnonce: settings.nonce,
                    taxonomy: tax,
                    term_name: term_name
                };

                if (mode === 'detailed')
                {
                    data.term_description = $form.find('.lsd_detailed_term_description').val();
                    data.term_parent = $form.find('.lsd_detailed_term_parent').val();
                    data.term_icon = $form.find('.lsd_icon').val();
                    data.term_color = $form.find('.lsd_color').val();
                }

                ajax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: data,
                    dataType: "JSON",
                    success: function (response)
                    {
                        if (response.success === 1)
                        {
                            $message.html(listdom_alertify(response.message, "lsd-success"));

                            if (response.data && response.data.term_id) insert_term(response.data.term_id, response.data.term_name);

                            $form.find("input[type=text], textarea").val("");
                            $form.find("select").prop("selectedIndex", 0);

                            $button.prop('disabled', false).html(originalHTML);
                        }
                        else
                        {
                            $message.html(listdom_alertify(response.message, "lsd-error"));
                            $button.prop('disabled', false).html(originalHTML);
                        }
                    },
                    error: function (xhr, status, error)
                    {
                        $button.prop('disabled', false).html(originalHTML);
                        $message.html(listdom_alertify("AJAX request failed: " + error, "lsd-error"));
                    },
                });
            }

            function insert_term(id, name)
            {
                const containers = {
                    'listdom-category': '.lsd-dashboard-category',
                    'listdom-location': '.lsd-dashboard-locations',
                    'listdom-tag': '.lsd-dashboard-tags',
                    'listdom-feature': '.lsd-dashboard-features',
                    'listdom-label': '.lsd-dashboard-labels'
                };

                const $container = jQuery(containers[tax]);
                if (!$container.length) return;

                const $select = $container.find('select.lsd-fd-taxonomies-dropdown');
                if ($select.length)
                {
                    $select.each(function(){
                        jQuery(this).append(new Option(name, id, true, true)).trigger('change');
                    });
                    return;
                }

                const $ul = $container.find('ul.lsd-fd-taxonomies-checkboxes');
                if ($ul.length)
                {
                    const nameAttr = $ul.find('input[type=checkbox]').first().attr('name');
                    const inputId = 'in-listdom-location-' + id;
                    const $checkbox = jQuery('<input>', {
                        type: 'checkbox',
                        name: nameAttr,
                        value: id,
                        id: inputId,
                        checked: true,
                    });
                    const $label = jQuery('<label>', {
                        class: 'selectit'
                    }).append($checkbox).append(' ' + name);
                    $ul.append(jQuery('<li>').append($label));
                }
            }
        });
    };
})(jQuery);

// Listdom DASHBOARD Profile PLUGIN
(function ($) {
    $.fn.listdomDashboardProfile = function (options) {
        // Default Options
        const settings = $.extend(
            {
                // These are the defaults.
                ajax_url: 0,
            },
            options
        );

        let $dashboard = $("#lsd_dashboard");
        let $form = $("#lsd_dashboard_profile");
        window.lsdDashboardApplySidebar($dashboard);
        let $profile_image_input = $("#lsd_profile_image");
        let $profile_image_upload = $("#lsd_profile_image_file");
        let $profile_image_preview = $("#lsd_dashboard_profile_image_preview");
        let $profile_image_remove = $("#lsd_profile_image_remove_button");
        let $profile_image_button = $(".lsd-choose-profile-image");

        let $hero_image_input = $("#lsd_hero_image");
        let $hero_image_upload = $("#lsd_hero_image_file");
        let $hero_image_preview = $("#lsd_dashboard_hero_image_preview");
        let $hero_image_remove = $("#lsd_hero_image_remove_button");
        let $hero_image_button = $(".lsd-choose-hero-image");

        let ajax = false;

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                save();
            });

            $profile_image_upload.off("change").on("change", function () {
                profile_image_upload();
            });

            $profile_image_remove.off("click").on("click", function () {
                profile_image_remove();
            });

            $hero_image_upload.off("change").on("change", function () {
                hero_image_upload();
            });

            $hero_image_remove.off("click").on("click", function () {
                hero_image_remove();
            });
        }

        function save() {
            // Message
            const $message = $("#lsd_dashboard_profile_message");
            const $pass_message = $(".lsd-password-message");

            // Hide the Message
            $message.html("");
            $pass_message.html("");

            // Add loading Class to the form
            $form.addClass("lsd-loading");

            // Check Password Matching
            const password = $("#lsd_password").val();
            const confirmPassword = $("#lsd_confirm_password").val();

            if (password && password.length < 6) {
                $pass_message.html(listdom_alertify("Password must be at least 6 characters long!", "lsd-error"));
                $form.removeClass("lsd-loading");
                return;
            }

            if (password && password !== confirmPassword) {
                $pass_message.html(listdom_alertify("Passwords do not match!", "lsd-error"));
                $form.removeClass("lsd-loading");
                return;
            }

            // Abort previous request
            if (ajax) ajax.abort();

            const data = $form.serialize();
            ajax = $.ajax({
                type: "POST",
                url: settings.ajax_url,
                data: data,
                dataType: "JSON",
                success: function (response) {
                    // Remove the Loading Class from the Form
                    $form.removeClass("lsd-loading");

                    if (response.success === 1) {
                        clearFieldMessages();

                        // Show the message
                        $message.html(listdom_alertify(response.message, "lsd-success"));

                        // Set the event id
                        $("#lsd_dashboard_id").val(response.data.id);

                        // Labelize Addon
                        const $labelize = $("#lsd_labelize_button");
                        if ($labelize.length) {
                            // Set Listing ID
                            $labelize.data("id", response.data.id);

                            // Hide Message
                            $(".lsd-labelize-metabox .lsd-labelize-message").addClass(
                                "lsd-util-hide"
                            );

                            // Show Button
                            $labelize.removeClass("lsd-util-hide");
                        }
                    } else {
                        // Show the message
                        $message.html(listdom_alertify(response.message, "lsd-error"));
                        applyFieldMessages(response.data || {});
                    }
                },
                error: function () {
                    // Remove the Loading Class from the Form
                    $form.removeClass("lsd-loading");
                },
            });
        }
        function profile_image_upload() {
            // Alert
            let $alert = $("#lsd_profile_image_message");

            // Wrapper
            let $wrapper = $(".lsd-profile-image-container");

            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_upload_profile_image");
            fd.append("_wpnonce", settings.nonce);
            fd.append("file", $profile_image_upload.prop("files")[0]);

            // Empty Alert
            $alert.html("");

            $.ajax({
                url: settings.ajax_url,
                type: "POST",
                data: fd,
                dataType: "json",
                processData: false,
                contentType: false,
            }).done(function (response) {
                // Loading Style
                $wrapper.removeClass("lsd-loading");

                if (response.success) {
                    $profile_image_input.val(response.data.attachment_id);
                    $profile_image_upload.val("");
                    $profile_image_preview.html('<img src="' + response.data.url + '" alt="">');
                    $profile_image_remove.removeClass("lsd-util-hide");
                    $profile_image_button.addClass("lsd-util-hide");
                    $profile_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    $profile_image_input.val('');
                    $profile_image_upload.val("");
                    $profile_image_button.removeClass("lsd-util-hide");
                    $profile_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 9000);
            });
        }

        function profile_image_remove() {
            $profile_image_input.val("");
            $profile_image_preview.html("");
            $profile_image_remove.addClass("lsd-util-hide");
            $profile_image_upload.addClass("lsd-util-hide");
            $profile_image_button.removeClass("lsd-util-hide");
        }

        function hero_image_upload() {
            // Alert
            let $alert = $("#lsd_hero_image_message");

            // Wrapper
            let $wrapper = $(".lsd-profile-hero-image-container");

            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_upload_profile_image");
            fd.append("_wpnonce", settings.nonce);
            fd.append("file", $hero_image_upload.prop("files")[0]);

            // Empty Alert
            $alert.html("");

            $.ajax({
                url: settings.ajax_url,
                type: "POST",
                data: fd,
                dataType: "json",
                processData: false,
                contentType: false,
            }).done(function (response) {
                // Loading Style
                $wrapper.removeClass("lsd-loading");

                if (response.success) {
                    $hero_image_input.val(response.data.attachment_id);
                    $hero_image_upload.val("");
                    $hero_image_preview.html('<img src="' + response.data.url + '" alt="">');
                    $hero_image_remove.removeClass("lsd-util-hide");
                    $hero_image_button.addClass("lsd-util-hide");
                    $hero_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    $hero_image_input.val('');
                    $hero_image_upload.val("");
                    $hero_image_button.removeClass("lsd-util-hide");
                    $hero_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 9000);
            });
        }

        function hero_image_remove() {
            $hero_image_input.val("");
            $hero_image_preview.html("");
            $hero_image_remove.addClass("lsd-util-hide");
            $hero_image_upload.addClass("lsd-util-hide");
            $hero_image_button.removeClass("lsd-util-hide");
        }
    };
})(jQuery);

// Listdom LOGIN FORM PLUGIN
(function ($) {
    $.fn.listdomLoginForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,
            can_resend: false,
            username_placeholder: "",
            password_placeholder: "",
        }, options);

        return this.each(function () {
            let $form = $(this);
            let $wrapper = $form.closest(".lsd-login-wrapper");
            let $login = $wrapper.length ? $wrapper : $form;
            let $message = $wrapper.find(".lsd-login-form-message");
            let $resend = $wrapper.find(".lsd-resend-verification");
            let $resendWrapper = $wrapper.find(".lsd-login-resend-wrapper");
            let $userLogin = $form.find('input[name="log"]');
            let $userPassword = $form.find('input[name="pwd"]');
            const canResend = !!(settings.can_resend && $resend.length && $resendWrapper.length);
            let ajax = false;
            let resendAjax = false;

            if (settings.username_placeholder && $userLogin.length) $userLogin.attr("placeholder", settings.username_placeholder);

            if (settings.password_placeholder && $userPassword.length) $userPassword.attr("placeholder", settings.password_placeholder);

            setListeners();
            handleResendVisibility(false);

            function getMessageTarget()
            {
                if ($message.length) return $message;
                const $fallback = $form.find(".lsd-login-form-message");
                return $fallback.length ? $fallback : $login;
            }

            function setListeners() {
                $form.off("submit").on("submit", function (event) {
                    event.preventDefault();
                    login();
                });

                if (!canResend) {
                    $resend.prop("disabled", true);
                    return;
                }

                $resend.off("click").on("click", function (event) {
                    event.preventDefault();
                    resendVerification();
                });
            }

            function login() {
                // Message
                const $messageTarget = getMessageTarget();

                // Hide the Message
                $messageTarget.html("");

                // Add loading Class to the form
                $form.addClass("lsd-loading");

                // Loading Style
                $login.fadeTo(200, 0.7);

                // Abort previous request
                if (ajax) ajax.abort();

                const data = $form.serialize() + '&action=lsd_login&lsd_login=' + settings.nonce;

                ajax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: data,
                    dataType: "JSON",
                    success: function (response) {
                        $form.removeClass("lsd-loading");

                        if (response.success === 1) {
                            $messageTarget.html(listdom_alertify(response.message, "lsd-success"));
                            handleResendVisibility(false);
                            if (response.redirect) {
                                setTimeout(() => {
                                    window.location.href = response.redirect;
                                }, 1000);
                            }
                        } else {
                            $messageTarget.html(listdom_alertify(response.message, "lsd-error"));
                            handleResendVisibility(response.allow_resend === 1 || response.allow_resend === true, response.user_login);
                        }
                        $login.fadeTo(200, 1);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $messageTarget.html(listdom_alertify(errorThrown, "lsd-error"));
                        $form.removeClass("lsd-loading");
                    },
                });
            }

            function resendVerification() {
                const $messageTarget = getMessageTarget();
                const userLogin = $userLogin.val();

                // Hide the Message
                $messageTarget.html("");

                $login.fadeTo(200, 0.7);
                $resend.addClass("lsd-loading").prop("disabled", true);

                // Abort previous request
                if (resendAjax) resendAjax.abort();

                const resendNonce = settings.resend_nonce ? settings.resend_nonce : $form.find('input[name="lsd_resend_verification"]').val();

                resendAjax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: {
                        action: "lsd_resend_verification",
                        log: userLogin,
                        lsd_resend_verification: resendNonce,
                    },
                    dataType: "JSON",
                    success: function (response) {
                        const type = response.success === 1 ? "lsd-success" : "lsd-error";
                        $messageTarget.html(listdom_alertify(response.message, type));
                        handleResendVisibility(response.allow_resend === 1 || response.allow_resend === true, response.user_login || userLogin);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $messageTarget.html(listdom_alertify(errorThrown, "lsd-error"));
                    },
                    complete: function () {
                        $resend.removeClass("lsd-loading").prop("disabled", false);
                        $login.fadeTo(200, 1);
                    },
                });
            }

            function handleResendVisibility(allow, userLogin) {
                if (!canResend || !$resendWrapper.length) return;

                if (allow) {
                    if (userLogin) $userLogin.val(userLogin);
                    $resendWrapper.removeClass('lsd-util-hide');
                    $resend.prop('disabled', false);
                    return;
                }

                $resendWrapper.addClass('lsd-util-hide');
                $resend.prop('disabled', true);
            }
        });
    };
})(jQuery);

// Listdom REGISTER FORM PLUGIN
(function ($) {
    $.fn.listdomRegisterForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,

        }, options);
        return this.each(function () {
            let $form = $(this);
            let $register = $form.closest(".lsd-register-wrapper");
            let $message = $register.find(".lsd-register-form-message");
            let $password = $form.find('input[name="lsd_password"]');
            let $rulesWrapper = $register.find(".lsd-register-password-rules");
            let $rules = $rulesWrapper.find(".lsd-register-password-rule");
            let ajax = false;

            if (!$message.length) $message = $form.find(".lsd-register-form-message");
            if (!$rulesWrapper.length) $rulesWrapper = $form.find(".lsd-register-password-rules");
            if (!$rules.length) $rules = $rulesWrapper.find(".lsd-register-password-rule");

            setListeners();

            function setListeners() {
                $form.off("submit").on("submit", function (event) {
                    event.preventDefault();
                    register();
                });

                if ($password.length && $rules.length) {
                    $password.off("input.lsdPasswordRules").on("input.lsdPasswordRules", function () {
                        updatePasswordRules();
                    });
                    updatePasswordRules();
                }
            }

            function updatePasswordRules() {
                if (!$password.length || !$rules.length) return;

                const value = $password.val() || "";

                $rules.each(function () {
                    const $rule = $(this);
                    const rule = $rule.data("rule");
                    let isMet = false;

                    switch (rule) {
                        case "length": {
                            const minLength = parseInt($rule.data("min-length"), 10) || 0;
                            isMet = value.length >= minLength;
                            break;
                        }

                        case "uppercase":
                            isMet = /[A-Z]/.test(value);
                            break;

                        case "lowercase":
                            isMet = /[a-z]/.test(value);
                            break;

                        case "number":
                            isMet = /\d/.test(value);
                            break;

                        case "special":
                            isMet = /[\W_]/.test(value);
                            break;
                    }

                    $rule.toggleClass("lsd-register-password-rule--met", isMet);
                });
            }

            function register() {

                // Hide the Message
                $message.html("");

                // Add loading Class to the form
                $form.addClass("lsd-loading");

                // Loading Style
                $register.fadeTo(200, 0.7);

                // Abort previous request
                if (ajax) ajax.abort();

                const data = $form.serialize() + '&action=lsd_register&lsd_register=' + settings.nonce;
                ajax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: data,
                    dataType: "JSON",
                    success: function (response) {
                        // Remove the Loading Class from the Form
                        $form.removeClass("lsd-loading");

                        if (response.success === 1) {
                            // Show the message
                            $message.html(listdom_alertify(response.message, "lsd-success"));
                            if (response.redirect) {
                                setTimeout(() => {
                                    window.location.href = response.redirect;
                                }, 1000);
                            }
                        } else {
                            // Show the message
                            $message.html(listdom_alertify(response.message, "lsd-error"));
                        }
                        // Loading Style
                        $register.fadeTo(200, 1);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Remove the Loading Class from the Form
                        $message.html(listdom_alertify(errorThrown, "lsd-error"));
                        $form.removeClass("lsd-loading");
                    },
                });
            }
        });
    };
})(jQuery);

// Listdom FORGOT PASSWORD FORM PLUGIN
(function ($) {
    $.fn.listdomForgotPasswordForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,

        }, options);
        return this.each(function () {
            let $form = $(this);
            let $register = $form.closest(".lsd-forgot-password-wrapper");
            let $message = $register.find(".lsd-forgot-password-form-message");
            let ajax = false;

            if (!$message.length) $message = $form.find(".lsd-forgot-password-form-message");

            setListeners();

            function setListeners() {
                $form.off("submit").on("submit", function (event) {
                    event.preventDefault();
                    forgotPassword();
                });
            }

            function forgotPassword() {

                // Hide the Message
                $message.html("");

                // Add loading Class to the form
                $form.addClass("lsd-loading");

                // Loading Style
                $register.fadeTo(200, 0.7);

                // Abort previous request
                if (ajax) ajax.abort();

                const data = $form.serialize() + '&action=lsd_forgot_password&lsd_forgot_password=' + settings.nonce;
                ajax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: data,
                    dataType: "JSON",
                    success: function (response) {
                        // Remove the Loading Class from the Form
                        $form.removeClass("lsd-loading");

                        if (response.success === 1) {
                            // Show the message
                            $message.html(listdom_alertify(response.message, "lsd-success"));
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            // Show the message
                            $message.html(listdom_alertify(response.message, "lsd-error"));
                        }
                        // Loading Style
                        $register.fadeTo(200, 1);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Remove the Loading Class from the Form
                        $message.html(listdom_alertify(errorThrown, "lsd-error"));
                        $form.removeClass("lsd-loading");
                    },
                });
            }
        });
    };
})(jQuery);

// Listdom RESET PASSWORD PLUGIN
(function ($) {
    $.fn.listdomResetPasswordForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,
        }, options);

        return this.each(function () {
            let $form = $(this);
            let $wrapper = $form.closest(".lsd-reset-password-wrapper");
            let $message = $wrapper.find(".lsd-reset-password-form-message");
            let ajax = false;

            if (!$message.length) $message = $form.find(".lsd-reset-password-form-message");

            setListeners();

            function setListeners() {
                $form.off("submit").on("submit", function (event) {
                    event.preventDefault();
                    resetPassword();
                });
            }

            function resetPassword() {
                $message.html("");
                $form.addClass("lsd-loading");
                $wrapper.fadeTo(200, 0.7);

                if (ajax) ajax.abort();

                const data = $form.serialize() + '&action=lsd_reset_password&lsd_reset_password=' + settings.nonce;
                ajax = $.ajax({
                    type: "POST",
                    url: settings.ajax_url,
                    data: data,
                    dataType: "JSON",
                    success: function (response) {
                        $form.removeClass("lsd-loading");

                        if (response.success === 1) {
                            $message.html(listdom_alertify(response.message, "lsd-success"));
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            $message.html(listdom_alertify(response.message, "lsd-error"));
                        }
                        $wrapper.fadeTo(200, 1);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $message.html(listdom_alertify(errorThrown, "lsd-error"));
                        $form.removeClass("lsd-loading");
                    },
                });
            }
        });
    };
})(jQuery);

// Listdom MOBILE VERIFICATION PLUGIN
(function ($) {
    $.fn.listdomMobileVerification = function (options) {
        // Default Options
        let settings = $.extend(
            {
                // These are the defaults.
                ajax_url: 0,
            },
            options
        );

        let $wrapper = $(
            "#lsdaddsms_verification_form_" + settings.id + "_wrapper"
        );
        let $sendForm = $wrapper.find($(".lsdaddsms-send-vcode"));
        let $verifyForm = $wrapper.find($(".lsdaddsms-verify-vcode"));

        setListeners();

        function setListeners() {
            // Send Verification Code
            $sendForm.off("submit").on("submit", function (e) {
                e.preventDefault();

                let $alert = $wrapper.find(
                    jQuery(".lsdaddsms-mobile-verification-alert")
                );
                let data = $sendForm.serialize();

                // Loading Style
                $wrapper.addClass("lsd-loading");

                // Empty Alert
                $alert.html("");

                jQuery.ajax({
                    url: lsd.ajaxurl,
                    data: data,
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                        // Loading Style
                        $wrapper.removeClass("lsd-loading");

                        if (response.success) {
                            // Hide Send VCode Form
                            $sendForm.removeClass("lsd-util-show").addClass("lsd-util-hide");

                            // Show Verify VCode Form
                            $verifyForm
                            .removeClass("lsd-util-hide")
                            .addClass("lsd-util-show");

                            // Show Alert
                            $alert.html(listdom_alertify(response.message, "lsd-success"));
                        } else {
                            // Show Alert
                            $alert.html(listdom_alertify(response.message, "lsd-error"));
                        }
                    },
                    error: function () {
                        // Loading Style
                        $wrapper.removeClass("lsd-loading");
                    },
                });
            });

            // Verify the Code
            $verifyForm.off("submit").on("submit", function (e) {
                e.preventDefault();

                let $alert = $wrapper.find(
                    jQuery(".lsdaddsms-mobile-verification-alert")
                );
                let data = $verifyForm.serialize();

                // Loading Style
                $wrapper.addClass("lsd-loading");

                // Empty Alert
                $alert.html("");

                jQuery.ajax({
                    url: lsd.ajaxurl,
                    data: data,
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                        // Loading Style
                        $wrapper.removeClass("lsd-loading");

                        if (response.success) {
                            // Hide Verify VCode Form
                            $verifyForm
                            .removeClass("lsd-util-show")
                            .addClass("lsd-util-hide");

                            // Show Alert
                            $alert.html(listdom_alertify(response.message, "lsd-success"));

                            // Reload the Page
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            // Show Alert
                            $alert.html(listdom_alertify(response.message, "lsd-error"));
                        }
                    },
                    error: function () {
                        // Loading Style
                        $wrapper.removeClass("lsd-loading");
                    },
                });
            });
        }
    };
})(jQuery);

function listdom_onload() {
    if (typeof window.lsdInitFlatpickr === 'function') window.lsdInitFlatpickr(document);

    jQuery(document).off('click.lsdBookableFlatpickr', '.lsd-add-bookable-button').on('click.lsdBookableFlatpickr', '.lsd-add-bookable-button', function ()
    {
        const target = jQuery(this).data('for');
        if (!target) return;

        setTimeout(function ()
        {
            const $target = jQuery(target);
            if ($target.length && typeof window.lsdInitFlatpickr === 'function') window.lsdInitFlatpickr($target);
        }, 0);
    });

    listdom_trigger_favorites();
    listdom_trigger_compare_modal();
    listdom_trigger_message_modal();
    listdom_trigger_share_modal();

    listdom_trigger_compare();
    listdom_trigger_compare_delete();
    if (jQuery('.lsdaddcmp-compare').length) listdom_compare_add_listings();

    lsdaddrev_trigger_feedback();
    lsdaddrev_trigger_delete();
    listdom_trigger_autosuggest_remove();
    listdom_trigger_toggle();
    listdom_image_slider();
    listdom_linear_gallery_modal();
    listdom_listing_link_lightbox();
    listdom_trigger_pickr();
    listdom_bricks_onload();

    // Listdom Onload Event
    jQuery(document).trigger('listdom:onload');
}

function listdom_bricks_onload() {
    if (typeof bricksRunAllFunctions !== 'function') return;

    bricksRunAllFunctions();
}

function listdom_bricks_infowindow_onload() {
    setTimeout(listdom_bricks_onload, 0);
}

function listdom_mount_fragment_assets($content) {
    if (!$content || !$content.length || typeof document === 'undefined') return;

    const trimValue = function (value) {
        return typeof value === 'string' ? value.trim() : '';
    };

    const $assets = $content
    .filter('style, link[rel="stylesheet"], script')
    .add($content.find('style, link[rel="stylesheet"], script'));

    $assets.each(function () {
        const node = this;
        const tag = (node.tagName || '').toLowerCase();

        if (tag === 'style') {
            const key = node.id
                ? 'style-id:' + node.id
                : 'style:' + (node.getAttribute('data-template-id') || '') + ':' + trimValue(node.textContent || '');

            const alreadyMounted = Array.from(document.querySelectorAll('style[data-lsd-mounted-key]')).some(function (style) {
                return style.getAttribute('data-lsd-mounted-key') === key;
            });

            if (!key || alreadyMounted) return;

            const style = document.createElement('style');
            if (node.id) style.id = node.id;
            if (node.getAttribute('data-template-id')) style.setAttribute('data-template-id', node.getAttribute('data-template-id'));
            style.setAttribute('data-lsd-mounted-key', key);
            style.textContent = node.textContent || '';
            document.head.appendChild(style);
        } else if (tag === 'link') {
            const href = node.href || node.getAttribute('href');
            if (!href) return;

            const alreadyMounted = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(function (link) {
                return link.href === href;
            });

            if (alreadyMounted) return;

            document.head.appendChild(node.cloneNode(true));
        } else if (tag === 'script') {
            const src = node.src || node.getAttribute('src');
            if (src) {
                const alreadyMounted = Array.from(document.scripts).some(function (script) {
                    return script.src === src;
                });

                if (alreadyMounted) return;
            }

            const key = src
                ? 'script-src:' + src
                : 'script-inline:' + trimValue(node.textContent || '');

            const alreadyMounted = Array.from(document.querySelectorAll('script[data-lsd-mounted-key]')).some(function (script) {
                return script.getAttribute('data-lsd-mounted-key') === key;
            });

            if (!key || alreadyMounted) return;

            const script = document.createElement('script');
            Array.from(node.attributes || []).forEach(function (attribute) {
                script.setAttribute(attribute.name, attribute.value);
            });

            script.setAttribute('data-lsd-mounted-key', key);
            if (!src) script.text = node.textContent || '';

            document.body.appendChild(script);
        }
    });
}

function listdom_trigger_favorites() {
    // Favorite Button
    jQuery(document)
    .on('click', '.lsd-favorite-toggle', function (e) {
        e.preventDefault();

        let $button = jQuery(this);
        let id = $button.data("id");
        let nonce = $button.data("nonce");

        // Add Loading
        $button.addClass("lsd-favorite-loading");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: "action=lsd_favorite&id=" + id + "&_wpnonce=" + nonce,
            dataType: "json",
            type: "post",
            success: function (response) {
                // Remove Loading
                $button.removeClass("lsd-favorite-loading");

                if (response.success) {
                    $button.data("status", response.status);

                    // Added
                    if (response.status)
                        $button
                        .removeClass("lsd-favorite-off")
                        .addClass("lsd-favorite-on");
                    // Removed
                    else
                        $button
                        .removeClass("lsd-favorite-on")
                        .addClass("lsd-favorite-off");

                    // Update Count
                    jQuery(".lsdaddfav-count").html(response.count);
                }
            },
            error: function () {
                // Remove Loading
                $button.removeClass("lsd-favorite-loading");
            },
        });
    });
}

function listdom_trigger_share_modal() {
    jQuery('.lsd-share-modal-button').on('click', function () {
        const dataId = jQuery(this).data('id');
        if (!dataId || typeof ListdomModal === 'undefined') return;

        ListdomModal.open(`#lsd-share-modal-${dataId}`, { appendToBody: true });
    });
}

function listdom_trigger_message_modal() {
    jQuery('.lsd-message-modal-button').on('click', function () {
        if (typeof ListdomModal === 'undefined') return;

        ListdomModal.open('#lsd-message-modal');
    });
}

function listdom_trigger_compare_modal() {

    // Elementor Skins widgets ancestor handling
    const hasConstrainedAncestor = (modal) =>
    {
        const constrainedAncestor = modal.parents().filter(function () {
            const styles = window.getComputedStyle(this);
            const overflowX = styles.overflowX;
            const overflowY = styles.overflowY;
            const transform = styles.transform;
            const perspective = styles.perspective;
            const filter = styles.filter;
            const backdropFilter = styles.backdropFilter;
            const contain = styles.contain;

            const clipsOverflow = ['hidden', 'clip', 'auto', 'scroll'].includes(overflowX) || ['hidden', 'clip', 'auto', 'scroll'].includes(overflowY);
            const createsStackingContext =
                (transform && transform !== 'none') ||
                (perspective && perspective !== 'none') ||
                (filter && filter !== 'none') ||
                (backdropFilter && backdropFilter !== 'none') ||
                (contain && contain !== 'none');

            return clipsOverflow || createsStackingContext;
        }).first();

        return constrainedAncestor.length > 0;
    };

    const detachIfClipped = (modal) => {
        if (!modal.length || modal.data('lsdCompareDetached')) return modal;

        const isClipped =
            modal.closest('.owl-stage-outer, .owl-carousel').length > 0 ||
            hasConstrainedAncestor(modal);

        if (!isClipped) return modal;

        const placeholder = jQuery('<span class="lsd-compare-modal-placeholder" aria-hidden="true"></span>');
        placeholder.insertBefore(modal);
        modal
        .data('lsdCompareDetached', true)
        .data('lsdComparePlaceholder', placeholder)
        .appendTo('body');

        return modal;
    };

    const restoreModal = (modal) => {
        const placeholder = modal.data('lsdComparePlaceholder');

        if (placeholder && placeholder.length) {
            placeholder.replaceWith(modal);
            modal.removeData('lsdComparePlaceholder').removeData('lsdCompareDetached');
        }

        jQuery('[data-detached="true"]').remove();
    };

    const closeModal = (modal) => {
        if (typeof ListdomModal !== 'undefined') {
            ListdomModal.close(modal);
            return;
        }

        modal.fadeOut(100, () => {
            listdom_resume_page_scroll_if_locked();
            restoreModal(modal);
        });
    };

    jQuery('.lsd-compare-toggle').off('click.lsdCompareModal').on('click.lsdCompareModal', function () {
        const dataId = jQuery(this).data('id');
        const title = jQuery(this).data('listing-title');
        const cover = jQuery(this).data('cover');
        let modal = jQuery(`#lsd_compare_${dataId}`).find(`#lsd-compare-modal-${dataId}`);

        if (!modal.length) modal = jQuery(`#lsd-compare-modal-${dataId}`);

        if (!modal.length) return;

        modal = detachIfClipped(modal);
        if (typeof ListdomModal !== 'undefined') {
            ListdomModal.open(modal, {appendToBody: !!modal.data('lsdCompareDetached')});
        }
        else {
            modal.css('display', 'flex').hide().fadeIn(100);
            if (typeof ListdomPageScroll !== 'undefined') ListdomPageScroll.stop();
        }

        jQuery('.lsd-modal-title').html(title);
        jQuery('.lsd-modal-cover').html(cover);

        modal
        .off('listdom:modal:closed.lsdCompareModal')
        .on('listdom:modal:closed.lsdCompareModal', function () {
            jQuery('.lsd-compare-message').html('');
            restoreModal(modal);
        });
    });

    jQuery(window).off('click.lsdCompareModal').on('click.lsdCompareModal', function (event) {
        if (jQuery(event.target).closest('.lsd-modal-content').length === 0 && jQuery(event.target).is('.lsd-modal')) {
            const $modal = jQuery(event.target);
            closeModal($modal);
            jQuery('.lsd-compare-message').html('');
        }
    });
}

function listdom_resume_page_scroll_if_locked() {
    const isLocked =
        typeof ListdomPageScroll !== 'undefined' &&
        (
            ListdomPageScroll.isLocked ||
            document.body.classList.contains(ListdomPageScroll.lockedClass) ||
            document.documentElement.classList.contains(ListdomPageScroll.lockedClass)
        );

    if (isLocked) {
        ListdomPageScroll.start();

        if (jQuery('.lsd-modal:visible').length) {
            ListdomPageScroll.stop();
        }
    }
}

function listdom_trigger_compare() {
    jQuery(".lsd-modal-content .lsd-compare-toggle")
    .off("click")
    .on("click", function (e) {
        e.preventDefault();
        let compare = jQuery(this);
        let alert = jQuery('.lsd-compare-message');
        let parentToggle = jQuery(`.lsd-compare-toggle[data-id="${compare.data("id")}"]`).not(compare);

        let id = compare.data("id"),
            nonce = compare.data("nonce");
        compare.addClass("lsd-compare-loading"),
            jQuery.ajax({
                url: lsd.ajaxurl,
                data: "action=lsd_compare&content=1&id=" + id + "&_wpnonce=" + nonce,
                dataType: "json",
                type: "post",
                success: function (res) {
                    compare.removeClass("lsd-compare-loading"),
                    res.success &&
                    (compare.data("status", res.status),
                        res.status ? compare.removeClass("lsd-compare-off").addClass("lsd-compare-on") : compare.removeClass("lsd-compare-on").addClass("lsd-compare-off"),
                        jQuery(".lsdaddcmp-count").html(res.count));

                    parentToggle.data("status", res.status);
                    if (res.status) parentToggle.removeClass("lsd-compare-off").addClass("lsd-compare-on");
                    else parentToggle.removeClass("lsd-compare-on").addClass("lsd-compare-off");

                    if (res.success && res.content) {
                        jQuery(".lsdaddcmp-compare").replaceWith(res.content);
                        listdom_compare_add_listings();
                        listdom_trigger_compare_delete();
                        listdom_resume_page_scroll_if_locked();
                    }

                    if (!res.success) alert.html(listdom_alertify(res.message, 'lsd-error'));
                },

                error: function () {
                    compare.removeClass("lsd-compare-loading");
                },
            });
    });
}

function listdom_trigger_compare_delete() {
    jQuery(".lsd-compare-delete")
    .off('click')
    .on('click', function (e) {
        e.preventDefault();

        const $icon = jQuery(this);
        const id = $icon.data('id');
        const nonce = $icon.data('nonce');

        // Loading Style
        $icon.addClass('lsd-compare-loading');

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: "action=lsd_compare&content=1&id=" + id + "&_wpnonce=" + nonce,
            dataType: 'json',
            type: 'post',
            success: function (response) {
                // Loading Style
                $icon.removeClass('lsd-compare-loading');

                // Replace Content
                if (response.success) {
                    jQuery(".lsdaddcmp-compare").replaceWith(response.content);
                }

                listdom_compare_add_listings();
                listdom_trigger_compare_delete();
            },
            error: function () {
                // Loading Style
                $icon.removeClass('lsd-compare-loading');
            },
        });
    });
}

function listdom_compare_add_listings() {

    const getBtn = () => jQuery('.lsdaddcmp-add-listings').first();

    const getCompareWrapper = () => {
        const $btn = getBtn();
        if ($btn.length) {
            const $closestCompare = $btn.closest('.lsdaddcmp-compare');
            if ($closestCompare.length) return $closestCompare;

            const $near = $btn
            .closest('.lsdaddcmp-wrap, .lsdaddcmp-container, .lsdaddcmp-section, .lsdaddcmp')
            .find('.lsdaddcmp-compare')
            .first();

            if ($near.length) return $near;
        }

        return jQuery('.lsdaddcmp-compare').first();
    };

    // Cache modal elements
    const $modal = jQuery('#lsdaddcmp-add-modal');
    const $container = $modal.find('.lsdaddcmp-shortcode');

    if (!getBtn().length) return;

    const handleAjaxError = (xhr, textStatus, error) => {
        if (typeof listdom_toastify === 'function') {
            listdom_toastify('AJAX request failed: ' + error, 'lsd-error');
        }
    };

    const isDisabled = () => {
        const $btn = getBtn();
        return (
            $btn.attr('aria-disabled') === 'true' ||
            $btn.hasClass('is-disabled')
        );
    };

    const showLimitNotice = () => {
        const $btn = getBtn();
        const message =
            $btn.data('limit-message') ||
            $btn.data('max-items-message') ||
            $btn.data('message');

        if (message && typeof listdom_toastify === 'function') {
            listdom_toastify(message, 'lsd-warning');
        }
    };

    const getWrapperIds = () => {
        const $wrapper = getCompareWrapper();
        return $wrapper.data('ids');
    };

    const syncModalIds = () => {
        const wrapperIds = getWrapperIds();
        if (wrapperIds !== undefined) {
            $modal.attr('data-ids', wrapperIds);
            $modal.data('ids', wrapperIds);
        }
    };

    const refreshAddButtonState = () => {
        const $btn = getBtn();
        if (!$btn.length) return;

        const rawMax =
            $btn.data('max-items') ??
            $btn.data('max_items') ??
            $btn.data('max') ??
            $btn.data('limit');

        const maxItems = parseInt(
            rawMax === undefined || rawMax === null || rawMax === ''
                ? 8
                : rawMax,
            10
        );

        if (isNaN(maxItems) || maxItems <= 0) return;

        const ids = getWrapperIds();
        const count = ids ? ids.toString().split(',').filter(Boolean).length : 0;
        const isAtLimit = count >= maxItems;

        $btn
        .toggleClass('is-disabled', isAtLimit)
        .attr('aria-disabled', isAtLimit ? 'true' : 'false');
    };

    const loadModalShortcode = () => {
        syncModalIds();

        const ids = $modal.data('ids')?.toString() || '';

        if (typeof lsd === 'undefined' || !lsd.ajaxurl) return;

        $container.addClass('lsd-loading');

        jQuery.ajax({
            url: lsd.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'lsd_compare_modal_shortcode',
                ids
            },
            success: (response) => {
                if (response && response.success && response.content !== undefined) {
                    $container.html(response.content);
                    if (typeof listdom_onload === 'function') {
                        listdom_onload();
                    }
                } else if (response && response.message && typeof listdom_toastify === 'function') {
                    listdom_toastify(response.message, 'lsd-error');
                }
            },
            error: handleAjaxError,
            complete: () => {
                $container.removeClass('lsd-loading');
            }
        });
    };

    const openModal = () => {
        $modal.css('display', 'flex').hide().fadeIn(100);

        if (
            typeof ListdomPageScroll !== 'undefined' &&
            typeof ListdomPageScroll.stop === 'function'
        ) {
            ListdomPageScroll.stop();
        }

        loadModalShortcode();
    };

    const closeModal = () => {
        $modal.fadeOut(100);

        if (
            typeof ListdomPageScroll !== 'undefined' &&
            typeof ListdomPageScroll.start === 'function'
        ) {
            ListdomPageScroll.start();
        }
    };

    const toggleCompare = (id) => {
        if (!id) return;
        if (typeof lsd === 'undefined' || !lsd.ajaxurl) return;

        jQuery.ajax({
            url: lsd.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'lsd_compare_nonce',
                id
            },
            success: (nonceRes) => {
                if (!nonceRes || !nonceRes.success || !nonceRes.nonce) {
                    if (nonceRes?.message && typeof listdom_toastify === 'function') {
                        listdom_toastify(nonceRes.message, 'lsd-error');
                    }
                    return;
                }

                jQuery.ajax({
                    url: lsd.ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: `action=lsd_compare&content=1&id=${id}&_wpnonce=${nonceRes.nonce}`,
                    success: (res) => {
                        if (res && res.success && res.content) {
                            const $old = getCompareWrapper();
                            if ($old.length) {
                                $old.replaceWith(res.content);
                            } else {
                                jQuery('.lsdaddcmp-compare').first().replaceWith(res.content);
                            }

                            syncModalIds();
                            refreshAddButtonState();

                            if ($modal.is(':visible')) {
                                loadModalShortcode();
                            }

                            if (typeof listdom_trigger_compare_delete === 'function') {
                                listdom_trigger_compare_delete();
                            }

                            if (typeof listdom_resume_page_scroll_if_locked === 'function') {
                                listdom_resume_page_scroll_if_locked();
                            }
                        }

                        if (res?.message && typeof listdom_toastify === 'function') {
                            listdom_toastify(
                                res.message,
                                res.success ? 'lsd-success' : 'lsd-error'
                            );
                        }
                    },
                    error: handleAjaxError
                });
            },
            error: handleAjaxError
        });
    };

    // Event bindings
    jQuery(document).off('click', '.lsdaddcmp-add-listings').on('click', '.lsdaddcmp-add-listings', (e) => {
        e.preventDefault();

        if (isDisabled())
        {
            showLimitNotice();
            return;
        }

        openModal();
    });

    $modal.off('click').on('click', (e) => {
        const clickedInside = !!jQuery(e.target).closest('.lsd-modal-content').length;
        const clickedBackdrop = jQuery(e.target).is('.lsd-modal');

        if (!clickedInside && clickedBackdrop) {
            closeModal();
        }
    });

    $container
    .off('click', '.lsd-listing')
    .on('click', '.lsd-listing', (e) => {
        if (jQuery(e.target).closest('.lsd-compare-toggle').length) return;

        e.preventDefault();
        e.stopPropagation();

        const $listing = jQuery(e.currentTarget);
        const $toggle = $listing.find('.lsd-compare-toggle').first();

        if ($toggle.length) {
            $toggle.trigger('click');
            return;
        }

        const id = $listing.find('[data-listing-id]').data('listing-id');
        toggleCompare(id);
    });

    if (typeof listdom_trigger_compare === 'function') {
        listdom_trigger_compare();
    }

    refreshAddButtonState();
    syncModalIds();
}

function lsdaddrev_trigger_feedback() {
    // Review Like / Dislike
    jQuery(".lsdaddrev-feedback-module li")
    .off("click")
    .on("click", function (e) {
        e.preventDefault();

        let $module = jQuery(this).parent().parent();
        let id = $module.data("id");
        let nonce = $module.data("nonce");
        let type = jQuery(this).data("type");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: {
                action: "lsdaddrev_feedback",
                _wpnonce: nonce,
                id: id,
                type: type,
            },
            type: "post",
            dataType: "json",
            success: function (response) {
                // Update Stats
                if (response.success) {
                    $module
                    .find(jQuery(".lsd-feedback-likes"))
                    .html(response.data.likes);
                    $module
                    .find(jQuery(".lsd-feedback-dislikes"))
                    .html(response.data.dislikes);
                }
            },
            error: function () {
            },
        });
    });
}

function lsdaddrev_trigger_delete() {
    // Review Delete
    jQuery(".lsd-review-delete")
    .off("click")
    .on("click", function (e) {
        e.preventDefault();

        let $button = jQuery(this);
        let id = $button.data("id");
        let nonce = $button.data("nonce");

        // Delete Confirm
        let confirm = $button.data("confirm");
        if (confirm === 0) {
            $button.data("confirm", 1);
            $button.addClass("lsd-need-confirm");

            setTimeout(function () {
                $button.data("confirm", 0);
                $button.removeClass("lsd-need-confirm");
            }, 10000);

            return;
        }

        // Review
        let $review = jQuery("#lsdaddrev_review" + id);

        // Loading Style
        $review.addClass("lsd-loading");

        // Disable the Button
        $button.prop("disabled", "disabled");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: {
                action: "lsdaddrev_delete",
                _wpnonce: nonce,
                id: id,
            },
            type: "post",
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Remove Review Item
                    $review.remove();
                }
            },
            error: function () {
                // Loading Style
                $review.removeClass("lsd-loading");

                // Enable the Button
                $button.removeProp("disabled");
            },
        });
    });
}

function lsdaddbok_trigger_booking_form() {
    // Booking Request
    jQuery(".lsd-booking-form")
    .off("submit")
    .on("submit", function (e) {
        e.preventDefault();

        let $form = jQuery(this);
        let $module = $form.parent().parent();
        let $alert = $module.find(jQuery(".lsd-booking-form-alert"));
        let data = $form.serialize();

        // Loading Style
        $module.addClass("lsd-loading");

        // Empty Alert
        $alert.html("");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: data,
            type: "post",
            dataType: "json",
            success: function (response) {
                // Loading Style
                $module.removeClass("lsd-loading");

                if (response.success) {
                    // Hide Form
                    $form.slideUp();

                    // Show Alert
                    $alert.html(
                        listdom_alertify(
                            response.message,
                            response.need_payment ? "lsd-info" : "lsd-success"
                        )
                    );

                    // Redirect
                    if (response.redirect && response.url) {
                        setTimeout(function () {
                            window.location.href = response.url;
                        }, 3000);
                    }
                } else {
                    // Show Alert
                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }
            },
            error: function () {
                // Loading Style
                $module.removeClass("lsd-loading");
            },
        });
    });
}

function lsdaddbok_trigger_booking_manage_actions() {
    // Manage Bookings
    jQuery(".lsd-bookings-manage-actions li")
    .off("click")
    .on("click", function () {
        let $button = jQuery(this);

        let id = $button.data("id");
        let method = $button.data("method");
        let nonce = $button.data("nonce");

        if (method === "trash" || method === "reject" || method === "cancel") {
            let confirm = $button.data("confirm");
            if (confirm === 0) {
                $button.data("confirm", 1);
                $button.addClass("lsd-need-confirm");

                setTimeout(function () {
                    $button.data("confirm", 0);
                    $button.removeClass("lsd-need-confirm");
                }, 5000);

                return;
            }
        }

        let $booking = jQuery("#lsd_bm_" + id);
        let $status = $booking.find(jQuery(".lsd-bookings-status-wrapper"));
        let $actions = $booking.find(jQuery(".lsd-bookings-manage-actions"));

        // Loading Style
        $booking.addClass("lsd-loading");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: {
                action: "lsdaddbok_bm", // Booking Manage
                id: id,
                method: method,
                _wpnonce: nonce,
            },
            type: "post",
            dataType: "json",
            success: function (response) {
                // Loading Style
                $booking.removeClass("lsd-loading");

                if (response.success) {
                    $status.html(response.new.status);
                    $actions.html(response.new.actions);

                    setTimeout(function () {
                        lsdaddbok_trigger_booking_manage_actions();
                    }, 500);
                }
            },
            error: function () {
                // Loading Style
                $booking.removeClass("lsd-loading");
            },
        });
    });
}

function lsdaddjob_trigger_application_manage_actions() {
    // Manage Applications
    jQuery(".lsdaddjob-applications-manage-actions li")
    .off("click")
    .on("click", function () {
        let $button = jQuery(this);

        let id = $button.data("id");
        let method = $button.data("method");
        let nonce = $button.data("nonce");

        if (method === "trash" || method === "reject") {
            let confirm = $button.data("confirm");
            if (confirm === 0) {
                $button.data("confirm", 1);
                $button.addClass("lsd-need-confirm");

                setTimeout(function () {
                    $button.data("confirm", 0);
                    $button.removeClass("lsd-need-confirm");
                }, 5000);

                return;
            }
        }

        let $application = jQuery("#lsdaddjob_application_" + id);
        let $status = $application.find(
            jQuery(".lsdaddjob-applications-status-wrapper")
        );
        let $actions = $application.find(
            jQuery(".lsdaddjob-applications-manage-actions")
        );

        // Loading Style
        $application.addClass("lsd-loading");

        jQuery.ajax({
            url: lsd.ajaxurl,
            data: {
                action: "lsdaddjob_am", // Application Manage
                id: id,
                method: method,
                _wpnonce: nonce,
            },
            type: "post",
            dataType: "json",
            success: function (response) {
                // Loading Style
                $application.removeClass("lsd-loading");

                if (response.success) {
                    $status.html(response.new.status);
                    $actions.html(response.new.actions);

                    setTimeout(function () {
                        lsdaddjob_trigger_application_manage_actions();
                    }, 500);
                }
            },
            error: function () {
                // Loading Style
                $application.removeClass("lsd-loading");
            },
        });
    });
}

function listdom_image_slider() {
    if (!jQuery.fn.lightSlider) return;

    jQuery('.lsd-image-slider-slider:visible').each(function () {
        var $slider = jQuery(this);

        // Prevent re-initialization
        if ($slider.data('lsdLightSlider')) return;

        // Remove ready class to start hidden
        $slider.removeClass('lsd-ready');

        var inst = $slider.lightSlider({
            item: 1,
            pager: false,
            adaptiveHeight: true,
            onSliderLoad: function ()
            {
                $slider.addClass('lsd-ready');
            }
        });

        $slider.data('lsdLightSlider', inst);
    });
}

function listdom_trigger_pickr()
{
    if (typeof Pickr === 'undefined') return;

    jQuery('.lsd-color-picker').each(function ()
    {
        const $container = jQuery(this);
        const $input = $container.prev('.lsd_color');
        const colorPickerInput = jQuery('.lsd-color-picker-input');

        colorPickerInput.val('#1d7ed3');
        
        const picker = Pickr.create({
            el: this,
            theme: 'classic',
            default: $input.val() || '#1d7ed3',
            components: {
                preview: false,
                opacity: false,
                hue: true,
                interaction: {
                    hex: false,
                    rgba: false,
                    input: false,
                    clear: false,
                    save: false
                }
            }
        });

        picker.on('change', (color) => {
            const hexColor = color.toHEXA().toString();
            if ($input.length) $input.val(hexColor);
            picker.setColor(hexColor);
            colorPickerInput.val(hexColor);
        });
    });
}

function listdom_linear_gallery_modal() {
    jQuery('.lsd-all-photos-button').on('click', function() {
        jQuery('#lsd-gallery-modal').fadeIn();
        if (typeof ListdomPageScroll !== 'undefined') ListdomPageScroll.stop();
    });

    jQuery('.lsd-gallery-modal-close, #lsd-gallery-modal').on('click', function(e) {
        if (e.target !== this) return;

        jQuery('#lsd-gallery-modal').fadeOut(() => {
            if (typeof ListdomPageScroll !== 'undefined') ListdomPageScroll.start();
        });
    });
}

function listdom_enable_listing_container_clicks(context) {
    const $context = context ? jQuery(context) : jQuery(document);

    if (!$context || !$context.length) return;

    $context
    .find('.lsd-listing')
    .attr('data-listdom-container-link', '1');
}

function listdom_listing_link_lightbox() {
    const findListingMap = function ($link) {
        const $skin = $link.closest('[id^="lsd_skin"]');

        if ($skin.length) {
            const skinId = parseInt(($skin.attr('id') || '').replace('lsd_skin', ''), 10);

            if (!isNaN(skinId)) {
                const linkedMap = new ListdomMaps(skinId);
                if (linkedMap.get()) return linkedMap;
            }
        }

        const mapIds = Object.keys(listdomSkinMaps || {});
        for (let i = 0; i < mapIds.length; i++) {
            const listingMap = new ListdomMaps(mapIds[i]);
            if (listingMap.get()) return listingMap;
        }

        return null;
    };

    const scrollToMapIfNeeded = function (listingMap, callback) {
        const mapInstance = listingMap ? listingMap.get() : null;
        const element = mapInstance && mapInstance.element ? mapInstance.element : null;
        const $element = element ? jQuery(element) : jQuery();

        if (!$element.length) {
            callback();
            return;
        }

        const domElement = $element.get(0);
        if (!domElement) {
            callback();
            return;
        }

        const rect = domElement.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        const topOffset = 120;
        const isVisible = rect.top >= topOffset && rect.bottom <= viewportHeight;

        if (isVisible) {
            callback();
            return;
        }

        jQuery('html, body').stop(true).animate(
            {
                scrollTop: Math.max(($element.offset()?.top || 0) - topOffset, 0),
            },
            300,
            callback
        );
    };

    jQuery(document)
    .off("click.listdomMapLink", "a[data-listdom-map]")
    .on("click.listdomMapLink", "a[data-listdom-map]", function (e) {
        e.preventDefault();

        const $a = jQuery(this);
        const listing_id = parseInt($a.data('listing-id'), 10);
        const listingMap = findListingMap($a);
        const href = $a.attr('href');

        if (!listingMap) {
            if (href) window.location.href = href;
            return;
        }

        scrollToMapIfNeeded(listingMap, function () {
            if (listingMap.openListing(listing_id)) return;
            if (href) window.location.href = href;
        });
    });

    // Lightbox
    jQuery("a[data-listdom-lightbox]").off("click").on("click", function (e) {
        e.preventDefault();
        const listing_id = jQuery(this).data('listing-id');
        const style = jQuery(this).data('listdom-style');
        let url = jQuery(this).attr('href');

        // Add Raw to the URL
        if (url.includes('?')) url += '&raw&lsd-style=' + style;
        else url += '?raw&lsd-style=' + style;

        // Listdom Details Plugin
        new ListdomDetails(
            listing_id,
            url,
            {}
        ).lightbox();
    });

    // Panel
    jQuery("a[data-listdom-panel]").off("click").on("click", function (e) {
        e.preventDefault();
        const $a = jQuery(this);
        const listing_id = $a.data('listing-id');
        const panel = $a.data('listdom-panel');
        const style = $a.data('listdom-style');
        let url = $a.attr('href');

        // Add Raw to the URL
        if (url.includes('?')) url += '&raw&lsd-style=' + style;
        else url += '?raw&lsd-style=' + style;

        // Listdom Details Plugin
        const $details = new ListdomDetails(
            listing_id,
            url,
            {}
        );

        // Left Panel
        if (panel === 'left') $details.leftPanel();
        // Right Panel
        else if (panel === 'right') $details.rightPanel();
        // Bottom Panel
        else $details.bottomPanel();
    });

    const containerClickAttribute = 'data-listdom-container-link';
    const containerClickSelector = `.lsd-listing[${containerClickAttribute}]`;
    const interactiveTargets =
        'a, button, input, select, textarea, label, [data-listdom-lightbox], [data-listdom-panel], .lsd-listing-icons-wrapper, .lsd-listing-share';

    const findPrimaryListingLink = function ($container) {
        const selectors = [
            'a[data-listdom-panel]',
            'a[data-listdom-lightbox]',
            '.lsd-listing-title a[data-listing-id]',
            '.lsd-listing-title a[href]',
            'a[data-listing-id]'
        ];

        for (let i = 0; i < selectors.length; i++) {
            const $candidate = $container
            .find(selectors[i])
            .filter(function () {
                return !jQuery(this).closest('.lsd-listing-icons-wrapper, .lsd-listing-share').length;
            })
            .first();

            if ($candidate.length) return $candidate;
        }

        return null;
    };

    jQuery(document).off('click.listdomListingContainer', containerClickSelector).on('click.listdomListingContainer', containerClickSelector, function (e)
    {
        const $target = jQuery(e.target);
        if ($target.closest(interactiveTargets).length) return;

        const $container = jQuery(this);
        const $link = findPrimaryListingLink($container);
        if (!$link || !$link.length) return;

        if ($link.is('[data-listdom-panel], [data-listdom-lightbox]')) {
            e.preventDefault();
            $link.trigger('click');
            return;
        }

        const linkNode = $link.get(0);
        if (!linkNode) return;

        e.preventDefault();

        if (typeof linkNode.click === 'function') {
            linkNode.click();
            return;
        }

        const href = $link.attr('href');
        if (!href) return;

        const target = $link.attr('target');
        if (target && target !== '_self') window.open(href, target);
        else window.location.href = href;
    });

    jQuery('[data-listdom-cta="popup"]').off('click').on('click', function (e) {
        e.preventDefault();
        if (typeof ListdomModal === 'undefined') return;

        const target = jQuery(this).data('listdom-cta-target');
        if (!target) return;

        ListdomModal.open('#' + target, { appendToBody: true });
    });
}

function lsdCheckoutShowProcessing()
{
    const $processing = jQuery('.lsd-checkout-processing');
    if (!$processing.length) return;

    jQuery('.lsd-checkout-appreciation').addClass('lsd-util-hide');
    jQuery('.lsd-checkout-wrapper').addClass('lsd-util-hide');
    $processing.removeClass('lsd-util-hide');
}

function lsdCheckoutHideProcessing()
{
    const $processing = jQuery('.lsd-checkout-processing');
    if (!$processing.length) return;

    $processing.addClass('lsd-util-hide');
}

function lsdCheckoutRestoreFromProcessing()
{
    const $processing = jQuery('.lsd-checkout-processing');
    if (!$processing.length || $processing.hasClass('lsd-util-hide')) return;

    $processing.addClass('lsd-util-hide');
    jQuery('.lsd-checkout-wrapper').removeClass('lsd-util-hide');
}

function lsdCheckoutComplete(orderKey)
{
    lsdCheckoutHideProcessing();

    jQuery('.lsd-checkout-wrapper').addClass('lsd-util-hide');

    const $app = jQuery('.lsd-checkout-appreciation');
    $app.removeClass('lsd-util-hide');

    const $invoiceWrapper = $app.find('.lsd-checkout-appreciation-invoice');
    if ($invoiceWrapper.length)
    {
        const base = $invoiceWrapper.data('invoiceBase') || '';
        if (base && orderKey)
        {
            const sep = base.indexOf('?') !== -1 ? '&' : '?';
            const invoiceUrl = base + sep + 'lsd-invoice=' + encodeURIComponent(orderKey);

            $invoiceWrapper.find('a').attr('href', invoiceUrl);
            $invoiceWrapper.removeClass('lsd-util-hide');
        }
    }

    const url = $app.data('thankyou');
    if (url)
    {
        setTimeout(function ()
        {
            const sep = url.indexOf('?') !== -1 ? '&' : '?';
            window.location.href = url + sep + 'lsd-order=' + orderKey;

        }, 3000);
    }
}

(function ($) {
    // Document is Ready!
    $(document).ready(function () {
        // Trigger
        listdom_onload();

        // Checkout gateway tabs
        $(document).on('click', '.lsd-checkout-tabs-nav a', function (e) {
            e.preventDefault();

            const $a = $(this);
            const gateway = $a.data('gateway');
            const $wrapper = $a.closest('.lsd-fe-tabs');
            const $li = $a.parent('li');

            $li.siblings().removeClass('lsd-active');
            $li.addClass('lsd-active');

            $wrapper.find('.lsd-gateway-form').addClass('lsd-util-hide');
            $('#lsd-gateway-form-' + gateway).removeClass('lsd-util-hide');
        });

        // Checkout button
        $(document).on('click', '.lsd-checkout-button', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const nonce = $btn.data('nonce');
            const gateway = $btn.data('gateway');
            const $wrapper = $btn.closest('.lsd-gateway-wrapper');
            const message = $wrapper.find('.lsd-checkout-message').val() || '';
            const name = $wrapper.find('.lsd-checkout-user-name').val() || '';
            const email = $wrapper.find('.lsd-checkout-user-email').val() || '';

            const consentEl = $wrapper.find('input[name="lsd_privacy_consent"]').get(0);

            if (consentEl && consentEl.required)
            {
                if (!consentEl.checked)
                {
                    if (typeof consentEl.reportValidity === 'function') consentEl.reportValidity();
                    return;
                }

                // Clear message when checked
                consentEl.setCustomValidity('');
            }

            const consentValue = consentEl && consentEl.checked ? '1' : '';

            $.ajax({
                url: lsd.ajaxurl,
                data: {action: 'lsd_checkout', _wpnonce: nonce, gateway: gateway, message: message, name: name, email: email, lsd_privacy_consent: consentValue},
                dataType: 'json',
                type: 'post',
                success: function (res) {
                    if (res && res.success) {
                        lsdCheckoutComplete(res.key ? res.key : res.order_id);
                    } else {
                        lsdCheckoutRestoreFromProcessing();

                        if (res && res.message) {
                            $wrapper.find('.lsd-checkout-response').html(res.message);
                        }
                    }
                },
                error: function () {
                    lsdCheckoutRestoreFromProcessing();
                }
            });
        });

        // Profile Contact Form
        $(".lsd-profile-contact-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $alert = $(".lsd-profile-contact-form-alert");

            let id = $form.data("id");
            let data = $form.serialize();

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $(
                "#lsd_profile_contact_form_" +
                id +
                " .lsd-profile-contact-form-button button"
            ).prop("disabled", "disabled");

            // Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_profile_contact_form_" +
                        id +
                        " .lsd-profile-contact-form-button button"
                    ).removeProp("disabled");

                    if (response.success) {
                        $form.hide();
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_profile_contact_form_" +
                        id +
                        " .lsd-profile-contact-form-button button"
                    ).removeProp("disabled");
                },
            });
        });

        // Contact Form
        $(".lsd-owner-contact-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $alert = $(".lsd-owner-contact-form-alert");

            let id = $form.data("id");
            let data = $form.serialize();

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $(
                "#lsd_owner_contact_form_" +
                id +
                " .lsd-owner-contact-form-button button"
            ).prop("disabled", "disabled");

            // Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_owner_contact_form_" +
                        id +
                        " .lsd-owner-contact-form-button button"
                    ).removeProp("disabled");

                    if (response.success) {
                        $form.hide();
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_owner_contact_form_" +
                        id +
                        " .lsd-owner-contact-form-button button"
                    ).removeProp("disabled");
                },
            });
        });

        // Report Abuse
        $(".lsd-report-abuse-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $alert = $(".lsd-report-abuse-form-alert");

            let id = $form.data("id");
            let data = $form.serialize();

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $(
                "#lsd_report_abuse_form_" + id + " .lsd-report-abuse-form-button button"
            ).prop("disabled", "disabled");

            // Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_report_abuse_form_" +
                        id +
                        " .lsd-report-abuse-form-button button"
                    ).removeProp("disabled");

                    if (response.success) {
                        $form.hide();
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $(
                        "#lsd_report_abuse_form_" +
                        id +
                        " .lsd-report-abuse-form-button button"
                    ).removeProp("disabled");
                },
            });
        });

        // Claim Form
        $(".lsdaddclm-claim-form").on("submit", function (e) {
            e.preventDefault();

            let id = $(this).data("id");

            // Form Data
            let fd = new FormData();

            let fields = $(this).find($(":input")).serializeArray();
            jQuery.each(fields, function (i, field) {
                fd.append(field.name, field.value);
            });

            // Append File
            let $file = $("#lsd_claim_form_file");
            if (typeof $file.prop("files") !== "undefined")
                fd.append("claim-doc", $file.prop("files")[0]);

            let $alert = $(".lsd-claim-form-alert");
            let $form = $("#lsd_claim_form_" + id);
            let $button = $form.find($(".lsd-row-submit button"));

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                type: "POST",
                data: fd,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (response) {
                    // Enable the Button
                    $button.removeProp("disabled");

                    // Loading Style
                    $form.removeClass("lsd-loading");

                    if (response.success) {
                        // Hide Form
                        $form.hide();

                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));

                        // Redirect to Payment Page
                        if (response.data.next) {
                            setTimeout(function () {
                                window.location.replace(response.data.next);
                            }, 2000);
                        }
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Hide Form
                    $form.hide();

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Claim Checkout Form
        $(".lsdaddclm-claim-checkout-form").on("submit", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let data = $(this).serialize();

            // Elements
            let $form = $("#lsd_claim_form_checkout_" + id);
            let $button = $form.find("button");
            let $alert = $(".lsd-claim-checkout-form-alert");

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");

                    if (response.success) {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));

                        // Redirect to Payment Page
                        if (response.data.next) {
                            setTimeout(function () {
                                window.location.replace(response.data.next);
                            }, 1000);
                        }
                    } else {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-error"));
                    }
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Top Up Checkout Form
        $(".lsdaddtup-topup-checkout-form").on("submit", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let data = $(this).serialize();

            // Elements
            let $form = $("#lsd_topup_form_checkout_" + id);
            let $button = $form.find("button");
            let $alert = $(".lsd-topup-checkout-form-alert");

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");

                    if (response.success) {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));

                        // Redirect to Payment Page
                        if (response.data.next) {
                            setTimeout(function () {
                                window.location.replace(response.data.next);
                            }, 1000);
                        }
                    } else {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-error"));
                    }
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Labelize Checkout Form
        $("#lsd_labelize_button").on("click", function (e) {
            e.preventDefault();

            let listing_id = $(this).data("id");
            let nonce = $("#lsdaddlbl_nonce").val();

            let labels = "";
            $(".lsd-labelize-label:checked").each(function () {
                labels += $(this).val() + ",";
            });

            // Elements
            let $wrapper = $(".lsd-labelize-metabox");
            let $button = $(this);
            let $alert = $(".lsd-labelize-checkout-form-alert");

            // Loading Style
            $wrapper.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data:
                    "id=" +
                    listing_id +
                    "&_wpnonce=" +
                    nonce +
                    "&action=lsdaddlbl_labelize_checkout&labels=" +
                    labels,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");

                    if (response.success) {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else {
                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-error"));
                    }
                },
                error: function () {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Subscription Checkout Form
        $(".lsd-package-checkout-form").on("submit", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let data = $(this).serialize();

            // Elements
            let $form = $("#lsd_package_checkout_form_" + id);
            let $button = $form.find("button");
            let $wrapper = $("#lsd_dashboard_packages");

            // Loading Style
            $wrapper.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");

                    if (response.success) {
                        // Redirect to Payment Page
                        if (response.data.next) {
                            setTimeout(function () {
                                window.location.replace(response.data.next);
                            }, 100);
                        }
                    } else {
                    }
                },
                error: function () {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Subscription Switch
        $(".lsdaddsub-switch").on("change", function (e) {
            e.preventDefault();

            // Elements
            let $dropdown = $(this);
            let $wrapper = $dropdown.parent().parent().parent().parent();

            let listing_id = $dropdown.data("listing");
            let original_subscription_id = $dropdown.data("original-subscription");
            let subscription_id = $dropdown.val();
            let nonce = $dropdown.data("nonce");

            // Message
            let $message = $("#lsdaddsub_switch_message_" + listing_id);

            // Loading Style
            $wrapper.addClass("lsd-loading");

            // Remove Message
            $message.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data:
                    "id=" +
                    listing_id +
                    "&subscription=" +
                    subscription_id +
                    "&_wpnonce=" +
                    nonce +
                    "&action=lsdaddsub_switch",
                dataType: "json",
                type: "post",
                success: function (response) {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");

                    // Show Message
                    $message.html(response.message);

                    if (response.success) {
                        // Update Original Subscription
                        $dropdown.data("original-subscription", subscription_id);
                    } else {
                        // Show Message
                        $message.html(listdom_alertify(response.message, "lsd-error"));

                        // Original Subscription
                        if (original_subscription_id)
                            $dropdown.val(original_subscription_id);
                    }

                    // Remove Message
                    setTimeout(function () {
                        $message.html("");
                    }, 5000);
                },
                error: function () {
                    // Loading Style
                    $wrapper.removeClass("lsd-loading");
                },
            });
        });

        // Review Form
        $(".lsdaddrev-review-form").on("submit", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let image_module = $(this).data("images");

            let $alert = $(".lsd-review-form-alert");
            let $form = $("#lsd_review_form_" + id);
            let $button = $form.find($(".lsd-row-submit button"));

            // Form Data
            let fd = new FormData();

            let fields = $(this).find($(":input")).serializeArray();
            jQuery.each(fields, function (i, field) {
                fd.append(field.name, field.value);
            });

            // Append Images
            let $images = $form.find($(".lsd-review-form-images-input"));
            if (image_module && typeof $images.prop("files") !== "undefined") {
                jQuery.each($images.prop("files"), function (i, file) {
                    fd.append("review-images[]", file);
                });
            }

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: fd,
                type: "POST",
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (response) {
                    // Enable the Button
                    $button.removeProp("disabled");

                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Reset Recaptcha
                    typeof grecaptcha !== 'undefined' && grecaptcha.reset();

                    if (response.success) {
                        // Hide Form
                        $form.hide();

                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Hide Form
                    $form.hide();

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Reviews Sort
        $(".lsdaddrev-sort").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $module = $form.parent();
            let data = $form.serialize();

            // Loading Style
            $module.addClass("lsd-loading");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                type: "post",
                dataType: "json",
                success: function (response) {
                    // Loading Style
                    $module.removeClass("lsd-loading");

                    if (response.success) {
                        // Update HTML
                        $module.find($("ul")).html(response.html);

                        // Trigger
                        listdom_onload();
                    }
                },
                error: function () {
                    // Loading Style
                    $module.removeClass("lsd-loading");
                },
            });
        });

        // Auction Form
        $(".lsdaddauc-auction-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let data = $(this).serialize();

            let $alert = $form.parent().find($(".lsd-auction-form-alert"));
            let $button = $form.find($(".lsd-row-submit button"));

            // Loading Style
            $form.addClass("lsd-loading");

            // Disable the Button
            $button.prop("disabled", "disabled");

            // Remove Alert
            $alert.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                type: "post",
                dataType: "json",
                success: function (response) {
                    // Enable the Button
                    $button.removeProp("disabled");

                    // Loading Style
                    $form.removeClass("lsd-loading");

                    if (response.success) {
                        // Hide Form
                        $form.hide();

                        // Alert
                        $alert.html(listdom_alertify(response.message, "lsd-success"));
                    } else $alert.html(listdom_alertify(response.message, "lsd-error"));
                },
                error: function () {
                    // Loading Style
                    $form.removeClass("lsd-loading");

                    // Hide Form
                    $form.hide();

                    // Enable the Button
                    $button.removeProp("disabled");
                },
            });
        });

        // Booking Period
        $(".lsdaddbok-period").on("apply.daterangepicker", function () {
            let $input = $(this);
            let $form = $input.closest("form");

            $form.submit();
        });

        // Booking Inquiry
        $(".lsd-booking-inquiry-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $module = $form.parent();
            let data = $form.serialize();

            // Loading Style
            $module.addClass("lsd-loading");

            $.ajax({
                url: lsd.ajaxurl,
                data: data,
                type: "post",
                dataType: "json",
                success: function (response) {
                    // Loading Style
                    $module.removeClass("lsd-loading");

                    if (response.success) {
                        // Update HTML
                        $module.find($(".lsd-booking-bookables")).html(response.html);

                        setTimeout(function () {
                            let $grecaptcha = $module.find($(".g-recaptcha"));
                            if (typeof grecaptcha !== "undefined" && $grecaptcha.length) {
                                let siteKey = $grecaptcha.data("sitekey");
                                grecaptcha.render($grecaptcha.get(0), {
                                    sitekey: siteKey,
                                });
                            }
                        }, 500);

                        // Trigger
                        listdom_onload();

                        // Booking Form
                        lsdaddbok_trigger_booking_form();
                    }
                },
                error: function () {
                    // Loading Style
                    $module.removeClass("lsd-loading");
                },
            });
        });

        // Booking Form
        lsdaddbok_trigger_booking_form();

        // Booking Manage Actions
        lsdaddbok_trigger_booking_manage_actions();

        // Job Application
        $(".lsdaddjob-application-form").on("submit", function (e) {
            e.preventDefault();

            let $form = $(this);
            let $module = $form.parent();
            let $message = $module.find($(".lsdaddjob-application-message"));
            let $resume = $form.find($("#lsdaddjob_application_resume"));
            let $cover = $form.find($("#lsdaddjob_application_cover"));

            // Form Data
            let fd = new FormData();

            let fields = $form.find($(":input")).serializeArray();
            jQuery.each(fields, function (i, field) {
                fd.append(field.name, field.value);
            });

            // Append Resume
            if (typeof $resume.prop("files")[0] !== "undefined")
                fd.append("resume", $resume.prop("files")[0]);

            // Append Cover Letter
            if (typeof $cover.prop("files")[0] !== "undefined")
                fd.append("cover", $cover.prop("files")[0]);

            // Loading Style
            $module.addClass("lsd-loading");

            // Remove Message
            $message.html("");

            $.ajax({
                url: lsd.ajaxurl,
                data: fd,
                type: "POST",
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (response) {
                    // Loading Style
                    $module.removeClass("lsd-loading");

                    if (response.success) {
                        // Hide Form
                        $form.slideUp();

                        // Show message
                        $message.html(listdom_alertify(response.message, "lsd-success"));
                    } else {
                        // Show message
                        $message.html(listdom_alertify(response.message, "lsd-error"));
                    }
                },
                error: function () {
                    // Loading Style
                    $module.removeClass("lsd-loading");
                },
            });
        });

        // Application Manage Actions
        lsdaddjob_trigger_application_manage_actions();

        // Terms Dropdown
        $(".lsd-terms-dropdown select").on("change", function () {
            let dropdown = $(this);
            if (dropdown.val() > 0) dropdown.parent().submit();
        });

        /**
         * Sortable tab system
         */
        $(".lsd-sortable").sortable();

        // Lightbox
        let $gallery = $(".lsd-image-lightbox a");
        if ($gallery.length) {
            $gallery.simpleLightbox({});
        }

        /**
         * Listdom Rate Field
         */
        $(".lsd-rate .lsd-rate-stars a").on("click", function (e) {
            e.preventDefault();

            let $star = $(this);
            let $wrapper = $star.parent().parent();
            let $input = $wrapper.find($("input.lsd-rate-input"));

            // New Rate
            let rate = $star.data("rating-value");

            // Update Dropdown
            $input.val(rate).trigger("change");

            // Update Stars
            $wrapper.find($(".lsd-rate-stars a")).each(function () {
                let stars = $(this).data("rating-value");
                if (rate >= stars) {
                    $(this).addClass("lsd-rate-selected");
                    $(this).find("i").removeClass("far").addClass("fas");
                } else {
                    $(this).removeClass("lsd-rate-selected");
                    $(this).find("i").removeClass("fas").addClass("far");
                }
            });
        });

        // Open iframe links in new window
        if ($("body").hasClass("lsd-raw-page")) $("a").attr("target", "_parent");

        // LightSlider
        listdom_image_slider();

        const $modal = jQuery('.lsd-gallery-modal');
        if ($modal.length) {
            $modal.appendTo('body');
        }

        // Linear Gallery
        listdom_linear_gallery_modal();

        $('.lsd-new-tax-wrapper').each(function()
        {
            const $wrapper = $(this);
            const tax = $wrapper.data('tax');
            const $modal = jQuery(`#lsd_dashboard_new_term_${tax}`);

            if ($modal.length)
            {
                $modal.off('listdom:modal:opened.listdomNewTax listdom:modal:closed.listdomNewTax');
                $modal.on('listdom:modal:closed.listdomNewTax', function ()
                {
                    jQuery('#lsd_new_term_message_' + tax).html('');
                });
            }

            $('#lsd_show_create_taxonomy_form_' + tax).on('click', function(e)
            {
                e.preventDefault();
                if (!$modal.length) return;

                if (typeof ListdomModal !== 'undefined')
                {
                    jQuery(window).off('click.listdomNewTaxFallback');
                    ListdomModal.open($modal);
                }
                else
                {
                    $modal.css('display', 'flex').hide().fadeIn(100);
                    if (typeof ListdomPageScroll !== 'undefined') ListdomPageScroll.stop();

                    jQuery(window)
                    .off('click.listdomNewTaxFallback')
                    .on('click.listdomNewTaxFallback', function (event)
                    {
                        if (jQuery(event.target).closest('.lsd-modal-content').length === 0 && jQuery(event.target).is('.lsd-modal'))
                        {
                            $modal.fadeOut(100, function ()
                            {
                                if (typeof ListdomPageScroll !== 'undefined') ListdomPageScroll.start();
                            });
                            jQuery('#lsd_new_term_message_' + tax).html('');
                        }
                    });
                }
            });
        });

        /**
         * Listdom Icon Picker
         */
        if (typeof $.fn.fontIconPicker !== 'undefined') $('.lsd-iconpicker').fontIconPicker(
        {
            emptyIcon: false,
            emptyIconValue: '',
        });

        /**
         * Listdom Color Picker
         */
        if (typeof $.fn.wpColorPicker !== 'undefined')
        {
            $('.lsd-colorpicker').wpColorPicker();
        }
    });
})(jQuery);
