How to use money and fee.
========================

Formats
-------

Format of fee: `.0000`

Format of money: `12,333,444.99`


Auto formatting on keyboard events.
------------------------------------

When input is ready to work apply autoNumeric to it:

    $('input#money').autoNumeric({mDec: '0', aSep: ',', aDec: '.', vMax: '99999999999999'});

where:

*   mDec - count of decimals,
*   aSep - thousands separator,
*   aDec - decimal separator,
*   vMax - maximal value.

Format in underscore templates
------------------------------

For showing data from model to view you can use prototypes:


    .formatMoney(decimals, decimalSeparator, thousandsSeparator)


or just:

    .formatMoney()

For example, template can be like that:

    <input type="text" name="myMoney" value="<%= maxBillingValue.formatMoney() %>">

Get value from formatted string
-------------------------------

    .moneyToFloat(deciamlSeparator, thousandSeparator)

For example, when you set your model data from inputs, you can use next pattern:

    this.model.set({
        maxBillingValue: this.ui.moneyInput.val().moneyToFloat()
    });

As result, your model will contain a float number (not formatted string).

If you need integer value, you can use for example Math.floor:

    var nextValue = Math.floor( $('.moneyInput').val().moneyToFloat() );

Using decimal fees
------------------

You can bind any input with event `change` and convert it to fee:

    $('.fee').on('change', function(e){
        $(this).val( $(this).val().formatFee() );
    });

You will get string like `.0100`


When you needs to get float from this formatted string:

    var feeValue = $('.fee').feeToFloat();

End
------------------
All prototypes had placed in /src/Wealthbot/RiaBundle/Resources/public/js/src/fields.js
