<?php

class LSD_AI_Visibility_Schema_Normalizer extends LSD_Base
{
    /**
     * Normalize a schema.org property name.
     * @param mixed $value
     * @return string
     */
    public static function schema_property($value): string
    {
        // Property Value
        if (!is_scalar($value)) return '';

        $value = trim($value);

        if ($value === '') return '';

        $value = preg_replace('/^https?:\/\/schema\.org\//i', '', $value);
        $value = trim((string) $value, " \t\n\r\0\x0B/#");

        if ($value === '') return '';

        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $value)) return '';

        return $value;
    }

    /**
     * Normalize a schema.org type URL.
     * @param mixed $value
     * @return string
     */
    public static function schema_type($value): string
    {
        // Type Value
        if (!is_scalar($value)) return '';

        $value = trim($value);
        if ($value === '') return '';

        $value = preg_replace('/^https?:\/\/schema\.org\//i', '', $value);
        $value = trim((string) $value, " \t\n\r\0\x0B/#");
        if ($value === '') return '';

        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $value)) return '';

        return 'https://schema.org/' . $value;
    }

    /**
     * Check if a schema type is an Event type.
     * @param mixed $value
     * @return bool
     */
    public static function is_event_type($value): bool
    {
        // Type Value
        if (!is_scalar($value)) return false;

        $value = preg_replace('/^https?:\/\/schema\.org\//i', '', (string) $value);
        $value = trim((string) $value, " \t\n\r\0\x0B/#");

        return strcasecmp($value, 'Event') === 0 || (bool) preg_match('/Event$/i', $value);
    }
}
