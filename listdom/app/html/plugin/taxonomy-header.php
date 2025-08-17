<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Admin $this */
/** @var string $title */
/** @var string $url */
/** @var bool $show_links */
/** @var array $menus */
?>
<style>.wrap > h1.wp-heading-inline, .wrap > h1 {display: none;}</style>
<div id="lsd-tax-header" class="wrap about-wrap lsd-wrap">
    <?php LSD_Menus::header($title, $url, $menus ?? []); ?>
</div>
<?php if ($show_links): ?>
<script>
(function ($)
{
    const $links = $("#screen-meta-links");
    if ($links.length)
    {
        $links.insertAfter("#lsd-tax-header").addClass("lsd-screen-meta-links").show();
        $links.after("<div class=\"clear\"></div>");
    }

    const $meta = $("#screen-meta");
    if ($meta.length) $meta.insertAfter("#lsd-tax-header");
})(jQuery);
</script>
<?php else: ?>
<style>#screen-meta-links {display: none;}</style>
<?php endif;
