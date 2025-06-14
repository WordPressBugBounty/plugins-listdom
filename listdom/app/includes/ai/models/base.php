<?php

class LSD_AI_Models_Base
{
    private $api_key;

    public function __construct(string $api_key)
    {
        $this->api_key = $api_key;
    }

    public function api_key(): string
    {
        return $this->api_key;
    }

    public function json_extract(string $text): ?array
    {
        // Try to find the start of a JSON object or array
        $start_json_object = strpos($text, '{');
        $start_json_array = strpos($text, '[');

        $start_index = -1;

        // Determine the earliest valid JSON start character
        if ($start_json_object !== false && ($start_json_array === false || $start_json_object < $start_json_array))
        {
            $start_index = $start_json_object;
        }
        else if ($start_json_array !== false)
        {
            $start_index = $start_json_array;
        }

        if ($start_index === -1)
        {
            return []; // No potential JSON start found
        }

        // Iterate from the start_index, attempting to decode substrings
        // This is a heuristic approach, as true JSON parsing requires a state machine
        // or dedicated parser for perfect accuracy. However, for well-formed JSON
        // embedded in text, this often works.
        for ($i = strlen($text); $i >= $start_index; $i--)
        {
            $potential_json_string = substr($text, $start_index, $i - $start_index);

            // Attempt to decode
            $decoded_json = json_decode($potential_json_string, true);

            // Check if decoding was successful and if the decoded result is a valid JSON structure (array or object)
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded_json) || is_object($decoded_json)))
            {
                return $decoded_json; // Found and successfully decoded valid JSON
            }
        }

        return []; // No valid JSON found
    }
}
