<?php

/** Bounded, typed evidence shared by the standalone diagnostic services. */
class LSD_Diagnostics_Report
{
    public $data;
    private $nodes = 0;
    private $links = 0;
    private $bytes = 0;

    public function __construct(string $scope)
    {
        $this->data = ['status' => 'completed', 'scope' => $scope, 'audience' => 'anonymous',
            'facts' => [], 'findings' => [], 'limitations' => [], 'candidates' => []];
    }

    public function fact(string $reference, string $key, $value): void
    {
        $this->data['facts'][] = compact('reference', 'key', 'value');
    }

    public function finding(string $code, string $severity, string $message, array $evidence): void
    {
        $this->data['findings'][] = compact('code', 'severity', 'message', 'evidence');
    }

    public function limit(string $code, string $message): void
    {
        foreach ($this->data['limitations'] as $limit) if ($limit['code'] === $code) return;
        $this->data['limitations'][] = compact('code', 'message');
        if ($this->data['status'] === 'completed') $this->data['status'] = 'partial';
    }

    /** One traversal/source budget is shared across all services in a request. */
    public function visit(int $depth, int $bytes = 0, bool $link = false): bool
    {
        $this->nodes++;
        $this->bytes += $bytes;
        if ($link) $this->links++;
        if ($depth <= 5 && $this->nodes <= 500 && $this->links <= 50 && $this->bytes <= 1048576) return true;
        $this->limit('traversal_limit', __('Saved-reference traversal reached its depth, node, resource or source-size limit.', 'listdom'));
        return false;
    }

    /** Remaining raw saved-source allowance, used before fetching metadata payloads. */
    public function remaining_source_bytes(): int
    {
        return max(0, 1048576 - $this->bytes);
    }

    /** Require administrator access and the native per-resource edit policy. */
    public static function resource(int $id, array $types)
    {
        if (!current_user_can('manage_options')) return self::error('forbidden', __('Administrator access is required.', 'listdom'));
        $post = get_post($id);
        if (!$post instanceof WP_Post)
            return self::error('invalid_target', __('The diagnostic target does not exist or has the wrong type.', 'listdom'));
        if (!current_user_can('edit_post', $id)) return self::error('forbidden', __('You cannot inspect a referenced resource.', 'listdom'));
        if (!in_array($post->post_type, $types, true)) return self::error('invalid_target', __('The diagnostic target has the wrong type.', 'listdom'));
        return $post;
    }

    public static function error(string $code, string $message): WP_Error
    {
        return new WP_Error('listdom_diagnostics_' . $code, $message);
    }

    /** Look up an already registered owning-package callback without booting it. */
    public static function callback(string $hook, string $class, string $method)
    {
        global $wp_filter;
        $callbacks = isset($wp_filter[$hook]) ? $wp_filter[$hook]->callbacks : [];
        foreach ($callbacks as $priority)
        {
            foreach ($priority as $entry)
            {
                $fn = $entry['function'];
                if (is_array($fn) && is_object($fn[0]) && get_class($fn[0]) === $class && $fn[1] === $method) return $fn;
            }
        }
        return null;
    }

    /** APS's thumbnail fallback writes an object cache; skip it during saved-evidence reads. */
    public static function suspend_thumbnail_fallback(): callable
    {
        global $wp_filter;
        $original = $wp_filter['get_post_metadata'] ?? null;
        if ($original)
        {
            $copy = clone $original;
            foreach ($copy->callbacks as $priority => $entries)
            {
                foreach ($entries as $id => $entry)
                {
                    $fn = $entry['function'];
                    if (is_array($fn) && is_object($fn[0]) && get_class($fn[0]) === 'LSDPACAPS\\Fallback' && $fn[1] === 'set') unset($copy->callbacks[$priority][$id]);
                }
            }
            $wp_filter['get_post_metadata'] = $copy;
        }
        return static function () use ($original)
        {
            if ($original) $GLOBALS['wp_filter']['get_post_metadata'] = $original;
            else unset($GLOBALS['wp_filter']['get_post_metadata']);
        };
    }

    /** Run a core-only read without arbitrary query/filter callbacks or global leakage. */
    public static function isolated(callable $read)
    {
        $names = ['wp_filter', 'wp_actions', 'wp_filters', 'post', 'wp_query', 'wp_the_query', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages'];
        $saved = [];
        foreach ($names as $name) $saved[$name] = [array_key_exists($name, $GLOBALS), $GLOBALS[$name] ?? null];
        $GLOBALS['wp_filter'] = [];
        try { return $read(); }
        finally
        {
            foreach ($saved as $name => $value)
            {
                if ($value[0]) $GLOBALS[$name] = $value[1];
                else unset($GLOBALS[$name]);
            }
        }
    }

    /** No raw options, HTML, URLs or filesystem paths belong in this envelope. */
    public function result()
    {
        $json = wp_json_encode($this->data);
        if ($json === false || strlen($json) > 32768)
            return self::error('result_too_large', __('The diagnostic evidence exceeds 32 KiB. Inspect a smaller media page or select one view reference.', 'listdom'));
        return $this->data;
    }
}
