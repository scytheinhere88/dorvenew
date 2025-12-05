<?php
/**
 * Test Biteship API Key
 * POST /admin/integration/test-biteship-api.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/BiteshipClient.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Initialize Biteship client
    $client = new BiteshipClient();
    
    // Test with a simple area search (lightweight endpoint)
    $result = $client->getAreas('Jakarta');
    
    if ($result['success']) {
        // Update settings with test status
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('biteship_api_test_status', 'connected'),
                   ('biteship_api_test_time', NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Biteship API key is valid and reachable!',
            'details' => 'Connection successful. Environment: ' . BiteshipConfig::get('environment'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Update settings with failed status
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('biteship_api_test_status', 'failed'),
                   ('biteship_api_test_time', NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute();
        
        throw new Exception($result['error'] ?? 'API connection failed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '❌ Biteship API key is invalid or environment is wrong',
        'error' => $e->getMessage(),
        'details' => 'Please check your API key and environment setting (sandbox/production)'
    ]);
}
