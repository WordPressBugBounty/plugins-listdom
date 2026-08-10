<?php

class LSD_Menus_Launch_Checklist extends LSD_Menus
{
    public function output()
    {
        $report = LSD_Checklist::instance()->get_report();

        $this->include_html_file('menus/launch-checklist/tpl.php', [
            'parameters' => [
                'report' => $report,
                'checklist' => LSD_Checklist::instance(),
            ],
        ]);
    }
}
