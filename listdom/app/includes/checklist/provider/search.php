<?php

class LSD_Checklist_Provider_Search extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('Search & Discovery', 'listdom'));

        $this->register_checks($registry, [
            $this->check([
                'id' => 'search_form_exists',
                'title' => esc_html__('Search forms', 'listdom'),
                'description' => esc_html__('Search is central to directory usability and discovery.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_SEARCH),
            ], [$this, 'search_form_exists']),
            $this->check([
                'id' => 'ajax_search',
                'title' => esc_html__('AJAX search', 'listdom'),
                'description' => esc_html__('Live filtering can make archive browsing faster when the APS search add-on is available.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('Edit form', 'listdom'),
                'action_url' => $this->search_form_editor_url(),
                'action_focus_target' => '#lsd_form_connected_shortcodes_input, #lsd_search_form_ajax',
            ], [$this, 'ajax_search']),
            $this->check([
                'id' => 'ai_search_mode',
                'title' => esc_html__('AI search mode', 'listdom'),
                'description' => esc_html__('AI-powered search can be enabled for more advanced query handling when the APS search add-on is active.', 'listdom'),
                'category' => $category,
                'importance' => 'low',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->addon_action_url('aps'),
                'action_focus_target' => '',
            ], [$this, 'ai_search_mode']),
            $this->check([
                'id' => 'map_settings',
                'title' => esc_html__('Map settings', 'listdom'),
                'description' => esc_html__('If maps are part of the experience, the active provider should be ready for frontend use.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('general', 'map-module'),
                'action_focus_target' => '#lsd_settings_map_provider, #lsd_settings_googlemaps_api_key:visible, #lsd_settings_google_geocoding_api_key',
            ], [$this, 'map_settings']),
        ]);
    }

    protected function search_form_exists(?LSD_Checklist_Check_Interface $check = null): array
    {
        $published_forms = $this->helper->published_post_ids(LSD_Base::PTYPE_SEARCH);
        $available_forms = $this->helper->published_search_form_ids();

        if (count($available_forms) > 0) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => sprintf(esc_html__('%d published search form(s) are available on the frontend.', 'listdom'), count($available_forms)),];

        if (count($published_forms) > 0) return ['status' => LSD_Checklist_Result::STATUS_WARNING, 'message' => esc_html__('Search forms exist, but no published page references a published search form.', 'listdom'),];

        return [
            'status' => LSD_Checklist_Result::STATUS_INCOMPLETE,
            'message' => esc_html__('Create a search form for visitors.', 'listdom'),
            'action_label' => esc_html__('Create', 'listdom'),
            'action_url' => $this->helper->post_new_url(LSD_Base::PTYPE_SEARCH),
            'action_focus_target' => '#lsd_search_available_fields_keyword, #lsd_search_available_fields_listdom-category, #lsd_search_available_fields_listdom-location',
        ];
    }

    protected function ajax_search(?LSD_Checklist_Check_Interface $check = null): array
    {
        if (!$this->addon_active('aps')) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('APS search add-on is not active, so AJAX search is unavailable.', 'listdom'),];

        $form_ids = $this->helper->published_shortcode_post_ids('listdom-search', LSD_Base::PTYPE_SEARCH);
        $snapshot = $this->helper->search_form_snapshot($form_ids);
        $ajax_ready = (int) $snapshot['ajax_ready'];
        $searchable_skins = LSD_Skins::get_searchable_skins();

        foreach ($this->helper->published_shortcode_post_ids('listdom', LSD_Base::PTYPE_SHORTCODE) as $shortcode_id)
        {
            $display = get_post_meta($shortcode_id, 'lsd_display', true);
            $skin = is_array($display) && !empty($display['skin']) ? (string) $display['skin'] : 'grid';

            if (!isset($searchable_skins[$skin])) continue;

            $search = get_post_meta($shortcode_id, 'lsd_search', true);
            if (!is_array($search) || empty($search['ajax'])) continue;

            $form_id = absint($search['shortcode'] ?? 0);
            if ($form_id < 1 || get_post_type($form_id) !== LSD_Base::PTYPE_SEARCH || !$this->helper->is_published_post($form_id)) continue;

            $ajax_ready++;
        }

        if ($ajax_ready > 0) return [
            'status' => LSD_Checklist_Result::STATUS_COMPLETE,
            'message' => sprintf(
                esc_html__('%d frontend AJAX search configuration(s) are ready.', 'listdom'),
                $ajax_ready
            ),
        ];

        if ($snapshot['connected_shortcodes'] > 0) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Connected shortcodes were found, but AJAX search is still off.', 'listdom'),];

        return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Connect a search form to searchable shortcodes if you want AJAX filtering.', 'listdom'),];
    }

    protected function ai_search_mode(?LSD_Checklist_Check_Interface $check = null): array
    {
        if (!$this->addon_active('aps')) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('APS search add-on is not active, so AI search mode is unavailable.', 'listdom'),];

        $form_ids = $this->helper->published_search_form_ids();
        $snapshot = $this->helper->search_form_snapshot($form_ids);

        if ($snapshot['ai_search'] > 0) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => sprintf(esc_html__('%d search form(s) include an AI search field.', 'listdom'), (int) $snapshot['ai_search']),];

        return [
            'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => esc_html__('AI search is available but not configured in your current search forms.', 'listdom'),
        ];
    }

    protected function map_settings(?LSD_Checklist_Check_Interface $check = null): array
    {
        $settings = LSD_Options::settings();

        if (empty($settings['components']['map'])) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Map component is disabled.', 'listdom')];

        $provider = $settings['map_provider'] ?? LSD_MP_LEAFLET;
        $has_key = $provider !== LSD_MP_GOOGLE || trim((string) ($settings['googlemaps_api_key'] ?? '')) !== '';

        if ($has_key) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => sprintf(esc_html__('Map provider is set to %s.', 'listdom'), $provider)];

        return [
            'status' => LSD_Checklist_Result::STATUS_WARNING,
            'message' => esc_html__('Google Maps is selected but no API key is configured.', 'listdom'),
            'action_focus_target' => $this->map_settings_focus_target(),
        ];
    }

    protected function search_form_editor_url(): string
    {
        $post_id = $this->helper->first_published_post_id(LSD_Base::PTYPE_SEARCH);
        if ($post_id > 0) return $this->helper->edit_post_url($post_id);

        return $this->helper->post_new_url(LSD_Base::PTYPE_SEARCH);
    }

    protected function map_settings_focus_target(): string
    {
        return '#lsd_settings_map_provider, #lsd_settings_googlemaps_api_key, #lsd_settings_google_geocoding_api_key';
    }
}
