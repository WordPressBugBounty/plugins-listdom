<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$faqs = get_post_meta($post->ID, 'lsd_faqs', true);
if (!is_array($faqs)) $faqs = [];
?>
<div class="lsd-listing-faqs-container lsd-listing-module-faq <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-2"><h3 class="<?php echo \LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('FAQs', 'listdom'); ?><?php $dashboard && $dashboard->required_html('_faqs'); ?></h3></div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-10">
            <ul id="lsd_listing_faqs" class="lsd-listing-faqs lsd-sortable">
                <?php $i = 0; foreach ($faqs as $faq): ?>
                <li class="<?php echo LSD_Base::get_lsd_class('box-gray'); ?>" data-id="<?php echo esc_attr($i); ?>" id="lsd_listing_faqs_<?php echo esc_attr($i); ?>">
                    <div class="lsd-row">
                        <div class="lsd-faqs-fields lsd-col-11 <?php echo LSD_Base::get_lsd_class('subsections'); ?>">
                            <input class="lsd-admin-input" type="text" name="lsd[_faqs][<?php echo esc_attr($i); ?>][question]" value="<?php echo isset($faq['question']) ? esc_attr($faq['question']) : ''; ?>" title="<?php esc_attr_e('Question', 'listdom'); ?>" placeholder="<?php esc_attr_e('Question', 'listdom'); ?>">
                            <textarea name="lsd[_faqs][<?php echo esc_attr($i); ?>][answer]" title="<?php esc_attr_e('Answer', 'listdom'); ?>" class="lsd-admin-input" placeholder="<?php esc_attr_e('Answer', 'listdom'); ?>"><?php echo isset($faq['answer']) ? esc_textarea($faq['answer']) : ''; ?></textarea>
                        </div>
                        <div class="lsd-faqs-actions lsd-col-1 lsd-text-center <?php echo LSD_Base::get_lsd_class('subsections'); ?>">
                            <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                            <i class="lsd-icon fas fa-trash-alt lsd-remove-faq-single-button"></i>
                        </div>
                    </div>
                </li>
                <?php $i++; endforeach; ?>
            </ul>
            <input type="hidden" id="lsd_listing_faqs_index" value="<?php echo esc_attr($i); ?>">
        </div>
    </div>
    <ul id="lsd_listing_faqs_template" class="lsd-util-hide">
        <li class="lsd-admin-box-gray" data-id=":i:" id="lsd_listing_faqs_:i:">
            <div class="lsd-row">
                <div class="lsd-faqs-fields lsd-col-11 lsd-admin-subsections">
                    <input class="lsd-admin-input" type="text" name="lsd[_faqs][:i:][question]" value="" title="<?php esc_attr_e('Question', 'listdom'); ?>" placeholder="<?php esc_attr_e('Question', 'listdom'); ?>">
                    <textarea name="lsd[_faqs][:i:][answer]" class="lsd-admin-input" title="<?php esc_attr_e('Answer', 'listdom'); ?>" placeholder="<?php esc_attr_e('Answer', 'listdom'); ?>"></textarea>
                </div>
                <div class="lsd-faqs-actions lsd-col-1 lsd-text-center lsd-admin-subsections">
                    <i class="lsd-icon fas fa-trash-alt lsd-remove-faq-single-button"></i>
                    <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                </div>
            </div>
        </li>
    </ul>
    <div class="lsd-form-row">
        <div class="lsd-col-8 lsd-faqs-buttons">
            <button class="<?php echo is_admin() ? 'lsd-neutral-button':'lsd-light-button'; ?> lsd-w-auto lsd-add-faq-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>" data-template="#lsd_listing_faqs_template" data-for="#lsd_listing_faqs" type="button"><?php esc_html_e('Add FAQ', 'listdom'); ?></button>
            <button class="lsd-text-button lsd-w-auto lsd-remove-faq-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?> <?php echo count($faqs) ? '' : 'lsd-util-hide'; ?>" data-for="#lsd_listing_faqs" type="button"><?php esc_html_e('Remove All FAQs', 'listdom'); ?></button>
        </div>
    </div>
</div>
