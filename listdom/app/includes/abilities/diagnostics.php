<?php

/** Thin native adapters; domain services own all diagnostic behavior. */
class LSD_Abilities_Diagnostics
{
    public function register(): void
    {
        $labels = [
            'get-search-display-configuration' => [__('Inspect Listdom search display configuration', 'listdom'), __('Supply either a saved view_id or a local page_id to resolve views and report first-party image presentation without rendering a page. A page URL alone is not an ID.', 'listdom')],
            'inspect-listing-media' => [__('Inspect Listdom listing media', 'listdom'), __('Inspect a page of attachment records, metadata and local upload files without fetching URLs.', 'listdom')],
            'diagnose-listing-images' => [__('Diagnose Listdom listing images', 'listdom'), __('Correlate saved display configuration with media evidence, identifying limitations requiring browser inspection.', 'listdom')],
            'diagnose-listing-visibility' => [__('Diagnose Listdom listing visibility', 'listdom'), __('Evaluate anonymous-visitor query eligibility and deterministic known add-on rules without changing listing state.', 'listdom')],
            'get-frontend-submission-diagnostics' => [__('Inspect Listdom frontend submission', 'listdom'), __('Inspect saved form readiness, core restrictions, module availability and server upload configuration.', 'listdom')],
        ];
        foreach ($labels as $name => $label)
        {
            LSD_Abilities_Schema::register('listdom/' . $name, $label[0], $label[1], self::input_schema($name), self::output_schema(),
                [$this, 'permitted'], function ($input) use ($name) { return $this->execute($name, $input); }, true);
        }
    }

    public function permitted($input = null): bool
    {
        return get_current_user_id() > 0 && current_user_can('manage_options');
    }

    public static function input_schema(string $name): array
    {
        $id = ['type' => 'integer', 'minimum' => 1];
        $properties = [];
        $required = [];
        if ($name !== 'get-frontend-submission-diagnostics' && $name !== 'get-search-display-configuration')
        {
            $properties['listing_id'] = $id;
            $required[] = 'listing_id';
        }
        if (in_array($name, ['inspect-listing-media', 'diagnose-listing-images'], true))
        {
            $properties['page'] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 10000, 'default' => 1];
            $properties['per_page'] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 20, 'default' => 5];
        }
        if (in_array($name, ['get-search-display-configuration', 'diagnose-listing-images', 'diagnose-listing-visibility'], true))
        {
            $properties['view_id'] = $id;
            $properties['page_id'] = $id;
            $properties['reference'] = ['type' => 'string', 'pattern' => '^[a-f0-9]{32}$', 'maxLength' => 32];
        }
        if ($name === 'diagnose-listing-visibility') $properties['search'] = LSD_Abilities_Search::input_schema();
        $schema = LSD_Abilities_Schema::object($properties, $required);
        if (isset($properties['view_id']))
        {
            // Advertise the same exclusive target requirement enforced by the resolver.
            $schema['oneOf'] = [
                ['type' => 'object', 'required' => ['view_id']],
                ['type' => 'object', 'required' => ['page_id']],
            ];
        }
        return $schema;
    }

    public static function output_schema(): array
    {
        $s = LSD_Abilities_Schema::class;
        $text = ['type' => 'string'];
        $fact = $s::object(['reference' => $text, 'key' => $text,
            'value' => ['type' => ['string', 'integer', 'number', 'boolean', 'null']]], ['reference', 'key', 'value']);
        $finding = $s::object(['code' => $text, 'severity' => ['type' => 'string', 'enum' => ['info', 'warning', 'error']],
            'message' => $text, 'evidence' => $s::list($text, 20)], ['code', 'severity', 'message', 'evidence']);
        $limitation = $s::object(['code' => $text, 'message' => $text], ['code', 'message']);
        $candidate = $s::object(['selector' => $text, 'view_id' => ['type' => 'integer'], 'source' => $text], ['selector', 'view_id', 'source']);
        return $s::object([
            'status' => ['type' => 'string', 'enum' => ['completed', 'partial', 'ambiguous', 'unsupported']],
            'scope' => $text, 'audience' => ['type' => 'string', 'enum' => ['anonymous']],
            'facts' => $s::list($fact, null), 'findings' => $s::list($finding, null),
            'limitations' => $s::list($limitation, null), 'candidates' => $s::list($candidate, 20),
            'pagination' => $s::object(['page' => ['type' => 'integer'], 'per_page' => ['type' => 'integer'],
                'total' => ['type' => 'integer'], 'has_more' => ['type' => 'boolean']], ['page', 'per_page', 'total', 'has_more']),
        ], ['status', 'scope', 'audience', 'facts', 'findings', 'limitations', 'candidates']);
    }

    /** Direct callers receive the same authorization and bounds as native execution. */
    public function execute(string $name, $input)
    {
        if (!$this->permitted()) return LSD_Diagnostics_Report::error('forbidden', __('Administrator access is required.', 'listdom'));
        $input = LSD_Abilities_Schema::input($input);
        if (strlen((string) wp_json_encode($input)) > 32768)
            return LSD_Diagnostics_Report::error('invalid_input', __('Diagnostic input exceeds 32 KiB.', 'listdom'));
        if (function_exists('rest_validate_value_from_schema'))
        {
            $valid = rest_validate_value_from_schema($input, self::input_schema($name));
            if (is_wp_error($valid)) return LSD_Diagnostics_Report::error('invalid_input', __('Diagnostic input does not match the registered contract.', 'listdom'));
        }
        $report = new LSD_Diagnostics_Report($name);
        $restore_metadata = LSD_Diagnostics_Report::suspend_thumbnail_fallback();
        try
        {
            if ($name === 'get-frontend-submission-diagnostics') $result = (new LSD_Diagnostics_Submission())->inspect($report);
            elseif ($name === 'inspect-listing-media') $result = (new LSD_Diagnostics_Media())->inspect($input, $report);
            elseif (in_array($name, ['get-search-display-configuration', 'diagnose-listing-images', 'diagnose-listing-visibility'], true))
            {
                // Authorize the listing even when page discovery is ambiguous.
                if ($name !== 'get-search-display-configuration')
                {
                    $resource = LSD_Diagnostics_Report::resource((int) ($input['listing_id'] ?? 0), [LSD_Base::PTYPE_LISTING]);
                    if (is_wp_error($resource)) return $resource;
                }
                $candidate = (new LSD_Diagnostics_References($report))->resolve($input);
                if (is_wp_error($candidate)) return $candidate;
                if (!$candidate) return $report->result();
                $input['view_id'] = $candidate['view_id'];
                $display = (new LSD_Diagnostics_Display())->inspect($candidate, $report);
                if (is_wp_error($display)) return $display;
                if ($display === null) return $report->result();
                $result = true;
                if ($name === 'diagnose-listing-images') $result = (new LSD_Diagnostics_Media())->inspect($input, $report, $display);
                elseif ($name === 'diagnose-listing-visibility') $result = (new LSD_Diagnostics_Visibility())->inspect($input, $display, $report);
            }
            else return LSD_Diagnostics_Report::error('invalid_input', __('Unknown diagnostic operation.', 'listdom'));
            return is_wp_error($result) ? $result : $report->result();
        }
        catch (Throwable $exception)
        {
            return LSD_Diagnostics_Report::error('inspection_failed', __('Diagnostic evidence could not be read. No repair or retry was attempted.', 'listdom'));
        }
        finally
        {
            $restore_metadata();
        }
    }
}
