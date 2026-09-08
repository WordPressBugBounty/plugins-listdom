<?php

class LSD_Dashboard_Promotions extends LSD_Base
{
    public const MODE = 'promotions';
    public const TOPUP_ACCENT = '#32b7ff';
    public const TOPUP_RECORDS_PER_PAGE = 10;
    public const POPUP_LISTINGS_PER_PAGE = 10;

    protected ?LSD_Dashboard_Payments $payments_dashboard = null;
    protected bool $rendering_popup_listing_items = false;
    protected string $rendering_popup_listing_mode = '';
    protected ?array $rendering_popup_category_child_ids = null;
    protected array $rendering_popup_category_root_ids = [];
    protected ?array $rendering_popup_search_child_ids = null;
    protected array $rendering_popup_search_root_ids = [];

    public function init()
    {
        add_filter('lsd_dashboard_menus', [$this, 'menu'], 15, 2);
        add_filter('lsd_dashboard_modes', [$this, 'dashboard'], 15, 2);
        add_action('wp_ajax_lsd_dashboard_promotions_checkout', [$this, 'checkout']);
        add_action('wp_ajax_lsd_dashboard_promotions_topup_checkout', [$this, 'topup_checkout']);
        add_action('wp_ajax_lsd_dashboard_promotions_remove_label', [$this, 'remove_label']);
        add_action('wp_ajax_lsd_dashboard_promotions_remove_topup', [$this, 'remove_topup']);
        add_action('wp_ajax_lsd_dashboard_promotions_listings', [$this, 'listings']);
        add_action('wp_ajax_lsd_dashboard_promotions_topup_records', [$this, 'topup_records']);
        add_filter('lsd_dashboard_listing_selection_control', [$this, 'listing_selection_control'], 10, 2);
        add_filter('lsd_dashboard_allowed_children', [$this, 'popup_allowed_children'], 10, 3);
    }

    public function menu(array $menus, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!$this->is_available() || !get_current_user_id()) return $menus;

        $logout = $menus['logout'] ?? null;
        if ($logout) unset($menus['logout']);

        $menus[self::MODE] = [
            'label' => esc_html__('Promotions', 'listdom'),
            'default_label' => esc_html__('Promotions', 'listdom'),
            'id' => 'lsd_dashboard_menus_promotions',
            'url' => $dashboard->add_qs_var('mode', self::MODE, $dashboard->url),
            'icon' => 'fa-solid fa-angles-up',
        ];

        if ($logout) $menus['logout'] = $logout;

        return $menus;
    }

    public function dashboard(string $output, LSD_Shortcodes_Dashboard $dashboard): string
    {
        if ($dashboard->mode !== self::MODE) return $output;
        if (!get_current_user_id()) return $dashboard->auth();

        ob_start();
        include lsd_template('dashboard/promotions.php');
        return ob_get_clean();
    }

    public function is_available(): bool
    {
        return $this->is_labelize_available() || $this->is_topup_available();
    }

    public function is_topup_available(): bool
    {
        return class_exists('\LSDPACTUP\Topup') && $this->is_toolkit_addon_enabled('topup');
    }

    public function is_labelize_available(): bool
    {
        return class_exists('\LSDPACLBL\Addon') && $this->is_toolkit_addon_enabled('labelize');
    }

    public function get_available_labels(): array
    {
        if (!$this->is_labelize_available()) return [];

        $terms = get_terms([
            'taxonomy' => self::TAX_LABEL,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
            'fields' => 'ids',
        ]);

        if (is_wp_error($terms) || !is_array($terms)) return [];

        $payment = lsd_payment();
        $label_ids = [];

        foreach ($terms as $term_id)
        {
            $term_id = (int) $term_id;
            if ($term_id < 1) continue;

            $product_id = (int) get_term_meta($term_id, 'lsd_product', true);
            if ($product_id < 1) continue;

            $product = $payment->plan($product_id);
            if (!$payment->plan_supported($product_id) || !\LSDPACLBL\Addon::is_valid_product($product)) continue;

            $label_ids[] = $term_id;
        }

        $labels = [];
        foreach (LSD_Taxonomies::get_labels_data($label_ids) as $label)
        {
            $labels[] = array_merge($label, [
                'search' => strtolower((string) ($label['name'] ?? '')),
                'chip_style' => LSD_Taxonomies::label_chip_style($label),
            ]);
        }

        return $labels;
    }

    public function get_topup_product_data(): array
    {
        if (!$this->is_topup_available()) return ['id' => 0, 'product' => null, 'is_valid' => false,];

        $topup = new \LSDPACTUP\Topup();
        $product = $topup->get_product();

        return ['id' => $topup->get_product_id(), 'product' => $product, 'is_valid' => $topup->is_valid_product($product),];
    }

    public function get_user_listings(int $user_id, bool $respect_dashboard_hierarchy = false): array
    {
        if ($user_id < 1) return [];

        $query = [
            'post_type' => self::PTYPE_LISTING,
            'post_status' => ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!current_user_can('edit_others_posts')) $query['author'] = $user_id;
        if ($respect_dashboard_hierarchy) $query = apply_filters('lsd_dashboard_manage_query', $query);

        $listings = get_posts($query);
        return is_array($listings) ? $listings : [];
    }

    public function get_popup_listings(int $user_id, LSD_Shortcodes_Dashboard $dashboard, string $mode = ''): array
    {
        $listings = [];
        $mode = sanitize_key($mode);
        $topup_access = $mode === 'topup' && class_exists('\LSDPACTUP\Access') ? new \LSDPACTUP\Access() : null;

        foreach ($this->get_user_listings($user_id, true) as $listing)
        {
            if (!$listing instanceof WP_Post) continue;
            if ($topup_access instanceof \LSDPACTUP\Access && $topup_access->is_active((int) $listing->ID)) continue;

            $title = get_the_title($listing->ID);
            $detail_parts = $dashboard->get_listing_detail_parts($listing);
            $details = count($detail_parts) ? implode(' • ', $detail_parts) : '';
            $category = $dashboard->get_listing_primary_category_name($listing);

            $listings[] = [
                'id' => (int) $listing->ID,
                'title' => $title,
                'details' => $details,
                'category' => $category,
                'category_id' => $this->get_listing_primary_category_id($listing->ID),
                'status' => $dashboard->get_listing_status_data($listing->post_status),
                'badges' => $dashboard->get_listing_badges($listing),
                'search' => strtolower(trim($title . ' ' . $details . ' ' . $category)),
                'permalink' => get_permalink($listing->ID),
            ];
        }

        return $listings;
    }

    public function get_popup_listings_page(int $user_id, LSD_Shortcodes_Dashboard $dashboard, int $page = 1, int $per_page = 2, string $search = '', int $category_id = 0, string $mode = ''): array
    {
        $page = max(1, $page);
        $per_page = max(1, $per_page);
        $search = strtolower(trim($search));
        $this->rendering_popup_category_child_ids = null;
        $this->rendering_popup_category_root_ids = [];
        $this->rendering_popup_search_child_ids = null;
        $this->rendering_popup_search_root_ids = [];

        $query = [
            'post_type' => self::PTYPE_LISTING,
            'post_status' => ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED],
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!current_user_can('edit_others_posts')) $query['author'] = $user_id;
        $query = apply_filters('lsd_dashboard_manage_query', $query);

        // Top-ups take effect immediately, so only published listings can use them.
        if (sanitize_key($mode) === 'topup') $query['post_status'] = ['publish'];

        if ($search !== '')
        {
            $matching_ids = $this->get_popup_listing_search_ids($search);
            $matching_ids = $this->get_popup_listing_query_ids($matching_ids);
            if (!count($matching_ids)) return [
                'items' => [],
                'count' => 0,
                'total' => 0,
                'has_more' => false,
                'next_page' => 0,
            ];

            $query['post__in'] = $matching_ids;
        }
        if ($category_id > 0 && class_exists('\\LSDPACFS\\Dashboard'))
        {
            $category_data = $this->get_popup_category_listing_ids($category_id, $user_id);
            if (!count($category_data['roots'])) return ['items' => [], 'count' => 0, 'total' => 0, 'has_more' => false, 'next_page' => 0];

            $this->rendering_popup_category_child_ids = $category_data['children'];
            $this->rendering_popup_category_root_ids = $category_data['direct_roots'];
            $query['post__in'] = isset($query['post__in'])
                ? array_values(array_intersect($query['post__in'], $category_data['roots']))
                : $category_data['roots'];
            if (!count($query['post__in'])) return ['items' => [], 'count' => 0, 'total' => 0, 'has_more' => false, 'next_page' => 0];
        }
        else if ($category_id > 0)
        {
            if (!isset($query['tax_query'])) $query['tax_query'] = [];

            $query['tax_query'][] = [
                'taxonomy' => self::TAX_CATEGORY,
                'field' => 'term_id',
                'terms' => [$category_id],
            ];
        }

        if ($this->rendering_popup_category_child_ids !== null
            && $this->rendering_popup_search_child_ids !== null)
        {
            $query['post__in'] = array_values(array_intersect(
                $query['post__in'] ?? [],
                $this->get_popup_combined_root_ids()
            ));

            if (!count($query['post__in'])) return ['items' => [], 'count' => 0, 'total' => 0, 'has_more' => false, 'next_page' => 0];
        }

        if (sanitize_key($mode) === 'topup')
        {
            $active_topup_ids = $this->get_active_topup_listing_ids($user_id);
            $excluded_topup_ids = $this->get_popup_topup_excluded_ids($active_topup_ids);
            if (count($excluded_topup_ids)) $query['post__not_in'] = $excluded_topup_ids;
        }

        $listings_query = new WP_Query($query);
        $items = [];

        foreach ($listings_query->posts as $listing)
        {
            if (!$listing instanceof WP_Post) continue;

            $title = get_the_title($listing->ID);
            $detail_parts = $dashboard->get_listing_detail_parts($listing);
            $details = count($detail_parts) ? implode(' • ', $detail_parts) : '';
            $category = $dashboard->get_listing_primary_category_name($listing);

            $items[] = [
                'id' => (int) $listing->ID,
                'title' => $title,
                'details' => $details,
                'category' => $category,
                'category_id' => $this->get_listing_primary_category_id($listing->ID),
                'status' => $dashboard->get_listing_status_data($listing->post_status),
                'badges' => $dashboard->get_listing_badges($listing),
                'search' => strtolower(trim($title . ' ' . $details . ' ' . $category)),
                'permalink' => get_permalink($listing->ID),
            ];
        }

        $total = (int) $listings_query->found_posts;

        return [
            'items' => $items,
            'count' => count($items),
            'total' => $total,
            'has_more' => $page < (int) $listings_query->max_num_pages,
            'next_page' => $page < (int) $listings_query->max_num_pages ? $page + 1 : 0,
        ];
    }

    protected function get_popup_category_listing_ids(int $category_id, int $user_id): array
    {
        $args = [
            'post_type' => self::PTYPE_LISTING,
            'post_status' => ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
            'tax_query' => [[
                'taxonomy' => self::TAX_CATEGORY,
                'field' => 'term_id',
                'terms' => [$category_id],
            ]],
        ];
        if (!current_user_can('edit_others_posts')) $args['author'] = $user_id;

        $roots = [];
        $children = [];
        $direct_roots = [];
        foreach (get_posts($args) as $listing_id)
        {
            $listing_id = (int) $listing_id;
            if ($listing_id < 1) continue;

            $parent_id = (int) get_post_meta($listing_id, 'lsd_parent', true);
            $root_id = $parent_id > 0 ? $parent_id : $listing_id;
            $roots[] = $root_id;
            if ($parent_id > 0) $children[] = $listing_id;
            else $direct_roots[] = $listing_id;
        }

        return [
            'roots' => array_values(array_unique($roots)),
            'children' => array_values(array_unique($children)),
            'direct_roots' => array_values(array_unique($direct_roots)),
        ];
    }

    protected function get_popup_listing_search_ids(string $search): array
    {
        $search_like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
        $db = new LSD_DB();
        $sql = $db->prepare("SELECT DISTINCT p.ID
            FROM `#__posts` AS p
            WHERE (
                p.post_title LIKE %s
                OR EXISTS (
                    SELECT 1
                    FROM `#__term_relationships` AS category_relationship
                    INNER JOIN `#__term_taxonomy` AS category_taxonomy ON category_taxonomy.term_taxonomy_id = category_relationship.term_taxonomy_id
                    INNER JOIN `#__terms` AS category ON category.term_id = category_taxonomy.term_id
                    WHERE category_relationship.object_id = p.ID
                      AND category_taxonomy.taxonomy = %s
                      AND category.name LIKE %s
                )
                OR EXISTS (
                    SELECT 1
                    FROM `#__postmeta` AS package_meta
                    INNER JOIN `#__posts` AS package ON package.ID = CAST(package_meta.meta_value AS UNSIGNED)
                    WHERE package_meta.post_id = p.ID
                      AND package_meta.meta_key = %s
                      AND package.post_title LIKE %s
                )
                OR EXISTS (
                    SELECT 1
                    FROM `#__postmeta` AS subscription_meta
                    INNER JOIN `#__postmeta` AS subscription_package_meta ON subscription_package_meta.post_id = CAST(subscription_meta.meta_value AS UNSIGNED)
                    INNER JOIN `#__posts` AS subscription_package ON subscription_package.ID = CAST(subscription_package_meta.meta_value AS UNSIGNED)
                    WHERE subscription_meta.post_id = p.ID
                      AND subscription_meta.meta_key = %s
                      AND subscription_package_meta.meta_key = %s
                      AND subscription_package.post_title LIKE %s
                )
            )", [
            $search_like,
            self::TAX_CATEGORY,
            $search_like,
            'lsd_package',
            $search_like,
            'lsd_subscription',
            'lsd_package',
            $search_like,
        ]);

        return array_values(array_unique(array_map('absint', (array) $db->select($sql, 'loadColumn'))));
    }

    protected function get_popup_listing_query_ids(array $listing_ids): array
    {
        if (!class_exists('\\LSDPACFS\\Dashboard')) return $listing_ids;

        // The Franchise dashboard queries roots and renders matching children below them.
        $query_ids = [];
        $search_child_ids = [];
        $search_root_ids = [];
        foreach ($listing_ids as $listing_id)
        {
            $listing_id = absint($listing_id);
            if ($listing_id < 1) continue;

            $parent_id = absint(get_post_meta($listing_id, 'lsd_parent', true));
            if ($parent_id > 0)
            {
                $query_ids[] = $parent_id;
                $search_child_ids[] = $listing_id;
            }
            else
            {
                $query_ids[] = $listing_id;
                $search_root_ids[] = $listing_id;
            }
        }

        $this->rendering_popup_search_child_ids = array_values(array_unique($search_child_ids));
        $this->rendering_popup_search_root_ids = array_values(array_unique($search_root_ids));

        return array_values(array_unique($query_ids));
    }

    protected function get_popup_combined_root_ids(): array
    {
        $root_ids = array_intersect($this->rendering_popup_category_root_ids, $this->rendering_popup_search_root_ids);
        $matching_child_ids = array_intersect($this->rendering_popup_category_child_ids ?? [], $this->rendering_popup_search_child_ids ?? []);

        foreach ($matching_child_ids as $child_id)
        {
            $parent_id = absint(get_post_meta((int) $child_id, 'lsd_parent', true));
            if ($parent_id > 0) $root_ids[] = $parent_id;
        }

        return array_values(array_unique(array_map('absint', $root_ids)));
    }

    protected function get_popup_topup_excluded_ids(array $active_topup_ids): array
    {
        if (!class_exists('\\LSDPACFS\\Dashboard')) return $active_topup_ids;
        if (!class_exists('\\LSDPACTUP\\Access')) return $active_topup_ids;

        $access = new \LSDPACTUP\Access();
        $excluded_ids = [];

        foreach ($active_topup_ids as $listing_id)
        {
            $listing_id = absint($listing_id);
            if ($listing_id < 1 || absint(get_post_meta($listing_id, 'lsd_parent', true)) > 0) continue;

            // Keep active parents in the query while an eligible inactive child can still be promoted.
            $has_inactive_child = false;
            $listing = get_post($listing_id);
            $child_ids = $listing instanceof WP_Post ? $this->get_popup_filtered_child_ids($listing) : [];
            foreach ($child_ids as $child_id)
            {
                if (!$access->is_active($child_id))
                {
                    $has_inactive_child = true;
                    break;
                }
            }

            if (!$has_inactive_child) $excluded_ids[] = $listing_id;
        }

        return array_values(array_unique($excluded_ids));
    }

    public function get_popup_listing_categories(): array
    {
        $categories = get_terms([
            'taxonomy' => self::TAX_CATEGORY,
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        return is_wp_error($categories) || !is_array($categories) ? [] : $categories;
    }

    public function get_listing_categories(array $listings): array
    {
        $categories = [];

        foreach ($listings as $listing)
        {
            $category_id = isset($listing['category_id']) ? (int) $listing['category_id'] : 0;
            $category = isset($listing['category']) ? trim((string) $listing['category']) : '';

            if ($category_id < 1 || $category === '') continue;
            $categories[$category_id] = $category;
        }

        asort($categories);

        return $categories;
    }

    public function get_label_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!$this->is_labelize_available()) return [];

        $access = new \LSDPACLBL\Access();
        $records = [];

        foreach ($this->get_user_listings($user_id) as $listing)
        {
            if (!$listing instanceof WP_Post) continue;

            foreach ($this->get_listing_label_ids((int) $listing->ID, $access) as $label_id)
            {
                $label_data = $this->get_label_data($label_id);
                $label_available = count($label_data) > 0;

                $raw_status = sanitize_key((string) get_post_meta($listing->ID, 'lsd_labelize_status_' . $label_id, true));
                $resolved_status = $this->label_status($access, (int) $listing->ID, $label_id, $raw_status);
                $start_time = (string) get_post_meta($listing->ID, 'lsd_labelize_start_' . $label_id, true);
                $expiry_time = (string) get_post_meta($listing->ID, 'lsd_labelize_expiry_' . $label_id, true);
                $recurring_id = (int) get_post_meta($listing->ID, 'lsd_labelize_recurring_' . $label_id, true);
                $product_id = $label_available ? (int) get_term_meta($label_id, 'lsd_product', true) : 0;
                $product = $product_id ? lsd_payment()->plan($product_id) : null;
                $recurring = ($recurring_id > 0 && class_exists('LSD_Payments_Recurrings')) ? LSD_Payments_Recurrings::get($recurring_id) : null;
                $product_available = $product_id > 0 && lsd_payment()->plan_supported($product_id) && \LSDPACLBL\Addon::is_valid_product($product);
                $renewal_message = !$label_available
                    ? esc_html__('This label is no longer available.', 'listdom')
                    : (!$product_available ? esc_html__('This label cannot be renewed right now. Please contact the site administrator.', 'listdom') : '');

                $records[] = [
                    'listing_id' => (int) $listing->ID,
                    'listing_title' => get_the_title($listing->ID),
                    'listing_url' => get_permalink($listing->ID),
                    'label_id' => $label_id,
                    'label_name' => trim((string) ($label_data['name'] ?? '')) !== '' ? (string) $label_data['name'] : esc_html__('Unavailable Label', 'listdom'),
                    'label_color' => trim((string) ($label_data['color'] ?? '')),
                    'label_chip_style' => LSD_Taxonomies::label_chip_style($label_data),
                    'status' => $resolved_status,
                    'expired_on' => $expiry_time !== '' ? $this->format_date($expiry_time) : esc_html__('N/A', 'listdom'),
                    'sort_expiry' => $expiry_time,
                    'progress' => $this->get_label_progress($start_time, $expiry_time, $resolved_status, $label_data),
                    'details' => $this->format_listing_details($dashboard, $listing),
                    'is_recurring' => $recurring instanceof LSD_Payments_Recurring,
                    'recurring_id' => $recurring_id,
                    'recurring_badge' => $this->get_payments_dashboard()->recurring_badge($recurring instanceof LSD_Payments_Recurring ? $recurring : null),
                    'manage_url' => $recurring instanceof LSD_Payments_Recurring ? $this->get_payments_dashboard()->get_recurring_manage_url($dashboard->url, $recurring) : '',
                    'can_renew' => $product_available,
                    'renewal_message' => $renewal_message,
                    'can_remove' => $recurring_id < 1,
                    'search' => strtolower(trim(($label_data['name'] ?? '') . ' ' . get_the_title($listing->ID) . ' ' . $dashboard->get_listing_primary_category_name($listing))),
                ];
            }
        }

        usort($records, [$this, 'compare_label_records']);

        return $records;
    }

    protected function get_listing_label_ids(int $listing_id, \LSDPACLBL\Access $access): array
    {
        if ($listing_id < 1) return [];

        $label_ids = [];
        $terms = wp_get_post_terms($listing_id, self::TAX_LABEL, ['fields' => 'ids']);

        if (is_array($terms))
        {
            foreach ($terms as $term_id)
            {
                $term_id = (int) $term_id;
                if ($term_id < 1) continue;
                if ($access->has_access($listing_id, $term_id)) $label_ids[] = $term_id;
            }
        }

        $all_meta = get_post_meta($listing_id);
        if (!is_array($all_meta) || !count($all_meta)) return array_values(array_unique($label_ids));

        $prefixes = [
            'lsd_labelize_status_',
            'lsd_labelize_time_',
            'lsd_labelize_start_',
            'lsd_labelize_expiry_',
            'lsd_labelize_recurring_',
            'lsd_labelize_order_',
            'lsd_labelize_wc_order_',
            'lsd_labelize_plan_',
            'lsd_labelize_tier_',
            'lsd_labelize_product_',
            'lsd_labelize_source_',
        ];

        foreach ($all_meta as $meta_key => $values)
        {
            foreach ($prefixes as $prefix)
            {
                if (strpos((string) $meta_key, $prefix) !== 0) continue;

                $label_id = (int) substr((string) $meta_key, strlen($prefix));
                if ($label_id > 0) $label_ids[] = $label_id;
                break;
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $label_ids))));
    }

    public function get_active_label_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        $records = [];

        foreach ($this->get_label_records($user_id, $dashboard) as $record)
        {
            if (($record['status'] ?? '') !== \LSDPACLBL\Access::STATUS_ACTIVE) continue;

            $records[] = $record;
        }

        return $records;
    }

    public function group_label_records(array $records): array
    {
        $groups = [];

        foreach ($records as $record)
        {
            $label_id = isset($record['label_id']) ? (int) $record['label_id'] : 0;
            if ($label_id < 1) continue;

            if (!isset($groups[$label_id]))
            {
                $groups[$label_id] = [
                    'label_id' => $label_id,
                    'label_name' => (string) ($record['label_name'] ?? ''),
                    'label_color' => (string) ($record['label_color'] ?? ''),
                    'label_chip_style' => (string) ($record['label_chip_style'] ?? ''),
                    'search' => '',
                    'records' => [],
                ];
            }

            $groups[$label_id]['records'][] = $record;
            $groups[$label_id]['search'] .= ' ' . (string) ($record['search'] ?? '');
        }

        foreach ($groups as &$group) $group['search'] = trim($group['search']);
        unset($group);

        return array_values($groups);
    }

    public function get_expired_label_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        $records = [];

        foreach ($this->get_label_records($user_id, $dashboard) as $record)
        {
            if (($record['status'] ?? '') !== \LSDPACLBL\Access::STATUS_EXPIRED) continue;

            $records[] = $record;
        }

        return $records;
    }

    public function get_topup_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!$this->is_topup_available() || !class_exists('\LSDPACTUP\Access')) return [];

        $access = new \LSDPACTUP\Access();
        $topup_product = $this->get_topup_product_data();
        $records = [];

        foreach ($this->get_user_listings($user_id) as $listing)
        {
            if (!$listing instanceof WP_Post) continue;
            $record = $this->get_topup_record($listing, $dashboard, $access, $topup_product);
            if (is_array($record)) $records[] = $record;
        }

        usort($records, [$this, 'compare_topup_records']);

        return $records;
    }

    public function get_active_topup_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        $records = [];

        foreach ($this->get_topup_records($user_id, $dashboard) as $record)
        {
            if (($record['status'] ?? '') !== \LSDPACTUP\Access::STATUS_ACTIVE) continue;

            $records[] = $record;
        }

        return $records;
    }

    public function get_active_topup_records_page(int $user_id, LSD_Shortcodes_Dashboard $dashboard, int $page = 1, int $per_page = self::TOPUP_RECORDS_PER_PAGE, string $sort = 'expiry_asc', string $search = ''): array
    {
        if (!$this->is_topup_available() || !class_exists('\LSDPACTUP\Access')) return [
            'items' => [],
            'count' => 0,
            'total' => 0,
            'has_more' => false,
            'next_page' => 0,
        ];

        $page = max(1, $page);
        $per_page = max(1, $per_page);
        $sort = in_array($sort, ['expiry_asc', 'expiry_desc', 'title_asc', 'title_desc'], true) ? $sort : 'expiry_asc';
        $search = strtolower(trim($search));

        $result = $this->get_active_topup_listing_ids_page($user_id, $page, $per_page, $sort, $search);
        $access = new \LSDPACTUP\Access();
        $topup_product = $this->get_topup_product_data();
        $records = [];

        foreach ($result['ids'] as $listing_id)
        {
            $listing = get_post($listing_id);
            if (!$listing instanceof WP_Post) continue;

            $record = $this->get_topup_record($listing, $dashboard, $access, $topup_product);
            if (is_array($record) && ($record['status'] ?? '') === \LSDPACTUP\Access::STATUS_ACTIVE) $records[] = $record;
        }

        return [
            'items' => $records,
            'count' => count($records),
            'total' => $result['total'],
            'has_more' => $result['has_more'],
            'next_page' => $result['next_page'],
        ];
    }

    protected function get_active_topup_listing_ids_page(int $user_id, int $page, int $per_page, string $sort, string $search = ''): array
    {
        $db = new LSD_DB();
        $page = max(1, $page);
        $per_page = max(1, $per_page);
        $offset = ($page - 1) * $per_page;
        $statuses = ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED];
        $status_placeholders = implode(', ', array_fill(0, count($statuses), '%s'));
        $author_sql = current_user_can('edit_others_posts') ? '' : ' AND p.post_author = %d';
        $order_sql = $sort === 'title_asc' ? 'p.post_title ASC' : ($sort === 'title_desc' ? 'p.post_title DESC' : "CASE WHEN topup_expiry.meta_value IS NULL OR topup_expiry.meta_value = '' THEN 1 ELSE 0 END ASC, CAST(topup_expiry.meta_value AS DATETIME) " . ($sort === 'expiry_desc' ? 'DESC' : 'ASC') . ', p.post_title ASC');
        $search_sql = '';
        $search_parameters = [];

        if ($search !== '')
        {
            $search_like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $search_sql = "
              AND (
                p.post_title LIKE %s
                OR EXISTS (
                    SELECT 1
                    FROM `#__term_relationships` AS category_relationship
                    INNER JOIN `#__term_taxonomy` AS category_taxonomy ON category_taxonomy.term_taxonomy_id = category_relationship.term_taxonomy_id
                    INNER JOIN `#__terms` AS category ON category.term_id = category_taxonomy.term_id
                    WHERE category_relationship.object_id = p.ID
                      AND category_taxonomy.taxonomy = %s
                      AND category.name LIKE %s
                )
                OR EXISTS (
                    SELECT 1
                    FROM `#__postmeta` AS package_meta
                    INNER JOIN `#__posts` AS package ON package.ID = CAST(package_meta.meta_value AS UNSIGNED)
                    WHERE package_meta.post_id = p.ID
                      AND package_meta.meta_key = %s
                      AND package.post_title LIKE %s
                )
                OR EXISTS (
                    SELECT 1
                    FROM `#__postmeta` AS subscription_meta
                    INNER JOIN `#__postmeta` AS subscription_package_meta ON subscription_package_meta.post_id = CAST(subscription_meta.meta_value AS UNSIGNED)
                    INNER JOIN `#__posts` AS subscription_package ON subscription_package.ID = CAST(subscription_package_meta.meta_value AS UNSIGNED)
                    WHERE subscription_meta.post_id = p.ID
                      AND subscription_meta.meta_key = %s
                      AND subscription_package_meta.meta_key = %s
                      AND subscription_package.post_title LIKE %s
                )
              )";
            $search_parameters = [
                $search_like,
                self::TAX_CATEGORY,
                $search_like,
                'lsd_package',
                $search_like,
                'lsd_subscription',
                'lsd_package',
                $search_like,
            ];
        }

        $where_sql = "
            FROM `#__posts` AS p
            LEFT JOIN `#__postmeta` AS topup_status ON topup_status.post_id = p.ID AND topup_status.meta_key = %s
            LEFT JOIN `#__postmeta` AS topup_expiry ON topup_expiry.post_id = p.ID AND topup_expiry.meta_key = %s
            LEFT JOIN `#__postmeta` AS legacy_topup_order ON legacy_topup_order.post_id = p.ID AND legacy_topup_order.meta_key = %s
            LEFT JOIN `#__postmeta` AS legacy_topup_wc_order ON legacy_topup_wc_order.post_id = p.ID AND legacy_topup_wc_order.meta_key = %s
            WHERE p.post_type = %s
              AND p.post_status IN ({$status_placeholders})
              AND (
                (
                    topup_status.meta_value = %s
                    AND (
                        topup_expiry.post_id IS NULL
                        OR topup_expiry.meta_value = ''
                        OR CAST(topup_expiry.meta_value AS DATETIME) > %s
                    )
                )
                OR (
                    topup_status.post_id IS NULL
                    AND (legacy_topup_order.meta_value > 0 OR legacy_topup_wc_order.meta_value > 0)
                )
              ){$search_sql}{$author_sql}";

        $parameters = array_merge([
            'lsd_topup_status',
            'lsd_topup_expiry',
            'lsd_topup_order_id',
            'lsd_topup_wc_order_id',
            self::PTYPE_LISTING,
        ], $statuses, [
            \LSDPACTUP\Access::STATUS_ACTIVE,
            current_time('Y-m-d H:i:s'),
        ], $search_parameters);

        if ($author_sql !== '') $parameters[] = $user_id;

        $count_sql = $db->prepare("SELECT COUNT(DISTINCT p.ID) {$where_sql}", $parameters);
        $total = $db->num($count_sql);
        $ids_sql = $db->prepare("SELECT p.ID {$where_sql} GROUP BY p.ID ORDER BY {$order_sql} LIMIT %d OFFSET %d", array_merge($parameters, [$per_page, $offset]));
        $ids = array_map('absint', (array) $db->select($ids_sql, 'loadColumn'));
        $has_more = ($offset + $per_page) < $total;

        return [
            'ids' => $ids,
            'total' => $total,
            'has_more' => $has_more,
            'next_page' => $has_more ? $page + 1 : 0,
        ];
    }

    protected function get_active_topup_listing_ids(int $user_id): array
    {
        $result = $this->get_active_topup_listing_ids_page($user_id, 1, PHP_INT_MAX, 'expiry_asc');
        return $result['ids'];
    }

    protected function get_topup_record(WP_Post $listing, LSD_Shortcodes_Dashboard $dashboard, \LSDPACTUP\Access $access, array $topup_product): ?array
    {
        if (!$access->has_access((int) $listing->ID)) return null;

        $raw_status = sanitize_key((string) get_post_meta($listing->ID, 'lsd_topup_status', true));
        $resolved_status = $this->topup_status($access, (int) $listing->ID, $raw_status);
        $start_time = (string) get_post_meta($listing->ID, 'lsd_topup_start', true);
        $expiry_time = (string) get_post_meta($listing->ID, 'lsd_topup_expiry', true);
        $recurring_id = (int) get_post_meta($listing->ID, 'lsd_topup_recurring', true);
        $order_id = (int) get_post_meta($listing->ID, 'lsd_topup_order', true);
        $recurring = ($recurring_id > 0 && class_exists('LSD_Payments_Recurrings')) ? LSD_Payments_Recurrings::get($recurring_id) : null;

        if ($start_time === '')
        {
            $topup_time = (int) get_post_meta($listing->ID, 'lsd_topup', true);
            if ($topup_time > 0) $start_time = lsd_date('Y-m-d H:i:s', $topup_time);
        }

        return [
            'listing_id' => (int) $listing->ID,
            'listing_title' => get_the_title($listing->ID),
            'listing_url' => get_permalink($listing->ID),
            'listing_image' => get_the_post_thumbnail_url($listing->ID, 'thumbnail'),
            'status' => $resolved_status,
            'expired_on' => $expiry_time !== '' ? $this->format_date($expiry_time) : esc_html__('N/A', 'listdom'),
            'sort_expiry' => $expiry_time,
            'progress' => $this->get_topup_progress($start_time, $expiry_time, $resolved_status),
            'details' => $this->format_listing_details($dashboard, $listing),
            'is_recurring' => $recurring instanceof LSD_Payments_Recurring,
            'recurring_id' => $recurring_id,
            'recurring_badge' => $this->get_payments_dashboard()->recurring_badge($recurring instanceof LSD_Payments_Recurring ? $recurring : null),
            'manage_url' => $recurring instanceof LSD_Payments_Recurring ? $this->get_payments_dashboard()->get_recurring_manage_url($dashboard->url, $recurring) : '',
            'order_url' => $this->get_topup_order_url($dashboard, $order_id),
            'can_renew' => $listing->post_status === 'publish' && (bool) ($topup_product['is_valid'] ?? false),
            'can_remove' => $recurring_id < 1 && $resolved_status === \LSDPACTUP\Access::STATUS_ACTIVE,
            'search' => strtolower(trim(get_the_title($listing->ID) . ' ' . $this->format_listing_details($dashboard, $listing) . ' ' . $dashboard->get_listing_primary_category_name($listing))),
        ];
    }

    public function get_expired_topup_records(int $user_id, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!$this->is_topup_available() || !class_exists('\LSDPACTUP\Access')) return [];

        $access = new \LSDPACTUP\Access();
        $topup_product = $this->get_topup_product_data();
        $records = [];

        foreach ($this->get_expired_topup_listing_ids($user_id) as $listing_id)
        {
            $listing = get_post($listing_id);
            if (!$listing instanceof WP_Post) continue;

            $record = $this->get_topup_record($listing, $dashboard, $access, $topup_product);
            if (!is_array($record)) continue;
            if (($record['status'] ?? '') !== \LSDPACTUP\Access::STATUS_EXPIRED) continue;

            $records[] = $record;
        }

        usort($records, [$this, 'compare_topup_records']);

        return $records;
    }

    protected function get_expired_topup_listing_ids(int $user_id): array
    {
        $db = new LSD_DB();
        $statuses = ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED];
        $status_placeholders = implode(', ', array_fill(0, count($statuses), '%s'));
        $author_sql = current_user_can('edit_others_posts') ? '' : ' AND p.post_author = %d';
        $sql = "
            SELECT DISTINCT p.ID
            FROM `#__posts` AS p
            INNER JOIN `#__postmeta` AS topup_status ON topup_status.post_id = p.ID AND topup_status.meta_key = %s
            LEFT JOIN `#__postmeta` AS topup_expiry ON topup_expiry.post_id = p.ID AND topup_expiry.meta_key = %s
            WHERE p.post_type = %s
              AND p.post_status IN ({$status_placeholders})
              AND (
                topup_status.meta_value = %s
                OR (
                    topup_status.meta_value = %s
                    AND topup_expiry.meta_value <> ''
                    AND topup_expiry.meta_value <= %s
                )
              ){$author_sql}
            ORDER BY p.post_date DESC";

        $parameters = array_merge([
            'lsd_topup_status',
            'lsd_topup_expiry',
            self::PTYPE_LISTING,
        ], $statuses, [
            \LSDPACTUP\Access::STATUS_EXPIRED,
            \LSDPACTUP\Access::STATUS_ACTIVE,
            current_time('Y-m-d H:i:s'),
        ]);

        if ($author_sql !== '') $parameters[] = $user_id;

        return array_map('absint', (array) $db->select($db->prepare($sql, $parameters), 'loadColumn'));
    }

    public function checkout(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!$this->is_labelize_available()) $this->json_response(['success' => 0, 'message' => esc_html__('Labelize addon is not available.', 'listdom')]);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_checkout')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $labels = \LSDPACLBL\Addon::normalize_labels($_POST['labels'] ?? []);
        $listing_ids = array_values(array_unique(array_filter(array_map('absint', (array) wp_unslash($_POST['listing_ids'] ?? [])))));

        if (!count($labels)) $this->json_response(['success' => 0, 'message' => esc_html__('Please select at least one label.', 'listdom')]);
        if (!count($listing_ids)) $this->json_response(['success' => 0, 'message' => esc_html__('Please select at least one listing.', 'listdom')]);

        $payment = lsd_payment();
        if (!$payment->requirements_meet()) $this->json_response(['success' => 0, 'message' => esc_html($payment->requirements_message())]);

        $added = 0;
        $skipped = 0;

        foreach ($listing_ids as $listing_id)
        {
            if (!$this->can_manage_listing($listing_id))
            {
                $skipped += count($labels);
                continue;
            }

            $partition = \LSDPACLBL\Addon::partition_labels($listing_id, $labels);

            if (count($partition['payable']))
            {
                $added_labels = \LSDPACLBL\Addon::cart($listing_id, $partition['payable']);
                $added += count($added_labels);
                $skipped += count($partition['payable']) - count($added_labels);
            }

            $skipped += count($partition['skipped']);
        }

        if ($added < 1) $this->json_response(['success' => 0, 'message' => esc_html__('All selected labels are already assigned or are not payable for the chosen listings.', 'listdom'),]);

        $this->json_response([
            'success' => 1,
            'message' => esc_html__('Selected labels were added to checkout.', 'listdom'),
            'notice' => $skipped > 0 ? esc_html__('Some selected labels were skipped because they are already assigned or are not payable for one or more listings.', 'listdom') : '',
            'data' => [
                'next' => $payment->checkout_url(),
                'added' => $added,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function topup_checkout(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!$this->is_topup_available() || !class_exists('\LSDPACTUP\Topup') || !class_exists('\LSDPACTUP\Access')) $this->json_response(['success' => 0, 'message' => esc_html__('Top-up addon is not available.', 'listdom')]);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_topup_checkout')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $listing_ids = array_values(array_unique(array_filter(array_map('absint', (array) wp_unslash($_POST['listing_ids'] ?? [])))));
        if (!count($listing_ids)) $this->json_response(['success' => 0, 'message' => esc_html__('Please select at least one listing.', 'listdom')]);

        $payment = lsd_payment();
        if (!$payment->requirements_meet()) $this->json_response(['success' => 0, 'message' => esc_html($payment->requirements_message())]);

        $topup = new \LSDPACTUP\Topup();
        $product_id = $topup->get_product_id();
        if ($product_id < 1) $this->json_response(['success' => 0, 'message' => esc_html($payment->unavailable_plan_message())]);
        if (!$payment->plan_supported($product_id)) $this->json_response(['success' => 0, 'message' => esc_html($payment->unsupported_plan_message())]);
        if (!$topup->is_valid_product($topup->get_product())) $this->json_response(['success' => 0, 'message' => esc_html($payment->unavailable_plan_message())]);

        $access = new \LSDPACTUP\Access();
        $added = 0;
        $skipped = 0;

        foreach ($listing_ids as $listing_id)
        {
            if (!$this->can_manage_listing($listing_id))
            {
                $skipped++;
                continue;
            }

            $listing = get_post($listing_id);
            if (!$listing instanceof WP_Post || $listing->post_status !== 'publish')
            {
                $skipped++;
                continue;
            }

            if ($access->is_active($listing_id))
            {
                $skipped++;
                continue;
            }

            if ($topup->add_to_cart($listing_id)) $added++;
            else $skipped++;
        }

        if ($added < 1) $this->json_response(['success' => 0, 'message' => esc_html__('All selected listings already have an active Top-up or could not be added to checkout.', 'listdom'),]);

        $this->json_response([
            'success' => 1,
            'message' => esc_html__('Selected listings were added to checkout.', 'listdom'),
            'notice' => $skipped > 0 ? esc_html__('Some selected listings were skipped because Top-up is already active or the listing could not be processed.', 'listdom') : '',
            'data' => [
                'next' => $payment->checkout_url(),
                'added' => $added,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function remove_label(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!$this->is_labelize_available()) $this->json_response(['success' => 0, 'message' => esc_html__('Labelize addon is not available.', 'listdom')]);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_remove_label')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $listing_id = isset($_POST['listing_id']) ? absint(wp_unslash($_POST['listing_id'])) : 0;
        $label_id = isset($_POST['label_id']) ? absint(wp_unslash($_POST['label_id'])) : 0;
        if ($listing_id < 1 || $label_id < 1) $this->json_response(['success' => 0, 'message' => esc_html__('Invalid request.', 'listdom')]);
        if (!$this->can_manage_listing($listing_id)) $this->json_response(['success' => 0, 'message' => esc_html__('You do not have permission to manage this listing.', 'listdom')]);

        $access = new \LSDPACLBL\Access();
        if (!$access->has_access($listing_id, $label_id)) $this->json_response(['success' => 0, 'message' => esc_html__('Label access was not found.', 'listdom')]);

        $stored_recurring_id = (int) get_post_meta($listing_id, 'lsd_labelize_recurring_' . $label_id, true);
        if ($stored_recurring_id > 0) $this->json_response(['success' => 0, 'message' => esc_html__('Use Manage Subscription to cancel recurring labels.', 'listdom')]);

        $access->cancel($listing_id, $label_id);

        $this->json_response(['success' => 1, 'message' => esc_html__('The label was removed from the listing.', 'listdom'),]);
    }

    public function remove_topup(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!$this->is_topup_available() || !class_exists('\LSDPACTUP\Access')) $this->json_response(['success' => 0, 'message' => esc_html__('Top-up addon is not available.', 'listdom')]);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_remove_topup')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $listing_id = isset($_POST['listing_id']) ? absint(wp_unslash($_POST['listing_id'])) : 0;
        if ($listing_id < 1) $this->json_response(['success' => 0, 'message' => esc_html__('Invalid request.', 'listdom')]);
        if (!$this->can_manage_listing($listing_id)) $this->json_response(['success' => 0, 'message' => esc_html__('You do not have permission to manage this listing.', 'listdom')]);

        $access = new \LSDPACTUP\Access();
        if (!$access->has_access($listing_id) || !$access->is_active($listing_id)) $this->json_response(['success' => 0, 'message' => esc_html__('Top-up access was not found.', 'listdom')]);

        $stored_recurring_id = (int) get_post_meta($listing_id, 'lsd_topup_recurring', true);
        if ($stored_recurring_id > 0) $this->json_response(['success' => 0, 'message' => esc_html__('Use Manage Subscription to cancel recurring Top-ups.', 'listdom')]);

        $access->cancel($listing_id);

        $this->json_response(['success' => 1, 'message' => esc_html__('The Top-up was removed from the listing.', 'listdom'),]);
    }

    public function listings(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_listings')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $dashboard = new LSD_Shortcodes_Dashboard();
        $page = isset($_POST['page']) ? absint(wp_unslash($_POST['page'])) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category_id = isset($_POST['category_id']) ? absint(wp_unslash($_POST['category_id'])) : 0;
        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '';
        if (!in_array($mode, ['', 'labelize', 'topup'], true)) $mode = '';

        $result = $this->get_popup_listings_page(get_current_user_id(), $dashboard, $page, self::POPUP_LISTINGS_PER_PAGE, $search, $category_id, $mode);

        $this->json_response([
            'success' => 1,
            'data' => [
                'html' => $this->popup_listing_items($result['items'], $mode),
                'count' => $result['count'],
                'total' => $result['total'],
                'has_more' => $result['has_more'] ? 1 : 0,
                'next_page' => $result['next_page'],
            ],
        ]);
    }

    public function topup_records(): void
    {
        if (!get_current_user_id()) $this->json_response(['success' => 0, 'message' => esc_html__('Please sign in to continue.', 'listdom')]);
        if (!$this->is_topup_available()) $this->json_response(['success' => 0, 'message' => esc_html__('Top-up addon is not available.', 'listdom')]);
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_promotions_topup_records')) $this->json_response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $dashboard = new LSD_Shortcodes_Dashboard();
        $dashboard->url = $this->get_payments_dashboard()->get_dashboard_url();
        $page = isset($_POST['page']) ? absint(wp_unslash($_POST['page'])) : 1;
        $sort = isset($_POST['sort']) ? sanitize_key(wp_unslash($_POST['sort'])) : 'expiry_asc';
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $result = $this->get_active_topup_records_page(get_current_user_id(), $dashboard, $page, self::TOPUP_RECORDS_PER_PAGE, $sort, $search);

        $records = $result['items'];
        ob_start();
        include lsd_template('dashboard/promotions/topup/cards.php');
        $html = ob_get_clean();

        $this->json_response([
            'success' => 1,
            'data' => [
                'html' => $html,
                'count' => $result['count'],
                'total' => $result['total'],
                'has_more' => $result['has_more'] ? 1 : 0,
                'next_page' => $result['next_page'],
            ],
        ]);
    }

    protected function label_status(\LSDPACLBL\Access $access, int $listing_id, int $label_id, string $status): string
    {
        if ($access->is_active($listing_id, $label_id)) return \LSDPACLBL\Access::STATUS_ACTIVE;
        if ($status === \LSDPACLBL\Access::STATUS_CANCELED) return \LSDPACLBL\Access::STATUS_CANCELED;
        if ($status === \LSDPACLBL\Access::STATUS_REFUNDED) return \LSDPACLBL\Access::STATUS_REFUNDED;

        return \LSDPACLBL\Access::STATUS_EXPIRED;
    }

    protected function topup_status(\LSDPACTUP\Access $access, int $listing_id, string $status): string
    {
        if ($access->is_active($listing_id)) return \LSDPACTUP\Access::STATUS_ACTIVE;
        if ($status === \LSDPACTUP\Access::STATUS_CANCELED) return \LSDPACTUP\Access::STATUS_CANCELED;
        if ($status === \LSDPACTUP\Access::STATUS_REFUNDED) return \LSDPACTUP\Access::STATUS_REFUNDED;

        return \LSDPACTUP\Access::STATUS_EXPIRED;
    }

    protected function get_label_progress(string $start_time, string $expiry_time, string $status, array $label_data = []): array
    {
        $accent = isset($label_data['color']) ? (string) $label_data['color'] : '';
        return $this->get_progress_data($start_time, $expiry_time, $status, $accent);
    }

    protected function get_topup_progress(string $start_time, string $expiry_time, string $status): array
    {
        return $this->get_progress_data($start_time, $expiry_time, $status, self::TOPUP_ACCENT);
    }

    protected function get_progress_data(string $start_time, string $expiry_time, string $status, string $accent = ''): array
    {
        $start_timestamp = $start_time !== '' ? \LSD_Base::strtotime($start_time) : 0;
        $expiry_timestamp = $expiry_time !== '' ? \LSD_Base::strtotime($expiry_time) : 0;
        $now = current_time('timestamp', true);

        if ($start_timestamp < 1) $start_timestamp = $now;

        if ($expiry_timestamp < 1)
        {
            return [
                'percent' => 100,
                'status_class' => 'lifetime',
                'bar_style' => $this->progress_style(100, 'lifetime', $accent),
                'message' => sprintf(
                    esc_html__('Started %1$s - Lifetime', 'listdom'),
                    $this->format_date($start_time)
                ),
                'started' => sprintf(esc_html__('Started %s', 'listdom'), $this->format_date($start_time)),
                'remaining_value' => esc_html__('Lifetime', 'listdom'),
                'remaining_label' => '',
            ];
        }

        $duration = max(1, $expiry_timestamp - $start_timestamp);
        $remaining = max(0, $expiry_timestamp - $now);
        $percent = $status === 'active' ? (int) round(($remaining / $duration) * 100) : 0;
        $percent = max(0, min(100, $percent));
        $remaining_days = max(0, (int) ceil(($expiry_timestamp - $now) / DAY_IN_SECONDS));

        return [
            'percent' => $percent,
            'status_class' => $status,
            'bar_style' => $this->progress_style($percent, $status, $accent),
                'message' => sprintf(
                    esc_html__('Started %1$s - %2$s', 'listdom'),
                    $this->format_date($start_time),
                    sprintf(_n('%d day remaining', '%d days remaining', $remaining_days, 'listdom'), $remaining_days)
                ),
            'started' => sprintf(esc_html__('Started %s', 'listdom'), $this->format_date($start_time)),
            'remaining_value' => $remaining_days,
            'remaining_label' => _n('day remaining', 'days remaining', $remaining_days, 'listdom'),
        ];
    }

    protected function progress_style(int $percent, string $status, string $accent = ''): string
    {
        $styles = ['width: ' . max(0, min(100, $percent)) . '%'];

        if ($accent !== '' && !in_array($status, ['expired', 'canceled', 'refunded'], true)) $styles[] = 'background-color: ' . $accent;

        return implode('; ', $styles) . ';';
    }

    protected function compare_label_records(array $first, array $second): int
    {
        return $this->compare_records_by_expiry($first, $second, 'label_name');
    }

    protected function compare_topup_records(array $first, array $second): int
    {
        return $this->compare_records_by_expiry($first, $second, 'listing_title');
    }

    protected function compare_records_by_expiry(array $first, array $second, string $secondary_key): int
    {
        $first_active = ($first['status'] ?? '') === 'active';
        $second_active = ($second['status'] ?? '') === 'active';
        $first_expiry = isset($first['sort_expiry']) ? \LSD_Base::strtotime((string) $first['sort_expiry']) : 0;
        $second_expiry = isset($second['sort_expiry']) ? \LSD_Base::strtotime((string) $second['sort_expiry']) : 0;

        if ($first_active && $second_active)
        {
            if ($first_expiry > 0 && $second_expiry > 0 && $first_expiry !== $second_expiry) return $first_expiry <=> $second_expiry;
            if ($first_expiry < 1 && $second_expiry > 0) return 1;
            if ($first_expiry > 0 && $second_expiry < 1) return -1;
        }
        else if (!$first_active && !$second_active)
        {
            if ($first_expiry !== $second_expiry) return $second_expiry <=> $first_expiry;
        }
        else return $first_active ? -1 : 1;

        $first_secondary = (string) ($first[$secondary_key] ?? '');
        $second_secondary = (string) ($second[$secondary_key] ?? '');
        if ($first_secondary !== $second_secondary) return strcmp($first_secondary, $second_secondary);

        return strcmp((string) ($first['listing_title'] ?? ''), (string) ($second['listing_title'] ?? ''));
    }

    public function label_chip(string $label, int $seed, array $args = []): string
    {
        return $this->membership_chip($label, array_merge([
            'seed' => $seed,
        ], $args));
    }

    public function popup_listing_items(array $listings, string $mode = ''): string
    {
        if (!count($listings)) return '';

        $this->rendering_popup_listing_items = true;
        $this->rendering_popup_listing_mode = sanitize_key($mode);
        ob_start();
        include lsd_template('dashboard/promotions/items.php');
        $output = ob_get_clean();
        $this->rendering_popup_listing_items = false;
        $this->rendering_popup_listing_mode = '';
        $this->rendering_popup_category_child_ids = null;
        $this->rendering_popup_category_root_ids = [];
        $this->rendering_popup_search_child_ids = null;
        $this->rendering_popup_search_root_ids = [];

        return $output;
    }

    public function listing_selection_control(string $output, $listing): string
    {
        if (!$this->rendering_popup_listing_items || !$listing instanceof WP_Post) return $output;

        $listing_id = (int) $listing->ID;
        if ($listing_id < 1) return $output;

        $title = get_the_title($listing);
        $select_label = sprintf(__('Select %s', 'listdom'), $title);

        if ($this->rendering_popup_listing_mode === 'topup'
            && class_exists('\\LSDPACTUP\\Access')
            && (new \LSDPACTUP\Access())->is_active($listing_id))
        {
            $unavailable_label = sprintf(__('Top-up already active for %s', 'listdom'), $title);
            return '<span class="lsd-dashboard-promotions-modal-item-selector is-disabled"><input type="checkbox" class="lsd-fe-input-check lsd-promotion-listing-checkbox" value="' . esc_attr($listing_id) . '" disabled aria-label="' . esc_attr($unavailable_label) . '"></span>';
        }

        return '<span class="lsd-dashboard-promotions-modal-item-selector"><input type="checkbox" class="lsd-fe-input-check lsd-promotion-listing-checkbox" value="' . esc_attr($listing_id) . '" data-listing-title="' . esc_attr($title) . '" aria-label="' . esc_attr($select_label) . '"></span>';
    }

    public function popup_allowed_children($allowed_child_ids, $listing, LSD_Shortcodes_Dashboard $dashboard)
    {
        if (!$this->rendering_popup_listing_items || !$listing instanceof WP_Post) return $allowed_child_ids;
        if ($this->rendering_popup_listing_mode !== 'topup'
            && $this->rendering_popup_category_child_ids === null
            && $this->rendering_popup_search_child_ids === null) return $allowed_child_ids;

        $access = class_exists('\\LSDPACTUP\\Access') ? new \LSDPACTUP\Access() : null;
        $child_ids = [];

        foreach ($this->get_popup_filtered_child_ids($listing) as $child_id)
        {
            if ($this->rendering_popup_listing_mode === 'topup' && $access && $access->is_active($child_id)) continue;
            $child_ids[] = $child_id;
        }

        if (!is_array($allowed_child_ids)) return $child_ids;

        return array_values(array_intersect(array_map('intval', $allowed_child_ids), $child_ids));
    }

    protected function get_popup_filtered_child_ids(WP_Post $listing): array
    {
        $child_ids = [];

        foreach ((new LSD_Entity_Listing($listing))->get_children() as $child)
        {
            $child_id = $child instanceof WP_Post ? (int) $child->ID : (int) $child;
            if ($child_id < 1) continue;
            if ($this->rendering_popup_category_child_ids !== null
                && !in_array((int) $listing->ID, $this->rendering_popup_category_root_ids, true)
                && !in_array($child_id, $this->rendering_popup_category_child_ids, true)) continue;
            if ($this->rendering_popup_search_child_ids !== null
                && !in_array((int) $listing->ID, $this->rendering_popup_search_root_ids, true)
                && !in_array($child_id, $this->rendering_popup_search_child_ids, true)) continue;
            $child_ids[] = $child_id;
        }

        return $child_ids;
    }

    protected function get_label_data(int $label_id): array
    {
        $labels = LSD_Taxonomies::get_labels_data([$label_id]);
        return isset($labels[0]) && is_array($labels[0]) ? $labels[0] : [];
    }

    protected function get_topup_order_url(LSD_Shortcodes_Dashboard $dashboard, int $order_id): string
    {
        if ($order_id < 1) return '';

        return $this->get_payments_dashboard()->get_order_detail_link($dashboard->url, $order_id);
    }

    protected function get_listing_primary_category_id(int $listing_id): int
    {
        $category = LSD_Entity_Listing::get_primary_category($listing_id);
        if ($category instanceof WP_Term) return (int) $category->term_id;

        $terms = get_the_terms($listing_id, self::TAX_CATEGORY);
        if (is_array($terms))
        {
            foreach ($terms as $term)
            {
                if ($term instanceof WP_Term) return (int) $term->term_id;
            }
        }

        return 0;
    }

    protected function format_listing_details(LSD_Shortcodes_Dashboard $dashboard, WP_Post $listing): string
    {
        $parts = $dashboard->get_listing_detail_parts($listing);
        return count($parts) ? implode(' - ', $parts) : '';
    }

    protected function format_date(string $date): string
    {
        $timestamp = \LSD_Base::strtotime($date);
        return $timestamp ? wp_date('Y-m-d', $timestamp) : esc_html__('N/A', 'listdom');
    }

    protected function can_manage_listing(int $listing_id): bool
    {
        $listing = get_post($listing_id);
        if (!$listing instanceof WP_Post || $listing->post_type !== self::PTYPE_LISTING) return false;

        return current_user_can('edit_post', $listing_id);
    }

    protected function is_toolkit_addon_enabled(string $slug): bool
    {
        if (!class_exists('\LSDTKBU\Boot')) return true;

        return (new \LSDTKBU\Boot())->is_addon_enabled($slug);
    }

    protected function get_payments_dashboard(): LSD_Dashboard_Payments
    {
        if (!$this->payments_dashboard instanceof LSD_Dashboard_Payments) $this->payments_dashboard = new LSD_Dashboard_Payments();

        return $this->payments_dashboard;
    }

    protected function json_response(array $response): void
    {
        wp_send_json($response);
    }
}
