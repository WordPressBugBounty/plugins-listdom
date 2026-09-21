(function ($) {
    window.lsdDashboardInitializeElementor = function ($root) {
        let $dashboards = $root && $root.length
            ? $root.filter('.lsd-dashboard').add($root.find('.lsd-dashboard'))
            : $('.lsd-dashboard');

        if (typeof window.lsdDashboardApplySidebar === 'function') {
            window.lsdDashboardApplySidebar($dashboards);
        }

        $dashboards.each(function () {
            const dashboard = $(this);
            const dashboardOptions = {
                ajax_url: typeof lsd !== 'undefined' ? lsd.ajaxurl : 0,
                nonce: dashboard.data('dashboard-nonce') || ''
            };

            if (typeof $.fn.listdomDashboard === 'function') {
                dashboard.listdomDashboard(dashboardOptions);
            }

            if (dashboard.find('form.lsd-dashboard-form').length && typeof $.fn.listdomDashboardForm === 'function') {
                dashboard.listdomDashboardForm(dashboardOptions);
            }

            if (typeof window.lsdDashboardInitEditors === 'function') {
                window.lsdDashboardInitEditors(dashboard);
            }

            if (typeof window.lsdDashboardInitACF === 'function') {
                window.lsdDashboardInitACF(dashboard);
            }

            if (typeof window.lsdDashboardInitTaxonomyForms === 'function') {
                window.lsdDashboardInitTaxonomyForms(dashboard);
            }

            if (typeof window.lsdDashboardInitAdditionalCategories === 'function') {
                window.lsdDashboardInitAdditionalCategories(dashboard);
            }

            if (typeof window.lsdDashboardInitCoreControls === 'function') {
                window.lsdDashboardInitCoreControls(dashboard);
            }

            if (typeof window.lsdDashboardInitRecaptcha === 'function') {
                window.lsdDashboardInitRecaptcha(dashboard);
            }

            if (dashboard.find('form.lsd-dashboard-profile-form').length && typeof $.fn.listdomDashboardProfile === 'function') {
                dashboard.find('form.lsd-dashboard-profile-form').listdomDashboardProfile({
                    ajax_url: dashboardOptions.ajax_url,
                    nonce: dashboard.data('profile-nonce') || ''
                });
            }
        });
    };

    // Elementor's init Hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($element) {
            if (elementorFrontend.isEditMode()) {
                window.lsdDashboardInitializeElementor($element);

                typeof listdom_image_slider === 'function' && listdom_image_slider();
                typeof listdom_linear_gallery_modal === 'function' && listdom_linear_gallery_modal();

                const triggerListdomMasonry = (attempt = 0) => {
                    const hasMasonryDependencies = typeof $.fn.listdomMasonrySkin === 'function';
                    const hasIsotope = typeof $.fn.isotope === 'function' || typeof window.Isotope !== 'undefined';

                    if ((!hasMasonryDependencies || !hasIsotope) && attempt < 20) {
                        setTimeout(() => {
                            triggerListdomMasonry(attempt + 1);
                        }, 100);
                        return;
                    }

                    $('.lsd-masonry-view-wrapper').each(function () {
                        const el = $(this);
                        const rawId = el.attr('id');
                        const numericId = rawId?.replace('lsd_skin', '').match(/\d+/)?.[0];

                        if (!numericId) return;

                        setTimeout(() => {
                            el.listdomMasonrySkin({
                                id: numericId,
                                ajax_url: window.lsd?.ajax_url || '',
                                atts: el.data('atts'),
                                rtl: $('body').hasClass('rtl'),
                                duration: el.data('duration') || 400,
                            });
                        }, 200);
                    });
                };

                // Masonry Init
                triggerListdomMasonry(0);

                if (typeof listdom_onload === 'function') listdom_onload($element);

                // Trigger Listdom Preview
                $(window).trigger("listdom/preview-content");
            }
        });
    });
})(jQuery);
