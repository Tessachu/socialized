=== Socialized ===
Contributors: tessawatkinsllc
Donate link: https://just1voice.com/donate/
Tags: social media, link tracking, Google UTM, sharing, Custom Google Campaign, SEO, Facebook, Twitter, Pinterest, LinkedIn, copy to email, copy to clipboard assurance, qa, aria, landmarks, screen reader, Google Analytics, Google Tag Manager, GA4
Tested up to: 6.1
Stable tag: 3.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add social media sharing buttons to your posts, pages, and custom post types that automatically track to a custom campaign with your Google Analytics!

== Description ==

Socialized adds social media sharing buttons to your posts, pages, and custom post types that are automatically integrated with Google Analytics using campaign parameters. When a user shares your post using these buttons, they can share a vanity URL that is either automatically generated or customized by you and is specific to that button. When another user on social media then clicks on that shared link, the vanity URL redirects them to your page with analytics tracking automatically.

By adding campaign parameters to the destination URLs, you can collect information about the overall efficacy of these campaigns, and also understand where the campaigns are more effective. For example, your “Summer Sale” campaign might be generating lots of revenue, but if you’re running the campaign on several social media platforms, you’ll want to know which of them is sending you the customers who generate the most revenue. Or if you’re running different versions of the campaign via email, video ads, and in-app ads, you can compare the results to see where your marketing is most effective.

The campaign parameters that are automatically added are sent to Google Analytics when a user clicks on one of the shared links, and the related data is available in the campaigns reports. Below is a list of the campaign parameters that are added and their possible values:

* `utm_source`. Possible value(s): `facebook` | `twitter` | `linkedin` | `pinterest` | `email` | `vanity-url`
* `utm_medium`. Possible value(s): `social`
* `utm_content`. Possible value(s): `socialized-share-link`
* `utm_campaign`: Possible value(s): `socialized` | or define in *Settings*
* `utm_term`:  Possible value(s):  Defined by typing in the text box in the metabox on the post or page | or the “Focus keyphrase” by Yoast SEO

Your permalink struture will not be affected. The randomly generated vanity URLs are automatically created when you save the post. [Learn more](https://support.google.com/analytics/answer/1033863) about custom campaigns in Google.

== Installation ==

There are three (3) ways to install my plugin: automatically, upload, or manually.

= Install Method 1: Automatic Installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser.

1. Log in to your WordPress dashboard.
1. Navigate to **Plugins > Add New**.
1. Where it says “Keyword” in a dropdown, change it to “Author”
1. In the search form, type “TessaWatkinsLLC” (results may begin populating as you type but my plugins will only show when the full name is there)
1. Once you’ve found my plugin in the search results that appear, click the **Install Now** button and wait for the installation process to complete.
1. Once the installation process is completed, click the **Activate** button to activate my plugin.
1. After this plugin is activated, see below for additional instructions for setup.

= Install Method 2: Upload via WordPress Admin =

This method involves is a little more involved. You don’t need to leave your web browser, but you’ll need to download and then upload the files yourself.

1. [Download my plugin](https://wordpress.org/plugins/socialized/) from WordPress.org; it will be in the form of a zip file.
1. Log in to your WordPress dashboard.
1. Navigate to **Plugins > Add New**.
1. Click the **Upload Plugin** button at the top of the screen.
1. Select the zip file from your local file system that was downloaded in step 1.
1. Click the **Install Now** button and wait for the installation process to complete.
1. Once the installation process is completed, click the **Activate** button to activate my plugin.
1. After this plugin is activated, see below for additional instructions for setup.

= Install Method 3: Manual Installation =

This method is the most involved as it requires you to be familiar with the process of transferring files using an SFTP client.

1. [Download my plugin](https://wordpress.org/plugins/socialized/) from WordPress.org; it will be in the form of a zip file.
1. Unzip the contents; you should have a single folder named `socialized`.
1. Connect to your WordPress server with your favorite SFTP client.
1. Copy folder from step 2 to the `/wp-content/plugins/` folder in your WordPress directory. Once the folder and all of its files are there, installation is complete.
1. Now log in to your WordPress dashboard.
1. Navigate to **Plugins > Installed Plugins**. You should now see my plugin in your list.
1. Click the **Activate** button under my plugin to activate it.
1. After this plugin is installed and activated, see below for additional instructions for setup.

= After Plugin Installation and Activation =

1. Navigate to **Tools > Socialized** to change any *global settings* to fit your needs.
1. When creating new posts or editing existing ones, use the meta box to configure settings specific to that post.
1. Publish your post to see the buttons on the page.

== Screenshots ==

1. Plugin settings screen
2. The Socialized plugin general meta box. You'll see this on edit post pages for posts, pages, and custom post types that you've set to allow in the settings.
3. The default appearance of the social media sharing buttons using images. There is no hover state.
4. The default appearance of the social media sharing buttons using Font Awesome. When active, focused, or hovering over a button, the icon animates to be rounded. E.g. the LinkedIn icon.
5. The default appearance of the social media sharing buttons as text. When active, focused, or hovering over a button, the icon animates the background and text colors to swap. E.g. the LinkedIn icon.
6. Acquisition report in Google Analytics identifies sources of traffic from the people that arrived by clicking on links shared by users on their respective platforms. Traditionally, when people share the default URL or permalink, this traffic goes untracked.

== Frequently Asked Questions ==

= What platforms are supported? =

Facebook, Twitter, LinkedIn, Pinterest, email, and copying to clipboard (for sites with SSL enabled) are supported.

= Can I change the name of the campaign? =

Yes! You can change the name of the campaign, or the value of `utm_campaign`, from the plugin settings page.

= Can I change the vanity URL? =

Yes! When a new post is created, a random string will be generated for that post, but you can edit it to be whatever you want. However, it will be suffixed with a dash and a character to recognize the platform and map it to the correct campaign parameters.

= Does this affect my page's URL, permalink, or canonical URL? =

No. This won't have any effect on your page's URL, permalink, permalink settings, or canonical URL. The vanity URL is simply another way of getting to your page but with the added Google campaign parameters automatically added. This plugin does not create any new pages, only redirects, so there won't be any duplicate content issues.

= Does this affect my page's SEO or “page rank”? =

It will not harm your page's SEO. Since the vanity URLs redirect using 301 permanent redirects, the “page rank” is transferred to your original page, preserving your SEO value.

= Does this affect my domain authority? =

Yes, but in a good way! Using a URL shortener service like bit.ly or TinyURL gets a miniscule amount of “domain authority” when pages are redirected through their domains since it is ultimately their URL that gets shared on other websites. You actually gain this bonus back when it is your domain that gets shared instead.

= How does it choose an image to share on Pinterest? =

If there is an image in the content of your article, it will find the first one and use that. Otherwise, it will fall back to using the featured image. If no image is associated with the article, then Pinterest will display all of the images found on the page, allowing the user to select which they want to use.

= What icons do you have? =

You can choose between lossless PNG images, [Font Awesome 5](https://fontawesome.com/?ref=socialized-wordpress-plugin) icons, or English text. The HTML is simple so you can easily change the appearance with CSS.

= Is this compatible with the Yoast SEO plugin? =

Yes! If you leave the “Campaign Term” blank in the metabox on your page, the `utm_term` value will default to your page's “focus keyphrase” in Yoast SEO's metabox.

= I'm a developer and I want to contribute! =

That's awesome, thank you! The source code is [available on GitHub here](https://github.com/Tessachu/socialized).

== Upgrade Notice ==

= 3.0.3 =
Added the `nofollow` value to the `rel` attribute for sharing links to help prevent unnecessary indexing.

== Changelog ==

= 3.0.3 =

**Release Date: October 27, 2022**

Fix: Added the `nofollow` value to the `rel` attribute for frontend sharing links to help reduce unnecessary crawling by search engines.

= 3.0.2 =
**Release Date: September 26, 2022**

* Fix: Fixed a fatal error that "DOMDocument" was not found.

= 3.0.1 =
**Release Date: September 21, 2022**

* Language: Updated translations for es-mx  and es-es (I do not speak Spanish natively, so any help is appreciated!)

= 3.0.0 =
**Release Date: September 21, 2022**

* Language: Added translations for es-mx (I do not speak Spanish natively, so any help is appreciated!)
* Feature: Added a "View All" tab in Settings where you can view all posts that have redirects enabled. Pagination is 200 per page.
* Fix: Overhauled plugin code to align better with WordPress plugin development best practices.

= 2.0.4 =
**Release Date: September 18, 2022**

* Text: Updated author name and links due to rebranding as AuRise Creative!
* Fix: JavaScript error about a null parent on pages that didn't use StickyBits (for really real this time)
* Fix: CSS edits for the copy-to-clipboard button that didn't use StickyBits
* Fix: Removed the copy-to-clipboard button from sites that don't use SSL because the browser feature is disabled for non-SSL sites

= 2.0.3 =
**Release Date: June 23, 2022**

* Fix: JavaScript error about a null parent on pages that didn't use StickyBits (for real this time)

= 2.0.2 =
**Release Date: June 2, 2022**

* Updated to be compatible with the release of WordPress version 6.0.

= 2.0.1 =
**Release Date: May 13, 2022**

* Fix: JavaScript error about a null parent on pages that didn't use StickyBits

= 2.0.0 =
**Release Date: March 5, 2022**

* Feature: Added a copy-to-clipboard button
* Feature: JavaScript no longer depends on jQuery library! Compatible with lite themes that are jQuery-free.
* Update: Updated the version of stickybits.js
* Update: Updated the version of FontAwesome to 6.0.0 (SVG implementation)
* Fix: Fixed stickybits issue where they wouldn't be sticky sometimes. Can override auto-fix.

= 1.6.0 =
**Release Date: January, 20, 2022**

* Feature: Updated external links with `rel` attribute to provide an additional layer of security.

= 1.5.4 =
**Release Date: September 26, 2021**

* Fix: Backend stylesheet for post edit screen bug has been fixed

= 1.5.3 =
**Release Date: September 17, 2021**

* Fix: Added explicit width and height attributes to image elements.
* Fix: Minified frontend CSS and JS resources.
* Deleted: Obsolete FontAwesome files.

= 1.5.2 =
**Release Date: September 9, 2021**

* Fix: Fixed the FontAwesome library so that it loads properly on the backend
* Fix: New email button now uses correct image icon.
* Fix: The post types value correctly pre-populates the settings page (bug introduced in 1.5.0).

= 1.5.1 =
**Release Date: September 8, 2021**

* Fix: Fixed the FontAwesome library so that it loads properly.

= 1.5.0 =
**Release Date: September 7, 2021**

* Feature: Added an email button to the frontend sharing options
* Updated the Font Awesome library to use an open kit so always the latest icons are used instead of the static 5.1 version.
* Updated the redirect query to use transients with a 4-hour expiration to improve performance
* Updated the backend admin page to use standardized navigation styles
* Updated the backend admin page to modernize the appearance, added logo in header
* Text: Fixed the URL for the Yoast SEO links on the About tab.
* Text: Added the list of post types that will be affected on the Generate Missing Vanity URLs tab.

= 1.4.3 =
**Release Date: September 2, 2021**

* Updated to be compatible with the release of WordPress version 5.8.

= 1.4.2 =
**Release Date: September 15, 2020**

* Fix: Resolved HTML encoding within the "About" tab on the settings page.
* Updated to be compatible with the release of WordPress version 5.5.

= 1.4.1 =
**Release Date: April 1, 2020**

* Fix: Resolved JS and CSS 404 errors for plugin admin assets.

= 1.4.0 =
**Release Date: March 28, 2020**

* Changed how the plugin checks for existing vanity slugs to avoid generating duplicates.
* Feature: Added an "Update Links" button in the backend which checks if the chosen slug is already in use and provides feedback to user without needing to save the entire post.
* Included a local FontAwesome version 5.2 stylesheet and webfonts for use within the metabox on the post edit admin pages.
* Updated to be compatible with the release of WordPress version 5.4!

= 1.3.7 =
**Release Date: October 31, 2019**

* Updated to be compatible with the upcoming release of WordPress version 5.3!
* Fix: Resolved bug where redirect would not work if there was a query in the URL. Now it redirects and includes the original query.
* Feature: Added a `data-platform` attribute to the `<a>` element for users that requested one for GTM link click tracking purposes.

= 1.2.4 =
**Release Date: August 8, 2019**

* Fix: Removed extra double quote from Pinterest's share link so outputted HTML is W3C compliant again.

= 1.2.3 =
**Release Date: July 1, 2019**

* Fix: Resolved bug where custom post types were not getting redirected from the 404 page.
* Text: Updated short description of plugin to be more descriptive.

= 1.2.2 =
**Release Date: June 18, 2019**

* Fix: Resolved small text error in the title attribute of the frontend links.

= 1.2.1 =
**Release Date: June 17, 2019**

* Fix: Changed image icon's alt text from “`platform` Icon” to “Share on `platform`”
* Fix: Set image icon's margin to 0 as default CSS to fix screen reader mouse selection.

= 1.2.0 =
**Release Date: June 4, 2019**

* Feature: Added two (2) button placements: stick left and right.

= 1.0.1 =
**Release Date: May 31, 2019**

* Fix: Sanitized input data to improve security.
* Fix: Escaped outputs to help secure data.
* Fix: Enqueued Font Awesome stylesheet for Settings admin page.
* Fix: Updated languages location.

= 1.0.0 =
**Release Date: May 30, 2019**

* Major: First release to the public!