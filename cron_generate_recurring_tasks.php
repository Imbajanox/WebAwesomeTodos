<?php
/**
 * Cron Job Script for Generating Recurring Tasks
 * This script calls the API endpoint to generate recurring tasks automatically
 */

// Configuration - CHANGE THESE VALUES
$api_url = 'http://localhost/wa-todo/WebAwesomeTodos/api.php'; // Update with your actual domain
$secret_key = 'GENERATE_RECURRING_TASKS_SECRET_KEY'; // Must match the key in api.php
$log_file = __DIR__ . '/recurring_tasks.log'; // Log file location

// Build the API URL with secret key
$endpoint_url = $api_url . '?action=generate_recurring_tasks&secret_key=' . urlencode($secret_key);

// Function to log messages
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Execute the API call
log_message("Starting recurring task generation...");

$context = stream_context_create([
    'http' => [
        'timeout' => 30, // 30 seconds timeout
        'method' => 'GET',
        'header' => "User-Agent: RecurringTasksCron/1.0\r\n"
    ]
]);

$response = @file_get_contents($endpoint_url, false, $context);

if ($response === false) {
    $error = error_get_last();
    log_message("ERROR: Failed to call API endpoint. " . ($error['message'] ?? 'Unknown error'));
    exit(1);
}

// Parse the response
$response_data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_message("ERROR: Invalid JSON response from API. Raw response: " . $response);
    exit(1);
}

if (isset($response_data['error'])) {
    log_message("ERROR: API returned error: " . $response_data['error']);
    exit(1);
}

if (isset($response_data['success']) && $response_data['success']) {
    log_message("SUCCESS: " . $response_data['message']);
    log_message("Timestamp: " . ($response_data['timestamp'] ?? 'N/A'));
} else {
    log_message("WARNING: Unexpected API response: " . $response);
}

log_message("Recurring task generation completed.");
log_message("----------------------------------------");