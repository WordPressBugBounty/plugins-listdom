<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Taxonomy $this */

// Max Grid
$max_grid = $this->atts['max_grid'] ?? 8;
if(!is_numeric($max_grid) || $max_grid > 8 || $max_grid < 1) $max_grid = 8;

$image_sizes = [
    '1' => [500, 300],
    '2' => [500, 500],
    '3' => [400, 400],
    '4' => [300, 300],
    '5' => [300, 300],
    '6' => [300, 200],
    '7' => [300, 200],
    '8' => [300, 200],
];
?>
<div class="lsd-taxonomy-shortcode-wrapper lsd-taxonomy-shortcode-image lsd-font-m">
    <?php if(!count($this->terms)): ?>
        <?php echo LSD_Base::alert(esc_html__('No item found!', 'listdom'), 'warning'); ?>
    <?php else: ?>

        <?php $i = 0; foreach($this->terms as $term): $size = min($max_grid, (count($this->terms)-$i)); $image_id = get_term_meta($term->term_id, 'lsd_image', true); ?>

            <?php if($i % $max_grid == 0): ?><div class="lsd-ts-image-wrapper lsd-ts-image-size-<?php echo esc_attr($size); ?>"><?php endif; ?>
            <div class="lsd-ts-image <?php echo ($image_id ? '' : 'lsd-no-image'); ?> lsd-ts-image-<?php ++$i; echo ($i % $max_grid != 0 ? $i % $max_grid : $max_grid); ?>">
                <a href="<?php echo esc_url(get_term_link($term->term_id)); ?>">

                    <?php echo $image_id ? '<div class="lsd-image">'.wp_get_attachment_image($image_id, $image_sizes[$size]).'</div>' : ''; ?>

                    <h3 class="lsd-title">
                        <?php echo esc_html($term->name); ?>
                        <?php if(isset($this->atts['show_count']) && $this->atts['show_count']): ?>
                            <span class="lsd-count">(<?php echo esc_html($term->count); ?>)</span>
                        <?php endif; ?>
                    </h3>

                </a>
            </div>
            <?php if($i % $max_grid == 0): ?></div><?php endif; ?>

        <?php endforeach; ?>
        <?php if($i % $max_grid != 0) echo '</div>'; ?>

    <?php endif; ?>
</div>
