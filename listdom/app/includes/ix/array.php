<?php

abstract class LSD_IX_Array extends LSD_IX
{
    protected LSD_Socials $SN;

    protected array $networks;

    /**
     * @var WP_Term[]
     */
    protected $attributes;

    private array $statuses;

    public function __construct()
    {
        parent::__construct();

        // Social Networks
        $this->SN = new LSD_Socials();
        $this->networks = LSD_Options::socials();

        // Attributes
        $this->attributes = LSD_Main::get_attributes();

        // Statuses
        $this->statuses = [
            'publish',
            'trash',
            'pending',
            'draft',
            'future',
            LSD_Base::STATUS_EXPIRED,
            LSD_Base::STATUS_HOLD,
            LSD_Base::STATUS_OFFLINE
        ];
    }

    /**
     * Get Exportable listings
     *
     * @return WP_Post[]
     */
    public function listings(): array
    {
        // Filter Options
        $args = apply_filters('lsd_export_listings_args', [
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $this->statuses,
            'posts_per_page' => '-1',
            'orderby' => [
                'date' => 'DESC',
                'ID' => 'DESC',
            ],
        ]);

        // Listings to Export
        return apply_filters('lsd_export_listings', get_posts($args));
    }

    /**
     * Get exportable listings in chunks.
     *
     * @return WP_Post[]
     */
    public function listings_chunk(int $offset, int $limit): array
    {
        $args = apply_filters('lsd_export_listings_args', [
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $this->statuses,
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => [
                'date' => 'DESC',
                'ID' => 'DESC',
            ],
        ]);

        $args['posts_per_page'] = $limit;
        $args['offset'] = $offset;

        return apply_filters('lsd_export_listings', get_posts($args));
    }

    /**
     * Get Columns
     * Used in Export
     *
     * @return array
     */
    public function columns(): array
    {
        // Columns
        $columns = [
            esc_html__('ID', 'listdom'),
            esc_html__('Title', 'listdom'),
            esc_html__('Slug', 'listdom'),
            esc_html__('Description', 'listdom'),
            esc_html__('Excerpt', 'listdom'),
            esc_html__('Date', 'listdom'),
            esc_html__('Owner', 'listdom'),
            esc_html__('Status', 'listdom'),
            esc_html__('Price', 'listdom'),
            esc_html__('Price Max', 'listdom'),
            esc_html__('Price Description', 'listdom'),
            esc_html__('Currency', 'listdom'),
            esc_html__('Address', 'listdom'),
            esc_html__('Latitude', 'listdom'),
            esc_html__('Longitude', 'listdom'),
            esc_html__('Link', 'listdom'),
            esc_html__('Email', 'listdom'),
            esc_html__('Phone', 'listdom'),
            esc_html__('Website', 'listdom'),
            esc_html__('Contact Address', 'listdom'),
            esc_html__('Remark', 'listdom'),
            esc_html__('Image', 'listdom'),
            esc_html__('Gallery', 'listdom'),
            esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY)),
            esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')),
            esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')),
            esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')),
            esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')),
        ];

        foreach ($this->networks as $network => $values)
        {
            $obj = $this->SN->get($network, $values);

            // Social Network is not Enabled
            if (!$obj || !$obj->option('listing')) continue;

            $columns[] = $obj->label();
        }

        // Add attributes to columns
        foreach ($this->attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type === 'separator') continue;

            $columns[] = $attribute->name;
        }

        foreach (LSD_Main::get_weekdays() as $weekday)
        {
            $columns[] = sprintf(
                /* translators: %s: Weekday label. */
                esc_html__('%s Hours', 'listdom'),
                $weekday['label'] ?? $weekday['day']
            );
        }

        return apply_filters('lsd_export_columns', $columns);
    }

    /**
     * Get Listing Data
     * Used in Export
     *
     * @param array $listing
     * @return array
     */
    public function row(array $listing): array
    {
        // Meta Values
        $metas = $this->get_post_meta($listing['ID']);

        // Taxonomies
        $taxonomies = $this->get_taxonomies($listing['ID']);
        $category_slug = isset($taxonomies[LSD_Base::TAX_CATEGORY][0]['slug']) ? sanitize_title($taxonomies[LSD_Base::TAX_CATEGORY][0]['slug']) : '';

        // Listing Data
        $row = [
            $listing['ID'],
            $listing['post_title'],
            $listing['post_name'],
            $listing['post_content'],
            $listing['post_excerpt'],
            $listing['post_date'],
            get_the_author_meta('user_email', $listing['post_author']),
            in_array($listing['post_status'], $this->statuses) ? $listing['post_status'] : 'draft',
            $metas['lsd_price'] ?? '',
            $metas['lsd_price_max'] ?? '',
            $metas['lsd_price_after'] ?? '',
            $metas['lsd_currency'] ?? '',
            $metas['lsd_address'] ?? '',
            $metas['lsd_latitude'] ?? '',
            $metas['lsd_longitude'] ?? '',
            $metas['lsd_link'] ?? '',
            $metas['lsd_email'] ?? '',
            $metas['lsd_phone'] ?? '',
            $metas['lsd_website'] ?? '',
            $metas['lsd_contact_address'] ?? '',
            $metas['lsd_remark'] ?? '',
            get_the_post_thumbnail_url($listing['ID'], 'full'),
            implode(',', $this->get_gallery($listing['ID'])),
            $this->get_taxonomies_text($taxonomies, LSD_Base::TAX_CATEGORY),
            $this->get_taxonomies_text($taxonomies, LSD_Base::TAX_LOCATION),
            $this->get_taxonomies_text($taxonomies, LSD_Base::TAX_TAG),
            $this->get_taxonomies_text($taxonomies, LSD_Base::TAX_FEATURE),
            $this->get_taxonomies_text($taxonomies, LSD_Base::TAX_LABEL),
        ];

        // Social Networks
        foreach ($this->networks as $network => $values)
        {
            $obj = $this->SN->get($network, $values);

            // Social Network is not Enabled
            if (!$obj || !$obj->option('listing')) continue;

            $row[] = $metas['lsd_' . $obj->key()] ?? '';
        }

        $attribute_context = LSD_Taxonomies_Attribute::context([
            'post_id' => (int) $listing['ID'],
            'category_slug' => $category_slug,
        ]);

        // Add attributes to data
        foreach ($this->attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type === 'separator') continue;

            if (LSD_Taxonomies_Attribute::applies($attribute->term_id, $attribute_context))
            {
                $meta_value = $metas['lsd_attribute_' . $attribute->slug] ?? '';
                $row[] = $this->normalize_export_attribute_value($meta_value, $type);
            }
            else $row[] = '';
        }

        $availability = isset($metas['lsd_ava']) && is_array($metas['lsd_ava']) ? $metas['lsd_ava'] : [];
        foreach (LSD_Main::get_weekdays() as $weekday)
        {
            $daycode = $weekday['code'];
            $hours = isset($availability[$daycode]['hours']) ? (string) $availability[$daycode]['hours'] : '';
            $off = !empty($availability[$daycode]['off']);

            $row[] = $off ? 'Off' : $hours;
        }

        return apply_filters('lsd_export_data', $row, $listing['ID']);
    }

    /**
     * To convert terms to text
     * Used in Export
     *
     * @param $taxonomies
     * @param $taxonomy
     * @return string
     */
    public function get_taxonomies_text($taxonomies, $taxonomy): string
    {
        $terms = [];
        if (isset($taxonomies[$taxonomy]) && is_array($taxonomies[$taxonomy]) && count($taxonomies[$taxonomy]))
        {
            foreach ($taxonomies[$taxonomy] as $term) $terms[] = $term['name'];
        }

        return implode(',', $terms);
    }

    protected function normalize_term_meta_value(string $meta_key, $value, string $taxonomy = '')
    {
        if ($meta_key === 'lsd_product') return $this->format_product_value($value);

        $value = apply_filters('lsd_ix_export_term_meta_value', $value, $meta_key, $taxonomy);

        if (is_array($value))
        {
            if ($meta_key === 'lsd_categories') $value = implode(',', array_keys(LSD_Taxonomies_Attribute::normalize_categories($value)));
            else $value = implode(',', $value);
        }

        return $value;
    }

    protected function format_product_value($value): string
    {
        $post_id = self::get_product_post_id_from_value($value);
        if ($post_id)
        {
            $title = get_the_title($post_id);
            if (is_string($title) && trim($title) !== '') return $title;
        }

        return '';
    }

    protected static function normalize_import_status($status, string $fallback = 'publish'): string
    {
        $status = is_string($status) ? strtolower(trim($status)) : '';
        return in_array($status, LSD_Main::allowed_statuses(), true) ? $status : $fallback;
    }

    protected static function default_import_status(array $mapping): string
    {
        $default = $mapping['post_status']['default'] ?? 'publish';
        return self::normalize_import_status($default);
    }

    public static function map(array $row, array $mapping, array $options = []): array
    {
        // Main Library
        $main = new LSD_Main();

        // Mapper
        $mapper = new LSD_IX_Mapping();

        // Get Mapped Data
        $mapped = $mapper->map($row, $mapping);
        $default_status = self::default_import_status($mapping);

        // Required Title
        $title = $mapped['post_title'] ?? '';
        if (trim($title) === '') return [];

        // Building Listing Data
        $listing = [
            'unique_id' => $mapped['unique_id'] ?? '',
            'post_title' => $title,
            'post_name' => $mapped['post_name'] ?? sanitize_title($title),
            'post_content' => $mapped['post_content'] ?? '',
            'post_excerpt' => $mapped['post_excerpt'] ?? '',
            'post_author' => $mapped['post_author'] ?? '',
            'post_date' => $mapped['post_date'] ?? '',
            'post_status' => self::normalize_import_status($mapped['post_status'] ?? '', $default_status),
            'image' => $mapped['lsd_image'] ?? '',
            'gallery' => isset($mapped['lsd_gallery']) ? explode(',', $mapped['lsd_gallery']) : '',
            'taxonomies' => [],
            'attributes' => [],
            'meta' => [],
        ];

        foreach ($mapped as $key => $value)
        {
            // Taxonomy
            if (in_array($key, $main->taxonomies()))
            {
                $listing['taxonomies'][$key] = [];
                if (is_array($value)) $value = implode(',', $value);
                if (is_null($value) || trim((string) $value) === '') continue;

                if (self::hierarchical_taxonomy_enabled($key, $options))
                {
                    $path = self::parse_hierarchy_path($value);
                    if (!count($path)) continue;

                    $leaf = end($path);
                    if (!is_string($leaf) || trim($leaf) === '') continue;

                    $listing['taxonomies'][$key][] = [
                        'name' => trim($leaf),
                        'path' => $path,
                    ];
                }
                else
                {
                    $values = explode(',', (string) $value);
                    foreach ($values as $v)
                    {
                        if (trim($v) === '') continue;
                        $listing['taxonomies'][$key][] = ['name' => trim($v)];
                    }
                }
            }
            // Attributes
            else if (strpos($key, 'lsd_attribute_') !== false)
            {
                $ex = explode('_', $key);

                $slug = $ex[2] ?? 0;
                if (!$slug) continue;

                $term = get_term_by('slug', $slug, LSD_Base::TAX_ATTRIBUTE);
                if (is_wp_error($term)) continue;

                // Add to Attributes
                $listing['attributes'][] = [
                    'term' => [
                        'name' => $term->name,
                        'slug' => $term->slug,
                    ],
                    'value' => $value ? trim($value) : '',
                ];
            }
            // ACF
            else if (strpos($key, 'lsd_acf_') !== false)
            {
                $id = substr($key, strlen('lsd_acf_'));
                if (!$id) continue;

                // Add to Attributes
                $listing['acf'][] = [
                    'key' => trim($id),
                    'value' => $value ? trim($value) : '',
                ];
            }
            // Availability
            else if (strpos($key, 'lsd_ava_') === 0)
            {
                $daycode = (int) substr($key, strlen('lsd_ava_'));
                if ($daycode < 1 || $daycode > 7) continue;

                $hours = is_string($value) || is_numeric($value) ? trim((string) $value) : '';
                if ($hours === '') continue;

                $is_off = strtolower($hours) === 'off' || $hours === '0';

                $listing['meta']['lsd_ava'][$daycode] = [
                    'hours' => $is_off ? '' : $hours,
                    'off' => $is_off ? 1 : 0,
                ];
            }
            // Meta
            else if (strpos($key, 'lsd_') !== false)
            {
                if (in_array($key, ['lsd_image', 'lsd_gallery'])) continue;

                // Add to Meta Values
                $listing['meta'][$key] = $value ? trim($value) : '';
            }
        }

        return [$listing, $mapped];
    }

    public static function map_term(array $row, array $mapping, string $taxonomy, array $options = []): array
    {
        // Mapper
        $mapper = new LSD_IX_Mapping();

        // Get Mapped Data
        $mapped = $mapper->map($row, $mapping);

        $name = isset($mapped['name']) ? trim((string) $mapped['name']) : '';
        $path = [];

        if ($name !== '' && self::hierarchical_taxonomy_enabled($taxonomy, $options))
        {
            $path = self::parse_hierarchy_path($name);

            if (count($path))
            {
                $leaf = end($path);
                $name = is_string($leaf) ? trim($leaf) : '';
            }
        }

        if ($name === '') return [];

        $term = [
            'name' => $name,
            'slug' => isset($mapped['slug']) && trim((string) $mapped['slug']) !== '' ? sanitize_title($mapped['slug']) : '',
            'description' => $mapped['description'] ?? '',
        ];

        if (count($path)) $term['path'] = $path;

        if (isset($mapped['parent_slug']) && trim((string) $mapped['parent_slug']) !== '') $term['parent_slug'] = sanitize_title($mapped['parent_slug']);
        if (isset($mapped['image']) && trim((string) $mapped['image']) !== '') $term['image'] = trim((string) $mapped['image']);

        foreach ($mapped as $key => $value)
        {
            if (in_array($key, ['name', 'slug', 'description', 'parent', 'parent_slug', 'image'], true)) continue;

            if ($taxonomy === LSD_Base::TAX_ATTRIBUTE && $key === 'lsd_categories')
            {
                $value = self::parse_attribute_categories($value);
            }

            if ($key === 'lsd_product')
            {
                $value = self::get_product_post_id_from_value($value) ?: '';
            }

            $value = apply_filters('lsd_ix_term_meta_value', $value, $key, $taxonomy, $mapped);

            $term['meta'][$key] = $value;
        }

        return [$term, $mapped];
    }

    protected static function parse_attribute_categories($value): array
    {
        if (is_array($value)) $values = $value;
        else if (is_string($value) && trim($value) !== '') $values = explode(',', $value);
        else return [];

        $categories = [];

        foreach ($values as $val)
        {
            if (is_string($val)) $val = trim($val);
            if ($val === '' || $val === null) continue;

            if (is_numeric($val))
            {
                $slug = LSD_Taxonomies::to_slug(LSD_Base::TAX_CATEGORY, $val);
            }
            else
            {
                $slug = sanitize_title($val);
                $term = $slug !== '' ? get_term_by('slug', $slug, LSD_Base::TAX_CATEGORY) : null;

                if ($term && !is_wp_error($term)) $slug = sanitize_title($term->slug);
                else
                {
                    $term = get_term_by('name', $val, LSD_Base::TAX_CATEGORY);
                    if ($term && !is_wp_error($term)) $slug = sanitize_title($term->slug);
                }
            }

            if ($slug !== '') $categories[$slug] = 1;
        }

        return $categories;
    }

    protected static function get_product_post_id_from_value($value): int
    {
        if (is_array($value)) $value = reset($value);
        if (is_numeric($value))
        {
            $post_id = (int) $value;
            return get_post_status($post_id) ? $post_id : 0;
        }

        $title = is_string($value) ? trim($value) : '';
        if ($title === '') return 0;

        $post_types = [LSD_Base::PTYPE_PLAN];
        if (post_type_exists('product')) $post_types[] = 'product';

        $post = LSD_Base::get_post_by_title($title, $post_types);
        if ($post instanceof WP_Post) return $post->ID;

        $post = get_page_by_path(sanitize_title($title), OBJECT, $post_types);
        if ($post instanceof WP_Post) return $post->ID;

        return 0;
    }

    protected static function hierarchical_taxonomy_enabled(string $taxonomy, array $options): bool
    {
        return !empty($options['hierarchical_terms'])
            && is_taxonomy_hierarchical($taxonomy)
            && in_array($taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION], true);
    }

    protected static function parse_hierarchy_path($value): array
    {
        if (!is_string($value) && !is_numeric($value)) return [];

        $parts = explode(',', (string) $value);
        $path = [];

        foreach ($parts as $part)
        {
            $part = trim((string) $part);
            if ($part === '') continue;

            $path[] = $part;
        }

        return $path;
    }
}
