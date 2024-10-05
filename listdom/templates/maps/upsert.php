<?php
// no direct access
defined('ABSPATH') || die();

$object_type = get_post_meta($this->id, 'lsd_object_type', true);
if(!trim($object_type)) $object_type = 'marker';

$zoomlevel = get_post_meta($this->id, 'lsd_zoomlevel', true);
if(!trim($zoomlevel)) $zoomlevel = $this->settings['map_backend_zl'];

$address = get_post_meta($this->id, 'lsd_address', true);

$latitude = (float) get_post_meta($this->id, 'lsd_latitude', true);
if(!trim($latitude)) $latitude = $this->settings['map_backend_lt'];

$longitude = (float) get_post_meta($this->id, 'lsd_longitude', true);
if(!trim($longitude)) $longitude = $this->settings['map_backend_ln'];

$shape_type = get_post_meta($this->id, 'lsd_shape_type', true);
$shape_paths = get_post_meta($this->id, 'lsd_shape_paths', true);
$shape_radius = get_post_meta($this->id, 'lsd_shape_radius', true);

// Get Shape
$shape = LSD_Shape::get([
    'type' => $shape_type,
    'paths' => $shape_paths,
    'radius' => $shape_radius,
]);
?>
<div class="lsd-dashboard lsd-dashboard-form">
    <div class="lsd-dashboard-right-box lsd-dashboard-address">
        <div class="lsd-metabox">

            <div class="lsd-object-type-address">
                <div class="lsd-form-row">
                    <label for="lsd_object_type_address" class="lsd-address-label lsd-col-12"><?php esc_html_e('Address', 'listdom'); ?></label>
                    <div class="lsd-col-12">
                        <input class="lsd-update-listener" type="text" id="lsd_object_type_address" placeholder="<?php esc_attr_e('Listing Address', 'listdom'); ?>" value="<?php echo esc_attr($address); ?>">
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-12">
                        <input class="lsd-update-listener" type="text" id="lsd_object_type_latitude" title="<?php esc_attr_e('Map Center Latitude', 'listdom'); ?>" placeholder="<?php esc_attr_e('Map Center Latitude', 'listdom'); ?>" value="<?php echo esc_attr($latitude); ?>">
                    </div>
                </div>
				<div class="lsd-form-row">
					<div class="lsd-col-12">
                        <input class="lsd-update-listener" type="text" id="lsd_object_type_longitude" title="<?php esc_attr_e('Map Center Longitude', 'listdom'); ?>" placeholder="<?php esc_attr_e('Map Center Longitude', 'listdom'); ?>" value="<?php echo esc_attr($longitude); ?>">
                    </div>
				</div>

                <?php
                    // Third Party Plugins!
                    do_action('lsd_listing_address_metabox', $this->id);
                ?>
            </div>
            <div class="lsd-tabs lsd-object-type-tabs">
                <ul class="nav-tab-wrapper">
                    <li class="nav-tab <?php echo $object_type === 'marker' ? 'nav-tab-active' : ''; ?>" id="lsd_metabox_object_type_marker" data-key="marker"><?php esc_html_e('Marker', 'listdom'); ?></li>
                    <li class="nav-tab <?php echo $object_type === 'shape' ? 'nav-tab-active' : ''; ?>" id="lsd_metabox_object_type_shape" data-key="shape"><?php esc_html_e('Shape', 'listdom'); ?></li>
                </ul>
            </div>
            <div class="lsd-form-row lsd-object-type-map">
                <div class="lsd-col-12">
                    <div id="lsd_address_map"></div>
                </div>
            </div>
            <div class="lsd-metabox-contents">
                <div class="lsd-tab-content <?php echo ($object_type == 'marker' ? 'lsd-tab-content-active' : ''); ?>" id="lsd_tab_content_marker" data-key="marker"></div>
                <div class="lsd-tab-content <?php echo ($object_type == 'shape' ? 'lsd-tab-content-active' : ''); ?>" id="lsd_tab_content_shape" data-key="shape"></div>
            </div>

            <input type="hidden" class="lsd-update-listener" id="lsd_object_type" value="<?php echo esc_attr($object_type); ?>">
            <input type="hidden" class="lsd-update-listener" id="lsd_object_type_zoomlevel" value="<?php echo esc_attr($zoomlevel); ?>">

            <?php /* Shape Fields */ ?>
            <input type="hidden" class="lsd-update-listener" id="lsd_shape_type" value="<?php echo esc_attr($shape_type); ?>">
            <input type="hidden" class="lsd-update-listener" id="lsd_shape_paths" value="<?php echo esc_attr($shape_paths); ?>">
            <input type="hidden" class="lsd-update-listener" id="lsd_shape_radius" value="<?php echo esc_attr($shape_radius); ?>">

        </div>
    </div>
</div>
<?php
$mp = new LSD_Map_Provider();
$mp->form($shape);
