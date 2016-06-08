How to use CollectionSorter
===========================

CollectionSorter.js - sorting Backbone Collection or Marrionette CompositeView.


Sorting only collection
-----------------------

For sorting collection you have to:
1. Add type description for your collection:


    myCollection = new Backbone.Collection.extend({
        name: 'str',
        count: 'int',
        cash: 'float',
        createdAt: 'date'
    });


2. When you need call sort method by selected model attribute:


    CollectionSorter.sort(myCollection, 'cash', 'asc');


You can leave direction undefined.
In that case sorter will use asc direction at first time and after that switch between asc and desc.

Sorting CollectionView (or CompositeView)
-----------------------------------------

For sorting view by some column you can use `sortView` action:


    CollectionSorter.sortView(view, 'cash');


Automatically sorting CompositeView
-----------------------------------

For minimum of code for CompositeView sorting you can use `autoSortView` with template:


        <table class="table r5 table-bordered margin-top-25 table-hover table-stripped">
            <thead>
                <tr>
                    <th class="sortable" data-sortable="name">Client</th>
                    <th class="sortable" data-sortable="count">Count Accounts</th>
                </tr>
            </thead>
        ...


Pay your attention for class `sortable` and attribute `data-sortable` that contains model attribute name.
Class `sortable` is used for pointer cursor and catching click event.

    autoSortView(collectionView, event, options);

Default options (if not specified):

    options = {
        up: 'icon-chevron-up',
        down: 'icon-chevron-down',
        header: 'thead'
    };

Where, `up` - class of `<i>` element with UP icon, `down` - class for DOWN icon,
`header` - selector for header element of table.

You can override some default options:

    autoSortView(collectionView, event, {header: '.myHeader'});

To use it in your CompositeView:


    myCompositeView = new Marionette.CompositeView.extend({
        ...
        events: {
            'click .sortable': 'onClickSortable'
        },
        onClickSortable: function(e){
            CollectionSorter.autoSortView(this, e);
        }
    });


It's all of your needs for sorting your composite view.


Example
-------


    Mod.AccountCollection = Backbone.Collection.extend({
        model: Mod.AccountModel,

        sorting: {
            name: 'str',
            status: 'int',
            averageAccountValue: 'float'
        }
    });

    Mod.SummaryAccounts = Marionette.CompositeView.extend({
        template: '#tplSummaryAccountsView',

        events: {
            'click .sortable': 'clickSortableHeader'
        },

        clickSortableHeader: function(e){
            e.preventDefault();
            CollectionSorter.autoSortView(this, e);
            return false;
        }
    });

    <script id="tplSummaryAccountsView" type="text/template">
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-sortable="name">Account Name</th>
                    <th class="sortable" data-sortable="status">Account Status</th>
                    <th class="sortable" data-sortable="averageAccountValue">Average Account Value</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </script>


The End
-------
