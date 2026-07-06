<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var array $empty_state */

$empty_state = isset($empty_state) && is_array($empty_state) ? $empty_state : [];
?>
<div class="lsd-dashboard-empty-state">
    <div class="lsd-dashboard-empty-state-illustration" aria-hidden="true">
        <img src="<?php echo esc_url($this->lsd_asset_url($empty_state['image'] ?? 'img/dashboard/no-listings.svg')); ?>" alt="<?php echo esc_attr($empty_state['title'] ?? esc_attr__('Nothing here yet', 'listdom')); ?>">
    </div>

    <div class="lsd-dashboard-empty-state-content">
        <h3 class="lsd-fe-title"><?php echo esc_html($empty_state['title'] ?? esc_html__('Nothing here yet', 'listdom')); ?></h3>

        <?php if (!empty($empty_state['description'])): ?>
            <p class="lsd-fe-description"><?php echo esc_html($empty_state['description']); ?></p>
        <?php endif; ?>

        <?php if (!empty($empty_state['action']['label']) && !empty($empty_state['action']['url'])): ?>
            <div class="lsd-dashboard-empty-state-action">
                <a class="<?php echo esc_attr($empty_state['action']['class'] ?? 'lsd-primary-button'); ?>" href="<?php echo esc_url($empty_state['action']['url']); ?>">
                    <?php if (!empty($empty_state['action']['icon'])): ?>
                        <i class="lsd-fe-icon <?php echo esc_attr($empty_state['action']['icon']); ?>" aria-hidden="true"></i>
                    <?php endif; ?>

                    <?php echo esc_html($empty_state['action']['label']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($empty_state['quick_actions']) && is_array($empty_state['quick_actions'])): ?>
        <span class="lsd-dashboard-empty-state-subtitle"><?php esc_html_e('Choose what you want to do next:', 'listdom'); ?></span>

        <div class="lsd-dashboard-empty-state-grid">
            <?php foreach ($empty_state['quick_actions'] as $action): ?>
                <?php if (!is_array($action)) continue; ?>

                <div class="lsd-dashboard-empty-state-card">
                    <div class="lsd-dashboard-empty-state-card-icon">
                        <i class="lsd-fe-icon <?php echo esc_attr($action['icon'] ?? 'fas fa-circle'); ?>"></i>
                    </div>

                    <h3 class="lsd-fe-title"><?php echo esc_html($action['label'] ?? ''); ?></h3>

                    <?php if (!empty($action['description'])): ?>
                        <p class="lsd-fe-description"><?php echo esc_html($action['description']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($action['url'])): ?>
                        <a class="<?php echo esc_attr($action['button_class'] ?? 'lsd-light-button'); ?>" href="<?php echo esc_url($action['url']); ?>">
                            <?php echo esc_html($action['button_label'] ?? ($action['label'] ?? '')); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
