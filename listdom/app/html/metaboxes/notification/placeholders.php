<?php
// no direct access
defined('ABSPATH') || die();

$placeholders = LSD_Notifications::placeholders();
?>
<div class="lsd-metabox lsd-notification-placeholders-metabox">
    <div class="lsd-placeholders">
        <ul>
            <?php foreach($placeholders as $category => $items): foreach($items as $pattern => $description): ?>
            <li class="lsd-placeholder-item lsd-placeholder-<?php echo esc_attr($category); ?>">
                <code>#<?php echo esc_html($pattern); ?>#</code>
                <span><?php echo esc_html($description); ?></span>
            </li>
            <?php endforeach; endforeach; ?>
        </ul>
    </div>
</div>