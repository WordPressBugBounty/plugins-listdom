<?php

class LSD_Skins_Timeline extends LSD_Skins
{
    public $skin = 'timeline';
    public $default_style = 'style1';
    public $horizontal = false;
    public $vertical_alignment = 'zigzag';
    public $horizontal_alignment = 'zigzag';

    public function init()
    {
        add_action('wp_ajax_lsd_timeline_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_timeline_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_timeline_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_timeline_sort', [$this, 'filter']);
    }

    public function start($atts)
    {
        parent::start($atts);

        $horizontal_enabled = isset($this->skin_options['horizontal']) && (int) $this->skin_options['horizontal'];
        $this->horizontal = (bool) $horizontal_enabled;

        $vertical_alignment = isset($this->skin_options['vertical_alignment'])
            ? strtolower(sanitize_text_field($this->skin_options['vertical_alignment']))
            : 'zigzag';
        if (!in_array($vertical_alignment, ['left', 'right', 'zigzag'], true)) $vertical_alignment = 'zigzag';
        $this->vertical_alignment = $vertical_alignment;

        $horizontal_alignment = isset($this->skin_options['horizontal_alignment'])
            ? strtolower(sanitize_text_field($this->skin_options['horizontal_alignment']))
            : 'zigzag';
        if (!in_array($horizontal_alignment, ['top', 'bottom', 'zigzag'], true)) $horizontal_alignment = 'zigzag';
        $this->horizontal_alignment = $horizontal_alignment;

        $columns = isset($this->skin_options['columns']) && trim($this->skin_options['columns']) !== ''
            ? (int) $this->skin_options['columns']
            : 3;
        $columns = max(1, $columns);
        $this->columns = $this->horizontal ? $columns : 1;

        $autoplay = !isset($this->skin_options['autoplay']) || (int) $this->skin_options['autoplay'] === 1;
        $this->autoplay = $this->horizontal && $autoplay;
    }

    public function output()
    {
        // Pro is needed
        if (LSD_Base::isLite())
        {
            return LSD_Base::alert(
                LSD_Base::missFeatureMessage(
                    esc_html__('Timeline Skin', 'listdom')
                ),
                'warning'
            );
        }

        return parent::output();
    }

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        return $this->has_listing_price($listing) || $this->display_share_buttons;
    }

    public function has_body(LSD_Entity_Listing $listing): bool
    {
        return $this->display_categories
            || $this->display_favorite_icon
            || $this->display_compare_icon
            || $this->display_title
            || $this->display_contact_info
            || $this->has_bottom_bar($listing)
            || $this->display_cta;
    }
}
