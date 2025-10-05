<?php

abstract class LSD_AI_Models_GPT extends LSD_AI_Models_Base
{
    use LSD_AI_Tasks_Mapping;
    use LSD_AI_Tasks_Availability;
    use LSD_AI_Tasks_Content;

    private $url = 'https://api.openai.com/v1/chat/completions';

    private function request(string $prompt, float $temperature = 0.2, string $system = ''): array
    {
        // Prompt
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        // System Prompt
        if ($system) $messages[] = [
            'role' => 'system',
            'content' => $system,
        ];

        // Request Body
        $body = [
            'model' => $this->key(),
            'temperature' => $temperature,
            'messages' => $messages,
        ];

        // Perform the HTTP POST request
        $response = wp_remote_post($this->url, [
            'method' => 'POST',
            'timeout' => 10, // 10-second timeout
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key(),
            ],
            'body' => wp_json_encode($body),
            'data_format' => 'body', // Important: tells WP to send body as raw data
        ]);

        // Check for WP_Error (network issues, timeouts, etc.)
        if (is_wp_error($response))
        {
            // Log the error for debugging purposes
            error_log('Listdom AI Request Error: ' . $response->get_error_message());

            return [];
        }

        // Get the response body
        $body = wp_remote_retrieve_body($response);

        // Decode and return response
        $decoded_response = json_decode($body, true);

        // Check if JSON decoding failed or if the response is not an array/object
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_response) && !is_object($decoded_response))
        {
            // Log a JSON decoding error
            error_log('Listdom AI JSON Decode Error: ' . json_last_error_msg() . ' for response: ' . $body);

            return [];
        }

        return $decoded_response;
    }

    protected function string(array $response): string
    {
        return $response['choices'][0]['message']['content'] ?? '';
    }
}
