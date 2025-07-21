<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $uid */
?>
<div class="lsd-inline-popup-wrapper lsd-no-button-styles lsd-editor-ai-button" data-editor-id="<?php echo esc_attr($uid); ?>">
    <button type="button" class="button lsd-inline-popup-trigger" data-focus="#lsd_ai_content_text_<?php echo esc_attr($uid); ?>" data-for="#lsd-editor-ai-popup-<?php echo esc_attr($uid); ?>" id="lsd_ai_content_open_<?php echo esc_attr($uid); ?>">
        <i class="listdom-icon lsdi-stars"></i>
    </button>
    <div id="lsd-editor-ai-popup-<?php echo esc_attr($uid); ?>" class="lsd-inline-popup-content">
        <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3">
            <div>
                <?php echo LSD_Form::label(['for' => 'lsd_ai_content_profile_' . $uid, 'title' => esc_html__('AI Profile', 'listdom'), 'class' => 'lsd-d-block lsd-mb-2']) . LSD_Form::ai_profiles(['id' => 'lsd_ai_content_profile_' . $uid]); ?>
            </div>
            <div>
                <textarea title="" class="lsd-d-block" id="lsd_ai_content_text_<?php echo esc_attr($uid); ?>" rows="5" maxlength="200" placeholder="<?php esc_attr_e('e.g. write a short description', 'listdom'); ?>"></textarea>
            </div>
            <div>
                <button type="button" class="button button-primary lsd_ai_content_generate" data-editor-id="<?php echo esc_attr($uid); ?>">
                    <i class="listdom-icon lsdi-stars lsd-mr-3"></i><?php esc_html_e('Generate', 'listdom'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function ($)
{
    $('#lsd-editor-ai-popup-<?php echo esc_js($uid); ?> .lsd_ai_content_generate').on('click', function ()
    {
        const ai_profile = $('#lsd_ai_content_profile_<?php echo esc_js($uid); ?>').val();
        const text = $('#lsd_ai_content_text_<?php echo esc_js($uid); ?>').val();
        const $btn = $(this);

        // Disable Button
        $btn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: lsd.ajaxurl || ajaxurl,
            data: {
                action: 'lsd_ai_content',
                ai_profile: ai_profile,
                text: text,
                _wpnonce: '<?php echo wp_create_nonce('lsd_ai_content'); ?>'
            },
            dataType: 'json'
        }).done(function (r)
        {
            // Enable Button
            $btn.prop('disabled', false);

            if (r.success === 1 && typeof r.content === 'string')
            {
                if (typeof tinymce !== 'undefined' && tinymce.get('<?php echo esc_js($uid); ?>')) tinymce.get('<?php echo esc_js($uid); ?>').execCommand('mceInsertContent', false, r.content);
                else $('#<?php echo esc_js($uid); ?>').val($('#<?php echo esc_js($uid); ?>').val() + r.content);

                $('#lsd-editor-ai-popup-<?php echo esc_js($uid); ?>').removeClass('lsd-inline-popup-active');
            }
        }).fail(function ()
        {
            // Enable Button
            $btn.prop('disabled', false);
        });
    });
});
</script>
