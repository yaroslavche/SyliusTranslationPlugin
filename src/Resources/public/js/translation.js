var SyliusTranslationPlugin = {
    editTranslation: (dataContainer) => {
        let
            localeCode = dataContainer.data('locale-code'),
            domain = dataContainer.data('domain'),
            messageDomain = dataContainer.data('message-domain'),
            translation = dataContainer.data('translation'),
            newTranslation = dataContainer.find('.translation_input').val();

        if (translation == newTranslation) return;
        dataContainer.find('.edit.link.icon').addClass('loading');
        SyliusTranslationPlugin.setMessage(localeCode, domain, messageDomain, newTranslation, (result) => {
            dataContainer.find('.edit.link.icon').removeClass('loading');
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
                url: '/admin/translation/set',
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
