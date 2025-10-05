<?php
// no direct access
defined('ABSPATH') || die();

$field = $field ?? [];

$id = isset($field['id']) ? esc_attr($field['id']) : 'lsd_privacy_consent';
$name = isset($field['name']) ? esc_attr($field['name']) : 'lsd_privacy_consent';
$wrapper_class = isset($field['wrapper_class']) ? esc_attr($field['wrapper_class']) : 'lsd-privacy-consent-wrapper';
$input_class = isset($field['class']) ? esc_attr($field['class']) : 'lsd-privacy-consent-checkbox';
$label = $field['label'] ?? '';
$description = $field['description'] ?? '';
$required = !empty($field['required']);
$checked = !empty($field['checked']);
$required_message = isset($field['required_message']) ? sanitize_text_field($field['required_message']) : '';
?>
<div class="<?php echo sanitize_html_class($wrapper_class); ?>">
    <label class="lsd-privacy-consent-label" for="<?php echo esc_attr($id); ?>">
        <input
            type="checkbox"
            class="<?php echo sanitize_html_class($input_class); ?>"
            name="<?php echo esc_attr($name); ?>"
            id="<?php echo esc_attr($id); ?>"
            value="1"
            <?php checked($checked); ?>
            <?php echo $required ? 'required aria-required="true"' : ''; ?>
            <?php if ($required && $required_message !== ''): ?>
                oninvalid="this.setCustomValidity('<?php echo esc_js($required_message); ?>')"
                oninput="this.setCustomValidity('')"
                title="<?php echo esc_attr($required_message); ?>"
            <?php endif; ?>
        />
        <span><?php echo wp_kses_post($label); ?></span>
    </label>
    <?php if ($description): ?>
        <small class="lsd-privacy-consent-description"><?php echo wp_kses_post($description); ?></small>
    <?php endif; ?>
</div>
