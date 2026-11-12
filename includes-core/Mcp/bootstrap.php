<?php

/** Optional private Series abilities; native services own domain operations. */

defined('ABSPATH') || exit;
if (!function_exists('wp_register_ability') || !function_exists('wp_register_ability_category') || defined('PPSERIES_MCP_LOADED')) {
    return;
}
define('PPSERIES_MCP_LOADED', true);
foreach (['Registry', 'Settings', 'Layouts', 'Content'] as $file) {
    require_once __DIR__ . '/' . $file . '.php';
}
add_action('wp_abilities_api_categories_init', static function () {
    if (!wp_has_ability_category('publishpress')) {
        wp_register_ability_category('publishpress', ['label' => __('PublishPress', 'organize-series'), 'description' => __('Actions provided by PublishPress plugins.', 'organize-series')]);
    }
});
add_action('wp_abilities_api_init', static function () {
    \PublishPress\Series\Mcp\Settings::register();
    \PublishPress\Series\Mcp\Layouts::register();
    \PublishPress\Series\Mcp\Content::register();
});
