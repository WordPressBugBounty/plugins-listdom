<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="lsd-ix-wrap lsd-pt-4 lsd-alert-no-my">

    <?php echo LSD_Base::alert($this->missAddonMessage('Excel', esc_html__('Excel Import / Export', 'listdom')), 'warning'); ?>

</div>
