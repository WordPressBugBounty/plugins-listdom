<?php

class LSD_AI_Visibility_Payload extends LSD_Base
{
    protected LSD_AI_Visibility $owner;
    protected LSD_AI_Visibility_Settings $settings;
    protected array $details_page_options_cache = [];

    /**
     * Initialize service dependencies.
     * @param LSD_AI_Visibility $owner
     * @param LSD_AI_Visibility_Settings $settings
     * @return void
     */
    public function __construct(LSD_AI_Visibility $owner, LSD_AI_Visibility_Settings $settings)
    {
        // Dependencies
        $this->owner = $owner;
        $this->settings = $settings;
    }

    /**
     * Build public listing payload.
     * @param int $listing_id
     * @return array
     */
    public function public_listing_payload(int $listing_id): array
    {
        // Listing Post
        $post = get_post($listing_id);
        if (!$this->is_public_listing($post)) return [];

        $entity = new LSD_Entity_Listing($post);
        $payload = $this->listing_base_payload($post, $entity);

        $this->append_listing_fields($payload, $post, $entity);
        $this->append_listing_addon_data($payload, $post, $entity);
        $this->avoid_undated_event_payload_type($payload);

        return (array) apply_filters('lsd_ai_visibility_public_listing', $payload, $post->ID, $entity, $this->owner);
    }

    /**
     * Build base listing payload.
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return array
     */
    protected function listing_base_payload(WP_Post $post, LSD_Entity_Listing $entity): array
    {
        // Published Dates
        $published_gmt = $post->post_date_gmt !== '0000-00-00 00:00:00' ? $post->post_date_gmt : '';
        $modified_gmt = $post->post_modified_gmt !== '0000-00-00 00:00:00' ? $post->post_modified_gmt : '';

        return [
            'id' => $post->ID,
            'type' => $this->schema_type($entity),
            'title' => get_the_title($post),
            'url' => get_permalink($post),
            'published_at' => $this->format_feed_timestamp($published_gmt ?: $post->post_date, $published_gmt !== ''),
            'updated_at' => $this->format_feed_timestamp($modified_gmt ?: $post->post_modified, $modified_gmt !== ''),
        ];
    }

    /**
     * Append core listing fields.
     * @param array &$payload
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function append_listing_fields(array &$payload, WP_Post $post, LSD_Entity_Listing $entity): void
    {
        // Field Groups
        $this->append_content_fields($payload, $post);
        $this->append_location_fields($payload, $post);
        $this->append_taxonomy_fields($payload, $post, $entity);
        $this->append_media_fields($payload, $post);
    }

    /**
     * Append visible content fields.
     * @param array &$payload
     * @param WP_Post $post
     * @return void
     */
    protected function append_content_fields(array &$payload, WP_Post $post): void
    {
        // Content Visibility
        $content_enabled = $this->listing_element_enabled($post->ID, 'content');
        $excerpt_enabled = $this->listing_element_enabled($post->ID, 'excerpt');

        $excerpt = $excerpt_enabled ? $this->listing_excerpt($post) : '';

        if ($this->settings->field_enabled('description') && ($content_enabled || $excerpt_enabled))
        {
            $description = '';

            if ($content_enabled) $description = $this->listing_description($post);

            if ($description === '' && $excerpt_enabled) $description = $excerpt;

            if ($description !== '') $payload['description'] = $description;
        }

        if ($this->settings->field_enabled('excerpt') && $excerpt_enabled && $excerpt !== '') $payload['excerpt'] = $excerpt;

        if ($this->settings->field_enabled('faqs'))
        {
            $faqs = $this->public_faq_payload($post->ID);
            if (count($faqs)) $payload['faqs'] = $faqs;
        }
    }

    /**
     * Append visible location and contact fields.
     * @param array &$payload
     * @param WP_Post $post
     * @return void
     */
    protected function append_location_fields(array &$payload, WP_Post $post): void
    {
        // Address
        if ($this->settings->field_enabled('address') && $this->listing_element_enabled($post->ID, 'address'))
        {
            $address = get_post_meta($post->ID, 'lsd_address', true);
            if (is_string($address) && trim($address) !== '') $payload['address'] = trim($address);
        }

        if ($this->settings->field_enabled('coordinates') && $this->listing_element_enabled($post->ID, 'map'))
        {
            $latitude = get_post_meta($post->ID, 'lsd_latitude', true);
            $longitude = get_post_meta($post->ID, 'lsd_longitude', true);
            $coordinates = $this->coordinates($latitude, $longitude);

            if (count($coordinates)) $payload['coordinates'] = $coordinates;
        }

        if ($this->settings->field_enabled('contact'))
        {
            $contact_visibility = $this->listing_contact_visibility($post->ID);
            $contact_enabled = !array_key_exists('enabled', $contact_visibility) || !empty($contact_visibility['enabled']);
            $contact = [];
            $email = get_post_meta($post->ID, 'lsd_email', true);
            $phone = get_post_meta($post->ID, 'lsd_phone', true);
            $website = get_post_meta($post->ID, 'lsd_website', true);
            $contact_address = get_post_meta($post->ID, 'lsd_contact_address', true);

            if ($contact_enabled && !empty($contact_visibility['show_email']) && is_email($email)) $contact['email'] = sanitize_email($email);
            if ($contact_enabled && !empty($contact_visibility['show_phone']) && is_string($phone) && trim($phone) !== '') $contact['phone'] = trim($phone);
            if ($contact_enabled && !empty($contact_visibility['show_website']) && is_string($website) && trim($website) !== '')
            {
                $website = $this->absolute_url($website);
                if ($website !== '') $contact['website'] = $website;
            }
            if ($contact_enabled && !empty($contact_visibility['show_address']) && is_string($contact_address) && trim($contact_address) !== '') $contact['address'] = trim($contact_address);

            if (count($contact)) $payload['contact'] = $contact;
        }

        if ($this->settings->field_enabled('opening_hours') && ($this->listing_element_enabled($post->ID, 'availability') || $this->listing_availability_summary_visible($post->ID)))
        {
            $hours = $this->listing_element_enabled($post->ID, 'availability')
                ? $this->public_hours_payload($post->ID)
                : $this->public_hours_payload($post->ID, (int) current_time('N'));
            if (count($hours)) $payload['opening_hours'] = $hours;
        }
    }

    /**
     * Append visible taxonomy fields.
     * @param array &$payload
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function append_taxonomy_fields(array &$payload, WP_Post $post, LSD_Entity_Listing $entity): void
    {
        // Categories
        if ($this->settings->field_enabled('categories') && $this->listing_element_enabled($post->ID, 'categories'))
        {
            $category_terms = $this->visible_listing_category_terms($post->ID, $entity);
            $categories = $this->taxonomy_terms_payload($category_terms);

            if (count($categories))
            {
                $payload['categories'] = $categories;
                $primary_category = $entity->get_data_category();
                if ($primary_category instanceof WP_Term)
                {
                    $payload['primary_category'] = [
                        'id' => $primary_category->term_id,
                        'name' => $primary_category->name,
                        'slug' => $primary_category->slug,
                        'url' => $this->safe_url(get_term_link($primary_category)),
                    ];
                }
                else
                {
                    $payload['primary_category'] = $categories[0];
                }
            }
        }

        if ($this->settings->field_enabled('locations') && $this->listing_element_enabled($post->ID, 'locations'))
        {
            $locations = $this->listing_terms_payload($post->ID, LSD_Base::TAX_LOCATION);
            if (count($locations)) $payload['locations'] = $locations;
        }
    }

    /**
     * Append visible media and price fields.
     * @param array &$payload
     * @param WP_Post $post
     * @return void
     */
    protected function append_media_fields(array &$payload, WP_Post $post): void
    {
        // Price
        if ($this->settings->field_enabled('price') && $this->listing_element_enabled($post->ID, 'price'))
        {
            $price = $this->public_price_payload($post->ID);
            if (count($price)) $payload['price'] = $price;
        }

        if ($this->settings->field_enabled('images'))
        {
            $image_enabled = $this->listing_element_enabled($post->ID, 'image');
            $gallery_enabled = $this->listing_element_enabled($post->ID, 'gallery');
            $gallery_config = $gallery_enabled ? $this->listing_element_config($post->ID, 'gallery') : [];
            $images = ($image_enabled || $gallery_enabled) ? $this->listing_image_urls($post->ID, $image_enabled, $gallery_enabled, $gallery_config) : [];
            if (count($images)) $payload['images'] = $images;
        }
    }

    /**
     * Append visible add-on data.
     * @param array &$payload
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function append_listing_addon_data(array &$payload, WP_Post $post, LSD_Entity_Listing $entity): void
    {
        // Verified Status
        if ($this->settings->enabled('include_verified_status')) $payload['verified'] = $entity->is_claimed();

        if ($this->settings->enabled('include_reviews'))
        {
            $reviews = [];
            if ($this->listing_element_enabled($post->ID, 'discussion')) $reviews = apply_filters('lsd_ai_visibility_public_reviews', [], $post->ID, $entity, $this->owner);
            if ((!is_array($reviews) || !count($reviews)) && $this->listing_rating_summary_visible($post->ID)) $reviews = apply_filters('lsd_ai_visibility_public_review_summary', [], $post->ID, $entity, $this->owner);

            if (is_array($reviews) && count($reviews)) $payload['reviews'] = $reviews;
        }

        if ($this->settings->enabled('include_booking_summary') && $this->listing_element_enabled($post->ID, 'booking'))
        {
            $booking = apply_filters('lsd_ai_visibility_public_booking_summary', [], $post->ID, $entity, $this->owner);
            if (is_array($booking) && count($booking)) $payload['booking_summary'] = $booking;
        }
    }

    /**
     * Avoid undated Event payload types.
     * @param array &$payload
     * @return void
     */
    protected function avoid_undated_event_payload_type(array &$payload): void
    {
        // Event Type
        if (!isset($payload['type']) || !LSD_AI_Visibility_Schema_Normalizer::is_event_type($payload['type'])) return;

        $booking = isset($payload['booking_summary']) && is_array($payload['booking_summary']) ? $payload['booking_summary'] : [];
        if ($this->booking_summary_has_event_dates($booking)) return;

        $payload['type'] = 'https://schema.org/LocalBusiness';
    }

    /**
     * Check whether a booking summary has event dates.
     * @param array $booking
     * @return bool
     */
    protected function booking_summary_has_event_dates(array $booking): bool
    {
        // Direct Dates
        if (!empty($booking['start_date']) || !empty($booking['end_date'])) return true;

        if (!isset($booking['occurrences']) || !is_array($booking['occurrences'])) return false;

        foreach ($booking['occurrences'] as $occurrence)
        {
            if (!is_array($occurrence)) continue;
            if (!empty($occurrence['start_date']) || !empty($occurrence['end_date'])) return true;
        }

        return false;
    }

    /**
     * Build public term payload.
     * @param WP_Term $term
     * @param int|null $count
     * @return array
     */
    public function public_term_payload(WP_Term $term, ?int $count = null): array
    {
        // Term Payload
        $payload = [
            'id' => $term->term_id,
            'taxonomy' => $term->taxonomy,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => $this->safe_url(get_term_link($term)),
            'description' => trim(wp_strip_all_tags((string) term_description($term))),
            'count' => $count !== null ? max(0, $count) : (int) $term->count,
            'parent' => $term->parent ? (int) $term->parent : null,
        ];
        $payload = array_merge($payload, $this->taxonomy_schema_meta($term));

        return (array) apply_filters('lsd_ai_visibility_public_term', $payload, $term, $this->owner);
    }

    /**
     * Get visible listing category terms.
     * @param int $listing_id
     * @param LSD_Entity_Listing $entity
     * @return array
     */
    public function visible_listing_category_terms(int $listing_id, ?LSD_Entity_Listing $entity = null): array
    {
        // Listing Entity
        if (!$entity instanceof LSD_Entity_Listing)
        {
            $post = get_post($listing_id);

            if (!$post instanceof WP_Post) return [];

            $entity = new LSD_Entity_Listing($post);
        }

        $multiple = (bool) apply_filters('lsd_listing_display_multiple_categories', false);

        if (!$multiple)
        {
            $primary = $entity->get_data_category();

            return $primary instanceof WP_Term ? [$primary] : [];
        }

        $terms = wp_get_post_terms($listing_id, LSD_Base::TAX_CATEGORY);

        if (is_wp_error($terms) || !is_array($terms)) return [];

        return array_values(array_filter(
            $terms,
            static function ($term): bool
            {
                return $term instanceof WP_Term;
            }
        ));
    }

    /**
     * Build taxonomy term payloads.
     * @param array $terms
     * @return array
     */
    protected function taxonomy_terms_payload(array $terms): array
    {
        // Term Items
        $items = [];

        foreach ($terms as $term)
        {
            if (!$term instanceof WP_Term) continue;

            $items[] = array_merge([
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'url' => $this->safe_url(get_term_link($term)),
            ], $this->taxonomy_schema_meta($term));
        }

        return $items;
    }

    /**
     * Build listing taxonomy term payloads.
     * @param int $listing_id
     * @param string $taxonomy
     * @return array
     */
    public function listing_terms_payload(int $listing_id, string $taxonomy): array
    {
        // Listing Terms
        $terms = wp_get_post_terms($listing_id, $taxonomy);

        if (is_wp_error($terms) || !is_array($terms)) return [];

        return $this->taxonomy_terms_payload($terms);
    }

    /**
     * Build listing image URLs.
     * @param int $listing_id
     * @param bool $include_featured
     * @param bool $include_gallery
     * @param array $gallery_config
     * @return array
     */
    public function listing_image_urls(int $listing_id, bool $include_featured = true, bool $include_gallery = true, array $gallery_config = []): array
    {
        // Image IDs
        $image_ids = [];
        $include_gallery_thumbnail = $include_gallery && !empty($gallery_config['thumbnail']);

        if ($include_featured || $include_gallery_thumbnail)
        {
            $featured = get_post_thumbnail_id($listing_id);

            if ($featured) $image_ids[] = $featured;
        }

        if ($include_gallery)
        {
            $gallery = get_post_meta($listing_id, 'lsd_gallery', true);
            if (!is_array($gallery)) $gallery = [];

            $gallery = array_map('absint', $gallery);

            $gallery_style = isset($gallery_config['style']) && is_scalar($gallery_config['style']) ? sanitize_key((string) $gallery_config['style']) : 'list';
            $image_limit = isset($gallery_config['image_limit']) && is_scalar($gallery_config['image_limit']) ? (int) $gallery_config['image_limit'] : 0;
            if ($gallery_style === 'linear' && $image_limit > 0) $gallery = array_slice($gallery, 0, $include_gallery_thumbnail ? max(0, $image_limit - 1) : $image_limit);

            $image_ids = array_merge($image_ids, $gallery);
        }

        $image_ids = array_values(array_unique(array_filter($image_ids)));
        $images = [];

        foreach ($image_ids as $image_id)
        {
            $url = wp_get_attachment_image_url($image_id, 'full');
            if (!is_string($url) || trim($url) === '') continue;

            $url = esc_url_raw($url);

            if ($url !== '') $images[] = $url;
        }

        return $images;
    }

    /**
     * Convert content to plain text.
     * @param string $content
     * @return string
     */
    public function plain_text_content(string $content): string
    {
        // Text Cleanup
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $content = preg_replace('/\s+/u', ' ', $content);

        return trim((string) $content);
    }

    /**
     * Get visible price data.
     * @param int $listing_id
     * @return array
     */
    protected function visible_price_data(int $listing_id): array
    {
        // Pricing Component
        if (!LSD_Components::pricing()) return [];

        $minimum = get_post_meta($listing_id, 'lsd_price', true);

        // Frontend Price
        if (!$minimum || !is_scalar($minimum) || !is_numeric($minimum)) return [];

        $components = LSD_Options::price_components();

        $data = ['min' => $minimum];

        // Maximum Price
        if (!empty($components['max']))
        {
            $maximum = get_post_meta($listing_id, 'lsd_price_max', true);
            if (is_scalar($maximum) && trim((string) $maximum) !== '' && is_numeric($maximum)) $data['max'] = $maximum;
        }

        // Price Label
        if (!empty($components['after']))
        {
            $suffix = get_post_meta($listing_id, 'lsd_price_after', true);
            $suffix = is_scalar($suffix) ? trim((string) $suffix) : '';
            if ($suffix) $data['label'] = $suffix;
        }

        // Currency
        if (!empty($components['currency']))
        {
            // Listing Currency
            $currency = get_post_meta($listing_id, 'lsd_currency', true);
            $currency = is_scalar($currency) ? trim((string) $currency) : '';
        }
        else
        {
            $currency = LSD_Options::currency();
            $currency = is_scalar($currency) ? trim((string) $currency) : '';
        }

        if ($currency !== '') $data['currency'] = $currency;

        return $data;
    }

    /**
     * Build public price payload.
     * @param int $listing_id
     * @return array
     */
    public function public_price_payload(int $listing_id): array
    {
        // Price Data
        $price = $this->visible_price_data($listing_id);

        if (!count($price)) return [];

        $payload = ['min' => $price['min'],];

        // Maximum Price
        if (array_key_exists('max', $price)) $payload['max'] = $price['max'];

        // Label
        if (isset($price['label'])) $payload['label'] = $price['label'];

        // Currency
        if (isset($price['currency'])) $payload['currency'] = $price['currency'];

        $display = $this->price_range($listing_id);

        if ($display !== '') $payload['display'] = $display;

        return $payload;
    }

    /**
     * Build public opening hours payload.
     * @param int $listing_id
     * @param int|null $only_day
     * @return array
     */
    public function public_hours_payload(int $listing_id, ?int $only_day = null): array
    {
        // Availability
        $availability = LSD_API_Resources_Availability::get($listing_id);
        if (!is_array($availability) || !count($availability)) return [];

        $hours = [];
        foreach ($availability as $daycode => $day)
        {
            if (!is_array($day)) continue;
            if ($only_day !== null && (int) $daycode !== $only_day) continue;

            $day_hours = isset($day['hours']) && is_scalar($day['hours']) ? trim((string) $day['hours']) : '';
            $off = !empty($day['off']);

            if (!$off && $day_hours === '') continue;

            $hours[] = [
                'day' => isset($day['day']) && is_scalar($day['day']) ? (string) $day['day'] : '',
                'hours' => $day_hours,
                'off' => $off,
            ];
        }

        return $hours;
    }

    /**
     * Build public FAQ payload.
     * @param int $listing_id
     * @return array
     */
    public function public_faq_payload(int $listing_id): array
    {
        // FAQ Config
        $faq_config = $this->listing_element_config($listing_id, 'faq');
        if (empty($faq_config['enabled'])) return [];

        $element = new LSD_Element_Faq();
        $count = isset($faq_config['count']) && is_scalar($faq_config['count']) ? (int) $faq_config['count'] : 0;
        $faqs = $element->items($listing_id, $count);
        if (!is_array($faqs) || !count($faqs)) return [];

        $items = [];
        foreach ($faqs as $faq)
        {
            if (!is_array($faq)) continue;

            $question = isset($faq['question']) ? trim(wp_strip_all_tags((string) $faq['question'])) : '';
            $answer = isset($faq['answer']) ? trim(wp_strip_all_tags((string) $faq['answer'])) : '';
            if ($question === '' || $answer === '') continue;

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }

    /**
     * Return safe URL.
     * @param mixed $url
     * @return string
     */
    public function safe_url($url): string
    {
        // Safe URL
        if (is_wp_error($url)) return '';
        if (!is_scalar($url)) return '';

        return esc_url_raw((string) $url);
    }

    /**
     * Return absolute URL.
     * @param mixed $url
     * @return string
     */
    public function absolute_url($url): string
    {
        // Absolute URL
        if (!is_scalar($url)) return '';

        $url = trim((string) $url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return '';

        return esc_url_raw($url);
    }

    /**
     * Normalize coordinates.
     * @param mixed $latitude
     * @param mixed $longitude
     * @return array
     */
    public function coordinates($latitude, $longitude): array
    {
        // Coordinates
        if (!is_scalar($latitude) || !is_scalar($longitude)) return [];
        if (!is_numeric($latitude) || !is_numeric($longitude)) return [];

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if (!is_finite($latitude) || !is_finite($longitude)) return [];
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) return [];

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Build a taxonomy query clause.
     * @param string $taxonomy
     * @param mixed $value
     * @return array
     */
    public function term_query(string $taxonomy, $value): array
    {
        // Terms
        $field = 'slug';
        $terms = [];

        if (is_array($value) && isset($value['terms']))
        {
            $field = isset($value['field']) && $value['field'] === 'term_id' ? 'term_id' : 'slug';
            $value = $value['terms'];
        }

        if ($field === 'term_id')
        {
            $value = is_array($value) ? $value : [$value];

            foreach ($value as $term)
            {
                if (!is_scalar($term)) continue;

                $term_id = absint($term);
                if ($term_id > 0) $terms[] = $term_id;
            }
        }
        else if (is_array($value))
        {
            foreach ($value as $term)
            {
                if (!is_scalar($term)) continue;

                $term = trim(sanitize_text_field((string) $term));
                if ($term === '') continue;

                $terms[] = $term;
            }
        }
        else if (is_scalar($value))
        {
            $term = trim(sanitize_text_field((string) $value));
            if ($term !== '') $terms[] = $term;
        }

        return [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms,
        ];
    }

    /**
     * Get listing contact visibility settings.
     * @param int $listing_id
     * @return array
     */
    protected function listing_contact_visibility(int $listing_id): array
    {
        // Contact Defaults
        $visibility = self::parse_args($this->listing_element_config($listing_id, 'contact'), [
            'enabled' => 0,
            'show_email' => 1,
            'show_phone' => 1,
            'show_website' => 1,
            'show_address' => 1,
        ]);

        foreach (['enabled', 'show_email', 'show_phone', 'show_website', 'show_address'] as $key)
        {
            $visibility[$key] = !empty($visibility[$key]) ? 1 : 0;
        }

        return $visibility;
    }

    /**
     * Check whether a listing element is enabled.
     * @param int $listing_id
     * @param string $key
     * @return bool
     */
    public function listing_element_enabled(int $listing_id, string $key): bool
    {
        // Element Config
        $config = $this->listing_element_config($listing_id, $key);

        return !empty($config['enabled']);
    }

    /**
     * Check whether the header availability summary is visible.
     * @param int $listing_id
     * @return bool
     */
    public function listing_availability_summary_visible(int $listing_id): bool
    {
        // Listing Style
        if (!LSD_Components::work_hours()) return false;
        if (!in_array(LSD_PTypes_Listing_Single::get_listing_style($listing_id), ['style2', 'style3', 'style4'], true)) return false;

        $availability = get_post_meta($listing_id, 'lsd_ava', true);
        if (!is_array($availability)) return false;

        $today = $availability[(int) current_time('N')] ?? [];
        if (!is_array($today)) return false;

        return !empty($today['off']) || (isset($today['hours']) && is_scalar($today['hours']) && trim((string) $today['hours']) !== '');
    }

    /**
     * Check whether the header rating summary is visible.
     * @param int $listing_id
     * @return bool
     */
    public function listing_rating_summary_visible(int $listing_id): bool
    {
        // Listing Style
        if (!in_array(LSD_PTypes_Listing_Single::get_listing_style($listing_id), ['style2', 'style3', 'style4'], true)) return false;

        $rating = get_post_meta($listing_id, 'lsd_rate', true);

        return is_numeric($rating) && (float) $rating > 0;
    }

    /**
     * Get listing element configuration.
     * @param int $listing_id
     * @param string $key
     * @return array
     */
    public function listing_element_config(int $listing_id, string $key): array
    {
        // Details Options
        $settings = $this->listing_details_page_options($listing_id);

        if (!isset($settings['elements'][$key]) || !is_array($settings['elements'][$key])) return ['enabled' => 0,];

        $config = $settings['elements'][$key];

        $listing_elements = LSD_PTypes_Listing_Single::get_listing_elements($listing_id);
        $has_listing_elements = count($listing_elements) > 0;

        $style = LSD_PTypes_Listing_Single::get_listing_style($listing_id);

        if ($style === 'style1') $config['enabled'] = $has_listing_elements ? (!empty($config['enabled']) ? 1 : 0) : (strpos(LSD_Options::details_page_pattern(), '{' . $key . '}') !== false ? 1 : 0);
        else if ($style === 'dynamic') $config['enabled'] = !empty($config['enabled']) && $this->dynamic_builder_has_element($settings, $key) ? 1 : 0;
        else if (!in_array($style, ['style2', 'style3', 'style4', 'dynamic'], true)) $config['enabled'] = 0;

        return $config;
    }

    /**
     * Get filtered listing details page options.
     * @param int $listing_id
     * @return array
     */
    protected function listing_details_page_options(int $listing_id): array
    {
        // Cached Options
        if (isset($this->details_page_options_cache[$listing_id])) return $this->details_page_options_cache[$listing_id];

        $settings = LSD_Options::details_page();
        $listing_style = LSD_PTypes_Listing_Single::get_listing_style($listing_id);
        $listing_elements = LSD_PTypes_Listing_Single::get_listing_elements($listing_id);

        if (count($listing_elements))
        {
            foreach ($listing_elements as $key => $element)
            {
                if (is_array($element) && isset($settings['elements'][$key]) && is_array($settings['elements'][$key]))
                {
                    $settings['elements'][$key] = self::parse_args($element, $settings['elements'][$key]);
                }
            }
        }

        $settings['general']['style'] = $listing_style;

        $post = get_post($listing_id);
        if ($post instanceof WP_Post) $settings = apply_filters('lsd_details_page_options', $settings, $post);

        $this->details_page_options_cache[$listing_id] = is_array($settings) ? $settings : [];
        return $this->details_page_options_cache[$listing_id];
    }

    /**
     * Check whether dynamic builder contains an element.
     * @param array $settings
     * @param string $key
     * @return bool
     */
    protected function dynamic_builder_has_element(array $settings, string $key): bool
    {
        // Builder Sections
        $builder = isset($settings['builder']) && is_array($settings['builder']) ? $settings['builder'] : [];

        foreach ($builder as $section)
        {
            if (!is_array($section) || !isset($section['elements']) || !is_array($section['elements'])) continue;

            if (!empty($section['elements'][$key]['enabled'])) return true;
        }

        return false;
    }

    /**
     * Format a feed timestamp.
     * @param string $date
     * @param bool $utc
     * @return string
     */
    protected function format_feed_timestamp(string $date, bool $utc = false): string
    {
        // Timestamp
        if ($date === '') return '';

        $timezone = $utc ? new DateTimeZone('UTC') : wp_timezone();
        $datetime = date_create_immutable_from_format('Y-m-d H:i:s', $date, $timezone);

        if ($datetime instanceof DateTimeImmutable) return $datetime->format('c');

        return mysql2date('c', $date, false);
    }

    /**
     * Check whether a listing is public.
     * @param mixed $post
     * @return bool
     */
    public function is_public_listing($post): bool
    {
        // Listing Status
        if (!($post instanceof WP_Post)) return false;
        if ($post->post_type !== LSD_Base::PTYPE_LISTING) return false;
        if ($post->post_status !== 'publish') return false;
        if ($post->post_password !== '') return false;
        if (post_password_required($post)) return false;

        return (bool) apply_filters('lsd_ai_visibility_is_public_listing', true, $post, $this->owner);
    }

    /**
     * Build taxonomy schema metadata.
     * @param WP_Term $term
     * @return array
     */
    protected function taxonomy_schema_meta(WP_Term $term): array
    {
        // Schema Meta
        $meta = [];

        if (in_array($term->taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_FEATURE], true))
        {
            $schema_type = LSD_AI_Visibility_Schema_Normalizer::schema_type(get_term_meta($term->term_id, 'lsd_schema', true));
            if ($schema_type !== '') $meta['schema_type'] = $schema_type;
        }

        if ($term->taxonomy === LSD_Base::TAX_FEATURE)
        {
            $schema_property = LSD_AI_Visibility_Schema_Normalizer::schema_property(get_term_meta($term->term_id, 'lsd_itemprop', true));
            if ($schema_property !== '') $meta['schema_property'] = $schema_property;
        }

        return $meta;
    }

    /**
     * Resolve listing schema type.
     * @param LSD_Entity_Listing $entity
     * @return string
     */
    public function schema_type(LSD_Entity_Listing $entity): string
    {
        // Primary Category
        $category = $entity->get_data_category();
        $type = $category instanceof WP_Term ? get_term_meta($category->term_id, 'lsd_schema', true) : '';

        $type = LSD_AI_Visibility_Schema_Normalizer::schema_type($type);
        if ($type !== '') return $type;

        return 'https://schema.org/LocalBusiness';
    }

    /**
     * Build listing excerpt text.
     * @param WP_Post $post
     * @return string
     */
    public function listing_excerpt(WP_Post $post): string
    {
        // Excerpt
        $excerpt = get_the_excerpt($post);

        if (!is_string($excerpt)) return '';

        return $this->plain_text_content($excerpt);
    }

    /**
     * Build listing description text.
     * @param WP_Post $post
     * @return string
     */
    public function listing_description(WP_Post $post): string
    {
        // Description
        return $this->plain_text_content((string) $post->post_content);
    }

    /**
     * Build listing price range text.
     * @param int $listing_id
     * @return string
     */
    public function price_range(int $listing_id): string
    {
        // Price Data
        $price = $this->visible_price_data($listing_id);

        if (!count($price)) return '';

        $currency = $price['currency'] ?? '';
        $parts = [];

        $minimum = $this->plain_text_content($this->render_price($price['min'], $currency, false));

        if ($minimum !== '') $parts[] = $minimum;

        if (array_key_exists('max', $price))
        {
            $maximum = $this->plain_text_content($this->render_price($price['max'], $currency, false));

            if ($maximum !== '') $parts[] = $maximum;
        }

        $range = implode(' - ', $parts);

        if ($range !== '' && isset($price['label'])) $range .= ' / ' . $price['label'];

        return $range;
    }
}
