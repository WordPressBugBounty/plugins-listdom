<?php

class LSD_Checklist_Provider_Addons extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $this->register_checks($registry, [
            $this->check([
                'id' => 'claim_settings',
                'title' => esc_html__('Claim settings', 'listdom'),
                'description' => esc_html__('Claim flows are useful when businesses or owners should verify ownership.', 'listdom'),
                'category' => $this->category( esc_html__('Monetization', 'listdom')),
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => $this->addon_action_url('claim'),
            ], [$this, 'claim_settings']),
            $this->check([
                'id' => 'featured_rank',
                'title' => esc_html__('Visibility upgrade options', 'listdom'),
                'description' => esc_html__('Upsell mechanics can be added later, but they should be explicit if you plan to sell visibility.', 'listdom'),
                'category' => $this->category( esc_html__('Monetization', 'listdom')),
                'importance' => 'low',
                'optional' => true,
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => $this->visibility_addon_action_url(),
            ], [$this, 'featured_rank']),
            $this->check([
                'id' => 'reviews',
                'title' => esc_html__('Reviews', 'listdom'),
                'description' => esc_html__('Reviews can improve trust and repeat engagement when they match your business model.', 'listdom'),
                'category' => $this->category( esc_html__('Trust & Engagement', 'listdom')),
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => $this->addon_action_url('reviews'),
            ], [$this, 'reviews']),
            $this->check([
                'id' => 'verified_badge',
                'title' => esc_html__('Claim and verified badge', 'listdom'),
                'description' => esc_html__('Verified ownership is optional, but useful for trust-heavy directories.', 'listdom'),
                'category' => $this->category( esc_html__('Trust & Engagement', 'listdom')),
                'importance' => 'low',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->addon_action_url('claim'),
            ], [$this, 'verified_badge']),
        ]);
    }

    protected function claim_settings(?LSD_Checklist_Check_Interface $check = null): array
    {
        $claim_active = $this->addon_active('claim');

        return [
            'status' => $claim_active ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $claim_active ? esc_html__('Claim addon is active.', 'listdom') : esc_html__('Claim flow is optional and currently disabled.', 'listdom'),
        ];
    }

    protected function featured_rank(?LSD_Checklist_Check_Interface $check = null): array
    {
        $addon = $this->active_visibility_addon();

        return [
            'status' => $addon ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $addon ? esc_html__('At least one ranking or upgrade addon e.g. Topup, Labelize, Rank is active.', 'listdom') : esc_html__('Visibility upsells are optional and not enabled e.g. Topup, Labelize, Rank.', 'listdom'),
        ];
    }

    protected function reviews(?LSD_Checklist_Check_Interface $check = null): array
    {
        $active = $this->addon_active('reviews');
        $options = LSD_Options::addons('reviews');
        $guest_readable = ($options['read_permission'] ?? 'guest') === 'guest';
        $visible = $this->helper->listing_element_visible('discussion') && $guest_readable;
        $layout = $this->helper->single_listing_layout_context();
        $rating_summary = in_array((string) ($layout['style'] ?? ''), ['style2', 'style3', 'style4'], true);
        $reviews = $active && ($visible || $rating_summary);

        return [
            'status' => $reviews ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $reviews ? esc_html__('Reviews addon is active.', 'listdom') : esc_html__('Reviews are optional and currently disabled.', 'listdom'),
        ];
    }

    protected function verified_badge(?LSD_Checklist_Check_Interface $check = null): array
    {
        $active = $this->addon_active('claim');
        $claim = LSD_Options::addons('claim');

        // Match the Claim add-on runtime default.
        $visible = !isset($claim['display_verified']) || (bool) $claim['display_verified'];
        $badge = $active && $visible;

        return [
            'status' => $badge ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $badge ? esc_html__('Claim badge support is available.', 'listdom') : esc_html__('Verified badges are optional and not enabled.', 'listdom'),
        ];
    }

    protected function active_visibility_addon(): string
    {
        foreach (['topup', 'labelize', 'rank'] as $key)
        {
            if ($this->addon_active($key)) return $key;
        }

        return '';
    }

    protected function visibility_addon_action_url(): string
    {
        $addon = $this->active_visibility_addon();
        return $addon !== '' ? $this->addon_action_url($addon) : admin_url('admin.php?page=listdom-addons');
    }
}
