<?php

class LSD_Main extends LSD_Base
{
    public function get_installed_db_version()
    {
        $installed_db_ver = get_option('lsd_db_version');
        if (trim($installed_db_ver) === '') $installed_db_ver = 0;

        return $installed_db_ver;
    }

    public function is_db_update_required(): bool
    {
        $installed_db_ver = $this->get_installed_db_version();
        $db = new LSD_db();

        return version_compare($installed_db_ver, LSD_Base::DB_VERSION, '<')
            || !$db->exists('lsd_data')
            || !$db->exists('lsd_jobs');
    }

    public function geopoint($address): array
    {
        $address = urlencode(apply_filters('lsd_geopoint_address', $address));

        $user_agent = 'listdom';
        if (isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']))
        {
            $user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
            if ($user_agent === '') $user_agent = 'listdom';
        }

        /**
         * OSM (OpenStreetMap)
         */

        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . $address;

        // Getting Geo Point
        $JSON = LSD_Main::download($url, [
            'timeout' => 10,
            'user-agent' => $user_agent,
            'sslverify' => false,
        ]);

        $data = json_decode($JSON, true);
        $place = $data[0] ?? null;

        if (isset($place['lat']) && $place['lat'] && isset($place['lon']) && $place['lon']) return [$place['lat'], $place['lon']];

        // Listdom Settings
        $settings = LSD_Options::settings();

        /**
         * Google Geocoding
         */

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address;

        // API Key
        if (isset($settings['google_geocoding_api_key']) && trim($settings['google_geocoding_api_key']) !== '') $url .= '&key=' . $settings['google_geocoding_api_key'];

        // Getting Geo Point
        $JSON = LSD_Main::download($url, [
            'timeout' => 10,
            'user-agent' => $user_agent,
            'sslverify' => false,
        ]);

        $data = json_decode($JSON, true);
        $geopoint = isset($data['results'][0]) ? $data['results'][0]['geometry']['location'] : null;

        if (isset($geopoint['lat']) && $geopoint['lat'] && isset($geopoint['lng']) && $geopoint['lng']) return [$geopoint['lat'], $geopoint['lng']];

        return [0, 0];
    }

    /**
     * Returns weekdays
     * @return array
     */
    public static function get_weekdays(): array
    {
        $week_start = LSD_Main::get_first_day_of_week();

        /**
         * Don't change it to translate-able strings
         */
        $raw = [
            ['day' => 'Sunday', 'code' => 7, 'label' => esc_html__('Sunday', 'listdom')],
            ['day' => 'Monday', 'code' => 1, 'label' => esc_html__('Monday', 'listdom')],
            ['day' => 'Tuesday', 'code' => 2, 'label' => esc_html__('Tuesday', 'listdom')],
            ['day' => 'Wednesday', 'code' => 3, 'label' => esc_html__('Wednesday', 'listdom')],
            ['day' => 'Thursday', 'code' => 4, 'label' => esc_html__('Thursday', 'listdom')],
            ['day' => 'Friday', 'code' => 5, 'label' => esc_html__('Friday', 'listdom')],
            ['day' => 'Saturday', 'code' => 6, 'label' => esc_html__('Saturday', 'listdom')],
        ];

        $labels = array_slice($raw, $week_start);
        $rest = array_slice($raw, 0, $week_start);

        foreach ($rest as $label) $labels[] = $label;
        return apply_filters('lsd_weekdays', $labels);
    }

    public static function get_first_day_of_week()
    {
        return get_option('start_of_week', 1);
    }

    public static function grecaptcha_field($class = ''): string
    {
        // Listdom Options
        $settings = LSD_Options::settings();

        // Recaptcha is not enabled!
        if (!isset($settings['grecaptcha_status']) || !$settings['grecaptcha_status']) return '';

        // Site Key
        $sitekey = isset($settings['grecaptcha_sitekey']) && trim($settings['grecaptcha_sitekey']) ? $settings['grecaptcha_sitekey'] : null;

        // Site key is empty!
        if (!$sitekey) return '';

        // Include JS Library
        $assets = new LSD_Assets();
        $assets->grecaptcha();

        return '<div class="g-recaptcha ' . sanitize_html_class($class) . '" data-sitekey="' . esc_attr($sitekey) . '"></div>';
    }

    public static function grecaptcha_check($g_recaptcha_response, $remote_ip = null): bool
    {
        // Listdom Options
        $settings = LSD_Options::settings();

        // Recaptcha is not enabled!
        if (!isset($settings['grecaptcha_status']) || !$settings['grecaptcha_status']) return true;

        // Secret Key
        $secretkey = isset($settings['grecaptcha_secretkey']) && trim($settings['grecaptcha_secretkey']) ? $settings['grecaptcha_secretkey'] : null;

        // Secret key is empty!
        if (!$secretkey) return false;

        // Get the IP
        if (is_null($remote_ip))
        {
            if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) $remote_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            else $remote_ip = '';
        }

        // Data
        $data = ['secret' => $secretkey, 'remoteip' => $remote_ip, 'response' => $g_recaptcha_response];

        // Request
        $request = '';
        foreach ($data as $key => $value) $request .= $key . '=' . urlencode(stripslashes($value)) . '&';

        // Validating the re-captcha
        $JSON = LSD_Main::download('https://www.google.com/recaptcha/api/siteverify?' . trim($request, '& '));
        $response = json_decode($JSON, true);

        if (isset($response['success']) && trim($response['success'])) return true;
        else return false;
    }

    public static function download($url, $args = []): string
    {
        return wp_remote_retrieve_body(wp_remote_get($url, $args));
    }

    public static function assign($post_id, $user_id)
    {
        // DB Library
        $db = new LSD_db();

        // Assign Listing
        return $db->q("UPDATE `#__posts` SET `post_author`=" . ((int) $user_id) . " WHERE `ID`=" . ((int) $post_id));
    }

    public function standardize_format($date, $from, $to = 'Y-m-d')
    {
        if (!trim($date)) return '';

        $date = str_replace('.', '-', $date);
        if ($from === 'dd/mm/yyyy')
        {
            $d = explode('/', $date);
            $date = $d[2] . '-' . $d[1] . '-' . $d[0];
        }

        return lsd_date($to, LSD_Base::strtotime($date));
    }

    public function jstophp_format($js_format = 'yyyy-mm-dd'): string
    {
        if ($js_format === 'dd-mm-yyyy') $php_format = 'd-m-Y';
        else if ($js_format === 'yyyy/mm/dd') $php_format = 'Y/m/d';
        else if ($js_format === 'dd/mm/yyyy') $php_format = 'd/m/Y';
        else if ($js_format === 'yyyy.mm.dd') $php_format = 'Y.m.d';
        else if ($js_format === 'dd.mm.yyyy') $php_format = 'd.m.Y';
        else $php_format = 'Y-m-d';

        return $php_format;
    }

    public static function get_name_parts($fullname): array
    {
        $ex = explode(' ', trim($fullname));

        // First Name
        $first_name = $ex[0];
        unset($ex[0]);

        // Last Name
        $last_name = implode(' ', $ex);

        return [$first_name, $last_name];
    }

    public function is_geo_request(): bool
    {
        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_search_form')) return false;

        $vars = array_merge($_GET, $_POST);

        $sf = isset($vars['sf']) && is_array($vars['sf']) ? $vars['sf'] : [];
        return isset($sf['min_latitude'], $sf['max_latitude'], $sf['min_longitude'], $sf['max_longitude']);
    }

    public static function get_post_id_by_meta(string $key, $value): ?int
    {
        $db = new LSD_db();
        return $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='" . esc_sql($key) . "' AND `meta_value`='" . esc_sql($value) . "'", 'loadResult');
    }

    public static function get_attributes()
    {
        $attributes = get_terms([
            'taxonomy' => LSD_Base::TAX_ATTRIBUTE,
            'hide_empty' => false,
            'meta_key' => 'lsd_index',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        ]);

        // Always Array
        return is_wp_error($attributes) ? [] : $attributes;
    }

    public static function get_attributes_details(): array
    {
        $terms = self::get_attributes();

        $attributes = [];
        foreach ($terms as $term)
        {
            $field_type = get_term_meta($term->term_id, 'lsd_field_type', true);
            $values_raw = get_term_meta($term->term_id, 'lsd_values', true);
            $icon = get_term_meta($term->term_id, 'lsd_icon', true);
            $required = get_term_meta($term->term_id, 'lsd_required', true);
            $editor = get_term_meta($term->term_id, 'lsd_editor', true);
            $link_label = get_term_meta($term->term_id, 'lsd_link_label', true);

            $values_array = is_string($values_raw)
                ? explode(',', $values_raw)
                : (is_array($values_raw) ? $values_raw : []);

            $values = array_combine($values_array, $values_array);

            $attributes[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'field_type' => $field_type,
                'values' => $values,
                'icon' => $icon,
                'required' => $required,
                'editor' => $editor,
                'link_label' => $link_label,
            ];
        }

        return $attributes;
    }

    public static function get_attr_id($id): int
    {
        if (!is_numeric($id))
        {
            $term = get_term_by('slug', $id, LSD_Base::TAX_ATTRIBUTE);
            return $term->term_id ?? 0;
        }

        return (int) $id;
    }

    public static function get_attr_slug($slug): string
    {
        if (is_numeric($slug))
        {
            $term = get_term($slug);
            return $term->slug ?? '';
        }

        return $slug;
    }

    public static function get_uncategorized_category_id(): int
    {
        $term = get_term_by('slug', 'uncategorized', LSD_Base::TAX_CATEGORY);
        return $term instanceof WP_Term ? $term->term_id : 0;
    }

    public static function allowed_statuses(): array
    {
        $default = [
            'publish',
            'trash',
            'pending',
            'draft',
            'future',
        ];

        $custom = array_keys((new LSD_Statuses())->statuses());

        return array_merge($default, $custom);
    }

    public function is_request(string $type): bool
    {
        switch ($type)
        {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
            default:
                return false;
        }
    }

    public static function update_geopoints(int $limit = 100)
    {
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'numberposts' => $limit,
        ]);

        // Listing Entity
        $entity = new LSD_Entity_Listing();

        foreach ($listings as $listing)
        {
            $lat = get_post_meta($listing->ID, 'lsd_latitude', true);
            $lng = get_post_meta($listing->ID, 'lsd_longitude', true);

            $entity->update_geopoint($listing->ID, $lat, $lng);
        }
    }
}
