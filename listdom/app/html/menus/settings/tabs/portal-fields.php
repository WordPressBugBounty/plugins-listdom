<?php
defined('ABSPATH') || die();

$rows = LSD_Portal_Fieldlayout::rows(true);
?>
<div class="lsd-settings-fields-wrapper" id="lsd-user-field-settings">
    <div class="lsd-flex" style="flex-direction: column; gap: 6px;">
        <p class="lsd-admin-description"><?php esc_html_e('Turning a field off hides it and keeps its saved values and settings.', 'listdom'); ?></p>
        <p class="lsd-admin-description">
            <?php esc_html_e('Username, account email, and password follow the existing account rules.', 'listdom'); ?>
            <a href="<?php echo esc_url(LSD_Portal::admin_url('login-registration', 'registration')); ?>"><?php esc_html_e('Registration settings', 'listdom'); ?></a>
            · <a href="<?php echo esc_url(LSD_Portal::admin_url('security', 'account-sections')); ?>"><?php esc_html_e('Account Security', 'listdom'); ?></a>
        </p>
    </div>
    <input type="hidden" name="lsd_user_field_layout" value="">
    <div class="lsd-form-row">
        <div class="lsd-col-8">
            <ul class="lsd-settings-dashboard-menus lsd-mt-0">
                <?php foreach ($rows as $row_id => $field) include __DIR__ . '/portal-field-row.php'; ?>
            </ul>
            <div class="lsd-flex lsd-flex-wrap lsd-gap-3">
                <button type="button" class="lsd-secondary-button" data-add="layout"><?php esc_html_e('Add Heading or Separator', 'listdom'); ?></button>
                <?php if (LSD_Base::isPro()): ?>
                    <button type="button" class="lsd-secondary-button" data-add="custom"><?php esc_html_e('Add Custom Field', 'listdom'); ?></button>
                <?php endif; ?>
            </div>
            <p class="lsd-admin-description lsd-mb-0 lsd-mt-2"><?php esc_html_e('Drag and drop the fields to change their order.', 'listdom'); ?></p>
            <?php if (!LSD_Base::isPro()): ?>
                <p class="lsd-admin-description-tiny"><?php esc_html_e('Custom user fields are available in Listdom Pro.', 'listdom'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (LSD_Base::isPro() ? ['layout', 'custom'] : ['layout'] as $kind): ?>
        <template data-new="<?php echo esc_attr($kind); ?>">
            <?php
            $row_id = '';
            $field = ['kind' => $kind, 'label' => '', 'type' => $kind === 'layout' ? 'heading' : 'text', 'enabled' => true];
            include __DIR__ . '/portal-field-row.php';
            ?>
        </template>
    <?php endforeach; ?>
</div>
<script>
jQuery(function ($)
{
    const $settings = $('#lsd-user-field-settings');
    const $list = $settings.find('.lsd-settings-dashboard-menus');
    const $output = $settings.find('[name="lsd_user_field_layout"]');
    const removed = [];
    let nextId = 0;

    function data()
    {
        const rows = {};
        $list.children('[data-row]').each(function ()
        {
            const settings = {};
            $(this).find('[data-field]').each(function ()
            {
                const key = this.dataset.field;
                if (key === 'roles')
                {
                    if (!settings.roles) settings.roles = [];
                    if (this.checked) settings.roles.push(this.value);
                }
                else if (key === 'enabled') settings[key] = this.value === '1';
                else settings[key] = this.type === 'checkbox' ? this.checked : this.value;
            });
            rows[this.dataset.row] = settings;
        });
        return JSON.stringify({rows, removed});
    }

    const initial = data();
    $settings.closest('form').on('lsd:prepare-user-fields', function ()
    {
        const current = data();
        $output.val(current === initial ? '' : current);
    });

    $settings.on('click', '[data-add]', function ()
    {
        const kind = this.dataset.add;
        const template = $settings.find('template[data-new="' + kind + '"]')[0];
        const row = template.content.firstElementChild.cloneNode(true);
        row.dataset.row = 'new-' + kind + ':' + ++nextId;
        $list.append(row);
        $(row).find('[data-expand]').trigger('click');
        row.querySelector('[data-field="label"]').focus();
    }).on('click', '[data-expand]', function ()
    {
        const $button = $(this);
        const open = $button.attr('aria-expanded') !== 'true';
        $button.closest('[data-row]').find('.lsd-dashboard-menu-content').toggleClass('lsd-util-hide', !open);
        $button.attr('aria-expanded', open ? 'true' : 'false');
        $button.find('i').toggleClass('fa-chevron-down', !open).toggleClass('fa-chevron-up', open);
    }).on('click', '[data-remove]', function ()
    {
        const $row = $(this).closest('[data-row]');
        if ($row.data('kind') === 'custom' && !window.confirm(<?php echo wp_json_encode(__('Remove this custom field? Existing values will be retained for personal data export and erasure.', 'listdom')); ?>)) return;
        removed.push($row.attr('data-row'));
        const $next = $row.next().length ? $row.next() : $row.prev();
        $row.remove();
        if ($next.length) $next.find('[data-expand]').trigger('focus');
        else $settings.find('[data-add]').first().trigger('focus');
    }).on('change', '[data-kind="custom"] [data-field="type"]', function ()
    {
        $(this).closest('[data-row]').find('[data-choices]').toggleClass('lsd-util-hide', !['select', 'multi', 'radio'].includes(this.value));
    }).on('input', '[data-field="label"]', function ()
    {
        const $label = $(this).closest('[data-row]').find('[data-label]');
        $label.text(this.value || $label.data('label'));
    });
});
</script>
