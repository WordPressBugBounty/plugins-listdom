(function ($, window, document) {
    'use strict';

    const AjaxHelpers = {
        getAjaxUrl() {
            if (window.lsd && window.lsd.ajaxurl) return window.lsd.ajaxurl;
            if (typeof ajaxurl !== 'undefined') return ajaxurl;
            return '';
        },
        getNonce($field, fallback = '') {
            return ($field && $field.length) ? $field.val() : fallback;
        }
    };

    const escapeRegExp = (string) => {
        return (string || '').toString().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    };

    const shouldDebugTemplateBuilder = () => !!(window.lsd && window.lsd.debug);
    const templateBuilderDebugCounters = {};
    const debugTemplateBuilder = (key, reason = '', details) => {
        if (!shouldDebugTemplateBuilder()) return;

        templateBuilderDebugCounters[key] = (templateBuilderDebugCounters[key] || 0) + 1;

        const parts = ['[Listdom Builder]', key, '#' + templateBuilderDebugCounters[key]];
        if (reason) parts.push(reason);

        if (typeof details !== 'undefined') {
            console.log(parts.join(' '), details);
            return;
        }

        console.log(parts.join(' '));
    };

    const previewListingSelector = 'input[name="lsd_template_preview_listing[]"], input[name="lsd_template_preview_listing"], select[name="lsd_template_preview_listing"]';
    const getPreviewListingInputs = ($scope) => {
        const $root = ($scope && $scope.length) ? $scope : $(document);
        return $root.find(previewListingSelector);
    };
    const getPreviewListingSignature = ($scope) => {
        return getPreviewListingInputs($scope).map(function ()
        {
            return ($(this).val() ?? '').toString();
        }).get().join('|');
    };
    const bindPreviewListingMutationObserver = ($scope, key, callback) => {
        if (typeof MutationObserver === 'undefined') return;

        const $root = ($scope && $scope.length) ? $scope : $(document);
        const $inputs = getPreviewListingInputs($root);
        const $current = $inputs.first().closest('.lsd-autosuggest-current');
        if (!$current.length) return;

        const observerKey = `${key}PreviewListingObserver`;
        const existingObserver = $current.data(observerKey);

        if (existingObserver && typeof existingObserver.disconnect === 'function') {
            existingObserver.disconnect();
        }

        let signature = getPreviewListingSignature($root);
        const observer = new MutationObserver(() => {
            const nextSignature = getPreviewListingSignature($root);
            if (nextSignature === signature) return;

            signature = nextSignature;
            callback();
        });

        observer.observe($current.get(0), {
            childList: true,
            subtree: true,
        });

        $current.data(observerKey, observer);
    };

    const iconPickerOptions = {
        emptyIcon: false,
        emptyIconValue: '',
        iconsPerPage: 16
    };

    const teardownIconPickers = ($scope) => {
        if (typeof $.fn.fontIconPicker === 'undefined') return;

        const $icons = ($scope && $scope.length) ? $scope.find('select.lsd-iconpicker') : $('select.lsd-iconpicker');

        $icons.each(function () {
            const $select = $(this);

            if (typeof $select.destroyPicker === 'function') {
                $select.destroyPicker();
            }

            $select.removeData('fontIconPicker');
            $select.removeData('fontIconPickerInitialized');
        });
    };

    const initIconPickers = ($scope) => {
        if (typeof $.fn.fontIconPicker === 'undefined') return;
        const $icons = ($scope && $scope.length) ? $scope.find('select.lsd-iconpicker') : $('select.lsd-iconpicker');

        $icons.each(function () {
            const $select = $(this);
            if ($select.closest('[data-lsd-repeater-template]').length) return;
            if ($select.data('fontIconPicker') || $select.data('fontIconPickerInitialized')) return;

            $select.fontIconPicker(iconPickerOptions);
            $select.data('fontIconPickerInitialized', true);
        });
    };

    const updateRepeaterIndices = ($repeater) => {
        if (!$repeater || !$repeater.length) return;

        const baseName = ($repeater.data('lsd-repeater-name') || '').toString();
        const baseId = ($repeater.data('lsd-repeater-id-base') || '').toString();

        const namePattern = baseName ? new RegExp('^' + escapeRegExp(baseName) + '\\[(\\d+|__INDEX__)\\]') : null;
        const idPattern = baseId ? new RegExp('^' + escapeRegExp(baseId) + '_(\\d+|__INDEX__)_') : null;

        $repeater.find('[data-lsd-repeater-item]').each(function (index) {
            const $item = $(this);
            $item.attr('data-lsd-repeater-index', index);

            $item.find('[name]').each(function () {
                const $field = $(this);
                const name = ($field.attr('name') || '').toString();
                if (!name) return;

                if (namePattern && namePattern.test(name)) {
                    $field.attr('name', name.replace(namePattern, baseName + '[' + index + ']'));
                } else if (name.indexOf('__INDEX__') !== -1) {
                    $field.attr('name', name.replace(/__INDEX__/g, index));
                }
            });

            if (idPattern) {
                $item.find('[id]').each(function () {
                    const $field = $(this);
                    const currentId = ($field.attr('id') || '').toString();
                    if (!currentId) return;

                    if (idPattern.test(currentId)) {
                        $field.attr('id', currentId.replace(idPattern, baseId + '_' + index + '_'));
                    } else if (currentId.indexOf('__INDEX__') !== -1) {
                        $field.attr('id', currentId.replace(/__INDEX__/g, index));
                    }
                });

                $item.find('label[for]').each(function () {
                    const $label = $(this);
                    const currentFor = ($label.attr('for') || '').toString();
                    if (!currentFor) return;

                    if (idPattern.test(currentFor)) {
                        $label.attr('for', currentFor.replace(idPattern, baseId + '_' + index + '_'));
                    } else if (currentFor.indexOf('__INDEX__') !== -1) {
                        $label.attr('for', currentFor.replace(/__INDEX__/g, index));
                    }
                });
            }
        });
    };

    const initRepeaters = ($scope) => {
        const $root = ($scope && $scope.length) ? $scope : $(document);

        $root.find('[data-lsd-repeater]').each(function () {
            const $repeater = $(this);
            updateRepeaterIndices($repeater);
        });

        $(document)
            .off('click.lsdRepeaterAdd')
            .on('click.lsdRepeaterAdd', '[data-lsd-repeater-add]', function (event) {
                event.preventDefault();
                const $repeater = $(this).closest('[data-lsd-repeater]');
                if (!$repeater.length) return;

                const $template = $repeater.find('[data-lsd-repeater-template]').first().find('[data-lsd-repeater-item]').first();
                const $items = $repeater.find('.lsd-template-editor-repeater__items').first();
                if (!$template.length || !$items.length) return;

                const $newItem = $template.clone();
                teardownIconPickers($newItem);
                $newItem.find('[data-lsd-repeater-template]').remove();
                $items.append($newItem);

                updateRepeaterIndices($repeater);
                initIconPickers($newItem);

                $newItem.find(':input').first().trigger('focus');
            })
            .off('click.lsdRepeaterRemove')
            .on('click.lsdRepeaterRemove', '[data-lsd-repeater-remove]', function (event) {
                event.preventDefault();
                const $repeater = $(this).closest('[data-lsd-repeater]');
                const $item = $(this).closest('[data-lsd-repeater-item]');
                if (!$item.length) return;

                $item.remove();
                updateRepeaterIndices($repeater);
            });
    };

    const SidebarTabManager = {
        lastOptionsTabKey: 'content',
        tabsBound: false,
        getWorkspace() {
            return $('.lsd-template-editor-workspace').first();
        },
        getMenu() {
            return $('[data-lsd-template-editor-menu="left-default"]').first();
        },
        getOptionsMenu() {
            return $('[data-lsd-template-editor-menu="left-options"]').first();
        },
        getSelectionDisplay() {
            return $('[data-lsd-template-selection]').first();
        },
        getLastOptionsTabKey() {
            return this.lastOptionsTabKey || 'content';
        },
        setLastOptionsTabKey(tabKey) {
            const key = (tabKey || '').toString().trim();
            if (!key) return;
            this.lastOptionsTabKey = key;
        },
        bindTabMemory() {
            if (this.tabsBound) return;
            this.tabsBound = true;

            const self = this;
            $(document).on('click.lsdOptionsTabMemory', '[data-lsd-template-editor-menu="left-options"] li[data-tab]', function () {
                const key = ($(this).data('tab') || '').toString();
                if (key) self.setLastOptionsTabKey(key);
            });
        },
        getActiveTabKey() {
            const $optionsMenu = this.getOptionsMenu();
            if ($optionsMenu.length) {
                const $active = $optionsMenu.find('li.lsd-sub-tabs-active').first();
                if ($active.length) return ($active.data('tab') || '').toString();
            }

            const $menu = this.getMenu();
            if (!$menu.length) return '';
            const $active = $menu.find('li.lsd-sub-tabs-active').first();
            return ($active.data('tab') || '').toString();
        },
        applyVisibility(hasSelection, settingsMode) {
            const $menu = this.getMenu();
            const $optionsMenu = this.getOptionsMenu();
            const $selectionDisplay = this.getSelectionDisplay();
            if (!$menu.length && !$optionsMenu.length) return;
            const $elementsList = $menu.length ? $menu.find('ul.lsd-tab-switcher').first() : $();

            const setVisibility = ($target, isVisible) => {
                if (!$target || !$target.length) return;
                $target
                    .toggleClass('lsd-util-hide', !isVisible)
                    .attr('aria-hidden', isVisible ? 'false' : 'true');
            };

            const setTabState = ($tabs, isVisible) => {
                if (!$tabs || !$tabs.length) return;
                $tabs
                    .toggleClass('lsd-util-hide', !isVisible)
                    .attr('aria-hidden', isVisible ? 'false' : 'true')
                    .find('a').attr('tabindex', isVisible ? '0' : '-1');
            };

            const $tabs = $menu.length ? $menu.find('li') : $();
            const $elementsTab = $tabs.filter('[data-tab="elements"]');
            const $settingsTabs = $tabs.filter('[data-tab="general"], [data-tab="layout"]');
            const $optionsTabs = $optionsMenu.length ? $optionsMenu.find('li') : $();
            const $workspace = this.getWorkspace();
            const isCollapsed = $workspace.hasClass('lsd-template-editor--sidebar-collapsed');
            const isLayoutDisabled = $workspace.length && $workspace.hasClass('lsd-template-editor--layout-disabled');

            const applyLayoutDisabled = () => {
                if (!isLayoutDisabled) return;
                const $layoutTab = $menu.find('li[data-tab="layout"]');
                const $layoutPanel = $('#lsd-tab-switcher-layout-content');

                setTabState($layoutTab, false);
                setVisibility($layoutPanel, false);

                if ($layoutTab.hasClass('lsd-sub-tabs-active')) {
                    const $fallback = $menu.find('li[data-tab="general"]').first();
                    if ($fallback.length) {
                        $fallback.trigger('click');
                    }
                }
            };

            if (settingsMode) {
                setTabState($elementsTab, false);
                setTabState($optionsTabs, false);
                setTabState($settingsTabs, true);
                setVisibility($optionsMenu, false);
                setVisibility($selectionDisplay, false);
                setVisibility($elementsList, true);
                $optionsTabs.removeClass('lsd-sub-tabs-active');
                applyLayoutDisabled();
                return;
            }

            setTabState($settingsTabs, false);
            if (isCollapsed) {
                setTabState($elementsTab, true);
                setTabState($optionsTabs, false);
                setVisibility($optionsMenu, false);
                setVisibility($selectionDisplay, false);
                setVisibility($elementsList, true);
                $optionsTabs.removeClass('lsd-sub-tabs-active');
                return;
            }

            if (hasSelection) {
                setTabState($elementsTab, false);
                setTabState($optionsTabs, true);
                setVisibility($optionsMenu, true);
                setVisibility($selectionDisplay, true);
                setVisibility($elementsList, false);
                if ($optionsMenu.length && !$optionsMenu.find('.lsd-sub-tabs-active').length) {
                    const $contentTab = $optionsMenu.find('[data-tab="content"]').first();
                    if ($contentTab.length) {
                        $contentTab.trigger('click');
                    }
                }
            } else {
                setTabState($elementsTab, true);
                setTabState($optionsTabs, false);
                setVisibility($optionsMenu, false);
                setVisibility($selectionDisplay, false);
                setVisibility($elementsList, true);
                $optionsTabs.removeClass('lsd-sub-tabs-active');
            }
            applyLayoutDisabled();
        },
        activate(tabKey) {
            const key = (tabKey || '').toString();
            if (!key) return;

            const $workspace = this.getWorkspace();
            if ($workspace.length) {
                $workspace.trigger('lsd-template-settings-mode', [false]);
            }

            const $menus = [this.getOptionsMenu(), this.getMenu()];
            let $tab = $();

            $menus.some(($menu) => {
                if (!$menu || !$menu.length) return false;
                const $candidate = $menu.find('li[data-tab="' + key + '"]').first();
                if (!$candidate.length) return false;
                $tab = $candidate;
                return true;
            });

            if (!$tab.length) return;

            if (this.getOptionsMenu().find('li[data-tab="' + key + '"]').length) {
                this.setLastOptionsTabKey(key);
            }
            $tab.trigger('click');
        }
    };

    /**
     * ------------------------------------------------------------------------
     * TemplateEditorCore – bootstraps all classes
     * ------------------------------------------------------------------------
     */
    class TemplateEditorCore {
        constructor() {
            this.$body = $('body');
            if (!this.$body.hasClass('lsd-template-editor')) return;

            this.setupRoot();

            const $workspace = $('.lsd-template-editor-workspace');

            this.dom = {
                $body: this.$body,
                $workspace,
                $structurePanel: $('[data-lsd-template-editor-structure]'),
                $responsiveControls: $('.lsd-template-editor-workspace__responsive [data-lsd-responsive]'),
                $titleInput: $('#title'),
                $titleWrapper: $('.lsd-template-editor-topbar__title'),
                $selectionWrapper: $('[data-lsd-template-selection]').first(),
                $selectionLabel: $('[data-lsd-template-selection-label]').first(),
                $selectionIcon: $('[data-lsd-template-selection] i').first(),
                $settingsTitleInput: $('[data-lsd-template-setting="title"]'),
                $typeSelect: $('#lsd-template-editor-type').length
                    ? $('#lsd-template-editor-type')
                    : $('#lsd-template-settings-type'),
                $typeMetaboxField: $('#lsd-template-type').find('[name="lsd_template_type"]'),
                $settingsTypeSelect: $('[data-lsd-template-setting="type"]'),
                $settingsSaveBtn: $('[data-lsd-template-action="settings-save"]'),
                $saveBtn: $('[data-lsd-template-action="save"]'),
                $publishBtn: $('[data-lsd-template-action="publish"]'),
                $settingsToggle: $('[data-lsd-template-action="settings"]'),
                $settingsBack: $('[data-lsd-template-action="settings-back"]'),
                $saveTarget: $('#save-post'),
                $publishTarget: $('#publish'),
                $postForm: $('#post'),
                $postIdInput: $('#post_ID'),
                $settingsStatusSelect: $('[data-lsd-template-setting="status"]'),
                $settingsPageTemplateSelect: $('[data-lsd-template-setting="page-template"]'),
                $autoSaveToggle: $('[data-lsd-template-setting="autosave"]'),
                $autoSaveStatus: $('.lsd-template-editor-auto-save-status'),
                $postStatusInput: $('#post_status'),
                $hiddenPostStatusInput: $('#hidden_post_status'),
                $postStatusDisplay: $('#post-status-display'),
                $wpStatusDropdown: $('#post-status-select').find('select'),
                $workspaceCanvas: $('[data-lsd-template-editor-canvas]'),
                $previewIframe: $('[data-lsd-template-preview-iframe]'),
                $canvasTemplateSource: $('[data-lsd-canvas-template-source]'),
                $layoutField: $('[data-lsd-template-layout]'),
                $contentPanel: $('#lsd-tab-switcher-content-content'),
                $stylePanel: $('#lsd-tab-switcher-style-content'),
                $advancedPanel: $('#lsd-tab-switcher-advanced-content')
            };

            this.initModules();
        }

        setupRoot() {
            const docEl = document.documentElement;
            if (!docEl) return;

            docEl.classList.add('lsd-template-editor-root');

            if (docEl.classList.contains('wp-toolbar')) {
                docEl.style.paddingTop = '0';
            }
        }

        initModules() {
            SidebarTabManager.bindTabMemory();
            new TitleSync(this.dom);
            new TypeSync(this.dom);
            new StatusSync(this.dom);
            new SettingsSaveBindings(this.dom);
            this.savePublishBindings = new SavePublishBindings(this.dom);
            new PreviewLinkGuard(this.dom, this.savePublishBindings);
            new AutoSaveToggle(this.dom);
            new LayoutTabGuard(this.dom);
            this.dom.structureFloatingPanel = new StructureFloatingPanel(this.dom);
            new ElementsAddToggle(this.dom);
            new NiceScrollManager(this.dom);
            this.dom.previewIframeManager = new PreviewIframeManager(this.dom);

            if (this.dom.$workspace.length) {
                this.dom.canvasManager = new CanvasManager(this.dom);
                if (this.dom.previewIframeManager && typeof this.dom.previewIframeManager.scheduleStateRender === 'function') {
                    this.dom.previewIframeManager.scheduleStateRender();
                }
                new UnsavedChangesGuard(this.dom);
                new SettingsUnsavedAlert(this.dom);
                new SettingsModeToggle(this.dom);
                new SidebarCollapseToggle(this.dom);
                new ElementSearchFilter(this.dom);
                new ElementListManager(this.dom);
                this.dom.responsivePreview = new ResponsivePreview(this.dom);
                new ResponsiveSettingsFields(this.dom);
            }
        }

        static init() {
            return new TemplateEditorCore();
        }
    }

    /**
     * ------------------------------------------------------------------------
     * StructureFloatingPanel – draggable floating structure panel
     * ------------------------------------------------------------------------
     */
    class StructureFloatingPanel {
        constructor(dom) {
            this.dom = dom || {};
            this.$panel = $('#lsd-tab-switcher-structure-content');
            this.$toggle = $('[data-lsd-template-action="structure"]');
            this.$close = this.$panel.find('[data-lsd-structure-close]');
            this.$workspace = this.dom.$workspace || $('.lsd-template-editor-workspace');
            this.isOpen = false;
            this.openClass = 'is-open';
            this.centeredClass = 'lsd-template-editor-structure-floating--centered';
            this.freeClass = 'lsd-template-editor-structure-floating--free';

            if (!this.$panel.length || !this.$toggle.length) return;

            const containment = 'window';

            if ($.fn && typeof $.fn.draggable === 'function') {
                this.$panel.draggable({
                    handle: '.lsd-template-editor-structure-drag-handle',
                    cancel: '[data-lsd-structure-close]',
                    containment: containment,
                    scroll: false,
                    start: () => {
                        this.$panel.removeClass(this.centeredClass).addClass(this.freeClass);
                    }
                });
            }

            this.bindToggle();
            this.bindClose();
            this.bindOutsideClick();
            this.bindWorkspaceClose();
        }

        bindToggle() {
            this.$toggle.on('click.lsdStructurePanel', (event) => {
                event.preventDefault();
                this.setOpen(!this.isOpen);
            });
        }

        bindClose() {
            if (!this.$close.length) return;

            this.$close.on('click.lsdStructurePanel', (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.setOpen(false);
            });
        }

        bindOutsideClick() {
            $(document).on('mousedown.lsdStructurePanel', (event) => {
                if (!this.isOpen) return;

                const $target = $(event.target);
                if ($target.closest(this.$panel).length) return;
                if ($target.closest(this.$toggle).length) return;

                this.setOpen(false);
            });
        }

        bindWorkspaceClose() {
            if (!this.$workspace.length) return;

            this.$workspace
                .off('lsd-template-structure-close.lsdStructurePanel')
                .on('lsd-template-structure-close.lsdStructurePanel', () => {
                    if (!this.isOpen) return;
                    this.setOpen(false);
                });
        }

        setOpen(nextState) {
            this.isOpen = !!nextState;
            if (this.isOpen) {
                this.$panel
                    .css({ top: '50%', left: '50%' })
                    .removeClass(this.freeClass)
                    .addClass(this.centeredClass);
            }

            this.$panel
                .toggleClass('lsd-tab-switcher-content-active', this.isOpen)
                .toggleClass(this.openClass, this.isOpen);
            this.$toggle.attr('aria-pressed', this.isOpen ? 'true' : 'false');
        }
    }

    class PreviewIframeManager {
        constructor(dom) {
            this.dom = dom || {};
            this.$workspace = this.dom.$workspace || $('.lsd-template-editor-workspace');
            this.$canvas = this.dom.$workspaceCanvas || $('[data-lsd-template-editor-canvas]');
            this.$iframe = this.dom.$previewIframe || this.$canvas.find('[data-lsd-template-preview-iframe]').first();
            this.$loading = this.$canvas.find('[data-lsd-template-preview-loading]').first();
            if (!this.$canvas.length || !this.$iframe.length) return;

            this.baseUrl = (this.$canvas.data('lsdPreviewIframeUrl') || '').toString();
            this.templateId = parseInt(this.$canvas.data('lsdPreviewTemplateId'), 10) || 0;
            this.lastUrl = '';
            this.renderTimer = null;
            this.loadingTimer = null;
            this.iframeReady = false;
            this.previewRootId = 'lsd-template-preview-root';
            this.niceScrollOptions = {
                cursorcolor: 'rgba(0, 0, 0, 0.45)',
                cursorwidth: 3,
                cursorborder: '0',
                cursorborderradius: 8,
                cursoropacitymin: 0,
                cursoropacitymax: 0.6,
                autohidemode: 'leave',
                railpadding: { top: 6, right: 4, left: 0, bottom: 6 },
                horizrailenabled: false,
                railalign: 'right',
                railvalign: 'top',
                background: 'transparent',
                zindex: 9999
            };

            if (!this.baseUrl || !this.templateId) return;

            this.init();
        }

        init() {
            this.$iframe
                .off('load.lsdPreviewIframe')
                .on('load.lsdPreviewIframe', () => {
                this.iframeReady = true;
                if (shouldDebugTemplateBuilder()) {
                    console.log('Listdom iframe loaded');
                }
                this.bindIframeLinkGuard();
                this.refreshIframeNiceScroll();
                if (this.$workspace && this.$workspace.length) {
                    if (shouldDebugTemplateBuilder()) {
                        console.log('Triggering iframe ready');
                    }
                    this.$workspace.trigger('lsd-template-iframe-ready', [this.getIframeWindow()]);
                }
                this.setLoadingState(false);
                this.scheduleStateRender();
                });

            this.setLoadingState(true);
            this.refresh(true);

            if (this.$workspace && this.$workspace.length) {
                this.$workspace
                    .off('lsd-template-settings-saved.lsdPreviewIframe')
                    .on('lsd-template-settings-saved.lsdPreviewIframe', () => {
                        this.refresh(true);
                    });

                this.$workspace
                    .off('lsd-template-state-updated.lsdPreviewIframe')
                    .on('lsd-template-state-updated.lsdPreviewIframe', (event, payload) => {
                        debugTemplateBuilder('lsd-template-state-updated:listener', 'preview-iframe', payload);
                        this.scheduleStateRender();
                    });

                this.$workspace
                    .off('lsd-template-responsive-mode.lsdPreviewIframe')
                    .on('lsd-template-responsive-mode.lsdPreviewIframe', () => {
                        this.refreshIframeNiceScroll();
                    });
            }

            $(document)
                .off('change.lsdPreviewIframe', previewListingSelector)
                .on('change.lsdPreviewIframe', previewListingSelector, () => {
                    window.setTimeout(() => this.refresh(true), 0);
                });

            bindPreviewListingMutationObserver(this.$workspace, 'iframe', () => {
                window.setTimeout(() => this.refresh(true), 0);
            });
        }

        getNonce() {
            const $nonceField = (this.$workspace && this.$workspace.length)
                ? this.$workspace.find('input[name="lsd-template-element"]').first()
                : $('input[name="lsd-template-element"]').first();

            return AjaxHelpers.getNonce($nonceField);
        }

        getTemplateType() {
            const $typeField = (this.$workspace && this.$workspace.length)
                ? this.$workspace.find('[data-lsd-template-setting="type"]').first()
                : $();

            return ($typeField.length ? $typeField.val() : '').toString();
        }

        getSerializedState() {
            if (!this.dom.canvasManager || !this.dom.canvasManager.state) return '';

            if (typeof this.dom.canvasManager.refreshLayoutState === 'function') {
                this.dom.canvasManager.refreshLayoutState();
            }

            return JSON.stringify(this.dom.canvasManager.state.serialize());
        }

        getPreviewRoot() {
            const iframeWindow = this.getIframeWindow();
            if (!iframeWindow || !iframeWindow.document) return null;

            let root = iframeWindow.document.getElementById(this.previewRootId);
            if (root) return root;

            const body = iframeWindow.document.body;
            if (!body) return null;

            root = iframeWindow.document.createElement('div');
            root.id = this.previewRootId;
            body.appendChild(root);

            return root;
        }

        getIframeWindow() {
            const iframeEl = this.$iframe.get(0);
            if (!iframeEl || !iframeEl.contentWindow) return null;

            return iframeEl.contentWindow;
        }

        getPreviewListing() {
            const $inputs = (this.$workspace && this.$workspace.length)
                ? this.$workspace.find('input[name="lsd_template_preview_listing[]"], input[name="lsd_template_preview_listing"], select[name="lsd_template_preview_listing"]')
                : $();

            if (!$inputs.length) return 0;

            const value = parseInt($inputs.first().val(), 10);
            return Number.isNaN(value) ? 0 : value;
        }

        buildPreviewUrl(baseUrl) {
            let url;

            try {
                url = new URL(baseUrl, window.location.origin);
            } catch (error) {
                return '';
            }

            const previewListing = this.getPreviewListing();
            const templateType = this.getTemplateType();

            url.searchParams.set('lsd_template_preview', '1');
            url.searchParams.set('lsd_template_id', String(this.templateId));

            if (previewListing > 0) {
                url.searchParams.set('lsd_preview_listing', String(previewListing));
            } else {
                url.searchParams.delete('lsd_preview_listing');
            }

            if (templateType) {
                url.searchParams.set('lsd_preview_template_type', templateType);
            } else {
                url.searchParams.delete('lsd_preview_template_type');
            }

            return url.toString();
        }

        buildUrl() {
            return this.buildPreviewUrl(this.baseUrl);
        }

        refresh(force = false) {
            if (!this.$iframe.length) return;

            const nextUrl = this.buildUrl();
            if (!nextUrl) return;
            if (!force && nextUrl === this.lastUrl) return;

            this.lastUrl = nextUrl;
            this.iframeReady = false;
            this.setLoadingState(true);
            this.$iframe.attr('src', nextUrl);
        }

        setLoadingState(isLoading) {
            const loading = !!isLoading;

            if (this.loadingTimer) {
                window.clearTimeout(this.loadingTimer);
                this.loadingTimer = null;
            }

            if (this.$canvas && this.$canvas.length) {
                this.$canvas.attr('aria-busy', loading ? 'true' : 'false');
            }

            if (!this.$loading || !this.$loading.length) return;

            if (loading) {
                this.$loading
                    .removeClass('lsd-util-hide is-leaving')
                    .addClass('is-active')
                    .attr('aria-hidden', 'false');

                return;
            }

            this.$loading.removeClass('is-active').addClass('is-leaving');

            this.loadingTimer = window.setTimeout(() => {
                this.$loading
                    .removeClass('is-leaving')
                    .addClass('lsd-util-hide')
                    .attr('aria-hidden', 'true');

                this.loadingTimer = null;
            }, 180);
        }

        scheduleStateRender() {
            if (!this.iframeReady) return;
            debugTemplateBuilder('scheduleStateRender', 'iframe-mounted');

            if (this.renderTimer) window.clearTimeout(this.renderTimer);
            this.renderTimer = window.setTimeout(() => {
                this.renderTimer = null;
                this.refreshIframeNiceScroll();
            }, 60);
        }

        runFrontendPreviewInit() {
            const iframeWindow = this.getIframeWindow();
            if (!iframeWindow) return;

            if (typeof iframeWindow.listdom_onload === 'function') {
                iframeWindow.listdom_onload();
            }
        }

        bindIframeLinkGuard() {
            const iframeWindow = this.getIframeWindow();
            if (!iframeWindow || !iframeWindow.document) return;

            const $iframeDocument = $(iframeWindow.document);

            $iframeDocument
                .off('click.lsdPreviewGuard', 'a')
                .on('click.lsdPreviewGuard', 'a', (event) => {
                    const $link = $(event.currentTarget);

                    const href = ($link.attr('href') || '').trim();
                    if (!href || href === '#' || href.toLowerCase().startsWith('javascript:')) return;

                    event.preventDefault();
                    event.stopPropagation();
                })
                .off('submit.lsdPreviewGuard', 'form')
                .on('submit.lsdPreviewGuard', 'form', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });
        }

        refreshIframeNiceScroll() {
            if (typeof $.fn.niceScroll !== 'function') return;

            const iframeWindow = this.getIframeWindow();
            if (!iframeWindow || !iframeWindow.document) return;

            const iframeDocument = iframeWindow.document;
            const scrollElement = this.resolveIframeScrollElement(iframeDocument);

            if (!scrollElement) return;

            const previousElement = this.iframeNiceScrollElement || null;
            if (previousElement && previousElement !== scrollElement) {
                const previousInstance = $(previousElement).getNiceScroll();
                if (previousInstance && previousInstance.length && typeof previousInstance.remove === 'function') {
                    previousInstance.remove();
                }
            }

            const $scrollElement = $(scrollElement);
            const instance = $scrollElement.getNiceScroll();
            this.iframeNiceScrollElement = scrollElement;

            if (instance && instance.length) {
                instance.resize();
                return;
            }

            $scrollElement.niceScroll(this.niceScrollOptions);
        }

        resolveIframeScrollElement(iframeDocument = null) {
            const iframeWindow = this.getIframeWindow();
            const doc = iframeDocument || (iframeWindow ? iframeWindow.document : null);
            if (!doc) return null;

            const isScrollable = (node) => {
                if (!node || !node.ownerDocument || !node.ownerDocument.defaultView) return false;
                const styles = node.ownerDocument.defaultView.getComputedStyle(node);
                const overflowY = (styles && styles.overflowY ? styles.overflowY : '').toLowerCase();
                return ['auto', 'scroll', 'overlay'].includes(overflowY) && node.scrollHeight > (node.clientHeight + 2);
            };

            if (
                this.iframeNiceScrollElement
                && this.iframeNiceScrollElement.ownerDocument === doc
                && isScrollable(this.iframeNiceScrollElement)
            ) {
                return this.iframeNiceScrollElement;
            }

            let scrollElement = doc.body;

            if (!scrollElement || !scrollElement.classList || !scrollElement.classList.contains('lsd-template-editor-iframe-surface') || !isScrollable(scrollElement)) {
                scrollElement = doc.body;
            }

            if (!isScrollable(scrollElement)) {
                scrollElement = doc.getElementById('lsd-template-preview-root');
            }

            if (!isScrollable(scrollElement)) {
                scrollElement = doc.scrollingElement || doc.documentElement || doc.body;
            }

            return scrollElement || null;
        }

        scrollIframeTo(top) {
            const iframeWindow = this.getIframeWindow();
            if (!iframeWindow || !iframeWindow.document) return false;

            const doc = iframeWindow.document;
            const body = doc.body;
            const scrollElement = this.resolveIframeScrollElement(doc);
            const isDocumentScrollEl = scrollElement === doc.scrollingElement
                || scrollElement === doc.documentElement
                || scrollElement === body;

            if (!scrollElement) return false;

            const $scrollElement = $(scrollElement);
            const instance = typeof $scrollElement.getNiceScroll === 'function'
                ? $scrollElement.getNiceScroll()
                : null;

            if (instance && instance.length && typeof instance.doScrollTop === 'function') {
                instance.doScrollTop(top, 300);
            } else {
                scrollElement.scrollTop = top;
                if (isDocumentScrollEl) {
                    if (doc.documentElement) doc.documentElement.scrollTop = top;
                    if (body) body.scrollTop = top;
                    if (typeof iframeWindow.scrollTo === 'function') {
                        iframeWindow.scrollTo(0, top);
                    }
                }
            }

            return true;
        }
    }

    /**
     * ------------------------------------------------------------------------
     * TemplateState – reusable layout & elements state
     * ------------------------------------------------------------------------
     */
    class TemplateState {
        constructor($layoutField) {
            this.$layoutField = $layoutField;
            this.state = {
                elements: {},
                layout: []
            };

            this.elementIdCounter = 0;
            this.layoutDirty = false;

            if (this.$layoutField && this.$layoutField.length) this.hydrateFromField();
        }

        // ---- Element helpers ----

        getElement(elementId) {
            if (!elementId) return null;
            return this.state.elements[elementId] || null;
        }

        ensureElement(elementData = {}) {
            if (!elementData || !elementData.id) return null;

            const existing = this.getElement(elementData.id);
            const label = elementData.label || elementData.type || '';
            const icon = elementData.icon || '';

            if (existing) {
                if (label && (!existing.label || existing.label !== label)) {
                    existing.label = label;
                }

                if (elementData.type && !existing.type) {
                    existing.type = elementData.type;
                }

                if (icon && (!existing.icon || existing.icon !== icon)) {
                    existing.icon = icon;
                }

                if (!existing.settings || typeof existing.settings !== 'object' || Array.isArray(existing.settings)) {
                    existing.settings = {};
                }

                existing.settings = this.normalizeSettingsShape(existing.settings);

                return existing;
            }

            this.state.elements[elementData.id] = {
                id: elementData.id,
                type: elementData.type || '',
                label,
                icon,
                settings: this.normalizeSettingsShape(elementData.settings || {})
            };

            return this.state.elements[elementData.id];
        }

        updateElementSettings(elementId, settings) {
            const state = this.getElement(elementId);
            if (!state) return;
            state.settings = this.normalizeSettingsShape(settings || {});
        }

        removeElement(elementId) {
            if (!elementId || !this.state.elements) return;
            delete this.state.elements[elementId];
        }

        // ---- Layout helpers ----

        purgeElementFromLayout(elementId) {
            if (!elementId || !Array.isArray(this.state.layout)) return;

            const removeFromNodes = (nodes) => {
                if (!Array.isArray(nodes)) return [];

                return nodes.reduce((rows, row) => {
                    if (!row || !row.id) return rows;
                    if (row.id === elementId) return rows;

                    const nextRow = Object.assign({}, row);
                    if (Array.isArray(nextRow.children)) {
                        nextRow.children = removeFromNodes(nextRow.children);
                    }

                    rows.push(nextRow);
                    return rows;
                }, []);
            };

            this.state.layout = removeFromNodes(this.state.layout);
        }

        createElementToken(length = 10) {
            const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            let token = '';

            if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
                const bytes = new Uint8Array(length);
                window.crypto.getRandomValues(bytes);
                for (let i = 0; i < bytes.length; i += 1) {
                    token += chars[bytes[i] % chars.length];
                }
                return token;
            }

            for (let i = 0; i < length; i += 1) {
                token += chars[Math.floor(Math.random() * chars.length)];
            }

            return token;
        }

        createElementId(elementKey = '') {
            const safeKey = String(elementKey || 'element')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '') || 'element';
            let elementId = '';
            let attempts = 0;

            do {
                elementId = `lsd-element-${safeKey}-${this.createElementToken(10)}`;
                attempts += 1;
            } while (this.state.elements[elementId] && attempts < 5);

            return elementId;
        }

        markLayoutDirty() {
            this.layoutDirty = true;
        }

        isLayoutDirty() {
            return this.layoutDirty;
        }

        clearLayoutDirty() {
            this.layoutDirty = false;
        }

        setCurrentElementId(elementId) {
            this.currentElementId = elementId || null;
        }

        normalizeLayoutTree(layout, depth = 0) {
            if (!Array.isArray(layout)) return [];

            return layout.reduce((nodes, item) => {
                if (!item || !item.id) return nodes;

                const existing = this.state.elements[item.id] || {};
                const itemType = item.type || existing.type || '';

                const next = {
                    id: item.id,
                    type: itemType,
                    children: []
                };

                if (item.label) next.label = item.label;
                if (Array.isArray(item.children)) {
                    next.children = this.normalizeLayoutTree(item.children, depth + 1);
                }

                if (depth === 0 && next.type !== 'container') {
                    const wrapperId = this.createElementId('container');

                    if (!this.state.elements[wrapperId]) {
                        this.state.elements[wrapperId] = {
                            id: wrapperId,
                            type: 'container',
                            label: 'Container',
                            icon: '',
                            settings: this.normalizeSettingsShape({})
                        };
                    }

                    nodes.push({
                        id: wrapperId,
                        type: 'container',
                        children: [next]
                    });
                    return nodes;
                }

                nodes.push(next);
                return nodes;
            }, []);
        }

        // ---- Persistence helpers ----

        parseLayoutField() {
            if (!this.$layoutField || !this.$layoutField.length) return null;
            const raw = this.$layoutField.val();
            if (!raw || typeof raw !== 'string') return null;

            try {
                const parsed = JSON.parse(raw);
                return parsed && typeof parsed === 'object' ? parsed : null;
            } catch (e) {
                return null;
            }
        }

        hydrateFromField() {
            const parsed = this.parseLayoutField();
            if (!parsed) return;

            if (parsed.elements && typeof parsed.elements === 'object') {
                const normalized = {};

                Object.keys(parsed.elements).forEach((id) => {
                    const el = parsed.elements[id] || {};

                    if (!el.id) el.id = id;
                    if (typeof el.type !== 'string') el.type = '';
                    if (typeof el.label !== 'string') el.label = el.type || '';
                    if (typeof el.icon !== 'string') el.icon = '';

                    if (!el.settings || typeof el.settings !== 'object' || Array.isArray(el.settings)) {
                        el.settings = {};
                    }

                    el.settings = this.normalizeSettingsShape(el.settings);

                    normalized[el.id] = el;
                });

                this.state.elements = normalized;
            }

            if (Array.isArray(parsed.layout)) {
                this.state.layout = this.normalizeLayoutTree(parsed.layout);
            }

            const ids = Object.keys(this.state.elements || {});
            ids.forEach((id) => {
                const match = String(id).match(/lsd-element-(\d+)/);
                if (!match || !match[1]) return;
                const num = parseInt(match[1], 10);
                if (!Number.isNaN(num) && num > this.elementIdCounter) {
                    this.elementIdCounter = num;
                }
            });
        }

        serialize() {
            return {
                layout: this.state.layout,
                elements: this.state.elements
            };
        }

        writeToField() {
            if (!this.$layoutField || !this.$layoutField.length) return;
            const serialized = this.serialize();
            this.$layoutField.val(JSON.stringify(serialized));
        }

        // ---- Deep merge / nested value utilities (used by settings) ----

        deepMerge(target, source) {
            if (!source || typeof source !== 'object') return target;

            Object.keys(source).forEach((key) => {
                const value = source[key];

                if (value && typeof value === 'object' && !Array.isArray(value)) {
                    if (!target[key] || typeof target[key] !== 'object' || Array.isArray(target[key])) {
                        target[key] = {};
                    }

                    this.deepMerge(target[key], value);
                } else {
                    target[key] = value;
                }
            });

            return target;
        }

        setNestedValue(target, path, value) {
            if (!path || !path.length) return;

            let current = target;

            for (let i = 0; i < path.length; i += 1) {
                const key = path[i];
                const isLast = i === path.length - 1;
                const nextKey = path[i + 1];

                if (key === '') {
                    if (!Array.isArray(current)) return;

                    if (isLast) {
                        current.push(value);
                        return;
                    }

                    const nextContainer = nextKey === '' ? [] : {};
                    current.push(nextContainer);
                    current = nextContainer;
                    continue;
                }

                if (isLast) {
                    current[key] = value;
                    return;
                }

                if (!current[key] || typeof current[key] !== 'object') {
                    current[key] = nextKey === '' ? [] : {};
                }

                current = current[key];
            }
        }

        normalizeSettingsShape(settings) {
            const normalized = { content: {}, style: {}, advanced: {} };

            if (!settings || typeof settings !== 'object' || Array.isArray(settings)) {
                return normalized;
            }

            if (settings.content && typeof settings.content === 'object' && !Array.isArray(settings.content)) {
                normalized.content = settings.content;
            }

            if (settings.style && typeof settings.style === 'object' && !Array.isArray(settings.style)) {
                normalized.style = settings.style;
            }

            if (settings.advanced && typeof settings.advanced === 'object' && !Array.isArray(settings.advanced)) normalized.advanced = settings.advanced;

            return normalized;
        }

        mergeSettings(settings) {
            const normalized = this.normalizeSettingsShape(settings);
            const merged = {};

            this.deepMerge(merged, normalized.content);
            this.deepMerge(merged, normalized.style);
            this.deepMerge(merged, normalized.advanced);

            return merged;
        }
    }

    /**
     * ------------------------------------------------------------------------
     * SettingsPanel – reusable content/style settings logic
     * ------------------------------------------------------------------------
     */
    class SettingsPanel {
        constructor($contentPanel, $stylePanel, $advancedPanel, state, options = {}) {
            this.$contentPanel = $contentPanel;
            this.$stylePanel = $stylePanel;
            this.$advancedPanel = $advancedPanel;
            this.state = state;
            this.onStateChange = (options && typeof options.onStateChange === 'function')
                ? options.onStateChange
                : null;
            this.suspendSyncSettings = false;
            this.toggleContainerPlaceholder = (options && typeof options.toggleContainerPlaceholder === 'function')
                ? options.toggleContainerPlaceholder
                : () => {};
            this.containerRootSelector = (options && options.containerRootSelector)
                ? options.containerRootSelector
                : '.lsd-template-editor-canvas__container-item';
            this.elementRootSelector = (options && options.elementRootSelector)
                ? options.elementRootSelector
                : '.lsd-template-editor-canvas__element-block';
            this.$selectionWrapper = (options && options.$selectionWrapper) ? options.$selectionWrapper : $();
            this.$selectionLabel = (options && options.$selectionLabel) ? options.$selectionLabel : $();
            this.$selectionIcon = (options && options.$selectionIcon) ? options.$selectionIcon : $();
            this.findRenderedElement = (options && typeof options.findRenderedElement === 'function')
                ? options.findRenderedElement
                : null;

            this.$selectedElement = $();
            this.settingsRequest = null;

            // Local strings, updated when an element settings form is first loaded
            this.settingsStrings = {
                select_placeholder: '',
                loading: '',
                settings_error: 'Unable to load settings for this element right now.',
                details_subtitle: ''
            };

            this.settingsErrorMessage = this.settingsStrings.settings_error;

            this.render(null);
        }

        updateSelectionTitle(title, iconClass = '') {
            if (!this.$selectionWrapper || !this.$selectionWrapper.length) return;

            const nextTitle = (title || '').toString().trim();
            const nextIcon = (iconClass || '').toString().trim();

            if (!nextTitle) {
                this.$selectionWrapper.addClass('lsd-util-hide');
                return;
            }

            if (this.$selectionLabel && this.$selectionLabel.length) {
                this.$selectionLabel.text(nextTitle);
            }

            if (this.$selectionIcon && this.$selectionIcon.length && nextIcon) {
                this.$selectionIcon.attr('class', nextIcon);
            }

            this.$selectionWrapper.removeClass('lsd-util-hide');
        }

        getString(key, fallback) {
            if (this.settingsStrings && typeof this.settingsStrings[key] === 'string' && this.settingsStrings[key].trim() !== '') {
                return this.settingsStrings[key];
            }
            return fallback;
        }

        initFieldControls($target) {
            if (!$target || !$target.length) return;

            if (typeof $.fn.wpColorPicker !== 'undefined') {
                const colorPickerOptions = {
                    change: function (event, ui) {
                        const $input = $(event.target);
                        const color = ui.color ? ui.color.toString() : '';
                        $input.val(color);
                        $input.trigger('change');
                    },
                    clear: function (event) {
                        const $input = $(event.target)
                            .closest('.wp-picker-container')
                            .find('input.lsd-colorpicker')
                            .first();

                        if (!$input.length) return;

                        $input.val('');
                        $input.trigger('input');
                    }
                };

                const attachColorPickerDock = ($input) => {
                    const $container = $input.closest('.wp-picker-container');
                    if (!$container.length) return;

                    const $button = $container.find('.wp-color-result').first();
                    if (!$button.length) return;

                    let pickerId = $container.data('lsdPickerId');
                    if (!pickerId) {
                        pickerId = 'lsd-colorpicker-' + Math.random().toString(36).slice(2);
                        $container.data('lsdPickerId', pickerId);
                        $container.attr('data-lsd-picker-id', pickerId);
                    }

                    const getHolder = () => {
                        let $holder = $container.data('lsdPickerHolder');
                        if ($holder && $holder.length) return $holder;

                        $holder = $container.find('.wp-picker-holder');
                        if ($holder.length) {
                            $container.data('lsdPickerHolder', $holder);
                            $holder.attr('data-lsd-picker-owner', pickerId);
                            return $holder;
                        }

                        $holder = $('.wp-picker-holder[data-lsd-picker-owner="' + pickerId + '"]');
                        if ($holder.length) {
                            $container.data('lsdPickerHolder', $holder);
                            return $holder;
                        }

                        return $();
                    };

                    const getHolderActions = ($holder) => {
                        let $actions = $holder.children('.lsd-colorpicker-holder-actions').first();

                        if (!$actions.length) {
                            $actions = $('<div class="lsd-colorpicker-holder-actions"></div>');
                            const $picker = $holder.children('.iris-picker').first();

                            if ($picker.length) $actions.insertBefore($picker);
                            else $holder.append($actions);
                        }

                        return $actions;
                    };

                    const dockControlsIntoHolder = () => {
                        const $holder = getHolder();
                        if (!$holder.length) return;

                        const $actions = getHolderActions($holder);
                        const $inputWrap = $container.find('.wp-picker-input-wrap').first();
                        const $actionButton = $container.find('.wp-picker-clear, .wp-picker-default').first();

                        if ($inputWrap.length && !$inputWrap.parent().is($actions)) {
                            $inputWrap.appendTo($actions);
                        }

                        if ($actionButton.length && !$actionButton.parent().is($actions)) {
                            $actionButton.appendTo($actions);
                        }
                    };

                    const resetHolder = () => {
                        const $holder = getHolder();
                        if (!$holder.length) return;

                        $holder
                            .removeClass('lsd-colorpicker-floating')
                            .css({
                                position: '',
                                top: '',
                                left: '',
                                zIndex: ''
                            });

                        if (!$holder.parent().is($container)) {
                            $holder.appendTo($container);
                        }

                        dockControlsIntoHolder();
                    };

                    const bindDockEvents = () => {
                        const ns = '.lsdColorPickerDock-' + pickerId;
                        $(window).off('resize' + ns);
                        $(document).off('mousedown' + ns);

                        const $scrollContainer = $container.closest('.lsd-template-editor-sidebar');
                        if ($scrollContainer.length) $scrollContainer.off('scroll' + ns);
                        const $iframes = $('iframe');
                        $iframes.off('mousedown' + ns);

                        $button
                            .off('click' + ns)
                            .on('click' + ns, () => {
                                window.setTimeout(() => {
                                    resetHolder();
                                }, 0);
                            });

                        const closePicker = () => {
                            try {
                                $input.wpColorPicker('close');
                            } catch (err) {
                                // ignore
                            }
                        };

                        $(document).on('mousedown' + ns, (event) => {
                            if (!$container.hasClass('wp-picker-active')) return;
                            if ($(event.target).closest($container).length) return;

                            closePicker();
                        });

                        $iframes.on('mousedown' + ns, () => {
                            if (!$container.hasClass('wp-picker-active')) return;
                            closePicker();
                        });
                    };

                    dockControlsIntoHolder();
                    bindDockEvents();
                };

                const ensureColorPicker = ($input) => {
                    if (!$input.length) return;

                    const hasInstance = !!$input.data('wpWpColorPicker');
                    const hasIris = !!$input.data('a8cIris');

                    if (!hasInstance || !hasIris) {
                        const pickerOptions = { ...colorPickerOptions };
                        const defaultColor = ($input.attr('data-default-color') || '').toString().trim();

                        if (defaultColor !== '') pickerOptions.defaultColor = defaultColor;

                        $input.wpColorPicker(pickerOptions);
                        attachColorPickerDock($input);
                        return;
                    }

                    if (!$input.hasClass('wp-color-picker')) {
                        $input.addClass('wp-color-picker');
                    }

                    $input.off('change.lsdColorSync').on('change.lsdColorSync', function () {
                        $input.trigger('input');
                    });

                    attachColorPickerDock($input);
                };

                $target.find('.lsd-colorpicker').each(function () {
                    const $input = $(this);
                    ensureColorPicker($input);
                });
            }

            if (typeof $.fn.select2 !== 'undefined') {
                $target.find('select.lsd-template-editor-select2, select[multiple]').each(function () {
                    const $select = $(this);
                    if ($select.hasClass('select2-hidden-accessible')) return;

                    $select.select2({
                        allowClear: !!$select.attr('multiple'),
                        placeholder: $select.attr('placeholder') || '',
                        width: '100%',
                        minimumResultsForSearch: 0,
                        shouldFocusInput: () => false,
                    });
                });
            }

            initIconPickers($target);
        }

        // ------------------ Rendering ------------------

        render(elementData, options = {}) {
            if (!this.$contentPanel.length) return;

            const state        = options.state || 'default';
            const contentHtml  = options.contentHtml || '';
            const styleHtml    = options.styleHtml || '';
            const advancedHtml    = options.advancedHtml || '';
            const message      = options.message || '';
            const elementTitle = elementData ? (elementData.label || elementData.type || '') : '';

            this.updateSelectionTitle(elementData ? elementTitle : '', elementData ? elementData.icon : '');

            const teardownColorPickers = ($scope) => {
                if (!$scope || !$scope.length || typeof $.fn.iris === 'undefined') return;

                $scope.find('.lsd-colorpicker').each(function () {
                    const $input = $(this);
                    const hasInstance = !!$input.data('wpWpColorPicker');
                    const hasIris = !!$input.data('a8cIris');

                    if (hasInstance && hasIris) {
                        try {
                            $input.wpColorPicker('close');
                        } catch (err) {
                            // ignore
                        }
                        try {
                            $input.iris('destroy');
                        } catch (err) {
                            // ignore
                        }
                    }

                    $input.removeClass('wp-color-picker');
                    $input.removeData('wpWpColorPicker');
                    $input.removeData('a8cIris');
                });

                $scope.find('.iris-picker, .iris-border, .wp-picker-holder, .wp-picker-container').remove();
            };

            const ensureOptionsWrapper = (html, elementKey) => {
                const $tmp = $('<div>').html(html || '');
                const hasWrapper = $tmp.find('.lsd-element-editor-options').length > 0
                    || $tmp.children('.lsd-element-editor-options').length > 0;

                if (hasWrapper) {
                    if (elementKey) {
                        $tmp.find('.lsd-element-editor-options').each(function () {
                            const $wrapper = $(this);
                            if (!$wrapper.data('lsd-element-key')) {
                                $wrapper.attr('data-lsd-element-key', elementKey);
                            }
                        });
                    }
                    return $tmp.html();
                }

                const $wrapper = $('<div>', {
                    class: 'lsd-element-editor-options',
                    'data-lsd-element-key': elementKey || ''
                });

                $wrapper.append($tmp.contents());
                return $('<div>').append($wrapper).html();
            };

            const buildPanel = ($target, html) => {
                if (!$target || !$target.length) return;

                const $settingsPanel = $target.find('.lsd-template-editor-settings-panel').first();
                const $placeholder   = $target.find('.lsd-template-editor-sidebar__placeholder').first();
                teardownColorPickers($target);
                teardownIconPickers($target);

                // Make sure we have a panel to work with
                if (!$settingsPanel.length) {
                    return;
                }

                // Ensure there is a body container for the options
                let $body = $settingsPanel.find('.lsd-template-editor-settings-panel__body');
                if (!$body.length) $body = $('<div>', { class: 'lsd-template-editor-settings-panel__body' }).appendTo($settingsPanel);

                const getDefaultText = () => {
                    if (!$placeholder.length) return '';

                    let defaultText = $placeholder.data('defaultText');
                    if (typeof defaultText !== 'string' || defaultText === '') {
                        defaultText = $placeholder.find('.lsd-admin-description').text();
                        $placeholder.data('defaultText', defaultText);
                    }

                    return defaultText;
                };

                const getEmptyText = () => {
                    const emptyText = $placeholder.data('emptyText');
                    if (typeof emptyText === 'string' && emptyText.trim() !== '') return emptyText;

                    return this.getString('no_settings', 'This element has no settings.');
                };

                const showPlaceholder = (text) => {
                    // mark as empty
                    $settingsPanel.addClass('lsd-template-editor-settings-panel--empty');

                    // clear body/options
                    $body.empty();
                    $settingsPanel.find('.lsd-element-editor-options').remove();

                    // show placeholder
                    if ($placeholder.length) {
                        $placeholder.removeClass('lsd-util-hide');

                        // only override text if an explicit message is given
                        if (typeof text !== 'undefined' && text !== null && text !== '') {
                            $placeholder
                            .find('.lsd-admin-description')
                            .text(text);
                        } else {
                            $placeholder
                            .find('.lsd-admin-description')
                            .text(getDefaultText());
                        }
                    }

                };

                const showOptions = (wrappedHtml) => {
                    // not empty anymore
                    $settingsPanel.removeClass('lsd-template-editor-settings-panel--empty');

                    // hide placeholder
                    if ($placeholder.length) {
                        $placeholder.addClass('lsd-util-hide');
                    }

                    // inject options
                    $body.html(wrappedHtml);
                    this.initFieldControls($body);
                    $(document).trigger('lsd-template-responsive-settings-refresh');

                    // After options are injected, update strings from data-* on .lsd-element-editor-options
                    const $optionsWrapper = $body.find('.lsd-element-editor-options').first();
                    if ($optionsWrapper.length) {
                        const prev = this.settingsStrings || {};
                        const fromDataOr = (dataKey, fallback) => {
                            const val = $optionsWrapper.data(dataKey);
                            if (typeof val === 'string' && val.trim() !== '') return val;
                            return fallback;
                        };

                        this.settingsStrings = {
                            select_placeholder: fromDataOr('lsdSelectPlaceholder', prev.select_placeholder || ''),
                            loading: fromDataOr('lsdLoadingText', prev.loading || ''),
                            settings_error: fromDataOr('lsdSettingsError', prev.settings_error || 'Unable to load settings for this element right now.'),
                            details_subtitle: fromDataOr('lsdDetailsSubtitle', prev.details_subtitle || '')
                        };

                        this.settingsErrorMessage = this.settingsStrings.settings_error;
                    }
                };

                if (!elementData) {
                    // keep original placeholder text from PHP
                    showPlaceholder();
                    return;
                }

                // Loading state ? show placeholder with loading text
                if (state === 'loading') {
                    showPlaceholder(message);
                    return;
                }

                // Error state ? show placeholder with error text
                if (state === 'error') {
                    showPlaceholder(message);
                    return;
                }

                // Normal state with element selected
                if (html) {
                    const wrappedHtml = ensureOptionsWrapper(html, elementData ? elementData.type : '');
                    showOptions(wrappedHtml);
                } else {
                    // No HTML for this element - show placeholder with empty-settings text
                    showPlaceholder(getEmptyText());
                }
            };

            // We now only rely on existing markup; no JS-generated placeholders.
            const closeFloatingColorPickers = () => {
                if (typeof $.fn.wpColorPicker === 'undefined') return;

                $('.lsd-template-editor-sidebar')
                    .find('.wp-picker-container.wp-picker-active .wp-color-picker')
                    .each(function () {
                        const $input = $(this);
                        try {
                            $input.wpColorPicker('close');
                        } catch (err) {
                            // ignore
                        }
                    });

                $('.wp-picker-holder.lsd-colorpicker-floating').remove();
            };

            closeFloatingColorPickers();
            buildPanel(this.$contentPanel, contentHtml);
            buildPanel(this.$stylePanel, styleHtml);
            buildPanel(this.$advancedPanel, advancedHtml);
        }

        getTemplateTypeValue() {
            if (this.$templateTypeInput && this.$templateTypeInput.length) {
                return this.$templateTypeInput.val();
            }

            const $radio = $('input[name="lsd_template_type"]:checked');
            return $radio.length ? $radio.val() : '';
        }

        getTemplateTypeLabel() {
            const $select = $('[data-lsd-template-setting="type"]').first();
            if ($select.length) {
                const value = ($select.val() || '').toString().trim();
                const label = $select.find('option:selected').text().trim();
                return label || value || '';
            }

            const $radio = $('input[name="lsd_template_type"]:checked');
            if ($radio.length) {
                const value = ($radio.val() || '').toString().trim();
                const label = $radio.closest('label').text().trim();
                return label || value || '';
            }

            return '';
        }

        // ------------------ Settings collection ------------------

        readFieldValue($field) {
            const type = ($field.attr('type') || '').toLowerCase();

            if (type === 'checkbox') {
                return $field.is(':checked') ? ($field.val() || '1') : '0';
            }

            if (type === 'radio') {
                if (!$field.is(':checked')) return null;
                return $field.val();
            }

            return $field.val();
        }

        parseSettingsPath(name, elementKey) {
            if (!name || !elementKey) return null;
            const escapedKey = elementKey.replace(/[-/\\^$*+?.()|\[\]{}]/g, '\\$&');
            const pattern = new RegExp('^lsd\\[(?:elements|template_elements)\\]\\[' + escapedKey + '\\]\\[(.+)\\]$');
            const match = name.match(pattern);
            if (!match || !match[1]) return null;
            return match[1].replace(/\]/g, '').split('[').filter((segment) => segment !== '');
        }

        collectElementSettings($scope, elementData) {
            const settings = {};
            if (!$scope || !$scope.length) return settings;

            const elementKey = $scope.data('lsd-element-key') || (elementData ? elementData.type : '');
            if (!elementKey) return settings;

            const shouldCollectResponsiveValue = ($field) => {
                const $deviceWrapper = $field.closest('[data-lsd-responsive-field][data-lsd-responsive]');
                if (!$deviceWrapper.length) return true;

                const device = ($deviceWrapper.data('lsd-responsive') || '').toString().trim();
                if (!device || device === 'desktop') return true;

                const isExplicit = !!$deviceWrapper.data('lsd-responsive-explicit');
                const isTouched = !!$deviceWrapper.data('lsd-responsive-touched');

                return isExplicit || isTouched;
            };

            $scope.find(':input[name^="lsd[elements]"]').each((index, field) => {
                const $field = $(field);
                if ($field.closest('[data-lsd-repeater-template]').length) return;
                const name = $field.attr('name');
                const path = this.parseSettingsPath(name, elementKey);

                if (!path || !path.length) return;
                if (!shouldCollectResponsiveValue($field)) return;

                const value = this.readFieldValue($field);
                if (value === null || typeof value === 'undefined') return;

                this.state.setNestedValue(settings, path, value);
            });

            $scope.find(':input[name^="lsd[template_elements]"]').each((index, field) => {
                const $field = $(field);
                if ($field.closest('[data-lsd-repeater-template]').length) return;
                const name = $field.attr('name');
                const path = this.parseSettingsPath(name, elementKey);

                if (!path || !path.length) return;
                if (!shouldCollectResponsiveValue($field)) return;

                const value = this.readFieldValue($field);
                if (value === null || typeof value === 'undefined') return;

                this.state.setNestedValue(settings, path, value);
            });

            return settings;
        }

        // ------------------ Preview ------------------

        applyPreviewFromState(elementId, reason = 'state-sync') {
            const state = this.state.getElement(elementId);
            if (!state) return;

            const rootSelector = state.type === 'container'
                ? this.containerRootSelector
                : this.elementRootSelector;

            const $element = this.findRenderedElement
                ? this.findRenderedElement(elementId, state.type)
                : $(rootSelector).filter('[data-lsd-element-id="' + elementId + '"]').first();
            if (!$element.length) return;

            if (window.LsdTemplatePreview && typeof window.LsdTemplatePreview.renderElementContent === 'function') {
                const $wrapper = $element.find('.lsd-template-editor-canvas__element-wrapper').first();
                const $target = $wrapper.length ? $wrapper : $element;
                const renderSettings = this.state.normalizeSettingsShape(state.settings);
                window.LsdTemplatePreview.renderElementContent($target, state.type, renderSettings, reason);
            }

            if (state.type === 'container') {
                const $container = $element.children(this.containerElementSelector).first();
                const $inner = $container.children(this.containerInnerSelector).first();
                if ($inner.length) this.toggleContainerPlaceholder($inner);

                $(document).trigger('lsd-template-responsive-refresh');
                return;
            }

            $(document).trigger('lsd-template-responsive-refresh');
        }

        // ------------------ Bind settings form ------------------

        bindForm(elementData) {
            if (!this.$contentPanel.length || !elementData) return;

            const elementState = this.state.ensureElement(elementData);
            const $panels = this.$contentPanel.add(this.$stylePanel).add(this.$advancedPanel);
            const $options = $panels.find('.lsd-element-editor-options');

            if (!elementState || !$options.length) return;
            this.suspendSyncSettings = true;

            const activateTab = ($switcher, tabKey) => {
                if (!$switcher || !$switcher.length || !tabKey) return;

                const contentSelector = $switcher.data('for') || '';
                const $tabs = $switcher.find('li');

                $tabs.removeClass('lsd-sub-tabs-active');
                $tabs.filter('[data-tab="' + tabKey + '"]').addClass('lsd-sub-tabs-active');

                if (!contentSelector) return;

                const $panelWrapper = $switcher.closest('.lsd-template-editor-settings-panel');
                const $contents = $panelWrapper.find(contentSelector);
                if (!$contents.length) return;

                $contents.removeClass('lsd-tab-switcher-content-active');
                $contents
                .filter('#lsd-tab-switcher-' + tabKey + '-content')
                .addClass('lsd-tab-switcher-content-active');
            };

            const bindTabs = () => {
                const $switchers = $panels.find('.lsd-tab-switcher[data-for]');
                if (!$switchers.length) return;

                $switchers.off('.lsdEditorTabs').on('click.lsdEditorTabs', 'li', (event) => {
                    event.preventDefault();
                    const $tab = $(event.currentTarget);
                    const tabKey = $tab.data('tab');
                    const $switcher = $tab.closest('.lsd-tab-switcher');
                    activateTab($switcher, tabKey);
                });
            };

            const getScopeForOptions = ($scope) => {
                const $container = $scope.closest('[data-lsd-settings-scope]');
                const scope = ($container.data('lsd-settings-scope') || '').toString().trim();
                return scope || 'content';
            };

            const applyToggleTriggers = () => {
                $options.each(function () {
                    const $scope = $(this);
                    const $triggerSelects = $scope.find('.lsd-trigger-select-options');
                    if (!$triggerSelects.length) return;

                    $triggerSelects.each(function () {
                        const $select = $(this);
                        const $selected = $select.find('option:selected');
                        const showTargets = ($selected.data('lsd-show') || '').toString().split(',');
                        const hideTargets = ($selected.data('lsd-hide') || '').toString().split(',');

                        hideTargets.forEach((selector) => {
                            const trimmed = selector.trim();
                            if (!trimmed) return;
                            $scope.find(trimmed).addClass('lsd-util-hide');
                        });

                        showTargets.forEach((selector) => {
                            const trimmed = selector.trim();
                            if (!trimmed) return;
                            $scope.find(trimmed).removeClass('lsd-util-hide');
                        });
                    });
                });
            };

            const markResponsiveTouched = ($field) => {
                const $deviceWrapper = $field.closest('[data-lsd-responsive-field][data-lsd-responsive]');
                if (!$deviceWrapper.length) return;

                const device = ($deviceWrapper.data('lsd-responsive') || '').toString().trim();
                if (!device || device === 'desktop') return;

                $deviceWrapper.attr('data-lsd-responsive-touched', '1');
            };

            const syncSettings = (reason = 'input') => {
                debugTemplateBuilder('syncSettings', reason, {
                    elementId: elementData.id,
                    elementType: elementData.type
                });

                applyToggleTriggers();
                const settings = { content: {}, style: {}, advanced: {} };

                $options.each((i, el) => {
                    const $scope = $(el);
                    const scopeKey = getScopeForOptions($scope);
                    const partial = this.collectElementSettings($scope, elementData);

                    if (!settings[scopeKey] || typeof settings[scopeKey] !== 'object') {
                        settings[scopeKey] = {};
                    }

                    this.state.deepMerge(settings[scopeKey], partial);
                });

                const nextSettings = this.state.normalizeSettingsShape(settings);
                const previousSettings = this.state.normalizeSettingsShape(elementState.settings || {});
                const previousSerialized = JSON.stringify(previousSettings);
                const nextSerialized = JSON.stringify(nextSettings);

                if (previousSerialized === nextSerialized) {
                    return;
                }

                this.state.updateElementSettings(elementData.id, nextSettings);
                this.applyPreviewFromState(elementData.id, 'settings-change');

                this.state.markLayoutDirty();
                this.state.writeToField();
                const payload = {
                    elementId: elementData.id,
                    reason: 'settings-change'
                };
                debugTemplateBuilder('lsd-template-state-updated:trigger', 'settings-change', payload);
                $('.lsd-template-editor-workspace').trigger('lsd-template-state-updated', [payload]);
                if (typeof this.onStateChange === 'function') {
                    this.onStateChange();
                }
            };

            bindTabs();
            applyToggleTriggers();
            initRepeaters($options);
            initIconPickers($options);

            $options
            .off('.lsdEditorSettings')
            .on('change.lsdEditorSettings input.lsdEditorSettings', ':input', (event) => {
                if (this.suspendSyncSettings) return;
                markResponsiveTouched($(event.currentTarget));
                syncSettings(event.type);
            });

            this.suspendSyncSettings = true;
            window.setTimeout(() => {
                this.suspendSyncSettings = false;
            }, 50);
        }

        // ------------------ Load settings via AJAX ------------------

        loadSettings(elementData) {
            if (!elementData || !elementData.type) {
                this.render(null);
                return;
            }

            const elementState = this.state.ensureElement(elementData);
            const settingsPayload = (elementState && elementState.settings) ? elementState.settings : {};

            if (this.settingsRequest && typeof this.settingsRequest.abort === 'function') {
                this.settingsRequest.abort();
                this.settingsRequest = null;
            }

            const ajaxUrl = AjaxHelpers.getAjaxUrl();

            const $nonceField = $('input[name="lsd-template-element-settings"]').first();
            const nonce = AjaxHelpers.getNonce($nonceField);

            this.render(elementData, {
                state: 'loading',
                message: this.getString('loading', 'Loading settings...')
            });

            if (!ajaxUrl || !nonce) {
                this.render(elementData, {
                    state: 'error',
                    message: this.settingsErrorMessage
                });
                return;
            }

            this.settingsRequest = $.ajax({
                url: ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'lsd_get_element_settings',
                    element_type: elementData.type,
                    element_id: elementData.id,
                    settings: JSON.stringify(settingsPayload),
                    _wpnonce: nonce
                }
            })
            .done((response) => {
                const currentId = this.$selectedElement.length ? this.$selectedElement.data('lsd-element-id') : '';
                if (!currentId || currentId !== elementData.id) return;

                if (response && response.success && typeof response.content === 'string') {
                    const contentHtml = typeof response.content === 'string' ? response.content : '';
                    const styleHtml = typeof response.style === 'string' ? response.style : '';
                    const advancedHtml = typeof response.advanced === 'string' ? response.advanced : '';
                    this.render(elementData, { contentHtml, styleHtml, advancedHtml });
                    this.bindForm(elementData);
                } else {
                    const message = (response && response.message) ? response.message : this.settingsErrorMessage;
                    this.render(elementData, { state: 'error', message });
                }
            })
            .fail(() => {
                const currentId = this.$selectedElement.length ? this.$selectedElement.data('lsd-element-id') : '';
                if (!currentId || currentId !== elementData.id) return;
                this.render(elementData, {
                    state: 'error',
                    message: this.settingsErrorMessage
                });
            })
            .always(() => {
                this.settingsRequest = null;
            });
        }

        // ------------------ Selection API ------------------

        clearSelection() {
            if (this.settingsRequest && typeof this.settingsRequest.abort === 'function') {
                this.settingsRequest.abort();
                this.settingsRequest = null;
            }

            if (this.$selectedElement.length) {
                // Remove visual selection from inner visual nodes
                this.$selectedElement
                .find('.lsd-template-editor-canvas__container, .lsd-template-editor-canvas__element-wrapper')
                .removeClass('is-selected');

                // Fallback: also remove from root if it had the class
                this.$selectedElement.removeClass('is-selected');

                this.$selectedElement = $();
            }

            if (this.state && typeof this.state.setCurrentElementId === 'function') {
                this.state.setCurrentElementId(null);
            }
            this.render(null);
            const isSettingsMode = SidebarTabManager.getWorkspace().hasClass('lsd-template-editor--settings-mode');
            SidebarTabManager.applyVisibility(false, isSettingsMode);
            SidebarTabManager.activate('elements');
        }


        selectElement($element) {
            if (!$element || !$element.length) {
                this.clearSelection();
                return;
            }

            // Always resolve to the root container/element node for state handling
            const $root = $element.closest(this.containerRootSelector + ', ' + this.elementRootSelector);
            if ($root.length) {
                $element = $root;
            }

            // If the same element is already selected, do nothing
            if (this.$selectedElement.length && this.$selectedElement.is($element)) {
                return;
            }

            const elementType  = $element.data('lsd-element-type') || '';
            const elementId    = $element.data('lsd-element-id') || '';
            const stateEntry   = this.state.getElement(elementId);
            const elementLabel = (stateEntry && stateEntry.label)
                ? stateEntry.label
                : (elementType || '');
            const elementIcon = (stateEntry && stateEntry.icon)
                ? stateEntry.icon
                : ($element.data('lsd-element-icon') || '');

            const elementData = { type: elementType, id: elementId, label: elementLabel, icon: elementIcon };
            this.state.ensureElement(elementData);

            // Clear previous visual selection
            if (this.$selectedElement.length) {
                this.$selectedElement
                .find('.lsd-template-editor-canvas__container, .lsd-template-editor-canvas__element-wrapper')
                .removeClass('is-selected');
                this.$selectedElement.removeClass('is-selected');
            }

            // Store root node in state (so data-lsd-element-id is still readable)
            this.$selectedElement = $element;
            this.state.setCurrentElementId(elementData.id || null);

            if (!this.isElementTypeAvailable(elementType)) {
                this.showTemplateTypeMismatchToast(elementLabel);
            }

            const $workspace = SidebarTabManager.getWorkspace();
            if ($workspace.hasClass('lsd-template-editor--sidebar-collapsed')) {
                const $toggle = $('[data-lsd-template-action="sidebar-toggle"]');
                if ($toggle.length) $toggle.trigger('click');
            }

            const isSettingsMode = $workspace.hasClass('lsd-template-editor--settings-mode');
            SidebarTabManager.applyVisibility(true, isSettingsMode);
            const activeTab = SidebarTabManager.getActiveTabKey();
            if (!activeTab || activeTab === 'elements' || activeTab === 'general' || activeTab === 'layout') {
                SidebarTabManager.activate('content');
                if (!this.$contentPanel.hasClass('lsd-tab-switcher-content-active')) {
                    this.$contentPanel
                        .closest('.lsd-template-editor-sidebar')
                        .find('.lsd-template-editor-sidebar-panel')
                        .removeClass('lsd-tab-switcher-content-active');
                    this.$contentPanel.addClass('lsd-tab-switcher-content-active');
                }
            }

            // Apply visual "is-selected" to the correct inner element
            if (elementType === 'container') {
                // Container: highlight the inner .lsd-template-editor-canvas__container
                const $visualTarget = $element
                .children('.lsd-template-editor-canvas__container')
                .first();

                if ($visualTarget.length) $visualTarget.addClass('is-selected');
                else $element.addClass('is-selected');

                $element.addClass('is-selected');
            }
            else
            {
                // Non-container element: highlight .lsd-template-editor-canvas__element-wrapper
                const $visualTarget = $element
                .find('.lsd-template-editor-canvas__element-wrapper')
                .first();

                if ($visualTarget.length) $visualTarget.addClass('is-selected');
                else $element.addClass('is-selected');

                $element.addClass('is-selected');
            }

            // Load settings for this element as before
            this.loadSettings(elementData);
        }

        isElementTypeAvailable(elementType) {
            if (!elementType || elementType === 'container') return true;

            const $list = $('[data-lsd-template-elements-list]').first();
            if (!$list.length) return true;

            return $list.find('.lsd-template-editor-element-list__button[data-lsd-element-type="' + elementType + '"]').length > 0;
        }

        showTemplateTypeMismatchToast(elementLabel = '') {
            const $workspace = SidebarTabManager.getWorkspace();
            const templateType = (this.getTemplateTypeLabel() || '').toString().trim();
            let message = ($workspace.data('lsdTemplateTypeMismatch') || '').toString().trim();
            const safeElementLabel = (elementLabel || '').toString().trim();


            if (message.indexOf('{template_type}') !== -1) message = message.replace('{template_type}', templateType || 'unknown');
            else if (templateType) message = `${message} (${templateType})`;

            if (message.indexOf('{element}') !== -1) message = message.replace('{element}', safeElementLabel || 'element');

            const options = {
                position: 'lsd-bottom-right',
                hideTime: 4000,
                showClose: true,
                progress: true
            };

            if (typeof window.listdom_toastify === 'function')
            {
                window.listdom_toastify(message, 'lsd-warning', options);
                return;
            }

            if (typeof window.ListdomToast === 'function') new window.ListdomToast(message, { type: 'lsd-warning' });
        }
    }

    /**
     * ------------------------------------------------------------------------
     * StructureMenu – renders the layout tree in the sidebar (collapsible)
     * ------------------------------------------------------------------------
     */
    class StructureMenu {
        constructor(state, options = {}) {
            this.state = state;
            this.$root = (options && options.$root) ? options.$root : $();
            this.$tree = this.$root.find('[data-lsd-structure-tree]');
            this.$placeholder = this.$root.find('[data-lsd-structure-placeholder]');
            this.onSelect = (options && typeof options.onSelect === 'function')
                ? options.onSelect
                : () => {};
            this.onReorder = (options && typeof options.onReorder === 'function')
                ? options.onReorder
                : () => {};

            // All nodes collapsed by default
            this.collapsed = new Set();
            this.didApplyInitialCollapse = false;
            this.layout = [];
            this.activeElementId = null;
            this.sortSelector = '.lsd-template-editor-structure__list, .lsd-template-editor-structure__children';
            this.$activeEmptyDropTarget = $();
        }

        // Collect all node IDs in the current layout (for “collapse all”)
        collectAllIds(nodes, acc = []) {
            (nodes || []).forEach((node) => {
                if (!node || !node.id) return;
                acc.push(node.id);
                if (Array.isArray(node.children) && node.children.length) {
                    this.collectAllIds(node.children, acc);
                }
            });
            return acc;
        }

        // Find path of IDs from root to target elementId
        findPath(nodes, targetId, path = []) {
            for (let i = 0; i < (nodes || []).length; i += 1) {
                const node = nodes[i];
                if (!node || !node.id) continue;

                const nextPath = path.concat(node.id);
                if (node.id === targetId) {
                    return nextPath;
                }

                if (Array.isArray(node.children) && node.children.length) {
                    const found = this.findPath(node.children, targetId, nextPath);
                    if (found) return found;
                }
            }
            return null;
        }

        buildList(nodes) {
            const $list = $('<ul>', { class: 'lsd-template-editor-structure__list' });

            (nodes || []).forEach((node) => {
                if (!node || !node.id) return;

                const elementState = this.state ? (this.state.getElement(node.id) || {}) : {};
                const type = node.type || elementState.type || '';
                const label = elementState.label || node.label || type || '';
                const iconClass = (elementState.icon || node.icon || '').toString().trim();

                const hasChildren = Array.isArray(node.children) && node.children.length > 0;
                const isContainer = type === 'container';
                const isCollapsed = hasChildren && this.collapsed.has(node.id);

                const $item = $('<li>', {
                    class: [
                        'lsd-template-editor-structure__item',
                        (isContainer ? 'lsd-template-editor-structure__item--container' : ''),
                        (isCollapsed ? 'lsd-template-editor-structure__item--collapsed' : '')
                    ].filter(Boolean).join(' '),
                    'data-lsd-element-id': node.id,
                    'data-lsd-element-type': type,
                    'data-lsd-element-icon': iconClass
                });

                // Main button
                const $button = $('<button>', {
                    type: 'button',
                    class: 'lsd-template-editor-structure__button'
                });

                // Wrapper around icon + label
                const $buttonInner = $('<span>', {
                    class: 'lsd-template-editor-structure__button-inner'
                });

                const $buttonMeta = $('<span>', {
                    class: 'lsd-template-editor-structure__meta'
                });

                if (iconClass) {
                    const $icon = $('<span>', {
                        class: 'lsd-structure-icon',
                        'aria-hidden': 'true'
                    });

                    $('<i>', {
                        class: iconClass,
                        'aria-hidden': 'true'
                    }).appendTo($icon);

                    $buttonMeta.append($icon);
                }

                const $labelSpan = $('<span>', {
                    class: 'lsd-structure-label',
                    text: label
                });

                $buttonMeta.append($labelSpan);
                $buttonInner.append($buttonMeta);

                if (isContainer && hasChildren) {
                    const $collapseIcon = $('<span>', {
                        class: 'lsd-structure-collapse-icon',
                        'aria-hidden': 'true'
                    });

                    $('<i>', {
                        class: 'fa fa-chevron-up',
                        'aria-hidden': 'true'
                    }).appendTo($collapseIcon);

                    $buttonInner.append($collapseIcon);
                }

                $button.append($buttonInner);
                $item.append($button);

                // Containers always get a child list so empty containers remain valid drop targets.
                let $children = $();
                if (isContainer) {
                    $children = hasChildren
                        ? this.buildList(node.children)
                        : $('<ul>', { class: 'lsd-template-editor-structure__list' });

                    $children
                        .addClass('lsd-template-editor-structure__children')
                        .toggleClass('lsd-template-editor-structure__children--empty', !hasChildren);

                    if (isCollapsed) $children.hide();

                    $item.append($children);
                }

                // Click logic
                $button.on('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    const $target = $(event.target);

                    // Toggle collapse
                    if (isContainer && hasChildren && $target.closest('.lsd-structure-collapse-icon').length) {
                        const currentlyCollapsed = $item.hasClass(
                            'lsd-template-editor-structure__item--collapsed'
                        );

                        if (currentlyCollapsed) {
                            $item.removeClass('lsd-template-editor-structure__item--collapsed');
                            this.collapsed.delete(node.id);
                            $children.stop(true, true).slideDown(150);
                        } else {
                            $item.addClass('lsd-template-editor-structure__item--collapsed');
                            this.collapsed.add(node.id);
                            $children.stop(true, true).slideUp(150);
                        }

                        return;
                    }

                    this.onSelect(node.id);
                });

                $list.append($item);
            });

            return $list;
        }

        buildLayoutFromList($list) {
            const layout = [];
            if (!$list || !$list.length) return layout;

            $list.children('li').each((index, item) => {
                const $item = $(item);
                const elementId = $item.data('lsd-element-id');

                if (!elementId) return;

                const state = this.state ? (this.state.getElement(elementId) || {}) : {};
                const type = $item.data('lsd-element-type') || state.type || '';

                const $childrenList = $item.children('.lsd-template-editor-structure__children').first();
                const children = $childrenList.length ? this.buildLayoutFromList($childrenList) : [];

                layout.push({
                    id: elementId,
                    type,
                    children
                });
            });

            return layout;
        }

        buildSortHelper($item) {
            if (!$item || !$item.length) return $('<div></div>');

            const $button = $item.children('.lsd-template-editor-structure__button').first();
            const buttonWidth = Math.ceil($button.outerWidth() || $item.outerWidth() || 0);
            const buttonHeight = Math.ceil($button.outerHeight() || $item.outerHeight() || 42);

            return $('<div class="lsd-template-editor-structure__sort-helper"></div>')
                .append($button.clone())
                .css({
                    width: buttonWidth ? buttonWidth + 'px' : '',
                    minHeight: buttonHeight ? buttonHeight + 'px' : '42px',
                    margin: 0,
                    pointerEvents: 'none',
                    zIndex: 1105
                });
        }

        resolveSortHelperPointerOffset($helper, fallback = { left: 24, top: 18 }) {
            const helperWidth = Math.ceil($helper && $helper.length ? ($helper.outerWidth() || 0) : 0);
            const helperHeight = Math.ceil($helper && $helper.length ? ($helper.outerHeight() || 0) : 0);
            const clamp = (value, size, fallbackValue) => {
                const nextValue = typeof value === 'number' ? value : fallbackValue;
                if (!size) return Math.max(0, nextValue);

                const inset = Math.min(12, Math.floor(size / 2));
                const max = Math.max(size - inset, inset);

                return Math.max(inset, Math.min(nextValue, max));
            };

            return {
                left: clamp(fallback.left, helperWidth, fallback.left),
                top: clamp(fallback.top, helperHeight, fallback.top)
            };
        }

        resolveSortCursorAt(event, $source, $helper) {
            return this.resolveSortHelperPointerOffset($helper, { left: 24, top: 18 });
        }

        applySortCursorAt($list, event, $source, $helper) {
            const cursorAt = this.resolveSortCursorAt(event, $source, $helper);

            if ($list && $list.length && typeof $list.sortable === 'function') {
                const instance = $list.sortable('instance');
                if (instance) {
                    instance.options.cursorAt = cursorAt;

                    if (instance.helper && instance.helper.length) {
                        if (typeof instance._cacheMargins === 'function') {
                            instance._cacheMargins();
                        }

                        if (typeof instance._cacheHelperProportions === 'function') {
                            instance._cacheHelperProportions();
                        }

                        if (typeof instance._adjustOffsetFromHelper === 'function') {
                            instance._adjustOffsetFromHelper(cursorAt);
                        }
                    }
                }
            }

            return cursorAt;
        }

        syncSortPlaceholderHeight($placeholder, height) {
            if (!$placeholder || !$placeholder.length) return;

            const nextHeight = Math.max(Math.ceil(height || 0), 0);
            if (!nextHeight) return;

            const currentHeight = Number($placeholder.data('lsd-placeholder-height') || 0);
            if (currentHeight === nextHeight) return;

            $placeholder
                .height(nextHeight)
                .data('lsd-placeholder-height', nextHeight);
        }

        clearEmptyDropTarget() {
            if (this.$activeEmptyDropTarget && this.$activeEmptyDropTarget.length) {
                this.$activeEmptyDropTarget.removeClass('is-drop-target');
                this.$activeEmptyDropTarget = $();
            }
        }

        refreshSortablePositions() {
            if (!this.$tree || !this.$tree.length) return;

            this.$tree.find(this.sortSelector).each((index, list) => {
                const $list = $(list);
                if (typeof $list.sortable !== 'function') return;

                const instance = $list.sortable('instance');
                if (instance && typeof instance.refreshPositions === 'function') {
                    instance.refreshPositions();
                }
            });
        }

        refreshEmptyChildrenState() {
            if (!this.$tree || !this.$tree.length) return;

            this.$tree.find('.lsd-template-editor-structure__children').each((index, list) => {
                const $list = $(list);
                const hasChildren = $list.children('li').length > 0;

                $list
                    .toggleClass('lsd-template-editor-structure__children--empty', !hasChildren)
                    .toggleClass('is-drop-target', !hasChildren && $list.is(this.$activeEmptyDropTarget));
            });
        }

        resolveEmptyDropTarget(point, $item) {
            if (!point || !this.$tree || !this.$tree.length) return $();

            const $lists = this.$tree.find('.lsd-template-editor-structure__children--empty:visible');
            if (!$lists.length) return $();

            let $target = $();
            $lists.each((index, list) => {
                const $list = $(list);
                if ($item && $item.length && $list.closest($item).length) return;

                const rect = list.getBoundingClientRect();
                if (!rect || !rect.width || !rect.height) return;

                const insideX = point.clientX >= rect.left && point.clientX <= rect.right;
                const insideY = point.clientY >= rect.top && point.clientY <= rect.bottom;
                if (!insideX || !insideY) return;

                $target = $list;
                return false;
            });

            return $target;
        }

        updateEmptyDropTarget(event, ui) {
            if (!ui || !ui.placeholder) {
                this.clearEmptyDropTarget();
                return;
            }

            const sourceEvent = event && event.originalEvent ? event.originalEvent : event;
            const point = sourceEvent && typeof sourceEvent.clientX === 'number' && typeof sourceEvent.clientY === 'number'
                ? {
                    clientX: sourceEvent.clientX,
                    clientY: sourceEvent.clientY
                }
                : null;
            const $item = ui.item ? $(ui.item) : $();
            const $target = this.resolveEmptyDropTarget(point, $item);

            if (!$target.length) {
                this.clearEmptyDropTarget();
                return;
            }

            if (!this.$activeEmptyDropTarget.length || this.$activeEmptyDropTarget.get(0) !== $target.get(0)) {
                this.clearEmptyDropTarget();
                this.$activeEmptyDropTarget = $target;
                this.$activeEmptyDropTarget.addClass('is-drop-target');
            }

            if (ui.placeholder.parent().get(0) !== $target.get(0)) {
                $target.append(ui.placeholder);
                this.syncSortPlaceholderHeight(
                    ui.placeholder,
                    (ui.helper && $(ui.helper).outerHeight()) || ui.placeholder.outerHeight()
                );
            }
        }

        initSortable() {
            if (!this.$tree || !this.$tree.length) return;
            if (!$.fn || typeof $.fn.sortable !== 'function') return;

            const selector = this.sortSelector;
            const $lists = this.$tree.find(selector);

            if (!$lists.length) return;

            $lists.each((index, list) => {
                const $list = $(list);
                if ($list.data('lsdSortableInit')) {
                    try {
                        $list.sortable('destroy');
                    } catch (err) {
                        // noop
                    }
                }
            });

            $lists.sortable({
                items: '> li',
                connectWith: selector,
                handle: '.lsd-template-editor-structure__button-inner',
                cancel: '.lsd-structure-collapse-icon',
                dropOnEmpty: true,
                helper: (event, item) => {
                    const $item = $(item);
                    const $helper = this.buildSortHelper($item);
                    const $cursorSource = $item.children('.lsd-template-editor-structure__button').first();

                    this.applySortCursorAt($item.parent(), event, $cursorSource.length ? $cursorSource : $item, $helper);

                    return $helper;
                },
                appendTo: 'body',
                cursorAt: {
                    left: 24,
                    top: 18
                },
                placeholder: 'lsd-template-editor-structure__drop-indicator',
                tolerance: 'pointer',
                toleranceElement: '> .lsd-template-editor-structure__button',
                distance: 4,
                forcePlaceholderSize: true,
                start: (event, ui) => {
                    if (ui && ui.placeholder && ui.item) {
                        const placeholderHeight = ui.item.children('.lsd-template-editor-structure__button').first().outerHeight()
                            || ui.item.outerHeight();
                        this.syncSortPlaceholderHeight(ui.placeholder, placeholderHeight);
                    }

                    this.clearEmptyDropTarget();

                    if (ui && ui.helper) {
                        ui.helper.addClass('lsd-template-editor-structure__item--dragging');
                    }

                    if (ui && ui.item && ui.helper) {
                        const $item = $(ui.item);
                        const $cursorSource = $item.children('.lsd-template-editor-structure__button').first();
                        this.applySortCursorAt($item.parent(), event, $cursorSource.length ? $cursorSource : $item, $(ui.helper));
                    }

                    if (this.$root && this.$root.length) {
                        this.$root.addClass('lsd-template-editor-structure--sorting');
                    }
                },
                sort: (event, ui) => {
                    if (ui && ui.placeholder && ui.helper) {
                        this.syncSortPlaceholderHeight(ui.placeholder, ui.helper.outerHeight() || ui.placeholder.outerHeight());
                    }

                    if (ui && ui.item && ui.helper) {
                        const $item = $(ui.item);
                        const $cursorSource = $item.children('.lsd-template-editor-structure__button').first();
                        this.applySortCursorAt(
                            ui.placeholder.parent(),
                            event,
                            $cursorSource.length ? $cursorSource : $item,
                            $(ui.helper)
                        );
                    }

                    this.updateEmptyDropTarget(event, ui);
                },
                change: (event, ui) => {
                    this.refreshEmptyChildrenState();
                    this.updateEmptyDropTarget(event, ui);
                    this.refreshSortablePositions();
                },
                receive: (event) => {
                    const $list = $(event.target);
                    if ($list.hasClass('lsd-template-editor-structure__children--empty')) {
                        $list.removeClass('lsd-template-editor-structure__children--empty');
                    }
                    this.refreshEmptyChildrenState();
                    this.refreshSortablePositions();
                },
                remove: (event, ui) => {
                    this.refreshEmptyChildrenState();
                    this.updateEmptyDropTarget(event, ui);
                    this.refreshSortablePositions();
                },
                stop: (event, ui) => {
                    if (this.$root && this.$root.length) {
                        this.$root.removeClass('lsd-template-editor-structure--sorting');
                    }
                    this.clearEmptyDropTarget();
                    this.refreshEmptyChildrenState();
                    if (ui && ui.placeholder) {
                        ui.placeholder.removeData('lsd-placeholder-height');
                    }
                    this.commitSort();
                }
            });

            $lists.data('lsdSortableInit', true);
            this.refreshEmptyChildrenState();
        }

        commitSort() {
            if (typeof this.onReorder !== 'function') return;

            const $rootList = this.$tree.children('.lsd-template-editor-structure__list').first();
            if (!$rootList.length) return;

            const layout = this.buildLayoutFromList($rootList);
            this.onReorder(layout);
        }

        togglePlaceholder(isEmpty) {
            if (this.$placeholder && this.$placeholder.length) {
                this.$placeholder.toggleClass('lsd-util-hide', !isEmpty);
            }

            if (this.$tree && this.$tree.length) {
                this.$tree.toggleClass('lsd-util-hide', isEmpty);
            }
        }

        render(layout) {
            if (!this.$tree || !this.$tree.length) return;

            const nodes = Array.isArray(layout) ? layout : [];
            this.layout = nodes; // keep for path calculations
            this.$tree.empty();

            if (!nodes.length) {
                this.didApplyInitialCollapse = false;
                this.togglePlaceholder(true);
                return;
            }

            // If nothing is active yet, collapse everything by default
            if (!this.activeElementId && !this.didApplyInitialCollapse) {
                const allIds = this.collectAllIds(this.layout, []);
                this.collapsed = new Set(allIds);
                this.didApplyInitialCollapse = true;
            }

            this.$tree.append(this.buildList(nodes));
            this.initSortable();
            this.togglePlaceholder(false);

            // Re-apply active highlight if we have one
            if (this.activeElementId) {
                const $target = this.$tree
                .find('[data-lsd-element-id="' + this.activeElementId + '"] > .lsd-template-editor-structure__button')
                .first();

                if ($target.length) {
                    $target.addClass('is-active');
                }
            }
        }

        setActive(elementId) {
            if (!this.$tree || !this.$tree.length) {
                this.activeElementId = elementId || null;
                return;
            }

            // Remove old active class
            this.$tree.find('.lsd-template-editor-structure__button').removeClass('is-active');
            this.activeElementId = elementId || null;

            if (!elementId) return;

            // Keep the user's open/closed state intact, but ensure active ancestors are visible.
            const path = this.findPath(this.layout, elementId) || [elementId];
            const ancestors = path.slice(0, -1);
            let needsRender = false;

            ancestors.forEach((id) => {
                if (this.collapsed.has(id)) {
                    this.collapsed.delete(id);
                    needsRender = true;
                }
            });

            if (needsRender) {
                this.render(this.layout);
            }

            // Mark active button
            const $target = this.$tree
            .find('[data-lsd-element-id="' + elementId + '"] > .lsd-template-editor-structure__button')
            .first();

            if ($target.length) {
                $target.addClass('is-active');
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     * CanvasManager – drag & drop + rendering using TemplateState + SettingsPanel
     * ------------------------------------------------------------------------
     */
    class CanvasManager {
        constructor(dom, options = {}) {
            this.dom = dom || {};
            this.$canvasHost = dom.$workspaceCanvas;
            this.$canvas = $();
            this.$workspace = this.dom.$workspace || this.$canvasHost.closest('.lsd-template-editor-workspace');
            this.$layoutField = dom.$layoutField;
            this.$contentPanel = dom.$contentPanel;
            this.$stylePanel = dom.$stylePanel;
            this.$advancedPanel = dom.$advancedPanel;
            this.$postForm = dom.$postForm;
            this.$structurePanel = dom.$structurePanel;
            this.$body = dom.$body || $('body');
            this.$templateSourceRoot = dom.$canvasTemplateSource || $();
            this.$templatePlaceholder = $();

            this.draggableButtonSelector = options.draggableButtonSelector || '.lsd-template-editor-sidebar__content .lsd-template-editor-element-list__button';
            this.draggableItemSelector = options.draggableItemSelector || '.lsd-template-editor-sidebar__content .lsd-template-editor-element-list__item';
            this.draggableSelector = options.draggableSelector || (this.draggableItemSelector + ', ' + this.draggableButtonSelector);

            this.containerInnerSelector = options.containerInnerSelector || '.lsd-template-editor-canvas__container-inner';
            this.playgroundSelector = options.playgroundSelector || '[data-lsd-template-editor-playground]';
            this.containerSelector = '.lsd-template-editor-canvas__container-item';
            this.containerElementSelector = '.lsd-template-editor-canvas__container';
            this.elementBlockSelector = '.lsd-template-editor-canvas__element-block';
            this.elementWrapperSelector = '.lsd-template-editor-canvas__element-wrapper';
            this.sortableHandleSelector = `${this.elementWrapperSelector}.is-selected, ${this.containerElementSelector}.is-selected`;

            if (!this.$canvasHost.length || !this.$layoutField.length || !this.$templateSourceRoot.length) return;

            this.$playground = $();
            this.$canvasPlaceholder = $();
            this.$templatesRoot = this.$templateSourceRoot;
            this.$containerTemplate = this.$templatesRoot.find('[data-lsd-canvas-template="container"]').children().first();
            this.$elementTemplate = this.$templatesRoot.find('[data-lsd-canvas-template="element-wrapper"]').children().first();
            this.$placeholderTemplate = this.$templatesRoot.find('[data-lsd-canvas-template="placeholder"]').first();
            this.elementIconMap = this.buildElementIconMap();
            this.history = [];
            this.historyLimit = 50;
            this.isRestoring = false;
            this.historyPosition = -1;
            this.paletteBound = false;
            this.paletteClickBound = false;
            this.palettePointerBound = false;
            this.selectionBound = false;
            this.canvasBooted = false;
            this.suppressPaletteClick = false;
            this.lastSidebarDragPoint = null;
            this.$iframeDropBridge = $();
            this.$activePaletteDropIndicator = $();
            this.$paletteDropIndicator = $();
            this.paletteDragState = null;
            this.activePaletteDropLocation = null;
            this.pendingIframeDrop = null;
            this.dragAutoScrollTimer = null;
            this.dragAutoScrollPoint = null;
            this.activeSortableDrag = null;

            this.state = new TemplateState(this.$layoutField);
            if (this.dom) this.dom.templateState = this.state;

            this.settingsPanel = new SettingsPanel(this.$contentPanel, this.$stylePanel, this.$advancedPanel, this.state, {
                toggleContainerPlaceholder: ($inner) => this.toggleContainerPlaceholder($inner),
                containerRootSelector: this.containerSelector,
                elementRootSelector: this.elementBlockSelector,
                findRenderedElement: (elementId, elementType) => this.findRenderedElementById(elementId, elementType),
                $selectionWrapper: this.dom.$selectionWrapper,
                $selectionLabel: this.dom.$selectionLabel,
                $selectionIcon: this.dom.$selectionIcon,
                onStateChange: () => {
                    this.recordHistory();
                }
            });

            this.structureMenu = (this.$structurePanel && this.$structurePanel.length)
                ? new StructureMenu(this.state, {
                    $root: this.$structurePanel,
                    onSelect: (elementId) => this.focusElementById(elementId),
                    onReorder: (layout) => this.applyStructureLayout(layout)
                })
                : null;

            this.initPreviewHelper();
            this.initUndoKeybind();
            this.bindIframeSurface();

            if (this.$workspace && this.$workspace.length) {
                this.$workspace.on('lsd-template-clear-selection', () => {
                    this.clearSelection();
                });
            }

            Object.keys(this.state.state.elements || {}).forEach((id) => {
                this.settingsPanel.applyPreviewFromState(id, 'canvas-init');
            });

            this.autoSaveOnSubmit();
        }

        bindIframeSurface() {
            const $workspace = (this.dom.previewIframeManager && this.dom.previewIframeManager.$workspace && this.dom.previewIframeManager.$workspace.length)
                ? this.dom.previewIframeManager.$workspace
                : this.$workspace;

            if ($workspace && $workspace.length) {
                this.$workspace = $workspace;
                this.$workspace
                    .off('lsd-template-iframe-ready.lsdCanvasSurface')
                    .on('lsd-template-iframe-ready.lsdCanvasSurface', (event, iframeWindow) => {
                        if (window.lsd && window.lsd.debug) console.log('CanvasManager received iframe ready');
                        this.mountIntoIframe(iframeWindow);
                    });
            }

            if (this.dom.previewIframeManager && typeof this.dom.previewIframeManager.getIframeWindow === 'function') {
                const iframeWindow = this.dom.previewIframeManager.getIframeWindow();
                if (iframeWindow && iframeWindow.document && this.dom.previewIframeManager.iframeReady) {
                    this.mountIntoIframe(iframeWindow);
                }
            }
        }

        isIframeCanvasMounted() {
            return !!(
                this.$canvas &&
                this.$canvas.length &&
                this.$playground &&
                this.$playground.length &&
                this.$canvas.closest('#lsd-template-preview-root').length
            );
        }

        mountIntoIframe(iframeWindow) {
            if (!iframeWindow || !iframeWindow.document) return;
            if (window.lsd && window.lsd.debug) console.log('Mounting Listdom canvas into iframe');

            const selectedId = this.getSelectedElementId();
            const rootNode = iframeWindow.document.getElementById('lsd-template-preview-root')
                || (this.dom.previewIframeManager && typeof this.dom.previewIframeManager.getPreviewRoot === 'function'
                    ? this.dom.previewIframeManager.getPreviewRoot()
                    : null);
            const $root = $(rootNode);
            if (!$root.length) return;

            const responsiveMode = this.getResponsiveMode();
            const placeholderHtml = this.$placeholderTemplate.length ? this.$placeholderTemplate.prop('outerHTML') : '';
            const shell = [
                '<div class="lsd-template-editor-workspace__content lsd-template-editor-workspace__content--iframe">',
                '<div class="lsd-template-editor-workspace__inner lsd-template-editor-workspace__inner--iframe">',
                '<div class="lsd-template-editor-canvas" data-lsd-responsive="', responsiveMode, '">',
                '<div class="lsd-template-editor-canvas__playground" data-lsd-template-editor-playground>',
                placeholderHtml,
                '</div>',
                '</div>',
                '</div>',
                '</div>'
            ].join('');

            $root.html(shell);

            this.$canvas = $root.find('.lsd-template-editor-canvas').first();
            this.$playground = this.$canvas.find('.lsd-template-editor-canvas__playground').first();
            this.$canvasPlaceholder = this.$playground.find('.lsd-template-editor-canvas__placeholder').first();

            if (this.dom.responsivePreview && typeof this.dom.responsivePreview.applyCanvasFrameWidth === 'function') {
                this.dom.responsivePreview.applyCanvasFrameWidth(this.$canvas, responsiveMode);
            }

            this.canvasBooted = false;
            this.selectionBound = false;

            this.bootCanvasSurface();

            if (selectedId) {
                const $selected = this.findCanvasNodeById(selectedId);
                if ($selected.length) {
                    this.selectElement($selected);
                }
            }
        }

        bootCanvasSurface() {
            if (!this.$canvas.length || !this.$playground.length) return;

            this.initDragAndDrop();
            this.initSelection();
            this.renderFromState();
            this.recordHistory();
            this.canvasBooted = true;
        }

        getResponsiveMode() {
            const $active = this.dom.$responsiveControls.filter('.lsd-sub-tabs-active').first();
            const mode = ($active.data('lsdResponsive') || '').toString();
            return mode || 'desktop';
        }

        findRenderedElementById(elementId, elementType = '') {
            if (!elementId || !this.$canvas.length) return $();

            const selector = elementType === 'container'
                ? this.containerSelector
                : this.elementBlockSelector;

            return this.$canvas.find(selector + '[data-lsd-element-id="' + elementId + '"]').first();
        }

        // ------------------ Shared preview helper ------------------

        initPreviewHelper() {
            const templateId = (this.dom && this.dom.$postIdInput && this.dom.$postIdInput.length)
                ? (parseInt(this.dom.$postIdInput.val(), 10) || 0)
                : 0;

            const getPreviewListing = () => {
                const $workspace = (this.dom && this.dom.$workspace)
                    ? this.dom.$workspace
                    : this.$canvas.closest('.lsd-template-editor-workspace');

                const $inputs = ($workspace && $workspace.length)
                    ? $workspace.find('input[name="lsd_template_preview_listing[]"], input[name="lsd_template_preview_listing"], select[name="lsd_template_preview_listing"]')
                    : $();

                if (!$inputs.length) return 0;

                const value = parseInt($inputs.first().val(), 10);
                return Number.isNaN(value) ? 0 : value;
            };

            const previewQueue = {
                maxConcurrent: 3,
                pending: [],
                active: 0,
                inFlight: new Map(),
                enqueue(task) {
                    if (!task || !task.key || typeof task.run !== 'function') return;

                    this.pending = this.pending.filter((item) => item.key !== task.key);

                    const inflight = this.inFlight.get(task.key);
                    if (inflight && typeof inflight.abort === 'function') inflight.abort();

                    this.pending.push(task);
                    this.pump();
                },
                pump() {
                    while (this.active < this.maxConcurrent && this.pending.length) {
                        const task = this.pending.shift();
                        if (!task || typeof task.run !== 'function') continue;

                        this.active += 1;

                        const finalize = () => {
                            this.active = Math.max(0, this.active - 1);
                            this.inFlight.delete(task.key);
                            this.pump();
                        };

                        const xhr = task.run();
                        if (xhr && typeof xhr.always === 'function') {
                            this.inFlight.set(task.key, xhr);
                            xhr.always(finalize);
                        } else {
                            finalize();
                        }
                    }
                }
            };
            const setElementContent = ($element, content, elementType = '') => {
                const type = elementType || ($element.data('lsd-element-type') || '').toString();
                const isContainer = type === 'container';
                const hasContent = typeof content === 'string' ? content.trim() !== '' : !!content;

                if (isContainer) {
                    const $parsed = $('<div>').html(hasContent ? content : '');
                    const $parsedWrapper = $parsed.children().not('style').first();
                    const $parsedItem = $parsedWrapper.is('.lsd-template-editor-canvas__container-item')
                        ? $parsedWrapper
                        : $parsedWrapper.children('.lsd-template-editor-canvas__container-item').first();
                    const $parsedContainer = $parsedItem.children('.lsd-template-editor-canvas__container').first();
                    const $parsedInner = $parsedContainer.children('.lsd-template-editor-canvas__container-inner').first();

                    const $targetItem = $element.hasClass('lsd-template-editor-canvas__container-item')
                        ? $element
                        : $element.closest('.lsd-template-editor-canvas__container-item');
                    const $targetContainer = $targetItem.children('.lsd-template-editor-canvas__container').first();
                    const $targetInner = $targetContainer.children('.lsd-template-editor-canvas__container-inner').first();

                    if (!$parsedItem.length || !$parsedContainer.length || !$parsedInner.length || !$targetContainer.length || !$targetInner.length) return;

                    const findWrapperClass = ($scope) => {
                        if (!$scope || !$scope.length) return '';

                        const extractClass = ($node) => {
                            const classList = ($node.attr('class') || '').split(/\s+/).filter(Boolean);
                            return classList.find((cls) => cls.indexOf('lsd-template-builder-element-') === 0) || '';
                        };

                        const direct = extractClass($scope);
                        if (direct) return direct;

                        const $descendant = $scope.find('[class*="lsd-template-builder-element-"]').first();
                        return $descendant.length ? extractClass($descendant) : '';
                    };

                    const applyWrapperClass = ($target, wrapperClass) => {
                        if (!$target || !$target.length || !wrapperClass) return;

                        const existing = ($target.attr('class') || '').split(/\s+/).filter(Boolean);
                        const withoutWrapper = existing.filter((cls) => !cls.startsWith('lsd-template-builder-element-'));
                        withoutWrapper.push(wrapperClass);

                        const deduped = Array.from(new Set(withoutWrapper));
                        $target.attr('class', deduped.join(' '));
                    };

                    const syncStyles = ($target, $styles) => {
                        if (!$target || !$target.length) return;

                        $target.children('style[data-lsd-template-css]').remove();

                        if ($styles && $styles.length) {
                            $styles.each(function () {
                                $(this).clone(false).prependTo($target);
                            });
                        }
                    };

                    const mergeClasses = (incoming, existing, layoutPrefix = 'lsd-container-layout-') => {
                        const incomingClasses = (incoming.attr('class') || '').split(/\s+/).filter(Boolean);
                        const existingClasses = (existing.attr('class') || '').split(/\s+/).filter(Boolean);

                        const preserved = existingClasses.filter((cls) => !cls.startsWith(layoutPrefix));
                        const next = incomingClasses.concat(preserved.filter((cls) => !incomingClasses.includes(cls)));

                        return next.join(' ');
                    };
                    const clonePlaceholder = ($scope) => {
                        const $placeholder = $scope.children('.lsd-template-editor-canvas__container-placeholder').first();
                        return $placeholder.length ? $placeholder.clone(true, true) : $();
                    };

                    const $styles = $parsed.children('style');
                    const wrapperClass = findWrapperClass($parsedWrapper.length ? $parsedWrapper : $parsedContainer);

                    applyWrapperClass($targetItem, wrapperClass);
                    syncStyles($targetItem, $styles);

                    const $newInner = $parsedInner.clone(false);
                    const $placeholder = clonePlaceholder($targetInner);

                    // Preserve BOTH elements and nested containers
                    const childSelector = '.lsd-template-editor-canvas__element-block, .lsd-template-editor-canvas__container-item';
                    const $children = $targetInner.children(childSelector).detach();

                    const $parsedPlaceholder = $newInner.children('.lsd-template-editor-canvas__container-placeholder').first();
                    if ($parsedPlaceholder.length && $placeholder.length) $parsedPlaceholder.replaceWith($placeholder);
                    else if (!$parsedPlaceholder.length && $placeholder.length) $newInner.prepend($placeholder);

                    if ($children.length) $newInner.append($children);

                    $targetContainer.attr('class', mergeClasses($parsedContainer, $targetContainer));
                    $targetInner.replaceWith($newInner);

                    const $freshContainer = $targetItem.children('.lsd-template-editor-canvas__container').first();
                    const $freshInner = $freshContainer.children('.lsd-template-editor-canvas__container-inner').first();

                    $freshInner.attr('class', mergeClasses($parsedInner, $freshInner));

                    this.initContainerInnerDropzone($freshInner);
                    this.toggleContainerPlaceholder($freshInner);

                    return;
                }

                const $contentWrapper = $element.children('.lsd-template-editor-canvas__content').first();
                if (!$contentWrapper.length) return;

                if (hasContent) {
                    $contentWrapper.html(content);
                } else {
                    $contentWrapper.empty();
                }

                this.neutralizePreviewInteractivity($contentWrapper);
                $contentWrapper.toggleClass('lsd-template-editor-canvas__content--empty', !hasContent);
            };

            window.LsdTemplatePreview = {
                renderElementContent: ($element, elementType, elementSettings, reason = 'direct-call') =>
                {
                    debugTemplateBuilder('renderElementContent', reason, {
                        elementType,
                        elementId: ($element.data('lsd-element-id') || $element.closest('[data-lsd-element-id]').data('lsd-element-id') || '').toString()
                    });

                    if (!elementType) {
                        setElementContent($element, '', elementType);
                        return;
                    }

                    const ajaxUrl = (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
                    const $nonceField = (this.dom && this.dom.$workspace && this.dom.$workspace.length)
                        ? this.dom.$workspace.find('input[name="lsd-template-element"]').first()
                        : $('input[name="lsd-template-element"]').first();
                    const nonce = AjaxHelpers.getNonce($nonceField);

                    if (!ajaxUrl || !nonce) {
                        setElementContent($element, '', elementType);
                        return;
                    }

                    const elementId = ($element.data('lsd-element-id') || $element.closest('[data-lsd-element-id]').data('lsd-element-id') || '').toString();
                    const queueKey = elementId ? elementType + ':' + elementId : elementType;
                    const settingsPayload = JSON.stringify(elementSettings || {});
                    const isElementAlive = () => {
                        if (!$element || !$element.length) return false;

                        const node = $element.get(0);
                        const ownerDocument = node ? node.ownerDocument : null;

                        return !!(node && ownerDocument && $.contains(ownerDocument, node));
                    };

                    previewQueue.enqueue({
                        key: queueKey,
                        run: () => {
                            if (!isElementAlive()) return null;

                            return $.ajax({
                                url: ajaxUrl,
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'lsd_render_template_element',
                                    element_type: elementType,
                                    template_type: (this.settingsPanel && typeof this.settingsPanel.getTemplateTypeValue === 'function')
                                        ? this.settingsPanel.getTemplateTypeValue()
                                        : '',
                                    settings: settingsPayload,
                                    post_id: templateId,
                                    preview_listing: getPreviewListing(),
                                    _wpnonce: nonce
                                }
                            })
                            .done((response) => {
                                if (!isElementAlive()) return;
                                if (response && response.success && typeof response.content === 'string')
                                {
                                    setElementContent($element, response.content, elementType);
                                }
                                else
                                {
                                    setElementContent($element, '', elementType);
                                }
                            })
                            .fail(() => {
                                if (!isElementAlive()) return;
                                setElementContent($element, '', elementType);
                            });
                        }
                    });
                }
            };

            this.setElementContent = setElementContent;
            this.initPreviewRefreshTriggers();
        }

        neutralizePreviewInteractivity($scope) {
            if (!$scope || !$scope.length) return;

            const previewFormId = 'lsd-preview-form';

            $scope.find('form').each(function () {
                const $form = $(this);
                const $replacement = $('<div>');

                const className = $form.attr('class');
                const id = $form.attr('id');

                if (className) $replacement.attr('class', className);
                if (id) $replacement.attr('id', id);

                $replacement.attr('data-lsd-preview-form', 'true');
                $replacement.append($form.contents());
                $form.replaceWith($replacement);
            });

            $scope.find('input[type="hidden"][name="action"]').remove();

            $scope.find('input, select, textarea, button').each(function () {
                const $field = $(this);
                $field.attr('form', previewFormId);
                $field.removeAttr('required');

                if ($field.is('button[type="submit"]')) $field.attr('type', 'button');
                if ($field.is('input[type="submit"]')) $field.attr('type', 'button');
            });
        }

        refreshAllPreviews(reason = 'manual-refresh') {
            debugTemplateBuilder('refreshAllPreviews', reason);
            Object.keys(this.state.state.elements || {}).forEach((id) => {
                this.settingsPanel.applyPreviewFromState(id, reason);
            });
        }

        initPreviewRefreshTriggers() {
            const $workspace = (this.dom && this.dom.$workspace)
                ? this.dom.$workspace
                : this.$canvas.closest('.lsd-template-editor-workspace');

            if (!$workspace || !$workspace.length) return;

            $workspace
            .off('change.lsdPreviewListing', previewListingSelector)
            .on('change.lsdPreviewListing', previewListingSelector, () => {
                this.refreshAllPreviews('preview-listing-change');
            });

            $workspace
            .off('lsd-template-settings-saved.lsdPreviewListing')
            .on('lsd-template-settings-saved.lsdPreviewListing', () => {
                bindPreviewListingMutationObserver($workspace, 'canvas', () => {
                    this.refreshAllPreviews('preview-listing-mutation');
                });
                this.refreshAllPreviews('template-settings-saved');
            });

            bindPreviewListingMutationObserver($workspace, 'canvas', () => {
                this.refreshAllPreviews('preview-listing-mutation');
            });
        }

        // ------------------ Canvas DOM helpers ------------------

        ensureCanvasContainer() {
            if (this.$playground && this.$playground.length) return this.$playground;
            this.$playground = this.$canvas.find('.lsd-template-editor-canvas__playground').first();
            if (!this.$playground.length) {
                debugTemplateBuilder('ensureCanvasContainer', 'missing-playground');
            }
            return this.$playground.length ? this.$playground : $();
        }

        getGlobalPlaceholder() {
            const $placeholder = this.$canvasPlaceholder && this.$canvasPlaceholder.length
                ? this.$canvasPlaceholder
                : this.$canvas.find('.lsd-template-editor-canvas__placeholder');

            const $container = this.ensureCanvasContainer();
            if ($placeholder.length && $container.length) {
                $container.append($placeholder);
            }

            return $placeholder;
        }

        toggleGlobalPlaceholder() {
            const $placeholder = this.getGlobalPlaceholder();
            const $container = this.ensureCanvasContainer();

            if (!$placeholder.length || !$container.length) return;

            const rootSelector = [this.containerSelector, this.elementBlockSelector].join(', ');
            const hasRootItems = $container.children(rootSelector).length > 0;
            const hasSortablePlaceholder = $container.children('.ui-sortable-placeholder').length > 0;

            $placeholder.toggleClass('lsd-util-hide', hasRootItems || hasSortablePlaceholder);
        }

        toggleContainerPlaceholder($containerInner)
        {
            if (!$containerInner.length) return;

            const $placeholder = $containerInner.children('.lsd-template-editor-canvas__container-placeholder');
            const childSelector = [this.elementBlockSelector, this.containerSelector].join(', ');
            const hasElements = $containerInner.children(childSelector).length > 0;
            const hasSortablePlaceholder = $containerInner.children('.ui-sortable-placeholder').length > 0;

            if ($placeholder.length) $placeholder.toggleClass('lsd-util-hide', hasElements || hasSortablePlaceholder);
        }

        refreshCanvasSortablePositions() {
            const $sortables = this.ensureCanvasContainer()
                .find(this.containerInnerSelector)
                .add(this.$playground);

            $sortables.each((index, sortableEl) => {
                const $sortable = $(sortableEl);
                if (typeof $sortable.sortable !== 'function') return;

                const instance = $sortable.sortable('instance');
                if (instance && typeof instance.refreshPositions === 'function') {
                    instance.refreshPositions();
                }
            });
        }

        refreshAllPlaceholders() {
            const self = this;
            this.ensureCanvasContainer().find(this.containerInnerSelector).each(function () {
                self.toggleContainerPlaceholder($(this));
            });
            this.toggleGlobalPlaceholder();
        }

        afterCanvasMutation($selectedElement = $(), reason = 'canvas-mutation') {
            this.refreshAllPlaceholders();

            if ($selectedElement && $selectedElement.length) {
                this.selectElement($selectedElement);
            }

            this.state.markLayoutDirty();
            this.persist(true, reason);

            if (
                this.dom.previewIframeManager &&
                typeof this.dom.previewIframeManager.refreshIframeNiceScroll === 'function'
            ) {
                this.dom.previewIframeManager.refreshIframeNiceScroll();
            }
        }

        requestStructurePanelClose() {
            if (!this.$workspace || !this.$workspace.length) return;
            this.$workspace.trigger('lsd-template-structure-close');
        }

        cloneTemplate($template) {
            if (!$template || !$template.length) return $();
            return $template.clone(true, true);
        }

        // ------------------ State <-> DOM sync ------------------

        collectChildrenFromInner($inner) {
            const children = [];
            if (!$inner || !$inner.length) return children;

            const selectors = [this.elementBlockSelector, this.containerSelector].join(', ');

            $inner.children(selectors).each((j, childNode) => {
                const $block = $(childNode);
                const $child = $block.find(this.elementWrapperSelector).first();
                const childId = $block.data('lsd-element-id') || $child.data('lsd-element-id');

                if (!childId) return;

                const childType = $block.data('lsd-element-type') || $child.data('lsd-element-type') || '';
                const childLabel = $block.data('lsd-element-label') || $child.data('lsd-element-label') || '';
                const childIcon = $block.data('lsd-element-icon') || $child.data('lsd-element-icon') || '';

                this.state.ensureElement({
                    id: childId,
                    type: childType,
                    label: childLabel,
                    icon: childIcon
                });

                if (childType === 'container') {
                    const $childInner = $block.find(this.containerInnerSelector).first();
                    children.push({
                        id: childId,
                        type: 'container',
                        children: this.collectChildrenFromInner($childInner)
                    });
                } else {
                    children.push({
                        id: childId,
                        type: childType,
                        children: []
                    });
                }
            });

            return children;
        }

        refreshLayoutState() {
            const nextLayout = [];
            const $container = this.ensureCanvasContainer();

            if ($container.length) {
                this.removeInvalidRootElements();
                $container.children(this.containerSelector).each((i, el) => {
                    const $row = $(el);
                    const $rowElement = $row.find(this.containerElementSelector).first();
                    const rowId = $row.data('lsd-element-id');
                    const rowType = $row.data('lsd-element-type') || 'container';
                    const rowLabel = $row.data('lsd-element-label') || ($rowElement.data('lsd-element-label') || '');
                    const rowIcon = $row.data('lsd-element-icon') || '';

                    if (!rowId) return;

                    this.state.ensureElement({
                        id: rowId,
                        type: rowType,
                        label: rowLabel,
                        icon: rowIcon
                    });

                    const $inner = $row.find(this.containerInnerSelector).first();
                    const children = this.collectChildrenFromInner($inner);

                    nextLayout.push({
                        id: rowId,
                        type: 'container',
                        children
                    });
                });
            }

            this.state.state.layout = nextLayout;
            return this.state.serialize();
        }

        persist(syncLayout = false, reason = 'persist') {
            if (syncLayout) {
                this.refreshLayoutState();
            }
            this.state.writeToField();
            this.recordHistory();
            this.syncStructure();
            const payload = { reason };
            debugTemplateBuilder('lsd-template-state-updated:trigger', reason, payload);
            this.$workspace.trigger('lsd-template-state-updated', [payload]);
        }

        getSnapshotString() {
            return JSON.stringify(this.state.serialize());
        }

        recordHistory() {
            if (this.isRestoring) return;

            const snapshot = this.getSnapshotString();
            const last = this.history.length ? this.history[this.history.length - 1] : '';

            if (snapshot === last) return;

            if (this.historyPosition >= 0 && this.historyPosition < this.history.length - 1) {
                this.history = this.history.slice(0, this.historyPosition + 1);
            }

            this.history.push(snapshot);
            if (this.history.length > this.historyLimit) {
                this.history.shift();
            }
            this.historyPosition = this.history.length - 1;
        }

        restoreSnapshot(snapshot) {
            if (!snapshot) return;

            let data = null;
            try {
                data = JSON.parse(snapshot);
            } catch (err) {
                return;
            }

            if (!data || typeof data !== 'object') return;

            this.isRestoring = true;

            this.state.state.elements = data.elements && typeof data.elements === 'object' ? data.elements : {};
            this.state.state.layout = Array.isArray(data.layout) ? data.layout : [];

            this.state.writeToField();
            this.renderFromState();
            if (this.settingsPanel && typeof this.settingsPanel.clearSelection === 'function') {
                this.settingsPanel.clearSelection();
            }

            this.isRestoring = false;
        }

        undo() {
            if (this.history.length < 2) return;
            if (this.historyPosition <= 0) return;

            this.historyPosition -= 1;
            const previous = this.history[this.historyPosition];
            this.restoreSnapshot(previous);
            this.showHistoryToast('Undo');
        }

        redo() {
            if (this.history.length < 2) return;
            if (this.historyPosition >= this.history.length - 1) return;

            this.historyPosition += 1;
            const next = this.history[this.historyPosition];
            this.restoreSnapshot(next);
            this.showHistoryToast('Redo');
        }

        showHistoryToast(message) {
            if (typeof window.listdom_toastify !== 'function') return;
            listdom_toastify(message, 'lsd-success', {
                position: 'lsd-bottom-right',
                hideTime: 2000,
                showClose: false,
                progress: true
            });
        }

        initUndoKeybind() {
            $(document).on('keydown.lsdTemplateUndo', (event) => {
                const key = (event.key || '').toLowerCase();
                if (key !== 'z') return;
                if (!event.ctrlKey && !event.metaKey) return;

                const $target = $(event.target);
                const isTyping = $target.is('input, textarea, select') || $target.closest('[contenteditable="true"]').length > 0;
                if (isTyping) return;

                event.preventDefault();
                if (event.shiftKey) {
                    this.redo();
                } else {
                    this.undo();
                }
            });
        }

        animateRemoval($target, callback) {
            if (!$target || !$target.length) {
                if (typeof callback === 'function') callback();
                return;
            }

            let finished = false;
            const finalize = () => {
                if (finished) return;
                finished = true;
                $target.off('.lsdRemoval');
                if (typeof callback === 'function') callback();
            };

            $target.addClass('is-removing');

            $target.one('animationend.lsdRemoval animationcancel.lsdRemoval transitionend.lsdRemoval', finalize);

            window.setTimeout(finalize, 300);
        }

        setDraggingState(isDragging) {
            if (this.$body && this.$body.length) {
                this.$body.toggleClass('lsd-template-editor--dragging', !!isDragging);
            }
        }

        setSidebarDraggingState(isDragging) {
            if (this.$body && this.$body.length) {
                this.$body.toggleClass('lsd-template-editor--sidebar-dragging', !!isDragging);
            }
            if (!isDragging) {
                this.refreshAllPlaceholders();
            }
        }

        setDragAutoScrollPoint(point) {
            if (!point || typeof point.clientX !== 'number' || typeof point.clientY !== 'number') return;

            this.dragAutoScrollPoint = {
                clientX: point.clientX,
                clientY: point.clientY
            };
        }

        startDragAutoScrollLoop() {
            if (this.dragAutoScrollTimer) return;

            this.dragAutoScrollTimer = window.setInterval(() => {
                if (!this.dragAutoScrollPoint) return;

                const iframeScrolled = this.autoScrollIframeOnDrag(this.dragAutoScrollPoint);
                const canvasScrolled = this.autoScrollCanvasOnDrag(this.dragAutoScrollPoint);

                if (this.paletteDragState && this.paletteDragState.dragging) {
                    this.updatePaletteDragIndicator(this.dragAutoScrollPoint);
                }

                if (iframeScrolled || canvasScrolled) {
                    this.syncActiveSortableDragOnAutoScroll(this.dragAutoScrollPoint);
                }
            }, 24);
        }

        stopDragAutoScrollLoop() {
            if (this.dragAutoScrollTimer) {
                window.clearInterval(this.dragAutoScrollTimer);
                this.dragAutoScrollTimer = null;
            }

            this.dragAutoScrollPoint = null;
        }

        showCanvasDropIndicator($target) {
            if (!$target || !$target.length) return;

            $target
                .removeClass('lsd-util-hide')
                .addClass('lsd-template-editor-canvas__drop-indicator');
        }

        isPointInsidePreviewIframe(point) {
            const iframeEl = this.dom.previewIframeManager && this.dom.previewIframeManager.$iframe
                ? this.dom.previewIframeManager.$iframe.get(0)
                : null;

            if (!iframeEl || !point || typeof iframeEl.getBoundingClientRect !== 'function') {
                return false;
            }

            const rect = iframeEl.getBoundingClientRect();

            return (
                point.clientX >= rect.left &&
                point.clientX <= rect.right &&
                point.clientY >= rect.top &&
                point.clientY <= rect.bottom
            );
        }

        hideCanvasDropIndicator($target, callback = null) {
            if (!$target || !$target.length) return;

            $target.removeClass('lsd-template-editor-canvas__drop-indicator');

            if (typeof callback === 'function') {
                callback();
            }
        }

        animateDrop($target) {
            if (!$target || !$target.length) return;

            let $animated = $target;
            if (!$animated.is(this.elementBlockSelector) && !$animated.is(this.containerSelector)) {
                $animated = $target.closest(this.elementBlockSelector + ', ' + this.containerSelector);
            }

            if (!$animated.length) return;

            $animated.addClass('is-drop-animated');

            $animated.one('animationend.lsdDrop animationcancel.lsdDrop', () => {
                $animated.removeClass('is-drop-animated');
            });

            window.setTimeout(() => {
                $animated.removeClass('is-drop-animated');
            }, 300);
        }

        getScrollMargin(scrollContext = null) {
            if (scrollContext && scrollContext.doc && scrollContext.doc !== document) {
                return 12;
            }

            const $topbar = $('.lsd-template-editor-topbar');
            return $topbar.length ? ($topbar.outerHeight() || 0) + 12 : 12;
        }

        resolveCanvasScrollTarget($target) {
            if (!$target || !$target.length) return $();

            if ($target.is(this.containerSelector)) {
                const $container = $target.children(this.containerElementSelector).first();
                if ($container.length) return $container;
            }

            if ($target.is(this.elementBlockSelector)) {
                const $wrapper = $target.children(this.elementWrapperSelector).first();
                if ($wrapper.length) return $wrapper;
            }

            if ($target.is(this.containerElementSelector + ', ' + this.elementWrapperSelector)) {
                return $target.first();
            }

            return $target.first();
        }

        isTargetVisibleWithinScrollContext(targetEl, scrollContext, margin = 0) {
            if (!targetEl || !scrollContext || !scrollContext.doc || !scrollContext.win || !scrollContext.scrollEl) {
                return false;
            }

            const targetRect = targetEl.getBoundingClientRect();
            const { doc, win, scrollEl, mode } = scrollContext;
            const bottomPadding = 12;
            const isDocumentScrollEl = scrollEl === doc.scrollingElement
                || scrollEl === doc.documentElement
                || scrollEl === doc.body;

            if (mode === 'iframe-body' || isDocumentScrollEl) {
                return targetRect.top >= margin && targetRect.bottom <= (win.innerHeight - bottomPadding);
            }

            const scrollRect = scrollEl.getBoundingClientRect();
            return targetRect.top >= (scrollRect.top + margin) && targetRect.bottom <= (scrollRect.bottom - bottomPadding);
        }

        autoScrollCanvasOnDrag(event) {
            if (!this.$canvas || !this.$canvas.length) return false;

            const $canvas = this.$canvas;
            const canvasEl = $canvas.get(0);
            if (!canvasEl || typeof canvasEl.getBoundingClientRect !== 'function') return false;

            const rect = canvasEl.getBoundingClientRect();
            const ownerWindow = canvasEl.ownerDocument && canvasEl.ownerDocument.defaultView
                ? canvasEl.ownerDocument.defaultView
                : window;
            const iframeEl = ownerWindow !== window && this.dom.previewIframeManager && this.dom.previewIframeManager.$iframe
                ? this.dom.previewIframeManager.$iframe.get(0)
                : null;
            const iframeRect = iframeEl && typeof iframeEl.getBoundingClientRect === 'function'
                ? iframeEl.getBoundingClientRect()
                : null;
            const height = rect.height;
            const width = rect.width;

            if (!height || !width) return false;
            if ((canvasEl.scrollHeight || 0) <= ((canvasEl.clientHeight || 0) + 2)) return false;

            const topOffset = iframeRect ? iframeRect.top + rect.top : rect.top;
            const leftOffset = iframeRect ? iframeRect.left + rect.left : rect.left;

            const edgeZone    = 72; // px from top/bottom where auto-scroll kicks in
            const x           = event.clientX;
            const y           = event.clientY;
            const topEdge     = topOffset + edgeZone;
            const bottomEdge  = topOffset + height - edgeZone;
            const leftEdge    = leftOffset;
            const rightEdge   = leftOffset + width;

            // Only scroll if the cursor is over the canvas bounds
            if (x < leftEdge || x > rightEdge) return false;
            if (y < topOffset || y > topOffset + height) return false;

            const currentScroll = $canvas.scrollTop();
            const minStep       = 8;
            const maxStep       = 24;
            let nextScroll = currentScroll;

            if (y < topEdge) {
                // scroll up
                const intensity = Math.min(1, Math.max(0, (topEdge - y) / edgeZone));
                const step = Math.max(minStep, Math.round(maxStep * intensity));
                nextScroll = Math.max(currentScroll - step, 0);
            } else if (y > bottomEdge) {
                // scroll down
                const intensity = Math.min(1, Math.max(0, (y - bottomEdge) / edgeZone));
                const step = Math.max(minStep, Math.round(maxStep * intensity));
                nextScroll = currentScroll + step;
            }

            if (nextScroll === currentScroll) return false;

            $canvas.scrollTop(nextScroll);
            return true;
        }

        scrollToCanvasTarget($target) {
            if (!$target || !$target.length || !this.$canvas || !this.$canvas.length) return;

            const $scrollTarget = this.resolveCanvasScrollTarget($target);
            const targetEl = $scrollTarget.get(0);
            if (!targetEl || typeof targetEl.getBoundingClientRect !== 'function') return;

            const scrollContext = this.getIframeScrollContext(targetEl);
            if (!scrollContext || !scrollContext.doc || !scrollContext.win || !scrollContext.scrollEl) return;

            const margin = this.getScrollMargin(scrollContext);
            const { doc, win, scrollEl, mode } = scrollContext;
            const targetRect = targetEl.getBoundingClientRect();
            const targetId = ($target.data('lsd-element-id') || '').toString();

            if (this.isTargetVisibleWithinScrollContext(targetEl, scrollContext, margin)) {
                return;
            }

            if (mode === 'iframe-body') {
                const currentScrollTop = scrollEl.scrollTop || 0;
                const destination = Math.max(currentScrollTop + targetRect.top - margin, 0);
                const didScrollViaHelper = !!(
                    this.dom.previewIframeManager &&
                    typeof this.dom.previewIframeManager.scrollIframeTo === 'function' &&
                    this.dom.previewIframeManager.scrollIframeTo(destination)
                );

                if (!didScrollViaHelper) {
                    scrollEl.scrollTop = destination;
                    if (doc.documentElement) {
                        doc.documentElement.scrollTop = destination;
                    }
                    if (doc.body) {
                        doc.body.scrollTop = destination;
                    }
                    if (typeof win.scrollTo === 'function') {
                        win.scrollTo(0, destination);
                    }
                }

                debugTemplateBuilder('scrollToCanvasTarget', 'iframe-body-forced', {
                    targetId,
                    currentScrollTop,
                    destination,
                    bodyScrollTop: doc.body ? doc.body.scrollTop : 0,
                    htmlScrollTop: doc.documentElement ? doc.documentElement.scrollTop : 0,
                    windowScrollY: win.pageYOffset || 0
                });
                return;
            }

            const isDocumentScrollEl = scrollEl === doc.scrollingElement || scrollEl === doc.documentElement || scrollEl === doc.body;
            const scrollElRect = isDocumentScrollEl
                ? { top: 0 }
                : scrollEl.getBoundingClientRect();
            const currentScrollTop = isDocumentScrollEl
                ? (win.pageYOffset
                    || (doc.documentElement ? doc.documentElement.scrollTop : 0)
                    || (doc.body ? doc.body.scrollTop : 0)
                    || 0)
                : (scrollEl.scrollTop || 0);
            const destination = Math.max(
                targetRect.top - scrollElRect.top + currentScrollTop - margin,
                0
            );
            const scrollDebugMeta = {
                targetId,
                scrollTag: scrollEl.tagName || '',
                scrollId: scrollEl.id || '',
                scrollClass: scrollEl.className || '',
                currentScrollTop,
                destination
            };

            debugTemplateBuilder('scrollToCanvasTarget', 'resolved-scroll-container', scrollDebugMeta);

            if (isDocumentScrollEl) {
                if (typeof win.scrollTo === 'function') {
                    win.scrollTo({
                        top: destination,
                        behavior: 'smooth'
                    });
                }
                return;
            }

            $(scrollEl).stop(true, false).animate({
                scrollTop: destination
            }, 300);
        }

        getScrollableAncestor(node) {
            if (!node || !node.ownerDocument || !node.ownerDocument.defaultView) return null;

            const doc = node.ownerDocument;
            const win = doc.defaultView;
            let current = node.parentElement;

            while (current) {
                const styles = win.getComputedStyle(current);
                const overflowY = (styles && styles.overflowY ? styles.overflowY : '').toLowerCase();
                const isScrollable = ['auto', 'scroll', 'overlay'].includes(overflowY)
                    && current.scrollHeight > (current.clientHeight + 2);

                if (isScrollable) {
                    return current;
                }

                if (current === doc.body) break;
                current = current.parentElement;
            }

            return doc.scrollingElement || doc.documentElement || doc.body;
        }

        getIframeScrollContext(targetEl) {
            if (!targetEl || !targetEl.ownerDocument) return null;

            const doc = targetEl.ownerDocument;
            const win = doc.defaultView;
            if (!win) return null;

            const body = doc.body;
            const previewIframeManager = this.dom.previewIframeManager || null;
            const scrollEl = previewIframeManager && typeof previewIframeManager.resolveIframeScrollElement === 'function'
                ? previewIframeManager.resolveIframeScrollElement(doc)
                : this.getScrollableAncestor(targetEl);

            if (!scrollEl) return null;

            if (body && scrollEl === body && body.classList && body.classList.contains('lsd-template-editor-iframe-surface')) {
                return { doc, win, scrollEl, mode: 'iframe-body' };
            }

            return { doc, win, scrollEl, mode: 'generic' };
        }

        collectElementIds($scope) {
            const ids = [];
            if (!$scope || !$scope.length) return ids;

            $scope.find('[data-lsd-element-id]').addBack($scope).each((i, el) => {
                const id = $(el).data('lsd-element-id');
                if (id && !ids.includes(id)) ids.push(id);
            });

            return ids;
        }

        getSelectedElementId() {
            if (this.settingsPanel && this.settingsPanel.$selectedElement && this.settingsPanel.$selectedElement.length) {
                return this.settingsPanel.$selectedElement.data('lsd-element-id') || null;
            }
            return null;
        }

        selectElement($element) {
            if (this.settingsPanel && typeof this.settingsPanel.selectElement === 'function') {
                this.settingsPanel.selectElement($element);
            }

            const selectedId = $element && $element.length ? ($element.data('lsd-element-id') || null) : null;
            if (this.$workspace && this.$workspace.length) {
                if (selectedId) {
                    this.$workspace.attr('data-lsd-current-element-id', selectedId);
                    this.$workspace.data('lsdCurrentElementId', selectedId);
                } else {
                    this.$workspace.removeAttr('data-lsd-current-element-id');
                    this.$workspace.removeData('lsdCurrentElementId');
                }
            }
            if (this.structureMenu) {
                this.structureMenu.setActive(selectedId);
            }
        }

        clearSelection() {
            if (this.settingsPanel && typeof this.settingsPanel.clearSelection === 'function') {
                this.settingsPanel.clearSelection();
            }

            if (this.$workspace && this.$workspace.length) {
                this.$workspace.removeAttr('data-lsd-current-element-id');
                this.$workspace.removeData('lsdCurrentElementId');
            }

            if (this.structureMenu) {
                this.structureMenu.setActive(null);
            }
        }

        focusElementById(elementId) {
            if (!elementId) {
                this.clearSelection();
                return;
            }

            const $target = this.findCanvasNodeById(elementId);
            if (!$target.length) return;

            this.selectElement($target);
            this.scrollToCanvasTarget($target);
        }

        getSelectedContainerInner() {
            if (!this.settingsPanel || !this.settingsPanel.$selectedElement) return $();

            const $selected = this.settingsPanel.$selectedElement;
            const $container = $selected.closest(this.containerSelector);

            return $container.length ? $container.find(this.containerInnerSelector).first() : $();
        }

        syncStructure(activeId = null) {
            if (!this.structureMenu) return;

            this.structureMenu.render(this.state.state.layout);
            const selectedId = activeId || this.getSelectedElementId();
            this.structureMenu.setActive(selectedId);
        }

        findCanvasNodeById(elementId) {
            if (!elementId || !this.$canvas || !this.$canvas.length) return $();

            const selector = '.lsd-template-editor-canvas__container-item[data-lsd-element-id="' + elementId + '"]'
                + ', .lsd-template-editor-canvas__element-block[data-lsd-element-id="' + elementId + '"]';

            return this.$canvas.find(selector).first();
        }

        applyCanvasOrderFromLayout(nodes, $targetContainer) {
            if (!$targetContainer || !$targetContainer.length || !Array.isArray(nodes)) return;

            nodes.forEach((node) => {
                if (!node || !node.id) return;

                const $item = this.findCanvasNodeById(node.id);
                if (!$item.length) return;

                $targetContainer.append($item);

                if (node.type === 'container' && Array.isArray(node.children)) {
                    const $inner = $item.find(this.containerInnerSelector).first();
                    if ($inner.length) {
                        this.applyCanvasOrderFromLayout(node.children, $inner);
                        this.toggleContainerPlaceholder($inner);
                    }
                }
            });
        }

        applyStructureLayout(layout) {
            if (!Array.isArray(layout)) return;

            const selectedId = this.getSelectedElementId();
            const $canvas = this.ensureCanvasContainer();

            this.state.state.layout = this.state.normalizeLayoutTree(layout);
            this.applyCanvasOrderFromLayout(this.state.state.layout, $canvas);
            this.refreshAllPlaceholders();
            this.syncStructure(selectedId);

            if (selectedId) {
                const $target = this.$canvas.find('[data-lsd-element-id="' + selectedId + '"]').first();
                this.afterCanvasMutation($target.length ? $target : $());
            } else {
                this.afterCanvasMutation();
            }
        }

        // ------------------ Drag / drop helpers ------------------

        isSidebarDrag(ui) {
            return !!(
                ui &&
                ui.draggable &&
                $(ui.draggable).closest('.lsd-template-editor-sidebar').length
            );
        }

        isSidebarPaletteButton(target) {
            const $button = this.resolvePaletteButton($(target));
            return $button.length ? $button : $();
        }

        resolvePaletteButton($source) {
            if (!$source || !$source.length) return $();

            const $button = $source.is(this.draggableButtonSelector)
                ? $source
                : $source.closest(this.draggableButtonSelector);
            if ($button.length) return $button.first();

            const $item = $source.is(this.draggableItemSelector)
                ? $source
                : $source.closest(this.draggableItemSelector);

            return $item.length
                ? $item.find('.lsd-template-editor-element-list__button[data-lsd-element-type]').first()
                : $();
        }

        getPaletteElementType($source) {
            const $button = this.resolvePaletteButton($source);
            return ($button.data('lsd-element-type') || '').toString();
        }

        getPaletteDragData($source) {
            const $button = this.resolvePaletteButton($source);
            const $item = $source && $source.length
                ? ($source.is(this.draggableItemSelector) ? $source : $source.closest(this.draggableItemSelector))
                : $();

            const elementType = (
                $button.data('lsd-element-type')
                || $item.data('lsd-element-type')
                || ''
            ).toString();
            const elementLabel = (
                $button.data('lsd-element-label')
                || $item.data('lsd-element-label')
                || elementType
            ).toString();
            const elementIcon = (
                $button.data('lsd-element-icon')
                || $item.data('lsd-element-icon')
                || $button.find('i').attr('class')
                || ''
            ).toString();

            return {
                elementType,
                elementLabel,
                elementIcon,
                $button
            };
        }

        buildElementIconMap() {
            const map = {};
            const $buttons = $(this.draggableButtonSelector);

            $buttons.each((index, button) => {
                const $button = $(button);
                const type = $button.data('lsd-element-type');
                const iconClass = $button.find('i').attr('class') || '';
                if (!type || !iconClass) return;
                map[type] = iconClass;
            });

            return map;
        }

        refreshElementPalette() {
            this.elementIconMap = this.buildElementIconMap();
            this.initDragAndDrop();
        }

        getIconForType(elementType) {
            if (!elementType) return '';
            return this.elementIconMap[elementType] || '';
        }

        createCanvasElement(elementType, elementLabel, elementIdOverride = null, elementIcon = '') {
            const elementId = elementIdOverride || this.state.createElementId(elementType);
            const safeLabel = elementLabel || elementType || '';
            const safeIcon = elementIcon || this.getIconForType(elementType);
            const initialSettings = elementType === 'categories'
                ? { content: { color_method: '' } }
                : {};

            const $elementBlock = this.cloneTemplate(this.$elementTemplate);
            if (!$elementBlock.length) return $();

            const $wrapper = $elementBlock.find(this.elementWrapperSelector).first();
            if (!$wrapper.length) return $();

            $elementBlock.attr({
                'data-lsd-element-type': elementType,
                'data-lsd-element-id': elementId,
                'data-lsd-element-label': safeLabel,
                'data-lsd-element-icon': safeIcon
            });

            $wrapper.attr({
                'data-lsd-element-type': elementType,
                'data-lsd-element-id': elementId,
                'data-lsd-element-label': safeLabel,
                'data-lsd-element-icon': safeIcon
            });

            const uniqueClass = `lsd-element-${elementId}`;
            $elementBlock.addClass(`lsd-element-instance ${uniqueClass}`);
            $wrapper.addClass(`${uniqueClass}__wrapper`);

            this.setElementContent($wrapper, '');

            this.state.ensureElement({
                id: elementId,
                type: elementType,
                label: safeLabel,
                icon: safeIcon,
                settings: initialSettings
            });

            return $elementBlock;
        }

        createContainer(elementLabel = null, elementId = null, elementIcon = '') {
            const $row = this.cloneTemplate(this.$containerTemplate);

            if (!$row.length) return $();

            const containerId = elementId || this.state.createElementId('container');
            const safeLabel = elementLabel || 'Container';
            const safeIcon = elementIcon || this.getIconForType('container');
            const $inner = $row.find(this.containerInnerSelector).first();
            const uniqueClass = `lsd-element-${containerId}`;

            $row.attr({
                'data-lsd-element-id': containerId,
                'data-lsd-element-type': 'container',
                'data-lsd-element-label': safeLabel,
                'data-lsd-element-icon': safeIcon,
            });

            $row.addClass(`lsd-element-instance ${uniqueClass}`);
            $row.find(this.containerElementSelector).first().addClass(`${uniqueClass}__container`);
            if ($inner.length) $inner.addClass(`${uniqueClass}__inner`);

            if ($inner.length) {
                this.initContainerInnerDropzone($inner);
                this.toggleContainerPlaceholder($inner);
            }

            this.state.ensureElement({
                id: containerId,
                type: 'container',
                label: safeLabel,
                icon: safeIcon,
            });

            return $row;
        }

        renderInsertedElement($element, reason = 'inserted') {
            if (!$element || !$element.length) return;

            const elementId = ($element.data('lsd-element-id') || '').toString();
            const elementType = ($element.data('lsd-element-type') || '').toString();

            if (!elementId || !elementType) return;

            this.settingsPanel.applyPreviewFromState(elementId, reason);
        }

        scrollToInsertedElement($target, reason = 'inserted') {
            if (!$target || !$target.length) return;

            const scroll = () => {
                if (!$target || !$target.length) return;
                this.scrollToCanvasTarget($target);

                if (
                    this.dom.previewIframeManager &&
                    typeof this.dom.previewIframeManager.refreshIframeNiceScroll === 'function'
                ) {
                    this.dom.previewIframeManager.refreshIframeNiceScroll();
                }
            };

            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => {
                    scroll();
                    window.setTimeout(scroll, 120);
                    window.setTimeout(scroll, 350);
                });
            });
        }

        handlePaletteClick(event, $button) {
            if (event) {
                event.preventDefault();
            }

            if (this.suppressPaletteClick) {
                return;
            }

            if (!$button || !$button.length) return;

            const elementType = ($button.data('lsd-element-type') || '').toString();
            const elementLabel = ($button.data('lsd-element-label') || elementType).toString();
            const elementIcon = ($button.find('i').attr('class') || '').toString();

            debugTemplateBuilder('palette-click', elementType);

            if (!elementType) return;

            if (elementType === 'container') {
                const $row = this.createContainer(elementLabel || 'Container', null, elementIcon);
                if (!$row.length) return;

                const $canvasElements = this.ensureCanvasContainer();
                if (!$canvasElements.length) return;

                $canvasElements.append($row);
                this.renderInsertedElement($row, 'click-add-container');

                if ($canvasElements.hasClass('ui-sortable')) {
                    $canvasElements.sortable('refresh');
                } else {
                    this.makeCanvasSortable($canvasElements);
                }

                this.afterCanvasMutation($row);
                this.scrollToInsertedElement($row, 'click-add-container');
                return;
            }

            this.addElementInNewContainer(elementType, elementLabel, elementIcon);
        }

        addElementInNewContainer(elementType, elementLabel = '', elementIcon = '', dropLocation = null) {
            if (!elementType || elementType === 'container') return;

            const $canvas = this.ensureCanvasContainer();
            if (!$canvas.length) return;

            // Create a brand-new container
            const $row = this.createContainer('Container', null, this.getIconForType('container'));
            if (!$row.length) return;

            const $inner = $row.find(this.containerInnerSelector).first();
            if (!$inner.length) return;

            // Create the element and append it to the new container
            const $element = this.createCanvasElement(elementType, elementLabel, null, elementIcon);
            if (!$element.length) return;

            $inner.append($element);
            this.toggleContainerPlaceholder($inner);

            // Add the new container (with element inside) to the canvas
            if (!(dropLocation && dropLocation.kind === 'root' && this.insertNodeAtDropLocation($row, dropLocation))) {
                $canvas.append($row);
            }
            this.renderInsertedElement($row, 'click-add-container-shell');
            this.renderInsertedElement($element, 'click-add-element');

            // Ensure sortables are aware of the new container
            if ($canvas.hasClass('ui-sortable')) {
                $canvas.sortable('refresh');
            } else {
                this.makeCanvasSortable($canvas);
            }

            this.afterCanvasMutation($element);
            this.scrollToInsertedElement($element, 'click-add-element');
        }

        autoScrollIframeOnDrag(event) {
            const iframe = this.dom.previewIframeManager && this.dom.previewIframeManager.$iframe
                ? this.dom.previewIframeManager.$iframe.get(0)
                : null;
            if (!iframe) return false;

            const iframeWindow = iframe.contentWindow;
            const iframeDocument = iframe.contentDocument;

            if (!iframeWindow || !iframeDocument) return false;

            const rect = iframe.getBoundingClientRect();

            const inside = event.clientX >= rect.left &&
                event.clientX <= rect.right &&
                event.clientY >= rect.top &&
                event.clientY <= rect.bottom;

            if (!inside) return false;

            const y = event.clientY - rect.top;
            const edgeZone = 88;
            const minStep = 8;
            const maxStep = 24;
            const body = iframeDocument.body;
            const previewIframeManager = this.dom.previewIframeManager || null;
            const scrollElement = previewIframeManager && typeof previewIframeManager.resolveIframeScrollElement === 'function'
                ? previewIframeManager.resolveIframeScrollElement(iframeDocument)
                : (iframeDocument.scrollingElement || iframeDocument.documentElement || body);
            const isDocumentScrollEl = scrollElement === iframeDocument.scrollingElement
                || scrollElement === iframeDocument.documentElement
                || scrollElement === body;

            if (!scrollElement) return false;

            const $scrollElement = $(scrollElement);
            const niceScrollInstance = typeof $scrollElement.getNiceScroll === 'function'
                ? $scrollElement.getNiceScroll()
                : null;
            const setScrollTop = (nextTop) => {
                if (niceScrollInstance && niceScrollInstance.length && typeof niceScrollInstance.doScrollTop === 'function') {
                    niceScrollInstance.doScrollTop(nextTop, 0);
                    return;
                }

                scrollElement.scrollTop = nextTop;

                if (isDocumentScrollEl) {
                    if (iframeDocument.documentElement) iframeDocument.documentElement.scrollTop = nextTop;
                    if (body) body.scrollTop = nextTop;
                    if (typeof iframeWindow.scrollTo === 'function') {
                        iframeWindow.scrollTo(0, nextTop);
                    }
                }
            };
            const currentScrollTop = isDocumentScrollEl
                ? (iframeWindow.pageYOffset
                    || (iframeDocument.documentElement ? iframeDocument.documentElement.scrollTop : 0)
                    || (body ? body.scrollTop : 0)
                    || 0)
                : (scrollElement.scrollTop || 0);
            let nextTop = currentScrollTop;

            if (y < edgeZone) {
                const intensity = Math.min(1, Math.max(0, (edgeZone - y) / edgeZone));
                const step = Math.max(minStep, Math.round(maxStep * intensity));
                nextTop = Math.max(currentScrollTop - step, 0);
            } else if (y > rect.height - edgeZone) {
                const maxScrollTop = Math.max((scrollElement.scrollHeight || 0) - (scrollElement.clientHeight || 0), 0);
                const intensity = Math.min(1, Math.max(0, (y - (rect.height - edgeZone)) / edgeZone));
                const step = Math.max(minStep, Math.round(maxStep * intensity));
                nextTop = Math.min(currentScrollTop + step, maxScrollTop);
            }

            if (nextTop === currentScrollTop) return false;

            setScrollTop(nextTop);
            return true;
        }

        buildCompactSortHelper($item) {
            if (!$item || !$item.length) return $('<div></div>');

            const elementType = ($item.data('lsd-element-type') || '').toString();
            const elementLabel = ($item.data('lsd-element-label') || elementType || 'Item').toString();
            const elementIcon = ($item.data('lsd-element-icon') || this.getIconForType(elementType) || '').toString();
            const isContainer = $item.is(this.containerSelector) || elementType === 'container';
            const helperClass = isContainer
                ? 'lsd-template-editor-canvas__sort-helper lsd-template-editor-canvas__sort-helper--container lsd-template-editor-element-list__drag-helper'
                : 'lsd-template-editor-canvas__sort-helper lsd-template-editor-canvas__sort-helper--element lsd-template-editor-element-list__drag-helper';
            const iconHtml = elementIcon
                ? '<i class="' + elementIcon.replace(/"/g, '&quot;') + '" aria-hidden="true"></i>'
                : '<i class="wbli-square" aria-hidden="true"></i>';

            return $(
                '<div class="' + helperClass + '">' +
                    '<span class="lsd-template-editor-canvas__sort-helper-icon lsd-template-editor-element-list__drag-helper-icon">' + iconHtml + '</span>' +
                    '<span class="lsd-template-editor-canvas__sort-helper-label lsd-template-editor-element-list__drag-helper-label"></span>' +
                '</div>'
            )
                .find('.lsd-template-editor-canvas__sort-helper-label')
                .text(elementLabel || 'Item')
                .end()
                .css({
                    width: '220px',
                    maxWidth: '220px',
                    margin: 0,
                    pointerEvents: 'none',
                    position: 'fixed',
                    zIndex: 999999
                });
        }

        resolveHelperPointerOffset($helper, fallback = { left: 28, top: 18 }) {
            const helperWidth = Math.ceil($helper && $helper.length ? ($helper.outerWidth() || 0) : 0);
            const helperHeight = Math.ceil($helper && $helper.length ? ($helper.outerHeight() || 0) : 0);
            const clamp = (value, size, fallbackValue) => {
                const nextValue = typeof value === 'number' ? value : fallbackValue;
                if (!size) return Math.max(0, nextValue);

                const inset = Math.min(12, Math.floor(size / 2));
                const max = Math.max(size - inset, inset);

                return Math.max(inset, Math.min(nextValue, max));
            };

            return {
                left: clamp(fallback.left, helperWidth, fallback.left),
                top: clamp(fallback.top, helperHeight, fallback.top)
            };
        }

        positionHelperAtPoint($helper, point, offset = null) {
            if (!$helper || !$helper.length || !point) return offset || { left: 0, top: 0 };

            const helperOffset = offset || this.resolveHelperPointerOffset($helper);
            $helper.css({
                left: (point.clientX - helperOffset.left) + 'px',
                top: (point.clientY - helperOffset.top) + 'px'
            });

            return helperOffset;
        }

        buildSyntheticPointerEvent(point, ownerDocument = document, target = null) {
            if (!point || typeof point.clientX !== 'number' || typeof point.clientY !== 'number') return null;

            const doc = ownerDocument || document;
            const win = doc.defaultView || window;
            const docEl = doc.documentElement || null;
            const body = doc.body || null;
            const scrollLeft = win.pageXOffset
                || (docEl ? docEl.scrollLeft : 0)
                || (body ? body.scrollLeft : 0)
                || 0;
            const scrollTop = win.pageYOffset
                || (docEl ? docEl.scrollTop : 0)
                || (body ? body.scrollTop : 0)
                || 0;

            return $.Event('mousemove', {
                clientX: point.clientX,
                clientY: point.clientY,
                pageX: point.clientX + scrollLeft,
                pageY: point.clientY + scrollTop,
                target: target || body || docEl || doc
            });
        }

        updateActiveSortableDrag($sortable, ui) {
            const $item = ui && ui.item ? $(ui.item) : $();
            const $helper = ui && ui.helper ? $(ui.helper) : $();
            const $placeholder = ui && ui.placeholder ? $(ui.placeholder) : $();
            const $currentSortable = $placeholder.length
                ? $placeholder.parent()
                : ($sortable && $sortable.length ? $sortable : ($item.length ? $item.parent() : $()));
            const helperNode = $helper.length ? $helper.get(0) : null;
            const ownerDocument = helperNode && helperNode.ownerDocument
                ? helperNode.ownerDocument
                : ($item.length && $item.get(0).ownerDocument ? $item.get(0).ownerDocument : document);
            const instance = $currentSortable.length && typeof $currentSortable.sortable === 'function'
                ? $currentSortable.sortable('instance')
                : null;

            this.activeSortableDrag = {
                $sortable: $currentSortable,
                $item,
                $helper,
                $placeholder,
                ownerDocument,
                instance
            };
        }

        clearActiveSortableDrag() {
            this.activeSortableDrag = null;
        }

        syncActiveSortableDragOnAutoScroll(point) {
            const drag = this.activeSortableDrag;
            if (!drag || !drag.$helper || !drag.$helper.length || !point) return;

            this.pinSortableHelperToPointer(drag.$helper, point);

            if (drag.$placeholder && drag.$placeholder.parent().length) {
                drag.$sortable = drag.$placeholder.parent();
            }

            if (drag.$sortable && drag.$sortable.length && typeof drag.$sortable.sortable === 'function') {
                drag.instance = drag.$sortable.sortable('instance') || drag.instance;
            }

            if (drag.instance && typeof drag.instance.refreshPositions === 'function') {
                drag.instance.refreshPositions();
            }

            const syntheticEvent = this.buildSyntheticPointerEvent(point, drag.ownerDocument, drag.$helper.get(0));
            if (drag.instance && syntheticEvent && typeof drag.instance._mouseDrag === 'function') {
                try {
                    drag.instance._mouseDrag(syntheticEvent);
                } catch (err) {
                    // noop
                }
            }

            this.refreshCanvasSortablePositions();
        }

        resolveSortableCursorAt(event, $source, $helper) {
            return this.resolveHelperPointerOffset($helper, { left: 28, top: 18 });
        }

        applySortableCursorAt($sortable, event, $source, $helper) {
            const cursorAt = this.resolveSortableCursorAt(event, $source, $helper);

            if ($sortable && $sortable.length && typeof $sortable.sortable === 'function') {
                const instance = $sortable.sortable('instance');
                if (instance) {
                    instance.options.cursorAt = cursorAt;

                    if (instance.helper && instance.helper.length) {
                        if (typeof instance._cacheMargins === 'function') {
                            instance._cacheMargins();
                        }

                        if (typeof instance._cacheHelperProportions === 'function') {
                            instance._cacheHelperProportions();
                        }

                        if (typeof instance._adjustOffsetFromHelper === 'function') {
                            instance._adjustOffsetFromHelper(cursorAt);
                        }
                    }
                }
            }

            return cursorAt;
        }

        pinSortableHelperToPointer($helper, point) {
            if (!$helper || !$helper.length || !point) return null;

            const helperOffset = this.resolveHelperPointerOffset($helper, { left: 28, top: 18 });
            this.positionHelperAtPoint($helper, point, helperOffset);

            return helperOffset;
        }

        getSortCursorSource($item) {
            if (!$item || !$item.length) return $();

            if ($item.is(this.containerSelector)) {
                const $container = $item.children(this.containerElementSelector).first();
                if ($container.length) return $container;
            }

            if ($item.is(this.elementBlockSelector)) {
                const $wrapper = $item.children(this.elementWrapperSelector).first();
                if ($wrapper.length) return $wrapper;
            }

            return $item;
        }

        getOwnerDocumentBody($node) {
            const node = $node && $node.length ? $node.get(0) : null;
            const ownerDocument = node && node.ownerDocument ? node.ownerDocument : document;

            return ownerDocument && ownerDocument.body ? ownerDocument.body : document.body;
        }

        buildPaletteDragHelper($button) {
            if (!$button || !$button.length) return $('<div></div>');

            const elementType = ($button.data('lsd-element-type') || '').toString();
            const elementLabel = ($button.data('lsd-element-label') || elementType || 'Item').toString();
            const elementIcon = ($button.find('i').attr('class') || this.getIconForType(elementType) || '').toString();
            const iconHtml = elementIcon
                ? '<i class="' + elementIcon.replace(/"/g, '&quot;') + '" aria-hidden="true"></i>'
                : '<i class="wbli-square" aria-hidden="true"></i>';

            return $(
                '<div class="lsd-template-editor-element-list__drag-helper">' +
                    '<span class="lsd-template-editor-element-list__drag-helper-icon">' + iconHtml + '</span>' +
                    '<span class="lsd-template-editor-element-list__drag-helper-label"></span>' +
                '</div>'
            )
                .find('.lsd-template-editor-element-list__drag-helper-label')
                .text(elementLabel || 'Item')
                .end()
                .css({
                    margin: 0,
                    pointerEvents: 'none'
                })
                .data('lsd-element-type', elementType)
                .data('lsd-element-label', elementLabel || elementType)
                .data('lsd-element-icon', elementIcon);
        }

        getClientPoint(event) {
            const source = event && event.originalEvent ? event.originalEvent : event;
            if (!source) return null;

            if (typeof source.clientX === 'number' && typeof source.clientY === 'number') {
                return {
                    clientX: source.clientX,
                    clientY: source.clientY
                };
            }

            if (source.changedTouches && source.changedTouches.length) {
                return {
                    clientX: source.changedTouches[0].clientX,
                    clientY: source.changedTouches[0].clientY
                };
            }

            if (source.touches && source.touches.length) {
                return {
                    clientX: source.touches[0].clientX,
                    clientY: source.touches[0].clientY
                };
            }

            return null;
        }

        getPaletteDropIndicator() {
            if (this.$paletteDropIndicator && this.$paletteDropIndicator.length) {
                return this.$paletteDropIndicator;
            }

            this.$paletteDropIndicator = $('<div class="lsd-template-editor-canvas__palette-indicator lsd-template-editor-canvas__drop-indicator ui-sortable-placeholder" aria-hidden="true"></div>');
            return this.$paletteDropIndicator;
        }

        getIframePoint(point) {
            const iframeEl = this.dom.previewIframeManager && this.dom.previewIframeManager.$iframe
                ? this.dom.previewIframeManager.$iframe.get(0)
                : null;
            const iframeDocument = iframeEl && iframeEl.contentDocument ? iframeEl.contentDocument : null;

            if (!iframeEl || !iframeDocument || !point || typeof iframeEl.getBoundingClientRect !== 'function') {
                return null;
            }

            const rect = iframeEl.getBoundingClientRect();

            return {
                iframeEl,
                iframeDocument,
                rect,
                iframeX: point.clientX - rect.left,
                iframeY: point.clientY - rect.top
            };
        }

        getPaletteDropItems($target, kind = 'root') {
            if (!$target || !$target.length) return $();

            const selector = kind === 'container'
                ? [this.containerSelector, this.elementBlockSelector].join(', ')
                : this.containerSelector;
            const $indicator = this.getPaletteDropIndicator();

            return $target.children(selector).not($indicator);
        }

        resolvePaletteDropLocation(point) {
            if (!point || !this.isPointInsidePreviewIframe(point)) return null;

            const iframePoint = this.getIframePoint(point);
            if (!iframePoint) return null;

            if (
                iframePoint.iframeX < 0 ||
                iframePoint.iframeY < 0 ||
                iframePoint.iframeX > iframePoint.rect.width ||
                iframePoint.iframeY > iframePoint.rect.height
            ) {
                return null;
            }

            const dropTarget = this.resolveIframeDropTarget(point);
            const kind = dropTarget && dropTarget.kind === 'container' ? 'container' : 'root';
            const $target = dropTarget && dropTarget.$target && dropTarget.$target.length
                ? dropTarget.$target.first()
                : (kind === 'container' ? $() : this.ensureCanvasContainer());

            if (!$target.length) return null;

            const $items = this.getPaletteDropItems($target, kind);
            if (!$items.length) {
                return {
                    kind,
                    mode: 'empty',
                    $target
                };
            }

            let location = {
                kind,
                mode: 'after',
                $target,
                $reference: $items.last()
            };

            $items.each((index, item) => {
                if (!item || typeof item.getBoundingClientRect !== 'function') return;

                const rect = item.getBoundingClientRect();
                const middle = rect.top + (rect.height / 2);

                if (iframePoint.iframeY < middle) {
                    location = {
                        kind,
                        mode: 'before',
                        $target,
                        $reference: $(item)
                    };
                    return false;
                }
            });

            return location;
        }

        isSamePaletteDropLocation(left, right) {
            if (!left || !right) return false;

            const leftTarget = left.$target && left.$target.length ? left.$target.get(0) : null;
            const rightTarget = right.$target && right.$target.length ? right.$target.get(0) : null;
            const leftReference = left.$reference && left.$reference.length ? left.$reference.get(0) : null;
            const rightReference = right.$reference && right.$reference.length ? right.$reference.get(0) : null;

            return left.kind === right.kind
                && left.mode === right.mode
                && leftTarget === rightTarget
                && leftReference === rightReference;
        }

        placePaletteDropIndicator(location) {
            if (!location || !location.$target || !location.$target.length) return $();

            if (location.mode === 'empty') {
                if (location.kind === 'container') {
                    return location.$target.children('.lsd-template-editor-canvas__container-placeholder').first();
                }

                return this.getGlobalPlaceholder();
            }

            const $indicator = this.getPaletteDropIndicator();

            if (location.mode === 'before' && location.$reference && location.$reference.length) {
                location.$reference.before($indicator);
            } else if (location.mode === 'after' && location.$reference && location.$reference.length) {
                location.$reference.after($indicator);
            } else {
                location.$target.append($indicator);
            }

            return $indicator;
        }

        clearPaletteDropIndicator() {
            this.activePaletteDropLocation = null;

            if (this.$activePaletteDropIndicator && this.$activePaletteDropIndicator.length) {
                const $previous = this.$activePaletteDropIndicator;
                this.$activePaletteDropIndicator = $();

                if ($previous.is('.lsd-template-editor-canvas__palette-indicator')) {
                    $previous.detach();
                    this.refreshAllPlaceholders();
                    return;
                }

                this.hideCanvasDropIndicator($previous);

                const $inner = $previous.closest(this.containerInnerSelector);
                if ($inner.length) {
                    this.toggleContainerPlaceholder($inner);
                } else {
                    this.refreshAllPlaceholders();
                }
            } else {
                this.refreshAllPlaceholders();
            }
        }

        updatePaletteDragIndicator(point) {
            const nextLocation = this.resolvePaletteDropLocation(point);
            if (!nextLocation) {
                this.clearPaletteDropIndicator();
                return;
            }

            if (this.isSamePaletteDropLocation(this.activePaletteDropLocation, nextLocation)) return;

            this.clearPaletteDropIndicator();

            const $nextIndicator = this.placePaletteDropIndicator(nextLocation);
            if ($nextIndicator.length) {
                this.activePaletteDropLocation = nextLocation;
                this.$activePaletteDropIndicator = $nextIndicator;
                this.showCanvasDropIndicator($nextIndicator);
            }
        }

        beginPalettePointerDrag(candidate, point) {
            if (!candidate || candidate.dragging) return;

            candidate.dragging = true;
            this.paletteDragState = candidate;
            this.suppressPaletteClick = true;
            this.setDraggingState(true);
            this.setSidebarDraggingState(true);
            this.setDragAutoScrollPoint(point);
            this.startDragAutoScrollLoop();
            this.showIframeDropBridge();

            candidate.$helper = this.buildPaletteDragHelper(candidate.dragData.$button)
                .css({
                    position: 'fixed',
                    zIndex: 999999,
                    margin: 0,
                    pointerEvents: 'none'
                })
                .appendTo('body');
            candidate.helperOffset = this.positionHelperAtPoint(
                candidate.$helper,
                point,
                this.resolveHelperPointerOffset(candidate.$helper, { left: 28, top: 18 })
            );
        }

        updatePalettePointerDrag(point) {
            const state = this.paletteDragState;
            if (!state || !state.dragging || !point) return;

            if (state.$helper && state.$helper.length) {
                state.helperOffset = this.positionHelperAtPoint(
                    state.$helper,
                    point,
                    state.helperOffset || this.resolveHelperPointerOffset(state.$helper, { left: 28, top: 18 })
                );
            }

            this.lastSidebarDragPoint = point;
            this.setDragAutoScrollPoint(point);
            this.autoScrollIframeOnDrag(point);
            this.autoScrollCanvasOnDrag(point);

            const insideIframe = this.isPointInsidePreviewIframe(point);
            const $bridge = this.getIframeDropBridge();
            if ($bridge.length) {
                $bridge.toggleClass('is-active', insideIframe);
            }

            if (insideIframe) {
                this.updatePaletteDragIndicator(point);
            } else {
                this.clearPaletteDropIndicator();
            }
        }

        finishPalettePointerDrag(point) {
            const state = this.paletteDragState;
            if (!state) return;

            const dropPoint = point || this.lastSidebarDragPoint || null;
            const activePaletteDropLocation = this.activePaletteDropLocation;
            const dropData = state.dragData || {};
            const shouldInsert = !!(
                state.dragging
                && dropPoint
                && this.isPointInsidePreviewIframe(dropPoint)
                && dropData.elementType
            );

            if (state.$helper && state.$helper.length) {
                state.$helper.remove();
            }

            this.clearPaletteDropIndicator();
            this.hideIframeDropBridge();
            this.setDraggingState(false);
            this.setSidebarDraggingState(false);
            this.stopDragAutoScrollLoop();

            this.paletteDragState = null;
            this.lastSidebarDragPoint = null;

            if (shouldInsert) {
                this.handleIframeSidebarDrop({
                    elementType: dropData.elementType,
                    elementLabel: dropData.elementLabel,
                    elementIcon: dropData.elementIcon,
                    point: {
                        clientX: dropPoint.clientX,
                        clientY: dropPoint.clientY
                    }
                }, dropPoint, activePaletteDropLocation);
            }

            window.setTimeout(() => {
                this.suppressPaletteClick = false;
            }, 0);
        }

        initPalettePointerDrag() {
            if (this.palettePointerBound) return;

            let candidate = null;
            const distanceThreshold = 6;

            $(document)
                .off('dragstart.lsdPalettePointerDrag', this.draggableSelector)
                .on('dragstart.lsdPalettePointerDrag', this.draggableSelector, (event) => {
                    event.preventDefault();
                })
                .off('mousedown.lsdPalettePointerDrag', this.draggableItemSelector)
                .on('mousedown.lsdPalettePointerDrag', this.draggableItemSelector, (event) => {
                    if (event.button !== 0) return;
                    if ($(event.target).closest('input, textarea, select, option').length) return;

                    const $item = $(event.currentTarget);
                    const dragData = this.getPaletteDragData($item);

                    if (!dragData.elementType) return;

                    candidate = {
                        dragData,
                        dragging: false,
                        startPoint: {
                            clientX: event.clientX,
                            clientY: event.clientY
                        },
                        $helper: $(),
                        helperOffset: null
                    };
                });

            $(document)
                .off('mousemove.lsdPalettePointerDrag')
                .on('mousemove.lsdPalettePointerDrag', (event) => {
                    if (!candidate) return;

                    const point = this.getClientPoint(event);
                    if (!point) return;

                    const deltaX = Math.abs(point.clientX - candidate.startPoint.clientX);
                    const deltaY = Math.abs(point.clientY - candidate.startPoint.clientY);

                    if (!candidate.dragging) {
                        if (deltaX < distanceThreshold && deltaY < distanceThreshold) {
                            return;
                        }

                        event.preventDefault();
                        this.beginPalettePointerDrag(candidate, point);
                    } else {
                        event.preventDefault();
                    }

                    this.updatePalettePointerDrag(point);
                });

            $(document)
                .off('mouseup.lsdPalettePointerDrag')
                .on('mouseup.lsdPalettePointerDrag', (event) => {
                    if (!candidate) return;

                    const point = this.getClientPoint(event);
                    const wasDragging = candidate.dragging;

                    if (wasDragging) {
                        event.preventDefault();
                    }

                    this.finishPalettePointerDrag(point);
                    candidate = null;
                });

            this.palettePointerBound = true;
        }

        removeInvalidRootElements() {
            const $container = this.ensureCanvasContainer();
            if (!$container.length) return;

            $container.children(this.elementBlockSelector).remove();
        }

        restoreFromInvalidRootDrop($item) {
            if (!$item || !$item.length) return false;

            const $originParent = $item.data('lsd-sort-origin-parent');
            const $originNext = $item.data('lsd-sort-origin-next');

            if (!$originParent || !$originParent.length) return false;

            if ($originNext && $originNext.length && $originNext.parent().is($originParent)) {
                $item.insertBefore($originNext);
            } else {
                $originParent.append($item);
            }

            if ($originParent.hasClass('ui-sortable')) {
                $originParent.sortable('refresh');
            }

            this.toggleContainerPlaceholder($originParent);
            return true;
        }

        makeContainerInnerSortable($containerInner) {
            if ($containerInner.length && typeof $containerInner.sortable === 'function') {
                if ($containerInner.data('ui-sortable')) {
                    try {
                        $containerInner.sortable('destroy');
                    } catch (err) {
                        // noop
                    }
                }

                $containerInner.sortable({
                    items: this.elementBlockSelector + ', ' + this.containerSelector,
                    handle: this.sortableHandleSelector,
                    helper: (event, item) => {
                        const $item = $(item);
                        const $helper = this.buildCompactSortHelper($item);
                        const point = this.getClientPoint(event);

                        this.applySortableCursorAt($item.parent(), event, this.getSortCursorSource($item), $helper);
                        if (point) this.pinSortableHelperToPointer($helper, point);

                        return $helper;
                    },
                    appendTo: this.getOwnerDocumentBody($containerInner),
                    cursorAt: {
                        left: 28,
                        top: 18
                    },
                    tolerance: 'pointer',
                    connectWith: this.containerInnerSelector + ', ' + this.playgroundSelector,
                    cancel: '.lsd-template-editor-canvas__controls, [data-lsd-element-control], input, textarea, select, option, button',
                    distance: 5,
                    placeholder: 'lsd-template-editor-canvas__drop-indicator',
                    forcePlaceholderSize: true,
                    scroll: false,
                    start: (event, ui) => {
                        const point = this.getClientPoint(event);
                        if (ui && ui.item) {
                            ui.item.data('lsd-sort-origin-parent', ui.item.parent());
                            ui.item.data('lsd-sort-origin-next', ui.item.next());
                            if (ui.helper) {
                                this.applySortableCursorAt(ui.item.parent(), event, this.getSortCursorSource($(ui.item)), $(ui.helper));
                                if (point) this.pinSortableHelperToPointer($(ui.helper), point);
                            }
                        }
                        this.updateActiveSortableDrag($(event.target), ui);
                        this.setDraggingState(true);
                        if (point) this.setDragAutoScrollPoint(point);
                        this.startDragAutoScrollLoop();
                        this.refreshAllPlaceholders();
                    },
                    sort: (event, ui) => {
                        const point = this.getClientPoint(event);
                        if (!point) return;

                        if (ui && ui.item && ui.helper) {
                            this.applySortableCursorAt($(event.target), event, this.getSortCursorSource($(ui.item)), $(ui.helper));
                            this.pinSortableHelperToPointer($(ui.helper), point);
                        }
                        this.updateActiveSortableDrag($(event.target), ui);

                        this.setDragAutoScrollPoint(point);
                        this.autoScrollIframeOnDrag(point);
                        this.autoScrollCanvasOnDrag(point);
                    },
                    change: () => {
                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    receive: (event) => {
                        const $inner = $(event.target);
                        this.toggleContainerPlaceholder($inner);
                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    remove: (event) => {
                        const $inner = $(event.target);
                        this.toggleContainerPlaceholder($inner);
                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    stop: (event, ui) => {
                        this.clearActiveSortableDrag();
                        this.setDraggingState(false);
                        this.stopDragAutoScrollLoop();
                        if (ui && ui.item) {
                            this.animateDrop($(ui.item));
                            ui.item.removeData('lsd-sort-origin-parent lsd-sort-origin-next');
                        }
                        this.afterCanvasMutation(ui && ui.item ? $(ui.item) : $());
                    }
                });
            }
        }

        makeCanvasSortable($container) {
            if ($container.length && typeof $container.sortable === 'function') {
                if ($container.data('ui-sortable')) {
                    try {
                        $container.sortable('destroy');
                    } catch (err) {
                        // noop
                    }
                }

                $container.sortable({
                    items: this.containerSelector,
                    handle: this.sortableHandleSelector,
                    helper: (event, item) => {
                        const $item = $(item);
                        const $helper = this.buildCompactSortHelper($item);
                        const point = this.getClientPoint(event);

                        this.applySortableCursorAt($item.parent(), event, this.getSortCursorSource($item), $helper);
                        if (point) this.pinSortableHelperToPointer($helper, point);

                        return $helper;
                    },
                    appendTo: this.getOwnerDocumentBody($container),
                    cursorAt: {
                        left: 28,
                        top: 18
                    },
                    tolerance: 'pointer',
                    connectWith: this.containerInnerSelector + ', ' + this.playgroundSelector,
                    cancel: '.lsd-template-editor-canvas__controls, [data-lsd-element-control], input, textarea, select, option, button',
                    distance: 5,
                    placeholder: 'lsd-template-editor-canvas__drop-indicator',
                    forcePlaceholderSize: true,
                    scroll: false,
                    start: (event, ui) => {
                        const point = this.getClientPoint(event);
                        if (ui && ui.item && ui.helper) {
                            this.applySortableCursorAt(ui.item.parent(), event, this.getSortCursorSource($(ui.item)), $(ui.helper));
                            if (point) this.pinSortableHelperToPointer($(ui.helper), point);
                        }
                        this.updateActiveSortableDrag($(event.target), ui);
                        this.setDraggingState(true);
                        if (point) this.setDragAutoScrollPoint(point);
                        this.startDragAutoScrollLoop();
                    },
                    sort: (event, ui) => {
                        const point = this.getClientPoint(event);
                        if (!point) return;

                        if (ui && ui.item && ui.helper) {
                            this.applySortableCursorAt($(event.target), event, this.getSortCursorSource($(ui.item)), $(ui.helper));
                            this.pinSortableHelperToPointer($(ui.helper), point);
                        }
                        this.updateActiveSortableDrag($(event.target), ui);

                        this.setDragAutoScrollPoint(point);
                        this.autoScrollIframeOnDrag(point);
                        this.autoScrollCanvasOnDrag(point);
                    },
                    change: () => {
                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    receive: (event, ui) => {
                        if (ui && ui.item && !$(ui.item).is(this.containerSelector)) {
                            const restored = this.restoreFromInvalidRootDrop($(ui.item));
                            if (!restored) {
                                try {
                                    $(event.target).sortable('cancel');
                                } catch (err) {
                                    // noop
                                }
                            }
                            this.refreshAllPlaceholders();
                            this.refreshCanvasSortablePositions();
                            return;
                        }

                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    remove: () => {
                        this.refreshAllPlaceholders();
                        this.refreshCanvasSortablePositions();
                    },
                    stop: (event, ui) => {
                        this.clearActiveSortableDrag();
                        this.setDraggingState(false);
                        this.stopDragAutoScrollLoop();
                        const $mutationTarget = ui && ui.item ? $(ui.item) : $();

                        if ($mutationTarget.length) {
                            this.animateDrop($mutationTarget);
                        }
                        this.afterCanvasMutation($mutationTarget);
                    }
                });
            }
        }

        initContainerInnerDropzone($containerInner) {
            if (!$containerInner.length) return;

            if ($containerInner.data('ui-droppable')) {
                try {
                    $containerInner.droppable('destroy');
                } catch (err) {
                    // noop
                }
            }

            $containerInner.droppable({
                greedy: true,
                accept: (el) => {
                    const type = this.getPaletteElementType($(el)) || ($(el).data('lsd-element-type') || '').toString();
                    return !!type;
                },
                tolerance: 'pointer',
                over: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    const $placeholder = $containerInner.children('.lsd-template-editor-canvas__container-placeholder').first();
                    this.showCanvasDropIndicator($placeholder);
                },
                out: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    const $placeholder = $containerInner.children('.lsd-template-editor-canvas__container-placeholder').first();
                    this.hideCanvasDropIndicator($placeholder, () => this.toggleContainerPlaceholder($containerInner));
                },
                drop: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    const $placeholder = $containerInner.children('.lsd-template-editor-canvas__container-placeholder').first();
                    this.hideCanvasDropIndicator($placeholder);

                    const elementType = ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type');
                    const elementLabel = (ui.helper.data('lsd-element-label')
                        || ui.draggable.data('lsd-element-label')
                        || elementType);
                    const elementIcon = ui.helper.data('lsd-element-icon') || '';

                    if (!elementType) return;

                    let $element = $();

                    if (elementType === 'container') {
                        $element = this.createContainer(elementLabel || 'Container', null, elementIcon);
                    } else {
                        $element = this.createCanvasElement(elementType, elementLabel, null, elementIcon);
                    }

                    if (!$element.length) return;

                    $containerInner.append($element);
                    this.renderInsertedElement($element, 'jquery-drop-container');
                    this.animateDrop($element);
                    this.setDraggingState(false);
                    this.setSidebarDraggingState(false);

                    if ($containerInner.hasClass('ui-sortable')) {
                        $containerInner.sortable('refresh');
                    } else {
                        this.makeContainerInnerSortable($containerInner);
                    }

                    this.afterCanvasMutation($element);
                    this.scrollToInsertedElement($element, 'jquery-drop-container');
                }
            });

            this.makeContainerInnerSortable($containerInner);
            this.toggleContainerPlaceholder($containerInner);
        }

        getIframeDropBridge() {
            if (this.$iframeDropBridge && this.$iframeDropBridge.length) return this.$iframeDropBridge;

            const $frame = this.dom.$workspaceCanvas.find('[data-lsd-template-preview-frame]').first();
            if (!$frame.length) return $();

            this.$iframeDropBridge = $('<div>', {
                class: 'lsd-template-editor-canvas__iframe-drop-bridge',
                'aria-hidden': 'true'
            }).appendTo($frame);

            return this.$iframeDropBridge;
        }

        initIframeDropBridge() {
            const $bridge = this.getIframeDropBridge();
            if (!$bridge.length || typeof $bridge.droppable !== 'function') return;

            if ($bridge.data('ui-droppable')) {
                try {
                    $bridge.droppable('destroy');
                } catch (err) {
                    // noop
                }
            }

            $bridge.droppable({
                accept: (el) => {
                    return !!(this.getPaletteElementType($(el)) || ($(el).data('lsd-element-type') || '').toString());
                },
                tolerance: 'pointer',
                over: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    $bridge.addClass('is-active');
                },
                out: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    $bridge.removeClass('is-active');
                },
                drop: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    $bridge.removeClass('is-active');
                    const dropPoint = this.getClientPoint(event) || this.lastSidebarDragPoint || null;
                    this.pendingIframeDrop = {
                        elementType: ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type') || '',
                        elementLabel: (ui.helper.data('lsd-element-label')
                            || ui.draggable.data('lsd-element-label')
                            || ''),
                        elementIcon: ui.helper.data('lsd-element-icon') || '',
                        point: dropPoint ? {
                            clientX: dropPoint.clientX,
                            clientY: dropPoint.clientY
                        } : null
                    };
                }
            });
        }

        showIframeDropBridge() {
            const $bridge = this.getIframeDropBridge();
            if (!$bridge.length) return;

            $bridge.addClass('is-visible');
        }

        hideIframeDropBridge() {
            if (!this.$iframeDropBridge || !this.$iframeDropBridge.length) return;

            this.$iframeDropBridge.removeClass('is-visible is-active');
        }

        insertNodeAtDropLocation($node, location) {
            if (!$node || !$node.length || !location || !location.$target || !location.$target.length) {
                return false;
            }

            if (location.mode === 'before' && location.$reference && location.$reference.length) {
                $node.insertBefore(location.$reference);
                return true;
            }

            if (location.mode === 'after' && location.$reference && location.$reference.length) {
                $node.insertAfter(location.$reference);
                return true;
            }

            location.$target.append($node);
            return true;
        }

        resolveIframeDropTargetFromHit($hit) {
            if (!$hit || !$hit.length) return null;

            const $containerInner = $hit.closest(this.containerInnerSelector);
            if ($containerInner.length) return { kind: 'container', $target: $containerInner.first() };

            const $container = $hit.closest(this.containerElementSelector);
            if ($container.length) {
                const $containerItem = $container.closest(this.containerSelector);
                const $inner = $containerItem.children(this.containerElementSelector).first().children(this.containerInnerSelector).first();
                if ($inner.length) return { kind: 'container', $target: $inner };
            }

            const $playground = $hit.closest(this.playgroundSelector);
            if ($playground.length) return { kind: 'root', $target: this.ensureCanvasContainer() };

            return null;
        }

        resolveIframeDropTargetByGeometry(iframeDocument, iframeX, iframeY) {
            if (!iframeDocument) return null;

            const compareScore = (left = [], right = []) => {
                const length = Math.max(left.length, right.length);

                for (let index = 0; index < length; index += 1) {
                    const leftValue = left[index] || 0;
                    const rightValue = right[index] || 0;

                    if (leftValue === rightValue) continue;
                    return leftValue < rightValue ? -1 : 1;
                }

                return 0;
            };
            const overflowDistance = (value, min, max) => {
                if (value < min) return min - value;
                if (value > max) return value - max;
                return 0;
            };
            const selectBestCandidate = ($candidates, options = {}) => {
                if (!$candidates || !$candidates.length) return $();

                const horizontalPadding = typeof options.horizontalPadding === 'number' ? options.horizontalPadding : 0;
                const topTolerance = typeof options.topTolerance === 'number' ? options.topTolerance : 0;
                const bottomTolerance = typeof options.bottomTolerance === 'number' ? options.bottomTolerance : 0;
                const depthSelector = (options.depthSelector || '').toString();
                let best = null;

                $candidates.each((index, candidate) => {
                    if (!candidate || typeof candidate.getBoundingClientRect !== 'function') return;

                    const rect = candidate.getBoundingClientRect();
                    if (!rect.width && !rect.height) return;

                    const horizontalOverflow = overflowDistance(
                        iframeX,
                        rect.left - horizontalPadding,
                        rect.right + horizontalPadding
                    );
                    if (horizontalOverflow > 0) return;

                    const verticalOverflow = overflowDistance(
                        iframeY,
                        rect.top - topTolerance,
                        rect.bottom + bottomTolerance
                    );
                    if (verticalOverflow > 0) return;

                    const $candidate = $(candidate);
                    const depth = depthSelector ? $candidate.parents(depthSelector).length : 0;
                    const score = [
                        overflowDistance(iframeY, rect.top, rect.bottom),
                        overflowDistance(iframeX, rect.left, rect.right),
                        -depth,
                        Math.round(rect.width * rect.height),
                    ];

                    if (!best || compareScore(score, best.score) < 0) {
                        best = {
                            $target: $candidate,
                            score
                        };
                    }
                });

                return best ? best.$target : $();
            };
            const $containerTarget = selectBestCandidate($(iframeDocument).find(this.containerInnerSelector), {
                horizontalPadding: 32,
                topTolerance: 20,
                bottomTolerance: 160,
                depthSelector: this.containerSelector
            });

            if ($containerTarget.length) {
                return {
                    kind: 'container',
                    $target: $containerTarget.first()
                };
            }

            const $playground = $(iframeDocument).find(this.playgroundSelector).first();
            const $rootTarget = selectBestCandidate($playground, {
                horizontalPadding: 48,
                topTolerance: 24,
                bottomTolerance: 200
            });

            if ($rootTarget.length) {
                return {
                    kind: 'root',
                    $target: this.ensureCanvasContainer()
                };
            }

            return null;
        }

        resolveIframeDropTarget(point) {
            const iframeEl = this.dom.previewIframeManager && this.dom.previewIframeManager.$iframe
                ? this.dom.previewIframeManager.$iframe.get(0)
                : null;
            const iframeDocument = iframeEl && iframeEl.contentDocument ? iframeEl.contentDocument : null;

            if (!iframeEl || !iframeDocument || !point) {
                return { kind: 'root', $target: this.ensureCanvasContainer() };
            }

            const rect = iframeEl.getBoundingClientRect();
            const iframeX = point.clientX - rect.left;
            const iframeY = point.clientY - rect.top;

            if (iframeX < 0 || iframeY < 0 || iframeX > rect.width || iframeY > rect.height) {
                return { kind: 'root', $target: this.ensureCanvasContainer() };
            }

            const hitNode = iframeDocument.elementFromPoint(iframeX, iframeY);
            const $hit = hitNode ? $(hitNode) : $();
            const directTarget = this.resolveIframeDropTargetFromHit($hit);
            if (directTarget) return directTarget;

            const geometricTarget = this.resolveIframeDropTargetByGeometry(iframeDocument, iframeX, iframeY);
            if (geometricTarget) return geometricTarget;

            return { kind: 'root', $target: this.ensureCanvasContainer() };
        }

        handleIframeSidebarDrop(ui, point, dropLocation = null) {
            const dropData = ui && ui.elementType ? ui : {
                elementType: ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type'),
                elementLabel: (ui.helper.data('lsd-element-label')
                    || ui.draggable.data('lsd-element-label')
                    || ''),
                elementIcon: ui.helper.data('lsd-element-icon') || '',
                point
            };
            const elementType = dropData.elementType || '';
            const elementLabel = (dropData.elementLabel || elementType);
            const elementIcon = dropData.elementIcon || '';

            if (!elementType) return;

            const dropTarget = dropLocation && dropLocation.$target && dropLocation.$target.length
                ? dropLocation
                : (this.resolvePaletteDropLocation(dropData.point) || this.resolveIframeDropTarget(dropData.point));
            const $dropContainer = dropTarget.kind === 'container' ? dropTarget.$target : $();

            if (elementType === 'container') {
                const $row = this.createContainer(elementLabel || 'Container', null, elementIcon);
                if (!$row.length) return;

                if ($dropContainer.length) {
                    this.insertNodeAtDropLocation($row, dropTarget);
                    this.renderInsertedElement($row, 'iframe-bridge-container');
                    this.animateDrop($row);

                    if ($dropContainer.hasClass('ui-sortable')) {
                        $dropContainer.sortable('refresh');
                    } else {
                        this.makeContainerInnerSortable($dropContainer);
                    }

                    this.afterCanvasMutation($row);
                    this.scrollToInsertedElement($row, 'iframe-bridge-container');
                    return;
                }

                const $canvasElements = this.ensureCanvasContainer();
                if (!$canvasElements.length) return;

                this.insertNodeAtDropLocation($row, dropTarget);
                this.renderInsertedElement($row, 'iframe-bridge-root-container');
                this.animateDrop($row);

                if ($canvasElements.hasClass('ui-sortable')) {
                    $canvasElements.sortable('refresh');
                } else {
                    this.makeCanvasSortable($canvasElements);
                }

                this.afterCanvasMutation($row);
                this.scrollToInsertedElement($row, 'iframe-bridge-root-container');
                return;
            }

            if ($dropContainer.length) {
                const $element = this.createCanvasElement(elementType, elementLabel, null, elementIcon);
                if (!$element.length) return;

                this.insertNodeAtDropLocation($element, dropTarget);
                this.renderInsertedElement($element, 'iframe-bridge-element');
                this.animateDrop($element);

                if ($dropContainer.hasClass('ui-sortable')) {
                    $dropContainer.sortable('refresh');
                } else {
                    this.makeContainerInnerSortable($dropContainer);
                }

                this.afterCanvasMutation($element);
                this.scrollToInsertedElement($element, 'iframe-bridge-element');
                return;
            }

            this.addElementInNewContainer(elementType, elementLabel, elementIcon, dropTarget.kind === 'root' ? dropTarget : null);
        }

        // ------------------ Initial render from state ------------------

        renderChildNode(child, $parentInner) {
            if (!child || !$parentInner || !$parentInner.length) return;

            const childState = this.state.getElement(child.id) || {};
            const childType = child.type || childState.type || '';
            const childLabel = childState.label || child.label || childType || '';
            const childIcon = childState.icon || '';

            if (childType === 'container') {
                const $nestedRow = this.createContainer(childLabel || 'Container', child.id, childIcon);
                const $nestedInner = $nestedRow.find(this.containerInnerSelector).first();

                $parentInner.append($nestedRow);

                if ($nestedInner.length && Array.isArray(child.children)) {
                    child.children.forEach((grandChild) => this.renderChildNode(grandChild, $nestedInner));
                }

                this.renderInsertedElement($nestedRow, 'render-state-container');
                return;
            }

            if (!childType) return;

            const $element = this.createCanvasElement(childType, childLabel, child.id, childIcon);
            if (!$element.length) return;

            $parentInner.append($element);
            this.renderInsertedElement($element, 'render-state-element');
        }

        renderFromState() {
            const $container = this.ensureCanvasContainer();
            if (!$container.length) return;

            $container.children(this.containerSelector + ', ' + this.elementBlockSelector).remove();

            if (!this.state.state.layout || !this.state.state.layout.length) {
                this.refreshAllPlaceholders();
                this.syncStructure();
                return;
            }

            this.state.state.layout.forEach((row) => {
                if (!row || !row.id) return;

                const rowState = this.state.getElement(row.id) || {};
                const rowLabel = rowState.label || row.label || 'Container';
                const rowIcon = rowState.icon || '';

                const $row = this.createContainer(rowLabel, row.id, rowIcon);
                const $inner = $row.find(this.containerInnerSelector).first();

                $container.append($row);

                if ($inner.length && Array.isArray(row.children)) {
                    row.children.forEach((child) => this.renderChildNode(child, $inner));
                }

                this.renderInsertedElement($row, 'render-state-root-container');
            });

            this.makeCanvasSortable($container);
            this.refreshAllPlaceholders();
            this.syncStructure();
        }

        // ------------------ Drag & Drop + Selection wiring ------------------

        initDragAndDrop() {
            const $draggables = $(this.draggableItemSelector);
            const self = this;
            const draggableOptions = {
                helper() {
                    return self.buildPaletteDragHelper(self.resolvePaletteButton($(this)));
                },
                appendTo: 'body',
                containment: 'window',
                iframeFix: true,
                scroll: false,
                revert: 'invalid',
                distance: 5,
                cancel: 'input, textarea, select, option',
                start: (event, ui) => {
                    const dragData = self.getPaletteDragData($(event.currentTarget));
                    const elementType = dragData.elementType;
                    const elementLabel = dragData.elementLabel;
                    const elementIcon = dragData.elementIcon;
                    if (!elementType) {
                        return false;
                    }

                    ui.helper.data('lsd-element-type', elementType);
                    ui.helper.data('lsd-element-label', elementLabel || elementType);
                    ui.helper.data('lsd-element-icon', elementIcon);
                    self.setDraggingState(true);
                    self.setSidebarDraggingState(true);
                    self.suppressPaletteClick = true;
                    self.lastSidebarDragPoint = self.getClientPoint(event);
                    self.showIframeDropBridge();
                },
                drag: (event) => {
                    self.lastSidebarDragPoint = self.getClientPoint(event);
                    self.autoScrollIframeOnDrag(event);
                    self.autoScrollCanvasOnDrag(event);
                },
                stop: (event, ui) => {
                    self.setDraggingState(false);
                    self.setSidebarDraggingState(false);
                    self.hideIframeDropBridge();
                    const pendingIframeDrop = self.pendingIframeDrop;
                    const stopPoint = self.getClientPoint(event) || self.lastSidebarDragPoint || null;
                    self.pendingIframeDrop = null;
                    self.lastSidebarDragPoint = null;
                    const fallbackIframeDrop = (!pendingIframeDrop && stopPoint && self.isPointInsidePreviewIframe(stopPoint))
                        ? {
                            elementType: ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type') || '',
                            elementLabel: (ui.helper.data('lsd-element-label')
                                || ui.draggable.data('lsd-element-label')
                                || ''),
                            elementIcon: ui.helper.data('lsd-element-icon') || '',
                            point: {
                                clientX: stopPoint.clientX,
                                clientY: stopPoint.clientY
                            }
                        }
                        : null;
                    window.setTimeout(() => {
                        if (pendingIframeDrop && pendingIframeDrop.elementType) {
                            self.handleIframeSidebarDrop(pendingIframeDrop, pendingIframeDrop.point || null);
                        } else if (fallbackIframeDrop && fallbackIframeDrop.elementType) {
                            self.handleIframeSidebarDrop(fallbackIframeDrop, fallbackIframeDrop.point || null);
                        }
                        self.suppressPaletteClick = false;
                    }, 0);
                }
            };

            // Legacy duplicate draggable init path kept disabled; the normalized
            // setup below is the only path that should attach palette draggables.
            if (false && $draggables.length && !this.paletteBound) {
                if ($.fn && typeof $draggables.draggable === 'function') {
                    $draggables.draggable({
                    helper() {
                        // Take the whole list item as the drag “box”
                        const $button = self.resolvePaletteButton($(this));
                        const $item = $button.closest('.lsd-template-editor-element-list__item');
                        const $clone = ($item.length ? $item.clone() : $button.clone());
                        const elementType = ($button.data('lsd-element-type') || '').toString();
                        const elementLabel = ($button.data('lsd-element-label') || elementType).toString();
                        const elementIcon = ($button.find('i').attr('class') || '').toString();

                        if (self.$workspace && self.$workspace.hasClass('lsd-template-editor--sidebar-collapsed')) {
                            $clone.addClass('lsd-template-editor-element-list__item--drag-helper');
                        }

                        $clone.css('list-style', 'none');
                        $clone.data('lsd-element-type', elementType);
                        $clone.data('lsd-element-label', elementLabel || elementType);
                        $clone.data('lsd-element-icon', elementIcon);

                        return $clone;
                    },
                    appendTo: 'body',
                    containment: 'window',
                    scroll: false,
                    revert: 'invalid',
                    distance: 5,
                    cancel: 'input, textarea, select, option, a',
                    start: (event, ui) => {
                        const $button = self.resolvePaletteButton($(event.currentTarget));
                        const elementType = ($button.data('lsd-element-type') || '').toString();
                        const elementLabel = ($button.data('lsd-element-label') || elementType).toString();
                        const elementIcon = ($button.find('i').attr('class') || '').toString();
                        const iframeEl = self.dom.previewIframeManager && self.dom.previewIframeManager.$iframe
                            ? self.dom.previewIframeManager.$iframe.get(0)
                            : null;

                        if (!elementType) {
                            return false;
                        }

                        ui.helper.data('lsd-element-type', elementType);
                        ui.helper.data('lsd-element-label', elementLabel || elementType);
                        ui.helper.data('lsd-element-icon', elementIcon);
                        self.setDraggingState(true);
                        self.setSidebarDraggingState(true);
                        self.suppressPaletteClick = true;

                        if (iframeEl) {
                            iframeEl.style.pointerEvents = 'none';
                        }
                    },
                    stop: () => {
                        const iframeEl = self.dom.previewIframeManager && self.dom.previewIframeManager.$iframe
                            ? self.dom.previewIframeManager.$iframe.get(0)
                            : null;

                        self.setDraggingState(false);
                        self.setSidebarDraggingState(false);
                        if (iframeEl) {
                            iframeEl.style.pointerEvents = '';
                        }
                        window.setTimeout(() => {
                            self.suppressPaletteClick = false;
                        }, 0);
                    },
                    drag: (event) => {
                        self.autoScrollIframeOnDrag(event);
                        self.autoScrollCanvasOnDrag(event);
                    }
                    });
                }

                this.paletteBound = true;
            }

            if (!this.paletteClickBound) {
                $(document)
                    .off('click.lsdCanvasAdd', '.lsd-template-editor-element-list__button')
                    .on('click.lsdCanvasAdd', '.lsd-template-editor-element-list__button', (event) => {
                        const $button = $(event.currentTarget);
                        if (!$button.closest('.lsd-template-editor-sidebar').length) return;
                        this.handlePaletteClick(event, $button);
                });
                this.paletteClickBound = true;
            }

            this.initPalettePointerDrag();

            if ($draggables.length && $.fn && typeof $.fn.draggable === 'function') {
                $draggables.each(function () {
                    const $draggable = $(this);
                    if ($draggable.data('ui-draggable')) {
                        $draggable.draggable('destroy');
                    }
                });
                this.paletteBound = true;
            }

            const $canvasElements = this.ensureCanvasContainer();
            this.makeCanvasSortable($canvasElements);
            this.initIframeDropBridge();

            // Existing droppable for containers from sidebar drag
            if ($canvasElements.data('ui-droppable')) {
                try {
                    $canvasElements.droppable('destroy');
                } catch (err) {
                    // noop
                }
            }

            $canvasElements.droppable({
                accept: (el) => {
                    return !!(this.getPaletteElementType($(el)) || ($(el).data('lsd-element-type') || '').toString());
                },
                tolerance: 'pointer',
                over: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.showCanvasDropIndicator(this.getGlobalPlaceholder());
                },
                out: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.hideCanvasDropIndicator(this.getGlobalPlaceholder(), () => this.refreshAllPlaceholders());
                },
                drop: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.hideCanvasDropIndicator(this.getGlobalPlaceholder());

                    const elementType = ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type');
                    const elementLabel = ui.helper.data('lsd-element-label') || elementType || 'Container';
                    const elementIcon = ui.helper.data('lsd-element-icon') || '';

                    if (!elementType || !$canvasElements.length) return;

                    if (elementType !== 'container') {
                        this.setDraggingState(false);
                        this.setSidebarDraggingState(false);
                        this.addElementInNewContainer(elementType, elementLabel, elementIcon);
                        return;
                    }

                    const $row = this.createContainer(elementLabel || 'Container', null, elementIcon);
                    if (!$row.length) return;

                    $canvasElements.append($row);
                    this.renderInsertedElement($row, 'jquery-drop-root-container');
                    this.animateDrop($row);
                    this.setDraggingState(false);
                    this.setSidebarDraggingState(false);

                    if ($canvasElements.hasClass('ui-sortable')) {
                        $canvasElements.sortable('refresh');
                    } else {
                        this.makeCanvasSortable($canvasElements);
                    }

                    this.afterCanvasMutation($row);
                    this.scrollToInsertedElement($row, 'jquery-drop-root-container');
                }
            });

            this.initGlobalPlaceholderDropzone();
        }

        initGlobalPlaceholderDropzone() {
            const $placeholder = this.getGlobalPlaceholder();
            const $canvasElements = this.ensureCanvasContainer();

            if (!$placeholder.length || !$canvasElements.length || typeof $placeholder.droppable !== 'function') return;

            if ($placeholder.data('ui-droppable')) {
                try {
                    $placeholder.droppable('destroy');
                } catch (err) {
                    // noop
                }
            }

            $placeholder.droppable({
                greedy: true,
                accept: (el) => {
                    return !!(this.getPaletteElementType($(el)) || ($(el).data('lsd-element-type') || '').toString());
                },
                tolerance: 'pointer',
                over: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.showCanvasDropIndicator($placeholder);
                },
                out: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.hideCanvasDropIndicator($placeholder, () => this.refreshAllPlaceholders());
                },
                drop: (event, ui) => {
                    if (!this.isSidebarDrag(ui)) return;
                    this.hideCanvasDropIndicator($placeholder);

                    const elementType = ui.helper.data('lsd-element-type') || ui.draggable.data('lsd-element-type');
                    const elementLabel = (ui.helper.data('lsd-element-label')
                        || ui.draggable.data('lsd-element-label')
                        || elementType);
                    const elementIcon = ui.helper.data('lsd-element-icon') || '';

                    if (!elementType) return;

                    if (elementType === 'container') {
                        const $row = this.createContainer(elementLabel || 'Container', null, elementIcon);
                        if (!$row.length) return;

                        $canvasElements.append($row);
                        this.renderInsertedElement($row, 'jquery-drop-placeholder-container');
                        this.animateDrop($row);
                        this.setDraggingState(false);
                        this.setSidebarDraggingState(false);

                        if ($canvasElements.hasClass('ui-sortable')) {
                            $canvasElements.sortable('refresh');
                        } else {
                            this.makeCanvasSortable($canvasElements);
                        }

                        this.afterCanvasMutation($row);
                        this.scrollToInsertedElement($row, 'jquery-drop-placeholder-container');
                        return;
                    }

                    this.setDraggingState(false);
                    this.setSidebarDraggingState(false);
                    this.addElementInNewContainer(elementType, elementLabel, elementIcon);
                }
            });
        }

        initSelection() {
            if (!this.$canvas.length || this.selectionBound) return;

            const selectionTargets = '[data-lsd-element-id]';
            const controlSelector = '[data-lsd-element-control]';
            const placeholderSelector = '.lsd-template-editor-canvas__placeholder, .lsd-template-editor-canvas__container-placeholder';

            this.$canvas.on('click', controlSelector, (event) => {
                event.preventDefault();
                event.stopPropagation();

                const $control = $(event.currentTarget);
                const action = $control.data('lsd-element-control');

                const $target = $control.closest(selectionTargets);

                if (!$target.length) return;

                if (action === 'settings') {
                    this.selectElement($target);
                } else if (action === 'remove') {
                    let $removalTarget = $target;
                    const elementType = $target.data('lsd-element-type');

                    if (elementType === 'container') {
                        const $wrapper = $target.closest(this.containerSelector);
                        if ($wrapper.length) $removalTarget = $wrapper;
                    }

                    if (elementType !== 'container' && $removalTarget.closest(this.elementBlockSelector).length) {
                        $removalTarget = $removalTarget.closest(this.elementBlockSelector);
                    }

                    const finalizeRemoval = () => {
                        const idsToRemove = this.collectElementIds($removalTarget);

                        idsToRemove.forEach((id) => {
                            this.state.removeElement(id);
                            this.state.purgeElementFromLayout(id);
                        });

                        const $parentInner = $removalTarget.closest(this.containerInnerSelector);
                        $removalTarget.remove();
                        if ($parentInner.length) this.toggleContainerPlaceholder($parentInner);
                        this.afterCanvasMutation();
                    };

                    this.clearSelection();
                    this.animateRemoval($removalTarget, finalizeRemoval);
                }
            });

            this.$canvas.on('mousedown.lsdStructureDismiss', () => {
                if (this.$body && this.$body.hasClass('lsd-template-editor--dragging')) return;
                this.requestStructurePanelClose();
            });

            this.$canvas.on('click', (event) => {
                const $containerHit = $(event.target).closest(this.containerElementSelector);
                const $elementHit = $(event.target).closest(this.elementWrapperSelector);
                let $target = $();

                if ($elementHit.length) {
                    $target = $elementHit.closest(this.elementBlockSelector);
                } else if ($containerHit.length) {
                    $target = $containerHit.closest(this.containerSelector);
                }

                if (!$target.length) {
                    this.clearSelection();
                    return;
                }

                this.selectElement($target);
            });

            this.$canvas.on('click', placeholderSelector, (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.clearSelection();
            });

            this.selectionBound = true;
        }

        autoSaveOnSubmit() {
            if (!this.$postForm || !this.$postForm.length) return;

            this.$postForm.on('submit', () => {
                if (!this.state.isLayoutDirty()) return;
                this.persist(true);
                this.state.clearLayoutDirty();
            });
        }
    }

    /**
     * ------------------------------------------------------------------------
     * ResponsivePreview – toggles preview widths for device icons
     * ------------------------------------------------------------------------
     */
    class ResponsivePreview {
        constructor(dom) {
            this.dom = dom || {};
            this.$controls = dom.$responsiveControls || $();
            this.$canvas = dom.$workspaceCanvas || $();
            this.$body = dom.$body || $('body');
            this.state = dom.templateState || null;
            this.defaultMode = 'desktop';
            this.currentMode = this.defaultMode;

            if (!this.$controls.length || !this.$canvas.length) return;

            this.init();
        }

        getCanvasTarget() {
            if (this.dom.canvasManager && this.dom.canvasManager.$canvas && this.dom.canvasManager.$canvas.length) {
                return this.dom.canvasManager.$canvas;
            }

            return this.$canvas;
        }

        applyCanvasFrameWidth($canvasTarget, mode) {
            if (!$canvasTarget || !$canvasTarget.length) return;

            const widths = {
                desktop: '',
                tablet: '70%',
                mobile: '40%'
            };
            const maxWidth = widths[mode] || '';
            const $surfaces = $canvasTarget.find('.lsd-template-editor-canvas__playground, .lsd-template-editor-canvas__preview-frame');

            if (!$surfaces.length) return;

            $surfaces.each(function () {
                this.style.maxWidth = maxWidth;
            });
        }

        applyMode(mode) {
            const targetMode = mode || this.defaultMode;
            const $canvasTarget = this.getCanvasTarget();
            const $workspace = this.dom.$workspace && this.dom.$workspace.length
                ? this.dom.$workspace
                : $('.lsd-template-editor-workspace');
            const iframeWindow = (this.dom.previewIframeManager && typeof this.dom.previewIframeManager.getIframeWindow === 'function')
                ? this.dom.previewIframeManager.getIframeWindow()
                : null;
            const iframeDocument = iframeWindow && iframeWindow.document ? iframeWindow.document : null;

            debugTemplateBuilder('applyMode', targetMode);

            this.$controls.removeClass('lsd-sub-tabs-active');
            this.$controls
            .filter('[data-lsd-responsive="' + targetMode + '"]')
            .addClass('lsd-sub-tabs-active');

            $canvasTarget.attr('data-lsd-responsive', targetMode);
            this.applyCanvasFrameWidth($canvasTarget, targetMode);
            if (this.$body && this.$body.length) {
                this.$body.attr('data-lsd-responsive', targetMode);
            }
            if (iframeDocument) {
                $(iframeDocument.documentElement).attr('data-lsd-responsive', targetMode);
                $(iframeDocument.body).attr('data-lsd-responsive', targetMode);
            }
            this.currentMode = targetMode;
            this.applyVisibility(targetMode);
            $workspace.trigger('lsd-template-responsive-mode', [targetMode]);
            $(document).trigger('lsd-template-responsive-mode', [targetMode]);
            if (
                this.dom.previewIframeManager &&
                typeof this.dom.previewIframeManager.refreshIframeNiceScroll === 'function'
            ) {
                this.dom.previewIframeManager.refreshIframeNiceScroll();
            }
        }

        applyVisibility(mode) {
            if (!this.state || !this.state.state || !this.state.state.elements) return;

            const $canvasTarget = this.getCanvasTarget();
            const elements = this.state.state.elements;
            const flagMap = {
                desktop: 'hide_on_desktop',
                tablet: 'hide_on_tablet',
                mobile: 'hide_on_mobile',
            };

            const flagKey = flagMap[mode] || '';
            if (!flagKey) return;
            
            const isEnabled = (value) => {
                if (value === null || typeof value === 'undefined') return false;
                if (typeof value === 'boolean') return value;
                const str = String(value).trim();
                return str !== '' && str !== '0';
            };

            Object.keys(elements).forEach((elementId) => {
                const element = elements[elementId] || {};
                const advanced = element.settings && element.settings.advanced ? element.settings.advanced : {};
                const hide = isEnabled(advanced[flagKey]);

                const $container = $canvasTarget.find('.lsd-template-editor-canvas__container-item[data-lsd-element-id="' + elementId + '"]');
                const $elementBlock = $canvasTarget.find('.lsd-template-editor-canvas__element-block[data-lsd-element-id="' + elementId + '"]');
                const $target = $container.length ? $container : $elementBlock;

                if ($target.length)
                {
                    $target.toggleClass('lsd-template-element--responsive-hidden', !!hide);
                }
            });
        }

        init() {
            const $active = this.$controls.filter('.lsd-sub-tabs-active').first();
            const startingMode = $active.length
                ? ($active.data('lsd-responsive') || this.defaultMode)
                : this.defaultMode;

            this.applyMode(startingMode);

            this.$controls.on('click', (event) => {
                event.preventDefault();
                const $control = $(event.currentTarget).closest('[data-lsd-responsive]');
                const mode = $control.data('lsd-responsive') || this.defaultMode;
                this.applyMode(mode);
            });

            $(document).on('lsd-template-responsive-refresh', () => {
                this.applyVisibility(this.currentMode || this.defaultMode);
            });
        }
    }

    class ResponsiveSettingsFields {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$controls = dom.$responsiveControls || $();
            this.currentMode = 'desktop';

            if (!this.$workspace.length) return;

            this.init();
        }

        getMode() {
            const $active = this.$controls.filter('.lsd-sub-tabs-active').first();
            return $active.length ? ($active.data('lsd-responsive') || 'desktop') : 'desktop';
        }

        iconClassForMode(mode) {
            if (mode === 'tablet') return 'listdom-icon wbli-tablet';
            if (mode === 'mobile') return 'listdom-icon wbli-mobile';
            return 'listdom-icon wbli-screen';
        }

        apply(mode) {
            const targetMode = mode || this.getMode();
            this.currentMode = targetMode;

            const $fields = this.$workspace.find('[data-lsd-responsive-field]');
            if ($fields.length) {
                $fields.each(function () {
                    const $field = $(this);
                    const fieldMode = $field.data('lsd-responsive') || 'desktop';
                    $field.toggleClass('lsd-util-hide', fieldMode !== targetMode);
                });
            }

            const iconClass = this.iconClassForMode(targetMode);
            this.$workspace.find('[data-lsd-responsive-icon] i').attr('class', iconClass);
        }

        init() {
            this.apply(this.getMode());

            $(document).on('lsd-template-responsive-mode', (event, mode) => {
                this.apply(mode || this.getMode());
            });

            $(document).on('lsd-template-responsive-settings-refresh', () => {
                this.apply(this.currentMode || this.getMode());
            });
        }
    }

    /**
     * ------------------------------------------------------------------------
     * TitleSync, TypeSync, StatusSync, SavePublishBindings – as tiny classes
     * ------------------------------------------------------------------------
     */
    class TitleSync {
        constructor(dom) {
            const { $titleInput, $titleWrapper, $settingsTitleInput } = dom;
            this.$titleInput = $titleInput;
            this.$settingsTitleInput = $settingsTitleInput;
            this.$titleDisplay = $titleWrapper.find('h4');
            this.placeholder = $titleWrapper.data('placeholder') || '';

            this.init();
        }

        syncTitle() {
            if (!this.$titleDisplay.length) return;
            const value = this.$titleInput.length ? $.trim(this.$titleInput.val()) : '';
            this.$titleDisplay.text(value || this.placeholder || '');
        }

        syncSettingsTitleField() {
            if (!this.$titleInput.length || !this.$settingsTitleInput.length) return;
            const value = this.$titleInput.val();
            if (this.$settingsTitleInput.val() !== value) {
                this.$settingsTitleInput.val(value);
            }
        }

        syncTitleFromSettings() {
            if (!this.$titleInput.length || !this.$settingsTitleInput.length) return;
            const value = this.$settingsTitleInput.val();
            if (this.$titleInput.val() === value) return;
            this.$titleInput.val(value).trigger('input');
        }

        init() {
            if (this.$titleInput.length) {
                this.$titleInput.on('input change', () => {
                    this.syncTitle();
                    this.syncSettingsTitleField();
                });
            }

            this.syncTitle();
            this.syncSettingsTitleField();

            if (this.$titleInput.length && this.$settingsTitleInput.length) {
                this.$settingsTitleInput.on('input change', () => this.syncTitleFromSettings());
            }
        }
    }

    class TypeSync {
        constructor(dom) {
            const { $typeSelect, $typeMetaboxField, $settingsTypeSelect } = dom;
            this.$typeSelect = $typeSelect;
            this.$typeMetaboxField = $typeMetaboxField;
            this.$settingsTypeSelect = $settingsTypeSelect;

            if (!$typeSelect.length && !$typeMetaboxField.length && !$settingsTypeSelect.length) return;
            this.init();
        }

        getPrimaryTypeValue() {
            if (this.$typeSelect.length) return this.$typeSelect.val();
            if (this.$settingsTypeSelect.length) return this.$settingsTypeSelect.val();
            return '';
        }

        resolveMetaboxTypeValue() {
            if (!this.$typeMetaboxField.length) return '';
            const $firstField = this.$typeMetaboxField.first();
            if ($firstField.is('select')) return $firstField.val();
            const $checked = this.$typeMetaboxField.filter(':checked');
            return $checked.length ? $checked.val() : '';
        }

        syncTypeToMetabox() {
            if (!this.$typeMetaboxField.length) return;
            const typeValue = this.getPrimaryTypeValue();
            if (!typeValue) return;

            const $firstField = this.$typeMetaboxField.first();
            if ($firstField.is('select')) {
                if ($firstField.val() !== typeValue) $firstField.val(typeValue);
                return;
            }

            this.$typeMetaboxField.each((index, element) => {
                const $field = $(element);
                const shouldCheck = $field.val() === typeValue;
                if ($field.prop('checked') !== shouldCheck) {
                    $field.prop('checked', shouldCheck);
                }
            });
        }

        syncSettingsTypeField() {
            if (!this.$typeSelect.length || !this.$settingsTypeSelect.length) return;
            const typeValue = this.$typeSelect.val();
            if (this.$settingsTypeSelect.val() !== typeValue) {
                this.$settingsTypeSelect.val(typeValue);
            }
        }

        init() {
            if (this.$typeSelect.length) {
                this.$typeSelect.on('change', () => {
                    this.syncTypeToMetabox();
                    this.syncSettingsTypeField();
                });

                this.syncTypeToMetabox();
                this.syncSettingsTypeField();
            }

            if (this.$typeMetaboxField.length && (this.$typeSelect.length || this.$settingsTypeSelect.length)) {
                this.$typeMetaboxField.on('change', () => {
                    const metaboxValue = this.resolveMetaboxTypeValue();
                    if (!metaboxValue) return;

                    const $target = this.$typeSelect.length ? this.$typeSelect : this.$settingsTypeSelect;
                    if (!$target.length || $target.val() === metaboxValue) return;
                    $target.val(metaboxValue).trigger('change');
                });
            }

            if (this.$typeSelect.length && this.$settingsTypeSelect.length) {
                this.$settingsTypeSelect.on('change', () => {
                    const value = this.$settingsTypeSelect.val();
                    if (this.$typeSelect.val() === value) return;
                    this.$typeSelect.val(value).trigger('change');
                });
            }
        }
    }

    class StatusSync {
        constructor(dom) {
            const {
                $settingsStatusSelect,
                $postStatusInput,
                $hiddenPostStatusInput,
                $postStatusDisplay,
                $wpStatusDropdown
            } = dom;

            this.$settingsStatusSelect = $settingsStatusSelect;
            this.$postStatusInput = $postStatusInput;
            this.$hiddenPostStatusInput = $hiddenPostStatusInput;
            this.$postStatusDisplay = $postStatusDisplay;
            this.$wpStatusDropdown = $wpStatusDropdown;

            if (!$settingsStatusSelect.length && !$wpStatusDropdown.length) return;
            this.init();
        }

        resolveCurrentStatusValue() {
            if (this.$wpStatusDropdown.length && this.$wpStatusDropdown.val()) return this.$wpStatusDropdown.val();
            if (this.$postStatusInput.length && this.$postStatusInput.val()) return this.$postStatusInput.val();
            if (this.$hiddenPostStatusInput.length && this.$hiddenPostStatusInput.val()) return this.$hiddenPostStatusInput.val();
            if (this.$settingsStatusSelect.length) {
                return this.$settingsStatusSelect.attr('data-current-status') || '';
            }
            return '';
        }

        syncSettingsStatusField() {
            if (!this.$settingsStatusSelect.length) return;
            const statusValue = this.resolveCurrentStatusValue();
            if (statusValue && this.$settingsStatusSelect.val() !== statusValue) {
                this.$settingsStatusSelect.val(statusValue);
            }
        }

        updateWordPressStatusFields() {
            if (!this.$settingsStatusSelect.length) return;
            const value = this.$settingsStatusSelect.val();
            if (this.$wpStatusDropdown.length) this.$wpStatusDropdown.val(value);
            if (this.$postStatusInput.length) this.$postStatusInput.val(value);
            if (this.$hiddenPostStatusInput.length) this.$hiddenPostStatusInput.val(value);
            if (this.$postStatusDisplay.length) {
                const label = this.$settingsStatusSelect.find('option:selected').text().trim();
                if (label) this.$postStatusDisplay.text(label);
            }
        }

        init() {
            if (this.$settingsStatusSelect.length) {
                this.$settingsStatusSelect.on('change', () => this.updateWordPressStatusFields());
                this.syncSettingsStatusField();
            }

            if (this.$wpStatusDropdown.length) {
                this.$wpStatusDropdown.on('change', () => this.syncSettingsStatusField());
            }
        }
    }

    class SettingsSaveBindings {
        constructor(dom) {
            const {
                $settingsSaveBtn,
                $settingsTitleInput,
                $settingsTypeSelect,
                $settingsStatusSelect,
                $settingsPageTemplateSelect,
                $postIdInput,
                $settingsBack,
                $workspace,
                $autoSaveToggle
            } = dom;

            this.dom = dom || {};
            this.$settingsSaveBtn   = $settingsSaveBtn;
            this.$settingsTitleInput = $settingsTitleInput;
            this.$settingsTypeSelect = $settingsTypeSelect;
            this.$settingsStatusSelect = $settingsStatusSelect;
            this.$settingsPageTemplateSelect = $settingsPageTemplateSelect;
            this.$postIdInput       = $postIdInput;
            this.$settingsBack      = $settingsBack;
            this.$workspace         = $workspace;
            this.$autoSaveToggle    = $autoSaveToggle;

            this.settingsRequest    = null;

            if (!this.$settingsSaveBtn || !this.$settingsSaveBtn.length) return;
            this.init();
        }

        getLayoutStatePayload() {
            if (!this.dom || !this.dom.canvasManager || !this.dom.canvasManager.state) return '';

            const canvasManager = this.dom.canvasManager;

            if (typeof canvasManager.refreshLayoutState === 'function') {
                canvasManager.refreshLayoutState();
            }

            if (canvasManager.state && typeof canvasManager.state.writeToField === 'function') {
                canvasManager.state.writeToField();
            }

            if (typeof canvasManager.getSnapshotString === 'function') {
                return canvasManager.getSnapshotString();
            }

            if (canvasManager.state && typeof canvasManager.state.serialize === 'function') {
                return JSON.stringify(canvasManager.state.serialize());
            }

            return '';
        }

        saveSettings($triggerButton) {
            const ajaxUrl = AjaxHelpers.getAjaxUrl();
            const $button = ($triggerButton && $triggerButton.length) ? $triggerButton : this.$settingsSaveBtn;

            const $nonceField = (this.$workspace && this.$workspace.length)
                ? this.$workspace.find('input[name="lsd-save-template-settings"]').first()
                : $('input[name="lsd-save-template-settings"]').first();
            const nonce = AjaxHelpers.getNonce($nonceField);

            let postId = (this.$postIdInput && this.$postIdInput.length)
                ? (parseInt(this.$postIdInput.val(), 10) || 0)
                : 0;

            if (!postId && this.$workspace && this.$workspace.length) {
                postId = parseInt(this.$workspace.data('lsdTemplateId'), 10) || 0;
            }

            const successMessage      = $button.data('lsd-settings-toast');
            const defaultErrorMessage = $button.data('lsd-settings-error-toast');

            const $previewInputs = (this.$workspace && this.$workspace.length)
                ? this.$workspace.find('input[name="lsd_template_preview_listing[]"], input[name="lsd_template_preview_listing"], select[name="lsd_template_preview_listing"]')
                : $('input[name="lsd_template_preview_listing[]"], input[name="lsd_template_preview_listing"], select[name="lsd_template_preview_listing"]');

            const previewListing = ($previewInputs && $previewInputs.length)
                ? (parseInt($previewInputs.first().val(), 10) || 0)
                : 0;
            const layoutState = this.getLayoutStatePayload();

            // Prepare loader for button
            const loader = new ListdomButtonLoader($button, 'before');

            // Basic guard: if anything critical is missing, show error and bail
            if (!ajaxUrl || !nonce || !postId) {
                if (typeof window.listdom_toastify === 'function') {
                    listdom_toastify(defaultErrorMessage, 'lsd-error', {
                        position: 'lsd-bottom-right',
                        hideTime: 4000,
                        showClose: true,
                        progress: true
                    });
                }
                return;
            }

            // Abort previous request if still running
            if (this.settingsRequest && typeof this.settingsRequest.abort === 'function') {
                this.settingsRequest.abort();
            }

            const postData = {
                title: this.$settingsTitleInput.val(),
                template_type: this.$settingsTypeSelect.val(),
                status: this.$settingsStatusSelect.val(),
                page_template: this.$settingsPageTemplateSelect && this.$settingsPageTemplateSelect.length
                    ? this.$settingsPageTemplateSelect.val()
                    : 'default',
                autosave_status: (
                    this.$autoSaveToggle &&
                    this.$autoSaveToggle.length &&
                    this.$autoSaveToggle.is(':checked')
                ) ? 1 : 0,
                preview_listing: previewListing,
            };

            if (layoutState !== '') {
                postData.lsd_template_layout = layoutState;
            }

            this.settingsRequest = $.ajax({
                url: ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'lsd_save_template_settings',
                    post_id: postId,
                    post: postData,
                    _wpnonce: nonce,
                },
                beforeSend: () => {
                    // Optional custom loading text, fallback to current label
                    const loadingText = $button.data('lsd-loading-text') || $button.text();
                    loader.start(loadingText);
                },
            })

            .done((response) => {
                if (!response || !response.success) {
                    const finalErrorMsg = response && response.message ? response.message : defaultErrorMessage;

                    if (typeof window.listdom_toastify === 'function') {
                        listdom_toastify(finalErrorMsg, 'lsd-error', {
                            position: 'lsd-bottom-right',
                            hideTime: 4000,
                            showClose: true,
                            progress: true
                        });
                    }
                    return;
                }

                if (typeof window.listdom_toastify === 'function' && successMessage) {
                    listdom_toastify(successMessage, 'lsd-success', {
                        position: 'lsd-bottom-right',
                        hideTime: 4000,
                        showClose: true,
                        progress: true
                    });
                }

                if (
                    response.data &&
                    response.data.layout_saved &&
                    this.dom.canvasManager &&
                    this.dom.canvasManager.state &&
                    typeof this.dom.canvasManager.state.clearLayoutDirty === 'function'
                ) {
                    this.dom.canvasManager.state.clearLayoutDirty();
                }

                // Inline "closeSettingsPanel" logic (no extra method)
                if (this.$workspace && this.$workspace.length) {
                    this.$workspace.trigger('lsd-template-settings-saved', [response.data || {}]);
                } else if (this.$settingsBack && this.$settingsBack.length) {
                    this.$settingsBack.trigger('click');
                }

                if (this.dom.canvasManager && typeof this.dom.canvasManager.refreshAllPreviews === 'function') {
                    this.dom.canvasManager.refreshAllPreviews('template-settings-saved');
                }

                if (this.dom.canvasManager && typeof this.dom.canvasManager.getSelectedElementId === 'function') {
                    const selectedId = this.dom.canvasManager.getSelectedElementId();
                    if (selectedId) {
                        window.setTimeout(() => {
                            SidebarTabManager.activate(SidebarTabManager.getLastOptionsTabKey());
                        }, 0);
                    }
                }
            })

            .fail(() => {
                if (typeof window.listdom_toastify === 'function') {
                    listdom_toastify(defaultErrorMessage, 'lsd-error', {
                        position: 'lsd-bottom-right',
                        hideTime: 4000,
                        showClose: true,
                        progress: true
                    });
                }
            })

            .always(() => {
                loader.stop();
                this.settingsRequest = null;
            });
        }

        init() {
            this.$settingsSaveBtn.on('click', (event) => {
                event.preventDefault();
                this.saveSettings($(event.currentTarget));
            });
        }
    }

    class SavePublishBindings {
        constructor(dom) {
            this.dom = dom || {};

            const {
                $saveBtn,
                $publishBtn,
                $saveTarget,
                $publishTarget,
                $settingsStatusSelect,
                $postForm,
                $workspace
            } = this.dom;

            this.$saveBtn = $saveBtn;
            this.$publishBtn = $publishBtn;
            this.$saveTarget = $saveTarget;
            this.$publishTarget = $publishTarget;
            this.$settingsStatusSelect = $settingsStatusSelect;
            this.$postForm = $postForm;
            this.$workspace = $workspace;
            this.canPublish = this.$workspace.length && parseInt(this.$workspace.attr('data-lsd-can-publish'), 10) === 1;

            this.init();
            this.showToastAfterReload();
        }

        readDataAttr($element, attrName) {
            if (!$element || !$element.length) return '';

            return $element.attr(attrName) || '';
        }

        forceStatus(status) {
            if (!this.$settingsStatusSelect.length) return;
            this.$settingsStatusSelect.attr('data-current-status', status);
            this.$settingsStatusSelect.val(status).trigger('change');
        }

        getEffectiveSaveTarget() {
            if (this.$saveTarget.length) return this.$saveTarget;
            if (this.$publishTarget.length) return this.$publishTarget;
            return $();
        }

        isPublishActionStatus(status) {
            return ['publish', 'future', 'private'].includes((status || '').toString());
        }

        resolveCurrentStatus() {
            const $wpStatusDropdown = $('#post-status-select').find('select');
            const $postStatusInput = $('#post_status');
            const $hiddenPostStatusInput = $('#hidden_post_status');

            if ($wpStatusDropdown.length && $wpStatusDropdown.val()) return $wpStatusDropdown.val();

            if ($postStatusInput.length && $postStatusInput.val()) return $postStatusInput.val();

            if ($hiddenPostStatusInput.length && $hiddenPostStatusInput.val()) return $hiddenPostStatusInput.val();

            if (this.$settingsStatusSelect.length) {
                const statusValue = this.$settingsStatusSelect.attr('data-current-status') || this.$settingsStatusSelect.val() || '';
                if (statusValue) return statusValue;
            }

            return '';
        }

        resolveSaveStatus() {
            return !this.canPublish && this.resolveCurrentStatus() === 'pending' ? 'pending' : 'draft';
        }

        syncCurrentStatus(status) {
            if (!status || !this.$settingsStatusSelect.length) return;

            this.$settingsStatusSelect.attr('data-current-status', status);

            if (this.$settingsStatusSelect.val() !== status) {
                this.$settingsStatusSelect.val(status);
            }

            this.$settingsStatusSelect.trigger('change');
        }

        updateButtonLabel($button, label) {
            if (!$button || !$button.length || !label) return;

            const $label = $button.find('[data-lsd-button-label]').first();
            if ($label.length) $label.text(label);
            else $button.contents().filter(function () {
                return this.nodeType === 3;
            }).last().replaceWith(' ' + label);
        }

        updateSaveButtonState() {
            if (!this.$saveBtn.length) return;

            const saveStatus = this.resolveSaveStatus();
            const label = saveStatus === 'pending'
                ? this.readDataAttr(this.$saveBtn, 'data-lsd-label-pending')
                : this.readDataAttr(this.$saveBtn, 'data-lsd-label-draft');
            const toast = saveStatus === 'pending'
                ? this.readDataAttr(this.$saveBtn, 'data-lsd-toast-pending')
                : this.readDataAttr(this.$saveBtn, 'data-lsd-toast-draft');

            this.$saveBtn.attr('data-lsd-save-status', saveStatus);

            if (toast) {
                this.$saveBtn.attr('data-lsd-toast', toast);
            }

            this.updateButtonLabel(this.$saveBtn, label);
        }

        updatePublishButtonState() {
            if (!this.$publishBtn.length) return;

            const label = this.isPublishActionStatus(this.resolveCurrentStatus())
                ? this.readDataAttr(this.$publishBtn, 'data-lsd-label-update')
                : this.readDataAttr(this.$publishBtn, 'data-lsd-label-publish');

            this.updateButtonLabel(this.$publishBtn, label);
        }

        refreshActionButtons() {
            this.updateSaveButtonState();
            this.updatePublishButtonState();
        }

        // Decide which toast message to show based on new/update
        getToastMessage($button, mode, currentStatus) {
            const msgNew = this.readDataAttr($button, 'data-lsd-toast-new');
            const msgUpdate = this.readDataAttr($button, 'data-lsd-toast-update');
            const msgPending = this.readDataAttr($button, 'data-lsd-toast-pending');
            const fallback = this.readDataAttr($button, 'data-lsd-toast');

            let isUpdate = false;

            if (mode === 'publish') isUpdate = this.isPublishActionStatus(currentStatus);
            else if (mode === 'pending') isUpdate = currentStatus === 'pending';
            else if (mode === 'draft') isUpdate = (currentStatus === 'draft' || currentStatus === 'auto-draft');

            if (isUpdate) return msgUpdate || msgNew || fallback;

            // New action
            if (mode === 'publish') return msgNew || fallback;
            if (mode === 'pending') return msgPending || msgUpdate || fallback;

            // mode === 'draft'
            return msgNew || fallback;
        }

        queueToast(mode, message) {
            localStorage.setItem('lsd-toast-mode', mode);
            localStorage.setItem('lsd-toast-message', message || '');
        }

        showToastAfterReload() {
            const mode = localStorage.getItem('lsd-toast-mode');
            const message = localStorage.getItem('lsd-toast-message');
            const redirectUrl = this.popPendingRedirect();

            if (mode) {
                if (message && typeof window.listdom_toastify === 'function') {
                    listdom_toastify(message, 'lsd-success', {
                        position: 'lsd-bottom-right',
                        hideTime: 4000,
                        showClose: true,
                        progress: true
                    });
                }

                // One-time only
                localStorage.removeItem('lsd-toast-mode');
                localStorage.removeItem('lsd-toast-message');
            }

            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        }

        disableValidation() {
            if (!this.$postForm || !this.$postForm.length) return;
            const form = this.$postForm.get(0);
            if (form) form.noValidate = true;
            this.$postForm.attr('novalidate', 'novalidate');
        }

        setPendingRedirect(url) {
            if (!url) return;
            if (window.sessionStorage) {
                sessionStorage.setItem('lsd-post-save-redirect', url);
            }
        }

        popPendingRedirect() {
            if (!window.sessionStorage) return '';
            const url = sessionStorage.getItem('lsd-post-save-redirect');
            if (url) sessionStorage.removeItem('lsd-post-save-redirect');
            return url || '';
        }

        persistCanvasState() {
            if (!this.dom || !this.dom.canvasManager || !this.dom.canvasManager.state) return;

            const canvasManager = this.dom.canvasManager;
            if (typeof canvasManager.persist === 'function') {
                canvasManager.persist(true);
            }

            if (
                canvasManager.state &&
                typeof canvasManager.state.isLayoutDirty === 'function' &&
                canvasManager.state.isLayoutDirty() &&
                typeof canvasManager.state.clearLayoutDirty === 'function'
            ) {
                canvasManager.state.clearLayoutDirty();
            }
        }

        submitWithStatus(status, redirectUrl) {
            const isPublishAction = this.isPublishActionStatus(status);
            const $target = isPublishAction ? this.$publishTarget : this.getEffectiveSaveTarget();
            if (!$target.length) return;

            const $button = isPublishAction ? this.$publishBtn : this.$saveBtn;
            const currentStatus = this.resolveCurrentStatus();
            const actionMode = isPublishAction ? 'publish' : status;
            const message = $button && $button.length ? this.getToastMessage($button, actionMode, currentStatus) : '';

            this.disableValidation();
            this.persistCanvasState();
            this.forceStatus(status);
            this.queueToast(actionMode, message);
            if (redirectUrl) this.setPendingRedirect(redirectUrl);

            $target.trigger('click');
        }

        resolvePublishStatus() {
            const currentStatus = this.resolveCurrentStatus();
            return this.isPublishActionStatus(currentStatus) ? currentStatus : 'publish';
        }

        init() {
            const $effectiveSaveTarget = this.getEffectiveSaveTarget();

            // SAVE ? Draft (new or update)
            if (this.$saveBtn.length && $effectiveSaveTarget.length) {
                this.$saveBtn.on('click', (event) => {
                    event.preventDefault();
                    this.submitWithStatus(this.$saveBtn.attr('data-lsd-save-status') || this.resolveSaveStatus());
                });
            }

            // PUBLISH ? Publish (new or update)
            if (this.$publishBtn.length && this.$publishTarget.length) {
                this.$publishBtn.on('click', (event) => {
                    event.preventDefault();
                    this.submitWithStatus(this.resolvePublishStatus());
                });
            }

            if (this.$settingsStatusSelect.length) {
                this.$settingsStatusSelect.on('change.lsdSavePublishBindings', () => this.refreshActionButtons());
            }

            if (this.$workspace.length) {
                this.$workspace
                .off('lsd-template-settings-saved.lsdSavePublishBindings')
                .on('lsd-template-settings-saved.lsdSavePublishBindings', (event, data) => {
                    if (data && data.status) {
                        this.syncCurrentStatus(data.status);
                        return;
                    }

                    this.refreshActionButtons();
                });
            }

            this.refreshActionButtons();
        }
    }

    class UnsavedChangesGuard {
        constructor(dom) {
            this.dom = dom || {};
            this.$workspace = this.dom.$workspace || $('.lsd-template-editor-workspace');
            this.$postForm = this.dom.$postForm || $('#post');
            this.state = this.dom.templateState || null;

            this.isDirty = false;
            this.isNavigating = false;
            this.isTracking = false;

            const msg = this.$workspace.data('lsdUnsavedMessage');
            const confirmText = this.$workspace.data('lsdUnsavedConfirmText');
            const cancelText = this.$workspace.data('lsdUnsavedCancelText');
            const resolveWorkspaceText = (value, fallback = '') => {
                if (value === null || typeof value === 'undefined') return fallback;

                const normalized = value.toString().trim();
                return normalized !== '' ? normalized : fallback;
            };

            this.message = resolveWorkspaceText(msg, 'You have unsaved changes. Do you want to leave without saving?');
            this.confirmText = resolveWorkspaceText(confirmText, 'Leave');
            this.cancelText = resolveWorkspaceText(cancelText, 'Cancel');

            if (!this.$workspace.length) return;
            this.init();
        }

        allowNavigate() {
            this.isNavigating = true;
            window.setTimeout(() => {
                this.isNavigating = false;
            }, 2000);
        }

        shouldBlock() {
            if (this.isNavigating) return false;
            if (this.isDirty) return true;
            if (this.state && typeof this.state.isLayoutDirty === 'function') {
                return this.state.isLayoutDirty();
            }
            return false;
        }

        confirmNavigate(onConfirm, onCancel) {
            const hasToastify = typeof window.listdom_toastify === 'function';
            const hasToast = typeof window.ListdomToast === 'function';

            if (hasToastify || hasToast) {
                if (this.confirmToast) return;

                const config = {
                    confirm: {
                        confirmText: this.confirmText,
                        cancelText: this.cancelText,
                        onConfirm: () => {
                            this.confirmToast = null;
                            if (typeof onConfirm === 'function') onConfirm();
                        },
                        onCancel: () => {
                            this.confirmToast = null;
                            if (typeof onCancel === 'function') onCancel();
                        },
                        onCloseOverlay: () => {
                            this.confirmToast = null;
                            if (typeof onCancel === 'function') onCancel();
                        }
                    },
                    position: 'lsd-center-center'
                };

                this.confirmToast = hasToastify
                    ? listdom_toastify(this.message, 'lsd-confirm', config)
                    : new window.ListdomToast(this.message, {
                        type: 'lsd-confirm',
                        confirm: config.confirm
                    });
                return;
            }

            if (window.confirm(this.message)) {
                if (typeof onConfirm === 'function') onConfirm();
            } else if (typeof onCancel === 'function') {
                onCancel();
            }
        }

        init() {
            const markDirty = () => {
                if (!this.isTracking) return;
                this.isDirty = true;
            };

            const fieldSelector = 'input, select, textarea';

            this.$workspace.on('input.lsdUnsaved change.lsdUnsaved', fieldSelector, (event) => {
                const $target = $(event.target);
                if ($target.is('[data-lsd-unsaved-ignore]')) return;
                if ($target.is('[data-lsd-template-layout]')) return;
                markDirty();
            });

            this.$workspace.on('lsd-template-settings-saved.lsdUnsaved', () => {
                this.isDirty = false;
            });

            if (this.$postForm && this.$postForm.length) {
                this.$postForm.on('submit.lsdUnsaved', () => {
                    this.allowNavigate();
                });
            }

            this.$workspace.on('click.lsdUnsavedLink', 'a', (event) => {
                const $link = $(event.currentTarget);
                if ($link.is('[data-lsd-unsaved-ignore]')) return;
                if ($link.is('[data-listdom-lightbox]')) return;

                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                const href = ($link.attr('href') || '').trim();
                if (!href || href === '#' || href.toLowerCase().startsWith('javascript:')) return;

                const target = ($link.attr('target') || '').toLowerCase();
                if (target === '_blank') return;

                if (!this.shouldBlock()) return;

                event.preventDefault();
                event.stopPropagation();

                this.confirmNavigate(() => {
                    this.allowNavigate();
                    window.location.href = href;
                });
            });

            window.addEventListener('beforeunload', (event) => {
                if (!this.shouldBlock()) return;
                event.preventDefault();
                event.returnValue = this.message;
                return this.message;
            });

            window.setTimeout(() => {
                this.isTracking = true;
            }, 0);
        }
    }

    class SettingsUnsavedAlert {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$panels = this.$workspace.find('#lsd-tab-switcher-general-content, #lsd-tab-switcher-layout-content');
            this.$alerts = this.$workspace.find('[data-lsd-template-settings-alert]');
            this.isDirty = false;

            if (!this.$workspace.length || !this.$panels.length || !this.$alerts.length) return;
            this.init();
        }

        setDirty(isDirty) {
            this.isDirty = !!isDirty;
            this.$alerts.toggleClass('lsd-util-hide', !this.isDirty);
        }

        init() {
            const fieldSelector = 'input, select, textarea';

            this.$panels.on('input.lsdSettingsDirty change.lsdSettingsDirty', fieldSelector, (event) => {
                const $target = $(event.target);
                if ($target.is('[data-lsd-unsaved-ignore]')) return;
                this.setDirty(true);
            });

            this.$workspace.on('lsd-template-settings-saved.lsdSettingsDirty', () => {
                this.setDirty(false);
            });
        }
    }

    class ElementListManager {
        constructor(dom) {
            this.dom = dom || {};
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$panel = this.$workspace.find('#lsd-tab-switcher-elements-content');
            this.$listContainer = this.$panel.find('[data-lsd-template-elements-list]').first();
            this.$typeSelect = dom.$typeSelect || $();
            this.$settingsTypeSelect = dom.$settingsTypeSelect || $();
            this.$search = this.$panel.find('.lsd-template-editor-element-search__input').first();

            this.request = null;
            this.refreshTimer = null;
            this.lastType = null;

            if (!this.$workspace.length || !this.$panel.length || !this.$listContainer.length) return;
            this.init();
        }

        queueRefresh(templateType) {
            const nextType = (templateType || '').toString();
            if (!nextType) return;
            if (this.lastType === nextType) return;
            this.lastType = nextType;

            if (this.refreshTimer) window.clearTimeout(this.refreshTimer);
            this.refreshTimer = window.setTimeout(() => {
                this.refreshTimer = null;
                this.refreshList(nextType);
            }, 120);
        }

        refreshList(templateType) {
            const ajaxUrl = AjaxHelpers.getAjaxUrl();
            const $nonceField = $('input[name="lsd-template-element-settings"]').first();
            const nonce = AjaxHelpers.getNonce($nonceField);

            if (!ajaxUrl || !nonce) return;

            if (this.request && typeof this.request.abort === 'function') {
                this.request.abort();
            }

            this.request = $.ajax({
                url: ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'lsd_template_elements_list',
                    template_type: templateType,
                    _wpnonce: nonce
                }
            })
            .done((response) => {
                if (!response || !response.success || typeof response.content !== 'string') return;

                this.$listContainer.html(response.content);
                if (this.$search.length) this.$search.trigger('input');

                if (this.dom.canvasManager && typeof this.dom.canvasManager.refreshElementPalette === 'function') {
                    this.dom.canvasManager.refreshElementPalette();
                }
            })
            .always(() => {
                this.request = null;
            });
        }

        init() {
            if (this.$typeSelect.length) {
                this.$typeSelect.on('change.lsdElementsList', () => {
                    this.queueRefresh(this.$typeSelect.val());
                });
            }

            if (this.$settingsTypeSelect.length) {
                this.$settingsTypeSelect.on('change.lsdElementsList', () => {
                    this.queueRefresh(this.$settingsTypeSelect.val());
                });
            }

            this.$workspace.on('lsd-template-settings-saved.lsdElementsList', (event, data) => {
                const typeValue = data && data.template_type
                    ? data.template_type
                    : (this.$settingsTypeSelect.val() || this.$typeSelect.val());
                this.queueRefresh(typeValue);
            });
        }
    }

    class PreviewLinkGuard {
        constructor(dom, savePublishBindings) {
            this.dom = dom || {};
            this.$canvas = this.dom.$workspaceCanvas || $('[data-lsd-template-editor-canvas]');
            this.$workspace = this.dom.$workspace || this.$canvas.closest('.lsd-template-editor-workspace');
            this.savePublishBindings = savePublishBindings || null;

            if (!this.$canvas.length) return;
            this.bind();
        }

        bind() {
            $(document).on('click.lsdPreviewLinkGuard', '.lsd-template-editor-canvas a', (event) => {
                const $link = $(event.currentTarget);

                if ($link.is('[data-lsd-element-control]') || $link.closest('.lsd-template-editor-canvas__controls').length) {
                    return;
                }

                const href = ($link.attr('href') || '').trim();
                if (!href || href === '#' || href.toLowerCase().startsWith('javascript:')) return;

                event.preventDefault();
                event.stopPropagation();
            });

            $(document).on('submit.lsdPreviewLinkGuard', '.lsd-template-editor-canvas form', (event) => {
                event.preventDefault();
                event.stopPropagation();
            });
        }
    }

    class AutoSaveToggle {
        constructor(dom) {
            const { $autoSaveToggle, $autoSaveStatus, $workspace, $settingsToggle } = dom;

            this.$toggle = $autoSaveToggle;
            this.$status = $autoSaveStatus;
            this.$workspace = $workspace;
            this.$settingsToggle = $settingsToggle;

            const statusVal = (this.$status && this.$status.length)
                ? parseInt(this.$status.data('lsdAutosaveStatus'), 10) || 0
                : 0;

            this.currentEnabled = statusVal === 1;

            this.strings = {
                active: (this.$status && this.$status.length ? this.$status.data('lsdAutosaveActiveText') : '') || 'Auto Save Active',
                inactive: (this.$status && this.$status.length ? this.$status.data('lsdAutosaveInactiveText') : '') || 'Auto Save Not Active'
            };

            this.init();
        }

        updateStatusLabel(enabled) {
            if (!this.$status || !this.$status.length) return;

            const text = enabled ? this.strings.active : this.strings.inactive;

            this.$status
            .toggleClass('lsd-template-editor-auto-save-status--disabled', !enabled)
            .attr('data-lsd-autosave-status', enabled ? '1' : '0')
            .attr('data-default-text', text);

            this.$status.find('.lsd-admin-subtitle-tiny').text(text);
        }

        applyStatus(enabled) {
            this.currentEnabled = !!enabled;

            if (this.$toggle && this.$toggle.length) {
                this.$toggle.prop('checked', this.currentEnabled);
            }

            this.updateStatusLabel(this.currentEnabled);
        }

        init() {
            this.bindSettingsLink();

            if (!this.$toggle || !this.$toggle.length) return;

            this.applyStatus(this.currentEnabled);

            this.$toggle.on('change', () => {
                const nextState = this.$toggle.is(':checked');
                this.applyStatus(nextState);
            });

            if (this.$workspace && this.$workspace.length) {
                this.$workspace.on('lsd-template-settings-saved', (event, data) => {
                    if (!data || typeof data.autosave_enabled === 'undefined') return;

                    this.applyStatus(!!data.autosave_enabled);
                });
            }
        }

        bindSettingsLink() {
            if (!this.$status || !this.$status.length || !this.$status.is('[data-lsd-autosave-link]')) return;

            const openSettings = () => {
                if (this.$workspace && this.$workspace.length) {
                    this.$workspace.trigger('lsd-template-settings-mode', [true]);
                }

                if (typeof SidebarTabManager !== 'undefined' && SidebarTabManager && typeof SidebarTabManager.activate === 'function') {
                    SidebarTabManager.activate('general');
                }

                if (this.$toggle && this.$toggle.length) {
                    const node = this.$toggle.get(0);
                    if (node && typeof node.scrollIntoView === 'function') {
                        node.scrollIntoView({ block: 'center' });
                    }
                    this.$toggle.trigger('focus');
                }
            };

            this.$status
            .off('click.lsdAutoSaveLink')
            .on('click.lsdAutoSaveLink', (event) => {
                event.preventDefault();
                openSettings();
            });

            this.$status
            .off('keydown.lsdAutoSaveLink')
            .on('keydown.lsdAutoSaveLink', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openSettings();
                }
            });
        }
    }

    class LayoutTabGuard {
        constructor(dom) {
            const { $workspace, $typeSelect, $settingsTypeSelect } = dom;
            this.$workspace = $workspace;
            this.$typeSelect = $typeSelect;
            this.$settingsTypeSelect = $settingsTypeSelect;
            this.hiddenTypes = ['listing_card', 'info_window'];

            if (!this.$workspace.length) return;
            this.init();
        }

        getCurrentType() {
            if (this.$settingsTypeSelect && this.$settingsTypeSelect.length) {
                return this.$settingsTypeSelect.val();
            }
            if (this.$typeSelect && this.$typeSelect.length) {
                return this.$typeSelect.val();
            }
            return '';
        }

        hasSelection() {
            if (!this.$workspace || !this.$workspace.length) return false;
            const selectedId = (this.$workspace.attr('data-lsd-current-element-id') || '').toString();
            if (selectedId) return true;
            return this.$workspace.find('.lsd-template-editor-canvas .is-selected').length > 0;
        }

        apply(type) {
            const value = (type || '').toString();
            const shouldDisable = this.hiddenTypes.includes(value);

            this.$workspace.toggleClass('lsd-template-editor--layout-disabled', shouldDisable);

            const $menu = SidebarTabManager.getMenu();
            const $layoutTab = $menu.find('li[data-tab="layout"]');
            const $layoutPanel = $('#lsd-tab-switcher-layout-content');

            $layoutTab
                .toggleClass('lsd-util-hide', shouldDisable)
                .attr('aria-hidden', shouldDisable ? 'true' : 'false')
                .find('a')
                .attr('tabindex', shouldDisable ? '-1' : '0');

            $layoutPanel
                .toggleClass('lsd-util-hide', shouldDisable)
                .attr('aria-hidden', shouldDisable ? 'true' : 'false');

            const hasSelection = this.hasSelection();
            const settingsMode = this.$workspace.hasClass('lsd-template-editor--settings-mode');
            SidebarTabManager.applyVisibility(hasSelection, settingsMode);
        }

        init() {
            this.apply(this.getCurrentType());

            this.$workspace.on('lsd-template-settings-saved.lsdLayoutTabGuard', (event, data) => {
                const type = data && data.template_type ? data.template_type : this.getCurrentType();
                this.apply(type);
            });
        }
    }

    /**
     * ------------------------------------------------------------------------
     * SettingsModeToggle – left menu / settings tab mode
     * ------------------------------------------------------------------------
     */
    class SettingsModeToggle {
        constructor(dom) {
            const { $workspace, $settingsToggle, $settingsBack } = dom;
            this.$workspace = $workspace;
            this.$settingsToggle = $settingsToggle;
            this.$settingsBack = $settingsBack;
            this.state = dom.templateState || null;

            if (!this.$workspace.length || !$settingsToggle.length) return;
            this.init();
        }

        init() {
            const settingsModeClass = 'lsd-template-editor--settings-mode';

            const $tabs = $('[data-lsd-template-editor-menu="left-default"] li');
            const $settingsTabs = $tabs.filter('[data-tab="general"], [data-tab="layout"]');
            const $defaultTabs = $tabs.not('[data-tab="general"], [data-tab="layout"]');
            const $sidebarToggle = $('[data-lsd-template-action="sidebar-toggle"]');
            const setSidebarCollapsed = (collapsed) => {
                this.$workspace.toggleClass('lsd-template-editor--sidebar-collapsed', collapsed);
                if ($sidebarToggle.length) {
                    $sidebarToggle.attr('aria-pressed', collapsed ? 'true' : 'false');
                }
            };

            const activateTab = ($li) => {
                $li.addClass('lsd-sub-tabs-active')
                .siblings().removeClass('lsd-sub-tabs-active');

                $li.find('a').trigger('click');
            };

            const toggleSettings = (enable) => {
                const active = !!enable;

                if (active) {
                    if (this.$workspace.hasClass('lsd-template-editor--sidebar-collapsed')) {
                        setSidebarCollapsed(false);
                    }
                }

                this.$workspace.toggleClass(settingsModeClass, active);
                this.$settingsToggle.attr('aria-pressed', active ? 'true' : 'false');

                SidebarTabManager.applyVisibility(this.hasSelection(), active);

                if (active) {
                    activateTab($settingsTabs.filter('[data-tab="general"]').first());
                } else {
                    activateTab($defaultTabs.first());
                }
            };

            this.$settingsToggle.on('click', (e) => {
                e.preventDefault();
                toggleSettings(!this.$workspace.hasClass(settingsModeClass));
            });

            $settingsTabs.on('click', () => {
                if (!this.$workspace.hasClass(settingsModeClass)) toggleSettings(true);
            });

            $defaultTabs.on('click', () => {
                if (this.$workspace.hasClass(settingsModeClass)) toggleSettings(false);
            });

            this.$workspace.on('lsd-template-settings-saved', () => {
                if (this.$workspace.hasClass(settingsModeClass)) toggleSettings(false);
            });

            if (this.$settingsBack && this.$settingsBack.length) {
                this.$settingsBack.on('click', (e) => {
                    e.preventDefault();

                    if (this.$workspace.hasClass(settingsModeClass)) {
                        toggleSettings(false);
                    }
                });
            }

            this.$workspace.on('lsd-template-settings-mode', (event, enabled) => {
                toggleSettings(!!enabled);
            });

            SidebarTabManager.applyVisibility(this.hasSelection(), this.$workspace.hasClass(settingsModeClass));
        }

        hasSelection() {
            if (this.state && this.state.currentElementId) return true;
            if (!this.$workspace || !this.$workspace.length) return false;
            return this.$workspace.find('.lsd-template-editor-canvas .is-selected').length > 0;
        }
    }

    class SidebarCollapseToggle {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$toggle = $('[data-lsd-template-action="sidebar-toggle"]');
            this.collapsedClass = 'lsd-template-editor--sidebar-collapsed';

            if (!this.$workspace.length || !this.$toggle.length) return;
            this.init();
        }

        hasSelection() {
            if (!this.$workspace || !this.$workspace.length) return false;
            const selectedId = (this.$workspace.attr('data-lsd-current-element-id') || '').toString();
            if (selectedId) return true;
            return this.$workspace.find('.lsd-template-editor-canvas .is-selected').length > 0;
        }

        init() {
            this.$toggle.on('click', (event) => {
                event.preventDefault();
                const next = !this.$workspace.hasClass(this.collapsedClass);
                this.$workspace.toggleClass(this.collapsedClass, next);
                this.$toggle.attr('aria-pressed', next ? 'true' : 'false');
                if (next && this.$workspace.hasClass('lsd-template-editor--settings-mode')) {
                    this.$workspace.trigger('lsd-template-settings-mode', [false]);
                    SidebarTabManager.activate('elements');
                }
                const isSettingsMode = this.$workspace.hasClass('lsd-template-editor--settings-mode');
                const hasSelection = this.hasSelection();
                SidebarTabManager.applyVisibility(hasSelection, isSettingsMode);
            });
        }
    }

    class ElementsAddToggle {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$toggle = $('[data-lsd-template-action="add"]');

            if (!this.$toggle.length) return;
            this.init();
        }

        init() {
            this.$toggle.on('click', (event) => {
                event.preventDefault();
                const $workspace = SidebarTabManager.getWorkspace();

                if ($workspace.hasClass('lsd-template-editor--settings-mode')) {
                    $workspace.trigger('lsd-template-settings-mode', [false]);
                }

                if ($workspace.hasClass('lsd-template-editor--sidebar-collapsed')) {
                    const $toggle = $('[data-lsd-template-action="sidebar-toggle"]');
                    if ($toggle.length) $toggle.trigger('click');
                }

                $workspace.trigger('lsd-template-clear-selection');

                SidebarTabManager.activate('elements');
            });
        }
    }

    class NiceScrollManager {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$targets = this.$workspace.find('.lsd-template-editor-sidebar-panel, .lsd-template-editor-canvas');
            this.options = {
                cursorcolor: 'rgba(0, 0, 0, 0.45)',
                cursorwidth: 3,
                cursorborder: '0',
                cursorborderradius: 8,
                cursoropacitymin: 0,
                cursoropacitymax: 0.6,
                autohidemode: 'leave',
                railpadding: { top: 6, right: 4, left: 0, bottom: 6 },
                horizrailenabled: false,
                railalign: 'right',
                railvalign: 'top',
                background: 'transparent',
                zindex: 9999
            };

            if (!this.$workspace.length || !this.$targets.length || typeof $.fn.niceScroll !== 'function') return;
            this.init();
        }

        apply() {
            this.$targets.each((index, el) => {
                const $el = $(el);
                const instance = $el.getNiceScroll();
                if (instance && instance.length) {
                    instance.remove();
                }
                $el.niceScroll(this.options);
            });
        }

        refresh() {
            this.$targets.each((index, el) => {
                const $el = $(el);
                const instance = $el.getNiceScroll();
                if (instance && instance.length) {
                    instance.resize();
                }
            });
        }

        init() {
            this.apply();

            const scheduleRefresh = () => {
                window.setTimeout(() => this.refresh(), 0);
            };

            $(window).on('resize.lsdNiceScroll', scheduleRefresh);
            this.$workspace.on('click.lsdNiceScroll', '.lsd-tab-switcher li', scheduleRefresh);
            this.$workspace.on('lsd-template-settings-saved.lsdNiceScroll lsd-template-responsive-refresh.lsdNiceScroll', scheduleRefresh);

            // Auto-refresh when DOM changes (accordion open/close, dynamic inserts, etc.)
            const observer = new MutationObserver(() => {
                // debounce a bit to avoid spamming resize during animations
                clearTimeout(this._nsMO);
                this._nsMO = setTimeout(() => this.refresh(), 50);
            });

            // Observe inside the scrolling areas (sidebar/canvas)
            this.$targets.each((i, el) => {
                observer.observe(el, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['style', 'class'] // common during accordion animations
                });
            });

            this._nsObserver = observer;
        }
    }

    /**
     * ------------------------------------------------------------------------
     * TemplateModal & DropdownToggle – keep your existing ones
     * ------------------------------------------------------------------------
     *
     */
    class ElementSearchFilter {
        constructor(dom) {
            this.$workspace = dom.$workspace || $('.lsd-template-editor-workspace');
            this.$panel = this.$workspace.find('#lsd-tab-switcher-elements-content');
            this.$search = this.$workspace.find('.lsd-template-editor-element-search__input').first();

            if (!this.$panel.length || !this.$search.length) return;

            this.$search.on('input', () => this.apply());
            this.$search.on('keydown', (event) => {
                if (event.key !== 'Escape') return;
                this.$search.val('');
                this.apply();
            });

            this.apply();
        }

        apply() {
            const query = (this.$search.val() || '').toString().trim().toLowerCase();
            const hasQuery = query.length > 0;
            const $sections = this.$panel.find('.lsd-template-editor-section');

            $sections.each(function () {
                const $section = $(this);
                const $items = $section.find('.lsd-template-editor-element-list__item');
                let visibleCount = 0;

                $items.each(function () {
                    const $item = $(this);
                    const $button = $item.find('.lsd-template-editor-element-list__button').first();
                    const label = ($button.data('lsd-element-label') || $button.text() || '').toString().toLowerCase();
                    const matches = !hasQuery || label.indexOf(query) !== -1;
                    $item.toggleClass('lsd-util-hide', !matches);
                    if (matches) visibleCount += 1;
                });

                $section.toggleClass('lsd-util-hide', hasQuery && visibleCount === 0);
            });
        }
    }

    class TemplateModal {
        static init() {
            const $modal = $('.lsd-template-modal').first();
            if (!$modal.length) return;

            const $backdrop = $modal.find('.lsd-template-modal-backdrop');
            const $form = $('#lsd-template-modal-form');
            if (!$form.length) return;

            const $error = $modal.find('.lsd-template-modal-error');
            const $nameField = $('#lsd-template-name');
            const getTypeRadios = () => $form.find('input[name="lsd_template_type"]');
            const $submitButton = $form.find('button[type="submit"]');
            const ajaxUrl = AjaxHelpers.getAjaxUrl();
            const modalId = $modal.attr('id') || '';

            const messages = {
                required: $form.data('requiredMessage'),
                failed:   $form.data('failedMessage')
            };

            const hideError = () => {
                $error.removeClass('is-visible').text('');
            };

            const showError = (message) => {
                const text = message;
                if (!text)
                {
                    hideError();
                    return;
                }
                $error.text(text).addClass('is-visible');
            };

            const resetForm = () => {
                if ($form.length && typeof $form.get(0).reset === 'function') $form.get(0).reset();
                ensureTypeSelection();
            };

            const ensureTypeSelection = () => {
                const $radios = getTypeRadios();
                if ($radios.length && !$radios.filter(':checked').length) $radios.first().prop('checked', true);
            };

            const getSelectedType = () => {
                const $selected = getTypeRadios().filter(':checked');
                return $selected.length ? $selected.val() : '';
            };

            const openModal = () => {
                hideError();
                ensureTypeSelection();
                $modal.addClass('is-open').attr('aria-hidden', 'false');
                if ($nameField.length) $nameField.trigger('focus');
            };

            const closeModal = () => {
                $modal.removeClass('is-open').attr('aria-hidden', 'true');
                hideError();
                resetForm();
            };

            // Open modal trigger
            $(document).on('click', '.lsd-open-template-modal', function (event) {
                const target = $(this).data('modal-target');
                if (target && target !== modalId) return;

                event.preventDefault();
                openModal();
            });

            // Close buttons
            $modal.on('click', '.lsd-template-modal-close, .lsd-template-modal-cancel', function (event) {
                event.preventDefault();
                closeModal();
            });

            // Click outside to close
            $backdrop.on('click', function (event) {
                if ($(event.target).is('.lsd-template-modal-backdrop')) closeModal();
            });

            // Change type clears error
            $form.on('change', 'input[name="lsd_template_type"]', hideError);

            // ESC to close
            $(document).on('keyup', function (event) {
                if (event.key === 'Escape' && $modal.hasClass('is-open')) closeModal();
            });

            $form.on('submit', function (event) {
                event.preventDefault();

                // Nonce now comes from the modal hidden field
                const $nonceField = $form.find('input[name="nonce"], input[name="lsd_template_nonce"]').first();
                const nonce = AjaxHelpers.getNonce($nonceField, (window.lsd && window.lsd.nonce) ? window.lsd.nonce : '');

                const title = jQuery.trim($nameField.val());
                const type  = getSelectedType();

                if (!title) {
                    showError(messages.required);
                    $nameField.trigger('focus');
                    return;
                }

                hideError();
                const loading = new ListdomButtonLoader($submitButton);
                loading.start($submitButton.data('lsd-loading-text') || $submitButton.text().trim());

                const payload = {
                    action: 'lsd_create_template',
                    nonce,
                    title,
                    type
                };

                if (!ajaxUrl || !nonce) {
                    showError(messages.failed);
                    loading.stop();
                    return;
                }

                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: payload
                })
                .done((response) => {

                    // Handle both string and object just in case
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            showError(messages.failed);
                            return;
                        }
                    }

                    const redirectUrl = response && response.editUrl ? response.editUrl : (response && response.redirectUrl ? response.redirectUrl : '');

                    if (response && response.success && redirectUrl) {
                        window.location.href = redirectUrl;
                        return;
                    }

                    const errorMessage = response && response.message ? response.message : messages.failed;
                    showError(errorMessage);
                })
                .fail((xhr, status, error) => {
                    const errorMessage = xhr && xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : messages.failed;
                    showError(errorMessage);
                })
                .always(() => {
                    loading.stop();
                });
            });

            // Delete / trash confirm (unchanged)
            $(document).on('click', '[data-lsd-template-delete]', function (event) {
                const $link = $(this);
                const href = $link.attr('href') || '';
                if (!href) return;

                const cfg = {
                    message: $link.data('lsd-template-confirm-message'),
                    confirmText: $link.data('lsd-template-confirm-text'),
                    cancelText: $link.data('lsd-template-cancel-text'),
                };
                const hasToast =
                    (typeof window.listdom_toastify === 'function') ||
                    (typeof window.ListdomToast === 'function');

                if (!cfg || !hasToast) return;

                event.preventDefault();
                event.stopPropagation();

                const message = cfg.message || '';
                const confirmText = cfg.confirmText || 'OK';
                const cancelText = cfg.cancelText || 'Cancel';

                const handleCancel = () => {};

                const confirmOptions = {
                    confirm: {
                        confirmText,
                        cancelText,
                        onConfirm: () => { window.location.href = href; },
                        onCancel: handleCancel,
                        onCloseOverlay: handleCancel
                    },
                    position: 'lsd-center-center'
                };

                if (typeof window.listdom_toastify === 'function') {
                    listdom_toastify(message, 'lsd-confirm', confirmOptions);
                    return;
                }

                new window.ListdomToast(message, {
                    type: 'lsd-confirm',
                    confirm: confirmOptions.confirm
                });
            });
        }
    }

    class DropdownToggle {
        static init() {
            $(document).ready(function () {
                $('.lsd-dropdown-toggle').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const $dropdown = $(this).parent('.lsd-template-card-dropdown');

                    $('.lsd-template-card-dropdown')
                    .not($dropdown)
                    .removeClass('open')
                    .find('.lsd-dropdown-toggle')
                    .attr('aria-expanded', 'false');

                    $dropdown.toggleClass('open');
                    const isOpen = $dropdown.hasClass('open');
                    $(this).attr('aria-expanded', isOpen ? 'true' : 'false');
                });

                $(document).on('click', function () {
                    $('.lsd-template-card-dropdown')
                    .removeClass('open')
                    .find('.lsd-dropdown-toggle')
                    .attr('aria-expanded', 'false');
                });
            });
        }
    }

    /**
     * ------------------------------------------------------------------------
     * Bootstraps
     * ------------------------------------------------------------------------
     */
    $(document).ready(function () {
        TemplateEditorCore.init();
        TemplateModal.init();
        DropdownToggle.init();

        function lsdRefreshTemplateCardPreviews() {
            const $previews = $('.lsd-template-card-preview-inner');
            if (!$previews.length) return;

            $previews.each(function () {
                const $inner = $(this);
                const $thumb = $inner.closest('.lsd-template-card-thumb');
                if (!$thumb.length) return;

                const thumbEl = $thumb.get(0);
                $thumb.removeClass('is-ready');
                const revealTimer = $thumb.data('lsdPreviewRevealTimer');
                if (revealTimer) window.clearTimeout(revealTimer);

                const thumbStyles = window.getComputedStyle(thumbEl);
                const previewWidth = parseFloat(thumbStyles.getPropertyValue('--lsd-template-preview-width')) || 1200;
                const previewHeight = parseFloat(thumbStyles.getPropertyValue('--lsd-template-preview-height')) || 210;
                const thumbWidth = $thumb.innerWidth();
                const naturalHeight = $inner.get(0).offsetHeight || 0;

                if (!thumbWidth || !previewWidth || !previewHeight || !naturalHeight) return;

                const scale = thumbWidth / previewWidth;
                const renderedHeight = naturalHeight * scale;
                const maxTranslate = Math.min(0, previewHeight - renderedHeight);

                thumbEl.style.setProperty('--lsd-template-preview-scale', scale.toFixed(4));
                thumbEl.style.setProperty('--lsd-template-preview-translate', '0px');
                thumbEl.style.setProperty('--lsd-template-preview-translate-max', `${maxTranslate.toFixed(2)}px`);
                const timerId = window.setTimeout(() => {
                    $thumb.addClass('is-ready');
                    $thumb.removeData('lsdPreviewRevealTimer');
                }, 1000);
                $thumb.data('lsdPreviewRevealTimer', timerId);
            });
        }

        lsdRefreshTemplateCardPreviews();

        let lsdPreviewResizeTimer = null;
        $(window).on('resize', function () {
            if (lsdPreviewResizeTimer) window.clearTimeout(lsdPreviewResizeTimer);
            lsdPreviewResizeTimer = window.setTimeout(lsdRefreshTemplateCardPreviews, 150);
        });

        document.addEventListener('load', function (event) {
            const target = event.target;
            if (!target || target.tagName !== 'IMG') return;
            if (!$(target).closest('.lsd-template-card-preview-inner').length) return;
            lsdRefreshTemplateCardPreviews();
        }, true);

        function lsdInitAccordions()
        {
            // Hide only panels that are NOT marked open by PHP
            $('.lsd-accordion-panel').not('.lsd-accordion-open').hide();

            // Deactivate only titles that are NOT active
            $('.lsd-accordion-title').not('.lsd-accordion-active').removeClass('lsd-accordion-active');
        }

        lsdInitAccordions();

        // If accordions are injected via AJAX / preview renders
        $(document).ajaxComplete(lsdInitAccordions);
        $(document).ajaxComplete(function () {
            initRepeaters($(document));
            initIconPickers($(document));
        });

        $(document).on('click', '.lsd-accordion-title', function () {
            const $title = $(this);
            const target = $title.data('lsd-accordion-target');
            const $panel  = $(target);

            if (!$panel.length) return;

            const isOpen = $title.hasClass('lsd-accordion-active');

            if (isOpen) {
                $title.removeClass('lsd-accordion-active');
                $panel
                .removeClass('lsd-accordion-open')
                .stop(true, true)
                .slideUp(250);
            } else {
                $title.addClass('lsd-accordion-active');
                $panel
                .addClass('lsd-accordion-open')
                .stop(true, true)
                .slideDown(250);
            }
        });
    });
})(jQuery, window, document);

(function ($) {
    'use strict';

    function normalizeConditionValue(value) {
        if (value === true) return '1';
        if (value === false) return '0';
        if (value === null || typeof value === 'undefined') return '';

        value = String(value).trim().toLowerCase();

        if (['true', 'yes', 'on', 'checked'].includes(value)) return '1';
        if (['false', 'no', 'off', 'unchecked'].includes(value)) return '0';

        return value;
    }

    function conditionValueMatches(current, expected) {
        if (Array.isArray(current)) {
            return current.some(function (singleCurrent) {
                return conditionValueMatches(singleCurrent, expected);
            });
        }

        if (Array.isArray(expected)) {
            return expected.some(function (singleExpected) {
                return conditionValueMatches(current, singleExpected);
            });
        }

        return normalizeConditionValue(current) === normalizeConditionValue(expected);
    }

    function getFieldValue($scope, controlId, $conditionItem) {
        let $field = $();

        const $repeaterItem = ($conditionItem && $conditionItem.length)
            ? $conditionItem.closest('[data-lsd-repeater-item]')
            : $();

        if ($repeaterItem.length) {
            $field = $repeaterItem.find('[data-lsd-control-id="' + controlId + '"]').first();
        }

        if (!$field.length) {
            $field = $scope.find('[data-lsd-control-id="' + controlId + '"]').first();
        }

        if (!$field.length) {
            $field = $('[data-lsd-control-id="' + controlId + '"]').first();
        }

        if (!$field.length) return '';

        const $select = $field.find('select').first();

        if ($select.length) {
            const value = $select.val();
            return value === null || typeof value === 'undefined' ? '' : value;
        }

        const $radio = $field.find('input[type="radio"]').first();

        if ($radio.length) {
            const name = $radio.attr('name');
            if (!name) return '';

            const $checkedInRepeater = $repeaterItem.length
                ? $repeaterItem.find('input[name="' + name + '"]:checked').first()
                : $();

            if ($checkedInRepeater.length) return $checkedInRepeater.val();

            const $checkedInScope = $scope.find('input[name="' + name + '"]:checked').first();
            if ($checkedInScope.length) return $checkedInScope.val();

            return $('input[name="' + name + '"]:checked').first().val() || '';
        }

        const $checkboxes = $field.find('input[type="checkbox"]');

        if ($checkboxes.length) {
            const $checked = $checkboxes.filter(':checked').first();
            return $checked.length ? ($checked.val() || '1') : '0';
        }

        const $input = $field.find('input:not([type="hidden"]), textarea').first();
        if ($input.length) return $input.val();

        const $hidden = $field.find('input[type="hidden"]').last();
        if ($hidden.length) return $hidden.val();

        return '';
    }

    function conditionMatches($scope, condition, $conditionItem) {
        const relation = normalizeConditionValue(condition.relation || 'AND');
        const isOr = relation === 'or';

        let hasCondition = false;
        let matchedAny = false;

        for (const controlId in condition) {
            if (!Object.prototype.hasOwnProperty.call(condition, controlId)) continue;
            if (controlId === 'relation') continue;

            hasCondition = true;

            const expected = condition[controlId];
            const current = getFieldValue($scope, controlId, $conditionItem);
            const matched = conditionValueMatches(current, expected);

            if (isOr) {
                if (matched) {
                    matchedAny = true;
                    break;
                }
            } else if (!matched) {
                return false;
            }
        }

        if (!hasCondition) return true;

        return isOr ? matchedAny : true;
    }

    function getConditionTarget($item) {
        if ($item.is('.lsd-template-editor-settings-section')) return $item;

        if ($item.is('li[data-tab]')) return $item;

        if ($item.is('.lsd-tab-switcher-content')) return $item;

        const $repeaterField = $item.closest('.lsd-template-editor-repeater__field');

        if ($repeaterField.length) return $repeaterField;

        const $row = $item.closest('.lsd-template-editor-settings-panel__row');

        if ($row.length) return $row;

        return $item;
    }

    function activateFirstVisibleSettingsTabs($scope) {
        $scope = ($scope && $scope.length) ? $scope : $(document);

        $scope.find('.lsd-template-editor-settings-tabs[data-for]').each(function () {
            const $switcher = $(this);
            const contentSelector = $switcher.data('for');

            if (!contentSelector) return;

            const $panelWrapper = $switcher.closest('.lsd-template-editor-settings-panel');
            const $allContents = $panelWrapper.length
                ? $panelWrapper.find(contentSelector)
                : $scope.find(contentSelector);

            const hasConditionalTabs =
                $switcher.find('li[data-tab][data-lsd-control-condition]').length > 0 ||
                $allContents.filter('[data-lsd-control-condition]').length > 0;

            if (!hasConditionalTabs) return;

            const $allTabs = $switcher.find('li[data-tab]');
            const $availableTabs = $allTabs.not('[data-lsd-condition-hidden="1"]');

            $allTabs
            .filter('[data-lsd-condition-hidden="1"]')
            .removeClass('lsd-sub-tabs-active');

            $allContents
            .filter('[data-lsd-condition-hidden="1"]')
            .removeClass('lsd-tab-switcher-content-active');

            const $activeTab = $availableTabs.filter('.lsd-sub-tabs-active').first();

            if ($activeTab.length) {
                return;
            }

            const $firstAvailableTab = $availableTabs.first();

            if (!$firstAvailableTab.length) return;

            const firstKey = $firstAvailableTab.attr('data-tab');
            const $firstContent = $allContents.filter('#lsd-tab-switcher-' + firstKey + '-content');

            $allTabs.removeClass('lsd-sub-tabs-active');
            $allContents.removeClass('lsd-tab-switcher-content-active');

            $firstAvailableTab.addClass('lsd-sub-tabs-active');

            if ($firstContent.length && !$firstContent.is('[data-lsd-condition-hidden="1"]')) {
                $firstContent.addClass('lsd-tab-switcher-content-active');
            }
        });
    }

    function applyTemplateBuilderConditions($scope) {
        $scope = ($scope && $scope.length) ? $scope : $(document);

        $scope.find('[data-lsd-control-condition]').each(function () {
            const $item = $(this);
            const $target = getConditionTarget($item);

            let condition = {};

            try {
                condition = JSON.parse($item.attr('data-lsd-control-condition') || '{}');
            } catch (e) {
                condition = {};
            }

            if (conditionMatches($scope, condition, $item)) {
                $target.show().removeAttr('data-lsd-condition-hidden');
            } else {
                $target
                .hide()
                .attr('data-lsd-condition-hidden', '1')
                .removeClass('lsd-sub-tabs-active lsd-tab-switcher-content-active');
            }
        });

        activateFirstVisibleSettingsTabs($scope);
    }

    function scheduleTemplateBuilderConditions($scope) {
        $scope = ($scope && $scope.length) ? $scope : $(document);

        applyTemplateBuilderConditions($scope);

        window.setTimeout(function () {
            applyTemplateBuilderConditions($scope);
        }, 0);

        window.setTimeout(function () {
            applyTemplateBuilderConditions($scope);
        }, 50);

        window.setTimeout(function () {
            applyTemplateBuilderConditions($scope);
        }, 150);
    }

    $(document).on(
        'change input',
        '.lsd-element-editor-options .lsd-template-editor-settings-field select, ' +
        '.lsd-element-editor-options .lsd-template-editor-settings-field input, ' +
        '.lsd-element-editor-options .lsd-template-editor-settings-field textarea',
        function () {
            scheduleTemplateBuilderConditions($(this).closest('.lsd-element-editor-options'));
        }
    );

    $(document).on(
        'click',
        '.lsd-element-editor-options .lsd-template-editor-settings-field, ' +
        '.lsd-element-editor-options .lsd-template-editor-settings-field label, ' +
        '.lsd-element-editor-options .lsd-template-editor-settings-field .lsd-switcher',
        function () {
            scheduleTemplateBuilderConditions($(this).closest('.lsd-element-editor-options'));
        }
    );

    $(document).ready(function () {
        scheduleTemplateBuilderConditions($('.lsd-element-editor-options'));
    });

    $(document).ajaxComplete(function () {
        scheduleTemplateBuilderConditions($('.lsd-element-editor-options'));
    });

    $(document).on(
        'lsd-template-editor-settings-loaded ' +
        'lsd-template-builder-settings-loaded ' +
        'lsd-template-responsive-settings-refresh ' +
        'lsd-template-settings-saved',
        function () {
            scheduleTemplateBuilderConditions($('.lsd-element-editor-options'));
        }
    );

    $(document).on('click', '[data-lsd-repeater-add]', function () {
        const $scope = $(this).closest('.lsd-element-editor-options');

        window.setTimeout(function () {
            scheduleTemplateBuilderConditions($scope);
        }, 0);

        window.setTimeout(function () {
            scheduleTemplateBuilderConditions($scope);
        }, 50);
    });

})(jQuery);
