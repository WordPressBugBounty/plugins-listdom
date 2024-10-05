<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Singlemap $this */
?>
<div class="lsd-skin-wrapper lsd-singlemap-view-wrapper <?php if($this->sm_shortcode) echo 'lsd-search-position-' . esc_attr($this->sm_position); ?> <?php echo esc_attr($this->html_class); ?>">

    <?php if($this->sm_shortcode && in_array($this->sm_position, ['top', 'left', 'right', 'before_listings'])) echo LSD_Kses::form($this->get_search_module()); ?>

    <div class="lsd-singlemap-wrapper">
        <?php if(!count($this->listings)) echo LSD_Kses::page($this->get_not_found_message()); ?>

        <?php echo lsd_map($this->listings, [
            'provider' => $this->map_provider,
            'clustering' => $this->skin_options['clustering'] ?? true,
            'clustering_images' => $this->skin_options['clustering_images'] ?? '',
            'mapstyle' => $this->skin_options['mapstyle'] ?? '',
            'id' => $this->id,
            'onclick' => $this->skin_options['mapobject_onclick'] ?? 'infowindow',
            'mapcontrols' => $this->mapcontrols,
            'atts' => $this->atts,
            'mapsearch' => $this->mapsearch,
            'autoGPS' => $this->autoGPS,
            'max_bounds' => $this->maxBounds,
        ]); ?>
    </div>

    <?php if($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
</div>