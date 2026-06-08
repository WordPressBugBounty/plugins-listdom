<?php

interface LSD_AI_Model
{
    public function key(): string;

    public function auto_mapping(array $listdom_fields, array $source_fields): array;

    public function structured_search(string $query, array $schema): array;

    public function supports_embeddings(): bool;

    public function embedding(string $text, string $task = 'document'): array;

    public function availability(string $text): array;

    public function content(string $text): string;
}
