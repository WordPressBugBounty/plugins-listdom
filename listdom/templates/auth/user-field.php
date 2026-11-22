<?php
defined('ABSPATH') || die();

$type = $field['type'];
$required = !empty($field['required']);
?>
<div class="form-group lsd-portal-user-field">
    <?php if (!$editable): ?>
        <strong><?php echo esc_html($field['label']); ?></strong>
        <?php if ($type === 'url' && $value !== ''): ?>
            <p><a href="<?php echo esc_url($value, ['http', 'https']); ?>"><?php echo esc_html($value); ?></a></p>
        <?php else: ?>
            <p><?php echo nl2br(esc_html(is_array($value) ? implode(', ', $value) : $value)); ?></p>
        <?php endif; ?>
    <?php elseif ($type === 'radio'): ?>
        <fieldset>
            <legend><?php echo esc_html($field['label']); ?></legend>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="">
            <?php foreach ($field['choices'] as $choice): ?>
                <label><input type="radio" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($choice); ?>" <?php checked($choice, $value); ?> <?php echo $required ? 'required' : ''; ?>> <?php echo esc_html($choice); ?></label>
            <?php endforeach; ?>
            <?php if (!$required): ?>
                <label><input type="radio" name="<?php echo esc_attr($name); ?>" value="" <?php checked('', $value); ?>> <?php esc_html_e('None', 'listdom'); ?></label>
            <?php endif; ?>
        </fieldset>
    <?php else: ?>
        <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($field['label']); ?></label>
        <?php if ($type === 'checkbox'): ?>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="0">
            <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="1" <?php checked('1', $value); ?> <?php echo $required ? 'required' : ''; ?>>
        <?php elseif ($type === 'textarea'): ?>
            <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea($value); ?></textarea>
        <?php elseif ($type === 'select' || $type === 'multi'): ?>
            <?php if ($type === 'multi'): ?>
                <input type="hidden" name="<?php echo esc_attr($name); ?>[]" value="">
            <?php endif; ?>
            <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?><?php echo $type === 'multi' ? '[]' : ''; ?>" <?php echo $type === 'multi' ? 'multiple' : ''; ?> <?php echo $required ? 'required' : ''; ?>>
                <?php if ($type === 'select'): ?><option value=""><?php esc_html_e('Choose an option', 'listdom'); ?></option><?php endif; ?>
                <?php foreach ($field['choices'] as $choice): ?>
                    <option value="<?php echo esc_attr($choice); ?>" <?php selected(in_array($choice, (array) $value, true)); ?>><?php echo esc_html($choice); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($value); ?>" <?php echo $type === 'number' ? 'step="any"' : ''; ?> <?php echo $required ? 'required' : ''; ?>>
        <?php endif; ?>
    <?php endif; ?>
</div>
