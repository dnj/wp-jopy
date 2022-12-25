<?php
/**
 * Plugin Name:       Wordpress Jopy Plugin
 * Plugin URI:        https://github.com/dnj/wp-jopy
 * Description:       Jopy is Geo based mirror which can help you if your server cannot reach wordpress.org
 * Version:           1.0.3
 * Author:            DNJ
 * Author URI:        https://github.com/dnj
 * Domain Path:       /languages
 * Requires PHP:      5.6.0.
 */
if (!defined('ABSPATH')) {
    exit; // Silence is golden.
}

require_once __DIR__.'/src/Jopy.php';

define('JOPY_PLUGIN_FILE', __FILE__);

$jopy = Jopy::getInstance();
$jopy->registerHooks();
