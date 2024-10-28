<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Widgets Ta Class.
 *
 * @class LSD_Widgets_TaxonomyCloud
 * @version    1.0.0
 */
class LSD_Widgets_TaxonomyCloud extends WP_Widget
{
    public $LSD;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('LSD_Widgets_TaxonomyCloud', esc_html__('(Listdom) Cloud', 'listdom'), [
            'description' => esc_html__('Put tag cloud of Listdom taxonomies into a sidebar/widget area.', 'listdom'),
        ]);

        // Listdom Object
        $this->LSD = new LSD_Widgets();
    }

    public function widget($args, $instance)
    {
        // Before Widget
        echo($args['before_widget'] ?? '');

        // Print the widget title
        if (!empty($instance['title']))
        {
            echo ($args['before_title'] ?? '') . apply_filters('widget_title', $instance['title']) . ($args['after_title'] ?? '');
        }

        // Print the output
        $shortcode = new LSD_Shortcodes_TaxonomyCloud();
        echo LSD_Kses::full($shortcode->output($instance));

        // After Widget
        echo($args['after_widget'] ?? '');
    }

    public function form($instance)
    {
        $taxonomies = [
            LSD_Base::TAX_TAG => esc_html__('Tags', 'listdom'),
            LSD_Base::TAX_CATEGORY => esc_html__('Categories', 'listdom'),
            LSD_Base::TAX_LOCATION => esc_html__('Locations', 'listdom'),
            LSD_Base::TAX_FEATURE => esc_html__('Features', 'listdom'),
            LSD_Base::TAX_LABEL => esc_html__('Labels', 'listdom'),
        ];

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('title') . '">' . esc_html__('Title', 'listdom') . '</label>
            <input class="widefat" type="text" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . (isset($instance['title']) ? esc_attr($instance['title']) : '') . '" />
        </p>';

        echo '<p class="lsd-widget-row">
                <label for="' . $this->get_field_id('taxonomy') . '">' . esc_html__('Taxonomy', 'listdom') . '</label>
                <select class="widefat" name="' . $this->get_field_name('taxonomy') . '" id="' . $this->get_field_id('taxonomy') . '">';

        foreach ($taxonomies as $taxonomy => $label) echo '<option value="' . $taxonomy . '"' . (isset($instance['taxonomy']) && $instance['taxonomy'] == $taxonomy ? ' selected="selected"' : '') . '>' . $label . '</option>';
        echo '</select></p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('show_count') . '">' . esc_html__('Display Count', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('show_count') . '" id="' . $this->get_field_id('show_count') . '">
                <option value="0">' . esc_html__('No', 'listdom') . '</option>
                <option value="1"' . (isset($instance['show_count']) && $instance['show_count'] == 1 ? ' selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('hide_empty') . '">' . esc_html__('Hide Empty', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('hide_empty') . '" id="' . $this->get_field_id('hide_empty') . '">
                <option value="1">' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0"' . (isset($instance['hide_empty']) && $instance['hide_empty'] == 0 ? ' selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('orderby') . '">' . esc_html__('Sort By', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('orderby') . '" id="' . $this->get_field_id('orderby') . '">
                <option value="name">' . esc_html__('Name', 'listdom') . '</option>
                <option value="count"' . (isset($instance['orderby']) && $instance['orderby'] == 'count' ? ' selected="selected"' : '') . '>' . esc_html__('Count', 'listdom') . '</option>
            </select>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('limit') . '">' . esc_html__('Limit', 'listdom') . '</label>
            <input class="widefat" type="number" id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" value="' . (isset($instance['limit']) ? esc_attr($instance['limit']) : 24) . '" />
        </p>';
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = [];
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['taxonomy'] = isset($new_instance['taxonomy']) ? sanitize_text_field($new_instance['taxonomy']) : LSD_Base::TAX_TAG;
        $instance['show_count'] = isset($new_instance['show_count']) ? (int) $new_instance['show_count'] : 0;
        $instance['hide_empty'] = isset($new_instance['hide_empty']) ? (int) $new_instance['hide_empty'] : 1;
        $instance['orderby'] = isset($new_instance['orderby']) ? sanitize_text_field($new_instance['orderby']) : 'name';
        $instance['limit'] = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 24;

        return $instance;
    }
}
