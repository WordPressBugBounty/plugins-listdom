<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Widgets SimpleMap Class.
 *
 * @class LSD_Widgets_SimpleMap
 * @version    1.0.0
 */
class LSD_Widgets_SimpleMap extends WP_Widget
{
    public $LSD;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('LSD_Widgets_SimpleMap', esc_html__('(Listdom) Simple Map', 'listdom'), ['description' => esc_html__('Show a certain address on map.', 'listdom')]);

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

        $address = $instance['address'] ?? '';
        $map_provider = $instance['map_provider'] ?? 'leaflet';
        $style = $instance['style'] ?? '';
        $name = $instance['name'] ?? '';
        $zoomlevel = $instance['zoomlevel'] ?? 14;

        // Print the shortcode output
        echo do_shortcode('[listdom_simplemap address="' . esc_attr($address) . '" provider="' . esc_attr($map_provider) . '" style="' . esc_attr($style) . '" title="' . esc_attr($name) . '" zoomlevel="' . esc_attr($zoomlevel) . '"]');

        // After Widget
        echo($args['after_widget'] ?? '');
    }

    public function form($instance)
    {
        $provider = $instance['map_provider'] ?? 'leaflet';

        echo '<div id="' . $this->get_field_id('lsd_wrapper') . '">';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('title') . '">' . esc_html__('Title', 'listdom') . '</label>
            <input class="widefat" type="text" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . (isset($instance['title']) ? esc_attr($instance['title']) : '') . '" />
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('address') . '">' . esc_html__('Address', 'listdom') . '</label>
            <input class="widefat" type="text" id="' . $this->get_field_id('address') . '" name="' . $this->get_field_name('address') . '" value="' . (isset($instance['address']) ? esc_attr($instance['address']) : '') . '" />
            <p class="description">' . esc_html__('The address that you want to show on the map!', 'listdom') . '</p>
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('map_provider') . '">' . esc_html__('Map Provider', 'listdom') . '</label>
            ' . LSD_Form::providers([
                'id' => $this->get_field_id('map_provider'),
                'name' => $this->get_field_name('map_provider'),
                'class' => 'widefat lsd-map-provider-toggle',
                'value' => $provider,
                'attributes' => [
                    'data-parent' => '#' . $this->get_field_id('lsd_wrapper'),
                ],
            ]) . '
        </p>';

        echo '<p class="lsd-widget-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="' . $this->get_field_id('style') . '">' . esc_html__('Style', 'listdom') . '</label>
            ' . LSD_Form::mapstyle([
                'id' => $this->get_field_id('style'),
                'name' => $this->get_field_name('style'),
                'class' => 'widefat',
                'value' => $instance['style'] ?? '',
            ]) . '
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('name') . '">' . esc_html__('Location Name', 'listdom') . '</label>
            <input class="widefat" type="text" id="' . $this->get_field_id('name') . '" name="' . $this->get_field_name('name') . '" value="' . (isset($instance['name']) ? esc_attr($instance['name']) : '') . '" placeholder="' . esc_attr__("Our Office", 'listdom') . '" />
        </p>';

        echo '<p class="lsd-widget-row">
            <label for="' . $this->get_field_id('zoomlevel') . '">' . esc_html__('Map Zoom Level', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => $this->get_field_id('zoomlevel'),
                'name' => $this->get_field_name('zoomlevel'),
                'class' => 'widefat',
                'value' => $instance['zoomlevel'] ?? 14,
                'options' => [
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8,
                    9 => 9,
                    10 => 10,
                    11 => 11,
                    12 => 12,
                    13 => 13,
                    14 => 14,
                    15 => 15,
                    16 => 16,
                    17 => 17,
                ],
            ]) . '
        </p>';

        echo '</div>';
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = [];
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['address'] = isset($new_instance['address']) ? sanitize_text_field($new_instance['address']) : '';
        $instance['map_provider'] = isset($new_instance['map_provider']) ? sanitize_text_field($new_instance['map_provider']) : 'leaflet';
        $instance['style'] = isset($new_instance['style']) ? sanitize_text_field($new_instance['style']) : '';
        $instance['name'] = isset($new_instance['name']) ? sanitize_text_field($new_instance['name']) : '';
        $instance['zoomlevel'] = isset($new_instance['zoomlevel']) ? (int) $new_instance['zoomlevel'] : 14;

        return $instance;
    }
}
