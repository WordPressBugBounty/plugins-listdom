<?php

// no direct access
defined('ABSPATH') || die();

/** @var string $id */
/** @var array $role_tabs */
/** @var string $redirect */
/** @var bool $explicit_redirect */
/** @var string $form_type */

?>
<?php if (count($role_tabs) > 1): ?>
    <div class="lsd-auth-dropdown-role-tabs" role="tablist">
        <?php foreach ($role_tabs as $index => $tab):
            $active = $index === 0 ? ' lsd-active' : '';
            $target_id = $id . '-tab-' . $index;
            ?>
            <button type="button" class="lsd-auth-dropdown-role-tab<?php echo esc_attr($active); ?>" data-target="#<?php echo esc_attr($target_id); ?>" role="tab" aria-controls="<?php echo esc_attr($target_id); ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                <?php echo esc_html($tab['label']); ?>
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="lsd-auth-dropdown-role-panels">
    <?php foreach ($role_tabs as $index => $tab):
        $panel_active = $index === 0 ? ' lsd-active' : '';
        $panel_target = $id . '-tab-' . $index;

        $role = $tab['role'] ?? '';
        $attributes = $role ? ' role="' . esc_attr($role) . '"' : '';
        // This is shortcode syntax, so keep the validated URL's query separators unencoded.
        $attributes .= $redirect !== '' ? ' redirect="' . $redirect . '"' : '';
        $attributes .= ' explicit_redirect="' . ($explicit_redirect ? '1' : '0') . '"';

        switch ($form_type) {
            case 'login':
                $shortcode = 'listdom-login';
                break;
            case 'register':
                $shortcode = 'listdom-register';
                break;
            case 'forgot-password':
                $shortcode = 'listdom-forgot-password';
                break;
            default:
                $shortcode = 'listdom-auth';
                $attributes .= ' layout="tab"';
                break;
        }
        ?>
        <div id="<?php echo esc_attr($panel_target); ?>" class="lsd-auth-dropdown-role-panel<?php echo esc_attr($panel_active); ?>" role="tabpanel" <?php echo $index === 0 ? '' : 'hidden="hidden"'; ?>>
            <?php echo do_shortcode('[' . $shortcode . ' ' . trim($attributes) . ']'); ?>
        </div>
    <?php endforeach; ?>
</div>
