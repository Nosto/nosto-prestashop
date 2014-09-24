$(document).ready( function() {
    // Change event handler for "Already have a Nosto account?".
    $(".nostotagging input[name='nostotagging_has_account']").change(function() {
        var val = parseInt($(this).val()),
            $accountName = $("#nostotagging_account_name_group"),
            $newAccount = $("#nostotagging_new_account_group");
        if (val === 1) {
            $accountName.show();
            $newAccount.hide();
        } else {
            $accountName.hide();
            $newAccount.show();
        }
    });
});
