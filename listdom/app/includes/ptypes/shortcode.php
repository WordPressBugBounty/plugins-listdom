<?php

class LSD_PTypes_Shortcode extends LSD_PTypes
{
    public $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_SHORTCODE;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'filter_columns']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'filter_columns_content'], 10, 2);

        add_action('restrict_manage_posts', [$this, 'skin_dropdown'], 10, 1);
        add_filter('parse_query', [$this, 'filter_by_skin']);

        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 10, 2);
        add_action('save_post', [$this, 'save'], 10, 2);

        // Duplicate Shortcode
        new LSD_Duplicate($this->PT);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Shortcodes', 'listdom'),
                'singular_name' => esc_html__('Shortcode', 'listdom'),
                'add_new' => esc_html__('Add Shortcode', 'listdom'),
                'add_new_item' => esc_html__('Add New Shortcode', 'listdom'),
                'edit_item' => esc_html__('Edit Shortcode', 'listdom'),
                'new_item' => esc_html__('New Shortcode', 'listdom'),
                'view_item' => esc_html__('View Shortcode', 'listdom'),
                'view_items' => esc_html__('View Shortcodes', 'listdom'),
                'search_items' => esc_html__('Search Shortcodes', 'listdom'),
                'not_found' => esc_html__('No shortcodes found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No shortcodes found in Trash!', 'listdom'),
                'all_items' => esc_html__('All Shortcodes', 'listdom'),
                'archives' => esc_html__('Shortcode Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'manage_options',
                'read_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'delete_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
            ],
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_shortcode_args', $args));
    }

    public function skin_dropdown($post_type)
    {
        if ($post_type !== $this->PT) return;

        $selected = isset($_GET['lsd_skin']) && $_GET['lsd_skin'] ? sanitize_text_field($_GET['lsd_skin']) : '';
        echo LSD_Form::skins([
            'id' => 'lsd_shortcode_filter_skin',
            'name' => 'lsd_skin',
            'value' => $selected,
            'empty_label' => esc_html__('All Skins', 'listdom'),
            'show_empty' => true,
        ]);
    }

    public function filter_by_skin($query)
    {
        global $pagenow, $typenow;

        if ($typenow === $this->PT && $pagenow == 'edit.php')
        {
            if (isset($_GET['lsd_skin']) && $_GET['lsd_skin'] !== '')
            {
                $query->query_vars['meta_query'] = [
                    [
                        'key' => 'lsd_skin',
                        'value' => sanitize_text_field($_GET['lsd_skin']),
                        'compare' => '=',
                    ],
                ];
            }
        }
    }

    public function filter_columns($columns)
    {
        // Move the date column to the end
        $date = $columns['date'];
        unset($columns['date']);

        $columns['shortcode'] = esc_html__('Shortcode', 'listdom');
        $columns['skin'] = esc_html__('Skin', 'listdom');
        $columns['date'] = $date;

        return $columns;
    }

    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'shortcode')
        {
            echo '[listdom id="' . esc_attr($post_id) . '"]';
        }
        else if ($column_name == 'skin')
        {
            $display = get_post_meta($post_id, 'lsd_display', true);
            echo is_array($display) && isset($display['skin']) ? '<strong>' . esc_html($display['skin']) . '</strong>' : '-----';
        }
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_shortcode', esc_html__('Shortcode', 'listdom'), [$this, 'metabox_shortcode'], $this->PT, 'side');
        add_meta_box('lsd_metabox_search', esc_html__('Search', 'listdom'), [$this, 'metabox_search'], $this->PT, 'side');
        add_meta_box('lsd_metabox_default_sort', esc_html__('Default Sort', 'listdom'), [$this, 'metabox_default_sort'], $this->PT, 'side');
        add_meta_box('lsd_metabox_sort_options', esc_html__('Sort Options', 'listdom'), [$this, 'metabox_sort_options'], $this->PT, 'side');
        add_meta_box('lsd_metabox_map_controls', esc_html__('Map Controls', 'listdom'), [$this, 'metabox_map_controls'], $this->PT, 'side');
        add_meta_box('lsd_metabox_display_options', esc_html__('Display Options', 'listdom'), [$this, 'metabox_display_options'], $this->PT, 'normal');
        add_meta_box('lsd_metabox_filter_options', esc_html__('Filter Options', 'listdom'), [$this, 'metabox_filter_options'], $this->PT, 'normal');
    }

    public function metabox_shortcode($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/shortcode.php', ['return_path' => true]);
    }

    public function metabox_search($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/search.php', ['return_path' => true]);
    }

    public function metabox_map_controls($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/map-controls.php', ['return_path' => true]);
    }

    public function metabox_default_sort($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/default-sort.php', ['return_path' => true]);
    }

    public function metabox_sort_options($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/sort-options.php', ['return_path' => true]);
    }

    public function metabox_display_options($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/display-options.php', ['return_path' => true]);
    }

    public function metabox_filter_options($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/shortcode/filter-options.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        // It's not a shortcode
        if ($post->post_type !== $this->PT) return;

        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) return;

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_shortcode_cpt')) return;

        // We don't need to do anything on post auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Listdom Data
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Display Options
        $display = $lsd['display'] ?? [];
        update_post_meta($post_id, 'lsd_display', $display);

        // Search Options
        $search = $lsd['search'] ?? [];
        update_post_meta($post_id, 'lsd_search', $search);

        // Skin
        update_post_meta($post_id, 'lsd_skin', $display['skin'] ?? '');

        // Filter and Exclude Options
        $filter = $lsd['filter'] ?? [];
        $exclude = $lsd['exclude'] ?? [];

        // Process each filter category
        foreach ($filter as $key => $filter_values)
        {
            if (isset($exclude[$key]) && is_array($filter_values))
                $filter[$key] = array_diff($filter_values, $exclude[$key]);
        }

        update_post_meta($post_id, 'lsd_filter', $filter);
        update_post_meta($post_id, 'lsd_exclude', $exclude);

        // Map Control Options
        $mapcontrols = $lsd['mapcontrols'] ?? [];
        update_post_meta($post_id, 'lsd_mapcontrols', $mapcontrols);

        // Sort Options
        $sorts = $lsd['sorts'] ?? [];
        update_post_meta($post_id, 'lsd_sorts', $sorts);
    }

    public function field_listing_link(string $skin, array $options)
    {
        $single_styles = ['' => esc_html__('Inherit From Global Options', 'listdom')] + LSD_Styles::details();
        $method = $options['listing_link'] ?? 'normal';
        ?>
        <div class="lsd-display-options-builder-option">
            <?php if ($this->isPro()): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                            'title' => esc_html__('Listing Link', 'listdom'),
                            'for' => 'lsd_display_options_skin_' . $skin . '_listing_link',
                        ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_' . $skin . '_listing_link',
                            'name' => 'lsd[display][' . $skin . '][listing_link]',
                            'value' => $method,
                            'options' => LSD_Base::get_listing_link_methods(),
                            'class' => 'lsd-do-listing-link',
                        ]); ?>
                        <p class="description"><?php esc_html_e("Link to single listing page.", 'listdom'); ?></p>
                    </div>
                </div>
                <div class="lsd-form-row <?php echo in_array($method, ['lightbox', 'right-panel', 'left-panel', 'bottom-panel']) ? '' : 'lsd-util-hide'; ?> lsd-do-listing-link-dependent lsd-do-listing-link-dependent-lightbox lsd-do-listing-link-dependent-right-panel lsd-do-listing-link-dependent-left-panel lsd-do-listing-link-dependent-bottom-panel">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Single Listing', 'listdom'),
                        'for' => 'lsd_display_options_skin_' . $skin . '_single_style',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_' . $skin . '_single_style',
                            'name' => 'lsd[display][' . $skin . '][single_style]',
                            'options' => $single_styles,
                            'value' => $options['single_style'] ?? '',
                        ]); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"></div>
                    <div class="lsd-col-6">
                        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link', 'listdom')); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
