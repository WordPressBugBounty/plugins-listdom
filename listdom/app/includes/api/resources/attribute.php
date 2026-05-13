<?php

class LSD_API_Resources_Attribute extends LSD_API_Resource
{
    public static function listing($id)
    {
        $values = get_post_meta($id, 'lsd_attributes', true);
        if (!is_array($values)) $values = [];

        $attribute_context = LSD_Taxonomies_Attribute::context([
            'post_id' => (int) $id,
        ]);

        $attributes = [];
        foreach ($values as $slug => $value)
        {
            // Term
            $term = get_term_by('slug', $slug, LSD_Base::TAX_ATTRIBUTE);

            // Invalid Term
            if (!$term || !isset($term->term_id)) continue;

            // Attribute Data
            $type = get_term_meta($term->term_id, 'lsd_field_type', true);
            $values = get_term_meta($term->term_id, 'lsd_values', true);
            $link_label = get_term_meta($term->term_id, 'lsd_link_label', true);

            if (!LSD_Taxonomies_Attribute::applies((int) $term->term_id, $attribute_context)) continue;

            $attributes[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'value' => $value,
                'type' => $type,
                'values' => $values,
                'link_label' => $link_label,
            ];
        }

        return apply_filters('lsd_api_resource_attribute', $attributes, $id);
    }
}
