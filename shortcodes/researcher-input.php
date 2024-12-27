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
    $python_path = '/Users/nicolaspavez/Local Sites/researcherplugin/app/public/wp-content/plugins/researcher/shortcodes/researcher-env/bin/python';

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
                    $command = 'YOUTUBE_API_KEY=' . escapeshellarg($api_key) . ' ' . escapeshellcmd("\"$python_path\"") . ' ' . escapeshellarg($python_script_path) . ' ' . escapeshellarg($concepts) . ' 2>&1';

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

    // Start output buffering to capture the generated HTML
    ob_start(); ?>

    <!-- Search Form -->
    <form method="post">
        <?php 
            // Nonce field for security
            wp_nonce_field('researcher_form_nonce', 'researcher_nonce'); 
        ?>
        <label for="researcher_concepts">Enter one or more concepts:</label><br>
        <input 
            type="text" 
            id="researcher_concepts" 
            name="researcher_concepts" 
            placeholder="E.g.: Historia del Salitre" 
            required
            value="<?php echo isset($_POST['researcher_concepts']) ? esc_attr($_POST['researcher_concepts']) : ''; ?>"
        ><br><br>
        <input 
            type="submit" 
            name="researcher_submit" 
            value="Search"
            style="padding: 10px 20px; background-color: #0073aa; color: #fff; border: none; cursor: pointer;"
        >
    </form>

    <?php
    // Display error messages if they exist
    if (!empty($error_message)) {
        echo "<div style='color: red; margin-top: 20px;'><p>" . esc_html($error_message) . "</p></div>";
    }

    // Display success messages and results if they exist
    if (!empty($success_message)) {
        echo "<h3 style='margin-top: 20px;'>" . esc_html($success_message) . "</h3>";
        
        if (!empty($results) && is_array($results)) {
            echo "<ul style='list-style-type: none; padding: 0;'>";
            foreach ($results as $video) {
                // Verify that the necessary fields exist
                if (isset($video['title'], $video['url'], $video['description'])) {
                    echo "<li style='margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
                    echo "<a href='" . esc_url($video['url']) . "' target='_blank' style='font-size: 18px; font-weight: bold; color: #0073aa; text-decoration: none;'>" . esc_html($video['title']) . "</a>";
                    echo "<p style='margin-top: 5px;'>" . esc_html($video['description']) . "</p>";
                    echo "</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p>No results found.</p>";
        }
    }
    
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
