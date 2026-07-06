<?php

class LSD_Action_Result
{
    protected bool $success;
    protected string $action_id;
    protected string $code;
    protected string $message;
    protected array $data;
    protected array $warnings;
    protected array $errors;
    protected array $meta;

    public function __construct(
        bool $success,
        string $action_id,
        string $code = '',
        string $message = '',
        array $data = [],
        array $warnings = [],
        array $errors = [],
        array $meta = []
    ) {
        $this->success = $success;
        $this->action_id = $action_id;
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->warnings = $warnings;
        $this->errors = $errors;
        $this->meta = $meta;
    }

    public static function success(string $action_id, string $message = '', array $data = [], array $warnings = [], array $meta = []): self
    {
        return new self(true, $action_id, 'ok', $message, $data, $warnings, [], $meta);
    }

    public static function failure(string $action_id, string $code, string $message, array $errors = [], array $data = [], array $meta = []): self
    {
        return new self(false, $action_id, $code, $message, $data, [], $errors, $meta);
    }

    public function is_success(): bool
    {
        return $this->success;
    }

    public function get_data(): array
    {
        return $this->data;
    }

    public function get_meta(string $key = null, $default = null)
    {
        if ($key === null) return $this->meta;
        return $this->meta[$key] ?? $default;
    }

    public function set_meta(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function add_warning(string $warning): self
    {
        if ($warning !== '') $this->warnings[] = $warning;
        return $this;
    }

    public function add_error(string $error): self
    {
        if ($error !== '') $this->errors[] = $error;
        return $this;
    }

    public function to_array(): array
    {
        return [
            'success' => $this->success,
            'action' => $this->action_id,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'meta' => $this->meta,
        ];
    }
}
