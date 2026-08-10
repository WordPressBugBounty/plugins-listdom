<?php

class LSD_Checklist_Result
{
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_WARNING = 'warning';
    public const STATUS_OPTIONAL = 'optional';
    public const STATUS_IGNORED = 'ignored';

    protected array $data = [];

    public function __construct(array $data)
    {
        $defaults = [
            'id' => '',
            'title' => '',
            'description' => '',
            'category' => '',
            'importance' => 'medium',
            'status' => self::STATUS_INCOMPLETE,
            'message' => '',
            'action_label' => '',
            'action_url' => '',
            'action_focus_target' => '',
            'admin_location' => '',
            'optional' => false,
            'skipped' => false,
        ];

        $this->data = array_merge($defaults, $data);
    }

    public function to_array(): array
    {
        return $this->data;
    }

    public function get_status(): string
    {
        return $this->data['status'];
    }

    public function is_complete(): bool
    {
        return $this->get_status() === self::STATUS_COMPLETE;
    }

    public function is_optional(): bool
    {
        return !empty($this->data['optional']);
    }

    public function is_skipped(): bool
    {
        return !empty($this->data['skipped']);
    }
}
