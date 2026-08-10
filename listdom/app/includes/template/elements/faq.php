<?php

class LSD_Template_Elements_Faq extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'faq';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing FAQs', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('count',
        [
            'type'        => 'number',
            'class'       => 'lsd-admin-input',
            'label'       => esc_html__('Question Count', 'listdom'),
            'description' => esc_html__('Use 0 to show all questions.', 'listdom'),
            'default'     => 0,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('accordion_' . $this->key,
        [
            'label'      => esc_html__('Accordion', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('accordion_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-item' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('accordion_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'accordion_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('accordion_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-item' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('question_' . $this->key,
        [
            'label'      => esc_html__('Questions', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('question_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-question' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('question_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'question_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-question' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('question_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-question' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('question_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-question' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('question_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-question' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('answer_' . $this->key,
        [
            'label'      => esc_html__('Answers', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('answer_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-answer' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('answer_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'answer_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-answer' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('answer_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-answer' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('answer_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-answer' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('answer_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-faq .lsd-faq-answer' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];
        $limit = isset($content_settings['count']) ? (int) $content_settings['count'] : 0;

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_faqs($limit);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-faq lsdtb-card-faq">' . $output . '</div>';
    }
}
