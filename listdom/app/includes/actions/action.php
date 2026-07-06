<?php

abstract class LSD_Actions_Action extends LSD_Base implements LSD_Action_Interface
{
    public function is_mutating(): bool
    {
        return true;
    }

    protected function success(string $message = '', array $data = [], array $warnings = [], array $meta = []): LSD_Action_Result
    {
        return LSD_Action_Result::success($this->get_id(), $message, $data, $warnings, $meta);
    }

    protected function failure(string $code, string $message, array $errors = [], array $data = [], array $meta = []): LSD_Action_Result
    {
        return LSD_Action_Result::failure($this->get_id(), $code, $message, $errors, $data, $meta);
    }

    protected function validated(array $input, array $warnings = [], array $data = []): LSD_Action_Result
    {
        return $this->success('', $data, $warnings, ['input' => $input]);
    }

    protected function validate_schema(array $input): array
    {
        return LSD_Action_Validator::validate($this->get_schema(), $input);
    }

    protected function set_nested_option(string $option_name, string $path, $value): bool
    {
        $option = get_option($option_name, []);
        if (!is_array($option)) $option = [];

        $keys = array_values(array_filter(array_map('trim', explode('.', $path))));
        if (!count($keys)) return false;

        $pointer = &$option;
        foreach ($keys as $index => $key)
        {
            if ($index === count($keys) - 1)
            {
                $pointer[$key] = $value;
                break;
            }

            if (!isset($pointer[$key]) || !is_array($pointer[$key])) $pointer[$key] = [];
            $pointer = &$pointer[$key];
        }

        return update_option($option_name, $option);
    }

    protected function mark_post(int $post_id, LSD_Action_Context $context): void
    {
        if ($context->source() !== 'blueprint' || $post_id <= 0) return;

        $blueprint_id = (string) $context->get('blueprint_id', '');
        if ($blueprint_id === '') return;

        update_post_meta($post_id, 'lsd_blueprint_id', $blueprint_id);
        update_post_meta($post_id, 'lsd_blueprint_application_id', (string) $context->get('application_id', ''));
        update_post_meta($post_id, 'lsd_action_source', $context->source());
        update_post_meta($post_id, 'lsd_action_id', $this->get_id());
    }

    protected function mark_term(int $term_id, LSD_Action_Context $context): void
    {
        if ($context->source() !== 'blueprint' || $term_id <= 0) return;

        $blueprint_id = (string) $context->get('blueprint_id', '');
        if ($blueprint_id === '') return;

        update_term_meta($term_id, 'lsd_blueprint_id', $blueprint_id);
        update_term_meta($term_id, 'lsd_blueprint_application_id', (string) $context->get('application_id', ''));
        update_term_meta($term_id, 'lsd_action_source', $context->source());
        update_term_meta($term_id, 'lsd_action_id', $this->get_id());
    }
}
