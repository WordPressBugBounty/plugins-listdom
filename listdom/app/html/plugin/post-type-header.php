<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Admin $this */
/** @var string $title */
/** @var string $url */
/** @var array $menus */
?>
<style>.wrap > h1.wp-heading-inline, .wrap > .page-title-action {display: none;}</style>
<div id="lsd-ptype-header" class="wrap about-wrap lsd-wrap">
    <?php LSD_Menus::header($title, $url, $menus ?? []); ?>
</div>
<script>
(function ($)
{
    const $links = $("#screen-meta-links");
    if ($links.length)
    {
        $links.insertAfter("#lsd-ptype-header").addClass("lsd-screen-meta-links").show();
        $links.after("<div class=\"clear\"></div>");
    }

    const $meta = $("#screen-meta");
    if ($meta.length) $meta.insertAfter("#lsd-ptype-header");
})(jQuery);
</script>
