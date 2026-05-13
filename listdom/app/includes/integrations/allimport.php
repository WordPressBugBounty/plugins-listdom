<?php

class LSD_Integrations_Allimport extends LSD_Integrations
{
    public function init()
    {
        add_action('pmxi_saved_post', [$this, 'saved'], 10, 3);
    }

    public function saved($post_id, $node = null, $is_update = false)
    {
        $post_id = absint($post_id);
        if (!$post_id) return;

        $post = get_post($post_id);
        if (!$post instanceof WP_Post || $post->post_type !== LSD_Base::PTYPE_LISTING) return;

        $address = $this->normalize_address(
            $this->record_value($node, ['lsd_address', 'address'])
        );

        $lat = $this->normalize_coordinate(
            $this->record_value($node, ['lsd_latitude', 'latitude', 'lat']), 'latitude'
        );

        $lng = $this->normalize_coordinate(
            $this->record_value($node, ['lsd_longitude', 'longitude', 'lng', 'lon']), 'longitude'
        );

        if ($this->has_value($address)) update_post_meta($post_id, 'lsd_address', $address);
        if ($lat !== '') update_post_meta($post_id, 'lsd_latitude', $lat);
        if ($lng !== '') update_post_meta($post_id, 'lsd_longitude', $lng);

        if ($lat === '' || $lng === '') return;

        (new LSD_Entity_Listing($post_id))->update_geopoint($post_id, $lat, $lng);
    }

    private function has_value($value): bool
    {
        if (is_numeric($value)) return true;
        if (is_string($value)) return trim($value) !== '';

        return !empty($value);
    }

    private function record_value($node, array $keys): string
    {
        foreach ($keys as $key)
        {
            if (!is_string($key) || $key === '') continue;

            $value = '';
            if ($node instanceof SimpleXMLElement)
            {
                if (isset($node->{$key})) $value = (string) $node->{$key};
            }
            else if (is_array($node))
            {
                if (array_key_exists($key, $node) && is_scalar($node[$key])) $value = (string) $node[$key];
            }
            else if (is_object($node))
            {
                if (isset($node->{$key}) && is_scalar($node->{$key})) $value = (string) $node->{$key};
            }

            if ($this->has_value($value)) return $value;
        }

        return '';
    }

    private function normalize_address($value): string
    {
        if (!is_scalar($value)) return '';

        return trim(sanitize_text_field(wp_unslash((string) $value)));
    }

    private function normalize_coordinate($value, string $type): string
    {
        if (!is_scalar($value)) return '';

        $value = trim((string) $value);
        if ($value === '') return '';

        $value = trim($value, "'\"`");

        if (preg_match('/^=\s*[\'"]?\s*([-+]?[0-9]+(?:[.,][0-9]+)?)\s*[\'"]?\s*$/', $value, $matches))
        {
            $value = $matches[1];
        }

        $value = str_replace(' ', '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') === false) $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) return '';

        $number = (float) $value;
        if ($type === 'latitude' && ($number < -90 || $number > 90)) return '';
        if ($type === 'longitude' && ($number < -180 || $number > 180)) return '';

        $normalized = rtrim(rtrim(number_format($number, 8, '.', ''), '0'), '.');
        if ($normalized === '' || $normalized === '-0') $normalized = '0';

        return $normalized;
    }
}
