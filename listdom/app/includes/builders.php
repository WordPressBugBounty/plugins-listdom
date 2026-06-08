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

    public function single($single): LSD_Builders
    {
        $this->single = $single;
        return $this;
    }

    public function listing(LSD_Entity_Listing $listing): LSD_Builders
    {
        $this->listing = $listing;
        return $this;
    }

    public function build($template_id)
    {
        $template = get_post($template_id);

        if (!($template instanceof WP_Post)) return '';

        // Listdom Bar
        if (is_numeric($template_id)) (LSD_Bar::instance())->add($template_id);

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
