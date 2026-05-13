<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$tiers = get_post_meta($post->ID, 'lsd_tiers', true);
if (!is_array($tiers)) $tiers = [];
if (!count($tiers)) $tiers[] = [];
?>
<div class="lsd-metabox lsd-plan-metabox lsd-p-3">
    <?php /* Security Nonce */ LSD_Form::nonce('lsd_plan_cpt', '_lsdnonce'); ?>
    <button type="button" class="button lsd-mb-4" id="lsd_add_tier"><?php esc_html_e('Add Tier', 'listdom'); ?></button>
    <div id="lsd_plan_tiers" class="lsd-plan-tiers lsd-flex lsd-flex-col lsd-gap-4">
        <?php foreach ($tiers as $index => $tier) $this->include_html_file('metaboxes/plan/tier.php', ['parameters' => ['index' => $index, 'tier' => $tier]]); ?>
    </div>
</div>
