<?php

class LSD_Checklist_Provider_Core extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('Core Directory Setup', 'listdom'));
        $layout_context = $this->helper->single_listing_layout_context();

        $this->register_checks($registry, [
            $this->check([
                'id' => 'listings_exist',
                'title' => esc_html__('Listings', 'listdom'),
                'description' => esc_html__('Launching a directory without published listings leaves the archive empty.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'action_label' => esc_html__('Manage', 'listdom'),
                'action_url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_LISTING),
            ], [$this, 'listings_exist']),
            $this->check([
                'id' => 'categories_exist',
                'title' => esc_html__('Categories', 'listdom'),
                'description' => esc_html__('Categories help visitors browse and help listings stay organized.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'action_label' => esc_html__('Manage', 'listdom'),
                'action_url' => admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY . '&post_type=' . LSD_Base::PTYPE_LISTING),
            ], [$this, 'categories_exist']),
            $this->check([
                'id' => 'important_taxonomies',
                'title' => esc_html__('Important taxonomies', 'listdom'),
                'description' => esc_html__('Locations, features, and tags make discovery and filtering more useful.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_LOCATION . '&post_type=' . LSD_Base::PTYPE_LISTING),
            ], [$this, 'important_taxonomies']),
            $this->check([
                'id' => 'directory_page',
                'title' => esc_html__('Directory pages and shortcodes', 'listdom'),
                'description' => esc_html__('Visitors need a published page that exposes your directory output.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_SHORTCODE),
            ], [$this, 'directory_page']),
            $this->check([
                'id' => 'single_listing_layout',
                'title' => esc_html__('Single listing layout', 'listdom'),
                'description' => esc_html__('Listings should open into a detail page with key sections enabled.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $layout_context['action_url'],
                ], [$this, 'single_listing_layout']),
            $this->check([
                'id' => 'ai_profiles',
                'title' => esc_html__('AI profiles', 'listdom'),
                'description' => esc_html__('AI-powered features need at least one configured profile before they can be used in content, mapping, or semantic search.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('ai', 'profiles'),
                'action_focus_target' => '#lsd_panel_ai_profiles, #lsd_settings_ai_add_profile',
                ], [$this, 'ai_profiles']),
        ]);
    }

    protected function listings_exist(?LSD_Checklist_Check_Interface $check = null): array
    {
        $count = $this->helper->published_posts_count(LSD_Base::PTYPE_LISTING);

        return [
            'status' => $count > 0 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_INCOMPLETE,
            'message' => $count > 0 ? sprintf(esc_html__('%d published listings found.', 'listdom'), $count) : esc_html__('Create your first published listing.', 'listdom'),
        ];
    }

    protected function categories_exist(?LSD_Checklist_Check_Interface $check = null): array
    {
        $count = $this->helper->term_count(LSD_Base::TAX_CATEGORY);

        return [
            'status' => $count > 0 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_INCOMPLETE,
            'message' => $count > 0 ? sprintf(esc_html__('%d categories are available.', 'listdom'), $count) : esc_html__('Add at least one listing category.', 'listdom'),
        ];
    }

    protected function important_taxonomies(?LSD_Checklist_Check_Interface $check = null): array
    {
        $locations = $this->helper->term_count(LSD_Base::TAX_LOCATION);
        $features = $this->helper->term_count(LSD_Base::TAX_FEATURE);
        $tags = $this->helper->term_count(LSD_Base::TAX_TAG);
        $configured = $locations + $features + $tags;

        return [
            'status' => $configured > 0 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING,
            'message' => $configured > 0 ? sprintf(esc_html__('Locations: %1$d, features: %2$d, tags: %3$d.', 'listdom'), $locations, $features, $tags) : esc_html__('Locations, features, and tags are still empty.', 'listdom'),
        ];
    }

    protected function directory_page(?LSD_Checklist_Check_Interface $check = null): array
    {
        $published_pages = $this->helper->count_pages_with_shortcodes(['listdom']);

        $available_views = $this->helper->published_shortcode_post_ids('listdom', LSD_Base::PTYPE_SHORTCODE);

        $shortcodes = $this->helper->published_posts_count(LSD_Base::PTYPE_SHORTCODE);

        if ($published_pages > 0)
        {
            return [
                'status' => LSD_Checklist_Result::STATUS_COMPLETE,
                'message' => sprintf(esc_html__('%d published page(s) include Listdom directory output.', 'listdom'), $published_pages),
            ];
        }

        if (count($available_views) > 0)
        {
            return [
                'status' => LSD_Checklist_Result::STATUS_COMPLETE,
                'message' => sprintf(esc_html__('%d published directory view(s) are used through page-builder widgets.', 'listdom'), count($available_views)),
            ];
        }

        return [
            'status' => $shortcodes > 0 ? LSD_Checklist_Result::STATUS_WARNING : LSD_Checklist_Result::STATUS_INCOMPLETE,
            'message' => $shortcodes > 0 ? esc_html__('Directory views exist, but no published page exposes directory output.', 'listdom') : esc_html__('Create a directory view and place it on a published page.', 'listdom'),
        ];
    }

    protected function single_listing_layout(?LSD_Checklist_Check_Interface $check = null): array
    {
        $layout = $this->helper->single_listing_layout_context();

        $available_elements = [
            'title' => esc_html__('Title', 'listdom'),
            'content' => esc_html__('Content', 'listdom'),
            'categories' => esc_html__('Categories', 'listdom'),
            'contact' => esc_html__('Contact', 'listdom'),
            'address' => esc_html__('Address', 'listdom'),
            'map' => esc_html__('Map', 'listdom'),
            'gallery' => esc_html__('Gallery', 'listdom'),
        ];

        $active = [];
        $remaining = [];
        $focus_targets = [];

        foreach ($available_elements as $key => $label)
        {
            if ($this->helper->listing_element_visible($key)) $active[] = $label;
            else
            {
                $remaining[] = $label;
                if (!$layout['is_template_builder']) $focus_targets[] = '#lsd_element_' . $key;
            }
        }

        $enabled = count($active);
        $is_complete = $enabled >= 3;

        if ($is_complete)
        {
            $message = sprintf(esc_html__('%1$d key single-listing sections are enabled. Active: %2$s. Remaining: %3$s.', 'listdom'),
                $enabled,
                implode(', ', $active),
                $remaining ? implode(', ', $remaining) : esc_html__('None', 'listdom')
            );
        }
        else $message = sprintf(esc_html__('Review the single listing layout before going live. Active: %1$s. Remaining: %2$s. Activate at least 3 sections.', 'listdom'), $active ? implode(', ', $active) : esc_html__('None', 'listdom'), implode(', ', $remaining));

        return [
            'status' => $is_complete ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING,
            'message' => $message,
            'action_focus_target' => implode(', ', $focus_targets),
        ];
    }

    protected function ai_profiles(?LSD_Checklist_Check_Interface $check = null): array
    {
        $ai = LSD_Options::ai();
        $profiles = isset($ai['profiles']) && is_array($ai['profiles']) ? $ai['profiles'] : [];

        $configured = [];

        foreach ($profiles as $profile)
        {
            if (!is_array($profile)) continue;

            $id = trim((string) ($profile['id'] ?? ''));
            $api_key = trim((string) ($profile['api_key'] ?? ''));

            if ($id === '' || $api_key === '') continue;

            $configured[] = $profile;
        }

        $count = count($configured);

        return [
            'status' => $count > 0 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $count > 0 ? sprintf(esc_html__('%d usable AI profile(s) are configured.', 'listdom'), $count)
                : esc_html__('AI profiles are optional and none are currently configured.', 'listdom'),
        ];
    }
}
