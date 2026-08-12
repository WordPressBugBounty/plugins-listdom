<?php

class LSD_Checklist extends LSD_Base
{
    public const MENU_SLUG = 'listdom-launch-checklist';
    public const USER_META_SKIPS = 'lsd_checklist_skips';
    public const USER_META_BADGE_COUNT = 'lsd_checklist_badge_count';
    public const BADGE_CACHE_VERSION_OPTION = 'lsd_checklist_badge_cache_version';
    public const BADGE_CACHE_OPTIONS = [
        'blogdescription',
        'blog_public',
        'lsd_addons',
        'lsd_ai',
        'lsd_auth',
        'lsd_details_page',
        'lsd_details_page_pattern',
        'lsd_payments',
        'lsd_settings',
        'lsd_version',
    ];

    protected static ?LSD_Checklist $instance = null;
    protected ?LSD_Checklist_Registry $registry = null;
    protected ?LSD_Checklist_Helper $helper = null;
    protected ?array $report = null;

    public static function instance(): LSD_Checklist
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init()
    {
        add_action('admin_init', [$this, 'handle_optional_actions']);
        add_action('save_post', [$this, 'invalidate_badge_cache_on_post_change']);
        add_action('before_delete_post', [$this, 'invalidate_badge_cache_on_post_change']);
        add_action('set_object_terms', [$this, 'invalidate_badge_cache']);
        add_action('created_term', [$this, 'invalidate_badge_cache']);
        add_action('edited_term', [$this, 'invalidate_badge_cache']);
        add_action('delete_term', [$this, 'invalidate_badge_cache']);
        add_action('added_option', [$this, 'invalidate_badge_cache_on_option_change']);
        add_action('updated_option', [$this, 'invalidate_badge_cache_on_option_change']);
        add_action('activated_plugin', [$this, 'invalidate_badge_cache']);
        add_action('deactivated_plugin', [$this, 'invalidate_badge_cache']);
    }

    public function helper(): LSD_Checklist_Helper
    {
        if ($this->helper instanceof LSD_Checklist_Helper) return $this->helper;

        $this->helper = new LSD_Checklist_Helper();
        return $this->helper;
    }

    public function registry(): LSD_Checklist_Registry
    {
        if ($this->registry instanceof LSD_Checklist_Registry) return $this->registry;

        $this->registry = new LSD_Checklist_Registry();
        $this->register_core_checks($this->registry);

        do_action('lsd_register_checklist_checks', $this->registry, $this->helper());

        return $this->registry;
    }

    public function get_report(): array
    {
        if (is_array($this->report)) return $this->report;

        $grouped = [];
        $results = [];
        $priority = [];
        $counts = [
            'complete' => 0,
            'warning' => 0,
            'incomplete' => 0,
            'skipped' => 0,
            'optional' => 0,
        ];

        $score_total = 0.0;
        $score_earned = 0.0;

        foreach ($this->registry()->all() as $check)
        {
            $result = $check->evaluate()->to_array();

            if ($check->is_optional())
            {
                $result['optional'] = true;
                $result['skipped'] = $this->is_skipped($check->get_id());
                if (!empty($result['skipped']) && $result['status'] !== LSD_Checklist_Result::STATUS_COMPLETE)
                {
                    $result['status'] = LSD_Checklist_Result::STATUS_IGNORED;
                }
                elseif ($result['status'] === LSD_Checklist_Result::STATUS_OPTIONAL)
                {
                    $result['status'] = LSD_Checklist_Result::STATUS_OPTIONAL;
                }
            }

            $grouped[$result['category']][] = $result;
            $results[] = $result;

            if ($result['status'] === LSD_Checklist_Result::STATUS_COMPLETE) $counts['complete']++;
            elseif ($result['status'] === LSD_Checklist_Result::STATUS_WARNING) $counts['warning']++;
            elseif ($result['status'] === LSD_Checklist_Result::STATUS_INCOMPLETE) $counts['incomplete']++;
            elseif ($result['status'] === LSD_Checklist_Result::STATUS_IGNORED) $counts['skipped']++;
            else $counts['optional']++;

            if (!in_array($result['status'], [LSD_Checklist_Result::STATUS_OPTIONAL, LSD_Checklist_Result::STATUS_IGNORED], true))
            {
                $weight = $this->importance_weight($result['importance']);
                $score_total += $weight;

                if ($result['status'] === LSD_Checklist_Result::STATUS_COMPLETE) $score_earned += $weight;
                elseif ($result['status'] === LSD_Checklist_Result::STATUS_WARNING) $score_earned += ($weight * 0.5);
            }

            if (in_array($result['status'], [LSD_Checklist_Result::STATUS_INCOMPLETE, LSD_Checklist_Result::STATUS_WARNING], true))
            {
                $priority[] = $result;
            }
        }

        usort($priority, function (array $a, array $b): int
        {
            $severity_order = [
                LSD_Checklist_Result::STATUS_INCOMPLETE => 0,
                LSD_Checklist_Result::STATUS_WARNING => 1,
                LSD_Checklist_Result::STATUS_OPTIONAL => 2,
                LSD_Checklist_Result::STATUS_IGNORED => 3,
                LSD_Checklist_Result::STATUS_COMPLETE => 4,
            ];

            $status_compare = ($severity_order[$a['status']] ?? 99) <=> ($severity_order[$b['status']] ?? 99);
            if ($status_compare !== 0) return $status_compare;

            return $this->importance_weight($b['importance']) <=> $this->importance_weight($a['importance']);
        });

        $score = $score_total > 0 ? (int) round(($score_earned / $score_total) * 100) : 100;

        $this->report = [
            'score' => $score,
            'counts' => $counts,
            'items' => $results,
            'sections' => $grouped,
            'priority' => array_slice($priority, 0, 4),
            'checked_at' => current_time('timestamp'),
            'total' => count($results),
        ];

        $needs_attention = (int) $counts['warning'] + (int) $counts['incomplete'];
        set_transient($this->badge_cache_key(), $needs_attention, DAY_IN_SECONDS);
        update_user_meta(get_current_user_id(), $this->badge_user_meta_key(), $needs_attention);

        return $this->report;
    }

    public function needs_attention_count(): int
    {
        $needs_attention = get_transient($this->badge_cache_key());
        if ($needs_attention !== false) return (int) $needs_attention;

        $needs_attention = get_user_meta(get_current_user_id(), $this->badge_user_meta_key(), true);
        return is_numeric($needs_attention) ? (int) $needs_attention : 0;
    }

    public function invalidate_badge_cache(): void
    {
        $this->report = null;
        update_option(self::BADGE_CACHE_VERSION_OPTION, microtime(true));
    }

    public function invalidate_badge_cache_on_post_change(int $post_id): void
    {
        $post_type = get_post_type($post_id);
        $post_type_object = $post_type ? get_post_type_object($post_type) : null;
        $checklist_post_types = [
            LSD_Base::PTYPE_LISTING,
            LSD_Base::PTYPE_NOTIFICATION,
            LSD_Base::PTYPE_PLAN,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_TEMPLATE,
        ];

        if ((!$post_type_object || !$post_type_object->public) && !in_array($post_type, $checklist_post_types, true)) return;

        $this->invalidate_badge_cache();
    }

    public function invalidate_badge_cache_on_option_change(string $option): void
    {
        if (!in_array($option, self::BADGE_CACHE_OPTIONS, true)) return;

        $this->invalidate_badge_cache();
    }

    protected function badge_cache_version(): string
    {
        return (string) get_option(self::BADGE_CACHE_VERSION_OPTION, '1');
    }

    protected function badge_cache_key(): string
    {
        return 'lsd_checklist_badge_' . get_current_user_id() . '_' . $this->badge_cache_version();
    }

    protected function badge_user_meta_key(): string
    {
        return self::USER_META_BADGE_COUNT . '_' . get_current_blog_id();
    }

    public function handle_optional_actions(): void
    {
        if (!is_admin() || !current_user_can('manage_options')) return;
        if (!isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== self::MENU_SLUG) return;
        if (!isset($_GET['lsd_checklist_action'], $_GET['lsd_checklist_item'])) return;

        $action = sanitize_key(wp_unslash($_GET['lsd_checklist_action']));
        $item_id = sanitize_key(wp_unslash($_GET['lsd_checklist_item']));
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!wp_verify_nonce($nonce, 'lsd_checklist_optional_' . $action . '_' . $item_id)) return;

        $check = $this->registry()->get($item_id);
        if (!$check || !$check->is_optional()) return;

        $skips = $this->get_skips();

        if ($action === 'skip') $skips[$item_id] = 1;
        elseif ($action === 'unskip') unset($skips[$item_id]);

        update_user_meta(get_current_user_id(), self::USER_META_SKIPS, array_keys($skips));
        $this->invalidate_badge_cache();

        wp_safe_redirect(remove_query_arg(['lsd_checklist_action', 'lsd_checklist_item', '_wpnonce']));
        exit;
    }

    public function optional_action_url(string $item_id, string $action): string
    {
        return wp_nonce_url(add_query_arg([
            'page' => self::MENU_SLUG,
            'lsd_checklist_action' => $action,
            'lsd_checklist_item' => $item_id,
        ], admin_url('admin.php')), 'lsd_checklist_optional_' . $action . '_' . $item_id);
    }

    public function build_action_url(array $item): string
    {
        return $this->helper()->build_action_url($item);
    }

    public static function get_section_icon(string $section): string
    {
        $icons = [
            esc_html__('Core Directory Setup', 'listdom') => 'wbli-settings',
            esc_html__('Submission & User Flow', 'listdom') => 'wbli-account',
            esc_html__('Search & Discovery', 'listdom') => 'wbli-search',
            esc_html__('Monetization', 'listdom') => 'wbli-hot-price',
            esc_html__('Trust & Engagement', 'listdom') => 'wbli-eye',
            esc_html__('SEO & AI Visibility', 'listdom') => 'wbli-chart-increase',
            esc_html__('Priority Fixes', 'listdom') => 'wbli-left-to-right-list-star',
            esc_html__('Health Check Summary', 'listdom') => 'wbli-curvy-right-direction',
        ];

        return $icons[$section] ?? 'fa-circle-o';
    }

    public static function get_status_icon(string $status): string
    {
        $icons = [
            LSD_Checklist_Result::STATUS_COMPLETE => 'wbli-checkmark-circle',
            LSD_Checklist_Result::STATUS_WARNING => 'wbli-alert',
            LSD_Checklist_Result::STATUS_INCOMPLETE => 'wbli-cross',
            LSD_Checklist_Result::STATUS_OPTIONAL => 'wbli-first-aid-kit',
            LSD_Checklist_Result::STATUS_IGNORED => 'fas fa-ban',
        ];

        return $icons[$status] ?? 'fa-circle';
    }

    protected function register_core_checks(LSD_Checklist_Registry $registry): void
    {
        foreach ($this->providers() as $provider)
        {
            $provider->register($registry);
        }
    }

    protected function providers(): array
    {
        $helper = $this->helper();

        return [
            new LSD_Checklist_Provider_Core($helper),
            new LSD_Checklist_Provider_Submission($helper),
            new LSD_Checklist_Provider_Search($helper),
            new LSD_Checklist_Provider_Monetization($helper),
            new LSD_Checklist_Provider_Addons($helper),
            new LSD_Checklist_Provider_Trust($helper),
            new LSD_Checklist_Provider_Seo($helper),
        ];
    }

    protected function importance_weight(string $importance): int
    {
        if ($importance === 'high') return 3;
        if ($importance === 'low') return 1;
        return 2;
    }

    protected function get_skips(): array
    {
        $stored = get_user_meta(get_current_user_id(), self::USER_META_SKIPS, true);
        $stored = is_array($stored) ? $stored : [];

        $skips = [];
        foreach ($stored as $item_id)
        {
            $item_id = sanitize_key((string) $item_id);
            if ($item_id !== '') $skips[$item_id] = 1;
        }

        return $skips;
    }

    protected function is_skipped(string $item_id): bool
    {
        $skips = $this->get_skips();
        return isset($skips[$item_id]);
    }

    public static function get_score(int $score): array
    {
        $score = max(0, min(100, $score));

        if ($score >= 80)
        {
            $context = [
                'class' => 'is-strong',
                'label' => esc_html__('Healthy', 'listdom'),
                'title' => esc_html__('Your directory is almost ready.', 'listdom'),
                'summary' => esc_html__(
                    'Your directory is in good shape. Review the remaining items before launch.',
                    'listdom'
                ),
            ];
        }
        elseif ($score >= 60)
        {
            $context = [
                'class' => 'is-steady',
                'label' => esc_html__('Needs Attention', 'listdom'),
                'title' => esc_html__('Your directory needs some attention.', 'listdom'),
                'summary' => esc_html__(
                    'Your directory is progressing, but several areas still need attention.',
                    'listdom'
                ),
            ];
        }
        else
        {
            $context = [
                'class' => 'is-needs-work',
                'label' => esc_html__('Needs Work', 'listdom'),
                'title' => esc_html__('Your directory is not ready yet.', 'listdom'),
                'summary' => esc_html__(
                    'Complete the critical setup items before launching your directory.',
                    'listdom'
                ),
            ];
        }

        return apply_filters('lsd_checklist_score', $context, $score);
    }
}
