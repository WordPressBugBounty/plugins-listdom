<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
$active_subtab = in_array($this->subtab, ['dummy-data', 'blueprint'], true) ? $this->subtab : 'dummy-data';
?>
<div class="lsd-dummy-data-wrap">
    <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex lsd-flex-wrap lsd-mb-4" data-for=".lsd-dummy-data-tab-switcher-content">
        <li data-tab="dummy-data-ix-dummy-data" class="<?php echo $active_subtab === 'dummy-data' ? 'lsd-sub-tabs-active' : ''; ?>">
            <a href="#"><?php esc_html_e('Dummy Data', 'listdom'); ?></a>
        </li>
        <li data-tab="dummy-data-ix-blueprint" class="<?php echo $active_subtab === 'blueprint' ? 'lsd-sub-tabs-active' : ''; ?>">
            <a href="#"><?php esc_html_e('Data Collection', 'listdom'); ?></a>
        </li>
    </ul>

    <div class="lsd-tab-switcher-content lsd-dummy-data-tab-switcher-content<?php echo $active_subtab === 'dummy-data' ? ' lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-dummy-data-ix-dummy-data-content">
        <?php $this->include_html_file('menus/ix/tabs/dummy-data/dummy.php'); ?>
    </div>

    <div class="lsd-tab-switcher-content lsd-dummy-data-tab-switcher-content<?php echo $active_subtab === 'blueprint' ? ' lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-dummy-data-ix-blueprint-content">
        <?php $this->include_html_file('menus/ix/tabs/dummy-data/blueprint.php'); ?>
    </div>
</div>
