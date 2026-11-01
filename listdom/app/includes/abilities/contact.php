<?php

class LSD_Abilities_Contact
{
    /** Register the listing owner contact ability. */
    public function register(): void
    {
        // Listing Contact Request
        LSD_Abilities_Schema::register(
            'listdom/contact-listing',
            __('Contact a listing owner', 'listdom'),
            __('Send a validated contact request for a public listing. Requires a signed-in user and limits repeated requests.', 'listdom'),
            LSD_Abilities_Schema::object([
                'id' => ['type' => 'integer', 'minimum' => 1],
                'name' => ['type' => 'string', 'maxLength' => 120],
                'email' => ['type' => 'string', 'format' => 'email'],
                'phone' => ['type' => 'string', 'maxLength' => 50],
                'message' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 4000],
            ], ['id', 'email', 'message']),
            LSD_Abilities_Schema::object(['sent' => ['type' => 'boolean']], ['sent']),
            [$this, 'permitted'],
            [$this, 'contact'],
            false
        );
    }

    /** Require a signed-in user with basic read access. */
    public function permitted(): bool
    {
        return get_current_user_id() > 0 && current_user_can('read');
    }

    /**
     * Send a rate-limited request to a visible listing owner.
     *
     * @return array|WP_Error
     */
    public function contact($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        $id = (int) ($input['id'] ?? 0);
        $visibility = LSD_AI_Visibility::instance();

        // Validate visibility before contacting the listing owner.
        if (!$visibility->payload_service()->is_public_listing(get_post($id))) return new WP_Error('listdom_listing_not_found', __('Listing not found or not visible.', 'listdom'));

        $limit_key = 'lsd_ability_contact_' . get_current_user_id() . '_' . $id;
        if (get_transient($limit_key)) return new WP_Error('listdom_contact_rate_limit', __('Please wait before contacting this listing again.', 'listdom'));

        $result = (new LSD_Listings_Service())->contact($id, $input);
        if (!is_wp_error($result)) set_transient($limit_key, 1, MINUTE_IN_SECONDS);
        return $result;
    }
}
