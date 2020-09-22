CollectionSorter = (function($, _, window, undefined) {

    var comparator = function (a, b, name, types, dir) {
        var valueA = a.get(name);
        if (valueA === null) {
            valueA = '';
        }
        var valueB = b.get(name);
        if (valueB === null) {
            valueB = '';
        }
        var res = 0;
        switch (types[name]) {
            case 'str':
                //compare str
                if (valueA > valueB) {
                    res = 1;
                }
                if (valueA < valueB ){
                    res = -1;
                }
                break;
            case 'int':
                //compare int
                valueA = parseInt(valueA);
                valueB = parseInt(valueB);
                res = valueA - valueB;
                break;
            case 'float':
                //compare float
                valueA = parseFloat(valueA);
                valueB = parseFloat(valueB);
                res = valueA - valueB;
                break;
            case 'date':
                //compare date
                var date1 = new Date(valueA);
                var date2 = new Date(valueB);
                res = date1.getMilliseconds() - date2.getMilliseconds();
                break;
        }
        return res * dir;
    };

    var defaultViewOptions = {
        up: 'icon-chevron-up',
        down: 'icon-chevron-down',
        header: 'thead'
    };

    return {

        /**
         *
         * Add to your backbone collection information about model attribute types:
         *       sorting: {
		 *           colName: sortType
	     *       }
         *
         *       colName - model attribute, sortType: 'int', 'float', 'str', 'date'.
         *
         *       If direction is undefined, direction of sorting will automatically switch between asc and desc.
         *
         * @param collection - Backbone collection
         * @param column - Name of model attribute
         * @param direction - 'asc' or 'desc'
         * @returb direction - 'asc' or 'desc'
         */
        sort: function(collection, column, direction){
            if (direction === undefined) {

                if (collection.lastSortedAsc == column){
                    direction = 'desc';
                    collection.lastSortedAsc = null;
                } else {
                    direction = 'asc';
                    collection.lastSortedAsc = column;
                }
            }

            var types = collection.sorting;

            collection.comparator = function(a, b){
                return comparator(a, b, column, types, (direction == 'asc' ? 1 : -1));
            };

            collection.sort();

            return direction;
        },

        sortView: function(collectionView, column) {
            var direction = this.sort(collectionView.collection, column);
            collectionView.render();
            return direction;
        },

        /**
         * For this function you have to:
         *
         *     1) In view template for every header TH set class="sortable" and data-sortable="attribute",
         *     where attribute is model attribute name.

         *
         *     2) In view add to events: 'click .sortable', and this function must call autoSortView method.

         *
         * options - is object with: {
         *      up: 'up-i-class-name',
         *      down: 'down-i-class-name',
         *      header: 'your-header-selector'
         * }

         *
         * If you are using CollectionView instead of CompositeView
         * you have to remove 'I' elements manually before using this method.
         *
         *
         * @param collectionView - Marionette CollectionView or CompositeView.
         * @param event - Jquery event
         */
        autoSortView: function(collectionView, event, options) {
            var $el = $(event.currentTarget);
            var name = $el.attr('data-sortable');
            if (name !== undefined) {
                if (options === undefined) {
                    options = defaultViewOptions;
                } else {
                    options = _.extend(defaultViewOptions, options);
                }
                var direction = this.sortView(collectionView, name);
                var header = collectionView.$(options.header);
                $el = header.find('*[data-sortable="' + name + '"]');
                if (direction == 'asc') {
                    $el.prepend('<i class="' + options.down + '"></i>');
                } else {
                    $el.prepend('<i class="' + options.up + '"></i>');
                }
            }
        }


    }
})(jQuery, _, window);