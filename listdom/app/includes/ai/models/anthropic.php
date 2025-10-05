<?php

abstract class LSD_AI_Models_Anthropic extends LSD_AI_Models_Base
{
    use LSD_AI_Tasks_Mapping;
    use LSD_AI_Tasks_Availability;
    use LSD_AI_Tasks_Content;

    private $url = 'https://api.anthropic.com/v1/messages';

    private function request(string $prompt, float $temperature = 0.2, string $system = ''): array
    {
        // Request Body
        $body = [
            'model' => $this->key(),
            'max_tokens' => 1024,
            'temperature' => $temperature,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt]
                    ]
                ]
            ],
        ];

        // System Prompt
        if ($system) $body['system'] = $system;

        $response = wp_remote_post($this->url, [
            'method' => 'POST',
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key(),
                'anthropic-version' => '2023-06-01',
            ],
            'body' => wp_json_encode($body),
            'data_format' => 'body',
        ]);

        if (is_wp_error($response))
        {
            error_log('Listdom AI Request Error: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_response))
        {
            error_log('Listdom AI JSON Decode Error: ' . json_last_error_msg() . ' for response: ' . $body);
            return [];
        }

        return $decoded_response;
    }

    protected function string(array $response): string
    {
        return $response['content'][0]['text'] ?? '';
    }
}
