<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$grid = $options['grid'] ?? [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show the directories and listings in a grid format. Also, you can include a map too.', 'listdom'), '<strong>'.esc_html__('Grid', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_grid_style',
            'class' => 'lsd-display-options-style-selector',
            'name' => 'lsd[display][grid][style]',
            'options' => LSD_Styles::grid(),
            'value' => $grid['style'] ?? 'style1'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Provider', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_map_provider',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::providers([
            'id' => 'lsd_display_options_skin_grid_map_provider',
            'name' => 'lsd[display][grid][map_provider]',
            'value' => $grid['map_provider'] ?? LSD_Map_Provider::def(),
            'disabled' => true,
            'class' => 'lsd-map-provider-toggle',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_grid'
            ]
        ]); ?>
    </div>
</div>
<div class="lsd-form-group lsd-form-row-map-needed <?php echo isset($grid['map_provider']) && $grid['map_provider'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_grid_map_options">
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Position', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_map_position',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_grid_map_position',
                'name' => 'lsd[display][grid][map_position]',
                'options' => [
                    'top' => esc_html__('Show before the Grid view', 'listdom'),
                    'bottom' => esc_html__('Show after the Grid view', 'listdom')
                ],
                'value' => $grid['map_position'] ?? 'top'
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Style', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_mapstyle',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::mapstyle([
                'id' => 'lsd_display_options_skin_grid_mapstyle',
                'name' => 'lsd[display][grid][mapstyle]',
                'value' => $grid['mapstyle'] ?? ''
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Clustering', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_clustering',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_display_options_skin_grid_clustering',
                'toggle' => '#lsd_display_options_skin_grid_clustering_options',
                'name' => 'lsd[display][grid][clustering]',
                'value' => $grid['clustering'] ?? '1'
            ]); ?>
        </div>
    </div>
    <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div id="lsd_display_options_skin_grid_clustering_options" <?php echo !isset($grid['clustering']) || $grid['clustering'] ? '' : 'style="display: none;"'; ?>>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Bubbles', 'listdom'),
                    'for' => 'lsd_display_options_skin_grid_clustering_images',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_grid_clustering_images',
                        'name' => 'lsd[display][grid][clustering_images]',
                        'options' => LSD_Base::get_clustering_icons(),
                        'value' => $grid['clustering_images'] ?? 'img/cluster1/m'
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Marker/Shape On Click', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_mapobject_onclick',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_grid_mapobject_onclick',
                'name' => 'lsd[display][grid][mapobject_onclick]',
                'options' => [
                    'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                    'redirect' => esc_html__('Redirect to Listing Details Page', 'listdom'),
                    'lightbox' => esc_html__('Open Listing Details in a Lightbox', 'listdom'),
                    'none' => esc_html__('None', 'listdom')
                ],
                'value' => $grid['mapobject_onclick'] ?? 'infowindow'
            ]); ?>
            <p class="description"><?php esc_html_e("You can select to show an infowindow when someone clicks on a marker or shape on the map or open the listing details page directly. Also it's possible to show the details on a Lightbox without reloading the page.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Search', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_mapsearch',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php if($this->isPro()): ?>
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_grid_mapsearch',
                    'name' => 'lsd[display][grid][mapsearch]',
                    'value' => $grid['mapsearch'] ?? '1',
                ]); ?>
                <p class="description"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
            <?php else: ?>
                <p class="lsd-alert lsd-warning"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Limit', 'listdom'),
            'for' => 'lsd_display_options_skin_grid_maplimit',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_grid_maplimit',
                'name' => 'lsd[display][grid][maplimit]',
                'value' => $grid['maplimit'] ?? '300'
            ]); ?>
            <p class="description"><?php esc_html_e("This option contrlos the number of the items showed on the map. If you increase the limit to more than 300, then the page may load pretty slow. We suggest you to use filter options to filter only the listings that you want to include in this shortcode.", 'listdom'); ?></p>
        </div>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_map_options', 'grid', $options);
    ?>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_grid_columns',
            'name' => 'lsd[display][grid][columns]',
            'options' => ['2'=>2, '3'=>3, '4'=>4, '6'=>6],
            'value' => $grid['columns'] ?? '3'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_grid_limit',
            'name' => 'lsd[display][grid][limit]',
            'value' => $grid['limit'] ?? '12'
        ]); ?>
        <p class="description"><?php echo sprintf(esc_html__("Number of the Listings per page. It should be a multiple of the %s option. For example if the %s is set to 3, then you should set the limit to 3, 6, 9, 12, 30, etc.", 'listdom'), '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>', '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_grid_pagination',
            'name' => 'lsd[display][grid][pagination]',
            'value' => $grid['pagination'] ?? (isset($grid['load_more']) && $grid['load_more'] == 0 ? 'disabled' : 'loadmore'),
            'options' => [
                'loadmore' => esc_html__('Load More Button', 'listdom'),
                'scroll' => esc_html__('Infinite Scroll', 'listdom'),
                'disabled' => esc_html__('Disabled', 'listdom'),
            ],
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_grid_listing_link',
            'name' => 'lsd[display][grid][listing_link]',
            'value' => $grid['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Image', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_display_image',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_grid_display_image',
            'name' => 'lsd[display][grid][display_image]',
            'value' => $grid['display_image'] ?? '1',
            'toggle' => '#lsd_display_options_skin_grid_image_method'
        ]); ?>
        <p class="description"><?php esc_html_e("Display listing image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option <?php echo !isset($grid['display_image']) || $grid['display_image'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_grid_image_method">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Image Method', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_image_method',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_grid_image_method',
            'name' => 'lsd[display][grid][image_method]',
            'options' => [
                'cover' => esc_html__('Cover', 'listdom'),
                'slider' => esc_html__('Slider', 'listdom'),
            ],
            'value' => $grid['image_method'] ?? 'cover'
        ]); ?>
        <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link & Display Image', 'listdom'), true); ?></p>
    </div>
</div>
<?php endif; ?>

<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Labels', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_display_labels',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_grid_display_labels',
            'name' => 'lsd[display][grid][display_labels]',
            'value' => $grid['display_labels'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the listing labels on the image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Share Buttons', 'listdom'),
        'for' => 'lsd_display_options_skin_grid_display_share_buttons',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_grid_display_share_buttons',
            'name' => 'lsd[display][grid][display_share_buttons]',
            'value' => $grid['display_share_buttons'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the share buttons.", 'listdom'); ?></p>
    </div>
</div>