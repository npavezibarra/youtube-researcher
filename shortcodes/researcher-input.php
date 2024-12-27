<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate the search form and handle the search logic.
 *
 * @return string HTML of the form and results.
 */
function researcher_input_form() {
    // Define the path to the Python script
    $python_script_path = plugin_dir_path(__FILE__) . '../python/youtube_search.py';

    // Define the full path to the Python executable
    $python_path = plugin_dir_path(__FILE__) . 'researcher-env/bin/python';

    // Initialize variables for error and success messages and results
    $error_message   = '';
    $success_message = '';
    $results         = [];

    // Process the form submission
    if (isset($_POST['researcher_submit'])) {
        // Validate the nonce for security
        if (!isset($_POST['researcher_nonce']) || !wp_verify_nonce($_POST['researcher_nonce'], 'researcher_form_nonce')) {
            $error_message = 'Security error. Please try again.';
        } else {
            // Sanitize the search term
            $concepts = sanitize_text_field($_POST['researcher_concepts']);

            // Check if the search term is empty
            if (empty($concepts)) {
                $error_message = 'Please enter one or more concepts to search.';
            } else {
                // Retrieve the API key from the database
                $api_key = get_option('researcher_youtube_api_key', '');

                // Check if the API key is set
                if (empty($api_key)) {
                    $error_message = 'YouTube API Key not configured. Please configure it in the plugin settings.';
                } else {
                    // Add YOUTUBE_API_KEY to the environment before executing the command
                    $command = 'YOUTUBE_API_KEY=' . escapeshellarg($api_key) . ' ' .
                        escapeshellcmd("\"$python_path\"") . ' ' .
                        escapeshellarg($python_script_path) . ' ' .
                        escapeshellarg($concepts) . ' 2>&1';

                    // Execute the command and capture the output
                    $output = shell_exec($command);

                    // Log the raw output for debugging
                    error_log("Python script output: " . $output);

                    // Decode the JSON output
                    $decoded_json = json_decode($output, true);

                    // Handle possible JSON errors
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $error_message = "Error decoding JSON: " . json_last_error_msg();
                    } elseif (isset($decoded_json['error'])) {
                        // Handle errors returned by the Python script
                        $error_message = "Python script error: " . esc_html($decoded_json['error']);
                    } elseif (isset($decoded_json['videos']) && is_array($decoded_json['videos'])) {
                        // Assign the search results
                        $results = $decoded_json['videos'];
                        $success_message = "Search results for: " . esc_html($concepts);
                    } else {
                        // Handle unexpected JSON formats
                        $error_message = "Unexpected JSON format.";
                    }
                }
            }
        }
    }

    // Include the separate components for the form, filters, and results
    include_once plugin_dir_path(__FILE__) . 'search-form.php';
    include_once plugin_dir_path(__FILE__) . 'filters.php';
    include_once plugin_dir_path(__FILE__) . 'results.php';

    // Start output buffering to capture the full layout
    ob_start(); ?>
    <div style="display: flex; gap: 20px;">
        <div style="flex: 1; max-width: 30%;">
            <?php 
            // Render the search form
            echo render_search_form();

            // Render the filters section
            echo render_filters_section();
            ?>
        </div>
        <div style="flex: 2;" id="researcher-results">
            <?php
            // Render the results section
            echo render_results_section($error_message, $success_message, $results);
            ?>
        </div>
    </div>
    <?php
    // Capture the buffer content and return it
    return ob_get_clean();
}

/**
 * Register the shortcode.
 */
function researcher_register_shortcode() {
    add_shortcode('researcher_input', 'researcher_input_form');
}
add_action('init', 'researcher_register_shortcode');
