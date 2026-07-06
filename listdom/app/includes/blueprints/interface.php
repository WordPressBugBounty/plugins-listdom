<?php

interface LSD_Blueprints_Interface
{
    public function get_id(): string;
    public function get_label(): string;
    public function get_description(): string;
    public function definition(array $options = []): array;
}
