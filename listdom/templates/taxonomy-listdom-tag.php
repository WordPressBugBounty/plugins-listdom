<?php
// no direct access
defined('ABSPATH') || die();

// Main HTML Tag
$html_tag = apply_filters(LSD_Base::TAX_TAG.'_html_tag', 'div');

// Title Status
$title_status = apply_filters(LSD_Base::TAX_TAG.'_show_title', true);

/**
 * Listdom Tag Archive Template
 *
 * @author Webilia <hello@webilia.com>
 * @package LSD/Templates
 * @version 1.0.0
 */
get_header('lsd');
?>

    <<?php echo esc_attr($html_tag); ?> id="<?php echo esc_attr(apply_filters(LSD_Base::TAX_TAG.'_html_id', 'content')); ?>" class="<?php echo esc_attr(apply_filters(LSD_Base::TAX_TAG.'_html_class', 'container')); ?>">

        <?php if($title_status): ?>
        <h1><?php echo single_term_title(''); ?></h1>
        <?php endif; ?>

        <div class="lsd-taxonomy-content"><?php do_action(LSD_Base::TAX_TAG.'_archive_content'); ?></div>

    </<?php echo esc_attr($html_tag); ?>>

<?php get_footer('lsd');