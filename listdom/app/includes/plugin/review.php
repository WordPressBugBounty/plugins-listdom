<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Plugin Review Class.
 *
 * @class LSD_Plugin_Review
 * @version    1.0.0
 */
class LSD_Plugin_Review extends LSD_Base
{
    public function init()
    {
        add_action('admin_notices', [$this, 'display']);
    }

    public function display($force = false)
    {
        $review = $_GET['lsd-review'] ?? '';
        if ($review) $this->adjust_review_display($review);

        // Listdom Home
        $home = isset($_GET['page']) && $_GET['page'] === 'listdom';
        if($home && !$force) return '';

        return $this->include_html_file('menus/review/review.php', [
            'parameters' => [
                'home' => $home
            ]
        ]);
    }

    public function can_display_review(): bool
    {
        $display_time = $this->get_display_review_time();

        // Already Disabled
        if ($display_time == 0) return false;

        // Is it the time?
        return current_time('timestamp') > $display_time;
    }

    public function get_display_review_time(): int
    {
        $display_time = get_option('lsd_ask_review_time', null);

        // Simulate Display Time
        if (is_null($display_time))
        {
            $installation_time = (int) get_option('lsd_installed_at', 0);
            $display_time = $installation_time + (WEEK_IN_SECONDS * 2); // Two weeks after installation
        }

        return (int) $display_time;
    }

    public function adjust_review_display(string $action)
    {
        $display_time = $this->get_display_review_time();

        if ($action === 'later')
        {
            $display_time = current_time('timestamp') > $display_time ? current_time('timestamp') : $display_time;
            $display_time += WEEK_IN_SECONDS;
        }
        else if ($action === 'done') $display_time = 0;

        update_option('lsd_ask_review_time', $display_time);
    }
}
