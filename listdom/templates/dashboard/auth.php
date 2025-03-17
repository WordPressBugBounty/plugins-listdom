<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

// Main
$main = new LSD_Main();
?>
<div class="lsd-dashboard lsd-dashboard-auth">

    <p class="lsd-alert lsd-info lsd-mb-2"><?php esc_html_e("Please login to continue.", 'listdom'); ?></p>
    <?php echo do_shortcode('[listdom-auth redirect="'.$main->current_url().'"]'); ?>

</div>
