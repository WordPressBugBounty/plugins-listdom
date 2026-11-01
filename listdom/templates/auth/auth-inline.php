<?php

// no direct access
defined('ABSPATH') || die();

/** @var string $id */
/** @var array $role_tabs */
/** @var string $redirect */
/** @var bool $explicit_redirect */
/** @var string $form_type */

?>
<div class="lsd-auth-inline" id="<?php echo esc_attr($id); ?>">
    <?php include lsd_template('auth/role-tabs.php'); ?>
</div>
