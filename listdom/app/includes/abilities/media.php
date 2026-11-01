<?php

class LSD_Abilities_Media
{
    /** Register the editable image lookup. */
    public function register(): void
    {
        // Get Image
        LSD_Abilities_Schema::register(
            'listdom/get-image',
            __('Get a listing image', 'listdom'),
            __('Inspect an image attachment the current user may edit before assigning it to a listing.', 'listdom'),
            LSD_Abilities_Schema::object(['id' => ['type' => 'integer', 'minimum' => 1]], ['id']),
            LSD_Abilities_Schema::object([
                'id' => ['type' => 'integer'],
                'url' => ['type' => 'string'],
                'alt' => ['type' => 'string'],
                'width' => ['type' => 'integer'],
                'height' => ['type' => 'integer'],
            ], ['id', 'url', 'alt', 'width', 'height']),
            [$this, 'permitted'],
            [$this, 'get'],
            true
        );
    }

    /** Require edit access to the requested attachment. */
    public function permitted($input): bool
    {
        $input = LSD_Abilities_Schema::input($input);
        $id = (int) ($input['id'] ?? 0);
        return $id > 0 && current_user_can('edit_post', $id);
    }

    /**
     * Return usable image details for an editable attachment.
     *
     * @return array|WP_Error
     */
    public function get($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        $id = (int) ($input['id'] ?? 0);

        // Restrict the response to image attachments with usable metadata.
        if (!wp_attachment_is_image($id)) return new WP_Error('listdom_image_not_found', __('Image not found.', 'listdom'));

        $url = wp_get_attachment_url($id);
        $metadata = wp_get_attachment_metadata($id);
        if (!$url || !is_array($metadata)) return new WP_Error('listdom_image_not_found', __('Image not found.', 'listdom'));

        return [
            'id' => $id,
            'url' => $url,
            'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true),
            'width' => (int) ($metadata['width'] ?? 0),
            'height' => (int) ($metadata['height'] ?? 0),
        ];
    }
}
