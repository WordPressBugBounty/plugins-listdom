<?php

/** Shared native registration and schema helpers for Listdom abilities. */
class LSD_Abilities_Schema
{
    /** Build an object schema with explicit properties and requirements. */
    public static function object(array $properties, array $required = [], bool $additional = false): array
    {
        $schema = [
            'type' => 'object',
            'additionalProperties' => $additional,
        ];
        if ($properties) $schema['properties'] = $properties;
        if ($required) $schema['required'] = $required;
        return $schema;
    }

    /** Build a list schema with an optional item limit. */
    public static function list(array $item, ?int $max = 100): array
    {
        $schema = ['type' => 'array', 'items' => $item];
        if ($max !== null) $schema['maxItems'] = $max;
        return $schema;
    }

    /** Convert decoded ability input to nested arrays. */
    public static function input($input): array
    {
        // WordPress may pass decoded JSON objects to ability callbacks.
        if ($input instanceof stdClass) $input = (array) $input;
        if (!is_array($input)) return [];

        foreach ($input as $key => $value)
        {
            if ($value instanceof stdClass || is_array($value)) $input[$key] = self::input($value);
        }

        return $input;
    }

    /** Register an ability with Listdom's common metadata. */
    public static function register(string $id, string $label, string $description, array $input, array $output, callable $permission, callable $execute, bool $readonly, bool $destructive = false, bool $rest = true, array $workflow = [], bool $idempotent = false): void
    {
        wp_register_ability($id, [
            'label' => $label,
            'description' => $description,
            'category' => 'listdom',
            'input_schema' => $input,
            'output_schema' => $output,
            'permission_callback' => $permission,
            'execute_callback' => $execute,
            'meta' => [
                'workflow' => $workflow,
                'provider' => 'Listdom',
                'public' => true,
                'show_in_rest' => $rest,
                'annotations' => [
                    'readonly' => $readonly,
                    'destructive' => $destructive,
                    'idempotent' => $readonly || $idempotent,
                ],
            ],
        ]);
    }
}
