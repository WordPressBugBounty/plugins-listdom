<?php

class LSD_Blueprints_Registry extends LSD_Base
{
    protected array $items = [];

    public function __construct()
    {
        $this->register(new LSD_Blueprints_Business_Directory());
        $this->register(new LSD_Blueprints_City_Portal());
        $this->register(new LSD_Blueprints_Service_Marketplace());

        $this->items = apply_filters('lsd_blueprints_registry', $this->items, $this);
    }

    public function register(LSD_Blueprints_Interface $blueprint): void
    {
        $this->items[$blueprint->get_id()] = $blueprint;
    }

    public function get(string $blueprint_id): ?LSD_Blueprints_Interface
    {
        return $this->items[$blueprint_id] ?? null;
    }

    public function all(): array
    {
        return $this->items;
    }
}
