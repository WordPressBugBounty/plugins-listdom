<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Labels $this */
/** @var int $post_id */

$labels = wp_get_post_terms($post_id, LSD_Base::TAX_LABEL);
if (!is_array($labels) || !count($labels)) return '';
?>
<?php if ($this->style === 'links'): ?>
    <ul class="lsd-labels-simple">
        <?php foreach ($labels as $label): ?>
            <li class="lsd-labels-simple-item">
                <a href="<?php echo $this->enable_link ? esc_url(get_term_link($label->term_id, LSD_Base::TAX_LABEL)) : '#'; ?>"><?php echo esc_html($label->name); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <ul class="lsd-labels-list">
        <?php foreach ($labels as $label): ?>
            <li class="lsd-labels-list-item">
                <?php if ($this->enable_link): ?>
                    <a <?php echo LSD_Element_Labels::styles($label->term_id); ?> href="<?php echo esc_url(get_term_link($label->term_id, LSD_Base::TAX_LABEL)); ?>"><?php echo esc_html($label->name); ?></a>
                <?php else: ?>
                    <span <?php echo LSD_Element_Labels::styles($label->term_id); ?>><?php echo esc_html($label->name); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif;
