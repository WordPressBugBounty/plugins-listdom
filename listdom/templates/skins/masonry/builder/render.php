<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Masonry $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="<?php echo esc_attr($this->filters_classes($id)); ?>">
    <div class="lsd-listing<?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
        <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
    </div>
</div>
<?php endforeach;
