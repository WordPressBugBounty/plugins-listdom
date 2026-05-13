<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$call_to_action = get_post_meta($post->ID, 'lsd_call_to_action', true);
if (!is_array($call_to_action)) $call_to_action = [];

$cta_mode = $call_to_action['mode'] ?? 'inherit';
if (!in_array($cta_mode, ['inherit', 'custom'], true)) $cta_mode = 'inherit';
?>
<div class="lsd-listing-module-cta <?php echo LSD_Base::get_lsd_class('box-white'); ?>" data-lsd-cta-root>
    <div class="lsd-form-row">
        <div class="lsd-col-8"><h3 class="lsd-mt-0"><?php esc_html_e('Call to Action', 'listdom'); ?></h3></div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2">
            <?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Inherit', 'listdom'),
                'for' => 'lsd_cta_mode_toggle',
            ]); ?>
        </div>
        <div class="lsd-col-8">
            <div data-lsd-cta-mode-toggle>
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_cta_mode_toggle',
                    'name' => 'lsd[cta_mode_toggle]',
                    'value' => $cta_mode === 'custom' ? '0' : '1',
                ]); ?>
            </div>
            <input type="hidden" name="lsd[cta_mode]" id="lsd_cta_mode"
                   value="<?php echo esc_attr($cta_mode); ?>"
                   data-lsd-cta-mode="input">
            <p class="<?php echo LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e('Inherit call to action settings from element global settings', 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-cta-custom-fields <?php echo LSD_Base::get_lsd_class('subsections'); ?> <?php echo (($call_to_action['mode'] ?? '') === 'custom') ? '' : 'lsd-util-hide'; ?>" data-lsd-cta-custom-fields>
        <div class="lsd-form-row">
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_cta_text"><?php esc_html_e('Button Text', 'listdom'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input class="lsd-admin-input" type="text" name="lsd[cta_text]" id="lsd_cta_text"
                       placeholder="<?php esc_attr_e('Click Here', 'listdom'); ?>"
                       value="<?php echo esc_attr($call_to_action['text'] ?? ''); ?>">
                <p class="<?php echo LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e('Leave empty to use the default "Click Here" button text for this listing.', 'listdom'); ?></p>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_cta_target"><?php esc_html_e('Button Action', 'listdom'); ?></label>
            </div>
            <div class="lsd-col-8">
                <select class="lsd-admin-input" name="lsd[cta_target]" id="lsd_cta_target" data-lsd-cta-target="1">
                    <option value="details" <?php selected(($call_to_action['target'] ?? 'details'), 'details'); ?>><?php esc_html_e('Open listing details page', 'listdom'); ?></option>
                    <option value="lightbox" <?php selected(($call_to_action['target'] ?? 'details'), 'lightbox'); ?>><?php esc_html_e('Open listing details in lightbox', 'listdom'); ?></option>
                    <option value="custom" <?php selected(($call_to_action['target'] ?? 'details'), 'custom'); ?>><?php esc_html_e('Open a custom link', 'listdom'); ?></option>
                    <?php if (LSD_Base::isPro()): ?>
                        <option value="popup" <?php selected(($call_to_action['target'] ?? 'details'), 'popup'); ?>><?php esc_html_e('Popup with custom content', 'listdom'); ?></option>
                    <?php endif; ?>
                </select>
                <?php if (!LSD_Base::isPro()): ?>
                    <div class="lsd-alert-no-my lsd-mt-2">
                        <?php echo LSD_Base::alert(
                            LSD_Base::missFeatureMessage(esc_html__('Popup CTA', 'listdom'))
                        ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lsd-form-row lsd-cta-target-field <?php echo (($call_to_action['target'] ?? 'details') === 'custom') ? '' : 'lsd-util-hide'; ?>" data-lsd-cta-target-field="custom">
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_cta_url"><?php esc_html_e('Custom Link', 'listdom'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input type="url" name="lsd[cta_url]" id="lsd_cta_url"
                       placeholder="<?php esc_attr_e('https://example.com/apply', 'listdom'); ?>"
                       value="<?php echo esc_url($call_to_action['url'] ?? ''); ?>">
            </div>
        </div>

        <?php if (LSD_Base::isPro()): ?>
            <div class="lsd-form-row lsd-cta-target-field <?php echo (($call_to_action['target'] ?? 'details') === 'popup') ? '' : 'lsd-util-hide'; ?>" data-lsd-cta-target-field="popup">
                <div class="lsd-col-2"></div>
                <div class="lsd-col-8">
                    <button type="button" class="lsd-cta-popup-button <?php echo $dashboard ? 'lsd-light-button': 'lsd-secondary-button'; ?>" data-lsd-cta-open-modal="lsd_cta_popup_modal"><?php esc_html_e('Edit Popup Content', 'listdom'); ?></button>
                    <p class="<?php echo LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e('HTML and shortcodes are supported.', 'listdom'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (LSD_Base::isPro()): ?>
    <div class="lsd-modal lsd-cta-modal lsd-util-hide" id="lsd_cta_popup_modal">
        <div class="lsd-modal-content">
            <a href="#" class="lsd-modal-close" aria-label="<?php esc_attr_e('Close popup', 'listdom'); ?>">
                <i class="listdom-icon fa fa-times"></i>
            </a>
            <div class="lsd-modal-body">
                <?php wp_editor(($call_to_action['content'] ?? ''), 'lsd_cta_popup_editor', [
                    'textarea_name' => 'lsd[cta_popup]',
                    'textarea_rows' => 6,
                    'media_buttons' => true,
                    'quicktags' => false,
                ]); ?>
            </div>
        </div>
    </div>
    <textarea class="lsd-util-hide"
          data-lsd-cta-storage="lsd_cta_popup_editor"
          name="lsd[cta_popup]"><?php echo esc_textarea($call_to_action['content'] ?? ''); ?></textarea>
<?php else: ?>
    <input type="hidden" name="lsd[cta_popup]" value="<?php echo esc_attr($call_to_action['content'] ?? ''); ?>">
<?php endif; ?>

<script>
jQuery(document).ready(function($)
{
    const $ctaRoot = $('.lsd-listing-module-cta');
    const $modeToggle = $ctaRoot.find('[data-lsd-cta-mode-toggle] input[type="checkbox"]');
    const $modeInput = $ctaRoot.find('[data-lsd-cta-mode]');

    function listdomToggleCTAMode(triggered)
    {
        const inherit = $modeToggle.length ? $modeToggle.is(':checked') : true;
        const mode = inherit ? 'inherit' : 'custom';

        if ($modeInput.length) $modeInput.val(mode);

        $ctaRoot.find('[data-lsd-cta-custom-fields]').toggleClass('lsd-util-hide', mode !== 'custom');

        if (triggered && mode === 'custom') listdomToggleCTATarget(false);
    }

    function listdomToggleCTATarget(triggered)
    {
        const value = $('#lsd_cta_target').val();
        $ctaRoot.find('[data-lsd-cta-target-field]').addClass('lsd-util-hide');

        if (value === 'custom')
        {
            $ctaRoot.find('[data-lsd-cta-target-field="custom"]').removeClass('lsd-util-hide');
        }
        else if (value === 'popup')
        {
            const $popupField = $ctaRoot.find('[data-lsd-cta-target-field="popup"]').removeClass('lsd-util-hide');
            if (triggered)
            {
                const $button = $popupField.find('[data-lsd-cta-open-modal]');
                if ($button.length) $button.trigger('click');
            }
        }
    }

    if ($modeToggle.length)
    {
        $modeToggle.on('change', function ()
        {
            listdomToggleCTAMode(true);
        });
    }

    $('#lsd_cta_target').on('change', function ()
    {
        listdomToggleCTATarget(true);
    });

    listdomToggleCTAMode(false);
    listdomToggleCTATarget(false);

    $(document).on('click', '[data-lsd-cta-open-modal]', function(e)
    {
        e.preventDefault();

        const target = $(this).data('lsd-cta-open-modal');
        if (!target) return;

        const $modal = $('#' + target);
        if (!$modal.length) return;

        if (!$modal.parent().is('body')) $modal.appendTo('body');
        $modal.removeClass('lsd-util-hide').css('display', 'flex').hide().fadeIn(100);
    });

    $(document).on('click', '.lsd-cta-modal .lsd-modal-close', function(e)
    {
        e.preventDefault();
        $(this).closest('.lsd-cta-modal').fadeOut(100, function ()
        {
            $(this).addClass('lsd-util-hide');
        });
    });

    $(document).on('click', '.lsd-cta-modal', function(e)
    {
        if ($(e.target).is('.lsd-cta-modal'))
        {
            $(this).fadeOut(100, function ()
            {
                $(this).addClass('lsd-util-hide');
            });
        }
    });
});
</script>
