<?php

class LSD_Checklist_Provider_Trust extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('Trust & Engagement', 'listdom'));
        $layout = $this->helper->single_listing_layout_context();

        $this->register_checks($registry, [
            $this->check([
                'id' => 'report_abuse',
                'title' => esc_html__('Report abuse', 'listdom'),
                'description' => esc_html__('Public directories often benefit from a visible report channel.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $layout['action_url'],
                'action_focus_target' => $layout['is_template_builder'] ? '' : '#lsd_element_abuse',
                ], [$this, 'report_abuse']),
            $this->check([
                'id' => 'contact_leads',
                'title' => esc_html__('Contact and lead options', 'listdom'),
                'description' => esc_html__('Listings should provide a clear path for contact or conversion.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $layout['action_url'],
                'action_focus_target' => $layout['is_template_builder'] ? '' : '#lsd_element_contact',
                ], [$this, 'contact_leads']),
        ]);
    }

    protected function report_abuse(?LSD_Checklist_Check_Interface $check = null): array
    {
        $enabled = $this->helper->listing_element_visible('abuse');
        $notifications = count((new LSD_Notifications())->get('lsd_listing_report_abuse'));

        if (!$enabled) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Abuse reporting is optional and disabled.', 'listdom'),];
        if ($notifications === 0) return ['status' => LSD_Checklist_Result::STATUS_WARNING, 'message' => esc_html__('Abuse reporting is enabled, but no abuse notification is published.', 'listdom'),];

        return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('Abuse form and notifications are available.', 'listdom'),];
    }

    protected function contact_leads(?LSD_Checklist_Check_Interface $check = null): array
    {
        $contact_element = $this->helper->listing_element_visible('contact');
        $cta_element = $this->helper->listing_element_visible('cta');
        $lead_path = $contact_element || $cta_element;

        return [
            'status' => $lead_path ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING,
            'message' => $lead_path ? esc_html__('A public contact or conversion path is enabled on listing pages.', 'listdom') : esc_html__('No public contact or conversion path was detected on listing pages.', 'listdom'),
        ];
    }
}
