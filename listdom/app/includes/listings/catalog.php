<?php

/** Explicit listing fields shared by discovery, validation and domain writes. */
class LSD_Listings_Catalog
{
    public static function object(array $properties): array
    {
        return LSD_Abilities_Schema::object($properties);
    }

    public function definitions(array $context = []): array
    {
        $string = ['type' => 'string', 'maxLength' => 10000];
        $number = ['type' => ['number', 'string']];
        $id = ['type' => 'integer', 'minimum' => 1];
        $ids = LSD_Abilities_Schema::list($id, 100);
        $fields = [];
        $add = static function ($key, $schema, $module = '', $default = '', $required = false) use (&$fields)
        {
            $fields[$key] = compact('key', 'schema', 'module', 'default', 'required') + [
                'owner' => 'Listdom', 'available' => true, 'readable' => true, 'writable' => true,
                'clearable' => !$required, 'capability' => '',
            ];
        };
        $add('title', ['type' => 'string', 'minLength' => 1, 'maxLength' => 250], '', '', true);
        $add('content', ['type' => 'string', 'maxLength' => 100000]);
        $add('excerpt', $string, 'excerpt');
        $add('listing_category', $id, '', 0, true);
        $add('listing_categories', $ids, '', []);
        $taxonomies = [];
        foreach ((new LSD_Listings_Taxonomies())->available() as $slug => $taxonomy) $taxonomies[$slug] = $ids;
        unset($taxonomies[LSD_Base::TAX_CATEGORY]);
        $add('taxonomies', self::object($taxonomies), '', []);
        foreach (['address', 'shape_paths'] as $key) $add($key, $string, 'address');
        foreach (['latitude', 'longitude', 'shape_radius', 'zoomlevel'] as $key) $add($key, $number, 'address');
        $add('object_type', ['type' => 'string', 'enum' => ['marker', 'shape']], 'address', 'marker');
        $add('shape_type', ['type' => 'string', 'enum' => ['', 'circle', 'polygon', 'polyline', 'rectangle']], 'address');
        foreach (['price', 'price_max'] as $key) $add($key, $number, 'price', 0);
        $add('price_after', $string, 'price');
        $add('price_class', ['type' => ['integer', 'string'], 'enum' => [1, 2, 3, 4, '1', '2', '3', '4']], 'price', 2);
        $add('currency', ['type' => 'string', 'enum' => array_values(LSD_Base::get_currencies())], 'price', LSD_Options::currency());
        foreach (['email', 'phone', 'website', 'contact_address', 'link'] as $key) $add($key, $string, 'contact');
        $days = [];
        foreach (LSD_Main::get_weekdays() as $day)
            $days[$day['code']] = self::object(['hours' => ['type' => 'string', 'maxLength' => 500], 'off' => ['type' => ['boolean', 'integer'], 'enum' => [true, false, 0, 1]]]);
        $add('ava', self::object($days), 'availability', []);
        $add('featured_image', ['type' => 'integer', 'minimum' => 0], 'image', 0);
        $add('featured_image_alt', $string, 'image');
        $add('gallery', $ids, 'gallery', []);
        $add('gallery_alt', self::object([]), 'gallery', []);
        // Gallery alt keys are attachment IDs, not arbitrary postmeta keys.
        $fields['gallery_alt']['schema'] = ['type' => 'object', 'additionalProperties' => ['type' => 'string', 'maxLength' => 1000], 'maxProperties' => 100];
        $add('remark', $string, 'remark');
        $add('related_listings', $ids, 'related', []);
        $add('embeds', LSD_Abilities_Schema::list(self::object(['name' => $string, 'code' => $string, 'featured' => ['type' => ['string', 'integer'], 'enum' => ['', '0', '1', 0, 1]]]), 50), 'embed', []);
        $add('faqs', LSD_Abilities_Schema::list(self::object(['question' => $string, 'answer' => $string]), 50), 'faq', []);
        foreach (['cta_text', 'cta_url', 'cta_popup'] as $key) $add($key, $string, 'cta');
        $add('cta_mode', ['type' => 'string', 'enum' => ['inherit', 'custom']], 'cta', 'custom');
        $add('cta_target', ['type' => 'string', 'enum' => LSD_Base::isPro() ? ['details', 'lightbox', 'custom', 'popup'] : ['details', 'lightbox', 'custom']], 'cta', 'details');

        $dashboard = new LSD_Shortcodes_Dashboard();
        $settings = LSD_Options::settings();
        foreach (['latitude' => 'map_backend_lt', 'longitude' => 'map_backend_ln', 'zoomlevel' => 'map_backend_zl'] as $key => $setting)
            $fields[$key]['default'] = $settings[$setting] ?? '';
        foreach (['currency', 'price_class', 'object_type', 'latitude', 'longitude', 'zoomlevel'] as $key) $fields[$key]['clearable'] = false;
        $limits = $this->restrictions($context);
        if (trim((string) $limits['max_gallery_images']) !== '') $fields['gallery']['schema']['maxItems'] = min(100, (int) $limits['max_gallery_images']);
        if (trim((string) $limits['description_length']) !== '') $fields['content']['max_text_length'] = (int) $limits['description_length'];
        if (isset($fields['taxonomies']['schema']['properties'][LSD_Base::TAX_TAG]) && trim((string) $limits['max_tags']) !== '') $fields['taxonomies']['schema']['properties'][LSD_Base::TAX_TAG]['maxItems'] = min(100, (int) $limits['max_tags']);
        foreach ($fields as $key => &$field)
        {
            if ($field['module']) $field['available'] = $this->module_enabled($field['module'], $context);
            $component = ['address' => 'map', 'price' => 'pricing', 'availability' => 'work_hours', 'cta' => 'cta', 'related' => 'related', 'faq' => 'faq', 'embed' => 'embed', 'remark' => 'remark'][$field['module']] ?? '';
            if ($component && !call_user_func(['LSD_Components', $component])) $field['available'] = false;
            $required_key = ['gallery' => '_gallery', 'embeds' => '_embeds', 'faqs' => '_faqs'][$key] ?? $key;
            $field['required'] = $field['required'] || $dashboard->is_required($required_key);
            $field['clearable'] = $field['clearable'] && !$field['required'];
            if (in_array($key, ['featured_image', 'gallery'], true)) $field['capability'] = 'upload_files';
            $field['writable'] = $field['available'] && (!$field['capability'] || current_user_can($field['capability']));
        }
        unset($field);
        $relationships = [];
        foreach ((new LSD_Listings_Taxonomies())->available() as $slug => $taxonomy)
        {
            if ($slug === LSD_Base::TAX_CATEGORY) continue;
            $module = [LSD_Base::TAX_TAG => 'tags', LSD_Base::TAX_LOCATION => 'locations', LSD_Base::TAX_FEATURE => 'features', LSD_Base::TAX_LABEL => 'labels'][$slug] ?? '';
            $required_key = $slug === LSD_Base::TAX_TAG ? 'tags' : $slug;
            $available = !$module || $this->module_enabled($module, $context);
            $required = $available && $dashboard->is_required($required_key);
            $relationships[$slug] = ['module' => $module, 'required' => $required, 'available' => $available,
                'writable' => $available && current_user_can($taxonomy->cap->assign_terms)];
        }
        $fields['taxonomies']['relationships'] = $relationships;
        $fields['taxonomies']['required'] = in_array(true, array_column($relationships, 'required'), true);
        $fields['taxonomies']['clearable'] = !$fields['taxonomies']['required'];
        $prices = LSD_Options::price_components();
        foreach (['currency' => 'currency', 'price_max' => 'max', 'price_after' => 'after', 'price_class' => 'class'] as $key => $option)
            if (empty($prices[$option])) $fields[$key]['available'] = $fields[$key]['writable'] = false;
        if (isset($settings['submission_contact_fields'])) foreach (['email', 'phone', 'website', 'contact_address', 'link'] as $key)
            if (isset($settings['submission_contact_fields'][$key]) && !$settings['submission_contact_fields'][$key]) $fields[$key]['available'] = $fields[$key]['writable'] = false;

        $socials = [];
        $social_types = [];
        $required_socials = [];
        $social = new LSD_Socials();
        foreach (LSD_Options::socials() as $network => $options)
        {
            $object = $social->get($network, $options);
            if (!$object || !$object->option('listing')) continue;
            $key = $object->key();
            $socials[$key] = ['type' => 'string', 'maxLength' => 2000];
            $social_types[$key] = $object->get_input_type();
            if ($dashboard->is_required($key)) $required_socials[] = $key;
        }
        $add('sc', self::object($socials), 'contact', [], (bool) $required_socials);
        $fields['sc']['input_types'] = $social_types;
        $fields['sc']['required_fields'] = $required_socials;
        $fields['sc']['available'] = $fields['sc']['writable'] = LSD_Components::socials() && $this->module_enabled('contact', $context);

        $attributes = [];
        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            $key = $attribute['slug'];
            $schema = $string;
            $type = $attribute['field_type'];
            if (!in_array($type, ['text', 'radio', 'checkbox', 'number', 'email', 'tel', 'url', 'date', 'time', 'datetime', 'image', 'file', 'dropdown', 'textarea'], true)) continue;
            if ($type === 'number') $schema = $number;
            if (in_array($type, ['checkbox', 'checkboxes', 'multiselect'], true)) $schema = LSD_Abilities_Schema::list(['type' => 'string'], 100);
            if (in_array($type, ['image', 'file'], true)) $schema = ['type' => ['integer', 'string']];
            $values = array_values(array_filter($attribute['values'], 'strlen'));
            if ($values)
            {
                if ($schema['type'] === 'array') $schema['items']['enum'] = $values;
                else $schema['enum'] = array_values(array_unique(array_merge([''], $values)));
            }
            $attributes[$key] = $schema;
        }
        $add('attributes', self::object($attributes), 'attributes', []);
        $fields['attributes']['available'] = $fields['attributes']['writable'] = $this->module_enabled('attributes', $context);

        // Only booted providers register here. UI fields alone never grant writes.
        foreach ($this->providers() as $provider_id => $provider)
            foreach ($provider['fields'] as $key => $field)
            {
                $name = $provider_id . '.' . $key;
                $fields[$name] = $field + ['key' => $name, 'module' => '', 'default' => null, 'required' => false, 'clearable' => false];
                $fields[$name]['owner'] = $provider['owner'];
                $fields[$name]['available'] = true;
                $allowed = current_user_can($field['capability']);
                $fields[$name]['readable'] = $allowed;
                $fields[$name]['writable'] = $allowed && is_callable($provider['save'] ?? null);
                $fields[$name]['provider'] = $provider_id;
            }
        return $fields;
    }

    public function module_enabled(string $module, array $context = []): bool
    {
        return (bool) apply_filters('lsd_dashboard_modules_status', LSD_API_Resources_Fields::is_enabled($module), $module, (int) ($context['id'] ?? 0), $context);
    }

    public function restrictions(array $context = []): array
    {
        return apply_filters('lsd_dashboard_restriction_rules', LSD_Shortcodes_Dashboard::restriction_rules(LSD_Options::settings()), $context);
    }

    public function providers(): array
    {
        $providers = apply_filters('lsd_listing_field_providers', []);
        if (!is_array($providers)) return [];
        foreach ($providers as $id => $provider)
        {
            if (!is_string($id) || !preg_match('/^[a-z][a-z0-9_-]{0,39}$/D', $id) || empty($provider['owner'])
                || !is_callable($provider['available'] ?? null) || !call_user_func($provider['available'])
                || !is_callable($provider['read'] ?? null) || !is_callable($provider['validate'] ?? null) || !is_array($provider['fields'] ?? null))
            {
                unset($providers[$id]);
                continue;
            }
            foreach ($provider['fields'] as $key => $field)
                if (!is_string($key) || !preg_match('/^[a-z][a-z0-9_]{0,39}$/D', $key) || empty($field['capability']) || !is_array($field['schema'] ?? null))
                    unset($providers[$id]['fields'][$key]);
        }
        return $providers;
    }

    /** Creation needs an existing category ID or the supported explicit selector. */
    public function creation_schema(bool $legacy = false, array $required = ['title']): array
    {
        $properties = $this->input_properties($legacy);
        return LSD_Abilities_Schema::object($properties, $required) + ['anyOf' => [
            ['type' => 'object', 'properties' => ['listing_category' => $properties['listing_category']], 'required' => ['listing_category']],
            ['type' => 'object', 'properties' => ['primary_category' => $properties['primary_category']], 'required' => ['primary_category']],
        ]];
    }

    public function input_properties(bool $legacy = false): array
    {
        $properties = [];
        $extensions = [];
        foreach ($this->definitions() as $key => $field)
        {
            if (isset($field['provider']))
            {
                if ($field['writable']) $extensions[$key] = $field['schema'];
            }
            else $properties[$key] = $field['schema'];
        }
        $properties['extensions'] = self::object($extensions);
        $properties['primary_category'] = self::object(['id' => ['type' => 'integer', 'minimum' => 1], 'slug' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200], 'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200], 'parent' => ['type' => 'integer', 'minimum' => 0]]);
        // Payload bounds are independent of effective package/global limits. In
        // particular, removals must be able to repair a gallery above its new limit.
        $properties['gallery']['maxItems'] = 100;
        $properties['gallery_add'] = $properties['gallery'];
        $properties['gallery_remove'] = LSD_Abilities_Schema::list(['type' => 'integer', 'minimum' => 1], 100);
        foreach (array_keys($properties['taxonomies']['properties'] ?? []) as $taxonomy) $properties['taxonomies']['properties'][$taxonomy]['maxItems'] = 100;
        $properties['request_id'] = ['type' => 'string', 'minLength' => 8, 'maxLength' => 128, 'pattern' => '^[A-Za-z0-9_-]+$'];
        if ($legacy) $properties['taxonomies']['properties'][LSD_Base::TAX_TAG] = ['type' => ['string', 'array'], 'items' => ['type' => 'integer', 'minimum' => 1], 'maxItems' => 100, 'maxLength' => 2000];
        return $properties;
    }

    /** Read only declared values, without the public feed or public serializer. */
    public function values(int $id): array
    {
        $post = get_post($id);
        $values = [];
        $primary = LSD_Entity_Listing::get_primary_category($id);
        foreach ($this->definitions() as $key => $field)
        {
            if (isset($field['provider']) || !$field['readable'] || !$field['available']) continue;
            if (in_array($key, ['title', 'content', 'excerpt'], true)) $value = $post->{'post_' . $key};
            elseif ($key === 'listing_category') $value = $primary instanceof WP_Term ? (int) $primary->term_id : 0;
            elseif ($key === 'listing_categories')
            {
                $terms = wp_get_object_terms($id, LSD_Base::TAX_CATEGORY, ['fields' => 'ids']);
                $value = is_wp_error($terms) ? [] : array_values(array_diff(array_map('intval', $terms), [$primary->term_id ?? 0]));
            }
            elseif ($key === 'featured_image') $value = (int) get_post_thumbnail_id($id);
            elseif ($key === 'taxonomies')
            {
                $value = [];
                foreach ((new LSD_Listings_Taxonomies())->available() as $slug => $taxonomy)
                {
                    if ($slug === LSD_Base::TAX_CATEGORY || !current_user_can($taxonomy->cap->assign_terms)) continue;
                    $terms = wp_get_object_terms($id, $slug, ['fields' => 'ids']);
                    if (!is_wp_error($terms)) $value[$slug] = array_map('intval', $terms);
                }
            }
            elseif ($key === 'sc')
            {
                $value = [];
                foreach (array_keys($field['schema']['properties'] ?? []) as $social) $value[$social] = get_post_meta($id, 'lsd_' . $social, true);
            }
            elseif (strpos($key, 'cta_') === 0)
            {
                $cta = get_post_meta($id, 'lsd_call_to_action', true);
                $part = $key === 'cta_popup' ? 'content' : substr($key, 4);
                $value = $cta[$part] ?? $field['default'];
            }
            else $value = metadata_exists('post', $id, 'lsd_' . $key) ? get_post_meta($id, 'lsd_' . $key, true) : $field['default'];
            if (in_array($key, ['attributes', 'sc', 'ava', 'gallery_alt', 'taxonomies'], true))
            {
                $value = is_array($value) ? $value : [];
                $allowed = array_keys($field['schema']['properties'] ?? []);
                if ($key !== 'gallery_alt') $value = array_intersect_key($value, array_flip($allowed));
            }
            // Dashboard checkbox values are stored as strings; expose the declared integer form.
            if ($key === 'ava')
                foreach ($value as $code => $day)
                    if (is_array($day) && array_key_exists('off', $day)) $value[$code]['off'] = (int) !empty($day['off']);
            if ($field['schema']['type'] === 'array') $value = is_array($value) ? array_values($value) : [];
            $values[$key] = $value;
        }
        foreach ($this->providers() as $provider_id => $provider)
        {
            $readable = [];
            foreach ($provider['fields'] as $key => $field) if (current_user_can($field['capability'])) $readable[] = $key;
            if (!$readable) continue;
            $data = call_user_func($provider['read'], $id, $readable);
            if (!is_array($data)) continue;
            foreach ($readable as $key) if (array_key_exists($key, $data)) $values['extensions'][$provider_id . '.' . $key] = $data[$key];
        }
        return $values;
    }

    public function describe(array $input)
    {
        $context_fields = ['listing_category' => (int) ($input['category_id'] ?? 0), 'extensions' => $input['extensions'] ?? []];
        $valid = rest_validate_value_from_schema($context_fields['extensions'], $this->input_properties()['extensions'], 'extensions');
        if (is_wp_error($valid)) return LSD_Listings_Workflow::error('extensions', $valid->get_error_message());
        foreach ($this->providers() as $provider_id => $provider)
        {
            $patch = [];
            foreach ($context_fields['extensions'] as $name => $value) if (strpos($name, $provider_id . '.') === 0) $patch[substr($name, strlen($provider_id) + 1)] = $value;
            if (!$patch) continue;
            $valid = call_user_func($provider['validate'], $patch, 0, $context_fields);
            if (is_wp_error($valid)) return $valid;
            if ($valid !== true) return LSD_Listings_Workflow::error('extensions', 'The field provider rejected the discovery context.');
        }
        $context = LSD_Taxonomies_Attribute::context(['category_id' => $context_fields['listing_category'], 'resolve_post_category' => false, 'submission' => true, 'listing_fields' => $context_fields]);
        $definitions = $this->definitions(['id' => 0, 'fields' => $context_fields]);
        $fields = [];
        foreach ($definitions as $key => $field)
        {
            $entry = array_intersect_key($field, array_flip(['key', 'owner', 'module', 'schema', 'default', 'required', 'available', 'readable', 'writable', 'clearable', 'max_text_length']));
            $entry['nullable'] = false;
            if ($key === 'attributes') $entry['schema'] = ['type' => 'object', 'description' => 'Named attribute fields are listed as attributes.<slug>.'];
            $fields[] = $entry;
        }
        $taxonomy_field = $definitions['taxonomies'];
        foreach ($taxonomy_field['relationships'] as $slug => $relationship)
            $fields[] = ['key' => 'taxonomies.' . $slug, 'owner' => 'Listdom', 'module' => $relationship['module'],
                'schema' => $taxonomy_field['schema']['properties'][$slug], 'required' => $relationship['required'],
                'available' => $relationship['available'], 'readable' => true, 'writable' => $relationship['writable'],
                'clearable' => !$relationship['required'], 'nullable' => false];
        $social_field = $definitions['sc'];
        foreach ($social_field['schema']['properties'] ?? [] as $key => $schema)
        {
            $required = in_array($key, $social_field['required_fields'], true);
            $fields[] = ['key' => 'sc.' . $key, 'owner' => 'Listdom', 'module' => 'contact', 'schema' => $schema,
                'input_type' => $social_field['input_types'][$key], 'required' => $required,
                'available' => $social_field['available'], 'readable' => true, 'writable' => $social_field['writable'],
                'clearable' => !$required, 'nullable' => false];
        }
        $attribute_field = $definitions['attributes'];
        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            if (!isset($attribute_field['schema']['properties'][$attribute['slug']])) continue;
            $fields[] = ['key' => 'attributes.' . $attribute['slug'], 'owner' => 'Listdom', 'module' => 'attributes',
                'schema' => $attribute_field['schema']['properties'][$attribute['slug']], 'required' => (bool) $attribute['required'],
                'available' => $attribute_field['available'], 'readable' => true, 'writable' => $attribute_field['writable'],
                'clearable' => !$attribute['required'], 'nullable' => false,
                'categories' => (object) LSD_Taxonomies_Attribute::category_rules((int) $attribute['id']),
                'applicable' => LSD_Taxonomies_Attribute::applies((int) $attribute['id'], $context)];
        }
        $page = max(1, (int) ($input['page'] ?? 1));
        $size = min(100, max(1, (int) ($input['per_page'] ?? 20)));
        return ['fields' => array_slice($fields, ($page - 1) * $size, $size), 'page' => $page, 'has_more' => count($fields) > $page * $size];
    }
}
