<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$listgrid = $options['listgrid'] ?? [];
?>
<?php if (LSD_Components::map()): ?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Map Structure", 'listdom'); ?></h3>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Map Provider', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_map_provider',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::providers([
                    'id' => 'lsd_display_options_skin_listgrid_map_provider',
                    'name' => 'lsd[display][listgrid][map_provider]',
                    'value' => $listgrid['map_provider'] ?? LSD_Map_Provider::def(),
                    'disabled' => true,
                    'class' => 'lsd-map-provider-toggle lsd-admin-input',
                    'attributes' => [
                        'data-parent' => '#lsd_skin_display_options_map_listgrid'
                    ]
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-group lsd_display_options_skin_listgrid_map_options lsd-form-row-map-needed lsd-p-0 lsd-m-0 <?php echo isset($listgrid['map_provider']) && $listgrid['map_provider'] ? '' : 'lsd-util-hide'; ?>">
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Position', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_map_position',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::select([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_listgrid_map_position',
                        'name' => 'lsd[display][listgrid][map_position]',
                        'options' => [
                            'top' => esc_html__('Show before the List + Grid view', 'listdom'),
                            'bottom' => esc_html__('Show after the List + Grid view', 'listdom'),
                            'left' => esc_html__('Show on left', 'listdom'),
                            'right' => esc_html__('Show on right', 'listdom')
                        ],
                        'value' => $listgrid['map_position'] ?? 'top'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Map Style', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_mapstyle',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::mapstyle([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_listgrid_mapstyle',
                        'name' => 'lsd[display][listgrid][mapstyle]',
                        'value' => $listgrid['mapstyle'] ?? ''
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Map Height', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_map_height',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::text([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_listgrid_map_height',
                        'name' => 'lsd[display][listgrid][map_height]',
                        'value' => $listgrid['map_height'] ?? ''
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Use this option to set the map height. Enter a value with units, such as 500px or 100vh. If you're unsure, leave it blank.", 'listdom'); ?></p>
                </div>
            </div>

            <?php
            // Action for KML Layers
            do_action('lsd_shortcode_kml_layers_map_options', 'listgrid', $options);
            ?>
        </div>
    </div>

    <div class="lsd-form-group lsd_display_options_skin_listgrid_map_options lsd-settings-group-wrapper lsd-form-row-map-needed lsd-p-0 lsd-m-0 <?php echo isset($listgrid['map_provider']) && $listgrid['map_provider'] ? '' : 'lsd-util-hide'; ?>">
        <div class="lsd-settings-fields-wrapper">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Markers", 'listdom'); ?></h3>

            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Marker/Shape On Click', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_mapobject_onclick',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::select([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_listgrid_mapobject_onclick',
                        'name' => 'lsd[display][listgrid][mapobject_onclick]',
                        'options' => [
                            'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                            'redirect' => esc_html__('Redirect to Single Listing Page', 'listdom'),
                            'lightbox' => esc_html__('Open Single Listing in a Lightbox', 'listdom'),
                            'none' => esc_html__('None', 'listdom')
                        ],
                        'value' => $listgrid['mapobject_onclick'] ?? 'infowindow'
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("You can choose to display an info window when someone clicks on a marker or shape on the map, open the single listing page directly, or show the details in a lightbox without reloading the page.", 'listdom'); ?></p>
                </div>
            </div>
            <?php
            // Action for Third Party Plugins
            do_action('lsd_shortcode_map_options', 'listgrid', $options);
            ?>
        </div>

        <div class="lsd-settings-fields-wrapper">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Clustering", 'listdom'); ?></h3>

            <div class="lsd-form-row lsd-flex-align-items-center">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Enable', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_clustering',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_clustering',
                        'toggle' => '#lsd_display_options_skin_listgrid_clustering_options',
                        'name' => 'lsd[display][listgrid][clustering]',
                        'value' => $listgrid['clustering'] ?? '1'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div id="lsd_display_options_skin_listgrid_clustering_options" <?php echo ((!isset($listgrid['clustering']) || $listgrid['clustering']) ? '' : 'style="display: none;"'); ?>>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Bubbles', 'listdom'),
                                'for' => 'lsd_display_options_skin_listgrid_clustering_images',
                            ]); ?></div>
                        <div class="lsd-col-7">
                            <?php echo LSD_Form::select([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_display_options_skin_listgrid_clustering_images',
                                'name' => 'lsd[display][listgrid][clustering_images]',
                                'options' => LSD_Base::get_clustering_icons(),
                                'value' => $listgrid['clustering_images'] ?? 'img/cluster1/m'
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-settings-fields-wrapper">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Search & Location", 'listdom'); ?></h3>

            <div class="lsd-form-row lsd-flex-align-items-center lsd-map-provider-dependency lsd-map-provider-dependency-googlemap lsd-map-provider-dependency-leaflet">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Map Search', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_mapsearch',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php if ($this->isPro()): ?>
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_display_options_skin_listgrid_mapsearch',
                            'name' => 'lsd[display][listgrid][mapsearch]',
                            'value' => $listgrid['mapsearch'] ?? '1',
                        ]); ?>
                    <?php else: ?>
                        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
                    <?php endif; ?>
                </div>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
            </div>

            <?php
            // Action for Third Party Plugins
            do_action('lsd_shortcode_auto_gps_map_options', 'listgrid', $options);
            ?>
        </div>

        <div class="lsd-settings-fields-wrapper">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Restrictions & Rules", 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Map Limit', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_maplimit',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::text([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_listgrid_maplimit',
                        'name' => 'lsd[display][listgrid][maplimit]',
                        'value' => $listgrid['maplimit'] ?? '300'
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("This option controls the number of items displayed on the map. Increasing the limit beyond 300 may significantly slow down the page loading time. We recommend using filter options to include only the listings you want in this shortcode.", 'listdom'); ?></p>
                </div>
            </div>

            <?php
            // Action for Third Party Plugins
            do_action('lsd_shortcode_restrictions_map_options', 'listgrid', $options);
            ?>
        </div>
    </div>
</div>
<?php endif;
