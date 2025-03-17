<?php

class LSD_API_Controllers_Push extends LSD_API_Controller
{
    public function listing(WP_REST_Request $request): WP_REST_Response
    {
        $vars = $request->get_params();

        $tax = isset($vars['taxonomies']) && is_array($vars['taxonomies']) ? $vars['taxonomies'] : [];
        $attrs = isset($vars['attrs']) && is_array($vars['attrs']) ? $vars['attrs'] : [];
        $post_title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';
        $post_content = $vars['content'] ?? '';
        $post_excerpt = $vars['excerpt'] ?? '';

        // Listing Title is Required
        if (!trim($post_title)) return $this->response([
            'data' => new WP_Error('400', esc_html__("Listing title field is required!", 'listdom')),
            'status' => 400,
        ]);

        // Include Functions
        if (!function_exists('wp_create_term')) include ABSPATH . 'wp-admin/includes/taxonomy.php';
        if (!function_exists('post_exists')) include ABSPATH . 'wp-admin/includes/post.php';

        $post_id = isset($vars['id']) ? (int) $vars['id'] : null;

        // Post ID provided but not exists
        if ($post_id && !get_post($post_id)) $post_id = null;

        // Post ID not provided so search by title and type
        if (!$post_id && $exists = post_exists($post_title, '', '', LSD_Base::PTYPE_LISTING)) $post_id = $exists;

        // IX Class
        $ix = new LSD_IX();

        // Post Status
        $status = isset($vars['status']) ? sanitize_text_field($vars['status']) : 'pending';

        // Filter Listing Status
        $status = apply_filters('lsd_default_listing_status', $status, $vars);
        $status = apply_filters('lsd_api_listing_status', $status, $vars);

        // Create New / Update Listing
        $post = [
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_excerpt' => $post_excerpt,
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $status,
        ];

        // Create User
        if (isset($vars['user']) && is_email($vars['user']))
        {
            $post['post_author'] = LSD_User::create($vars['user']);
        }

        if ($post_id)
        {
            $post['ID'] = $post_id;
            $id = wp_update_post($post);
        }
        else $id = wp_insert_post($post);

        // Source Data
        $source_id = isset($vars['source_id']) ? (int) $vars['source_id'] : null;
        $source_type = isset($vars['source_type']) ? sanitize_text_field($vars['source_type']) : null;

        update_post_meta($id, 'lsd_source_id', $source_id);
        update_post_meta($id, 'lsd_source_type', $source_type);

        // Taxonomy Terms
        foreach ($tax as $taxonomy => $terms)
        {
            if (!in_array($taxonomy, [
                LSD_Base::TAX_CATEGORY,
                LSD_Base::TAX_TAG,
                LSD_Base::TAX_LOCATION,
                LSD_Base::TAX_FEATURE,
                LSD_Base::TAX_LABEL,
            ])) continue;

            $ids = [];
            foreach ($terms as $t)
            {
                // Term Parent
                $parent = $t['parent'] ?? '';
                $parent_id = 0;

                // Create Parent
                if (trim($parent))
                {
                    $p = wp_create_term($parent, $taxonomy);
                    if (is_array($p) && isset($p['term_id'])) $parent_id = $p['term_id'];
                }

                // Term Name
                $name = $t['name'] ?? '';
                $term = wp_create_term($name, $taxonomy);

                if (is_array($term) && isset($term['term_id']))
                {
                    $term_id = (int) $term['term_id'];

                    // Slug & Parent & Description
                    wp_update_term($term_id, $taxonomy, [
                        'slug' => $t['slug'] ?? '',
                        'parent' => $parent_id,
                        'description' => $t['description'] ?? '',
                    ]);

                    // Metadata
                    if (isset($t['meta']) && is_array($t['meta']) && count($t['meta']))
                    {
                        foreach ($t['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
                    }

                    $ids[] = $term_id;
                }
            }

            wp_set_post_terms($id, $ids, $taxonomy);
        }

        // Primary Category
        $primary_category = isset($vars['category']) ? sanitize_text_field($vars['category']) : '';
        if ($primary_category)
        {
            $term = get_term_by('name', $primary_category, LSD_Base::TAX_CATEGORY);

            // Primary Category Meta
            if (!is_wp_error($term))
            {
                update_post_meta($id, 'lsd_primary_category', $term->term_id);
                $vars['listing_category'] = $term->term_id;
            }
        }

        // Attributes
        if (count($attrs))
        {
            $attributes = [];
            foreach ($attrs as $attr)
            {
                $name = $attr['name'] ?? '';
                if (trim($name) === '') continue;

                $term = wp_create_term($name, LSD_Base::TAX_ATTRIBUTE);

                $term_id = is_array($term) && isset($term['term_id']) ? $term['term_id'] : null;
                if (!$term_id) continue;

                $value = $attr['value'] ?? '';
                $attributes[$term_id] = $value;

                $meta = $attr['meta'] ?? [];
                if (is_array($meta) && count($meta))
                {
                    foreach ($meta as $k => $v)
                    {
                        update_term_meta($term_id, $k, $v);
                    }
                }
            }

            $vars['attributes'] = $attributes;
        }
        else $vars['attributes'] = [];

        // Featured Image
        $featured_image = isset($vars['featured_image']) ? sanitize_url($vars['featured_image']) : '';
        if ($featured_image)
        {
            $image_id = $ix->attach($featured_image);
            if ($image_id) set_post_thumbnail($id, $image_id);
        }
        else delete_post_thumbnail($id);

        // Gallery
        $gallery = isset($vars['gallery']) && is_array($vars['gallery']) ? $vars['gallery'] : [];
        if (count($gallery))
        {
            $images = [];
            foreach ($gallery as $image)
            {
                $image_id = $ix->attach($image);
                if ($image_id) $images[] = $image_id;
            }

            update_post_meta($id, 'lsd_gallery', $images);
            $vars['gallery'] = $images;
        }
        else update_post_meta($id, 'lsd_gallery', []);

        // Publish Listing
        if ($status === 'publish' && get_post_status($id) !== 'published') wp_publish_post($id);

        // Sanitization
        array_walk_recursive($vars, 'sanitize_text_field');

        // Save the Data
        $entity = new LSD_Entity_Listing($id);
        $entity->save($vars);

        // Metadata
        if (isset($vars['meta']) && is_array($vars['meta']))
        {
            foreach ($vars['meta'] as $k => $v) update_post_meta($id, $k, $v);
        }

        if ($status === 'publish') $message = esc_html__('The listing published.', 'listdom');
        else $message = esc_html__('The listing submitted. It will publish as soon as possible.', 'listdom');

        // Trigger Action
        do_action('lsd_api_listing_created', $id, $request);
        do_action('lsd_api_listing_pushed', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'message' => $message,
                'listing' => [
                    'id' => $id,
                ],
            ],
            'status' => 200,
        ]);
    }
}
