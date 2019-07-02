=== Socialized ===
Contributors: tessawatkinsllc
Donate link: https://tessawatkins.com/socialized/
Tags: social media, link tracking, Google UTM, sharing, Custom Google Campaign
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 1.2.3
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add social media sharing buttons to your posts, pages, and custom post types that automatically track to a custom campaign with your Google Analytics!

== Description ==

Socialized adds social media sharing buttons to your posts, pages, and custom post types. When a user shares your page using these buttons, they can share a vanity URL specific to that button. When another user on social media then clicks on that shared link, the vanity URL redirects to your page with Google's UTM parameters for analytics tracking.

By adding campaign parameters to the destination URLs, you can collect information about the overall efficacy of those campaigns, and also understand where the campaigns are more effective. For example, your “Summer Sale” campaign might be generating lots of revenue, but if you're running the campaign in several different social apps, you want to know which of them is sending you the customers who generate the most revenue. Or if you're running different versions of the campaign via email, video ads, and in-app ads, you can compare the results to see where your marketing is most effective.

When a user clicks a referral link, the parameters you add are sent to Analytics, and the related data is available in the Campaigns reports.

[Learn more](https://support.google.com/analytics/answer/1033863) about Custom Campaigns in Google.

This plugin accomplishes two (2) things:

1. Automatically generates a vanity URL for each social media sharing button for each post that redirects to the post with the following UTM parameters:
    * `utm_source`. Possible value(s): `facebook` | `twitter` | `linkedin` | `pinterest`
    * `utm_medium`. Possible value(s): `social`
    * `utm_content`. Possible value(s): `socialized-share-link`
    * `utm_campaign`: Possible value(s): `socialized` | or define in Settings
    * `utm_term`:  Possible value(s):  Defined by typing in the text box on the post or page | or the “Focus keyphrase” by Yoast SEO
1. Displays social media sharing links in the content of each post that uses these vanity URLs.

Your permalink struture will not be affected. The randomly generated vanity URLs are automatically created when you save the post.

== Installation ==

= Automatic Installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser.

1. Log in to your WordPress dashboard
1. Navigate to the Plugins menu and click the “Add New” button.
1. In the search field, type “Socialized” and click the “Search Plugins” button.
1. Once you’ve found my plugin, click “Install Now” and wait for the installation process to complete.
1. Once the installation process is completed, click “Activate”.

= Manual Installation =
The manual installation method involves downloading my plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= After activation =

1. Navigate to “Tools > Socialized” to change any settings to fit your needs.
1. When creating new posts or editing existing ones, use the meta box to configure settings specific to that post.
1. Preview your post to see the buttons on the page.

== Frequently Asked Questions ==

= What platforms are supported? =

Facebook, Twitter, LinkedIn, and Pinterest are supported.

= Can I change the name of the campaign? =

Yes! You can change the name of the campaign, or the value of `utm_campaign`, from the plugin settings page.

= Can I change the vanity URL? =

Yes! When a new post is created, a random string of will be generated for that post, but you can edit it to whatever you want. However, it will be suffixed with a dash and a character to recognize the platform and map it to the correct UTM parameters.

= How does this affect my page's SEO? =

It won't have any effect. Since the vanity URLs redirect using 301 permanent redirects, the "page rank" is transferred, preserving your SEO value.

= How does it choose an image to share on Pinterest? =

If there is an image in the content of your article, it will find the first one and use that. Otherwise, it will fall back to using the featured image. If no image is associated with the article, then Pinterest will display all of the images found on the page, allowing the user to select which they want to use.

= What icons do you have? =

Right now, you can choose between using PNG images, [Font Awesome 5](https://fontawesome.com/) icons, or English text. The HTML is simple for developers to easily change the appearance with CSS.

= Is this compatible with the Yoast SEO plugin? =

Yes! If you leave the “Campaign Term” blank in the metabox on your page, the `utm_term` value will default to your page's “focus keyphrase” in Yoast SEO's metabox.

= How does the redirect work? =

When a user lands on a 404 page, the plugin reads the slug in the URL and matches it to one that was created. If found, it then performs a 301 redirect to that page with the appropriate UTM parameters appended in the URL.

== Screenshots ==

1. Plugin settings screen
2. The Socialized plugin general meta box. You'll see this on edit post pages for posts, pages, and custom post types that you've set to allow in the settings.
3. The default appearance of the social media sharing buttons using images. There is no hover state.
4. The default appearance of the social media sharing buttons using Font Awesome. When active, focused, or hovering over a button, the icon animates to be rounded. E.g. the LinkedIn icon.
5. The default appearance of the social media sharing buttons as text. When active, focused, or hovering over a button, the icon animates the background and text colors to swap. E.g. the LinkedIn icon.

== Upgrade Notice ==

= 1.2.3 =
Fixes a bug where custom post types were not getting redirected from the 404 page.

= 1.2.2 =
Fixes a minor text error in the link title attribute.

= 1.2.1 =
Fixes image icon labels for screen readers.

= 1.2.0 =
Added two (2) new button placements: stick left and right!

= 1.0.1 =
Fixes based on first plugin review.

= 1.0.0 =
Initial submission!

== Changelog ==

= 1.2.3 =
* Fix: Resolved bug where custom post types were not getting redirected from the 404 page.
* Text: Updated short description of plugin to be more descriptive.

= 1.2.2 =
* Fix: Resolved small text error in the title attribute of the frontend links.

= 1.2.1 =
* Fix: Changed image icon's alt text from “`platform` Icon” to “Share on `platform`”
* Fix: Set image icon's margin to 0 as default CSS to fix screen reader mouse selection.

= 1.2.0 =
* Feature: Added two (2) button placements: stick left and right.

= 1.0.1 =
* Fix: Sanitized input data to improve security.
* Fix: Escaped outputs to help secure data.
* Fix: Enqueued Font Awesome stylesheet for Settings admin page.
* Fix: Updated languages location.

= 1.0.0 =
* Major: Initial submission!