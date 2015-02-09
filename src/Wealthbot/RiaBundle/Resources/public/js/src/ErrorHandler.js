ErrorHandler = (function(window, $, undefined){
    var moduleObj = {

        /** form options */
        opt: null,

        /**
         * form - form selector or jQuery form object
         *
         * formOptions - object with form options for every subform.
         * main form has name "main", another - by name in Symfony2 form.
         * For every form you can use next options:
         *      * fieldSelector (selector that will be using for finding inputs)
         *      * type : collection of one
         * @param errors
         * @param form
         * @param formOptions
         */
        handle: function(errors, form, formOptions){
            this.opt = this._prepareOptions(formOptions);
            this.form = $(form);
            if (this.form.length == 0) {
                console.log('ErrorHandler: not found form with selector: ' + form);
                return ;
            }
            this.recursHandleErrors(errors, 'main');
        },
        recursHandleErrors: function(errors, formName, fieldNum){
            for(var field in errors) {
                var obj = errors[field];
                if (this.opt[field] !== undefined) {
                    if (this.opt[field].type == 'collection') {
                        for(var i in obj) {
                            this.recursHandleErrors(obj[i], field, i);
                        }
                    }else{
                        this.recursHandleErrors(obj, field);
                    }
                }else{
                    var sel = this.opt[formName].fieldSelector;
                    var fields = this.form;
                    if (sel != '') {
                        fields = fields.find(sel);
                    }
                    if (fieldNum !== undefined) {
                        fields = fields.eq(fieldNum);
                    }
                    var filter = 'input[name="' + field +'"],textarea[name="' + field +'"]';
                    var foundField = fields.find(filter);
                    if (foundField.length == 1) {
                        var errorText = (obj.join(','));
                        foundField.addClass('has-error');
                        $div = $('<div />');
                        $div.addClass('error-hint');
                        $div.html(errorText);
                        foundField.after($div);
                    }else{
                        console.log('ErrorHandler: not found field with selector: ' + sel + ', and filter: ' + filter);
                    }
                }
            }
        },
        _prepareOptions: function(formOptions){
            if (formOptions === undefined) {
                formOptions = {};
            }
            if (formOptions.main === undefined) {
                formOptions.main = { fieldSelector: '' }
            }
            return formOptions;
        },
        clearErrors: function(){
            $('.has-error').removeClass('has-error');
            $('.error-hint').remove();
        }
    };
    return moduleObj;
})(window, jQuery);