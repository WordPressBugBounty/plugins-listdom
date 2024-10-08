<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom IX Array.
 * Used in CSV and Excel
 *
 * @class LSD_IX_Array
 * @version    1.0.0
 */
abstract class LSD_IX_Array extends LSD_IX
{
    /**
     * @var LSD_Socials
     */
    protected $SN;

    /**
     * @var array
     */
    protected $networks;

    /**
     * @var WP_Term[]
     */
    protected $attributes;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Social Networks
        $this->SN = new LSD_Socials();
        $this->networks = LSD_Options::socials();

        // Attributes
        $this->attributes = LSD_Main::get_attributes();
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
            'post_status' => ['publish'],
            'posts_per_page' => '-1',
        ]);

        // Listings to Export
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
            esc_html__('ID', 'listdom-csv'),
            esc_html__('Title', 'listdom-csv'),
            esc_html__('Description', 'listdom-csv'),
            esc_html__('Date', 'listdom-csv'),
            esc_html__('Owner', 'listdom-csv'),
            esc_html__('Price', 'listdom-csv'),
            esc_html__('Price Max', 'listdom-csv'),
            esc_html__('Price Description', 'listdom-csv'),
            esc_html__('Currency', 'listdom-csv'),
            esc_html__('Address', 'listdom-csv'),
            esc_html__('Latitude', 'listdom-csv'),
            esc_html__('Longitude', 'listdom-csv'),
            esc_html__('Link', 'listdom-csv'),
            esc_html__('Email', 'listdom-csv'),
            esc_html__('Phone', 'listdom-csv'),
            esc_html__('Website', 'listdom-csv'),
            esc_html__('Contact Address', 'listdom-csv'),
            esc_html__('Remark', 'listdom-csv'),
            esc_html__('Image', 'listdom-csv'),
            esc_html__('Gallery', 'listdom-csv'),
            esc_html__('Category', 'listdom-csv'),
            esc_html__('Locations', 'listdom-csv'),
            esc_html__('Tags', 'listdom-csv'),
            esc_html__('Features', 'listdom-csv'),
            esc_html__('Labels', 'listdom-csv'),
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
            if ($type == 'separator') continue;

            $columns[] = $attribute->name;
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
        $category_id = (isset($taxonomies[LSD_Base::TAX_CATEGORY]) and is_array($taxonomies[LSD_Base::TAX_CATEGORY]) and isset($taxonomies[LSD_Base::TAX_CATEGORY][0]) and isset($taxonomies[LSD_Base::TAX_CATEGORY][0]['term_id'])) ? $taxonomies[LSD_Base::TAX_CATEGORY][0]['term_id'] : null;

        // Listing Data
        $row = [
            $listing['ID'],
            $listing['post_title'],
            $listing['post_content'],
            $listing['post_date'],
            get_the_author_meta('user_email', $listing['post_author']),
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

        // Add attributes to data
        foreach ($this->attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type == 'separator') continue;

            // Available for All Categories?
            $all_categories = get_term_meta($attribute->term_id, 'lsd_all_categories', true);
            if (trim($all_categories) == '') $all_categories = 1;

            // Specific Categories
            $categories = get_term_meta($attribute->term_id, 'lsd_categories', true);
            if ($all_categories) $categories = [];

            if ($all_categories or ($category_id and is_array($categories) and count($categories) and isset($categories[$category_id]) and $categories[$category_id])) $row[] = $metas['lsd_attribute_' . $attribute->term_id] ?? '';
            else $row[] = '';
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
        if (isset($taxonomies[$taxonomy]) and is_array($taxonomies[$taxonomy]) and count($taxonomies[$taxonomy]))
        {
            foreach ($taxonomies[$taxonomy] as $term) $terms[] = $term['name'];
        }

        return implode(',', $terms);
    }

    public static function map(array $row, array $mapping): array
    {
        // Main Library
        $main = new LSD_Main();

        // Mapper
        $mapper = new LSD_IX_Mapping();

        // Get Mapped Data
        $mapped = $mapper->map($row, $mapping);

        // Required Title
        $title = $mapped['post_title'] ?? '';
        if (trim($title) == '') return [];

        // Building Listing Data
        $listing = [
            'unique_id' => $mapped['unique_id'] ?? '',
            'post_title' => $title,
            'post_content' => $mapped['post_content'] ?? '',
            'post_author' => $mapped['post_author'] ?? '',
            'post_date' => $mapped['post_date'] ?? '',
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
                if (is_null($value) || trim($value) == '') continue;

                $values = explode(',', $value);
                foreach ($values as $v)
                {
                    if (trim($v) == '') continue;
                    $listing['taxonomies'][$key][] = ['name' => trim($v)];
                }
            }
            // Attributes
            else if (strpos($key, 'lsd_attribute_') !== false)
            {
                $ex = explode('_', $key);

                $id = $ex[2] ?? 0;
                if (!$id) continue;

                $term = get_term($id, LSD_Base::TAX_ATTRIBUTE);
                if (is_wp_error($term)) continue;

                // Add to Attributes
                $listing['attributes'][] = [
                    'term' => [
                        'name' => $term->name,
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
}
