(function ($, window)
{
    'use strict';

    if (typeof window.ListdomToast === 'function') return;

    class ListdomToast {
        constructor(alert, options = {}) {
            this.alert = alert;
            this.type = options.type || 'lsd-natural';
            this.position = options.position || 'lsd-bottom-right';
            this.icon = options.icon || null;
            this.hideTime = typeof options.hideTime === 'number' ? options.hideTime : 8000;
            this.progress = options.progress !== false;
            this.showClose = options.showClose !== false;
            this.confirm = options.confirm || null;

            this.container = this.getContainer();
            this.toast = this.createToast();
            this.messageEl = this.toast.find('.lsd-toast-message');
            this.autoHideTimer = null;

            this.appendToast();

            if (!this.confirm) this.initAutoHide();

            this.bindEvents();
        }

        getContainer()
        {
            let container = $('.lsd-toast-container.' + this.position);
            if (!container.length) container = $('<div class="lsd-toast-container ' + this.position + '"></div>').appendTo('body');

            return container;
        }

        createToast()
        {
            const toast = $('<div class="lsd-toast ' + this.type + '"></div>');

            const defaultIcons = {
                'lsd-error': '<i class="fa fa-times-circle"></i>',
                'lsd-warning': '<i class="fa fa-exclamation-triangle"></i>',
                'lsd-info': '<i class="fa fa-info-circle"></i>',
                'lsd-success': '<i class="fa fa-check-circle"></i>',
                'lsd-natural': '<i class="fa fa-bell"></i>',
                'lsd-in-progress': '<i class="lsd-loader"></i>',
                'lsd-confirm': '<i class="listdom-icon lsdi-question"></i>',
            };

            const finalIcon = this.icon || defaultIcons[this.type] || '';

            if (finalIcon)
            {
                if (finalIcon.indexOf('<') === -1) toast.append('<span class="lsd-toast-icon"><i class="' + finalIcon + '"></i></span>');
                else toast.append('<span class="lsd-toast-icon">' + finalIcon + '</span>');
            }

            toast.append('<span class="lsd-toast-message">' + this.alert + '</span>');

            if (this.confirm)
            {
                this.overlay = $('<div class="lsd-toast-overlay"></div>');
                $('body').append(this.overlay);

                this.overlay.on('click', (e) => {
                    if (e.target === this.overlay[0]) {
                        if (typeof this.confirm.onCloseOverlay === 'function') {
                            this.confirm.onCloseOverlay(this);
                        }

                        this.remove();
                    }
                });

                const btnWrap = $('<div class="lsd-toast-actions"></div>');

                const confirmLabel = this.confirm.confirmText || 'Confirm';
                const cancelLabel = this.confirm.cancelText || 'Cancel';

                const confirmBtn = $('<button type="button" class="lsd-secondary-button">' + confirmLabel + '</button>');
                const cancelBtn = $('<button type="button" class="lsd-secondary-button">' + cancelLabel + '</button>');

                confirmBtn.on('click', () => {
                    if (typeof this.confirm.onConfirm === 'function') this.confirm.onConfirm(this);
                    this.remove();
                });

                cancelBtn.on('click', () => {
                    if (typeof this.confirm.onCancel === 'function') this.confirm.onCancel(this);
                    this.remove();
                });

                btnWrap.append(confirmBtn, cancelBtn);
                toast.append(btnWrap);

                this.progress = false;
                this.hideTime = 0;
            }

            if (this.showClose && !this.confirm)
            {
                const closeBtn = $('<span class="lsd-toast-close">&times;</span>');
                closeBtn.on('click', () => this.remove());
                toast.append(closeBtn);
            }

            if (this.progress && this.hideTime > 0)
            {
                toast.addClass('lsd-has-progress');
                toast.css('--lsd-progress-time', this.hideTime + 'ms');
                setTimeout(() => toast.addClass('lsd-progress-run'), 50);
            }

            return toast;
        }

        appendToast()
        {
            this.container.append(this.toast);
        }

        initAutoHide()
        {
            if (this.hideTime > 0) this.autoHideTimer = setTimeout(() => this.remove(), this.hideTime);
        }

        bindEvents()
        {
            if (!this.confirm)
            {
                this.toast.on('mouseenter', () => this.pause());
                this.toast.on('mouseleave', () => this.resume());
            }
        }

        pause()
        {
            this.toast.addClass('lsd-paused');

            if (this.autoHideTimer) clearTimeout(this.autoHideTimer);

            const computed = getComputedStyle(this.toast[0], '::after');
            const width = computed.getPropertyValue('width');

            this.toast[0].style.setProperty('--lsd-paused-width', width);
        }

        resume()
        {
            this.toast.removeClass('lsd-paused');

            if (this.hideTime > 0)
            {
                this.toast.css('--lsd-progress-time', this.hideTime + 'ms');
                this.toast.addClass('lsd-progress-run');
                this.autoHideTimer = setTimeout(() => this.remove(), this.hideTime);
            }
        }

        update(newMessage, newType)
        {
            this.messageEl.html(newMessage);

            if (newType)
            {
                this.toast.removeClass('lsd-error lsd-warning lsd-info lsd-success lsd-natural lsd-in-progress lsd-confirm');
                this.toast.addClass(newType);
            }
        }

        remove()
        {
            this.toast.addClass('lsd-toast-remove');

            setTimeout(() =>
            {
                this.toast.remove();

                if (this.overlay) this.overlay.remove();
                if (!this.container.children().length) this.container.remove();
            }, 300);
        }
    }

    window.ListdomToast = ListdomToast;

})(jQuery, window);
