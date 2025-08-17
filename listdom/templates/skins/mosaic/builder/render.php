<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Mosaic $this */

$ids = $this->listings;
?>
<?php $index = 0; foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); $index++; ?>
<div>
    <div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
        <div class="<?php echo $index % 2 === 0 ? 'lsd-left-side-mobile' : '';  ?> lsd-mosaic-left-side<?php echo (($index - 3) % 4 === 0) || (($index - 4) % 4 === 0) ? ' lsd-left-side-even-rows' : '' ?>">
            <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
                <?php if ($this->display_image): ?>
                    <?php echo LSD_Kses::element($listing->get_image_module($this)); ?>
                <?php endif; ?>
            </div>

            <?php if($this->display_favorite_icon || $this->display_compare_icon): ?>
                <div class="lsd-listing-icons-wrapper">
                    <?php if ($this->display_favorite_icon): ?>
                        <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                    <?php endif; ?>
                    <?php if ($this->display_compare_icon): ?>
                        <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="lsd-listing-body lsd-mosaic-right-side<?php echo (($index - 3) % 4 === 0) || (($index - 4) % 4 === 0) ? ' lsd-right-side-even-rows' : '' ?>">
            <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
        </div>
    </div>
</div>
<?php endforeach;
