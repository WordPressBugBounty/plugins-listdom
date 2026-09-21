<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var array $restricted_state */

// Main
$main = new LSD_Main();
$restricted_state = isset($restricted_state) && is_array($restricted_state) ? $restricted_state : [];
$dashboard_wrapper = $this->get_dashboard_wrapper([
    'classes' => [
        'lsd-dashboard',
        'lsd-dashboard-auth',
        $restricted_state ? 'lsd-dashboard-restricted' : '',
    ],
]);
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>"<?php echo $dashboard_wrapper['attributes']; ?>>

    <?php if (!$restricted_state): ?>
        <p class="lsd-alert lsd-info lsd-mb-2"><?php esc_html_e("Please login to continue.", 'listdom'); ?></p>
        <?php echo do_shortcode('[listdom-auth redirect="' . $main->current_url() . '"]'); ?>
    <?php else: ?>
        <div class="lsd-dashboard-restricted-state">
            <div class="lsd-row lsd-dashboard-wrapper">
                <div class="lsd-dashboard-menus-wrapper">
                    <?php echo LSD_Kses::full($this->menus()); ?>
                </div>
                <div class="lsd-dashboard-restricted-state-card">
                    <?php if (!empty($restricted_state['icon'])): ?>
                        <div class="lsd-dashboard-restricted-state-icon" aria-hidden="true">
                            <?php if (strpos((string) $restricted_state['icon'], '<') !== false): ?>
                                <?php echo LSD_Kses::full($restricted_state['icon']); ?>
                            <?php else: ?>
                                <i class="lsd-fe-icon <?php echo esc_attr($restricted_state['icon']); ?>"></i>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($restricted_state['title'])): ?>
                        <h2 class="lsd-fe-title"><?php echo esc_html($restricted_state['title']); ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($restricted_state['description'])): ?>
                        <p class="lsd-fe-description"><?php echo esc_html($restricted_state['description']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($restricted_state['show_signin_button']) && !empty($restricted_state['signin_url'])): ?>
                        <a class="lsd-auth-button" href="<?php echo esc_url($restricted_state['signin_url']); ?>" target="<?php echo esc_attr($restricted_state['signin_target'] ?? '_self'); ?>"<?php echo !empty($restricted_state['signin_rel']) ? ' rel="' . esc_attr($restricted_state['signin_rel']) . '"' : ''; ?>>
                            <?php echo esc_html($restricted_state['signin_button_label'] ?? esc_html__('Sign In', 'listdom')); ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($restricted_state['show_register_link']) && !empty($restricted_state['register_url'])): ?>
                        <a class="lsd-dashboard-register-link" href="<?php echo esc_url($restricted_state['register_url']); ?>" target="<?php echo esc_attr($restricted_state['register_target'] ?? '_self'); ?>"<?php echo !empty($restricted_state['register_rel']) ? ' rel="' . esc_attr($restricted_state['register_rel']) . '"' : ''; ?>>
                            <?php echo esc_html($restricted_state['register_link_text'] ?? esc_html__("Don't have an account? Register", 'listdom')); ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($restricted_state['social_login'])): ?>
                        <div class="lsd-dashboard-social-login">
                            <?php echo LSD_Kses::full($restricted_state['social_login']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
