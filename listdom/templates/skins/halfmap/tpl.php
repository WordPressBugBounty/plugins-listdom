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
        view: "'.$this->default_view.'",
        columns: "'.$this->columns.'"
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-halfmap-view-wrapper <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m lsd-map-position-<?php echo esc_attr($this->map_position); ?>" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>" data-view="<?php echo esc_attr($this->default_view); ?>">

    <?php if($this->sm_shortcode && $this->sm_position === 'top'): ?>
    <div class="lsd-row">
        <div class="lsd-col-12">
            <?php echo LSD_Kses::form($this->get_search_module()); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="lsd-halfmap-view-map-list-wrapper lsd-row">
        <div class="lsd-halfmap-view-map-section-wrapper lsd-col-6">
            <?php echo lsd_map($this->search(['posts_per_page' => $this->map_limit]), [
                'provider' => $this->map_provider,
                'clustering' => $this->skin_options['clustering'] ?? true,
                'clustering_images' => $this->skin_options['clustering_images'] ?? '',
                'mapstyle' => $this->skin_options['mapstyle'] ?? '',
                'id' => $this->id,
                'onclick' => $this->skin_options['mapobject_onclick'] ?? 'infowindow',
                'mapcontrols' => $this->mapcontrols,
                'canvas_height' => $this->map_height,
                'atts' => $this->atts,
                'mapsearch' => $this->mapsearch,
                'autoGPS' => $this->autoGPS,
                'max_bounds' => $this->maxBounds,
            ]); ?>
        </div>

        <div class="lsd-halfmap-view-list-section-wrapper lsd-col-6">

            <?php if($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

            <?php echo LSD_Kses::form($this->get_switcher_buttons()); ?>

            <div class="lsd-halfmap-view-listings-wrapper lsd-viewstyle-<?php echo esc_attr($this->default_view); ?>">
                <div class="lsd-listing-wrapper">
                    <?php echo LSD_Kses::full($listings_html); ?>
                </div>
            </div>

            <?php echo LSD_Kses::element($this->get_pagination()); ?>

        </div>

    </div>

</div>
