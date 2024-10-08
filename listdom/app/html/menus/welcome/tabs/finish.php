<div class="lsd-welcome-step-content lsd-util-hide" id="step-4">
    <div class="lsd-finish-setup">
        <div class="lsd-check">
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/check.svg')); ?>" alt="">
        </div>
        <h2 class="text-xl"><?php echo esc_html__('Great, your directory is ready!', 'listdom'); ?></h2>
        <p><?php echo esc_html__('Now you can start your Listdom journey.', 'listdom'); ?></p>
        <a class="lsd-step-link button button-hero button-primary" href="<?php echo admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING); ?>"><?php esc_html_e('Create your first listing', 'listdom'); ?>
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
        </a>
    </div>
    <div class="lsd-skip-wizard">
        <div class="lsd-listdom-guid-button">
            <a href="<?php echo LSD_Base::getListdomDocsURL(); ?>" class="button">
                <?php echo esc_html__('Documentation', 'listdom'); ?>
                <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right-purple.svg')); ?>" alt="">
            </a>
            <a href="<?php echo LSD_Base::getSupportURL(); ?>" class="button">
                <?php echo esc_html__('Get Help', 'listdom'); ?>
                <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right-purple.svg')); ?>" alt="">
            </a>
        </div>
        <a href="<?php echo admin_url('/admin.php?page=listdom'); ?>"><?php echo esc_html__('Return To WordPress Dashboard', 'listdom'); ?></a>
    </div>
</div>
