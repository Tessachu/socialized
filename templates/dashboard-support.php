<div class="au-plugin-support">
    <h2><?php _e('Plugin Support', 'socialized'); ?></h2>
    <?php $link_prefix = sprintf(
        'https://aurisecreative.com/click/?utm_source=%s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%s&utm_term=',
        str_replace(array('https://', 'http://'), '', home_url()), //UTM Source
        $args['slug']
    ); ?>
    <p>
        <?php _e('Enjoying my plugin? Please leave a review!', 'socialized'); ?><br />
        <a class="button button-secondary" href="<?php echo (esc_url($link_prefix . 'write-a-review&redirect=' . urlencode(sprintf('https://wordpress.org/support/plugin/%s/reviews/#new-post', $args['slug'])))); ?>" target="_blank" rel="noopener noreferrer">
            <?php _e('Write a Review', 'socialized'); ?>
        </a>
    </p>
    <p>
        <?php _e("If you're experiencing issues with this plugin or have a suggestion for a feature or fix, please check the support threads or submit a ticket to give me the opportunity to make it better. I want to help!", 'socialized'); ?><br />
        <a class="button button-secondary" href="<?php echo (esc_url($link_prefix . 'support-forums&redirect=' . urlencode(sprintf('https://wordpress.org/support/plugin/%s/', $args['slug'])))); ?>" target="_blank" rel="noopener noreferrer">
            <?php _e('Support Forums', 'socialized'); ?>
        </a>
    </p>
    <p>
        <?php _e('This is a <em>free</em> plugin that I poured a bit of my heart and soul into with the sole purpose of being helpful to you and the users of your WordPress website. Please consider supporting my queer and autistic-led small business by donating! Thank you!', 'socialized'); ?><br />
    </p>
    <div class="donate-button">
        <a title="<?php _e('Donate', 'socialized'); ?>" href="<?php echo (esc_url($link_prefix . 'donate&redirect=' . urlencode('https://just1voice.com/donate'))); ?>" target="_blank" rel="noopener noreferrer">
            <span>
                <img src="<?php echo (esc_url($args['url'] . 'assets/images/kofi-cup.png')); ?>" alt="<?php _e('Coffee cup', 'socialized'); ?>" />
                <?php _e('Buy me a Coffee', 'socialized'); ?>
            </span>
        </a>
    </div>
</div>