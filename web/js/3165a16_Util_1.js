Util = {

    spinner: function (enable, elementSelector, className, spinnerHalfSize, fillParentRelative) {
        var elementSelector = _.isUndefined(elementSelector) ? 'body' : elementSelector;
        if (_.isEmpty(className)) {
            className = 'util-spinner';
        }
        if (_.isUndefined(spinnerHalfSize)) {
            spinnerHalfSize = 64;
        }
        var $el = $(elementSelector);
        var wasEnabled = $el.prev().hasClass(className);
        if ($el.length > 0 && !wasEnabled && enable) {

            var pos = $el.offset();
            var width = $el.fullWidth();
            var height = $el.fullHeight();

            var loader = $('<div class="' + className + '"></div>');
            loader.append('<i class="util-spinner-image"></i>');
            $el.before(loader);

            if (fillParentRelative !== undefined) {
                loader.css({
                    'width': '100%',
                    'height': '100%',
                    'position': 'absolute'
                });
                var img = loader.find('.util-spinner-image');
                img.css({
                    'top': '50%',
                    'margin-top': '-16px',
                    'margin-left': 'auto',
                    'position': 'relative'
                });
            }else{
                loader.width(width).height(height).offset(pos);

                var iL = pos.left + (width / 2 - spinnerHalfSize);
                var iT = pos.top + (height / 2 - spinnerHalfSize);
                loader.find('.util-spinner-image').offset({left: iL, top: iT});
            }


        }else if (wasEnabled && !enable) {

            $el.prev().remove();
        }
    },
    spinner16: function(enable, elementSelector){
        return this.spinner(enable, elementSelector, 'util-spinner spinner16', 8);
    },
    spinner32: function(enable, elementSelector){
        return this.spinner(enable, elementSelector, 'util-spinner spinner32', 16);
    },
    spinnerFill: function(enable, elementSelector){
        return this.spinner(enable, elementSelector, 'util-spinner spinner32', 16, true);
    },
    statusMessage: function(head, message){
        if (message.responseText !== undefined) {
            try {
                var data = JSON.parse(message.responseText);
                var t = $('<div/>', {text: data.status});
                message = t.html();
            } catch (e) {
                message = message.responseText;
            }
        }
        var headBlock = $('<h3 />', {text: head});
        var textBlock = $('<p />', {html: message});
        var statusWin = $('<div />', {
            class: 'status-message'
        });
        statusWin.append(headBlock);
        statusWin.append(textBlock);
        $('body').append(statusWin);
        statusWin
            .css('right', '-30%')
            .css('opacity', 0);
        statusWin.animate({
            right:'1%',
            opacity: 1
        });
        var x = 30;
        var timer = null;
        var statusWinOff = function(){
            statusWin.animate({
                opacity: 0
            }, function(){
                statusWin.remove();
            });
        };
        var check = function(){
            timer = null;
            x -= 1;
            if (x==0) {
                statusWinOff();
            }else{
                timer = setTimeout(check, 1000);
            }
        };
        statusWin.on('mouseenter', function(){
            if (timer !== null) {
                clearTimeout(timer);
                timer = null;
            }
            statusWin.on('mouseleave', function(){
                statusWinOff();
            });
        });
        check();
    }
};

$.fn.fullWidth = function () {
    var w = 0;
    $(this).each(function(){
        var theDiv = $(this);
        var totalWidth = theDiv.width();
        totalWidth += parseInt(theDiv.css("padding-left"), 10) + parseInt(theDiv.css("padding-right"), 10); //Total Padding Width
        totalWidth += parseInt(theDiv.css("borderLeftWidth"), 10) + parseInt(theDiv.css("borderRightWidth"), 10); //Total Border Width
        w += totalWidth;
    });
    return w;
};

$.fn.fullHeight = function () {
    var h = 0;
    $(this).each(function(){
        var theDiv = $(this);
        var totalHeight = theDiv.height();
        totalHeight += parseInt(theDiv.css("padding-top"), 10) + parseInt(theDiv.css("padding-bottom"), 10); //Total Padding Width
        totalHeight += parseInt(theDiv.css("borderTopWidth"), 10) + parseInt(theDiv.css("borderBottomWidth"), 10); //Total Border Width
        h += totalHeight;
    });
    return h;
};

$.fn.spinner128 = function(enabled){
    if (_.isUndefined(enabled)) {
        enabled = true;
    }
    $(this).each(function(){
        Util.spinner(enabled, this);
    });
};

$.fn.spinner16 = function(enabled){
    if (_.isUndefined(enabled)) {
        enabled = true;
    }
    $(this).each(function(){
        Util.spinner16(enabled, this);
    });
};

$.fn.spinner32 = function(enabled){
    if (_.isUndefined(enabled)) {
        enabled = true;
    }
    $(this).each(function(){
        Util.spinner32(enabled, this);
    });
};

$.fn.spinnerFill = function(enabled){
    if (_.isUndefined(enabled)) {
        enabled = true;
    }
    $(this).each(function(){
        Util.spinnerFill(enabled, this);
    });
};

// Redirect to url
Util.redirect = function(url) {
    window.location.href = url;
}