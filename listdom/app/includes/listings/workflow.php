<?php

/** Prepared, bounded management operations over the canonical listing entity. */
class LSD_Listings_Workflow
{
    public static function error(string $field, string $message, array $extra = []): WP_Error
    {
        return new WP_Error('listdom_listing_validation', __($message, 'listdom'), $extra + ['outcome' => 'failed', 'fields' => [['field' => $field, 'message' => __($message, 'listdom')]]]);
    }

    public function authorized(int $id = 0)
    {
        if (!get_current_user_id()) return self::error('permission', 'Authentication is required.');
        if (!$id) return LSD_Capability::can('edit_listings', 'edit_posts') ? true : self::error('permission', 'You cannot create listings.');
        $post = (new LSD_Listings_Service())->listing($id);
        if (is_wp_error($post)) return $post;
        if (!current_user_can('edit_post', $id)) return self::error('permission', 'You cannot edit this listing.');
        return true;
    }

    public function read(int $id)
    {
        $allowed = $this->authorized($id);
        if (is_wp_error($allowed)) return $allowed;
        return ['id' => $id, 'status' => get_post_status($id), 'values' => (object) (new LSD_Listings_Catalog())->values($id),
            'edit_url' => (string) get_edit_post_link($id, 'raw')];
    }

    /** No persistence, term creation, or status changes are performed here. */
    public function prepare(array $input, int $id = 0, bool $legacy = false)
    {
        $allowed = $this->authorized($id);
        if (is_wp_error($allowed)) return $allowed;
        if ($id && get_post_meta($id, 'lsd_lock_edit', true)) return new WP_Error('listdom_listing_locked', __('This listing is locked for editing.', 'listdom'));
        $catalog = new LSD_Listings_Catalog();
        unset($input['id'], $input['request_id']);
        if ($id && !$input) return self::error('input', 'Supply at least one listing field.');
        $schema = LSD_Listings_Catalog::object($catalog->input_properties($legacy));
        $validation = rest_validate_value_from_schema($input, $schema, 'listing');
        if (is_wp_error($validation)) return self::error('input', $validation->get_error_message());
        $saved = $id ? $catalog->values($id) : [];
        $taxonomy_service = new LSD_Listings_Taxonomies();
        if (isset($input['primary_category']))
        {
            $resolved = $taxonomy_service->resolve($input['primary_category']);
            if (is_wp_error($resolved)) return $resolved;
            if (isset($input['listing_category']) && $input['listing_category'] !== $resolved) return self::error('primary_category', 'Category selectors disagree.');
            $input['listing_category'] = $resolved;
            unset($input['primary_category']);
        }
        $definitions = $catalog->definitions();
        if (isset($input['gallery']) && (isset($input['gallery_add']) || isset($input['gallery_remove']))) return self::error('gallery', 'Use replacement or add/remove operations, not both.');
        if (isset($input['gallery_add']) || isset($input['gallery_remove']))
        {
            if (!$definitions['gallery']['writable']) return self::error('gallery', 'The gallery is not writable.');
            $gallery = (array) ($saved['gallery'] ?? []);
            $input['gallery'] = array_values(array_unique(array_merge(array_diff($gallery, $input['gallery_remove'] ?? []), $input['gallery_add'] ?? [])));
            unset($input['gallery_add'], $input['gallery_remove']);
            if (count($input['gallery']) > 100) return self::error('gallery', 'A gallery may contain at most 100 images.');
        }
        if (isset($input['sc']) && !$input['sc'])
            $input['sc'] = array_fill_keys(array_keys($definitions['sc']['schema']['properties'] ?? []), '');
        $effective = array_replace($saved, $input);
        foreach (['attributes', 'sc', 'ava', 'extensions', 'taxonomies'] as $key)
            if (isset($saved[$key]) && (isset($input[$key]) && (in_array($key, ['extensions', 'taxonomies'], true) || $input[$key])))
            {
                $effective[$key] = $input[$key] + $saved[$key];
                if ($key === 'ava') foreach ($input[$key] as $day => $hours) $effective[$key][$day] = $hours + ($saved[$key][$day] ?? []);
            }
        foreach ($catalog->providers() as $provider_id => $provider)
        {
            $patch = [];
            foreach ($input['extensions'] ?? [] as $name => $value) if (strpos($name, $provider_id . '.') === 0) $patch[substr($name, strlen($provider_id) + 1)] = $value;
            if (!$patch && empty($provider['validate_saved'])) continue;
            $valid = call_user_func($provider['validate'], $patch, $id, $effective);
            if (is_wp_error($valid)) return $valid;
            if ($valid !== true) return self::error('extensions', 'The field provider rejected this input.');
        }
        $policy_context = ['id' => $id, 'fields' => $effective];
        $definitions = $catalog->definitions($policy_context);
        foreach ($input as $key => $value)
        {
            if (in_array($key, ['gallery_add', 'gallery_remove', 'extensions'], true)) continue;
            if (!isset($definitions[$key]) || !$definitions[$key]['writable']) return self::error($key, 'This field is unavailable or not writable.');
            if ($value === null) return self::error($key, 'Null is not a clearing value. Use the declared empty value.');
            if (($value === '' || $value === []) && !$definitions[$key]['clearable']) return self::error($key, 'This field cannot be cleared.');
        }
        foreach ($definitions as $key => $field)
        {
            if (!$field['available'] || !$field['writable'] || isset($field['provider'])) continue;
            $value = $effective[$key] ?? null;
            $required = $field['required'];
            if ($required && ($value === null || $value === '' || $value === [] || ($key === 'title' && !trim($value)) || ($key === 'featured_image' && (int) $value === 0))) return self::error($key, 'This configured field is required.');
        }
        $category = (int) ($effective['listing_category'] ?? 0);
        if (!$category || !(get_term($category, LSD_Base::TAX_CATEGORY) instanceof WP_Term)) return self::error('listing_category', 'Select an existing primary category.');
        if (isset($input['listing_category']) || isset($input['listing_categories']) || !$id)
        {
            $taxonomy = $taxonomy_service->available()[LSD_Base::TAX_CATEGORY] ?? null;
            if (!$taxonomy || !current_user_can($taxonomy->cap->assign_terms)) return self::error('listing_category', 'You cannot assign categories.');
            $input['listing_category'] = $category;
            $input['listing_categories'] = $effective['listing_categories'] ?? [];
            foreach ($input['listing_categories'] as $term_id) if (!(get_term($term_id, LSD_Base::TAX_CATEGORY) instanceof WP_Term)) return self::error('listing_categories', 'Every category must exist.');
        }
        foreach ($input['taxonomies'] ?? [] as $slug => &$terms)
        {
            $taxonomy = $taxonomy_service->available()[$slug] ?? null;
            if (!$taxonomy || !current_user_can($taxonomy->cap->assign_terms)) return self::error('taxonomies.' . $slug, 'You cannot assign this taxonomy.');
            $module = [LSD_Base::TAX_LOCATION => 'locations', LSD_Base::TAX_FEATURE => 'features', LSD_Base::TAX_LABEL => 'labels', LSD_Base::TAX_TAG => 'tags'][$slug] ?? '';
            if ($module && !$catalog->module_enabled($module, $policy_context)) return self::error('taxonomies.' . $slug, 'This taxonomy module is disabled.');
            if (is_string($terms))
            {
                // Legacy comma-separated tags are resolved before any writes.
                $names = array_filter(array_map('trim', explode(',', $terms)), 'strlen');
                $terms = [];
                foreach ($names as $name)
                {
                    $matches = get_terms(['taxonomy' => $slug, 'name' => $name, 'hide_empty' => false, 'number' => 2]);
                    if (is_wp_error($matches)) return $matches;
                    if (count($matches) > 1) return self::error('taxonomies.' . $slug, 'Choose an existing term ID for an ambiguous tag.');
                    if (!$matches && !$taxonomy_service->permission($slug, 'create')) return self::error('taxonomies.' . $slug, 'Creating a missing legacy tag requires term-creation permission.');
                    $terms[] = $matches ? (int) $matches[0]->term_id : $name;
                }
            }
            foreach ($terms as $term_id) if (is_int($term_id) && !(get_term($term_id, $slug) instanceof WP_Term)) return self::error('taxonomies.' . $slug, 'Every assigned term must exist in its taxonomy.');
            if ($slug === LSD_Base::TAX_LABEL && class_exists('\LSDPACLBL\Access'))
            {
                $access = new \LSDPACLBL\Access();
                $subscription_id = isset($effective['extensions']['subscriptions.subscription_id']) ? (int) $effective['extensions']['subscriptions.subscription_id'] : null;
                $package_labels = class_exists('\LSDPACLBL\Addon') ? \LSDPACLBL\Addon::package_label_ids($id, $subscription_id) : [];
                foreach ($terms as $label_id)
                    if ((int) get_term_meta($label_id, 'lsd_product', true) > 0 && !in_array($label_id, $package_labels, true) && (!$id || !$access->is_active($id, $label_id, false)))
                        return self::error('taxonomies.' . $slug, 'This paid label requires active access or authorization from the effective membership package.');
            }
            $input['taxonomies'][$slug] = $terms;
            $effective['taxonomies'][$slug] = $terms;
        }
        unset($terms);
        foreach ($definitions['taxonomies']['relationships'] as $slug => $relationship)
            if ($relationship['required'] && empty($effective['taxonomies'][$slug])) return self::error('taxonomies.' . $slug, 'This configured taxonomy relationship is required.');

        foreach (['latitude' => [-90, 90], 'longitude' => [-180, 180], 'price' => [0, PHP_FLOAT_MAX], 'price_max' => [0, PHP_FLOAT_MAX], 'shape_radius' => [0, PHP_FLOAT_MAX], 'zoomlevel' => [0, 22]] as $key => $range)
        {
            if (!array_key_exists($key, $input)) continue;
            $value = $input[$key];
            if ($value !== '' && (!is_numeric($value) || !is_finite((float) $value) || $value < $range[0] || $value > $range[1])) return self::error($key, 'Supply a number within the allowed range.');
            if (in_array($key, ['latitude', 'longitude'], true) && $value === '') return self::error($key, 'Coordinates cannot be cleared independently of the geographic index.');
        }
        if (!empty($effective['price_max']) && (float) $effective['price_max'] < (float) ($effective['price'] ?? 0)) return self::error('price_max', 'Maximum price must not be below the price.');
        foreach (['email', 'website', 'link', 'cta_url'] as $key)
            if (!empty($input[$key]) && ($key === 'email' ? !is_email($input[$key]) : !wp_http_validate_url($input[$key]))) return self::error($key, 'Supply a valid email address or HTTP(S) URL.');
        foreach ($input['sc'] ?? [] as $key => $value)
            if ($value !== '' && ($definitions['sc']['input_types'][$key] ?? '') === 'url' && !wp_http_validate_url($value)) return self::error('sc.' . $key, 'Supply a valid HTTP(S) social URL.');
        if ($definitions['sc']['writable'])
            foreach ($definitions['sc']['required_fields'] as $key)
                if (!strlen(trim($effective['sc'][$key] ?? ''))) return self::error('sc.' . $key, 'This configured social field is required.');
        $images = ['featured_image' => isset($input['featured_image']) ? [$input['featured_image']] : [], 'gallery' => $input['gallery'] ?? []];
        foreach (array_merge(isset($input['featured_image']) ? [$input['featured_image']] : [], $input['gallery'] ?? []) as $attachment)
            if ($attachment && (!current_user_can('upload_files') || !wp_attachment_is_image($attachment) || !current_user_can('edit_post', $attachment))) return self::error('media', 'Select existing images you may edit.');
        foreach ($input['gallery_alt'] ?? [] as $attachment => $alt)
            if (!ctype_digit((string) $attachment) || !in_array((int) $attachment, $effective['gallery'] ?? [], true)) return self::error('gallery_alt', 'Alt text must refer to an image in the effective gallery.');
        foreach ($input['related_listings'] ?? [] as $related)
            if (get_post_type($related) !== LSD_Base::PTYPE_LISTING || !current_user_can('read_post', $related)) return self::error('related_listings', 'Select listings you may read.');
        if ($definitions['ava']['writable'] && $definitions['ava']['required'])
            foreach (LSD_Main::get_weekdays() as $weekday)
            {
                $day = $effective['ava'][$weekday['code']] ?? [];
                if (empty($day['off']) && !strlen(trim($day['hours'] ?? ''))) return self::error('ava.' . $weekday['code'], 'Set hours or mark this day off.');
            }
        foreach ($input['extensions'] ?? [] as $name => $value)
        {
            if (empty($definitions[$name]['writable'])) return self::error('extensions.' . $name, 'This extension field is not writable.');
            if (($value === null || $value === '' || $value === []) && empty($definitions[$name]['clearable'])) return self::error('extensions.' . $name, 'This extension field cannot be cleared.');
        }
        foreach ($definitions as $name => $field)
            if (isset($field['provider']) && $field['writable'] && $field['required'] && !array_key_exists($name, $effective['extensions'] ?? [])) return self::error('extensions.' . $name, 'This extension field is required.');
        $context = LSD_Taxonomies_Attribute::context(['post_id' => $id, 'category_id' => $category, 'resolve_post_category' => false, 'submission' => true, 'listing_fields' => $effective]);
        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            $key = $attribute['slug'];
            $applies = LSD_Taxonomies_Attribute::applies((int) $attribute['id'], $context);
            if (!$applies && array_key_exists($key, $input['attributes'] ?? [])) return self::error('attributes.' . $key, 'This attribute does not apply to the selected category or package.');
            $value = $effective['attributes'][$key] ?? '';
            if ($definitions['attributes']['writable'] && $applies && $attribute['required'] && ($value === '' || $value === [])) return self::error('attributes.' . $key, 'This attribute is required.');
            if (!array_key_exists($key, $input['attributes'] ?? [])) continue;
            if (in_array($attribute['field_type'], ['image', 'file'], true) && $value !== '')
            {
                if (!ctype_digit((string) $value) || !current_user_can('upload_files') || get_post_type((int) $value) !== 'attachment' || !current_user_can('edit_post', (int) $value)) return self::error('attributes.' . $key, 'Use an attachment ID you may edit.');
                if ($attribute['field_type'] === 'image' && !wp_attachment_is_image((int) $value)) return self::error('attributes.' . $key, 'Select an image attachment.');
                if ($attribute['field_type'] === 'image') $images['attributes.' . $key] = [(int) $value];
                if ($attribute['field_type'] === 'file')
                {
                    $file = LSD_Entity_Listing::validate_file_attribute_value($value, (int) $attribute['id'], $attribute['name'], true);
                    if (empty($file['valid'])) return self::error('attributes.' . $key, (string) $file['message']);
                }
            }
            if (in_array($attribute['field_type'], ['date', 'time', 'datetime'], true) && $value !== '' && strtotime($value) === false) return self::error('attributes.' . $key, 'Supply a valid date or time.');
            if ($attribute['field_type'] === 'number' && $value !== '' && !is_numeric($value)) return self::error('attributes.' . $key, 'Supply a numeric attribute value.');
            if ($attribute['field_type'] === 'email' && $value !== '' && !is_email($value)) return self::error('attributes.' . $key, 'Supply a valid email address.');
            if ($attribute['field_type'] === 'url' && $value !== '' && !wp_http_validate_url($value)) return self::error('attributes.' . $key, 'Supply a valid HTTP(S) URL.');
        }
        $limits = $catalog->restrictions($policy_context);
        $max_image_bytes = (int) ($limits['max_upload_size'] ?? 0) * 1024;
        if ($max_image_bytes > 0)
            foreach ($images as $field => $attachments)
                foreach ($attachments as $attachment)
                {
                    $path = $attachment ? get_attached_file($attachment) : false;
                    if ($path && file_exists($path) && filesize($path) > $max_image_bytes) return self::error($field, 'The selected image exceeds the configured image-size limit.');
                }
        if (trim((string) $limits['max_gallery_images']) !== '' && count($effective['gallery'] ?? []) > (int) $limits['max_gallery_images']) return self::error('gallery', 'The gallery exceeds the configured image limit.');
        if (trim((string) $limits['description_length']) !== '' && mb_strlen(trim(wp_strip_all_tags($effective['content'] ?? ''))) > (int) $limits['description_length']) return self::error('content', 'The description exceeds the configured limit.');
        if (trim((string) $limits['max_tags']) !== '' && count(array_unique($effective['taxonomies'][LSD_Base::TAX_TAG] ?? [])) > (int) $limits['max_tags']) return self::error('taxonomies.' . LSD_Base::TAX_TAG, 'The tags exceed the configured limit.');
        $valid = (new LSD_Entity_Listing($id ?: null))->validate_location($effective);
        if (is_wp_error($valid)) return $valid;
        $valid = apply_filters('lsd_api_listing_validate', true, $effective, $id ? get_post($id) : null);
        if (is_wp_error($valid)) return $valid;
        if ($valid !== true) return self::error('input', 'Listing validation was rejected.');
        return $input;
    }

    public function validate(array $input)
    {
        $prepared = $this->prepare($input, (int) ($input['id'] ?? 0));
        if (is_wp_error($prepared)) return ['valid' => false, 'errors' => $prepared->get_error_data()['fields'] ?? [['field' => 'input', 'message' => $prepared->get_error_message()]], 'warnings' => []];
        return ['valid' => true, 'errors' => [], 'warnings' => []];
    }

    public function patch(int $id, array $input)
    {
        $prepared = $this->prepare($input, $id, true);
        if (is_wp_error($prepared)) return $prepared;
        return $this->persist($id, $prepared);
    }

    private function persist(int $id, array $input, bool $creating = false)
    {
        try
        {
            $post = ['ID' => $id];
            foreach (['title', 'content', 'excerpt'] as $key) if (array_key_exists($key, $input)) $post['post_' . $key] = $key === 'title' ? sanitize_text_field($input[$key]) : wp_kses_post($input[$key]);
            $updated = wp_update_post($post, true);
            if (is_wp_error($updated)) return $updated;
            foreach ($input['taxonomies'] ?? [] as $taxonomy => $ids)
            {
                foreach ($ids as &$term_id)
                {
                    if (!is_string($term_id)) continue;
                    $term = (new LSD_Listings_Taxonomies())->write(['taxonomy' => $taxonomy, 'name' => $term_id]);
                    if (is_wp_error($term)) return self::error('taxonomies.' . $taxonomy, $term->get_error_message(), ['outcome' => 'partial', 'id' => $id]);
                    $term_id = $term['term']['id'];
                }
                unset($term_id);
                $input['taxonomies'][$taxonomy] = $ids;
                $result = wp_set_object_terms($id, array_values(array_unique($ids)), $taxonomy);
                if (is_wp_error($result)) return self::error('taxonomies.' . $taxonomy, $result->get_error_message(), ['outcome' => 'partial', 'id' => $id]);
            }
            if (array_key_exists('featured_image', $input))
            {
                if ($input['featured_image']) set_post_thumbnail($id, $input['featured_image']);
                else delete_post_thumbnail($id);
            }
            $saved = (new LSD_Entity_Listing($id))->save_managed($input, $creating);
            // Populate socials before provider writes and notify listeners only after
            // every managed field has been persisted successfully.
            if (!empty($input['sc'])) (new LSD_Socials())->listing_save(get_post($id), $input);
            if (is_wp_error($saved)) return self::error('input', $saved->get_error_message(), ['outcome' => 'partial', 'id' => $id]);
            // Verify relationships and media; WordPress setters may return false for unchanged values.
            foreach ($input['taxonomies'] ?? [] as $taxonomy => $ids)
            {
                $actual = wp_get_object_terms($id, $taxonomy, ['fields' => 'ids']);
                if (is_wp_error($actual) || array_diff($ids, $actual) || array_diff($actual, $ids)) return self::error('taxonomies.' . $taxonomy, 'Saved terms did not match the request.', ['outcome' => 'partial', 'id' => $id]);
            }
            if (isset($input['featured_image']) && (int) get_post_thumbnail_id($id) !== $input['featured_image']) return self::error('featured_image', 'The image association could not be confirmed.', ['outcome' => 'partial', 'id' => $id]);
            foreach ((new LSD_Listings_Catalog())->providers() as $provider_id => $provider)
            {
                $patch = [];
                foreach ($input['extensions'] ?? [] as $name => $value) if (strpos($name, $provider_id . '.') === 0) $patch[substr($name, strlen($provider_id) + 1)] = $value;
                if (!$patch) continue;
                $saved = call_user_func($provider['save'], $id, $patch);
                if (is_wp_error($saved) || $saved !== true) return self::error('extensions', 'The field provider write could not be confirmed.', ['outcome' => 'partial', 'id' => $id]);
            }
            if (!$creating)
            {
                $saved_data = $input;
                if (get_post_status($id) === 'draft') $saved_data['_lsd_managed_draft'] = true;
                do_action('lsd_listing_saved', get_post($id), $saved_data, false);
            }
            return $this->result($id);
        }
        catch (Throwable $error)
        {
            return self::error('input', 'The write was interrupted. Inspect this listing before correcting it.', ['outcome' => 'uncertain', 'id' => $id]);
        }
    }

    private function result(int $id, array $warnings = []): array
    {
        return ['id' => $id, 'status' => (string) get_post_status($id), 'message' => __('Listing saved.', 'listdom'), 'warnings' => $warnings, 'edit_url' => (string) get_edit_post_link($id, 'raw')];
    }

    public function create(array $input, bool $draft = true)
    {
        $allowed = $this->authorized();
        if (is_wp_error($allowed)) return $allowed;
        $request = $input['request_id'] ?? '';
        if ($request !== '' && (!is_string($request) || !preg_match('/^[A-Za-z0-9_-]{8,128}$/D', $request))) return self::error('request_id', 'Use 8–128 ASCII letters, digits, underscores or hyphens.');
        if ($draft && !$request) return self::error('request_id', 'A stable request ID is required for draft creation.');
        $reservation = $request ? 'lsd_listing_request_' . hash('sha256', get_current_user_id() . ':' . $request) : '';
        $fingerprint = hash('sha256', serialize([$draft, $this->canonical($input)]));
        if ($reservation)
        {
            $existing = get_option($reservation);
            if ($existing) return $this->replay($existing, $fingerprint);
        }
        $prepared = $this->prepare($input, 0, !$draft);
        if (is_wp_error($prepared)) return $prepared;
        $status = $draft ? 'draft' : (current_user_can(get_post_type_object(LSD_Base::PTYPE_LISTING)->cap->publish_posts) ? 'publish' : 'pending');
        if (!$draft)
        {
            $status = apply_filters('lsd_default_listing_status', $status, $prepared);
            $status = apply_filters('lsd_api_listing_status', $status, $prepared);
        }
        if (in_array($status, ['publish', 'private', 'future'], true) && !current_user_can(get_post_type_object(LSD_Base::PTYPE_LISTING)->cap->publish_posts)) return self::error('status', 'You cannot publish listings.');
        if (!is_string($status) || !get_post_status_object($status)) return self::error('status', 'A status filter returned an unregistered status.');
        $record = ['fingerprint' => $fingerprint, 'state' => 'reserved', 'id' => 0];
        if ($reservation && !add_option($reservation, $record, '', false)) return $this->replay((array) get_option($reservation), $fingerprint);
        $id = 0;
        try
        {
            // Never publish a half-populated listing. Run normal transition hooks after saving.
            $id = wp_insert_post(['post_type' => LSD_Base::PTYPE_LISTING, 'post_title' => sanitize_text_field($prepared['title']), 'post_content' => wp_kses_post($prepared['content'] ?? ''), 'post_status' => 'draft',
                'meta_input' => [LSD_Listings_Lifecycle::META => 'preparing']], true);
            if (is_wp_error($id))
            {
                if ($reservation) delete_option($reservation);
                return $id;
            }
            $id = (int) $id;
            $record['id'] = $id;
            $record['state'] = 'writing';
            if ($reservation && !update_option($reservation, $record, false)) return self::error('request_id', 'The listing exists but retry tracking could not be confirmed.', ['outcome' => 'uncertain', 'id' => $id]);
            $result = $this->persist($id, $prepared, true);
            if (is_wp_error($result)) return $result;
            if (!$draft && $status !== get_post_status($id))
            {
                $updated = wp_update_post(['ID' => $id, 'post_status' => $status], true);
                if (is_wp_error($updated)) return self::error('status', 'Fields were saved but the status transition failed.', ['outcome' => 'partial', 'id' => $id]);
            }
            if ($draft && get_post_status($id) !== 'draft') return self::error('status', 'A hook changed the draft status. Inspect the saved listing.', ['outcome' => 'uncertain', 'id' => $id]);
            $saved_data = $prepared;
            if ($draft) $saved_data['_lsd_managed_draft'] = true;
            do_action('lsd_listing_saved', get_post($id), $saved_data, true);
            if ($draft && get_post_status($id) !== 'draft') return self::error('status', 'A hook changed the draft status. Inspect the saved listing.', ['outcome' => 'uncertain', 'id' => $id]);
            if ($draft)
            {
                update_post_meta($id, LSD_Listings_Lifecycle::META, 'waiting');
                if (get_post_meta($id, LSD_Listings_Lifecycle::META, true) !== 'waiting') return self::error('input', 'The draft was saved but deferred announcement tracking could not be confirmed.', ['outcome' => 'uncertain', 'id' => $id]);
            }
            else
            {
                // Legacy creation still announces its completed published/pending
                // submission once, rather than its internal staging transition.
                delete_post_meta($id, LSD_Listings_Lifecycle::META);
                do_action('lsd_new_listing', $id);
            }
            if ($draft && get_post_status($id) !== 'draft') return self::error('status', 'A hook changed the draft status. Inspect the saved listing.', ['outcome' => 'uncertain', 'id' => $id]);
            $record['state'] = 'completed';
            if ($reservation && !update_option($reservation, $record, false)) return self::error('request_id', 'The saved listing exists but completion could not be recorded.', ['outcome' => 'uncertain', 'id' => $id]);
            return $this->result($id, get_post_status($id) !== $status ? [__('A moderation hook changed the requested status.', 'listdom')] : []);
        }
        catch (Throwable $error)
        {
            return self::error('input', 'Creation was interrupted. Do not use a new request ID to retry.', ['outcome' => 'uncertain', 'id' => is_int($id) ? $id : 0]);
        }
        finally
        {
            // An interrupted draft can still be inspected, corrected, and explicitly
            // submitted without replaying its reserved creation request.
            if ($draft && is_int($id) && $id > 0 && get_post_status($id) === 'draft' && get_post_meta($id, LSD_Listings_Lifecycle::META, true) === 'preparing')
                update_post_meta($id, LSD_Listings_Lifecycle::META, 'waiting');
        }
    }

    private function canonical(array $input): array
    {
        if ($input && array_keys($input) !== range(0, count($input) - 1)) ksort($input);
        foreach ($input as &$value) if (is_array($value)) $value = $this->canonical($value);
        return $input;
    }

    private function replay(array $record, string $fingerprint)
    {
        if (($record['fingerprint'] ?? '') !== $fingerprint) return self::error('request_id', 'This request ID was used with different input.');
        $id = (int) ($record['id'] ?? 0);
        if ($id)
        {
            $allowed = $this->authorized($id);
            if (is_wp_error($allowed)) return $allowed;
        }
        if (($record['state'] ?? '') !== 'completed' || !$id) return self::error('request_id', 'The earlier creation is incomplete or uncertain. Inspect it before applying corrections.', ['outcome' => 'uncertain', 'id' => $id]);
        return $this->result($id, [__('This request already created the listing; no write was repeated.', 'listdom')]);
    }

    public function transition(array $input)
    {
        $id = (int) $input['id'];
        $allowed = $this->authorized($id);
        if (is_wp_error($allowed)) return $allowed;
        if (get_post_meta($id, 'lsd_lock_edit', true)) return self::error('id', 'This listing is locked.');
        $status = $input['status'];
        if (!in_array($status, ['draft', 'pending', 'private', 'publish'], true)) return self::error('status', 'This status is unsupported.');
        if (in_array(get_post_status($id), ['trash', LSD_Base::STATUS_DENIED], true) && $status !== 'pending') return self::error('status', 'This listing must follow its existing moderation workflow.');
        if (get_post_status($id) === 'trash') return self::error('status', 'Restore trashed listings through the existing workflow first.');
        if (in_array($status, ['publish', 'private'], true) && !current_user_can(get_post_type_object(LSD_Base::PTYPE_LISTING)->cap->publish_posts)) return self::error('status', 'You cannot publish listings.');
        $post = get_post($id);
        $context = ['id' => $id, 'listing' => $post, 'subscription_id' => (int) get_post_meta($id, 'lsd_subscription', true)];
        $valid = apply_filters('lsd_dashboard_validate_request', true, $context);
        if ($valid !== true) return self::error('status', is_wp_error($valid) ? $valid->get_error_message() : 'Listdom moderation rejected the transition.');
        if (in_array($status, ['publish', 'private'], true))
        {
            $values = (new LSD_Listings_Catalog())->values($id);
            unset($values['extensions']);
            // Validate effective fields, including required values, without resubmitting disabled fields.
            $valid = $this->prepare(['id' => $id, 'title' => $values['title']], $id);
            if (is_wp_error($valid)) return $valid;
        }
        $requested = $status;
        $status = apply_filters('lsd_dashboard_listing_status', $status, $context);
        // Denied listings must return to review even when a package autoconfirms.
        if ($post->post_status === LSD_Base::STATUS_DENIED) $status = 'pending';
        if (!in_array($status, ['draft', 'pending', 'private', 'publish'], true)) return self::error('status', 'A moderation hook returned an unsupported status.');
        // Draft/private intent cannot authorize submission through a moderation filter.
        if (in_array($requested, ['draft', 'private'], true) && $status !== $requested) return self::error('status', 'Moderation cannot honor the requested draft or private status. Request submission or publication separately.');
        if ($status === 'publish' && $requested !== 'publish') return self::error('status', 'Public publication requires an explicit publish request.');
        if (in_array($status, ['publish', 'private'], true) && (!in_array($requested, ['publish', 'private'], true) || !current_user_can(get_post_type_object(LSD_Base::PTYPE_LISTING)->cap->publish_posts))) return self::error('status', 'A hook cannot grant publication intent or permission.');
        $update = ['ID' => $id, 'post_status' => $status];
        if ($status === 'publish' && in_array($post->post_status, ['draft', 'future'], true))
        {
            $update['post_date'] = current_time('mysql');
            $update['post_date_gmt'] = get_gmt_from_date($update['post_date']);
        }
        try { $result = wp_update_post($update, true); }
        catch (Throwable $error)
        {
            return self::error('status', 'The transition or its notifications were interrupted. Inspect the saved listing before retrying.', ['outcome' => 'uncertain', 'id' => $id]);
        }
        if (is_wp_error($result)) return $result;
        if (get_post_status($id) === 'publish' && $post->post_status !== 'publish') do_action('lsd_dashboard_listing_published', get_post($id));
        do_action('lsd_listing_saved', get_post($id), [], false);
        return $this->result($id, get_post_status($id) !== $requested ? [__('Moderation changed the requested status.', 'listdom')] : []);
    }
}
