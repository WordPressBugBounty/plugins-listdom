<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Cover $this */

// Get HTML of Listings
$listings_html = $this->listings_html();
?>
<div class="lsd-skin-wrapper lsd-cover-view-wrapper <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>">

    <div class="lsd-cover-view-listings-wrapper">
        <div class="lsd-listing-wrapper">
            <?php echo LSD_Kses::full($listings_html); ?>
        </div>
    </div>

</div>