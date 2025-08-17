<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_dashboard").listdomDashboard(
    {
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        page: ' . json_encode($this->page) . ',
        nonce: "' . wp_create_nonce('lsd_dashboard') . '"
    });
});
</script>');

global $wp_post_statuses;
$counts = $this->listing_counts();
?>
<div class="lsd-dashboard" id="lsd_dashboard">

    <div class="lsd-row">
        <div class="lsd-col-2 lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($this->menus()); ?>
        </div>
        <div class="lsd-col-10 lsd-dashboard-listings-list">
            <form class="lsd-dashboard-search-form" method="get">
                <input type="hidden" name="mode" value="manage">
                <?php if (isset($_GET['status'])): ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr(sanitize_text_field($_GET['status'])); ?>">
                <?php endif; ?>
                <?php echo LSD_Form::taxonomy(LSD_Base::TAX_CATEGORY, [
                    'id' => 'lsd_dashboard_category',
                    'name' => 'lsd_category',
                    'show_empty' => true,
                    'empty_label' => esc_html__('View All', 'listdom'),
                    'value' => $this->category ?: ''
                ]); ?>
                <?php echo LSD_Form::search(['id' => 'lsd_dashboard_search', 'name' => 'lsd_s', 'placeholder' => esc_html__('Search…', 'listdom'), 'value' => $this->search]); ?>
                <button type="submit" class="lsd-neutral-button"><?php esc_html_e('Search', 'listdom'); ?></button>
            </form>

            <?php if (count($counts) && is_array($wp_post_statuses) && count($wp_post_statuses)): ?>
                <ul class="lsd-dashboard-listing-status-filter">
                    <li>
                        <a href="<?php echo (new LSD_Main())->remove_qs_var('status'); ?>"><?php esc_html_e('All', 'listdom'); ?></a>
                    </li>
                    <?php foreach ($counts as $status => $count): if (!$count || !isset($wp_post_statuses[$status])) continue; if (!isset($wp_post_statuses[$status]->show_in_admin_status_list) || !$wp_post_statuses[$status]->show_in_admin_status_list) continue; ?>
                    <li>
                        <a href="<?php echo (new LSD_Main())->add_qs_var('status', $status); ?>"><?php echo esc_html($wp_post_statuses[$status]->label); ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($this->listings)): ?>
                <ul class="lsd-dashboard-listings-list-items">
                    <?php foreach ($this->listings as $listing) $this->item($listing); ?>
                </ul>

                <div class="pagination lsd-pagination">
                    <?php echo paginate_links([
                        'base' => str_replace($this->limit, '%#%', esc_url(get_pagenum_link($this->limit))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $this->q->max_num_pages,
                        'type' => 'list',
                        'prev_next' => true,
                    ]); ?>
                </div>
            <?php else: echo LSD_Base::alert(sprintf(esc_html__("No listing found! %s your first listing now.", 'listdom'), '<a href="' . esc_url($this->get_form_link()) . '">' . esc_html__('Add', 'listdom') . '</a>')); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
