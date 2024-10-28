<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Add Listing Shortcode Class.
 *
 * @class LSD_Shortcodes_AddListing
 * @version    1.0.0
 */
class LSD_Shortcodes_AddListing extends LSD_Shortcodes_Dashboard
{
    public $form_type;
    public $add_listing;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Add Listing Page
        $this->add_listing = isset($this->settings['add_listing_page_status']) && $this->settings['add_listing_page_status'];
    }

    public function init()
    {
        // Shortcode
        add_shortcode('listdom-add-listing', [$this, 'output']);
    }

    public function output($atts = [])
    {
        if ($this->isLite())
        {
            return $this->alert($this->missFeatureMessage(esc_html__('Add Listing', 'listdom')), 'error');
        }

        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-add-listing');
        if (trim($pre)) return $pre;

        // Include WordPress Media
        LSD_Assets::media();

        // Shortcode attributes
        $this->atts = is_array($atts) ? $atts : [];

        // Add Listing URL
        global $post;
        $this->page = $post;

        // Add Listing URL
        $this->url = get_permalink($this->page);

        // Mode
        $this->mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'form';

        // Form Type
        $this->form_type = true;

        // Payload
        LSD_Payload::set('add_listing', $this);

        // Form
        if ($this->mode == 'form') return $this->add_listing();
        // Subscription Modes
        else return apply_filters('lsd_dashboard_modes', $this->alert(esc_html__('Not found!', 'listdom'), 'error'), $this);
    }

    public function add_listing()
    {
        if (!get_current_user_id() && !$this->guest_status)
        {
            return $this->alert(sprintf(esc_html__("Unfortunately you don't have permission to view this page. Please %s first.", 'listdom'), '<a href="' . wp_login_url($this->current_url()) . '">' . esc_html__('login', 'listdom') . '</a>'), 'error');
        }
        else if (!$this->add_listing)
        {
            return $this->alert(esc_html__("Unfortunately you don't have permission to create or edit listings.", 'listdom'), 'error');
        }

        return $this->form();
    }
}
