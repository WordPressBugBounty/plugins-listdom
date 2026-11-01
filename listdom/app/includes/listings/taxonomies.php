<?php

/** Canonical, permission-checked listing relationships and term operations. */
class LSD_Listings_Taxonomies
{
    public function available(): array
    {
        $allowed = [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_FEATURE, LSD_Base::TAX_LABEL, LSD_Base::TAX_TAG];
        // An active add-on must explicitly opt its relationship taxonomy in.
        $allowed = apply_filters('lsd_listing_writable_taxonomies', $allowed);
        $items = [];
        foreach (get_object_taxonomies(LSD_Base::PTYPE_LISTING, 'objects') as $slug => $taxonomy)
            if (in_array($slug, $allowed, true) && $slug !== LSD_Base::TAX_ATTRIBUTE) $items[$slug] = $taxonomy;
        return $items;
    }

    public function permission(string $slug, string $operation): bool
    {
        $taxonomy = $this->available()[$slug] ?? null;
        if (!$taxonomy || !get_current_user_id()) return false;
        if ($operation === 'read') return current_user_can($taxonomy->cap->manage_terms) || current_user_can($taxonomy->cap->edit_terms);
        $cap = $operation === 'create' ? ($taxonomy->cap->create_terms ?? $taxonomy->cap->edit_terms) : $taxonomy->cap->edit_terms;
        return current_user_can($cap);
    }

    public function describe(array $input): array
    {
        $items = [];
        foreach ($this->available() as $slug => $taxonomy)
        {
            if (!$this->permission($slug, 'read')) continue;
            $items[] = ['slug' => $slug, 'label' => $taxonomy->labels->name, 'hierarchical' => (bool) $taxonomy->hierarchical,
                'can_create' => $this->permission($slug, 'create'), 'can_edit' => $this->permission($slug, 'edit'),
                'can_assign' => current_user_can($taxonomy->cap->assign_terms), 'metadata' => array_keys($this->metadata($slug))];
        }
        $page = max(1, (int) ($input['page'] ?? 1));
        $size = min(100, max(1, (int) ($input['per_page'] ?? 20)));
        return ['taxonomies' => array_slice($items, ($page - 1) * $size, $size), 'page' => $page, 'has_more' => count($items) > $page * $size];
    }

    public function terms(array $input)
    {
        $slug = $input['taxonomy'];
        if (!$this->permission($slug, 'read')) return self::error('taxonomy', 'This taxonomy is unavailable or you cannot manage it.');
        $page = max(1, (int) ($input['page'] ?? 1));
        $size = min(100, max(1, (int) ($input['per_page'] ?? 20)));
        $args = ['taxonomy' => $slug, 'hide_empty' => false, 'number' => $size + 1, 'offset' => ($page - 1) * $size,
            'search' => $input['search'] ?? '', 'orderby' => 'term_id', 'order' => 'ASC'];
        if (isset($input['parent'])) $args['parent'] = $input['parent'];
        $terms = get_terms($args);
        if (is_wp_error($terms)) return $terms;
        // Do not include counts or listing identifiers in management discovery.
        return ['terms' => array_map([$this, 'term'], array_slice($terms, 0, $size)), 'page' => $page, 'has_more' => count($terms) > $size];
    }

    public function term(WP_Term $term): array
    {
        return ['id' => (int) $term->term_id, 'taxonomy' => $term->taxonomy, 'name' => $term->name, 'slug' => $term->slug,
            'description' => $term->description, 'parent' => (int) $term->parent];
    }

    /** Resolve exactly one explicit identifier. Never creates a category. */
    public function resolve(array $selector)
    {
        $keys = array_intersect(['id', 'slug', 'name'], array_keys($selector));
        if (count($keys) !== 1) return self::error('primary_category', 'Supply exactly one category ID, slug or name.');
        $key = reset($keys);
        if ($key === 'id') $term = get_term((int) $selector['id'], LSD_Base::TAX_CATEGORY);
        elseif ($key === 'slug') $term = get_term_by('slug', $selector['slug'], LSD_Base::TAX_CATEGORY);
        else
        {
            $args = ['taxonomy' => LSD_Base::TAX_CATEGORY, 'hide_empty' => false, 'name' => $selector['name'], 'number' => 21];
            if (isset($selector['parent'])) $args['parent'] = $selector['parent'];
            $matches = get_terms($args);
            if (is_wp_error($matches)) return $matches;
            if (count($matches) > 1) return self::error('primary_category', 'The category name is ambiguous. Choose a candidate ID.', ['candidates' => array_map([$this, 'term'], array_slice($matches, 0, 20)), 'has_more' => count($matches) > 20]);
            $term = $matches[0] ?? null;
        }
        if (!$term instanceof WP_Term || (isset($selector['parent']) && (int) $term->parent !== $selector['parent']))
            return self::error('primary_category', 'No matching category exists. Create it explicitly with create-term if authorized.');
        return (int) $term->term_id;
    }

    public function metadata(string $taxonomy): array
    {
        $string = ['type' => 'string', 'maxLength' => 500];
        $fields = [];
        if ($taxonomy === LSD_Base::TAX_CATEGORY) $fields = ['icon' => $string, 'color' => ['type' => 'string', 'pattern' => '^$|^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$'], 'image' => ['type' => 'integer', 'minimum' => 0], 'disabled_icon' => ['type' => 'integer', 'enum' => [0, 1]]];
        if ($taxonomy === LSD_Base::TAX_LOCATION) $fields = ['image' => ['type' => 'integer', 'minimum' => 0]];
        if ($taxonomy === LSD_Base::TAX_FEATURE) $fields = ['icon' => $string];
        if ($taxonomy === LSD_Base::TAX_LABEL) $fields = ['color' => $string];
        if (LSD_Base::isPro() && in_array($taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_FEATURE], true)) $fields['schema'] = $string;
        if (LSD_Base::isPro() && $taxonomy === LSD_Base::TAX_FEATURE) $fields['itemprop'] = $string;
        return $fields;
    }

    /** Shared by nonce-checked admin handlers and authenticated domain writes. */
    public static function save_metadata(int $id, array $data): void
    {
        foreach ($data as $key => $value) update_term_meta($id, 'lsd_' . $key, is_int($value) ? $value : sanitize_text_field($value));
    }

    public function write(array $input, bool $update = false)
    {
        $slug = $input['taxonomy'];
        $taxonomy = $this->available()[$slug] ?? null;
        if (!$this->permission($slug, $update ? 'edit' : 'create')) return self::error('taxonomy', 'You cannot write this taxonomy.');
        $id = $update ? (int) ($input['id'] ?? 0) : 0;
        $term = $id ? get_term($id, $slug) : null;
        if ($update && (!$term instanceof WP_Term || !current_user_can('edit_term', $id))) return self::error('id', 'You cannot edit this term.');
        $args = array_intersect_key($input, array_flip(['name', 'slug', 'description', 'parent']));
        if (isset($args['slug']))
        {
            $args['slug'] = sanitize_title($args['slug']);
            if ($args['slug'] === '') return self::error('slug', 'Supply a slug containing valid characters.');
        }
        if (!$update && $taxonomy->hierarchical && !isset($args['parent'])) $args['parent'] = 0;
        if (!$update && empty(trim($args['name'] ?? ''))) return self::error('name', 'A term name is required.');
        if (isset($args['name']) && !trim($args['name'])) return self::error('name', 'A term name cannot be empty.');
        if (isset($args['parent']))
        {
            $parent = (int) $args['parent'];
            if (!$taxonomy->hierarchical && $parent) return self::error('parent', 'This taxonomy is not hierarchical.');
            if ($parent && !(get_term($parent, $slug) instanceof WP_Term)) return self::error('parent', 'The parent must exist in this taxonomy.');
            if ($id && ($parent === $id || in_array($id, get_ancestors($parent, $slug, 'taxonomy'), true))) return self::error('parent', 'A term cannot be its own ancestor.');
        }
        $metadata = $input['metadata'] ?? [];
        $validation = rest_validate_value_from_schema($metadata, LSD_Listings_Catalog::object($this->metadata($slug)), 'metadata');
        if (is_wp_error($validation)) return self::error('metadata', $validation->get_error_message());
        if (!empty($metadata['image']) && (!current_user_can('upload_files') || !wp_attachment_is_image($metadata['image']) || !current_user_can('edit_post', $metadata['image']))) return self::error('metadata.image', 'Select an image you may edit.');
        $duplicate = null;
        if (!empty($args['slug'])) $duplicate = get_term_by('slug', sanitize_title($args['slug']), $slug);
        if ($duplicate && $id && (int) $duplicate->term_id !== $id) return self::error('slug', 'This slug belongs to another term.');
        if (!$update)
        {
            $names = get_terms(['taxonomy' => $slug, 'hide_empty' => false, 'name' => $args['name'], 'parent' => $args['parent'] ?? 0, 'number' => 2]);
            if (is_wp_error($names)) return $names;
            if (!$duplicate && count($names) === 1) $duplicate = $names[0];
            if ($duplicate) return $this->existing($duplicate, $args, $metadata);
        }
        $result = $update ? wp_update_term($id, $slug, $args) : wp_insert_term($args['name'], $slug, $args);
        if (is_wp_error($result))
        {
            if (!$update && $result->get_error_code() === 'term_exists')
            {
                $existing = get_term((int) $result->get_error_data(), $slug);
                if ($existing instanceof WP_Term) return $this->existing($existing, $args, $metadata);
            }
            return $result;
        }
        $id = (int) $result['term_id'];
        self::save_metadata($id, $metadata);
        foreach ($metadata as $key => $value)
            if ((string) get_term_meta($id, 'lsd_' . $key, true) !== (string) $value) return self::error('metadata.' . $key, 'The term was saved but its metadata could not be confirmed.', ['outcome' => 'partial', 'id' => $id]);
        return ['term' => $this->term(get_term($id, $slug)), 'outcome' => $update ? 'updated' : 'created'];
    }

    private function existing(WP_Term $term, array $args, array $metadata)
    {
        foreach ($args as $key => $value)
            if ((string) $term->$key !== (string) $value) return self::error($key, 'An existing term conflicts with this request.', ['candidates' => [$this->term($term)]]);
        foreach ($metadata as $key => $value)
            if ((string) get_term_meta($term->term_id, 'lsd_' . $key, true) !== (string) $value) return self::error('metadata.' . $key, 'Existing term metadata differs. Use update-term explicitly.', ['candidates' => [$this->term($term)]]);
        return ['term' => $this->term($term), 'outcome' => 'existing'];
    }

    public static function error(string $field, string $message, array $extra = []): WP_Error
    {
        return new WP_Error('listdom_invalid_term', __($message, 'listdom'), $extra + ['outcome' => 'failed', 'fields' => [['field' => $field, 'message' => __($message, 'listdom')]]]);
    }
}
