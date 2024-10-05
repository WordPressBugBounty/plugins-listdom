<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Carousel $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomCarouselSkin(
    {
        id: "'.$this->id.'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        items: "'.$this->columns.'",
        loop: "'.(count($this->listings) > $this->columns ? '1' : '0').'",
        autoplay: true,
        autoplayHoverPause: true,
        dots: true,
        nav: false,
        responsiveClass: true,
        responsive:
        {
            0: {
                items: 1
            },
            568: {
                items: "'.min(2, $this->columns).'"
            },
            1024: {
                items: "'.min(3, $this->columns).'"
            },
            1440: {
                items: "'.$this->columns.'"
            }
        }
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-carousel-view-wrapper <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>">

    <div class="lsd-carousel-view-listings-wrapper">
        <div class="lsd-listing-wrapper">
            <div class="lsd-skin-<?php echo esc_attr($this->id); ?>-carousel lsd-owl-carousel">
                <?php echo LSD_Kses::full($listings_html); ?>
            </div>
        </div>
    </div>

</div>