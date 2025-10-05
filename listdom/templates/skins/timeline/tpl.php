<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Timeline $this */

// Get HTML of Listings
$listings_html = $this->listings_html();

$orientation_class = $this->horizontal ? 'lsd-timeline-horizontal' : 'lsd-timeline-vertical';
$items_wrapper_classes = [
    'lsd-timeline-items',
    'lsd-skin-' . $this->id . '-timeline',
];

if ($this->horizontal)
{
    $items_wrapper_classes[] = 'lsd-owl-carousel';
    $horizontal_alignment = in_array($this->horizontal_alignment, ['top', 'bottom', 'zigzag'], true)
        ? $this->horizontal_alignment
        : 'zigzag';
    $items_wrapper_classes[] = 'lsd-timeline-align-' . $horizontal_alignment;
}

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_skin'.$this->id.'").listdomTimelineSkin(
    {
        id: "'.$this->id.'",
        load_more: "'.($this->pagination === 'loadmore' ? '1' : '0').'",
        infinite_scroll: "'.($this->pagination === 'scroll' ? '1' : '0').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        atts: "'.http_build_query(['atts'=>$this->atts], '', '&').'",
        next_page: "'.$this->next_page.'",
        limit: "'.$this->limit.'",
        horizontal: "'.($this->horizontal ? '1' : '0').'",
        autoplay: "'.($this->autoplay ? '1' : '0').'",
        columns: "'.(int) $this->columns.'",
        vertical_alignment: "'.esc_js($this->vertical_alignment).'",
        horizontal_alignment: "'.esc_js($this->horizontal_alignment).'",
        style: "'.esc_js($this->style).'",
    });
});
</script>');
?>
<div class="lsd-skin-wrapper lsd-timeline-view-wrapper <?php echo sanitize_html_class($this->get_bar_class()); ?> <?php echo esc_attr($this->html_class); ?> lsd-style-<?php echo esc_attr($this->style); ?> <?php echo esc_attr($orientation_class); ?> lsd-font-m" id="lsd_skin<?php echo esc_attr($this->id); ?>" data-next-page="<?php echo esc_attr($this->next_page); ?>" data-columns="<?php echo esc_attr($this->columns); ?>" data-horizontal="<?php echo esc_attr($this->horizontal ? '1' : '0'); ?>">

    <?php echo LSD_Kses::form($this->get_left_bar()); ?>

    <div class="lsd-skin-main-bar-wrapper">
        <?php if ($this->sm_shortcode && $this->sm_position === 'top') echo LSD_Kses::form($this->get_search_module()); ?>

        <div class="lsd-list-wrapper">
            <?php
                /**
                 * Top Position of Timeline View
                 */
                if ($this->map_provider && $this->map_position === 'top')
                {
                    echo '<div class="lsd-timeline-view-top-wrapper">';
                    echo $this->get_map();
                    echo '</div>';
                }
            ?>

            <?php if ($this->sm_shortcode && $this->sm_position === 'before_listings') echo LSD_Kses::form($this->get_search_module()); ?>

            <?php echo LSD_Kses::form($this->get_sortbar()); ?>

            <div class="lsd-timeline-view-listings-wrapper lsd-viewstyle-timeline">
                <div class="lsd-listing-wrapper <?php echo $this->image_fit === 'contain' ? 'lsd-image-object-contain' : ''; ?>">
                    <div class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $items_wrapper_classes))); ?>">
                        <?php echo LSD_Kses::full($listings_html); ?>
                    </div>
                </div>
            </div>

            <?php echo LSD_Kses::element($this->get_pagination()); ?>

            <?php
                /**
                 * Bottom Position of Timeline View
                 */
                if ($this->map_provider && $this->map_position === 'bottom')
                {
                    echo '<div class="lsd-timeline-view-bottom-wrapper">';
                    echo $this->get_map();
                    echo '</div>';
                }
            ?>
        </div>

        <?php if ($this->sm_shortcode && $this->sm_position === 'bottom') echo LSD_Kses::form($this->get_search_module()); ?>
    </div>

    <?php echo LSD_Kses::form($this->get_right_bar()); ?>
</div>
