<?php

class LSD_AI_Models_GPT extends LSD_AI_Models_Base
{
    private $url = 'https://api.openai.com/v1/chat/completions';

    public function key(): string
    {
        return '';
    }

    public function auto_mapping(array $listdom_fields, array $source_fields): array
    {
        $listdom_fields_compact = [];
        foreach ($listdom_fields as $field_key => $listdom_field)
        {
            $listdom_fields_compact[$field_key] = [
                'label' => $listdom_field['label'] ?? '',
                'type' => $listdom_field['type'] ?? 'text',
            ];
        }

        $listdom_fields_json = json_encode($listdom_fields_compact, JSON_PRETTY_PRINT);
        $source_fields_json = json_encode($source_fields, JSON_PRETTY_PRINT);

        $prompt = 'You are an intelligent field-mapping assistant. Your task is to map Listdom fields to the most appropriate column indices from a provided CSV or Excel file.

        Instructions:
        - Read both the Listdom fields and the CSV/Excel column titles. Evaluate each column individually, regardless of its position in the file. Do not skip valid columns based on their order.
        - Always prioritize **exact or very close name matches** (e.g., "name" → "post_title", "city" → "locations").
        - Ignore any CSV/Excel columns that are empty or unnamed. 
          In the JSON input, such columns may appear as an empty string (""), null, or whitespace-only. 
          Do not map these columns to any Listdom field.
        - Map each Listdom field to the most relevant CSV column index.
        - Use semantic similarity to guide mappings. For example: 
          - "name" or "listing name" → "post_title"
          - "places" or "city" → "locations"
        - If a clear match cannot be found, exclude that field from the mapping.
        
        Inputs:
        Listdom Fields (JSON):
        ' . $listdom_fields_json . '
        
        CSV/Excel Columns (first row as JSON):
        ' . $source_fields_json . '
        
        Output:
        Generate a clean JSON object where the key is the Listdom field name and the value is the corresponding CSV column index.
        Only include fields that have a valid match and are not based on empty or unnamed columns.
        
        Example format:
        {"unique_id":0,"post_title":3,"post_name":5}
        
        Return only the JSON object without any explanation or extra text.';

        $response = $this->request([
            'model' => $this->key(),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ]);

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return [];

        return $this->response($response);
    }

    public function availability(string $text): array
    {
        // General Settings
        $settings = LSD_Options::settings();

        // Hour Format
        $hour_format = $settings['timepicker_format'] ?? '24';

        // Prompt
        $prompt = sprintf('Convert the following text that describes weekly working hours into a JSON object.
        
        Days are represented by numbers 1 (Monday) to 7 (Sunday).
        Each day should include "hours" and "off" (1 for closed, 0 otherwise).
        Times should be based on %s format.
        Example: {"1":{"hours":"9am-5pm","off":0},"2":{"off":1}}
        Return only the JSON object without any explanation.', $hour_format);

        $response = $this->request([
            'model' => $this->key(),
            'messages' => [
                ['role' => 'user', 'content' => $prompt . "\n\n" . mb_substr($text, 0, 200)],
            ],
            'temperature' => 0.2,
        ]);

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return [];

        return $this->response($response);
    }

    public function content(string $text): string
    {
        $response = $this->request([
            'model' => $this->key(),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an assistant that writes listing descriptions from a short explanation. '
                        . 'Return only the generated text with no formatting or commentary.',
                ],
                ['role' => 'user', 'content' => mb_substr($text, 0, 200)],
            ],
            'temperature' => 0.7,
        ]);

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return '';

        return trim($response['choices'][0]['message']['content'] ?? '');
    }

    private function request(array $request): array
    {
        // Perform the HTTP POST request
        $response = wp_remote_post($this->url, [
            'method' => 'POST',
            'timeout' => 10, // 10-second timeout
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key(),
            ],
            'body' => json_encode($request),
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

    private function response(array $response): ?array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        return $this->json_extract($content);
    }
}
