<?php
defined('ABSPATH') || die();

$blueprints = LSD_Blueprints::instance()->all();
$history = LSD_Blueprints::instance()->history();
$preview_nonce = wp_create_nonce('lsd_blueprints_preview');
$apply_nonce = wp_create_nonce('lsd_blueprints_apply');
$audit_result = LSD_Actions::instance()->execute('audit_directory_setup', [], ['source' => 'internal']);
$audit = !empty($audit_result['success']) && isset($audit_result['data']) && is_array($audit_result['data']) ? $audit_result['data'] : [];
$summary = isset($audit['summary']) && is_array($audit['summary']) ? $audit['summary'] : [];
$missFeatureMessages = [];
?>
<form id="lsd_blueprint_form">
    <div class="lsd-settings-group-wrapper">
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-admin-title lsd-my-0"><?php echo esc_html__('Current Structure', 'listdom'); ?></h3>
                <p class="lsd-admin-description lsd-my-0"><?php esc_html_e('Review the current directory setup before importing sample content.', 'listdom'); ?></p>
            </div>

            <div class="lsd-admin-subsections">
                <div class="lsd-form-row">
                    <div class="lsd-col-3">
                        <label class="lsd-fields-label"><?php esc_html_e('Summary', 'listdom'); ?></label>
                    </div>
                    <div class="lsd-col-9">
                        <ul class="lsd-boxed-list">
                            <li><strong><?php esc_html_e('Listings:', 'listdom'); ?></strong> <?php echo esc_html((string) (int) ($summary['listings'] ?? 0)); ?></li>
                            <li><strong><?php esc_html_e('Categories:', 'listdom'); ?></strong> <?php echo esc_html((string) (int) ($summary['categories'] ?? 0)); ?></li>
                            <li><strong><?php esc_html_e('Custom Fields:', 'listdom'); ?></strong> <?php echo esc_html((string) (int) ($summary['custom_fields'] ?? 0)); ?></li>
                            <li><strong><?php esc_html_e('Search Forms:', 'listdom'); ?></strong> <?php echo esc_html((string) (int) ($summary['search_forms'] ?? 0)); ?></li>
                            <li><strong><?php esc_html_e('Skin Shortcodes:', 'listdom'); ?></strong> <?php echo esc_html((string) (int) ($summary['directory_views'] ?? 0)); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-admin-title lsd-my-0"><?php esc_html_e('Data Collection', 'listdom'); ?></h3>
                <p class="lsd-admin-description lsd-my-0"><?php esc_html_e('Data collections generate practical directory structure. Preview always runs first, and apply mode reuses existing items by default.', 'listdom'); ?></p>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-3">
                    <?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Data Collection', 'listdom'),
                        'for' => 'lsd_blueprint_select',
                    ]); ?>
                </div>
                <div class="lsd-col-5">
                    <select class="lsd-admin-input" id="lsd_blueprint_select" name="blueprint">
                        <option value=""><?php esc_html_e('Select a data collection', 'listdom'); ?></option>
                        <?php foreach ($blueprints as $blueprint): ?>
                            <option value="<?php echo esc_attr($blueprint['id']); ?>"><?php echo esc_html($blueprint['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-3">
                    <?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Include Demo Listings', 'listdom'),
                        'for' => 'lsd_blueprint_include_demo',
                    ]); ?>
                </div>
                <div class="lsd-col-5">
                    <label>
                        <input type="checkbox" id="lsd_blueprint_include_demo" name="include_demo" value="1" checked="checked">
                        <?php esc_html_e('Generate sample listings.', 'listdom'); ?>
                    </label>
                </div>
            </div>
        </div>
        <div class="lsd-settings-fields-wrapper lsd-util-hide" id="lsd_blueprint_preview_panel">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-admin-title lsd-my-0"><?php esc_html_e('Preview Generated Data Collection', 'listdom'); ?></h3>
            </div>
            <div id="lsd_blueprint_preview_content"></div>
        </div>
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-admin-title lsd-my-0"><?php esc_html_e('History', 'listdom'); ?></h3>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-12">
                    <?php if (!count($history)): ?>
                        <p class="lsd-admin-description lsd-my-0"><?php esc_html_e('No History available.', 'listdom'); ?></p>
                    <?php else: ?>
                        <ul class="lsd-boxed-list">
                            <?php foreach ($history as $entry): ?>
                                <li>
                                    <?php
                                    $summary = $entry['summary'] ?? [];
                                    echo esc_html(sprintf(
                                        __('%1$s | Created: %2$d, Updated: %3$d, Reused: %4$d, Errors: %5$d', 'listdom'),
                                        $entry['label'] ?? ($entry['blueprint_id'] ?? ''),
                                        (int) ($summary['create'] ?? 0),
                                        (int) ($summary['update'] ?? 0),
                                        (int) ($summary['reuse'] ?? 0),
                                        (int) ($summary['error'] ?? 0)
                                    ));
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-spacer-10"></div>
    <div class="lsd-form-row lsd-settings-submit-wrapper">
        <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end lsd-flex-align-center">
            <button type="button" class="lsd-secondary-button" id="lsd_blueprint_preview_button"><?php esc_html_e('Preview Data Collection', 'listdom'); ?> <i class="fa fa-eye"></i></button>
            <button type="button" class="lsd-primary-button" id="lsd_blueprint_apply_button" disabled="disabled"><?php esc_html_e('Apply Data Collection', 'listdom'); ?> <i class="wbli wbli-checkmark-circle"></i></button>
        </div>
    </div>
</form>

<script>
jQuery(function($) {
    let lastBlueprint = '';
    let lastIncludeDemo = true;

    function escapeHtml(text) {
        return $('<div>').text(text || '').html();
    }

    function summaryMarkup(summary) {
        return '<ul class="lsd-boxed-list">' +
            '<li><?php echo esc_js(__('Create', 'listdom')); ?>: <strong>' + (summary.create || 0) + '</strong></li>' +
            '<li><?php echo esc_js(__('Update', 'listdom')); ?>: <strong>' + (summary.update || 0) + '</strong></li>' +
            '<li><?php echo esc_js(__('Reuse', 'listdom')); ?>: <strong>' + (summary.reuse || 0) + '</strong></li>' +
            '<li><?php echo esc_js(__('Errors', 'listdom')); ?>: <strong>' + (summary.error || 0) + '</strong></li>' +
        '</ul>';
    }

    function resultBadgeClass(operation, result) {
        if (!result || result.success === false || operation === 'error') return 'lsd-error';
        if (operation === 'create') return 'lsd-dark-success';
        if (operation === 'update') return 'lsd-success';
        if (operation === 'reuse') return 'lsd-neutral';
        return 'lsd-info';
    }

    function renderPreview(payload) {
        const preview = payload.preview || {};
        const definition = preview.definition || {};
        const summary = preview.summary || {};
        const items = Array.isArray(preview.items) ? preview.items : [];

        let html = '';
        html += '<div class="lsd-admin-section-heading"><h3 class="lsd-admin-title lsd-my-0">' + escapeHtml(definition.label || '') + '</h3><p class="lsd-admin-description lsd-my-0">' + escapeHtml(definition.description || '') + '</p></div>';
        html += summaryMarkup(summary);

        if (items.length) {
            html += '<h4 class="lsd-admin-title"><?php echo esc_js(__('Actions', 'listdom')); ?></h4><ul class="lsd-boxed-list">';
            items.forEach(function(item) {
                const result = item.result || {};
                const op = item.operation || 'create';
                const code = result.code ? ' (' + result.code + ')' : '';
                const badgeClass = resultBadgeClass(op, result);
                html += '<li class="lsd-badge ' + badgeClass + '">' + escapeHtml(item.label || item.action) + ' [' + escapeHtml(op) + ']: ' + escapeHtml((result.message || '') + code) + '</li>';
            });
            html += '</ul>';
        }

        $('#lsd_blueprint_preview_content').html(html);
        $('#lsd_blueprint_preview_panel').removeClass('lsd-util-hide');
    }

    function postBlueprint(action, nonce, done) {
        const blueprint = $('#lsd_blueprint_select').val();
        const includeDemo = $('#lsd_blueprint_include_demo').is(':checked') ? 1 : 0;
        const $button = action === 'lsd_blueprints_apply' ? $('#lsd_blueprint_apply_button') : $('#lsd_blueprint_preview_button');

        if (!blueprint) {
            const message = '<?php echo esc_js(__('Select a data collection first.', 'listdom')); ?>';
            listdom_toastify(message, 'lsd-warning');
            return;
        }

        const loading = new ListdomButtonLoader($button);
        loading.start(action === 'lsd_blueprints_apply'
            ? '<?php echo esc_js(__('Applying Data Collection', 'listdom')); ?>'
            : '<?php echo esc_js(__('Previewing Data Collection', 'listdom')); ?>');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: action,
                _wpnonce: nonce,
                blueprint: blueprint,
                include_demo: includeDemo
            }
        }).done(function(response) {
            if (!response || !response.success) {
                const message = response && response.message ? response.message : '<?php echo esc_js(__('The request failed.', 'listdom')); ?>';
                loading.stop();
                listdom_toastify(message, 'lsd-error');
                return;
            }

            lastBlueprint = blueprint;
            lastIncludeDemo = !!includeDemo;
            loading.stop();
            done(response);
        }).fail(function(xhr) {
            const message = xhr && xhr.responseText ? xhr.responseText : '<?php echo esc_js(__('The request failed.', 'listdom')); ?>';
            loading.stop();
            listdom_toastify(message, 'lsd-error');
        });
    }

    $('#lsd_blueprint_preview_button').on('click', function() {
        postBlueprint('lsd_blueprints_preview', '<?php echo esc_js($preview_nonce); ?>', function(response) {
            renderPreview(response);
            $('#lsd_blueprint_apply_button').prop('disabled', false);
            const message = response.message || '<?php echo esc_js(__('Preview is ready. Review it before applying the data collection.', 'listdom')); ?>';
            listdom_toastify(message, 'lsd-success');
        });
    });

    $('#lsd_blueprint_apply_button').on('click', function() {
        if (!lastBlueprint || lastBlueprint !== $('#lsd_blueprint_select').val() || lastIncludeDemo !== $('#lsd_blueprint_include_demo').is(':checked')) {
            const message = '<?php echo esc_js(__('Preview the current data collection before applying it.', 'listdom')); ?>';
            listdom_toastify(message, 'lsd-warning');
            return;
        }

        postBlueprint('lsd_blueprints_apply', '<?php echo esc_js($apply_nonce); ?>', function(response) {
            const application = response.application || {};
            let html = '<h4 class="lsd-admin-title"><?php echo esc_js(__('Data collection applied.', 'listdom')); ?></h4>';

            if (application.summary) html += summaryMarkup(application.summary);

            $('#lsd_blueprint_preview_content').html(html);
            $('#lsd_blueprint_preview_panel').removeClass('lsd-util-hide');
            const message = response.message || '<?php echo esc_js(__('Operation is successfull. All Required item created or updated.', 'listdom')); ?>';
            listdom_toastify(message, 'lsd-success');
        });
    });
});
</script>
