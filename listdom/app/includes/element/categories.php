<?php

class LSD_Element_Categories extends LSD_Element
{
    public $key = 'categories';
    public $label;
    public $show_color;
    public $color_method;
    public $multiple_categories;
    public $enable_link;
    public $display_name = true;
    public $display_icon = false;

    public function __construct(array $args = [])
    {
        parent::__construct();

        $this->label = esc_html__('Categories', 'listdom');
        $this->has_title_settings = false;

        $this->show_color = $args['show_color'] ?? true;
        $this->multiple_categories = $args['multiple_categories'] ?? false;
        $this->color_method = $args['color_method'] ?? 'bg';
        $this->enable_link = $args['enable_link'] ?? true;
        $this->display_name = $args['display_name'] ?? true;
        $this->display_icon = $args['display_icon'] ?? false;
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Entity
        $entity = new LSD_Entity_Listing($post_id);

        if (!$this->multiple_categories)
        {
            $category = $entity->get_data_category();
            if (!$category) return '';
        }
        else
        {
            // Get All Categories
            $categories = wp_get_post_terms($post_id, LSD_Base::TAX_CATEGORY);
            if (!count($categories)) return '';
        }

        // Generate output
        ob_start();
        include lsd_template('elements/categories.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public static function styles($category_id, $method = 'bg'): string
    {
        $color = get_term_meta($category_id, 'lsd_color', true);

        if ($method === 'text') return 'style="color: ' . esc_attr($color) . ';"';
        else
        {
            $text = LSD_Base::get_text_color($color);
            return 'style="background-color: ' . esc_attr($color) . '; color: ' . esc_attr($text) . ';"';
        }
    }

    public function display(WP_Term $category): string
    {
        $icon = $this->display_icon ? LSD_Taxonomies::icon($category->term_id) : '';

        if ($this->enable_link) $inner = '<a href="' . esc_url(get_term_link($category->term_id)) . '" ' . ($this->show_color ? LSD_Element_Categories::styles($category->term_id, $this->color_method) : '') . ' ' . lsd_schema()->category() . '>' . $icon . ($this->display_name ? esc_html($category->name) : '') . '</a>';
        else $inner = '<span class="lsd-single-term" ' . ($this->show_color ? LSD_Element_Categories::styles($category->term_id, $this->color_method) : '') . ' ' . lsd_schema()->category() . '>' . $icon . ($this->display_name ? esc_html($category->name) : '') . '</span>';

        return '<span>' . $inner . '</span>';
    }

    protected function general_settings(array $data): string
    {
        $enable_link = isset($data['enable_link']) ? (int) $data['enable_link'] : 1;

        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_enable_link">' . esc_html__('Enable Link To Archive', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][enable_link]" id="lsd_elements_' . esc_attr($this->key) . '_enable_link">
                <option value="1" ' . ($enable_link === 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . ($enable_link === 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>';
    }
}
