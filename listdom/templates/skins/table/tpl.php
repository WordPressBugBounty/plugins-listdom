<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Fields
$fields = new LSD_Fields();

$columns = isset($this->skin_options['columns']) && is_array($this->skin_options['columns']) && count($this->skin_options['columns'])
	? $this->skin_options['columns']
	: $fields->get();

$titles = $fields->titles($columns);

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomTableSkin(
    {
        id: "'.$this->id.'",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        next_page: "'.$this->next_page.'",
        limit: "'.$this->limit.'",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-table-view-wrapper <?php if($this->sm_shortcode) echo 'lsd-search-position-' . esc_attr($this->sm_position); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>">

    <?php if($this->sm_shortcode && in_array($this->sm_position, ['top', 'left', 'right'])) echo LSD_Kses::form($this->get_search_module()); ?>

	<div class="lsd-list-wrapper">
		<?php echo LSD_Kses::form($this->get_sortbar()); ?>

		<?php if($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

		<div class="lsd-table-view-listings-wrapper">
			<div class="lsd-listing-wrapper">
				<table class="lsd-listing-table">
					<thead>
						<tr class="lsd-listing-head">
                            <?php foreach ($titles as $key => $title): ?>
                                <?php if (isset($columns[$key]['enabled']) && $columns[$key]['enabled'] == '1'): ?>
                                    <th>
                                        <?php esc_html_e($title, 'listdom'); ?>
                                    </th>
                                <?php endif; ?>
                            <?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php echo LSD_Kses::full($listings_html); ?>
					</tbody>
				</table>
			</div>
		</div>

		<?php echo LSD_Kses::element($this->get_pagination()); ?>
	</div>

	<?php if($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
</div>
