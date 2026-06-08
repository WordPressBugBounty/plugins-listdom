<?php

abstract class LSD_AI_Models_Google extends LSD_AI_Models_Base
{
    use LSD_AI_Tasks_Mapping;
    use LSD_AI_Tasks_Structured_Search;
    use LSD_AI_Tasks_Availability;
    use LSD_AI_Tasks_Content;

    private string $url = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private function request(string $prompt, float $temperature = 0.2, string $system = ''): array
    {
        // Request Body
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 2048,
            ],
        ];

        // System Prompt
        if ($system) $body['systemInstruction'] = ['parts' => [['text' => $system]]];

        // Model URL
        $url = $this->url . $this->key() . ':generateContent?key=' . $this->api_key();

        $response = wp_remote_post($url, [
            'method' => 'POST',
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
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
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function supports_embeddings(): bool
    {
        return true;
    }

    public function embedding(string $text, string $task = 'document'): array
    {
        $model = apply_filters('lsd_ai_google_embedding_model', 'gemini-embedding-001', $this);
        $url = $this->url . $model . ':embedContent?key=' . $this->api_key();
        $task_type = $task === 'query' ? 'RETRIEVAL_QUERY' : 'RETRIEVAL_DOCUMENT';

        $response = wp_remote_post($url, [
            'method' => 'POST',
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
                'taskType' => $task_type,
            ]),
            'data_format' => 'body',
        ]);

        if (is_wp_error($response))
        {
            error_log('Listdom AI Embedding Error: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded))
        {
            error_log('Listdom AI Embedding JSON Decode Error: ' . json_last_error_msg() . ' for response: ' . $body);
            return [];
        }

        $embedding = $decoded['embedding']['values'] ?? [];
        return is_array($embedding) ? array_map('floatval', $embedding) : [];
    }
}
