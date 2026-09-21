(function ()
{
    let initialized = false;

    function init()
    {
    if (initialized) return;
    const config = window.lsdSetupWizard || {};
    const root = document.querySelector('.lsd-admin-wizard');
    if (!root) return;
    const selectedBlueprint = root.querySelector('[data-blueprint].is-selected') || root.querySelector('[data-blueprint]');
    if (!selectedBlueprint) return;
    selectedBlueprint.classList.add('is-selected');
    initialized = true;

    const card = root.querySelector('.lsd-admin-wizard__card');
    const steps = Array.prototype.slice.call(root.querySelectorAll('.lsd-admin-wizard__step'));
    const progress = document.getElementById('lsd-admin-wizard-progress');
    const back = document.getElementById('lsd-admin-wizard-back');
    const skip = document.getElementById('lsd-admin-wizard-skip');
    const directoryActions = document.getElementById('lsd-admin-wizard-directory-actions');
    const storageKey = config.storageKey || ('lsdSetupWizardState_' + window.location.pathname);
    let blueprint = selectedBlueprint.dataset.blueprint;
    let mode = 'directory';
    let position = 0;
    let loading = false;
    let hasRendered = false;
    const defaultAnswers = answers();

    function questionSteps()
    {
        return steps.filter(function (step) { return step.dataset.step === 'question'; });
    }

    function sampleListings()
    {
        const input = directoryActions.querySelector('.lsd-switch input[type="checkbox"]');

        return Boolean(input && input.checked);
    }

    function answers()
    {
        const values = {include_demo: sampleListings()};

        visibleQuestions().forEach(function (question)
        {
            const yes = question.querySelector('[data-answer="yes"]');
            if (yes) values[question.dataset.key] = yes.classList.contains('is-selected');
        });

        return values;
    }

    function answersFor(key)
    {
        const question = steps.find(function (step) { return step.dataset.key === key; });
        const yes = question ? question.querySelector('[data-answer="yes"]') : null;
        return Boolean(yes && yes.classList.contains('is-selected'));
    }

    function visibleQuestions()
    {
        return questionSteps().filter(function (question)
        {
            const blueprints = question.dataset.blueprints ? question.dataset.blueprints.split(',') : [];
            const blueprintAllowed = !blueprints.length || blueprints.indexOf(blueprint) !== -1;
            return blueprintAllowed && (!question.dataset.conditional || answersFor(question.dataset.conditional));
        });
    }

    function updateProgress()
    {
        const total = visibleQuestions().length + 2;
        let current = 1;
        if (mode === 'question') current = position + 2;
        if (mode === 'complete') current = total;
        progress.style.width = ((current / total) * 100) + '%';
    }

    function updateFooter()
    {
        directoryActions.classList.toggle('lsd-util-hide', mode !== 'directory');
        back.classList.toggle('lsd-util-hide', mode !== 'question' && mode !== 'complete');
        skip.classList.toggle('lsd-util-hide', mode !== 'question');
    }

    function focusDefault(step)
    {
        if (!step) return;
        const selector = step.dataset.step === 'directory' ? '[data-blueprint].is-selected' : '[data-answer].is-selected';
        let choice = step.querySelector(selector);
        if (!choice && step.dataset.step === 'question')
        {
            choice = step.querySelector('[data-answer="no"]') || step.querySelector('[data-answer]');
            if (choice)
            {
                step.querySelectorAll('[data-answer]').forEach(function (item) { item.classList.toggle('is-selected', item === choice); });
            }
        }
        if (choice) choice.focus({preventScroll: true});
    }

    function resetAnswers()
    {
        const includeDemo = sampleListings();

        questionSteps().forEach(function (question)
        {
            const answer = Boolean(defaultAnswers[question.dataset.key]);
            question.querySelector('[data-answer="yes"]').classList.toggle('is-selected', answer);
            question.querySelector('[data-answer="no"]').classList.toggle('is-selected', !answer);
        });

        directoryActions.querySelector('.lsd-switch input[type="checkbox"]').checked = includeDemo;
    }

    function remember()
    {
        if (mode === 'complete')
        {
            window.sessionStorage.removeItem(storageKey);
            return;
        }

        window.sessionStorage.setItem(storageKey, JSON.stringify({
            answers: answers(),
            blueprint: blueprint,
            markup: '',
            mode: mode,
            position: position
        }));
    }

    function restore()
    {
        let state = null;
        try { state = JSON.parse(window.sessionStorage.getItem(storageKey)); }
        catch (error) { return false; }

        if (!state || typeof state !== 'object') return false;

        if (state.mode === 'complete')
        {
            window.sessionStorage.removeItem(storageKey);
            return false;
        }

        const selected = root.querySelector('[data-blueprint="' + state.blueprint + '"]');
        if (selected)
        {
            blueprint = state.blueprint;
            root.querySelectorAll('[data-blueprint]').forEach(function (item) { item.classList.toggle('is-selected', item === selected); });
        }

        if (state.answers && typeof state.answers === 'object')
        {
            questionSteps().forEach(function (question)
            {
                if (typeof state.answers[question.dataset.key] !== 'boolean') return;
                question.querySelector('[data-answer="yes"]').classList.toggle('is-selected', state.answers[question.dataset.key]);
                question.querySelector('[data-answer="no"]').classList.toggle('is-selected', !state.answers[question.dataset.key]);
            });

            if (typeof state.answers.include_demo === 'boolean')
            {
                const input = directoryActions.querySelector('.lsd-switch input[type="checkbox"]');
                if (input) input.checked = state.answers.include_demo;
            }
        }

        if (state.mode !== 'question' && state.mode !== 'review') return false;

        const questions = visibleQuestions();
        if (!questions.length) return false;

        position = state.mode === 'review' ? questions.length - 1 : Math.min(Math.max(parseInt(state.position, 10) || 0, 0), questions.length - 1);
        show(questions[position], 'question');
        return true;
    }

    function show(step, nextMode)
    {
        mode = nextMode;
        const activate = function ()
        {
            steps.forEach(function (item) { item.classList.remove('is-current'); });
            step.classList.add('is-current');
            void step.offsetWidth;
            card.classList.remove('is-transitioning');
            updateProgress();
            updateFooter();
            remember();
            focusDefault(step);
        };

        if (hasRendered)
        {
            card.classList.add('is-transitioning');
            window.setTimeout(activate, 140);
            return;
        }

        hasRendered = true;
        activate();
    }

    function post(data)
    {
        const values = new URLSearchParams();
        Object.keys(data).forEach(function (key)
        {
            if (data[key] && typeof data[key] === 'object')
            {
                Object.keys(data[key]).forEach(function (option)
                {
                    const value = data[key][option];
                    values.append(key + '[' + option + ']', typeof value === 'boolean' ? (value ? '1' : '0') : value);
                });
                return;
            }

            values.append(key, data[key]);
        });

        return window.fetch(config.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: values.toString()
        }).then(function (response) { return response.json(); });
    }

    function notify(response)
    {
        if (response && response.message && window.listdom_toastify) window.listdom_toastify(response.message, 'lsd-error');
    }

    function nextQuestion()
    {
        const questions = visibleQuestions();
        if (position < questions.length - 1)
        {
            position++;
            show(questions[position], 'question');
            return;
        }

        applyPlan();
    }

    function applyPlan()
    {
        if (loading) return;
        loading = true;
        post({action: 'lsd_blueprints_apply', _wpnonce: config.applyNonce, blueprint: blueprint, options: answers()}).then(function (response)
        {
            loading = false;
            if (!response || !response.success)
            {
                notify(response);
                return;
            }

            root.querySelector('[data-completion-markup]').innerHTML = response.markup || '';
            show(steps.find(function (step) { return step.dataset.step === 'complete'; }), 'complete');
        }).catch(function ()
        {
            loading = false;
            notify({message: config.applyError || ''});
        });
    }

    root.addEventListener('click', function (event)
    {
        const target = event.target.closest('[data-blueprint], [data-answer]');
        if (!target || !root.contains(target)) return;

        if (target.hasAttribute('data-blueprint'))
        {
            resetAnswers();
            blueprint = target.dataset.blueprint;
            root.querySelectorAll('[data-blueprint]').forEach(function (item) { item.classList.remove('is-selected'); });
            target.classList.add('is-selected');
            position = 0;
            show(visibleQuestions()[position], 'question');
            return;
        }

        if (target.hasAttribute('data-answer'))
        {
            target.closest('[data-step="question"]').querySelectorAll('[data-answer]').forEach(function (item) { item.classList.remove('is-selected'); });
            target.classList.add('is-selected');
            nextQuestion();
            return;
        }

    });

    root.addEventListener('keydown', function (event)
    {
        if (event.key !== 'Enter' || event.defaultPrevented) return;
        if (card.classList.contains('is-transitioning')) return;

        const choice = event.target.closest('[data-blueprint], [data-answer]');
        if (!choice || !root.contains(choice)) return;

        event.preventDefault();
        choice.click();
    });

    root.addEventListener('change', function (event)
    {
        if (!event.target.matches('.lsd-admin-wizard__directory-actions .lsd-switch input[type="checkbox"]')) return;
        remember();
    });

    root.addEventListener('submit', function (event)
    {
        const form = event.target.closest('.lsd-admin-wizard__email-form');
        if (!form) return;

        event.preventDefault();
        const input = form.querySelector('input[type="email"]');
        if (!input.value.trim() || !input.checkValidity())
        {
            input.focus();
            return;
        }

        const values = {};
        new FormData(form).forEach(function (value, key) { values[key] = value; });
        post(values).then(function (response)
        {
            const status = form.querySelector('.lsd-admin-wizard__email-status');
            if (status)
            {
                status.textContent = response && response.message ? response.message : '';
                status.classList.toggle('is-success', Boolean(response && response.success));
                status.classList.toggle('is-error', !response || !response.success);
            }
            if (response && response.success) form.querySelectorAll('input, button').forEach(function (element) { element.disabled = true; });
        }).catch(function ()
        {
            const status = form.querySelector('.lsd-admin-wizard__email-status');
            if (!status) return;
            status.textContent = config.newsletterError || '';
            status.classList.remove('is-success');
            status.classList.add('is-error');
        });
    });

    back.addEventListener('click', function ()
    {
        if (loading || mode === 'directory') return;
        if (mode === 'complete') { position = Math.max(0, visibleQuestions().length - 1); show(visibleQuestions()[position], 'question'); return; }
        if (position > 0) position--;
        else
        {
            position = 0;
            resetAnswers();
            show(steps.find(function (step) { return step.dataset.step === 'directory'; }), 'directory');
            return;
        }
        show(visibleQuestions()[position], 'question');
    });

    skip.addEventListener('click', function ()
    {
        const no = visibleQuestions()[position].querySelector('[data-answer="no"]');
        if (no) no.click();
    });

    document.getElementById('lsd-admin-wizard-close').addEventListener('click', function () { document.getElementById('lsd-admin-wizard-modal').hidden = false; });
    document.getElementById('lsd-admin-wizard-cancel').addEventListener('click', function () { document.getElementById('lsd-admin-wizard-modal').hidden = true; });
    document.getElementById('lsd-admin-wizard-stay').addEventListener('click', function () { document.getElementById('lsd-admin-wizard-modal').hidden = true; });
    if (!restore())
    {
        updateProgress();
        updateFooter();
        focusDefault(steps.find(function (step) { return step.classList.contains('is-current'); }));
    }
    }

    document.addEventListener('DOMContentLoaded', init);
    window.addEventListener('load', init);
    init();
})();
