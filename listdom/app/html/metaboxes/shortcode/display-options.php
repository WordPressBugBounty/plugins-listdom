<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Listdom Skins
$skins = new LSD_Skins();

// Display Options
$options = get_post_meta($post->ID, 'lsd_display', true);
?>
<div class="lsd-metabox lsd-metabox-display-options">
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Skin', 'listdom'),
            'for' => 'lsd_display_options_skin',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin',
                'name' => 'lsd[display][skin]',
                'options' => $skins->get_skins(),
                'value' => $options['skin'] ?? ''
            ]); ?>
            <p class="description"><?php esc_html_e("Select the skin of the shortcode. Listing cards will be displyed in this skin.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-display-options-builder-skin <?php echo is_numeric($list['style'] ?? 'style1') ? '' : 'lsd-util-hide'; ?>">
        <p class="lsd-alert lsd-info lsd-p-4"><?php esc_html_e("Because you're using a custom style, certain display options in the shortcode will be turned off. You can adjust them in the custom layout settings.", 'listdom'); ?></p>
    </div>
    <div id="lsd_skin_display_options_container">
        <?php foreach($skins->get_skins() as $skin=>$label): ?>
        <div class="lsd-skin-display-options" id="lsd_skin_display_options_<?php echo esc_attr($skin); ?>">
            <?php $this->include_html_file('metaboxes/shortcode/display-options/'.$skin.'.php', [
                'parameters' => [
                    'options' => $options
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