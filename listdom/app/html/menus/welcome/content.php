<?php
// no direct access
defined('ABSPATH') || die();

$this->include_html_file('menus/welcome/tabs/features.php');
$this->include_html_file('menus/welcome/tabs/location.php');
$this->include_html_file('menus/welcome/tabs/dummy-data.php');
$this->include_html_file('menus/welcome/tabs/collect.php');
$this->include_html_file('menus/welcome/tabs/finish.php');
?>
<script>
jQuery(function ($)
{
    const Stepper = {
        active: 1,

        init() {
            this.active = this.getStepFromURL();
            this.goTo(this.active);

            // Event listeners
            $('.lsd-welcome-button-wrapper .lsd-skip-step').on('click', (e) => {
                e.preventDefault();
                if (this.active < this.steps().length) this.goTo(this.active + 1);
            });

            window.handleStepNavigation = (step) => this.goTo(step);
        },

        goTo(step) {
            this.active = step;
            this.updateSteps();
            this.updateContent();
            this.updateURL();

            if (step === this.steps().length) this.steps().eq(step - 1).addClass('completed');
        },

        updateSteps() {
            const $steps = this.steps();
            $steps.removeClass('active completed');
            $steps.slice(0, this.active - 1).addClass('completed');
            $steps.eq(this.active - 1).addClass('active');
        },

        updateContent() {
            $('.lsd-welcome-step-content')
            .addClass('lsd-util-hide')
            .filter(`#step-${this.active}`)
            .removeClass('lsd-util-hide');
        },

        updateURL() {
            const url = new URL(window.location.href);
            url.searchParams.set('step', this.active);
            window.history.pushState({ path: url.toString() }, '', url.toString());
        },

        getStepFromURL() {
            const step = parseInt(new URLSearchParams(window.location.search).get('step'), 10) || 1;

            const $map_component = $('#lsd_component_map');
            const map_enabled = $map_component.length ? $map_component.is(':checked') : true;
            if (!map_enabled && step === 2) return 1;

            return step;
        },

        steps() {
            return $('.lsd-stepper-tabs .step');
        }
    };

    Stepper.init();
});
</script>
