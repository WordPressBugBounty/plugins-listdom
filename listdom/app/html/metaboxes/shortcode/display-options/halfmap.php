<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$halfmap = $options['halfmap'] ?? [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show a List+Grid view of the listings beside a map.', 'listdom'), '<strong>'.esc_html__('Half Map', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_style',
            'name' => 'lsd[display][halfmap][style]',
            'class' => 'lsd-display-options-style-selector',
            'options' => LSD_Styles::halfmap(),
            'value' => $halfmap['style'] ?? 'style1'
        ]); ?>
    </div>
</div>
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
                'data-parent' => '#lsd_skin_display_options_halfmap'
            ]
        ]); ?>
    </div>
</div>
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
                'redirect' => esc_html__('Redirect to Listing Details Page', 'listdom'),
                'lightbox' => esc_html__('Open Listing Details in a Lightbox', 'listdom'),
                'none' => esc_html__('None', 'listdom')
            ],
            'value' => $halfmap['mapobject_onclick'] ?? 'infowindow'
        ]); ?>
        <p class="description"><?php esc_html_e("You can select to show an infowindow when someone clicks on a marker or shape on the map or open the listing details page directly. Also it's possible to show the details on a Lightbox without reloading the page.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Search', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_mapsearch',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php if($this->isPro()): ?>
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_display_options_skin_halfmap_mapsearch',
                'name' => 'lsd[display][halfmap][mapsearch]',
                'value' => $halfmap['mapsearch'] ?? '1',
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
        'for' => 'lsd_display_options_skin_halfmap_maplimit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_halfmap_maplimit',
            'name' => 'lsd[display][halfmap][maplimit]',
            'value' => $halfmap['maplimit'] ?? '300'
        ]); ?>
        <p class="description"><?php esc_html_e("This option contrlos the number of the items showed on the map. If you increase the limit to more than 300, then the page may load pretty slow. We suggest you to use filter options to filter only the listings that you want to include in this shortcode.", 'listdom'); ?></p>
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
            'value' => $halfmap['map_height'] ?? '500'
        ]); ?>
        <p class="description"><?php esc_html_e("Map height in pixels. Don't insert any unit like px, etc.", 'listdom'); ?></p>
    </div>
</div>

<?php
    // Action for Third Party Plugins
    do_action('lsd_shortcode_map_options', 'halfmap', $options);
?>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Default View', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_default_view',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_default_view',
            'name' => 'lsd[display][halfmap][default_view]',
            'options' => [
                'grid' => esc_html__('Grid View', 'listdom'),
                'list' => esc_html__('List View', 'listdom')
            ],
            'value' => $halfmap['default_view'] ?? 'grid'
        ]); ?>
        <p class="description"><?php esc_html_e("Choose the default view of the listing cards.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_columns',
            'name' => 'lsd[display][halfmap][columns]',
            'options' => ['2' => 2, '3' => 3, '4' => 4],
            'value' => $halfmap['columns'] ?? '2'
        ]); ?>
        <p class="description"><?php esc_html_e("Set the count of the listing cards per row for the Grid view.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_halfmap_limit',
            'name' => 'lsd[display][halfmap][limit]',
            'value' => $halfmap['limit'] ?? '12'
        ]); ?>
        <p class="description"><?php echo sprintf(esc_html__("Number of the Listings per page. It should be a multiple of the %s option. For example if the %s is set to 3, then you should set the limit to 3, 6, 9, 12, 30, etc.", 'listdom'), '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>', '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_pagination',
            'name' => 'lsd[display][halfmap][pagination]',
            'value' => $halfmap['pagination'] ?? (isset($halfmap['load_more']) && $halfmap['load_more'] == 0 ? 'disabled' : 'loadmore'),
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
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_listing_link',
            'name' => 'lsd[display][halfmap][listing_link]',
            'value' => $halfmap['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Image', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_display_image',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_halfmap_display_image',
            'name' => 'lsd[display][halfmap][display_image]',
            'value' => $halfmap['display_image'] ?? '1',
            'toggle' => '#lsd_display_options_skin_halfmap_image_method'
        ]); ?>
        <p class="description"><?php esc_html_e("Display listing image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row <?php echo !isset($halfmap['display_image']) || $halfmap['display_image'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_halfmap_image_method">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Image Method', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_image_method',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_halfmap_image_method',
            'name' => 'lsd[display][halfmap][image_method]',
            'options' => [
                'cover' => esc_html__('Cover', 'listdom'),
                'slider' => esc_html__('Slider', 'listdom'),
            ],
            'value' => $halfmap['image_method'] ?? 'cover'
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

<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Labels', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_display_labels',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_halfmap_display_labels',
            'name' => 'lsd[display][halfmap][display_labels]',
            'value' => $halfmap['display_labels'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the listing labels on the image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Share Buttons', 'listdom'),
        'for' => 'lsd_display_options_skin_halfmap_display_share_buttons',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_halfmap_display_share_buttons',
            'name' => 'lsd[display][halfmap][display_share_buttons]',
            'value' => $halfmap['display_share_buttons'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the share buttons.", 'listdom'); ?></p>
    </div>
</div>