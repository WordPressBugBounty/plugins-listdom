<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Taxonomy $this */

$show_count = $this->atts['show_count'] ?? 0;
$widget = $this->atts['widget'] ?? 0;
?>
<div class="lsd-taxonomy-shortcode-wrapper lsd-taxonomy-shortcode-cloud lsd-font-m <?php echo ($widget ? ' lsd-widget' : ''); ?>">
    <?php if(!count($this->terms)): ?>
        <?php echo LSD_Base::alert(esc_html__('No item found!', 'listdom'), 'warning'); ?>
    <?php else: ?>
        <ul>
            <?php foreach($this->terms as $term): ?>
            <li>
                <a href="<?php echo esc_url(get_term_link($term->term_id)); ?>">

                    <span class="lsd-title"><?php echo ucwords($term->name); ?></span>

                    <?php if($show_count): ?>
                    <span class="lsd-count"><?php echo esc_html($term->count); ?></span>
                    <?php endif; ?>

                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>