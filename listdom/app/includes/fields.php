<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Fields Class.
 *
 * @class LSD_Fields
 * @version    1.0.0
 */
class LSD_Fields extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function titles(array $fields = []): array
    {
        if (empty($fields)) $fields = $this->get();

        $titles = [];
        foreach ($fields as $key => $field)
        {
            if (isset($field['label']))
            {
                $titles[$key] = $field['label'];
            }
        }

        return $titles;
    }

    public function get()
    {
        $fields = [
            'title' => ['label' => esc_html__('Listing Title', 'listdom'), 'enabled' => 1],
            'address' => ['label' => esc_html__('Address', 'listdom'), 'enabled' => 1],
            'price' => ['label' => esc_html__('Price', 'listdom'), 'enabled' => 1],
            'availability' => ['label' => esc_html__('Work Hours', 'listdom'), 'enabled' => 1],
            'phone' => ['label' => esc_html__('Phone', 'listdom'), 'enabled' => 1],
            'email' => ['label' => esc_html__('Email', 'listdom'), 'enabled' => 0],
            'labels' => ['label' => esc_html__('Labels', 'listdom'), 'enabled' => 0],
            'website' => ['label' => esc_html__('Website', 'listdom'), 'enabled' => 0],
            'image' => ['label' => esc_html__('Featured Image', 'listdom'), 'enabled' => 0],
            'description' => ['label' => esc_html__('Listing Description', 'listdom'), 'enabled' => 0],
            'remark' => ['label' => esc_html__('Remark', 'listdom'), 'enabled' => 0],
            'price_class' => ['label' => esc_html__('Price Class', 'listdom'), 'enabled' => 0],
            'contact' => ['label' => esc_html__('Contact Address', 'listdom'), 'enabled' => 0],
            'category' => ['label' => esc_html__('Category', 'listdom'), 'enabled' => 0],
            'tags' => ['label' => esc_html__('Tags', 'listdom'), 'enabled' => 0],
            'locations' => ['label' => esc_html__('Locations', 'listdom'), 'enabled' => 0],
            'features' => ['label' => esc_html__('Features', 'listdom'), 'enabled' => 0],
            'map' => ['label' => esc_html__('Map', 'listdom'), 'enabled' => 0],
        ];

        // Conditionally include or exclude fields based on specific class existence
        if (class_exists('LSDADDREV_Base')) $fields['review_stars'] = ['label' => esc_html__('Review Rates', 'listdom'), 'enabled' => 0];
        if (class_exists('LSDADDCMP_Compare')) $fields['compare'] = ['label' => esc_html__('Compare Icon', 'listdom'), 'enabled' => 0];
        if (class_exists('LSDADDFAV_Base')) $fields['favorite'] = ['label' => esc_html__('Favorite Icon', 'listdom'), 'enabled' => 0];
        if (class_exists('LSDADDCLM_Base')) $fields['claim'] = ['label' => esc_html__('Claim', 'listdom'), 'enabled' => 0];

        $SN = new LSD_Socials();
        $networks = LSD_Options::socials();

        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);
            $fields['sn_' . $obj->key()] = ['label' => $obj->label(), 'enabled' => 0];
        }

        $attributes = LSD_Main::get_attributes();
        if (is_array($attributes) && !empty($attributes))
        {
            foreach ($attributes as $attribute)
            {
                $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
                if ($type == 'separator') continue;

                $fields[$attribute->term_id] = ['label' => $attribute->name, 'enabled' => 0];
            }
        }

        // Fetch ACF Field Groups
        if (function_exists('acf_get_field_groups') && class_exists('LSDADDACF_Base'))
        {
            $field_groups = acf_get_field_groups([
                'post_type' => LSD_Base::PTYPE_LISTING,
                'post_status' => 'publish',
            ]);

            foreach ($field_groups as $acf_group)
            {
                $acf_fields = acf_get_fields($acf_group['key']);
                if ($acf_fields)
                {
                    foreach ($acf_fields as $acf_field)
                    {
                        $fields['acf_' . $acf_field['name']] = [
                            'label' => $acf_field['label'],
                            'enabled' => 0,
                        ];
                    }
                }
            }
        }

        return apply_filters('lsd_dashboard_fields', $fields);
    }

    public function content($key, LSD_Entity_Listing $listing)
    {
        $output = '';
        $table_skin = new LSD_Skins_Table();

        switch ($key)
        {
            case 'title':
                $output = '<h3 class="lsd-listing-title" ' . lsd_schema()->name() . '>' .
                    LSD_Kses::element($table_skin->get_title_tag($listing)) .
                    '</h3>';
                break;

            case 'address':
                $output = LSD_Kses::element($listing->get_address(false));
                break;

            case 'remark':
                $output = LSD_Kses::element($listing->get_remark());
                break;

            case 'labels':
                $output = LSD_Kses::element($listing->get_labels());
                break;

            case 'price':
                $output = LSD_Kses::element($listing->get_price());
                break;

            case 'email':
                $output = LSD_Kses::element($listing->get_email());
                break;

            case 'website':
                $output = get_post_meta($listing->id(), 'lsd_' . $key, true);
                break;

            case 'price_class':
                $output = LSD_Kses::element($listing->get_price_class());
                break;

            case 'image':
                $output = LSD_Kses::element($listing->get_cover_image());
                break;

            case 'phone':
                $output = LSD_Kses::element($listing->get_phone());
                break;

            case 'availability':
                $output = LSD_Kses::element($listing->get_availability(true));
                break;

            case 'locations':
                $output = LSD_Kses::element($listing->get_locations());
                break;

            case 'category':
                $output = LSD_Kses::element($listing->get_categories(true, true));
                break;

            case 'tags':
                $output = LSD_Kses::element($listing->get_tags());
                break;

            case 'contact':
                $output = get_post_meta($listing->id(), 'lsd_contact_address', true);
                break;

            case 'description':
                $output = LSD_Kses::element($listing->get_excerpt());
                break;

            case 'features':
                $output = LSD_Kses::element($listing->get_features());
                break;

            case 'review_stars':
                $output = LSD_Kses::element($listing->get_rate_stars());
                break;

            case 'compare':
                $output = LSD_Kses::element($listing->get_compare_button());
                break;

            case 'claim':
                $output = LSD_Kses::element($listing->get_claim_button());
                break;

            case 'favorite':
                $output = LSD_Kses::element($listing->get_favorite_button());
                break;

            case 'map':
                $output = LSD_Kses::element($listing->get_map());
                break;

            case is_numeric($key):
                $attributes = get_post_meta($listing->id(), 'lsd_attributes', true);
                if (!is_array($attributes)) $attributes = [];

                $att = new LSD_Entity_Attribute($key);
                $output = LSD_Kses::element($att->render($attributes[$key] ?? ''));
                break;

            case substr($key, 0, 3) === 'sn_':
                $key_without_prefix = substr($key, 3);
                $value = $listing->get_meta('lsd_' . $key_without_prefix);

                if (!empty($value)) $output = '<a href="' . esc_url($value) . '" target="_blank"><i class="lsd-icon fab fa-' . $key_without_prefix . '"></i></a>';

                break;

            case substr($key, 0, 4) === 'acf_':
                $key_without_prefix = substr($key, 4);
                $listing_id = $listing->id();

                $field = acf_get_field($key_without_prefix);
                $type = $field['type'] ?? '';

                if (in_array($type, ['tab', 'accordion', 'message'])) break;

                if ($type == 'group')
                {
                    $group = get_field_object($field['key'], $listing_id);
                    if (isset($group['sub_fields']) && is_array($group['sub_fields']) && count($group['sub_fields']))
                    {
                        foreach ($group['sub_fields'] as $sub_field)
                        {
                            $sub_field_label = $sub_field['label'] ?? '';
                            $sub_field_value = $group['value'][$sub_field['name']] ?? '';

                            if ($sub_field_label && $sub_field_value) $output .= '<h6>' . esc_html($sub_field_label) . ':</h6> <span>' . $sub_field_value . '</span>';
                        }
                    }
                }
                else $output = self::acf(get_field_object($field['key'], $listing_id), $listing_id);

                break;
        }

        return $output;
    }

    public function schema($key)
    {
        switch ($key)
        {
            case 'title':
                $output = lsd_schema()->name();
                break;

            case 'address':
                $output = lsd_schema()->address();
                break;

            case 'price':
                $output = lsd_schema()->priceRange();
                break;

            case 'phone':
                $output = lsd_schema()->telephone();
                break;

            case 'category':
                $output = lsd_schema()->category();
                break;

            case 'description':
                $output = lsd_schema()->description();
                break;

            case is_numeric($key):
                $output = LSD_Entity_Attribute::schema($key);
                break;

            default:
                $output = lsd_schema()->prop($key);
                break;
        }

        return $output;
    }

    public static function acf($field, $listing_id)
    {
        $type = $field['type'] ?? null;
        $label = $field['label'] ?? null;

        if ($type === 'image')
        {
            if (is_array($field['value']))
            {
                $title = isset($field['value']['title']) && trim($field['value']['title']) ? $field['value']['title'] : $label;
                return '<a href="' . esc_url($field['value']['url']) . '"><img src="' . esc_url($field['value']['sizes']['thumbnail']) . '" alt="' . esc_attr($title) . '"></a>';
            }
            else if (is_numeric($field['value']))
            {
                $image = wp_get_attachment_image_url($field['value']);
                return $image ? '<img src="' . esc_url($image) . '" alt="">' : '';
            }

            return '<img src="' . esc_url($field['value']) . '" alt="">';
        }
        else if ($type === 'checkbox' && is_array($field['value']))
        {
            return implode(', ', $field['value']);
        }
        else if ($type === 'file' && is_array($field['value']))
        {
            $title = isset($field['value']['title']) && trim($field['value']['title']) ? $field['value']['title'] : esc_url($field['value']['url']);
            return '<a href="' . esc_url($field['value']['url']) . '">' . esc_html($title) . '</a>';
        }
        else if ($type === 'post_object')
        {
            $ID = is_object($field['value']) ? $field['value']->ID : $field['value'];
            return '<a href="' . get_permalink($ID) . '">' . get_the_title($ID) . '</a>';
        }
        else if ($type === 'user' && is_array($field['value']))
        {
            return $field['value']['display_name'];
        }
        else if ($type === 'google_map' && is_array($field['value']))
        {
            return $field['value']['lat'] . ', ' . $field['value']['lng'];
        }
        else if ($type === 'select' && is_array($field['value']))
        {
            return implode(', ', $field['value']);
        }
        else if (!is_array($field['value']))
        {
            $value = trim($field['value']) ? $field['value'] : '';
            if (trim($value) == '') $value = get_post_meta($listing_id, $field['name'], true);

            return $value;
        }

        return '';
    }

}
