<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Welcome Menu Class.
 *
 * @class LSD_Menus_Welcome
 * @version    1.0.0
 */
class LSD_Menus_Welcome extends LSD_Menus
{
    /**
     * @var string
     */
    public $tab;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
    }
    
    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'welcome';

        // Generate output
        $this->include_html_file('menus/welcome/tpl.php');
    }
}
