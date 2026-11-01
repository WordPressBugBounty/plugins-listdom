<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
?>
<div class="lsd-nav-tab-wrapper lsd-tabs wui">
    <button type="button" class="nav-tab lsd-neutral-button nav-tab-active lsd-display-options-skin-tab" data-key="skin">
        <i class="wui-icon wui-icon--layout"></i>
        <?php esc_html_e('Skin', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-elements-tab" data-key="elements">
        <i class="wui-icon wui-icon--dashboard-circle-edit"></i>
        <?php esc_html_e('Style', 'listdom'); ?>
    </button>
    <?php if (LSD_Components::map()): ?>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-map-tab" data-key="map">
        <i class="wui-icon wui-icon--map-pinpoint"></i>
        <?php esc_html_e('Map', 'listdom'); ?>
    </button>
    <?php endif; ?>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-layout-tab" data-key="layout">
        <i class="wui-icon wui-icon--group-items"></i>
        <?php esc_html_e('Layout', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-sort-tab" data-key="sort">
        <i class="wui-icon wui-icon--sort-by-up"></i>
        <?php esc_html_e('Sort', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-search-tab" data-key="search">
        <i class="wui-icon wui-icon--search"></i>
        <?php esc_html_e('Search', 'listdom'); ?>
    </button>
    <button type="button" class="nav-tab lsd-neutral-button lsd-display-options-filter-options-tab" data-key="filter-options">
        <i class="wui-icon wui-icon--structure-check"></i>
        <?php esc_html_e('Filter Options', 'listdom'); ?>
    </button>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_shortcode_settings_tabs', $this);
    ?>
</div>
