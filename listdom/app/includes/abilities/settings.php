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
        return ['type' => ['string', 'integer', 'number', 'boolean', 'null'], 'maxLength' => 150];
    }

    /** Bounded JSON values for explicitly scoped, structured configuration. */
    private static function setting_value(int $depth = 2): array
    {
        $types = ['string', 'integer', 'number', 'boolean', 'null'];
        $schema = ['type' => $types, 'maxLength' => 512];
        if ($depth > 0)
        {
            $schema['type'][] = 'array';
            $schema['type'][] = 'object';
            $schema += ['items' => self::setting_value($depth - 1), 'maxItems' => 50,
                'additionalProperties' => self::setting_value($depth - 1), 'maxProperties' => 50];
        }
        return $schema;
    }

    private static function field_schema(): array
    {
        $string = ['type' => 'string'];
        $properties = [
            'key' => $string, 'label' => $string, 'description' => $string, 'group' => $string,
            // Choice metadata (for example currencies) is independent of the 20-field page limit.
            'allowedValues' => self::array_of(self::scalar(), 512), 'type' => ['type' => 'string', 'enum' => ['integer', 'string', 'limit', 'number', 'boolean', 'array', 'object', 'union']],
            'minimum' => ['type' => ['number', 'null']], 'maximum' => ['type' => ['number', 'null']], 'defaultValue' => self::setting_value(), 'effectiveValue' => self::setting_value(),
            'available' => ['type' => 'boolean'], 'readable' => ['type' => 'boolean'], 'writable' => ['type' => 'boolean'],
            'risk' => ['type' => 'string', 'enum' => ['configuration']], 'dependencies' => self::array_of($string),
            'tab' => $string, 'subtab' => $string, 'adminUrl' => $string,
        ];
        $required = array_keys($properties);
        $properties['storedPresent'] = ['type' => 'boolean'];
        $properties['storedValue'] = self::setting_value();
        $properties += [
            'domain' => ['type' => 'string', 'maxLength' => 80], 'owner' => ['type' => 'string', 'maxLength' => 100],
            'addon' => ['type' => 'string', 'maxLength' => 80], 'path' => self::array_of(['type' => 'string', 'maxLength' => 191], 12),
            'schema' => ['type' => 'object', 'maxProperties' => 20],
            'valueStatus' => ['type' => 'string', 'enum' => ['valid', 'invalid', 'unavailable']],
            'writeReason' => ['type' => 'string', 'enum' => ['', 'domain_writer_not_available', 'read_only', 'status_only', 'permission', 'dependency']],
            'permissions' => self::array_of(['type' => 'string', 'maxLength' => 100]),
            'sensitivity' => ['type' => 'string', 'enum' => ['configuration', 'status_only']],
        ];
        return self::object($properties, $required);
    }

    public function register(): void
    {
        $catalog = new LSD_Settings_Catalog();
        $empty = self::object([]) + ['default' => (object) []];
        $revision = ['type' => 'string', 'pattern' => '^[a-f0-9]{64}$'];
        $fields = self::array_of(self::field_schema());
        $domain = ['type' => 'string', 'maxLength' => 80, 'pattern' => '^(\\*|(core|addons)\\.[a-z0-9_-]+)$',
            'description' => 'Omit for the legacy settings view. Use core.settings, core.customizer, core.details_page, core.auth, core.payments, core.socials, core.styles, core.ai or core.api; * lists all registered domains. Exact domain revisions authorize only that domain’s approved writes.'];
        $list_input = self::object([
            'group' => ['type' => 'string', 'maxLength' => 80],
            'domain' => $domain, 'addon' => ['type' => 'string', 'maxLength' => 80, 'description' => 'Registered add-on slug; requires an explicit domain or *.'],
            'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000],
            'perPage' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
        ], []) + ['default' => (object) []];
        $get_input = self::object([
            'keys' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 20, 'uniqueItems' => true, 'items' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 191]],
            'includeStored' => ['type' => 'boolean'],
            'domain' => $domain,
        ], ['keys']);
        $list_properties = ['settings' => $fields, 'revision' => $revision, 'page' => ['type' => 'integer'], 'perPage' => ['type' => 'integer'], 'total' => ['type' => 'integer'], 'hasMore' => ['type' => 'boolean']];
        $get_properties = ['settings' => $fields, 'revision' => $revision];
        $scoped = ['domain' => ['type' => 'string'], 'readOnly' => ['type' => 'boolean']];
        $change = self::object(['key' => ['type' => 'string'], 'before' => self::setting_value(), 'requested' => self::setting_value(), 'current' => self::setting_value(),
            'verified' => ['type' => 'boolean'], 'errors' => self::array_of(['type' => 'string'])]);
        $schemas = [
            'listdom/list-settings' => [__('List Listdom settings', 'listdom'), __('Discover settings, values and constraints. Omit domain for the legacy view; domain=* discovers all registered core/add-on domains. Filter by domain, addon, group and page. Exact domain revisions authorize only that domain’s approved writes.', 'listdom'), $list_input,
                self::object($list_properties + $scoped, array_keys($list_properties))],
            'listdom/get-settings' => [__('Read Listdom settings', 'listdom'), __('Read up to 20 registered keys. Omit domain for legacy settings/revisions; supply one exact domain for expanded configuration and its scoped revision. Discover keys with list-settings. No raw options or credentials.', 'listdom'), $get_input, self::object($get_properties + $scoped, array_keys($get_properties))],
            'listdom/update-settings' => [__('Update Listdom settings', 'listdom'), __('Apply up to 20 approved changes with expectedRevision. Omit domain for the original settings contract; supply one concrete domain and its fresh read revision for expanded writes. Discovery revisions cannot authorize writes. Dependencies are evaluated against the complete proposed configuration. Confirm exact inputs before execution.', 'listdom'),
                ['type' => 'object', 'required' => ['patch', 'expectedRevision'], 'oneOf' => [
                    self::object(['patch' => $catalog->patch_schema(), 'expectedRevision' => $revision]),
                    self::object(['domain' => ['type' => 'string', 'maxLength' => 80, 'pattern' => '^(core|addons)\\.[a-z0-9_-]+$'],
                        'patch' => ['type' => 'object', 'minProperties' => 1, 'maxProperties' => 20, 'additionalProperties' => self::setting_value()],
                        'expectedRevision' => $revision]),
                ]],
                self::object(['outcome' => ['type' => 'string', 'enum' => ['applied', 'noop', 'unchanged']], 'verified' => ['type' => 'boolean', 'enum' => [true]],
                    'fields' => self::array_of($change), 'domain' => ['type' => 'string'], 'revision' => $revision], ['outcome', 'verified', 'fields'])],
            'listdom/audit-directory-setup' => [__('Audit Listdom directory setup', 'listdom'), __('Run the existing Listdom directory setup audit and return bounded summary counts and actionable findings.', 'listdom'), $empty, $this->audit_schema()],
            'listdom/get-dashboard-status' => [__('Inspect Listdom dashboard', 'listdom'), __('Inspect the assigned frontend dashboard page, published/shortcode readiness, form modules and restrictions. Does not create or assign pages.', 'listdom'), $empty, $this->dashboard_schema()],
        ];
        $update_input = $schemas['listdom/update-settings'][2];
        LSD_Abilities_Schema::register('listdom/preview-settings-update', __('Preview Listdom settings changes', 'listdom'), __('Validate exact settings inputs without saving.', 'listdom'), $update_input, ['type' => 'object'], [LSD_Settings_Service::class, 'permitted'], static function ($input) { return (new LSD_Settings_Service())->update(LSD_Abilities_Schema::input($input), true); }, true);
        LSD_Abilities_Schema::register('listdom/verify-settings-update', __('Verify Listdom settings changes', 'listdom'), __('Independently compare requested values with current effective settings.', 'listdom'), $update_input, ['type' => 'object'], [LSD_Settings_Service::class, 'permitted'], static function ($raw)
        {
            $input = LSD_Abilities_Schema::input($raw);
            $read = ['keys' => array_keys($input['patch'])];
            if (isset($input['domain'])) $read['domain'] = $input['domain'];
            $result = (new LSD_Settings_Service())->get_settings($read);
            if (is_wp_error($result)) return $result;
            $matched = 0;
            foreach ($result['settings'] as $field) if (LSD_Settings_Writer::equal($field['effectiveValue'], $input['patch'][$field['key']])) $matched++;
            return ['verification' => ['outcome' => $matched === count($input['patch']) ? 'fixed' : ($matched ? 'partially_fixed' : 'not_fixed'), 'scope' => 'effective_configuration'], 'settings' => $result['settings']];
        }, true);
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
                'meta' => ['workflow' => $id === 'listdom/update-settings' ? ['version' => 1, 'preview' => LSD_Abilities_Repairs::whole_input('preview-settings-update'), 'verify' => LSD_Abilities_Repairs::whole_input('verify-settings-update')] : [], 'provider' => 'Listdom', 'public' => true, 'show_in_rest' => $read, 'annotations' => ['readonly' => $read, 'destructive' => false, 'idempotent' => $read]],
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
