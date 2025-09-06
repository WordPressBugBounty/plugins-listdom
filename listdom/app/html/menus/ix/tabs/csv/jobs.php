<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_IX_Jobs_CSV $handler */

// Import Jobs
$jobs = $handler->all();

// Template
$template = new LSD_IX_Templates_CSV();

// Date Time Format
$datetime_format = LSD_Base::datetime_format();

// Check if any job has these fields (for table headers)
$has_imported_at = false;
$has_last_import_count = false;

foreach ($jobs as $job)
{
    if (isset($job['imported_at'])) $has_imported_at = true;
    if (isset($job['last_import_count'])) $has_last_import_count = true;
}
?>
<div class="lsd-settings-fields-wrapper lsd-ix-auto-import-existing-jobs-wrapper">
    <div class="lsd-ix-auto-import-existing-jobs">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-m-0 lsd-admin-title"><?php esc_html_e('Existing Jobs', 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Here are the running jobs used for auto importing.', 'listdom'); ?></p>
        </div>
        <div class="lsd-ix-auto-import-jobs lsd-mt-4">
            <table class="lsd-admin-table">
                <thead>
                <tr>
                    <th style="width:30%"><?php esc_html_e('URL', 'listdom'); ?></th>
                    <th><?php esc_html_e('Created at', 'listdom'); ?></th>
                    <th><?php esc_html_e('Mapping Template', 'listdom'); ?></th>
                    <th><?php esc_html_e('Size', 'listdom'); ?></th>
                    <th><?php esc_html_e('Interval', 'listdom'); ?></th>
                    <?php if ($has_imported_at): ?><th><?php esc_html_e('Last import at', 'listdom'); ?></th><?php endif; ?>
                    <?php if ($has_last_import_count): ?><th><?php esc_html_e('Last import count', 'listdom'); ?></th><?php endif; ?>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($jobs as $key => $j): ?>
                    <tr>
                        <td><a href="<?php echo esc_url_raw($j['url']); ?>" target="_blank"><?php echo esc_html($j['url']); ?></a></td>
                        <td><?php echo wp_date($datetime_format, $key); ?></td>
                        <td><?php echo esc_html($template->get($j['mapping'])['name'] ?? ''); ?></td>
                        <td><?php echo esc_html($j['size']); ?></td>
                        <td><?php echo ucfirst($j['interval']); ?></td>
                        <?php if ($has_imported_at): ?>
                            <td><?php echo isset($j['imported_at']) ? wp_date($datetime_format, $j['imported_at']) : ''; ?></td>
                        <?php endif; ?>
                        <?php if ($has_last_import_count): ?>
                            <td><?php echo isset($j['last_import_count']) ? esc_html($j['last_import_count']) : ''; ?></td>
                        <?php endif; ?>
                        <td>
                            <button class="lsd-text-button lsd-csv-job-remove"
                                    data-name="<?php echo esc_attr($template->get($j['mapping'])['name'] ?? ''); ?>"
                                    data-key="<?php echo esc_attr($key ?? ''); ?>">
                                <i class="listdom-icon fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        jQuery('.lsd-csv-job-remove').on('click', function()
        {
            const $button = jQuery(this);
            const $row = $button.closest('tr');
            const key = $button.data('key');
            const name = $button.data('name');

            // Loading Wrapper
            const loading = (new ListdomButtonLoader($button));
            loading.start("");

            listdom_toastify("<?php echo esc_js(__("Are you sure you want to delete this?", 'listdom')); ?>", "lsd-confirm", {
                position: "lsd-center-center",
                confirm: {
                    confirmText: "<?php echo esc_js(__('Confirm', 'listdom')); ?>",
                    cancelText:  "<?php echo esc_js(__('Cancel', 'listdom')); ?>",
                    onConfirm: function(toast) {
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: "action=lsdaddcsv_remove_job&_wpnonce=<?php echo wp_create_nonce('lsdaddcsv_remove_job'); ?>&key=" + key,
                            dataType: 'json',
                            success: function(response) {

                                loading.stop();

                                if (response.success === 1) {
                                    $row.remove();

                                    if (!jQuery('.lsd-ix-auto-import-jobs tbody tr').length) {
                                        jQuery('.lsd-ix-auto-import-existing-jobs').addClass('lsd-util-hide');
                                    }
                                    listdom_toastify(name + ' ' + "<?php echo esc_js(__('Removed', 'listdom')); ?>", 'lsd-success');
                                }
                            },
                            error: function() {
                                loading.stop();
                            }
                        });
                    },
                    onCancel: function(toast) {
                        loading.stop();
                    }
                }
            });
        });
    </script>
</div>
