<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Cover $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
    <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
</div>
<?php endforeach;
