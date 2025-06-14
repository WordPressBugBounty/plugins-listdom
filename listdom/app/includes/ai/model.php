<?php

interface LSD_AI_Model
{
    public function key(): string;

    public function auto_mapping(array $listdom_fields, array $source_fields): array;
}
