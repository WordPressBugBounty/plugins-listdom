<?php
defined('ABSPATH') || die();
?>
<?php $open('password'); ?>
<?php $field('password', __('New password', 'listdom'), 'password'); ?>
<?php $field('password_confirmation', __('Repeat new password', 'listdom'), 'password'); ?>
<?php $close(__('Change password', 'listdom')); ?>
