<?php
// check if the user have submitted the settings
// WordPress will add the "settings-updated" $_GET parameter to the url
if (isset($_GET['settings-updated'])) {
    // add settings saved message with the class of "updated"
    add_settings_error(
        $args['plugin_settings']['prefix'] . 'messages',
        $args['plugin_settings']['prefix'] . 'message',
        __('Settings Saved!', 'socialized'),
        'updated'
    );
}
// show error/update messages
settings_errors($args['plugin_settings']['prefix'] . 'messages'); ?>
<div class="wrap au-plugin">
    <h1><img src="<?php echo (esc_url($args['plugin_settings']['url'])); ?>assets/images/admin-logo.png" alt="<?php esc_html_e($args['plugin_settings']['name'] . ' ' . __('by AuRise Creative', 'accessible-reading')); ?>" width="293" height="60" /></h1>
    <div class="au-plugin-admin-ui">
        <div class="loading-spinner"><img src="<?php echo (esc_url($args['plugin_settings']['url'])); ?>assets/images/progress.gif" alt="" width="32" height="32" /></div>
        <div class="admin-ui hide">
            <?php $plugin = socialized();  ?>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab" id="open-settings" href="#settings"><?php esc_html_e('Settings', 'socialized') ?></a>
                <a class="nav-tab" id="open-view" href="#view"><?php esc_html_e('View All', 'socialized') ?></a>
                <a class="nav-tab" id="open-about" href="#about"><?php esc_html_e('About', 'socialized') ?></a>
                <a class="nav-tab" id="open-regenerate" href="#regenerate"><?php esc_html_e('Generate Missing Vanity URLs', 'socialized') ?></a>
            </h2>
            <div id="tab-content" class="container">
                <section id="settings" class="tab">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields($args['plugin_settings']['group']);
                        do_settings_sections($args['plugin_settings']['group']);
                        $location = esc_attr(get_option($args['plugin_settings']['prefix'] . 'buttons_location', $args['plugin_settings']['default']['buttons_location']));
                        $icon_type = esc_attr(get_option($args['plugin_settings']['prefix'] . 'icon_type', $args['plugin_settings']['default']['icon_type']));
                        ?>
                        <fieldset>
                            <h3><?php esc_html_e('Display', 'socialized'); ?></h3>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>icon_type">
                                        <strong><?php esc_html_e('Icons', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>icon_type" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>icon_type" class="controller" required>
                                        <?php foreach ($args['plugin_settings']['button_icon'] as $button_icon_value => $button_icon_label) {
                                            printf(
                                                '<option value="%s"%s>%s</option>',
                                                esc_attr($button_icon_value),
                                                esc_attr($icon_type == $button_icon_value ? ' selected' : ''),
                                                esc_html($button_icon_label)
                                            );
                                        } ?>
                                    </select>
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>icon_type">
                                        <?php esc_html_e('Choose a button style for displaying the links', 'socialized'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="au-row input-field checkbox has-checkbox-switch" data-controller="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>icon_type" data-values="fontawesome">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>exclude_fontawesome_check">
                                        <strong><?php esc_html_e('FontAwesome', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <?php $plugin->display_checkbox_switch(array(
                                        'name' => $args['plugin_settings']['prefix'] . 'exclude_fontawesome',
                                        'value' => get_option($args['plugin_settings']['prefix'] . 'exclude_fontawesome', $args['plugin_settings']['default']['exclude_fontawesome']),
                                        'yes' => __('ON', 'socialized'),
                                        'no' => __('OFF', 'socialized'),
                                        'label' => __('Toggle this off if your theme or another plugin already implements FontAwesome version 6 or later.', 'socialized'),
                                        'reverse' => true
                                    )); ?>
                                </div>
                            </div>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>buttons_location">
                                        <strong><?php esc_html_e('Location', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>buttons_location" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>buttons_location" required>
                                        <?php foreach ($args['plugin_settings']['button_locations'] as $button_location_value => $button_location_label) {
                                            printf(
                                                '<option value="%s"%s>%s</option>',
                                                esc_attr($button_location_value),
                                                esc_attr($location == $button_location_value ? ' selected' : ''),
                                                esc_html($button_location_label)
                                            );
                                        } ?>
                                    </select>
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>buttons_location">
                                        <?php esc_html_e('Choose where to display the sharing buttons', 'socialized'); ?><br />
                                        <span class="note">
                                            <?php esc_html_e(' (Use the shortcode ', 'socialized'); ?>
                                            <span class="inline-code-snippet">[<?php esc_html_e($args['plugin_settings']['slug']); ?>]</span>
                                            <?php esc_html_e(' to display these buttons within the content. If placed, it will override the automatic placement so they\'re only displayed once.', 'socialized'); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <h3><?php esc_html_e('Link Tracking', 'socialized'); ?></h3>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>utm_campaign">
                                        <strong><?php esc_html_e('Campaign', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <input id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>utm_campaign" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>utm_campaign" value="<?php esc_attr_e(get_option($args['plugin_settings']['prefix'] . 'utm_campaign', $args['plugin_settings']['utm']['campaign'])); ?>" required />
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>utm_campaign">
                                        <?php esc_html_e('The value of the "utm_campaign" parameter for all links shared with this plugin.', 'socialized'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter">
                                        <strong><?php esc_html_e('Twitter Handle', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <input id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter" value="<?php esc_attr_e(get_option($args['plugin_settings']['prefix'] . 'twitter', $args['plugin_settings']['default']['twitter'])); ?>" placeholder="TessaTechArtist" />
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter">
                                        <?php esc_html_e('Your default Twitter handle', 'socialized'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter_related">
                                        <strong><?php esc_html_e('Related Twitter Handles', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <input id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter_related" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter_related" value="<?php esc_attr_e(get_option($args['plugin_settings']['prefix'] . 'twitter_related', $args['plugin_settings']['default']['twitter_related'])); ?>" placeholder="TessaTechArtist,SirPatStew,GeorgeTakei" />
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>twitter_related">
                                        <?php esc_html_e('Comma separated list of up to three (3) related Twitter handles to recommend to the user after they tweet your content.', 'socialized'); ?>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <h3><?php esc_html_e('Advanced Settings', 'socialized'); ?></h3>
                            <div class="au-row input-field checkbox has-checkbox-switch">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>redirecting_check">
                                        <strong><?php esc_html_e('Autofix Sticky', 'socialized'); ?></strong><br /><span class="note"><?php esc_html_e(' (recommended on)'); ?></span>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <?php $plugin->display_checkbox_switch(array(
                                        'name' => $args['plugin_settings']['prefix'] . 'autofix_sticky',
                                        'value' => get_option($args['plugin_settings']['prefix'] . 'autofix_sticky', $args['plugin_settings']['default']['autofix_sticky']),
                                        'yes' => __('ON', 'socialized'),
                                        'no' => __('OFF', 'socialized'),
                                        'label' => __('When sticking the buttons to the left or right, certain CSS attributes need to be set for it to work. Enabling this will automatically fix those issues.', 'socialized'),
                                        'reverse' => false
                                    )); ?>
                                </div>
                            </div>
                            <div class="au-row input-field checkbox has-checkbox-switch">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>redirecting_check">
                                        <strong><?php esc_html_e('Redirects Enabled', 'socialized'); ?></strong><br /><span class="note"><?php esc_html_e(' (recommended on)'); ?></span>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <?php $plugin->display_checkbox_switch(array(
                                        'name' => $args['plugin_settings']['prefix'] . 'redirecting',
                                        'value' => get_option($args['plugin_settings']['prefix'] . 'redirecting', $args['plugin_settings']['default']['redirecting']),
                                        'yes' => __('ON', 'socialized'),
                                        'no' => __('OFF', 'socialized'),
                                        'label' => __('If disabled, the social media sharing links will still appear on the posts/pages, but will share your permalink with the UTM parameters added to it instead. Any vanity URLs created by this plugin that were shared on social media or other mediums will also stop redirecting, resulting in a 404 page to be displayed', 'socialized'),
                                        'reverse' => false
                                    )); ?>
                                </div>
                            </div>
                            <div class="au-row input-field">
                                <div class="col-xs-12 col-md-3">
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>post_types">
                                        <strong><?php esc_html_e('Post Types', 'socialized'); ?></strong>
                                    </label>
                                </div>
                                <div class="col-xs-12 col-sm-9">
                                    <?php $allowed_post_types = implode(',', $args['post_types']); ?>
                                    <input id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>post_types" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>post_types" value="<?php esc_attr_e($allowed_post_types); ?>" placeholder="post,page,product" required />
                                    <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>post_types">
                                        <?php esc_html_e('Comma separated list of the post types to generate URLs for', 'socialized'); ?>
                                    </label>
                                </div>
                            </div>
                            <?php submit_button(__('Save All Settings', 'socialized')); ?>
                        </fieldset>
                    </form>
                </section>
                <section id="about" class="tab">
                    <p><?php _e('By adding campaign parameters to the destination URLs you use in your ad campaigns, you can collect information about the overall efficacy of those campaigns, and also understand where the campaigns are more effective. For example, your <em>Summer Sale</em> campaign might be generating lots of revenue, but if you\'re running the campaign in several different social apps, you want to know which of them is sending you the customers who generate the most revenue. Or if you\'re running different versions of the campaign via email, video ads, and in-app ads, you can compare the results to see where your marketing is most effective.', 'socialized'); ?></p>
                    <p><?php esc_html_e('When a user clicks a referral link, the parameters you add are sent to Analytics, and the related data is available in the Campaigns reports.', 'socialized'); ?></p>
                    <p><a href="https://support.google.com/analytics/answer/1033863" target="_blank"><?php esc_html_e('Learn more', 'socialized'); ?></a> <?php esc_html_e('about Custom Campaigns in Google.', 'socialized'); ?></p>
                    <p><?php esc_html_e('This plugin accomplishes two (2) things:', 'socialized'); ?></p>
                    <ol>
                        <li>
                            <?php esc_html_e('Automatically generates a vanity URL for each social media sharing button for each post that redirects to the post with the following UTM parameters:', 'socialized'); ?>
                            <ul>
                                <li><strong>utm_source</strong>. <?php esc_html_e('Possible value(s):', 'socialized'); ?> facebook | twitter | linkedin | pinterest | email</li>
                                <li><strong>utm_medium</strong>. <?php esc_html_e('Possible value(s):', 'socialized'); ?> social</li>
                                <li><strong>utm_content</strong>. <?php esc_html_e('Possible value(s):', 'socialized'); ?> <?php esc_html_e($args['plugin_settings']['slug']); ?>-share-link</li>
                                <li><strong>utm_campaign</strong>: <?php esc_html_e('Possible value(s):', 'socialized'); ?> <?php esc_html_e($args['plugin_settings']['slug']); ?> | <?php _e('or define in <em>Settings</em>', 'socialized'); ?></li>
                                <li>
                                    <strong>utm_term</strong>:&nbsp;
                                    <?php esc_html_e('Possible value(s):', 'socialized'); ?>&nbsp;
                                    <?php esc_html_e('Defined by typing in the text box on the post or page', 'socialized'); ?>
                                    <?php if ($args['yoast-seo']) {
                                        printf(
                                            __(' | or the “<a href="%1$s" target="%3$s">Focus keyphrase</a>” by <a href="%2$s" target="%3$s" rel="noopener noreferrer">Yoast SEO</a>', 'socialized'),
                                            'https://yoast.com/focus-keyword/#utm_source=yoast-seo&utm_medium=referral&utm_term=focus-keyphrase-qm&utm_content=socialized-plugin&utm_campaign=wordpress-general&platform=wordpress',
                                            'https://wordpress.org/plugins/wordpress-seo/',
                                            '_blank'
                                        );
                                    } ?>
                                </li>
                            </ul>
                        </li>
                        <li><?php esc_html_e('Displays social media sharing links in the content of each post that uses these vanity URLs.', 'socialized'); ?></li>
                    </ol>
                    <p><?php esc_html_e('Your permalink struture will not be affected.', 'socialized'); ?></p>
                </section>
                <section id="regenerate" class="tab">
                    <p><?php printf(__('Click the button below to generate vanity URLs for the following post type(s) that do not already have them: <code>%s</code>. It was already run once when this plugin was activated, and each newly created one generates their own when it is first saved, even as a draft. If you created a custom post type and wish to generate links for all of those as well, be sure to save the custom post type\'s slug in <em>Settings</em> before clicking this button.', 'socialized'), $allowed_post_types); ?></p>
                    <p class="buttons">
                        <button id="generate-urls" class="button button-primary"><?php esc_html_e('Generate Missing Vanity URLs', 'socialized'); ?></button>
                        <span class="progress-spinner hide"><img src="<?php echo (esc_url($args['plugin_settings']['url'])); ?>assets/images/progress.gif" alt="" width="32" height="32" /></span>
                    </p>
                    <p id="generate-status" class="status-text notice notice-info hide"></p>
                </section>
                <section id="view" class="tab">
                    <?php echo ($args['view_table']); ?>
                </section>
            </div>
        </div>
    </div>
    <?php load_template($args['plugin_settings']['path'] . 'templates/dashboard-support.php', true, $args['plugin_settings']); ?>
</div>