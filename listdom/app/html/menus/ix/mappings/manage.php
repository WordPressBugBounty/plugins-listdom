<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_IX_Template $template */
/** @var string $action */

// Mapping Templates
$templates = $template->all();

// Date Time Format
$datetime_format = LSD_Base::datetime_format();
?>
<div class="lsd-max-w-600">
    <?php if(!count($templates)): ?>
    <p class="lsd-alert lsd-info"><?php esc_html_e('There is no mapping template to manage yet. You can save the mappings during the import process and manage them here afterward.', 'listdom'); ?></p>
    <?php endif; ?>

    <?php foreach($templates as $key => $t): ?>
    <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-gap-5 lsd-mb-4">
        <div>
            <h4 class="lsd-mb-2 lsd-mt-0"><?php echo $t['name'] ?? 'N/A'; ?></h4>
            <p class="description"><?php echo sprintf(esc_html__('Created at: %s', 'listdom'), wp_date($datetime_format, $key)); ?></p>
        </div>
        <button
            class="button button-primary lsd-ix-templates-remove"
            data-key="<?php echo esc_attr($key); ?>"
        ><?php esc_html_e('Remove', 'listdom'); ?></button>
    </div>
    <?php endforeach; ?>
</div>
<script>
// Remove Template
jQuery('.lsd-ix-templates-remove').on('click', function()
{
    // Remove Button
    const $button = jQuery(this);

    // Template
    const $template = $button.parent();

    // Mapping Key
    const key = $button.data('key');

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>').attr('disabled', 'disabled');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=<?php echo esc_js($action); ?>&_wpnonce=<?php echo wp_create_nonce($action); ?>&key="+key,
        dataType: 'json',
        success: function(response)
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Remove', 'listdom')); ?>").removeAttr('disabled');

            if(response.success === 1)
            {
                // Hide Elements
                $button.hide();
                $template.hide();
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