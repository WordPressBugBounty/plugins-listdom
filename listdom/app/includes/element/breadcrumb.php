<?php

class LSD_Element_Breadcrumb extends LSD_Element
{
    public $key = 'breadcrumb';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Breadcrumb', 'listdom');
    }

    public function get($icon, $taxonomy)
    {
        ob_start();
        include lsd_template('elements/breadcrumb.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'icon' => $icon,
                'taxonomy' => $taxonomy,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_icon">' . esc_html__('Show Home Icon', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_icon',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][icon]',
                'value' => $data['icon'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_taxonomy">' . esc_html__('Second Layer', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_taxonomy',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][taxonomy]',
                'value' => $data['taxonomy'] ?? 'category',
                'options' => [
                    'listdom-category' => esc_html__('Category', 'listdom'),
                    'listdom-location' => esc_html__('Location', 'listdom'),
                    'listdom-label' => esc_html__('Label', 'listdom'),
                    'listdom-tags' => esc_html__('Tag', 'listdom'),
                    'listdom-features' => esc_html__('Feature', 'listdom'),
                ],
            ]) . '
        </div>';
    }
}
