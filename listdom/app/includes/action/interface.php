<?php

interface LSD_Action_Interface
{
    public function get_id(): string;
    public function get_label(): string;
    public function get_capability(): string;
    public function get_schema(): array;
    public function is_mutating(): bool;
    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result;
    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result;
}
