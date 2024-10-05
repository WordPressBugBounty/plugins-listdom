<?php
// no direct access
defined('ABSPATH') || die();
/** @var LSD_Skins $this */
?>
<div class="lsd-load-more-wrapper">
    <div class="lsd-load-more">
		<span class="lsd-load-more-text lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>">
			<span class="lsd-load-more-title"><?php esc_html_e('Load More', 'listdom'); ?></span>
			<span class="lsd-load-more-spinner"><i class="lsd-icon fa fa-circle-notch fa-spin fa-3x fa-fw"></i></span>
		</span>
    </div>
</div>