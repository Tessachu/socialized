/*!
    Name: admin-dashboard.js
    Author: AuRise Creative | https://aurisecreative.com
    Last Modified: 2022.09.20.12.29
*/
var $ = jQuery.noConflict(),
    auPluginAdminDashboard = {
        version: '2022.09.20.12.29',
        init: function() {
            //Plugin initialization
            console.info('Initialising admin-dashboard.js. Last modified ' + auPluginAdminDashboard.version);
            auPluginAdminDashboard.tabs.init();
            auPluginAdminDashboard.forms.init();

            //Custom plugin functionality
            auPluginAdminDashboard.generation.init();

            //Init complete, display admin UI
            auPluginAdminDashboard.initComplete();
        },
        tabs: {
            init: function() {
                //Hide all tabs
                $('.au-plugin section.tab').addClass('hide');

                //Add button listeners
                $('.au-plugin a.nav-tab').on('click', auPluginAdminDashboard.tabs.handler);
            },
            handler: function(event) {
                event.preventDefault();
                var tab = $(this).attr('href').replace('#', '');
                auPluginAdminDashboard.tabs.open(tab);
            },
            open: function(tab) {
                $('.au-plugin a.nav-tab, .au-plugin #tab-content section.tab').removeClass('nav-tab-active'); //Deactivate all of the tab buttons and tab contents
                $('.au-plugin #tab-content section.tab').addClass('hide'); //Hide all of the tab contents
                $('.au-plugin #' + tab).removeClass('hide').addClass('nav-tab-active'); //Show and activate the tab content
                $('.au-plugin #open-' + tab).addClass('nav-tab-active'); //Activate the tab button
            }
        },
        forms: {
            init: function() {
                //Add checkbox listeners for switch toggles
                let $checkboxes = $('.au-plugin input[type="hidden"]+input[type="checkbox"]');
                if ($checkboxes.length) {
                    $('.au-plugin input[type="hidden"]+input[type="checkbox"]').on('click', function(e) {
                        var $checkbox = $(this),
                            value = $checkbox.is(':checked') || $checkbox.prop('checked');
                        if ($checkbox.hasClass('reverse-checkbox')) {
                            //Reverse checkboxes show a positive association with the "false" value
                            $checkbox.prev('input[type="hidden"]').val(value ? 'false' : 'true');
                        } else {
                            $checkbox.prev('input[type="hidden"]').val(value ? 'true' : 'false');
                        }
                    });
                }
                auPluginAdminDashboard.forms.controlledFields.init();
            },
            getCheckbox: function(input) {
                //Returns a true/false boolean value based on whether the checkbox is checked
                var $input = $(input);
                return ($input.is(':checked') || $input.prop('checked'));
            },
            toggleCheckbox: function(input, passedValue) {
                //Changes a checkbox input to be checked or unchecked based on boolean parameter (or toggles if not included)
                //Only changes it visually - it does not change any data in any objects
                var $input = $(input);
                var value = passedValue;
                if (typeof(value) != 'boolean' || value === undefined) {
                    value = !auPluginAdminDashboard.forms.controlledFields.getCheckbox($input);
                }
                if (value) {
                    $input.attr('checked', 'checked');
                    $input.prop('checked', true);
                } else {
                    $input.removeAttr('checked');
                    $input.prop('checked', false);
                }
            },
            controlledFields: {
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
                init: function() {
                    //Add controllable field listeners
                    if ($('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller, .au-plugin select.controller').length) {
                        $('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller').on('click', auPluginAdminDashboard.forms.controlledFields.toggleHandler);
                        $('.au-plugin select.controller').on('change', auPluginAdminDashboard.forms.controlledFields.toggleHandler);
                        $('.au-plugin input[type=checkbox].controller, .au-plugin input[type=radio].controller, .au-plugin select.controller').each(function() {
                            var $controller = $(this);
                            var id = $controller.attr('id');
                            var $controlled = $('[data-controller="' + id + '"]');
                            if ($controlled.length) {
                                var controlled_value = $controller.is('input[type=checkbox]') ? auPluginAdminDashboard.forms.controlledFields.getCheckbox($controller) : $controller.val();
                                auPluginAdminDashboard.forms.controlledFields.toggleControlledFields(id, controlled_value);
                            } else {
                                console.warn('Controlled fields for Controller #' + id + ' do not exist!');
                            }
                        });
                    }
                },
                toggleHandler: function(e) {
                    var $controller = typeof(e) == 'string' ? $('#' + e) : $(this);
                    var id = $controller.attr('id');
                    auPluginAdminDashboard.forms.controlledFields.toggleControlledFields(id, null);
                },
                toggleControlledFields: function(id, forcedToggle) {
                    var $controller = $('#' + id);
                    if ($controller.length < 1) { console.warn('Controller #' + id + ' does not exist!'); return; }
                    //console.info('Toggle Fields: ' + id);
                    var $controlled = $('[data-controller="' + id + '"]');
                    if ($controlled.length < 1) { console.warn('Controlled fields for Controller #' + id + ' do not exist!'); return; }
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
                                            auPluginAdminDashboard.forms.controlledFields.toggleCheckbox($c, false);
                                            auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($c.attr('id'), false);
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        var toggle = forcedToggle === null || forcedToggle === undefined ? auPluginAdminDashboard.forms.controlledFields.getCheckbox($controller) : forcedToggle;
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
                                        auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($(this).attr('id'), false);
                                    });
                                }
                            }
                        } else {
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
                                        auPluginAdminDashboard.forms.controlledFields.toggleCheckbox($c, false);
                                        auPluginAdminDashboard.forms.controlledFields.toggleControlledFields($c.attr('id'), false);
                                    }
                                });
                            }
                        }
                    }
                }
            }
        },
        initComplete: function() {
            //If there is a Hash in the URL, open that tab
            let current_tab = document.location.hash;
            if (current_tab && $(current_tab).length) {
                //open the current tab
                auPluginAdminDashboard.tabs.open(current_tab.replace('#', ''));
            } else {
                //open first tab
                auPluginAdminDashboard.tabs.open($('.au-plugin a.nav-tab').first().attr('href').replace('#', ''));
            }
            //init is completed. Hide loading spinner image and display the admin UI
            $('.au-plugin .loading-spinner').addClass('hide');
            $('.au-plugin .admin-ui').removeClass('hide');
            console.info('Initialisation completed for admin-dashboard.js.');
        },
        generation: {
            init: function() {
                $('.au-plugin #generate-urls.button').on('click', auPluginAdminDashboard.generation.handler);
            },
            handler: function(e) {
                e.preventDefault();
                var $btn = $(this);
                console.info('Generate once now.');
                $btn.attr('disabled', 'disabled').next('.progress-spinner').removeClass('hide');
                $('#generate-status').attr('class', 'status-text notice notice-info hide');
                setTimeout(function() {
                    $.ajax({
                        type: 'post',
                        url: au_object.ajax_url,
                        data: { 'action': 'socialized_regenerate_urls' }, //name of handle after "wp_ajax_" prefix in socialized.php
                        cache: false,
                        error: function(xhr) {
                            console.error('AJAX Error (Error Code: AuWP-Soc-01)', xhr);
                            auPluginAdminDashboard.generation.complete({
                                'success': 0,
                                'error': xhr,
                                'output': xhr.responseText + ' Error Code: AuWP-Soc-01'
                            });
                        },
                        success: function(response) {
                            try {
                                response = JSON.parse(response);
                                auPluginAdminDashboard.generation.complete(response);
                            } catch (xhr) {
                                console.error('AJAX Error (Error Code: AuWP-Soc-02)', xhr);
                                auPluginAdminDashboard.generation.complete({
                                    'success': 0,
                                    'error': xhr,
                                    'response': response,
                                    'output': xhr.responseText + ' Error Code: AuWP-Soc-02'
                                });
                            }
                        }
                    });
                }, 500);
            },
            complete: function(response) {
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
                    $('.au-plugin #generate-status').addClass(notice_class).removeClass('notice-info hide').html(response.output);
                    $('.au-plugin #generate-urls').removeAttr('disabled').next('.progress-spinner').addClass('hide');
                    if (response.error) {
                        console.error('An error has occurred (Error Code: AuWP-Soc-03)', response);
                    } else {
                        console.info('Completed successfully', response);
                    }
                }, 1000);
            },
        }
    };
$(document).ready(auPluginAdminDashboard.init);