<?php

class LSD_Abilities_Listings
{
    /** Register listing read and write abilities. */
    public function register(): void
    {
        // Define the shared listing inputs and responses before registration.
        $id = ['type' => 'integer', 'minimum' => 1];
        $listing = LSD_Abilities_Schema::object([
            'id' => $id,
            'title' => ['type' => 'string'],
            'url' => ['type' => 'string'],
        ], ['id', 'title', 'url'], true);

        $properties = (new LSD_Listings_Catalog())->input_properties(true);
        $data = (new LSD_Listings_Catalog())->creation_schema(true);
        $created = LSD_Abilities_Management::result_schema();
        $updated = $created;
        $update = LSD_Abilities_Schema::object(['id' => $id] + $properties, ['id']);
        unset($update['properties']['request_id']);
        $update['minProperties'] = 2;

        // Register read and write operations with their matching schemas.
        LSD_Abilities_Schema::register(
            'listdom/get-listing',
            __('Get a Listdom listing', 'listdom'),
            __('Read the public, visibility-filtered machine payload for a published Listdom listing.', 'listdom'),
            LSD_Abilities_Schema::object(['id' => $id], ['id']),
            $listing,
            '__return_true',
            [$this, 'get'],
            true
        );

        // Create Listing
        LSD_Abilities_Schema::register(
            'listdom/create-listing',
            __('Create a Listdom listing', 'listdom'),
            __('Create a listing with the normal Listdom validation, taxonomy handling, media, and publishing rules.', 'listdom'),
            $data,
            $created,
            [$this, 'can_create'],
            [$this, 'create'],
            false
        );

        // Update Listing
        LSD_Abilities_Schema::register(
            'listdom/update-listing',
            __('Update a Listdom listing', 'listdom'),
            __('Update supplied supported listing fields, preserving omitted fields. Discover fields and clearing rules with get-listing-schema.', 'listdom'),
            $update,
            $updated,
            [$this, 'can_update'],
            [$this, 'update'],
            false,
            true
        );

        // Trash Listing
        LSD_Abilities_Schema::register(
            'listdom/trash-listing',
            __('Trash a Listdom listing', 'listdom'),
            __('Move an authorized listing to the WordPress trash. Does not permanently delete it.', 'listdom'),
            LSD_Abilities_Schema::object(['id' => $id], ['id']),
            LSD_Abilities_Schema::object(['id' => $id, 'trashed' => ['type' => 'boolean']], ['id', 'trashed']),
            [$this, 'can_trash'],
            [$this, 'trash'],
            false,
            true
        );
    }

    /** Check whether the current user may create listings. */
    public function can_create(): bool
    {
        return get_current_user_id() > 0 && LSD_Capability::can('edit_listings', 'edit_posts');
    }

    /** Check edit access to the requested listing. */
    public function can_update($input): bool
    {
        $input = LSD_Abilities_Schema::input($input);
        $id = (int) ($input['id'] ?? 0);
        return $id > 0 && get_post_type($id) === LSD_Base::PTYPE_LISTING && current_user_can('edit_post', $id);
    }

    /** Check delete access to the requested listing. */
    public function can_trash($input): bool
    {
        $input = LSD_Abilities_Schema::input($input);
        $id = (int) ($input['id'] ?? 0);
        return $id > 0 && get_post_type($id) === LSD_Base::PTYPE_LISTING && current_user_can('delete_post', $id);
    }

    /**
     * Read a listing through the public payload service.
     *
     * @return array|WP_Error
     */
    public function get($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        $visibility = LSD_AI_Visibility::instance();

        // Keep direct reads subject to the public feed setting.
        if (!$visibility->settings_service()->public_feed_available()) return new WP_Error('listdom_feed_unavailable', __('Public listing discovery is disabled.', 'listdom'));

        $payload = $visibility->payload_service()->public_listing_payload((int) ($input['id'] ?? 0));
        return $payload ?: new WP_Error('listdom_listing_not_found', __('Listing not found or not visible.', 'listdom'));
    }

    /**
     * Create a listing through the shared listing service.
     *
     * @return array|WP_Error
     */
    public function create($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        return (new LSD_Listings_Service())->create_managed($input, false);
    }

    /**
     * Update only the supplied listing fields.
     *
     * @return array|WP_Error
     */
    public function update($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        return (new LSD_Listings_Service())->patch((int) ($input['id'] ?? 0), $input);
    }

    /**
     * Move an authorized listing to the trash.
     *
     * @return array|WP_Error
     */
    public function trash($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        return (new LSD_Listings_Service())->trash((int) ($input['id'] ?? 0));
    }
}
