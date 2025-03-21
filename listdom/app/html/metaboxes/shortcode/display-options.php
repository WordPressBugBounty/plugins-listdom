<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Listdom Skins
$skins = new LSD_Skins();

// Display Options
$options = get_post_meta($post->ID, 'lsd_display', true);
$selected_skin = $options['skin'] ?? '';

// Price Components
$price_components = LSD_Options::price_components();
?>
<div class="lsd-metabox lsd-metabox-display-options">
    <div class="lsd-form-group lsd-skins-form-group lsd-mt-0">
        <h3 class="lsd-my-0"><?php echo esc_html__("Skin", 'listdom'); ?></h3>
        <p class="description lsd-mb-4 lsd-mt-0"><?php echo esc_html__("Choose the skin for this shortcode.", 'listdom'); ?> </p>

        <div class="lsd-form-row lsd-m-0">
            <div class="lsd-col-12">
                <div class="lsd-skin-style-options">
                    <?php foreach ($skins->get_skins() as $skin_key => $skin_label): ?>
                        <div class="lsd-skin-style-option <?php echo $selected_skin === $skin_key ? 'selected' : ''; ?>" data-skin="<?php echo esc_attr($skin_key); ?>">
                            <img width="120" src="<?php echo esc_url($this->lsd_asset_url('img/skins/'.$skin_key.'.svg')); ?>" alt="<?php echo esc_attr($skin_label); ?>">
                            <h4 class="lsd-mt-2 lsd-mb-3"><?php echo esc_html($skin_label); ?></h4>
                        </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="lsd[display][skin]" id="lsd_display_options_skin" value="<?php echo esc_attr($options['skin'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-display-options-builder-skin <?php echo is_numeric($list['style'] ?? 'style1') ? '' : 'lsd-util-hide'; ?>">
        <p class="lsd-alert lsd-info"><?php esc_html_e("Because you're using a custom style, certain display options in the shortcode will be turned off. You can adjust them in the custom layout settings.", 'listdom'); ?></p>
    </div>
    <div id="lsd_skin_display_options_container">
        <?php foreach($skins->get_skins() as $skin=>$label): ?>
        <div class="lsd-skin-display-options" id="lsd_skin_display_options_<?php echo esc_attr($skin); ?>">
            <?php $this->include_html_file('metaboxes/shortcode/display-options/'.$skin.'.php', [
                'parameters' => [
                    'options' => $options,
                    'price_components' => $price_components,
                ]
            ]); ?>
            <?php
                // Action for Third Party Plugins
                do_action('lsd_shortcode_display_options', $skin, $options);
            ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
