<?php

/**
 * Plugin Name:       WWJ Zendesk Guide
 * Plugin URI:        https://example.com/
 * Description:       Connects to the Zendesk Guide API to build a help center in WordPress.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            Jamie Smith
 * Author URI:        https://worldwidejamie.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       wwj-zdguide
 *
 * @package Wwj_Zdguide
 */

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Define plugin constants.
 */
define('WWJ_ZDGUIDE_VERSION', '0.1.0');
define('WWJ_ZDGUIDE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WWJ_ZDGUIDE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load the main plugin class.
 */
require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Plugin.php';

/**
 * Initialize the plugin.
 *
 * @return WwjZdguide\Plugin
 */
function wwj_zdguide(): WwjZdguide\Plugin
{
	return WwjZdguide\Plugin::instance();
}

// Initialize the plugin.
wwj_zdguide();

/**
 * Plugin activation hook.
 */
register_activation_hook(__FILE__, array('WwjZdguide\Plugin', 'activate'));

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook(__FILE__, array('WwjZdguide\Plugin', 'deactivate'));
