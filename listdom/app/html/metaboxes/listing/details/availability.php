<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

// AI
$ai = new LSD_AI();

$ava = get_post_meta($post->ID, 'lsd_ava', true);
if (!is_array($ava)) $ava = [];
?>
<div class="lsd-listing-module-availability <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">

        <div class="lsd-col-8 lsd-flex lsd-flex-row lsd-flex-content-start lsd-flex-items-center lsd-gap-3">
            <h3 class="<?php echo LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Work Hours', 'listdom'); ?><?php $dashboard && $dashboard->required_html('ava'); ?></h3>
            <?php if ($ai->has_access(LSD_AI::TASK_AVAILABILITY)): ?>
                <div class="lsd-inline-popup-wrapper lsd-no-button-styles">
                    <button type="button" class="button lsd-inline-popup-trigger lsd-text-button" data-focus="#lsd_ava_ai_text" data-for="#lsd-availability-ai-popup" id="lsd_ava_ai_open"><i class="listdom-icon fa-solid fa-magic-wand-sparkles"></i></button>
                    <div id="lsd-availability-ai-popup" class="lsd-inline-popup-content">
                        <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3">
                            <div>
                                <?php echo LSD_Form::label(['for' => 'lsd_ava_ai_profile', 'title' => esc_html__('AI Profile', 'listdom'), 'class' => 'lsd-d-block lsd-mb-2']); ?>
                                <?php echo LSD_Form::ai_profiles(['id' => 'lsd_ava_ai_profile', 'class' => 'lsd-admin-input']); ?>
                            </div>
                            <div>
                                <textarea class="lsd-d-block lsd-admin-input" title="" id="lsd_ava_ai_text" rows="5" maxlength="200" placeholder="<?php esc_attr_e('e.g. Mon-Fri 9 AM - 5 PM, Sat off', 'listdom'); ?>"></textarea>
                            </div>
                            <div>
                                <button type="button" class="<?php echo is_admin() ? 'lsd-primary-button': 'lsd-general-button'; ?>" id="lsd_ava_ai_generate"><i class="listdom-icon lsdi-stars lsd-mr-3"></i><?php esc_html_e('Generate', 'listdom'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php foreach (LSD_Main::get_weekdays() as $weekday): $daycode = $weekday['code']; ?>
    <div class="lsd-form-row" id="lsd-ava-<?php echo esc_attr($daycode); ?>">
        <div class="lsd-col-2 ">
            <label class="lsd-fields-label" for="lsd_ava<?php echo esc_attr($daycode); ?>"><?php echo esc_html($weekday['day']); ?></label>
        </div>
        <?php echo !is_admin() ? '<div class="lsd-form-row lsd-col-12">' : ''; ?>
            <div class="<?php echo is_admin() ? 'lsd-col-7' : 'lsd-col-11'; ?> lsd-ava-hours">
                <input class="lsd-admin-input" type="text" name="lsd[ava][<?php echo esc_attr($daycode); ?>][hours]" id="lsd_ava<?php echo esc_attr($daycode); ?>" placeholder="<?php esc_attr_e('9 - 18, 9 AM to 9 PM', 'listdom'); ?>" value="<?php echo isset($ava[$daycode]['hours']) ? esc_attr($ava[$daycode]['hours']) : ''; ?>">
            </div>
            <div class="lsd-col-1 lsd-ava-off">
                <label class="lsd-fields-label">
                    <input type="hidden" name="lsd[ava][<?php echo esc_attr($daycode); ?>][off]" value="0">
                    <input type="checkbox" name="lsd[ava][<?php echo esc_attr($daycode); ?>][off]" value="1" class="lsd-ava-off" data-daycode="<?php echo esc_attr($daycode); ?>" <?php echo isset($ava[$daycode]['off']) && $ava[$daycode]['off'] ? 'checked="checked"' : ''; ?>>
                    <?php esc_html_e('Closed', 'listdom'); ?>
                </label>
            </div>
        <?php echo !is_admin() ? '</div>' : ''; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php if ($ai->has_access(LSD_AI::TASK_AVAILABILITY)): ?>
<script>
jQuery(document).ready(function($)
{
    const $popup = $('#lsd-availability-ai-modal');
    $('#lsd_ava_ai_generate').on('click', function()
    {
        const ai_profile = $('#lsd_ava_ai_profile').val();
        const text = $('#lsd_ava_ai_text').val();

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'lsd_ai_availability',
                ai_profile: ai_profile,
                text: text,
                _wpnonce: '<?php echo wp_create_nonce('lsd_ai_availability'); ?>'
            },
            dataType: 'json'
        }).done(function(response)
        {
            $btn.prop('disabled', false);
            if (response.success === 1 && typeof response.availability === 'object')
            {
                for (const day in response.availability)
                {
                    if (!response.availability.hasOwnProperty(day)) continue;

                    const item = response.availability[day];

                    $('#lsd_ava'+day).val(item.hours || '');
                    $('#lsd-ava-'+day+' .lsd-ava-off input[type=checkbox]')
                        .prop('checked', item.off)
                        .trigger('change');
                }

                $popup.hide();
            }
        }).fail(function()
        {
            $btn.prop('disabled', false);
        });
    });
});
</script>
<?php endif;
