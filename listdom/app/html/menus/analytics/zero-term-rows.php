<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $rows */
?>
<?php foreach ($rows as $row): ?>
    <tr>
        <td><?php echo esc_html($row['term']); ?></td>
        <td><?php echo esc_html(number_format_i18n($row['zero_results'])); ?></td>
        <td>
            <?php if (current_user_can('edit_posts')): ?>
                <a class="lsd-analytics-zero-results-add" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING)); ?>"><?php esc_html_e('Add Listing', 'listdom'); ?></a>
            <?php else: ?>
                &mdash;
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
