<?php

class LSD_Checklist_Provider_Seo extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('SEO & AI Visibility', 'listdom'));

        $this->register_checks($registry, [
            $this->check([
                'id' => 'listing_schema',
                'title' => esc_html__('Structured data', 'listdom'),
                'description' => esc_html__('Structured data improves machine readability for single listings and directory archives.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('general', 'general'),
                'action_focus_target' => '#lsd_settings_ai_visibility_structured_data',
            ], [$this, 'listing_schema']),
            $this->check([
                'id' => 'archive_schema',
                'title' => esc_html__('Category schema types', 'listdom'),
                'description' => esc_html__('Category schema types help search engines understand archive intent.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->category_term_action_url(),
                'action_focus_target' => '#lsd_schema',
            ], [$this, 'archive_schema']),
            $this->check([
                'id' => 'ai_feed',
                'title' => esc_html__('Public AI-readable feed', 'listdom'),
                'description' => esc_html__('The public Listdom feed can support future audits, indexing, and AI-friendly ingestion.', 'listdom'),
                'category' => $category,
                'importance' => 'low',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('general', 'general'),
                'action_focus_target' => '#lsd_settings_ai_visibility_public_feed',
            ], [$this, 'ai_feed']),
            $this->check([
                'id' => 'content_completeness',
                'title' => esc_html__('Public feed fields', 'listdom'),
                'description' => esc_html__('Expose the core listing fields that make the public AI feed useful and understandable.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('general', 'general'),
                'action_focus_target' => $this->required_fields_focus_target(),
            ], [$this, 'content_completeness']),
            $this->check([
                'id' => 'directory_summary',
                'title' => esc_html__('Site tagline', 'listdom'),
                'description' => esc_html__('A clear tagline helps visitors and AI systems understand the directory scope.', 'listdom'),
                'category' => $category,
                'importance' => 'low',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => admin_url('options-general.php'),
                'action_focus_target' => '#blogdescription',
            ], [$this, 'directory_summary']),
        ]);
    }

    protected function listing_schema(?LSD_Checklist_Check_Interface $check = null): array
    {
        $settings = LSD_AI_Visibility::settings();
        $enabled = !empty($settings['structured_data']);

        return [
            'status' => $enabled ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING,
            'message' => $enabled ? esc_html__('Structured data output is enabled.', 'listdom') : esc_html__('Enable structured data for public listing pages.', 'listdom'),
        ];
    }

    protected function archive_schema(?LSD_Checklist_Check_Interface $check = null): array
    {
        if (!LSD_Base::isPro()) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Per-category schema types are available in Listdom Pro.', 'listdom'), 'action_label' => '', 'action_url' => '', 'action_focus_target' => '',];

        $categories = get_terms([
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'hide_empty' => false,
        ]);

        if (!is_array($categories) || !count($categories))
        {
            return [
                'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
                'message' => esc_html__('Add a listing category before assigning schema types.', 'listdom'),
                'action_url' => $this->helper->category_term_action_url(),
                'action_focus_target' => '',
            ];
        }

        $with_schema = 0;
        $first_missing = null;

        foreach ($categories as $category_term)
        {
            if (!$category_term instanceof WP_Term) continue;

            if (trim((string) get_term_meta($category_term->term_id, 'lsd_schema', true)) !== '')
            {
                $with_schema++;
                continue;
            }

            if (!$first_missing) $first_missing = $category_term;
        }

        if ($first_missing instanceof WP_Term)
        {
            return [
                'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
                'message' => $with_schema > 0 ? sprintf(esc_html__('%1$d categories have schema types. %2$s still needs one.', 'listdom'), $with_schema, $first_missing->name) : sprintf(esc_html__('%s still needs a schema type.', 'listdom'), $first_missing->name),
                'action_url' => $this->helper->category_term_action_url($first_missing->term_id),
                'action_focus_target' => '#lsd_schema',
            ];
        }

        return [
            'status' => LSD_Checklist_Result::STATUS_COMPLETE,
            'message' => sprintf(esc_html__('%d categories have explicit schema types.', 'listdom'), $with_schema),
        ];
    }

    protected function ai_feed(?LSD_Checklist_Check_Interface $check = null): array
    {
        $service = new LSD_AI_Visibility_Settings();
        $enabled = $service->public_feed_enabled();
        $available = $service->public_feed_available();

        if ($available) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('Public AI-readable feed is enabled and publicly accessible.', 'listdom'),];
        if ($enabled) return ['status' => LSD_Checklist_Result::STATUS_WARNING, 'message' => esc_html__('The public feed is enabled, but WordPress is currently blocking public access through the search engine visibility setting.', 'listdom'),];

        return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Public AI-readable feed is optional and currently disabled.', 'listdom'),];
    }

    protected function content_completeness(?LSD_Checklist_Check_Interface $check = null): array
    {
        $settings = LSD_AI_Visibility::settings();

        if (empty($settings['public_feed'])) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Enable the public AI-readable feed to manage its exposed fields.', 'listdom'), 'action_focus_target' => '#lsd_settings_ai_visibility_public_feed'];

        $fields = wp_parse_args(
            isset($settings['fields']) && is_array($settings['fields']) ? $settings['fields'] : [],
            LSD_AI_Visibility::field_defaults()
        );
        $field_options = LSD_AI_Visibility::field_options();

        foreach ($this->helper->ai_visibility_required_fields() as $field_key)
        {
            if (!empty($fields[$field_key])) continue;

            return [
                'status' => LSD_Checklist_Result::STATUS_WARNING,
                'message' => sprintf(esc_html__('Enable %s in the public AI-readable feed.', 'listdom'), $field_options[$field_key] ?? $field_key),
                'action_focus_target' => '#lsd_settings_ai_visibility_field_' . $field_key,
            ];
        }

        return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('Core public AI feed fields are enabled.', 'listdom'),];
    }

    protected function directory_summary(?LSD_Checklist_Check_Interface $check = null): array
    {
        $description = trim((string) get_bloginfo('description'));

        return ['status' => $description !== '' ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING, 'message' => $description !== '' ? esc_html__('Site tagline is set.', 'listdom') : esc_html__('Add a short site tagline.', 'listdom'),];
    }

    protected function required_fields_focus_target(): string
    {
        return '#lsd_settings_ai_visibility_field_description, #lsd_settings_ai_visibility_field_categories, #lsd_settings_ai_visibility_field_images';
    }
}
