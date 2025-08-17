<?php
namespace LSDPACVIS;

class Base
{
    public function include_html_file($file = '', $args = [])
    {
        // File is empty
        if (!trim($file)) return esc_html__('HTML file path is empty.', 'listdom-visibility');

        // Core File
        $path = dirname(__FILE__) . '/../html/' . ltrim($file, '/');

        // Apply Filter
        $path = apply_filters('lsdaddvis_include_html_file', $path, $file, $args);

        // File does not exist
        if (!file_exists($path)) return esc_html__('HTML file does not exist! Check the file path.', 'listdom-visibility');

        // Return the File Path
        if (isset($args['return_path']) && $args['return_path']) return $path;

        // Parameters passed
        if (isset($args['parameters']) && is_array($args['parameters']) && count($args['parameters'])) extract($args['parameters']);

        // Start buffering
        ob_start();

        // Include Once
        if (isset($args['include_once']) && $args['include_once']) include_once $path;
        else include $path;

        // Get Buffer
        $output = ob_get_clean();

        // Return the file output
        if (isset($args['return_output']) && $args['return_output']) return $output;

        // Print the output
        echo $output;
        return '';
    }

    public function template($tpl, $override = true)
    {
        $path = dirname(__FILE__) . '/../templates/' . ltrim($tpl, '/');

        if ($override)
        {
            // Main Theme
            $theme = get_template_directory();
            $theme_path = $theme . '/listdom-visibility/' . ltrim($tpl, '/');

            if (is_file($theme_path)) $path = $theme_path;

            // Child Theme
            if (is_child_theme())
            {
                $child_theme = get_stylesheet_directory();
                $child_theme_path = $child_theme . '/listdom-visibility/' . ltrim($tpl, '/');

                if (is_file($child_theme_path)) $path = $child_theme_path;
            }
        }

        // Apply Filter
        return apply_filters('lsd_template', $path, $tpl, $override);
    }

    public static function install()
    {
        // General Settings
        $settings = \LSD_Options::settings();
        if (isset($settings['submission_module']) && !isset($settings['submission_module']['visibility'])) $settings['submission_module']['visibility'] = 0;

        // Update Options
        update_option('lsd_settings', $settings);

        // Add Visibility Meta
        $listings = get_posts([
            'post_type' => \LSD_Base::PTYPE_LISTING,
            'posts_per_page' => 1000,
            'meta_query' => [
                [
                    'key' => 'lsd_visible_until',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);

        foreach ($listings as $listing) add_post_meta($listing->ID, 'lsd_visible_until', 0, true);
    }

    public function response(array $response)
    {
        echo json_encode($response, JSON_NUMERIC_CHECK);
        exit;
    }
}
