<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Import / Export class.
 */
class LSD_IX extends LSD_Base
{
    protected $db;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // DB Library
        $this->db = new LSD_db();
    }

    protected function data(): array
    {
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'posts_per_page' => '-1',
        ]);

        $data = [];
        foreach ($listings as $listing)
        {
            // Force to Array
            $listing = (array) $listing;

            // ID
            $listing_id = $listing['ID'];

            // Remove Useless Keys
            foreach ([
                 'ID', 'comment_count', 'comment_status', 'filter', 'guid',
                 'menu_order', 'ping_status', 'pinged', 'to_ping', 'post_content_filtered',
                 'post_parent', 'post_mime_type',
             ] as $key) unset($listing[$key]);

            $metas = $this->get_post_meta($listing_id);

            // Remove Useless Keys
            foreach ($metas as $key => $value)
            {
                if (in_array($key, [
                        '_edit_last', '_edit_lock', '_thumbnail_id',
                        'lsd_attributes', 'lsd_gallery',
                    ]) or strpos($key, 'lsd_attribute_') !== false) unset($metas[$key]);
            }

            // Meta Values
            $listing['meta'] = $metas;

            // Taxonomies
            $listing['taxonomies'] = $this->get_taxonomies($listing_id);

            // Gallery
            $listing['gallery'] = $this->get_gallery($listing_id);

            // Featured Image
            $listing['image'] = get_the_post_thumbnail_url($listing_id, 'full');

            // Attributes
            $listing['attributes'] = $this->get_attributes($listing_id);

            $data[] = $listing;
        }

        return $data;
    }

    public function import($file)
    {
        // Unlimited Time Needed!
        set_time_limit(0);

        // Content to Import
        $content = LSD_File::read($file);

        $ex = explode('.', $file);
        $extension = strtolower(end($ex));

        switch ($extension)
        {
            case 'json':

                return $this->import_json($content);

            default:
                return false;
        }
    }

    public function import_json($JSON): array
    {
        $listings = json_decode($JSON, true);
        return $this->collection($listings);
    }

    public function collection($listings): array
    {
        $ids = [];
        foreach ($listings as $listing)
        {
            $ids[] = $this->save($listing);
        }

        do_action('lsd_import_finished');

        return $ids;
    }

    public function save($listing)
    {
        $post = [
            'post_title' => $listing['post_title'],
            'post_content' => $listing['post_content'] ?? '',
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $listing['post_status'] ?? 'publish',
            'post_date' => isset($listing['post_date']) ? date('Y-m-d', strtotime($listing['post_date'])) : null,
            'post_password' => $listing['post_password'] ?? '',
        ];

        // Don't Duplicate the Listing by Unique ID
        if (isset($listing['unique_id']) && $listing['unique_id'])
        {
            $db = new LSD_db();
            $exists = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='" . esc_sql($listing['unique_id']) . "' AND `meta_key`='lsd_sys_unique_id'", 'loadResult');

            // Perform Duplicate Detection with ID or Not.
            $id_duplicate_detection = apply_filters('lsd_ix_id_duplicate_detection', false);

            // Detect Duplicate using ID
            if (!$exists && $id_duplicate_detection)
            {
                $p = get_post($listing['unique_id']);
                if ($p && is_object($p) && isset($p->ID) && $p->post_type === LSD_Base::PTYPE_LISTING) $exists = $p->ID;
            }
        }
        // Don't Duplicate the Listing by Title and Content
        else
        {
            $exists = post_exists($listing['post_title'], $listing['post_content'] ?? '', '', LSD_Base::PTYPE_LISTING);
        }

        if ($exists) $post['ID'] = $exists;

        // Insert User
        if (isset($listing['post_author']) and trim($listing['post_author']) and is_email($listing['post_author']))
        {
            $email = sanitize_email($listing['post_author']);

            $exists = email_exists($email);
            if ($exists) $post['post_author'] = $exists;
            else
            {
                $user_id = register_new_user($email, $email);
                if (!is_wp_error($user_id)) $post['post_author'] = $user_id;
            }
        }

        // Add-ons and Third Party Applications
        $post = apply_filters('lsd_ix_before_upsert', $post, $listing);

        // Insert / Update Post
        $post_id = wp_insert_post($post);

        // Import Taxonomies
        $taxonomies = (isset($listing['taxonomies']) and is_array($listing['taxonomies'])) ? $listing['taxonomies'] : [];
        foreach ($taxonomies as $taxonomy => $terms)
        {
            $t = [];
            foreach ($terms as $term)
            {
                $exists = term_exists($term['name'], $taxonomy);

                if (is_array($exists) and isset($exists['term_id'])) $term_id = (int) $exists['term_id'];
                else
                {
                    // Create Term
                    $wpt = wp_insert_term($term['name'], $taxonomy, [
                        'description' => $term['description'] ?? '',
                        'parent' => $term['parent'] ?? 0,
                        'slug' => $term['slug'] ?? '',
                    ]);

                    // An Error Occurred
                    if (!is_array($wpt)) continue;

                    // Term ID
                    $term_id = (int) $wpt['term_id'];

                    // Import Term Meta
                    if (isset($term['meta']) and is_array($term['meta']) and count($term['meta']))
                    {
                        foreach ($term['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
                    }

                    // Import Image
                    if (isset($term['image']) and trim($term['image']))
                    {
                        $attachment_id = $this->attach($term['image']);
                        if ($attachment_id) update_term_meta($term_id, 'lsd_image', $attachment_id);
                    }
                }

                $t[] = $term_id;
            }

            wp_set_post_terms($post_id, $t, $taxonomy);
        }

        // Import Image
        if (isset($listing['image']) and trim($listing['image']))
        {
            $attachment_id = $this->attach(trim($listing['image']));
            if ($attachment_id) set_post_thumbnail($post_id, $attachment_id);
        }

        // Import Gallery
        $gallery = [];
        if (isset($listing['gallery']) and is_array($listing['gallery']) and count($listing['gallery']))
        {
            foreach ($listing['gallery'] as $image)
            {
                $attachment_id = $this->attach(trim($image));
                if ($attachment_id) $gallery[] = $attachment_id;
            }
        }

        // Import Attributes
        $attributes = [];
        if (isset($listing['attributes']) and is_array($listing['attributes']) and count($listing['attributes']))
        {
            foreach ($listing['attributes'] as $attribute)
            {
                $term = $attribute['term'] ?? [];
                if (!is_array($term) or !count($term)) continue;

                $exists = term_exists($term['name'], LSD_Base::TAX_ATTRIBUTE);

                if (is_array($exists) and isset($exists['term_id'])) $term_id = (int) $exists['term_id'];
                else
                {
                    // Create Term
                    $wpt = wp_insert_term($term['name'], LSD_Base::TAX_ATTRIBUTE, [
                        'description' => $term['description'] ?? '',
                        'parent' => $term['parent'] ?? 0,
                        'slug' => $term['slug'] ?? '',
                    ]);

                    // An Error Occurred
                    if (!is_array($wpt)) continue;

                    // Term ID
                    $term_id = (int) $wpt['term_id'];

                    // Import Term Meta
                    if (isset($term['meta']) and is_array($term['meta']) and count($term['meta']))
                    {
                        foreach ($term['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
                    }
                }

                $value = $attribute['value'] ?? '';

                // Add to Attributes
                $attributes[$term_id] = $value;
            }
        }

        // Metas
        $metas = isset($listing['meta']) && is_array($listing['meta']) ? $listing['meta'] : [];

        // Prepare Data
        $data = [
            'listing_category' => null,
            'object_type' => $metas['lsd_object_type'] ?? 'marker',
            'zoomlevel' => $metas['lsd_zoomlevel'] ?? 6,
            'latitude' => $metas['lsd_latitude'] ?? null,
            'longitude' => $metas['lsd_longitude'] ?? null,
            'address' => $metas['lsd_address'] ?? '',
            'shape_type' => $metas['lsd_shape_type'] ?? '',
            'shape_paths' => $metas['lsd_shape_paths'] ?? '',
            'shape_radius' => $metas['lsd_shape_radius'] ?? '',
            'attributes' => $attributes,
            'link' => $metas['lsd_link'] ?? '',
            'price' => $metas['lsd_price'] ?? 0,
            'price_max' => $metas['lsd_price_max'] ?? 0,
            'price_after' => $metas['lsd_price_after'] ?? '',
            'currency' => $metas['lsd_currency'] ?? 'USD',
            'ava' => $metas['lsd_ava'] ?? [],
            'email' => $metas['lsd_email'] ?? '',
            'phone' => $metas['lsd_phone'] ?? '',
            'website' => $metas['lsd_website'] ?? '',
            'contact_address' => $metas['lsd_contact_address'] ?? '',
            'remark' => $metas['lsd_remark'] ?? '',
            'displ' => $metas['lsd_displ'] ?? [],
            'gallery' => $gallery,
            'sc' => [], // Social Networks
        ];

        // Social Networks
        $SN = new LSD_Socials();

        $networks = LSD_Options::socials();
        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);

            // Social Network is Disabled or Data Not Available
            if (!$obj || !isset($metas['lsd_' . $obj->key()])) continue;

            // Save Social Network Data
            $data['sc'][$obj->key()] = $metas['lsd_' . $obj->key()];
        }

        if (isset($metas['lsd_guest_email']))
        {
            $data['guest_email'] = sanitize_email($metas['lsd_guest_email']);
            $data['guest_fullname'] = isset($metas['lsd_guest_fullname']) ? sanitize_email($metas['lsd_guest_fullname']) : '';
            $data['guest_message'] = $metas['lsd_guest_message'] ?? '';
        }

        $entity = new LSD_Entity_Listing($post_id);
        $entity->save($data, false);

        // Save the Unique ID
        if (isset($listing['unique_id']) and $listing['unique_id']) update_post_meta($post_id, 'lsd_sys_unique_id', $listing['unique_id']);

        // New Listing Imported
        do_action('lsd_listing_imported', $post_id, $listing);

        return $post_id;
    }

    public function get_attributes($post_id): array
    {
        $attributes = [];

        $values = get_post_meta($post_id, 'lsd_attributes', true);
        if (!is_array($values)) $values = [];

        foreach ($values as $id => $value)
        {
            $term = (array) get_term($id);

            // Remove Useless Keys
            foreach ([
                 'count', 'filter', 'term_group',
                 'term_id', 'term_taxonomy_id',
             ] as $key) unset($term[$key]);

            // Meta Values
            $term['meta'] = $this->get_term_meta($id);

            $attr = [
                'value' => $value,
                'term' => $term,
            ];

            $attributes[] = $attr;
        }

        return $attributes;
    }

    public function get_taxonomies($post_id): array
    {
        $taxonomies = [];

        foreach ([
             LSD_Base::TAX_CATEGORY,
             LSD_Base::TAX_LABEL,
             LSD_Base::TAX_LOCATION,
             LSD_Base::TAX_FEATURE,
             LSD_Base::TAX_TAG,
         ] as $taxonomy)
        {
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms and !is_wp_error($terms))
            {
                $t = [];
                foreach ($terms as $term)
                {
                    // Force to Array
                    $term = (array) $term;

                    // Term ID
                    $term_id = $term['term_id'];

                    // Remove Useless Keys
                    foreach ([
                         'count', 'filter',
                         'term_group', 'term_taxonomy_id',
                     ] as $key) unset($term[$key]);

                    // Metas
                    $metas = $this->get_term_meta($term_id);

                    // Image
                    if (isset($metas['lsd_image']))
                    {
                        $term['image'] = wp_get_attachment_url($metas['lsd_image']);
                        unset($metas['lsd_image']);
                    }

                    $term['meta'] = $metas;

                    $t[] = $term;
                }

                $taxonomies[$taxonomy] = $t;
            }
        }

        return $taxonomies;
    }

    public function get_gallery($post_id): array
    {
        $value = get_post_meta($post_id, 'lsd_gallery', true);
        if (!is_array($value)) $value = [];

        $gallery = [];
        foreach ($value as $attachment_id)
        {
            $image = wp_get_attachment_url($attachment_id);
            if (!$image) continue;

            $gallery[] = $image;
        }

        return $gallery;
    }

    public function attach($image)
    {
        // Already imported
        if ($attachment_id = LSD_Main::get_post_id_by_meta('lsd_imported_from', $image)) return $attachment_id;

        // Image is already exist in website
        if ($attachment_id = attachment_url_to_postid($image)) return $attachment_id;

        $buffer = LSD_File::download($image);
        if (!$buffer) return false;

        return $this->attach_by_buffer($buffer, basename($image), $image);
    }

    public function attach_by_buffer($buffer, $name, $url = null)
    {
        // Media Libraries
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $upload = wp_upload_bits($name, null, $buffer);

        $file = $upload['file'];
        $wp_filetype = wp_check_filetype($file);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => basename($file),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment_id = wp_insert_attachment($attachment, $file);
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        // Flag the attachment imported from URL
        update_post_meta($attachment_id, 'lsd_imported_from', $url);

        return $attachment_id;
    }
}
