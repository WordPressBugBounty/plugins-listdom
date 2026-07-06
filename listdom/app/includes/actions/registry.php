<?php

class LSD_Actions_Registry extends LSD_Base
{
    protected array $actions = [];

    public function __construct()
    {
        $this->register(new LSD_Actions_Create_Category());
        $this->register(new LSD_Actions_Create_Custom_Field());
        $this->register(new LSD_Actions_Create_Search_Form());
        $this->register(new LSD_Actions_Create_Directory_Page());
        $this->register(new LSD_Actions_Create_Demo_Listing());
        $this->register(new LSD_Actions_Audit_Directory_Setup());
    }

    public function register(LSD_Action_Interface $action): void
    {
        $this->actions[$action->get_id()] = $action;
    }

    public function get(string $action_id): ?LSD_Action_Interface
    {
        return $this->actions[$action_id] ?? null;
    }

    public function all(): array
    {
        return $this->actions;
    }
}
