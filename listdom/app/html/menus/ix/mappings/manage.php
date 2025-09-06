<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_IX_Template $template */
/** @var string $action */

$job = new LSD_IX_Jobs_CSV();
$jobs = $job->all();

// Mapping Templates
$templates = $template->all();

// Date Time Format
$datetime_format = LSD_Base::datetime_format();
?>
<div>
    <?php if (!count($templates)): ?>
        <p class="lsd-alert lsd-info">
            <?php esc_html_e('Try to import a file and save it as a new template.', 'listdom'); ?>
        </p>
    <?php else: ?>
        <table class="lsd-admin-table">
            <thead>
            <tr>
                <th><?php esc_html_e('Name', 'listdom'); ?></th>
                <th><?php esc_html_e('Date', 'listdom'); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $key => $t): $is_used = $template::is_template_used($key, $jobs);?>
                <tr>
                    <td><?php echo esc_html($t['name'] ?? 'N/A'); ?></td>
                    <td><?php echo esc_html(wp_date($datetime_format, $key)); ?></td>
                    <td>
                        <button class="lsd-text-button lsd-ix-templates-remove"
                                data-job="<?php echo ($is_used ? 'true' : 'false'); ?>"
                                data-name="<?php echo esc_attr($t['name']); ?>"
                                data-key="<?php echo esc_attr($key); ?>">
                            <i class="listdom-icon fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
    jQuery(document).off('click', '.lsd-ix-templates-remove').on('click', '.lsd-ix-templates-remove', function () {
        const $button = jQuery(this);
        const $row = $button.closest('tr');
        const key = $button.data('key');
        const name = $button.data('name');
        const isUsed = $button.attr('data-job') === 'true';

        const loading = new ListdomButtonLoader($button);
        loading.start('');

        const msgUsed = "<?php echo esc_js( sprintf( __('%s is used in an auto import job. By removing this template, the related job will also be deleted. Are you sure you want to continue?', 'listdom'), '%s' ) ); ?>";
        const msgDelete = "<?php echo esc_js(__('Are you sure you want to delete this?', 'listdom')); ?>";
        const message = isUsed ? msgUsed.replace('%s', name) : msgDelete;

        const confirmText = "<?php echo esc_js(__('Confirm', 'listdom')); ?>";
        const cancelText = "<?php echo esc_js(__('Cancel', 'listdom')); ?>";

        listdom_toastify(message, "lsd-confirm", {
            position: "lsd-center-center",
            confirm: {
                confirmText: confirmText,
                cancelText: cancelText,
                onConfirm: function(toast) {
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: "action=<?php echo esc_js($action); ?>&_wpnonce=<?php echo wp_create_nonce($action); ?>&key=" + key,
                        dataType: 'json',
                        success: function (response) {

                            loading.stop();

                            if (response.success === 1) {
                                $row.fadeOut(200, function () { jQuery(this).remove(); });

                                // Remove from dropdowns
                                jQuery('#lsd_ix_csv_auto_import_mapping option[value="' + key + '"]').remove();
                                jQuery('#lsd_ix_template option[value="' + key + '"]').remove();

                                // Remove related jobs
                                if (response.jobs)
                                {
                                    response.jobs.forEach(function(jobKey)
                                    {
                                        jQuery('.lsd-csv-job-remove[data-key="' + jobKey + '"]').closest('tr').remove();
                                    });

                                    if (!jQuery('.lsd-ix-auto-import-jobs tbody tr').length) jQuery('.lsd-ix-auto-import-existing-jobs').addClass('lsd-util-hide');
                                }

                                listdom_toastify(name + ' ' + "<?php echo esc_js(__('Removed', 'listdom')); ?>", 'lsd-success');
                            }

                        },
                        error: function () {
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
