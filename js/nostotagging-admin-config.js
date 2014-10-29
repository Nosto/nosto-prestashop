$(document).ready( function() {
    // Change event handler for "Do you have a Nosto account?".
    $(".nostotagging input[name='nostotagging_has_account']").change(function() {
        var val = parseInt($(this).val()),
            $existingAccount = $("#nostotagging_existing_account_group"),
            $newAccount = $("#nostotagging_new_account_group");
        if (val === 1) {
            $existingAccount.show();
            $newAccount.hide();
        } else {
            $existingAccount.hide();
            $newAccount.show();
        }
    });
    // Change event handler for "Edit different shop language:".
    $("#nostotagging_language").change(function() {
        var langId = parseInt($(this).val()),
            $currentLanguage = $('#nostotagging_current_language'),
            $form = $('form.nostotagging');
        $currentLanguage.val(langId);
        $form.submit();
    });
    // Click event handler for the "Account settings".
    $("#nostotagging_account_setup").click(function(event) {
        event.preventDefault();
        var $iframe = $('#nostotagging_iframe'),
            $installedView = $('#nostotagging_installed');
        $installedView.show();
        $iframe.hide();
    });
    // Click event handler for the "Back" button on the "You have installed Nosto...." page.
    $('#nostotagging_back_to_iframe').click(function(event) {
        event.preventDefault();
        var $iframe = $('#nostotagging_iframe'),
            $installedView = $('#nostotagging_installed');
        $iframe.show();
        $installedView.hide();
    });
    // Init the iframe re-sizer.
    $('#nostotagging_iframe').iFrameResize();
});
