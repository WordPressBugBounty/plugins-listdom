<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>

<div class="lsd-ix-wrap">
    <div class="lsd-settings-group-wrapper lsd-px-4">
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-alert-no-my">
                <?php echo LSD_Base::alert($this->missAddonMessage('Excel', esc_html__('Excel Import / Export', 'listdom')), 'warning'); ?>
            </div>
        </div>
    </div>
</div>
