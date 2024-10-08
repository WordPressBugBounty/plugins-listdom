<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Duplicate Data Class.
 *
 * @class LSD_Duplicate
 * @version 1.0.0
 */
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
        if (!current_user_can('edit_posts'))
        {
            return $actions;
        }

        // Ensure that the link is only added for the specified post type
        if ($post->post_type !== $this->post_type)
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
        if (empty($_GET['post']) || empty($_GET['lsd_duplicate_nonce']) || !wp_verify_nonce($_GET['lsd_duplicate_nonce'], basename(__FILE__)))
        {
            return;
        }

        $post_id = absint($_GET['post']);
        $post = get_post($post_id);

        // Copy the post and set its status to draft
        if ($post && current_user_can('edit_posts'))
        {
            $new_post = [
                'post_title' => $post->post_title . ' - Duplicate',
                'post_content' => $post->post_content,
                'post_status' => 'draft',
                'post_type' => $post->post_type,
                'post_author' => get_current_user_id(),
            ];

            $new_post_id = wp_insert_post($new_post);

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

            // Flash Message
            LSD_Flash::add(esc_html__('Post duplicated successfully. Now you can modify the duplicated post.', 'listdom'), 'success');

            $url = admin_url('post.php?action=edit&post=' . $new_post_id);
        }
        else
        {
            // Flash Message
            LSD_Flash::add(esc_html__("You don't have access to duplicate this post!", 'listdom'), 'success');

            $url = wp_get_referer();
            if (!$url) $url = admin_url();
        }

        wp_redirect($url);
        exit;
    }
}
