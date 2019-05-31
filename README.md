# Socialized WordPress Plugin

Socialized adds social media sharing buttons to your posts, pages, and/or custom post types. When a user shares your page using these buttons, they can share a vanity URL specific to that button. When another user on social media then clicks on that shared link, the vanity URL redirects to your page with Google's UTM parameters for analytics tracking.

By adding campaign parameters to the destination URLs, you can collect information about the overall efficacy of those campaigns, and also understand where the campaigns are more effective. For example, your “Summer Sale” campaign might be generating lots of revenue, but if you're running the campaign in several different social apps, you want to know which of them is sending you the customers who generate the most revenue. Or if you're running different versions of the campaign via email, video ads, and in-app ads, you can compare the results to see where your marketing is most effective.

When a user clicks a referral link, the parameters you add are sent to Analytics, and the related data is available in the Campaigns reports.

[Learn more](https://support.google.com/analytics/answer/1033863) about Custom Campaigns in Google.

This plugin accomplishes two (2) things:

1. Automatically generates a vanity URL for each social media sharing button for each post that redirects to the post with the following UTM parameters:
* utm_source. Possible value(s): `facebook` | `twitter` | `linkedin` | `pinterest`
* utm_medium. Possible value(s): `social`
* utm_content. Possible value(s): `socialized-share-link`
* utm_campaign: Possible value(s): `socialized` | or define in Settings
* utm_term:  Possible value(s):  Defined by typing in the text box on the post or page | or the “Focus keyphrase” by Yoast SEO

2. Displays social media sharing links in the content of each post that uses these vanity URLs.

Your permalink struture will not be affected.

The randomly generated vanity URLs are automatically created when you save the post.
