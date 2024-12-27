<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the results section.
 *
 * @param string $error_message Error message to display, if any.
 * @param string $success_message Success message to display, if any.
 * @param array $results Array of video results to display.
 * @return string HTML of the results section.
 */
function render_results_section($error_message = '', $success_message = '', $results = []) {
    // Start output buffering to capture the generated HTML
    ob_start(); ?>

    <!-- Results Section -->
    <div id="researcher-results" style="background-color: #f5f5f5; padding: 20px; border: 1px solid #ddd;">
        <?php
        // Display error messages if they exist
        if (!empty($error_message)) {
            echo "<div style='color: red; margin-bottom: 20px;'><p>" . esc_html($error_message) . "</p></div>";
        }

        // Display success messages and results if they exist
        if (!empty($success_message)) {
            echo "<h3 style='margin-bottom: 20px;'>" . esc_html($success_message) . "</h3>";
            
            if (!empty($results) && is_array($results)) {
                echo "<ul style='list-style-type: none; padding: 0;'>";
                foreach ($results as $video) {
                    // Verify that the necessary fields exist
                    if (isset($video['title'], $video['url'], $video['description'])) {
                        echo "<li style='margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #fff;'>";
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
        ?>
    </div>

    <?php
    // Capture the buffer content and return it
    return ob_get_clean();
}
