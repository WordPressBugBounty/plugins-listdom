<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Listgrid $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomListGridSkin(
    {
        id: "'.$this->id.'",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        next_page: "'.$this->next_page.'",
        limit: "'.$this->limit.'",
        view: "'.$this->default_view.'",
        columns: "'.($this->style === 'style4' ? '2' : $this->columns).'",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-listgrid-view-wrapper <?php echo sanitize_html_class($this->get_bar_class()); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>" data-view="<?php echo esc_attr($this->default_view); ?>">

    <?php echo LSD_Kses::full($this->get_left_bar()); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && $this->sm_position === 'top') echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-list-wrapper">
            <?php
                /**
                 * Top Position of List + Grid View
                 */
                if ($this->map_provider && $this->map_position === 'top')
                {
                    echo '<div class="lsd-listgrid-view-top-wrapper">';
                    echo $this->get_map();
                    echo '</div>';
                }
            ?>

            <?php if ($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

            <?php echo LSD_Kses::form($this->get_switcher_buttons()); ?>

            <div class="lsd-listgrid-view-listings-wrapper lsd-viewstyle-<?php echo esc_attr($this->default_view); ?>">
                <div class="lsd-listing-wrapper <?php echo 'lsd-listgrid-columns-'.$this->columns; ?> <?php echo $this->image_fit === 'contain' ? 'lsd-image-object-contain' : ''; ?>">
                    <?php echo LSD_Kses::full($listings_html); ?>
                </div>
            </div>

            <?php echo LSD_Kses::element($this->get_pagination()); ?>

            <?php
                /**
                 * Bottom Position of List + Grid View
                 */
                if ($this->map_provider && $this->map_position === 'bottom')
                {
                    echo '<div class="lsd-listgrid-view-bottom-wrapper">';
                    echo $this->get_map();
                    echo '</div>';
                }
            ?>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::full($this->get_right_bar()); ?>
</div>
