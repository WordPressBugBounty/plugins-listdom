<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Price $this */
/** @var boolean $minimized */
/** @var int $post_id */

$min_price = get_post_meta($post_id, 'lsd_price', true);
if(!$min_price) return '';

$max_price = get_post_meta($post_id, 'lsd_price_max', true);
$text_price = get_post_meta($post_id, 'lsd_price_after', true);
$currency = get_post_meta($post_id, 'lsd_currency', true);
?>
    <span class="lsd-min-price"><?php echo LSD_Kses::element($this->render_price($min_price, $currency, $minimized)); ?></span>

<?php /** Max Price **/ if(trim($max_price)): ?>
    <span class="lsd-minmax-price-separator"> - </span><span class="lsd-max-price"><?php echo LSD_Kses::element($this->render_price($max_price, $currency, $minimized)); ?></span>
<?php endif; ?>

<?php /** Price Description **/ if(trim($text_price) && !$minimized): ?>
    <span class="lsd-text-price-separator"> /</span><span class="lsd-text-price"><?php echo esc_attr($text_price); ?></span>
<?php endif;