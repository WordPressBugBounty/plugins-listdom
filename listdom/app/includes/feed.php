<?php

class LSD_Feed extends LSD_Base
{
    private const DEFAULT_FEED_NAME = 'listdom-listings';

    public function init()
    {
        add_action('init', [$this, 'register']);
    }

    public static function feed_name(): string
    {
        $settings = LSD_Options::settings();

        $feed_name = $settings['rss_slug'] ?? self::DEFAULT_FEED_NAME;
        $feed_name = sanitize_title($feed_name);
        if ($feed_name === '') $feed_name = self::DEFAULT_FEED_NAME;

        return apply_filters('lsd_listings_feed_name', $feed_name);
    }

    public static function feed_url(): string
    {
        return get_feed_link(self::feed_name());
    }

    public function register()
    {
        if (!$this->is_enabled()) return;

        add_feed(self::feed_name(), [$this, 'render']);
        add_action('pre_get_posts', [$this, 'pre_get_posts']);
        add_action('wp_head', [$this, 'head_link']);
        add_filter('the_content_feed', [$this, 'filter_content'], 10, 2);
        add_filter('the_excerpt_rss', [$this, 'filter_excerpt']);
    }

    private function is_enabled(): bool
    {
        $settings = LSD_Options::settings();

        return !isset($settings['rss_status']) || $settings['rss_status'];
    }

    public function render()
    {
        if (!$this->is_enabled())
        {
            status_header(404);
            exit;
        }

        if (!$this->matches_preferred_path())
        {
            status_header(404);
            exit;
        }

        do_feed_rss2(false);
    }

    public function pre_get_posts($query)
    {
        if (!$this->is_enabled()) return;
        if (!($query instanceof WP_Query)) return;
        if (is_admin() || !$query->is_main_query() || !$query->is_feed()) return;

        $feed = $query->get('feed');
        if ($feed !== self::feed_name()) return;

        $query->set('post_type', LSD_Base::PTYPE_LISTING);
        $query->set('post_status', 'publish');
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
        $query->set('ignore_sticky_posts', 1);

        $per_page = absint(get_option('posts_per_rss'));
        $per_page = (int) apply_filters('lsd_listings_feed_posts_per_page', $per_page);
        if ($per_page <= 0) $per_page = absint(get_option('posts_per_page'));

        if ($per_page > 0)
        {
            $query->set('posts_per_rss', $per_page);
            $query->set('posts_per_page', $per_page);
        }
    }

    public function head_link()
    {
        if (!$this->is_enabled()) return;

        $url = self::feed_url();
        $title = sprintf(
            /* translators: 1: Site title. */
            esc_html__('%s Listings Feed', 'listdom'),
            get_bloginfo('name')
        );

        $title = apply_filters('lsd_listings_feed_headline', $title, $url);

        echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr($title) . '" href="' . esc_url($url) . '">' . "\n";
    }

    public function filter_content($content, $feed_type): string
    {
        if (!is_feed(self::feed_name())) return $content;

        return $this->append_details($content, true);
    }

    public function filter_excerpt($content): string
    {
        if (!is_feed(self::feed_name())) return $content;

        return $this->append_details($content, false);
    }

    private function append_details(string $content, bool $allow_html): string
    {
        global $post;
        if (!$post instanceof WP_Post || $post->post_type !== LSD_Base::PTYPE_LISTING) return $content;

        $content = trim($content);
        if ($content === '')
        {
            $excerpt = get_post_field('post_excerpt', $post->ID);
            if (is_string($excerpt) && trim($excerpt) !== '')
            {
                $excerpt = trim($excerpt);
                if ($allow_html) $content = '<p>' . esc_html($excerpt) . '</p>';
                else $content = wp_strip_all_tags($excerpt);
            }
        }

        $details = $allow_html ? $this->details_html($post) : $this->details_text($post);
        if ($details === '') return $content;

        if ($content === '') return $details;
        if ($allow_html) return $content . "\n" . $details;

        return $content . "\n\n" . $details;
    }

    private function matches_preferred_path(): bool
    {
        $feed_base = isset($GLOBALS['wp_rewrite']->feed_base) ? (string) $GLOBALS['wp_rewrite']->feed_base : 'feed';
        $feed_name = self::feed_name();

        // Support query-string based feeds to avoid breaking backwards compatibility.
        if (isset($_GET['feed']) && sanitize_title($_GET['feed']) === $feed_name) return true;

        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
        if (!is_string($request_uri)) return true;

        $request_uri = trim($request_uri, '/');
        if ($request_uri === '') return true;

        $segments = explode('/', $request_uri);
        $base_index = array_search($feed_base, $segments, true);
        if ($base_index === false) return false;

        return isset($segments[$base_index + 1]) && $segments[$base_index + 1] === $feed_name;
    }

    private function details_html(WP_Post $post): string
    {
        $details = $this->collect_details($post);
        if (!$details) return '';

        $lines = [];
        foreach ($details as $detail)
        {
            $value = $detail['value'];
            if (!empty($detail['is_url'])) $value = '<a href="' . esc_url($value) . '">' . esc_html($value) . '</a>';
            else $value = esc_html($value);

            $lines[] = '<p><strong>' . esc_html($detail['label']) . ':</strong> ' . $value . '</p>';
        }

        return implode("\n", $lines);
    }

    private function details_text(WP_Post $post): string
    {
        $details = $this->collect_details($post);
        if (!$details) return '';

        $lines = [];
        foreach ($details as $detail)
        {
            $value = $detail['value'];
            if (!empty($detail['is_url'])) $value = esc_url($value);
            else $value = sanitize_text_field($value);

            $label = sanitize_text_field($detail['label']);
            $lines[] = $label . ': ' . $value;
        }

        return implode("\n", $lines);
    }

    private function collect_details(WP_Post $post): array
    {
        $details = [];
        $include_sensitive_details = $this->include_sensitive_details($post);

        $address = get_post_meta($post->ID, 'lsd_address', true);
        if ($include_sensitive_details && is_string($address) && trim($address) !== '')
        {
            $address_value = sanitize_text_field($address);
            if ($address_value !== '')
            {
                $details[] = [
                    'label' => esc_html__('Address', 'listdom'),
                    'value' => $address_value,
                ];
            }
        }

        if (LSD_Components::pricing())
        {
            $min_price = get_post_meta($post->ID, 'lsd_price', true);
            if ($min_price !== '' && $min_price !== null)
            {
                $currency = get_post_meta($post->ID, 'lsd_currency', true);
                if (!is_string($currency) || trim($currency) === '') $currency = LSD_Options::currency();

                $formatter = new LSD_Base();
                $price_text = $formatter->render_price($min_price, $currency);

                $max_price = get_post_meta($post->ID, 'lsd_price_max', true);
                if (is_string($max_price) && trim($max_price) !== '')
                {
                    $price_text .= ' - ' . $formatter->render_price($max_price, $currency);
                }

                $price_after = get_post_meta($post->ID, 'lsd_price_after', true);
                if (is_string($price_after) && trim($price_after) !== '')
                {
                    $price_text .= ' / ' . $price_after;
                }

                $details[] = [
                    'label' => esc_html__('Price', 'listdom'),
                    'value' => wp_strip_all_tags($price_text),
                ];
            }
        }

        $categories = get_the_terms($post, LSD_Base::TAX_CATEGORY);
        if ($categories && !is_wp_error($categories))
        {
            $names = array_filter(array_map(static function ($term)
            {
                return isset($term->name) ? sanitize_text_field($term->name) : '';
            }, $categories));

            if ($names)
            {
                $details[] = [
                    'label' => esc_html__('Categories', 'listdom'),
                    'value' => implode(', ', $names),
                ];
            }
        }

        $phone = get_post_meta($post->ID, 'lsd_phone', true);
        if ($include_sensitive_details && is_string($phone) && trim($phone) !== '')
        {
            $phone_value = sanitize_text_field($phone);
            if ($phone_value !== '')
            {
                $details[] = [
                    'label' => esc_html__('Phone', 'listdom'),
                    'value' => $phone_value,
                ];
            }
        }

        $email = get_post_meta($post->ID, 'lsd_email', true);
        if ($include_sensitive_details && is_string($email) && trim($email) !== '')
        {
            $email_value = sanitize_email($email);
            if ($email_value !== '')
            {
                $details[] = [
                    'label' => esc_html__('Email', 'listdom'),
                    'value' => $email_value,
                ];
            }
        }

        $website = get_post_meta($post->ID, 'lsd_website', true);
        if ($include_sensitive_details && is_string($website) && trim($website) !== '')
        {
            $website_value = esc_url_raw($website);
            if ($website_value !== '')
            {
                $details[] = [
                    'label' => esc_html__('Website', 'listdom'),
                    'value' => $website_value,
                    'is_url' => true,
                ];
            }
        }

        return apply_filters('lsd_listings_feed_details', $details, $post);
    }

    private function include_sensitive_details(WP_Post $post): bool
    {
        $settings = LSD_Options::settings();
        $include = isset($settings['rss_include_sensitive_details']) && $settings['rss_include_sensitive_details'];

        return (bool) apply_filters('lsd_listings_feed_include_sensitive_details', $include, $post);
    }
}
