/**
 *  socialized.js
 *  @version 1.2.0
 *  @author Tessa Watkins LLC <contact@tessawatkins.com> (https://tessawatkins.com)
 **/
var $ = jQuery.noConflict();
var socialized = {
    init: function() {
        var offset = 50; //Arbitrarily set to 50px
        if ($('#wpadminbar').length) { offset += $('#wpadminbar').outerHeight(true); } //Increase offset if viewing WP admin bar
        try {
            //Stickybits Documentation: https://dollarshaveclub.github.io/stickybits/
            stickybits($('.socialized-sticky-wrapper .socialized-links'), {
                useStickyClasses: true,
                stickyBitStickyOffset: offset
            });
        } catch (err) {
            console.warn('Error sticking socialized links!', err);
        }
    }
};
$(document).ready(socialized.init);