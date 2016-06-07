How to use Spinner.
========================


When you need preventing click on any objects while you are waiting some objects loading, you can use spinner.
For example you waiting form data loading to server and for that time blocking the form:

    $('form').spinner128();

After form will have loaded you have to unblock form (spinner removing):

    $('form').spinner128(false);

The best practice is using spinner for block button of entire form before ajax action and unblock after
(in "success" and "error" callbacks).

    $('form').spinner128(false);

    $.ajax({
        url: Routing.generate('rx_ria_api_some_action'),
        type: 'POST',
        data: {
            form_main: data
        },
        success: function() {
            $('form').spinner128(false);

            someWindow.close();
        },
        error: function(xnr) {
            $('form').spinner128(false);
            var response = JSON.parse(xnr.responseText);

            ErrorHandler.handle(response, '.somePage form', {
                subForms: {
                    fieldSelector: '.subFormRegion table tbody tr',
                    type: 'collection'
                }
            });
        }
    });

Sizes
-----

There are 3 predefined sizes of spinner image:

    spinner128()
    spinner32()
    spinner16()

Autosize
--------

When you need to block, for example, line in form and that line isn't showing now (or can be sized)
you can use autosized spinner to parent.

For that you need to set ``position:relative`` for any parent element that entire will be blocked

In css:

    form tr{
        position:relative
    }

And for example you have HTML like:

    <form>
    ....
    <table>
        ....
        <tr>
            <input type="submit" id="submit">
        </tr>
    </table>

In that case in js you have to use next:

    $('#submit').spinnerFill(); //turn on spinner over TR tag

    ...

    $('#submit').spinnerFill(false); //turn off spinner over TR tag



Files
-----

Functions is placed in /src/Wealthbot/RiaBundle/Resources/public/js/src/Util.js
