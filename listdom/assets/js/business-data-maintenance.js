(function ($)
{
    'use strict';

    $(function ()
    {
        const config = window.lsdBusinessMaintenance;
        const $panel = $('#lsd-business-data-maintenance');
        if (!config || !$panel.length) return;

        const state = {mode: 'find', page: 1, pages: 1, selected: new Set(), quoteCount: 0, reviews: [], batchKeys: {}, fetchIds: [], appliedTokens: new Set(), appliedCount: 0, skippedCount: 0, busy: false, restored: false};
        const listingTitles = new Map();
        let listRequest = 0;
        state.selectionLoading = false;
        state.available = false;
        state.fetchError = '';
        const labels = config.fieldLabels;
        const seedIds = (config.listingIds || []).map(Number).filter(function (id) { return Number.isInteger(id) && id > 0; });
        const storageKey = function (mode) { return 'lsd_business_data_maintenance_' + config.storageScope + '_' + mode; };
        const saveProgress = function ()
        {
            if (!['enrich', 'refresh'].includes(state.mode)) return;
            try { window.sessionStorage.setItem(storageKey(state.mode), JSON.stringify({selected: Array.from(state.selected), batchKeys: state.batchKeys, seedIds: seedIds, savedAt: Date.now()})); }
            catch (error) { /* Private browsing can disable session storage. */ }
        };
        const restoreProgress = function (mode)
        {
            try
            {
                const saved = JSON.parse(window.sessionStorage.getItem(storageKey(mode)) || 'null');
                if (!saved || Date.now() - saved.savedAt > 30 * 60 * 1000 || !Array.isArray(saved.selected) || saved.selected.length > 100
                    || JSON.stringify(Array.isArray(saved.seedIds) ? saved.seedIds : []) !== JSON.stringify(seedIds)) return false;
                state.selected = new Set(saved.selected.filter(function (id) { return Number.isInteger(id) && id > 0; }));
                state.batchKeys = saved.batchKeys && typeof saved.batchKeys === 'object' ? saved.batchKeys : {};
                state.fetchIds = Array.from(state.selected);
                return true;
            }
            catch (error) { /* A saved selection is optional. */ }
            return false;
        };
        const resetFetched = function ()
        {
            state.fetchError = '';
            state.quoteCount = 0;
            state.reviews = [];
            state.batchKeys = {};
            state.fetchIds = [];
            state.appliedTokens.clear();
            state.appliedCount = 0;
            state.skippedCount = 0;
            $panel.find('[data-bdm-review]').empty();
            $panel.find('[data-bdm-apply-actions]').prop('hidden', true);
            $panel.find('[data-bdm-fetch]').text(config.strings.fetch);
            $panel.find('[data-bdm-quote-result]').empty();
            saveProgress();
            updateSelection();
        };
        const escapeHtml = function (value)
        {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        };
        const uuid = function ()
        {
            if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c)
            {
                const r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 3 | 8)).toString(16);
            });
        };
        const request = function (action, data)
        {
            return $.ajax({url: config.ajaxurl, method: 'POST', data: $.extend({action: 'lsd_business_data_maintenance_' + action, _wpnonce: config.nonce}, data)}).then(function (response)
            {
                if (!response || !response.success) throw new Error(response && response.message || config.strings.error);
                return response;
            });
        };
        const errorMessage = function (error)
        {
            return error && error.responseJSON && error.responseJSON.message || error && error.message || config.strings.error;
        };
        const setProgress = function (message, type, completed)
        {
            const $progress = $panel.find('[data-bdm-progress]');
            $progress.removeClass('lsd-info lsd-success lsd-warning lsd-error lsd-bdm-error')
                .addClass('lsd-' + (type === true ? 'error' : type || 'info')).text(message).prop('hidden', !message);
            if (completed && message)
            {
                $progress[0].focus({preventScroll: true});
                $progress[0].scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        };
        const savedSummary = function ()
        {
            const result = state.appliedCount ? config.strings.saved.replace('%d', state.appliedCount) : config.strings.noneSaved;
            return result + (state.skippedCount ? ' ' + config.strings.skipped.replace('%d', state.skippedCount) : '');
        };
        const updateSelection = function ()
        {
            const $selected = $panel.find('[data-bdm-selected]').prop('hidden', !state.selected.size).empty();
            state.selected.forEach(function (id)
            {
                const title = listingTitles.get(id) || config.strings.listingId.replace('%d', id);
                const $button = $('<button>').attr({type: 'button', 'data-bdm-remove': id, 'aria-label': config.strings.removeListing.replace('%s', title)})
                    .prop('disabled', state.busy).text('×');
                $('<span>').addClass('lsd-bdm-chip').append($('<span>').text(title), $button).appendTo($selected);
            });
            const $inputs = $panel.find('[data-bdm-listing]');
            $inputs.each(function () { this.checked = state.selected.has(Number(this.getAttribute('data-bdm-listing'))); });
            $panel.find('[data-bdm-select-page]').prop('checked', $inputs.length > 0 && $inputs.toArray().every(function (input) { return input.checked; }));
            $panel.find('[data-bdm-quote]').prop('disabled', !state.available || state.selectionLoading || state.busy);
            $panel.find('[data-bdm-selection-count]').text(config.strings.selectionCount.replace('%d', state.selected.size));
            $panel.find('[data-bdm-fetch]').prop('hidden', state.selectionLoading || state.quoteCount !== state.selected.size || !state.quoteCount);
        };
        const updateBalance = function ()
        {
            $.post(config.ajaxurl, {action: 'lsd_business_data_balance', _wpnonce: config.nonce}).done(function (response)
            {
                if (response && response.success) $('[data-bdm-balance]').text(response.credits_balance);
            });
        };

        function showMode(mode)
        {
            if (!['find', 'enrich', 'refresh', 'history'].includes(mode)) mode = 'find';
            state.mode = mode;
            listRequest++;
            setProgress('');
            state.fetchError = '';
            const connectionRequired = !config.connected && ['enrich', 'refresh'].includes(mode);
            $('#lsd-business-data').prop('hidden', mode !== 'find');
            $panel.prop('hidden', mode === 'find');
            $panel.find('[data-bdm-connection-guide]').prop('hidden', !connectionRequired);
            $panel.find('[data-bdm-workspace]').prop('hidden', mode === 'history' || connectionRequired);
            $panel.find('[data-bdm-history-panel]').prop('hidden', mode !== 'history');
            $panel.find('[data-bdm-shared-controls]').prop('hidden', mode !== 'refresh');
            if (mode === 'history') $panel.find('[data-bdm-title]').text(config.strings.historyTitle);
            if (mode === 'enrich' || mode === 'refresh')
            {
                $panel.find('[data-bdm-title]').text(mode === 'enrich' ? config.strings.enrichTitle : config.strings.refreshTitle);
                $panel.find('[data-bdm-description]').text(mode === 'enrich' ? config.strings.enrichDescription : config.strings.refreshDescription);
                if (connectionRequired) return;
                state.page = 1;
                state.selected.clear();
                state.reviews = [];
                state.batchKeys = {};
                state.fetchIds = [];
                state.appliedTokens.clear();
                state.appliedCount = 0;
                state.skippedCount = 0;
                state.quoteCount = 0;
                state.restored = restoreProgress(mode);
                if (!state.restored) seedIds.forEach(function (id) { state.selected.add(id); });
                saveProgress();
                state.available = false;
                $panel.find('[data-bdm-shared-rule]').val('none');
                $panel.find('[data-bdm-shared-field]').prop('checked', false);
                $panel.find('[data-bdm-shared-fields]').prop('hidden', true);
                $panel.find('[data-bdm-review]').empty();
                $panel.find('[data-bdm-apply-actions]').prop('hidden', true);
                updateSelection();
                loadListings();
                request('quote', {mode: mode, count: 1}).done(function ()
                {
                    state.available = true;
                    updateSelection();
                }).fail(function (error)
                {
                    state.available = false;
                    updateSelection();
                    setProgress(errorMessage(error) || config.strings.unavailable, true);
                });
            }
            if (mode === 'history') loadHistory();
        }

        function loadListings()
        {
            const requestId = ++listRequest;
            const selectedIds = Array.from(state.selected);
            state.selectionLoading = true;
            $panel.find('[data-bdm-listings]').text(config.strings.loadingListings);
            updateSelection();
            request('list', {mode: state.mode, page: state.page, search: $panel.find('[data-bdm-search]').val(), selected_ids: selectedIds}).done(function (response)
            {
                if (requestId !== listRequest) return;
                state.selectionLoading = false;
                const selected = response.selected || [];
                selected.forEach(function (item) { listingTitles.set(item.id, item.title); });
                const eligible = new Set(selected.map(function (item) { return item.id; }));
                let removed = false;
                selectedIds.forEach(function (id)
                {
                    if (!eligible.has(id) && state.selected.delete(id)) removed = true;
                });
                if (removed)
                {
                    resetFetched();
                    setProgress(config.strings.selectionRemoved);
                }
                state.pages = response.pages || 1;
                const list = response.listings || [];
                list.forEach(function (item) { listingTitles.set(item.id, item.title); });
                $panel.find('[data-bdm-listings]').html(list.length ? list.map(function (item)
                {
                    return '<label><input type="checkbox" data-bdm-listing="' + item.id + '"' + (state.selected.has(item.id) ? ' checked' : '') + '>'
                        + '<span><strong>' + escapeHtml(item.title) + '</strong><small>' + escapeHtml(item.address)
                        + (item.place_id ? ' · ' + escapeHtml(config.strings.linkedTo.replace('%s', item.place_id)) : '') + '</small></span></label>';
                }).join('') : '<p>' + escapeHtml(config.strings.noEligible) + '</p>');
                $panel.find('[data-bdm-page]').text(config.strings.page.replace('%1$d', state.page).replace('%2$d', state.pages));
                $panel.find('[data-bdm-prev]').prop('disabled', state.page <= 1);
                $panel.find('[data-bdm-next]').prop('disabled', state.page >= state.pages);
                updateSelection();
            }).fail(function (error)
            {
                if (requestId !== listRequest) return;
                state.selectionLoading = false;
                updateSelection();
                setProgress(errorMessage(error), true);
            });
        }

        function currentFields(current)
        {
            return $.extend({}, current, {
                coordinates: current.latitude !== '' && current.longitude !== '' ? [current.latitude, current.longitude] : '',
                socials: Object.values(current.socials || {}).filter(Boolean)
            });
        }
        function displayValue(value)
        {
            if (Array.isArray(value)) return value.join(', ');
            return value || '—';
        }
        function displayCategories(codes, names, primary)
        {
            if (!Array.isArray(codes)) return displayValue(codes);
            return displayValue(codes.map(function (code, index)
            {
                return (names && names[index] || code) + (code === primary ? ' (' + config.strings.primary + ')' : '');
            }));
        }

        function renderFields($article, candidate)
        {
            const entry = $article.data('entry');
            const source = candidate.fields || {};
            const current = currentFields(entry.listing.current);
            const rows = Object.keys(labels).map(function (field)
            {
                const before = field === 'categories' ? displayCategories(current.categories, current.category_labels, current.primary_category) : displayValue(current[field]);
                const after = field === 'categories' ? displayCategories(source.categories, null, source.primary_category) : displayValue(source[field]);
                const sameCategories = field === 'categories' && Array.isArray(current.categories) && Array.isArray(source.categories)
                    && current.categories.slice().sort().join('\0') === source.categories.slice().sort().join('\0')
                    && current.primary_category === source.primary_category;
                const sameSocials = field === 'socials' && Array.isArray(current.socials) && Array.isArray(source.socials)
                    && current.socials.slice().sort().join('\0') === source.socials.slice().sort().join('\0');
                if (after === '—' || sameCategories || sameSocials || (!['categories', 'socials'].includes(field) && before === after)) return '';
                const checked = before === '—' ? ' checked' : '';
                return '<tr><td><label><input type="checkbox" data-bdm-field="' + field + '"' + checked + '> ' + escapeHtml(labels[field]) + '</label></td>'
                    + '<td>' + escapeHtml(before) + '</td><td>' + escapeHtml(after) + '</td></tr>';
            }).join('');
            const closed = ['temporarily_closed', 'permanently_closed'].includes(source.operating_status)
                ? '<p class="lsd-bdm-warning">' + escapeHtml(config.strings.closed.replace('%s', source.operating_status.replace('_', ' '))) + '</p>' : '';
            $article.find('[data-bdm-diffs]').html(closed + (rows ? '<table><thead><tr><th>' + escapeHtml(config.strings.applyColumn) + '</th><th>' + escapeHtml(config.strings.currentColumn) + '</th><th>' + escapeHtml(config.strings.sourceColumn) + '</th></tr></thead><tbody>' + rows + '</tbody></table>' : '<p>' + escapeHtml(config.strings.noDifferences) + '</p>'));
        }

        function renderReviews()
        {
            const $review = $panel.find('[data-bdm-review]').empty();
            state.reviews.forEach(function (batch)
            {
                if (state.appliedTokens.has(batch.token)) return;
                Object.keys(batch.entries).forEach(function (id)
                {
                    const entry = batch.entries[id];
                    const candidates = entry.candidates || [];
                    const $article = $('<article>').attr('data-bdm-review-id', id).data('entry', entry).data('token', batch.token);
                    $article.append('<h4>' + escapeHtml(entry.listing.title) + '</h4>');
                    if (!candidates.length)
                    {
                        $article.append('<p class="lsd-bdm-warning">' + escapeHtml(state.mode === 'enrich' ? config.strings.noMatch : config.strings.notFound) + '</p>');
                        if (state.mode === 'refresh') $article.append('<button type="button" class="lsd-secondary-button" data-bdm-rematch="' + id + '">' + escapeHtml(config.strings.rematch) + '</button>');
                    }
                    else
                    {
                        const options = candidates.map(function (candidate, index)
                        {
                            const place = candidate.place;
                            return '<option value="' + index + '">' + escapeHtml(place.name) + ' — ' + escapeHtml(candidate.fields && candidate.fields.address || '') + ' (' + Math.round(Number(candidate.score) * 100) + '%)</option>';
                        }).join('');
                        $article.append('<label>' + escapeHtml(config.strings.match) + ' <select data-bdm-candidate><option value="">' + escapeHtml(config.strings.skip) + '</option>' + options + '</select></label><div data-bdm-diffs></div>');
                        if (state.mode === 'refresh') $article.find('select').val('0').trigger('change');
                    }
                    $review.append($article);
                    if (state.mode === 'refresh' && candidates.length) renderFields($article, candidates[0]);
                });
            });
            $panel.find('[data-bdm-apply-actions]').prop('hidden', !state.reviews.some(function (batch) { return !state.appliedTokens.has(batch.token); }));
        }

        function loadHistory()
        {
            const $history = $panel.find('[data-bdm-history]').removeClass('lsd-bdm-error').text(config.strings.loadingHistory);
            request('history', {}).done(function (response)
            {
                $history.html((response.history || []).map(function (item)
                {
                    const count = (item.operation === 'search' ? config.strings.historyResults : config.strings.historyListings).replace('%d', item.count);
                    const details = [new Date(Number(item.created_at) * 1000).toLocaleString(), config.strings.historyOperations[item.operation] || item.operation, count];
                    if (item.credits_charged !== null && item.credits_charged !== undefined) details.push(config.strings.historyCredits.replace('%s', item.credits_charged));
                    return '<div class="lsd-business-data-history-item"><div class="lsd-business-data-history-details"><span>' + escapeHtml(details.join(' — ')) + '</span>'
                        + (item.import_count ? '<small>' + escapeHtml(config.strings.historyImports.replace('%d', item.import_count)) + '</small>' : '') + '</div></div>';
                }).join('') || '<p>' + escapeHtml(config.strings.noHistory) + '</p>');
            }).fail(function (error) { $history.addClass('lsd-bdm-error').text(errorMessage(error)); });
        }

        $panel.on('click', '[data-bdm-search-button]', function () { if (!state.busy) { state.page = 1; loadListings(); } });
        $panel.on('keydown', '[data-bdm-search]', function (event) { if (event.key === 'Enter') { event.preventDefault(); if (!state.busy) { state.page = 1; loadListings(); } } });
        $panel.on('click', '[data-bdm-prev]', function () { if (!state.busy && state.page > 1) { state.page--; loadListings(); } });
        $panel.on('click', '[data-bdm-next]', function () { if (!state.busy && state.page < state.pages) { state.page++; loadListings(); } });
        $panel.on('click', '[data-bdm-remove]', function ()
        {
            if (state.busy) return;
            const index = $panel.find('[data-bdm-remove]').index(this);
            state.selected.delete(Number(this.getAttribute('data-bdm-remove')));
            resetFetched();
            const $buttons = $panel.find('[data-bdm-remove]');
            if ($buttons.length) $buttons.eq(Math.min(index, $buttons.length - 1)).trigger('focus');
            else $panel.find('[data-bdm-search]').trigger('focus');
        });
        $panel.on('change', '[data-bdm-listing]', function ()
        {
            const id = Number(this.getAttribute('data-bdm-listing'));
            if (state.busy) { this.checked = state.selected.has(id); return; }
            if (this.checked && state.selected.size < 100) state.selected.add(id);
            else { state.selected.delete(id); this.checked = false; }
            resetFetched();
        });
        $panel.on('change', '[data-bdm-select-page]', function ()
        {
            if (state.busy)
            {
                this.checked = $panel.find('[data-bdm-listing]').length > 0 && $panel.find('[data-bdm-listing]').toArray().every(function (input)
                {
                    return state.selected.has(Number(input.getAttribute('data-bdm-listing')));
                });
                return;
            }
            const checked = this.checked;
            $panel.find('[data-bdm-listing]').each(function ()
            {
                const id = Number(this.getAttribute('data-bdm-listing'));
                if (checked && state.selected.size < 100) state.selected.add(id);
                else if (!checked) state.selected.delete(id);
                this.checked = state.selected.has(id);
            });
            resetFetched();
        });
        $panel.on('click', '[data-bdm-quote]', function ()
        {
            if (state.busy || state.selectionLoading || !state.available) return;
            setProgress('');
            if (!state.selected.size) { setProgress(config.strings.selectListings, true); return; }
            const quotedIds = JSON.stringify(Array.from(state.selected));
            request('quote', {mode: state.mode, count: state.selected.size}).done(function (response)
            {
                if (quotedIds !== JSON.stringify(Array.from(state.selected))) return;
                state.quoteCount = state.selected.size;
                $panel.find('[data-bdm-quote-result]').text(config.strings.cost.replace('%s', response.quote.credits_estimated).replace('%d', state.selected.size));
                setProgress('');
                updateSelection();
            }).fail(function (error) { setProgress(errorMessage(error), true); });
        });
        $panel.on('click', '[data-bdm-fetch]', async function ()
        {
            if (state.busy || state.selectionLoading || state.quoteCount !== state.selected.size) return;
            state.fetchError = '';
            state.busy = true;
            setProgress('');
            updateSelection();
            $panel.find('[data-bdm-listing],[data-bdm-select-page]').prop('disabled', true);
            const ids = Array.from(state.selected);
            if (JSON.stringify(state.fetchIds) !== JSON.stringify(ids))
            {
                state.reviews = [];
                state.batchKeys = {};
                state.fetchIds = ids;
            }
            for (let index = state.reviews.length * 20; index < ids.length; index += 20)
            {
                const batch = ids.slice(index, index + 20);
                setProgress(config.strings.fetching.replace('%1$d', index + 1).replace('%2$d', Math.min(index + 20, ids.length)).replace('%3$d', ids.length));
                try
                {
                    if (!state.batchKeys[index]) state.batchKeys[index] = uuid();
                    saveProgress();
                    const response = await request('fetch', {mode: state.mode, listing_ids: batch, idempotency_key: state.batchKeys[index]});
                    state.reviews.push({token: response.token, entries: response.entries,
                        creditsCharged: Number(response.meta.credits_charged || 0), replayed: !!response.meta.replayed});
                    $('[data-business-data-balance-value]').text(response.meta.credits_balance || '');
                    $('[data-bdm-balance]').text(response.meta.credits_balance || '');
                }
                catch (error)
                {
                    if (error.status === 402 || error.status === 503) delete state.batchKeys[index];
                    saveProgress();
                    state.fetchError = errorMessage(error);
                    setProgress(state.fetchError, true);
                    break;
                }
            }
            state.busy = false;
            $panel.find('[data-bdm-listing],[data-bdm-select-page]').prop('disabled', false);
            renderReviews();
            if (state.reviews.length === Math.ceil(ids.length / 20))
            {
                state.quoteCount = 0;
                updateSelection();
                const charged = state.reviews.reduce(function (total, batch) { return total + batch.creditsCharged; }, 0);
                const replayed = state.reviews.some(function (batch) { return batch.replayed; });
                setProgress(config.strings.ready + ' ' + config.strings.charged.replace('%s', charged.toFixed(3)) + (replayed ? ' ' + config.strings.replayed : ''), 'success', true);
            }
            else
            {
                $panel.find('[data-bdm-fetch]').text(config.strings.resume);
                updateSelection();
            }
        });
        $panel.on('change', '[data-bdm-candidate]', function ()
        {
            const $article = $(this).closest('article');
            const index = this.value;
            if (index === '') { $article.find('[data-bdm-diffs]').empty(); return; }
            renderFields($article, $article.data('entry').candidates[Number(index)]);
        });
        $panel.on('click', '[data-bdm-rematch]', function ()
        {
            const id = Number(this.getAttribute('data-bdm-rematch'));
            const url = new URL(window.location.href);
            url.searchParams.set('mode', 'enrich');
            url.searchParams.set('listing_ids', String(id));
            window.location.assign(url.toString());
        });
        function applySharedRule()
        {
            if (state.mode !== 'refresh') return;
            const rule = $panel.find('[data-bdm-shared-rule]').val();
            $panel.find('[data-bdm-shared-fields]').prop('hidden', rule !== 'selected');
            if (rule === 'none') return;
            const selected = $panel.find('[data-bdm-shared-field]:checked').map(function () { return this.getAttribute('data-bdm-shared-field'); }).get();
            $panel.find('[data-bdm-diffs] tr').each(function ()
            {
                const $checkbox = $(this).find('[data-bdm-field]');
                if (!$checkbox.length) return;
                const isEmpty = $(this).children('td').eq(1).text() === '—';
                $checkbox.prop('checked', rule === 'empty' ? isEmpty : selected.includes($checkbox.attr('data-bdm-field')));
            });
        }
        $panel.on('change', '[data-bdm-shared-rule], [data-bdm-shared-field]', applySharedRule);
        $panel.on('click', '[data-bdm-apply]', async function ()
        {
            if (state.busy) return;
            state.busy = true;
            setProgress(state.fetchError, true);
            updateSelection();
            $panel.find('[data-bdm-listing],[data-bdm-select-page]').prop('disabled', true);
            for (const batch of state.reviews)
            {
                if (state.appliedTokens.has(batch.token)) continue;
                const choices = [];
                $panel.find('article').each(function ()
                {
                    const $article = $(this);
                    if ($article.data('token') !== batch.token) return;
                    const choiceIndex = $article.find('[data-bdm-candidate]').val();
                    if (choiceIndex === undefined || choiceIndex === '') return;
                    const candidate = $article.data('entry').candidates[Number(choiceIndex)];
                    choices.push({listing_id: Number(this.getAttribute('data-bdm-review-id')), place_id: candidate.place.id,
                        fields: $article.find('[data-bdm-field]:checked').map(function () { return this.getAttribute('data-bdm-field'); }).get()});
                });
                if (!choices.length) continue;
                try
                {
                    const response = await request('apply', {token: batch.token, choices: choices});
                    state.appliedCount += (response.updated || []).length;
                    state.skippedCount += (response.skipped || []).length;
                    state.appliedTokens.add(batch.token);
                }
                catch (error)
                {
                    $panel.find('[data-bdm-review] article').each(function ()
                    {
                        if (state.appliedTokens.has($(this).data('token'))) $(this).remove();
                    });
                    setProgress(savedSummary() + ' ' + errorMessage(error), 'error', true);
                    state.busy = false;
                    updateSelection();
                    $panel.find('[data-bdm-listing],[data-bdm-select-page]').prop('disabled', false);
                    return;
                }
            }
            state.busy = false;
            $panel.find('[data-bdm-listing],[data-bdm-select-page]').prop('disabled', false);
            setProgress(savedSummary()
                + (state.fetchError ? ' ' + state.fetchError : ''), state.fetchError || state.skippedCount || !state.appliedCount ? 'warning' : 'success', true);
            if (state.fetchError)
            {
                renderReviews();
                updateSelection();
                return;
            }
            state.reviews = [];
            state.appliedTokens.clear();
            $panel.find('[data-bdm-apply-actions]').prop('hidden', true);
            $panel.find('[data-bdm-review]').empty();
            state.appliedCount = 0;
            state.skippedCount = 0;
            state.selected.clear();
            state.quoteCount = 0;
            try { window.sessionStorage.removeItem(storageKey(state.mode)); } catch (error) { /* Session storage is optional. */ }
            updateSelection();
            loadListings();
        });

        showMode(config.mode);
        if (config.connected && state.mode !== 'find') updateBalance();
    });
})(jQuery);
