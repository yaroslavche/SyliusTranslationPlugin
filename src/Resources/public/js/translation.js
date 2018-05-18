var SyliusTranslationPlugin = {
    editTranslation: (dataContainer) => {
        let
            localeCode = dataContainer.data('locale-code'),
            domain = dataContainer.data('domain'),
            messageDomain = dataContainer.data('message-domain'),
            translation = dataContainer.data('translation'),
            newTranslation = dataContainer.find('.translation_input').val();

        if (translation == newTranslation) return;
        dataContainer.find('.ui.icon.input').addClass('loading');
        SyliusTranslationPlugin.setMessage(localeCode, domain, messageDomain, newTranslation, (result) => {
            dataContainer.find('.ui.icon.input').removeClass('loading');
            if (result) {
                if (translation == '')
                    dataContainer.find('.icon.circle.outline.red').removeClass('red').addClass('check green');
                if (dataContainer.find('.icon.cog.teal').length == 0)
                    dataContainer.find('.icon.circle.check.green').after('<i class="icon cog teal"></i>');
            } else {
                alert('something wrong');
            }
        });
    },
    setMessage: (localeCode, domain, messageDomain, translation, callback) => {
        let result = false;
        jQuery.ajax({
                method: 'POST',
                url: '/admin/translation/setMessage',
                data: {
                    'localeCode': localeCode,
                    'domain': domain,
                    'messageDomain': messageDomain,
                    'translation': translation
                },
                dataType: 'json'
            })
            .done(function(response) {
                callback(response.status == 'success');
            });
    },
    addDomain: function(dataContainer) {
        SyliusTranslationPlugin.showModal(dataContainer, function() {
            let
                domainInput = jQuery('#addDomainModal_domainInput'),
                localeCode = dataContainer.data('locale-code');
            if (domainInput.length > 0) {
                jQuery.ajax({
                        method: 'POST',
                        url: '/admin/translation/addDomain',
                        data: {
                            'localeCode': localeCode,
                            'domain': domainInput.val()
                        },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.status == 'success') {
                            domainInput.val('');
                            SyliusTranslationPlugin.reloadDomainList()
                        } else {
                            alert('something wrong, try again');
                        }
                    });
            }
        });
    },
    addDomainMessage: function(dataContainer) {
        SyliusTranslationPlugin.showModal(dataContainer, function() {
            let
                localeCode = dataContainer.data('locale-code'),
                domain = dataContainer.data('domain'),
                messageDomainInput = jQuery('#addDomainMessageModal_messageDomainInput'),
                translationInput = jQuery('#addDomainMessageModal_translationInput');
            if (messageDomainInput.length > 0 && translationInput.length > 0) {
                jQuery.ajax({
                        method: 'POST',
                        url: '/admin/translation/addDomainMessage',
                        data: {
                            'localeCode': localeCode,
                            'domain': domain,
                            'messageDomain': messageDomainInput.val(),
                            'translation': translationInput.val()
                        },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.status == 'success') {
                            messageDomainInput.val('');
                            translationInput.val('');
                            SyliusTranslationPlugin.reloadMessageList();
                        } else {
                            alert('something wrong, try again');
                        }
                    });
            }
        });
    },
    showModal: function(modalContainer, callback) {
        if (modalContainer.length > 0)
            modalContainer
            .modal('setting', 'closable', false)
            .modal({
                onApprove: function(element) {
                    callback(element);
                }
            }).modal('show');
    },
    reloadMessageList: function() {
        console.log('implement reloadMessageList');
        location.reload();
    },
    reloadDomainList: function() {
        console.log('implement reloadDomainList');
        location.reload();
    }
};

jQuery(document).ready(function() {
    if (jQuery('.progress').length > 0) jQuery('.progress').progress();
    jQuery('.translation_input').keypress(function(e) {
        if (e.which == 13) {
            SyliusTranslationPlugin.editTranslation(jQuery(this).parent().parent().parent());
        }
    });
});
