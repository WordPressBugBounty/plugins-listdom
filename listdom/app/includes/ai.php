<?php

class LSD_AI extends LSD_Base
{
    const TASK_AVAILABILITY = 'availability';
    const TASK_CONTENT = 'content';
    const TASK_MAPPING = 'mapping';

    private $settings;

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
}
