<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $key */
/** @var array $f_fields */
/** @var array $field_samples */
/** @var array $current_mapping */

$taxonomy_mapping = isset($current_mapping[$key]) && is_array($current_mapping[$key]) ? $current_mapping[$key] : [];
$taxonomy_sources = LSD_IX_Mapping::taxonomy_sources($taxonomy_mapping);
$taxonomy_mode = LSD_IX_Mapping::taxonomy_mode($taxonomy_mapping);
$hierarchical_taxonomy = in_array($key, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION], true);
$taxonomy_id = 'lsd_ix_taxonomy_mapping_' . sanitize_key($key);
$boolean_columns = [];

foreach ($f_fields as $field_index => $field_label)
{
    $boolean_columns[$field_index] = LSD_IX_Mapping::is_boolean_samples($field_samples[$field_index] ?? []);
}
?>
<div class="lsd-ix-taxonomy-mapping" id="<?php echo esc_attr($taxonomy_id); ?>">
    <?php if ($hierarchical_taxonomy): ?>
    <div class="lsd-row lsd-mb-2">
        <div class="lsd-col-12">
            <label class="lsd-fields-label" for="<?php echo esc_attr($taxonomy_id); ?>_mode"><?php esc_html_e('Import Mode', 'listdom'); ?></label>
            <select class="lsd-admin-input lsd-ix-taxonomy-mode" id="<?php echo esc_attr($taxonomy_id); ?>_mode" name="ix[mapping][<?php echo esc_attr($key); ?>][taxonomy_mode]">
                <option value="flat" <?php echo $taxonomy_mode === 'flat' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Flat terms', 'listdom'); ?></option>
                <option value="hierarchy" <?php echo $taxonomy_mode === 'hierarchy' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Hierarchy', 'listdom'); ?></option>
            </select>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-ix-taxonomy-hierarchy-description <?php echo $taxonomy_mode !== 'hierarchy' ? 'lsd-util-hide' : ''; ?>"><?php esc_html_e('Selected value columns become parent-to-child path segments in their displayed order.', 'listdom'); ?></p>
        </div>
    </div>
    <?php endif; ?>
    <div class="lsd-ix-taxonomy-sources"></div>
    <button type="button" class="lsd-secondary-button lsd-ix-taxonomy-source-add"><?php esc_html_e('Add Source Column', 'listdom'); ?></button>
</div>

<style>
#<?php echo esc_attr($taxonomy_id); ?> .lsd-ix-taxonomy-source {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

#<?php echo esc_attr($taxonomy_id); ?> .lsd-ix-taxonomy-source-fields {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

#<?php echo esc_attr($taxonomy_id); ?> .lsd-ix-taxonomy-source-mode-wrap {
    width: 100%;
}

#<?php echo esc_attr($taxonomy_id); ?> .lsd-ix-taxonomy-source-remove {
    flex: 0 0 30px;
    width: 30px;
    min-width: 30px;
    height: 30px;
    min-height: 30px;
    padding: 5px;
    gap: 0;
    border-radius: 6px;
}

#<?php echo esc_attr($taxonomy_id); ?> .lsd-ix-taxonomy-source-remove i {
    width: 14px;
    height: 14px;
    font-size: 14px;
}
</style>

<script type="text/html" id="<?php echo esc_attr($taxonomy_id); ?>_template">
    <div class="lsd-ix-taxonomy-source lsd-mb-2">
        <div class="lsd-ix-taxonomy-source-fields">
            <select class="lsd-admin-input lsd-ix-taxonomy-source-column" name="ix[mapping][<?php echo esc_attr($key); ?>][sources][__index__][column]" title="<?php esc_attr_e('Source column', 'listdom'); ?>">
                <option value=""><?php esc_html_e('Select a column', 'listdom'); ?></option>
                <?php foreach ($f_fields as $field_index => $field_label): ?>
                <option value="<?php echo esc_attr($field_index); ?>" data-label="<?php echo esc_attr($field_label); ?>"><?php echo esc_html(sprintf('#%d — %s', ((int) $field_index) + 1, $field_label)); ?></option>
            <?php endforeach; ?>
            </select>
            <input type="hidden" class="lsd-ix-taxonomy-source-label" name="ix[mapping][<?php echo esc_attr($key); ?>][sources][__index__][label]">
            <div class="lsd-ix-taxonomy-source-mode-wrap">
                <select class="lsd-admin-input lsd-ix-taxonomy-source-mode" name="ix[mapping][<?php echo esc_attr($key); ?>][sources][__index__][mode]" title="<?php esc_attr_e('Source behavior', 'listdom'); ?>">
                    <option value="value"><?php esc_html_e('Use column values', 'listdom'); ?></option>
                    <option value="label"><?php esc_html_e('Use column label when true', 'listdom'); ?></option>
                </select>
            </div>
        </div>
        <button type="button" class="lsd-secondary-button lsd-ix-taxonomy-source-remove" aria-label="<?php esc_attr_e('Remove source column', 'listdom'); ?>" title="<?php esc_attr_e('Remove source column', 'listdom'); ?>">
            <i class="lsd-icon fas fa-trash-alt" aria-hidden="true"></i>
        </button>
    </div>
</script>

<script>
(function ($)
{
    const $root = $('#<?php echo esc_js($taxonomy_id); ?>');
    const $sources = $root.find('.lsd-ix-taxonomy-sources');
    const $template = $('#<?php echo esc_js($taxonomy_id); ?>_template');
    const booleanColumns = <?php echo wp_json_encode($boolean_columns); ?>;
    const hierarchical = <?php echo $hierarchical_taxonomy ? 'true' : 'false'; ?>;
    let index = 0;

    function hierarchyMode()
    {
        return hierarchical && $root.find('.lsd-ix-taxonomy-mode').val() === 'hierarchy';
    }

    function updateSource($source, autoMode)
    {
        const $column = $source.find('.lsd-ix-taxonomy-source-column');
        const $label = $source.find('.lsd-ix-taxonomy-source-label');
        const $mode = $source.find('.lsd-ix-taxonomy-source-mode');
        const columnIndex = $column.val();

        $label.val($column.find('option:selected').data('label') || '');
        if (autoMode) $mode.val(booleanColumns[columnIndex] ? 'label' : 'value');

        const hierarchy = hierarchyMode();
        $source.find('.lsd-ix-taxonomy-source-mode-wrap').toggleClass('lsd-util-hide', hierarchy);
        if (hierarchy)
        {
            if ($mode.data('flat-mode') === undefined) $mode.data('flat-mode', $mode.val());
            $mode.val('value');
        }
        else if ($mode.data('flat-mode') !== undefined)
        {
            $mode.val($mode.data('flat-mode'));
            $mode.removeData('flat-mode');
        }
    }

    function addSource(source)
    {
        source = source || {};

        const markup = $template.html().replace(/__index__/g, index++);
        const $source = $(markup);
        $sources.append($source);

        const $column = $source.find('.lsd-ix-taxonomy-source-column');
        const $mode = $source.find('.lsd-ix-taxonomy-source-mode');

        if (source.column !== undefined && source.column !== null && source.column !== '') $column.val(String(source.column));
        updateSource($source, source.mode === undefined);

        if (source.mode === 'label' || source.mode === 'value') $mode.val(source.mode);
        if (hierarchyMode()) $mode.val('value');
    }

    function normalizeSources(mapping)
    {
        if (mapping && Array.isArray(mapping.sources)) return mapping.sources;
        if (mapping && mapping.sources && typeof mapping.sources === 'object')
        {
            return Object.keys(mapping.sources)
                .sort(function (a, b) { return Number(a) - Number(b); })
                .map(function (key) { return mapping.sources[key]; });
        }
        if (mapping && mapping.map !== undefined && mapping.map !== '') return [{column: mapping.map, mode: 'value'}];

        return [];
    }

    function load(mapping)
    {
        mapping = mapping || {};
        $sources.empty();

        if (hierarchical) $root.find('.lsd-ix-taxonomy-mode').val(mapping.taxonomy_mode === 'hierarchy' ? 'hierarchy' : 'flat');
        normalizeSources(mapping).forEach(addSource);
        refresh();
    }

    function refresh()
    {
        const hierarchy = hierarchyMode();
        $root.find('.lsd-ix-taxonomy-hierarchy-description').toggleClass('lsd-util-hide', !hierarchy);
        $root.find('.lsd-ix-taxonomy-source').each(function ()
        {
            updateSource($(this), false);
        });
    }

    $root.on('click', '.lsd-ix-taxonomy-source-add', function ()
    {
        addSource();
    });

    $root.on('click', '.lsd-ix-taxonomy-source-remove', function ()
    {
        $(this).closest('.lsd-ix-taxonomy-source').remove();
    });

    $root.on('change', '.lsd-ix-taxonomy-source-column', function ()
    {
        updateSource($(this).closest('.lsd-ix-taxonomy-source'), true);
    });

    $root.on('change', '.lsd-ix-taxonomy-mode', refresh);

    window.lsd_ix_taxonomy_mappings = window.lsd_ix_taxonomy_mappings || {};
    window.lsd_ix_taxonomy_mappings['<?php echo esc_js($key); ?>'] = {load: load};

    <?php foreach ($taxonomy_sources as $taxonomy_source): ?>
    addSource(<?php echo wp_json_encode($taxonomy_source); ?>);
    <?php endforeach; ?>
    refresh();
})(jQuery);
</script>
