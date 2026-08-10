<?php

class LSD_Element_Attributes extends LSD_Element
{
    public $key = 'attributes';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html(lsd_t_label(LSD_Base::TAX_ATTRIBUTE, 'plural'));
    }

    public function get($post_id = null, $show_icons = 0, $show_attribute_title = 1, $show_separator = 0, $layout = 'column')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $layout = in_array($layout, ['column', 'row', 'table'], true) ? $layout : 'column';
        if (!$this->has_renderable_rows((int) $post_id)) return '';

        // Generate output
        ob_start();
        include lsd_template('elements/attributes.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'show_icons' => $show_icons,
                'show_attribute_title' => $show_attribute_title,
                'show_separator' => $show_separator,
                'layout' => $layout,
            ]
        );
    }

    private function has_renderable_rows(int $post_id): bool
    {
        $post_attributes = get_post_meta($post_id, 'lsd_attributes', true);
        if (!is_array($post_attributes) || !count($post_attributes)) return false;

        $terms = LSD_Main::get_attributes();
        $attribute_context = LSD_Taxonomies_Attribute::context([
            'post_id' => $post_id,
        ]);

        foreach ($terms as $term)
        {
            if (!LSD_Taxonomies_Attribute::applies((int) $term->term_id, $attribute_context)) continue;

            $att = new LSD_Entity_Attribute($term->term_id);
            if ($att->type === 'separator') continue;

            $key = $term->slug;
            $value_exists = isset($post_attributes[$key])
                && !((is_string($post_attributes[$key]) && trim($post_attributes[$key]) === '') || (is_array($post_attributes[$key]) && count(array_filter($post_attributes[$key])) === 0));

            if (!$value_exists) continue;

            $raw_value = $post_attributes[$key];
            if (in_array($att->type, ['checkbox', 'dropdown'], true))
            {
                $items = $this->get_attribute_items($att->type, $raw_value);
                if (count($items)) return true;

                continue;
            }

            $value = $att->render($raw_value);
            if (trim((string) $value) !== '') return true;
        }

        return false;
    }

    private function get_attribute_items(string $type, $raw_value): array
    {
        if ($type === 'checkbox')
        {
            $items = is_array($raw_value) ? $raw_value : array_map('trim', explode(',', (string) $raw_value));
        }
        else
        {
            $items = is_array($raw_value) ? $raw_value : [(string) $raw_value];
        }

        return array_values(array_filter($items, static function ($item)
        {
            return trim((string) $item) !== '';
        }));
    }

    protected function general_settings(array $data): string
    {
        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_show_icons">' . esc_html__('Show Icons', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][show_icons]" id="lsd_elements_' . esc_attr($this->key) . '_show_icons">
                <option value="0" ' . (isset($data['show_icons']) && $data['show_icons'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                <option value="1" ' . (isset($data['show_icons']) && $data['show_icons'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_layout">' . esc_html__('Layout', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][layout]" id="lsd_elements_' . esc_attr($this->key) . '_layout">
                <option value="column" ' . ((isset($data['layout']) ? $data['layout'] : 'column') === 'column' ? 'selected="selected"' : '') . '>' . esc_html__('Default', 'listdom') . '</option>
                <option value="row" ' . ((isset($data['layout']) ? $data['layout'] : '') === 'row' ? 'selected="selected"' : '') . '>' . esc_html__('Inline', 'listdom') . '</option>
                <option value="table" ' . ((isset($data['layout']) ? $data['layout'] : '') === 'table' ? 'selected="selected"' : '') . '>' . esc_html__('Table', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">' . esc_html__('Show Custom Field Title', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][show_attribute_title]" id="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">
                <option value="1" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_show_separator">' . esc_html__('Show Separator', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][show_separator]" id="lsd_elements_' . esc_attr($this->key) . '_show_separator">
                <option value="0" ' . (isset($data['show_separator']) && $data['show_separator'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                <option value="1" ' . (isset($data['show_separator']) && $data['show_separator'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </div>';
    }
}
