<?php

class LSD_Shortcodes_Listdom extends LSD_Shortcodes
{
    public function init()
    {
        add_shortcode('listdom', [$this, 'output']);
    }

    public function output($atts = [], $override = [])
    {
        if ($this->is_block_editor()) return $this->shortcode_placeholder('listdom', $atts);

        $shortcode_id = isset($atts['id']) ? (int) $atts['id'] : 0;

        // Listdom Bar
        (LSD_Bar::instance())->add($shortcode_id);

        $atts = wp_parse_args($override, apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, $atts)));
        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom');
        if (trim($pre)) return $pre;

        return $this->skin($skin, $atts);
    }

    public function skin($skin, $atts)
    {
        $ai_visibility = class_exists('LSD_AI_Visibility') ? LSD_AI_Visibility::instance() : null;

        // Get Skin Object
        $SKO = $this->SKO($skin);

        $shortcode_id = isset($atts['id']) ? absint($atts['id']) : 0;
        static $inline_shortcode_sequence = 0;
        if ($shortcode_id <= 0)
        {
            $inline_shortcode_sequence++;
            $SKO->collection_schema_fragment_id = 'inline-' . $inline_shortcode_sequence;
        }

        // Start the skin
        $SKO->start($atts);
        $SKO->after_start();

        // Generate the Query
        $SKO->query();

        // Apply Search
        $SKO->apply_search($_GET);

        // Fetch the listings
        $SKO->fetch();
        $suppress_legacy_schema = $ai_visibility instanceof LSD_AI_Visibility
            && $ai_visibility->schema_service()->replaces_shortcode_collection_schema($SKO);

        if ($suppress_legacy_schema)
        {
            LSD_Schema::suppress_markup();
            LSD_Schema::suppress_default_listing_scope();
            LSD_Schema::suppress_default_listing_properties();
        }

        try
        {
            $output = $SKO->output();
        }
        finally
        {
            if ($suppress_legacy_schema)
            {
                LSD_Schema::restore_default_listing_properties();
                LSD_Schema::restore_default_listing_scope();
                LSD_Schema::restore_markup();
            }
        }

        if ($ai_visibility instanceof LSD_AI_Visibility)
        {
            $output .= $ai_visibility->schema_service()->shortcode_collection_markup($SKO);
        }

        return $output;
    }

    public function widget($shortcode_id)
    {
        $atts = apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, [
            'id' => $shortcode_id,
            'html_class' => 'lsd-widget lsd-shortcode-widget',
            'widget' => true,
        ]));

        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        return $this->skin($skin, $atts);
    }

    public function embed($shortcode_id)
    {
        $atts = apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, [
            'id' => $shortcode_id,
            'html_class' => 'lsd-embed lsd-shortcode-embed',
            'embed' => true,
        ]));

        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        return $this->skin($skin, $atts);
    }

    public function SKO($skin)
    {
        if ($skin === 'singlemap') $SKO = new LSD_Skins_Singlemap();
        else if ($skin === 'list') $SKO = new LSD_Skins_List();
        else if ($skin === 'grid') $SKO = new LSD_Skins_Grid();
        else if ($skin === 'side') $SKO = new LSD_Skins_Side();
        else if ($skin === 'listgrid') $SKO = new LSD_Skins_Listgrid();
        else if ($skin === 'halfmap') $SKO = new LSD_Skins_Halfmap();
        else if ($skin === 'table') $SKO = new LSD_Skins_Table();
        else if ($skin === 'cover') $SKO = new LSD_Skins_Cover();
        else if ($skin === 'carousel') $SKO = new LSD_Skins_Carousel();
        else if ($skin === 'slider') $SKO = new LSD_Skins_Slider();
        else if ($skin === 'masonry') $SKO = new LSD_Skins_Masonry();
        else if ($skin === 'accordion') $SKO = new LSD_Skins_Accordion();
        else if ($skin === 'mosaic') $SKO = new LSD_Skins_Mosaic();
        else if ($skin === 'timeline') $SKO = new LSD_Skins_Timeline();
        else if ($skin === 'gallery') $SKO = new LSD_Skins_Gallery();
        else $SKO = new LSD_Skins_Grid();

        return $SKO;
    }
}
