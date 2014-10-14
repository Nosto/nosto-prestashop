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
    $(".nostotagging select#nostotagging_language").change(function() {
        var langId = parseInt($(this).val()),
            $currentLanguage = $('#nostotagging_current_language');
        $currentLanguage.val(langId);
    });
});
