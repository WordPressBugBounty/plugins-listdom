<?php

/** Branch outcomes: usable references, definite absences, and evidence that could add references. */
class LSD_Diagnostics_Resolution
{
    private $report;
    private $unknown = false;

    public function __construct(LSD_Diagnostics_Report $report)
    {
        $this->report = $report;
    }

    public function record(string $source, string $state, string $code = '', string $message = ''): void
    {
        $this->report->fact($source, 'reference_state', $state);
        if ($state === 'unknown') $this->unknown = true;
        if ($code !== '') $this->report->limit($code, $message);
    }

    public function complete(): bool
    {
        return !$this->unknown;
    }

    /** A known usable form proves existence even when another branch is unresolved. */
    public function existence(bool $found): ?bool
    {
        return $found ? true : ($this->unknown ? null : false);
    }
}
