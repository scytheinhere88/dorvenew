<?php
/**
 * Test Webhook Endpoint
 * POST /admin/integration/test-webhook.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Prepare test payload
    $testPayload = [
        'event' => 'test.webhook',
        'data' => [
            'id' => 'test_' . time(),
            'message' => 'This is a local webhook test from admin panel',
            'timestamp' => date('c')
        ]
    ];
    
    // Get base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $webhookUrl = $protocol . '://' . $host . '/api/biteship/webhook.php';
    
    // Send test request to webhook
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Biteship-Webhook-Test'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }
    
    if ($httpCode === 200 && (trim($response) === 'ok' || trim($response) === 'error')) {
        // Update settings
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('biteship_webhook_test_status', 'ok'),
                   ('biteship_webhook_test_time', NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Webhook endpoint is active and responding correctly!',
            'details' => 'HTTP ' . $httpCode . ' - Webhook URL: ' . $webhookUrl,
            'response' => $response,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Update settings
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('biteship_webhook_test_status', 'failed'),
                   ('biteship_webhook_test_time', NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute();
        
        throw new Exception('Webhook returned HTTP ' . $httpCode . ' - Expected 200');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '❌ Webhook endpoint failed',
        'error' => $e->getMessage(),
        'details' => 'Check routing, file permissions, and PHP error logs',
        'webhook_url' => $webhookUrl ?? 'Unknown'
    ]);
}
