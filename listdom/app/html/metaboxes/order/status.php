<?php
// no direct access
defined('ABSPATH') || die();

$statuses = (new LSD_Payments_Statuses())->statuses();

$options = [];
foreach ($statuses as $key => $data) $options[$key] = $data['applied'];

/** @var WP_Post $post */
?>
<div class="lsd-order-status">
    <?php echo LSD_Form::select([
        'id' => 'lsd_order_status',
        'name' => 'lsd_order_status',
        'options' => $options,
        'value' => $post->post_status,
    ]); ?>
</div>
