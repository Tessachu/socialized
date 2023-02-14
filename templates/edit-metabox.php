<div class="au-metabox-<?php esc_attr_e($args['plugin_settings']['slug']); ?>">
    <?php
    ?>
    <fieldset>
        <p class="input-field">
            <label for="<?php echo ($args['plugin_settings']['prefix']); ?>term">
                <?php esc_html_e('Campaign Term', 'socialized'); ?>
                <?php if ($args['yoast-seo']) {
                    printf('<span class="note">%s</span>', esc_html__('If left blank, this will use Yoast SEO\'s "Focus keyphrase"', 'socialized'));
                } ?>
            </label>
            <input name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>term" id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>term" value="<?php esc_attr_e($args['term']); ?>" placeholder="<?php esc_attr_e($args['default_term']); ?>" />
        </p>
        <p class="input-field">
            <label for="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>slug"><?php esc_html_e('Vanity Slug', 'socialized'); ?></label>
            <input name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>slug" id="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>slug" value="<?php esc_attr_e($args['slug']); ?>" placeholder="<?php esc_attr_e($args['slug']); ?>" />
        </p>
        <button type="submit" name="<?php esc_attr_e($args['plugin_settings']['prefix']); ?>submit" class="button button-primary"><?php esc_html_e('Update Links', 'socialized'); ?></button>
        <span class="loading-spinner hide"><i class="fa-solid fa-spinner fa-spin"></i></span>
        <p id="update-links-output" class="notice notice-info is-dismissible hide"></p>
    </fieldset>
    <?php if (count($args['platforms'])) : ?>
        <ol>
            <?php foreach ($args['platforms'] as $key => $platform) : ?>
                <li id="<?php esc_attr_e($args['plugin_settings']['prefix'] . $key); ?>">
                    <p><strong><?php esc_html_e($platform['title']); ?></strong></p>
                    <ul>
                        <li class="vanity-url"><strong><?php esc_html_e('Vanity URL:', 'socialized'); ?></strong>&nbsp;<?php printf('<a href="%1$s" target="_blank" class="value">%2$s</a>', esc_url(home_url($platform['slug'])), esc_html($platform['slug'])); ?></li>
                        <li class="hits"><strong><?php esc_html_e('Hits:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['hits'] ? $platform['hits'] : 0); ?></span></li>
                        <li class="campaign-source"><strong><?php esc_html_e('Campaign Source:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['query']['utm_source']); ?></span></li>
                        <li class="campaign-medium"><strong><?php esc_html_e('Campaign Medium:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['query']['utm_medium']); ?></span></li>
                        <li class="campaign-name"><strong><?php esc_html_e('Campaign Name:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['query']['utm_campaign']); ?></span></li>
                        <li class="campaign-content"><strong><?php esc_html_e('Campaign Content:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['query']['utm_content']); ?></span></li>
                        <?php if (!empty($platform['query']['utm_term'])) : ?>
                            <li class="campaign-term"><strong><?php esc_html_e('Campaign Term:', 'socialized'); ?></strong>&nbsp;<span class="value"><?php esc_html_e($platform['query']['utm_term']); ?></span></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else : ?>
        <p><?php esc_html_e('Edit the vanity slug above and save or', 'socialized'); ?> <a href="<?php echo (esc_url($args['plugin_settings']['admin_url'])); ?>"><?php esc_html_e('automatically generate the missing ones', 'socialized'); ?></a>.</p>
    <?php endif;
    if (get_option($args['plugin_settings']['prefix'] . 'redirecting') != 'true') : ?>
        <p><strong><em><?php esc_html_e('Redirects are disabled!', 'socialized'); ?></em></strong> <a href="<?php echo (esc_url($args['plugin_settings']['admin_url'])); ?>"><?php esc_html_e('Enable vanity URLs', 'socialized'); ?></a></p>
    <?php endif; ?>
    <p><a href="<?php echo (esc_url($args['plugin_settings']['admin_url'])); ?>"><?php esc_html_e('Edit Global Settings', 'socialized'); ?></a></p>
</div>