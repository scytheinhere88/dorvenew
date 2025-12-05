<?php
/**
 * Direct Database Restoration
 * Simple & Direct approach without complex parsing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(600); // 10 minutes

// Load config
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Direct Database Restore</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Direct Database Restore</h1>

<?php

if (isset($_GET['run'])) {
    echo "<h2>🚀 Executing Restoration...</h2>";
    
    $sqlFile = __DIR__ . '/COMPLETE-DATABASE-RESTORE-V2.sql';
    
    if (!file_exists($sqlFile)) {
        echo "<p class='error'>❌ SQL file not found: $sqlFile</p>";
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        echo "<p class='error'>❌ Failed to read SQL file</p>";
        exit;
    }
    
    echo "<p class='info'>📄 SQL File loaded: " . strlen($sql) . " bytes</p>";
    
    // Execute with mysqli for better compatibility
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p class='error'>❌ Connection failed: " . $mysqli->connect_error . "</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Connected to database: " . DB_NAME . "</p>";
    
    // Execute multi-query
    echo "<h3>Executing SQL...</h3>";
    echo "<pre>";
    
    $success = $mysqli->multi_query($sql);
    
    if ($success) {
        $count = 0;
        do {
            $count++;
            if ($result = $mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    print_r($row);
                }
                $result->free();
            }
            
            if ($mysqli->more_results()) {
                echo ".";
                if ($count % 50 == 0) echo "\n";
            }
            
        } while ($mysqli->next_result());
        
        echo "</pre>";
        
        if ($mysqli->errno) {
            echo "<p class='error'>⚠️ Last Error: " . $mysqli->error . "</p>";
        }
        
        echo "<h3 class='success'>✅ Restoration Complete!</h3>";
        
        // Verify tables
        $result = $mysqli->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "<p><strong>Tables created (" . count($tables) . "):</strong></p>";
        echo "<pre>" . implode("\n", $tables) . "</pre>";
        
        // Check admin users
        $result = $mysqli->query("SELECT id, name, email, role FROM users WHERE role = 'admin'");
        echo "<p><strong>Admin Users:</strong></p>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['name']} ({$row['email']})\n";
        }
        echo "</pre>";
        
        echo "<p class='success'>✅ <strong>Database ready to use!</strong></p>";
        echo "<p><a href='/admin/login.php' class='btn'>Login to Admin Panel</a></p>";
        
    } else {
        echo "</pre>";
        echo "<p class='error'>❌ Execution failed: " . $mysqli->error . "</p>";
    }
    
    $mysqli->close();
    
} else {
    ?>
    
    <h2>Ready to Restore</h2>
    <p>This will drop all existing tables and create fresh database.</p>
    
    <h3>Database Info:</h3>
    <ul>
        <li><strong>Host:</strong> <?php echo DB_HOST; ?></li>
        <li><strong>Database:</strong> <?php echo DB_NAME; ?></li>
        <li><strong>User:</strong> <?php echo DB_USER; ?></li>
    </ul>
    
    <h3>What will be created:</h3>
    <ul>
        <li>20 Tables (users, products, orders, etc.)</li>
        <li>2 Stored Procedures</li>
        <li>3 Triggers</li>
        <li>2 Views</li>
        <li>Sample data (categories, vouchers, admin users)</li>
    </ul>
    
    <form method="get">
        <button type="submit" name="run" value="1" class="btn" 
                onclick="return confirm('Are you sure? This will delete all existing data!')">
            🚀 Start Restoration
        </button>
    </form>
    
    <?php
}
?>

    </div>
</body>
</html>
