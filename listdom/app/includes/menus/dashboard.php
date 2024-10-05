<?php
// no direct access
defined('ABSPATH') || die();

if(!class_exists('LSD_Menus_Dashboard')):

/**
 * Listdom Dashboard Menu Class.
 *
 * @class LSD_Menus_Dashboard
 * @version	1.0.0
 */
class LSD_Menus_Dashboard extends LSD_Menus
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

        // Initialize the menu
        $this->init();
	}
    
    public function init()
    {
    }
    
    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

        $review = $_GET['review'] ?? '';
        if($review) $this->adjust_review_display($review);
        
        // Generate output
        $this->include_html_file('menus/dashboard/tpl.php');
    }

    public function can_display_review(): bool
    {
        $display_time = $this->get_display_review_time();

        // Already Disabled
        if($display_time == 0) return false;

        // Is it the time?
        return current_time('timestamp') > $display_time;
    }

    public function get_display_review_time(): int
    {
        $display_time = get_option('lsd_ask_review_time', null);

        // Simulate Display Time
        if(is_null($display_time))
        {
            $installation_time = (int) get_option('lsd_installed_at', 0);
            $display_time = $installation_time + (WEEK_IN_SECONDS * 2); // Two weeks after installation
        }

        return (int) $display_time;
    }

    public function adjust_review_display(string $action)
    {
        $display_time = $this->get_display_review_time();

        if($action === 'later') $display_time += WEEK_IN_SECONDS;
        elseif($action === 'done') $display_time = 0;

        update_option('lsd_ask_review_time', $display_time);
    }
}

endif;