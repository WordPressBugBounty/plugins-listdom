<?php

class LSD_AI_Semantic extends LSD_Base
{
    private const TABLE = '#__lsd_ai_embeddings';
    private const DEFAULT_MIN_SCORE = 0.45;
    private const QUEUE_STATUS_QUEUED = 'queued';
    private const QUEUE_STATUS_ALREADY_PENDING = 'already_pending';
    private const QUEUE_STATUS_FAILED = 'failed';

    public const OBJECT_TYPE_LISTING = 'listing';
    public const USAGE_SEARCH = 'semantic-search';
    public const DOCUMENT_VERSION = 1;
    public const SEARCH_VERSION = 2;

    private LSD_db $db;

    public function __construct()
    {
        $this->db = new LSD_db();
    }

    public function init()
    {
        add_action('lsd_listing_saved', [$this, 'schedule_reindex'], 10, 3);
        add_action('delete_post', [$this, 'delete_listing_index']);
        add_action('wp_ajax_lsd_ai_semantic_reindex', [$this, 'ajax_reindex']);

        add_filter('lsd_job_ai_semantic_index', [$this, 'run_job'], 10, 3);
    }

    public function settings(): array
    {
        $ai = LSD_Options::ai();
        $settings = $ai['semantic_search'] ?? [];

        return is_array($settings) ? $settings : [];
    }

    public function enabled(): bool
    {
        return isset($this->settings()['enabled']) && (int) $this->settings()['enabled'] === 1;
    }

    public function profile_id(): string
    {
        $profile_id = isset($this->settings()['profile_id']) ? sanitize_text_field($this->settings()['profile_id']) : '';
        if ($profile_id !== '' && isset($this->profiles()[$profile_id])) return $profile_id;

        return '';
    }

    public function profiles(): array
    {
        $ai = LSD_Options::ai();
        $models = LSD_AI_Models::get_models();
        $engine = new LSD_AI();
        $profiles = [];

        if (isset($ai['profiles']) && is_array($ai['profiles']))
        {
            foreach ($ai['profiles'] as $profile)
            {
                $id = isset($profile['id']) ? sanitize_text_field($profile['id']) : '';
                if ($id === '' || !$engine->profile_supports_embeddings($id)) continue;

                $name = isset($profile['name']) && trim($profile['name']) !== ''
                    ? $profile['name']
                    : esc_html__('Unnamed Profile', 'listdom');
                $model = isset($profile['model'], $models[$profile['model']]) ? $models[$profile['model']] : '';

                $profiles[$id] = $model !== '' ? $name . ' (' . $model . ')' : $name;
            }
        }

        return $profiles;
    }

    public function attribute_ids(): array
    {
        $attribute_ids = isset($this->settings()['attributes']) && is_array($this->settings()['attributes'])
            ? $this->settings()['attributes']
            : [];

        return array_values(array_filter(array_map('intval', $attribute_ids)));
    }

    public function attribute_options(): array
    {
        $options = [];

        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            $attribute_id = isset($attribute['id']) ? (int) $attribute['id'] : 0;
            $field_type = $attribute['field_type'] ?? '';

            if (!$attribute_id || $field_type === 'file') continue;

            $label = $attribute['name'] ?? ('#' . $attribute_id);
            $options[$attribute_id] = [
                'html' => $field_type
                    ? esc_html($label) . ' <span class="lsd-muted">(' . esc_html($field_type) . ')</span>'
                    : esc_html($label),
            ];
        }

        return $options;
    }

    public function config(): array
    {
        $profile_id = $this->profile_id();
        if (!$this->enabled() || $profile_id === '') return [];

        return [
            'usage' => self::USAGE_SEARCH,
            'object_type' => self::OBJECT_TYPE_LISTING,
            'profile_id' => $profile_id,
            'attribute_ids' => $this->attribute_ids(),
        ];
    }

    public function is_ready(): bool
    {
        return $this->enabled() && count($this->config()) > 0;
    }

    public function min_score(): float
    {
        $score = $this->settings()['min_score'] ?? self::DEFAULT_MIN_SCORE;
        if (!is_numeric($score)) return self::DEFAULT_MIN_SCORE;

        $score = (float) $score;
        if ($score < 0 || $score > 1) return self::DEFAULT_MIN_SCORE;

        return $score;
    }

    public function schedule_reindex($post, $data = [], bool $is_new = false)
    {
        if (!$post instanceof WP_Post || $post->post_type !== self::PTYPE_LISTING) return;

        $configs = $this->configs((int) $post->ID);
        if (!count($configs))
        {
            $this->delete_object((int) $post->ID);
            return;
        }

        if ($post->post_status !== 'publish')
        {
            $this->delete_object((int) $post->ID);
            return;
        }

        $this->queue_listing((int) $post->ID);
    }

    public function delete_listing_index($post_id)
    {
        $post = get_post($post_id);
        if (!$post instanceof WP_Post || $post->post_type !== self::PTYPE_LISTING) return;

        $this->delete_object((int) $post_id);
    }

    public function queue_listing(int $listing_id): string
    {
        if ($listing_id <= 0) return self::QUEUE_STATUS_FAILED;

        $jobs = new LSD_Jobs();
        $result = $jobs->add_once('ai', ['listing_id' => $listing_id], 'semantic_index');
        $status = isset($result['status']) ? (string) $result['status'] : '';

        if ($status === LSD_Jobs::STATUS_QUEUED) return self::QUEUE_STATUS_QUEUED;
        if ($status === LSD_Jobs::STATUS_ALREADY_PENDING) return self::QUEUE_STATUS_ALREADY_PENDING;

        return self::QUEUE_STATUS_FAILED;
    }

    public function queue_listings(array $listing_ids): array
    {
        $results = [
            self::QUEUE_STATUS_QUEUED => 0,
            self::QUEUE_STATUS_ALREADY_PENDING => 0,
            self::QUEUE_STATUS_FAILED => 0,
        ];

        foreach ($listing_ids as $listing_id)
        {
            $status = $this->queue_listing((int) $listing_id);
            if (!isset($results[$status])) $status = self::QUEUE_STATUS_FAILED;

            $results[$status]++;
        }

        return $results;
    }

    public function queue_all_listings(): array
    {
        if (!count($this->configs()))
        {
            return [
                self::QUEUE_STATUS_QUEUED => 0,
                self::QUEUE_STATUS_ALREADY_PENDING => 0,
                self::QUEUE_STATUS_FAILED => 0,
            ];
        }

        $listing_ids = get_posts([
            'post_type' => self::PTYPE_LISTING,
            'post_status' => 'publish',
            'fields' => 'ids',
            'numberposts' => -1,
            'suppress_filters' => false,
        ]);

        return $this->queue_listings(is_array($listing_ids) ? $listing_ids : []);
    }

    public function run_job($result, $data, $job): bool
    {
        $listing_id = isset($data['listing_id']) ? (int) $data['listing_id'] : 0;
        if ($listing_id <= 0) return true;

        $configs = $this->configs($listing_id);
        if (!count($configs))
        {
            $this->delete_object($listing_id);
            return true;
        }

        $indexed = false;
        foreach ($configs as $config)
        {
            $indexed = $this->index_listing($listing_id, $config) || $indexed;
        }

        return $indexed;
    }

    public function search(string $query, array $config, int $limit = 250): array
    {
        $config = $this->normalize_config($config);
        if (!count($config) || trim($query) === '') return [];

        $cache_key = $this->query_cache_key($query, $config, $limit);
        $cached = get_transient($cache_key);
        if (is_array($cached)) return array_values(array_map('intval', $cached));

        $model = $this->model($config['profile_id']);
        if (!$model || !$model->supports_embeddings()) return [];

        $query_embedding = $model->embedding($this->normalize_text($query), 'query');
        if (!count($query_embedding)) return [];

        $config_hash = $this->config_hash($config);
        $rows = $this->db->select($this->db->prepare(
            "SELECT `object_id`, `embedding` FROM `#__lsd_ai_embeddings` WHERE `object_type` = %s AND `usage` = %s AND `profile_id` = %s AND `config_hash` = %s",
            $config['object_type'],
            $config['usage'],
            $config['profile_id'],
            $config_hash
        ), 'loadAssocList');

        if (!is_array($rows) || !count($rows)) return [];

        $scores = [];
        foreach ($rows as $row)
        {
            $object_id = isset($row['object_id']) ? (int) $row['object_id'] : 0;
            if ($object_id <= 0 || !isset($row['embedding'])) continue;

            $embedding = json_decode((string) $row['embedding'], true);
            if (!is_array($embedding) || !count($embedding)) continue;

            $score = $this->cosine_similarity($query_embedding, array_map('floatval', $embedding));
            if ($score <= 0) continue;

            $scores[$object_id] = $score;
        }

        if (!count($scores)) return [];

        arsort($scores, SORT_NUMERIC);
        $scores = $this->filter_scores($scores, $query, $config, $limit);
        if (!count($scores))
        {
            set_transient($cache_key, [], $this->query_cache_ttl($config));
            return [];
        }

        $ids = array_keys($scores);
        $ids = array_values(array_map('intval', $ids));

        set_transient($cache_key, $ids, $this->query_cache_ttl($config));

        return $ids;
    }

    public function count_indexed(array $config): int
    {
        $config = $this->normalize_config($config);
        if (!count($config)) return 0;

        return (int) $this->db->select($this->db->prepare(
            "SELECT COUNT(*) FROM `#__lsd_ai_embeddings` WHERE `object_type` = %s AND `usage` = %s AND `profile_id` = %s AND `config_hash` = %s",
            $config['object_type'],
            $config['usage'],
            $config['profile_id'],
            $this->config_hash($config)
        ), 'loadResult');
    }

    public function count_total_listings(): int
    {
        return (int) wp_count_posts(self::PTYPE_LISTING)->publish;
    }

    public function index_listing(int $listing_id, array $config): bool
    {
        $config = $this->normalize_config($config);
        if (!count($config) || $listing_id <= 0) return false;

        $post = get_post($listing_id);
        if (!$post instanceof WP_Post || $post->post_type !== self::PTYPE_LISTING || $post->post_status !== 'publish')
        {
            $this->delete_index($config, $listing_id);
            return true;
        }

        $document = $this->document($listing_id, $config);
        if ($document === '')
        {
            $this->delete_index($config, $listing_id);
            return true;
        }

        $content_hash = md5($document);
        $config_hash = $this->config_hash($config);
        $existing = $this->row($config, $listing_id);

        if (
            is_array($existing)
            && ($existing['content_hash'] ?? '') === $content_hash
            && ($existing['config_hash'] ?? '') === $config_hash
            && trim((string) ($existing['embedding'] ?? '')) !== ''
        )
        {
            return true;
        }

        $model = $this->model($config['profile_id']);
        if (!$model || !$model->supports_embeddings()) return false;

        $embedding = $model->embedding($document);
        if (!count($embedding)) return false;

        $now = current_time('mysql');
        $saved = (bool) $this->db->replace(self::TABLE, [
            'object_type' => $config['object_type'],
            'object_id' => $listing_id,
            'usage' => $config['usage'],
            'profile_id' => $config['profile_id'],
            'model' => $this->profile_model($config['profile_id']),
            'config_hash' => $config_hash,
            'content_hash' => $content_hash,
            'document' => $document,
            'embedding' => wp_json_encode(array_values(array_map('floatval', $embedding))),
            'dimensions' => count($embedding),
            'indexed_at' => $now,
            'updated_at' => $now,
        ], [
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
        ]);

        if ($saved) $this->touch_cache_version();

        return $saved;
    }

    public function supports_profile(string $profile_id): bool
    {
        $profile_id = sanitize_text_field($profile_id);
        if ($profile_id === '') return false;

        return (new LSD_AI())->profile_supports_embeddings($profile_id);
    }

    public function configs(int $listing_id = 0): array
    {
        $configs = [];
        $default = $this->config();
        if (count($default)) $configs[] = $default;

        $extra = apply_filters('lsd_ai_semantic_configs', [], $listing_id);
        if (is_array($extra) && count($extra)) $configs = array_merge($configs, $extra);

        $normalized = [];
        foreach ($configs as $config)
        {
            if (!is_array($config)) continue;

            $config = $this->normalize_config($config);
            if (!count($config) || !$this->supports_profile($config['profile_id'])) continue;

            $normalized[$config['usage'] . ':' . $config['profile_id']] = $config;
        }

        return array_values($normalized);
    }

    public function ajax_reindex()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_ai_semantic_reindex'))
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('Security nonce is invalid.', 'listdom')]);
        }

        if (!current_user_can('manage_options'))
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('You are not allowed to reindex semantic search.', 'listdom')]);
        }

        if (!$this->enabled())
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('Enable Semantic Search first, then queue a reindex.', 'listdom')]);
        }

        if (!count($this->config()))
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('Choose an embedding-capable AI profile before queueing a semantic reindex.', 'listdom')]);
        }

        $results = $this->queue_all_listings();
        $queued = isset($results[self::QUEUE_STATUS_QUEUED]) ? (int) $results[self::QUEUE_STATUS_QUEUED] : 0;
        $already_pending = isset($results[self::QUEUE_STATUS_ALREADY_PENDING]) ? (int) $results[self::QUEUE_STATUS_ALREADY_PENDING] : 0;
        $failed = isset($results[self::QUEUE_STATUS_FAILED]) ? (int) $results[self::QUEUE_STATUS_FAILED] : 0;

        if (!$queued && !$already_pending)
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('No listings were queued for semantic reindexing.', 'listdom')]);
        }

        $message = '';
        if ($queued && $already_pending)
        {
            $message = sprintf(
                /* translators: 1: newly queued listings count. 2: already pending listings count. */
                esc_html__('%1$d listings were queued for semantic reindexing. %2$d listings were already pending.', 'listdom'),
                $queued,
                $already_pending
            );
        }
        else if ($queued)
        {
            $message = sprintf(
                /* translators: %d: newly queued listings count. */
                esc_html__('%d listings were queued for semantic reindexing.', 'listdom'),
                $queued
            );
        }
        else
        {
            $message = sprintf(
                /* translators: %d: already pending listings count. */
                esc_html__('%d listings were already queued for semantic reindexing.', 'listdom'),
                $already_pending
            );
        }

        if ($failed)
        {
            $message .= ' ' . sprintf(
                /* translators: %d: failed listings count. */
                esc_html__('%d listings could not be queued.', 'listdom'),
                $failed
            );
        }

        wp_send_json([
            'success' => 1,
            'message' => $message,
        ]);
    }

    private function normalize_config(array $config): array
    {
        $profile_id = isset($config['profile_id']) ? sanitize_text_field($config['profile_id']) : '';
        if ($profile_id === '') return [];

        $model = isset($config['model']) ? sanitize_text_field($config['model']) : $this->profile_model($profile_id);
        if ($model === '') return [];

        $attribute_ids = isset($config['attribute_ids']) && is_array($config['attribute_ids']) ? $config['attribute_ids'] : [];
        $attribute_ids = array_values(array_filter(array_map('intval', $attribute_ids)));
        sort($attribute_ids, SORT_NUMERIC);

        return [
            'object_type' => isset($config['object_type']) ? sanitize_key($config['object_type']) : self::OBJECT_TYPE_LISTING,
            'usage' => isset($config['usage']) ? sanitize_key($config['usage']) : self::USAGE_SEARCH,
            'profile_id' => $profile_id,
            'model' => $model,
            'attribute_ids' => $attribute_ids,
        ];
    }

    private function config_hash(array $config): string
    {
        $extensions = apply_filters('lsd_ai_semantic_config_signature', [], $config);
        if (!is_array($extensions)) $extensions = [];

        return md5(wp_json_encode([
            'document_version' => self::DOCUMENT_VERSION,
            'object_type' => $config['object_type'],
            'usage' => $config['usage'],
            'profile_id' => $config['profile_id'],
            'model' => $config['model'],
            'attribute_ids' => $config['attribute_ids'],
            'extensions' => $extensions,
        ]));
    }

    private function query_cache_key(string $query, array $config, int $limit): string
    {
        return 'lsd_ai_semantic_query_' . md5(wp_json_encode([
            'version' => (int) get_option('lsd_ai_semantic_cache_version', 1),
            'search_version' => self::SEARCH_VERSION,
            'query' => $this->normalize_text($query),
            'limit' => $limit,
            'lang' => isset($_REQUEST['lang']) ? sanitize_text_field(wp_unslash($_REQUEST['lang'])) : '',
            'config_hash' => $this->config_hash($config),
            'min_score' => $this->min_score(),
        ]));
    }

    private function query_cache_ttl(array $config): int
    {
        $ttl = (int) apply_filters('lsd_ai_semantic_query_cache_ttl', DAY_IN_SECONDS, $config);
        return $ttl > 0 ? $ttl : DAY_IN_SECONDS;
    }

    private function touch_cache_version()
    {
        $version = (int) get_option('lsd_ai_semantic_cache_version', 1);
        update_option('lsd_ai_semantic_cache_version', $version + 1, false);
    }

    private function row(array $config, int $listing_id): ?array
    {
        $row = $this->db->select($this->db->prepare(
            "SELECT * FROM `#__lsd_ai_embeddings` WHERE `object_type` = %s AND `object_id` = %d AND `usage` = %s AND `profile_id` = %s LIMIT 1",
            $config['object_type'],
            $listing_id,
            $config['usage'],
            $config['profile_id']
        ), 'loadAssoc');

        return is_array($row) ? $row : null;
    }

    private function delete_index(array $config, int $listing_id)
    {
        $deleted = $this->db->delete(self::TABLE, [
            'object_type' => $config['object_type'],
            'object_id' => $listing_id,
            'usage' => $config['usage'],
            'profile_id' => $config['profile_id'],
        ], ['%s', '%d', '%s', '%s']);

        if ($deleted) $this->touch_cache_version();
    }

    private function delete_object(int $object_id)
    {
        $deleted = $this->db->delete(self::TABLE, [
            'object_type' => sanitize_key(self::OBJECT_TYPE_LISTING),
            'object_id' => $object_id,
        ], ['%s', '%d']);

        if ($deleted) $this->touch_cache_version();
    }

    private function document(int $listing_id, array $config): string
    {
        $post = get_post($listing_id);
        if (!$post instanceof WP_Post) return '';

        $parts = [];

        $parts[] = $this->section('Title', get_the_title($listing_id));
        $parts[] = $this->section('Excerpt', $post->post_excerpt);
        $parts[] = $this->section('Description', $post->post_content);
        $parts[] = $this->section('Categories', $this->term_names($listing_id, self::TAX_CATEGORY));
        $parts[] = $this->section('Locations', $this->term_names($listing_id, self::TAX_LOCATION));
        $parts[] = $this->section('Features', $this->term_names($listing_id, self::TAX_FEATURE));
        $parts[] = $this->section('Labels', $this->term_names($listing_id, self::TAX_LABEL));
        $parts[] = $this->section('Tags', $this->term_names($listing_id, self::TAX_TAG));
        $parts[] = $this->section('Address', get_post_meta($listing_id, 'lsd_address', true));
        $parts[] = $this->section('Price', get_post_meta($listing_id, 'lsd_price', true));
        $parts[] = $this->section('Price Class', $this->price_class((string) get_post_meta($listing_id, 'lsd_price_class', true)));
        $parts[] = $this->section('Attributes', $this->attribute_lines($listing_id, $config['attribute_ids']));

        $document = trim(implode("\n", array_filter($parts)));
        $document = apply_filters('lsd_ai_semantic_document', $document, $listing_id, $config);

        return $this->normalize_text($document);
    }

    private function attribute_lines(int $listing_id, array $attribute_ids): string
    {
        if (!count($attribute_ids)) return '';

        $lines = [];
        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            $attribute_id = isset($attribute['id']) ? (int) $attribute['id'] : 0;
            if (!$attribute_id || !in_array($attribute_id, $attribute_ids, true)) continue;

            $field_type = $attribute['field_type'] ?? '';
            if ($field_type === 'file') continue;

            $slug = $attribute['slug'] ?? '';
            if ($slug === '') continue;

            $value = get_post_meta($listing_id, 'lsd_attribute_' . $slug, true);
            if (is_array($value)) $value = implode(', ', array_map('sanitize_text_field', $value));
            else $value = sanitize_text_field((string) $value);

            if (trim($value) === '') continue;

            $lines[] = ($attribute['name'] ?? $slug) . ': ' . $value;
        }

        return implode("\n", $lines);
    }

    private function term_names(int $listing_id, string $taxonomy): string
    {
        $terms = wp_get_post_terms($listing_id, $taxonomy, ['fields' => 'names']);
        if (is_wp_error($terms) || !is_array($terms) || !count($terms)) return '';

        return implode(', ', array_map('sanitize_text_field', $terms));
    }

    private function section(string $label, $value): string
    {
        $value = is_string($value) ? $value : (is_numeric($value) ? (string) $value : '');
        $value = $this->normalize_text($value);
        if ($value === '') return '';

        return $label . ': ' . $value;
    }

    private function normalize_text(string $text): string
    {
        $text = wp_strip_all_tags(strip_shortcodes($text), true);
        $text = preg_replace('/\s+/', ' ', trim($text));
        return is_string($text) ? $text : '';
    }

    private function cosine_similarity(array $first, array $second): float
    {
        $length = min(count($first), count($second));
        if ($length === 0) return 0.0;

        $dot = 0.0;
        $first_norm = 0.0;
        $second_norm = 0.0;

        for ($i = 0; $i < $length; $i++)
        {
            $a = (float) $first[$i];
            $b = (float) $second[$i];

            $dot += $a * $b;
            $first_norm += $a * $a;
            $second_norm += $b * $b;
        }

        if ($first_norm <= 0 || $second_norm <= 0) return 0.0;

        return $dot / (sqrt($first_norm) * sqrt($second_norm));
    }

    private function filter_scores(array $scores, string $query, array $config, int $limit): array
    {
        if (!count($scores)) return [];

        $max_candidates = (int) apply_filters('lsd_ai_semantic_max_candidates', 25, $query, $config, $scores);
        if ($max_candidates <= 0) $max_candidates = 25;

        $minimum_score = (float) apply_filters('lsd_ai_semantic_min_score', $this->min_score(), $query, $config, $scores);
        $relative_score = (float) apply_filters('lsd_ai_semantic_relative_score', 0.96, $query, $config, $scores);

        $best_score = reset($scores);
        $best_score = is_numeric($best_score) ? (float) $best_score : 0.0;
        if ($best_score <= 0) return [];

        $relative_score = $relative_score > 0 && $relative_score <= 1 ? $relative_score : 0.96;
        $threshold = max($minimum_score, $best_score * $relative_score);

        $filtered = array_filter($scores, static function ($score) use ($threshold)
        {
            return (float) $score >= $threshold;
        });

        if (!count($filtered)) return [];

        return array_slice($filtered, 0, max(1, min($limit, $max_candidates)), true);
    }

    private function model(string $profile_id): ?LSD_AI_Model
    {
        $profile_id = sanitize_text_field($profile_id);
        if ($profile_id === '') return null;

        return (new LSD_AI())->by_profile($profile_id);
    }

    private function profile_model(string $profile_id): string
    {
        $profile = (new LSD_AI())->get_profile($profile_id);
        return isset($profile['model']) ? sanitize_text_field($profile['model']) : '';
    }

    private function price_class(string $value): string
    {
        $value = trim($value);
        if ($value === '1') return '$';
        if ($value === '2') return '$$';
        if ($value === '3') return '$$$';
        if ($value === '4') return '$$$$';

        return '';
    }
}
