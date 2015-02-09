$(function() {
    addCompleteTransferCustodianEvent('.ria-find-clients-with-prospects-form-type-search', '', function(item) {
        window.location.href = item.redirect_url;
    });
});