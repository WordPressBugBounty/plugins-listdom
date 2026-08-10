<?php

interface LSD_Checklist_Check_Interface
{
    public function get_id(): string;
    public function get_title(): string;
    public function get_description(): string;
    public function get_category(): string;
    public function get_importance(): string;
    public function is_optional(): bool;
    public function get_action_label(): string;
    public function get_action_url(): string;
    public function get_admin_location(): string;
    public function evaluate(): LSD_Checklist_Result;
}
