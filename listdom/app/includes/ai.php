<?php

class LSD_AI extends LSD_Base
{
    const TASK_AVAILABILITY = 'availability';
    const TASK_CONTENT = 'content';
    const TASK_MAPPING = 'mapping';

    private array $settings;

    public function __construct()
    {
        // General Settings
        $this->settings = LSD_Options::ai();
    }

    public function by_profile(string $id): ?LSD_AI_Model
    {
        // Profile
        $profile = $this->get_profile($id);

        // API Key
        $key = isset($profile['api_key']) && trim($profile['api_key']) ? $profile['api_key'] : '';

        // No API Key or Invalid Profile
        if (!$key) return null;

        // AI Model
        $model = $profile['model'] ?? LSD_AI_Models::def();

        // GPT 5 Mini
        if ($model === LSD_AI_Models::OPENAI_GPT_5_MINI) return new LSD_AI_Models_GPT5Mini($key);

        // GPT 5 Nano
        if ($model === LSD_AI_Models::OPENAI_GPT_5_NANO) return new LSD_AI_Models_GPT5Nano($key);

        // 4o Mini
        if ($model === LSD_AI_Models::OPENAI_GPT_4O_MINI) return new LSD_AI_Models_GPT4oMini($key);

        // Claude Sonnet 4
        if ($model === LSD_AI_Models::ANTHROPIC_CLAUDE_SONNET_4) return new LSD_AI_Models_ClaudeSonnet4($key);

        // Claude Haiku 3.5
        if ($model === LSD_AI_Models::ANTHROPIC_CLAUDE_HAIKU_35) return new LSD_AI_Models_ClaudeHaiku35($key);

        // Gemini 2.5 Flash
        if ($model === LSD_AI_Models::GOOGLE_GEMINI_25_FLASH) return new LSD_AI_Models_Gemini25Flash($key);

        // Gemini 2.5 Flash Lite
        if ($model === LSD_AI_Models::GOOGLE_GEMINI_25_FLASH_LITE) return new LSD_AI_Models_Gemini25FlashLite($key);

        // Default Provider
        return new LSD_AI_Models_GPT41Nano($key);
    }

    public function profile_supports_embeddings(string $id): bool
    {
        $profile = $this->get_profile($id);
        if (!count($profile)) return false;

        $model = $profile['model'] ?? LSD_AI_Models::def();
        return LSD_AI_Models::embedding_capable($model);
    }

    public function get_profile(string $id): array
    {
        // No Profile
        if (!$this->has_profile()) return [];

        foreach ($this->settings['profiles'] as $p)
        {
            if (isset($p['id']) && $id === $p['id']) return $p;
        }

        return [];
    }

    public function has_profile(): bool
    {
        return isset($this->settings['profiles'])
            && is_array($this->settings['profiles'])
            && count($this->settings['profiles']);
    }

    public function has_access(string $module): bool
    {
        // Guest User
        if (!is_user_logged_in()) return false;

        // No Profile
        if (!$this->has_profile()) return false;

        // Check Access
        $access = isset($this->settings['modules'][$module]['access'])
            && is_array($this->settings['modules'][$module]['access'])
            ? $this->settings['modules'][$module]['access']
            : ['administrator'];

        $user = wp_get_current_user();
        if (!($user instanceof WP_User)) return false;

        foreach ($user->roles as $role)
        {
            if (in_array($role, $access, true)) return true;
        }

        // No Access
        return false;
    }

    public function modules(): array
    {
        return apply_filters('lsd_ai_modules', [
            self::TASK_AVAILABILITY => esc_html__('Working Hours', 'listdom'),
            self::TASK_CONTENT => esc_html__('Content Generation', 'listdom'),
            self::TASK_MAPPING => esc_html__('Auto Mapping', 'listdom'),
        ]);
    }

    public function connector_approval_notice(): array
    {
        $page = $this->connector_approvals_page();
        if ($page === '') return [
            'show' => false,
            'url' => '',
        ];

        $connectors = $this->configured_connectors();
        if (!count($connectors)) return [
            'show' => false,
            'url' => '',
        ];

        $approvals = get_option('wpai_connector_approvals', []);
        $approvals = is_array($approvals) ? $approvals : [];

        $plugin_approvals = $approvals[LSD_BASENAME] ?? [];
        $plugin_approvals = is_array($plugin_approvals) ? $plugin_approvals : [];

        foreach ($connectors as $connector)
        {
            if (!empty($plugin_approvals[$connector])) continue;

            return [
                'show' => true,
                'url' => admin_url('tools.php?page=' . rawurlencode($page)),
            ];
        }

        return [
            'show' => false,
            'url' => '',
        ];
    }

    private function configured_connectors(): array
    {
        $configured = [];

        foreach ([
            'anthropic' => 'connectors_ai_anthropic_api_key',
            'google' => 'connectors_ai_google_api_key',
            'openai' => 'connectors_ai_openai_api_key',
        ] as $connector => $option)
        {
            if (trim((string) get_option($option, '')) === '') continue;
            $configured[] = $connector;
        }

        return $configured;
    }

    private function connector_approvals_page(): string
    {
        global $submenu;

        if (!isset($submenu['tools.php']) || !is_array($submenu['tools.php'])) return '';

        foreach ($submenu['tools.php'] as $item)
        {
            $page = isset($item[2]) ? (string) $item[2] : '';
            if ($page === '' || strpos($page, 'connector-approval') === false) continue;

            return $page;
        }

        return '';
    }
}
