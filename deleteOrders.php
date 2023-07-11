<?php
/**
 * Plugin Name: Delete Orders Plugin
 * Plugin URI: https://rckflr.party/
 * Description: Plugin to delete WooCommerce orders within a specific date range.
 * Version: 1.0
 * Author: Mauricio Perera
 * Author URI: https://www.linkedin.com/in/mauricioperera/
 * Donate link: https://www.buymeacoffee.com/rckflr
 */

// Add the plugin menu to the admin panel
add_action('admin_menu', 'delete_orders_plugin_menu');
function delete_orders_plugin_menu() {
    add_menu_page(
        __('Delete Orders Plugin', 'delete-orders-plugin'), // Internationalization
        __('Delete Orders', 'delete-orders-plugin'), // Internationalization
        'manage_options',
        'delete-orders-plugin',
        'delete_orders_plugin_page',
        'dashicons-trash'
    );
}

// Plugin configuration page
function delete_orders_plugin_page() {
    // Check if the current user has the required permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'delete-orders-plugin')); // Internationalization
    }
    
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verify the nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_orders_nonce')) {
            die('Invalid nonce');
        }
        
        // Process the delete request
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        
        // Check if the dates are valid and run the SQL queries
        if (validate_date($end_date)) {
            delete_orders_within_date_range($start_date, $end_date);
        } else {
            echo '<div class="notice notice-error"><p>' . __('Invalid date format. Please enter dates in the format YYYY-MM-DD.', 'delete-orders-plugin') . '</p></div>'; // Internationalization
        }
    }
    
    // Display the form to enter the date range
    echo '<div class="wrap">';
    echo '<h1>' . __('Delete Orders', 'delete-orders-plugin') . '</h1>'; // Internationalization
    echo '<a href="https://www.buymeacoffee.com/rckflr" target="_blank"><img
    src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee"
    style="height: 60px !important;width: 217px !important;"></a>
<p>' . __('If you like this plugin and want to support my work, please consider buying me a coffee. Thank you!', 'delete-orders-plugin') . '</p>'; // Internationalization
    echo '<form method="post">';
    wp_nonce_field('delete_orders_nonce');
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="start_date">' . __('Start Date:', 'delete-orders-plugin') . '</label></th>'; // Internationalization
    echo '<td><input type="date" id="start_date" name="start_date" class="regular-text"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="end_date">' . __('End Date:', 'delete-orders-plugin') . '</label></th>'; // Internationalization
    echo '<td><input type="date" id="end_date" name="end_date" class="regular-text" required></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><input type="submit" value="' . __('Delete Orders', 'delete-orders-plugin') . '" class="button button-primary"></p>'; // Internationalization
    echo '</form>';
    echo '</div>';
}

// Function to validate the date format
function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Function to delete orders within the specified date range
function delete_orders_within_date_range($start_date, $end_date) {
    $args = array(
        'limit' => -1,
        'date_before' => $end_date . ' 23:59:59',
        'return' => 'ids',
    );
    if (validate_date($start_date)) {
        $args['date_after'] = $start_date . ' 00:00:00';
    }
    $orders = wc_get_orders($args);
    foreach ($orders as $order_id) {
        wc_delete_order($order_id);
    }
    // Display a success message
    echo '<div class="notice notice-success"><p>' . __('Orders and related data within the specified date range have been deleted.', 'delete-orders-plugin') . '</p></div>'; // Internationalization
}
