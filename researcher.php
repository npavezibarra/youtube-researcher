<?php
/*
Plugin Name: Researcher
Plugin URI: https://example.com/researcher
Description: A plugin to search and display YouTube videos based on user input.
Version: 1.0
Author: Your Name
Author URI: https://example.com
License: GPL2
Text Domain: researcher
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define Plugin Constants
 */
define( 'RESEARCHER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RESEARCHER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RESEARCHER_VERSION', '1.0' );

/**
 * Include Shortcode Handler
 */
require_once RESEARCHER_PLUGIN_DIR . 'shortcodes/researcher-input.php';

/**
 * Activation Hook
 */
function researcher_activate() {
    // Actions to perform upon plugin activation, if any
    // For example, creating necessary files or setting default options
}
register_activation_hook( __FILE__, 'researcher_activate' );

/**
 * Deactivation Hook
 */
function researcher_deactivate() {
    // Actions to perform upon plugin deactivation, if any
    // For example, cleaning up temporary files or resetting options
}
register_deactivation_hook( __FILE__, 'researcher_deactivate' );

/**
 * Enqueue Scripts and Styles
 *
 * If your plugin requires additional CSS or JavaScript files, enqueue them here.
 * Currently, the plugin does not require external scripts or styles.
 */
function researcher_enqueue_scripts() {
    // Example: Enqueue a custom CSS file
    /*
    wp_enqueue_style(
        'researcher-styles',
        RESEARCHER_PLUGIN_URL . 'assets/css/researcher-styles.css',
        array(),
        RESEARCHER_VERSION
    );
    */

    // Example: Enqueue a custom JavaScript file
    /*
    wp_enqueue_script(
        'researcher-scripts',
        RESEARCHER_PLUGIN_URL . 'assets/js/researcher-scripts.js',
        array( 'jquery' ),
        RESEARCHER_VERSION,
        true
    );
    */
}
add_action( 'wp_enqueue_scripts', 'researcher_enqueue_scripts' );

/* ADMIN PAGE */

// Crear la página de administración del plugin
function researcher_add_admin_menu() {
    add_menu_page(
        'Researcher Settings',       // Título de la página
        'Researcher',                // Título del menú
        'manage_options',            // Capacidad requerida
        'researcher-settings',       // Slug del menú
        'researcher_settings_page',  // Función que renderiza la página
        'dashicons-admin-generic',   // Icono del menú
        100                          // Posición del menú
    );
}
add_action('admin_menu', 'researcher_add_admin_menu');

// Renderizar la página de ajustes
function researcher_settings_page() {
    // Verificar permisos
    if (!current_user_can('manage_options')) {
        return;
    }

    // Manejar el envío del formulario
    if (isset($_POST['researcher_save_settings'])) {
        // Verificar nonce para seguridad
        if (isset($_POST['researcher_nonce']) && wp_verify_nonce($_POST['researcher_nonce'], 'researcher_settings_nonce')) {
            // Sanitizar y guardar la clave API
            $youtube_api_key = sanitize_text_field($_POST['youtube_api_key']);
            update_option('researcher_youtube_api_key', $youtube_api_key);

            // Mostrar mensaje de éxito
            echo '<div class="updated"><p>API Key guardada exitosamente.</p></div>';
        } else {
            // Mostrar mensaje de error si el nonce no es válido
            echo '<div class="error"><p>Error de seguridad. Intenta nuevamente.</p></div>';
        }
    }

    // Obtener el valor actual de la clave API
    $current_api_key = get_option('researcher_youtube_api_key', '');

    // Renderizar el formulario
    ?>
    <div class="wrap">
        <h1>Researcher Settings</h1>
        <form method="post">
            <?php wp_nonce_field('researcher_settings_nonce', 'researcher_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="youtube_api_key">YouTube API Key:</label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            name="youtube_api_key" 
                            id="youtube_api_key" 
                            value="<?php echo esc_attr($current_api_key); ?>" 
                            class="regular-text" 
                        />
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="researcher_save_settings" id="submit" class="button button-primary" value="Guardar cambios">
            </p>
        </form>
    </div>
    <?php
}
/* ADMIN PAGE FINISH */

/**
 * Additional Plugin Functionalities
 *
 * If your plugin requires more functionalities, you can add them below.
 * For example, creating admin settings pages, handling AJAX requests, etc.
 */

// Example: Adding an Admin Settings Page (Optional)
/*
function researcher_add_admin_menu() {
    add_menu_page(
        'Researcher Settings',
        'Researcher',
        'manage_options',
        'researcher',
        'researcher_settings_page',
        'dashicons-search',
        80
    );
}
add_action( 'admin_menu', 'researcher_add_admin_menu' );

function researcher_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post" action="options.php">
            <?php
                settings_fields( 'researcher_options_group' );
                do_settings_sections( 'researcher' );
                submit_button();
            ?>
        </form>
    </div>
    <?php
}
*/

/**
 * Shortcode Registration
 *
 * The shortcode `[researcher_input]` is registered in the included `researcher-input.php` file.
 * No further action is needed here unless you have additional shortcodes.
 */

/**
 * Security and Best Practices
 *
 * - Ensure that your Python script (`youtube_search.py`) is not publicly accessible via the web.
 * - Regularly update your API keys and handle them securely using environment variables.
 * - Validate and sanitize all user inputs to prevent security vulnerabilities.
 */

?>
