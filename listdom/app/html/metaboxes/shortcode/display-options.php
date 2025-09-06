<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Listdom Skins
$skins = new LSD_Skins();

// Display Options
$options = get_post_meta($post->ID, 'lsd_display', true);
$selected_skin = $options['skin'] ?? '';

// Price Components
$price_components = LSD_Options::price_components();
?>
<div class="lsd-metabox lsd-metabox-display-options">
    <?php $this->include_html_file('metaboxes/shortcode/tabs.php'); ?>

    <div class="lsd-metabox-display-options-content">
        <div id="lsd_tab_content_skin" class="lsd-tab-content lsd-tab-content-active">
            <div class="lsd-settings-group-wrapper lsd-form-group lsd-skins-form-group lsd-m-0 lsd-p-0">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-admin-section-heading">
                        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Skin", 'listdom'); ?></h3>
                        <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Choose how listings are displayed: grid, list, carousel, map, or other visual layouts.", 'listdom'); ?> </p>
                    </div>
                    <div class="lsd-form-row lsd-m-0">
                    <div class="lsd-col-12">
                        <div class="lsd-skin-style-options">
                            <?php foreach ($skins->get_skins() as $skin_key => $skin_label): ?>
                                <div class="lsd-skin-style-option <?php echo $selected_skin === $skin_key ? 'selected' : ''; ?>" data-skin="<?php echo esc_attr($skin_key); ?>">
                                    <img width="120" src="<?php echo esc_url($this->lsd_asset_url('img/skins/'.$skin_key.'.svg')); ?>" alt="<?php echo esc_attr($skin_label); ?>">
                                    <h4 class="lsd-mt-2 lsd-mb-3"><?php echo esc_html($skin_label); ?></h4>
                                </div>
                            <?php endforeach; ?>
                            <input type="hidden" name="lsd[display][skin]" id="lsd_display_options_skin" value="<?php echo esc_attr($options['skin'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                      <?php foreach ($skins->get_skins() as $skin => $label): ?>
                          <div class="lsd-form-row lsd-skin-content lsd-m-0 <?php echo $selected_skin === $skin ? '':'lsd-util-hide'; ?>" data-skin="<?php echo esc_attr($skin); ?>">
                              <?php $this->include_html_file('metaboxes/shortcode/display-options/skin/'.$skin.'.php'); ?>
                          </div>
                      <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div id="lsd_tab_content_elements" class="lsd-tab-content">
            <div id="lsd_skin_display_options_container">
                <?php foreach ($skins->get_skins() as $skin => $label): ?>
                <div class="lsd-skin-display-options" id="lsd_skin_display_options_<?php echo esc_attr($skin); ?>">
                    <?php $this->include_html_file('metaboxes/shortcode/display-options/elements/'.$skin.'.php', [
                        'parameters' => [
                            'options' => $options,
                            'skin' => $skin,
                            'price_components' => $price_components,
                        ]
                    ]); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="lsd-display-options-builder-skin <?php echo is_numeric($list['style'] ?? 'style1') ? '' : 'lsd-util-hide'; ?>">
                <p class="lsd-alert lsd-info"><?php esc_html_e("Because you're using a custom style, certain display options in the shortcode will be turned off. You can adjust them in the custom layout settings.", 'listdom'); ?></p>
            </div>
        </div>
        <div id="lsd_tab_content_map" class="lsd-tab-content">
            <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex" data-for=".lsd-tab-switcher-content-map">
                <li data-tab="map" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Settings', 'listdom'); ?></a></li>
                <li data-tab="map-controls" class="lsd-metabox-map-controls-tab"><a href="#"><?php esc_html_e('Google Maps Controls', 'listdom'); ?></a></li>
            </ul>
            <div class="lsd-tab-content-map-controls">
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-map lsd-tab-switcher-content-active" id="lsd-tab-switcher-map-content">
                    <?php foreach ($skins->get_skins() as $skin => $label): ?>
                        <div class="lsd-skin-display-options lsd_skins_map_options" id="lsd_skin_display_options_map_<?php echo esc_attr($skin); ?>">
                            <?php $this->include_html_file('metaboxes/shortcode/display-options/map/'.$skin.'.php', [
                                'parameters' => [
                                    'options' => $options,
                                    'price_components' => $price_components,
                                ]
                            ]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (LSD_Components::map()) include $this->include_html_file('metaboxes/shortcode/map-controls.php', ['return_path' => true]); ?>
            </div>
        </div>
        <div id="lsd_tab_content_layout" class="lsd-tab-content">
            <?php foreach ($skins->get_skins() as $skin => $label): ?>
                <div class="lsd-skin-display-options" id="lsd_skin_display_options_layout_<?php echo esc_attr($skin); ?>">
                    <?php $this->include_html_file('metaboxes/shortcode/display-options/layout/'.$skin.'.php', [
                        'parameters' => [
                            'options' => $options,
                            'price_components' => $price_components,
                        ]
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="lsd_tab_content_sort" class="lsd-tab-content">
            <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex" data-for=".lsd-tab-switcher-content-sort">
                <li data-tab="default-sort" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Default Sort', 'listdom'); ?></a></li>
                <li data-tab="sort" id="lsd_metabox_sort_options_tab"><a href="#"><?php esc_html_e('Sort Options', 'listdom'); ?></a></li>
            </ul>
            <div class="lsd-tab-switcher-content lsd-tab-switcher-content-sort lsd-tab-switcher-content-active" id="lsd-tab-switcher-default-sort-content">
                <div><?php include $this->include_html_file('metaboxes/shortcode/default-sort.php', ['return_path' => true]); ?></div>
            </div>
            <div class="lsd-tab-switcher-content lsd-tab-switcher-content-sort" id="lsd-tab-switcher-sort-content">
                <div><?php include $this->include_html_file('metaboxes/shortcode/sort-options.php', ['return_path' => true]); ?></div>
            </div>
        </div>
        <div id="lsd_tab_content_search" class="lsd-tab-content">
            <?php include $this->include_html_file('metaboxes/shortcode/search.php', ['return_path' => true]);?>
        </div>
        <div id="lsd_tab_content_filter-options" class="lsd-tab-content">
            <?php include $this->include_html_file('metaboxes/shortcode/filter-options.php', ['return_path' => true]); ?>
        </div>

    </div>
</div>
