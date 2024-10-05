<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Taxonomy $this */

// Add List Skin JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).on("ready", function()
{
    jQuery("#lsd_terms_carousel_'.$this->id.'").owlCarousel(
    {
        items: 4,
        loop: '.(count($this->terms) > 4 ? 'true' : 'false').',
        autoplay: true,
        autoplayHoverPause: true,
        dots: true,
        nav: false,
        responsiveClass: true,
        responsive:
        {
            0: {
                items: 1
            },
            568: {
                items: 2
            },
            1024: {
                items: 4
            },
            1440: {
                items: 5
            }
        }
    });
});
</script>');
?>
<div class="lsd-taxonomy-shortcode-wrapper lsd-taxonomy-shortcode-carousel lsd-font-m">
    <?php if(!count($this->terms)): ?>
        <?php echo LSD_Base::alert(esc_html__('No item found!', 'listdom'), 'warning'); ?>
    <?php else: ?>
    <div id="lsd_terms_carousel_<?php echo esc_attr($this->id); ?>" class="lsd-owl-carousel">
        <?php foreach($this->terms as $term): $icon = LSD_Taxonomies::icon($term->term_id); $color = get_term_meta($term->term_id, 'lsd_color', true); ?>
        <div class="lsd-term-carousel-item">
			<div class="lsd-term-carousel-item-wrapper">
				<a href="<?php echo esc_url(get_term_link($term->term_id)); ?>">

					<div class="lsd-term-circle" style="background-color: <?php echo esc_attr(LSD_Color::lighter($color, 80)); ?>;">
						<?php if(trim($icon)): ?>
						<div class="lsd-icon-wrapper" style="color: <?php echo esc_attr($color); ?>;"><?php echo LSD_Kses::element($icon); ?></div>
						<?php endif; ?>
					</div>

					<h5 class="lsd-term-name"><?php echo esc_html($term->name); ?></h5>
				</a>
			</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>