<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Taxonomy $this */

$grid = $this->atts['grid'] ?? 1;
if(!in_array($grid, [1, 2, 3, 4, 6])) $grid = 3;
?>
<div class="lsd-taxonomy-shortcode-wrapper lsd-taxonomy-shortcode-simple lsd-font-m lsd-columns-<?php echo esc_attr($this->columns); ?>">
    <?php if(!count($this->terms)): ?>
        <?php echo LSD_Base::alert(esc_html__('No item found!', 'listdom'), 'warning'); ?>
    <?php else: ?>
        <?php $i = 1; foreach($this->terms as $term): ?>

        <?php if($i == 1): ?><div class="lsd-row"><?php endif; ?>

        <div class="lsd-col-<?php echo (12/$grid); ?>">
            <a href="<?php echo esc_url(get_term_link($term->term_id)); ?>">
                <span class="lsd-title"><?php echo esc_html($term->name); ?></span>
				<?php if(!isset($this->atts['show_count']) || (isset($this->atts['show_count']) && $this->atts['show_count'])): ?>
                    <span class="lsd-count">
                        (<?php echo esc_html($term->count); ?>)
                    </span>
                <?php endif; ?>
            </a>

            <?php if($this->hierarchical && $children = $this->get_terms($term->term_id)): ?>
            <ul class="lsd-children">
                <?php foreach($children as $child): ?>
                <li>
                    <a href="<?php echo esc_url(get_term_link($child->term_id)); ?>">
                        <div class="lsd-title">
                            <?php echo esc_html($child->name); ?>
                            <?php if(isset($this->atts['show_count']) && $this->atts['show_count']): ?>
                                <span class="lsd-count">(<?php echo esc_html($child->count); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <?php if($i == $grid): $i = 0; ?></div><?php endif; ?>

        <?php $i++; endforeach; ?>

        <?php if($i != 1 && $i <= $grid) echo '<div class="lsd-col-transparent lsd-col-'.(12 / $grid).'"></div></div>'; ?>

    <?php endif; ?>
</div>