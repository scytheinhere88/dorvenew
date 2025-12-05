<?php
/**
 * Batch Print A6 Shipping Labels
 * Print multiple labels at once
 */

require_once __DIR__ . '/../../config.php';
if (!isAdmin()) redirect('/admin/login.php');

$batchId = intval($_GET['batch_id'] ?? 0);

if ($batchId <= 0) {
    die('Invalid batch ID');
}

// Get batch info
$stmt = $pdo->prepare("SELECT * FROM print_batches WHERE id = ?");
$stmt->execute([$batchId]);
$batch = $stmt->fetch();

if (!$batch) {
    die('Batch not found');
}

// Get orders in this batch
$stmt = $pdo->query("
    SELECT 
        o.*,
        s.*,
        s.id as shipment_id,
        oa.name as recipient_name,
        oa.phone as recipient_phone,
        oa.address_line as recipient_address,
        oa.district as recipient_district,
        oa.city as recipient_city,
        oa.province as recipient_province,
        oa.postal_code as recipient_postal
    FROM orders o
    INNER JOIN biteship_shipments s ON o.id = s.order_id
    INNER JOIN order_addresses oa ON o.id = oa.order_id AND oa.type = 'shipping'
    WHERE s.label_print_batch_id = $batchId
    ORDER BY o.id ASC
");
$shipments = $stmt->fetchAll();

if (empty($shipments)) {
    die('No shipments found in this batch');
}

// Load store info
require_once __DIR__ . '/../../includes/BiteshipConfig.php';
$storeInfo = BiteshipConfig::load()['store'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Batch <?php echo $batch['batch_code']; ?> - Dorve.id</title>
    <link rel="stylesheet" href="/admin/orders/label-a6.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .label { page-break-after: always; }
            .label:last-child { page-break-after: auto; }
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<!-- Print Controls -->
<div class="no-print">
    <h3 style="margin: 0 0 12px; font-size: 16px;">Batch: <?php echo $batch['batch_code']; ?></h3>
    <p style="margin: 0 0 12px; font-size: 14px; color: #6B7280;">
        <?php echo count($shipments); ?> label(s) ready to print
    </p>
    <button onclick="window.print()" style="padding: 10px 20px; background: #10B981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; margin-bottom: 8px;">
        🖨️ Print All Labels
    </button>
    <button onclick="window.close()" style="padding: 10px 20px; background: #6B7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%;">
        ← Close
    </button>
</div>

<!-- Labels -->
<?php foreach ($shipments as $index => $shipment): ?>
    <?php include __DIR__ . '/label-a6-template.php'; ?>
<?php endforeach; ?>

<script>
// Auto print on load (optional)
// window.onload = function() { window.print(); };

// Update status after print
window.onafterprint = function() {
    // Send AJAX to update status
    fetch('/admin/orders/update-print-status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({batch_id: <?php echo $batchId; ?>})
    }).then(() => {
        console.log('Print status updated');
    });
};
</script>

</body>
</html>
