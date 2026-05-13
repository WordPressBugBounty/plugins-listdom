<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $empty_cart_logo */
/** @var string $empty_cart_message */
/** @var int $empty_cart_show_buttons */

$empty_cart_logo = $empty_cart_logo ?? '';
$empty_cart_message = $empty_cart_message ?? '<p>' . esc_html__('Your cart is empty.', 'listdom') . '</p>';
$empty_cart_show_buttons = $empty_cart_show_buttons ?? 1;

// General Settings
$settings = LSD_Options::settings();

// Dashboard Page
$dashboard_page_id = isset($settings['submission_page']) && $settings['submission_page'] ? $settings['submission_page'] : 0;

$url = add_query_arg(['mode' => 'manage'], get_permalink($dashboard_page_id));
?>
<div class="lsd-empty-cart-wrapper">
    <div class="lsd-cart-empty-logo">
        <?php if (trim($empty_cart_logo)): echo LSD_Kses::element($empty_cart_logo); ?>
        <?php else: ?>
            <img src="<?php echo esc_url($this->lsd_asset_url('img/empty-cart.webp')); ?>" alt="<?php esc_attr_e('Empty Cart Illustration', 'listdom'); ?>">
        <?php endif; ?>
    </div>
    <?php if (trim($empty_cart_message)) echo LSD_Kses::full($empty_cart_message); ?>
    <?php if ($empty_cart_show_buttons): ?>
        <div class="lsd-cart-buttons">
            <a class="lsd-light-button" href="<?php echo esc_url($url); ?>">
                <?php esc_html_e('My Dashboard', 'listdom'); ?>
            </a>
            <div class="lsd-cart-empty">
                <a class="lsd-general-button lsd-color-white-txt" href="<?php echo esc_url(site_url()); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Homepage', 'listdom'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

