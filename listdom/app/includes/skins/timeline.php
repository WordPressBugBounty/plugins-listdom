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

        // Timeline skin only supports vertical layout.
        $this->horizontal = false;

        $vertical_alignment = isset($this->skin_options['vertical_alignment'])
            ? strtolower(sanitize_text_field($this->skin_options['vertical_alignment']))
            : 'zigzag';
        if (!in_array($vertical_alignment, ['left', 'right', 'zigzag'], true)) $vertical_alignment = 'zigzag';
        $this->vertical_alignment = $vertical_alignment;

        $this->horizontal_alignment = 'zigzag';
        $this->columns = 1;
        $this->autoplay = false;
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
}
