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
$base_options = $this->get_available_sort_options();

if(!isset($sorts['options'])) $options = $base_options;
else
{
    $options = $sorts['options'];
    foreach($base_options as $k=>$b) if(!isset($options[$k])) $options[$k] = $b;
}
?>
<div class="lsd-metabox lsd-metabox-sort-options">
    <div class="lsd-mt-4">
        <div class="lsd-form-row">
            <div class="lsd-col-8">
                <label for="lsd_sort_options_status"><?php esc_html_e('Display', 'listdom'); ?></label>
            </div>
            <div class="lsd-col-4 lsd-text-right">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_sort_options_status',
                    'toggle' => '#lsd_sort_options_toggle',
                    'name' => 'lsd[sorts][display]',
                    'value' => $sorts['display'] ?? '1'
                ]); ?>
            </div>
        </div>
    </div>
    <div class="<?php echo isset($sorts['display']) && $sorts['display'] ? '' : 'lsd-util-hide'; ?>" id="lsd_sort_options_toggle">
        <div class="lsd-metabox-sort-options-default-style lsd-mb-5">
            <label for="lsd_sort_options_style"><?php esc_html_e('Sort Style', 'listdom'); ?></label>
            <select name="lsd[sorts][sort_style]" id="lsd_sort_options_style">
                <option value=""><?php esc_html_e('Default Style', 'listdom'); ?></option>
                <option value="drop-down" <?php echo isset($sorts['sort_style']) && $sorts['sort_style'] === 'drop-down' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Drop Down', 'listdom'); ?></option>
                <option value="list" <?php echo isset($sorts['sort_style']) && $sorts['sort_style'] === 'list' ? 'selected="selected"' : ''; ?>><?php esc_html_e('List', 'listdom'); ?></option>
            </select>
        </div>
        <div class="lsd-sortable">
            <?php foreach($options as $key=>$option): ?>
            <?php
                $base = $base_options[$key] ?? [];
                if(!count($base)) continue;

                $status = $option['status'] ?? $base['status'];
            ?>
            <div class="lsd-metabox-sort-option lsd-mt-4" id="lsd-sort-options-<?php echo esc_attr($key); ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-1 lsd-cursor-move lsd-text-left">
                        <i class="lsd-icon fas fa-arrows-alt"></i>
                    </div>
                    <div class="lsd-col-9">
                        <?php if(isset($base['name'])): ?>
                        <strong><?php echo esc_html($base['name']); ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="lsd-col-2 lsd-cursor-pointer lsd-text-right lsd-sort-option-toggle" data-key="<?php echo esc_attr($key); ?>">
                        <i class="lsd-icon fa fa-<?php echo $status ? 'check' : 'minus-circle'; ?>"></i>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-12">
                        <input type="hidden" name="lsd[sorts][options][<?php echo esc_attr($key); ?>][status]" value="<?php echo esc_attr($status); ?>" id="lsd-sort-options-<?php echo esc_attr($key); ?>-status">
                        <input type="text" name="lsd[sorts][options][<?php echo esc_attr($key); ?>][name]" placeholder="<?php esc_attr_e('Name', 'listdom'); ?>" title="<?php esc_attr_e('Name', 'listdom'); ?>" value="<?php echo $option['name'] ?? ($base['name'] ?? ''); ?>" <?php echo $status ? '' : 'disabled="disabled"'; ?>>
                        <select name="lsd[sorts][options][<?php echo esc_attr($key); ?>][order]" title="<?php esc_attr_e('Default Order', 'listdom'); ?>" <?php echo ($status ? '' : 'disabled="disabled"'); ?>>
                            <option value="DESC" <?php echo ($option['order'] ?? ($base['order'] ?? '')) == 'DESC' ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Descending', 'listdom'); ?></option>
                            <option value="ASC" <?php echo ($option['order'] ?? ($base['order'] ?? '')) == 'ASC' ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Ascending', 'listdom'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
