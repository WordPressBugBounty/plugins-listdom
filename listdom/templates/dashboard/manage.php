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
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        page: '.json_encode($this->page).',
        nonce: "'.wp_create_nonce('lsd_dashboard').'"
    });
});
</script>');
?>
<div class="lsd-dashboard" id="lsd_dashboard">

    <div class="lsd-row">
        <div class="lsd-col-2 lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($this->menus()); ?>
        </div>
        <div class="lsd-col-10">

            <div class="lsd-row lsd-dashboard-listings-list">
                <div class="lsd-col-12">

                    <?php if(count($this->listings)): ?>
                    <ul>
                        <?php foreach($this->listings as $listing) $this->item($listing); ?>
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

                    <?php else: echo LSD_Base::alert(sprintf(esc_html__("No listing found! %s your first listing now.", 'listdom'), '<a href="'.esc_url($this->get_form_link()).'">'.esc_html__('Add', 'listdom').'</a>'), 'info'); ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>