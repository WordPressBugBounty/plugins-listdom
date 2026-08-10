<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $class */
/** @var string $body */
/** @var string $preview_root_id */

$root_id = (
    isset($preview_root_id) &&
    is_string($preview_root_id) &&
    $preview_root_id !== ''
)
    ? $preview_root_id
    : 'lsd-template-preview-root';

$body_class = trim(($class ?? '') . ' lsd-template-editor-iframe-surface');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        @media screen { html { margin-top: 0 !important; } }
    </style>
</head>
<body <?php body_class($body_class); ?>>
    <?php wp_body_open(); ?>

    <div id="<?php echo esc_attr($root_id); ?>">
        <?php echo LSD_Kses::full(do_shortcode($body)); ?>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
