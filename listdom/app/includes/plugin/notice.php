<?php

class LSD_Plugin_Notice extends LSD_Base
{
    // Define notices
    protected $notices = [
        'review' => [
            'template' => 'notices/review.php',
            'option_key' => 'lsd_review_display_time',
            'default_delay' => WEEK_IN_SECONDS * 2, // Two weeks after installation
            'actions' => ['later', 'done'],
        ],
        'listdomer' => [
            'template' => 'notices/listdomer.php',
            'option_key' => 'lsd_listdomer_display_time',
            'default_delay' => WEEK_IN_SECONDS, // One week
            'actions' => ['later', 'dismiss'],
        ],
    ];

    /**
     * Initialize notices and register admin notifies action.
     */
    public function init()
    {
        add_action('admin_notices', [$this, 'display']);
    }

    /**
     * Display the notice(s) in the admin panel.
     *
     * @param string $notice The specific notice key to display.
     * @param bool $force Force display of the notice.
     */
    public function display(string $notice = '', bool $force = false)
    {
        // Listdom Home
        $home = isset($_GET['page']) && $_GET['page'] === 'listdom';
        if ($home && !$force) return;

        // Theme / Plugin Update or Install
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'update.php') !== false) return;

        // If a specific notice key is provided, display only that notice
        if (trim($notice))
        {
            echo $this->render_notice($notice, $home);
            return;
        }

        // Display all notices (including the specific one if it's not shown already)
        foreach ($this->notices as $key => $notice)
        {
            if ($this->can_display_notice($key)) echo $this->render_notice($key, '');
        }
    }

    /**
     * Check if a notice can be displayed based on the time condition.
     *
     * @param string $notice The notice key.
     * @return bool Whether the notice can be displayed.
     */
    protected function can_display_notice(string $notice): bool
    {
        $display_time = $this->get_display_time($notice);

        // Already Disabled
        if ($display_time === 0) return false;

        // Is it the time?
        return current_time('timestamp') > $display_time;
    }

    /**
     * Get the display time for a specific notice.
     *
     * @param string $notice The notice key.
     * @return int The timestamp when the notice can be displayed.
     */
    protected function get_display_time(string $notice): int
    {
        if (!isset($this->notices[$notice])) return 0;

        $notice_config = $this->notices[$notice];
        $display_time = get_option($notice_config['option_key'], null);

        // Simulate Display Time
        if (is_null($display_time))
        {
            $installation_time = (int) get_option('lsd_installed_at', 0);
            $display_time = $installation_time + $notice_config['default_delay'];
        }

        return (int) $display_time;
    }

    /**
     * Adjust the display time of a notice based on user action.
     *
     * @param string $notice The notice key.
     * @param string $action The action to perform (e.g., 'later', 'done').
     */
    public function adjust_notice_display(string $notice, string $action)
    {
        // Ensure the provided notice is valid
        if (!isset($this->notices[$notice])) return;

        $notice_config = $this->notices[$notice];
        $display_time = $this->get_display_time($notice);

        // Handle actions for the notice
        if ($action === 'later') $display_time = max(current_time('timestamp'), $display_time) + WEEK_IN_SECONDS;
        else if ($action === 'done') $display_time = 0;

        update_option($notice_config['option_key'], $display_time, false);
    }

    /**
     * Render the HTML for the notice.
     *
     * @param string $notice The notice key.
     * @param bool $home Whether it's the home page.
     * @return string The rendered HTML.
     */
    protected function render_notice(string $notice, bool $home): string
    {
        $action = $_GET['lsd-' . $notice] ?? '';

        // If an action is passed (e.g., 'later' or 'done'), adjust the notice display
        if ($action) $this->adjust_notice_display($notice, $action);

        if (!isset($this->notices[$notice])) return '';

        $notice_config = $this->notices[$notice];
        $template = $notice_config['template'];

        return $this->include_html_file($template, [
            'parameters' => [
                'notice' => $notice,
                'home' => $home,
            ],
        ]);
    }
}
