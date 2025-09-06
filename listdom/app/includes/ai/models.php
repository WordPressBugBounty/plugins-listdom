<?php

class LSD_AI_Models extends LSD_Base
{
    const OPENAI_GPT_41_NANO = 'gpt-4.1-nano';
    const OPENAI_GPT_4O_MINI = 'gpt-4o-mini';
    const OPENAI_GPT_5_MINI = 'gpt-5-mini';
    const OPENAI_GPT_5_NANO = 'gpt-5-nano';
    const ANTHROPIC_CLAUDE_SONNET_4 = 'claude-sonnet-4-20250514';
    const ANTHROPIC_CLAUDE_HAIKU_35 = 'claude-3-5-haiku-20241022';
    const GOOGLE_GEMINI_25_FLASH = 'gemini-2.5-flash';
    const GOOGLE_GEMINI_25_FLASH_LITE = 'gemini-2.5-flash-lite';

    public static function get_models(): array
    {
        return [
            self::OPENAI_GPT_41_NANO => esc_html__('OpenAI GPT 4.1 Nano', 'listdom'),
            self::OPENAI_GPT_4O_MINI => esc_html__('OpenAI GPT 4o Mini', 'listdom'),
            self::OPENAI_GPT_5_MINI => esc_html__('OpenAI GPT 5 Mini', 'listdom'),
            self::OPENAI_GPT_5_NANO => esc_html__('OpenAI GPT 5 Nano', 'listdom'),
            self::ANTHROPIC_CLAUDE_SONNET_4 => esc_html__('Anthropic Claude Sonnet 4', 'listdom'),
            self::ANTHROPIC_CLAUDE_HAIKU_35 => esc_html__('Anthropic Claude Haiku 3.5', 'listdom'),
            self::GOOGLE_GEMINI_25_FLASH => esc_html__('Google Gemini 2.5 Flash', 'listdom'),
            self::GOOGLE_GEMINI_25_FLASH_LITE => esc_html__('Google Gemini 2.5 Flash Lite', 'listdom'),
        ];
    }

    public static function valid(string $model): bool
    {
        $models = LSD_AI_Models::get_models();
        return isset($models[$model]);
    }

    public static function def(): string
    {
        return self::OPENAI_GPT_41_NANO;
    }
}
