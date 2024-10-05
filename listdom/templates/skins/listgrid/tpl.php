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
        columns: "'.$this->columns.'",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-listgrid-view-wrapper <?php if($this->sm_shortcode) echo 'lsd-search-position-' . esc_attr($this->sm_position); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>" data-view="<?php echo esc_attr($this->default_view); ?>">

    <?php if($this->sm_shortcode && in_array($this->sm_position, ['top', 'left', 'right'])) echo LSD_Kses::form($this->get_search_module()); ?>

    <div class="lsd-list-wrapper">
        <?php
            /**
             * Top Position of List + Grid View
             */
            if($this->map_provider && isset($this->skin_options['map_position']) && $this->skin_options['map_position'] === 'top')
            {
                echo '<div class="lsd-listgrid-view-top-wrapper">';
                echo $this->get_map();
                echo '</div>';
            }
        ?>

        <?php if($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

        <?php echo LSD_Kses::form($this->get_switcher_buttons()); ?>

        <div class="lsd-listgrid-view-listings-wrapper lsd-viewstyle-<?php echo esc_attr($this->default_view); ?>">
            <div class="lsd-listing-wrapper">
                <?php echo LSD_Kses::full($listings_html); ?>
            </div>
        </div>

        <?php echo LSD_Kses::element($this->get_pagination()); ?>

        <?php
            /**
             * Bottom Position of List + Grid View
             */
            if($this->map_provider && isset($this->skin_options['map_position']) && $this->skin_options['map_position'] === 'bottom')
            {
                echo '<div class="lsd-listgrid-view-bottom-wrapper">';
                echo $this->get_map();
                echo '</div>';
            }
        ?>
    </div>

    <?php if($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
</div>