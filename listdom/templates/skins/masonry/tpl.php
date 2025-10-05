<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Masonry $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Add masonry assets to the page
$assets = new LSD_Assets();
$assets->isotope();

// Add Masonry Skin JS codes to footer
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin' . $this->id . '").listdomMasonrySkin(
    {
        id: "' . $this->id . '",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        atts: "' . http_build_query(['atts' => $this->atts], '', '&') . '",
        rtl: ' . (is_rtl() ? 'true' : 'false') . ',
        duration: ' . ((int) $this->duration) . ',
        next_page: ' . $this->next_page . ',
        limit: ' . $this->limit . ',
        nonce: "' . esc_js(wp_create_nonce('lsd_search_form')) . '",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-masonry-view-wrapper <?php echo sanitize_html_class($this->get_bar_class()); ?> <?php echo esc_attr($this->html_class); ?> <?php echo $this->list_view ? 'lsd-masonry-view-list-view' : ''; ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>">

    <?php echo LSD_Kses::full($this->get_left_bar()); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && $this->sm_position === 'top') echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-list-wrapper">
            <?php if (trim($this->filter_by)) echo LSD_Kses::element($this->filters()); ?>

            <?php if ($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

            <div class="lsd-masonry-view-listings-wrapper">
                <div class="lsd-listing-wrapper <?php echo $this->image_fit === 'contain' ? 'lsd-image-object-contain' : ''; ?> lsd-columns-<?php echo $this->style === 'style4' ? '2' : esc_attr($this->columns); ?>">
                    <?php echo LSD_Kses::full($listings_html); ?>
                </div>
            </div>

            <?php echo LSD_Kses::element($this->get_pagination()); ?>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::full($this->get_right_bar()); ?>
</div>
