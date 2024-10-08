<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom IX Template.
 * Used in CSV and Excel
 *
 * @class LSD_IX_Template
 * @version    1.0.0
 */
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
