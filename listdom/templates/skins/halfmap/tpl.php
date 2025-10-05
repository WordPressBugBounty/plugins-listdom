<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Halfmap $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomHalfMapSkin(
    {
        id: "'.$this->id.'",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        next_page: "'.$this->next_page.'",
        limit: "'.$this->limit.'",
        nonce: "' . esc_js(wp_create_nonce('lsd_search_form')) . '",
        view: "'.$this->default_view.'",
        columns: "'.$this->columns.'"
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-halfmap-view-wrapper <?php echo sanitize_html_class($this->get_bar_class(false)); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m lsd-map-position-<?php echo esc_attr($this->map_position); ?>" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>" data-view="<?php echo esc_attr($this->default_view); ?>">
    <?php echo LSD_Kses::full($this->get_left_bar(false)); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && $this->sm_position === 'top') echo LSD_Kses::form($this->get_search_module('default')); ?>

        <div class="lsd-halfmap-view-map-list-wrapper lsd-row">
            <div class="lsd-halfmap-view-map-section-wrapper lsd-col-6">
                <?php echo $this->get_map(true, $this->map_limit); ?>
            </div>

            <div class="lsd-halfmap-view-list-section-wrapper lsd-list-wrapper lsd-col-6">

                <?php if ($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

                <?php echo LSD_Kses::form($this->get_switcher_buttons(!is_numeric($this->style))); ?>

                <div class="lsd-halfmap-view-listings-wrapper lsd-viewstyle-<?php echo esc_attr($this->default_view); ?>">
                    <div class="lsd-listing-wrapper <?php echo 'lsd-listgrid-columns-'.$this->columns; ?> <?php echo $this->image_fit === 'contain' ? 'lsd-image-object-contain' : ''; ?>">
                        <?php echo LSD_Kses::full($listings_html); ?>
                    </div>
                </div>

                <?php echo LSD_Kses::element($this->get_pagination()); ?>

            </div>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::full($this->get_right_bar(false)); ?>
</div>
