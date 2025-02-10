<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Search Options
$search = get_post_meta($post->ID, 'lsd_search', true);
?>
<div class="lsd-metabox lsd-metabox-search lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 lsd-pt-2">
    <div>
        <?php echo LSD_Form::label([
            'title' => esc_html__('Search Form', 'listdom'),
            'for' => 'lsd_search_shortcode',
        ]); ?>
        <?php echo LSD_Form::searches([
            'id' => 'lsd_search_shortcode',
            'name' => 'lsd[search][shortcode]',
            'show_empty' => true,
            'value' => $search['shortcode'] ?? ''
        ]); ?>
        <p class="description lsd-mb-0"><?php esc_html_e("Add a search form to this shortcode. It is disabled by default.", 'listdom'); ?></p>
    </div>
    <div>
        <?php echo LSD_Form::label([
            'title' => esc_html__('Search Form Position', 'listdom'),
            'for' => 'lsd_search_position',
        ]); ?>
        <?php echo LSD_Form::select([
            'id' => 'lsd_search_position',
            'name' => 'lsd[search][position]',
            'options' => [
                'top' => esc_html__('Show on top', 'listdom'),
                'bottom' => esc_html__('Show on bottom', 'listdom'),
                'left' => esc_html__('Show on left', 'listdom'),
                'right' => esc_html__('Show on right', 'listdom'),
                'before_listings' => esc_html__('Show before the listings', 'listdom')
            ],
            'value' => $search['position'] ?? 'top'
        ]); ?>
    </div>

    <?php do_action('lsd_shortcode_search_metabox_end', $post, $search); ?>
</div>
