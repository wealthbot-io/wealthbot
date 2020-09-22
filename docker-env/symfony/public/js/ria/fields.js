/**
 * Formats number value as money
 *
 * @param c Number of fractional digits
 * @param d Decimal separator
 * @param t Thousands separator
 * @return {string}
 */
Number.prototype.formatMoney = function(c, d, t){
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "." : d, t = t == undefined ? "," : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

String.prototype.replaceAll = function(search, replace){
    return this.split(search).join(replace);
};

String.prototype.formatMoney = function(c, d, t){
    return parseFloat(this.toString()).formatMoney();
};

String.prototype.moneyToFloat = function(deciamlSeparator, thousandSeparator){
    var s = this.toString();
    deciamlSeparator = deciamlSeparator == undefined ? "." : deciamlSeparator;
    thousandSeparator = thousandSeparator == undefined ? "," : thousandSeparator;
    if (thousandSeparator !== undefined) {
        s = s.replaceAll(thousandSeparator, '');
    }
    s = s.replaceAll(deciamlSeparator, '.').replace(/^[^\d]+|[^\d]+$/g, '');
    if (!s || isNaN(s)) {
        return 0;
    }
    return parseFloat(s).toFixed(2);
};

Number.prototype.formatFee = function(){
    var fee = this - Math.floor(this);
    fee = isNaN(fee) ? 0 : fee;
    return fee == 0 ? '' : fee.toFixed(4).replace('0.', '.');
};

String.prototype.formatFee = function(){
    var n = parseFloat(this.toString());
    return n.formatFee();
};

String.prototype.feeToFloat = function(){
    var str = this.toString();
    return parseFloat(str.replace(/^\./, '0.'));
};

String.prototype.feeToInt = function(){
    var fee = parseInt(this.toString());
    return fee.feeToInt();
};

$('input.auto-numeric').focus(function() {
    var elem = $(this);
    var isAutoNumeric = elem.autoNumeric('getSettings') ? true : false;
    var value = parseFloat(elem.val()).toFixed(4);

    if (isAutoNumeric && value < 0.0001) {
        elem.autoNumeric('destroy');
        elem.val('');
    }
});

$('input.auto-numeric').blur(function() {
    var elem = $(this);
    var isAutoNumeric = elem.autoNumeric('getSettings') ? true : false;
    var value = elem.val();

    if (value.length < 1 || parseFloat(value).toFixed(4) < 0.0001) {
        elem.val('0.00');
    }

    if (!isAutoNumeric) {
        elem.autoNumeric('init', {vMax: '99999999999999.99'});
    } else {
        elem.autoNumeric('update', {vMax: '99999999999999.99'});
    }

});