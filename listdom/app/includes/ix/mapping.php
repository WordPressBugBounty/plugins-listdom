<?php

class LSD_IX_Mapping extends LSD_IX
{
    public function listdom_fields()
    {
        // Default Value
        $default = new LSD_IX_Mapping_Default();

        $fields = [
            'unique_id' => [
                'label' => esc_html__('Unique ID', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("It's required if you want to update the listings later. If you don't map it, then Listdom tries to update an existing listing with the same title and content!", 'listdom'),
                'default' => false,
            ],
            'post_title' => [
                'label' => esc_html__('Listing Title', 'listdom'),
                'type' => 'text',
                'mandatory' => true,
                'default' => false,
            ],
            'post_name' => [
                'label' => esc_html__('Listing Slug', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("If you do not map it, then the listing title will be used to generate the slug.", 'listdom'),
                'default' => false,
            ],
            'post_content' => [
                'label' => esc_html__('Listing Content', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'default' => false,
            ],
            'post_excerpt' => [
                'label' => esc_html__('Listing Excerpt', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'default' => false,
            ],
            'post_date' => [
                'label' => esc_html__('Listing Date', 'listdom'),
                'type' => 'date',
                'mandatory' => false,
                'description' => esc_html__("A date field needs to be mapped.", 'listdom'),
                'default' => [$default, 'date'],
            ],
            'post_author' => [
                'label' => esc_html__('Listing Owner', 'listdom'),
                'type' => 'email',
                'mandatory' => false,
                'description' => esc_html__("An email field should get mapped. If mapped, then Listdom will create a user if one does not exist and assign the listing to that user.", 'listdom'),
                'default' => [$default, 'email'],
            ],
            'post_status' => [
                'label' => esc_html__('Listing Status', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field can be mapped. If you don't map it or the feed value is invalid, the selected default value will be used.", 'listdom'),
                'default' => [$default, 'status'],
            ],
            'lsd_price' => [
                'label' => esc_html__('Price', 'listdom'),
                'type' => 'number',
                'mandatory' => false,
                'description' => esc_html__("A numeric field needs to be mapped.", 'listdom'),
                'default' => [$default, 'number'],
            ],
            'lsd_price_max' => [
                'label' => esc_html__('Price Max', 'listdom'),
                'type' => 'number',
                'mandatory' => false,
                'description' => esc_html__("A numeric field needs to be mapped.", 'listdom'),
                'default' => [$default, 'number'],
            ],
            'lsd_price_after' => [
                'label' => esc_html__('Price Description', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_currency' => [
                'label' => esc_html__('Currency', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field needs to be mapped.", 'listdom'),
                'default' => [$default, 'currency'],
            ],
            'lsd_address' => [
                'label' => esc_html__('Listing Address', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_latitude' => [
                'label' => esc_html__('Listing Latitude', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A latitude field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_longitude' => [
                'label' => esc_html__('Listing Longitude', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A longitude field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_link' => [
                'label' => esc_html__('Listing Link', 'listdom'),
                'type' => 'url',
                'mandatory' => false,
                'description' => esc_html__("A URL field needs to be mapped.", 'listdom'),
                'default' => [$default, 'url'],
            ],
            'lsd_email' => [
                'label' => esc_html__('Listing Email', 'listdom'),
                'type' => 'email',
                'mandatory' => false,
                'description' => esc_html__("An email field needs to be mapped.", 'listdom'),
                'default' => [$default, 'email'],
            ],
            'lsd_phone' => [
                'label' => esc_html__('Listing Phone', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("An phone field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_website' => [
                'label' => esc_html__('Listing Website', 'listdom'),
                'type' => 'url',
                'mandatory' => false,
                'description' => esc_html__("A URL field needs to be mapped.", 'listdom'),
                'default' => [$default, 'url'],
            ],
            'lsd_contact_address' => [
                'label' => esc_html__('Listing Contact Address', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_remark' => [
                'label' => esc_html__('Listing Remark', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("An text field needs to be mapped.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            'lsd_image' => [
                'label' => esc_html__('Featured Image', 'listdom'),
                'type' => 'url',
                'mandatory' => false,
                'description' => esc_html__("A URL field should be mapped. It should contain an image URL.", 'listdom'),
                'default' => [$default, 'url'],
            ],
            'lsd_gallery' => [
                'label' => esc_html__('Listing Gallery', 'listdom'),
                'type' => 'url',
                'mandatory' => false,
                'description' => esc_html__("A comma-separated multiple URL field should be mapped. It should contain URLs to images.", 'listdom'),
                'default' => [$default, 'url'],
            ],
            LSD_Base::TAX_CATEGORY => [
                'label' => sprintf(esc_html__('Listing %s', 'listdom'), esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY))),
                'type' => 'text',
                'mandatory' => false,
                'description' => sprintf(
                    esc_html__("A text field should be mapped. Listdom will create a %s using the text if one does not exist and assign the listing to that %s.", 'listdom'),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_CATEGORY)),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_CATEGORY))
                ),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_LOCATION => [
                'label' => sprintf(esc_html__('Listing %s', 'listdom'), esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural'))),
                'type' => 'text',
                'mandatory' => false,
                'description' => sprintf(
                    esc_html__("A text field should be mapped. Listdom will create a %s using the text if one does not exist and assign the listing to that %s.", 'listdom'),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_LOCATION)),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_LOCATION))
                ),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_TAG => [
                'label' => sprintf(esc_html__('Listing %s', 'listdom'), esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural'))),
                'type' => 'text',
                'mandatory' => false,
                'description' => sprintf(
                    esc_html__("A text field should be mapped. Listdom will create a %s using the text if one does not exist and assign the listing to that %s.", 'listdom'),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_TAG)),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_TAG))
                ),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_FEATURE => [
                'label' => sprintf(esc_html__('Listing %s', 'listdom'), esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural'))),
                'type' => 'text',
                'mandatory' => false,
                'description' => sprintf(
                    esc_html__("A text field should be mapped. Listdom will create a %s using the text if one does not exist and assign the listing to that %s.", 'listdom'),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_FEATURE)),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_FEATURE))
                ),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_LABEL => [
                'label' => sprintf(esc_html__('Listing %s', 'listdom'), esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural'))),
                'type' => 'text',
                'mandatory' => false,
                'description' => sprintf(
                    esc_html__("A text field should be mapped. Listdom will create a %s using the text if one does not exist and assign the listing to that %s.", 'listdom'),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_LABEL)),
                    esc_html(lsd_t_label_lc(LSD_Base::TAX_LABEL))
                ),
                'default' => [$default, 'text'],
            ],
        ];

        // Social Networks
        $SN = new LSD_Socials();

        $networks = LSD_Options::socials();
        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);

            // Social Network is not Enabled
            if (!$obj || !$obj->option('listing')) continue;

            // Input Type
            $type = $obj->get_input_type();

            $fields['lsd_' . $obj->key()] = [
                'label' => esc_html($obj->label()),
                'type' => $type,
                'mandatory' => false,
                'description' => sprintf(
                    /* translators: %s: Field type label. */
                    esc_html__("A %s field should be mapped.", 'listdom'),
                    $type
                ),
                'default' => [$default, $type],
            ];
        }

        // Attributes
        $attributes = LSD_Main::get_attributes();

        foreach ($attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type === 'separator') continue;

            $mapping_type = in_array($type, ['number', 'email', 'url', 'tel', 'date', 'time', 'datetime'], true) ? $type : 'text';
            if ($type === 'file') $mapping_type = 'url';

            $default_type = in_array($mapping_type, ['time', 'datetime'], true) ? 'text' : $mapping_type;

            $fields['lsd_attribute_' . $attribute->slug] = [
                'label' => $attribute->name,
                'type' => $mapping_type,
                'mandatory' => false,
                'description' => sprintf(
                    /* translators: %s: Attribute field type. */
                    esc_html__("A %s field should be mapped.", 'listdom'),
                    $mapping_type
                ),
                'default' => [$default, $default_type],
            ];
        }

        // Availability
        foreach (LSD_Main::get_weekdays() as $weekday)
        {
            $fields['lsd_ava_' . $weekday['code']] = [
                'label' => sprintf(
                    /* translators: %s: Weekday label. */
                    esc_html__('%s Hours', 'listdom'),
                    $weekday['label'] ?? $weekday['day']
                ),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("Provide the working hours for this weekday. Use Off or 0 to mark the day as closed.", 'listdom'),
                'default' => [$default, 'text'],
            ];
        }

        // Apply Filters
        return apply_filters('lsd_ix_listdom_fields', $fields);
    }

    public function fields_for_type(string $type = 'listings'): array
    {
        $type = LSD_IX::normalize_import_type($type);

        if ($taxonomy = LSD_IX::taxonomy_for_type($type)) return $this->term_fields($taxonomy);

        return $this->listdom_fields();
    }

    public function term_fields(string $taxonomy): array
    {
        $fields = [
            'name' => [
                'label' => esc_html__('Name', 'listdom'),
                'type' => 'text',
                'mandatory' => true,
                'default' => false,
            ],
            'slug' => [
                'label' => esc_html__('Slug', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("If left empty, the slug will be generated from the name.", 'listdom'),
                'default' => false,
            ],
            'description' => [
                'label' => esc_html__('Description', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'default' => false,
            ],
        ];

        if (is_taxonomy_hierarchical($taxonomy))
        {
            $fields['parent_slug'] = [
                'label' => esc_html__('Parent Slug', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__('You can also set the parent using the slug.', 'listdom'),
                'default' => false,
            ];
        }

        $fields['image'] = [
            'label' => esc_html__('Image', 'listdom'),
            'type' => 'url',
            'mandatory' => false,
            'description' => esc_html__("A URL to a term image (where supported).", 'listdom'),
            'default' => false,
        ];

        $meta_fields = $this->taxonomy_meta_fields($taxonomy);

        foreach ($meta_fields as $key => $meta_field) $fields[$key] = $meta_field;

        // Apply Filters
        return apply_filters('lsd_ix_term_fields', $fields, $taxonomy);
    }

    public function taxonomy_meta_fields(string $taxonomy): array
    {
        $fields = [];

        $default_meta = [
            LSD_Base::TAX_CATEGORY => ['lsd_icon', 'lsd_color', 'lsd_disabled_icon', 'lsd_schema'],
            LSD_Base::TAX_LOCATION => [],
            LSD_Base::TAX_TAG => [],
            LSD_Base::TAX_FEATURE => ['lsd_icon', 'lsd_itemprop'],
            LSD_Base::TAX_LABEL => ['lsd_color'],
            LSD_Base::TAX_ATTRIBUTE => ['lsd_field_type', 'lsd_values', 'lsd_file_extensions', 'lsd_file_max_size', 'lsd_icon', 'lsd_disabled_icon', 'lsd_required', 'lsd_editor', 'lsd_link_label', 'lsd_all_categories', 'lsd_categories', 'lsd_index'],
        ];

        if (isset($default_meta[$taxonomy]))
        {
            foreach ($default_meta[$taxonomy] as $meta_key) $fields[$meta_key] = $this->meta_field($meta_key);
        }

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_array($terms))
        {
            foreach ($terms as $term)
            {
                $metas = get_term_meta($term->term_id, '', true);
                if (!is_array($metas)) continue;

                foreach (array_keys($metas) as $meta_key)
                {
                    if ($meta_key === 'lsd_image') continue;
                    if ($taxonomy === LSD_Base::TAX_CATEGORY && in_array($meta_key, ['lsd_elements', 'lsd_elements_global'], true)) continue;
                    if (isset($fields[$meta_key])) continue;

                    $fields[$meta_key] = $this->meta_field($meta_key);
                }
            }
        }

        return apply_filters('lsd_ix_taxonomy_meta_fields', $fields, $taxonomy);
    }

    protected function meta_field(string $meta_key): array
    {
        if ($meta_key === 'lsd_product')
        {
            return [
                'label' => lsd_payment()->plan_label(),
                'type' => 'text',
                'mandatory' => false,
                'default' => false,
            ];
        }

        return [
            'label' => ucwords(str_replace(['lsd_', '_'], ['', ' '], $meta_key)),
            'type' => 'text',
            'mandatory' => false,
            'default' => false,
        ];
    }

    /**
     * @param string $file
     * @return array
     */
    public function feed_fields(string $file): array
    {
        $ex = explode('.', $file);
        $extension = strtolower(end($ex));

        $fields = [];
        switch ($extension)
        {
            case 'csv':

                $fh = fopen($file, 'r');
                $delimiter = $this->delimiter($file);

                $row = fgetcsv($fh, 0, $delimiter);
                if ($row !== false)
                {
                    foreach ($row as $k => $v)
                    {
                        $v = $this->unbom($v);
                        $fields[$k] = mb_convert_encoding($v, 'UTF-8', mb_detect_encoding($v));
                    }
                }

                fclose($fh);
                break;

            default:
                return $fields;
        }

        return $fields;
    }

    /**
     * @param array $raw
     * @param array $mappings
     * @return array
     */
    public function map(array $raw, array $mappings): array
    {
        $mapped = [];
        foreach ($mappings as $key => $mapping)
        {
            $field = isset($mapping['map']) && trim($mapping['map']) !== '' ? $mapping['map'] : null;
            $default = isset($mapping['default']) && trim($mapping['default']) !== '' ? $mapping['default'] : null;

            // Not Mapped
            if (is_null($field) && is_null($default)) continue;

            // Value
            $value = !is_null($field) && isset($raw[$field]) && trim($raw[$field]) !== '' ? $raw[$field] : $default;

            // Normalize the Value
            if ($value && !preg_match('!!u', $value))
            {
                $detected_encoding = mb_detect_encoding($value, mb_detect_order(), true);
                $value = mb_convert_encoding($value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
            }

            // Add to Mapped Data
            $mapped[$key] = $value;
        }

        // Latitude & Longitude by Address
        if ((!isset($mapped['lsd_latitude']) || !isset($mapped['lsd_longitude'])) && isset($mapped['lsd_address']) && trim($mapped['lsd_address']))
        {
            $main = new LSD_Main();
            $geopoint = $main->geopoint($mapped['lsd_address']);

            if (isset($geopoint[0]) && $geopoint[0] && isset($geopoint[1]) && $geopoint[1])
            {
                $mapped['lsd_latitude'] = $geopoint[0];
                $mapped['lsd_longitude'] = $geopoint[1];
            }
        }

        return $mapped;
    }

    public function unbom($text)
    {
        $bom = pack('H*', 'EFBBBF');

        $text = str_replace("\xEF\xBB\xBF", '', $text);
        return preg_replace("/^$bom/", '', $text);
    }

    public function delimiter($csv)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        $handle = fopen($csv, 'r');
        $first_line = fgets($handle);
        fclose($handle);

        foreach ($delimiters as $delimiter => &$count)
        {
            $count = count(str_getcsv($first_line, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }
}
