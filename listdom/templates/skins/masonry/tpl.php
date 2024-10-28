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
    jQuery("#lsd_skin'.$this->id.'").listdomMasonrySkin(
    {
        id: "'.$this->id.'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        rtl: '.(is_rtl() ? 'true' : 'false').',
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-masonry-view-wrapper <?php if($this->sm_shortcode) echo 'lsd-search-position-' . esc_attr($this->sm_position); ?> <?php echo esc_attr($this->html_class); ?> <?php echo $this->list_view ? 'lsd-masonry-view-list-view' : ''; ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>">

    <?php if($this->sm_shortcode && in_array($this->sm_position, ['top', 'left', 'right'])) echo LSD_Kses::form($this->get_search_module()); ?>

    <div class="lsd-list-wrapper">
        <?php if(trim($this->filter_by)) echo LSD_Kses::element($this->filters()); ?>

        <?php if($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-masonry-view-listings-wrapper">
            <div class="lsd-listing-wrapper lsd-columns-<?php echo esc_attr($this->columns); ?>">
                <?php echo LSD_Kses::full($listings_html); ?>
            </div>
        </div>
    </div>

    <?php if($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
</div>
