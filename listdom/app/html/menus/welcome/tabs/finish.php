<?php
// no direct access
defined('ABSPATH') || die();

?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-5">
    <div class="lsd-welcome-content-header">
        <div class="lsd-admin-section-heading">
            <div class="lsd-admin-title-icon">
                <i class="wui-icon wui-icon--checkmark-circle"></i>
                <h2 class="lsd-admin-title lsd-m-0"><?php echo esc_html__('Great, your directory is ready!', 'listdom'); ?></h2>
            </div>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Now you can start your Listdom journey.' , 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-welcome-content-wrapper">
        <div class="lsd-finish-settings">
            <p class="lsd-my-0 lsd-admin-description"><?php echo esc_html__('Watch Mark J Draper set up a directory, edit it with Claude and Easy MCP, and import business listings as drafts.', 'listdom'); ?></p>
            <div class="lsd-wizard-video" data-video-id="_Jch4Zpa9_M">
                <button type="button" class="lsd-wizard-video-cover" aria-label="<?php echo esc_attr__('Play Mark J Draper\'s Listdom walkthrough', 'listdom'); ?>">
                    <img src="https://i.ytimg.com/vi/_Jch4Zpa9_M/maxresdefault.jpg" alt="<?php echo esc_attr__('Mark J Draper\'s Listdom walkthrough', 'listdom'); ?>" loading="eager" fetchpriority="high">
                    <span class="lsd-wizard-video-play" aria-hidden="true"><i class="fas fa-play"></i></span>
                </button>
            </div>
        </div>
    </div>
    <div class="lsd-welcome-button-wrapper">
        <a class="lsd-text-button" href="<?php echo admin_url('/admin.php?page=listdom'); ?>"><?php echo esc_html__('Return to Listdom Dashboard', 'listdom'); ?></a>
        <a class="lsd-primary-button" href="<?php echo admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING); ?>">
            <?php esc_html_e('Create your first listing', 'listdom'); ?>
            <i class="wui-icon wui-icon--right-arrow"></i>
        </a>
    </div>
</div>
<script>
jQuery(function ($)
{
    $('.lsd-wizard-video-cover').off('click').on('click', function ()
    {
        const $cover = $(this);
        const $video = $cover.closest('.lsd-wizard-video');
        const videoId = $video.data('video-id');
        if (!videoId) return;

        const $iframe = $('<iframe>', {
            width: 640,
            height: 360,
            src: 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0&vq=hd1080',
            title: '<?php echo esc_js(__('Mark J Draper demonstrates Listdom, Claude and Business Data', 'listdom')); ?>',
            frameborder: 0,
            allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
            referrerpolicy: 'strict-origin-when-cross-origin',
            allowfullscreen: true
        });

        $video.empty().append($iframe);
    });
});
</script>
