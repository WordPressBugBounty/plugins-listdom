<?php

class LSD_Action_Validator
{
    public static function validate(array $schema, array $input): array
    {
        $errors = [];
        $sanitized = [];

        foreach ($schema as $key => $rules)
        {
            $required = !empty($rules['required']);
            $has_value = array_key_exists($key, $input);
            $value = $has_value ? $input[$key] : ($rules['default'] ?? null);

            if ($required && (!$has_value || $value === '' || $value === null || $value === []))
            {
                $errors[] = sprintf('Field "%s" is required.', $key);
                continue;
            }

            if (!$has_value && !array_key_exists('default', $rules)) continue;

            $type = $rules['type'] ?? 'string';
            $sanitized[$key] = self::sanitize($value, $type);

            if (!empty($rules['enum']) && !in_array($sanitized[$key], $rules['enum'], true))
            {
                $errors[] = sprintf('Field "%s" contains an unsupported value.', $key);
            }
        }

        return [$sanitized, $errors];
    }

    public static function sanitize($value, string $type)
    {
        switch ($type)
        {
            case 'int':
                return (int) $value;
            case 'bool':
                return (bool) $value;
            case 'array':
                return is_array($value) ? $value : [];
            case 'float':
                return (float) $value;
            case 'key':
                return sanitize_key((string) $value);
            case 'email':
                return sanitize_email((string) $value);
            case 'url':
                return esc_url_raw((string) $value);
            case 'string':
            default:
                return sanitize_text_field((string) $value);
        }
    }
}
