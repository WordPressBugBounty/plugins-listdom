<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Grid $this */

$ids = $this->listings;
foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div
    data-listing-id="<?php echo esc_attr($listing->id()); ?>"
    data-url="<?php echo esc_url(get_the_permalink($listing->id())); ?>"
    class="lsd-listing <?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>"
    <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>
>
    <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
</div>
<?php endforeach;
