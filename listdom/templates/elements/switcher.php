<?php
// no direct access
defined('ABSPATH') || die();

/** @var bool $display_switcher */
/** @var LSD_Skins $this */
?>
<div class="lsd-view-switcher-sortbar-wrapper">
    <div class="lsd-lsd-view-sortbar-wrapper">
        <?php echo LSD_Kses::form($this->get_sortbar()); ?>
    </div>
    <?php if ($display_switcher): ?>
    <ul class="lsd-view-switcher-buttons">
        <li data-view="list" class="<?php echo $this->default_view === 'list' ? 'lsd-active lsd-color-m-txt' : ''; ?>">
            <i class="lsd-icon fa fa-bars fa-fw" aria-hidden="true"></i>
        </li>
        <li data-view="grid" class="<?php echo $this->default_view === 'grid' ? 'lsd-active lsd-color-m-txt' : ''; ?>">
            <i class="lsd-icon fa fa-th-large fa-fw" aria-hidden="true"></i>
        </li>
    </ul>
    <?php endif; ?>
</div>
