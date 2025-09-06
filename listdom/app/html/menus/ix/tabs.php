<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="lsd-nav-wrapper">
    <ul class="lsd-nav-tab-wrapper">
        <li class="lsd-has-children lsd-csv-nav <?php echo $this->tab === 'csv' ? ' lsd-nav-expanded' : ''; ?>">
            <a class="lsd-nav-tab <?php echo $this->tab === 'csv' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv')); ?>">
                <i class="listdom-icon lsdi-csv lsd-m-0"></i>
                <?php esc_html_e('CSV', 'listdom'); ?>
            </a>
            <ul class="lsd-nav-sub-tabs" data-parent="csv" <?php echo $this->tab === 'csv' ? '' : 'hidden'; ?>>
                <li class="lsd-nav-tab <?php echo $this->tab === 'csv' && ($this->subtab === 'import' || !$this->subtab) ? 'lsd-nav-tab-active' : ''; ?>" data-key="import"><?php esc_html_e('Import', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'csv' && $this->subtab === 'mapping-templates' ? 'lsd-nav-tab-active' : ''; ?>" data-key="mapping-templates"><?php esc_html_e('Mapping Templates', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'csv' && $this->subtab === 'auto-import' ? 'lsd-nav-tab-active' : ''; ?>" data-key="auto-import"><?php esc_html_e('Auto Import', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'csv' && $this->subtab === 'export' ? 'lsd-nav-tab-active' : ''; ?>" data-key="export"><?php esc_html_e('Export', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-has-children lsd-excel-nav <?php echo $this->tab === 'excel' ? ' lsd-nav-expanded' : ''; ?>">
            <a class="lsd-nav-tab <?php echo $this->tab === 'excel' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=excel')); ?>">
                <i class="listdom-icon lsdi-excel lsd-m-0"></i>
                <?php esc_html_e('Excel', 'listdom'); ?>
            </a>
            <ul class="lsd-nav-sub-tabs" data-parent="excel" <?php echo $this->tab === 'excel' ? '': 'hidden'; ?>>
                <li class="lsd-nav-tab <?php echo $this->tab === 'excel' && ($this->subtab === 'import' || !$this->subtab) ? 'lsd-nav-tab-active' : ''; ?>" data-key="import"><?php esc_html_e('Import', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'excel' && $this->subtab === 'mapping-templates' ? 'lsd-nav-tab-active' : ''; ?>" data-key="mapping-templates"><?php esc_html_e('Mapping Templates', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'excel' && $this->subtab === 'export' ? 'lsd-nav-tab-active' : ''; ?>" data-key="export"><?php esc_html_e('Export', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-has-children lsd-json-nav <?php echo $this->tab === 'json' ? ' lsd-nav-expanded' : ''; ?>">
            <a class="lsd-nav-tab <?php echo $this->tab === 'json' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=json')); ?>">
                <i class="listdom-icon lsdi-file-script lsd-m-0"></i>
                <?php esc_html_e('JSON', 'listdom'); ?>
            </a>
            <ul class="lsd-nav-sub-tabs" data-parent="json" <?php echo $this->tab === 'json' ? '': 'hidden'; ?>>
                <li class="lsd-nav-tab <?php echo $this->tab === 'json' && ($this->subtab === 'import' || !$this->subtab) ? 'lsd-nav-tab-active' : ''; ?>" data-key="import"><?php esc_html_e('Import', 'listdom'); ?></li>
                <li class="lsd-nav-tab <?php echo $this->tab === 'json' && $this->subtab === 'export' ? 'lsd-nav-tab-active' : ''; ?>" data-key="export"><?php esc_html_e('Export', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-dummy-date-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'dummy-data' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=dummy-data')); ?>">
                <i class="listdom-icon lsdi-database lsd-m-0"></i>
                <?php esc_html_e('Dummy Data', 'listdom'); ?>
            </a>
        </li>
        <?php
        /**
         * Third party plugins
         */
        do_action('lsd_admin_ix_tabs', $this->tab);
        ?>
    </ul>
    <p class="lsd-nav-support-link"><?php echo sprintf(esc_html__("Have Problems? %s", 'listdom'), '<strong><a href="' . LSD_Base::getSupportURL() . '" target="_blank">' . esc_html__('Contact Support', 'listdom') . '</a></strong>'); ?></p>
</div>
