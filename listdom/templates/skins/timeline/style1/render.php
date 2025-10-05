<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Timeline $this */

$ids = $this->listings;
?>
<?php $index =0 ; foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); $index++; ?>
    <?php
    $timeline_classes = ['lsd-timeline', 'lsd-timeline-style1'];
    if ($this->horizontal)
    {
        switch ($this->horizontal_alignment)
        {
            case 'top':
                $timeline_classes[] = 'lsd-timeline-top';
                break;
            case 'bottom':
                $timeline_classes[] = 'lsd-timeline-bottom';
                break;
            default:
                $timeline_classes[] = $index % 2 === 0 ? 'lsd-timeline-bottom' : 'lsd-timeline-top';
                break;
        }
    }
    else
    {
        switch ($this->vertical_alignment)
        {
            case 'left':
                $timeline_classes[] = 'lsd-timeline-left';
                break;
            case 'right':
                $timeline_classes[] = 'lsd-timeline-right';
                break;
            default:
                $timeline_classes[] = $index % 2 === 0 ? 'lsd-timeline-right' : 'lsd-timeline-left';
                break;
        }
    }
    ?>
    <div class="lsd-timeline-item">
        <div class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $timeline_classes))); ?>">
            <div class="lsd-listing<?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
                <div class="lsd-listing-timeline">
                    <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
                        <?php if ($this->display_image): ?>
                            <div class="lsd-image">
                                <?php echo LSD_Kses::element($listing->get_image_module($this)); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($this->display_labels): ?>
                            <div class="lsd-listing-labels">
                                <?php echo LSD_Kses::element($listing->get_labels()); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($this->display_review_stars): ?>
                            <?php echo LSD_Kses::element($listing->get_rate_stars('stars', false)); ?>
                        <?php endif; ?>
                    </div>

                    <div class="lsd-listing-body">

                        <div class="lsd-listing-cat-icon-section">
                            <?php if ($this->display_categories): ?>
                                <div class="lsd-listing-categories">
                                    <?php echo LSD_Kses::element($listing->get_categories()); ?>
                                </div>
                            <?php endif; ?>
                            <div class="lsd-listing-icons-wrapper">
                                <?php if ($this->display_favorite_icon): ?>
                                    <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                                <?php endif; ?>
                                <?php if ($this->display_compare_icon): ?>
                                    <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($this->display_title): ?>
                            <h3 class="lsd-listing-title lsd-m-0" <?php echo lsd_schema()->name(); ?>>
                                <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($this->display_contact_info): ?>
                            <div class="lsd-listing-contact-info">
                                <?php echo LSD_Kses::element($listing->get_contact_info()); ?>
                            </div>
                        <?php endif; ?>

                        <?php do_action('lsd_skins_after_content', $this, $listing); ?>

                        <div class="lsd-divider"></div>

                        <div class="lsd-listing-bottom-bar">
                            <?php if ($this->display_price): ?>
                                <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                                    <?php echo LSD_Kses::element($listing->get_price()); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($this->display_share_buttons): ?>
                                <div class="lsd-listing-share">
                                    <?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach;
