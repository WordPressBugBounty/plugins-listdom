/* global jQuery, ListdomButtonLoader, ListdomCountdown */
(function ($) {
    "use strict";

    function decode(value) {
        const padded = value.replace(/-/g, "+").replace(/_/g, "/");
        const binary = atob(padded + "=".repeat((4 - padded.length % 4) % 4));
        return Uint8Array.from(binary, character => character.charCodeAt(0));
    }

    function encode(value) {
        if (!value) return null;
        let binary = "";
        new Uint8Array(value).forEach(byte => { binary += String.fromCharCode(byte); });
        return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
    }

    function notice(element, response) {
        element.replaceChildren();
        element.classList.add("lsd-alert");
        element.classList.toggle("lsd-error", !response.success);
        element.classList.toggle("lsd-info", false);
        element.classList.toggle("lsd-success", !!response.success);
        if (response.countdown && window.ListdomCountdown) {
            ListdomCountdown.mount(element, response.countdown);
        } else element.textContent = response.message || "";
        element.hidden = element.textContent.trim() === "";
    }

    async function post(url, fields) {
        const response = await fetch(url, {
            method: "POST",
            headers: {Accept: "application/json"},
            body: new URLSearchParams(fields),
            credentials: "same-origin"
        });
        return response.json();
    }

    function updateButtonPreview()
    {
        const panel = document.getElementById("lsd-native-button-design");
        if (!panel) return;
        const text = panel.querySelector("#lsd_native_button_show_text");
        const logo = panel.querySelector("#lsd_native_button_show_logo");
        const branded = panel.querySelector("#lsd_native_button_branded").checked;
        if (!text.checked && !logo.checked) text.checked = true;
        text.disabled = !logo.checked;
        logo.disabled = !text.checked;
        [text, logo].forEach(input =>
        {
            // A disabled checked switch still submits its saved value through the hidden field.
            input.previousElementSibling.value = input.disabled && input.checked ? "1" : "0";
            input.setAttribute("aria-describedby", "lsd-native-button-content-help");
        });
        document.querySelectorAll(".lsd-native-button-preview .lsd-provider-button").forEach(button =>
        {
            const image = button.querySelector(".lsd-provider-logo");
            const showText = text.checked || !image;
            if (image) image.hidden = !logo.checked;
            button.querySelector(".lsd-provider-text").hidden = !showText;
            button.classList.toggle("lsd-provider-icon-only", !showText);
            button.classList.toggle("lsd-provider-branded", branded);
        });
    }

    $(updateButtonPreview);
    $(document).on("change", "#lsd-native-button-design input[type=checkbox]", updateButtonPreview);

    $(document).on("click", ".lsd-native-password-toggle", function ()
    {
        const visible = this.getAttribute("aria-pressed") !== "true";
        this.closest("form").querySelectorAll("input[name=password], input[name=password_confirmation]").forEach(input =>
        {
            input.type = visible ? "text" : "password";
        });
        this.setAttribute("aria-pressed", visible ? "true" : "false");
        this.textContent = visible ? this.dataset.hideLabel : this.dataset.showLabel;
    });

    $(document).on("submit", ".lsd-native-registration", function ()
    {
        // Keep password fields recognizable to password managers on normal form submission.
        const toggle = this.querySelector(".lsd-native-password-toggle");
        if (toggle.getAttribute("aria-pressed") === "true") toggle.click();
    });

    $(document).on("click", ".lsd-native-credential-replace", function ()
    {
        const fields = document.getElementById(this.getAttribute("aria-controls"));
        const input = fields.querySelector("input, textarea");
        const editing = this.getAttribute("aria-expanded") !== "true";
        fields.classList.toggle("lsd-util-hide", !editing);
        input.disabled = !editing;
        if (!editing) input.value = "";
        if (editing) fields.parentElement.querySelector(".lsd-native-credential-remove").checked = false;
        this.setAttribute("aria-expanded", editing ? "true" : "false");
        this.textContent = editing ? this.dataset.cancelLabel : this.dataset.replaceLabel;
        if (editing) input.focus();
    });

    $(document).on("change", ".lsd-native-credential-remove", function ()
    {
        if (!this.checked) return;
        const column = this.closest(".lsd-col-5");
        const button = column.querySelector(".lsd-native-credential-replace");
        if (button.getAttribute("aria-expanded") === "true") button.click();
        column.querySelector("input[type=password], textarea").value = "";
    });

    $(document).on("lsd:auth-saved", "#lsd_auth_form", function (event, credentials)
    {
        Object.entries(credentials).forEach(([provider, saved]) =>
        {
            const fields = document.getElementById("lsd_native_" + provider + "_replacement");
            if (!fields) return;
            const column = fields.parentElement;
            const input = fields.querySelector("input, textarea");
            const button = column.querySelector(".lsd-native-credential-replace");
            const hint = column.querySelector(".lsd-native-credential-hint");
            column.querySelector(".lsd-native-credential-saved").classList.toggle("lsd-util-hide", !saved);
            column.querySelector(".lsd-native-credential-remove").checked = false;
            fields.classList.toggle("lsd-util-hide", saved);
            input.disabled = saved;
            input.value = "";
            button.setAttribute("aria-expanded", "false");
            button.textContent = button.dataset.replaceLabel;
            hint.textContent = saved ? hint.dataset.savedText : hint.dataset.emptyText;
        });
        this.querySelectorAll(".lsd-native-message").forEach(element => notice(element, {success: 1, message: ""}));
    });

    $(document).on("submit", ".lsd-native-magic-form", async function (event)
    {
        event.preventDefault();
        const form = this;
        const button = form.querySelector("button[type=submit]");
        if (button.disabled) return;
        const details = form.closest(".lsd-native-magic");
        const output = details.querySelector(".lsd-native-message");
        const fields = new FormData(form);
        const loader = typeof ListdomButtonLoader === "function" ? new ListdomButtonLoader($(button)) : null;
        loader?.start(button.innerHTML);
        button.disabled = true;

        try
        {
            const response = await post(form.action, fields);
            notice(output, response);
            if (response.success)
            {
                form.classList.add("lsd-util-hide");
                details.querySelector(".lsd-native-magic-back").hidden = false;
            }
        }
        catch (error)
        {
            notice(output, {success: 0, message: form.dataset.error});
        }
        finally
        {
            loader?.stop();
            button.disabled = false;
            if (details.open && !output.hidden) output.focus();
        }
    });

    $(document).on("click", ".lsd-native-magic-back button", function ()
    {
        const details = this.closest(".lsd-native-magic");
        const form = details.querySelector(".lsd-native-magic-form");
        form.reset();
        form.classList.remove("lsd-util-hide");
        notice(details.querySelector(".lsd-native-message"), {success: 1, message: ""});
        this.parentElement.hidden = true;
        details.open = false;
        const login = details.closest(".lsd-login-wrapper, .lsd-dashboard-auth");
        (login?.querySelector('input[name="log"]') || details.querySelector("summary")).focus();
    });

    function clearPasskeyReturn()
    {
        const url = new URL(window.location.href);
        if (url.hash !== "#lsd-account-passkeys" || !url.searchParams.has("lsd_auth_flow")) return;
        url.searchParams.delete("lsd_auth_flow");
        url.hash = "";
        window.history.replaceState(window.history.state, "", url);
    }

    function resetPasskeyEnrollment(form)
    {
        const add = form.closest(".lsd-native-passkey-controls").querySelector(".lsd-native-passkey-add");
        const name = add.querySelector('input[name="label"]');
        name.value = form.elements.label.value;
        form.remove();
        add.hidden = false;
        clearPasskeyReturn();
        name.focus();
    }

    $(document).on("click", ".lsd-native-passkey-restart", function (event) {
        event.preventDefault();
        const form = this.closest("form");
        if (form.querySelector("button[type=submit]").disabled) return;
        resetPasskeyEnrollment(form);
    });

    $(document).on("submit", ".lsd-native-passkey", async function (event) {
        const form = this;
        const button = form.querySelector("button[type=submit]");
        const controls = form.closest(".lsd-native-passkey-controls");
        const output = (controls || form).querySelector(".lsd-native-message");
        const fields = Object.fromEntries(new FormData(form));
        if (!button.disabled && fields.reauth_provider) return;
        event.preventDefault();
        if (button.disabled) return;
        const fallback = form.dataset.passkeyError || form.closest(".lsd-native-methods")?.dataset.passkeyError || "Passkey authentication is unavailable.";
        const loader = typeof ListdomButtonLoader === "function" ? new ListdomButtonLoader($(button)) : null;
        if (!window.PublicKeyCredential || !window.isSecureContext) {
            notice(output, {success: 0, message: fallback});
            if (controls) output.focus({preventScroll: true});
            return;
        }
        const passkeyUrl = form.dataset.passkeyUrl || form.action;
        const operation = form.dataset.operation;
        const resumed = controls && !!fields.proof;
        if (controls) notice(output, {success: 1, message: ""});
        loader?.start(button.innerHTML);
        button.disabled = true;
        try {
            if (controls && !resumed) {
                const reauth = await post(form.action, fields);
                if (!reauth.success) {
                    notice(output, reauth);
                    return;
                }
                fields.proof = reauth.proof;
                form.elements.current_password.value = "";
                if (form.elements.code) form.elements.code.value = "";
            }
            const options = await post(passkeyUrl, {
                _wpnonce: fields._wpnonce,
                context: fields.context,
                proof: fields.proof || "",
                operation
            });
            if (!options.success) {
                notice(output, options);
                return;
            }
            const publicKey = options.options.publicKey;
            publicKey.challenge = decode(publicKey.challenge);
            const create = operation === "create_options";
            if (create) {
                publicKey.user.id = decode(publicKey.user.id);
                (publicKey.excludeCredentials || []).forEach(item => { item.id = decode(item.id); });
            } else (publicKey.allowCredentials || []).forEach(item => { item.id = decode(item.id); });
            const credential = create ? await navigator.credentials.create({publicKey}) : await navigator.credentials.get({publicKey});
            if (!credential) throw new Error("cancelled");
            const payload = {id: credential.id, type: credential.type, response: {}};
            ["clientDataJSON", "attestationObject", "authenticatorData", "signature", "userHandle"].forEach(key => {
                if (credential.response[key] !== undefined) payload.response[key] = encode(credential.response[key]);
            });
            const result = await post(passkeyUrl, {
                _wpnonce: fields._wpnonce,
                operation: "finish",
                challenge: options.challenge,
                credential: JSON.stringify(payload),
                label: fields.label || ""
            });
            if (result.success && controls && result.html) {
                controls.innerHTML = result.html;
                const status = controls.querySelector(".lsd-native-message");
                notice(status, result);
                clearPasskeyReturn();
                status.focus({preventScroll: true});
            } else if (result.success && result.redirect) window.location.assign(result.redirect);
            else notice(output, result);
        } catch (error) {
            notice(output, {success: 0, message: fallback});
        } finally {
            loader?.stop();
            button.disabled = false;
            if (resumed && form.isConnected) resetPasskeyEnrollment(form);
            if (controls && output.isConnected && !output.hidden) output.focus({preventScroll: true});
        }
    });

    $(document).on("click", ".lsd-native-diagnose", async function () {
        const button = this;
        const output = button.nextElementSibling;
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.url, {credentials: "same-origin"});
            notice(output, await response.json());
        } catch (error) {
            notice(output, {success: 0, message: button.dataset.error || "Unable to check this connection."});
        } finally { button.disabled = false; }
    });
})(jQuery);
