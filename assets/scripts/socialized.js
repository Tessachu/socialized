/*!
    Name: socialized.js
    Author: AuRise Creative | https://aurisecreative.com
    Last Modified: September 21, 2022, at 18:29
*/
var socialized = {
    init: function() {
        let sticky_buttons = document.querySelector('socialized-sticky-yes');
        if (!!sticky_buttons) {
            let parent = document.querySelector('.socialized-sticky-yes').parentElement;
            while (parent) {
                if (getComputedStyle(parent).overflow !== 'visible') {
                    console.info('[Socialized] changing overflow for element to "visible" so sticky works');
                    parent.style.overflow = 'visible';
                }
                if (!!parent.parentElement) {
                    parent = parent.parentElement;
                } else {
                    parent = false;
                }
            }
            let offset = 50, //Arbitrarily set to 50px
                adminBar = document.getElementById('wpadminbar');
            if (!!adminBar) { offset += adminBar.offsetHeight; } //Increase offset if viewing WP admin bar
            try {
                //Stickybits Documentation: https://dollarshaveclub.github.io/stickybits/
                stickybits('.socialized-sticky-yes', {
                    useStickyClasses: true,
                    stickyBitStickyOffset: offset
                });
            } catch (err) {
                console.warn('[Socialized] Error sticking socialized links!', err);
            }
        }
    },
    copyToClipboard: function(e) {
        e.preventDefault(); //Prevent default behavior
        e.stopPropagation(); //Stop from bubbling up so it's only triggered once on the link element
        if (window.isSecureContext) {
            //The clipboard feature is only available on secure sites
            let urlToCopy = e.currentTarget.getAttribute('href');
            navigator.clipboard.writeText(urlToCopy).then(function() {
                document.getElementById('socialized-copied-popup').classList.remove('hidden');
                setTimeout(function() {
                    document.getElementById('socialized-copied-popup').classList.add('hidden');
                }, 300 * 5);
            });
            console.info('[Socialized] Copied to clipboard', urlToCopy);
        } else {
            console.warn('[Socialized] The "copy to clipboard" feature is unavailable on this website. Pretending anyway...');
            document.getElementById('socialized-copied-popup').classList.remove('hidden');
            setTimeout(function() {
                document.getElementById('socialized-copied-popup').classList.add('hidden');
            }, 300 * 5);
        }
    }
};
socialized.init();