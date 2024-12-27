<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the search form.
 *
 * @return string HTML of the search form.
 */
function render_search_form() {
    // Start output buffering to capture the generated HTML
    ob_start(); ?>

    <!-- Search Form -->
    <form method="post" id="search-term-input" style="margin-bottom: 20px;">
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
    // Capture the buffer content and return it
    return ob_get_clean();
}
