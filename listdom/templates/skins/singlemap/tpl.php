<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Singlemap $this */

// Add Single Map Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomSinglemapSkin(
    {
        id: "'.$this->id.'",
        sidebar: "'.($this->sidebar ? '1' : '0').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-singlemap-view-wrapper <?php echo sanitize_html_class($this->get_bar_class()); ?> <?php echo esc_attr($this->html_class); ?>" id="lsd_skin<?php echo esc_attr($this->id); ?>">
    <?php echo LSD_Kses::full($this->get_left_bar()); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && in_array($this->sm_position, ['top', 'before_listings'])) echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-singlemap-wrapper <?php echo $this->sidebar ? 'lsd-singlemap-has-sidebar' : ''; ?>" <?php if ($this->map_height) echo 'style="height: '.esc_attr($this->map_height).';"'; ?>>
            <?php if ($this->sidebar): ?>
                <div class="lsd-map-sidebar-wrapper <?php echo $this->sidebar_state === 'closed_optional' ? '' : 'lsd-map-sidebar-open'; ?>">
                    <?php if (in_array($this->sidebar_state, ['open_optional', 'closed_optional'])): ?>
                    <div class="lsd-map-sidebar-toggle">
                        <span class="lsd-map-sidebar-toggle-close-icon"><</span>
                        <span class="lsd-map-sidebar-toggle-open-icon">></span>
                    </div>
                    <?php endif; ?>
                    <div class="lsd-map-sidebar">
                        <div class="lsd-map-sidebar-inner">
                            <div class="lsd-map-sidebar-listings" data-limit="<?php echo esc_attr($this->tablet_limit); ?>"></div>
                            <div class="lsd-map-sidebar-loadmore">
                                <button type="button" class="lsd-general-button lsd-loadmore-button"><?php esc_html_e('Load More', 'listdom'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="lsd-map-wrapper">
                <?php echo lsd_map($this->listings, [
                    'provider' => $this->map_provider,
                    'clustering' => $this->skin_options['clustering'] ?? true,
                    'clustering_images' => $this->skin_options['clustering_images'] ?? '',
                    'mapstyle' => $this->skin_options['mapstyle'] ?? '',
                    'id' => $this->id,
                    'sidebar' => $this->sidebar,
                    'onclick' => $this->skin_options['mapobject_onclick'] ?? 'infowindow',
                    'mapcontrols' => $this->mapcontrols,
                    'map_height' => $this->map_height,
                    'atts' => $this->atts,
                    'mapsearch' => $this->mapsearch,
                    'autoGPS' => $this->autoGPS,
                    'max_bounds' => $this->maxBounds,
                    'connected_shortcodes' => $this->connected_shortcodes,
                    'force_to_show' => true,
                    'ignore_map_exclusion' => $this->ignore_map_exclusion,
                ]); ?>
            </div>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::full($this->get_right_bar()); ?>
</div>
