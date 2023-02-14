<?php

/**
 * Utilities
 *
 * @package AuRise\Plugin\Socialized
 */

namespace AuRise\Plugin\Socialized;

use \DateTime;

if (!function_exists('array_key_first')) {
    /**
     * Gets the first key of an array
     *
     * Gets the first key of the given array without affecting the internal array pointer.
     *
     * @param array $array
     * @return int|string|null
     */
    function array_key_first($array = array())
    {
        foreach ($array as $key => $value) {
            return $key;
        }
        return null;
    }
}

if (!class_exists('Utilities')) {
    /**
     * Class Utilities
     *
     * @package AuRise\Plugin\Video
     */
    class Utilities
    {
        /**
         * Array Key Exists and Has Value
         *
         * @since 1.0.0
         * @param string|int $key The key to search for in the array.
         * @param array $array The array to search.
         * @param mixed $default The default value to return if not found or is empty. Default is an empty string.
         * @return mixed|null The value of the key found in the array if it exists or the value of `$default` if not found or is empty.
         */
        public static function array_has_key($key, $array = array(), $default = '')
        {
            //Check if this key exists in the array
            $valid_key = (is_string($key) && !empty($key)) || is_numeric($key);
            $valid_array = is_array($array) && count($array);
            if ($valid_key && $valid_array && array_key_exists($key, $array)) {
                //Always return if it's a boolean or otherwise only return it if it has any value
                if ($array[$key] || is_bool($array[$key])) {
                    return $array[$key];
                }
            }
            return $default;
        }

        /**
         * Send Time to Server-Timing API
         *
         * @param DateTime $start a DateTime object of when the timing begain
         * @param string $name The name of what you're timing
         * @param string $key Optional. The key of what you're timing. Default is `wordpressPlugin`
         */
        public static function server_timing($start, $name, $key = 'wordpressPlugin')
        {
            $end = new DateTime('now');
            header(sprintf(
                'Server-Timing: %s;desc="%s";dur=%s',
                $key,
                $name,
                intval($end->format('Uv')) - intval($start->format('Uv')) //Measured in milliseconds
            ), false);
        }

        /**
         * Check if Cache Should Be Cleared
         *
         * @param bool $logged_in Refresh cache if user is logged in, otherwise respond to queries only.
         * @return bool True if cache should be refreshed. False otherwise.
         */
        public static function refresh_cache($logged_in = true)
        {
            global $wp_customize;
            $soft_check = isset($_GET['nocache']) || (isset($_GET['avoid-minify']) && $_GET['avoid-minify'] == 'true') || isset($wp_customize);
            if (!$soft_check && $logged_in) {
                return is_user_logged_in();
            }
            return $soft_check;
        }

        /**
         * Get Caching Prefix
         *
         * Prefixes are unique to language, group, and post ID
         *
         * @param string $group Optional
         * @param int $post_id Optional
         *
         * @return string A sanitized cache key prefix
         */
        private static function cache_prefix($group = '', $post_id = '')
        {
            $prefix = 'locale-' . get_user_locale(0); //Get locale based on user, pass 0 so it looks them up
            if ($group) {
                $prefix .= '_' . $group;
            }
            if ($post_id) {
                $prefix .= '_post-' . $post_id;
            }
            return sanitize_key($prefix);
        }


        /**
         * Cache Data
         *
         * @param string $key The key to use. This function will sanitize it for the database. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
         * @param mixed $value The data to be cached
         * @param int $expire Optional. The number of hours to cache before expiration. Default is 4
         * @param string $type Optional. The type of caching method. Can be `transient`, `cache`, or `both`. Default is `both`
         * @param string $group Optional. The grouping for this cache. Default is an empty string. The value is always automatically prepended with language data.
         * @param int|false $post_id Optional. If this data is associted with a post, pass the post ID or false to save it to the post's meta data
         * @param bool $generated_at Optional. If you want to display a timestamp for when this item was cached, set to true. Default is false. $value must be a string.
         *
         * @return mixed $value
         */
        public static function set_cache($key, $value, $expire = 4, $type = 'both', $group = '', $post_id = '', $generated_at = false)
        {
            if ($key) {
                $prefix = self::cache_prefix($group, $post_id);
                $key = sanitize_key($key);
                $transient_key = $prefix . '_' . $key;
                if ($generated_at && is_string($value)) {
                    $value = sprintf(
                        '<!-- Cache generated for [%s] on %s to expire in %s hour(s) -->',
                        $transient_key,
                        date('n/j/Y H:i:s:u T'),
                        $expire
                    ) . $value;
                }
                if ($type !== 'transient') {
                    //Documentation: https://developer.wordpress.org/reference/functions/wp_cache_set/
                    wp_cache_set($key, $value, $prefix, $expire * HOUR_IN_SECONDS);
                }
                if ($type !== 'cache') {
                    //Documentation: https://developer.wordpress.org/reference/functions/set_transient/
                    set_transient($transient_key, $value, $expire * HOUR_IN_SECONDS);
                }
            }
            return $value;
        }



        /**
         * Get Cached Data
         *
         * @param string $key The key to use. This function will sanitize it for the database
         * @param string $type Optional. The type of caching method. Can be `transient`, `cache`, or `both`. Default is `both`
         * @param string $group Optional. The grouping for this cache. Default is an empty string. The value is always automatically prepended with language data.
         * @param int $post_id Optional. If this data is associted with a post, pass the post ID or false to attempt retrieval from the post's meta data
         *
         * @return mixed|false Value of data, false on failure to retrieve contents.
         */
        public static function get_cache($key, $type = 'both', $group = '', $post_id = '')
        {
            $value = false;
            if ($key) {
                $prefix = self::cache_prefix($group, $post_id);
                $key = sanitize_key($key);
                //If type is `cache` or `both, attempt to get the cache first
                if ($type !== 'transient' && $value === false) {
                    //Documentation: https://developer.wordpress.org/reference/functions/wp_cache_get/
                    $value = wp_cache_get(
                        $key, //(int|string) (Required) The key under which the cache contents are stored.
                        $prefix //(string) (Optional) Where the cache contents are grouped. Default value: ''
                        //false, //(bool) (Optional) Whether to force an update of the local cache from the persistent cache. Default value: false
                        //null//(bool) (Optional) Whether the key was found in the cache (passed by reference). Disambiguates a return of false, a storable value. Default value: null
                    ); //(mixed|false) The cache contents on success, false on failure to retrieve contents.
                }
                if ($type !== 'cache' && $value === false) {
                    //Documentation: https://developer.wordpress.org/reference/functions/get_transient/
                    $value = get_transient($prefix . '_' . $key); //(mixed|false) Value of transient, false on failure to retrieve contents.
                }
            }
            return $value;
        }

        /**
         * Parse Content for Specified Shortcodes
         *
         * Parse a string of content for a specific shortcode to retrieve its attributes and content
         *
         * @param string $content The content to parse
         * @param string $tag The shortcode tag
         * @param bool $closing_tag If true, it will look for closing tags. If false, it assumes the shortcode is self-closing. Default is false.
         * @return array An associative array with `tag` (string) and `shortcodes` (sequential array). If shortcodes were discovered, each one has keys for `atts` (associative array) and `content` (string)
         */
        public static function discover_shortcodes($content, $tag, $closing_tag = false)
        {
            $return = array(
                'tag' => $tag,
                'shortcodes' => array()
            );
            $start_tag = '[' . $tag . ' '; //Opens the start tag, assumes there are attributes
            $start_tag_end = ']'; //Closes the start tag
            if ($closing_tag) {
                //If this is NOT a self-closing tag, it will have a closing tag after content
                $closing_tag = '[/' . $tag . ']';
            }
            $original_content = $content;
            $start = strpos($content, $start_tag);
            while ($start !== false) {
                $shortcode = array(
                    'atts' => array(),
                    'content' => ''
                );
                //Parse for shortcode attributes
                $atts_str = trim(str_replace(
                    array($start_tag, $start_tag_end),
                    '',
                    substr($content, $start, strpos($content, $start_tag_end, $start))
                ));
                if (strpos($atts_str, '"') !== false) {
                    $atts = explode('" ', substr(
                        $atts_str,
                        0,
                        -1 //Clip off the last character, which is a double quote
                    ));
                    if (is_array($atts) && count($atts)) {
                        foreach ($atts as $att_str) {
                            $pair = explode('="', $att_str);
                            if (is_array($pair) && count($pair) > 1) {
                                //Validate & normalize the key
                                $key = is_string($pair[0]) ? trim($pair[0]) : '';
                                if (!empty($key)) {
                                    $shortcode['atts'][$key] = is_string($pair[1]) ? html_entity_decode($pair[1]) : $pair[1];
                                }
                            }
                        }
                    }
                }
                $content_end = strpos($content, $start_tag_end, $start) + strlen($start_tag_end); //End after the self-closing start tag
                if ($closing_tag) {
                    $closing_tag_pos = strpos($content, $closing_tag, $content_end);
                    if ($closing_tag_pos !== false) {
                        $shortcode['content'] = substr($content, $content_end, $closing_tag_pos); //Get the content between the opening and closing tag
                        $content_end = strpos($content, $start_tag_end, $closing_tag_pos) + strlen($closing_tag); //End after the closing tag
                    }
                }
                //If anything was discovered in this shortcode, add it to the return object
                if (count($shortcode['atts']) || !empty($shortcode['content'])) {
                    $return['shortcodes'][] = $shortcode;
                }
                $content = substr($content, $content_end); //Remove this shortcode from the content to continue parsing in the do-while
                $start = $content_end;
            }

            //Now do it again, but assuming there are no attributes, just totally self closing
            $start_tag = '[' . $tag . ']'; //A single open tag with no attributes
            $content = $original_content; //Reset the content back to it's original state
            $start = strpos($content, $start_tag);
            while ($start !== false) {
                $shortcode = array(
                    'atts' => array(),
                    'content' => ''
                );
                $content_end = strlen($start_tag); //End after the self-closing start tag
                if ($closing_tag) {
                    $closing_tag_pos = strpos($content, $closing_tag, $content_end);
                    if ($closing_tag_pos !== false) {
                        $shortcode['content'] = substr($content, $content_end, $closing_tag_pos); //Get the content between the opening and closing tag
                        $content_end = $content_end + strlen($shortcode['content']) + strlen($closing_tag); //End after the closing tag
                    }
                }
                //If anything was discovered in this shortcode, add it to the return object
                if (!empty($shortcode['content'])) {
                    $return['shortcodes'][] = $shortcode;
                }
                $content = substr($content, $content_end); //Remove this shortcode from the content to continue parsing in the while
                $start = $content_end;
            }

            return $return;
        }

        /**
         * Optionally Load Resource
         *
         * Usually from within a shortcode, enqueue a script or stylesheet if it hasn't been already.
         *
         * @param string $handle The resource handle.
         * @param string $url Optional. An absolute URL to the resource to load. If excluded, can only enqueue previously registered resources.
         * @param array $dependencies Optional. An array of handles that the resource depends on.
         * @param string|bool $version Optional. The version, if any. Default is `false` to exclude a version.
         * @param bool|string $media_or_footer Optional. For stylesheets, this parameter is expecting a media type. Default is `all`. For scripts, this parameter is expecting a boolean value for whether to place script in the footer. Default is true.
         * @param array $localized_data Optional. Additional data to localize a script.
         * @return string The status (if debugging is enabled)
         */
        public static function optionally_load_resource($handle, $url = '', $type = 'style', $dependencies = array(), $version = false, $media_or_footer = '', $localized_data = array())
        {
            $result = '';
            if ($type == 'style') {
                if (wp_style_is($handle, 'registered') && !wp_style_is($handle, 'queue')) {
                    wp_enqueue_style($handle);
                    if (WP_DEBUG) {
                        $result = sprintf('Stylesheet [%s] is already registered, just enqueue it', $handle);
                    }
                } elseif (!wp_style_is($handle, 'registered') && !wp_style_is($handle, 'queue')) {
                    if ($url) {
                        wp_enqueue_style($handle, $url, $dependencies, $version, $media_or_footer ? $media_or_footer : 'all');
                        if (WP_DEBUG) {
                            $result = sprintf('Stylesheet [%s] is NOT enqueued, set it [%s]', $handle, $url);
                        }
                    } elseif (WP_DEBUG) {
                        $result = sprintf('Stylesheet [%s] is NOT enqueued and a URL was not provided to set it', $handle);
                    }
                } elseif (WP_DEBUG) {
                    $result = sprintf('Stylesheet [%s] is already registered and enqueued, do nothing', $handle);
                }
            } elseif ($type == 'script') {
                if (wp_script_is($handle, 'registered') && !wp_script_is($handle, 'queue')) {
                    wp_enqueue_script($handle);
                    if (WP_DEBUG) {
                        $result = sprintf('Script [%s] is already registered, just enqueue it', $handle);
                    }
                } elseif (!wp_script_is($handle, 'registered') && !wp_script_is($handle, 'queue')) {
                    if ($url) {
                        wp_enqueue_script($handle, $url, $dependencies, $version, is_bool($media_or_footer) ? $media_or_footer : true); //Place in footer
                        if (WP_DEBUG) {
                            $result = sprintf('Script [%s] is NOT registored OR enqueued, set it [%s]', $handle, $url);
                        }
                    } elseif (WP_DEBUG) {
                        $result = sprintf('Script [%s] is NOT enqueued and a URL was not provided to set it', $handle);
                    }
                } elseif (WP_DEBUG) {
                    $result = sprintf('Script [%s] is already registered and enqueued, do nothing', $handle);
                }
                if (count($localized_data)) {
                    global $wp_scripts;
                    $data = $wp_scripts->get_data($handle, 'data');
                    if (!empty($data)) {
                        //Localize it
                        wp_localize_script($handle, 'aurise_video_obj', $localized_data);
                    }
                }
            }
            return $result;
        }

        /**
         * Display an object for debugging purposes
         *
         * Uses `var_dump()` within an output buffer and surrounds it in a `<pre></pre>` HTML element.
         *
         * @param mixed The variable to dump.
         * @param bool $visible Optional. If true, the `<pre>` element will be visible. If false, it will be hidden. Default is true.
         * @param bool $echo Optional. If true, the content in the output buffer will be echoed. If false, it will be only be returned.
         * @param string $title Optional. An additional title to display before the dumped variable.
         * @param bool $wrapper Optional. If true, the content is wrapped in HTML markup. Otherwise, it is not. Default is true. $visible does not matter if there is no wrapper.
         * @return string The content of the output buffer of the variable dump.
         */
        public static function var_dump($obj, $visible = true, $echo = true, $title = '', $wrapper = true)
        {
            if (WP_DEBUG && WP_DEBUG_DISPLAY) {
                ob_start();
                if ($title) {
                    if ($wrapper) {
                        printf('<p style="display: %s;"><strong>%s</strong></p>', $visible ? 'block' : 'none !important', $title);
                    } else {
                        echo ($title . PHP_EOL);
                    }
                }
                if ($wrapper) {
                    printf('<pre style="display: %s;" data-title="%s">', $visible ? 'block' : 'none !important', $title);
                } else {
                    echo (PHP_EOL);
                }
                var_dump($obj);
                if ($wrapper) {
                    echo ('</pre>');
                } else {
                    echo (PHP_EOL);
                }
                $output = ob_get_contents();
                ob_end_clean();
                if ($echo) {
                    echo ($output);
                }
                return $output;
            }
            return '';
        }

        /**
         * Write a message to debug.log
         *
         * Uses `error_log`
         *
         * @param mixed The variable to dump.
         */
        public static function debug_log($obj, $title = '')
        {
            if (WP_DEBUG && WP_DEBUG_LOG) {
                if ($title) {
                    error_log($title);
                }
                if (is_array($obj) || is_object($obj)) {
                    error_log(print_r($obj, true));
                } else {
                    error_log($obj);
                }
            }
        }

        // /**
        //  * Test to see if executing an AJAX call specific to the WP Migrate DB family of plugins.
        //  *
        //  * @return bool
        //  */
        // public static function is_ajax()
        // {
        //         // must be doing AJAX the WordPress way
        //         if (!defined('DOING_AJAX') || !DOING_AJAX) {
        //                 return false;
        //         }

        //         // must be one of our actions -- e.g. core plugin (wpmdb_*), media files (wpmdbmf_*)
        //         if (!isset($_POST['action']) || 0 !== strpos($_POST['action'], 'wpmdb')) {
        //                 return false;
        //         }

        //         // must be on blog #1 (first site) if multisite
        //         if (is_multisite() && 1 != get_current_site()->id) {
        //                 return false;
        //         }

        //         return true;
        // }

        // /**
        //  * Checks if another version of WPMDB(Pro) is active and deactivates it.
        //  * To be hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
        //  *
        //  * @param string $plugin
        //  *
        //  */
        // public static function deactivate_other_instances($plugin)
        // {
        //         if (!in_array(basename($plugin), array('wp-migrate-db-pro.php', 'wp-migrate-db.php'))) {
        //                 return;
        //         }

        //         $plugin_to_deactivate  = 'wp-migrate-db.php';
        //         $deactivated_notice_id = '1';
        //         if (basename($plugin) == $plugin_to_deactivate) {
        //                 $plugin_to_deactivate  = 'wp-migrate-db-pro.php';
        //                 $deactivated_notice_id = '2';
        //         }

        //         if (is_multisite()) {
        //                 $active_plugins = (array) get_site_option('active_sitewide_plugins', array());
        //                 $active_plugins = array_keys($active_plugins);
        //         } else {
        //                 $active_plugins = (array) get_option('active_plugins', array());
        //         }

        //         foreach ($active_plugins as $basename) {
        //                 if (false !== strpos($basename, $plugin_to_deactivate)) {
        //                         set_transient('wp_migrate_db_deactivated_notice_id', $deactivated_notice_id, 1 * HOUR_IN_SECONDS);
        //                         deactivate_plugins($basename);

        //                         return;
        //                 }
        //         }
        // }

        // /**
        //  * Return unserialized object or array
        //  *
        //  * @param string    $serialized_string  Serialized string.
        //  * @param string    $method             The name of the caller method.
        //  *
        //  * @return mixed, false on failure
        //  */
        // public static function unserialize($serialized_string, $method = '')
        // {
        //         if (!is_serialized($serialized_string)) {
        //                 return false;
        //         }

        //         $serialized_string   = trim($serialized_string);
        //         $unserialized_string = @unserialize($serialized_string);

        //         if (false === $unserialized_string && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        //                 $scope = $method ? sprintf(__('Scope: %s().', 'wp-migrate-db'), $method) : false;
        //                 $error = sprintf(__('WPMDB Error: Data cannot be unserialized. %s', 'wp-migrate-db'), $scope);
        //                 error_log($error);
        //         }

        //         return $unserialized_string;
        // }

        // /**
        //  * Use wp_unslash if available, otherwise fall back to stripslashes_deep
        //  *
        //  * @param string|array $arg
        //  *
        //  * @return string|array
        //  */
        // public static function safe_wp_unslash($arg)
        // {
        //         if (function_exists('wp_unslash')) {
        //                 return wp_unslash($arg);
        //         } else {
        //                 return stripslashes_deep($arg);
        //         }
        // }
        // /**
        //  * Display Radio Button HTML
        //  *
        //  * @since 1.0.0
        //  * @param array $args An associatve array with keys for `name` (string), `value`
        //  * (string of `true` or `false`), `yes` (string), `no` (string), `label`, and
        //  * `reverse` (bool)
        //  * @return string HTML for the checkbox switch
        //  */
        // private function get_radio_group($args)
        // {
        //         $value = esc_attr($args['value']);
        //         $html = sprintf('<span class="radio-group"><strong class="radio-group-label">%s</strong>', esc_attr($args['label']));
        //         if (is_array($args['options']) && count($args['options'])) {
        //                 foreach ($args['options'] as $option_value => $option) {
        //                         $html .= sprintf(
        //                                 '<label><input type="radio" name="%s" value="%s"%s /><span class="label">%s</span></label>',
        //                                 esc_attr($args['name']),
        //                                 esc_attr($option_value),
        //                                 $option_value == $value ? ' selected' : '',
        //                                 esc_html($option['label'])
        //                         );
        //                 }
        //         }
        //         $html .= '</span>';
        //         return $html;
        // }

        // /**
        //  * Display Dropdown HTML
        //  *
        //  * @since 1.0.0
        //  * @param array $args An associatve array with keys for `name` (string), `value`
        //  * (string of `true` or `false`), `yes` (string), `no` (string), and `label` (string)
        //  * @return string HTML for the checkbox switch
        //  */
        // private function get_dropdown($args)
        // {
        //         $value = esc_attr(strval($args['value']));
        //         $html = sprintf(
        //                 '<label for="%1$s%2$s">%3$s</label><select id="%1$s%2$s" name="%1$s%2$s">',
        //                 esc_attr($this->settings['prefix']), // 1 - Setting Prefix
        //                 esc_attr($args['name']), //2 - Input name
        //                 esc_html($args['label']), //3 - Input Label
        //         );
        //         if (is_array($args['options']) && count($args['options'])) {
        //                 foreach ($args['options'] as $option_value => $option) {
        //                         $option_value = esc_attr(strval($option_value));
        //                         $html .= sprintf(
        //                                 '<option value="%s"%s>%s</option>',
        //                                 $option_value,
        //                                 $option_value === $value ? ' selected' : '',
        //                                 esc_html($option['label'])
        //                         );
        //                 }
        //         } else {
        //                 $html .= sprintf('<option value="global">%s</option>', __('Use Global Setting', $this->settings['slug']));
        //         }
        //         $html .= '</select>';
        //         return $html;
        // }
    }
}
