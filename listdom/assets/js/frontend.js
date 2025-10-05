// Requests Object for Skins
let listdomPageHistoryCache = window.location.href;

// Listdom PAGE HISTORY PLUGIN
function ListdomPageHistory() {
    this.push = function (url, update = true) {
        listdomPageHistoryCache = this.apply(listdomPageHistoryCache, url);

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

    this.apply = function (source_qs, new_qs) {
        source_qs = decodeURI(source_qs);
        new_qs = decodeURI(new_qs);

        if (new_qs.substring(0, 1) === "?") new_qs = new_qs.substring(1);
        let key_value_vars = new_qs.split("&");

        let url = new URL(source_qs);
        for (let i in key_value_vars) {
            let key_value_var = key_value_vars[i].split("=");
            url.searchParams.set(key_value_var[0], key_value_var[1]);
        }

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

// Requests Object for Skins
let listdomRequests = {};

// Listdom REQUEST PLUGIN
function ListdomRequest(id, settings) {
    this.id = id;
    this.settings = settings;

    this.get = function (request, atts) {
        // Get Cached Request
        let cached = listdomRequests[this.id];
        if (!cached) cached = "?";

        if (request === "") {
            let url = new URL(window.location.href);
            request = url.search ? url.search.substring(1) : "";
        }

        // Render new Request
        let newParameters = atts + "&" + request;
        let rendered = this.apply(cached, newParameters);

        if (this.settings && typeof this.settings.nonce !== "undefined" && this.settings.nonce) {
            let params = new URLSearchParams(rendered);
            params.set("_wpnonce", this.settings.nonce);
            rendered = params.toString();
        }

        // Push to Object
        listdomRequests[this.id] = rendered;

        // Return Rendered Parameters
        return rendered;
    };

    this.apply = function (source_qs, new_qs) {
        source_qs = decodeURI(source_qs);
        new_qs = decodeURI(new_qs);

        let source_qs_sp = new URLSearchParams(source_qs);
        let new_qs_sp = new URLSearchParams(new_qs);

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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });

            $('.lsd-accordion-body').hide();

            $('.lsd-accordion-header').on('click', function() {
                const $header = $(this);
                const $body = $header.next('.lsd-accordion-body');

                $('.lsd-accordion-body').not($body).slideUp();
                $('.lsd-accordion-header').not($header).find('.accordion-arrow').removeClass('rotated');

                // Toggle current body and initialize slider when shown
                $body.stop(true, true).slideToggle(400, function() {
                    if ($body.is(':visible')) {
                        if (typeof listdom_image_slider === 'function') {
                            listdom_image_slider();
                        }
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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

// Listdom Accordion SKIN PLUGIN
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
        const $items_wrapper = $wrapper.find('.lsd-timeline-items');
        const isHorizontal = parseInt(settings.horizontal) === 1;
        const autoplay = parseInt(settings.autoplay) === 1;
        const parsedColumns = parseInt(settings.columns, 10);
        const parsedLimit = parseInt(settings.limit, 10);
        const normalizedLimit = Math.max(1, isNaN(parsedLimit) ? 1 : parsedLimit);
        const columns = Math.max(1, isNaN(parsedColumns) ? normalizedLimit : parsedColumns);
        const itemsPerPage = isHorizontal ? columns : normalizedLimit;
        settings.limit = itemsPerPage;
        const horizontalAlignment = (settings.horizontal_alignment || 'zigzag').toString().toLowerCase();

        // Set the listener
        setListeners();
        initCarousel();
        if (isHorizontal) window.requestAnimationFrame(syncHorizontalTimeline);

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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
                $("#lsd_skin" + settings.id).data("next-page", 1);

                loadMore(false);
            });
        }

        function syncHorizontalTimeline() {
            if (!isHorizontal || !$items_wrapper.length) return;

            const $timelines = $items_wrapper.find('.lsd-timeline');
            if (!$timelines.length) return;

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

            $timelines.each(function (index) {
                const $timeline = $(this);
                $timeline.removeClass('lsd-timeline-top lsd-timeline-bottom lsd-timeline-left lsd-timeline-right');

                if (horizontalAlignment === 'top') $timeline.addClass('lsd-timeline-top');
                else if (horizontalAlignment === 'bottom') $timeline.addClass('lsd-timeline-bottom');
                else if (index % 2 === 0) $timeline.addClass('lsd-timeline-top');
                else $timeline.addClass('lsd-timeline-bottom');
            });
        }

        function initCarousel() {
            if (!isHorizontal || !$items_wrapper.length || $items_wrapper.hasClass('owl-loaded')) return;

            $items_wrapper.owlCarousel({
                items: columns,
                loop: false,
                autoplay: autoplay,
                autoplayHoverPause: true,
                dots: true,
                nav: false,
                margin: 30,
                responsiveClass: true,
                autoHeight: true,
                responsive: {
                    0: { items: 2 },
                    640: { items: Math.min(2, columns) },
                    1024: { items: Math.min(3, columns) },
                    1280: { items: columns },
                },
            });

            window.requestAnimationFrame(syncHorizontalTimeline);
        }

        function renderListings(html, append = true) {
            if (!$items_wrapper.length) {
                if (!append) $list_wrapper.html(html);
                else $list_wrapper.append(html);

                listdom_onload();
                return;
            }

            const $content = $(html);

            if (!append) {
                if (isHorizontal && $items_wrapper.hasClass('owl-loaded')) {
                    $items_wrapper.trigger('replace.owl.carousel', [$content]).trigger('refresh.owl.carousel');
                } else {
                    $items_wrapper.html($content);
                    if (isHorizontal) initCarousel();
                }
            } else {
                if (isHorizontal && $items_wrapper.hasClass('owl-loaded')) {
                    $content.each(function () {
                        $items_wrapper.trigger('add.owl.carousel', [$(this)]);
                    });
                    $items_wrapper.trigger('refresh.owl.carousel');
                } else {
                    $items_wrapper.append($content);
                    if (isHorizontal) initCarousel();
                }
            }

            if (isHorizontal) window.requestAnimationFrame(syncHorizontalTimeline);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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
            $('body').on('lsd-sync', function (e, {id, request}) {
                if (id !== parseInt(settings.id)) return;

                req.get(request);
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

                const map_container_width = $(selector).width();
                const offset = $(selector).offset();
                const offset_top = offset.top;

                $(selector).width(map_container_width);
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
            $(".lsd-skin-" + settings.id + "-carousel").owlCarousel({
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
            $("#lsd_skin" + settings.id + " .lsd-masonry-filters").off("click", "a").on("click", "a", function () {
                let e = $(this);
                let f = e.attr("data-filter");

                masonry.isotope({
                    filter: f,
                });

                if (e.hasClass("lsd-selected")) return false;

                e.parents(".lsd-masonry-filters")
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
            $(window).on('lsd-search-success', () => {
                masonry.isotope('reloadItems').isotope();
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
                geo_request: false,
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
            settings.clustering = false;

        let canvas = this;
        let DOM = canvas[0];
        let updateBounds = true;

        // Map Options
        let mapOptions = {
            scrollWheelZoom: false,
            dragging: !L.Browser.mobile,
        };

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
                spiderfyOnMaxZoom: false,
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

        // Extend Shapes
        extend();

        // Loaded Objects
        let loadedObjects = [];

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
        $('body').on('lsd-sync', function (e, {id, request}) {
            if (id !== parseInt(settings.id)) return;
            mapsearch_request(request);
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
                map.setView([dataObject.latitude, dataObject.longitude], settings.zoom);
            }

            // Apply Clustering
            if (settings.clustering) map.addLayer(clustering);

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

            if (settings.display_infowindow) {
                // InfoWindow
                if (markerData.onclick === "infowindow") {
                    marker.bindPopup(markerData.infowindow, popupOptions);
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

            // Add to Clustering
            if (settings.clustering) clustering.addLayer(marker);
            // Add to Map
            else marker.addTo(map);

            // Extend the bounds to include each marker's position
            bounds.extend([markerData.latitude, markerData.longitude]);

            // Add to Loaded Objects
            loadedObjects.push(marker);
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

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
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

            // Add to Clustering
            if (settings.clustering) clustering.addLayer(shape);
            // Add to Map
            else {
                shape.addTo(map);

                // Extend the bounds to include each shape's position
                bounds.extend(shape.getBounds());
            }

            // Add to Loaded Objects
            loadedObjects.push(shape);
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

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
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

            // Add to Clustering
            if (settings.clustering) clustering.addLayer(shape);
            // Add to Map
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            loadedObjects.push(shape);
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

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
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

            // Add to Clustering
            if (settings.clustering) clustering.addLayer(shape);
            // Add to Map
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            loadedObjects.push(shape);
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

            if (settings.display_infowindow) {
                // InfoWindow
                if (shapeData.onclick === "infowindow") {
                    shape.bindPopup(shapeData.infowindow, popupOptions);
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

            // Add to Clustering
            if (settings.clustering) clustering.addLayer(shape);
            // Add to Map
            else shape.addTo(map);

            // Extend the bounds to include each shape's position
            bounds.extend(shape.getBounds());

            // Add to Loaded Objects
            loadedObjects.push(shape);
        }

        function removeObjects(objects) {
            for (let i in objects) {
                let object = objects[i];

                if (settings.clustering) clustering.removeLayer(object);
                else map.removeLayer(object);
            }
        }

        function reloadObjects(objects) {
            // Remove Existing Objects
            removeObjects(loadedObjects);
            loadedObjects = [];

            // Add New Objects
            loadObjects(objects);
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
                setLatLng: function () {
                },
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
                setLatLng: function () {
                },
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
                setLatLng: function () {
                },
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

        function mapsearch_request(request) {
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
                lsdShouldUpdateAddressBar(settings.id)
            );

            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            // Pagination Wrapper
            const $pagination = $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper");

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_ajax_search&" + req.get(request, settings.args),
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
                    $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                        response.listings
                    );

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

                    // Trigger
                    listdom_onload();

                    // Trigger Connected Shortcodes Sync
                    if (typeof settings.connected_shortcodes !== 'undefined') {
                        for (const i in settings.connected_shortcodes) {
                            const shortcode_id = settings.connected_shortcodes[i];
                            $('body').trigger('lsd-sync', {
                                id: shortcode_id,
                                request: request
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
            load: function (objects) {
                reloadObjects(objects);
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
                geo_request: false,
                max_bounds: {},
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
                scrollwheel: false,
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

        // Init Infowindow
        let infowindow = new InfoBox({
            alignBottom: true,
        });

        // Loaded Objects
        let loadedObjects = [];
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
        if (settings.mapcontrols.draw) drawsearch();

        // Init Google Places
        if (settings.gplaces) gplaces();

        // Init Direction
        if (settings.direction.status) direction();

        // Sync
        $('body').on('lsd-sync', function (e, {id, request}) {
            if (id !== parseInt(settings.id)) return;
            mapsearch_request(request);
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

            // Marker Info-Window
            settings.display_infowindow && google.maps.event.addListener(marker, 'click', function () {
                // Open InfoWindow
                if (this.lsd_onclick === 'infowindow') {
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
                } else if (this.lsd_onclick === 'redirect') {
                    window.location = this.lsd_link;
                } else if (this.lsd_onclick === 'lightbox') {
                    // Listdom Details Plugin
                    new ListdomDetails(
                        this.listing_id,
                        this.raw_link,
                        settings
                    ).lightbox();
                }
            });

            // Extend the bounds to include each marker's position
            bounds.extend(marker.position);

            // Add to Loaded Objects
            loadedObjects.push(marker);
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

            shape.getPosition = function () {
                return shape.getCenter();
            };

            // Shape Info-Window
            settings.display_infowindow && google.maps.event.addListener(shape, "click", function (event) {
                // Open InfoWindow
                if (this.lsd_onclick === "infowindow") {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event.latLng);
                    infowindow.open(map, this);
                } else if (this.lsd_onclick === "redirect") {
                    window.location = this.lsd_link;
                } else if (this.lsd_onclick === "lightbox") {
                    // Listdom Details Plugin
                    new ListdomDetails(
                        this.listing_id,
                        this.raw_link,
                        settings
                    ).lightbox();
                }
            });

            // Extend the bounds to include each shape's position
            bounds.union(shape.getBounds());

            // Add to Loaded Objects
            loadedObjects.push(shape);
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
            settings.display_infowindow && google.maps.event.addListener(shape, "click", function (event) {
                // Open InfoWindow
                if (this.lsd_onclick === "infowindow") {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event.latLng);
                    infowindow.open(map, this);
                } else if (this.lsd_onclick === "redirect") {
                    window.location = this.lsd_link;
                } else if (this.lsd_onclick === "lightbox") {
                    // Listdom Details Plugin
                    new ListdomDetails(
                        this.listing_id,
                        this.raw_link,
                        settings
                    ).lightbox();
                }
            });

            // Extend the bounds to include each shape's position
            shape.getPaths().forEach(function (path) {
                let points = path.getArray();
                for (let p in points) bounds.extend(points[p]);
            });

            // Add to Loaded Objects
            loadedObjects.push(shape);
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

            // Shape Info-Window
            settings.display_infowindow && google.maps.event.addListener(shape, "click", function (event) {
                // Open InfoWindow
                if (this.lsd_onclick === "infowindow") {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event.latLng);
                    infowindow.open(map, this);
                } else if (this.lsd_onclick === "redirect") {
                    window.location = this.lsd_link;
                } else if (this.lsd_onclick === "lightbox") {
                    // Listdom Details Plugin
                    new ListdomDetails(
                        this.listing_id,
                        this.raw_link,
                        settings
                    ).lightbox();
                }
            });

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

            // Add to Loaded Objects
            loadedObjects.push(shape);
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

            shape.getPosition = function () {
                return shape.getBounds().getCenter();
            };

            // Shape Info-Window
            settings.display_infowindow && google.maps.event.addListener(shape, "click", function (event) {
                // Open InfoWindow
                if (this.lsd_onclick === "infowindow") {
                    infowindow.close();
                    infowindow.setContent(this.infowindow);
                    infowindow.setPosition(event.latLng);
                    infowindow.open(map, this);
                } else if (this.lsd_onclick === "redirect") {
                    window.location = this.lsd_link;
                } else if (this.lsd_onclick === "lightbox") {
                    // Listdom Details Plugin
                    new ListdomDetails(
                        this.listing_id,
                        this.raw_link,
                        settings
                    ).lightbox();
                }
            });

            // Extend the bounds to include each shape's position
            bounds.union(shape.getBounds());

            // Add to Loaded Objects
            loadedObjects.push(shape);
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
            let $gps = $("#lsd_direction_gps" + settings.id);
            let $address = $("#lsd_direction_address" + settings.id);
            let $reset = $("#lsd_direction_reset" + settings.id);
            let $latitude = $("#lsd_direction_latitude" + settings.id);
            let $longitude = $("#lsd_direction_longitude" + settings.id);

            $form.on("submit", function (event) {
                event.preventDefault();

                let dest = new google.maps.LatLng(
                    settings.direction.destination.latitude,
                    settings.direction.destination.longitude
                );

                let latitude = $latitude.val();
                let longitude = $longitude.val();

                // Start Point By Address
                let from = $address.val();

                // Start Point By Geo Position
                if (longitude && latitude)
                    from = new google.maps.LatLng(latitude, longitude);

                // Reset The Direction
                if (typeof directionsDisplay !== "undefined") {
                    directionsDisplay.setMap(null);
                    start_marker.setMap(null);
                    end_marker.setMap(null);
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

                // Hide Reset Button
                $reset.addClass("lsd-util-hide");
            });

            $gps.on("click", function () {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        $latitude.val(position.coords.latitude);
                        $longitude.val(position.coords.longitude);
                        $form.submit();
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

        function drawsearch() {
            let drawManager = new google.maps.drawing.DrawingManager({
                drawingControl: true,
                drawingControlOptions: {
                    position: getMapControlPosition(settings.mapcontrols.draw),
                    drawingModes: [
                        google.maps.drawing.OverlayType.POLYGON,
                        google.maps.drawing.OverlayType.CIRCLE,
                        google.maps.drawing.OverlayType.RECTANGLE,
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
            if (type === google.maps.drawing.OverlayType.POLYGON) {
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
            else if (type === google.maps.drawing.OverlayType.CIRCLE) {
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
            else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
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

            if (type === google.maps.drawing.OverlayType.POLYGON) {
                overlay.getPaths().forEach(function (path) {
                    let points = path.getArray();
                    for (let b in points) bounds.extend(points[b]);
                });
            } else if (type === google.maps.drawing.OverlayType.CIRCLE) {
                bounds.union(overlay.getBounds());
            } else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
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

            if (type === google.maps.drawing.OverlayType.POLYGON) {
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
            } else if (type === google.maps.drawing.OverlayType.CIRCLE) {
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
            } else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
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

        function mapsearch_request(request) {
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
                lsdShouldUpdateAddressBar(settings.id)
            );

            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            // Pagination Wrapper
            const $pagination = $("#lsd_skin" + settings.id + " .lsd-numeric-pagination-wrapper");

            $.ajax({
                url: settings.ajax_url,
                data: "action=lsd_ajax_search&" + req.get(request, settings.args),
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
                    $("#lsd_skin" + settings.id + " .lsd-listing-wrapper").html(
                        response.listings
                    );

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

                    // Trigger
                    listdom_onload();

                    // Trigger Connected Shortcodes Sync
                    if (typeof settings.connected_shortcodes !== 'undefined') {
                        for (const i in settings.connected_shortcodes) {
                            const shortcode_id = settings.connected_shortcodes[i];
                            $('body').trigger('lsd-sync', {
                                id: shortcode_id,
                                request: request
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
            load: function (objects) {
                // Freez the Map Search
                mapsearchFreez = true;

                reloadObjects(objects);

                // Release the Map Search
                setTimeout(function () {
                    mapsearchFreez = false;
                }, 1000);
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

        setListeners();

        function setListeners() {
            $container
            .find('select[data-enhanced=1]')
            .each(function () {
                const $select = $(this);
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

            // More Options
            $container
            .find($(".lsd-search-row-more-options"))
            .off("click")
            .on("click", function () {
                const type = $(this).data('type');
                const width = $(this).data('width');
                const target = $(this).data('for');

                // Popup
                if (type === 'popup')
                {
                    const $device = $(this).closest($container).find($(target));
                    let $more = $device.find($(".lsd-search-included-in-more")).first();

                    if ($more.length && $device.find(".lsd-popup-wrapper").length === 0)
                    {
                        $more.wrapAll(`<div class="lsd-popup-wrapper lsd-modal lsd-search-modal"><div class="lsd-modal-content"></div></div>`);

                        $device.find(".lsd-modal-content").css("width", width + "vw");
                        $device.find(".lsd-modal-content").prepend('<a href="#" class="lsd-modal-close">&times;</a>');

                        $more.fadeIn();
                    }

                    $device.find(".lsd-popup-wrapper").first().fadeIn();

                    // Close Icon
                    $(document).on('click', '.lsd-modal-close', function (e)
                    {
                        e.preventDefault();
                        $(this).closest('.lsd-popup-wrapper').fadeOut();
                    });
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
                $form.find("input[type=text], input[type=search], input[type=email], input[type=url], input[type=tel], input[type=number], input[type=hidden]").val("");

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

            // On Submit
            $form.on("submit", function (e) {
                e.preventDefault();
                search();

                $('.lsd-popup-wrapper').fadeOut();
            });
        }

        function search() {
            // Listdom Request Plugin
            let req = new ListdomRequest(settings.shortcode, settings);

            let $skin = $("#lsd_skin" + settings.shortcode);
            let $wrapper = $("#lsd_skin" + settings.shortcode + " .lsd-listing-wrapper");
            let $loadMoreWrapper = $("#lsd_skin" + settings.shortcode + " .lsd-load-more-wrapper");
            const $pagination = $skin.find($(".lsd-numeric-pagination-wrapper"));

            // Add loading Class
            $wrapper.addClass("lsd-loading");

            // Push to History
            new ListdomPageHistory().push(
                "?" + $form.serialize(),
                lsdShouldUpdateAddressBar(settings.id)
            );

            // Trigger Connected Shortcodes Sync
            if (typeof settings.connected_shortcodes !== 'undefined' && settings.connected_shortcodes.length) {
                for (const i in settings.connected_shortcodes) {
                    const shortcode_id = settings.connected_shortcodes[i];
                    $('body').trigger('lsd-sync', {
                        id: shortcode_id,
                        request: $form.serialize() + "&page=1"
                    });
                }
                // Search
            } else {
                $.ajax({
                    url: settings.ajax_url,
                    data:
                        "action=lsd_ajax_search&" +
                        req.get($form.serialize() + "&page=1&view=" + $skin.data('view'), ""),
                    dataType: "json",
                    type: "post",
                    success: function (response) {
                        // Remove Loading Class
                        $wrapper.removeClass("lsd-loading");

                        // Display Items
                        $wrapper.html(response.listings);

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

                        // Map Objects
                        new ListdomMaps(settings.shortcode).load(response.objects);

                        // Search Success Event
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
        let $featured_image_input = $("#lsd_featured_image");
        let $featured_image_upload = $("#lsd_featured_image_file");
        let $gallery_upload = $("#lsd_listing_gallery_uploader");
        let $featured_image_preview = $("#lsd_dashboard_featured_image_preview");
        let $featured_image_remove = $("#lsd_featured_image_remove_button");
        let $multiline_select = $(".lsd-select-multiple");
        let ajax = false;

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

            if (!isValid) {
                $('html, body').animate({
                    scrollTop: $('.lsd-attribute-error:first').offset().top - 100
                }, 300);
                return;
            }

            // Add loading Class to the form
            $form.addClass("lsd-loading");

            // Loading Style
            $dashboard.fadeTo(200, 0.7);

            // Fix WordPress editor issue
            $("#lsd_dashboard_content-html").click();
            $("#lsd_dashboard_content-tmce").click();

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

            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_listing_upload_featured_image");
            fd.append("_wpnonce", settings.nonce);
            fd.append("file", $featured_image_upload.prop("files")[0]);

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
                    $featured_image_input.val(response.data.attachment_id);
                    $featured_image_upload.val("");
                    $featured_image_preview.html('<img src="' + response.data.url + '" alt="">');
                    $featured_image_remove.removeClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    $featured_image_input.val('');
                    $featured_image_upload.val("");

                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 9000);
            });
        }

        function featured_image_remove() {
            $featured_image_input.val("");
            $featured_image_preview.html("");
            $featured_image_remove.addClass("lsd-util-hide");
        }

        function gallery_upload() {
            // Alert
            let $alert = $("#lsd_listing_gallery_uploader_message");

            // Wrapper
            let $wrapper = $(".lsd-listing-gallery-container");

            // Loading Style
            $wrapper.addClass("lsd-loading");

            let fd = new FormData();
            fd.append("action", "lsd_dashboard_listing_upload_gallery");
            fd.append("_wpnonce", settings.nonce);

            // Append Images
            let ins = $gallery_upload.prop("files").length;
            for (let x = 0; x < ins; x++)
                fd.append("files[]", $gallery_upload.prop("files")[x]);

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

                let $target = $($gallery_upload.data("for"));
                let name = $gallery_upload.data("name");
                if (response.data && response.data.length > 0) {
                    response.data.map(function (attachment) {
                        $target.append(
                            '<li data-id="' + attachment.id + '"><input type="hidden" name="' + name + '" value="' + attachment.id + '"><img src="' + attachment.url + '" alt=""><div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div></li>'
                        );
                    });

                    // Trigger Remove Button
                    $(".lsd-remove-gallery-single-button")
                    .off("click")
                    .on("click", function (event) {
                        event.preventDefault();
                        $(this).parent().parent().remove();
                    });
                    // Show Alert
                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                }

                // If there are errors, show them
                if (!response.success || response.data.length === 0) {
                    $alert.html(listdom_alertify(response.message, "lsd-error"));
                }

                // Show / Hide Mass Remove Button
                if ($target.find($("li")).length > 0)
                    $(".lsd-remove-gallery-button").removeClass("lsd-util-hide");
                else $(".lsd-remove-gallery-button").addClass("lsd-util-hide");

                // Empty Alert
                setTimeout(function () {
                    $alert.html("");
                }, 60000);
            });
        }
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

        let $form = $("#lsd_dashboard_profile");
        let $profile_image_input = $("#lsd_profile_image");
        let $profile_image_upload = $("#lsd_profile_image_file");
        let $profile_image_preview = $("#lsd_dashboard_profile_image_preview");
        let $profile_image_remove = $("#lsd_profile_image_remove_button");

        let $hero_image_input = $("#lsd_hero_image");
        let $hero_image_upload = $("#lsd_hero_image_file");
        let $hero_image_preview = $("#lsd_dashboard_hero_image_preview");
        let $hero_image_remove = $("#lsd_hero_image_remove_button");

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
            let $wrapper = $(".lsd-dashboard-profile-image");

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
                    $profile_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    $profile_image_input.val('');
                    $profile_image_upload.val("");

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
            $profile_image_upload.removeClass("lsd-util-hide");
        }

        function hero_image_upload() {
            // Alert
            let $alert = $("#lsd_hero_image_message");

            // Wrapper
            let $wrapper = $(".lsd-dashboard-hero-image");

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
                    $hero_image_upload.addClass("lsd-util-hide");

                    $alert.html(listdom_alertify(response.message, "lsd-success"));
                } else {
                    $hero_image_input.val('');
                    $hero_image_upload.val("");

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
            $hero_image_upload.removeClass("lsd-util-hide");
        }
    };
})(jQuery);

// Listdom LOGIN FORM PLUGIN
(function ($) {
    $.fn.listdomLoginForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,
        }, options);

        let $login = $("#lsd_login_wrapper");
        let $form = $('#lsd-login');
        let ajax = false;

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                login();
            });
        }

        function login() {
            // Message
            const $message = $("#lsd_login_form_message");

            // Hide the Message
            $message.html("");

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
                        $message.html(listdom_alertify(response.message, "lsd-success"));
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    } else {
                        $message.html(listdom_alertify(response.message, "lsd-error"));
                    }
                    $login.fadeTo(200, 1);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $message.html(listdom_alertify(errorThrown, "lsd-error"));
                    $form.removeClass("lsd-loading");
                },
            });
        }
    };
})(jQuery);

// Listdom REGISTER FORM PLUGIN
(function ($) {
    $.fn.listdomRegisterForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,

        }, options);
        let $register = $("#lsd_register_wrapper");
        let $form = $('#lsd-registration-form');
        let ajax = false;

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                register();
            });
        }

        function register() {
            // Message
            const $message = $("#lsd_register_form_message");

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
    };
})(jQuery);

// Listdom FORGOT PASSWORD FORM PLUGIN
(function ($) {
    $.fn.listdomForgotPasswordForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,

        }, options);
        let $register = $(".lsd-forgot-password-wrapper");
        let $form = $('#lsd-forgot-password');
        let ajax = false;

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                forgotPassword();
            });
        }

        function forgotPassword() {
            // Message
            const $message = $("#lsd_forgot_password_form_message");

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
    };
})(jQuery);

// Listdom RESET PASSWORD PLUGIN
(function ($) {
    $.fn.listdomResetPasswordForm = function (options) {
        const settings = $.extend({
            ajax_url: 0,
        }, options);

        let $wrapper = $(".lsd-reset-password-wrapper");
        let $form = $('#lsd-reset-password');
        let ajax = false;

        setListeners();

        function setListeners() {
            $form.off("submit").on("submit", function (event) {
                event.preventDefault();
                resetPassword();
            });
        }

        function resetPassword() {
            const $message = $("#lsd_reset_password_form_message");

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

    // Listdom Onload Event
    jQuery(document).trigger('listdom:onload');
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
        const modal = jQuery(`#lsd-share-modal-${dataId}`);

        if (!modal.parent().is('body')) {
            modal.appendTo('body');
        }

        modal.css('display', 'flex').hide().fadeIn(100);
    });

    jQuery(window).on('click', function (event) {
        if (
            jQuery(event.target).closest('.lsd-modal-content').length === 0 &&
            jQuery(event.target).is('.lsd-modal')
        ) {
            jQuery('.lsd-modal').fadeOut(100);
        }
    });
}

function listdom_trigger_message_modal() {
    jQuery('.lsd-message-modal-button').on('click', function () {
        const modal = jQuery(`#lsd-message-modal`);

        modal.css('display', 'flex').hide().fadeIn(100);
    });

    jQuery(window).on('click', function (event) {
        if (jQuery(event.target).closest('.lsd-modal-content').length === 0 && jQuery(event.target).is('.lsd-modal')) {
            jQuery('.lsd-modal').fadeOut(100);
            jQuery('.lsd-compare-message').html('');
        }
    });
}

function listdom_trigger_compare_modal() {
    jQuery('.lsd-compare-toggle').on('click', function () {
        const dataId = jQuery(this).data('id');
        const title = jQuery(this).data('listing-title');
        const cover = jQuery(this).data('cover');
        const modal = jQuery(`#lsd_compare_${dataId}`).find(`#lsd-compare-modal-${dataId}`);

        modal.css('display', 'flex').hide().fadeIn(100);
        jQuery('.lsd-modal-title').html(title);
        jQuery('.lsd-modal-cover').html(cover);
    });

    jQuery(window).on('click', function (event) {
        if (jQuery(event.target).closest('.lsd-modal-content').length === 0 && jQuery(event.target).is('.lsd-modal')) {
            jQuery('.lsd-modal').fadeOut(100);
            jQuery('.lsd-compare-message').html('');
        }
    });
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
                        ListdomPageScroll.start();
                    }
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
    const $btn = jQuery('.lsdaddcmp-add-listings');
    if (!$btn.length) return;

    const $modal = jQuery('#lsdaddcmp-add-modal');
    const $container = $modal.find('.lsdaddcmp-shortcode');

    const isDisabled = () => $btn.attr('aria-disabled') === 'true' || $btn.hasClass('is-disabled');

    const showLimitNotice = () => {
        const message = $btn.data('limit-message');
        if (message) listdom_toastify(message, 'lsd-warning');
    };

    const handleAjaxError = (error) => listdom_toastify('AJAX request failed: ' + error, 'lsd-error');

    // Sync modal IDs from the compare wrapper
    const syncModalIds = () => {
        const wrapperIds = jQuery('.lsdaddcmp-compare').data('ids');
        if (wrapperIds !== undefined) {
            $modal.attr('data-ids', wrapperIds);
            $modal.data('ids', wrapperIds);
        }
    };

    // Load modal content via AJAX
    const loadModalShortcode = () => {
        syncModalIds();
        const ids = $modal.data('ids')?.toString() || '';
        $container.addClass('lsd-loading');

        jQuery.ajax({
            url: lsd.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: { action: 'lsd_compare_modal_shortcode', ids },
            success: (response) => {
                if (response.success) {
                    $container.html(response.content);
                    listdom_onload();
                } else if (response.message) {
                    listdom_toastify(response.message, 'lsd-error');
                }
            },
            error: (xhr, textStatus, error) => handleAjaxError(error),
            complete: () => $container.removeClass('lsd-loading')
        });
    };

    // Open modal
    const openModal = () => {
        $modal.css('display', 'flex').hide().fadeIn(100);
        ListdomPageScroll.stop();
        loadModalShortcode();
    };

    // Close modal
    const closeModal = () => {
        $modal.fadeOut(100, () => {});
        ListdomPageScroll.start();
    };

    // Add or remove listing from compare
    const toggleCompare = (id) => {
        if (!id) return;

        jQuery.ajax({
            url: lsd.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: { action: 'lsd_compare_nonce', id },
            success: (nonceRes) => {
                if (!nonceRes.success || !nonceRes.nonce) {
                    if (nonceRes.message) listdom_toastify(nonceRes.message, 'lsd-error');
                    return;
                }

                jQuery.ajax({
                    url: lsd.ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: `action=lsd_compare&content=1&id=${id}&_wpnonce=${nonceRes.nonce}`,
                    success: (res) => {
                        if (res.success && res.content) {
                            jQuery('.lsdaddcmp-compare').replaceWith(res.content);
                            listdom_compare_add_listings();
                            listdom_trigger_compare_delete();
                            ListdomPageScroll.start();
                        }

                        if (res.message) {
                            const type = res.success ? 'lsd-success' : 'lsd-error';
                            listdom_toastify(res.message, type);
                        }
                    },
                    error: (xhr, textStatus, error) => handleAjaxError(error)
                });
            },
            error: (xhr, textStatus, error) => handleAjaxError(error)
        });
    };

    // Event bindings
    $btn.off('click').on('click', (e) => {
        e.preventDefault();
        if (isDisabled()) return showLimitNotice();
        openModal();
    });

    $modal.off('click').on('click', (e) => {
        if (!jQuery(e.target).closest('.lsd-modal-content').length && jQuery(e.target).is('.lsd-modal')) {
            closeModal();
        }
    });

    $container.off('click', '.lsd-listing').on('click', '.lsd-listing', (e) => {
        if (jQuery(e.target).closest('.lsd-compare-toggle').length) return;

        e.preventDefault();
        e.stopPropagation();

        const $listing = jQuery(e.currentTarget);
        const $toggle = $listing.find('.lsd-compare-toggle');

        if ($toggle.length) {
            $toggle.trigger('click');
            return;
        }

        $listing.addClass('lsd-loading');

        const id = $listing.find('[data-listing-id]').data('listing-id');
        toggleCompare(id);
    });

    listdom_trigger_compare();
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
        jQuery('body').css('overflow', 'hidden');
    });

    jQuery('.lsd-gallery-modal-close, #lsd-gallery-modal').on('click', function(e) {
        if (e.target !== this) return;

        jQuery('#lsd-gallery-modal').fadeOut();
        jQuery('body').css('overflow', '');
    });
}

function listdom_listing_link_lightbox() {
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
}

(function ($) {
    // Document is Ready!
    $(document).ready(function () {
        // Trigger
        listdom_onload();

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
            $input.val(rate);

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

            $('#lsd_show_create_taxonomy_form_' + tax).on('click', function(e)
            {
                e.preventDefault();
                const modal = jQuery(`#lsd_dashboard_new_term_` + tax);

                modal.css('display', 'flex').hide().fadeIn(100);
                jQuery('body').css('overflow', 'hidden');
            });

            jQuery(window).on('click', function (event) {
                if (jQuery(event.target).closest('.lsd-modal-content').length === 0 && jQuery(event.target).is('.lsd-modal')) {
                    jQuery('.lsd-modal').fadeOut(100);
                    jQuery('#lsd_new_term_message_' + tax).html('');
                    jQuery('body').css('overflow', '');
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
