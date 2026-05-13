<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

// Columns metadata
$display = $this->get_table_columns_display();
$fields = $display['fields'];
$titles = $display['titles'];

$columns_config = $this->get_table_columns_config();

$columns_config_json = wp_json_encode($columns_config);
if (!$columns_config_json) $columns_config_json = '{}';

// Get HTML of Listings
$listings_html = $this->listings_html();

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin' . $this->id . '").listdomTableSkin(
    {
        id: "' . $this->id . '",
        load_more: "' . ($this->pagination === 'loadmore' ? '1' : '0') . '",
        infinite_scroll: "' . ($this->pagination === 'scroll' ? '1' : '0') . '",
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        atts: "' . http_build_query(['atts' => $this->atts], '', '&') . '",
        next_page: "' . $this->next_page . '",
        limit: "' . $this->limit . '",
        columns: ' . $columns_config_json . ',
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-table-view-wrapper <?php echo sanitize_html_class($this->get_bar_class()); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>">
    <?php echo LSD_Kses::full($this->get_left_bar()); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && $this->sm_position === 'top') echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-list-wrapper">
            <?php echo LSD_Kses::form($this->get_sortbar()); ?>

            <?php if ($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

            <div class="lsd-h-scroll-shadow-wrapper">
                <div class="lsd-h-scroll-shadow lsd-h-scroll-shadow-left"></div>
                <div class="lsd-h-scroll-shadow lsd-h-scroll-shadow-right"></div>
                <div class="lsd-h-scroll-shadow-content lsd-table-view-listings-wrapper">
                    <table class="lsd-listing-table ">
                        <thead>
                            <tr class="lsd-listing-head">
                                <?php foreach ($display['columns_union'] as $key => $column): ?>
                                    <?php
                                    $key_class = sanitize_html_class($key);
                                    $enabled = isset($display['desktop_enabled'][$key]) ? (int) $display['desktop_enabled'][$key] : 0;
                                    $width = isset($display['desktop_width'][$key]) ? (int) $display['desktop_width'][$key] : 150;
                                    if ($width <= 0) $width = 150;

                                    $classes = ['lsd-table-column', 'lsd-table-column-' . $key_class];
                                    if (!$enabled) $classes[] = 'lsd-table-column-hidden';
                                    ?>
                                    <th class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-column="<?php echo esc_attr($key); ?>" style="width: <?php echo esc_attr($width); ?>px;">
                                        <?php echo esc_html($titles[$key] ?? ''); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="lsd-listing-wrapper">
                            <?php echo LSD_Kses::full($listings_html); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php echo LSD_Kses::element($this->get_pagination()); ?>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::full($this->get_right_bar()); ?>
</div>
