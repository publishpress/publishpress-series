<?php

/**
 *      captures plugin activation errors for debugging
 *
 *      @return void
 */
function orgseries_plugin_activation_errors()
{
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        $errors = ob_get_contents();
        if (!empty($errors)) {
            update_option('orgseries_plugin_activation_errors', $errors);
        }
    }
}
add_action('activated_plugin', 'orgseries_plugin_activation_errors');



/**
 *      orgseries_debug loaded
 *
 *      @return         void
 */
function orgseries_debug_loaded()
{
    if ($activation_errors = get_option('orgseries_plugin_activation_errors', false)) {
        echo '<div class="error"><p>' . esc_html($activation_errors) . '</p></div>';
        update_option('orgseries_plugin_activation_errors', false);
    }
}
add_action('admin_notices', 'orgseries_debug_loaded', 5);
