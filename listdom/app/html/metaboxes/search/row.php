<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $row  **/
/** @var int $i  **/
?>
<div class="lsd-search-row-params">
    <?php echo LSD_Form::switcher([
        'id' => 'lsd_fields_'.$i.'_buttons',
        'name' => 'lsd[fields]['.$i.'][buttons]',
        'value' => $row['buttons'] ?? 0,
    ]); ?>
    <label for="lsd_fields_<?php echo esc_attr($i); ?>_buttons"><?php esc_html_e('Search Buttons', 'listdom'); ?></label>
</div>