var nbAddNew = angular.module( 'nbAddNew', ['ui.select', 'ui.bootstrap', 'ngSanitize', 'angular-toArrayFilter', 'angularSlideables', 'ngAnimate' ] );

    nbAddNew.factory( 'Trigger', ['$rootScope', '$http', function( $rootScope, $http, $scope ) {

        var triggers = {};

        triggers.added = [];

        triggers.getTriggers = function() {
            return triggers.added;
        };

        triggers.getTriggerById = function( triggerId ) {
            return triggers.added[triggerId];
        };

        triggers.updateTrigger = function( triggerId, trigger ) {
            triggers.added[triggerId] = trigger;
        };

        triggers.updateTriggerProperty = function( triggerId, property, propertyValue) {
            triggers.added[triggerId][property] = angular.copy( propertyValue );
        };

        triggers.add = function( trigger ) {
            triggers.added.push(trigger);
        };

        triggers.delete = function( triggerId ) {
            triggers.added.splice(triggerId, 1);
        };

        triggers.availableTriggerSelections = function( categoryId ) {
        };





        triggers.addCategory = function( id, category ) {
            triggers.added[id].category = category;
        };

        triggers.triggerSelected = function( id, selectedTrigger ) {

            triggers.added[id].selected.id = selectedTrigger.id;
            triggers.added[id].selected.label = selectedTrigger.label;
            triggers.added[id].mergeTags = selectedTrigger.merge_tags;
            triggers.added[id].message = selectedTrigger.placeholder;
            triggers.added[id].conditionalLogic = [];
            triggers.added[id].conditionalLogic.available = selectedTrigger.local_settings;
            triggers.added[id].conditionalLogic.selected = [{}];

            $rootScope.$broadcast('triggers.changed');
        };

        triggers.optionSelected = function( triggerId, optionKey, optionValue ) {
            triggers.added[triggerId][optionKey] = optionValue;
        };

        triggers.triggersFromCat = function( id ) {

            var category = triggers.added[id].category;

            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: category
                }
            }).then(function (response) {
                triggers.available[id] = response.data;
            });

        };

        if ( window.editingExisting ) {
            angular.forEach( window.editingExisting, function(value, key) {
                var triggerData = angular.copy( {
                    category: {
                        id: value.cat_info.id,
                        label: value.cat_info.label
                    },
                    conditionalLogic: {
                        available: value.trigger_info.local_settings,
                        selected: [value.conditional_logic]
                    },
                    conditionalLogicEnabled: value.conditional_logic_enabled,
                    mergeTags: value.trigger_info.merge_tags,
                    message: value.message,
                    selected: {
                        id: value.trigger_event,
                        label: value.trigger_info.label
                    }
                });

                triggers.add( triggerData );
            });
        }

        return triggers;

    }]);







    function triggerItemsController( Trigger ) {
        var ctrl = this;

        ctrl.groupTriggers = Trigger.getTriggers();

        ctrl.deleteTrigger = function( triggerToDelete ) {
            var idx = ctrl.groupTriggers.indexOf( triggerToDelete );
            if ( idx >= 0 ) {
                ctrl.groupTriggers.splice( idx, 1 );
            }
        };

        ctrl.updateTriggerDetails = function( triggerObj, detailKey, detailValue ) {
            var idx = ctrl.groupTriggers.indexOf( triggerObj );
            if ( idx >= 0 ) {
                ctrl.groupTriggers[idx][detailKey] = detailValue;
            }
        };

        ctrl.addConditionGroup = function( triggerObj ) {
            var idx = ctrl.groupTriggers.indexOf( triggerObj );
            if ( idx >= 0 ) {
                ctrl.groupTriggers[idx].conditionalLogic.selected.push({});
                console.log(ctrl.groupTriggers);
            }
        }
    }

    nbAddNew.component('triggerItems', {
        controller: triggerItemsController,
        template: '<trigger-item class="trigger-item-container" ng-repeat="trigger in $ctrl.groupTriggers" trigger-details="trigger" on-delete="$ctrl.deleteTrigger(trigger)">Trigger</trigger-item>'
    });

    function triggerItemController( $http ) {
        var ctrl = this;

        ctrl.triggerHeader = ctrl.triggerDetails.selected.label;

        ctrl.editMode = false;

        ctrl.toggleEditMode = function( active ) {
            ctrl.editMode = active;
        };

        ctrl.deleteTrigger = function() {
            ctrl.onDelete( ctrl.trigger );
        };

        ctrl.setCategory = function( category ) {

            ctrl.triggerGroup.updateTriggerDetails( ctrl.triggerDetails, 'category', category );

            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: category.id
                }
            }).then(function ( response ) {
                ctrl.triggerGroup.updateTriggerDetails( ctrl.triggerDetails, 'triggersAvailable', response.data )
            });


        };

        ctrl.setMergeTags = function( mergeTags ) {
            ctrl.triggerGroup.updateTriggerDetails( ctrl.triggerDetails, 'mergeTags', mergeTags );
        };

        ctrl.toggleConditionalLogic = function( enabled ) {
            ctrl.triggerGroup.updateTriggerDetails( ctrl.triggerDetails, 'conditionalLogicEnabled', enabled )
        };

        ctrl.addConditionGroup = function() {
            console.log(ctrl.triggerGroup);
            ctrl.triggerGroup.addConditionGroup( ctrl.triggerDetails );
        };


    }

    nbAddNew.component('triggerItem', {
        controller: triggerItemController,
        template:
            '<trigger-header class="trigger-header" header-title="{{ $ctrl.triggerHeader }}"></trigger-header>' +
            '<trigger-options ng-show="$ctrl.editMode">Options Placeholder</trigger-options>' +
            '<div>Trigger</div>',
        bindings: {
            triggerDetails: '<',
            onDelete: '&'
        },
        require: {
            triggerGroup: '^triggerItems'
        }

    });

    function triggerHeaderController() {
        var ctrl = this;

    }

    nbAddNew.component('triggerHeader', {
        controller: triggerHeaderController,
        bindings: {
            headerTitle: '@',
            triggerItem: '<'
        },
        template:
            '<trigger-header-title></trigger-header-title>' +
            '<trigger-edit></trigger-edit>' +
            '<trigger-delete trigger-item="$ctrl.triggerItem"></trigger-delete>'
    });

    function triggerHeaderTitleController() {
        var ctrl = this;

        ctrl.$onInit = function() {
            ctrl.headerTitle = ctrl.currentTriggerItem.triggerHeader;
        };

    }

    nbAddNew.component('triggerHeaderTitle', {
        controller: triggerHeaderTitleController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template: '<span class="header-text">{{ $ctrl.headerTitle }}</span>'
    });

    function triggerEditController() {
        var ctrl = this;

        ctrl.activateEditMode = function() {
            ctrl.currentTriggerItem.toggleEditMode( true );
        }

    }

    nbAddNew.component('triggerEdit', {
        controller: triggerEditController,
        template: '<span class="header-edit-text" ng-click="$ctrl.activateEditMode()">Edit</span>',
        require: {
            currentTriggerItem: '^?triggerItem'
        }
    });

    function triggerDeleteController() {
        var ctrl = this;

        ctrl.deleteCurrentTrigger = function() {
            ctrl.currentTriggerItem.deleteTrigger();
        }
    }

    nbAddNew.component('triggerDelete', {
        controller: triggerDeleteController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template: '<span class="header-delete-text" ng-click="$ctrl.deleteCurrentTrigger()">Delete</span>'
    });

    nbAddNew.component('triggerOptions', {
        template:
            '<trigger-selections></trigger-selections>' +
            '<trigger-message></trigger-message>' +
            '<trigger-conditional-logic></trigger-conditional-logic>' +
            '<trigger-save></trigger-save>'

    });

    function triggerSelectionsController( $http ) {
        var ctrl = this;

        //if ( ctrl.currentTriggerItem.triggerDetails.category ) {
        //    ctrl.currentCategory = ctrl.currentTriggerItem.triggerDetails.category;
        //} else {
        //    ctrl.currentCategory = [];
        //}
        //
        //if ( ctrl.currentTriggerItem.triggerDetails.selected ) {
        //    ctrl.currentTrigger = ctrl.currentTriggerItem.triggerDetails.selected;
        //} else {
        //    ctrl.currentTrigger = [];
        //}

        ctrl.triggersAvailable = {data: []};

        ctrl.categoryChanged = function( categoryData ) {
            ctrl.currentCategory = categoryData;
            ctrl.getTriggersFromCat( categoryData );
            console.log(categoryData);
        };

        ctrl.getTriggersFromCat = function( categoryData ) {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: categoryData.id
                }
            }).then(function ( response ) {
                // Set the response data to a variable we can select triggers from
                ctrl.triggersAvailable = {data: response.data };
                console.log(response.data);
                console.log(ctrl.selectedCategory);
            });
        };

        ctrl.getAvailableTriggers = function() {
            console.log(ctrl.triggersAvailable);
            return ctrl.triggersAvailable.data;
        };



        //ctrl.currentTrigger = ctrl.currentTriggerItem.triggerDetails.selected;
    }

    nbAddNew.component('triggerSelections', {
        controller: triggerSelectionsController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template: '<trigger-selections-category></trigger-selections-category><trigger-selections-trigger available-triggers="$ctrl.triggersAvailable"></trigger-selections-trigger>'
    });

    function triggerSelectionsCategoryController( $http ) {
        var ctrl = this;

        ctrl.categorySelected = function() {
            console.log(ctrl.currentTriggerItem);
            ctrl.currentTriggerItem.setCategory(ctrl.currentCategory);
        };



        //ctrl.$onInit = function() {
        //
        //    if ( ctrl.currentTriggerItem.triggerDetails.category ) {
        //        ctrl.currentCategory = ctrl.currentTriggerItem.triggerDetails.category;
        //    } else {
        //        ctrl.currentCategory = {};
        //    }
        //
        //};

        //ctrl.categorySelected = function() {
        //    ctrl.currentTriggerItem.setCategory( ctrl.currentCategory );
        //    ctrl.categoryChange( ctrl.currentCategory );
        //    console.log(ctrl.currentCategory);
        //};

        ctrl.categoriesAvailable = [];
        //
        //// AJAX request to get the initial categories
        $http({
            method: 'GET',
            url: nbAjax.ajaxurl,
            params: {
                action: 'get_events'
            }
        }).then(function ( response ) {
            // Set the response data to a variable that we can select categories from
            ctrl.categoriesAvailable = response.data;
            //console.log(ctrl.categoriesAvailable);
        });



        //ctrl.categoriesAvailable = ctrl.currentTriggerItem.categoriesAvailable

    }

    nbAddNew.component('triggerSelectionsCategory', {
        controller: triggerSelectionsCategoryController,
        require: {
            currentTriggerItem: '^?triggerItem',
            currentTriggerSelections: '^?triggerSelections'
        },
        bindings: {
            currentCategory: '<',
            changeCategory: '&'
        },
        template:
            '<ui-select ng-model="$ctrl.currentCategory" on-select="$ctrl.categorySelected()" theme="bootstrap">' +
                '<ui-select-match placeholder="Select a category">{{ $select.selected.label }}</ui-select-match>' +
                '<ui-select-choices repeat="event in $ctrl.categoriesAvailable | filter: $select.search">' +
                    '<span ng-bind-html="event.label"></span>' +
                '</ui-select-choices>' +
            '</ui-select>'
    });

    function triggerSelectionsTriggerController( $http, $scope ) {
        var ctrl = this;

        //ctrl.triggersInCategory = [];

        //ctrl.$onInit = function() {
        //    if ( ctrl.currentTriggerItem.category ) {
        //        $http({
        //            method: 'GET',
        //            url: nbAjax.ajaxurl,
        //            params: {
        //                action: 'get_triggers',
        //                selected_event: ctrl.currentTriggerItem.category.id
        //            }
        //        }).then(function ( response ) {
        //            // Set the response data to a variable we can select triggers from
        //            ctrl.triggersInCategory = response.data;
        //        });
        //    }
        //};

        //ctrl.currentCategory = ctrl.currentTriggerItem.category;

        //ctrl.triggersAvailable = ctrl.availableTriggers();

        ctrl.triggerSelected = function() {
            ctrl.currentTriggerItem.setMergeTags( ctrl.currentTrigger.merge_tags );
        };


        //ctrl.$onInit = function() {
        //    ctrl.triggersAvailable = { data: ctrl.currentTriggerSelections.triggersAvailable };
        //    console.log(ctrl.triggersAvailable);
        //    console.log('booyah');
        //};

        //ctrl.triggersAvailable = ctrl.getTriggersAvailable();

    }

    nbAddNew.component('triggerSelectionsTrigger', {
        controller: triggerSelectionsTriggerController,
        require: {
            currentTriggerItem: '^?triggerItem',
            currentTriggerSelections: '^triggerSelections'
        },
        bindings: {
            selectedCategory: '<',
            availableTriggers: '&'
        },
        template:
            '<ui-select ng-model="$ctrl.currentTrigger" on-select="$ctrl.triggerSelected()" theme="bootstrap">' +
                '<ui-select-match placeholder="Select a category">{{ $select.selected.label }}</ui-select-match>' +
                '<ui-select-choices repeat="event in $ctrl.currentTriggerItem.triggerDetails.triggersAvailable | filter: $select.search">' +
                    '<span ng-bind-html="event.label"></span>' +
                '</ui-select-choices>' +
            '</ui-select>'

    });

    function triggerMessageController() {
        //Set defaults

        var ctrl = this;

        ctrl.mergeTags = {};

        //ctrl.$onInit = function() {
        //    ctrl.mergeTags = { data: ctrl.currentTriggerItem.triggerDetails.mergeTags };
        ////};



    }

    nbAddNew.component('triggerMessage', {
        controller: triggerMessageController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template:
            '<trigger-message-content class="btn-group message-tag-buttons"></trigger-message-content>' +
            '<trigger-message-tags class="btn-group message-tag-buttons" merge-tag-item="tag" ng-repeat="tag in $ctrl.currentTriggerItem.triggerDetails.mergeTags"></trigger-message-tags>'
    });

    function triggerMessageTagsController() {

        var ctrl = this;

        ctrl.lastFocused = null;

        angular.element("textarea").focus( function() {
            ctrl.lastFocused = document.activeElement;
        });

        // Handles the insertion of text at the caret when a button is clicked
        ctrl.insertText = function(text) {
            var input = ctrl.lastFocused;
            if (input == undefined) { return; }
            var scrollPos = input.scrollTop;
            var pos = 0;
            var browser = ((input.selectionStart || input.selectionStart == "0") ?
                "ff" : (document.selection ? "ie" : false ) );
            if (browser == "ie") {
                input.focus();
                var range = document.selection.createRange();
                range.moveStart ("character", -input.value.length);
                pos = range.text.length;
            }

            else if (browser == "ff") { pos = input.selectionStart }
            var front = (input.value).substring(0, pos);
            var back = (input.value).substring(pos, input.value.length);
            input.value = front+text+back;
            pos = pos + text.length;
            if (browser == "ie") {
                input.focus();
                var range = document.selection.createRange();
                range.moveStart ("character", -input.value.length);
                range.moveStart ("character", pos);
                range.moveEnd ("character", 0);
                range.select();
            }
            else if (browser == "ff") {
                input.selectionStart = pos;
                input.selectionEnd = pos;
                input.focus();
            }
            input.scrollTop = scrollPos;
            angular.element(input).trigger('input');
        };
    }

    nbAddNew.component('triggerMessageTags', {
        controller: triggerMessageTagsController,
        template: '<a class="btn btn-default" ng-click="$ctrl.insertText(\'{\' + $ctrl.mergeTagItem + \'}\')">{{ $ctrl.mergeTagItem }}</a>',
        bindings: {
            mergeTagItem: '<'
        }

    });

    nbAddNew.component('triggerMessageContent', {
        template:
        '<form>' +
            '<div class="form-group">' +
                '<textarea ng-model="$ctrl.currentTrigger.message" class="form-control" rows="5">' +
            '</div>' +
        '</form>'
    });



    nbAddNew.component('triggerConditionalLogic', {
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template:
        '<trigger-conditional-logic-enabled></trigger-conditional-logic-enabled>' +
        '<trigger-conditional-logic-group ' +
            'condition-group-details="conditionGroup" ' +
            'ng-repeat="conditionGroup in $ctrl.currentTriggerItem.triggerDetails.conditionalLogic.selected"' +
        '>ConditionalTest</trigger-conditional-logic-group>' +
        '<add-conditional-logic-group></add-conditional-logic-group>'
    });

    function triggerConditionalLogicEnabledController() {
        var ctrl = this;

        ctrl.$onInit = function() {
            ctrl.conditionalLogicEnabled = ctrl.currentTriggerItem.triggerDetails.conditionalLogicEnabled;
        };

        ctrl.conditionalLogicToggle = function() {
            ctrl.currentTriggerItem.toggleConditionalLogic( ctrl.conditionalLogicEnabled );
            console.log(ctrl.currentTriggerItem);
        };

    }

    nbAddNew.component('triggerConditionalLogicEnabled', {
        controller: triggerConditionalLogicEnabledController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template:
        '<div class="column-left">Enable Conditional Logic</div>' +
        '<div class="column-right">' +
            '<input type="checkbox" ng-model="$ctrl.conditionalLogicEnabled" ng-change="$ctrl.conditionalLogicToggle()">' +
        '</div>'
    });

    nbAddNew.component('triggerConditionalLogicGroup', {
        template: '<conditional-logic-rule ng-repeat="conditionRule in $ctrl.conditionGroupDetails"></conditional-logic-rule>',
        bindings: {
            conditionGroupDetails: '<'
        }
    });

    nbAddNew.component('conditionalLogicRule', {
        template:
        '<conditional-logic-if></conditional-logic-if>' +
        '<conditional-logic-is></conditional-logic-is>' +
        '<conditional-logic-condition></conditional-logic-condition>'
    });

    function conditionalLogicIfController() {
        var ctrl = this;
        
    }

    nbAddNew.component('conditionalLogicIf', {
        controller: conditionalLogicIfController,
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template:
        '<ui-select ng-model="ctrl.selectedCondition" theme="bootstrap">' +
            '<ui-select-match placeholder="If"></ui-select-match>' +
            '<ui-select-choices repeat="condition in $ctrl.currentTriggerItem.conditionalLogic.available | toArray:false | filter: $select.search">' +
                '<span ng-bind-html="condition.label | highlight: $select.search"></span>' +
            '</ui-select-choices>' +
        '</ui-select>'
    });

    nbAddNew.component('conditionalLogicIs', {
        template: ''
    });

    nbAddNew.component('conditionalLogicCondition', {
        template: ''
    });

    nbAddNew.component('addConditionalLogicGroup', {
        require: {
            currentTriggerItem: '^?triggerItem'
        },
        template: '<button ng-click="$ctrl.currentTriggerItem.addConditionGroup()">Add New Condition</button>'
    });

    nbAddNew.component('triggerSave', {

    });




    /** Trigger Selection */

    /**
     * Handles trigger selection dropdowns
     *
     * @param $http
     * @param Trigger
     * @constructor
     */
    function triggerSelectController( $http, Trigger ) {

        // Set defaults
        var ctrl = this;
        ctrl.categoriesAvailable  = [];
        ctrl.triggersInCategory   = [];
        ctrl.currentCategory = [];

        ctrl.currentTrigger = angular.copy( Trigger.getTriggerById( ctrl.triggerId ) );

        ctrl.currentCategorySelected = ctrl.currentTrigger.selected;
        ctrl.currentTriggerSelected = null;



        // AJAX request to get the initial categories
        $http({
            method: 'GET',
            url: nbAjax.ajaxurl,
            params: {
                action: 'get_events'
            }
        }).then(function ( response ) {
            // Set the response data to a variable that we can select categories from
            ctrl.categoriesAvailable = angular.copy( response.data );
            //console.log(ctrl.categoriesAvailable);
        });

        ctrl.triggerCategorySelected = function() {
            Trigger.added[ctrl.triggerId].category = angular.copy( ctrl.currentCategory );
            ctrl.getTriggersInCategory( ctrl.currentCategory.id );
            ctrl.currentTrigger = null;
            console.log(ctrl.triggerId);
        };

        ctrl.getTriggersInCategory = function( categoryId ) {

            // AJAX request to get triggers from a category
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: categoryId
                }
            }).then(function ( response ) {
                // Set the response data to a variable we can select triggers from
                ctrl.triggersInCategory = angular.copy( response.data );
            });
        };

        // Fired when a trigger is selected.  Adds it to the Trigger service
        ctrl.triggerSelected = function() {
            Trigger.triggerSelected( ctrl.triggerId, ctrl.currentTrigger );
        };

        if ( ctrl.currentTrigger.category ) {
            ctrl.currentCategory = Trigger.added[ctrl.triggerId].category;
            ctrl.getTriggersInCategory( ctrl.currentCategory.id );
        }

        if ( ctrl.currentTrigger.selected ) {
            ctrl.currentTrigger = Trigger.added[ctrl.triggerId].selected;
        }

    }

    // Component that outputs the output of the trigger selection dropdown
    nbAddNew.component('triggerCategorySelect', {
        controller: triggerSelectController,
        bindings: {
            triggerId: '='
        },
        transclude: true,
        require: 'ui.select',
        template: [
        '<div id="events-{{$index}}" class="events">',
            '<ui-select ng-model="$ctrl.currentCategory" on-select="$ctrl.triggerCategorySelected()" theme="bootstrap">',
                '<ui-select-match placeholder="Select a category">{{ $select.selected.label }}</ui-select-match>',
                '<ui-select-choices repeat="event in $ctrl.categoriesAvailable | filter: $select.search">',
                    '<span ng-bind-html="event.label"></span>',
                '</ui-select-choices>',
            '</ui-select>',
        '</div>',
        '<div id="triggers-{{$index}}" class="triggers">',
            '<ui-select ng-model="$ctrl.currentTrigger" on-select="$ctrl.triggerSelected()" theme="bootstrap">',
                '<ui-select-match placeholder="Select a trigger">{{ $select.selected.label }}</ui-select-match>',
                '<ui-select-choices repeat="trigger in $ctrl.triggersInCategory | filter: $select.search">',
                    '<span ng-bind-html="trigger.label"></span>',
                '</ui-select-choices>',
            '</ui-select>',
        '</div>'].join('')
    });

    /** Trigger Message */

    /**
     * Handles the configuration of the message for the selected trigger
     *
     * @param Trigger
     * @param $rootScope
     */
    //function triggerMessageController( Trigger ) {
    //
    //    // Set defaults
    //    var ctrl = this;
    //    ctrl.triggerMessage = '';
    //    ctrl.currentTrigger = {};
    //
    //    ctrl.currentTrigger = Trigger.getTriggerById( ctrl.triggerId );
    //
    //    ctrl.lastFocused = null;
    //
    //    angular.element("textarea").focus( function() {
    //        ctrl.lastFocused = document.activeElement;
    //    });
    //
    //    // Handles the insertion of text at the caret when a button is clicked
    //    ctrl.insertText = function(text) {
    //        var input = ctrl.lastFocused;
    //        if (input == undefined) { return; }
    //        var scrollPos = input.scrollTop;
    //        var pos = 0;
    //        var browser = ((input.selectionStart || input.selectionStart == "0") ?
    //            "ff" : (document.selection ? "ie" : false ) );
    //        if (browser == "ie") {
    //            input.focus();
    //            var range = document.selection.createRange();
    //            range.moveStart ("character", -input.value.length);
    //            pos = range.text.length;
    //        }
    //        else if (browser == "ff") { pos = input.selectionStart }
    //
    //        var front = (input.value).substring(0, pos);
    //        var back = (input.value).substring(pos, input.value.length);
    //        input.value = front+text+back;
    //        pos = pos + text.length;
    //        if (browser == "ie") {
    //            input.focus();
    //            var range = document.selection.createRange();
    //            range.moveStart ("character", -input.value.length);
    //            range.moveStart ("character", pos);
    //            range.moveEnd ("character", 0);
    //            range.select();
    //        }
    //        else if (browser == "ff") {
    //            input.selectionStart = pos;
    //            input.selectionEnd = pos;
    //            input.focus();
    //        }
    //        input.scrollTop = scrollPos;
    //        angular.element(input).trigger('input');
    //    };
    //
    //}

    //nbAddNew.component( 'triggerMessage', {
    //
    //    controller: triggerMessageController,
    //    bindings: {
    //        triggerId: '='
    //    },
    //    template:
    //        ['<div class="btn-group message-tag-buttons" ng-if="$ctrl.currentTrigger.mergeTags">',
    //            '<a ng-repeat="tag in $ctrl.currentTrigger.mergeTags" class="btn btn-default" ng-click="$ctrl.insertText(\'{\' + tag + \'}\')">{{ tag }}</a>',
    //        '</div>',
    //            '<form>',
    //            '<div class="form-group">',
    //                '<textarea ng-model="$ctrl.currentTrigger.message" class="form-control" rows="5">',
    //            '</div>',
    //        '</form>'].join('')
    //
    //});

    // Conditional Logic

    //function conditionalLogicController( Trigger ) {
    //
    //    // Set defaults
    //    var ctrl = this;
    //
    //    ctrl.currentTrigger = Trigger.getTriggerById( ctrl.triggerId );
    //    console.log(ctrl.currentTrigger);
    //
    //    ctrl.conditionalLogicConditions = [];
    //    ctrl.conditionalLogicSelected = null;
    //    ctrl.conditionalLogicEnabled = false;
    //    ctrl.conditionalLogicIs = [];
    //    ctrl.conditionalLogicConditionsAvailable = [];
    //
    //    ctrl.conditionalLogicGroupStart = 0;
    //    ctrl.conditionalLogicStart = 0;
    //
    //    ctrl.nextConditionId = 0;
    //    ctrl.nextGroupId = 0;
    //
    //    ctrl.conditionalLogicConditionSelected = function(conditionGroup, conditionItem, $select) {
    //
    //    };
    //
    //    ctrl.conditionalLogicConditionGroupAdded = function() {
    //
    //        if ( ctrl.currentTrigger.conditionalLogic.selected) {
    //            ctrl.currentTrigger.conditionalLogic.selected.push({ children: [{ child: null }] });
    //        } else {
    //            ctrl.currentTrigger.conditionalLogic.selected = [];
    //            ctrl.currentTrigger.conditionalLogic.selected.push({ children: [{ child: null }] });
    //        }
    //
    //    };
    //
    //    ctrl.conditionalLogicConditionAdded = function(conditionGroup) {
    //        ctrl.currentTrigger.conditionalLogic.selected[conditionGroup].children.push({ child: null });
    //    };
    //
    //}

    //nbAddNew.component( 'conditionalLogic', {
    //
    //    //controller: conditionalLogicController,
    //    bindings: {
    //        triggerId: '='
    //    },
    //    require: 'uiSelect',
    //    template: ['<div class="trigger-conditional-logic">',
    //        '<h2>Conditional Logic</h2>',
    //        '<div class="column-left">Enable Conditional Logic</div><div class="column-right"><input type="checkbox" ng-model="$ctrl.currentTrigger.conditionalLogicEnabled"></div>',
    //        '<div class="conditional-logic-options" ng-show="$ctrl.currentTrigger.conditionalLogicEnabled">',
    //            '<div class="conditional-logic-row" ng-repeat="conditionGroup in $ctrl.currentTrigger.conditionalLogic.selected track by $index">',
    //                '<div class="condition-container" ng-repeat="condition in conditionGroup.children">',
    //                    '<div class="conditional-condition">',
    //                        '<ui-select ng-model="condition.selectedCondition" theme="bootstrap">',
    //                            '<ui-select-match placeholder="If">{{ condition.selectedCondition.label }}</ui-select-match>',
    //                            '<ui-select-choices repeat="condition in $ctrl.currentTrigger.conditionalLogic.available | toArray:false | filter: $select.search">',
    //                                '<span ng-bind-html="condition.label | highlight: $select.search"></span>',
    //                            '</ui-select-choices>',
    //                        '</ui-select>',
    //                    '</div>',
    //                    '<div class="conditional-if">',
    //                        '<select class="form-control">',
    //                            '<option>is equal to</option>',
    //                            '<option>is not equal to</option>',
    //                        '</select>',
    //                    '</div>',
    //                    '<div class="conditional-is">',
    //                        '<ui-select ng-model="condition.selectedIs" theme="bootstrap">',
    //                            '<ui-select-match placeholder="Is">{{ condition.selectedIs }}</ui-select-match>',
    //                            '<ui-select-choices repeat="is in condition.selectedCondition.selections | toArray:false | filter: $select.search">',
    //                                '<span ng-bind-html="is | highlight: $select.search"></span>',
    //                            '</ui-select-choices>',
    //                        '</ui-select>',
    //                    '</div>',
    //                    '<div class="add-remove-condition">',
    //                        '<button type="button" ng-click="$ctrl.conditionalLogicConditionAdded($parent.$index)" class="button">AND</button>',
    //                    '</div>',
    //                '</div>',
    //            '</div>',
    //            '<div class="add-new-condition">',
    //                '<button type="button" ng-click="$ctrl.conditionalLogicConditionGroupAdded()" class="button">Add Condition</button>',
    //            '</div>',
    //        '</div>',
    //    '</div>'].join('')
    //
    //});

    // Save Notification

    function saveNotificationController( Trigger, $http ) {

        var ctrl = this;

        ctrl.saveButtonClicked = function() {
            ctrl.currentTrigger = Trigger.getTriggerById( ctrl.triggerId );

            $http({
                method: 'POST',
                url: nbAjax.ajaxurl,
                params: {
                    action:                   'save_trigger',
                    group_id:                  window.nbGroupId,
                    trigger_id:                ctrl.triggerId,
                    trigger:                   ctrl.currentTrigger.selected.id,
                    message:                   ctrl.currentTrigger.message,
                    conditional_logic_enabled: ctrl.currentTrigger.conditionalLogicEnabled,
                    conditional_logic:         ctrl.currentTrigger.conditionalLogic.selected
                }
            }).then(function (response) {
                console.log(response.data);
                ctrl.currentTrigger.editMode = false;
            });
        };

    }

    nbAddNew.component( 'saveNotification', {
        controller: saveNotificationController,
        bindings: {
            triggerId: '='
        },
        template: '<div class="button button-primary save-trigger" ng-click="$ctrl.saveButtonClicked()">Save</div>'
    });


    // End New Stuff












    nbAddNew.component('triggerSelect', {
        controller: triggerSelectController,
        bindings: {
            triggerSelects: '&'
        },
        transclude: true,
        require: 'ui.select',
        template: [
            '<div id="events-{{$index}}" class="events">',
            '<ui-select ng-model="$ctrl.triggerSelected" theme="bootstrap">',
                '<ui-select-match placeholder="Select a trigger">{{ $select.selected.label }}</ui-select-match>',
                '<ui-select-choices repeat="trigger in $ctrl.triggerSelects | filter: $select.search">',
                    '<span ng-bind-html="trigger.label"></span>',
                '</ui-select-choices>',
            '</ui-select>'].join('')
    });

    nbAddNew.factory( 'TriggersFromCat', ['$rootScope', '$http', function( $rootScope, $http ) {

        var triggersFromCat = {};

        triggersFromCat.available = [];

        triggersFromCat.get = function( category ) {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: category
                }
            }).then(function (response) {
                triggersFromCat = response.data;
            });
        };

        return triggersFromCat;

    }]);

    nbAddNew.directive( 'addTriggerButton', [ 'Trigger', '$rootScope', function( Trigger, $rootScope ) {
        return {
            restrict: "A",
            link: function( scope, element, attrs ) {
                element.bind( 'click', function() {
                    Trigger.add( {selected: [], message: false, mergeTags: false, conditionalLogic: ''} );
                    console.log('meh');
                    $rootScope.$broadcast('trigger.added');
                });
            }
        }
    }]);

    nbAddNew.directive( 'triggerCategory', [ 'Trigger', '$rootScope', function( Trigger, $rootScope ) {
        return {
            require: 'uiSelect',
            link: function( scope, element, attrs, $select ) {
                scope.$watch( '$select.selected.id', function(newValue, oldValue) {
                    if ( newValue ) {
                        Trigger.addCategory( attrs.triggerCategory, newValue );
                    }
                });
            }
        }
    }]);

    nbAddNew.directive( 'triggersAvailable', [ 'Trigger', '$rootScope', 'triggersFromCat', function( Trigger, $rootScope, triggersFromCat ) {
        return {
            require: 'uiSelect',
            link: function( scope, element, attrs, $select ) {
                scope.$watch( '$select.selected.id', function(newValue, oldValue) {
                    if ( newValue ) {
                        Trigger.triggerSelected( attrs.triggerSelect, newValue );
                    }
                });
            }
        }
    }]);

    nbAddNew.directive( 'triggerCategory', [ 'Trigger', function( Trigger ) {
        return {
            require: 'uiSelect',
            link: function( scope, element, attrs, $select ) {
                scope.$watch( '$select.selected.id', function(newValue, oldValue) {
                    Trigger.categorySelected( attrs.triggerCategory, newValue );
                    //console.log(scope);
                    //console.log(attrs);
                    //Trigger.categorySelected(  );
                } );

            }
        }
    }]);

    nbAddNew.controller( 'configureNotification', ['$scope', '$http', '$sce', '$rootScope', 'Trigger', function( $scope, $http, $sce, $rootScope, Trigger ) {

        $scope.triggersAdded = Trigger.getTriggers();

        //this.blahblah = 'stuffs';

        $scope.$on( 'trigger.added', function( event ) {
            $scope.triggersAdded = Trigger.getTriggers();
            $scope.$apply();
        });

        $scope.triggerCollapsed = function( triggerId ) {
            if ( $scope.triggersAdded[triggerId] ) {
                if ( ! $scope.triggersAdded[triggerId].editMode ) {
                    return true;
                }
            }

        };

        $scope.deleteTrigger = function() {
            //console.log(Trigger.added);
            //console.log($scope);
            //console.log(Trigger.added.indexOf(ctrl.triggerId));
            //console.log(ctrl.triggerId);
            //delete Trigger.added[ctrl.triggerId];
            //ctrl.currentTrigger.trashed = true;
            Trigger.delete(ctrl.triggerId);
            //Trigger.added[ctrl.triggerId] = null;
            //console.log(Trigger.added);

            //$rootScope.$broadcast('trigger.added');
        };

        console.log($scope);

        $scope.services = [];
        $scope.methods  = [];
        $scope.events   = [];
        $scope.triggers = [];

        $scope.services.available = [];
        $scope.methods.available  = [];
        $scope.events.available   = [];
        $scope.triggers.available = [];

        $scope.services.selected = [];
        $scope.methods.selected  = [];
        $scope.events.selected   = [];
        $scope.triggers.selected = [];
        $scope.registeredTools = [];

        $scope.triggerRowID = null;

        methodsRequest();
        eventsRequest();

        $scope.showMethods = true;



        $scope.triggerHeader = 'New Trigger';
        //$scope.rowToolbarButtons = [];

        function methodsRequest() {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_methods'
                }
            }).then(function (response) {
                $scope.methods.available = response.data;
            });
        }

        function servicesRequest( method_id ) {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_services',
                    selected_method: method_id
                }
            }).then(function (response) {
                $scope.services.available = response.data;
            });
        }

        function eventsRequest() {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_events'
                }
            }).then(function (response) {
                $scope.events.available = response.data;
            });
        }

        function triggersRequest(row_id, event_id) {

            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_triggers',
                    selected_event: event_id
                }
            }).then(function (response) {
                $scope.triggers.available[row_id] = response.data;
            });
        }

        function generateConditionalLogic( row_id, trigger ) {
            var conditionalLogic = [];
            angular.forEach( trigger.local_settings, function(value, key) {
                var selectionsArray = [];
                angular.forEach( value.selections, function(value, key) {
                    selectionsArray.push( value );
                });
                conditionalLogic.push( { type: value.input_type, label: value.label, selections: selectionsArray } );
            });

            return conditionalLogic;
        }
        //$scope.rowToolbarButtons = [['this', 'tha', 'else']];

        function generateTriggerMessage( row_id, trigger ) {
            $http({
                method: 'GET',
                url: nbAjax.ajaxurl,
                params: {
                    action: 'get_trigger_message',
                    trigger_id: trigger.id,
                    placeholder: trigger.placeholder,
                    merge_tags: trigger.merge_tags
                }
            }).then(function (response) {
                //QTags( tinyMCEPreInit.qtInit[ 'notifybot-message-post-delete' ] );
                //QTags._buttonsInit();
                //tinymce.init(tinyMCEPreInit.mceInit['editorcontentid']);
            });
        }

        $scope.saveTrigger = function ( selectedTriggerData ) {
            console.log($scope.messageContent);
        };

        $scope.methodClicked = function( method_id, method_label ) {
            $scope.methods.selected = {id: method_id, label: method_label};
            servicesRequest( method_id );
            $scope.methodSelected = true;
            $scope.showServices = true;
        };

        $scope.serviceClicked = function ( service_id, service_label ) {
            $scope.services.selected = {id: service_id, label: service_label};
            $scope.events.available = eventsRequest();
            $scope.serviceSelected = true;
            $scope.showEvents = true;
        };

        $scope.eventSelected = function (row_id, event_id, event_label ) {
            $scope.events.selected[row_id] = {id: event_id, label: event_label};
            triggersRequest( row_id, event_id );
            $scope.showTriggers = true;
        };

        $scope.triggerSelected = function ( row_id, trigger ) {
            var conditionalLogic = generateConditionalLogic(row_id, trigger);
            $scope.triggers.selected[row_id] = {id: trigger.id, label: trigger.label, conditions: conditionalLogic};

            //var toolbar = textAngularManager.retrieveToolbarsViaEditor('trigger-message-content-' + row_id);
            //toolbar.name = 'trigger-message-toolbar-' + row_id;
            //textAngularManager.retrieveToolbarsViaEditor('trigger-message-content-' + row_id)[0].tools = {};
            //console.log(textAngularManager.retrieveToolbarsViaEditor('trigger-message-content-' + row_id)[0].tools);
            //textAngularManager.unregisterToolbar('trigger-message-toolbar-' + row_id);
            //textAngularManager.registerToolbar(toolbar);
            //console.log(textAngularManager.retrieveToolbarsViaEditor('trigger-message-content-' + row_id));
            //textAngularManager.updateToolDisplay('post_title', null, true);


            angular.forEach(trigger.merge_tags, function(value, key) {
                textAngularManager.removeTool(value);
                textAngularManager.addToolToToolbar(value, {buttontext: value, action: function(){ this.$editor().wrapSelection( 'insertHTML' , '{' + value + '}' ) } }, 'trigger-message-toolbar-' + row_id );
                $scope.registeredTools.push(value);
            });

            //$rootScope.$broadcast( '$destroy' );

            console.log($scope);


            //console.log(taTools);

            //textAngularManager.updateToolsDisplay({"whateverthisis": {iconclass: 'fa fa-code'}});
            //taRegisterTool('colourRed', {
            //    iconclass: "fa fa-square red",
            //    action: function(){
            //        this.$editor().wrapSelection('forecolor', 'red');
            //    }
            //});
            //textAngularManager.addToolToToolbar('somethingfunky');

            //textAngularManager.registerToolbar('testtools');
            //var editor = textAngularManager.retrieveEditor('something');

            //$scope.rowToolbarButtons['1'] = [['this', 'tha', 'else']];

        };

        $scope.addNewTriggerRow = function() {
            $scope.triggerRowID = $scope.triggerRowID + 1;
            $scope.triggers.selected.push($scope.triggerRowID);
        };


    }]);
