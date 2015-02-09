/**
 * jQuery PickList Widget
 *
 * Copyright (c) 2012-2013 Jonathon Freeman <jonathon@awnry.com>
 * Distributed under the terms of the MIT License.
 *
 * http://code.google.com/p/jquery-ui-picklist/
 */
(function($)
{
	$.widget("awnry.pickList",
	{
		widgetEventPrefix: "pickList_",

		options:
		{
			// Container classes
			mainClass:                  "pickList",
			listContainerClass:         "pickList_listContainer",
			sourceListContainerClass:   "pickList_sourceListContainer",
			controlsContainerClass:     "pickList_controlsContainer",
			targetListContainerClass:   "pickList_targetListContainer",
			listClass:                  "pickList_list",
			sourceListClass:            "pickList_sourceList",
			targetListClass:            "pickList_targetList",
			clearClass:                 "pickList_clear",

			// List item classes
			listItemClass:              "pickList_listItem",
			richListItemClass:          "pickList_richListItem",
			selectedListItemClass:      "pickList_selectedListItem",

			// Control classes
			addAllClass:                "pickList_addAll",
			addClass:                   "pickList_add",
			removeAllClass:             "pickList_removeAll",
			removeClass:                "pickList_remove",

			// Control labels
			addAllLabel:                "&gt;&gt;",
			addLabel:                   "&gt;",
			removeAllLabel:             "&lt;&lt;",
			removeLabel:                "&lt;",

			// List labels
			listLabelClass:             "pickList_listLabel",
			sourceListLabel:            "Available Groups",
			sourceListLabelClass:       "pickList_sourceListLabel",
			targetListLabel:            "Active Groups",
			targetListLabelClass:       "pickList_targetListLabel",

			// Sorting
			sortItems:                  true,
			sortAttribute:              "label",

			// Name of custom value attribute for list items
			listItemValueAttribute:     "data-value",

			// Additional list items
			items:						[]
		},

		_create: function()
		{
			var self = this;

			self._buildPickList();
			self._refresh();
		},

		_buildPickList: function()
		{
			var self = this;

			self._trigger("beforeBuild");

			self.pickList = $("<div/>")
					.hide()
					.addClass(self.options.mainClass)
					.insertAfter(self.element)
					.append(self._buildSourceList())
					.append(self._buildControls())
					.append(self._buildTargetList())
					.append( $("<div/>").addClass(self.options.clearClass) );

			self._populateLists();

			self.element.hide();
			self.pickList.show();

			self._trigger("afterBuild");
		},

		_buildSourceList: function()
		{
			var self = this;

			var container = $("<div/>")
					.addClass(self.options.listContainerClass)
					.addClass(self.options.sourceListContainerClass)
					.css({
						"-moz-user-select": "none",
						"-webkit-user-select": "none",
						"user-select": "none",
						"-ms-user-select": "none"
					})
					.each(function()
					{
						this.onselectstart = function() { return false; };
					});

			var label = $("<div/>")
					.text(self.options.sourceListLabel)
					.addClass(self.options.listLabelClass)
					.addClass(self.options.sourceListLabelClass);

			self.sourceList = $("<ul/>")
					.addClass(self.options.listClass)
					.addClass(self.options.sourceListClass)
					.delegate("li", "click", { pickList: self }, self._changeHandler);

			container
					.append(label)
					.append(self.sourceList);

			self.sourceList.delegate(".pickList_listItem", "dblclick", {pickList: self}, function(e)
			{
				var self = e.data.pickList;
				self._addItems( self.sourceList.children(".ui-selected") );
			});

			return container;
		},

		_buildTargetList: function()
		{
			var self = this;

			var container = $("<div/>")
					.addClass(self.options.listContainerClass)
					.addClass(self.options.targetListContainerClass)
					.css({
						"-moz-user-select": "none",
						"-webkit-user-select": "none",
						"user-select": "none",
						"-ms-user-select": "none"
					})
					.each(function()
					{
						this.onselectstart = function() { return false; };
					});

			var label = $("<div/>")
					.text(self.options.targetListLabel)
					.addClass(self.options.listLabelClass)
					.addClass(self.options.targetListLabelClass);

			self.targetList = $("<ul/>")
					.addClass(self.options.listClass)
					.addClass(self.options.targetListClass)
					.delegate("li", "click", { pickList: self }, self._changeHandler);

			container
					.append(label)
					.append(self.targetList);

			self.targetList.delegate(".pickList_listItem", "dblclick", {pickList: self}, function(e)
			{
				var self = e.data.pickList;
				self._removeItems( self.targetList.children(".ui-selected") );
			});

			return container;
		},

		_buildControls: function()
		{
			var self = this;

			self.controls = $("<div/>").addClass(self.options.controlsContainerClass);

			self.addButton = $("<button type='button'/>").click({pickList: self}, self._addHandler).html(self.options.addLabel).addClass(self.options.addClass);
			self.removeButton = $("<button type='button'/>").click({pickList: self}, self._removeHandler).html(self.options.removeLabel).addClass(self.options.removeClass);

			self.controls
					.append(self.addButton)
					.append(self.removeButton)

			return self.controls;
		},

		_populateLists: function()
		{
			var self = this;

			self._trigger("beforePopulate");

			var sourceListItems = [];
			var targetListItems = [];
			var selectItems = self.element.children();

			selectItems.not(":selected").each(function()
			{
				sourceListItems.push( self._createDoppelganger(this) );
			});

			selectItems.filter(":selected").each(function()
			{
				targetListItems.push( self._createDoppelganger(this) );
			});

			self.sourceList.append(sourceListItems.join("\n"));
			self.targetList.append(targetListItems.join("\n"));
			self.insertItems(self.options.items);

			self._trigger("afterPopulate");
		},

		_addItems: function(items)
		{
			var self = this;
			
			self._trigger("beforeAdd");

			self.targetList.append( self._removeSelections(items) );

			var itemIds = [];
			items.each(function()
			{
				itemIds.push( self._getItemValue(this) );
			});

			self.element.children().filter(function()
			{
				return $.inArray(this.value, itemIds) != -1;
			}).attr("selected", "selected");

			self._refresh();

			self._trigger("afterAdd", null, { items: items });
			self._trigger("onChange", null, { type: "add", items: items });
		},

		_removeItems: function(items)
		{
			var self = this;
			
			self._trigger("beforeRemove");

			self.sourceList.append( self._removeSelections(items) );

			var itemIds = [];
			items.each(function()
			{
				itemIds.push( self._getItemValue(this) );
			});

			self.element.children().filter(function()
			{
				return $.inArray(this.value, itemIds) != -1;
			}).removeAttr("selected");

			self._refresh();

			self._trigger("afterRemove", null, { items: items });
			self._trigger("onChange", null, { type: "remove", items: items });
		},


		_addHandler: function(e)
		{
			var self = e.data.pickList;
			self._addItems(self.sourceList.children(".ui-selected"));
		},

		_removeHandler: function(e)
		{
			var self = e.data.pickList;
			self._removeItems(self.targetList.children(".ui-selected"));
		},

		_refresh: function()
		{
			var self = this;

			self._trigger("beforeRefresh");

			self._refreshControls();

			// Sort the selection lists.
			if(self.options.sortItems)
			{
				self._sortItems(self.sourceList, self.options);
				self._sortItems(self.targetList, self.options);
			}

			self._trigger("afterRefresh");
		},

		_refreshControls: function()
		{
			var self = this;

			self._trigger("beforeRefreshControls");

			// Enable/disable the Add All button state.
			if(self.sourceList.children().length)
			{
			}
			else
			{
			}

			// Enable/disable the Remove All button state.
			if(self.targetList.children().length)
			{
			}
			else
			{
			}

			// Enable/disable the Add button state.
			if(self.sourceList.children(".ui-selected").length)
			{
				self.addButton.removeAttr("disabled");
			}
			else
			{
				self.addButton.attr("disabled", "disabled");
			}

			// Enable/disable the Remove button state.
			if(self.targetList.children(".ui-selected").length)
			{
				self.removeButton.removeAttr("disabled");
			}
			else
			{
				self.removeButton.attr("disabled", "disabled");
			}

			self._trigger("afterRefreshControls");
		},

		_sortItems: function(list, options)
		{
			var items = new Array();

			list.children().each(function()
			{
				items.push( $(this) );
			});

			items.sort(function(a, b)
			{
				if(a.attr(options.sortAttribute) > b.attr(options.sortAttribute))
				{
					return 1;
				}
				else if(a.attr(options.sortAttribute) == b.attr(options.sortAttribute))
				{
					return 0;
				}
				else
				{
					return -1;
				}
			});

			list.empty();

			for(var i = 0; i < items.length; i++)
			{
				list.append(items[i]);
			}
		},

		_changeHandler: function(e)
		{
			var self = e.data.pickList;

			if(e.ctrlKey)
			{
				if(self._isSelected( $(this) ))
				{
					self._removeSelection( $(this) );
				}
				else
				{
					self.lastSelectedItem = $(this);
					self._addSelection( $(this) );
				}
			}
			else if(e.shiftKey)
			{
				var current = self._getItemValue(this);
				var last = self._getItemValue(self.lastSelectedItem);

				if($(this).index() < $(self.lastSelectedItem).index())
				{
					var temp = current;
					current = last;
					last = temp;
				}

				var pastStart = false;
				var beforeEnd = true;

				self._clearSelections( $(this).parent() );

				$(this).parent().children().each(function()
				{
					if(self._getItemValue(this) == last)
					{
						pastStart = true;
					}

					if(pastStart && beforeEnd)
					{
						self._addSelection( $(this) );
					}

					if(self._getItemValue(this) == current)
					{
						beforeEnd = false;
					}

				});
			}
			else
			{
				self.lastSelectedItem = $(this);
				self._clearSelections( $(this).parent() );
				self._addSelection( $(this) );
			}

			self._refreshControls();
		},

		_isSelected: function(listItem)
		{
			return listItem.hasClass("ui-selected");
		},

		_addSelection: function(listItem)
		{
			var self = this;

			return listItem
					.addClass("ui-selected")
					.addClass("ui-state-highlight")
					.addClass(self.options.selectedListItemClass);
		},

		_removeSelection: function(listItem)
		{
			var self = this;

			return listItem
					.removeClass("ui-selected")
					.removeClass("ui-state-highlight")
					.removeClass(self.options.selectedListItemClass);
		},

		_removeSelections: function(listItems)
		{
			var self = this;

			listItems.each(function()
			{
				$(this)
						.removeClass("ui-selected")
						.removeClass("ui-state-highlight")
						.removeClass(self.options.selectedListItemClass);
			});

			return listItems;
		},

		_clearSelections: function(list)
		{
			var self = this;

			list.children().each(function()
			{
				self._removeSelection( $(this) );
			});
		},

		_setOption: function(key, value)
		{
			switch(key)
			{
				case "clear":
				{
					break;
				}
			}

			$.Widget.prototype._setOption.apply(this, arguments);
		},

		destroy: function()
		{
			var self = this;

			self._trigger("onDestroy");

			self.pickList.remove();
			self.element.show();

			$.Widget.prototype.destroy.call(self);
		},

		insert: function(item)
		{
			var self = this;

			var list = item.selected ? self.targetList : self.sourceList;
			var selectItem = self._createSelectItem(item);
			var listItem = self._createListItem(item);

			self.element.append(selectItem);
			list.append(listItem);

			self._trigger("onChange");

			self._refresh();
		},

		insertItems: function(items)
		{
			var self = this;

			var selectItems = [];
			var sourceItems = [];
			var targetItems = [];

			$(items).each(function()
			{
				var selectItem = self._createSelectItem(this);
				var listItem = self._createListItem(this);

				selectItems.push(selectItem);

				if(this.selected)
				{
					targetItems.push(listItem);
				}
				else
				{
					sourceItems.push(listItem);
				}
			});

			self.element.append(selectItems.join("\n"));
			self.sourceList.append(sourceItems.join("\n"));
			self.targetList.append(targetItems.join("\n"));

			self._trigger("onChange");

			self._refresh();
		},

		_createSelectItem: function(item)
		{
			var selected = item.selected ? " selected='selected'" : "";
			return "<option value='" + item.value + "'" + selected + ">" + item.label + "</option>";
		},

		_createListItem: function(item)
		{
			var self = this;

			if(item.element != undefined)
			{
				var richItemHtml = item.element.clone().wrap("<div>").parent().html();
				item.element.hide();
				return "<li " + self.options.listItemValueAttribute + "='" + item.value + "' label='" + item.label + "' class='" + self.options.listItemClass + " " + self.options.richListItemClass + "'>" + richItemHtml + "</li>";
			}

			return "<li " + self.options.listItemValueAttribute + "='" + item.value + "' label='" + item.label + "' class='" + self.options.listItemClass + "'>" + item.label + "</li>";
		},

		_createDoppelganger: function(item)
		{
			var self = this;
			return "<li " + self.options.listItemValueAttribute + "='" + $(item).val() + "' label='" + $(item).text() + "' class='" + self.options.listItemClass + "'>" + $(item).text() + "</li>";
		},

		_getItemValue: function(item)
		{
			var self = this;
			return $(item).attr(self.options.listItemValueAttribute);
		}
	});
}(jQuery));
