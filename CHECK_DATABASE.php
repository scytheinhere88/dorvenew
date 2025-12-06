<?php
/**
 * DATABASE CHECKER - Check Required Tables & Fields
 * Run this file to see what needs to be created
 * Access: http://dorve.id/CHECK_DATABASE.php
 */

require_once __DIR__ . '/config.php';

$checks = [];
$missing = [];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Check - Dorve.id</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #1a1a1a; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .check-item { padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #ddd; }
        .check-item.ok { background: #e8f5e9; border-color: #4caf50; }
        .check-item.missing { background: #ffebee; border-color: #f44336; }
        .check-item.warning { background: #fff3e0; border-color: #ff9800; }
        .status { font-weight: bold; margin-bottom: 5px; }
        .status.ok { color: #4caf50; }
        .status.missing { color: #f44336; }
        .status.warning { color: #ff9800; }
        .detail { font-size: 14px; color: #666; }
        .sql-box { background: #f5f5f5; padding: 15px; border-radius: 4px; margin-top: 10px; font-family: monospace; font-size: 13px; overflow-x: auto; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; text-align: center; color: #666; }
        .summary { background: #e3f2fd; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .summary h2 { color: #1976d2; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Database Check - Dorve.id</h1>
    <p class='subtitle'>Checking required tables and fields for new features</p>
";

// Check 1: Banners table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'banners'");
    if ($stmt->rowCount() > 0) {
        $checks['banners_table'] = true;
        
        // Check banners columns
        $stmt = $pdo->query("DESCRIBE banners");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $required_columns = ['banner_type', 'title', 'subtitle', 'image_url', 'link_url', 'cta_text', 'is_active', 'display_order'];
        $missing_columns = array_diff($required_columns, $columns);
        
        if (empty($missing_columns)) {
            $checks['banners_columns'] = true;
        } else {
            $checks['banners_columns'] = false;
            $missing['banners_columns'] = $missing_columns;
        }
    } else {
        $checks['banners_table'] = false;
        $missing['banners_table'] = true;
    }
} catch (PDOException $e) {
    $checks['banners_table'] = false;
    $missing['banners_table'] = true;
}

// Check 2: Categories icon field
try {
    $stmt = $pdo->query("DESCRIBE categories");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('icon', $columns)) {
        $checks['categories_icon'] = true;
    } else {
        $checks['categories_icon'] = false;
        $missing['categories_icon'] = true;
    }
} catch (PDOException $e) {
    $checks['categories_icon'] = false;
    $missing['categories_icon'] = true;
}

// Check 3: Products featured fields
try {
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('is_featured', $columns) || in_array('is_best_seller', $columns)) {
        $checks['products_featured'] = true;
    } else {
        $checks['products_featured'] = false;
        $missing['products_featured'] = true;
    }
} catch (PDOException $e) {
    $checks['products_featured'] = false;
    $missing['products_featured'] = true;
}

// Display Results
$total_checks = count($checks);
$passed_checks = count(array_filter($checks));

echo "<div class='summary'>";
echo "<h2>Summary</h2>";
echo "<p><strong>Total Checks:</strong> $total_checks</p>";
echo "<p><strong>Passed:</strong> $passed_checks</p>";
echo "<p><strong>Failed:</strong> " . ($total_checks - $passed_checks) . "</p>";
echo "</div>";

// Banners Table
if ($checks['banners_table'] === true) {
    echo "<div class='check-item ok'>";
    echo "<div class='status ok'>✅ Banners Table - OK</div>";
    echo "<div class='detail'>Table 'banners' exists in database</div>";
    echo "</div>";
} else {
    echo "<div class='check-item missing'>";
    echo "<div class='status missing'>❌ Banners Table - MISSING</div>";
    echo "<div class='detail'>Table 'banners' does not exist. Need to create it.</div>";
    echo "<div class='sql-box'>";
    echo "CREATE TABLE banners (\n";
    echo "  id INT PRIMARY KEY AUTO_INCREMENT,\n";
    echo "  banner_type VARCHAR(50) NOT NULL COMMENT 'slider, popup, marquee',\n";
    echo "  title VARCHAR(255),\n";
    echo "  subtitle TEXT,\n";
    echo "  image_url VARCHAR(500),\n";
    echo "  link_url VARCHAR(500),\n";
    echo "  cta_text VARCHAR(100),\n";
    echo "  is_active TINYINT(1) DEFAULT 1,\n";
    echo "  display_order INT DEFAULT 0,\n";
    echo "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    echo "  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    echo "</div>";
    echo "</div>";
}

// Banners Columns
if (isset($checks['banners_columns'])) {
    if ($checks['banners_columns'] === true) {
        echo "<div class='check-item ok'>";
        echo "<div class='status ok'>✅ Banners Columns - OK</div>";
        echo "<div class='detail'>All required columns exist</div>";
        echo "</div>";
    } else {
        echo "<div class='check-item missing'>";
        echo "<div class='status missing'>❌ Banners Columns - MISSING</div>";
        echo "<div class='detail'>Missing columns: " . implode(', ', $missing['banners_columns']) . "</div>";
        echo "<div class='sql-box'>";
        foreach ($missing['banners_columns'] as $col) {
            if ($col === 'banner_type') echo "ALTER TABLE banners ADD COLUMN banner_type VARCHAR(50) NOT NULL COMMENT 'slider, popup, marquee';\n";
            if ($col === 'title') echo "ALTER TABLE banners ADD COLUMN title VARCHAR(255);\n";
            if ($col === 'subtitle') echo "ALTER TABLE banners ADD COLUMN subtitle TEXT;\n";
            if ($col === 'image_url') echo "ALTER TABLE banners ADD COLUMN image_url VARCHAR(500);\n";
            if ($col === 'link_url') echo "ALTER TABLE banners ADD COLUMN link_url VARCHAR(500);\n";
            if ($col === 'cta_text') echo "ALTER TABLE banners ADD COLUMN cta_text VARCHAR(100);\n";
            if ($col === 'is_active') echo "ALTER TABLE banners ADD COLUMN is_active TINYINT(1) DEFAULT 1;\n";
            if ($col === 'display_order') echo "ALTER TABLE banners ADD COLUMN display_order INT DEFAULT 0;\n";
        }
        echo "</div>";
        echo "</div>";
    }
}

// Categories Icon
if ($checks['categories_icon'] === true) {
    echo "<div class='check-item ok'>";
    echo "<div class='status ok'>✅ Categories Icon Field - OK</div>";
    echo "<div class='detail'>Field 'icon' exists in categories table</div>";
    echo "</div>";
} else {
    echo "<div class='check-item missing'>";
    echo "<div class='status missing'>❌ Categories Icon Field - MISSING</div>";
    echo "<div class='detail'>Field 'icon' does not exist in categories table</div>";
    echo "<div class='sql-box'>";
    echo "ALTER TABLE categories ADD COLUMN icon VARCHAR(500) AFTER name;";
    echo "</div>";
    echo "</div>";
}

// Products Featured
if ($checks['products_featured'] === true) {
    echo "<div class='check-item ok'>";
    echo "<div class='status ok'>✅ Products Featured Field - OK</div>";
    echo "<div class='detail'>Field 'is_featured' or 'is_best_seller' exists</div>";
    echo "</div>";
} else {
    echo "<div class='check-item warning'>";
    echo "<div class='status warning'>⚠️ Products Featured Field - CHECK</div>";
    echo "<div class='detail'>Neither 'is_featured' nor 'is_best_seller' found. One should exist.</div>";
    echo "<div class='sql-box'>";
    echo "-- Check if field exists with different name, or add:\n";
    echo "ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0;\n";
    echo "-- OR\n";
    echo "ALTER TABLE products ADD COLUMN is_best_seller TINYINT(1) DEFAULT 0;";
    echo "</div>";
    echo "</div>";
}

echo "<div class='footer'>";
echo "<p><strong>Instructions:</strong></p>";
echo "<p>1. If any item shows ❌, copy the SQL from the grey box</p>";
echo "<p>2. Run the SQL in phpMyAdmin or your MySQL client</p>";
echo "<p>3. Refresh this page to verify</p>";
echo "<p style='margin-top: 20px; color: #f44336;'><strong>⚠️ DELETE THIS FILE AFTER CHECKING (for security)</strong></p>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
