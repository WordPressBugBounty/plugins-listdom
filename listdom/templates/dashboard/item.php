<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var WP_Post $listing */

$status = get_post_status_object(get_post_status($listing->ID));
?>
<li id="lsd_dashboard_listing_<?php echo esc_attr($listing->ID); ?>">
    <div class="lsd-row">
        <div class="lsd-col-6">

            <span class="lsd-dashboard-status" title="<?php echo esc_attr($status->label); ?>">
                <?php if($listing->post_status == 'publish'): ?>
                    <i class="lsd-icon far fa-check-circle"></i>
                <?php elseif($listing->post_status == 'trash'): ?>
                    <i class="lsd-icon fas fa-trash-alt"></i>
                <?php else: ?>
                    <i class="lsd-icon far fa-file-alt"></i>
                <?php endif; ?>
            </span>

            <?php if(LSD_Capability::can('edit_listings', 'edit_posts')): ?>
                <a class="lsd-dashboard-edit-link" href="<?php echo esc_url($this->get_form_link($listing->ID)); ?>"><?php echo get_the_title($listing->ID); ?></a>
            <?php else: echo get_the_title($listing->ID); ?>
            <?php endif; ?>

        </div>
        <div class="lsd-col-6 lsd-dashboard-listing-actions">

            <span class="lsd-dashboard-actions">
                <?php do_action('lsd_dashboard_actions', $listing, $this); ?>
            </span>

            <a class="lsd-dashboard-view" href="<?php echo esc_url(get_post_permalink($listing->ID)); ?>" target="_blank" title="<?php esc_attr_e('View', 'listdom'); ?>">
                <i class="lsd-icon fa fa-eye"></i>
            </a>

            <?php if(LSD_Capability::can('edit_listings', 'edit_posts')): ?>
            <a class="lsd-dashboard-view" href="<?php echo esc_url($this->get_form_link($listing->ID)); ?>" title="<?php esc_attr_e('Edit', 'listdom'); ?>">
                <i class="lsd-icon fa fa-edit"></i>
            </a>
            <?php endif; ?>

            <?php if(LSD_Capability::can('delete_listings', 'delete_posts')): ?>
                <span class="lsd-dashboard-delete" data-id="<?php echo esc_attr($listing->ID); ?>" data-confirm="0" title="<?php esc_attr_e('Delete', 'listdom'); ?>"><i class="lsd-icon fas fa-trash-alt"></i></span>
            <?php endif; ?>
        </div>
    </div>

    <?php do_action('lsd_dashboard_after_listing', $listing, $this); ?>
</li>