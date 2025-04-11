<?php

abstract class LSD_IX_Template extends LSD_Collections
{
    public function manage($action): string
    {
        // Main Library
        $main = new LSD_Main();

        return $main->include_html_file('menus/ix/mappings/manage.php', [
            'return_output' => true,
            'parameters' => [
                'template' => $this,
                'action' => $action,
            ],
        ]);
    }
}
