<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$halfmap = $options['halfmap'] ?? [];
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Map", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Configure map provider, controls, display behavior, marker actions, and map-based filters.", 'listdom'); ?> </p>
<?php if (LSD_Components::map()): ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Provider', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_map_provider',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::providers([
            'id' => 'lsd_display_options_skin_halfmap_map_provider',
            'name' => 'lsd[display][halfmap][map_provider]',
            'value' => $halfmap['map_provider'] ?? LSD_Map_Provider::def(),
            'class' => 'lsd-map-provider-toggle',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_map_halfmap'
            ]
        ]); ?>
    </div>
</div>
<div class="lsd-form-group lsd-form-row-map-needed lsd-p-0 lsd-m-0" id="lsd_display_options_skin_halfmap_map_options">
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Style', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_mapstyle',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::mapstyle([
                'id' => 'lsd_display_options_skin_halfmap_mapstyle',
                'name' => 'lsd[display][halfmap][mapstyle]',
                'value' => $halfmap['mapstyle'] ?? ''
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Clustering', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_clustering',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_display_options_skin_halfmap_clustering',
                'toggle' => '#lsd_display_options_skin_halfmap_clustering_options',
                'name' => 'lsd[display][halfmap][clustering]',
                'value' => $halfmap['clustering'] ?? '1'
            ]); ?>
        </div>
    </div>
    <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div id="lsd_display_options_skin_halfmap_clustering_options" <?php echo ((!isset($halfmap['clustering']) || $halfmap['clustering']) ? '' : 'style="display: none;"'); ?>>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Bubbles', 'listdom'),
                    'for' => 'lsd_display_options_skin_halfmap_clustering_images',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_halfmap_clustering_images',
                        'name' => 'lsd[display][halfmap][clustering_images]',
                        'options' => LSD_Base::get_clustering_icons(),
                        'value' => $halfmap['clustering_images'] ?? 'img/cluster1/m'
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Marker/Shape On Click', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_mapobject_onclick',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_halfmap_mapobject_onclick',
                'name' => 'lsd[display][halfmap][mapobject_onclick]',
                'options' => [
                    'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                    'redirect' => esc_html__('Redirect to Single Listing Page', 'listdom'),
                    'lightbox' => esc_html__('Open Single Listing in a Lightbox', 'listdom'),
                    'none' => esc_html__('None', 'listdom')
                ],
                'value' => $halfmap['mapobject_onclick'] ?? 'infowindow'
            ]); ?>
            <p class="description"><?php esc_html_e("You can choose to display an info window when someone clicks on a marker or shape on the map, open the single listing page directly, or show the details in a lightbox without reloading the page.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Search', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_mapsearch',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php if ($this->isPro()): ?>
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_halfmap_mapsearch',
                    'name' => 'lsd[display][halfmap][mapsearch]',
                    'value' => $halfmap['mapsearch'] ?? '1',
                ]); ?>
                <p class="description"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
            <?php else: ?>
                <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Limit', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_maplimit',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_halfmap_maplimit',
                'name' => 'lsd[display][halfmap][maplimit]',
                'value' => $halfmap['maplimit'] ?? '300'
            ]); ?>
            <p class="description"><?php esc_html_e("This option controls the number of items displayed on the map. Increasing the limit beyond 300 may significantly slow down the page loading time. We recommend using filter options to include only the listings you want in this shortcode.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Position', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_map_position',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_halfmap_map_position',
                'name' => 'lsd[display][halfmap][map_position]',
                'options' => [
                    'left' => esc_html__('Left', 'listdom'),
                    'right' => esc_html__('Right', 'listdom')
                ],
                'value' => $halfmap['map_position'] ?? 'left'
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Height', 'listdom'),
            'for' => 'lsd_display_options_skin_halfmap_map_height',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_halfmap_map_height',
                'name' => 'lsd[display][halfmap][map_height]',
                'value' => $halfmap['map_height'] ?? ''
            ]); ?>
            <p class="description"><?php esc_html_e("Use this option to set the map height. Enter a value with units, such as 500px or 100vh. If you're unsure, leave it blank.", 'listdom'); ?></p>
        </div>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_map_options', 'halfmap', $options);
    ?>
</div>
<?php endif;
