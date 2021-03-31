<?php
/**
 * Plugin Name:     WP React Plugin
 * Description:     A simple plugin loading React app in WorePress admin area
 * Author:          Jitesh Jani
 * Text Domain:     wp-react-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wp_React_Plugin
 */

// Hook to create admin menu
add_action('admin_menu', 'wrp_setup_plugin_menu');
function wrp_setup_plugin_menu()
{
    add_menu_page('WP React Plugin Page', 'WP React Plugin', 'manage_options', 'wp-react-plugin', 'wrp_setup_plugin_init');
}

function wrp_setup_plugin_init()
{
    echo "<h1>Hello from React plugin!</h1><hr /><div id=\"root\"></div>";
}

// Setting hook to load React app files.
add_action('admin_enqueue_scripts', 'wrp_load_react_app');

// NOTE:
// To fix static assets loading, add/modify below line in react-app/package.jsonthe entry :
// `homepage`: `/wp-content/plugins/wp-create-react-app-concept/react-app/build`

/**
 * Load react app files in WordPress admin.
 *
 * @return bool|void
 */
function wrp_load_react_app($hook)
{

    if ('toplevel_page_wp-react-plugin' != $hook)
    {
        return;
    }

    // Setting path variables.
    $plugin_app_dir_url = plugin_dir_url(__FILE__) . 'react-app/';
    $react_app_build = $plugin_app_dir_url . 'build/';
    $manifest_url = $react_app_build . 'asset-manifest.json';

    // Request manifest file.
    $request = file_get_contents($manifest_url);

    // If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
    if (!$request) return false;

    // Convert json to php array.
    $files_data = json_decode($request);
    if ($files_data === null) return;

    if (!property_exists($files_data, 'entrypoints')) return false;

    // Get assets links.
    $assets_files = $files_data->entrypoints;

    $js_files = array_filter($assets_files, 'wrp_filter_js_files');
    $css_files = array_filter($assets_files, 'wrp_filter_css_files');

    // Load css files.
    foreach ($css_files as $index => $css_file)
    {
        wp_enqueue_style('react-plugin-' . $index, $react_app_build . $css_file);
    }

    // Load js files.
    foreach ($js_files as $index => $js_file)
    {
        wp_enqueue_script('react-plugin-' . $index, $react_app_build . $js_file, array() , 1, true);
    }

    // Variables for app use.
    // wp_localize_script('react-plugin-0', 'rpReactPlugin',
    // 	array('appSelector' => '#wpbody .wrap')
    // );
    
}

/**
 * Get js files from assets array.
 *
 * @param array $file_string
 *
 * @return bool
 */
function wrp_filter_js_files($file_string)
{
    return pathinfo($file_string, PATHINFO_EXTENSION) === 'js';
}

/**
 * Get css files from assets array.
 *
 * @param array $file_string
 *
 * @return bool
 */
function wrp_filter_css_files($file_string)
{
    return pathinfo($file_string, PATHINFO_EXTENSION) === 'css';
}

