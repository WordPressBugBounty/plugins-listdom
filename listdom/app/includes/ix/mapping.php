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
                'description' => esc_html__("A text field should be mapped. Valid values are publish, trash, draft, and pending. The default value is publish.", 'listdom'),
                'default' => [$default, 'email'],
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
                'label' => esc_html__('Listing Category', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field should be mapped. Listdom will create a category using the text if one does not exist and assign the listing to that category.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_LOCATION => [
                'label' => esc_html__('Listing Locations', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field should be mapped. Listdom will create a location using the text if one does not exist and assign the listing to that location.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_TAG => [
                'label' => esc_html__('Listing Tags', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field should be mapped. Listdom will create a tag using the text if one does not exist and assign the listing to that tag.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_FEATURE => [
                'label' => esc_html__('Listing Features', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field should be mapped. Listdom will create a feature using the text if one does not exist and assign the listing to that feature.", 'listdom'),
                'default' => [$default, 'text'],
            ],
            LSD_Base::TAX_LABEL => [
                'label' => esc_html__('Listing Labels', 'listdom'),
                'type' => 'text',
                'mandatory' => false,
                'description' => esc_html__("A text field should be mapped. Listdom will create a label using the text if one does not exist and assign the listing to that label.", 'listdom'),
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
                'description' => sprintf(esc_html__("A %s field should be mapped.", 'listdom'), $type),
                'default' => [$default, $type],
            ];
        }

        // Attributes
        $attributes = LSD_Main::get_attributes();

        foreach ($attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type === 'separator') continue;

            $mapping_type = in_array($type, ['number', 'email', 'url']) ? $type : 'text';

            $fields['lsd_attribute_' . $attribute->term_id] = [
                'label' => $attribute->name,
                'type' => $mapping_type,
                'mandatory' => false,
                'description' => sprintf(esc_html__("A %s field should be mapped.", 'listdom'), $mapping_type),
                'default' => [$default, $mapping_type],
            ];
        }

        // Apply Filters
        return apply_filters('lsd_ix_listdom_fields', $fields);
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
            $value = $value && !preg_match('!!u', $value) ? utf8_encode($value) : $value;

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
