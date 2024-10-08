<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Widgets Search Class.
 *
 * @class LSD_Widgets_Search
 * @version    1.0.0
 */
class LSD_Widgets_Search extends WP_Widget
{
    public $LSD;
    public $instance;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('LSD_Widgets_Search', esc_html__('(Listdom) Search', 'listdom'), ['description' => esc_html__('A search form to filter listings!', 'listdom')]);

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

        // Widget Instance
        $this->instance = $instance;

        // Search Form
        ob_start();
        include lsd_template('widgets/search.php');
        echo ob_get_clean();

        // After Widget
        echo($args['after_widget'] ?? '');
    }

    public function form($instance)
    {
        echo '<div id="' . $this->get_field_id('lsd_wrapper') . '">';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('title') . '">' . esc_html__('Title', 'listdom') . '</label>
            <input class="widefat" type="text" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . (isset($instance['title']) ? esc_attr($instance['title']) : '') . '" />
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('search_id') . '">' . esc_html__('Search Form', 'listdom') . '</label>
            ' . LSD_Form::searches([
                'id' => $this->get_field_id('search_id'),
                'name' => $this->get_field_name('search_id'),
                'value' => (isset($instance['search_id']) and $instance['search_id']) ? $instance['search_id'] : null,
                'class' => 'widefat',
            ]) . '
        </p>';

        echo '</div>';
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = [];
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['search_id'] = isset($new_instance['search_id']) ? (int) $new_instance['search_id'] : '';

        return $instance;
    }
}
