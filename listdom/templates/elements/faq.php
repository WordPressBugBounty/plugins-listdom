<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */
/** @var int $limit */
/** @var array $faqs */
/** @var bool $suppress_schema */

// There are no FAQs
if (!count($faqs)) return '';
?>
<div class="lsd-faqs" <?php echo $suppress_schema ? '' : lsd_schema()->scope()->type('https://schema.org/FAQPage'); ?>>
    <div class="lsd-faqs-accordion">
        <?php foreach ($faqs as $faq): ?>
            <details class="lsd-faq-item" <?php echo $suppress_schema ? '' : lsd_schema()->prop('mainEntity')->scope()->type('https://schema.org/Question'); ?>>
                <summary class="lsd-faq-question" <?php echo $suppress_schema ? '' : lsd_schema()->name(); ?>><?php echo esc_html($faq['question']); ?></summary>
                <div class="lsd-faq-answer" <?php echo $suppress_schema ? '' : lsd_schema()->prop('acceptedAnswer')->scope()->type('https://schema.org/Answer'); ?>>
                    <div <?php echo $suppress_schema ? '' : lsd_schema()->prop('text'); ?>><?php echo esc_html($faq['answer']); ?></div>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</div>
