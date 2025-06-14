<?php

class LSD_AI extends LSD_Base
{
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

        // 4o Mini
        if ($model === LSD_AI_Models::OPENAI_GPT_4O_MINI) return new LSD_AI_Models_GPT4oMini($key);

        // Default Provider
        return new LSD_AI_Models_GPT41Nano($key);
    }

    public function get_profile(string $id): array
    {
        // No profile
        if (!$this->has_profile()) return [];

        $profile = [];
        foreach ($this->settings['profiles'] as $p)
        {
            if (isset($p['id']) && $id === $p['id'])
            {
                $profile = $p;
                break;
            }
        }

        return $profile;
    }

    public function has_profile(): bool
    {
        return isset($this->settings['profiles'])
            && is_array($this->settings['profiles'])
            && count($this->settings['profiles']);
    }
}
