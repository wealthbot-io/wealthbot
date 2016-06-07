/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.10.13
 * Time: 16:08
 * To change this template use File | Settings | File Templates.
 */

$(function() {
    addDocusignEventListeners();
});

function addDocusignEventListeners() {
    window.addEventListener('message', signatureListenerCallback, false);
}

function signatureListenerCallback(event) {
    var msg = event.data.message;
    if (msg) {
        switch (msg) {
            case 'account_signature':
                onAccountSignature(event.data);
                break;
        }
    }

    return false;
}

function onAccountSignature(data) {
    if (data.is_completed) {
        var accountsList = $('.account-signature-list');
        if (accountsList.length > 0) {
            var item;

            if (data.application_id) {
                item = accountsList.find('li[data-application="' + data.application_id + '"]');
            } else if (data.signature_id) {
                item = accountsList.find('li[data-signature="' + data.signature_id + '"]');
            }

            if (item.length > 0) {
                var link = item.find('a');
                if (link.length > 0) {
                    var span = '<span class="account-status-lnk completed">' + link.text() + '</span>';

                    item.html(span);
                }
            }
        }
    } else {
        var jointAccountOwnerMessage = $('#review_joint_account_owner');
        if (jointAccountOwnerMessage.length > 0 && jointAccountOwnerMessage.hasClass('hide')) {
            jointAccountOwnerMessage.removeClass('hide');
        }
    }
}
