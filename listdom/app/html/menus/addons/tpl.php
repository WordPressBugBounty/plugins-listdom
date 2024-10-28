<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Addons $this */
$addons = $this->get();
?>
<div class="wrap about-wrap lsd-wrap">
    <h1><?php esc_html_e('Listdom Addons', 'listdom'); ?></h1>
    <div class="about-text">
		<?php echo esc_html__("Listdom has one of the most complete addons designed for listing directory industry. You can freely use these addons to achieve what you want.", 'listdom'); ?>
    </div>

    <?php LSD_Ads::display('addons-top'); ?>

    <?php if(!$addons): ?>
    <p><?php echo LSD_Main::alert(sprintf(esc_html__('It seems there is a problem to get list of addons from Webilia server. Please try again later or check our website at %s', 'listdom'), '<a href="https://webilia.com" target="_blank"><strong>webilia.com</strong></a>'), 'warning'); ?></p>
    <?php else: ?>
    <div class="lsd-addons">
        <div class="lsd-grid lsd-g-3-columns">
            <?php foreach($addons as $addon): ?>
            <?php
                $basename = isset($addon->basename) && trim($addon->basename) ? $addon->basename : null;
                if(!$basename) continue;

                $installed = is_plugin_active($basename);
                $recommended = isset($addon->recommended) && $addon->recommended;

                $classes = ($recommended ? 'lsd-addons-recommended ' : '').($installed ? 'lsd-addons-installed ' : '');
                $description = strlen($addon->promotional) > 192 ? substr($addon->promotional, 0, 192).' ...' : $addon->promotional;
            ?>
            <div class="lsd-addon-wrapper <?php echo trim($classes); ?>">
				<div class="lsd-addon">
					<?php if(isset($addon->image) and trim($addon->image)): ?><img class="lsd-addon-icon" src="<?php echo esc_url($addon->image); ?>" alt="<?php echo esc_attr($addon->name); ?>"><?php endif; ?>
					
					<div class="lsd-buy-and-install">
					<?php if(!$installed): ?>
						<a class="button button-primary" href="<?php echo esc_url($addon->url); ?>" target="_blank"><?php esc_html_e('Download'); ?></a>
						
						<?php echo ($recommended ? '<div class="lsd-addons-recommended-badge">'.esc_html__('Recommended', 'listdom').'</div>' : ''); ?>
					<?php else: ?>
						<a class="button" href="<?php echo esc_url(LSD_Base::addUtmParameters($addon->documentation)); ?>" target="_blank"><?php esc_html_e('Documentation'); ?></a>
					<?php endif; ?>
					</div>
					
					<h3>
						<a href="<?php echo esc_url($addon->url); ?>" target="_blank"><?php echo esc_html($addon->name); ?></a>
					</h3>
					
					<p><?php echo LSD_Kses::element($description); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p><?php echo sprintf(esc_html__('Not sure which addon is suitable for your need? You can always send us an email at %s', 'listdom'), '<a href="mailto:hello@webilia.com"><strong>hello@webilia.com</strong></a>'); ?></p>
    </div>
    <?php endif; ?>

    <?php LSD_Ads::display('addons-bottom'); ?>
    
</div>
