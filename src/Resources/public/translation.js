jQuery(document).ready(function() {
    let localeSelect = jQuery('#acme_sylius_translation_plugin_locale');
    if (localeSelect.length != 0) {
        localeSelect.on('change', function() {
            // hardcoded
            location.href = '/admin/translation/' + localeSelect.val();
        });
    }
});
