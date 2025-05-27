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
?>
<div class="lsd-ix-auto-import-jobs">
    <?php foreach($jobs as $key => $j): ?>
        <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-gap-5 lsd-mb-4 lsd-border lsd-border-radius lsd-p-4">
            <div>
                <h4 class="lsd-mb-2 lsd-mt-0"><a href="<?php echo esc_url_raw($j['url']); ?>" target="_blank"><?php echo $j['url']; ?></a></h4>

                <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-gap-5">
                    <ul class="lsd-mb-0">
                        <li><?php echo sprintf(esc_html__('Created at: %s', 'listdom'), '<strong>'.wp_date($datetime_format, $key).'</strong>'); ?></li>
                        <li><?php echo sprintf(esc_html__('Field Mapping: %s', 'listdom'), '<strong>'.$template->get($j['mapping'])['name'].'</strong>'); ?></li>
                        <li><?php echo sprintf(esc_html__('Import Size: %s', 'listdom'), '<strong>'.$j['size'].'</strong>'); ?></li>
                        <li class="lsd-mb-0"><?php echo sprintf(esc_html__('Interval: %s', 'listdom'), '<strong>'.ucfirst($j['interval']).'</strong>'); ?></li>
                    </ul>
                    <?php if(isset($j['imported_at']) && isset($j['last_import_count'])): ?>
                    <div>
                        <ul>
                            <li><?php echo sprintf(esc_html__('Last Import at: %s', 'listdom'), '<strong>'.wp_date($datetime_format, $j['imported_at']).'</strong>'); ?></li>
                            <li class="lsd-mb-0"><?php echo sprintf(esc_html__('Last Import Count: %s', 'listdom'), '<strong>'.$j['last_import_count'].'</strong>'); ?></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <button
                class="button button-primary lsd-csv-job-remove"
                data-key="<?php echo esc_attr($key); ?>"
            ><?php esc_html_e('Remove', 'listdom'); ?></button>
        </div>
    <?php endforeach; ?>
</div>
<script>
// Remove Job
jQuery('.lsd-csv-job-remove').on('click', function()
{
    // Remove Button
    const $button = jQuery(this);

    // Job
    const $job = $button.parent();

    // Mapping Key
    const key = $button.data('key');

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>').attr('disabled', 'disabled');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsdaddcsv_remove_job&_wpnonce=<?php echo wp_create_nonce('lsdaddcsv_remove_job'); ?>&key="+key,
        dataType: 'json',
        success: function(response)
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Remove', 'listdom')); ?>").removeAttr('disabled');

            if(response.success === 1)
            {
                // Hide Elements
                $button.hide();
                $job.remove();

                // Remove Parent
                if (!jQuery('.lsd-ix-auto-import-jobs > div').length)
                {
                    jQuery('.lsd-ix-auto-import-existing-jobs').addClass('lsd-util-hide');
                }
            }
        },
        error: function()
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Remove', 'listdom')); ?>").removeAttr('disabled');
        }
    });
});
</script>
