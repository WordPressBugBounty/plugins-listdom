<?php
// no direct access
defined('ABSPATH') || die();

class LSD_Element_Features extends LSD_Element
{
    public $key = 'features';
    public $label;
    public $show_icons;
    public $enable_link;
    public $separator = '/';

    public function __construct($show_icons = false, $enable_link = true)
    {
        parent::__construct();

        $this->label = esc_html__('Features', 'listdom');
        $this->show_icons = $show_icons;
        $this->enable_link = $enable_link;
    }

    public function get($post_id = null, $list_style = 'per-row')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/features/list.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'list_style' => $list_style,
            ]
        );
    }

    public function text($post_id, $separator = '/')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Set the Separator
        $this->separator = $separator;

        // Generate output
        ob_start();
        include lsd_template('elements/features/text.php');
        return ob_get_clean();
    }

    protected function general_settings(array $data): string
    {
        $list_style = isset($data['list_style']) && trim($data['list_style']) ? $data['list_style'] : 'per-row';

        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_show_icons">' . esc_html__('Show Icons', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][show_icons]" id="lsd_elements_' . esc_attr($this->key) . '_show_icons">
                <option value="0" ' . (isset($data['show_icons']) && $data['show_icons'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                <option value="1" ' . (isset($data['show_icons']) && $data['show_icons'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_enable_link">' . esc_html__('Enable Link To Archive', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][enable_link]" id="lsd_elements_' . esc_attr($this->key) . '_enable_link">
                <option value="0" ' . (isset($data['enable_link']) && $data['enable_link'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                <option value="1" ' . (isset($data['enable_link']) && $data['enable_link'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_list_style">' . esc_html__('Layout', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][list_style]" id="lsd_elements_' . esc_attr($this->key) . '_list_style">
                <option value="per-row" ' . ($list_style === 'per-row' ? 'selected="selected"' : '') . '>' . esc_html__('One Per Row', 'listdom') . '</option>
                <option value="inline" ' . ($list_style === 'inline' ? 'selected="selected"' : '') . '>' . esc_html__('Inline', 'listdom') . '</option>
            </select>
        </div>';
    }
}
