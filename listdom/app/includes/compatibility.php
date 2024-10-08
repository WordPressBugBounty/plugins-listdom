<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Compatibility Class.
 *
 * @class LSD_Compatibility
 * @version    1.0.0
 */
class LSD_Compatibility extends LSD_Base
{
    public $tx_html_tag = '';
    public $tx_title_status = null;
    public $tx_html_id = '';
    public $tx_html_class = [];
    public $body_classes = [];

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Add Taxonomy Template Filters
        foreach ([
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_TAG,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ] as $TX)
        {
            add_filter($TX . '_html_tag', [$this, 'tx_html_tag']);
            add_filter($TX . '_show_title', [$this, 'tx_show_title']);
            add_filter($TX . '_html_id', [$this, 'tx_html_id']);
            add_filter($TX . '_html_class', [$this, 'tx_html_class']);
        }

        // Body Class
        add_filter('body_class', [$this, 'body_class']);

        // Init the Theme Compatibility
        add_action('init', [$this, 'theme_compatibility']);
    }

    public function theme_compatibility()
    {
        // WP Template
        $template = get_template();

        switch ($template)
        {
            case 'logitrans':

                $this->tx_html_class = ['wrapper'];
                break;

            case 'porto':

                $this->tx_html_class = ['m-t-lg', 'm-b-xl'];
                break;

            case 'twentyseventeen':

                $this->tx_html_id = '';
                $this->tx_html_class = ['wrap'];
                break;

            case 'twentysixteen':

                $this->tx_html_id = 'primary';
                $this->tx_html_class = ['content-area'];
                break;

            case 'twentyfifteen':

                $this->tx_title_status = false;
                $this->tx_html_tag = 'article';
                $this->tx_html_id = '';
                $this->tx_html_class = ['hentry', 'lsd-p-4', 'lsd-mt-5'];
                break;

            case 'phlox':

                $this->tx_title_status = false;
                $this->tx_html_class = ['aux-container', 'aux-fold', 'clearfix', 'lsd-mt-5', 'lsd-mb-5'];
                break;

            case 'bridge':

                $this->tx_html_class = ['container_inner', 'default_template_holder', 'clearfix'];
                break;
        }
    }

    public function tx_html_tag($tag)
    {
        if (trim($this->tx_html_tag)) return $this->tx_html_tag;
        else return $tag;
    }

    public function tx_show_title($title_status)
    {
        if (!is_null($this->tx_title_status)) return $this->tx_title_status;
        else return $title_status;
    }

    public function tx_html_id($id)
    {
        if (trim($this->tx_html_id)) return $this->tx_html_id;
        else return $id;
    }

    public function tx_html_class($class)
    {
        if (is_array($this->tx_html_class) and count($this->tx_html_class)) return $class . ' ' . implode(' ', $this->tx_html_class);
        else return $class;
    }

    public function body_class($classes)
    {
        // WP Template
        $template = get_template();

        // Listdom Classes
        $listdom = array_merge($this->body_classes, ['lsd-theme-' . strtolower($template)]);

        // Merge Classes
        return array_merge($classes, $listdom);
    }
}
