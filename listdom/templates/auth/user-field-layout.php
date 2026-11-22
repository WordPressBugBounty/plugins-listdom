<?php
defined('ABSPATH') || die();
?>
<?php if ($field['type'] === 'heading'): ?>
    <h3 class="lsd-profile-title"><?php echo esc_html($field['label']); ?></h3>
<?php else: ?>
    <hr class="lsd-user-field-separator">
<?php endif; ?>
