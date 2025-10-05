(function ($) {
    // Elementor's init Hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
            if (elementorFrontend.isEditMode()) {
                typeof listdom_image_slider === 'function' && listdom_image_slider();
                typeof listdom_linear_gallery_modal === 'function' && listdom_linear_gallery_modal();

                const triggerListdomMasonry = () => {
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
                        }, 100);
                    });
                };

                // Check Isotope first
                if (typeof window.Isotope !== 'undefined') {
                    setTimeout(triggerListdomMasonry, 300);
                }

                // Trigger Listdom Preview
                $(window).trigger("listdom/preview-content");
            }
        });
    });
})(jQuery);
