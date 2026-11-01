<?php

/** Canonical listing operations shared by Listdom REST and native abilities. */
class LSD_Listings_Service
{
    /**
     * Create a listing with Listdom's validation and publishing rules.
     *
     * @return array|WP_Error
     */
    public function create(array $vars, bool $require_capability = true)
    {
        if (!get_current_user_id() || ($require_capability && !LSD_Capability::can('edit_listings', 'edit_posts')))
            return new WP_Error('listdom_forbidden', esc_html__('You are not allowed to create listings.', 'listdom'));

        $title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';

        // Apply the same validation and publishing rules as REST creation.
        if (!trim($title)) return new WP_Error('listdom_invalid_title', esc_html__('Listing title field is required!', 'listdom'));

        $validation = apply_filters('lsd_api_listing_validate', true, $vars, null);
        if (is_wp_error($validation)) return $validation;

        // Validate defaults as well as supplied coordinates before inserting or publishing.
        $validation = (new LSD_Entity_Listing())->validate_location($vars);
        if (is_wp_error($validation)) return $validation;

        $status = current_user_can('publish_posts') ? 'publish' : 'pending';
        $status = apply_filters('lsd_default_listing_status', $status, $vars);
        $status = apply_filters('lsd_api_listing_status', $status, $vars);
        if (in_array($status, ['publish', 'private', 'future'], true) && !current_user_can('publish_posts'))
            return new WP_Error('listdom_forbidden', esc_html__('You are not allowed to publish listings.', 'listdom'));

        $id = wp_insert_post([
            'post_title' => $title,
            'post_content' => $vars['content'] ?? '',
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $status,
        ], true);
        if (is_wp_error($id)) return $id;

        // The REST flow assigns taxonomy and media before its publish hook.
        $this->save_terms_and_image((int) $id, $vars);

        if ($status === 'publish' && get_post_status($id) !== 'published') wp_publish_post($id);
        (new LSD_Entity_Listing($id))->save($vars);

        return [
            'id' => (int) $id,
            'status' => get_post_status($id),
            'message' => $status === 'publish'
                ? esc_html__('The listing published.', 'listdom')
                : esc_html__('The listing submitted. It will publish as soon as possible.', 'listdom'),
        ];
    }

    /**
     * Replace the core fields of an existing listing.
     *
     * @return array|WP_Error
     */
    public function update(int $id, array $vars)
    {
        $listing = $this->listing($id);
        if (is_wp_error($listing)) return $listing;
        if (!current_user_can('edit_post', $id)) return new WP_Error('listdom_forbidden', esc_html__('You are not allowed to edit this listing.', 'listdom'));
        if (get_post_meta($id, 'lsd_lock_edit', true)) return new WP_Error('listdom_listing_locked', esc_html__('This listing is locked for editing.', 'listdom'));

        $title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';
        if (!trim($title)) return new WP_Error('listdom_invalid_title', esc_html__('Listing title field is required!', 'listdom'));

        $validation = apply_filters('lsd_api_listing_validate', true, $vars, $listing);
        if (is_wp_error($validation)) return $validation;

        $validation = (new LSD_Entity_Listing($id))->validate_location($vars);
        if (is_wp_error($validation)) return $validation;

        $result = wp_update_post(['ID' => $id, 'post_title' => $title, 'post_content' => $vars['content'] ?? ''], true);
        if (is_wp_error($result)) return $result;

        $this->save_terms_and_image($id, $vars);
        (new LSD_Entity_Listing($id))->save($vars);
        return ['id' => $id, 'status' => get_post_status($id)];
    }

    /**
     * Apply a sparse listing update from the ability input.
     *
     * @return array|WP_Error
     */
    public function patch(int $id, array $vars)
    {
        return (new LSD_Listings_Workflow())->patch($id, $vars);
    }

    /** Native creation keeps domain validation and persistence outside adapters. */
    public function create_managed(array $vars, bool $draft = true)
    {
        return (new LSD_Listings_Workflow())->create($vars, $draft);
    }

    /**
     * Move an authorized listing to the trash.
     *
     * @return array|WP_Error
     */
    public function trash(int $id)
    {
        $listing = $this->listing($id);
        if (is_wp_error($listing)) return $listing;
        if (!current_user_can('delete_post', $id)) return new WP_Error('listdom_forbidden', esc_html__('You are not allowed to trash this listing.', 'listdom'));

        $trashed = wp_trash_post($id);
        if (!$trashed) return new WP_Error('listdom_trash_failed', esc_html__('The listing could not be moved to trash.', 'listdom'));
        return ['id' => $id, 'trashed' => true];
    }

    /**
     * Validate and send a listing owner contact request.
     *
     * @return array|WP_Error
     */
    public function contact(int $id, array $vars)
    {
        $listing = $this->listing($id);
        if (is_wp_error($listing)) return $listing;

        $email = sanitize_email($vars['email'] ?? '');
        $message = isset($vars['message']) ? sanitize_textarea_field($vars['message']) : '';
        if (!trim($email) || !trim($message)) return new WP_Error('listdom_invalid_contact', esc_html__('Email and message fields are required!', 'listdom'));
        if (!is_email($email)) return new WP_Error('listdom_invalid_email', esc_html__('Email is not valid!', 'listdom'));

        do_action('lsd_contact_owner', [
            'name' => sanitize_text_field($vars['name'] ?? ''),
            'email' => $email,
            'phone' => sanitize_text_field($vars['phone'] ?? ''),
            'message' => $message,
            'post_id' => $id,
        ]);

        (new LSD_Entity_Listing($id))->update_contacts();
        return ['sent' => true];
    }

    /**
     * Load a listing post by ID.
     *
     * @return WP_Post|WP_Error
     */
    public function listing(int $id)
    {
        $listing = get_post($id);
        return $listing instanceof WP_Post && $listing->post_type === LSD_Base::PTYPE_LISTING
            ? $listing
            : new WP_Error('listdom_listing_not_found', esc_html__('Listing not found.', 'listdom'));
    }

    /** Save the listing taxonomy and featured image before entity fields. */
    private function save_terms_and_image(int $id, array $vars): void
    {
        // Preserve the existing taxonomy and entity save order.
        $tax = isset($vars['taxonomies']) && is_array($vars['taxonomies']) ? $vars['taxonomies'] : [];
        $tags = isset($tax[LSD_Base::TAX_TAG]) ? sanitize_text_field($tax[LSD_Base::TAX_TAG]) : '';
        $locations = isset($tax[LSD_Base::TAX_LOCATION]) && is_array($tax[LSD_Base::TAX_LOCATION]) ? $tax[LSD_Base::TAX_LOCATION] : [];
        $featured_image = isset($vars['featured_image']) ? sanitize_text_field($vars['featured_image']) : '';

        wp_set_post_terms($id, $tags, LSD_Base::TAX_TAG);
        wp_set_post_terms($id, $locations, LSD_Base::TAX_LOCATION);

        foreach ([LSD_Base::TAX_FEATURE, LSD_Base::TAX_LABEL] as $taxonomy)
        {
            $terms = isset($tax[$taxonomy]) && is_array($tax[$taxonomy]) ? $tax[$taxonomy] : [];
            wp_set_post_terms($id, LSD_Taxonomies::name($terms, $taxonomy), $taxonomy);
        }

        set_post_thumbnail($id, $featured_image);
    }
}
