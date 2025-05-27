<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Breadcrumb $this */
/** @var bool $icon */
/** @var string $taxonomy */
?>
<nav class="lsd-breadcrumb" aria-label="<?php echo esc_attr__('Breadcrumb', 'listdom'); ?>">
    <ul class="lsd-breadcrumb-list">
        <?php echo LSD_Breadcrumb::breadcrumb($icon, $taxonomy); ?>
    </ul>
</nav>
