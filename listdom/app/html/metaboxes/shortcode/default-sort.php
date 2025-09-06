<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Sorts
$sorts = get_post_meta($post->ID, 'lsd_sorts', true);

// Apply default values
if(!is_array($sorts) || !count($sorts)) $sorts = LSD_Options::defaults('sorts');

// Available Options
$options = $this->get_available_sort_options();
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Default Sort", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Control how your listings are shown by default.", 'listdom'); ?> </p>
        </div>
        <div id="lsd_metabox_default_sort" class="lsd-metabox lsd-metabox-sort-options">
            <div class="lsd-metabox-sort-options-default">
                <div class="lsd-metabox-sort-options-default-order lsd-mb-3 lsd-mt-3">
                    <div class="lsd-form-row">
                        <label class="lsd-col-3 lsd-fields-label" for="lsd_sort_options_orderby"><?php esc_html_e('Order By', 'listdom'); ?></label>
                        <select class="lsd-col-8 lsd-admin-input" name="lsd[sorts][default][orderby]" id="lsd_sort_options_orderby">
                            <?php foreach($options as $key=>$option): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php echo ($sorts['default']['orderby'] == $key ? 'selected="selected"' : ''); ?>><?php echo esc_html($option['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="lsd-metabox-sort-options-default-order lsd-mb-3">
                   <div class="lsd-form-row">
                       <label class="lsd-col-3 lsd-fields-label" for="lsd_sort_options_order"><?php esc_html_e('Order', 'listdom'); ?></label>
                       <select class="lsd-col-8 lsd-admin-input" name="lsd[sorts][default][order]" id="lsd_sort_options_order">
                           <option value="DESC" <?php echo ($sorts['default']['order'] == 'DESC' ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Descending', 'listdom'); ?></option>
                           <option value="ASC" <?php echo ($sorts['default']['order'] == 'ASC' ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Ascending', 'listdom'); ?></option>
                       </select>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
