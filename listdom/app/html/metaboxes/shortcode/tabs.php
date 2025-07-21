<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
?>
<div class="lsd-nav-tab-wrapper lsd-tabs">
    <button type="button" class="nav-tab lsd-neutral-button nav-tab-active lsd-display-options-skin-tab" data-key="skin">
        <i class="lsdi lsdi-layout"></i>
        <?php esc_html_e('Skin', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-elements-tab" data-key="elements">
        <i class="lsdi lsdi-dashboard-circle-edit"></i>
        <?php esc_html_e('Style', 'listdom'); ?>
    </button>
    <?php if (LSD_Components::map()): ?>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-map-tab" data-key="map">
        <i class="lsdi lsdi-map-pinpoint"></i>
        <?php esc_html_e('Map', 'listdom'); ?>
    </button>
    <?php endif; ?>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-layout-tab" data-key="layout">
        <i class="lsdi lsdi-group-items"></i>
        <?php esc_html_e('Layout', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-sort-tab" data-key="sort">
        <i class="lsdi lsdi-sort-by-up"></i>
        <?php esc_html_e('Sort', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-search-tab" data-key="search">
        <i class="lsdi lsdi-search"></i>
        <?php esc_html_e('Search', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-filter-options-tab" data-key="filter-options">
        <i class="lsdi lsdi-structure-check"></i>
        <?php esc_html_e('Filter Options', 'listdom'); ?>
    </button>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_shortcode_settings_tabs', $this);
    ?>
</div>
