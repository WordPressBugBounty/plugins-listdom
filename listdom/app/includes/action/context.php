<?php

class LSD_Action_Context extends LSD_Base
{
    protected array $args = [];

    public function __construct(array $args = [])
    {
        $this->args = wp_parse_args($args, [
            'user_id' => get_current_user_id(),
            'dry_run' => false,
            'approved' => false,
            'require_approval' => false,
            'source' => 'internal',
            'request_id' => wp_generate_uuid4(),
        ]);

        $this->args['user_id'] = (int) $this->args['user_id'];
        $this->args['dry_run'] = (bool) $this->args['dry_run'];
        $this->args['approved'] = (bool) $this->args['approved'];
        $this->args['require_approval'] = (bool) $this->args['require_approval'];
        $this->args['source'] = sanitize_key((string) $this->args['source']);
        $this->args['request_id'] = sanitize_text_field((string) $this->args['request_id']);
        if (isset($this->args['blueprint_id'])) $this->args['blueprint_id'] = sanitize_key((string) $this->args['blueprint_id']);
        if (isset($this->args['application_id'])) $this->args['application_id'] = sanitize_text_field((string) $this->args['application_id']);
    }

    public function id(): string
    {
        return $this->args['request_id'];
    }

    public function user_id(): int
    {
        return $this->args['user_id'];
    }

    public function source(): string
    {
        return $this->args['source'];
    }

    public function is_dry_run(): bool
    {
        return $this->args['dry_run'];
    }

    public function is_approved(): bool
    {
        return $this->args['approved'];
    }

    public function requires_approval(): bool
    {
        return $this->args['require_approval'];
    }

    public function can(string $capability): bool
    {
        $user_id = $this->user_id();
        return $user_id > 0 && user_can($user_id, $capability);
    }

    public function to_array(): array
    {
        return $this->args;
    }

    public function get(string $key, $default = null)
    {
        return $this->args[$key] ?? $default;
    }
}
