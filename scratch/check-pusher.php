<?php

require __DIR__ . '/../vendor/autoload.php';

$app_id = '2147234';
$app_key = 'ad87669cf5e09ab05339';
$app_secret = '5490db5dfd6f0be111e0';
$app_cluster = 'ap1';

$pusher = new Pusher\Pusher($app_key, $app_secret, $app_id, [
    'cluster' => $app_cluster,
    'useTLS' => true
]);

echo "Attempting to trigger test event...\n";

try {
    $response = $pusher->trigger('test-channel', 'test-event', ['message' => 'hello world']);
    echo "Pusher response: " . json_encode($response) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
