<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$more_options = get_post_meta($post->ID, 'lsd_more_options', true);
if (!is_array($more_options)) $more_options = [];
?>
<div class="lsd-metabox lsd-search-more-options-metabox" id="lsd_more_options">
    <div class="lsd-row">
        <div class="lsd-col-12 lsd-search-form-more-option-type">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Type', 'listdom'),
                'for' => 'lsd_more_options_type',
            ]); ?>
            <?php echo LSD_Form::select([
                'name' => 'lsd[more_options][type]',
                'id' => 'lsd_more_options_type',
                'class' => 'lsd-more-options-type-toggle',
                'label' => esc_html__('Type', 'listdom'),
                'value' => isset($more_options['type']) && in_array($more_options['type'], ['normal', 'popup']) ? $more_options['type'] : 'normal',
                'options' => [
                    'normal' => esc_html__('Normal', 'listdom'),
                    'popup' => esc_html__('Popup', 'listdom'),
                ],
                'attributes' => [
                    'data-parent' => '#lsd_more_options'
                ],
            ]); ?>
        </div>
    </div>
    <div class="lsd-row lsd-more-options-type-dependency lsd-more-options-type-dependency-popup <?php echo isset($more_options['type']) && $more_options['type'] === 'popup' ? '':'lsd-util-hide';  ?>">
        <div class="lsd-col-12 lsd-search-form-more-option-popup-width">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Popup width', 'listdom'),
                'for' => 'lsd_more_options_popup_width',
            ]); ?>
            <?php echo LSD_Form::number([
                'name' => 'lsd[more_options][width]',
                'id' => 'lsd_more_options_popup_width',
                'value' => isset($more_options['width']) && $more_options['width'] ? $more_options['width'] : 60,
                'attributes' => [
                    'min' => 0,
                    'max' => 100,
                    'increment' => 10
                ],
            ]); ?>
            <p class="description"><?php echo esc_html__('Based on vw (viewport width), meaning the width of the popup will be a percentage of the browser window width.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-12 lsd-search-form-more-option-button-text">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Button Text', 'listdom'),
                'for' => 'lsd_more_options_button_text',
            ]); ?>
            <?php echo LSD_Form::text([
                'name' => 'lsd[more_options][button]',
                'id' => 'lsd_more_options_button_text',
                'value' => isset($more_options['button']) && $more_options['button'] ? $more_options['button'] : esc_html__('More Options', 'listdom'),
            ]); ?>
        </div>
    </div>
</div>
