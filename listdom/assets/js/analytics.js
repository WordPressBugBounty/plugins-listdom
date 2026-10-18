// Install or activate Stats from the Analytics page.
(function ($)
{
    $(function ()
    {
        const $button = $('.lsd-analytics-page .lsd-analytics-install');
        if (!$button.length) return;

        $button.on('click', function ()
        {
            if ($button.prop('disabled')) return;

            $button.prop('disabled', true);

            const loading = new ListdomButtonLoader($button);
            loading.start($button.data('loading'));

            $.post(ajaxurl, {
                action: $button.data('action'),
                basename: $button.data('basename'),
                _ajax_nonce: $button.data('nonce')
            }, function (response)
            {
                const result = response && response.data ? response.data : response;
                if (response && response.success && result.reload)
                {
                    window.listdom_toastify(result.message, 'lsd-success');
                    window.setTimeout(function () { window.location.reload(); }, 2500);
                    return;
                }

                window.listdom_toastify(result && result.message ? result.message : $button.data('failed'), 'lsd-error');
                loading.stop();
                $button.prop('disabled', false);
            }, 'json').fail(function (xhr)
            {
                const result = xhr.responseJSON && (xhr.responseJSON.data || xhr.responseJSON);
                window.listdom_toastify(result && result.message ? result.message : $button.data('failed'), 'lsd-error');
                loading.stop();
                $button.prop('disabled', false);
            });
        });
    });
})(jQuery);

// Expand report lists in place and load the next batch as the list is scrolled.
(function ($)
{
    $(function ()
    {
        const reports = {
            terms: { section: '.lsd-analytics-popular-terms', content: '.lsd-analytics-popular-terms-list', rows: '.lsd-analytics-popular-terms-row' },
            zero: { section: '.lsd-analytics-zero-results', content: '.lsd-analytics-zero-results-table-wrap', rows: 'tbody tr' }
        };
        const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 220;

        $('.lsd-analytics-page .lsd-analytics-expand-toggle').each(function ()
        {
            const $toggle = $(this);
            const type = $toggle.data('lsd-expand');
            const report = reports[type];
            if (!report) return;
            const $section = $toggle.closest(report.section);
            const $expanded = $('<div class="lsd-analytics-expanded" role="region" tabindex="0" hidden></div>')
                .attr('id', $toggle.attr('aria-controls'))
                .attr('aria-labelledby', $section.attr('aria-labelledby'))
                .append($section.find(report.content).first().clone()).appendTo($section);
            const $summary = $section.children().not('.lsd-analytics-report-header').not($expanded);
            const $error = $('<p class="lsd-admin-description-tiny lsd-analytics-load-error" role="status" hidden></p>')
                .append($('<a class="lsd-analytics-report-page-link"></a>')
                    .attr('href', $toggle.attr('href')).text($toggle.attr('data-lsd-load-error')))
                .appendTo($section);
            const showLabel = $toggle[0].firstChild.nodeValue;
            let offset = $expanded.find(report.rows).length;
            let total = parseInt($toggle.attr('data-lsd-total'), 10);
            let loaded = false;
            let loading = false;
            let failed = false;

            function show()
            {
                $summary.stop(true, true).slideUp(duration);
                $expanded.stop(true, true).removeAttr('hidden').hide().slideDown(duration, function ()
                {
                    $expanded.removeAttr('style');
                });
                $toggle.attr('aria-expanded', 'true').addClass('lsd-analytics-expanded-active');
                $toggle[0].firstChild.nodeValue = $toggle.data('hide-label');
            }

            function loadMore(first)
            {
                if (loading || failed || offset >= total) return;
                loading = true;
                $expanded.attr('aria-busy', 'true');
                $error.prop('hidden', true);

                // Keep search-term paging within the report's selected chart range.
                $.post(window.ajaxurl, {
                    action: 'lsd_analytics_terms',
                    _ajax_nonce: $toggle.attr('data-lsd-nonce'),
                    report: type,
                    from: $toggle.attr('data-lsd-from'),
                    maximum: $toggle.attr('data-lsd-maximum') || 1,
                    offset: offset
                }, null, 'json').done(function (response)
                {
                    if (!response || !response.success || !response.data)
                    {
                        failed = true;
                        $error.prop('hidden', false);
                        return;
                    }
                    const $target = type === 'zero' ? $expanded.find('tbody') : $expanded.find(report.content);
                    $target.append(response.data.rows);
                    offset += response.data.count;
                    total = response.data.total;
                    if (!response.data.count) total = offset;
                    loaded = true;
                    if (first) show();
                }).fail(function ()
                {
                    failed = true;
                    $error.prop('hidden', false);
                }).always(function ()
                {
                    loading = false;
                    $expanded.removeAttr('aria-busy');
                });
            }

            $expanded.on('scroll', function ()
            {
                if (this.scrollHeight > this.clientHeight && this.scrollTop + this.clientHeight >= this.scrollHeight - 80) loadMore(false);
            });

            $toggle.on('click', function (event)
            {
                event.preventDefault();
                if (loading) return;
                $error.prop('hidden', true);
                if ($toggle.attr('aria-expanded') === 'true')
                {
                    $expanded.stop(true, true).slideUp(duration, function ()
                    {
                        $expanded.attr('hidden', true).removeAttr('style');
                    });
                    $summary.stop(true, true).slideDown(duration, function () { $(this).removeAttr('style'); });
                    $toggle.attr('aria-expanded', 'false').removeClass('lsd-analytics-expanded-active');
                    $toggle[0].firstChild.nodeValue = showLabel;
                    return;
                }
                if (loaded)
                {
                    if (failed) $error.prop('hidden', false);
                    show();
                    return;
                }
                failed = false;
                loadMore(true);
            });
        });
    });
})(jQuery);

// Add five top listings at a time without leaving the Analytics page.
(function ($)
{
    $(function ()
    {
        const $toggle = $('.lsd-analytics-page .lsd-analytics-expand-toggle[data-lsd-expand="listings"]');
        if (!$toggle.length) return;

        const $section = $toggle.closest('.lsd-analytics-report-listings');
        const $summary = $section.find('.lsd-analytics-report-table-wrap').first();
        const $expanded = $('<div class="lsd-analytics-expanded" role="region" tabindex="0" hidden></div>')
            .attr('id', $toggle.attr('aria-controls'))
            .attr('aria-labelledby', $section.attr('aria-labelledby'))
            .append($summary.clone()).appendTo($section);
        // The overview stays compact; its expanded copy shows the tracked detail columns.
        $expanded.find('[data-lsd-detail-column]').removeAttr('hidden');
        const $error = $('<p class="lsd-admin-description-tiny lsd-analytics-load-error" role="status" hidden></p>')
            .text($toggle.attr('data-lsd-load-error')).appendTo($section);
        const filters = new URL($toggle.attr('href'), window.location.href).searchParams;
        const showLabel = $toggle[0].firstChild.nodeValue;
        const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 220;
        let offset = $expanded.find('tbody tr').length;
        let total = parseInt($toggle.attr('data-lsd-total'), 10);
        let loaded = false;
        let loading = false;

        function show()
        {
            $summary.stop(true, true).slideUp(duration);
            $expanded.stop(true, true).removeAttr('hidden').hide().slideDown(duration, function ()
            {
                $expanded.removeAttr('style');
            });
            $toggle.attr('aria-expanded', 'true').addClass('lsd-analytics-expanded-active');
            $toggle[0].firstChild.nodeValue = $toggle.data('hide-label');
        }

        function loadMore(first)
        {
            if (loading || offset >= total) return;
            loading = true;
            $expanded.attr('aria-busy', 'true');
            $error.attr('hidden', true);

            // Keep expanded listing rows aligned with the visible report filters.
            $.post(window.ajaxurl, {
                action: 'lsd_analytics_listings',
                _ajax_nonce: $toggle.attr('data-lsd-nonce'),
                offset: offset,
                tracked: $toggle.attr('data-lsd-tracked'),
                analytics_range: filters.get('analytics_range') || '',
                analytics_category: filters.get('analytics_category') || '',
                analytics_location: filters.get('analytics_location') || '',
                analytics_search: filters.get('analytics_search') || ''
            }, null, 'json').done(function (response)
            {
                if (!response || !response.success || !response.data)
                {
                    $error.removeAttr('hidden');
                    return;
                }
                $expanded.find('tbody').append(response.data.rows);
                offset += response.data.count;
                total = response.data.total;
                if (!response.data.count) total = offset;
                loaded = true;
                if (first) show();
            }).fail(function ()
            {
                $error.removeAttr('hidden');
            }).always(function ()
            {
                loading = false;
                $expanded.removeAttr('aria-busy');
            });
        }

        $expanded.on('scroll', function ()
        {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 80) loadMore(false);
        });

        $toggle.on('click', function (event)
        {
            event.preventDefault();
            if (loading) return;
            $error.attr('hidden', true);
            if ($toggle.attr('aria-expanded') === 'true')
            {
                $expanded.stop(true, true).slideUp(duration, function ()
                {
                    $expanded.attr('hidden', true).removeAttr('style');
                });
                $summary.stop(true, true).slideDown(duration, function () { $(this).removeAttr('style'); });
                $toggle.attr('aria-expanded', 'false').removeClass('lsd-analytics-expanded-active');
                $toggle[0].firstChild.nodeValue = showLabel;
                return;
            }
            if (loaded) { show(); return; }
            if (offset >= total) { loaded = true; show(); return; }
            loadMore(true);
        });
    });
})(jQuery);

// Reveal the additional listing actions and their date range together.
(function ($)
{
    $(function ()
    {
        const $breakdown = $('.lsd-analytics-page .lsd-analytics-action-clicks');
        const $toggle = $breakdown.find('.lsd-analytics-action-clicks-toggle');
        if (!$toggle.length) return;

        const $more = $breakdown.find('.lsd-analytics-action-clicks-more');
        const $range = $breakdown.find('.lsd-analytics-action-clicks-range-form');
        const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 220;

        $toggle.on('click', function ()
        {
            const expanded = $toggle.attr('aria-expanded') === 'true';
            $toggle.attr('aria-expanded', expanded ? 'false' : 'true');
            $toggle.find('.lsd-analytics-action-clicks-toggle-label').text($toggle.data(expanded ? 'show-label' : 'hide-label'));

            if (expanded)
            {
                $more.add($range).stop(true, true).slideUp(duration, function ()
                {
                    $(this).attr('hidden', true).removeAttr('style');
                });
            }
            else
            {
                $more.add($range).stop(true, true).removeAttr('hidden').hide().slideDown(duration, function ()
                {
                    $(this).removeAttr('style');
                });
            }
        });
    });
})(jQuery);

(function ()
{
    'use strict';

    document.addEventListener('DOMContentLoaded', function ()
    {
        // Dashboard Controls
        const statusDismiss = document.querySelector('.lsd-analytics-page .lsd-analytics-status-dismiss');
        if (statusDismiss) statusDismiss.addEventListener('click', function () { statusDismiss.closest('.lsd-analytics-status').remove(); });

        document.querySelectorAll('.lsd-analytics-page .lsd-analytics-report-chart-period').forEach(function (period)
        {
            period.addEventListener('change', function () { period.form.submit(); });
        });

        document.querySelectorAll('.lsd-analytics-page .lsd-analytics-activity-select').forEach(function (filter)
        {
            filter.addEventListener('change', function () { filter.form.requestSubmit(); });
        });

        // Search Chart
        const searchCanvas = document.querySelector('.lsd-analytics-page .lsd-analytics-search-chart');
        if (searchCanvas && typeof Chart !== 'undefined')
        {
            let searches;
            try { searches = JSON.parse(searchCanvas.dataset.series); }
            catch (error) { searches = null; }

            if (searches && Array.isArray(searches.labels) && Array.isArray(searches.searches))
            {
                const coverage = (searches.event_coverage && searches.event_coverage.search_executed) || searches.coverage || [];
                const existing = Chart.getChart(searchCanvas);
                if (existing) existing.destroy();

                new Chart(searchCanvas, {
                    type: 'bar',
                    data: {
                        labels: searches.labels,
                        datasets: [{
                            label: searchCanvas.dataset.searchesLabel,
                            data: searches.searches,
                            backgroundColor: '#9062f7',
                            borderWidth: 0,
                            barPercentage: 0.55,
                            categoryPercentage: 0.8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#252539',
                                callbacks: {
                                    title: function (items)
                                    {
                                        if (!items.length) return '';
                                        const index = items[0].dataIndex;
                                        const label = searches.full_labels[index];
                                        if (coverage[index] === 'unavailable') return label + ' (' + searchCanvas.dataset.unavailableLabel + ')';
                                        return coverage[index] === 'partial' ? label + ' (' + searchCanvas.dataset.partialLabel + ')' : label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: '#f0eff6', drawTicks: false },
                                border: { display: false },
                                ticks: { color: '#78819b', maxTicksLimit: 10, maxRotation: 0, font: { size: 10 }, padding: 8 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#eceef4', drawTicks: false },
                                border: { display: false },
                                ticks: { color: '#78819b', precision: 0, maxTicksLimit: 5, font: { size: 10 }, padding: 7 }
                            }
                        }
                    }
                });
            }
        }

        // Conversion Chart
        const conversionCanvas = document.querySelector('.lsd-analytics-page #lsd-analytics-conversion-chart');
        if (conversionCanvas && typeof Chart !== 'undefined')
        {
            let conversion;
            try { conversion = JSON.parse(conversionCanvas.dataset.values); }
            catch (error) { conversion = null; }

            if (conversion && Array.isArray(conversion.actions))
            {
                const actions = conversion.actions.filter(action => Number.isFinite(action.value));
                const hasActions = actions.some(action => action.value > 0);
                const existing = Chart.getChart(conversionCanvas);
                if (existing) existing.destroy();

                new Chart(conversionCanvas, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: hasActions ? actions.map(action => action.value) : [1],
                            backgroundColor: hasActions ? actions.map(action => action.color) : ['#e7e7ef'],
                            borderWidth: 0,
                            hoverOffset: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        rotation: -86,
                        animation: false,
                        events: [],
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        }
                    }
                });
            }
        }

        // Views and Contacts Chart
        const canvas = document.querySelector('.lsd-analytics-page #lsd-analytics-report-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        let series;
        try { series = JSON.parse(canvas.dataset.series); }
        catch (error) { return; }

        if (!Array.isArray(series.labels) || !Array.isArray(series.views) || !Array.isArray(series.contacts)) return;

        const views = series.views;
        const contacts = series.contacts;

        const drawing = canvas.getContext('2d');
        const viewsFill = drawing.createLinearGradient(0, 0, 0, 210);
        viewsFill.addColorStop(0, 'rgba(107, 58, 255, 0.24)');
        viewsFill.addColorStop(1, 'rgba(107, 58, 255, 0.02)');

        const contactsFill = drawing.createLinearGradient(0, 0, 0, 210);
        contactsFill.addColorStop(0, 'rgba(185, 157, 255, 0.24)');
        contactsFill.addColorStop(1, 'rgba(185, 157, 255, 0.02)');

        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();

        new Chart(drawing, {
            type: 'line',
            data: {
                labels: series.labels,
                datasets: [
                    {
                        label: canvas.dataset.viewsLabel,
                        data: views,
                        borderColor: '#6838ff',
                        backgroundColor: viewsFill,
                        fill: true,
                        tension: 0.42,
                        borderWidth: 2,
                        pointRadius: views.filter(function (value) { return value !== null; }).length === 1 ? 4 : 0,
                        pointHoverRadius: 4,
                        spanGaps: false
                    },
                    {
                        label: canvas.dataset.contactsLabel,
                        data: contacts,
                        borderColor: '#bca1ff',
                        backgroundColor: contactsFill,
                        fill: true,
                        tension: 0.42,
                        borderWidth: 2,
                        pointRadius: contacts.filter(function (value) { return value !== null; }).length === 1 ? 4 : 0,
                        pointHoverRadius: 4,
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            boxHeight: 8,
                            color: '#4b526b',
                            padding: 14,
                            font: { size: 10 },
                            generateLabels: function (chart)
                            {
                                return Chart.defaults.plugins.legend.labels.generateLabels(chart).map(function (item)
                                {
                                    const color = chart.data.datasets[item.datasetIndex].borderColor;
                                    item.fillStyle = color;
                                    item.strokeStyle = color;
                                    item.lineWidth = 0;
                                    return item;
                                });
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#252539',
                        padding: 9,
                        displayColors: true,
                        callbacks: {
                            title: function (items)
                            {
                                if (!items.length) return '';
                                const index = items[0].dataIndex;
                                const label = series.full_labels[index];
                                if (Array.isArray(series.coverage) && series.coverage[index] === 'unavailable') return label + ' (' + canvas.dataset.unavailableLabel + ')';
                                return Array.isArray(series.coverage) && series.coverage[index] === 'partial' ? label + ' (' + canvas.dataset.partialLabel + ')' : label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f0eff6', drawTicks: false },
                        border: { display: false },
                        ticks: { color: '#78819b', maxTicksLimit: 12, maxRotation: 0, font: { size: 10 }, padding: 8 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#eceef4', drawTicks: false },
                        border: { display: false },
                        ticks: { color: '#78819b', precision: 0, maxTicksLimit: 5, font: { size: 10 }, padding: 7 }
                    }
                }
            }
        });
    });
})();
