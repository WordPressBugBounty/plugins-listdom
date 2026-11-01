<?php

/** Diagnostic-only raw reads: withhold payloads in SQL before decoding or filling meta caches. */
class LSD_Diagnostics_Metadata
{
    private $report;

    public function __construct(LSD_Diagnostics_Report $report)
    {
        $this->report = $report;
    }

    /** Null means withheld; an absent key has the ordinary empty-string value. */
    public function value(int $id, string $key, int $depth = 0)
    {
        global $wpdb;
        $remaining = $this->report->remaining_source_bytes();
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT OCTET_LENGTH(meta_value) AS bytes,
                    CASE WHEN OCTET_LENGTH(meta_value) <= %d THEN meta_value ELSE NULL END AS value
             FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id ASC LIMIT 1",
            $remaining, $id, $key
        ));
        if (!empty($wpdb->last_error)) return $this->unavailable();
        if (!$row) return '';
        if (!$this->report->visit($depth, (int) $row->bytes) || $row->value === null) return null;
        return maybe_unserialize($row->value);
    }

    /** Match LSD_Base::get_post_meta's first-value semantics within one bounded DB snapshot. */
    public function view(int $id): ?array
    {
        global $wpdb;
        $remaining = $this->report->remaining_source_bytes();
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT m.meta_key, OCTET_LENGTH(m.meta_value) AS bytes,
                    CASE WHEN budget.bytes <= %d AND budget.entries <= 500 THEN m.meta_value ELSE NULL END AS value,
                    budget.bytes AS total_bytes, budget.entries
             FROM {$wpdb->postmeta} m
             JOIN (SELECT COALESCE(SUM(OCTET_LENGTH(meta_value) + OCTET_LENGTH(meta_key)), 0) AS bytes, COUNT(*) AS entries
                   FROM {$wpdb->postmeta} WHERE post_id = %d) budget ON 1 = 1
             WHERE m.post_id = %d ORDER BY m.meta_id ASC LIMIT 501",
            $remaining, $id, $id
        ));
        if (!empty($wpdb->last_error)) return $this->unavailable();
        if (!$rows) return [];
        if (!$this->report->visit(0, (int) $rows[0]->total_bytes) || (int) $rows[0]->entries > 500)
        {
            $this->report->limit('traversal_limit', __('Saved view metadata exceeds the shared source or entry limit.', 'listdom'));
            return null;
        }
        $values = [];
        foreach ($rows as $row)
        {
            if ($row->value === null) return null;
            if (!array_key_exists($row->meta_key, $values)) $values[$row->meta_key] = maybe_unserialize($row->value);
        }
        return $values;
    }

    /** Scalar presence predicates never retrieve unsupported or inactive builder payloads. */
    public function unsupported_builder(int $id): ?bool
    {
        global $wpdb;
        $found = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM {$wpdb->postmeta}
             WHERE post_id = %d AND (
                 (meta_key IN (%s, %s, %s) AND meta_value NOT IN ('', '0', 'a:0:{}', 'b:0;', 'N;', '[]'))
                 OR (meta_key = %s AND meta_value = 'on')
             ) LIMIT 1",
            $id, '_bricks_page_content_2', '_bricks_page_content', '_fl_builder_data', '_et_pb_use_builder'
        ));
        if (!empty($wpdb->last_error)) return $this->unavailable();
        return $found !== null;
    }

    private function unavailable()
    {
        $this->report->limit('metadata_unavailable', __('Saved metadata could not be read; no default configuration was inferred.', 'listdom'));
        return null;
    }
}

