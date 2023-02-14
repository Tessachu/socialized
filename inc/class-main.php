<?php

/**
 * Main Plugin File
 *
 * @package AuRise\Plugin\Socialized
 */

namespace AuRise\Plugin\Socialized;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use AuRise\Plugin\Socialized\Utilities;
use \DOMDocument;
use \WP_Query;

if (!class_exists('Socialized')) {

    class Socialized
    {
        /**
         * Plugin version.
         *
         * @var string
         */
        public $version = '3.0.3';

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
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
            $version = $this->version;
            $basename = plugin_basename(SOCIALIZED_FILE);
            $path = plugin_dir_path(SOCIALIZED_FILE);
            $url = plugin_dir_url(SOCIALIZED_FILE);
            $slug = dirname($basename);
            $slug_underscore = str_replace('-', '_', $slug);
            load_plugin_textdomain('socialized', false, $slug . '/languages'); //Translations

            $this->settings = array(

                // Basics
                'name' => __('Socialized', 'socialized'),
                'version' => $version,
                'capability_post' => 'edit_post',
                'capability_settings' => 'manage_options',

                // URLs
                'file' => SOCIALIZED_FILE,
                'basename' => $basename, // E.g.: "plugin-folder/file.php"
                'path' => $path, // E.g.: "/path/to/wp-content/plugins/plugin-folder/"
                'url' => $url, // E.g.: "https://domain.com/wp-content/plugins/plugin-folder/"
                'admin_url' => admin_url(sprintf('tools.php?page=%s', $slug)), // E.g.: "https://domain.com/wp-admin/tools.php?page=plugin-folder"
                'slug' => $slug, // E.g.: "plugin-folder"
                'prefix' => $slug_underscore . '_', // E.g.: "plugin_folder_"
                'group' => $slug . '-group', // E.g.: "plugin-folder-group"

                // Social Media Platforms
                'platforms' => array(
                    'facebook' => array(
                        'title' => __('Facebook', 'socialized'), //Platform's Title
                        'suffix' => '-f', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_facebook_32.png', //Absolute URL to icon image
                        'fontawesome' => 'facebook-f', //FontAwesome class
                        'link_format' => 'https://www.facebook.com/sharer.php?u=%1$s', //Platform's sharing link format
                        'width' => 600, //Pop-up window's width
                        'height' => 750, //Pop-up window's height
                        'prefix_title' => true //Adds "Share on [title] for screenreaders
                    ),
                    'twitter' => array(
                        'title' => __('Twitter', 'socialized'), //Platform's Title
                        'suffix' => '-t', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_twitter_32.png', //Absolute URL to icon image
                        'fontawesome' => 'twitter',
                        'link_format' => 'https://twitter.com/intent/tweet?url=%1$s&text=%2$s&via=%3$s&related=%4$s&original_referer=%5$s', //Platform's sharing link format
                        'width' => 600, //Pop-up window's width
                        'height' => 270, //Pop-up window's height
                        'prefix_title' => true //Adds "Share on [title] for screenreaders
                    ),
                    'linkedin' => array(
                        'title' => __('LinkedIn', 'socialized'), //Platform's Title
                        'suffix' => '-l', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_linkedin_32.png', //Absolute URL to icon image
                        'fontawesome' => 'linkedin-in', //FontAwesome class
                        'link_format' => 'https://www.linkedin.com/sharing/share-offsite/?url=%1$s', //Platform's sharing link format
                        'width' => 600, //Pop-up window's width
                        'height' => 530, //Pop-up window's height
                        'prefix_title' => true //Adds "Share on [title] for screenreaders
                    ),
                    'pinterest' => array(
                        'title' => __('Pinterest', 'socialized'), //Platform's Title
                        'suffix' => '-p', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_pinterest_32.png', //Absolute URL to icon image
                        'fontawesome' => 'pinterest-p', //FontAwesome class
                        'link_format' => 'https://pinterest.com/pin/create/button/?url=%1$s&media=%2$s&description=%3$s', //Platform's sharing link format
                        'width' => 800, //Pop-up window's width
                        'height' => 680, //Pop-up window's height
                        'prefix_title' => true //Adds "Share on [title] for screenreaders
                    ),
                    'email' => array(
                        'title' => __('Email', 'socialized'), //Platform's Title
                        'suffix' => '-e', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_email_32.png', //Absolute URL to icon image
                        'fontawesome' => 'envelope', //FontAwesome class
                        'link_format' => 'mailto:?subject=%1$s&body=%2$s', //Platform's sharing link format
                        'width' => 800, //Pop-up window's width
                        'height' => 680, //Pop-up window's height
                        'prefix_title' => __('Forward via email', 'socialized') //replaces the title for screenreaders
                    ),
                    'vanity-url' => array(
                        'title' => __('Copy URL', 'socialized'), //Platform's Title
                        'suffix' => '-c', //Suffix for identifying platform's vanity URL
                        'icon' => $url . 'assets/images/icon_link_32.png', //Absolute URL to icon image
                        'fontawesome' => 'link', //FontAwesome class
                        'visible' => is_ssl(), //This button only works on secure websites
                        'popup' => sprintf(
                            '<span id="%s" class="copied-popup hidden" role="tooltip"><span>%s</span></span>',
                            $slug . '-copied-popup',
                            __('Copied!', 'socialized')
                        ),
                        'prefix_title' => __('Copy to clipboard', 'socialized') // uses the title
                    )
                ),

                //Default UTM Values
                'utm' => array(
                    'medium' => 'social',
                    'campaign' => $slug,
                    'content' => $slug . '-share-link'
                ),

                //Possible button locations
                'button_locations' => array(
                    'top' => __('Before content (displays horizontally)', 'socialized'),
                    'end' => __('After content (displays horizontally)', 'socialized'),
                    'stick-left' => __('Left of content (displays vertically)', 'socialized'),
                    'stick-right' => __('Right of content (displays vertically)', 'socialized'),
                    'hide' => __('I will use shortcodes only', 'socialized')
                ),

                //Possible button displays
                'button_icon' => array(
                    'png' => __('Image Icons', 'socialized'),
                    'fontawesome' => __('FontAwesome Icons', 'socialized'),
                    'text' => __('Text Links', 'socialized')
                ),

                //Default Settings Values
                'default' => array(
                    'buttons_location' => 'top',
                    'post_types' => 'post',
                    'redirecting' => 'true',
                    'twitter' => '',
                    'twitter_related' => '',
                    'icon_type' => 'png',
                    'autofix_sticky' => 'false',
                    'exclude_fontawesome' => 'false'
                )
            );

            //Plugin Setup
            add_action('admin_init', array($this, 'register_settings')); //Register plugin option settings
            add_action('admin_menu', array($this, 'admin_menu')); //Add admin page link in WordPress dashboard
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts')); //Enqueue styles/scripts for admin page
            add_filter('plugin_action_links_' . $basename, array($this, 'plugin_links')); //Add link to admin page from plugins page

            //Plugin-specific Actions
            add_action('init', array($this, 'init_shortcodes')); //Add init hook to add shortcodes
            add_action('add_meta_boxes', array($this, 'add_metabox')); //Add metabox to posts dashboard pages
            add_action('save_post', array($this, 'save_post')); //Save metabox settings on post save
            add_action('template_redirect', array($this, 'redirect'), 20); //Perform redirect
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 20); //Enqueue styles for frontend
            add_action('wp_ajax_socialized_regenerate_urls', array($this, 'regenerate_urls')); //Add AJAX call for logged in users to regenerate missing vanity URLs from admin page
            add_action('wp_ajax_socialized_update_url', array($this, 'update_url')); //Add AJAX call for logged in users to update vanity URL from post edit page
            add_action('wp_ajax_socialized_view', array($this, 'get_backend_view')); //Add AJAX call for logged in users to get a paginated list of posts with links

            //Plugin-specific Filters
            add_filter('the_content', array($this, 'display_buttons'), 20); //Display buttons on frontend
        }

        //**** Plugin Settings ****//

        /**
         * Register Plugin Settings
         *
         * @since 1.0.0
         */
        public function register_settings()
        {
            register_setting($this->settings['group'], $this->settings['prefix'] . 'all_slugs'); //An array of all the slugs generated
            register_setting($this->settings['group'], $this->settings['prefix'] . 'buttons_location'); //Current button location
            register_setting($this->settings['group'], $this->settings['prefix'] . 'utm_campaign'); //value for utm_campaign
            register_setting($this->settings['group'], $this->settings['prefix'] . 'post_types'); //Post types to display buttons for
            add_settings_field(
                $this->settings['prefix'] . 'redirecting',
                __('Redirections Enabled', 'socialized'),
                array($this, 'display_redirecting_checkbox'),
                $this->settings['slug'],
                $this->settings['group']
            );
            register_setting($this->settings['group'], $this->settings['prefix'] . 'redirecting'); //If redirectig/using vanity URLs with UTM parameters or just the permalinks
            register_setting($this->settings['group'], $this->settings['prefix'] . 'twitter'); //Value for "via @user" for Twitter sharing
            register_setting($this->settings['group'], $this->settings['prefix'] . 'twitter_related'); //Value for up to three Twitter users to promote after sharing
            register_setting($this->settings['group'], $this->settings['prefix'] . 'icon_type'); //Icon type, the style for which the buttons display
            add_settings_field(
                $this->settings['prefix'] . 'exclude_fontawesome', //ID
                __('Exclude FontAwesome', 'socialized'), //Title
                array($this, 'display_checkbox'), //Callback (should echo its output)
                $this->settings['slug'], //Page
                $this->settings['group'], //Section
                array(
                    'type' => 'checkbox',
                    'name' => $this->settings['prefix'] . 'exclude_fontawesome',
                    'id' => $this->settings['prefix'] . 'exclude_fontawesome',
                    //Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string.
                    'checked' => checked(1, get_option($this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome']), false)
                )
            );
            register_setting($this->settings['group'], $this->settings['prefix'] . 'exclude_fontawesome'); //If using FontAwesome enqueued here or to exclude it
            add_settings_field(
                $this->settings['prefix'] . 'autofix_sticky', //ID
                __('Autofix Sticky Icons', 'socialized'), //Title
                array($this, 'display_checkbox'), //Callback (should echo its output)
                $this->settings['slug'], //Page
                $this->settings['group'], //Section
                array(
                    'type' => 'checkbox',
                    'name' => $this->settings['prefix'] . 'autofix_sticky',
                    'id' => $this->settings['prefix'] . 'autofix_sticky',
                    //Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string.
                    'checked' => checked(1, get_option($this->settings['prefix'] . 'autofix_sticky', $this->settings['default']['autofix_sticky']), false)
                )
            );
            register_setting($this->settings['group'], $this->settings['prefix'] . 'autofix_sticky'); //If sticking the icons, should the plugin automatically fix parent visibilities
        }

        /**
         * Display checkbox input
         *
         * @since 2.0.0
         * @param array $args
         */
        function display_checkbox($args)
        {
            printf(
                '<input type="%s" id="%s" name="%s" %s />',
                esc_attr($args['type']),
                esc_attr($args['name']),
                esc_attr($args['id']),
                $args['checked']
            );
        }

        /**
         * Display switch toggle
         *
         * @since 2.0.0
         * @param array $args An associatve array with keys for `name` (string), `value` (string of `true` or `false`), `yes` (string), `no` (string), `label`, and `reverse` (bool)
         */
        public function display_checkbox_switch($args)
        {
            $value = esc_attr($args['value']);
            $reverse = esc_attr($args['reverse']);
            printf(
                '<span class="checkbox-switch version-2">
                    <input type="hidden" id="%1$s" name="%1$s" value="%2$s" />
                    <input class="input-checkbox%7$s" type="checkbox" id="%1$s_check" name="%1$s_check" %3$s />
                    <span class="checkbox-animate">
                        <span class="checkbox-off">%4$s</span>
                        <span class="checkbox-on">%5$s</span>
                    </span>
                </span>
                <label for="%1$s_check"><span class="note">%6$s</span></label>',
                esc_attr($args['name']), //1 - Input name
                $value, //2 - Input value
                checked($reverse ? 'false' : 'true', $value, false), //3 - Checked attribute, if reversed, compare against the opposite value
                esc_attr($args['no']), //4 - on value
                esc_attr($args['yes']), //5 - off value
                esc_attr($args['label']), //6 - label
                $reverse ? ' reverse-checkbox' : '' //7 - whether checkbox should be visibly reversed
            );
        }

        /**
         * Checkbox display for redirecting setting.
         *
         * @since 1.0.0
         */
        function display_redirecting_checkbox()
        {
            //Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string.
            printf(
                '<input type="checkbox" id="%1$sredirecting" name="%1$sredirecting" %2$s />',
                esc_attr($this->settings['prefix']),
                esc_attr(checked(1, get_option($this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting']), true))
            );
        }

        /**
         * Checkbox display for exclude FontAwesome setting.
         *
         * @since 1.0.0
         */
        function display_exclude_fontawesome_checkbox()
        {
            //Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string
            printf(
                '<input type="checkbox" id="%1$sexclude_fontawesome" name="%1$sexclude_fontawesome" %2$s />',
                esc_attr($this->settings['prefix']),
                esc_attr(checked(1, get_option($this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome']), true))
            );
        }

        //**** Plugin Management Page ****//

        /**
         * Add Admin Page.
         *
         * Adds the admin page to the WordPress dashboard under "Tools".
         *
         * @since 1.0.0
         */
        public function admin_menu()
        {
            add_management_page(
                $this->settings['name'],
                $this->settings['name'],
                $this->settings['capability_settings'],
                $this->settings['slug'],
                array(&$this, 'admin_page')
            );
        }

        /**
         * Plugin Links
         *
         * Links to display on the plugins page.
         *
         * @since 1.0.0
         * @param array $links
         * @return array A list of links
         */
        public function plugin_links($links)
        {
            $settings_link = sprintf(
                '<a href="%s">%s</a>',
                $this->settings['admin_url'],
                __('Settings', 'socialized')
            );
            array_unshift($links, $settings_link);
            return $links;
        }

        /**
         * Admin Scripts and Styles
         *
         * Enqueue scripts and styles to be used on the admin pages
         *
         * @since 1.0.0
         * @param string $hook Hook suffix for the current admin page
         */
        public function admin_enqueue_scripts($hook)
        {
            //Register FontAwesome
            wp_register_script(
                $this->settings['prefix'] . '-fontawesome',
                $this->settings['url'] . 'assets/fontawesome/all.min.js',
                array(),
                '6.0.0',
                true
            );

            if ($hook == 'tools_page_' . $this->settings['slug']) {
                // Load only on our plugin page (a subpage of "Tools")

                //Plugin Stylesheets
                wp_enqueue_style(
                    $this->settings['prefix'] . 'layout',
                    $this->settings['url'] . 'assets/styles/pseudo-bootstrap.css',
                    array(),
                    filemtime($this->settings['path'] . 'assets/styles/pseudo-bootstrap.css')
                );
                wp_enqueue_style(
                    $this->settings['prefix'] . 'dashboard',
                    $this->settings['url'] . 'assets/styles/admin-dashboard.css',
                    array($this->settings['prefix'] . 'layout'),
                    filemtime($this->settings['path'] . 'assets/styles/admin-dashboard.css')
                );

                //Plugin Scripts
                wp_enqueue_script('jquery');
                wp_enqueue_script(
                    $this->settings['prefix'] . 'dashboard',
                    $this->settings['url'] . 'assets/scripts/admin-dashboard.js',
                    array('jquery', $this->settings['prefix'] . '-fontawesome'),
                    filemtime($this->settings['path'] . 'assets/scripts/admin-dashboard.js'),
                    true
                );
                wp_localize_script(
                    $this->settings['prefix'] . 'dashboard',
                    'au_object',
                    array('ajax_url' => admin_url('admin-ajax.php'))
                );
            } elseif ($hook == 'post-new.php' || $hook == 'post.php') {
                // Load only on the post edit page

                //Plugin Stylesheets
                wp_enqueue_style(
                    $this->settings['prefix'] . 'metabox',
                    $this->settings['url'] . 'assets/styles/admin-metabox.css',
                    array(),
                    $this->settings['version']
                );

                //Plugin Scripts
                wp_enqueue_script('jquery');
                wp_enqueue_script(
                    $this->settings['prefix'] . 'metabox',
                    $this->settings['url'] . 'assets/scripts/admin-metabox.js',
                    array('jquery', $this->settings['prefix'] . '-fontawesome'),
                    filemtime($this->settings['path'] . 'assets/scripts/admin-metabox.js'),
                    true
                );
                wp_localize_script(
                    $this->settings['prefix'] . 'metabox',
                    'au_object',
                    array('ajax_url' => admin_url('admin-ajax.php'))
                );
            }
        }

        /**
         * Display Admin Page
         *
         * HTML markup for the WordPress dashboard admin page for managing this plugin's settings.
         *
         * @since 1.0.0
         */
        public function admin_page()
        {
            //Prevent unauthorized users from viewing the page
            if (!current_user_can($this->settings['capability_settings'])) {
                return;
            }
            $paged = isset($_GET['paged']) && is_numeric($_GET['paged']) ? $_GET['paged'] : 0;
            load_template($this->settings['path'] . 'templates/dashboard-admin.php', true, array(
                'post_types' => $this->get_post_types(),
                'plugin_settings' => $this->settings,
                'view_table' => $this->get_view_table(array('paged' => $paged)),
                'yoast-seo' => $this->plugin_active('wordpress-seo/wp-seo.php')
            ));
        }

        /**
         * Get Allowed Post Types
         *
         * @since 3.0.0
         * @return array a sequential array of post types
         */
        private function get_post_types()
        {
            //$types = $this->get_setting('post_types', true);
            $types = get_option(
                $this->settings['prefix'] . 'post_types',
                $this->settings['default']['post_types']
            );
            if (!$types) {
                $types = $this->settings['default']['post_types'];
            }
            return explode(',', $types);
        }

        /**
         * Get Available Platforms
         *
         * @since 3.0.0
         * @return array an associative array of available platforms
         */
        private function get_platforms()
        {
            $platforms = array();
            foreach ($this->settings['platforms'] as $key => $platform) {
                //Only get visible platforms, if it's not configured, default to true
                if (Utilities::array_has_key('visible', $platform, true)) {
                    $platforms[$key] = $platform;
                }
            }
            return $platforms;
        }

        //**** Post Management Page ****//

        /**
         * Add Metabox
         *
         * Adds this plugin's metabox to the edit pages of the posts of the appropriate post type.
         *
         * @since 1.0.0
         */
        public function add_metabox()
        {
            $post_types = $this->get_post_types();
            foreach ($post_types as $post_type) {
                add_meta_box(
                    $this->settings['slug'], // Unique ID
                    $this->settings['name'], // Box title
                    array($this, 'display_metabox'), // Content callback, must be of type callable
                    $post_type // Post type
                );
            }
        }

        /**
         * Display Metabox
         *
         * HTML markup for the metabox used to manage this plugin at the post level
         *
         * @since 1.0.0
         * @param WP_Post $post Post Object.
         */
        public function display_metabox($post)
        {
            //Prevent unauthorized users from viewing the page
            if (!current_user_can($this->settings['capability_post'], $post->ID)) {
                return;
            }

            $slug = get_post_meta($post->ID, $this->settings['prefix'] . 'slug', true);
            $platforms = array();
            if ($slug) {
                foreach ($this->get_platforms() as $key => $platform) {
                    $query = $this->get_permalink_with_query($key, $post->ID, true);
                    $platforms[$key] = array(
                        'slug' => $slug . $platform['suffix'],
                        'title' => $platform['title'],
                        'query' => $query,
                        'hits' => get_post_meta($post->ID, $this->settings['prefix'] . 'hits_' . $query['utm_source'], true)
                    );
                }
            }

            //Load the template file
            load_template($this->settings['path'] . 'templates/edit-metabox.php', true, array(
                'post' => array('ID' => $post->ID, 'type' => $post->post_type),
                'plugin_settings' => $this->settings,
                'slug' => $slug,
                'term' => get_post_meta($post->ID, $this->settings['prefix'] . 'term', true),
                'default_term' => $this->get_term($post->ID),
                'platforms' => $platforms,
                'yoast-seo' => $this->plugin_active('wordpress-seo/wp-seo.php')
            ));
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
        private function get_term($post_id)
        {
            $term = get_post_meta($post_id, $this->settings['prefix'] . 'term', true); //Get the plugin's meta value first
            if (empty($term) && $this->plugin_active('wordpress-seo/wp-seo.php')) {
                //Set Yoast SEO's "focus keyphrase" as "utm_term" if empty
                $term = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            }
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
        public function save_post($post_id)
        {
            //Update the campaign term if it was edited
            $meta_key_term = $this->settings['prefix'] . 'term';
            if (array_key_exists($meta_key_term, $_POST) && !empty($_POST[$meta_key_term])) {
                update_post_meta($post_id, $meta_key_term, sanitize_text_field($_POST[$meta_key_term]));
            }

            //Generate social media URLs for just this post if it doesn't already exist
            $slug = '';
            $meta_key_slug = $this->settings['prefix'] . 'slug';
            if (array_key_exists($meta_key_slug, $_POST) && !empty($_POST[$meta_key_slug])) {
                $slug = sanitize_key($_POST[$meta_key_slug]);
                update_post_meta($post_id, $meta_key_slug, $slug);
            } else {
                $slug = get_post_meta($post_id, $meta_key_slug, true);
            }
            $this->generate_slug($post_id, $slug);
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
        public function generate_slug($post_id, $slug = '', $return = array('success' => 0, 'error' => 0, 'messages' => array()), $all_slugs = array())
        {
            $return['messages'][] = 'Generating slug for post ID ' . $post_id . ' - "' . $slug . '"';
            $continue = true;
            if (!count($all_slugs)) {
                $all_slugs = $this->get_all_slugs(); //Get option of all slugs, should be an array of arrays
            }
            //If the slug is empty, generate a new one
            if (empty($slug)) {
                $return['messages'][] = 'Provided slug was empty. Generate slug for ' . $post_id;
                $attempts = 0;
                $max_attempts = 100;
                for ($i = 0; $i < 1; $i++) {
                    $slug = $this->random_str(); //Get a random string using default values
                    $attempts++;
                    if ($attempts > $max_attempts) {
                        //Don't create an infinite loop, just give up if reached max attempts
                        $i += 2;
                        $return['error']++;
                        $return['messages'][] = 'MAXIMUM ATTEMPTS REACHED for Post ID ' . $post_id;
                        $continue = false;
                    } elseif ($all_slugs && gettype($all_slugs) == 'array' && count($all_slugs) && array_key_exists($slug, $all_slugs)) {
                        //check if exists, if it does, decrement $i to redo the slug
                        $i--;
                    }
                }
                $return['messages'][] = $attempts . ' attempts were made for post ID ' . $post_id;
            }
            //Continue if the slug was generated or provided
            if ($continue && !empty($slug)) {
                $updated = $this->update_post_with_slug($slug, $post_id, $all_slugs);
                if ($updated) {
                    $return['success']++;
                    $return['messages'][] = 'Post ID ' . $post_id . ' was updated with slug ' . $slug;
                } else {
                    $return['error']++;
                    $return['messages'][] = 'Post ID ' . $post_id . ' failed to update with slug ' . $slug;
                }
            }
            return $return;
        }

        /**
         * Get Post by Slug
         *
         * Return the post ID of that matches for the slug.
         *
         * @since 1.4.0
         * @param string $slug Socialized Vanity Slug
         * @param array $post_status A sequential array of post statuses (as strings) to look up. Default is all of them. Pass an empty array to use default.
         * @param bool $force_query If false, it will attempt to look up a cached version for performance. The cached versions expire after 4 hours. Default is true.
         * @return int Post ID. Returns 0 if not found.
         */
        public function get_post_by_slug($slug = '', $post_status = array(), $force_query = true)
        {
            if (!empty($slug)) {
                $cache_key = 'slug_' . $slug;
                $post_id = $this->get_cache($cache_key);
                if (!$post_id || $force_query) {
                    //Default post status for lookup
                    $post_status = is_array($post_status) && count($post_status) ? $post_status : array(
                        'publish', //A published post or page
                        'pending', //Pending review
                        'draft', //Draft status
                        'auto-draft', //Newly created post, with no content
                        'future', //Scheduled post
                        'private', //Not visible to users who are not logged in
                        'inherit', //A revision, see get_children
                        'trash' //In the trashbin
                    );
                    //Go find it
                    $soc_query = new WP_Query(array(
                        'post_type' => $this->get_post_types(), //Retrieves an array of post types from plugin settings
                        'posts_per_page' => -1, //Get all results
                        'post_status' => $post_status,
                        'meta_key' => $this->settings['prefix'] . 'slug',
                        'meta_value' => $slug,
                        'meta_compare' => '='
                    ));
                    if ($soc_query->have_posts()) {
                        while ($soc_query->have_posts()) {
                            $soc_query->the_post();
                            $post_id = get_the_ID();
                            $this->set_cache($cache_key, $post_id, 4); //Cache for 4 hours
                            return $post_id;
                        }
                    }
                }
                if (is_numeric($post_id)) {
                    return $post_id;
                }
            }
            return 0;
        }

        //**** Plugin Functions ****//

        /**
         * Perform the Redirect.
         *
         * When hitting a 404 page, read the URL and redirect to the appropriate article with the defined UTM parameters.
         *
         * @since 1.0.0
         */
        public function redirect()
        {
            if (is_404() && get_option($this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting']) == 'true') {
                $redirect_data = $this->get_cache($_SERVER['REQUEST_URI'], 'redirects');
                if (!is_array($redirect_data) || !count($redirect_data)) {
                    $original_url = parse_url($_SERVER['REQUEST_URI']);
                    $path_parts = explode('/', $original_url['path']);
                    $path_parts_c = count($path_parts);
                    //Looking specifically for a length of 1 or 2
                    if ($path_parts_c > 0 && $path_parts_c < 3) {
                        $slug = $path_parts[$path_parts_c - 1];
                        //If there is a trailing slash, then the last array item will be empty, get the next one
                        if (empty($slug)) {
                            $slug = $path_parts[$path_parts_c - 2];
                        }
                        //Don't continue if the slug is in a directory
                        if ((!($path_parts_c > 2 || ($path_parts_c == 2 && $path_parts[0]))) && !empty($slug)) {
                            $query = '';
                            if (array_key_exists('query', $original_url) && $original_url['query']) {
                                $query = '&' . $original_url['query'];
                            }
                            $slug_parts = explode('-', $slug); //In case it was added in the generation bit, get the last one
                            $slug_parts_c = count($slug_parts); //Should be at least 2, maybe more
                            if ($slug_parts_c > 1) {
                                $suffix = $slug_parts[$slug_parts_c - 1]; //Gets the last part, our suffix
                                unset($slug_parts[$slug_parts_c - 1]); //Removes the suffix from the array
                                $slug = implode('-', $slug_parts); //Reform the remaining slug back into a string
                                foreach ($this->get_platforms() as $social_class => $platform) {
                                    if ('-' . $suffix == $platform['suffix']) {
                                        //This suffix belongs to a generated URL, now go find it
                                        $post_id = $this->get_post_by_slug($slug, array('publish', 'future', 'private'), false);
                                        if ($post_id) {
                                            $redirect_data = array(
                                                'post_id' => $post_id,
                                                'redirect_url' => get_permalink($post_id) . '?' . $this->get_permalink_with_query($social_class, $post_id) . $query,
                                                'platform' => $social_class
                                            );
                                            $this->set_cache($_SERVER['REQUEST_URI'], $redirect_data, 4, 'redirects');
                                            break; //Break out of the foreach loop
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (is_array($redirect_data) && count($redirect_data)) {
                    //Don't register "hits" for admins
                    $user = wp_get_current_user();
                    if (!in_array('administrator', $user->roles)) {
                        $this->register_hit($redirect_data['post_id']);
                        $this->register_hit($redirect_data['post_id'], $redirect_data['platform']);
                    }
                    wp_safe_redirect($redirect_data['redirect_url'], 301, $this->settings['name']); //301 Moved Permanently, SEO transfers "page rank" to the new location
                    exit;
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
        function register_hit($post_id, $platform = false)
        {
            $meta_key = 'socialized_hits';
            if ($platform) {
                $meta_key .= '_' . $platform;
            }
            $hits = $this->get_hits($post_id, $platform);
            $hits++;
            update_post_meta($post_id, $meta_key, strval($hits));
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
        function get_hits($post_id, $platform = false)
        {
            $meta_key = 'socialized_hits';
            if ($platform) {
                $meta_key .= '_' . $platform;
            }
            $hits = get_post_meta($post_id, $meta_key, true); //returns empty string if doesn't exist
            if (empty($hits)) {
                $hits = '0';
                update_post_meta($post_id, $meta_key, $hits);
            }
            return intval($hits);
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
        function generate_urls($return = array('success' => 0, 'error' => 0, 'messages' => array()))
        {
            $return['messages'][] = 'Creating query...';

            $args = array(
                'post_type' => $this->get_post_types(), //Retrieves an array of post types from plugin settings
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => array(
                    'relation' => 'OR',
                    //Tests if the slug field doesn't exist
                    array(
                        'key' => $this->settings['prefix'] . 'slug',
                        'value' => 'bug #23268', //Included for compatibility for WordPress version below 3.9
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
            $posts_query = new WP_Query($args); //Returns an array of IDs
            $count = $posts_query->found_posts;
            $return['messages'][] = 'Results: ' . $count;
            $all_slugs = $this->get_all_slugs();
            if ($count) {
                $return['messages'][] = 'Begin looping through posts...';
                //Loop through all the posts and generate URLs for each one
                foreach ($posts_query->posts as $post_id) {
                    $return = $this->generate_slug($post_id, '', $return, $all_slugs);
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
        public function regenerate_urls()
        {
            $return = array(
                'success' => 0,
                'error' => 0,
                'messages' => array(),
                'output' => null
            );
            $return['messages'][] = 'Regenerating URLs via AJAX call';
            $return = $this->generate_urls($return);
            if ($return['success'] && !$return['error']) {
                $return['output'] = 'Generation completed successfully.';
            } else {
                $return['output'] = 'Errors occurred. Please check the browser\'s console log for details.';
            }
            //Echo response and die
            echo (json_encode($return));
            die();
        }

        /**
         * AJAX: Update Vanity URL for Single Post.
         *
         * Called from assets/scripts/admin-metabox.js via AJAX, it runs the generate_urls() function on button click in the admin page.
         *
         * @since 1.0.0
         * @return array Custom return object for debugging.
         */
        public function update_url()
        {
            $return = array(
                'success' => 0,
                'error' => 0,
                'messages' => array(),
                'output' => null,
                'fields' => json_decode(str_replace('%27', "'", urldecode($_POST['fields']))),
                'links' => array()
            );
            $return['fields'] = is_null($return['fields']) ? null : (array) $return['fields'];
            $return['messages'][] = 'Updating a single URL via AJAX call';
            if (is_null($return['fields'])) {
                $return['error']++;
                $return['messages'][] = 'Fields post data is null!!!';
            } elseif ($return['fields']['post_id']) {
                $return['success']++;
                //Validate that the slug doesn't already exist
                $post_id = $this->get_post_by_slug($return['fields']['slug']);
                if ($post_id && $post_id != $return['fields']['post_id']) {
                    $return['error']++;
                    $return['output'] = 'This vanity slug is already used by &ldquo;' . get_the_title($post_id) . '&rdquo;. Please try a different one.';
                } elseif (!$post_id) {
                    //This is good to update
                    $return['success']++;
                    $value_before = get_post_meta($return['fields']['post_id'], $this->settings['prefix'] . 'slug', true);
                    $return['messages'][] = 'No existing posts with this slug was found. Updating ' . $value_before . ' to ' . $return['fields']['slug'] . '...';
                    $value_after = $this->update_post_with_slug($return['fields']['slug'], $return['fields']['post_id'], array(), $value_before);
                    if ($value_after) {
                        $return['success']++;
                        $return['messages'][] = 'Post ID ' . $return['fields']['post_id'] . ' was updated with slug ' . $return['fields']['slug'];
                        $return['output'] = 'Success!';
                        foreach ($this->get_platforms() as $key => $platform) {
                            $p_slug = $return['fields']['slug'] . $platform['suffix'];
                            $query_data = $this->get_permalink_with_query($key, $return['fields']['post_id'], true);
                            $return['links'][$key] = array(
                                'vanity_url_link' => home_url($p_slug),
                                'vanity_url_label' => $p_slug,
                                'campaign_term' => $query_data['utm_term']
                            );
                        }
                    } else {
                        $return['error']++;
                        $return['messages'][] = 'Failed to update post ID ' . $return['fields']['post_id'] . ' with slug ' . $return['fields']['slug'];
                        $return['output'] = 'Errors occurred. Please check the browser\'s console log for details.';
                    }
                }
            }
            $return['all_slugs'] = get_option($this->settings['prefix'] . 'all_slugs', array());
            //Echo response and die
            echo (json_encode($return, JSON_PRETTY_PRINT));
            die();
        }

        /**
         * Get View Table
         *
         * @since 3.0.0
         * @param array $args An associative array of parameters for WP_Query()
         * @return string HTML output of the view table
         */
        private function get_view_table($args = array())
        {
            $o = '';
            $args = array_merge(array(
                'post_types' => $this->get_post_types(), //Get allowed post types
                'post_status' => 'any', //retrieves any status except for `inherit`, `trash` and `auto-draft`
                'posts_per_page' => 200, //The first 200 posts
                'paged' => 0, //Display the first page
                'ignore_sticky_posts' => true, //Ignore sticky posts
                'orderby' => 'ID',
                'order' => 'DESC', //Newest content first
                'meta_query' => array(array(
                    'key' => $this->settings['prefix'] . 'slug',
                    'value' => array(''),
                    'compare' => 'NOT IN'
                ))
            ), $args);
            $platforms = $this->get_platforms();
            $has_platforms = count($platforms);
            $wpq = new WP_Query($args);
            if ($wpq->have_posts()) {
                $nav = '';
                if ($wpq->max_num_pages > 1) {
                    $nav .= '<nav class="pagination">';
                    $nav .= paginate_links(array(
                        'base' => $this->settings['admin_url'] . '%_%#view',
                        'format' => '&paged=%#%',
                        'current' => max(1, $args['paged']),
                        'total' => $wpq->max_num_pages,
                        'prev_text' => '&#8249;',
                        'next_text' => '&#8250;',
                        'type' => 'list',
                        'end_size' => 0,
                        'mid_size' => 3
                    ));
                    $nav .= '</nav>';
                }
                $o .= $nav;
                $o .= sprintf(
                    '<table class="au-table">
                    <thead><tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    </tr></thead><tbody>',
                    __('ID', 'socialized'),
                    __('Post Type', 'socialized'),
                    __('Title', 'socialized'),
                    __('Social Sharing Info')
                );
                while ($wpq->have_posts()) {
                    $wpq->the_post();
                    $post_id = get_the_ID();
                    $post_type = get_post_type($post_id);
                    $slug = get_post_meta($post_id, $this->settings['prefix'] . 'slug', true);
                    $sharing_info = '';

                    if ($has_platforms) {
                        $sharing_info .= '<ul>';
                        foreach ($platforms as $key => $platform) {
                            $url_params = $this->get_permalink_with_query($key, $post_id, true);
                            $hits = get_post_meta($post_id, $this->settings['prefix'] . 'hits_' . $url_params['utm_source'], true);
                            $platform_slug = $slug . $platform['suffix'];
                            $sharing_info .= sprintf(
                                '<li><strong>%s</strong>, %s %s: <a href="%s" target="_blank" rel="noopener noreferrer">%s</a></li>',
                                esc_html($platform['title']),
                                $hits ? $hits : 0,
                                __('hit(s)', 'socialized'),
                                esc_url(home_url($platform_slug)),
                                esc_html($platform_slug)
                            );
                        }
                        $sharing_info .= '</ul>';
                    }
                    $o .= sprintf(
                        '<tr>
                            <td class="post-id"><code>%1$s</code></td>
                            <td class="post-type"><code>%2$s</code></td>
                            <td class="post-title">%5$s<br><a href="%3$s" target="_blank" rel="noopener noreferrer">%6$s</a> <a href="%4$s" target="_blank" rel="noopener noreferrer">%7$s</a></td>
                            <td class="slug">%8$s</td>
                        </tr>',
                        $post_id, // 1
                        $post_type, //2
                        get_edit_post_link($post_id), //3
                        get_the_permalink($post_id), //4
                        get_the_title($post_id), //5
                        __('Edit', 'socialized'), //6
                        __('View', 'socialized'), //7
                        $sharing_info //8
                    );
                }
                $o .= '</tbody></table>';
                $o .= $nav;
            }
            wp_reset_postdata();
            return $o;
        }

        /**
         * Plugin Activation Hook.
         *
         * Generates URLs for posts on plugin activation using default settings.
         *
         * @since 1.0.0
         */
        function activate()
        {
            $this->generate_urls();
        }

        //**** Frontend & Shortcode ****//

        /**
         * Enqueue Frontend Styles and Scripts.
         *
         * @since 1.0.0
         */
        function enqueue_scripts()
        {
            //Enqueue the frontend stylesheet for buttons
            wp_enqueue_style(
                $this->settings['slug'],
                $this->settings['url'] . 'assets/styles/socialized' . (WP_DEBUG ? '' : '.min') . '.css',
                array(),
                '2022.09.18.10.27', //Version of socialized.css
            );

            //Dependencies for main script
            $script_deps = array();

            //Enqueue the Free version of FontAwesome, optionally based on settings
            $icon_type = get_option($this->settings['prefix'] . 'icon_type', $this->settings['default']['icon_type']);
            $exclude_fontawesome = get_option($this->settings['prefix'] . 'exclude_fontawesome', $this->settings['default']['exclude_fontawesome']);
            if ($icon_type == 'fontawesome' && $exclude_fontawesome == 'false') {
                wp_register_script(
                    $this->settings['slug'] . '-fontawesome',
                    $this->settings['url'] . 'assets/fontawesome/all.min.js',
                    array(),
                    '6.0.0', //Version of FontAwesome
                    true //Load in Footer
                );
                $script_deps[] = $this->settings['slug'] . '-fontawesome';
            }

            //Register and enqueue frontend scripts for buttons
            wp_register_script(
                $this->settings['slug'] . '-stickybits',
                $this->settings['url'] . 'assets/scripts/vendor/stickybits.min.js',
                array(),
                '3.7.9', //Stickybits version
                true //Load in Footer
            );
            $script_deps[] = $this->settings['slug'] . '-stickybits';
            wp_enqueue_script(
                $this->settings['slug'],
                $this->settings['url'] . 'assets/scripts/socialized' . (WP_DEBUG ? '' : '.min') . '.js', //Use unminified script for debugging
                $script_deps,
                '2022.09.18.09.30', //Version of socialized.js
                true //Load in Footer
            );
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
        public function get_buttons($post_id = false, $echo = true, $placement = 'auto')
        {
            $post_id = $post_id === false ? get_the_ID() : $post_id;
            $output = '';
            $redirecting = get_option($this->settings['prefix'] . 'redirecting', $this->settings['default']['redirecting']) == 'true';
            $share_atts = array(
                'url' => '',
                'permalink' => get_the_permalink($post_id),
                'slug' => get_post_meta($post_id, 'socialized_slug', true),
                'title' => get_the_title($post_id),
                'description' => get_post_meta($post_id, '_yoast_wpseo_metadesc', true), //Get Yoast meta description
                'twitter' => urlencode(get_option($this->settings['prefix'] . 'twitter')),
                'twitter_related' => urlencode(get_option($this->settings['prefix'] . 'twitter_related')),
                'site_url' => urlencode(get_bloginfo('url')),
                'image' => ''
            );

            //Search for images within the content to feature

            //Get the article's body content HTML
            $post_content = get_post_field('post_content', $post_id);
            if (!empty($post_content)) {
                //Count the number of image elements
                $imgs_count = substr_count($post_content, '<img');
                if ($imgs_count > 0) {
                    //Create a DOM parser object
                    $dom = new DOMDocument;
                    //Parse the HTML
                    @$dom->loadHTML($post_content); //Suppress errors with @ because content is not always going to be valid HTML
                    //Iterate over the <img /> elements
                    $images = array();
                    foreach ($dom->getElementsByTagName('img') as $img) {
                        //Retrieve the src attribute from the element and add it to the array
                        $images[] = $img->getAttribute('src');
                    }
                    if (count($images) > 0) {
                        $share_atts['image'] = $images[0]; //Set the first image
                    }
                }
            }
            //If no image was found yet, fallback to the featured image
            if (empty($share_atts['image'])) {
                //Get the featured image
                $featured_image_url = get_the_post_thumbnail_url($post_id, 'full');
                //Convert the image to an absolute URL as the above returns relative
                if ($featured_image_url && $featured_image_url[0] == '/') {
                    $share_atts['image'] = home_url($featured_image_url);
                }
            }

            $buttons = array();
            foreach ($this->get_platforms() as $social_class => $platform) {
                if ($redirecting) {
                    $share_atts['url'] = urlencode(home_url($share_atts['slug'] . $platform['suffix']));
                } else {
                    if (strpos($share_atts['permalink'], '?') === false) {
                        $share_atts['permalink'] .= '?';
                    } else {
                        $share_atts['permalink'] .= '&';
                    }
                    $share_atts['url'] = urlencode($share_atts['permalink']) . $this->get_permalink_with_query($social_class);
                }
                $popup = array_key_exists('popup', $platform) ? $platform['popup'] : '';
                $fontawesome_class = 'fa-solid';
                $title_attr = Utilities::array_has_key('prefix_title', $platform, true);
                $link_atts = array(
                    'id' => sanitize_key('share-link-' . $social_class),
                    'title' => $title_attr ? ($title_attr === true ? __('Share on', 'socialized') . ' ' . $platform['title'] : $title_attr) : $platform['title'],
                    'data-platform' => $social_class,
                    'href' => '',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer nofollow',
                    'tabindex' => '0', //Accessibility documentation: https://web.dev/tabindex/
                    'onclick' => 'window.open(this.href, \'targetWindow\', \'toolbar=no,location=0,status=no,menubar=no,scrollbars=yes,resizable=yes,width=%1$s,height=%2$s\'); return false;'
                );
                switch ($social_class) {
                    case 'facebook':
                    case 'linkedin':
                        $link_atts['onclick'] = sprintf($link_atts['onclick'], $platform['width'], $platform['height']);
                        $fontawesome_class = 'fa-brands';
                        $link_atts['href'] = sprintf($platform['link_format'], $share_atts['url']);
                        break;
                    case 'twitter':
                        $link_atts['onclick'] = sprintf($link_atts['onclick'], $platform['width'], $platform['height']);
                        $fontawesome_class = 'fa-brands';
                        $link_atts['href'] = sprintf(
                            $platform['link_format'],
                            $share_atts['url'],
                            urlencode($share_atts['title']),
                            $share_atts['twitter'],
                            $share_atts['twitter_related'],
                            $share_atts['site_url']
                        );
                        break;
                    case 'pinterest':
                        $link_atts['onclick'] = sprintf($link_atts['onclick'], $platform['width'], $platform['height']);
                        $fontawesome_class = 'fa-brands';
                        $link_atts['href'] = sprintf(
                            $platform['link_format'],
                            $share_atts['url'],
                            $share_atts['image'],
                            urlencode($share_atts['title'])
                        );
                        break;
                    case 'email':
                        $link_atts['onclick'] = sprintf($link_atts['onclick'], $platform['width'], $platform['height']);
                        $link_atts['href'] = sprintf(
                            $platform['link_format'],
                            rawurlencode(__('I thought you might like this article:', 'socialized') . ' ') . str_replace(array('&', '&#038;', '&amp;'), '%26', $share_atts['title']),
                            rawurlencode($share_atts['description'] . ' ') . $share_atts['url']
                        );
                        break;
                    case 'vanity-url':
                        $link_atts['aria-describedby'] = $this->settings['slug'] . '-copied-popup';
                        $link_atts['target'] = '';
                        $link_atts['onclick'] = 'socialized.copyToClipboard(event); return false;';
                        $link_atts['href'] = urldecode($share_atts['url']);
                        break;
                    default:
                        break;
                }
                if (!empty($link_atts['href'])) {
                    $icon_type = get_option($this->settings['prefix'] . 'icon_type', $this->settings['default']['icon_type']);
                    $icon = '';
                    switch ($icon_type) {
                        case 'fontawesome':
                            $icon = sprintf('<i class="%s fa-%s"></i>', $fontawesome_class, $platform['fontawesome']);
                            $link_atts['width'] = 32;
                            $link_atts['height'] = 32;
                            break;
                        case 'text':
                            $icon = sprintf('<span class="%s-text">%s</span>', $this->settings['slug'], $platform['title']);
                            break;
                        default:
                            $icon = sprintf(
                                '<img class="%3$s-icon lazyload" src="%1$s" alt="%2$s" width="32" height="32" />',
                                $platform['icon'],
                                $platform['title'],
                                $this->settings['slug']
                            );
                            $link_atts['width'] = 32;
                            $link_atts['height'] = 32;
                            break;
                    }
                    $link_attrs = array();
                    foreach ($link_atts as $link_attr => $link_value) {
                        if ($link_value) {
                            $link_attrs[] = sprintf('%s="%s"', $link_attr, $link_value);
                        }
                    }

                    $buttons[] = sprintf(
                        '<span class="socialized-link-wrapper %s"><a class="socialized-link" %s>%s</a>%s</span>',
                        $social_class,
                        implode(' ', $link_attrs),
                        $icon,
                        $popup
                    );
                }
            }
            if (count($buttons) > 0) {
                $shareText = sprintf('<span class="share-text%2$s">%1$s</span>', __('Share this on:', 'socialized'), $icon_type == 'text' ? '' : ' screen-reader-text');
                $output = sprintf(
                    '<p class="socialized-links socialized-sticky-%s placement-%s icon-type-%s">%s%s</p>',
                    stripos($placement, 'stick') === false ? 'no' : 'yes',
                    $placement,
                    $icon_type,
                    $shareText,
                    implode('', $buttons)
                );
            }
            if ($echo) {
                echo ($output);
            } else {
                return $output;
            }
        }

        //**** Shortcode(s) ****//

        /**
         * Initialise Shortcode
         */
        public function init_shortcodes()
        {
            //Create shortcode for use in content
            add_shortcode($this->settings['slug'], array($this, 'shortcode'));
        }

        /**
         * Register Shortcode.
         *
         * Creates the shortcode to use in post content that displays the social media sharing
         * buttons on the frontend for a single post.
         *
         * @since 1.0.0
         * @param array $atts Optional. Shortcode attributes.
         * @param string $content Optional.
         * @param string $tag Optional.
         * @return string Social media sharing button HTML.
         */
        public function shortcode($atts = array(), $content = '', $tag = '')
        {
            // override default attributes with user attributes & normalize attribute keys, lowercase
            $atts = shortcode_atts(array(
                'placement' => '', // top | end | stick-left | stick-right | hide
                'icon' => ''
            ), array_change_key_case((array)$atts, CASE_LOWER), $tag);
            if (is_string($content) && $content) {
                return $this->display_buttons($content, in_array($atts['placement'], array('top', 'end', 'stick-left', 'stick-right', 'hide', '')) ? $atts['placement'] : '');
            }
            return $this->get_buttons(false, false);
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
        public function display_buttons($content = '', $placement = '')
        {
            $fullcontent = $content;
            $allowed_post_types = $this->get_post_types();
            if (is_single() && is_singular($allowed_post_types)) {
                $post_id = get_the_ID();
                //Only place automatically if the settings aren't set to hidden and if there isn't a shortcode already in the content
                $placement = $placement ? $placement : get_option($this->settings['prefix'] . 'buttons_location', $this->settings['default']['buttons_location']);
                /*
                    This function has a priority of 20 (default is 10), so shortcodes have already
                    been processed before this is run, that's why we're checking for the compiled
                    code instead of the shortcode.
                */
                $contains_shortcode = $content && stripos($content, '<p class="socialized-links') !== false;
                if ($placement != 'hide' && !$contains_shortcode) {
                    $buttons = $this->get_buttons($post_id, false, $placement);
                    switch ($placement) {
                        case 'end':
                            $fullcontent = $content . $buttons;
                            break;
                        case 'stick-left':
                        case 'stick-right':
                            //Only apply sticky buttons to icon buttons, not to text buttons
                            $icon_type = get_option($this->settings['prefix'] . 'icon_type', $this->settings['default']['icon_type']);
                            if ($icon_type != 'text') {
                                $fullcontent = sprintf(
                                    '<div class="socialized-sticky-wrapper %s">%s%s</div>',
                                    $placement,
                                    $buttons,
                                    $content
                                );
                            } else {
                                $fullcontent = $buttons . $content;
                            }
                            break;
                        default:
                            $fullcontent = $buttons . $content;
                            break;
                    }
                }
            }
            return $fullcontent;
        }

        /**
         * Get All Slugs
         *
         * Retrieve an associative array of key/value pairs, where the key is the slug, and the value is the post ID
         *
         * @since 1.4.0
         * @return array Returns an associative array
         */
        function get_all_slugs()
        {
            //As a backup, also do a WP query for all posts that have slugs
            $return = array();
            $post_ids = get_posts(array(
                'fields' => 'ids', //Returns an array of IDs
                'posts_per_page' => -1, //Get all results
                'post_type' => $this->get_post_types(), //Retrieves an array of post types from plugin settings
                'post_status' => array(
                    'publish', //A published post or page
                    'pending', //Pending review
                    'draft', //Draft status
                    'auto-draft', //Newly created post, with no content
                    'future', //Scheduled post
                    'private', //Not visible to users who are not logged in
                    'inherit', //A revision, see get_children
                    'trash' //In the trashbin
                ),
                'meta_key' => $this->settings['prefix'] . 'slug',
                'meta_value' => array(''), //Empty value
                'meta_compare' => 'NOT IN' //Compare against empty values
            ));
            if (count($post_ids)) {
                foreach ($post_ids as $post_id) {
                    $post_slug = esc_attr(get_post_meta($post_id, $this->settings['prefix'] . 'slug', true));
                    $return[$post_slug] = $post_id;
                }
            }
            update_option($this->settings['prefix'] . 'all_slugs', $return); //Update option with array of all slugs
            return $return;
        }

        /**
         * Add Slug
         *
         * Retrieve an associative array of key/value pairs, where the key is the slug, and the value is the post ID
         *
         * @since 1.4.0
         * @param string $slug Vanity slug to be added
         * @param string|int $post_id Post ID to be assigned this slug
         * @param array $all_slugs Optional. List of all slugs if already retrieved. Otherwise, it looks it up from the plugin options.
         * @return int|bool The new meta field ID if the field didn't exist and was therefore added, true on successful update, false on failure.
         */
        function update_post_with_slug($slug, $post_id, $all_slugs = array(), $prev_value = '')
        {
            $post_id = intval($post_id); //Convert it to an integer if it's a string
            if (!count($all_slugs)) {
                //Get all slugs if passed is empty
                $all_slugs = $this->get_all_slugs();
            }
            //Look for all of the old keys that belong to this post (if more than one) and remove them all from the array
            $old_keys = array_keys($all_slugs, $post_id);
            if (count($old_keys)) {
                foreach ($old_keys as $old_key) {
                    unset($all_slugs[$old_key]);
                }
            }
            $all_slugs[$slug] = $post_id; //Add new slug and post ID as key/value pair to array of all slugs
            update_option($this->settings['prefix'] . 'all_slugs', $all_slugs); //Update option with array of all slugs

            return update_post_meta($post_id, $this->settings['prefix'] . 'slug', $slug, $prev_value); //Update post meta with slug
        }

        //**** Utility Functions ****//

        /**
         * Get UTM Query for Post.
         *
         * @since 1.0.0
         * @param string $social_platform Platform identifier.
         * @param integer $post_id Optional. Post ID.
         * @param boolean $return_array Optional. Return query as an array or as a query string.
         * @return string|array The query as a URL query or an associative array of key/value pairs
         */
        public function get_permalink_with_query($social_platform, $post_id = false, $return_array = false)
        {
            $post_id = $post_id === false ? get_the_ID() : $post_id;
            $query_data = array(
                'utm_source' => esc_attr($social_platform), //Use utm_source to identify a search engine, newsletter name, or other source. Example: google
                'utm_medium' => esc_attr($this->settings['utm']['medium']), //Use utm_medium to identify a medium such as email or cost-per- click. Example: cpc
                'utm_campaign' => esc_attr(get_option($this->settings['prefix'] . 'utm_campaign', $this->settings['utm']['campaign'])), //Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign. Example: spring_sale
                'utm_content' => esc_attr($this->settings['utm']['content']), //Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.
                'utm_term' => $this->get_term($post_id) //Used for paid search. Use utm_term to note the keywords for this ad. Example: running+shoes
            );
            if ($return_array) {
                return $query_data;
            }
            return http_build_query($query_data);
        }

        /**
         * Cache Data
         *
         * WP Cache Documentation: https://developer.wordpress.org/reference/functions/wp_cache_set/
         * Transient Documentation: https://developer.wordpress.org/reference/functions/set_transient/
         *
         * @param string $key The key to use. This function will sanitize it for the database. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
         * @param mixed $value The data to be cached
         * @param int $expire Optional. The number of hours to cache before expiration. Default is 4
         * @param string $group Optional. The grouping for this cache. Default is an empty string. The value is always automatically prepended with language data.
         * @param int|false $post_id Optional. If this data is associted with a post, pass the post ID or false to save it to the post's meta data
         *
         * @return mixed $value
         */
        private function set_cache($key, $value, $expire = 4, $group = '', $post_id = null)
        {
            if ($key) {
                $group = sanitize_key(implode('_', array_filter(array(
                    $this->settings['prefix'],
                    get_locale(),
                    is_numeric($post_id) ? $post_id : '',
                    $group
                ))));
                $key = sanitize_key($key);
                wp_cache_set($key, $value, $group, $expire * HOUR_IN_SECONDS);
                set_transient($group . '_' . $key, $value, $expire * HOUR_IN_SECONDS);
            }
            return $value;
        }

        /**
         * Get Cached Data
         *
         * WP Cache Documentation: https://developer.wordpress.org/reference/functions/wp_cache_get/
         * Transient Documentation: https://developer.wordpress.org/reference/functions/get_transient/
         *
         * @param string $key The key to use. This function will sanitize it for the database
         * @param string $group Optional. The grouping for this cache. Default is an empty string. The value is always automatically prepended with language data.
         * @param int|false $post_id Optional. If this data is associted with a post, pass the post ID or false to attempt retrieval from the post's meta data
         *
         * @return mixed|false Value of data, false on failure to retrieve contents.
         */
        private function get_cache($key, $group = '', $post_id = null)
        {
            $value = false;
            if ($key) {
                $group = sanitize_key(implode('_', array_filter(array(
                    $this->settings['prefix'],
                    get_locale(),
                    is_numeric($post_id) ? $post_id : '',
                    $group
                ))));
                $key = sanitize_key($key);
                //If type is `cache` or `both, attempt to get the cache first
                $value = wp_cache_get(
                    $key, //(int|string) (Required) The key under which the cache contents are stored.
                    $group //(string) (Optional) Where the cache contents are grouped. Default value: ''
                    //false, //(bool) (Optional) Whether to force an update of the local cache from the persistent cache. Default value: false
                    //null//(bool) (Optional) Whether the key was found in the cache (passed by reference). Disambiguates a return of false, a storable value. Default value: null
                ); //(mixed|false) The cache contents on success, false on failure to retrieve contents.
                if ($value === false) {
                    $value = get_transient($group . '_' . $key); //(mixed|false) Value of transient, false on failure to retrieve contents.
                }
            }
            return $value;
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
        function random_str($length = 8, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_')
        {
            $str = '';
            $max = mb_strlen($keyspace, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $str .= $keyspace[random_int(0, $max)];
            }
            return sanitize_key($str);
        }

        /**
         * Is Plugin Active
         *
         * Used for generating random vanity URLs.
         *
         * @since 1.3.7
         * @param string $plugin Name of plugin to check for
         * @return bool Returns true if it is active, false if it is not
         */
        function plugin_active($plugin)
        {
            //Provides access to the is_plugin_active() function if not available
            if (!function_exists('is_plugin_active')) {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            return is_plugin_active($plugin);
        }
    }
}

/* Purposely excluding the closing PHP tag to avoid "headers already sent" error */