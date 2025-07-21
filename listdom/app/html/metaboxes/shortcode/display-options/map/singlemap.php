<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$singlemap = $options['singlemap'] ?? [];
$mapsearch = $singlemap['mapsearch'] ?? '1';
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Map", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Configure map provider, controls, display behavior, marker actions, and map-based filters.", 'listdom'); ?> </p>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Provider', 'listdom'),
        'for' => 'lsd_display_options_skin_singlemap_map_provider',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::providers([
            'id' => 'lsd_display_options_skin_singlemap_map_provider',
            'name' => 'lsd[display][singlemap][map_provider]',
            'value' => $singlemap['map_provider'] ?? LSD_Map_Provider::def(),
            'class' => 'lsd-map-provider-toggle',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_map_singlemap'
            ]
        ]); ?>
    </div>
</div>
<div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Style', 'listdom'),
        'for' => 'lsd_display_options_skin_singlemap_mapstyle',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::mapstyle([
            'id' => 'lsd_display_options_skin_singlemap_mapstyle',
            'name' => 'lsd[display][singlemap][mapstyle]',
            'value' => $singlemap['mapstyle'] ?? ''
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Clustering', 'listdom'),
        'for' => 'lsd_display_options_skin_singlemap_clustering',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_singlemap_clustering',
            'toggle' => '#lsd_display_options_skin_singlemap_clustering_options',
            'name' => 'lsd[display][singlemap][clustering]',
            'value' => $singlemap['clustering'] ?? '1'
        ]); ?>
    </div>
</div>
<div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
    <div id="lsd_display_options_skin_singlemap_clustering_options" <?php echo ((!isset($singlemap['clustering']) || $singlemap['clustering']) ? '' : 'style="display: none;"'); ?>>
        <div class="lsd-form-row">
            <div class="lsd-col-2"><?php echo LSD_Form::label([
                'title' => esc_html__('Bubbles', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_clustering_images',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::select([
                    'id' => 'lsd_display_options_skin_singlemap_clustering_images',
                    'name' => 'lsd[display][singlemap][clustering_images]',
                    'options' => LSD_Base::get_clustering_icons(),
                    'value' => $singlemap['clustering_images'] ?? 'img/cluster1/m'
                ]); ?>
            </div>
        </div>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Marker/Shape On Click', 'listdom'),
        'for' => 'lsd_display_options_skin_singlemap_mapobject_onclick',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_singlemap_mapobject_onclick',
            'name' => 'lsd[display][singlemap][mapobject_onclick]',
            'options' => [
                'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                'redirect' => esc_html__('Redirect to Single Listing Page', 'listdom'),
                'lightbox' => esc_html__('Open Single Listing in a Lightbox', 'listdom'),
                'none' => esc_html__('None', 'listdom')
            ],
            'value' => $singlemap['mapobject_onclick'] ?? 'infowindow'
        ]); ?>
        <p class="description"><?php esc_html_e("You can choose to display an info window when someone clicks on a marker or shape on the map, open the single listing page directly, or show the details in a lightbox without reloading the page.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Search', 'listdom'),
        'for' => 'lsd_display_options_skin_singlemap_mapsearch',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php if ($this->isPro()): ?>
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_display_options_skin_singlemap_mapsearch',
                'name' => 'lsd[display][singlemap][mapsearch]',
                'toggle' => '#lsd_skin_singlemap_connected_shortcodes',
                'value' => $mapsearch,
            ]); ?>
            <p class="description"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
        <?php else: ?>
            <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php if ($this->isPro()): ?>
<div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
    <div class="<?php echo $mapsearch ? '' : 'lsd-util-hide'; ?>" id="lsd_skin_singlemap_connected_shortcodes">
        <div class="lsd-form-row">
            <div class="lsd-col-2"><?php echo LSD_Form::label([
                'title' => esc_html__('Connected Shortcodes', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_connected_shortcodes_input',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::autosuggest([
                    'source' => LSD_Base::PTYPE_SHORTCODE.'-searchable',
                    'name' => 'lsd[display][singlemap][connected_shortcodes]',
                    'id' => 'lsd_display_options_skin_singlemap_connected_shortcodes',
                    'input_id' => 'lsd_display_options_skin_singlemap_connected_shortcodes_input',
                    'suggestions' => 'lsd_display_options_skin_singlemap_connected_shortcodes_suggestions',
                    'values' => $singlemap['connected_shortcodes'] ?? [],
                    'max_items' => 3,
                    'placeholder' => esc_html__("Enter at least 3 characters of the shortcode's title ...", 'listdom'),
                    'description' => esc_html__('You should select up to 3 search-able skin shortcodes e.g. List, Grid, Masonry, List + Grid, Half Map, Side By Side, etc.', 'listdom'),
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
    // Action for Third Party Plugins
    do_action('lsd_shortcode_map_options', 'singlemap', $options);
?>

<?php
    // Action for Third Party Plugins
    do_action('lsd_shortcode_singlemap_options', $singlemap);
?>
