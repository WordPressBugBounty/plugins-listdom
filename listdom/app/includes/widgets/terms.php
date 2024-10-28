<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Widgets Terms Class.
 *
 * @class LSD_Widgets_Terms
 * @version    1.0.0
 */
class LSD_Widgets_Terms extends WP_Widget
{
    public $LSD;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('LSD_Widgets_Terms', esc_html__('(Listdom) Terms', 'listdom'), [
            'description' => esc_html__('Put list or dropdown of Listdom taxonomies into a sidebar/widget area. Works exactly like WordPress Categories widget.', 'listdom'),
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
        $shortcode = new LSD_Shortcodes_Terms();
        echo '<div class="lsd-taxonomy-shortcode-wrapper lsd-taxonomy-shortcode-terms lsd-font-m">' . $shortcode->output($instance) . '</div>';

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
            <label for="' . $this->get_field_id('dropdown') . '">' . esc_html__('Display as Dropdown', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('dropdown') . '" id="' . $this->get_field_id('dropdown') . '">
                <option value="0">' . esc_html__('No', 'listdom') . '</option>
                <option value="1"' . (isset($instance['dropdown']) && $instance['dropdown'] == 1 ? ' selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('show_count') . '">' . esc_html__('Display Count', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('show_count') . '" id="' . $this->get_field_id('show_count') . '">
                <option value="0">' . esc_html__('No', 'listdom') . '</option>
                <option value="1"' . (isset($instance['show_count']) && $instance['show_count'] == 1 ? ' selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('hierarchical') . '">' . esc_html__('Hierarchical', 'listdom') . '</label>
            <select class="widefat" name="' . $this->get_field_name('hierarchical') . '" id="' . $this->get_field_id('hierarchical') . '">
                <option value="0">' . esc_html__('No', 'listdom') . '</option>
                <option value="1"' . (isset($instance['hierarchical']) && $instance['hierarchical'] == 1 ? ' selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </p>';
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = [];
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['taxonomy'] = isset($new_instance['taxonomy']) ? sanitize_text_field($new_instance['taxonomy']) : LSD_Base::TAX_TAG;
        $instance['show_count'] = isset($new_instance['show_count']) ? (int) $new_instance['show_count'] : 0;
        $instance['dropdown'] = isset($new_instance['dropdown']) ? (int) $new_instance['dropdown'] : 0;
        $instance['hierarchical'] = isset($new_instance['hierarchical']) ? (int) $new_instance['hierarchical'] : 0;

        return $instance;
    }
}
