<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $this */
?>
<div class="lsd-view-switcher-sortbar-wrapper">
    <div class="lsd-row">
        <div class="lsd-col-10">
            <?php echo LSD_Kses::form($this->get_sortbar()); ?>
        </div>
        <div class="lsd-col-2">
            <ul class="lsd-view-switcher-buttons">
                <li data-view="list" class="<?php echo $this->default_view === 'list' ? 'lsd-active lsd-color-m-txt' : ''; ?>">
                    <i class="lsd-icon fa fa-bars fa-fw" aria-hidden="true"></i>
                </li>
                <li data-view="grid" class="<?php echo $this->default_view === 'grid' ? 'lsd-active lsd-color-m-txt' : ''; ?>">
                    <i class="lsd-icon fa fa-th-large fa-fw" aria-hidden="true"></i>
                </li>
            </ul>
        </div>
    </div>
</div>