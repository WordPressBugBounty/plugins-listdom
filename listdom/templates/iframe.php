<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $class */
/** @var string $body */
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
<body <?php body_class($class); ?>>
    <?php wp_body_open(); ?>
	<?php echo LSD_Kses::full(do_shortcode($body)); ?>
    <?php wp_footer(); ?>
</body>
</html>
