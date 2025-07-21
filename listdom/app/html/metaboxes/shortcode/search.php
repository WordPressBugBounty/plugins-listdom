<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Search Options
$search = get_post_meta($post->ID, 'lsd_search', true);

// Searchable
$searchable = $search['searchable'] ?? 1;
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Search", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Add a search form and define its position and behavior.", 'listdom'); ?> </p>
<div id="lsd_metabox_search" class="lsd-metabox lsd-metabox-search lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 lsd-pt-2">
    <div class="lsd-flex lsd-flex-row lsd-searchable">
        <?php echo LSD_Form::label([
            'title' => esc_html__('Searchable', 'listdom'),
            'for' => 'lsd_search_searchable',
        ]); ?>
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_search_searchable',
            'name' => 'lsd[search][searchable]',
            'value' => $searchable,
            'toggle' => '#lsd_search_searchable_options',
            'toggle2' => '#lsd_search_non_searchable'
        ]); ?>
    </div>
    <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 lsd-pt-2 <?php echo $searchable ? '' : 'lsd-util-hide'; ?>" id="lsd_search_searchable_options">
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
    <div class="<?php echo $searchable ? 'lsd-util-hide' : ''; ?>" id="lsd_search_non_searchable">
        <p class="lsd-my-0"><?php esc_html_e("Non-searchable shortcodes display fixed results that do not change based on search queries.", 'listdom'); ?></p>
    </div>
</div>
