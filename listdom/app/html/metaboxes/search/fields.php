<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$meta_fields = get_post_meta($post->ID, 'lsd_fields', true);
if(!is_array($meta_fields)) $meta_fields = [];

// Add a default row
if(!count($meta_fields)) $meta_fields[] = ['type' => 'row', 'buttons' => 1];

// Reset Keys
$meta_fields = array_values($meta_fields);

$builder = new LSD_Search_Builder();
$fields = $builder->getAvailableFields($meta_fields);

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery(".lsd-search-fields-metabox").listdomSearchBuilder(
    {
        ajax_url: "'.admin_url('admin-ajax.php', null).'"
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
    <div class="lsd-search-top-buttons">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul>
                    <li><button type="button" class="button" id="lsd_search_add_row"><?php esc_html_e('Add row', 'listdom'); ?></button></li>
                    <li><button type="button" class="button" id="lsd_search_more_options"><?php esc_html_e('More Options', 'listdom'); ?></button></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="lsd-search-container">
        <div class="lsd-row">
           <div class="lsd-col-9 lsd-search-sandbox">
                <?php foreach($meta_fields as $i=>$row): $i = $i + 1; ?>
                <div class="<?php echo ($row['type'] === 'more_options' ? 'lsd-search-more-options' : 'lsd-search-row'); ?>" id="lsd_search_row_<?php echo esc_attr($i); ?>" data-i="<?php echo esc_attr($i); ?>">
                    <?php echo ($row['type'] === 'more_options' ? '<span class="lsd-search-more-options-label">'. esc_html__('Add the “More Options” fields in the row below', 'listdom') .'</span>' : ''); ?>

                    <input type="hidden" name="lsd[fields][<?php echo esc_attr($i); ?>][type]" value="<?php echo (isset($row['type']) and trim($row['type'])) ? $row['type'] : 'row'; ?>">

                    <div class="lsd-search-filters">
                        <?php if(isset($row['filters']) and is_array($row['filters'])) foreach($row['filters'] as $key=>$data) echo LSD_Kses::form($builder->params($key, $data, $i)); ?>
                    </div>

                    <ul class="lsd-search-row-actions">
                        <li class="lsd-search-row-actions-sort lsd-row-handler"><i class="lsd-icon fas fa-arrows-alt"></i></li>
                        <li class="lsd-search-row-actions-delete lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Click twice to delete', 'listdom'); ?>" data-confirm="0" data-i="<?php echo esc_attr($i); ?>"><i class="lsd-icon fas fa-trash-alt"></i></li>
                    </ul>
                    <?php if($row['type'] == 'row') echo LSD_Kses::form($builder->row($row, $i)); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="lsd-col-3 lsd-search-available-fields">
                <h3><?php esc_html_e('Available Fields', 'listdom'); ?></h3>
                <div id="lsd_search_available_fields">
                    <?php foreach($fields as $field): ?>
                    <div class="lsd-search-field" id="lsd_search_available_fields_<?php echo esc_attr($field['key']); ?>" data-key="<?php echo esc_attr($field['key']); ?>">
                        <strong><?php echo esc_html($field['title']); ?></strong>
                        <?php if(isset($field['description'])): ?>
                        <p class="description lsd-mb-0"><?php echo esc_html($field['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>