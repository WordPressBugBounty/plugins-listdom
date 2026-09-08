<?php

class LSD_Statuses extends LSD_Base
{
    protected $statuses;
    protected $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_LISTING;
    }

    public function init()
    {
        add_action('init', function ()
        {
            // Keep previously stored Listdom statuses queryable when the addon
            // that normally registers them is temporarily deactivated.
            $fallback_statuses = [
                'offline' => __('Offline', 'listdom'),
                'hold' => __('On Hold', 'listdom'),
                'on-hold' => __('On Hold', 'listdom'),
                'expired' => __('Expired', 'listdom'),
                'inactive' => __('Inactive', 'listdom'),
                'denied' => __('Denied', 'listdom'),
            ];
            foreach ($fallback_statuses as $key => $label)
            {
                if (!get_post_status_object($key)) register_post_status($key, [
                    'label' => $label,
                    'public' => false,
                    'internal' => false,
                    'protected' => true,
                    'show_in_admin_status_list' => false,
                    'show_in_admin_all_list' => false,
                ]);
            }

            $statuses = $this->statuses();
            foreach ($statuses as $key => $params) register_post_status($key, $params['args']);
        });

        add_action('admin_footer-post.php', function ()
        {
            global $post;

            $statuses = $this->statuses();
            foreach ($statuses as $key => $params)
            {
                if ($post->post_type !== $this->PT) continue;

                $selected = '';
                $label = '';
                $label_text = '';
                $action = $params['args']['label'] ?? '';
                $applied = $params['applied'] ?? '';

                if ($post->post_status === $key)
                {
                    $selected = ' selected="selected"';
                    $label = '<span id="post-status-display">' . $applied . '</span> ';
                    $label_text = $applied . ' ';
                }

                echo "<script>
                jQuery(document).ready(function($)
                {
                    const display = $('#post-status-display'); 
                    $('select#post_status').append('<option value=\"$key\"$selected>$action</option>');
                    
                    " . (!$selected ? "" : "
                    if(display.length) display.html('$label_text');
                    else $('.misc-pub-section .edit-post-status').before('$label');
                    ") . "
                });
                </script>";
            }
        });

        add_action('admin_footer-edit.php', function ()
        {
            global $post;
            if (!$post) return;

            $statuses = $this->statuses();
            foreach ($statuses as $key => $params)
            {
                if ($post->post_type !== $this->PT) continue;

                $action = $params['args']['label'] ?? '';

                echo "<script>
                jQuery(document).ready(function($)
                {
                    $('.inline-edit-status select').append('<option value=\"$key\">$action</option>');
                });
                </script>";
            }
        });

        add_filter('display_post_states', function ($states)
        {
            global $post;
            if (!$post) return $states;

            $statuses = $this->statuses();
            foreach ($statuses as $key => $params)
            {
                $status = get_query_var('post_status');
                $applied = $params['applied'] ?? '';

                if ($status !== $key && isset($post->post_status) && $post->post_status === $key) return [$applied];
            }

            return $states;
        });

        // Filter All Posts
        add_action('pre_get_posts', [$this, 'filter']);
    }

    public function filter(WP_Query $query)
    {
        if (!is_admin() || $query->get('post_type') !== $this->PT || !$query->is_main_query()) return;

        $post_status = isset($_GET['post_status']) && trim($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : '';
        if (!$post_status || $post_status === 'all')
        {
            $all = ['publish', 'private', 'future', 'draft', 'pending', 'offline', 'hold', 'on-hold', 'expired', 'inactive', 'denied'];

            $statuses = $this->statuses();
            foreach ($statuses as $key => $status) $all[] = $key;

            $query->set('post_status', $all);
        }
    }

    public function statuses()
    {
        return apply_filters('lsd_statuses', []);
    }
}
