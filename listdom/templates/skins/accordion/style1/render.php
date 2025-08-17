<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Accordion $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
    <div class="lsd-accordion-item">
        <div class="lsd-accordion-header" role="button" tabindex="0">
            <div class="lsd-listing-title-section">
                <?php if ($this->display_title): ?>
                    <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                        <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                    </h3>
                <?php endif; ?>
                <?php if ($this->display_labels): ?>
                    <div class="lsd-listing-labels">
                        <?php echo LSD_Kses::element($listing->get_labels()); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->display_price): ?>
                    <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                        <?php echo LSD_Kses::element($listing->get_price()); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="lsd-accordion-toggle-wrapper">
                <?php if ($this->display_price): ?>
                    <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                        <?php echo LSD_Kses::element($listing->get_price()); ?>
                    </div>
                <?php endif; ?>
                <div class="lsd-accordion-toggle-icon" title="<?php echo esc_html__('Toggle details', 'listdom'); ?>">
                    <i class="fas fa-chevron-down accordion-arrow"></i>
                </div>
            </div>
        </div>

        <!-- Accordion Body -->
        <div class="lsd-accordion-body" style="display: none;">
            <div class="lsd-listing<?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>

                <div class="lsd-listing-body-wrapper">
                    <div class="lsd-listing-body">
                        <?php do_action('lsd_skins_after_content', $this, $listing); ?>

                        <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
                            <?php if ($this->display_image): ?>
                                <div class="lsd-image">
                                    <?php echo LSD_Kses::element($listing->get_image_module($this)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="lsd-listing-meta-details">
                            <div class="lsd-listing-meta-top">
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

                            <div class="lsd-divider"></div>

                            <?php if ($this->display_description): ?>
                                <p class="lsd-listing-content" <?php echo lsd_schema()->description(); ?>>
                                    <?php echo LSD_Kses::element($listing->get_excerpt($this->description_length, false, $this->content_type === 'description')); ?>
                                </p>
                            <?php endif; ?>

                            <div class="lsd-listing-meta-middle">
                                <?php if ($this->display_address): ?>
                                    <div class="lsd-listing-address">
                                        <?php echo LSD_Kses::element($listing->get_address()); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($this->display_availability && $availability = $listing->get_availability(true)): ?>
                                    <div class="lsd-listing-availability">
                                        <?php echo LSD_Kses::element($availability); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="lsd-divider"></div>

                            <div class="lsd-listing-meta-bottom">
                                <?php if ($this->display_review_stars): ?>
                                    <?php echo LSD_Kses::element($listing->get_rate_stars('stars', false)); ?>
                                <?php endif; ?>
                                <div class="lsd-listing-bottom-bar">
                                    <?php if ($this->display_share_buttons): ?>
                                        <div class="lsd-listing-share">
                                            <?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($this->display_contact_info): ?>
                        <div class="lsd-listing-contact-info">
                            <?php echo LSD_Kses::element($listing->get_contact_info()); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach;
