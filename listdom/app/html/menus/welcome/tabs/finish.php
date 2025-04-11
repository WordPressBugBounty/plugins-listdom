<div class="lsd-welcome-step-content lsd-util-hide" id="step-5">
    <div class="lsd-finish-setup">
        <div class="lsd-check">
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/check.svg')); ?>" alt="">
        </div>
        <h2 class="text-xl lsd-my-0"><?php echo esc_html__('Great, your directory is ready!', 'listdom'); ?></h2>
        <p class="lsd-my-0"><?php echo esc_html__('Now you can start your Listdom journey.', 'listdom'); ?></p>
        <iframe width="640" height="360" src="https://www.youtube-nocookie.com/embed/du_96cv6BAw?si=E1LwDdzdgdZNXpkw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>
    <div class="lsd-skip-wizard">
        <div class="lsd-listdom-guid-button">
            <a class="lsd-step-link button button-hero button-primary" href="<?php echo admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING); ?>"><?php esc_html_e('Create your first listing', 'listdom'); ?>
                <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
            </a>
        </div>
        <a href="<?php echo admin_url('/admin.php?page=listdom'); ?>"><?php echo esc_html__('Return To WordPress Dashboard', 'listdom'); ?></a>
    </div>
</div>
