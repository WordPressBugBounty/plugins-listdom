<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Map Control Options
$mapcontrols = get_post_meta($post->ID, 'lsd_mapcontrols', true);

// Apply default values
if(!is_array($mapcontrols) || !count($mapcontrols)) $mapcontrols = LSD_Options::defaults('mapcontrols');

// Positions Array
$positions = $this->get_map_control_positions();
?>
<div class="lsd-metabox lsd-metabox-map-controls">
    <p class="description"><?php esc_html_e('You can manage the map controls/buttons.', 'listdom'); ?></p>
    <ul>
        <li>
            <label for="lsd_map_control_zoom"><?php esc_html_e('Zoom Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][zoom]" id="lsd_map_control_zoom">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <?php foreach($positions as $position=>$label): ?>
                <option value="<?php echo esc_attr($position); ?>" <?php echo isset($mapcontrols['zoom']) && $mapcontrols['zoom'] == $position ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Displays "+" and "-" buttons for changing the zoom level of the map.', 'listdom'); ?></p>
        </li>
        <li>
            <label for="lsd_map_control_maptype"><?php esc_html_e('Map Type Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][maptype]" id="lsd_map_control_maptype">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <?php foreach($positions as $position=>$label): ?>
                    <option value="<?php echo esc_attr($position); ?>" <?php echo isset($mapcontrols['maptype']) && $mapcontrols['maptype'] == $position ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Allows the user to choose a map type.', 'listdom'); ?></p>
        </li>
        <li>
            <label for="lsd_map_control_streetview"><?php esc_html_e('Street View Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][streetview]" id="lsd_map_control_streetview">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <?php foreach($positions as $position=>$label): ?>
                    <option value="<?php echo esc_attr($position); ?>" <?php echo isset($mapcontrols['streetview']) && $mapcontrols['streetview'] == $position ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Contains a Pegman icon which can be dragged onto the map to enable Street View.', 'listdom'); ?></p>
        </li>

        <?php if($this->isPro()): ?>
        <li>
            <label for="lsd_map_control_draw"><?php esc_html_e('Draw Controls', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][draw]" id="lsd_map_control_draw">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <?php foreach($positions as $position=>$label): ?>
                    <option value="<?php echo esc_attr($position); ?>" <?php echo isset($mapcontrols['draw']) && $mapcontrols['draw'] == $position ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Contains some tools to draw a shape on the map to filter the listings.', 'listdom'); ?></p>
        </li>
        <li>
            <label for="lsd_map_control_gps"><?php esc_html_e('GPS Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][gps]" id="lsd_map_control_gps">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <?php foreach($positions as $position=>$label): ?>
                    <option value="<?php echo esc_attr($position); ?>" <?php echo isset($mapcontrols['gps']) && $mapcontrols['gps'] == $position ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Asks users for their locations to show nearby listings. In some browsers it works only on secured websites with https.', 'listdom'); ?></p>
        </li>
        <?php else: ?>
        <li>
            <p class="lsd-alert lsd-warning lsd-mb-4"><?php echo LSD_Base::missFeatureMessage(esc_html__('Draw and GPS Controls', 'listdom'), true); ?></p>
        </li>
        <?php endif; ?>

        <li>
            <label for="lsd_map_control_scale"><?php esc_html_e('Scale Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][scale]" id="lsd_map_control_scale">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <option value="1" <?php echo isset($mapcontrols['scale']) && $mapcontrols['scale'] == 1 ? 'selected="selected"' : ''; ?>><?php esc_html_e('Enabled', 'listdom'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Displays a map scale element in the bottom of the map. This control is disabled by default.', 'listdom'); ?></p>
        </li>
        <li>
            <label for="lsd_map_control_fullscreen"><?php esc_html_e('Fullscreen Control', 'listdom'); ?></label>
            <select name="lsd[mapcontrols][fullscreen]" id="lsd_map_control_fullscreen">
                <option value="0"><?php esc_html_e('Disabled', 'listdom'); ?></option>
                <option value="1" <?php echo isset($mapcontrols['fullscreen']) && $mapcontrols['fullscreen'] == 1 ? 'selected="selected"' : ''; ?>><?php esc_html_e('Enabled', 'listdom'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Offers the option to open the map in full screen mode. This control is enabled by default on mobile devices, and is disabled by default on the desktop.', 'listdom'); ?></p>
        </li>
    </ul>
</div>