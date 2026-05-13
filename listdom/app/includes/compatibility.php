<?php

class LSD_Compatibility extends LSD_Base
{
    public $tx_html_tag = '';
    public $tx_title_status = null;
    public $tx_html_id = '';
    public $tx_html_class = [];
    public $body_classes = [];

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

        // Detect Frontend Pages With Listdom Shortcodes
        add_action('wp', [$this, 'page_has_listdom_shortcode']);

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

    public function page_has_listdom_shortcode()
    {
        if (is_admin()) return;

        $post = get_post();
        if (!($post instanceof WP_Post)) return;

        $content_sources = [];
        $content = $post->post_content;
        if (is_string($content) && trim($content) !== '') $content_sources[] = $content;

        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
        if (is_array($elementor_data)) $elementor_data = wp_json_encode($elementor_data);
        if (is_string($elementor_data) && trim($elementor_data) !== '') $content_sources[] = $elementor_data;

        foreach ($content_sources as $source)
        {
            // Check for Listdom shortcode directly here
            if (has_shortcode($source, 'listdom') || stripos($source, '[listdom') !== false)
            {
                if (!in_array('listdom-fe-shortcode-page', $this->body_classes, true)) $this->body_classes[] = 'listdom-fe-shortcode-page';
                return;
            }
        }
    }
}
