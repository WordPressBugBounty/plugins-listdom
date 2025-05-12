<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$form = get_post_meta($post->ID, 'lsd_form', true);
$style = is_array($form) && isset($form['style']) && $form['style'] ? $form['style'] : 'default';

$builder = new LSD_Search_Builder();

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd-search-fields").listdomSearchBuilder(
    {
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        post_id: '.$post->ID.',
    });
});
</script>');
?>
<div class="lsd-metabox lsd-search-fields-metabox">
    <div class="lsd-search-top-guide lsd-mt-4">
        <div class="lsd-alert lsd-info">
            <ul class="lsd-m-0">
                <li><?php esc_html_e("You can create as many rows as you like and put any number of the fields in each row.", 'listdom'); ?></li>
                <li><?php esc_html_e('Drag the fields from "Available Fields" section into rows.', 'listdom'); ?></li>
                <li><?php echo sprintf(esc_html__('To put some fields in %s section you can put a "More Options" row above them.', 'listdom'), '<strong>'.esc_html__("More Options", 'listdom').'</strong>'); ?></li>
            </ul>
        </div>
    </div>
    <div id="lsd-search-fields" class="<?php echo sanitize_html_class('lsd-search-style-'.$style); ?>" data-active-device="desktop">
        <div class="lsd-search-top-buttons">
            <div class="lsd-row">
                <div class="lsd-col-9 lsd-flex lsd-flex-row">
                    <ul>
                        <li class="lsd-mb-0"><button type="button" class="button" id="lsd_search_add_row"><?php esc_html_e('Add row', 'listdom'); ?></button></li>
                        <li class="lsd-mb-0"><button type="button" class="button" id="lsd_search_more_options"><?php esc_html_e('More Options', 'listdom'); ?></button></li>
                    </ul>
                    <div>
                        <ul class="lsd-search-device-tabs">
                            <?php foreach (['desktop' => 'fas fa-desktop', 'tablet' => 'fas fa-tablet-alt', 'mobile' => 'fas fa-mobile-alt'] as $device => $icon): ?>
                            <li class="<?php echo $device === 'desktop' ? 'lsd-tab-active' : ''; ?> lsd-tooltip" data-device="<?php echo esc_attr($device); ?>" data-lsd-tooltip="<?php echo esc_attr(ucfirst($device)); ?>">
                                <i class="lsd-icon <?php echo esc_attr($icon); ?>" aria-hidden="true"></i>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-search-container">
            <?php
                foreach (['desktop', 'tablet', 'mobile'] as $device) echo LSD_Kses::form($builder->device($device, $post));
            ?>
        </div>
    </div>
</div>
