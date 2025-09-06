<?php

/**
 * @method string($response)
 * @method request(string $prompt, float $temperature = 0.2, string $system = '')
 */
trait LSD_AI_Tasks_Content
{
    public function content(string $text): string
    {
        $response = $this->request(
            mb_substr($text, 0, 200),
            0.7,
            'You are an assistant that writes listing descriptions from a short explanation. Return only the generated text with no formatting or commentary.'
        );

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return '';

        return $this->string($response);
    }
}
