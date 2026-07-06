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
        page: ' . wp_json_encode($this->page) . ',
        nonce: "' . wp_create_nonce('lsd_dashboard') . '"
    });
});
</script>');

global $wp_post_statuses;
$counts = $this->listing_counts();
$current_status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';

$dashboard_wrapper = $this->get_dashboard_wrapper();
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="lsd_dashboard"<?php echo $dashboard_wrapper['attributes']; ?>>

    <div class="lsd-row lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($this->menus()); ?>
        </div>
        <div class="lsd-dashboard-content-wrapper lsd-dashboard-listings-list">
            <div class="lsd-fe-section-heading">
                <div class="lsd-fe-section-heading">
                    <div class="lsd-fe-title-icon">
                        <i class="lsd-fe-icon fa-solid fa-list-dots"></i>
                        <h2 class="lsd-fe-title"><?php echo esc_html__('My Listings', 'listdom'); ?></h2>
                    </div>
                    <p class="lsd-fe-description"><?php esc_html_e('Manage your listings and promot them', 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-fe-box-white">
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
                    <?php echo LSD_Form::search(['id' => 'lsd_dashboard_search', 'name' => 'lsd_s', 'placeholder' => esc_attr__('Search…', 'listdom'), 'value' => $this->search]); ?>
                    <button type="submit" class="lsd-search-button"><?php esc_html_e('Search', 'listdom'); ?></button>
                </form>
                <?php if (count($counts) && is_array($wp_post_statuses) && count($wp_post_statuses)): ?>
                    <div class="lsd-dashboard-listing-status-filter lsd-fe-tabs">
                        <ul class="lsd-fe-tabs-nav">
                            <li class="<?php echo $current_status === '' ? 'lsd-active' : ''; ?>">
                                <a href="<?php echo (new LSD_Main())->remove_qs_var('status'); ?>"><?php esc_html_e('All', 'listdom'); ?></a>
                            </li>
                            <?php foreach ($counts as $status => $count): if (!$count || !isset($wp_post_statuses[$status])) continue; if (!isset($wp_post_statuses[$status]->show_in_admin_status_list) || !$wp_post_statuses[$status]->show_in_admin_status_list) continue; ?>
                                <li class="<?php echo $current_status === $status ? 'lsd-active' : ''; ?>">
                                    <a href="<?php echo (new LSD_Main())->add_qs_var('status', $status); ?>"><?php echo esc_html($wp_post_statuses[$status]->label); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

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
            <?php else: ?>
                <div class="lsd-fe-box-white">
                    <?php $has_filters = !empty($this->search) || !empty($this->category) || $current_status !== '';

                    $this->empty([
                        'title' => $has_filters ? esc_html__('No listings match current filter', 'listdom') : esc_html__('No listings yet', 'listdom'),
                        'description' => $has_filters ? esc_html__('Try changing your search or category filter to find more listings.', 'listdom') : esc_html__('Create your first listing and start managing it from your dashboard.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'action' => $has_filters ? [] : [
                            'label' => esc_html__('Start Adding Your First Listing', 'listdom'),
                            'url' => $this->get_form_link(),
                            'class' => 'lsd-general-button',
                        ],
                        'quick_actions' => [],
                    ]); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
