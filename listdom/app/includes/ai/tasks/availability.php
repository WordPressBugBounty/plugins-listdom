<?php

/**
 * @method response($response)
 * @method request(string $prompt, float $temperature = 0.2, string $system = '')
 */
trait LSD_AI_Tasks_Availability
{
    public function availability(string $text): array
    {
        $settings = LSD_Options::settings();
        $hour_format = $settings['timepicker_format'] ?? '24';

        $prompt = sprintf('Convert the following text that describes weekly working hours into a JSON object.

        Days are represented by numbers 1 (Monday) to 7 (Sunday).
        Each day should include "hours" and "off" (1 for closed, 0 otherwise).
        Times should be based on %s format.
        Example: {"1":{"hours":"9am-5pm","off":0},"2":{"off":1}}
        Return only the JSON object without any explanation.', $hour_format);

        $response = $this->request($prompt . "\n\n" . mb_substr($text, 0, 200));

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return [];

        return $this->response($response);
    }
}
