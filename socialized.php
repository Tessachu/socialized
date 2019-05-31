<?php 
/**
 * Plugin Name: Socialized
 * Plugin URI: https://tessawatkins.com/socialized/
 * Description: Add social media sharing buttons to your articles that use shortened links which redirect with UTM parameters for link tracking purposes!
 * Version: 1.0.0
 * Author: Tessa Watkins LLC
 * Author URI: https://tessawatkins.com/
 * 
 * Text Domain: socialized
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( ! defined( 'SOCIALIZED_FILE' ) ) {
	define( 'SOCIALIZED_FILE', __FILE__ );
}

if( ! class_exists( 'Socialized' ) ) :

class Socialized {
    /**
	 * Plugin version.
	 *
	 * @var string
	 */
    public $version = '1.0.0';

    /**
	 * The single instance of the class.
	 *
	 * @var Socialized
	 * @since 1.0.0
	 */
	protected static $_instance = null;

    /** @var array The plugin settings array */
    public $settings = array();

    /**
	 * Main Instance
	 *
	 * Ensures only one instance of is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Socialized Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Constructor
     * 
	 * @since 1.0.0
	 */
	function __construct() {
        $version = $this->version;
        $basename = plugin_basename( SOCIALIZED_FILE );//"socialized/socialized.php"
        $path = plugin_dir_path( SOCIALIZED_FILE );
        $url = plugin_dir_url( SOCIALIZED_FILE );
        $slug = dirname( $basename );//"socialized"
        
        $this->settings = array(
            
            // Basics
            'name' => __( 'Socialized', $slug ),
            'version' => $version,
            'capability' => 'manage_options',
                        
            // URLs
            'file' => SOCIALIZED_FILE,
            'basename' => $basename,
            'path' => $path,
            'url' => $url,
            'slug' => $slug,
            'prefix' => $slug . '_',
            'group' => $slug . '_settings_group',
            'admin_url' => admin_url( sprintf( 'tools.php?page=%s', $slug ) ),
            
            // Social Media Platforms
            'platforms' => array(
                'facebook' => array(
                    'title' => __( 'Facebook', $slug ),//Platform's Title
                    'suffix' => '-f',//Suffix for identifying platform's vanity URL
                    'icon' => $url . 'assets/images/icon_facebook_32.png',//Absolute URL to icon image
                    'fontawesome' => 'facebook-f',//FontAwesome class
                    'link_format' => 'https://www.facebook.com/sharer.php?u=%1$s',//Platform's sharing link format
                    'width' => 600,//Pop-up window's width
                    'height' => 750//Pop-up window's height
                ),
                'twitter' => array(
                    'title' => __( 'Twitter', $slug ),//Platform's Title
                    'suffix' => '-t',//Suffix for identifying platform's vanity URL
                    'icon' => $url . 'assets/images/icon_twitter_32.png',//Absolute URL to icon image
                    'fontawesome' => 'twitter',
                    'link_format' => 'https://twitter.com/intent/tweet?url=%1$s&text=%2$s&via=%3$s&related=%4$s&original_referer=%5$s',//Platform's sharing link format
                    'width' => 600,//Pop-up window's width
                    'height' => 270//Pop-up window's height
                ),
                'linkedin' => array(
                    'title' => __( 'LinkedIn', $slug ),//Platform's Title
                    'suffix' => '-l',//Suffix for identifying platform's vanity URL
                    'icon' => $url . 'assets/images/icon_linkedin_32.png',//Absolute URL to icon image
                    'fontawesome' => 'linkedin-in',//FontAwesome class
                    'link_format' => 'https://www.linkedin.com/sharing/share-offsite/?url=%1$s',//Platform's sharing link format
                    'width' => 600,//Pop-up window's width
                    'height' => 530//Pop-up window's height
                ),
                'pinterest' => array(
                    'title' => __( 'Pinterest', $slug ),//Platform's Title
                    'suffix' => '-p',//Suffix for identifying platform's vanity URL
                    'icon' => $url . 'assets/images/icon_pinterest_32.png',//Absolute URL to icon image
                    'fontawesome' => 'pinterest-p',//FontAwesome class
                    'link_format' => 'https://pinterest.com/pin/create/button/?url=%1$s&media=%2$s&description=%3$s"',//Platform's sharing link format
                    'width' => 800,//Pop-up window's width
                    'height' => 680//Pop-up window's height
                )
            ),

            //Default UTM Values
            'utm' => array(
                'medium' => 'social',
                'campaign' => $slug,
                'content' => $slug . '-share-link'
            ),

            //Default Settings Values
            'default' => array(
                'buttons_location' => 'top',
                'post_types' => 'post',
                'redirecting' => 'true',
                'twitter' => '',
                'twitter_related' => '',
                'icon_type' => 'png',
                'exclude_fontawesome' => 'false'
            )
        );

        //Plugin Setup
        add_action( 'admin_init', array( $this, 'register_settings' ) );//Register plugin option settings
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );//Add admin page link in WordPress dashboard
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );//Enqueue styles/scripts for admin page
        add_filter( 'plugin_action_links_'. $basename, array( $this, 'plugin_links' ) );//Add link to admin page from plugins page
        register_activation_hook( SOCIALIZED_FILE, array( $this, 'activate' ) );//Add activation hook
        load_plugin_textdomain( $slug, false, $slug . '/languages/' );//Translations

        //Specifics
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );//Add metabox to posts dashboard pages
        add_action( 'save_post', array( $this, 'save_post' ) );//Save metabox settings on post save
        add_action( 'template_redirect', array( $this, 'redirect' ), 20 );//Perform redirect
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );//Enqueue styles for frontend
        add_action( 'wp_ajax_regenerate_urls', array( $this, 'regenerate_urls' ) );//Add AJAX call for logged in users to regenerate missing vanity URLs from admin page
        add_filter( 'the_content', array( $this, 'display_buttons' ), 20 );//Display buttons on frontend
        add_shortcode( $slug, array( $this, 'shortcode' ) );//Create shortcode for use in content 
    }

    //**** Plugin Settings ****//

    /**
	 * Register Plugin Settings
     * 
	 * @since 1.0.0
	 */
    function register_settings() {
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'all_slugs' );//An array of all the slugs generated
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'buttons_location' );//Current button location
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'utm_campaign' );//value for utm_campaign
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'post_types' );//Post types to display buttons for
        add_settings_field(
            $this->settings['prefix'] . 'redirecting',
            __( 'Redirections Enabled', $this->settings['slug'] ),
            array( $this, 'display_redirecting_checkbox' ),
            $this->settings['slug'],
            $this->settings['group']
        );
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'redirecting' );//If redirectig/using vanity URLs with UTM parameters or just the permalinks
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'twitter' );//Value for "via @user" for Twitter sharing
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'twitter_related' );//Value for up to three Twitter users to promote after sharing
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'icon_type' );//Icon type, the style for which the buttons display
        add_settings_field(
            $this->settings['prefix'] . 'exclude_fontawesome',
            __( 'Exclude FontAwesome', $this->settings['slug'] ),
            array( $this, 'display_exclude_fontawesome_checkbox' ),
            $this->settings['slug'],
            $this->settings['group']
        );
        register_setting( $this->settings['group'], $this->settings['prefix'] . 'exclude_fontawesome' );//If using Font Awesome enqueued here or to exclude it
    }
    
    /**
	 * Checkbox display for redirecting setting.
     * 
	 * @since 1.0.0
	 */
    function display_redirecting_checkbox() { ?>
        <!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
        <input type="checkbox" id="<?php esc_attr_e( $this->settings['prefix'] ); ?>redirecting" name="<?php esc_attr_e( $this->settings['prefix'] ); ?>redirecting" <?php checked( 1, esc_attr( get_option( $this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting'] ) ), true ); ?> /> 
    <?php }

    /**
	 * Checkbox display for exclude Font Awesome setting.
     * 
	 * @since 1.0.0
	 */
    function display_exclude_fontawesome_checkbox() { ?>
        <!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
        <input type="checkbox" id="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome" name="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome" <?php checked( 1, esc_attr( get_option( $this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome'] ) ), true ); ?> /> 
    <?php }

    //**** Plugin Management Page ****//

    /**
	 * Add Admin Page.
     * 
     * Adds the admin page to the WordPress dashboard under "Tools".
     * 
	 * @since 1.0.0
	 */
    function admin_menu() {
        add_management_page( $this->settings['name'], $this->settings['name'], $this->settings['capability'], $this->settings['slug'], array( &$this, 'admin_page' ) );
    }

    /**
	 * Plugin Links.
     * 
     * Links to display on the plugins page.
     * 
	 * @since 1.0.0
     * @return array A list of links
	 */
    function plugin_links( $links ) {
        $settings_link = sprintf( '<a href="%s">%s</a>', admin_url( sprintf( 'tools.php?page=%s', $this->settings['slug'] ) ), __( 'Settings', $this->settings['slug'] ) );
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
	 * Admin Scripts and Styles.
     * 
     * Enqueue scripts and styles to be used on the admin pages.
     * 
	 * @since 1.0.0
	 */
    function admin_enqueue_scripts( $hook ) {
        if( $hook == 'tools_page_' . $this->settings['slug'] ) {
            // Load only on our plugin page (a subpage of "Tools")
            wp_enqueue_style( $this->settings['slug'] . '-fontawesome', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', array(), '5.2.0' );
            wp_enqueue_style( $this->settings['slug'], $this->settings['url'] . 'assets/styles/admin-dashboard.css', array( $this->settings['slug'] . '-fontawesome' ), $this->settings['version'] );
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( $this->settings['slug'], $this->settings['url'] . 'assets/scripts/admin-dashboard.js', array( 'jquery' ), $this->settings['version'], true );
            wp_localize_script( $this->settings['slug'], 'tw_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        } elseif ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            // Load only on the post edit page
            wp_enqueue_style( $this->settings['slug'], $this->settings['url'] . 'assets/styles/admin-metabox.css', array(), $this->settings['version'] );
        }        
    }

    /**
	 * Display Admin Page.
     * 
     * HTML markup for the WordPress dashboard admin page for managing this plugin's settings.
     * 
	 * @since 1.0.0
	 */
    function admin_page() { ?>
        <div class="tw-plugin">
            <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous" /> -->
            <h1><?php esc_html_e( $this->settings['name'] ); ?></h1>
            <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="admin-ui hide">
                <ul id="tabs">
                    <li><button id="open-settings" data-id="settings" class="tab-btn"><?php esc_html_e( 'Settings', $this->settings['slug'] ) ?></button></li>
                    <li><button id="open-about" data-id="about" class="tab-btn"><?php esc_html_e( 'About', $this->settings['slug'] ) ?></button></li>
                    <li><button id="open-regenerate" data-id="regenerate" class="tab-btn"><?php esc_html_e( 'Generate Missing Vanity URLs', $this->settings['slug'] ) ?></button></li>
                </ul>
                <div id="tab-content">
                    <section id="about" class="tab">
                        <h2><?php esc_html_e( 'About', $this->settings['slug'] ); ?></h2>
                        <p><?php esc_html_e( 'By adding campaign parameters to the destination URLs you use in your ad campaigns, you can collect information about the overall efficacy of those campaigns, and also understand where the campaigns are more effective. For example, your <em>Summer Sale</em> campaign might be generating lots of revenue, but if you\'re running the campaign in several different social apps, you want to know which of them is sending you the customers who generate the most revenue. Or if you\'re running different versions of the campaign via email, video ads, and in-app ads, you can compare the results to see where your marketing is most effective.', $this->settings['slug'] ); ?></p>
                        <p><?php esc_html_e( 'When a user clicks a referral link, the parameters you add are sent to Analytics, and the related data is available in the Campaigns reports.', $this->settings['slug'] ); ?></p>
                        <p><a href="https://support.google.com/analytics/answer/1033863" target="_blank"><?php esc_html_e( 'Learn more', $this->settings['slug'] ); ?></a> <?php esc_html_e( 'about Custom Campaigns in Google.', $this->settings['slug'] ); ?></p>
                        <p><?php esc_html_e( 'This plugin accomplishes two (2) things:' ); ?></p>
                        <ol>
                            <li>
                                <?php esc_html_e( 'Automatically generates a vanity URL for each social media sharing button for each post that redirects to the post with the following UTM parameters:', $this->settings['slug'] ); ?>
                                <ul>
                                    <li><strong>utm_source</strong>. <?php esc_html_e( 'Possible value(s):', $this->settings['slug'] ); ?> facebook | twitter | linkedin | pinterest</li>
                                    <li><strong>utm_medium</strong>. <?php esc_html_e( 'Possible value(s):', $this->settings['slug'] ); ?> social</li>
                                    <li><strong>utm_content</strong>. <?php esc_html_e( 'Possible value(s):', $this->settings['slug'] ); ?> <?php esc_html_e( $this->settings['slug'] ); ?>-share-link</li>
                                    <li><strong>utm_campaign</strong>: <?php esc_html_e( 'Possible value(s):', $this->settings['slug'] ); ?> <?php esc_html_e( $this->settings['slug'] ); ?> | <?php esc_html_e( 'or define in <em>Settings</em>', $this->settings['slug'] ); ?></li>
                                    <li>
                                        <strong>utm_term</strong>:&nbsp;
                                        <?php esc_html_e( 'Possible value(s):', $this->settings['slug'] ); ?>&nbsp;
                                        <?php esc_html_e( 'Defined by typing in the text box on the post or page', $this->settings['slug'] ); ?>
                                        <?php if( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
                                            printf(
                                                esc_html( ' | or the “<a href="%1$s" target="%3$s">Focus keyphrase</a>” by <a href="%2$s" target="%3$s">Yoast SEO</a>', $this->settings['slug'] ),
                                                'https://yoast.com/focus-keyword/#utm_source=yoast-seo&utm_medium=referral&utm_term=focus-keyphrase-qm&utm_content=socialized-plugin&utm_campaign=wordpress-general&platform=wordpress',
                                                'https://wordpress.org/plugins/wordpress-seo/',
                                                '_blank'
                                            );
                                        } ?>
                                    </li>
                                </ul>
                            </li>
                            <li><?php esc_html_e( 'Displays social media sharing links in the content of each post that uses these vanity URLs.', $this->settings['slug'] ); ?></li>
                        </ol>
                        <p><?php esc_html_e( 'Your permalink struture will not be affected.', $this->settings['slug'] ); ?></p>
                    </section>
                    <section id="settings" class="tab">
                        <h2><?php esc_html_e( 'Settings', $this->settings['slug'] ); ?></h2>
                        <form method="post" action="options.php" class="container">
                            <?php
                                settings_fields( $this->settings['group'] );
                                do_settings_sections( $this->settings['group'] );
                                $location = esc_attr( get_option( $this->settings['prefix'] . 'buttons_location', $this->settings['default']['buttons_location'] ) );
                                $icon_type = esc_attr( get_option( $this->settings['prefix'] . 'icon_type', $this->settings['default']['icon_type'] ) );
                            ?>
                            <h3><?php esc_html_e( 'Display', $this->settings['slug'] ); ?></h3>
                            <fieldset>
                                <p class="input-field">
                                    <select id="<?php esc_attr_e( $this->settings['prefix'] ); ?>icon_type" name="<?php echo( $this->settings['prefix'] ); ?>icon_type" class="controller" required>
                                        <option value="png"<?php esc_attr_e( $icon_type == 'png' ? ' selected' : '' ); ?>><?php esc_html_e( 'Image Icons', $this->settings['slug'] ); ?></option>
                                        <option value="fontawesome"<?php esc_attr_e( $icon_type == 'fontawesome' ? ' selected' : '' ); ?>><?php esc_html_e( 'Font Awesome Icons', $this->settings['slug'] ); ?></option>
                                        <option value="text"<?php esc_attr_e( $icon_type == 'text' ? ' selected' : '' ); ?>><?php esc_html_e( 'Text Links', $this->settings['slug'] ); ?></option>
                                    </select>
                                    <label for="<?php esc_attr_e( $this->settings['prefix'] ); ?>icon_type">
                                        <?php esc_html_e( 'Choose a button style for displaying the links', $this->settings['slug'] ); ?>
                                        <span class="required"><?php esc_html_e( '(Required)', $this->settings['slug'] ); ?></span>
                                    </label>
                                </p>
                                <p class="input-field checkbox" data-controller="<?php esc_attr_e( $this->settings['prefix'] ); ?>icon_type" data-values="fontawesome">
                                    <?php $exclude_fontawesome_value = esc_attr( get_option( $this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome'] ) ); ?>
                                    <input type="hidden" id="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome" name="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome" value="<?php echo( $exclude_fontawesome_value ); ?>" />
                                    <input type="checkbox" id="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome_check" name="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome_check" <?php echo( $exclude_fontawesome_value == 'true' ? 'checked' : '' ); ?> />
                                    <label for="<?php esc_attr_e( $this->settings['prefix'] ); ?>exclude_fontawesome_check">
                                        <?php esc_html_e( 'Exclude Font Awesome 5 Free Library', $this->settings['slug'] ); ?>
                                        <span class="note">
                                            <?php esc_html_e( ' (Check this box if your theme already has Font Awesome implemented)', $this->settings['slug'] ); ?>
                                        </span>
                                    </label>
                                </p>
                                <p class="input-field">
                                    <select id="<?php echo( $this->settings['prefix'] ); ?>buttons_location" name="<?php echo( $this->settings['prefix'] ); ?>buttons_location" required>
                                        <option value="top"<?php echo( $location == 'top' ? ' selected' : '' ); ?>><?php esc_html_e( 'Automatically place before the article\'s content (displays buttons horizontally)', $this->settings['slug'] ); ?></option>
                                        <option value="end"<?php echo( $location == 'end' ? ' selected' : '' ); ?>><?php esc_html_e( 'Automatically place after the article\'s content (displays buttons horizontally)', $this->settings['slug'] ); ?></option>
                                        <option value="hide"<?php echo( $location == 'hide' ? ' selected' : ''); ?>><?php esc_html_e( 'No automatic placement; use shortcodes in the content only (displays buttons horizontally)', $this->settings['slug'] ); ?></option>
                                    </select>
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>buttons_location">
                                        <?php esc_html_e( 'Choose where to display the social media sharing buttons', $this->settings['slug'] ); ?>
                                        <span class="required"><?php esc_html_e( '(Required)', $this->settings['slug'] ); ?></span>
                                        <span class="note">
                                            <?php esc_html_e( ' (Use the shortcode ', $this->settings['slug'] ); ?>
                                            <span class="inline-code-snippet">[<?php echo( $this->settings['slug'] ); ?>]</span>
                                            <?php esc_html_e( ' to display these buttons within the content. If placed, it will override the automatic placement so they\'re only displayed once.', $this->settings['slug'] ); ?>
                                        </span>
                                    </label>
                                </p>
                            </fieldset>
                            <h3><?php esc_html_e( 'Link Tracking', $this->settings['slug'] ); ?></h3>
                            <fieldset>
                                <p class="input-field">
                                    <input id="<?php echo( $this->settings['prefix'] ); ?>utm_campaign" name="<?php echo( $this->settings['prefix'] ); ?>utm_campaign" value="<?php esc_attr_e( get_option($this->settings['prefix'] . 'utm_campaign', $this->settings['utm']['campaign'] ) ); ?>" required />
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>utm_campaign">
                                        <?php esc_html_e( 'The "utm_campaign" parameter\'s value', $this->settings['slug'] ); ?>
                                        <span class="required"><?php esc_html_e( '(Required)', $this->settings['slug'] ); ?></span>
                                    </label>
                                </p>
                                <p class="input-field">
                                    <input id="<?php echo( $this->settings['prefix'] ); ?>twitter" name="<?php echo( $this->settings['prefix'] ); ?>twitter" value="<?php esc_attr_e( get_option( $this->settings['prefix'] . 'twitter', $this->settings['default']['twitter'] ) ); ?>" placeholder="TessaTechArtist" />
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>twitter">
                                        <?php esc_html_e( 'Your default Twitter handle', $this->settings['slug'] ); ?>
                                    </label>
                                </p>
                                <p class="input-field">
                                    <input id="<?php echo( $this->settings['prefix'] ); ?>twitter_related" name="<?php echo( $this->settings['prefix'] ); ?>twitter_related" value="<?php esc_attr_e( get_option( $this->settings['prefix'] . 'twitter_related', $this->settings['default']['twitter_related'] ) ); ?>" placeholder="TessaTechArtist" />
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>twitter_related">
                                        <?php esc_html_e( 'Comma separated list of up to three (3) related Twitter handles to suggest the user follows after they tweet your post', $this->settings['slug'] ); ?>
                                    </label>
                                </p>
                            </fieldset>
                            <h3><?php esc_html_e( 'Advanced Settings' , $this->settings['slug'] ); ?></h3>
                            <fieldset>
                                <p class="input-field checkbox">
                                    <?php $redirecting_value = esc_attr( get_option( $this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting'] ) ); ?>
                                    <input type="hidden" id="<?php echo( $this->settings['prefix'] ); ?>redirecting" name="<?php echo( $this->settings['prefix'] ); ?>redirecting" value="<?php echo( $redirecting_value ); ?>" />
                                    <input type="checkbox" id="<?php echo( $this->settings['prefix'] ); ?>redirecting_check" name="<?php echo( $this->settings['prefix'] ); ?>redirecting_check" <?php echo( $redirecting_value == 'true' ? 'checked' : '' ); ?> />
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>redirecting_check">
                                        <?php esc_html_e( 'Redirects enabled', $this->settings['slug'] ); ?>
                                        <span class="note">
                                            <?php esc_html_e( ' (If disabled, the social media sharing links will still appear on the posts/pages, but will share your permalink with the UTM parameters added to it instead. Any vanity URLs that were shared on social media will also stop redirecting, resulting in a 404 page to be displayed)', $this->settings['slug'] ); ?>
                                        </span>
                                    </label>
                                </p>
                                <p class="input-field">
                                    <input id="<?php echo( $this->settings['prefix'] ); ?>post_types" name="<?php echo( $this->settings['prefix'] ); ?>post_types" value="<?php esc_attr_e( get_option( $this->settings['prefix'] . 'post_types', $this->settings['default']['post_types'] ) ); ?>" placeholder="post,page,product" required />
                                    <label for="<?php echo( $this->settings['prefix'] ); ?>post_types">
                                        <?php esc_html_e( 'Comma separated list of the post types to generate URLs for', $this->settings['slug'] ); ?>
                                        <span class="required"><?php esc_html_e( '(Required)', $this->settings['slug'] ); ?></span>
                                    </label>
                                </p>
                                <p class="buttons">
                                    <?php submit_button( __( 'Save Settings', $this->settings['slug'] ) ); ?>
                                </p>
                            </fieldset>
                        </form>
                    </section>
                    <section id="regenerate" class="tab">
                        <h2><?php esc_html_e( 'Generate Missing Vanity URLs', $this->settings['slug'] ); ?></h2>
                        <p><?php esc_html_e( 'Click the button below to generate vanity URLs for posts that do not have them. It was already run once when this plugin was activated, and each new post gets one when it is first saved, even as a draft. If you created a custom post type and wish to generate links for all of those posts, be sure to save the custom post type\'s slug in <em>Settings</em> before clicking this button.', $this->settings['slug'] ); ?></p>
                        <p class="buttons">
                            <button id="generate-urls" class="button button-primary"><?php esc_html_e( 'Generate Missing Vanity URLs', $this->settings['slug'] ); ?></button>
                            <span class="loading-spinner hide"><i class="fas fa-spinner fa-spin"></i></span>
                        </p>
                        <p id="generate-status" class="status-text notice notice-info hide"></p>
                    </section>
                </div>
            </div>
        </div>
    <?php }

    //**** Post Management Page ****//

    /**
	 * Add Metabox.
     * 
     * Adds this plugin's metabox to the edit pages of the posts of the appropriate post type.
     * 
	 * @since 1.0.0
	 */
    function add_metabox() {
        $post_types = explode( ',', get_option( $this->settings['prefix'] . 'post_types', $this->settings['default']['post_types'] ) );
        foreach( $post_types as $post_type ) {
            add_meta_box(
                $this->settings['slug'],// Unique ID
                $this->settings['name'],// Box title
                array( $this, 'display_metabox' ),// Content callback, must be of type callable
                $post_type// Post type
            );
        }
    }

    /**
	 * Display Metabox.
     * 
     * HTML markup for the metabox used to manage this plugin at the post level.
     * 
	 * @since 1.0.0
     * @param WP_Post $post Post Object.
	 */
    public function display_metabox( $post ) {
        $slug = esc_attr( get_post_meta( $post->ID, $this->settings['prefix'] . 'slug', true ) );
        $term = esc_attr( get_post_meta( $post->ID, $this->settings['prefix'] . 'term', true ) );
        $default_term = $this->get_term( $post->ID );
        $utm_content = get_the_title( $post->ID ) . ' (id: ' . $post->ID . ')';
        $utm_campaign = esc_attr( get_option( $this->settings['prefix'] . 'utm_campaign', $this->settings['utm']['campaign'] ) );
        ?>
        <fieldset>
            <p class="input-field">
                <label for="<?php echo( $this->settings['prefix'] ); ?>term">
                    <?php esc_html_e( 'Campaign Term', $this->settings['slug'] ); ?>
                    <?php if( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
                        printf( '<span class="note">%s</span>', __( 'If left blank, this will use Yoast SEO\'s "Focus keyphrase"', $this->settings['slug'] ) );
                    } ?>
                </label>
                <input name="<?php echo( $this->settings['prefix'] ); ?>term" id="<?php echo( $this->settings['prefix'] ); ?>term" value="<?php echo( esc_attr( $term ) ); ?>" placeholder="<?php echo( esc_attr( $default_term ) ); ?>" />
            </p>
            <p class="input-field">
                <label for="<?php echo( $this->settings['prefix'] ); ?>slug"><?php esc_html_e( 'Vanity Slug', $this->settings['slug'] ); ?></label>
                <input name="<?php echo( $this->settings['prefix'] ); ?>slug" id="<?php echo( $this->settings['prefix'] ); ?>slug" value="<?php echo( esc_attr( $slug ) ); ?>" />
            </p>
        </fieldset>
        <?php if( $slug ) : ?>
            <ol>
                <?php foreach( $this->settings['platforms'] as $key => $platform ) :
                    $p_slug = $slug . $platform['suffix'];
                    $query_data = $this->get_permalink_with_query( $key, $post->ID, true );
                    $hits = esc_attr( get_post_meta( $post->ID, $this->settings['prefix'] . 'hits_' . $query_data['utm_source'], true ) ); 
                    if( ! $hits ) { $hits = 0; } ?>
                    <li>
                        <p><strong><?php esc_html_e( $platform['title'] ); ?></strong></p>
                        <ul>
                            <li><strong><?php esc_html_e( 'Vanity URL: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php printf( '<a href="%1$s" target="_blank">%2$s</a>', get_home_url( null, $p_slug ), $p_slug ); ?></li>
                            <li><strong><?php esc_html_e( 'Hits: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $hits ); ?></li>
                            <li><strong><?php esc_html_e( 'Campaign Source: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $query_data['utm_source'] ); ?></li>
                            <li><strong><?php esc_html_e( 'Campaign Medium: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $query_data['utm_medium'] ); ?></li>
                            <li><strong><?php esc_html_e( 'Campaign Name: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $query_data['utm_campaign'] ); ?></li>
                            <li><strong><?php esc_html_e( 'Campaign Content: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $query_data['utm_content'] ); ?></li>
                            <?php if( ! empty( $query_data['utm_term'] ) ) : ?>
                            <li><strong><?php esc_html_e( 'Campaign Term: ', $this->settings['slug'] ); ?></strong>&nbsp;<?php echo( $query_data['utm_term'] ); ?></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else : ?>
            <p><?php esc_html_e( 'Edit the vanity slug above and save or', $this->settings['slug'] ); ?> <a href="<?php esc_url_e( $this->settings['admin_url'] ); ?>"><?php esc_html_e( 'automatically generate the missing ones', $this->settings['slug'] ); ?></a>.</p>
        <?php endif;
        if( esc_attr( get_option( $this->settings['prefix'] . 'redirecting' ) ) != 'true' ) : ?>
            <p><strong><em><?php esc_html_e( 'Redirects are disabled!', $this->settings['slug'] ); ?></em></strong> <a href="<?php esc_url_e( $this->settings['admin_url'] ); ?>"><?php esc_html_e( 'Enable vanity URLs', $this->settings['slug'] ); ?></a></p>           
        <?php endif; ?>
        <p><a href="<?php esc_url_e( $this->settings['admin_url'] ); ?>"><?php esc_html_e( 'Edit Global Settings', $this->settings['slug'] ); ?></a></p>
        <p><a href="https://support.google.com/analytics/answer/1033863" target="_blank"><?php esc_html_e( 'Learn more', $this->settings['slug'] ); ?></a> <?php esc_html_e( 'about Custom Campaigns in Google.', $this->settings['slug'] ); ?></p>
        <?php
    }

    /**
	 * Get Campaign Term.
     * 
     * Retrieves the value for utm_term for the individual post, using Yoast SEO's "focus keyphrase" if available.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
     * @return string Campaign term.
	 */
    private function get_term( $post_id ) {
        $term = esc_attr( get_post_meta( $post_id, $this->settings['prefix'] . 'term', true ) );//Get the plugin's meta value first
        if( empty( $term ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) { $term = esc_attr( get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ) ); }//Set Yoast SEO's "focus keyphrase" as "utm_term" if empty
        return $term;
    }

    /**
     * Save Post.
     * 
	 * When saving a new post, save the custom slug too.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
	 */
    public function save_post( $post_id ) {
        //Update the campaign term if it was edited
        $meta_key_term = $this->settings['prefix'] . 'term';
        if( array_key_exists( $meta_key_term, $_POST ) && ! empty( $_POST[$meta_key_term] ) ) {
            update_post_meta( $post_id, $meta_key_term, sanitize_text_field( $_POST[$meta_key_term] ) );
        }

        //Generate social media URLs for just this post if it doesn't already exist
        $slug = '';
        $meta_key_slug = $this->settings['prefix'] . 'slug';
        if ( array_key_exists( $meta_key_slug, $_POST ) && ! empty( $_POST[$meta_key_slug] ) ) {
            $slug = sanitize_key( $_POST[$meta_key_slug] );
            update_post_meta( $post_id, $meta_key_slug, $slug );
        } else {
            $slug = get_post_meta( $post_id, $meta_key_slug, true );
        }
        $this->generate_slug( $post_id, $slug );
    }
    
    /**
	 * Generate Slug
     * 
     * Create the slug used for a single post and save it.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
     * @param string $slug Optional. Custom slug. Generates randomly if left blank.
     * @param array $return Optional. Custom return object for debugging.
     * @return array Custom return object for debugging.
	 */
    public function generate_slug( $post_id, $slug = '', $return = array( 'success' => 0, 'error' => 0, 'messages' => array() ) ) {
        $return['messages'][] = 'Generating slug for post ID ' . $post_id . ' - "' . $slug . '"';
        $continue = true;
        $all_slugs = (array) esc_attr( get_option( $this->settings['prefix'] . 'all_slugs', array() ) );//Get option of all slugs
        //If the slug is empty, generate a new one
        if( empty( $slug ) ) {
            $return['messages'][] = 'Provided slug was empty. Generate slug for ' . $post_id;
            $attempts = 0;
            $max_attempts = 100;
            for( $i = 0; $i < 1; $i++ ) {
                $slug = $this->random_str();//Get a random string using default values
                $attempts++;
                if( $attempts > $max_attempts ) {
                    //Don't create an infinite loop, just give up if reached max attempts
                    $i += 2;
                    $return['error']++;
                    $return['messages'][] = 'MAXIMUM ATTEMPTS REACHED for Post ID ' . $post_id;
                    $continue = false;
                } else if( $all_slugs && gettype( $all_slugs ) == 'array' && count( $all_slugs ) && in_array( $slug, $all_slugs ) ) {
                    //check if exists, if it does, decrement $i to redo the slug
                    $i--;
                }
            }
            $return['messages'][] = $attempts . ' attempts were made for post ID ' . $post_id;
        }
        //Continue if the slug was generated or provided
        if( $continue && ! empty( $slug ) ) {
            $all_slugs[] = $slug;//Add slug to array of all slugs
            array_unique( $all_slugs, SORT_STRING );//Remove duplicate values from array
            update_option( $this->settings['prefix'] . 'all_slugs', $all_slugs );//Update option with array of all slugs
            update_post_meta( $post_id, $this->settings['prefix'] . 'slug', $slug );//Update post meta with slug
            $return['success']++;
            $return['messages'][] = 'Post ID ' . $post_id . ' was updated with slug ' . $slug;
        }
        return $return;
    }

    //**** Plugin Functions ****//

    /**
	 * Perform the Redirect.
     * 
     * When hitting a 404 page, read the URL and redirect to the appropriate article with the defined UTM parameters.
	 *
	 * @since 1.0.0
	 */
    public function redirect() {
        if( is_404() && get_option( $this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting'] ) == 'true' ) {
            $path_parts = explode( '/', $_SERVER['REQUEST_URI'] );
            $path_parts_c = count( $path_parts );
            if( $path_parts_c >= 2 ) {
                $slug = $path_parts[$path_parts_c - 1];//If there is a trailing slash, then the last array item will be empty
                if( empty( $slug ) ) { $slug = $path_parts[$path_parts_c - 2]; }
                if( ! empty( $slug ) ) {
                    $suffix = substr( $slug, -2 );
                    $slug = str_replace( $suffix, '', $slug );
                    $user = wp_get_current_user();
                    foreach( $this->settings['platforms'] as $social_class => $social_data ) {
                        if( $suffix == $social_data['suffix'] ) {
                            //This suffix belongs to a generated URL, now go find it
                            $soc_query = new WP_Query( array(
                                'post_status' => array(
                                    'publish',//A published post or page
                                    'future',//Scheduled post
                                    'private',//Not visible to users who are not logged in
                                ),
                                'meta_key' => 'socialized_slug',
                                'meta_value' => $slug,
                                'meta_compare' => '='
                            ) );
                            if( $soc_query->have_posts() ) {
                                while( $soc_query->have_posts() )  {
                                    $soc_query->the_post();
                                    $post_id = get_the_ID();
                                    $redirect_url = get_permalink( $post_id ) . '?' . $this->get_permalink_with_query( $social_class, $post_id );
                                    //Don't register "hits" for admins
                                    if( ! in_array( 'administrator', $user->roles ) ) {
                                        $this->register_hit( $post_id );
                                        $this->register_hit( $post_id, $social_class );
                                    }
                                    wp_safe_redirect( $redirect_url, 301 );//301 Moved Permanently, SEO transfers "page rank" to the new location
                                    exit;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
	 * Register Hit.
     * 
     * Registers a hit from a user being redirected using this plugin.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
     * @param string $platform Optional. Platform key identifier.
	 * @return integer $hits Total number of hits for this post or platform on this post.
	 */
    function register_hit( $post_id, $platform = false ) {
        $meta_key = 'socialized_hits';
        if( $platform ) { $meta_key .= '_' . $platform; }
        $hits = $this->get_hits( $post_id, $platform );
        $hits++;
        update_post_meta( $post_id, $meta_key, strval( $hits ) );
        return $hits;
    }

    /**
	 * Get Hits.
     * 
     * Retrieve the total number of hits of a given post and platform.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
     * @param string $platform Optional. Platform key identifier.
	 * @return integer $hits Total number of hits for this post or platform on this post.
	 */
    function get_hits( $post_id, $platform = false ) {
        $meta_key = 'socialized_hits';
        if( $platform ) { $meta_key .= '_' . $platform; }
        $hits = get_post_meta( $post_id, $meta_key, true );//returns empty string if doesn't exist
        if( empty( $hits ) ) {
            $hits = '0';
            update_post_meta( $post_id, $meta_key, $hits );
        }
        return intval( $hits );
    }

    /**
     * Generate URLs.
     * 
	 * Queries the posts, looking for those that should have a slug but doesn't and then looping through them, creating slugs for posts that need them.
	 *
	 * @since 1.0.0
     * @param array $return Optional. Custom return object for debugging.
     * @return array Custom return object for debugging.
	 */
    function generate_urls( $return = array( 'success' => 0, 'error' => 0, 'messages' => array() ) ) {
        $return['messages'][] = 'Creating query...';

        $args = array (
            'post_type' => explode( ',', get_option( $this->settings['prefix'] . 'post_types', $this->settings['default']['post_types'] ) ),//Retrieves an array of post types from plugin settings
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                //Tests if the slug field doesn't exist
                array(
                    'key' => $this->settings['prefix'] . 'slug',
                    'value' => 'bug #23268',//Included for compatibility for WordPress version below 3.9
                    'compare' => 'NOT EXISTS'
                ),
                //Tests if the slug field is empty
                array(
                    'key' => $this->settings['prefix'] . 'slug',
                    'value' => array(''),
                    'compare' => 'IN'
                )
            )
        );
        $return['messages'][] = $args;

        //Query posts of the specified post type that doesn't have a slug created
        $posts_query = new WP_Query( $args );//Returns an array of IDs
        $count = $posts_query->found_posts;
        $return['messages'][] = 'Results: ' . $count;
        if( $count ) {
            $return['messages'][] = 'Begin looping through posts...';
            //Loop through all the posts and generate URLs for each one
            foreach( $posts_query->posts as $post_id ) {
                $return = $this->generate_slug( $post_id, '', $return );
            }
            $return['messages'][] = 'Loop completed.';
        }
        $return['success']++;
        return $return;
    }


    /**
     * Regenerate URLs via AJAX.
     * 
	 * Called from assets/scripts/admin-dashboard.js via AJAX, it runs the generate_urls() function on button click in the admin page.
	 *
	 * @since 1.0.0
     * @return array Custom return object for debugging.
	 */
    public function regenerate_urls() {
        $return = array(
            'success' => 0,
            'error' => 0,
            'messages' => array(),
            'output' => null
        );
        $return['messages'][] = 'Regenerating URLs via AJAX call';
        $return = $this->generate_urls( $return );
        if( $return['success'] && ! $return['error'] ) {
            $return['output'] = 'Generation completed successfully.';
        } else {
            $return['output'] = 'Errors occurred. Please check the browser\'s console log for details.';
        }
        //Echo response and die
        echo( json_encode( $return ) );
        die();
    }

    /**
     * Plugin Activation Hook.
     * 
	 * Generates URLs for posts on plugin activation using default settings.
	 *
	 * @since 1.0.0
	 */
    function activate() {
        $this->generate_urls();
    }

    //**** Frontend & Shortcode ****//

    /**
	 * Enqueue Frontend Styles and Scripts.
	 *
	 * @since 1.0.0
	 */
    function enqueue_scripts() {
        //Enqueue the frontend stylesheet for buttons
        wp_enqueue_style( $this->settings['slug'], $this->settings['url'] . 'assets/styles/socialized.css', array(), $this->settings['version'] );
        
        //Enqueue the FontAwesome 5 Free stylesheet, optionally based on settings
        $icon_type = get_option( $this->settings['prefix'] . 'icon_type', $this->settings['default']['icon_type'] );
        $exclude_fontawesome = get_option( $this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome'] );
        if( $icon_type == 'fontawesome' && $exclude_fontawesome == 'false' ) {
            wp_enqueue_style( $this->settings['slug'] . '-fontawesome', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', array(), '5.2.0' );
        }
    }

    /**
     * Get Buttons.
     * 
	 * Generates the HTML elements of the social media sharing buttons for a single post.
	 *
	 * @since 1.0.0
     * @param integer $post_id Post ID.
     * @param boolean $echo Optional. Echo or return the button HTML.
     * @param string $placement Optional. Where to place the button HTML.
     * @return string Social media sharing buttons HTML.
	 */
    public function get_buttons( $post_id = false, $echo = true, $placement = 'auto' ) {
        $post_id = $post_id === false ? get_the_ID() : $post_id;
        $output = '';
        $redirecting = get_option( $this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting'] ) == 'true';       
        $share_atts = array(
            'url' => '',
            'permalink' => get_the_permalink( $post_id ),
            'slug' => get_post_meta( $post_id, 'socialized_slug', true ),
            'title' => urlencode( get_the_title( $post_id ) ),
            'description' => get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ),//Get Yoast meta description
            'twitter' => urlencode( get_option($this->settings['prefix'] . 'twitter') ),
            'twitter_related' => urlencode( get_option($this->settings['prefix'] . 'twitter_related') ),
            'site_url' => urlencode( get_bloginfo( 'url' ) ),
            'image' => ''
        );
        
        //Search for images within the content to feature

        //Get the article's body content HTML
        $post_content = get_post_field( 'post_content' );
        if( ! empty( $post_content ) ) {
            //Count the number of image elements
            $imgs_count = substr_count( $post_content, '<img' );
            if( $imgs_count > 0 ) {
                //Create a DOM parser object
                $dom = new DOMDocument;
                //Parse the HTML
                @$dom->loadHTML( $post_content );//Suppress errors with @ because content is not always going to be valid HTML
                //Iterate over the <img /> elements
                $images = array();
                foreach( $dom->getElementsByTagName( 'img' ) as $img ) {
                    //Retrieve the src attribute from the element and add it to the array
                    $images[] = $img->getAttribute( 'src' );
                }
                if( count( $images ) > 0 ) {
                    $share_atts['image'] = $images[0];//Set the first image
                }
            }
        }
        //If no image was found yet, fallback to the featured image
        if( empty( $share_atts['image']) ) {
            //Get the featured image
            $featured_image_url = get_the_post_thumbnail_url( $post_id, 'full' );
            //Convert the image to an absolute URL as the above returns relative
            if( $featured_image_url && $featured_image_url[0] == '/' ) { $share_atts['image'] = get_home_url( null, $featured_image_url ); }
        }

        $buttons = array();
        foreach( $this->settings['platforms'] as $social_class => $social_data ) {
            $link = '';
            if( $redirecting ) {
                $share_atts['url'] = urlencode( get_home_url( null, $share_atts['slug'] . $social_data['suffix'] ) );
            } else {
                if( strpos( $share_atts['permalink'], '?' ) === false ) { $share_atts['permalink'] .= '?'; } else { $share_atts['permalink'] .= '&'; }
                $share_atts['url'] = urlencode( $share_atts['permalink'] ) . $this->get_permalink_with_query( $social_class );
            }
            switch( $social_class ) {
                case 'facebook':
                case 'linkedin':
                    $link = sprintf( $social_data['link_format'], $share_atts['url'] );
                    break;
                case 'twitter':
                    $link = sprintf(
                        $social_data['link_format'], 
                        $share_atts['url'], 
                        $share_atts['title'], 
                        $share_atts['twitter'], 
                        $share_atts['twitter_related'],
                        $share_atts['site_url']
                    );
                    break;
                case 'pinterest':
                    $link = sprintf(
                        $social_data['link_format'],
                        $share_atts['url'],
                        $share_atts['image'],
                        $share_atts['title']
                    );
                    break;
                default:
                    break;
            }
            if( ! empty( $link ) ) {
                $icon_type = get_option( $this->settings['prefix'] . 'icon_type' );
                $icon = '';
                switch( $icon_type ) {
                    case 'fontawesome':
                        $icon = sprintf( '<i class="fa fab fa-%s"></i>', $social_data['fontawesome'] );
                        break;
                    case 'text':
                        $icon = sprintf( '<span class="%s-text">%s</span>', $this->settings['slug'], $social_data['title'] );
                        break;
                    default:
                        $icon = sprintf(
                            '<img class="%3$s-icon" src="%1$s" alt="%2$s Icon" />',
                            $social_data['icon'],
                            $social_data['title'],
                            $this->settings['slug']
                        );
                        break;
                }
                $buttons[] = sprintf( '<a href="%4$s" target="_blank" title="Share on %2$s" class="socialized-link %1$s" onclick="window.open(this.href, \'targetWindow\', \'toolbar=no,location=0,status=no,menubar=no,scrollbars=yes,resizable=yes,width=%5$s,height=%6$s\'); return false;">%3$s</a>',
                    $social_class,
                    $social_data['title'],
                    $icon,
                    $link,
                    $social_data['width'],
                    $social_data['height']
                );
            }
        }
        if( count( $buttons ) > 0 ) {
            $shareText = sprintf( '<span class="share-text%2$s">%1$s</span>', __( 'Share this on:', $this->settings['slug'] ), $icon_type == 'text' ? '' : ' screen-reader-text' );
            $output = sprintf( '<p class="socialized-links placement-%2$s icon-type-%3$s">%4$s%1$s</p>', implode( '', $buttons ), $placement, $icon_type, $shareText );
        }
        $output = esc_html( $output );
        if( $echo ) { echo( $output ); }
        else { return $output; }
    }

    /**
     * Register Shortcode.
     * 
	 * Creates the shortcode to use in post content that displays the social media sharing buttons on the frontend for a single post.
	 *
	 * @since 1.0.0
     * @param array $atts Optional. Shortcode attributes.
     * @param string $content Optional.
     * @param string $tag Optional.
     * @return string Social media sharing button HTML.
	 */
    public function shortcode( $atts = [], $content = null, $tag = '') {   
        return $this->get_buttons( false, false );
    }
 
    /**
     * Display Buttons.
     * 
	 * Displays social media sharing buttons on the frontend for a single post.
	 *
	 * @since 1.0.0
     * @param string $content. Post content.
     * @return string Modified post content.
	 */
    public function display_buttons( $content ) {
        $fullcontent = $content;
        $allowed_post_types = explode( ',', get_option( $this->settings['prefix'] . 'post_types', $this->settings['default']['post_types'] ) );
        if( is_single() && is_singular( $allowed_post_types ) ) {
            //Only place automatically if the settings aren't set to hidden and if there isn't a shortcode already in the content
            $placement = get_option( $this->settings['prefix'] . 'buttons_location', $this->settings['default']['buttons_location'] );
            /*
                This function has a priority of 20 (default is 10), so shortcodes have already 
                been processed before this is run, that's why we're checking for the compiled 
                code instead of the shortcode.
            */
            $contains_shortcode = strpos( $content, '<p class="socialized-links' ) !== false;
            if( $placement != 'hide' && ( ! $contains_shortcode ) ) {
                $buttons = $this->get_buttons( get_the_ID(), false );
                switch( $placement ) {
                    case 'end':
                        $fullcontent = $content . $buttons;
                        break;
                    default:
                        $fullcontent = $buttons . $content;
                        break;
                }
            }
        }
        return $fullcontent;
    }

    //**** Utility Functions ****//

    /**
	 * Get UTM Query for Post.
	 *
	 * @since 1.0.0
     * @param string $social_platform Platform identifier.
     * @param integer $post_id Optional. Post ID.
     * @param boolean $return_array Optional. Return query as an array or as a query string.
     * @return string A randomly generated string.
	 */
    public function get_permalink_with_query( $social_platform, $post_id = false, $return_array = false ) {
        $post_id = $post_id === false ? get_the_ID() : $post_id;      
        $query_data = array(
            'utm_source' => esc_attr( $social_platform ),//Use utm_source to identify a search engine, newsletter name, or other source. Example: google
            'utm_medium' => esc_attr( $this->settings['utm']['medium'] ),//Use utm_medium to identify a medium such as email or cost-per- click. Example: cpc
            'utm_campaign' => esc_attr( get_option( $this->settings['prefix'] . 'utm_campaign', $this->settings['utm']['campaign'] ) ),//Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign. Example: spring_sale
            'utm_content' => esc_attr( $this->settings['utm']['content'] ),//Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL. 
            'utm_term' => $this->get_term( $post_id )//Used for paid search. Use utm_term to note the keywords for this ad. Example: running+shoes
        );
        if( $return_array ) { return $query_data; }
        return http_build_query( $query_data );
    }

    /**
	 * Generate a Random String.
     * 
     * Used for generating random vanity URLs.
	 *
	 * @since 1.0.0
     * @param integer $length Optional. Number of characters to put in string. 8.
     * @param string $keyspace Optional. Letters, numbers, and/or symbols to select from for string.
     * @return string A randomly generated string.
	 */
    function random_str( $length = 8, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_' ) {
        $str = '';
        $max = mb_strlen( $keyspace, '8bit' ) - 1;
        for( $i = 0; $i < $length; ++$i ) { $str .= $keyspace[random_int(0, $max)]; }
        return sanitize_key( $str );
    }
}

endif; /*class_exists check */ 

/**
 * Returns the main instance of Socialized.
 *
 * @since  1.0.0
 * @return Socialized
 */
function socialized() { return Socialized::instance(); }
$GLOBALS['socialized'] = socialized();// Global for backwards compatibility.

/* Purposely excluding the closing PHP tag to avoid "headers already sent" error */