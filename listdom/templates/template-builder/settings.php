<?php
/**
 * Element settings sections template.
 *
 * @var array        $sections
 * @var array        $data
 * @var LSD_Template $this
 */

if (empty($sections)) return;

$index = 0;
$visible_index = 0;
?>

<div class="lsd-element-editor-options"
     data-lsd-element-key="<?php echo esc_attr($this->key); ?>"
     data-lsd-select-placeholder="<?php echo esc_html__('Select an element to view settings', 'listdom'); ?>"
     data-lsd-loading-text="<?php echo esc_html__('Loading settings...', 'listdom'); ?>"
     data-lsd-settings-error="<?php echo esc_html__('Unable to load settings for this element right now.', 'listdom'); ?>"
     data-lsd-details-subtitle="<?php echo esc_html__('Element details', 'listdom'); ?>">
    <?php foreach ($sections as $section_id => $section) : ?>
        <?php $section_condition = isset($section['condition']) && is_array($section['condition']) ? $section['condition'] : [];
        $section_visible = $this->condition_matches($section_condition, $data);

        $section_attrs = '';

        if (!empty($section_condition))
        {
            $section_attrs .= ' data-lsd-control-condition="' . esc_attr(wp_json_encode($section_condition)) . '"';
            $section_attrs .= ' data-lsd-condition-scope="section"';
        }

        if (!$section_visible) $section_attrs .= ' style="display:none;" data-lsd-condition-hidden="1"';

        $is_active_section = $section_visible && $visible_index === 0;

        if ($section_visible) $visible_index++;
        ?>

        <section class="lsd-template-editor-settings-section<?php echo !empty($section['class']) ? ' ' . esc_attr(sanitize_html_class($section['class'])) : ''; ?>"
                 id="<?php echo esc_attr($this->key . '-' . sanitize_key((string) $section_id)); ?>"
            <?php echo $section_attrs; ?>>
            <?php $panel_id = $this->key . '-' . sanitize_key((string) $section_id) . '-panel'; ?>
            <?php $section_is_responsive = !empty($section['responsive']); ?>

            <div class="lsd-accordion-title<?php echo $is_active_section ? ' lsd-accordion-active' : ''; ?>" data-lsd-accordion-target="#<?php echo esc_attr($panel_id); ?>">
                <?php if (!empty($section['label'])) : ?>
                    <h4 class="lsd-template-editor-settings-panel__subtitle lsd-admin-subtitle-tiny">
                        <span><?php echo esc_html((string) $section['label']); ?></span>

                        <?php if ($section_is_responsive) : ?>
                            <span class="lsd-template-editor-settings-panel__responsive lsd-ml-2" data-lsd-responsive-icon>
                                <i class="listdom-icon wbli-screen" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                    </h4>
                <?php endif; ?>

                <span class="lsd-accordion-icons">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <i class="fa fa-minus" aria-hidden="true"></i>
                </span>
            </div>

            <div class="lsd-accordion-panel<?php echo $is_active_section ? ' lsd-accordion-open' : ''; ?>" id="<?php echo esc_attr($panel_id); ?>">
                <div class="lsd-template-editor-settings-inner-section">
                    <?php
                    $fields = (array) ($section['fields'] ?? []);
                    $tab_groups = isset($section['tabs']) && is_array($section['tabs']) ? $section['tabs'] : [];

                    $tabbed_fields = [];
                    $untabbed_fields = [];

                    foreach ($fields as $field)
                    {
                        $group_id = isset($field['control_tabs']) ? sanitize_key((string) $field['control_tabs']) : '';
                        $tab_id = isset($field['control_tab']) ? sanitize_key((string) $field['control_tab']) : '';

                        if ($group_id !== '' && $tab_id !== '' && isset($tab_groups[$group_id]['tabs'][$tab_id]))
                        {
                            if (!isset($tabbed_fields[$group_id])) $tabbed_fields[$group_id] = [];
                            if (!isset($tabbed_fields[$group_id][$tab_id])) $tabbed_fields[$group_id][$tab_id] = [];

                            $tabbed_fields[$group_id][$tab_id][] = $field;
                        }
                        else
                        {
                            $untabbed_fields[] = $field;
                        }
                    }

                    $render_fields = function (array $fields) use ($data) {
                        foreach ($fields as $field) :
                            $field_type = isset($field['type']) ? (string) $field['type'] : 'text';

                            $field_visible = $this->field_initially_visible($data, $field);
                            $row_attrs = $field_visible ? '' : ' style="display:none;" data-lsd-condition-hidden="1"';

                            if ($field_type === 'subsection_title') :
                                if (!empty($field['label']) || !empty($field['description'])) :
                                    ?>
                                    <div class="lsd-template-editor-settings-subsection lsd-admin-section-heading"<?php echo $row_attrs; ?>>
                                        <?php if (!empty($field['label'])) : ?>
                                            <h5 class="lsd-template-editor-settings-subsection__title lsd-admin-subtitle">
                                                <?php echo esc_html((string) $field['label']); ?>
                                            </h5>
                                        <?php endif; ?>

                                        <?php if (!empty($field['description'])) : ?>
                                            <p class="lsd-template-editor-settings-subsection__description lsd-admin-description-tiny lsd-m-0">
                                                <?php echo esc_html((string) $field['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="lsd-template-editor-settings-panel__row"<?php echo $row_attrs; ?>>
                                    <?php echo $this->fields($data, $field); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach;
                    };

                    $render_fields($untabbed_fields);

                    foreach ($tab_groups as $group_id => $group) :
                        $tabs = isset($group['tabs']) && is_array($group['tabs']) ? $group['tabs'] : [];
                        $tabs_with_fields = [];
                        $group_key = sanitize_key($this->key . '-' . $section_id . '-' . $group_id);

                        foreach ($tabs as $tab_id => $tab)
                        {
                            $fields_for_tab = $tabbed_fields[$group_id][$tab_id] ?? [];
                            if (empty($fields_for_tab)) continue;

                            $tabs_with_fields[] = [
                                'id'        => $tab_id,
                                'key'       => sanitize_key($this->key . '-' . $section_id . '-' . $group_id . '-' . $tab_id),
                                'label'     => (string) ($tab['label'] ?? $tab_id),
                                'condition' => isset($tab['condition']) && is_array($tab['condition']) ? $tab['condition'] : [],
                                'fields'    => $fields_for_tab,
                            ];
                        }

                        if (empty($tabs_with_fields)) continue;
                        ?>
                        <ul class="lsd-tab-switcher lsd-sub-tabs lsd-level-5-menu lsd-template-editor-settings-tabs" data-for=".lsd-template-editor-settings-tab-content-<?php echo esc_attr($group_key); ?>">
                            <?php $active_tab_selected = false; ?>

                            <?php foreach ($tabs_with_fields as $tab_index => $tab_info) : ?>
                                <?php
                                $tab_condition = isset($tab_info['condition']) && is_array($tab_info['condition']) ? $tab_info['condition'] : [];
                                $tab_visible = $this->condition_matches($tab_condition, $data);

                                $tab_attrs = '';

                                if (!empty($tab_condition))
                                {
                                    $tab_attrs .= ' data-lsd-control-condition="' . esc_attr(wp_json_encode($tab_condition)) . '"';
                                    $tab_attrs .= ' data-lsd-condition-scope="tab"';
                                }

                                if (!$tab_visible)
                                {
                                    $tab_attrs .= ' style="display:none;" data-lsd-condition-hidden="1"';
                                }

                                $is_active_tab = false;

                                if ($tab_visible && !$active_tab_selected)
                                {
                                    $is_active_tab = true;
                                    $active_tab_selected = true;
                                }
                                ?>

                                <li data-tab="<?php echo esc_attr($tab_info['key']); ?>"
                                    class="<?php echo $is_active_tab ? 'lsd-sub-tabs-active' : ''; ?>"
                                    <?php echo $tab_attrs; ?>>
                                    <a href="#" role="tab"><?php echo esc_html($tab_info['label'] !== '' ? $tab_info['label'] : $tab_info['id']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php
                        $active_tab_content_selected = false;
                        ?>

                        <?php foreach ($tabs_with_fields as $tab_index => $tab_info) : ?>
                            <?php
                            $tab_condition = isset($tab_info['condition']) && is_array($tab_info['condition']) ? $tab_info['condition'] : [];
                            $tab_visible = $this->condition_matches($tab_condition, $data);

                            $tab_content_attrs = '';

                            if (!empty($tab_condition))
                            {
                                $tab_content_attrs .= ' data-lsd-control-condition="' . esc_attr(wp_json_encode($tab_condition)) . '"';
                                $tab_content_attrs .= ' data-lsd-condition-scope="tab-content"';
                            }

                            if (!$tab_visible)
                            {
                                $tab_content_attrs .= ' style="display:none;" data-lsd-condition-hidden="1"';
                            }

                            $is_active_tab_content = false;

                            if ($tab_visible && !$active_tab_content_selected)
                            {
                                $is_active_tab_content = true;
                                $active_tab_content_selected = true;
                            }
                            ?>

                            <div class="lsd-tab-switcher-content lsd-template-editor-settings-tab-content-<?php echo esc_attr($group_key); ?><?php echo $is_active_tab_content ? ' lsd-tab-switcher-content-active' : ''; ?>"
                                 id="<?php echo esc_attr('lsd-tab-switcher-' . $tab_info['key'] . '-content'); ?>"
                                <?php echo $tab_content_attrs; ?>>
                                <?php $render_fields($tab_info['fields']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php $index++; endforeach; ?>
</div>
