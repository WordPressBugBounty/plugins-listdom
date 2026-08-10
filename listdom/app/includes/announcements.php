<?php

class LSD_Announcements extends LSD_Base
{
    public const OPTION_KEY = 'lsd_announcements';
    public const CRON_HOOK = 'lsd_announcements_refresh';
    public const STATE_ACTIVE = 'active';
    public const STATE_DISMISSED = 'dismissed';
    public const SOURCE_REMOTE = 'remote';
    public const SOURCE_LOCAL = 'local';

    protected const OPTION_VERSION = 1;
    protected const LOCK_KEY = 'lsd_announcements_refresh_lock';
    protected const LOCK_TTL = 300;

    protected static ?LSD_Announcements $instance = null;

    public static function instance(): LSD_Announcements
    {
        if (is_null(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    public function init(): void
    {
        $this->ensure_option();

        add_action(self::CRON_HOOK, [$this, 'refresh']);
        add_action('init', [$this, 'schedule']);
        add_action('admin_init', [$this, 'maybe_refresh']);
        add_action('wp_ajax_lsd_announcements_dismiss', [$this, 'ajax_dismiss']);
    }

    public function schedule(): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) wp_schedule_event(time(), 'daily', self::CRON_HOOK);
    }

    public function maybe_refresh(): bool
    {
        if (!is_admin() || !current_user_can('manage_options')) return false;

        $data = $this->option();
        $last_requested_at = (int) ($data['last_requested_at'] ?? 0);
        if ($last_requested_at > 0 && $last_requested_at + $this->refresh_interval() > $this->now()) return false;

        return $this->refresh();
    }

    public function refresh(bool $force = false): bool
    {
        if (!$force)
        {
            $data = $this->option();
            $last_requested_at = (int) ($data['last_requested_at'] ?? 0);
            if ($last_requested_at > 0 && $last_requested_at + $this->refresh_interval() > $this->now()) return false;
        }

        if (get_transient(self::LOCK_KEY)) return false;
        set_transient(self::LOCK_KEY, 1, self::LOCK_TTL);

        try
        {
            $data = $this->option();
            $data['last_requested_at'] = $this->now();
            $this->save($data);

            if (!class_exists(\Webilia\WP\Announcements::class)) return false;

            $client = new \Webilia\WP\Announcements($this->announcement_products());
            $params = apply_filters('lsd_announcements_remote_params', []);
            if (!is_array($params)) $params = [];

            $remote_items = $client->getAnnouncements($params);
            if (!is_array($remote_items)) return false;

            $existing_items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
            $items = [];

            foreach ($existing_items as $id => $item)
            {
                if (!is_array($item)) continue;
                if (($item['source'] ?? '') !== self::SOURCE_LOCAL) continue;

                $normalized = $this->normalize($item, self::SOURCE_LOCAL, (string) $id);
                if ($normalized) $items[$normalized['id']] = $normalized;
            }

            foreach ($remote_items as $remote_item)
            {
                if (!is_array($remote_item)) continue;

                $normalized = $this->normalize($remote_item, self::SOURCE_REMOTE);
                if (!$normalized) continue;

                $existing = $existing_items[$normalized['id']] ?? null;
                if (is_array($existing) && ($existing['state'] ?? '') === self::STATE_DISMISSED)
                {
                    $normalized['state'] = self::STATE_DISMISSED;
                }

                $items[$normalized['id']] = $normalized;
            }

            $data['items'] = $items;
            $data['last_success_at'] = $this->now();

            return $this->save($data);
        }
        finally
        {
            delete_transient(self::LOCK_KEY);
        }
    }

    public function active(): array
    {
        $items = $this->items();
        $now = $this->now();

        $active = array_values(array_filter($items, static function (array $item) use ($now): bool
        {
            if (($item['state'] ?? '') !== self::STATE_ACTIVE) return false;

            $starts_at = (int) ($item['created_at'] ?? 0);
            if ($starts_at > 0 && $starts_at > $now) return false;

            $expires_at = (int) ($item['expires_at'] ?? 0);
            return $expires_at <= 0 || $expires_at > $now;
        }));

        return $this->sort_by_created_at($active);
    }

    public function active_count(): int
    {
        return count($this->active());
    }

    public function dismissed(): array
    {
        $items = $this->items();
        $dismissed = array_values(array_filter($items, static function (array $item): bool
        {
            return ($item['state'] ?? '') === self::STATE_DISMISSED;
        }));

        return $this->sort_by_created_at($dismissed);
    }

    public function all(): array
    {
        return $this->sort_by_created_at(array_values($this->items()));
    }

    public function dismiss(string $id): bool
    {
        $id = $this->normalize_id($id);
        if ($id === '') return false;

        $data = $this->option();
        if (!isset($data['items'][$id]) || !is_array($data['items'][$id]))
        {
            $items = $this->items();
            if (!isset($items[$id])) return false;

            $data['items'][$id] = $items[$id];
        }

        $data['items'][$id]['state'] = self::STATE_DISMISSED;

        return $this->save($data);
    }

    public function ajax_dismiss(): void
    {
        if (!current_user_can('manage_options'))
        {
            wp_send_json_error(['message' => esc_html__('You do not have permission to dismiss announcements.', 'listdom')], 403);
        }

        $post = wp_unslash($_POST);
        $nonce = isset($post['_wpnonce']) ? sanitize_text_field($post['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'lsd_announcements'))
        {
            wp_send_json_error(['message' => esc_html__('Security nonce is not valid.', 'listdom')], 403);
        }

        $id = isset($post['id']) ? sanitize_text_field($post['id']) : '';
        if (!$this->dismiss($id))
        {
            wp_send_json_error(['message' => esc_html__('Announcement could not be dismissed.', 'listdom')], 400);
        }

        wp_send_json_success([
            'active_count' => $this->active_count(),
            'dismissed_count' => count($this->dismissed()),
        ]);
    }

    public function upsert_local(string $id, array $announcement): bool
    {
        $normalized = $this->normalize($announcement, self::SOURCE_LOCAL, $id);
        if (!$normalized) return false;

        $data = $this->option();
        $existing = $data['items'][$normalized['id']] ?? null;
        if (is_array($existing) && ($existing['state'] ?? '') === self::STATE_DISMISSED)
        {
            $normalized['state'] = self::STATE_DISMISSED;
        }

        $data['items'][$normalized['id']] = $normalized;

        return $this->save($data);
    }

    public function option(): array
    {
        $stored = get_option(self::OPTION_KEY, null);
        if (!is_array($stored)) return $this->defaults();

        $data = wp_parse_args($stored, $this->defaults());
        $data['version'] = self::OPTION_VERSION;
        $data['last_requested_at'] = (int) ($data['last_requested_at'] ?? 0);
        $data['last_success_at'] = (int) ($data['last_success_at'] ?? 0);
        $data['items'] = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        return $data;
    }

    protected function items(): array
    {
        $data = $this->option();
        $items = [];

        foreach ($data['items'] as $id => $item)
        {
            if (!is_array($item)) continue;

            $normalized = $this->normalize($item, (string) ($item['source'] ?? ''), (string) $id);
            if ($normalized) $items[$normalized['id']] = $normalized;
        }

        $custom_items = apply_filters('lsd_announcements_custom_items', []);
        if (is_array($custom_items))
        {
            foreach ($custom_items as $id => $item)
            {
                if (!is_array($item)) continue;

                $fallback_id = is_string($id) ? $id : '';
                $normalized = $this->normalize($item, self::SOURCE_LOCAL, $fallback_id);
                if (!$normalized) continue;

                if (isset($items[$normalized['id']]['state']) && $items[$normalized['id']]['state'] === self::STATE_DISMISSED)
                {
                    $normalized['state'] = self::STATE_DISMISSED;
                }

                $items[$normalized['id']] = $normalized;
            }
        }

        return $items;
    }

    protected function normalize(array $announcement, string $source = '', string $fallback_id = ''): array
    {
        $source = in_array($source, [self::SOURCE_REMOTE, self::SOURCE_LOCAL], true) ? $source : self::SOURCE_REMOTE;
        $id = $this->normalize_id((string) ($announcement['id'] ?? $fallback_id));

        if ($id === '')
        {
            $id = $this->normalize_id(md5($source . '|' . (string) ($announcement['title'] ?? '') . '|' . (string) ($announcement['url'] ?? '')));
        }

        $title = sanitize_text_field((string) ($announcement['title'] ?? ''));
        if ($id === '' || $title === '') return [];

        $state = sanitize_key((string) ($announcement['state'] ?? self::STATE_ACTIVE));
        if (!in_array($state, [self::STATE_ACTIVE, self::STATE_DISMISSED], true)) $state = self::STATE_ACTIVE;

        $severity = sanitize_key((string) ($announcement['severity'] ?? 'info'));
        if (!in_array($severity, ['info', 'success', 'warning', 'danger', 'error'], true)) $severity = 'info';

        $cta = isset($announcement['cta']) && is_array($announcement['cta']) ? $announcement['cta'] : [];
        $description = $announcement['description'] ?? ($announcement['body'] ?? '');
        $cta_label = $announcement['cta_label'] ?? ($cta['label'] ?? '');
        $url = $announcement['url'] ?? ($cta['url'] ?? '');
        $created_at = $announcement['created_at'] ?? ($announcement['start_at'] ?? 0);
        $expires_at = $announcement['expires_at'] ?? ($announcement['end_at'] ?? 0);

        $meta = isset($announcement['meta']) && is_array($announcement['meta'])
            ? LSD_Sanitize::deep($announcement['meta'])
            : [];

        foreach (['category', 'priority', 'start_at', 'end_at'] as $meta_key)
        {
            if (array_key_exists($meta_key, $announcement) && !array_key_exists($meta_key, $meta))
            {
                $meta[$meta_key] = is_scalar($announcement[$meta_key]) ? sanitize_text_field((string) $announcement[$meta_key]) : LSD_Sanitize::deep($announcement[$meta_key]);
            }
        }

        return [
            'id' => $id,
            'source' => $source,
            'state' => $state,
            'title' => $title,
            'description' => wp_kses_post((string) $description),
            'cta_label' => sanitize_text_field((string) $cta_label),
            'url' => esc_url_raw((string) $url),
            'severity' => $severity,
            'created_at' => $this->normalize_timestamp($created_at),
            'expires_at' => $this->normalize_timestamp($expires_at),
            'meta' => $meta,
        ];
    }

    protected function announcement_products(): array
    {
        if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $products = [LSD_BASENAME, 'listdom/listdom.php'];
        $active_plugins = (array) get_option('active_plugins', []);

        if (is_multisite())
        {
            $active_plugins = array_merge($active_plugins, array_keys((array) get_site_option('active_sitewide_plugins', [])));
        }

        foreach ($active_plugins as $basename)
        {
            $basename = (string) $basename;
            if ($basename === LSD_BASENAME || $basename === 'listdom/listdom.php' || strpos($basename, 'listdom-') === 0)
            {
                if (strpos($basename, 'listdomer-') !== 0) $products[] = $basename;
            }
        }

        $products = apply_filters('lsd_announcements_products', $products);
        if (!is_array($products)) $products = [];

        $normalized = [];
        foreach ($products as $product)
        {
            $product = sanitize_text_field((string) $product);
            if ($product === '' || strpos($product, 'listdomer-') === 0 || in_array($product, $normalized, true)) continue;

            $normalized[] = $product;
        }

        return $normalized;
    }

    protected function save(array $data): bool
    {
        $data = wp_parse_args($data, $this->defaults());
        $data['version'] = self::OPTION_VERSION;
        $data['items'] = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        if ($this->ensure_option($data)) return true;

        return update_option(self::OPTION_KEY, $data, false);
    }

    protected function ensure_option(?array $data = null): bool
    {
        if (get_option(self::OPTION_KEY, null) !== null) return false;

        return add_option(self::OPTION_KEY, $data ?? $this->defaults(), '', false);
    }

    protected function defaults(): array
    {
        return [
            'version' => self::OPTION_VERSION,
            'last_requested_at' => 0,
            'last_success_at' => 0,
            'items' => [],
        ];
    }

    protected function normalize_id(string $id): string
    {
        return sanitize_key($id);
    }

    protected function normalize_timestamp($value): int
    {
        if (is_numeric($value)) return max(0, (int) $value);
        if (!is_string($value) || trim($value) === '') return 0;

        $timestamp = strtotime($value);
        return $timestamp ? $timestamp : 0;
    }

    protected function refresh_interval(): int
    {
        return max(MINUTE_IN_SECONDS, (int) apply_filters('lsd_announcements_refresh_interval', DAY_IN_SECONDS));
    }

    protected function sort_by_created_at(array $items): array
    {
        usort($items, static function (array $a, array $b): int
        {
            $created = (int) ($b['created_at'] ?? 0) <=> (int) ($a['created_at'] ?? 0);
            if ($created !== 0) return $created;

            return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
        });

        return $items;
    }

    protected function now(): int
    {
        return time();
    }
}
