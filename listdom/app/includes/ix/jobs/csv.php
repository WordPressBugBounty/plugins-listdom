<?php

class LSD_IX_Jobs_CSV extends LSD_Collections
{
    protected $key = 'lsdaddcsv_import_jobs';

    public function manage(): string
    {
        // Main
        $main = new LSD_Main();

        return $main->include_html_file('menus/ix/tabs/csv/jobs.php', [
            'return_output' => true,
            'parameters' => [
                'handler' => $this,
            ],
        ]);
    }
}
