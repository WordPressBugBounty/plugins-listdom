<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$embeds = get_post_meta($post->ID, 'lsd_embeds', true);
if (!is_array($embeds)) $embeds = [];
?>
<div class="lsd-listing-embed-container lsd-listing-module-embed <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-2"><h3 class="<?php echo \LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Embed', 'listdom'); ?><?php $dashboard && $dashboard->required_html('_embeds'); ?></h3></div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-10">
            <ul id="lsd_listing_embeds" class="lsd-listing-embeds lsd-sortable">
                <?php $i = 0; foreach ($embeds as $embed): ?>
                <li class="<?php echo LSD_Base::get_lsd_class('box-gray'); ?>" data-id="<?php echo esc_attr($i); ?>" id="lsd_listing_embeds_<?php echo esc_attr($i); ?>">
                    <div class="lsd-row">
                        <div class="lsd-embeds-fields lsd-col-11 <?php echo LSD_Base::get_lsd_class('subsections'); ?>">
                            <input class="lsd-admin-input" type="text" name="lsd[_embeds][<?php echo esc_attr($i); ?>][name]" value="<?php echo $embed['name'] ?? ''; ?>" title="<?php esc_attr_e('Title', 'listdom'); ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>">
                            <textarea name="lsd[_embeds][<?php echo esc_attr($i); ?>][code]" title="<?php esc_attr_e('Embed Code or URL', 'listdom'); ?>" class="lsd-embed-code lsd-admin-input" placeholder="<?php esc_attr_e('Embed Code or URL', 'listdom'); ?>"><?php echo isset($embed['code']) ? esc_textarea(stripslashes($embed['code'])) : ''; ?></textarea>
                            <input type="hidden" name="lsd[_embeds][<?php echo esc_attr($i); ?>][featured]" class="lsd-embed-featured-status lsd-admin-input" value="<?php echo $embed['featured'] ?? ''; ?>">
                        </div>
                        <div class="lsd-embeds-actions lsd-col-1 lsd-text-center <?php echo LSD_Base::get_lsd_class('subsections'); ?>">
                            <?php if (!empty($embed['featured']) && $embed['featured'] === '1'): ?>
                                <i class="lsd-icon fas fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Remove From Featured', 'listdom'); ?>" data-featured="1"></i>
                            <?php else: ?>
                                <i class="lsd-icon far fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Add as Featured Video', 'listdom'); ?>" data-featured="0"></i>
                            <?php endif; ?>
                            <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                            <i class="lsd-icon fas fa-trash-alt lsd-remove-embed-single-button"></i>
                        </div>
                    </div>
                </li>
                <?php $i++; endforeach; ?>
            </ul>
            <input type="hidden" id="lsd_listing_embeds_index" value="<?php echo esc_attr($i); ?>">
        </div>
    </div>
    <ul id="lsd_listing_embeds_template" class="lsd-util-hide">
        <li data-id=":i:" id="lsd_listing_embeds_:i:">
            <div class="lsd-row">
                <div class="lsd-embeds-fields lsd-col-11">
                    <input class="lsd-admin-input" type="text" name="lsd[_embeds][:i:][name]" value="" title="<?php esc_attr_e('Title', 'listdom'); ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>">
                    <textarea name="lsd[_embeds][:i:][code]"  class="lsd-embed-code lsd-admin-input" title="<?php esc_attr_e('Embed Code or URL', 'listdom'); ?>" placeholder="<?php esc_attr_e('Embed Code or URL', 'listdom'); ?>"></textarea>
                    <input type="hidden" name="lsd[_embeds][:i:][featured]" class="lsd-embed-featured-status lsd-admin-input" value="0">
                </div>
                <div class="lsd-embeds-actions lsd-col-1 lsd-text-center">
                    <i class="lsd-icon far fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Add as Featured Video', 'listdom'); ?>" data-featured="0"></i>
                    <i class="lsd-icon fas fa-trash-alt lsd-remove-embed-single-button"></i>
                    <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                </div>
            </div>
        </li>
    </ul>
    <div class="lsd-form-row">

        <div class="lsd-col-8 lsd-embeds-buttons">
            <button class="<?php echo is_admin() ? 'lsd-neutral-button':'lsd-light-button'; ?> lsd-w-auto lsd-add-embed-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>" data-template="#lsd_listing_embeds_template" data-for="#lsd_listing_embeds" type="button"><?php esc_html_e('Add Embed Section', 'listdom'); ?></button>
            <button class="lsd-text-button lsd-w-auto lsd-remove-embed-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?> <?php echo count($embeds) ? '' : 'lsd-util-hide'; ?>" data-for="#lsd_listing_embeds" type="button"><?php esc_html_e('Remove All Embed Codes', 'listdom'); ?></button>
        </div>
    </div>
</div>
