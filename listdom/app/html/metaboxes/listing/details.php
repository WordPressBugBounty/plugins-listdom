<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $dashboard */
/** @var WP_Post $post */

$price_currency = get_post_meta($post->ID, 'lsd_currency', true);
if(trim($price_currency) == '') $price_currency = LSD_Options::currency();

$price = get_post_meta($post->ID, 'lsd_price', true);
$price_max = get_post_meta($post->ID, 'lsd_price_max', true);
$price_after = get_post_meta($post->ID, 'lsd_price_after', true);

$price_class = get_post_meta($post->ID, 'lsd_price_class', true);
if(!trim($price_class)) $price_class = 2;

$ava = get_post_meta($post->ID, 'lsd_ava', true);

$email = get_post_meta($post->ID, 'lsd_email', true);
$phone = get_post_meta($post->ID, 'lsd_phone', true);
$website = get_post_meta($post->ID, 'lsd_website', true);
$contact_address = get_post_meta($post->ID, 'lsd_contact_address', true);
$link = get_post_meta($post->ID, 'lsd_link', true);
$remark = get_post_meta($post->ID, 'lsd_remark', true);

$gallery = get_post_meta($post->ID, 'lsd_gallery', true);
if(!is_array($gallery)) $gallery = [];

$embeds = get_post_meta($post->ID, 'lsd_embeds', true);
if(!is_array($embeds)) $embeds = [];

// Approval
$guest_email = get_post_meta($post->ID, 'lsd_guest_email', true);
$guest_fullname = get_post_meta($post->ID, 'lsd_guest_fullname', true);
$guest_message = get_post_meta($post->ID, 'lsd_guest_message', true);

// If Called by Dashboard
$dashboard = LSD_Payload::get('dashboard');

// Gallery Method
$gallery_method = $dashboard ? ($this->settings['submission_gallery_method'] ?? 'wp') : 'wp';
if(!is_user_logged_in()) $gallery_method = 'uploader';

$gallery_max_size = $dashboard ? ($this->settings['submission_max_image_upload_size'] ?? '') : '';
?>
<div class="lsd-metabox">

    <?php if(current_user_can('edit_others_posts') && isset($post->post_status) && $post->post_status === 'pending' && trim($guest_email)): ?>
    <div class="lsd-approval lsd-mt-4 lsd-mb-4">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9"><h3 class="lsd-mt-0"><?php esc_html_e('Submitter Request', 'listdom'); ?></h3></div>
        </div>
        <div class="lsd-alert lsd-info">

            <?php if($guest_fullname): ?>
            <div class="lsd-form-row">
                <div class="lsd-col-3 lsd-text-right"><?php esc_html_e('Name', 'listdom'); ?></div>
                <div class="lsd-col-9"><?php echo esc_html($guest_fullname); ?></div>
            </div>
            <?php endif; ?>

            <div class="lsd-form-row">
                <div class="lsd-col-3 lsd-text-right"><?php esc_html_e('Email', 'listdom'); ?></div>
                <div class="lsd-col-9"><?php echo esc_html($guest_email); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-3 lsd-text-right"><?php esc_html_e('Message', 'listdom'); ?></div>
                <div class="lsd-col-9"><?php echo nl2br($guest_message); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!$dashboard || $dashboard->is_enabled('price')): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-mb-0 lsd-form-group-price lsd-listing-module-price">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9"><h3 class="lsd-mt-0"><?php esc_html_e('Price Options', 'listdom'); ?></h3></div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_currency"><?php esc_html_e('Currency', 'listdom'); ?></label>
            </div>
            <div class="lsd-col-9">
                <select name="lsd[currency]" id="lsd_currency">
                    <?php foreach(LSD_Base::get_currencies() as $symbol=>$currency): ?>
                    <option value="<?php echo esc_attr($currency); ?>" <?php echo ($price_currency == $currency ? 'selected="selected"' : ''); ?>><?php echo esc_html($symbol); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_price"><?php esc_html_e('Price', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="text" name="lsd[price]" id="lsd_price" placeholder="<?php esc_attr_e('Price', 'listdom'); ?>" value="<?php echo esc_attr($price); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_price_max"><?php esc_html_e('Price (Max)', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_max'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="text" name="lsd[price_max]" id="lsd_price_max" placeholder="<?php esc_attr_e('Price (Max)', 'listdom'); ?>" value="<?php echo esc_attr($price_max); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_price_after"><?php esc_html_e('Price Description', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_after'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="text" name="lsd[price_after]" id="lsd_price_after" placeholder="<?php esc_attr_e('Per night, Per cup, ...', 'listdom'); ?>" value="<?php echo esc_attr($price_after); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_price_class"><?php esc_html_e('Price Class', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_class'); ?></label>
            </div>
            <div class="lsd-col-9">
                <select name="lsd[price_class]" id="lsd_price_class">
                    <option value="1" <?php echo $price_class == '1' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$ (Cheap)', 'listdom'); ?></option>
                    <option value="2" <?php echo $price_class == '2' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$ (Normal)', 'listdom'); ?></option>
                    <option value="3" <?php echo $price_class == '3' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$$ (High)', 'listdom'); ?></option>
                    <option value="4" <?php echo $price_class == '4' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$$$ (Ultra High)', 'listdom'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!$dashboard || $dashboard->is_enabled('availability')): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-availability">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9"><h3 class="lsd-mt-0"><?php esc_html_e('Work Hours', 'listdom'); ?></h3></div>
        </div>
        <?php foreach(LSD_Main::get_weekdays() as $weekday): $daycode = $weekday['code']; ?>
        <div class="lsd-form-row" id="lsd-ava-<?php echo esc_attr($daycode); ?>">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_ava<?php echo esc_attr($daycode); ?>"><?php esc_html_e($weekday['day'], 'listdom'); ?></label>
            </div>
            <div class="lsd-col-7 lsd-ava-hours">
                <input type="text" name="lsd[ava][<?php echo esc_attr($daycode); ?>][hours]" id="lsd_ava<?php echo esc_attr($daycode); ?>" placeholder="<?php esc_attr_e('9 - 18, 9 AM to 9 PM', 'listdom'); ?>" value="<?php echo isset($ava[$daycode]['hours']) ? esc_attr($ava[$daycode]['hours']) : ''; ?>" />
            </div>
            <div class="lsd-col-2">
                <label>
                    <input type="hidden" name="lsd[ava][<?php echo esc_attr($daycode); ?>][off]" value="0">
                    <input type="checkbox" name="lsd[ava][<?php echo esc_attr($daycode); ?>][off]" value="1" class="lsd-ava-off" data-daycode="<?php echo esc_attr($daycode); ?>" <?php echo isset($ava[$daycode]['off']) && $ava[$daycode]['off'] ? 'checked="checked"' : ''; ?>>
                    <?php esc_html_e('Off', 'listdom'); ?>
                </label>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if(!$dashboard || $dashboard->is_enabled('contact')): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-contact">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9"><h3 class="lsd-mt-0"><?php esc_html_e('Contact Details', 'listdom'); ?></h3></div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_email"><?php esc_html_e('Email', 'listdom'); ?><?php $dashboard && $dashboard->required_html('email'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="email" name="lsd[email]" id="lsd_email" placeholder="<?php esc_attr_e('Email', 'listdom'); ?>" value="<?php echo esc_attr($email); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_phone"><?php esc_html_e('Phone', 'listdom'); ?><?php $dashboard && $dashboard->required_html('phone'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="tel" name="lsd[phone]" id="lsd_phone" placeholder="<?php esc_attr_e('Phone', 'listdom'); ?>" value="<?php echo esc_attr($phone); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_website"><?php esc_html_e('Website', 'listdom'); ?><?php $dashboard && $dashboard->required_html('website'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="url" name="lsd[website]" id="lsd_website" placeholder="<?php esc_attr_e('https://yourwebsite.com', 'listdom'); ?>" value="<?php echo esc_url($website); ?>" />
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_contact_address"><?php esc_html_e('Contact Address', 'listdom'); ?><?php $dashboard && $dashboard->required_html('contact_address'); ?></label>
            </div>
            <div class="lsd-col-9">
                <input type="text" name="lsd[contact_address]" id="lsd_contact_address" placeholder="<?php esc_attr_e('Unit X, City, Zipcode State', 'listdom'); ?>" value="<?php echo esc_attr($contact_address); ?>" />
            </div>
        </div>

        <?php do_action('lsd_social_networks_listing_form', $post, $dashboard); ?>
    </div>

    <?php if(!isset($this->settings['listing_link_status']) || (isset($this->settings['listing_link_status']) && $this->settings['listing_link_status'])): ?>
    <div class="lsd-form-row">
        <div class="lsd-col-3 lsd-text-right">
            <label for="lsd_link"><?php esc_html_e('Listing Custom Link', 'listdom'); ?><?php $dashboard && $dashboard->required_html('link'); ?></label>
        </div>
        <div class="lsd-col-9">
            <input type="url" name="lsd[link]" id="lsd_link" placeholder="<?php esc_attr_e('https://anothersite.com/listing-page/', 'listdom'); ?>" value="<?php echo esc_attr($link); ?>" />
        </div>
		<div class="lsd-col-3"></div>
		<div class="lsd-col-9">
			<p class="description"><?php esc_html_e("If you fill it, then it will be used to override the default listing details page link. You can use it for linking the listing to a custom internal or external address.", 'listdom'); ?></p>
		</div>
    </div>
    <?php else: ?>
    <input type="hidden" name="lsd[link]" value="" />
    <?php endif; ?>

    <?php endif; ?>

    <?php if(!$dashboard || $dashboard->is_enabled('remark')): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-remark">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9"><h3 class="lsd-mt-0"><?php esc_html_e('Remark', 'listdom'); ?></h3></div>
        </div>
        <div class="lsd-form-row lsd-remark-row">
            <div class="lsd-col-3 lsd-text-right">
                <label for="lsd_remark"><?php esc_html_e('Owner Message', 'listdom'); ?><?php $dashboard && $dashboard->required_html('remark'); ?></label>
            </div>
            <div class="lsd-col-9">
                <?php wp_editor($remark, 'lsd_remark', [
                    'textarea_name' => 'lsd[remark]',
                    'textarea_rows' => 6,
                    'quicktags' => false,
                    'media_buttons' => false,
                ]); ?>
                <p class="description"><?php esc_html_e("It will show to the visitors in a different style so you can use it as remark or an ad remark!", 'listdom'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!$dashboard || $dashboard->is_enabled('gallery')): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-gallery-container lsd-listing-module-gallery">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-2"><h3 class="lsd-mt-0"><?php esc_html_e('Gallery', 'listdom'); ?><?php $dashboard && $dashboard->required_html('_gallery'); ?></h3></div>
			<div class="lsd-col-7 lsd-gallery-buttons">
				<button class="button lsd-color-m-bg <?php echo $gallery_method === 'wp' && LSD_Capability::can('upload_files') ? 'lsd-select-gallery-button' : 'lsd-upload-gallery-button'; ?> <?php echo esc_attr($this->get_text_class()); ?>" data-for="#lsd_listing_gallery" data-name="lsd[_gallery][]" type="button"><?php esc_html_e('Add Images', 'listdom'); ?></button>
                <button class="button lsd-remove-gallery-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?> <?php echo count($gallery) ? '' : 'lsd-util-hide'; ?>" data-for="#lsd_listing_gallery" type="button"><?php esc_html_e('Remove All Images', 'listdom'); ?></button>
			</div>
        </div>
        <?php if(($gallery_method === 'uploader')): ?>
        <div class="lsd-form-row">
            <?php if($dashboard && trim($gallery_max_size)): ?>
            <p class="description"><?php echo sprintf(esc_html__('Maximum Allowed Image Size: %s KB', 'listdom'), $gallery_max_size); ?></p>
            <?php endif; ?>
            <div class="lsd-col-12" id="lsd_listing_gallery_uploader_message"></div>
            <input type="file" class="lsd-util-hide" id="lsd_listing_gallery_uploader" multiple data-for="#lsd_listing_gallery" data-name="lsd[_gallery][]">
        </div>
        <?php endif; ?>
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9">
                <ul id="lsd_listing_gallery" class="lsd-listing-gallery lsd-sortable">
                    <?php foreach($gallery as $id): $image = wp_get_attachment_image_src($id, [160, 160]); if(!$image) continue; ?>
                    <li data-id="<?php echo esc_attr($id); ?>">
                        <input type="hidden" name="lsd[_gallery][]" value="<?php echo esc_attr($id); ?>">
                        <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr($id); ?>">
                        <div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($this->isPro() && (!$dashboard || $dashboard->is_enabled('embed'))): ?>
    <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-embed-container lsd-listing-module-embed">
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-2"><h3 class="lsd-mt-0"><?php esc_html_e('Embed', 'listdom'); ?><?php $dashboard && $dashboard->required_html('_embeds'); ?></h3></div>
            <div class="lsd-col-7 lsd-embeds-buttons">
                <button class="button lsd-add-embed-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>" data-template="#lsd_listing_embeds_template" data-for="#lsd_listing_embeds" type="button"><?php esc_html_e('Add', 'listdom'); ?></button>
                <button class="button lsd-remove-embed-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?> <?php echo count($embeds) ? '' : 'lsd-util-hide'; ?>" data-for="#lsd_listing_embeds" type="button"><?php esc_html_e('Remove All Embed Codes', 'listdom'); ?></button>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"></div>
            <div class="lsd-col-9">
                <ul id="lsd_listing_embeds" class="lsd-listing-embeds lsd-sortable">
                    <?php $i = 0; foreach($embeds as $embed): ?>
                    <li data-id="<?php echo esc_attr($i); ?>" id="lsd_listing_embeds_<?php echo esc_attr($i); ?>">
                        <div class="lsd-row">
                            <div class="lsd-embeds-fields lsd-col-11">
                                <input type="text" name="lsd[_embeds][<?php echo esc_attr($i); ?>][name]" value="<?php echo $embed['name'] ?? ''; ?>" title="<?php esc_attr_e('Title', 'listdom'); ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>">
                                <textarea name="lsd[_embeds][<?php echo esc_attr($i); ?>][code]" title="<?php esc_attr_e('Code', 'listdom'); ?>" class="lsd-embed-code" placeholder="<?php esc_attr_e('Code', 'listdom'); ?>"><?php echo (isset($embed['code']) ? esc_textarea(stripslashes($embed['code'])) : ''); ?></textarea>
                                <input type="hidden" name="lsd[_embeds][<?php echo esc_attr($i); ?>][featured]" class="lsd-embed-featured-status" value="<?php echo $embed['featured'] ?? ''; ?>">
                            </div>
                            <div class="lsd-embeds-actions lsd-col-1 lsd-text-center">
                                <?php if(!empty($embed['featured']) && $embed['featured'] === '1'): ?>
                                    <i class="lsd-icon fas fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Remove From Featured', 'listdom'); ?>" data-featured="1"></i>
                                <?php else: ?>
                                    <i class="lsd-icon far fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Add as Featured Video', 'listdom'); ?>" data-featured="0"></i>
                                <?php endif; ?>
                                <i class="lsd-icon fas fa-trash-alt lsd-remove-embed-single-button"></i>
                                <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                            </div>
                        </div>
                    </li>
                    <?php $i++; endforeach; ?>
                </ul>
                <input type="hidden" id="lsd_listing_embeds_index" value="<?php echo esc_attr($i); ?>">
            </div>
        </div>
        <div id="lsd_listing_embeds_template" class="lsd-util-hide">
            <li data-id=":i:" id="lsd_listing_embeds_:i:">
                <div class="lsd-row">
                    <div class="lsd-embeds-fields lsd-col-11">
                        <input type="text" name="lsd[_embeds][:i:][name]" value="" title="<?php esc_attr_e('Title', 'listdom'); ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>">
                        <textarea name="lsd[_embeds][:i:][code]"  class="lsd-embed-code" title="<?php esc_attr_e('Code', 'listdom'); ?>" placeholder="<?php esc_attr_e('Code', 'listdom'); ?>"></textarea>
                        <input type="hidden" name="lsd[_embeds][:i:][featured]" class="lsd-embed-featured-status" value="0">
                    </div>
                    <div class="lsd-embeds-actions lsd-col-1 lsd-text-center">
                        <i class="lsd-icon far fa-star lsd-embed-featured-icon" title="<?php esc_attr_e('Add as Featured Video', 'listdom'); ?>" data-featured="0"></i>
                        <i class="lsd-icon fas fa-trash-alt lsd-remove-embed-single-button"></i>
                        <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i>
                    </div>
                </div>
            </li>
        </div>
    </div>
    <?php endif; ?>

    <?php
        // Third Party Plugins!
        do_action('lsd_listing_details_metabox', $post, $dashboard);
    ?>

</div>
