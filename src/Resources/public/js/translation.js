const SyliusTranslationPlugin = {
    setMessage(localeCode, domain, id, translation) {
        (async () => {
            const rawResponse = await fetch('/admin/translation/setMessage', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'localeCode': localeCode,
                    'domain': domain,
                    'id': id,
                    'translation': translation
                })
            });
            const response = await rawResponse.json();
            if (response.status === 'success') this.applyFilters();
            this.showResponse(response);
        })();
    },
    showResponse(response) {
        let colors = {
            error: '#ff5751',
            success: '#19c3aa',
            info: '#55a9ee',
            warning: '#f2711c'
        };
        let icons = {
            error: 'times circle',
            success: 'checkmark circle',
            info: 'info',
            warning: 'bell'
        };

        $.uiAlert({
            introText: response.status,
            messageText: response.message,
            textColor: colors[response.status] || '#ff5751',
            icon: icons[response.status] || 'times circle',
            time: 5
        });
    },
    showSetMessageModal(modalContainer) {
        if (modalContainer.length > 0)
            modalContainer
                .modal('setting', 'closable', false)
                .modal({
                    onApprove: () => {
                        let
                            localeCode = modalContainer.data('locale-code'),
                            domain = modalContainer.data('domain'),
                            translationIdInput = document.getElementById('addDomainMessageModal_translationIdInput'),
                            translationInput = document.getElementById('addDomainMessageModal_translationInput');
                        if (translationIdInput.value.length > 0 && translationInput.value.length > 0) {
                            this.setMessage(localeCode, domain, translationIdInput.value, translationInput.value);
                        }
                    }
                }).modal('show');
    },
    selectDomain(domain) {
        let domainElement = document.getElementById('filterDomain');
        domainElement.value = domain;
        this.applyFilters();

        let domainFilterItems = document.getElementsByClassName('domain-item');
        Array.from(domainFilterItems).forEach((element) => {
            let iconElement = element.querySelector('.icon');
            iconElement.classList.remove('open');
            iconElement.classList.remove('teal');
            if(element.getAttribute('data-domain') === domain)
            {
                iconElement.classList.add('open');
                iconElement.classList.add('teal');
            }
        });
    },
    applyFilters() {
        let domainElement = document.getElementById('filterDomain');
        let idElement = document.getElementById('filterId');
        let translatedElement = document.getElementById('filterTranslated');
        let untranslatedElement = document.getElementById('filterUntranslated');
        let customElement = document.getElementById('filterCustom');
        this.filterTranslations({
            domain: domainElement.value,
            id: idElement.value,
            isTranslated: translatedElement.parentElement.classList.contains('checked'),
            isUntranslated: untranslatedElement.parentElement.classList.contains('checked'),
            isCustom: customElement.parentElement.classList.contains('checked')
        });
    },
    filterTranslations(filters) {
        const applyFilters = Object.assign({}, {
            domain: '',
            id: '',
            isTranslated: true,
            isUntranslated: true,
            isCustom: true
        }, filters);

        let messageListTitle = document.getElementById('message_list_title');
        let title = 'All ';
        if (applyFilters.domain.length > 0) title = applyFilters.domain + ' ';
        if (applyFilters.id.length > 0) title += applyFilters.id + ' ';
        let flags = [];
        if (applyFilters.isTranslated) flags.push('translated');
        if (applyFilters.isUntranslated) flags.push('untranslated');
        if (applyFilters.isCustom) flags.push('custom');
        if (flags.length > 0) title += `(${flags.join(', ')})`;
        messageListTitle.innerText = title;

        let translationRows = document.getElementsByClassName('translationRow');
        Array.from(translationRows).forEach((translationElement) => {
            translationElement.classList.remove('hidden');

            let isTranslated = translationElement.getAttribute('data-is-translated') === '1';
            if (!applyFilters.isTranslated && isTranslated) translationElement.classList.add('hidden');
            if (!applyFilters.isUntranslated && !isTranslated) translationElement.classList.add('hidden');

            let isCustom = translationElement.getAttribute('data-is-custom') === '1';
            if (!applyFilters.isCustom && isCustom) translationElement.classList.add('hidden');

            if (applyFilters.id.length > 0) {
                let id = translationElement.getAttribute('data-id');
                if (id !== applyFilters.id) translationElement.classList.add('hidden');
            }

            if (applyFilters.domain.length > 0) {
                let domain = translationElement.getAttribute('data-domain');
                if (domain !== applyFilters.domain) translationElement.classList.add('hidden');
            }
        });
    },
    editTranslation(element) {
        let row = element.parentElement.parentElement.parentElement;
        let currentTranslation = row.getAttribute('data-translation');
        let translation = element.value;
        if (currentTranslation === translation) return;

        let localeCode = row.getAttribute('data-locale-code');
        let domain = row.getAttribute('data-domain');
        let id = row.getAttribute('data-id');
        this.setMessage(localeCode, domain, id, translation);
    }
};

// todo: remove jquery dependency (uiAlert move to separate)
jQuery(document).ready(function () {
    jQuery('.progress').progress();
    jQuery('.translation_input').keypress(function (e) {
        if (e.which == 13) {
            SyliusTranslationPlugin.editTranslation(this);
        }
    });

    // todo: load catalogue (/admin/translation/getMessageCatalogue ? {localeCode: 'en_US'}) if there is container exists

    let filterDomainElement = document.getElementById('filterDomain');
    let filterIdElement = document.getElementById('filterId');
    let filterTranslatedElement = document.getElementById('filterTranslated');
    let filterUntranslatedElement = document.getElementById('filterUntranslated');
    let filterCustomElement = document.getElementById('filterCustom');

    if (filterDomainElement) filterDomainElement.onchange = (event) => SyliusTranslationPlugin.applyFilters();
    if (filterIdElement) filterIdElement.onchange = (event) => SyliusTranslationPlugin.applyFilters();
    if (filterTranslatedElement) filterTranslatedElement.onchange = (event) => SyliusTranslationPlugin.applyFilters();
    if (filterUntranslatedElement) filterUntranslatedElement.onchange = (event) => SyliusTranslationPlugin.applyFilters();
    if (filterCustomElement) filterCustomElement.onchange = (event) => SyliusTranslationPlugin.applyFilters();
    if (
        filterDomainElement &&
        filterIdElement &&
        filterTranslatedElement &&
        filterUntranslatedElement &&
        filterCustomElement
    ) SyliusTranslationPlugin.applyFilters();
});

jQuery.uiAlert = function (options) {
    const setUI = Object.assign({}, {
        introText: '',
        messageText: '',
        textColor: '#19c3aa',
        backgroundColor: '#fff',
        position: 'top-right',
        icon: '',
        time: 5,
        permanent: false
    }, options);

    let randomId = generateRandomString(32);

    // UiAlert message block
    let UiAlertContainer = document.createElement('div');
    UiAlertContainer.id = 'ui-alert-' + randomId;
    UiAlertContainer.classList.add('ui');
    UiAlertContainer.classList.add('icon');
    UiAlertContainer.classList.add('message');
    UiAlertContainer.style.backgroundColor = setUI.backgroundColor;
    UiAlertContainer.style.boxShadow = '0 0 0 1px rgba(255,255,255,.5) inset,0 0 0 0 transparent';

    // UiAlert icon inside message block
    let UiAlertIcon = document.createElement('i');
    setUI.icon.split(' ').forEach((icon) => {
        UiAlertIcon.classList.add(icon);
    });
    UiAlertIcon.classList.add('icon');
    UiAlertIcon.style.color = setUI.textColor;

    // UiAlert close button inside message block
    let UiAlertCloseButton = document.createElement('i');
    UiAlertCloseButton.classList.add('close');
    UiAlertCloseButton.classList.add('icon');
    UiAlertCloseButton.onclick = () => {
        $(UiAlertContainer).remove();
    };

    // UiAlert text container inside message block
    let UiAlertTextContainer = document.createElement('div');
    UiAlertTextContainer.style.color = setUI.textColor;
    UiAlertTextContainer.style.marginRight = '10px';
    let introText = document.createElement('div');
    introText.innerText = setUI.introText;
    introText.classList.add('header');
    let messageText = document.createElement('p');
    messageText.innerText = setUI.messageText;

    // append icon, close button and text into message block
    UiAlertContainer.appendChild(UiAlertIcon);
    UiAlertContainer.appendChild(UiAlertCloseButton);
    UiAlertContainer.appendChild(UiAlertTextContainer);
    UiAlertTextContainer.appendChild(introText);
    UiAlertTextContainer.appendChild(messageText);

    // search global positioning container and create if not exists
    let UiAlertMessagesContainer = document.getElementsByClassName(`ui-alert-content-${setUI.position}`)[0];
    if (typeof UiAlertMessagesContainer === 'undefined') {
        UiAlertMessagesContainer = document.createElement('div');
        UiAlertMessagesContainer.classList.add('ui-alert-content');
        UiAlertMessagesContainer.classList.add(`ui-alert-content-${setUI.position}`);
        document.body.appendChild(UiAlertMessagesContainer);
    }
    // append message block into global container
    UiAlertMessagesContainer.appendChild(UiAlertContainer);

    uiAlertShow(UiAlertContainer);
    if (setUI.permanent === false) {
        let timer = 0;
        UiAlertContainer.onmouseenter = () => {
            clearTimeout(timer);
        };
        UiAlertContainer.onmouseleave = () => {
            uiAlertHide(UiAlertContainer);
        };
        uiAlertHide(UiAlertContainer);
    }

    function uiAlertShow(element) {
        setTimeout(function () {
            element.style.opacity = 1;
        }, 300);
    }

    function uiAlertHide(element) {
        setTimeout(function () {
            $(element).remove();
        }, (setUI.time * 1000));
    }

    function generateRandomString(length) {
        let text = '';
        const possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        length = parseInt(length);
        for (let i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    }

};