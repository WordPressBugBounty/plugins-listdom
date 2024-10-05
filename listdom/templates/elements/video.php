<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */

$embeds = get_post_meta($post_id, 'lsd_embeds', true);
if(!is_array($embeds)) $embeds = [];

// There is no Embed Codes!
if(!count($embeds)) return '';
?>
<div class="lsd-embed-codes lsd-featured-video">
    <ul>
        <?php foreach($embeds as $embed): if(!isset($embed['code']) || !trim($embed['code'])) continue; ?>
            <?php if(isset($embed['featured']) && $embed['featured'] == 1): ?>
                <li <?php echo lsd_schema()->subjectOf(); ?> <?php echo lsd_schema()->scope()->type('https://schema.org/VideoObject'); ?>>
                    <?php if(isset($embed['name']) && trim($embed['name'])): ?>
                        <h2 class="lsd-single-page-section-title" <?php echo lsd_schema()->name(); ?> ><?php echo esc_html($embed['name']); ?></h2>
                    <?php endif; ?>
                    <div class="lsd-embed-code-wrapper">
                        <?php echo LSD_Kses::embed($embed['code']); ?>
                    </div>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
