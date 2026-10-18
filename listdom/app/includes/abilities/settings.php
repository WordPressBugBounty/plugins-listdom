<?php

class LSD_Abilities_Settings
{
    private static function object(array $properties, ?array $required = null): array
    {
        $schema = ['type' => 'object', 'additionalProperties' => false];
        if ($properties) $schema['properties'] = $properties;
        $required = $required ?? array_keys($properties);
        if ($required) $schema['required'] = $required;
        return $schema;
    }

    private static function array_of(array $items, int $max = 20): array
    {
        return ['type' => 'array', 'items' => $items, 'maxItems' => $max];
    }

    private static function scalar(): array
    {
        return ['type' => ['string', 'integer', 'boolean', 'null'], 'maxLength' => 150];
    }

    private static function field_schema(): array
    {
        $string = ['type' => 'string'];
        $properties = [
            'key' => $string, 'label' => $string, 'description' => $string, 'group' => $string,
            'allowedValues' => self::array_of(self::scalar()), 'type' => ['type' => 'string', 'enum' => ['integer', 'string', 'limit']],
            'minimum' => ['type' => ['integer', 'null']], 'maximum' => ['type' => ['integer', 'null']], 'defaultValue' => self::scalar(), 'effectiveValue' => self::scalar(),
            'available' => ['type' => 'boolean'], 'readable' => ['type' => 'boolean'], 'writable' => ['type' => 'boolean'],
            'risk' => ['type' => 'string', 'enum' => ['configuration']], 'dependencies' => self::array_of($string),
            'tab' => $string, 'subtab' => $string, 'adminUrl' => $string,
        ];
        $required = array_keys($properties);
        $properties['storedPresent'] = ['type' => 'boolean'];
        $properties['storedValue'] = self::scalar();
        return self::object($properties, $required);
    }

    public function register(): void
    {
        $catalog = new LSD_Settings_Catalog();
        $empty = self::object([]) + ['default' => (object) []];
        $revision = ['type' => 'string', 'pattern' => '^[a-f0-9]{64}$'];
        $fields = self::array_of(self::field_schema());
        $list_input = self::object([
            'group' => ['type' => 'string', 'enum' => ['', 'dashboard', 'submission', 'gallery', 'map']],
            'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000],
            'perPage' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
        ], []) + ['default' => (object) []];
        $get_input = self::object([
            'keys' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 20, 'uniqueItems' => true, 'items' => ['type' => 'string', 'enum' => array_keys($catalog->definitions())]],
            'includeStored' => ['type' => 'boolean'],
        ], ['keys']);
        $change = self::object(['key' => ['type' => 'string'], 'before' => self::scalar(), 'requested' => self::scalar(), 'current' => self::scalar(),
            'verified' => ['type' => 'boolean'], 'errors' => self::array_of(['type' => 'string'])]);
        $schemas = [
            'listdom/list-settings' => [__('List Listdom settings', 'listdom'), __('Discover approved Listdom settings, live effective values, constraints, dependencies and admin locations. Filter by group and page.', 'listdom'), $list_input,
                self::object(['settings' => $fields, 'revision' => $revision, 'page' => ['type' => 'integer'], 'perPage' => ['type' => 'integer'], 'total' => ['type' => 'integer'], 'hasMore' => ['type' => 'boolean']])],
            'listdom/get-settings' => [__('Read Listdom settings', 'listdom'), __('Read requested approved Listdom settings by exact key, with optional stored/default distinctions and an opaque revision for conditional updates. No arbitrary options or credentials.', 'listdom'), $get_input, self::object(['settings' => $fields, 'revision' => $revision])],
            'listdom/update-settings' => [__('Update Listdom settings', 'listdom'), __('Apply a patch of supported settings with expectedRevision from listdom/get-settings or listdom/list-settings. Changed settings or modules reject the update. Zero or empty limits mean no Listdom restriction.', 'listdom'),
                self::object(['patch' => $catalog->patch_schema(), 'expectedRevision' => $revision]), self::object(['outcome' => ['type' => 'string', 'enum' => ['applied', 'noop']], 'verified' => ['type' => 'boolean', 'enum' => [true]], 'fields' => self::array_of($change)])],
            'listdom/audit-directory-setup' => [__('Audit Listdom directory setup', 'listdom'), __('Run the existing Listdom directory setup audit and return bounded summary counts and actionable findings.', 'listdom'), $empty, $this->audit_schema()],
            'listdom/get-dashboard-status' => [__('Inspect Listdom dashboard', 'listdom'), __('Inspect the assigned frontend dashboard page, published/shortcode readiness, form modules and restrictions. Does not create or assign pages.', 'listdom'), $empty, $this->dashboard_schema()],
        ];
        foreach ($schemas as $id => $definition)
        {
            $read = $id !== 'listdom/update-settings';
            wp_register_ability($id, [
                'label' => $definition[0], 'description' => $definition[1], 'category' => 'listdom',
                'input_schema' => $definition[2], 'output_schema' => $definition[3],
                'permission_callback' => [LSD_Settings_Service::class, 'permitted'],
                'execute_callback' => function ($input = null) use ($id)
                {
                    if (!LSD_Settings_Service::permitted()) return new WP_Error('listdom_forbidden', __('Listdom settings require manage_options.', 'listdom'));
                    // Native schema defaults are JSON objects, while the shared
                    // services consume associative PHP arrays.
                    if ($input instanceof stdClass) $input = (array) $input;
                    if (!is_array($input)) return new WP_Error('listdom_invalid_input', __('Expected a settings request object.', 'listdom'));
                    $service = new LSD_Settings_Service();
                    switch ($id)
                    {
                        case 'listdom/list-settings': $result = $service->list_settings($input); break;
                        case 'listdom/get-settings': $result = $service->get_settings($input); break;
                        case 'listdom/update-settings': $result = $service->update($input); break;
                        case 'listdom/audit-directory-setup': $result = $this->audit(); break;
                        default: $result = $this->dashboard();
                    }
                    if (is_wp_error($result)) return $result;
                    $json = wp_json_encode($result);
                    return false === $json || strlen($json) > 32768 ? new WP_Error('listdom_result_limit', __('The result is too large. Request fewer settings.', 'listdom')) : $result;
                },
                'meta' => ['provider' => 'Listdom', 'public' => true, 'show_in_rest' => $read, 'annotations' => ['readonly' => $read, 'destructive' => false, 'idempotent' => $read]],
            ]);
        }
    }

    private function audit_schema(): array
    {
        $summary = [];
        foreach (['listings', 'categories', 'custom_fields', 'search_forms', 'directory_views', 'payments_enabled'] as $key) $summary[$key] = ['type' => 'integer'];
        return self::object(['summary' => self::object($summary), 'findings' => self::array_of(self::object([
            'type' => ['type' => 'string'], 'code' => ['type' => 'string'], 'target' => ['type' => 'string'], 'message' => ['type' => 'string'],
        ]), 50), 'healthy' => ['type' => 'boolean']]);
    }

    private function audit()
    {
        $result = LSD_Actions::instance()->execute('audit_directory_setup', [], ['user_id' => get_current_user_id(), 'source' => 'abilities']);
        if (empty($result['success'])) return new WP_Error('listdom_audit_failed', __('The directory audit could not be completed.', 'listdom'));
        $data = $result['data'] ?? [];
        $summary = [];
        foreach (['listings', 'categories', 'custom_fields', 'search_forms', 'directory_views', 'payments_enabled'] as $key) $summary[$key] = (int) ($data['summary'][$key] ?? 0);
        $findings = [];
        foreach (array_slice($data['findings'] ?? [], 0, 50) as $finding)
        {
            $entry = [];
            foreach (['type', 'code', 'target', 'message'] as $key) $entry[$key] = mb_substr(sanitize_text_field($finding[$key] ?? ''), 0, 300);
            $findings[] = $entry;
        }
        return ['summary' => $summary, 'findings' => $findings, 'healthy' => !empty($data['healthy'])];
    }

    private function dashboard_schema(): array
    {
        $value = self::object(['key' => ['type' => 'string'], 'value' => self::scalar(), 'available' => ['type' => 'boolean']]);
        return self::object(['submissionPage' => ['type' => 'integer'], 'exists' => ['type' => 'boolean'], 'published' => ['type' => 'boolean'],
            'shortcodeReady' => ['type' => 'boolean'], 'ready' => ['type' => 'boolean'], 'pageStatus' => ['type' => 'string'],
            'configuration' => self::array_of($value, 40), 'warnings' => self::array_of(['type' => 'string'], 40)]);
    }

    private function dashboard()
    {
        $snapshot = (new LSD_Settings_Service())->snapshot();
        if (is_wp_error($snapshot)) return $snapshot;
        $status = (new LSD_Checklist_Helper())->dashboard_status();
        $catalog = new LSD_Settings_Catalog();
        $effective = $snapshot['effective'];
        $configuration = [];
        $warnings = $status['ready'] ? [] : [__('Assign a published page containing the Listdom dashboard shortcode or supported builder widget.', 'listdom')];
        foreach ($catalog->definitions() as $key => $field)
        {
            if (!in_array($field['group'], ['dashboard', 'submission'], true)) continue;
            $value = $catalog->value($effective, $field);
            $configuration[] = ['key' => $key, 'value' => $value, 'available' => $field['available']];
            if ($value === null) $warnings[] = sprintf(__('Review the stored value for %s.', 'listdom'), $key);
        }
        return $status + ['configuration' => $configuration, 'warnings' => $warnings];
    }
}
