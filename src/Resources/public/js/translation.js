jQuery(document).ready(function() {
    jQuery('.progress').progress();
});

function stpEditMessage(localeCode, domain, messageDomain, translation)
{
    if(newTranslation = prompt(messageDomain + '\n(' + localeCode + ')', translation))
    {
        jQuery.post(
            '/admin/translation/set',
            {
                'localeCode': localeCode,
                'domain': domain,
                'messageDomain': messageDomain,
                'translation': newTranslation
            }, function(response) {
                if(response.status == 'success')
                {
                    alert('ok');
                    // change data
                }
                else alert('something wrong');
            }, 'json');
    }
}
