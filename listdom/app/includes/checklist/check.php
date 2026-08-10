<?php

class LSD_Checklist_Check implements LSD_Checklist_Check_Interface
{
    protected array $config = [];
    /** @var callable */
    protected $resolver;

    public function __construct(array $config, callable $resolver)
    {
        $defaults = [
            'id' => '',
            'title' => '',
            'description' => '',
            'category' => '',
            'importance' => 'medium',
            'optional' => false,
            'action_label' => '',
            'action_url' => '',
            'action_focus_target' => '',
            'admin_location' => '',
        ];

        $this->config = array_merge($defaults, $config);
        $this->resolver = $resolver;
    }

    public function get_id(): string
    {
        return (string) $this->config['id'];
    }

    public function get_title(): string
    {
        return (string) $this->config['title'];
    }

    public function get_description(): string
    {
        return (string) $this->config['description'];
    }

    public function get_category(): string
    {
        return (string) $this->config['category'];
    }

    public function get_importance(): string
    {
        return (string) $this->config['importance'];
    }

    public function is_optional(): bool
    {
        return (bool) $this->config['optional'];
    }

    public function get_action_label(): string
    {
        return (string) $this->config['action_label'];
    }

    public function get_action_url(): string
    {
        return (string) $this->config['action_url'];
    }

    public function get_admin_location(): string
    {
        return (string) $this->config['admin_location'];
    }

    public function evaluate(): LSD_Checklist_Result
    {
        $resolved = call_user_func($this->resolver, $this);
        $resolved = is_array($resolved) ? $resolved : [];

        return new LSD_Checklist_Result(array_merge($this->config, $resolved));
    }
}
