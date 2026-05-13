<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $dashboard */
/** @var WP_Post $post */
/** @var LSD_PTypes_Listing $this */

// If Called by Dashboard
$dashboard = LSD_Payload::get('dashboard');
?>
<div class="lsd-metabox <?php echo LSD_Base::get_lsd_class('sections'); ?>">
    <?php
        if (current_user_can('edit_others_posts') && isset($post->post_status) && $post->post_status === 'pending' && trim(get_post_meta($post->ID, 'lsd_guest_email', true)))
        {
            $this->include_html_file('metaboxes/listing/details/approval.php', [
                'parameters' => compact('post'),
            ]);
        }

        if ((!$dashboard || $dashboard->is_enabled('price')) && LSD_Components::pricing())
        {
            $this->include_html_file('metaboxes/listing/details/price.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if ((!$dashboard || $dashboard->is_enabled('cta')) && LSD_Components::cta())
        {
            $this->include_html_file('metaboxes/listing/details/cta.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if ((!$dashboard || $dashboard->is_enabled('availability')) && LSD_Components::work_hours())
        {
            $this->include_html_file('metaboxes/listing/details/availability.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if (!$dashboard || $dashboard->is_enabled('contact'))
        {
            $this->include_html_file('metaboxes/listing/details/contact.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if (!$dashboard || $dashboard->is_enabled('remark'))
        {
            $this->include_html_file('metaboxes/listing/details/remark.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if (!$dashboard || $dashboard->is_enabled('gallery'))
        {
            $this->include_html_file('metaboxes/listing/details/gallery.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if ($this->isPro() && (!$dashboard || $dashboard->is_enabled('embed')))
        {
            $this->include_html_file('metaboxes/listing/details/embed.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if (!$dashboard || $dashboard->is_enabled('faq'))
        {
            $this->include_html_file('metaboxes/listing/details/faq.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        if ((!$dashboard || $dashboard->is_enabled('related')) && LSD_Components::related())
        {
            $this->include_html_file('metaboxes/listing/details/related.php', [
                'parameters' => compact('dashboard', 'post'),
            ]);
        }

        // Third Party Plugins!
        do_action('lsd_listing_details_metabox', $post, $dashboard);
    ?>
</div>
