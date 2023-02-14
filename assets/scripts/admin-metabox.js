/*!
    Name: admin-metabox.js
    Author: AuRise Creative | https://aurisecreative.com
    Last Modified: 2022.09.20.12.29
*/
var $ = jQuery.noConflict(),
    socializedMetaBox = {
        version: '2022.09.20.12.29',
        init: function() {
            console.info('Initialising admin-metabox.js. Last modified: ' + socializedMetaBox.version);
            //Update Links button
            $('.au-metabox-socialized [type="submit"][name="socialized_submit"]').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this),
                    fields = {
                        'post_id': $('input#post_ID').val(),
                        'slug': $('input#socialized_slug').val(),
                        'slug_old': $('input#socialized_slug').attr('placeholder'),
                        'campaign_term': $('input#socialized_term').val()
                    };
                //Light client-side validation
                if (fields.slug != fields.slug_old) {
                    console.info('Update Links', fields);
                    $btn.attr('disabled', 'disabled').next('.loading-spinner').removeClass('hide');
                    var $notice = $('.au-metabox-socialized fieldset').find('#update-links-output');
                    if ($notice.length) {
                        //If the div element exists, simply hide it
                        $notice.attr('class', 'notice notice-info hide');
                    }
                    setTimeout(function() {
                        $.ajax({
                            type: 'post',
                            url: au_object.ajax_url,
                            data: {
                                'action': 'socialized_update_url', //name of handle after "wp_ajax_" prefix in socialized.php
                                'fields': encodeURIComponent(JSON.stringify(fields))
                            },
                            cache: false,
                            error: function(xhr) {
                                console.error('AJAX Error (Error Code: AuWP-Soc-01)', xhr);
                                socializedMetaBox.complete({
                                    'success': 0,
                                    'error': xhr,
                                    'output': xhr.responseText + ' Error Code: AuWP-Soc-01'
                                });
                            },
                            success: function(response) {
                                //console.info('AJAX Success', response);
                                try {
                                    response = JSON.parse(response);
                                    //console.info('JSON Success', response);
                                    socializedMetaBox.complete(response);
                                } catch (xhr) {
                                    console.error('JSON Error (Error Code: AuWP-Soc-02)', response);
                                    socializedMetaBox.complete({
                                        'success': 0,
                                        'error': xhr,
                                        'response': response,
                                        'output': xhr.responseText + ' Error Code: AuWP-Soc-02'
                                    });
                                }
                            }
                        });
                    }, 500);
                } else {
                    socializedMetaBox.complete({
                        'success': 1,
                        'error': 1,
                        'response': {},
                        'output': 'No change in vanity slug.'
                    });
                }
            });
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
            notice_class += ' is-dismissible';
            setTimeout(function() {
                var $notice = $('.au-metabox-socialized fieldset').find('#update-links-output');
                if (!$notice.length) {
                    //If the div element doesn't exist, create it
                    $('.au-metabox-socialized fieldset').append('<p id="update-links-output" class="notice notice-info hide"></p>');
                    $notice = $('.au-metabox-socialized fieldset').find('#update-links-output');
                }
                $notice.addClass(notice_class).removeClass('notice-info hide').html(response.output + '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
                $notice.find('.notice-dismiss').off('click').on('click', function(e) {
                    e.preventDefault();
                    $(this).closest('.notice.is-dismissible').addClass('hide');
                });
                $('.au-metabox-socialized [type="submit"][name="socialized_submit"]').removeAttr('disabled').next('.loading-spinner').addClass('hide');
                if (response.error) {
                    console.error('An error has occurred', response);
                } else {
                    //console.info('Completed successfully', response);
                    //Update all of the links displayed in the metabox
                    if (response.hasOwnProperty('links')) {
                        $.each(response.links, function(platform, data) {
                            $('.au-metabox-socialized ol li#socialized_' + platform + ' ul li.vanity-url a').attr('href', data.vanity_url_link).text(data.vanity_url_label); //Update the link and text of the vanity URL
                            $('.au-metabox-socialized ol li#socialized_' + platform + ' ul li.campaign-term .value').text(data.campaign_term); //Update the text displayed for the campaign term
                        });
                    }
                }
            }, 1000);
        }
    };
$(document).ready(socializedMetaBox.init);