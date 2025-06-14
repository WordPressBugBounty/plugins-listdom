<?php

class LSD_AI_Models extends LSD_Base
{
    const OPENAI_GPT_41_NANO = 'gpt-4.1-nano';
    const OPENAI_GPT_4O_MINI = 'gpt-4o-mini';

    public static function get_models(): array
    {
        return [
            self::OPENAI_GPT_41_NANO => esc_html__('OpenAI GPT 4.1 Nano', 'listdom'),
            self::OPENAI_GPT_4O_MINI => esc_html__('OpenAI GPT 4o Mini', 'listdom'),
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
