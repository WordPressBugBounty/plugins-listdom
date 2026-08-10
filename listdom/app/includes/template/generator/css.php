<?php

class LSD_Template_Generator_CSS
{
    /**
     * Build a deterministic wrapper class name for an element instance.
     *
     * @param string $element_id
     * @return string
     */
    public static function wrapper_class(string $element_id): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '-', $element_id);
        $sanitized = trim((string) $sanitized, '-');

        return 'lsd-template-builder-element-' . ($sanitized !== '' ? $sanitized : 'item');
    }

    /**
     * Compile CSS rules for a full template layout.
     *
     * @param int   $template_id
     * @param array $layout_state
     * @param string $context
     * @return string
     */
    public static function compile_for_template(int $template_id, array $layout_state, string $context = 'frontend'): string
    {
        $elements = isset($layout_state['elements']) && is_array($layout_state['elements']) ? $layout_state['elements'] : [];

        return self::compile_for_elements($elements, $context);
    }

    /**
     * Compile CSS rules for a set of elements.
     *
     * @param array $elements
     * @param string $context
     * @return string
     */
    public static function compile_for_elements(array $elements, string $context = 'frontend'): string
    {
        $css_blocks = [];

        foreach ($elements as $element_id => $element_data)
        {
            $id   = sanitize_key(is_array($element_data) ? ($element_data['id'] ?? $element_id) : $element_id);
            $type = sanitize_key(is_array($element_data) ? ($element_data['type'] ?? '') : '');

            if (!$id || !$type) continue;

            $settings = is_array($element_data) && isset($element_data['settings']) && is_array($element_data['settings']) ? $element_data['settings'] : [];

            /** @var LSD_Template|false $instance */
            $instance = LSD_Template::instance($type, $settings);
            if (!$instance) continue;

            $wrapper_selector = '.' . self::wrapper_class($id);

            $field_sections = $instance->get_controls_sections();
            $selectors = self::build_field_selectors($field_sections, $settings, 'desktop');

            $compiled = self::render_rules($selectors, $wrapper_selector);
            if ($compiled !== '') $css_blocks[] = $compiled;

            $responsive_css = self::responsive_field_style_rules($field_sections, $wrapper_selector, $settings, $context, $compiled);
            if ($responsive_css !== '') $css_blocks[] = $responsive_css;

            $visibilityCss = self::responsive_visibility_rules($wrapper_selector, $settings['advanced'] ?? [], $context);
            if ($visibilityCss !== '') $css_blocks[] = $visibilityCss;
        }

        return trim(implode("\n", $css_blocks));
    }

    /**
     * Render selector map to CSS rules, replacing wrapper placeholder.
     *
     * @param array  $selectors
     * @param string $wrapper_selector
     * @return string
     */
    protected static function render_rules(array $selectors, string $wrapper_selector): string
    {
        if (empty($selectors)) return '';

        $rules = [];

        foreach ($selectors as $selector => $declaration)
        {
            if (is_array($declaration)) $declaration = implode(' ', array_filter($declaration));

            $declaration = trim((string) $declaration);
            if ($declaration === '') continue;

            $compiled_selector = str_replace('{{WRAPPER}}', $wrapper_selector, (string) $selector);
            $compiled_selector = trim($compiled_selector);

            if ($compiled_selector === '') continue;

            $rules[] = $compiled_selector . '{' . $declaration . '}';
        }

        return trim(implode("\n", $rules));
    }

    protected static function build_field_selectors(array $sections, array $settings, string $device): array
    {
        $selectors = [];

        foreach ($sections as $section)
        {
            $fields = isset($section['fields']) && is_array($section['fields']) ? $section['fields'] : [];
            $section_tab = isset($section['tab']) ? (string) $section['tab'] : '';
            $section_condition = isset($section['condition']) && is_array($section['condition']) ? $section['condition'] : [];

            foreach ($fields as $field)
            {
                if (empty($field['id'])) continue;

                $field_condition = isset($field['condition']) && is_array($field['condition']) ? $field['condition'] : [];
                $condition = array_merge($section_condition, $field_condition);

                if (!self::condition_matches($condition, $sections, $settings)) continue;

                $field_id = (string) $field['id'];
                $field_tab = isset($field['tab']) ? (string) $field['tab'] : '';
                $scope_key = $field_tab !== '' ? $field_tab : ($section_tab !== '' ? $section_tab : 'style');
                $scope_settings = isset($settings[$scope_key]) && is_array($settings[$scope_key]) ? $settings[$scope_key] : [];
                $selector_map = $field['selectors'] ?? ($field['selector'] ?? null);
                if (empty($selector_map) || !is_array($selector_map)) continue;

                // Match the settings panel: when a control has never been touched, use its declared default.
                $raw_value = self::resolve_field_raw_value($field, $scope_settings);
                $value = self::resolve_responsive_value($raw_value, $device);
                $radius_fallback = isset($field['radius_fallback']) ? (string) $field['radius_fallback'] : '';
                if ($radius_fallback !== '')
                {
                    $fallback_raw = self::resolve_control_raw_value($radius_fallback, $sections, $settings);
                    $fallback_value = self::resolve_responsive_value($fallback_raw, $device);
                    if ($fallback_value !== null && $fallback_value !== '')
                    {
                        if (is_array($value))
                        {
                            $radius = $value['radius'] ?? null;
                            if ($radius === null || $radius === '')
                            {
                                $value['radius'] = $fallback_value;
                            }
                        }
                        elseif ($value === null || $value === '')
                        {
                            $value = ['radius' => $fallback_value];
                        }
                    }
                }
                $field_css = self::field_value_to_css($field, $value);

                if ($field_css === '') continue;

                foreach ($selector_map as $selector => $declaration)
                {
                    $declaration = (string) $declaration;
                    if (strpos($declaration, '{{VALUE}}') !== false)
                    {
                        $declaration = str_replace('{{VALUE}}', $field_css, $declaration);
                    }
                    elseif (trim($declaration) === '')
                    {
                        $declaration = $field_css;
                    }
                    else
                    {
                        $declaration = trim($declaration) . ' ' . $field_css;
                    }

                    $selectors[$selector][] = trim($declaration);
                }
            }
        }

        $compiled = [];
        foreach ($selectors as $selector => $declarations)
        {
            $declarations = array_filter($declarations, static function ($value) {
                return trim((string) $value) !== '';
            });

            if (!$declarations) continue;

            $compiled[$selector] = implode(' ', $declarations);
        }

        return $compiled;
    }

    protected static function resolve_field_raw_value(array $field, array $scope_settings)
    {
        $field_id = isset($field['id']) ? (string) $field['id'] : '';
        if ($field_id === '') return null;

        if (array_key_exists($field_id, $scope_settings) && $scope_settings[$field_id] !== '') return $scope_settings[$field_id];

        return array_key_exists('default', $field) ? $field['default'] : null;
    }

    protected static function resolve_control_raw_value(string $control_id, array $sections, array $settings)
    {
        $field_match = self::find_control_field($control_id, $sections);
        if ($field_match === null) return null;

        $field = $field_match['field'];
        $section = $field_match['section'];
        $scope_settings = self::resolve_scope_settings($field, $section, $settings);

        return self::resolve_field_raw_value($field, $scope_settings);
    }

    protected static function resolve_scope_settings(array $field, array $section, array $settings): array
    {
        $field_tab = isset($field['tab']) ? (string) $field['tab'] : '';
        $section_tab = isset($section['tab']) ? (string) $section['tab'] : '';
        $scope_key = $field_tab !== '' ? $field_tab : ($section_tab !== '' ? $section_tab : 'style');

        return isset($settings[$scope_key]) && is_array($settings[$scope_key]) ? $settings[$scope_key] : [];
    }

    protected static function find_control_field(string $control_id, array $sections): ?array
    {
        foreach ($sections as $section)
        {
            $fields = isset($section['fields']) && is_array($section['fields']) ? $section['fields'] : [];

            foreach ($fields as $field)
            {
                if (($field['id'] ?? '') !== $control_id) continue;

                return [
                    'field' => $field,
                    'section' => $section,
                ];
            }
        }

        return null;
    }

    protected static function condition_matches(array $condition, array $sections, array $settings): bool
    {
        if (empty($condition)) return true;

        $relation = strtoupper(trim((string) ($condition['relation'] ?? 'AND')));
        unset($condition['relation']);

        if (empty($condition)) return true;

        if ($relation === 'OR')
        {
            foreach ($condition as $control_id => $expected)
            {
                $current = self::resolve_control_raw_value((string) $control_id, $sections, $settings);

                if (self::condition_value_matches($current, $expected)) return true;
            }

            return false;
        }

        foreach ($condition as $control_id => $expected)
        {
            $current = self::resolve_control_raw_value((string) $control_id, $sections, $settings);

            if (!self::condition_value_matches($current, $expected)) return false;
        }

        return true;
    }

    protected static function condition_value_matches($current, $expected): bool
    {
        if (is_array($current))
        {
            foreach ($current as $single_current)
            {
                if (self::condition_value_matches($single_current, $expected)) return true;
            }

            return false;
        }

        if (is_array($expected))
        {
            foreach ($expected as $single_expected)
            {
                if (self::condition_value_matches($current, $single_expected)) return true;
            }

            return false;
        }

        return self::normalize_condition_value($current) === self::normalize_condition_value($expected);
    }

    protected static function normalize_condition_value($value): string
    {
        if (is_bool($value)) return $value ? '1' : '0';

        if (is_int($value) || is_float($value)) return (string) $value;

        if ($value === null) return '';

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['true', 'yes', 'on', 'checked'], true)) return '1';

        if (in_array($value, ['false', 'no', 'off', 'unchecked'], true)) return '0';

        return $value;
    }

    protected static function field_value_to_css(array $field, $value): string
    {
        $type = isset($field['type']) ? (string) $field['type'] : '';

        if ($value === null || $value === '') return '';

        switch ($type)
        {
            case 'padding':
                if (!is_array($value)) return '';
                return self::padding_css($value);
            case 'border':
                if (!is_array($value))
                {
                    if (is_numeric($value))
                    {
                        $value = ['radius' => $value];
                    }
                    else
                    {
                        return '';
                    }
                }
                return self::border_css($value);
            case 'typography':
                if (!is_array($value)) return '';
                return self::typography_css($value);
            case 'colorpicker':
                return trim((string) $value);
            case 'gap':
                if (is_array($value))
                {
                    $row_gap = $value['row'] ?? ($value[0] ?? null);
                    $column_gap = $value['column'] ?? ($value[1] ?? null);
                }
                else
                {
                    $row_gap = $value;
                    $column_gap = $value;
                }

                $normalize = static function ($gap) {
                    if ($gap === '' || $gap === null) return null;
                    if (is_numeric($gap)) return $gap . 'px';
                    $gap = trim((string) $gap);
                    return $gap === '' ? null : $gap;
                };

                $row_gap = $normalize($row_gap);
                $column_gap = $normalize($column_gap);

                if ($row_gap === null && $column_gap === null) return '';
                if ($row_gap === null) $row_gap = $column_gap;
                if ($column_gap === null) $column_gap = $row_gap;

                $gap_value = $row_gap;
                if ($column_gap !== $row_gap) $gap_value .= ' ' . $column_gap;

                return 'gap:' . $gap_value . ';';
            default:
                return is_scalar($value) ? trim((string) $value) : '';
        }
    }

    protected static function padding_css(array $value): string
    {
        if (isset($value['padding']) && is_array($value['padding'])) $value = $value['padding'];

        return 'padding:'
            . (float) ($value['top'] ?? 0) . 'px '
            . (float) ($value['right'] ?? 0) . 'px '
            . (float) ($value['bottom'] ?? 0) . 'px '
            . (float) ($value['left'] ?? 0) . 'px;';
    }

    protected static function border_css(array $value): string
    {
        if (isset($value['border']) && is_array($value['border'])) $value = $value['border'];

        $css = '';

        $style = $value['style'] ?? 'none';
        if ($style !== 'none')
        {
            $css .= 'border-style:' . $style . ';';

            $css .= 'border-width:'
                . (float) ($value['top'] ?? 0) . 'px '
                . (float) ($value['right'] ?? 0) . 'px '
                . (float) ($value['bottom'] ?? 0) . 'px '
                . (float) ($value['left'] ?? 0) . 'px;';

            if (!empty($value['color'])) $css .= 'border-color:' . trim((string) $value['color']) . ';';
        }

        if (isset($value['radius']) && $value['radius'] !== '') $css .= 'border-radius:' . (float) $value['radius'] . 'px;';

        return $css;
    }

    protected static function typography_css(array $value): string
    {
        if (isset($value['typography']) && is_array($value['typography'])) $value = $value['typography'];

        $css = '';

        if (!empty($value['family']) && $value['family'] !== 'inherit') $css .= 'font-family:' . trim((string) $value['family']) . ';';

        if (!empty($value['weight']) && $value['weight'] !== 'inherit') $css .= 'font-weight:' . trim((string) $value['weight']) . ';';

        if (!empty($value['align']) && $value['align'] !== 'inherit') $css .= 'text-align:' . trim((string) $value['align']) . ';';

        if (!empty($value['size']))
        {
            if (is_array($value['size']))
            {
                if ($value['size']['value'] !== '' && $value['size']['value'] !== null)
                {
                    $css .= 'font-size:'
                        . $value['size']['value']
                        . ($value['size']['unit'] ?? 'px')
                        . ';';
                }
            }
            else
            {
                $css .= 'font-size:' . trim((string) $value['size']) . ';';
            }
        }

        if (!empty($value['line_height']))
        {
            if (is_array($value['line_height']))
            {
                if ($value['line_height']['value'] !== '' && $value['line_height']['value'] !== null)
                {
                    $css .= 'line-height:'
                        . $value['line_height']['value']
                        . ($value['line_height']['unit'] ?? '')
                        . ';';
                }
            }
            else
            {
                $css .= 'line-height:' . trim((string) $value['line_height']) . ';';
            }
        }

        return $css;
    }

    protected static function resolve_responsive_value($value, string $device)
    {
        if (!is_array($value) || !self::is_responsive_value($value)) return $value;

        $desktop_value = $value['desktop'] ?? ($value['tablet'] ?? ($value['mobile'] ?? null));
        $device_value  = $value[$device] ?? null;

        if ($device !== 'desktop' && self::is_empty_value($device_value)) $device_value = $desktop_value;

        if (is_array($desktop_value) && is_array($device_value))
        {
            return self::merge_responsive_values($desktop_value, $device_value);
        }

        return $device_value;
    }


    protected static function is_responsive_value(array $value): bool
    {
        return isset($value['desktop']) || isset($value['tablet']) || isset($value['mobile']);
    }

    protected static function is_empty_value($value): bool
    {
        return $value === null || $value === '';
    }

    protected static function merge_responsive_values(array $base, array $override): array
    {
        $merged = $base;

        foreach ($override as $key => $value)
        {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]))
            {
                $merged[$key] = self::merge_responsive_values($base[$key], $value);
                continue;
            }

            if (self::is_empty_value($value)) continue;

            $merged[$key] = $value;
        }

        return $merged;
    }

    protected static function responsive_field_style_rules(array $sections, string $wrapper_selector, array $settings, string $context, string $base_css): string
    {
        $devices = [
            'tablet' => [
                'query'  => '(min-width: 768px) and (max-width: 1024px)',
                'prefix' => 'body[data-lsd-responsive="tablet"]',
            ],
            'mobile' => [
                'query'  => '(max-width: 767px)',
                'prefix' => 'body[data-lsd-responsive="mobile"]',
            ],
        ];

        $rules = [];

        foreach ($devices as $device => $config)
        {
            $device_selectors = self::build_field_selectors($sections, $settings, $device);
            $device_css = self::render_rules($device_selectors, $wrapper_selector);

            if ($device_css === '' || $device_css === $base_css) continue;

            if (in_array($context, ['preview', 'editor-preview'], true))
            {
                $rules[] = self::prefix_rules($device_css, $config['prefix']);
                continue;
            }

            $rules[] = '@media ' . $config['query'] . '{' . $device_css . '}';
        }

        return implode("\n", array_filter($rules));
    }

    protected static function prefix_rules(string $css, string $prefix): string
    {
        if ($css === '' || $prefix === '') return $css;

        $prefixed = [];
        $blocks = explode('}', $css);

        foreach ($blocks as $block)
        {
            $block = trim($block);
            if ($block === '') continue;

            $parts = explode('{', $block, 2);
            if (count($parts) !== 2) continue;

            $selector = trim($parts[0]);
            $declaration = $parts[1];

            if ($selector === '') continue;

            $prefixed[] = $prefix . ' ' . $selector . '{' . $declaration . '}';
        }

        return implode("\n", $prefixed);
    }

    /**
     * Build responsive visibility rules based on advanced settings.
     *
     * @param string $wrapper_selector
     * @param array  $settings
     * @return string
     */
    protected static function responsive_visibility_rules(string $wrapper_selector, array $settings, string $context = 'frontend'): string
    {
        if (!$wrapper_selector || empty($settings)) return '';

        if (in_array($context, ['preview', 'editor-preview'], true)) return '';

        $isEnabled = function ($value): bool {
            if (is_bool($value)) return $value;
            $string = trim((string) $value);
            return $string !== '' && $string !== '0';
        };

        $hideDesktop = $isEnabled($settings['hide_on_desktop'] ?? false);
        $hideTablet  = $isEnabled($settings['hide_on_tablet'] ?? false);
        $hideMobile  = $isEnabled($settings['hide_on_mobile'] ?? false);

        $rules = [];

        if ($hideDesktop) $rules[] = '@media (min-width: 1025px){' . $wrapper_selector . '{display:none !important;}}';

        if ($hideTablet) $rules[] = '@media (min-width: 768px) and (max-width: 1024px){' . $wrapper_selector . '{display:none !important;}}';

        if ($hideMobile) $rules[] = '@media (max-width: 767px){' . $wrapper_selector . '{display:none !important;}}';

        return implode("\n", array_filter($rules));
    }

    /**
     * Build token map from settings using shared style generator helpers.
     *
     * @param array $settings
     * @return array
     */

    /**
     * Save compiled CSS for a template to a file in uploads/template-builder/css/.
     *
     * @param int   $post_id
     * @param array $layout_state
     * @param string $context
     * @return string
     */
    public static function save_post_css(int $post_id, array $layout_state, string $context = 'save'): string
    {
        $css = self::compile_for_template($post_id, $layout_state, $context);

        [$file_path] = self::post_css_path($post_id, $context);
        if (!$file_path) return '';

        if ($css === '')
        {
            if (file_exists($file_path)) @unlink($file_path);
            return '';
        }

        $dir = dirname($file_path);
        if (!is_dir($dir)) wp_mkdir_p($dir);

        file_put_contents($file_path, $css);

        return $css;
    }

    /**
     * Load compiled CSS file for a template and print the stylesheet tag when needed.
     *
     * @param int    $post_id
     * @param array  $layout_state
     * @param string $context
     * @return string
     */
    public static function load_post_css(int $post_id, array $layout_state, string $context = 'frontend'): string
    {
        if (!self::enqueue_post_css($post_id, $context))
        {
            $css = self::save_post_css($post_id, $layout_state, $context);
            if ($css === '' || !self::enqueue_post_css($post_id, $context)) return '';
        }

        $should_print_now = (function_exists('wp_doing_ajax') && wp_doing_ajax()) || did_action('wp_head') || did_action('wp_print_styles');

        if (!$should_print_now) return '';

        $handle = self::post_css_handle($post_id, $context);
        if (wp_style_is($handle, 'done')) return '';

        ob_start();
        wp_print_styles($handle);

        return (string) ob_get_clean();
    }

    /**
     * Enqueue compiled CSS file if present.
     *
     * @param int    $post_id
     * @param string $context
     * @return bool
     */
    public static function enqueue_post_css(int $post_id, string $context = 'frontend'): bool
    {
        [$file_path, $file_url] = self::post_css_path($post_id, $context);
        if (!$file_path || !$file_url || !file_exists($file_path)) return false;

        $version = @filemtime($file_path) ?: LSD_Assets::version();

        wp_enqueue_style(self::post_css_handle($post_id, $context), $file_url, [], $version);

        return true;
    }

    /**
     * Resolve upload path and URL for a template CSS file.
     *
     * @param int    $post_id
     * @param string $context
     * @return array
     */
    protected static function post_css_path(int $post_id, string $context = 'frontend'): array
    {
        $upload_dir = wp_upload_dir();

        if (empty($upload_dir['basedir']) || empty($upload_dir['baseurl'])) return ['', ''];

        $dir = trailingslashit($upload_dir['basedir']) . 'template-builder/css/';
        $url = trailingslashit($upload_dir['baseurl']) . 'template-builder/css/';

        $file_context = self::post_css_context($context);
        $filename = $file_context === 'preview' ? 'post-' . $post_id . '-preview.css' : 'post-' . $post_id . '.css';

        return [$dir . $filename, $url . $filename];
    }

    /**
     * Build a stable stylesheet handle for a template CSS file.
     *
     * @param int    $post_id
     * @param string $context
     * @return string
     */
    protected static function post_css_handle(int $post_id, string $context = 'frontend'): string
    {
        $file_context = self::post_css_context($context);

        return $file_context === 'preview' ? 'lsd-template-builder-preview-' . $post_id : 'lsd-template-builder-' . $post_id;
    }

    /**
     * Normalize render context to the matching stylesheet file context.
     *
     * @param string $context
     * @return string
     */
    protected static function post_css_context(string $context): string
    {
        return in_array($context, ['preview', 'editor-preview'], true) ? 'preview' : 'frontend';
    }
}
