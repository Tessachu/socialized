<?php

/**
 * Socialized Plugin
 *
 * @package AuRise\Plugin\Socialized
 * @copyright Copyright (c) 2022, AuRise Creative - support@aurisecreative.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * Plugin Name: Socialized
 * Plugin URI: https://aurisecreative.com/socialized/
 * Description: Add social media sharing buttons to your posts, pages, and custom post types that automatically track to a custom campaign with your Google Analytics!
 * Version: 3.0.3
 * Author: AuRise Creative
 * Author URI: https://aurisecreative.com/
 * License: GPL v3
 * Requires at least: 5.8
 * Requires PHP: 5.6.20
 * Text Domain: socialized
 * Domain Path: /languages
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define root file
defined('SOCIALIZED_FILE') || define('SOCIALIZED_FILE', __FILE__);

// Load the utilities class: AuRise\Plugin\Socialized\Utilities
require_once('inc/class-utilities.php');

// Load the main plugin class: AuRise\Plugin\Socialized\Socialized
require_once('inc/class-main.php');

/**
 * Returns the main instance of Socialized.
 *
 * @since  1.0.0
 * @return Socialized
 */
function socialized()
{
    return AuRise\Plugin\Socialized\Socialized::instance();
}

/**
 * The global instance of the main class
 *
 * Run once to initialise
 *
 * @var AuRiseVideo $aurise_video
 * @since 1.0.0
 */
global $socialized;
$socialized = socialized();//Run once to init

/* Purposely excluding the closing PHP tag to avoid "headers already sent" error */