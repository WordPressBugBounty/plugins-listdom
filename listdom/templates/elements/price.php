<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Price $this */
/** @var boolean $minimized */
/** @var int $post_id */

$min_price = get_post_meta($post_id, 'lsd_price', true);
if(!$min_price) return '';

// Price Components
$price_components = LSD_Options::price_components();

$max_price = $price_components['max'] ? get_post_meta($post_id, 'lsd_price_max', true) : '';
$text_price = $price_components['after'] ? get_post_meta($post_id, 'lsd_price_after', true) : '';
$currency = $price_components['currency'] ? get_post_meta($post_id, 'lsd_currency', true) : LSD_Options::currency();
?>
<span class="lsd-min-price"><?php echo LSD_Kses::element($this->render_price($min_price, $currency, $minimized)); ?></span>

<?php if (trim($max_price)): /** Max Price **/ ?>
    <span class="lsd-minmax-price-separator"> - </span><span class="lsd-max-price"><?php echo LSD_Kses::element($this->render_price($max_price, $currency, $minimized)); ?></span>
<?php endif; ?>

<?php if (trim($text_price) && !$minimized): /** Price Description **/ ?>
    <span class="lsd-text-price-separator"> /</span><span class="lsd-text-price"><?php echo esc_html($text_price); ?></span>
<?php endif;
