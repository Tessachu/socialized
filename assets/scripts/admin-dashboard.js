/*!
    Name: admin-dashboard.js
    Author: Tessa Watkins LLC | https://tessawatkins.com
    Version: 1.0.0
*/
var $ = jQuery.noConflict();
var Socialized_Admin = {
    Version: '1.0.0',
    Init: function() {
        console.info('Initialising admin-dashboard.js. Version ' + Socialized_Admin.Version);
        $('section.tab').addClass('hide');
        //Add button listeners
        $('.tab-btn').on('click', function(e) {
            var tab = $(this).data('id');
            Socialized_Admin.OpenTab(tab);
        });
        //Add checkbox listeners
        $('input[type="hidden"]+input[type="checkbox"]').on('click', function(e) {
            var $checkbox = $(this);
            $checkbox.prev('input[type="hidden"]').val($checkbox.is(':checked') || $checkbox.prop('checked') ? 'true' : 'false');
        });
        Socialized_Admin.ControlledFields.Init();
        //Generate button
        $('#generate-urls.button').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            console.info('Generate once now.');
            $btn.attr('disabled', 'disabled').next('.loading-spinner').removeClass('hide');
            $('#generate-status').attr('class', 'status-text notice notice-info hide');
            setTimeout(function() {
                // Socialized_Admin.Complete({
                //     'success': 1,
                //     'error': 0,
                //     'output': 'Fake completion'
                // });

                $.ajax({
                    type: 'post',
                    url: tw_ajax_object.ajax_url,
                    //async: false,
                    data: { 'action': 'regenerate_urls' }, //name of handle after "wp_ajax_" prefix in socialized.php
                    cache: false,
                    error: function(xhr) {
                        console.error('AJAX Error', xhr);
                        Socialized_Admin.Complete({
                            'success': 0,
                            'error': xhr,
                            'output': xhr.responseText + ' Error: TWP-Soc-01'
                        });
                    },
                    success: function(response) {
                        console.info('AJAX Success');
                        try {
                            response = JSON.parse(response);
                            Socialized_Admin.Complete(response);
                        } catch (xhr) {
                            Socialized_Admin.Complete({
                                'success': 0,
                                'error': xhr,
                                'response': response,
                                'output': xhr.responseText + ' Error: TWP-Soc-02'
                            });
                        }
                    }
                });
            }, 500);
        });
        //Open first Tab
        Socialized_Admin.OpenTab($('#tabs .tab-btn').first().data('id'));
        //Init is completed. Hide loading spinner image and display the admin UI
        $('.loading-spinner').addClass('hide');
        $('.admin-ui').removeClass('hide');
    },
    ControlledFields: {
        /*
            To use this feature...

            1. Add a "controller" class to the radio, checkbox, or select HTML elements that will be controlling others
                - Checkbox: displays the controlled fields when checked and hides when unchecked.
                - Radio:    displays the controlled fields when checked and hides the rest.
                - Select:   displays the controlled fields when they match the value that is selected and hides the rest.
            2. Controlled fields should have a data-controller attribute on its wrapping element set to the unique ID of its controller
            3. Controlled fields should have a "hide" class added to its wrapping element to hide it by default. This feature simply toggles that class on/off, so you'll need CSS to actually hide it based on that class.
            4. If it is controlled by a radio button or select element, the wrapping element of the controlled field should also have a data-values attribute set to a comma separated list of the values used to display it.
            5. If the controlled field should be required when displayed, instead of adding the required attribute to the input/select field, add the data-required="true" attribute.
            6. It is possible to nest controllers.      
        */
        Init: function() {
            //Add controllable field listeners
            $('input[type=checkbox].controller, input[type=radio].controller').on('click', Socialized_Admin.ControlledFields.ToggleHandler);
            $('select.controller').on('change', Socialized_Admin.ControlledFields.ToggleHandler);
            $('input[type=checkbox].controller, input[type=radio].controller, select.controller').each(function() {
                var $controller = $(this);
                var id = $controller.attr('id');
                var $controlled = $('[data-controller="' + id + '"]');
                if ($controlled.length) {
                    var controlled_value = $controller.is('input[type=checkbox]') ? Socialized_Admin.ControlledFields.GetCheckbox($controller) : $controller.val();
                    Socialized_Admin.ControlledFields.ToggleControlledFields(id, controlled_value);
                } else {
                    console.warn('Controlled fields for Controller #' + id + ' do not exist!');
                }
            });
        },
        ToggleHandler: function(e) {
            var $controller = typeof(e) == 'string' ? $('#' + e) : $(this);
            var id = $controller.attr('id');
            Socialized_Admin.ControlledFields.ToggleControlledFields(id, null);
        },
        ToggleControlledFields: function(id, forcedToggle) {
            var $controller = $('#' + id);
            if ($controller.length < 1) { console.warn('Controller #' + id + ' does not exist!'); return; }
            //console.info('Toggle Fields: ' + id);
            var $controlled = $('[data-controller="' + id + '"]');
            if ($controlled.length < 1) { console.warn('Controlled fields for Controller #' + id + ' do not exist!'); return; }
            //$controlled.find('.tw-group-set-error').remove(); //Remove all old validation messages for controlled checkbox sets
            //$controlled.find('.already-checked-as-other').removeClass('already-checked-as-other'); //Reset the "already checked" classes for controlled checkbox sets
            if ($controller.is('select')) {
                var controlled_value = forcedToggle === null || forcedToggle === undefined ? $controller.val() : forcedToggle;
                //Because it is a select field, the value must match that of the input to display it
                $controlled.each(function() {
                    var $thisControlled = $(this);
                    var myValues = $thisControlled.data('values');
                    if (myValues.indexOf(',') >= 0) {
                        myValues = myValues.split(',');
                    } else {
                        myValues = [myValues];
                    }
                    var matches = 0;
                    $.each(myValues, function(i, value) {
                        if (value == controlled_value) { matches++; }
                    });
                    if (matches > 0) {
                        //This controlled element's value matches what was selected in the dropdown, display it
                        //console.info('Display Select\'s Fields...');
                        $thisControlled.removeClass('hide');
                        //If there are any required fields, add the required flag to them
                        var $required_fields = $thisControlled.find('[data-required="true"]');
                        if ($required_fields.length > 0) {
                            $required_fields.each(function() {
                                $(this).attr('required', 'required');
                            });
                        }
                    } else {
                        //This controlled element's value does not match what was selected in the dropdown, hide it
                        //console.info('Hide Select\'s Fields...');
                        //Checkbox or radio button is false, so hide its options
                        $thisControlled.addClass('hide');
                        //If there are any required fields, remove the required flag from them
                        var $required_fields = $thisControlled.find('[required]');
                        if ($required_fields.length > 0) {
                            $required_fields.each(function() {
                                $(this).removeAttr('required');
                            });
                        }
                        //Search through the fields that are being hidden, and if they are controllers themselves,
                        //toggle them off and hide their controlled fields
                        if ($thisControlled.length) {
                            $thisControlled.each(function(i, value) {
                                var $c = $(this).find('.controller');
                                if ($c.length) {
                                    //console.info('One of the fields you are hiding is a controller, so hide its fields!');
                                    Socialized_Admin.ControlledFields.ToggleCheckbox($c, false);
                                    Socialized_Admin.ControlledFields.ToggleControlledFields($c.attr('id'), false);
                                }
                            });
                        }
                    }
                });
            } else {
                var toggle = forcedToggle === null || forcedToggle === undefined ? Socialized_Admin.ControlledFields.GetCheckbox($controller) : forcedToggle;
                if (toggle) {
                    //Checkbox or radio button is true, so reveal its options
                    $controlled.removeClass('hide');
                    //If there are any required fields, add the required flag to them
                    var $required_fields = $controlled.find('[data-required="true"]');
                    if ($required_fields.length > 0) {
                        $required_fields.each(function() {
                            $(this).attr('required', 'required');
                        });
                    }
                    if ($controller.is('[type=radio]')) {
                        //Because we are a radio button, we have to hide all other options except for this
                        var $radioGroup = $('[name="' + $controller.attr('name') + '"]:not(#' + id + ')');
                        //Search through the fields that are being hidden, and if they are controllers themselves,
                        //toggle them off and hide their controlled fields
                        if ($radioGroup.length) {
                            $radioGroup.each(function(i, value) {
                                Socialized_Admin.ControlledFields.ToggleControlledFields($(this).attr('id'), false);
                            });
                        }
                    }
                } else {
                    //console.info('Hide Fields...');
                    //Checkbox or radio button is false, so hide its options
                    $controlled.addClass('hide');
                    //If there are any required fields, remove the required flag from them
                    var $required_fields = $controlled.find('[required]');
                    if ($required_fields.length > 0) {
                        $required_fields.each(function() {
                            $(this).removeAttr('required');
                        });
                    }
                    //Search through the fields that are being hidden, and if they are controllers themselves,
                    //toggle them off and hide their controlled fields
                    if ($controlled.length) {
                        $controlled.each(function(i, value) {
                            var $c = $(this).find('.controller');
                            if ($c.length) {
                                //console.info('One of the fields you are hiding is a controller, so hide its fields!');
                                Socialized_Admin.ControlledFields.ToggleCheckbox($c, false);
                                Socialized_Admin.ControlledFields.ToggleControlledFields($c.attr('id'), false);
                            }
                        });
                    }
                }
            }
        },
        GetCheckbox: function(input) {
            //Returns a true/false boolean value based on whether the checkbox is checked
            var $input = $(input);
            return ($input.is(':checked') || $input.prop('checked'));
        },
        ToggleCheckbox: function(input, passedValue) {
            //Changes a checkbox input to be checked or unchecked based on boolean parameter (or toggles if not included)
            //Only changes it visually - it does not change any data in any objects
            var $input = $(input);
            var value = passedValue;
            if (typeof(value) != 'boolean' || value === undefined) {
                value = !Socialized_Admin.ControlledFields.GetCheckbox($input);
            }
            if (value) {
                $input.attr('checked', 'checked');
                $input.prop('checked', true);
            } else {
                $input.removeAttr('checked');
                $input.prop('checked', false);
            }
        },
    },
    Complete: function(response) {
        //Arbitrarily update after a moment to allow for human processing
        var notice_class = 'notice-warning';
        if (response.success && !response.error) {
            notice_class = 'notice-success';
        } else if (response.success && response.error) {
            notice_class = 'notice-warning';
        } else if (response.error) {
            notice_class = 'notice-error';
        }
        setTimeout(function() {
            $('#generate-status').addClass(notice_class).removeClass('notice-info hide').html(response.output);
            $('#generate-urls').removeAttr('disabled').next('.loading-spinner').addClass('hide');
            if (response.error) {
                console.error('An error has occurred', response);
                debugger;
            } else {
                console.info('Completed successfully', response);
            }
        }, 1000);
    },
    OpenTab: function(tab) {
        $('.tab-btn, section.tab').removeClass('active'); //Deactivate all of the tab buttons and tab contents
        $('section.tab').addClass('hide'); //Hide all of the tab contents
        $('#' + tab).removeClass('hide').addClass('active'); //Show and activate the tab content
        $('#open-' + tab).addClass('active'); //Activate the tab button
    },
    StringExistsAndNotEmpty: function(source) {
        if (source !== undefined && source !== null) { return (source.toString().trim() !== ''); }
        return false;
    },
    EscapeRegExp: function(str) {
        return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
    }
};
$(document).ready(Socialized_Admin.Init);
/* Utility Functions */
String.prototype.replaceAll = function(f, r, no_escape) {
    var rexp = new RegExp(Socialized_Admin.EscapeRegExp(f), 'g');
    if (no_escape) { rexp = new RegExp(f, 'g'); }
    return this.replace(rexp, r);
};