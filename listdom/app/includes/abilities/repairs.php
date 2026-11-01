<?php

/** Native provider contracts; workflow metadata is optional for generic consumers. */
class LSD_Abilities_Repairs
{
    public static function relation(string $id, array $mapping): array
    {
        return ['abilityId' => 'listdom/' . $id, 'mapping' => $mapping];
    }

    public static function whole_input(string $id): array
    {
        return self::relation($id, [['to' => '', 'from' => 'input', 'path' => '']]);
    }

    public static function display_workflow(): array
    {
        return ['version' => 1, 'preview' => self::whole_input('preview-search-display-update'),
            'verify' => self::whole_input('verify-search-display-update'),
            'diagnostic' => self::relation('get-search-display-configuration', [['to' => '/view_id', 'from' => 'input', 'path' => '/view_id']])];
    }

    public static function media_workflow(): array
    {
        $job = [['to' => '/job_id', 'from' => 'result', 'path' => '/job_id']];
        return ['version' => 1, 'preview' => self::whole_input('preview-listing-thumbnail-repair'),
            'verify' => self::relation('get-thumbnail-repair-status', $job),
            'status' => self::relation('get-thumbnail-repair-status', $job),
            'cancel' => self::relation('cancel-thumbnail-repair', $job),
            'diagnostic' => self::relation('inspect-listing-media', [['to' => '/listing_id', 'from' => 'input', 'path' => '/listing_id'],
                ['to' => '/per_page', 'value' => 20]])];
    }

    private static function value_schema(int $depth = 3): array
    {
        $schema = ['type' => ['boolean', 'integer', 'number', 'string', 'null'], 'maxLength' => 1000];
        if ($depth > 0)
        {
            $schema['type'][] = 'array';
            $schema['type'][] = 'object';
            $schema += ['items' => self::value_schema($depth - 1), 'additionalProperties' => self::value_schema($depth - 1), 'maxItems' => 100, 'maxProperties' => 20];
        }
        return $schema;
    }

    private static function text_schema(): array
    {
        return ['type' => 'string', 'maxLength' => 1000];
    }

    private static function revision_schema(): array
    {
        return ['type' => 'string', 'pattern' => '^[a-f0-9]{64}$'];
    }

    private static function display_values_schema(): array
    {
        return LSD_Abilities_Schema::object([
            'display_image' => ['type' => 'boolean'],
            'image_method' => self::text_schema(),
            'image_fit' => self::text_schema(),
        ], ['display_image', 'image_method', 'image_fit']);
    }

    private static function verification_schema(): array
    {
        return LSD_Abilities_Schema::object([
            'outcome' => ['type' => 'string', 'enum' => ['fixed', 'partially_fixed', 'not_fixed', 'not_verified']],
            'scope' => self::text_schema(),
        ], ['outcome', 'scope']);
    }

    private static function preview_schema(): array
    {
        $s = LSD_Abilities_Schema::class;
        $field = $s::object([
            'key' => self::text_schema(),
            'label' => self::text_schema(),
            'before' => self::value_schema(),
            'after' => self::value_schema(),
        ], ['key', 'label', 'before', 'after']);
        return $s::object([
            'revision' => self::revision_schema(),
            'preview' => $s::object([
                'title' => self::text_schema(),
                'fields' => $s::list($field, 20),
                'warnings' => $s::list(self::text_schema(), 5),
            ], ['title', 'fields', 'warnings']),
        ], ['preview']);
    }

    private static function job_schema(): array
    {
        $s = LSD_Abilities_Schema::class;
        $item = $s::object([
            'attachment_id' => ['type' => 'integer'],
            'outcome' => ['type' => 'string', 'enum' => ['applied', 'unchanged']],
        ], ['attachment_id', 'outcome']);
        return $s::object([
            'job_id' => ['type' => 'string', 'pattern' => '^[a-f0-9]{40}$'],
            'state' => ['type' => 'string', 'enum' => ['queued', 'running', 'completed', 'failed', 'uncertain', 'cancelled']],
            'processed' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
            'total' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
            'results' => $s::list($item, 20),
            'verification' => self::verification_schema(),
            'limitations' => $s::list(self::text_schema(), 5),
        ], ['job_id', 'state', 'processed', 'total', 'results', 'verification', 'limitations']);
    }

    public static function output_schema(string $name): array
    {
        $s = LSD_Abilities_Schema::class;
        if (strpos($name, 'preview-') === 0) return self::preview_schema();
        if ($name === 'get-search-display-repair-state')
        {
            return $s::object([
                'view_id' => ['type' => 'integer'],
                'skin' => self::text_schema(),
                'values' => self::display_values_schema(),
                'revision' => self::revision_schema(),
            ], ['view_id', 'skin', 'values', 'revision']);
        }
        if ($name === 'update-search-display-configuration')
        {
            return $s::object([
                'outcome' => ['type' => 'string', 'enum' => ['applied', 'unchanged']],
                'values' => self::display_values_schema(),
                'revision' => self::revision_schema(),
            ], ['outcome', 'values', 'revision']);
        }
        if ($name === 'verify-search-display-update')
        {
            return $s::object([
                'verification' => self::verification_schema(),
                'values' => self::display_values_schema(),
                'limitations' => $s::list(self::text_schema(), 5),
            ], ['verification', 'values', 'limitations']);
        }
        return self::job_schema();
    }

    public function permitted($input = null): bool
    {
        return current_user_can('manage_options');
    }

    public function register(): void
    {
        $s = LSD_Abilities_Schema::class;
        $id = ['type' => 'integer', 'minimum' => 1];
        $revision = self::revision_schema();
        $display = $s::object(['view_id' => $id, 'expectedRevision' => $revision,
            'patch' => $s::object(['display_image' => ['type' => 'boolean'],
                'image_method' => ['type' => 'string', 'enum' => ['grid', 'list', 'slider']],
                'image_fit' => ['type' => 'string', 'enum' => ['cover', 'contain']]]) + ['minProperties' => 1, 'maxProperties' => 3]], ['view_id', 'patch', 'expectedRevision']);
        $media = $s::object(['listing_id' => $id,
            'attachment_ids' => ['type' => 'array', 'items' => $id, 'minItems' => 1, 'maxItems' => 20, 'uniqueItems' => true],
            'sizes' => ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => '^[a-zA-Z0-9_-]{1,80}$'], 'minItems' => 1, 'maxItems' => 5, 'uniqueItems' => true],
            'expectedRevision' => $revision], ['listing_id', 'attachment_ids', 'sizes', 'expectedRevision']);
        $preview_media = $media;
        $preview_media['required'] = ['listing_id', 'attachment_ids', 'sizes'];
        $job = $s::object(['job_id' => ['type' => 'string', 'pattern' => '^[a-f0-9]{40}$']], ['job_id']);
        $specs = [
            'get-search-display-repair-state' => [__('Read repairable directory image controls', 'listdom'), $s::object(['view_id' => $id], ['view_id']), true, []],
            'preview-search-display-update' => [__('Preview directory image controls', 'listdom'), $display, true, []],
            'update-search-display-configuration' => [__('Update directory image controls', 'listdom'), $display, false, self::display_workflow()],
            'verify-search-display-update' => [__('Verify saved directory image controls', 'listdom'), $display, true, []],
            'preview-listing-thumbnail-repair' => [__('Preview missing listing thumbnails', 'listdom'), $preview_media, true, []],
            'regenerate-listing-thumbnails' => [__('Generate missing listing thumbnails', 'listdom'), $media, false, self::media_workflow()],
            'get-thumbnail-repair-status' => [__('Read thumbnail repair status', 'listdom'), $job, true, []],
            'cancel-thumbnail-repair' => [__('Cancel pending thumbnail repair', 'listdom'), $job, false, []],
        ];
        foreach ($specs as $name => $spec)
        {
            $s::register('listdom/' . $name, $spec[0], __('Scoped administrator repair service. Preview exact inputs before confirming changes; state verification does not establish rendered appearance.', 'listdom'),
                $spec[1], self::output_schema($name), [$this, 'permitted'], function ($input) use ($name)
                {
                    return $this->execute($name, $input);
                }, $spec[2], false, true, $spec[3]);
        }
    }

    public function execute(string $name, $input)
    {
        if (!$this->permitted()) return LSD_Repairs_Display::error('forbidden');
        $input = LSD_Abilities_Schema::input($input);
        $display = new LSD_Repairs_Display();
        $media = new LSD_Repairs_Media();
        if ($name === 'get-search-display-repair-state')
        {
            $result = $display->read($input['view_id']);
            if (!is_wp_error($result)) unset($result['stored']);
        }
        elseif ($name === 'preview-search-display-update') $result = $display->preview($input);
        elseif ($name === 'update-search-display-configuration') $result = $display->update($input);
        elseif ($name === 'verify-search-display-update') $result = $display->verify($input);
        elseif ($name === 'preview-listing-thumbnail-repair') $result = $media->preview($input);
        elseif ($name === 'regenerate-listing-thumbnails') $result = $media->enqueue($input);
        elseif ($name === 'get-thumbnail-repair-status') $result = $media->status($input);
        else $result = $media->cancel($input);
        if (!is_wp_error($result) && strlen(wp_json_encode($result)) > 32768) return LSD_Repairs_Display::error('result_limit', !$this->is_read($name));
        return $result;
    }

    private function is_read(string $name): bool
    {
        return !in_array($name, ['update-search-display-configuration', 'regenerate-listing-thumbnails', 'cancel-thumbnail-repair'], true);
    }
}
