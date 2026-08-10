<?php

class LSD_IX_Mapping extends LSD_IX
{
    public static function listing_taxonomies(): array
    {
        return [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_TAG,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ];
    }

    public static function is_listing_taxonomy(string $key): bool
    {
        return in_array($key, self::listing_taxonomies(), true);
    }

    public static function is_boolean_samples(array $samples): bool
    {
        $values = [];
        foreach ($samples as $sample)
        {
            if (!is_scalar($sample)) continue;

            $value = strtolower(trim((string) $sample));
            if ($value === '') continue;

            $values[$value] = $value;
        }

        if (!count($values)) return false;

        $values = array_values($values);
        $allowed = ['yes', 'no', 'y', 'n', 'true', 'false', '1', '0'];
        if (count(array_diff($values, $allowed))) return false;

        $word_values = ['yes', 'no', 'y', 'n', 'true', 'false'];
        return count(array_intersect($values, $word_values)) > 0
            || (in_array('1', $values, true) && in_array('0', $values, true));
    }

    protected static function normalize_source_column($value): ?int
    {
        if (is_int($value)) return $value >= 0 ? $value : null;
        if (!is_string($value) || !preg_match('/^\d+$/', $value)) return null;

        return (int) $value;
    }

    public static function taxonomy_sources(array $mapping): array
    {
        $sources = [];
        if (isset($mapping['sources']) && is_array($mapping['sources']))
        {
            foreach ($mapping['sources'] as $source)
            {
                if (!is_array($source) || !isset($source['column'])) continue;

                $column = self::normalize_source_column($source['column']);
                if (is_null($column)) continue;

                $sources[] = [
                    'column' => $column,
                    'label' => isset($source['label']) && is_scalar($source['label']) ? sanitize_text_field((string) $source['label']) : '',
                    'mode' => isset($source['mode']) && $source['mode'] === 'label' ? 'label' : 'value',
                ];
            }
        }

        if (count($sources)) return $sources;

        if (isset($mapping['map']) && is_scalar($mapping['map']) && trim((string) $mapping['map']) !== '')
        {
            $column = self::normalize_source_column((string) $mapping['map']);
            if (is_null($column)) return $sources;

            $sources[] = [
                'column' => $column,
                'label' => '',
                'mode' => 'value',
            ];
        }

        return $sources;
    }

    public static function taxonomy_mode(array $mapping): string
    {
        return isset($mapping['taxonomy_mode']) && $mapping['taxonomy_mode'] === 'hierarchy' ? 'hierarchy' : 'flat';
    }

    protected static function taxonomy_mapping_value(array $raw, array $mapping, string $taxonomy, array $options = []): ?array
    {
        $sources = self::taxonomy_sources($mapping);
        $mode = self::taxonomy_mode($mapping);
        $global_hierarchy = $mode === 'flat'
            && !empty($options['hierarchical_terms'])
            && in_array($taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION], true);
        if ($global_hierarchy) $mode = 'hierarchy';
        $values = [];
        $explicit_false = false;

        foreach ($sources as $source)
        {
            if ($mode === 'hierarchy' && $source['mode'] === 'label') continue;

            $column = $source['column'];
            if (!array_key_exists($column, $raw) || !is_scalar($raw[$column])) continue;

            $value = (string) $raw[$column];
            if ($value !== '' && !preg_match('!!u', $value))
            {
                $detected_encoding = mb_detect_encoding($value, mb_detect_order(), true);
                $value = mb_convert_encoding($value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
            }
            $value = trim($value);
            if ($source['mode'] === 'label')
            {
                $normalized = strtolower($value);
                if (in_array($normalized, ['no', 'n', 'false', '0'], true)) $explicit_false = true;
                if (in_array($normalized, ['yes', 'y', 'true', '1'], true) && $source['label'] !== '') $values[] = $source['label'];
                continue;
            }

            if ($value === '') continue;
            if ($mode === 'hierarchy' && $global_hierarchy)
            {
                foreach (explode(',', $value) as $segment)
                {
                    $segment = trim($segment);
                    if ($segment !== '') $values[] = $segment;
                }
            }
            else if ($mode === 'hierarchy') $values[] = $value;
            else
            {
                foreach (explode(',', $value) as $term)
                {
                    $term = trim($term);
                    if ($term !== '') $values[] = $term;
                }
            }
        }

        if (!$explicit_false && !count($values) && isset($mapping['default']) && is_scalar($mapping['default']))
        {
            $default = trim((string) $mapping['default']);
            if ($default !== '')
            {
                foreach (explode(',', $default) as $term)
                {
                    $term = trim($term);
                    if ($term !== '') $values[] = $term;
                }
            }
        }

        if ($mode !== 'hierarchy') $values = array_values(array_unique($values));
        else $values = array_values($values);
        if (!count($values)) return null;

        return $mode === 'hierarchy' ? ['path' => $values] : ['terms' => $values];
    }

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

    /**
     * Repeatable listing fields supported by the importer.
     *
     * Add-ons can register their own repeaters with lsd_ix_repeater_fields.
     */
    public function repeater_fields(): array
    {
        $fields = [
            'faqs' => [
                'label' => esc_html__('FAQs', 'listdom'),
                'description' => esc_html__('Map each FAQ question and answer as a pair.', 'listdom'),
                'fields' => [
                    'question' => [
                        'label' => esc_html__('Question', 'listdom'),
                        'required' => true,
                    ],
                    'answer' => [
                        'label' => esc_html__('Answer', 'listdom'),
                        'required' => true,
                    ],
                ],
            ],
        ];

        if (LSD_Base::isPro())
        {
            $fields['embeds'] = [
                'label' => esc_html__('Embed Codes', 'listdom'),
                'description' => esc_html__('Map an optional title and an embed code or URL for each embed section.', 'listdom'),
                'fields' => [
                    'name' => [
                        'label' => esc_html__('Title', 'listdom'),
                        'required' => false,
                    ],
                    'code' => [
                        'label' => esc_html__('Embed Code or URL', 'listdom'),
                        'required' => true,
                    ],
                ],
            ];
        }

        return apply_filters('lsd_ix_repeater_fields', $fields);
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
            LSD_Base::TAX_LOCATION => ['lsd_schema'],
            LSD_Base::TAX_TAG => [],
            LSD_Base::TAX_FEATURE => ['lsd_icon', 'lsd_itemprop', 'lsd_schema'],
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

    public function feed_field_samples(string $file, int $limit = 250): array
    {
        $samples = [];
        $fh = fopen($file, 'r');
        if (!$fh) return $samples;

        $delimiter = $this->delimiter($file);
        $headers = fgetcsv($fh, 0, $delimiter);
        if (!is_array($headers))
        {
            fclose($fh);
            return $samples;
        }

        foreach ($headers as $index => $header) $samples[$index] = [];

        $rows = 0;
        while (($row = fgetcsv($fh, 0, $delimiter)) !== false && $rows < $limit)
        {
            $rows++;
            foreach ($headers as $index => $header)
            {
                if (count($samples[$index]) >= 20) continue;

                $value = isset($row[$index]) ? trim((string) $row[$index]) : '';
                if ($value === '' || in_array($value, $samples[$index], true)) continue;

                $samples[$index][] = $value;
            }
        }

        fclose($fh);
        return $samples;
    }

    /**
     * @param array $raw
     * @param array $mappings
     * @return array
     */
    public function map(array $raw, array $mappings, array $options = []): array
    {
        $mapped = [];
        foreach ($mappings as $key => $mapping)
        {
            if (!is_array($mapping)) continue;

            if (self::is_listing_taxonomy($key) && ((isset($mapping['sources']) && is_array($mapping['sources'])) || array_key_exists('taxonomy_mode', $mapping)))
            {
                $value = self::taxonomy_mapping_value($raw, $mapping, $key, $options);
                if ($value !== null) $mapped[$key] = $value;

                continue;
            }

            $field = isset($mapping['map']) && is_scalar($mapping['map']) && trim((string) $mapping['map']) !== '' ? $mapping['map'] : null;
            $default = isset($mapping['default']) && is_scalar($mapping['default']) && trim((string) $mapping['default']) !== '' ? $mapping['default'] : null;

            // Not Mapped
            if (is_null($field) && is_null($default)) continue;

            // Value
            $value = !is_null($field) && isset($raw[$field]) && trim($raw[$field]) !== '' ? $raw[$field] : $default;

            // Normalize the Value
            if (is_scalar($value) && $value !== '' && !preg_match('!!u', (string) $value))
            {
                $detected_encoding = mb_detect_encoding((string) $value, mb_detect_order(), true);
                $value = mb_convert_encoding((string) $value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
            }

            // Add to Mapped Data
            $mapped[$key] = $value;
        }

        foreach ($this->repeater_fields() as $key => $repeater)
        {
            if (!is_array($repeater) || !isset($repeater['fields']) || !is_array($repeater['fields'])) continue;

            $rows = $mappings[$key] ?? [];
            if (!is_array($rows)) continue;

            $items = [];
            $has_mapping = false;
            $ordinal = 0;
            foreach ($rows as $row_index => $row)
            {
                $current_ordinal = $ordinal++;
                if (!is_array($row)) continue;

                $item = [];
                $is_complete = true;
                $has_required_mappings = true;
                $required_fields = 0;

                foreach ($repeater['fields'] as $field_key => $field)
                {
                    if (!is_array($field)) continue;

                    $mapping = $row[$field_key] ?? [];
                    if (!is_array($mapping)) $mapping = [];

                    $column = self::normalize_source_column($mapping['map'] ?? null);
                    if (!empty($field['required']))
                    {
                        $required_fields++;
                        if ($column === null) $has_required_mappings = false;
                    }
                    $value = $column !== null && isset($raw[$column]) ? $raw[$column] : '';
                    $value = is_scalar($value) ? trim((string) $value) : '';

                    if ($value !== '' && !preg_match('!!u', $value))
                    {
                        $detected_encoding = mb_detect_encoding($value, mb_detect_order(), true);
                        $value = mb_convert_encoding($value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
                    }

                    if (!empty($field['required']) && $value === '') $is_complete = false;
                    $item[$field_key] = $value;
                }

                if ($required_fields && $has_required_mappings) $has_mapping = true;

                if (!$is_complete || !count($item)) continue;

                if (!empty($repeater['preserve_index']))
                {
                    $item['_index'] = is_scalar($row_index) ? (string) $row_index : (string) $current_ordinal;
                    $item['_legacy_index'] = $current_ordinal;
                }
                $items[] = $item;
            }

            if (count($items) || $has_mapping) $mapped[$key] = $items;
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
