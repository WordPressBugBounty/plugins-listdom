<?php

class LSD_Duplicate
{
    private $post_type;

    public function __construct($post_type)
    {
        $this->post_type = $post_type;

        // Add duplicate post links to the specified post type
        add_filter('post_row_actions', [$this, 'link'], 10, 2);
        add_filter("{$this->post_type}_row_actions", [$this, 'link'], 10, 2);

        // Register the duplication action
        add_action('admin_action_duplicate_post_as_draft', [$this, 'duplicate']);
    }

    /**
     * Add duplicate link to post row actions.
     * @param array $actions
     * @param WP_Post $post
     * @return array
     */
    public function link(array $actions, WP_Post $post): array
    {
        if (!$this->can_duplicate_post($post))
        {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action' => 'duplicate_post_as_draft',
                    'post' => $post->ID,
                ],
                'admin.php'
            ),
            basename(__FILE__),
            'lsd_duplicate_nonce'
        );

        $actions['lsd-duplicate'] = '<a href="' . esc_url($url) . '" title="' . esc_attr__('Duplicate This Item', 'listdom') . '" rel="permalink">' . esc_html__('Duplicate', 'listdom') . '</a>';
        return $actions;
    }

    /**
     * Duplicate a post as a draft.
     */
    public function duplicate()
    {
        if (
            empty($_GET['post'])
            || empty($_GET['lsd_duplicate_nonce'])
            || !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_GET['lsd_duplicate_nonce'])),
                basename(__FILE__)
            )
        )
        {
            return;
        }

        $post_id = absint(wp_unslash($_GET['post']));
        $post = get_post($post_id);

        if (!$post instanceof WP_Post)
        {
            return;
        }

        if ($post->post_type !== $this->post_type)
        {
            return;
        }

        // Copy the post and set its status to draft
        if ($this->can_duplicate_post($post))
        {
            $new_post = [
                'post_title' => $post->post_title . ' - Duplicate',
                'post_content' => $post->post_content,
                'post_status' => 'draft',
                'post_type' => $post->post_type,
                'post_author' => get_current_user_id(),
            ];

            $new_post_id = wp_insert_post($new_post, true);

            if (is_wp_error($new_post_id) || !$new_post_id)
            {
                LSD_Flash::add(esc_html__('Unable to duplicate this post.', 'listdom'), 'error');

                $url = $this->resolve_duplicate_redirect_url($post->post_type);
                wp_redirect($url);
                exit;
            }

            // Copy taxonomies and metadata
            $taxonomies = get_object_taxonomies($post->post_type);
            foreach ($taxonomies as $taxonomy)
            {
                $terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
                wp_set_object_terms($new_post_id, $terms, $taxonomy);
            }

            $meta_data = get_post_meta($post_id);
            foreach ($meta_data as $key => $values)
            {
                foreach ($values as $value)
                {
                    update_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }

            $edit_url = get_edit_post_link($new_post_id, '');

            if ($edit_url)
            {
                LSD_Flash::add(esc_html__('Post duplicated successfully. Now you can modify the duplicated post.', 'listdom'), 'success');
                $url = $edit_url;
            }
            else
            {
                LSD_Flash::add(esc_html__('Post duplicated successfully.', 'listdom'), 'success');
                $url = $this->resolve_duplicate_redirect_url($post->post_type);
            }
        }
        else
        {
            // Flash Message
            LSD_Flash::add(esc_html__("You don't have access to duplicate this post!", 'listdom'), 'error');

            $url = $this->resolve_duplicate_redirect_url($post->post_type);
        }

        wp_redirect($url);
        exit;
    }

    protected function resolve_duplicate_redirect_url(string $post_type): string
    {
        $url = wp_get_referer();
        if ($url) return $url;

        if ($post_type === 'post') return admin_url('edit.php');

        return admin_url('edit.php?post_type=' . $post_type);
    }

    protected function can_duplicate_post(WP_Post $post): bool
    {
        if ($post->post_type !== $this->post_type)
        {
            return false;
        }

        if (!current_user_can('edit_post', $post->ID))
        {
            return false;
        }

        $create_capability = $this->get_create_capability($post->post_type);

        return $create_capability !== '' && current_user_can($create_capability);
    }

    protected function get_create_capability(string $post_type): string
    {
        $post_type_object = get_post_type_object($post_type);

        if (
            $post_type_object
            && isset($post_type_object->cap)
            && isset($post_type_object->cap->create_posts)
            && is_string($post_type_object->cap->create_posts)
            && $post_type_object->cap->create_posts !== ''
        )
        {
            return $post_type_object->cap->create_posts;
        }

        if (
            $post_type_object
            && isset($post_type_object->cap)
            && isset($post_type_object->cap->edit_posts)
            && is_string($post_type_object->cap->edit_posts)
            && $post_type_object->cap->edit_posts !== ''
        )
        {
            return $post_type_object->cap->edit_posts;
        }

        return 'edit_posts';
    }
}
