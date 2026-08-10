<?php

abstract class LSD_Checklist_Provider_Base implements LSD_Checklist_Provider_Interface
{
    protected LSD_Checklist_Helper $helper;

    public function __construct(LSD_Checklist_Helper $helper)
    {
        $this->helper = $helper;
    }

    protected function category(string $label): string
    {
        return $label;
    }

    protected function check(array $config, callable $resolver): LSD_Checklist_Check
    {
        return new LSD_Checklist_Check($config, Closure::fromCallable($resolver));
    }

    protected function register_checks(LSD_Checklist_Registry $registry, array $checks): void
    {
        foreach ($checks as $check)
        {
            $registry->register($check);
        }
    }

    protected function addon_active(string $key): bool
    {
        $addons = LSD_Base::addons();

        return isset($addons[$key]);
    }

    protected function addon_action_url(string $key): string
    {
        if (!$this->addon_active($key)) return admin_url('admin.php?page=listdom-addons');

        return admin_url('admin.php?page=listdom-settings&tab=addons&subtab=' . rawurlencode($key));
    }
}
