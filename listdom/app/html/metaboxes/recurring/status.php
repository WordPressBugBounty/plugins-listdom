<?php
// no direct access
defined('ABSPATH') || die();

$statuses = (new LSD_Payments_Recurring_Statuses())->statuses();

$options = [];
foreach ($statuses as $key => $data) $options[$key] = $data['applied'];

/** @var WP_Post $post */
?>
<div class="lsd-order-status">
    <?php echo LSD_Form::select([
        'id' => 'lsd_recurring_status',
        'name' => 'lsd_recurring_status',
        'options' => $options,
        'value' => $post->post_status,
    ]); ?>
</div>
