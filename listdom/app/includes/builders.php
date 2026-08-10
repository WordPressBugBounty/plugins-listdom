<?php

class LSD_Builders extends LSD_Base
{
    /**
     * @var LSD_PTypes_Listing_Single
     */
    private $single = null;

    /**
     * @var LSD_Entity_Listing
     */
    private $listing = null;
    private $template_type = '';

    public function single($single): LSD_Builders
    {
        $this->single = $single;
        $this->template_type = 'single_listing';
        return $this;
    }

    public function listing(LSD_Entity_Listing $listing): LSD_Builders
    {
        $this->listing = $listing;
        if ($this->template_type === '') $this->template_type = 'listing_card';
        return $this;
    }

    public function template_type(string $template_type): LSD_Builders
    {
        $this->template_type = sanitize_key($template_type);
        return $this;
    }

    public function build($template_id)
    {
        $template_id = $this->normalize_template_id($template_id);
        if (!$template_id) return '';

        $template = get_post($template_id);
        if (!$template instanceof WP_Post) return '';

        if (!$this->is_template_type_compatible($template_id, $template)) return '';

        // Listdom Bar
        if (is_numeric($template_id)) (LSD_Bar::instance())->add($template_id);

        // Template Builder
        if ($template->post_type === LSD_Base::PTYPE_TEMPLATE) return $this->template_builder($template_id);

        // Elementor
        if (
            class_exists(\LSDPACELM\Base::class) &&
            class_exists(\Elementor\Plugin::class) &&
            $template->post_type === \LSDPACELM\Base::PTYPE_DETAILS
        ) return $this->elementor($template_id);

        // Divi
        if (
            class_exists(\LSDPACDIV\Base::class) &&
            function_exists('et_theme_builder_frontend_render_layout') &&
            $template->post_type === \LSDPACDIV\Base::PTYPE_DETAILS
        ) return $this->divi($template_id);

        // Bricks
        if (
            class_exists(\LSDPACBRX\Base::class) &&
            class_exists(\LSDPACBRX\Builder::class) &&
            class_exists(\Bricks\Frontend::class) &&
            $template->post_type === \LSDPACBRX\Base::PTYPE_DETAILS
        ) return $this->bricks($template_id);

        // Anything
        return $this->content($template_id);
    }

    private function normalize_template_id($template_id): int
    {
        if (is_numeric($template_id)) return (int) $template_id;

        if (is_string($template_id) && preg_match('/^tb_(\d+)$/', $template_id, $matches)) return (int) $matches[1];

        return 0;
    }

    private function is_template_type_compatible(int $template_id, WP_Post $template): bool
    {
        if ($this->template_type === '') return true;

        $template_type = $this->get_template_type($template_id, $template);
        if ($template_type === '') return true;

        return $template_type === $this->template_type;
    }

    private function get_template_type(int $template_id, WP_Post $template): string
    {
        if ($template->post_type === LSD_Base::PTYPE_TEMPLATE)
        {
            $preview_request = $this->get_template_preview_request($template_id);
            if (!empty($preview_request['template_type'])) return sanitize_key((string) $preview_request['template_type']);

            $template_type = sanitize_key((string) get_post_meta($template_id, '_lsd_template_type', true));

            return $template_type !== '' ? $template_type : 'single_listing';
        }

        $template_type = sanitize_key((string) get_post_meta($template_id, 'lsd_type', true));
        if ($template_type === 'details') return 'single_listing';
        if ($template_type === 'card') return 'listing_card';
        if ($template_type === 'infowindow') return 'info_window';

        return $template_type !== '' ? $template_type : 'single_listing';
    }

    public function template_builder($template_id): string
    {
        // Payload
        LSD_Payload::set('single', $this->single);
        LSD_Payload::set('listing', $this->listing);

        $preview_request = $this->get_template_preview_request((int) $template_id);

        // Set Current Post
        if ($this->listing) LSD_LifeCycle::post($this->listing->id());

        // Build Content using Listdom template builder
        $args = [
            'listing'        => $this->listing,
            'apply_filters'  => true,
            'context'        => empty($preview_request) ? 'frontend' : 'preview',
        ];

        if ($this->template_type !== '') $args['template_type'] = $this->template_type;

        $output = LSD_Template::get_template_builder_content_for_display($template_id, $args);

        // Back to Original Post
        if ($this->listing) LSD_LifeCycle::reset();

        return $output;
    }

    private function get_template_preview_request(int $template_id): array
    {
        if (!class_exists('LSD_PTypes_Template') || !method_exists('LSD_PTypes_Template', 'get_active_preview_request')) return [];

        $preview_request = LSD_PTypes_Template::get_active_preview_request();
        if (empty($preview_request)) return [];

        return (int) ($preview_request['template_id'] ?? 0) === $template_id ? $preview_request : [];
    }

    public function elementor($template_id): string
    {
        // Payload
        LSD_Payload::set('single', $this->single);
        LSD_Payload::set('listing', $this->listing);
        LSD_Payload::set('builder_layout_id', (int) $template_id);

        // Set Current Post
        if ($this->listing) LSD_LifeCycle::post($this->listing->id());

        // Build Content
        $output = Elementor\Plugin::instance()
            ->frontend
            ->get_builder_content_for_display($template_id, true);

        // Back to Original Post
        if ($this->listing) LSD_LifeCycle::reset();
        LSD_Payload::remove('builder_layout_id');

        return $output;
    }

    public function divi($template_id): string
    {
        // Payload
        LSD_Payload::set('single', $this->single);
        LSD_Payload::set('listing', $this->listing);
        LSD_Payload::set('builder_layout_id', (int) $template_id);

        // Set Current Post
        if ($this->listing) LSD_LifeCycle::post($this->listing->id());

        // Build Content
        $template_content = get_post_field('post_content', $template_id);
        $output = et_core_intentionally_unescaped(et_builder_render_layout($template_content), 'html');

        // Back to Original Post
        if ($this->listing) LSD_LifeCycle::reset();
        LSD_Payload::remove('builder_layout_id');

        return $output;
    }

    public function bricks($template_id): string
    {
        LSD_Payload::set('single', $this->single);
        LSD_Payload::set('listing', $this->listing);
        LSD_Payload::set('builder_layout_id', (int) $template_id);

        $builder = (new \LSDPACBRX\Builder())->single($this->single);
        if ($this->listing) $builder->listing($this->listing);

        // Set Current Post
        if ($this->listing) LSD_LifeCycle::post($this->listing->id());

        $output = $builder->build((int) $template_id);

        // Back to Original Post
        if ($this->listing) LSD_LifeCycle::reset();
        LSD_Payload::remove('builder_layout_id');

        return $output;
    }

    public function content($template_id)
    {
        // Template Content
        $content = get_the_content(null, false, $template_id);

        // Apply Filters
        $content = apply_filters('the_content', $content);
        return str_replace(']]>', ']]&gt;', $content);
    }
}
