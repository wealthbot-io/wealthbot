$(function() {
    function insertParams(url, params) {
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                key = encodeURI(key);
                var value = encodeURI(params[key]);
                var kvp = url.substr(1).split('&');
                var i = kvp.length;
                var x;
                while (i--) {
                    x = kvp[i].split('=');
                    if (x[0] == key) {
                        x[1] = value;
                        kvp[i] = x.join('=');
                        break;
                    }
                }
                if (i < 0) {
                    kvp[kvp.length] = [key, value].join('=');
                }
            }
        }
        return kvp.join('&');
    }

    function setURL(params) {
        if ($('#ria_dashboard_client_menu').length) {
            var url = $('#ria_dashboard_client_menu div ul li.active a').get(0);
            ajaxLoadPage(url.pathname + '?' + insertParams(url.search, params));
        } else {
            document.location.search = insertParams(document.location.search, params);
        }
    }

    $(document).on('change','#select_account_type', function() {
        setURL({
            'account_id': $(this).val()
        });
    });

    $(document).on('change','#year', function() {
        setURL({
            'year': $(this).val()
        });
    });

});
