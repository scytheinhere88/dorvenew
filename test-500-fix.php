<?php
/**
 * TEST FILE - Check if 500 error is fixed
 * Access: http://dorve.id/test-500-fix.php
 */

echo "<!DOCTYPE html><html><head><title>500 Error Test</title></head><body>";
echo "<h1>🔧 Testing 500 Error Fixes</h1>";
echo "<hr>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p style='color: green;'>✅ PHP version OK</p>";
} else {
    echo "<p style='color: orange;'>⚠️ PHP version old (need 7.4+)</p>";
}

// Test 2: Config file
echo "<h2>2. Config File</h2>";
try {
    require_once __DIR__ . '/config.php';
    echo "<p style='color: green;'>✅ config.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Config error: " . $e->getMessage() . "</p>";
}

// Test 3: Database connection
echo "<h2>3. Database Connection</h2>";
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT 1");
        echo "<p style='color: green;'>✅ Database connected</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ PDO not initialized</p>";
}

// Test 4: SEO Helper
echo "<h2>4. SEO Helper</h2>";
if (file_exists(__DIR__ . '/includes/seo-helper.php')) {
    try {
        require_once __DIR__ . '/includes/seo-helper.php';
        echo "<p style='color: green;'>✅ seo-helper.php loaded successfully</p>";
        
        // Test function
        if (function_exists('generateSEO')) {
            echo "<p style='color: green;'>✅ generateSEO() function exists</p>";
        } else {
            echo "<p style='color: red;'>❌ generateSEO() function not found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ SEO Helper error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ seo-helper.php file not found</p>";
}

// Test 5: Functions
echo "<h2>5. Required Functions</h2>";
$required_functions = ['getCartCount', 'getCanonicalUrl', 'formatPrice', 'isLoggedIn'];
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green;'>✅ $func() exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $func() missing</p>";
    }
}

// Test 6: Session
echo "<h2>6. Session</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ Session active</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Session not started</p>";
}

// Test 7: Header include
echo "<h2>7. Header Include Test</h2>";
ob_start();
try {
    include __DIR__ . '/includes/header.php';
    $header = ob_get_clean();
    if (strlen($header) > 100) {
        echo "<p style='color: green;'>✅ Header loaded successfully (" . strlen($header) . " bytes)</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Header loaded but seems short (" . strlen($header) . " bytes)</p>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Header error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✅ Summary</h2>";
echo "<p><strong>If all tests show ✅, your site should work now!</strong></p>";
echo "<p>Try accessing: <a href='/'>Homepage</a> | <a href='/debug-system.php'>Debug System</a></p>";
echo "</body></html>";
