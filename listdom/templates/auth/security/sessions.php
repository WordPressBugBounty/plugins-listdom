<?php
defined('ABSPATH') || die();
?>
<ul>
    <?php foreach ($sessions as $session): ?>
        <li>
            <?php if ($session === $current_session): ?><strong><?php esc_html_e('Current session', 'listdom'); ?></strong><?php endif; ?>
            <?php echo esc_html($session['ua'] ?? __('Unknown device', 'listdom')); ?>
            <?php if (!empty($session['login'])) echo esc_html(' — ' . wp_date(get_option('date_format') . ' ' . get_option('time_format'), $session['login'])); ?>
            <?php if (!empty($session['expiration'])) echo esc_html(sprintf(
                /* translators: %s: session expiry. */
                __(' (expires %s)', 'listdom'), wp_date(get_option('date_format') . ' ' . get_option('time_format'), $session['expiration'])
            )); ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php $open('sessions_others'); $close(__('Sign out other devices', 'listdom')); ?>
<?php $open('sessions_current', false); $close(__('Sign out this device', 'listdom')); ?>
