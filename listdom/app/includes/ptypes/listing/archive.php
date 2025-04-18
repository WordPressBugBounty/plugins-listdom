<?php

class LSD_PTypes_Listing_Archive extends LSD_PTypes_Listing
{
    protected $entity;

    public function hooks()
    {
        add_action('lsd_listings', [$this, 'do_listings'], 10, 3);
    }

    public function unhook()
    {
        remove_action('lsd_listings', [$this, 'do_listings']);
    }

    public function render_map_objects($post_ids = [], $args = [])
    {
        if (!is_array($post_ids)) return false;

        // Marker onclick method
        $onclick = isset($args['onclick']) ? sanitize_text_field($args['onclick']) : 'infowindow';

        // Sidebar
        $sidebar = isset($args['sidebar']) && $args['sidebar'];

        // Ignore Map Exclusion
        $ignore_map_exclusion = isset($args['ignore_map_exclusion']) && $args['ignore_map_exclusion'];

        $objects = [];
        foreach ($post_ids as $post_id)
        {
            // Listing
            $entity = new LSD_Entity_Listing($post_id);

            // Set Current Post
            LSD_LifeCycle::post($post_id);

            // Include Listing?
            if (!$ignore_map_exclusion && !apply_filters('lsd_map_include_object', true, $entity)) continue;

            $object_type = get_post_meta($post_id, 'lsd_object_type', true);
            if ($object_type === 'marker')
            {
                $object = [];
                $object['type'] = 'marker';
                $object['marker'] = $entity->get_marker();
            }
            else
            {
                $shape_type = get_post_meta($post_id, 'lsd_shape_type', true);
                $shape_paths = get_post_meta($post_id, 'lsd_shape_paths', true);
                $shape_radius = get_post_meta($post_id, 'lsd_shape_radius', true);

                // Get Shape
                $object = LSD_Shape::get([
                    'type' => $shape_type,
                    'paths' => $shape_paths,
                    'radius' => $shape_radius,
                ]);

                $object['marker'] = null;
                $object['fill_color'] = $entity->get_shape_fill_color();
                $object['fill_opacity'] = $entity->get_shape_fill_opacity();
                $object['stroke_color'] = $entity->get_shape_stroke_color();
                $object['stroke_opacity'] = $entity->get_shape_stroke_opacity();
                $object['stroke_weight'] = $entity->get_shape_stroke_weight();
            }

            $object['id'] = $post_id;
            $object['infowindow'] = $onclick === 'infowindow' ? $entity->get_infowindow() : null;
            $object['card'] = $sidebar ? $entity->get_map_card() : null;
            $object['latitude'] = (float) get_post_meta($post_id, 'lsd_latitude', true);
            $object['longitude'] = (float) get_post_meta($post_id, 'lsd_longitude', true);
            $object['onclick'] = $onclick;
            $object['link'] = $onclick === 'redirect' ? get_the_permalink($post_id) : null;
            $object['raw'] = $onclick === 'lightbox' ? trim(get_the_permalink($post_id), '/ ') . '/raw' : null;

            // It's required
            $object['lsd'] = [];

            $objects[] = $object;
        }

        // Back to Original Post
        LSD_LifeCycle::reset();

        return $objects;
    }

    public function do_listings($ids = [], $column = 'lsd-col-12', $columns = null)
    {
        include lsd_template('listings.php');
    }
}
