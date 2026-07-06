<?php

class LSD_Fields extends LSD_Base
{
    public const ATTRIBUTE_KEY_PREFIX = 'attr-';

    public static function attribute_key($slug): string
    {
        $slug = sanitize_title((string) $slug);
        return $slug === '' ? '' : self::ATTRIBUTE_KEY_PREFIX . $slug;
    }

    public static function attribute_slug_from_key($key): string
    {
        $key = (string) $key;

        if (strpos($key, self::ATTRIBUTE_KEY_PREFIX) !== 0) return '';

        return sanitize_title(substr($key, strlen(self::ATTRIBUTE_KEY_PREFIX)));
    }

    public static function attribute_id_from_key($key): int
    {
        if (is_numeric($key))
        {
            $term = get_term((int) $key, LSD_Base::TAX_ATTRIBUTE);
            return $term && !is_wp_error($term) && isset($term->term_id) ? (int) $term->term_id : 0;
        }

        $slug = self::attribute_slug_from_key($key);
        if ($slug === '') return 0;

        $term = get_term_by('slug', $slug, LSD_Base::TAX_ATTRIBUTE);
        return $term && !is_wp_error($term) && isset($term->term_id) ? (int) $term->term_id : 0;
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
            'excerpt' => ['label' => esc_html__('Listing Excerpt', 'listdom'), 'enabled' => 0],
            'address' => ['label' => esc_html__('Address', 'listdom'), 'enabled' => 1],
            'price' => ['label' => esc_html__('Price', 'listdom'), 'enabled' => 1],
            'availability' => ['label' => esc_html__('Work Hours', 'listdom'), 'enabled' => 1],
            'phone' => ['label' => esc_html__('Phone', 'listdom'), 'enabled' => 1],
            'email' => ['label' => esc_html__('Email', 'listdom'), 'enabled' => 0],
            'labels' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')), 'enabled' => 0],
            'website' => ['label' => esc_html__('Website', 'listdom'), 'enabled' => 0],
            'image' => ['label' => esc_html__('Featured Image', 'listdom'), 'enabled' => 0],
            'description' => ['label' => esc_html__('Listing Description', 'listdom'), 'enabled' => 0],
            'remark' => ['label' => esc_html__('Remark', 'listdom'), 'enabled' => 0],
            'price_class' => ['label' => esc_html__('Price Class', 'listdom'), 'enabled' => 0],
            'contact' => ['label' => esc_html__('Contact Address', 'listdom'), 'enabled' => 0],
            'cta' => ['label' => esc_html__('Call to Action', 'listdom'), 'enabled' => 0],
            'category' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY)), 'enabled' => 0],
            'tags' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')), 'enabled' => 0],
            'locations' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')), 'enabled' => 0],
            'features' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')), 'enabled' => 0],
            'map' => ['label' => esc_html__('Map', 'listdom'), 'enabled' => 0],
        ];

        if (!LSD_Components::pricing()) unset($fields['price'], $fields['price_class']);
        if (!LSD_Components::cta()) unset($fields['cta']);
        if (!LSD_Components::work_hours()) unset($fields['availability']);
        if (!LSD_Components::map()) unset($fields['address'], $fields['map']);

        // Conditionally include or exclude fields based on specific class existence
        if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)) $fields['review_stars'] = ['label' => esc_html__('Review Rates', 'listdom'), 'enabled' => 0];
        if (class_exists(\LSDPACCMP\Base::class)) $fields['compare'] = ['label' => esc_html__('Compare', 'listdom'), 'enabled' => 0];
        if (class_exists(\LSDPACFAV\Base::class)) $fields['favorite'] = ['label' => esc_html__('Favorite', 'listdom'), 'enabled' => 0];
        if (class_exists(\LSDPACCLM\Base::class)) $fields['claim'] = ['label' => esc_html__('Claim', 'listdom'), 'enabled' => 0];

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

                $slug = isset($attribute->slug) ? sanitize_title($attribute->slug) : '';
                $key = self::attribute_key($slug);
                if ($key === '') continue;

                $fields[$key] = [
                    'label' => $attribute->name,
                    'enabled' => 0,
                    'attribute_id' => (int) $attribute->term_id,
                    'attribute_slug' => $slug,
                    'legacy_key' => (string) $attribute->term_id,
                ];
            }
        }

        // Fetch ACF Field Groups
        if (function_exists('acf_get_field_groups') && (class_exists(LSDADDACF::class) || class_exists(\LSDPACACF\Base::class)))
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

    public function content($key, LSD_Entity_Listing $listing, $skin)
    {
        $output = '';
        $attribute_id = self::attribute_id_from_key($key);

        if ($attribute_id > 0)
        {
            $att = new LSD_Entity_Attribute($attribute_id);
            return LSD_Kses::element($att->render($att->value($listing->id())));
        }

        switch ($key)
        {
            case 'title':
                $output = '<h3 class="lsd-listing-title" ' . lsd_schema()->name() . '>' .
                    LSD_Kses::element($skin->get_title_tag($listing)) .
                    '</h3>';
                break;

            case 'address':
                $output = LSD_Kses::element($listing->get_address(false));
                break;

            case 'excerpt':
                $output = LSD_Kses::element($listing->get_excerpt());
                break;

            case 'remark':
                $output = LSD_Kses::element($listing->get_remark());
                break;

            case 'labels':
                $output = LSD_Kses::element($listing->get_labels());
                break;

            case 'price':
                if (LSD_Components::pricing()) $output = LSD_Kses::element($listing->get_price());
                break;

            case 'email':
                $output = LSD_Kses::element($listing->get_email());
                break;

            case 'website':
                $output = get_post_meta($listing->id(), 'lsd_' . $key, true);
                break;

            case 'price_class':
                if (LSD_Components::pricing()) $output = LSD_Kses::element($listing->get_price_class());
                break;

            case 'image':
                $output = LSD_Kses::element($listing->get_cover_image([390, 260], $skin->get_listing_link_method(), $skin->get_single_listing_style()));
                break;

            case 'phone':
                $output = LSD_Kses::element($listing->get_phone());
                break;

            case 'availability':
                if (LSD_Components::work_hours()) $output = LSD_Kses::element($listing->get_availability(true));
                break;

            case 'locations':
                $output = LSD_Kses::element($listing->get_locations());
                break;

            case 'category':
                $output = LSD_Kses::element($listing->get_categories(['show_color' => true, 'multiple_categories' => true]));
                break;

            case 'tags':
                $output = LSD_Kses::element($listing->get_tags());
                break;

            case 'contact':
                $output = get_post_meta($listing->id(), 'lsd_contact_address', true);
                break;

            case 'description':
                $output = LSD_Kses::element($listing->get_excerpt(50));
                break;

            case 'cta':
                if (LSD_Components::cta()) $output = '<div class="lsd-listing-cta lsd-cta-align-left">' . LSD_Kses::element($listing->get_cta('table')) . '</div>';
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
                $output = $listing->is_claimed()
                    ? '<span class="lsd-tooltip" data-lsd-tooltip="' . esc_attr__('Verified', 'listdom') . '"><i class="lsd-fe-icon fas fa-check-circle lsd-claimed-icon"></i></span>'
                    : LSD_Kses::element($listing->get_claim_button());
                break;

            case 'favorite':
                $output = LSD_Kses::element($listing->get_favorite_button());
                break;

            case 'map':
                $output = LSD_Kses::element($listing->get_map());
                break;

            case substr($key, 0, 3) === 'sn_':
                $key_without_prefix = substr($key, 3);
                $value = $listing->get_meta('lsd_' . $key_without_prefix);

                if (!empty($value)) $output = '<a href="' . esc_url($value) . '" target="_blank"><i class="lsd-fe-icon fab fa-' . $key_without_prefix . '"></i></a>';
                break;

            case substr($key, 0, 4) === 'acf_':
                $key_without_prefix = substr($key, 4);
                $listing_id = $listing->id();

                $field = acf_get_field($key_without_prefix);
                $type = $field['type'] ?? '';

                if (in_array($type, ['tab', 'accordion', 'message'])) break;

                $output = self::acf(get_field_object($field['key'], $listing_id), $listing_id);
                if (!is_string($output)) $output = is_scalar($output) ? (string) $output : '';
                break;
        }

        return $output;
    }

    public function schema($key)
    {
        $output = '';
        $attribute_id = self::attribute_id_from_key($key);

        if ($attribute_id > 0) return LSD_Entity_Attribute::schema($attribute_id);

        switch ($key)
        {
            case 'title':
                $output = lsd_schema()->name();
                break;

            case 'address':
                $output = lsd_schema()->address();
                break;

            case 'price':
                if (LSD_Components::pricing()) $output = lsd_schema()->priceRange();
                break;

            case 'phone':
                $output = lsd_schema()->telephone();
                break;

            case 'category':
                $output = lsd_schema()->category();
                break;

            case 'excerpt':
            case 'description':
                $output = lsd_schema()->description();
                break;

            default:
                $normalized = strtolower(trim((string) $key));
                $normalized = preg_replace('/([a-z])([A-Z])/', '$1_$2', $normalized);
                $normalized = preg_replace('/[\s\-]+/', '_', $normalized);
                $normalized = preg_replace('/_+/', '_', $normalized);

                $simplified = str_replace('_', '', $normalized);

                $map = [
                    'website' => 'url',
                    'web' => 'url',
                    'site' => 'url',
                    'link' => 'url',
                    'socials' => 'sameAs',
                    'social_links' => 'sameAs',
                    'sociallinks' => 'sameAs',
                    'email' => 'email',
                    'fax' => 'faxNumber',
                    'opening_hours' => 'openingHours',
                    'openinghours' => 'openingHours',
                ];

                if (isset($map[$normalized])) $output = lsd_schema()->prop($map[$normalized]);
                else if (isset($map[$simplified])) $output = lsd_schema()->prop($map[$simplified]);
                break;
        }

        return $output;
    }

    public static function acf($field, $listing_id)
    {
        if (!is_array($field)) return '';

        $type = $field['type'] ?? null;
        $label = $field['label'] ?? null;
        $value = $field['value'] ?? null;

        if ($type === 'image')
        {
            if (is_array($value))
            {
                $title = isset($value['title']) && trim($value['title']) ? $value['title'] : $label;
                $thumbnail = $value['sizes']['thumbnail'] ?? ($value['url'] ?? '');
                $url = $value['url'] ?? '';

                if ($thumbnail && $url) return '<a href="' . esc_url($url) . '"><img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '"></a>';
                else if ($thumbnail) return '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '">';

                return '';
            }
            else if (is_numeric($value))
            {
                $image = wp_get_attachment_image_url($value);
                return $image ? '<img src="' . esc_url($image) . '" alt="">' : '';
            }

            return $value ? '<img src="' . esc_url($value) . '" alt="">' : '';
        }
        else if ($type === 'gallery' && is_array($value))
        {
            $output = '';

            foreach ($value as $image)
            {
                $rendered = self::acf([
                    'type' => 'image',
                    'label' => $label,
                    'value' => $image,
                ], $listing_id);

                if (trim($rendered) !== '') $output .= '<span class="lsd-acf-gallery-item">' . $rendered . '</span>';
            }

            return $output ? '<div class="lsd-acf-gallery">' . $output . '</div>' : '';
        }
        else if ($type === 'checkbox' && is_array($value))
        {
            $items = [];

            foreach ($value as $item)
            {
                $rendered = self::acf_choice_value($item);
                if ($rendered !== '') $items[] = $rendered;
            }

            return implode(', ', $items);
        }
        else if (in_array($type, ['select', 'radio', 'button_group'], true) && is_array($value))
        {
            if (self::acf_is_choice_array($value))
            {
                return self::acf_choice_value($value);
            }

            $items = [];

            foreach ($value as $item)
            {
                $rendered = self::acf_choice_value($item);
                if ($rendered !== '') $items[] = $rendered;
            }

            return implode(', ', $items);
        }
        else if ($type === 'file' && is_array($value))
        {
            $url = $value['url'] ?? '';
            if (!$url) return '';

            $title = isset($value['title']) && trim($value['title']) ? $value['title'] : $url;
            return '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        }
        else if ($type === 'link' && is_array($value))
        {
            $url = $value['url'] ?? '';
            if (!$url) return '';

            $title = isset($value['title']) && trim($value['title']) ? $value['title'] : $url;
            return '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        }
        else if (in_array($type, ['post_object', 'page_link', 'relationship'], true))
        {
            if (is_array($value))
            {
                $items = [];

                foreach ($value as $item)
                {
                    $rendered = self::acf_linked_post_value($item);
                    if ($rendered) $items[] = $rendered;
                }

                return implode(', ', $items);
            }

            return self::acf_linked_post_value($value);
        }
        else if ($type === 'taxonomy')
        {
            if (is_array($value))
            {
                $items = [];

                foreach ($value as $term)
                {
                    $rendered = self::acf_term_value($term);
                    if ($rendered) $items[] = $rendered;
                }

                return implode(', ', $items);
            }

            return self::acf_term_value($value);
        }
        else if ($type === 'user')
        {
            if (is_array($value) && isset($value['display_name'])) return $value['display_name'];
            else if (is_array($value))
            {
                $items = [];

                foreach ($value as $user)
                {
                    $rendered = self::acf_user_value($user);
                    if ($rendered) $items[] = $rendered;
                }

                return implode(', ', $items);
            }

            return self::acf_user_value($value);
        }
        else if ($type === 'google_map' && is_array($value))
        {
            $parts = [];

            if (isset($value['address']) && trim($value['address']) !== '') $parts[] = $value['address'];
            else
            {
                $lat = $value['lat'] ?? null;
                $lng = $value['lng'] ?? null;

                if ($lat !== null && $lng !== null) $parts[] = $lat . ', ' . $lng;
            }

            return implode(' ', $parts);
        }
        else if ($type === 'group')
        {
            return is_array($value)
                ? self::acf_compound_rows($field['sub_fields'] ?? [], $value, $listing_id)
                : '';
        }
        else if ($type === 'repeater')
        {
            return is_array($value)
                ? self::acf_repeater_rows($field['sub_fields'] ?? [], $value, $listing_id, $label)
                : '';
        }
        else if ($type === 'flexible_content' && is_array($value))
        {
            $output = '';

            foreach ($value as $index => $row)
            {
                if (!is_array($row)) continue;

                $layout_name = $row['acf_fc_layout'] ?? '';
                $layout = null;

                if (isset($field['layouts']) && is_array($field['layouts']))
                {
                    foreach ($field['layouts'] as $candidate)
                    {
                        if (($candidate['name'] ?? '') === $layout_name)
                        {
                            $layout = $candidate;
                            break;
                        }
                    }
                }

                $sub_fields = $layout['sub_fields'] ?? [];
                $rendered = self::acf_compound_rows($sub_fields, $row, $listing_id);

                if (trim($rendered) === '') continue;

                $layout_label = $layout['label'] ?? sprintf(esc_html__('Row %d', 'listdom'), $index + 1);
                $output .= '<div class="lsd-acf-flexible-layout"><h6>' . esc_html($layout_label) . '</h6>' . $rendered . '</div>';
            }

            return $output ? '<div class="lsd-acf-flexible-content">' . $output . '</div>' : '';
        }
        else if ($type === 'clone')
        {
            if (isset($field['sub_fields']) && is_array($field['sub_fields']) && is_array($value))
            {
                return self::acf_compound_rows($field['sub_fields'], $value, $listing_id);
            }

            return '';
        }
        else if (in_array($type, ['gallery', 'flexible_content'], true))
        {
            return '';
        }
        else if ($type === 'icon_picker')
        {
            $icon = get_field($field['name'], $listing_id);

            if (filter_var($icon, FILTER_VALIDATE_URL)) return '<img alt="" src="'.esc_url($icon).'">';
            else return '<span class="dashicons '.sanitize_html_class($icon).'"></span>';
        }
        else if ($type === 'true_false')
        {
            return $value ? esc_html__('Yes', 'listdom') : esc_html__('No', 'listdom');
        }
        else if (!is_array($value))
        {
            $value = trim((string) $value) ? $value : '';
            if (trim($value) === '') $value = get_post_meta($listing_id, $field['name'], true);

            return $value;
        }

        return self::acf_fallback_value($value);
    }

    protected static function acf_compound_rows(array $sub_fields, array $values, $listing_id)
    {
        $output = '';

        foreach ($sub_fields as $sub_field)
        {
            $type = $sub_field['type'] ?? '';
            $name = $sub_field['name'] ?? '';
            $sub_label = $sub_field['label'] ?? '';

            if (in_array($type, ['tab', 'accordion', 'message'], true) || !$name) continue;

            $sub_field['value'] = $values[$name] ?? null;
            $rendered = self::acf($sub_field, $listing_id);

            if (trim($rendered) === '') continue;

            $output .= '<div class="lsd-acf-sub-field lsd-acf-sub-field-' . sanitize_html_class($type) . '">';
            if ($sub_label) $output .= '<span class="lsd-attr-key">' . esc_html($sub_label) . ': </span>';
            $output .= '<span class="lsd-attr-value">' . esc_html($rendered) . '</span>';
            $output .= '</div>';
        }

        return $output ? '<div class="lsd-acf-compound">' . $output . '</div>' : '';
    }

    protected static function acf_repeater_rows(array $sub_fields, array $rows, $listing_id, $label = '')
    {
        if (!count($rows)) return '';

        $output = '';

        foreach ($rows as $index => $row)
        {
            if (!is_array($row)) continue;

            $rendered = self::acf_compound_rows($sub_fields, $row, $listing_id);
            if (trim($rendered) === '') continue;

            $row_label = sprintf(esc_html__('Row %d', 'listdom'), $index + 1);
            $output .= '<div class="lsd-acf-repeater-row"><h6>' . esc_html($row_label) . '</h6>' . $rendered . '</div>';
        }

        return $output ? '<div class="lsd-acf-repeater">' . $output . '</div>' : '';
    }

    protected static function acf_linked_post_value($value)
    {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
        {
            return '<a href="' . esc_url($value) . '">' . esc_html($value) . '</a>';
        }

        $ID = is_object($value) ? ($value->ID ?? 0) : $value;
        $ID = absint($ID);

        if (!$ID) return '';

        return '<a href="' . esc_url(get_permalink($ID)) . '">' . esc_html(get_the_title($ID)) . '</a>';
    }

    protected static function acf_term_value($value)
    {
        if (is_object($value) && isset($value->name)) return $value->name;

        $term = get_term($value);
        return $term && !is_wp_error($term) ? $term->name : '';
    }

    protected static function acf_user_value($value)
    {
        if (is_object($value) && isset($value->display_name)) return $value->display_name;

        $user = get_user_by('id', absint($value));
        return $user ? $user->display_name : '';
    }

    protected static function acf_fallback_value($value)
    {
        if (is_scalar($value))
        {
            $value = (string) $value;
            return trim($value) !== '' ? esc_html($value) : '';
        }

        if (!is_array($value) || !count($value)) return '';

        $items = [];

        foreach ($value as $item)
        {
            $rendered = self::acf_fallback_value($item);
            if ($rendered !== '') $items[] = $rendered;
        }

        return count($items) ? implode(', ', $items) : '';
    }

    protected static function acf_choice_value($value)
    {
        if (is_scalar($value))
        {
            $value = (string) $value;
            return trim($value) !== '' ? esc_html($value) : '';
        }

        if (!is_array($value)) return '';

        if (isset($value['label']) && trim((string) $value['label']) !== '')
        {
            return esc_html($value['label']);
        }

        if (isset($value['value']) && !is_array($value['value']))
        {
            $choice = (string) $value['value'];
            return trim($choice) !== '' ? esc_html($choice) : '';
        }

        return self::acf_fallback_value($value);
    }

    protected static function acf_is_choice_array(array $value): bool
    {
        return array_key_exists('label', $value) || array_key_exists('value', $value);
    }
}
