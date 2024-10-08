<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom General Hooks Class.
 *
 * @class LSD_Hooks
 * @version    1.0.0
 */
class LSD_Hooks extends LSD_Base
{

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Register Actions
        $this->actions();

        // Register Filters
        $this->filters();
    }

    public function actions()
    {
        // Activation Redirect
        add_action('admin_init', [$this, 'redirect_after_activation']);

        // Notices
        add_action('admin_notices', ['LSD_Flash', 'show']);

        // Iframe
        add_action('wp', [$this, 'raw']);

        // Uploader
        add_action('wp_ajax_lsd_uploader', [$this, 'upload']);
    }

    public function filters()
    {
        add_filter('lsd_skins_atts', [$this, 'apply_search']);
        add_filter('ajax_query_attachments_args', [$this, 'protect_wp_media']);
    }

    /**
     * Redirect the user to Listdom Dashboard after plugin activation
     * @return void
     */
    public function redirect_after_activation()
    {
        // No need to redirect
        if (!get_option('lsd_activation_redirect', false)) return;

        // Delete the option to don't do it again
        delete_option('lsd_activation_redirect');

        // Redirect to Listdom Dashboard
        wp_redirect(admin_url('/admin.php?page=' . LSD_Base::WELCOME_SLUG));
        exit;
    }

    public function apply_search($atts)
    {
        // Get Search Form Options
        $sf = $this->get_sf();

        // There is no Search Options
        if (!$sf) return $atts;

        // Target Shortcode
        $shortcode = isset($_GET['sf-shortcode']) && trim($_GET['sf-shortcode']) ? sanitize_text_field($_GET['sf-shortcode']) : null;

        // Validate the Shortcode
        if ($shortcode && (!isset($atts['id']) || $atts['id'] != $shortcode)) return $atts;

        // Set the Filter Array
        if (!isset($atts['lsd_filter'])) $atts['lsd_filter'] = [];

        // Keyword
        if (isset($sf['s']) && trim($sf['s']) != '')
        {
            $atts['lsd_filter']['s'] = $sf['s'];
        }

        // Category
        if (isset($sf[LSD_Base::TAX_CATEGORY]) && (is_array($sf[LSD_Base::TAX_CATEGORY]) || trim($sf[LSD_Base::TAX_CATEGORY]) !== ''))
        {
            $atts['lsd_filter'][LSD_Base::TAX_CATEGORY] = is_array($sf[LSD_Base::TAX_CATEGORY]) ? $sf[LSD_Base::TAX_CATEGORY] : [$sf[LSD_Base::TAX_CATEGORY]];
        }

        // Location
        if (isset($sf[LSD_Base::TAX_LOCATION]) && (is_array($sf[LSD_Base::TAX_LOCATION]) || trim($sf[LSD_Base::TAX_LOCATION]) !== ''))
        {
            $atts['lsd_filter'][LSD_Base::TAX_LOCATION] = is_array($sf[LSD_Base::TAX_LOCATION]) ? $sf[LSD_Base::TAX_LOCATION] : [$sf[LSD_Base::TAX_LOCATION]];
        }

        // Tag
        if (isset($sf[LSD_Base::TAX_TAG]) && (is_array($sf[LSD_Base::TAX_TAG]) || trim($sf[LSD_Base::TAX_TAG]) !== ''))
        {
            if (is_array($sf[LSD_Base::TAX_TAG]))
            {
                $atts['lsd_filter'][LSD_Base::TAX_TAG] = $sf[LSD_Base::TAX_TAG];
            }
            else
            {
                $term = get_term($sf[LSD_Base::TAX_TAG]);
                $atts['lsd_filter'][LSD_Base::TAX_TAG] = ($term && isset($term->name)) ? $term->name : '';
            }
        }

        // Feature
        if (isset($sf[LSD_Base::TAX_FEATURE]) && (is_array($sf[LSD_Base::TAX_FEATURE]) || trim($sf[LSD_Base::TAX_FEATURE]) !== ''))
        {
            $atts['lsd_filter'][LSD_Base::TAX_FEATURE] = is_array($sf[LSD_Base::TAX_FEATURE]) ? $sf[LSD_Base::TAX_FEATURE] : [$sf[LSD_Base::TAX_FEATURE]];
        }

        // Label
        if (isset($sf[LSD_Base::TAX_LABEL]) && (is_array($sf[LSD_Base::TAX_LABEL]) || trim($sf[LSD_Base::TAX_LABEL]) !== ''))
        {
            $atts['lsd_filter'][LSD_Base::TAX_LABEL] = is_array($sf[LSD_Base::TAX_LABEL]) ? $sf[LSD_Base::TAX_LABEL] : [$sf[LSD_Base::TAX_LABEL]];
        }

        // Attributes
        if (isset($sf['attributes']) && is_array($sf['attributes']) && count($sf['attributes']))
        {
            $atts['lsd_filter']['attributes'] = $sf['attributes'];
        }

        // Radius
        if (isset($sf['circle']) && is_array($sf['circle']) && count($sf['circle']))
        {
            $atts['lsd_filter']['circle'] = $sf['circle'];
        }

        // Inquiry Period
        if (isset($sf['period']) && is_array($sf['period']) && count($sf['period']))
        {
            // Inquiry Key
            if (!isset($atts['lsd_filter']['inquiry'])) $atts['lsd_filter']['inquiry'] = [];

            $atts['lsd_filter']['inquiry']['period'] = $sf['period'];
        }

        // Inquiry Capacity
        if ((isset($sf['adults']) && trim($sf['adults'])) || (isset($sf['children']) && trim($sf['children'])))
        {
            // Inquiry Key
            if (!isset($atts['lsd_filter']['inquiry'])) $atts['lsd_filter']['inquiry'] = [];

            if (isset($sf['adults']) && trim($sf['adults'])) $atts['lsd_filter']['inquiry']['adults'] = $sf['adults'];
            if (isset($sf['children']) && trim($sf['children'])) $atts['lsd_filter']['inquiry']['children'] = $sf['children'];
        }

        return $atts;
    }

    public function protect_wp_media($query)
    {
        $user_id = get_current_user_id();
        if ($user_id && !current_user_can('administrator') && !current_user_can('editor')) $query['author'] = $user_id;

        return $query;
    }

    public function raw()
    {
        $ep = LSD_Endpoints::is();
        if ($ep === 'raw')
        {
            // Template Redirect Hook
            do_action('template_redirect');

            echo (new LSD_Endpoints_Raw())->output();
            exit;
        }
    }

    public function upload()
    {
        // User is not allowed to upload files
        if (!current_user_can('upload_files')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to upload files!', 'listdom')]);

        // Nonce is not set!
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Unique Key
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_uploader_' . $key)) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        // Include the function
        if (!function_exists('wp_handle_upload')) require_once ABSPATH . 'wp-admin/includes/file.php';

        $files = isset($_FILES['files']) && is_array($_FILES['files']) ? $_FILES['files'] : [];

        // No files
        if (!count($files)) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file!', 'listdom')]);

        // Allowed Extensions
        $allowed = ['jpeg', 'jpg', 'png', 'webp', 'pdf', 'zip', 'gif'];

        $success = 0;
        $data = [];

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++)
        {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $ex = explode('.', $file['name']);
            $extension = end($ex);

            // Invalid Extension
            if (!in_array(strtolower($extension), $allowed)) continue;

            $uploaded = wp_handle_upload($file, ['test_form' => false]);

            if ($uploaded && !isset($uploaded['error']))
            {
                $success = 1;
                $attachment = [
                    'post_mime_type' => $uploaded['type'],
                    'post_title' => '',
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];

                // Add as Attachment
                $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

                // Update Metadata
                require_once ABSPATH . 'wp-admin/includes/image.php';
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));

                $data[] = [
                    'id' => $attachment_id,
                    'url' => $uploaded['url'],
                ];
            }
        }

        $message = $success ? esc_html__('The files are uploaded!', 'listdom') : esc_html__('An error occurred!', 'listdom');
        $this->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }
}
