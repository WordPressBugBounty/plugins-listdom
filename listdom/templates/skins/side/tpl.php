<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Side $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Listdom Assets
$assets = new LSD_Assets();

// Add Masonry Skin JS codes to footer
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomSideSkin(
    {
        id: "'.$this->id.'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        next_page: "'.$this->next_page.'",
        limit: "'.$this->limit.'"
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-side-view-wrapper <?php if($this->sm_shortcode) echo 'lsd-search-position-' . esc_attr($this->sm_position); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>">
    <?php if($this->sm_shortcode && in_array($this->sm_position, ['top', 'left', 'right'])) echo LSD_Kses::form($this->get_search_module()); ?>
    <div class="lsd-side-scaffold lsd-side-scaffold-width-<?php echo esc_attr($this->layout_width); ?>">
        <div class="lsd-side-listings lsd-list-wrapper">
            <?php if($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>
            <?php echo LSD_Kses::form($this->get_sortbar()); ?>
            <div class="lsd-side-view-listings-wrapper">
                <div class="lsd-listing-wrapper">
                    <?php echo LSD_Kses::full($listings_html); ?>
                </div>
            </div>
            <?php echo LSD_Kses::element($this->get_pagination()); ?>
        </div>
        <div class="lsd-side-details">
            <div class="lsd-side-details-close"><i class="lsd-icon fa fa-window-close"></i></div>
            <div class="lsd-side-details-iframe"></div>
        </div>
    </div>
    <?php if($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
</div>
