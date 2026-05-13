<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$price_currency = get_post_meta($post->ID, 'lsd_currency', true);
if (trim($price_currency) === '') $price_currency = LSD_Options::currency();

$price = get_post_meta($post->ID, 'lsd_price', true);
$price_max = get_post_meta($post->ID, 'lsd_price_max', true);
$price_after = get_post_meta($post->ID, 'lsd_price_after', true);

$price_class = get_post_meta($post->ID, 'lsd_price_class', true);
if (!trim($price_class)) $price_class = 2;

// Price Components
$price_components = LSD_Options::price_components();
?>
<div class="lsd-form-group-price lsd-listing-module-price <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-8"><h3 class="<?php echo LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Price Options', 'listdom'); ?></h3></div>
    </div>
    <?php if ($price_components['currency']): ?>
    <div class="lsd-form-row">

        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="lsd_currency"><?php esc_html_e('Currency', 'listdom'); ?></label>
        </div>
        <div class="lsd-col-8">
            <select name="lsd[currency]" id="lsd_currency" class="lsd-admin-input">
                <?php foreach (LSD_Base::get_currencies() as $symbol => $currency): ?>
                <option value="<?php echo esc_attr($currency); ?>" <?php echo $price_currency === $currency ? 'selected="selected"' : ''; ?>><?php echo esc_html($symbol); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php endif; ?>
    <div class="lsd-form-row">

        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="lsd_price"><?php esc_html_e('Price', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price'); ?></label>
        </div>
        <div class="lsd-col-8">
            <input class="lsd-admin-input" type="text" name="lsd[price]" id="lsd_price" placeholder="<?php esc_attr_e('Price', 'listdom'); ?>" value="<?php echo esc_attr($price); ?>">
        </div>
    </div>
    <?php if ($price_components['max']): ?>
    <div class="lsd-form-row">

        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="lsd_price_max"><?php esc_html_e('Price (Max)', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_max'); ?></label>
        </div>
        <div class="lsd-col-8">
            <input class="lsd-admin-input" type="text" name="lsd[price_max]" id="lsd_price_max" placeholder="<?php esc_attr_e('Price (Max)', 'listdom'); ?>" value="<?php echo esc_attr($price_max); ?>">
        </div>
    </div>
    <?php endif; ?>
    <?php if ($price_components['after']): ?>
    <div class="lsd-form-row">

        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="lsd_price_after"><?php esc_html_e('Price Description', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_after'); ?></label>
        </div>
        <div class="lsd-col-8">
            <input class="lsd-admin-input" type="text" name="lsd[price_after]" id="lsd_price_after" placeholder="<?php esc_attr_e('Per night, Per cup, ...', 'listdom'); ?>" value="<?php echo esc_attr($price_after); ?>">
        </div>
    </div>
    <?php endif; ?>
    <?php if ($price_components['class']): ?>
    <div class="lsd-form-row">

        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="lsd_price_class"><?php esc_html_e('Price Class', 'listdom'); ?><?php $dashboard && $dashboard->required_html('price_class'); ?></label>
        </div>
        <div class="lsd-col-8">
            <select name="lsd[price_class]" id="lsd_price_class" class="lsd-admin-input">
                <option value="1" <?php echo $price_class == '1' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$ (Cheap)', 'listdom'); ?></option>
                <option value="2" <?php echo $price_class == '2' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$ (Normal)', 'listdom'); ?></option>
                <option value="3" <?php echo $price_class == '3' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$$ (High)', 'listdom'); ?></option>
                <option value="4" <?php echo $price_class == '4' ? 'selected="selected"' : ''; ?>><?php esc_html_e('$$$$ (Ultra High)', 'listdom'); ?></option>
            </select>
        </div>
    </div>
    <?php endif; ?>
</div>
