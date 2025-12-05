<?php
/**
 * MEMBER - MY VOUCHERS PAGE
 * View all available vouchers (dari tier, referral, sistem)
 */
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user info for tier checking
$stmt = $pdo->prepare("SELECT tier, referral_code FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$userTier = $user['tier'] ?? 'bronze';
$hasReferral = !empty($user['referral_code']);

// Get all available vouchers for this user
$stmt = $pdo->prepare("
    SELECT v.*,
           COALESCE(uv.usage_count, 0) as usage_count,
           CASE 
               WHEN v.total_usage_limit IS NOT NULL AND v.total_used >= v.total_usage_limit THEN 1
               ELSE 0
           END as is_limit_reached,
           CASE
               WHEN v.target_type = 'all' THEN 1
               WHEN v.target_type = 'tier' AND v.target_tier = ? THEN 1
               WHEN v.target_type = 'referral' AND ? = 1 THEN 1
               ELSE 0
           END as is_eligible
    FROM vouchers v
    LEFT JOIN user_vouchers uv ON v.id = uv.voucher_id AND uv.user_id = ?
    WHERE v.is_active = 1
      AND v.valid_from <= NOW()
      AND v.valid_until >= NOW()
    HAVING is_eligible = 1
    ORDER BY v.type DESC, v.min_purchase ASC
");
$stmt->execute([$userTier, $hasReferral ? 1 : 0, $userId]);
$vouchers = $stmt->fetchAll();

$freeShipping = [];
$discount = [];

foreach ($vouchers as $voucher) {
    if ($voucher['type'] === 'free_shipping') {
        $freeShipping[] = $voucher;
    } else {
        $discount[] = $voucher;
    }
}

$pageTitle = 'My Vouchers';
?>
<!DOCTYPE html>
<html lang=\"id\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>My Vouchers - Dorve.id</title>
    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap\" rel=\"stylesheet\">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F8F9FA; color: #1A1A1A; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .header { margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .header .subtitle { color: #6B7280; font-size: 16px; }
        .tier-badge { 
            display: inline-block; padding: 8px 16px; border-radius: 20px; 
            font-size: 14px; font-weight: 600; margin-top: 12px;
        }
        .tier-bronze { background: #FEF3C7; color: #92400E; }
        .tier-silver { background: #E5E7EB; color: #374151; }
        .tier-gold { background: #FEF3C7; color: #92400E; }
        .tier-platinum { background: #DBEAFE; color: #1E40AF; }
        .tier-vvip { background: #FCE7F3; color: #9F1239; }
        
        .section-title { 
            font-size: 24px; font-weight: 700; margin: 40px 0 24px; 
            display: flex; align-items: center; gap: 12px;
        }
        .section-subtitle { color: #6B7280; font-size: 14px; margin-top: 4px; }
        
        .voucher-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
            gap: 24px; margin-bottom: 40px;
        }
        
        .voucher-card {
            background: white; border-radius: 20px; overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 2px solid transparent; cursor: pointer; position: relative;
        }
        .voucher-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
            opacity: 0; transition: opacity 0.4s;
        }
        .voucher-card:hover::before { opacity: 1; }
        .voucher-card:hover { 
            transform: translateY(-8px) scale(1.02); 
            box-shadow: 0 16px 40px rgba(0,0,0,0.15); 
            border-color: #667EEA;
        }
        
        .voucher-header {
            padding: 24px; background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white; position: relative;
        }
        .voucher-header.free-shipping {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }
        
        .voucher-icon {
            width: 60px; height: 60px; border-radius: 12px; 
            object-fit: cover; margin-bottom: 16px; background: white;
        }
        .voucher-code {
            font-size: 28px; font-weight: 700; font-family: 'Courier New', monospace;
            letter-spacing: 2px; margin-bottom: 8px;
        }
        .voucher-name {
            font-size: 16px; opacity: 0.95; font-weight: 500;
        }
        
        .voucher-body { padding: 24px; }
        .voucher-value {
            font-size: 32px; font-weight: 700; color: #1F2937; margin-bottom: 12px;
        }
        .voucher-description {
            color: #6B7280; font-size: 14px; line-height: 1.6; margin-bottom: 16px;
        }
        
        .voucher-conditions {
            background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 16px;
        }
        .condition-item {
            display: flex; align-items: center; gap: 8px; 
            font-size: 13px; color: #374151; margin-bottom: 8px;
        }
        .condition-item:last-child { margin-bottom: 0; }
        .condition-icon { font-size: 16px; }
        
        .voucher-footer {
            padding: 0 24px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .usage-info {
            font-size: 13px; color: #6B7280;
        }
        .usage-count {
            font-weight: 600; color: #3B82F6;
        }
        .valid-until {
            font-size: 12px; color: #6B7280;
        }
        
        .btn-use {
            padding: 12px 24px; 
            background: linear-gradient(135deg, #10B981 0%, #059669 100%); 
            color: white; border-radius: 10px; font-weight: 700; border: none;
            cursor: pointer; transition: all 0.3s; font-size: 14px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            position: relative; overflow: hidden;
        }
        .btn-use::before {
            content: ''; position: absolute; top: 50%; left: 50%;
            width: 0; height: 0; border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        .btn-use:hover::before {
            width: 200px; height: 200px;
        }
        .btn-use:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5);
        }
        
        .empty-state {
            text-align: center; padding: 80px 20px; background: white;
            border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .empty-icon { font-size: 64px; margin-bottom: 16px; }
        
        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            color: #6B7280; text-decoration: none; margin-bottom: 20px;
            font-weight: 500; transition: color 0.3s;
        }
        .back-btn:hover { color: #1A1A1A; }
        
        .terms-toggle {
            color: #3B82F6; font-size: 13px; cursor: pointer;
            text-decoration: underline; margin-top: 8px; display: inline-block;
        }
        .terms-content {
            display: none; margin-top: 12px; padding: 12px;
            background: #F3F4F6; border-radius: 6px; font-size: 12px;
            color: #374151; line-height: 1.6;
        }
        .terms-content.show { display: block; }
        
        @media (max-width: 768px) {
            .voucher-grid { grid-template-columns: 1fr; }
            .container { padding: 20px; }
            .header h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <a href=\"/member/index.php\" class=\"back-btn\">
            <span>←</span> Back to Dashboard
        </a>
        
        <div class=\"header\">
            <h1>🎟️ My Vouchers</h1>
            <p class=\"subtitle\">Voucher yang tersedia untuk Anda</p>
            <div class=\"tier-badge tier-<?= strtolower($userTier) ?>\">
                <?php
                $tierEmojis = [
                    'bronze' => '🥉',
                    'silver' => '🥈',
                    'gold' => '🥇',
                    'platinum' => '💎',
                    'vvip' => '👑'
                ];
                echo ($tierEmojis[$userTier] ?? '⭐') . ' ' . strtoupper($userTier) . ' Member';
                ?>
            </div>
        </div>

        <?php if (empty($vouchers)): ?>
            <div class=\"empty-state\">
                <div class=\"empty-icon\">🎟️</div>
                <h3>No Vouchers Available</h3>
                <p style=\"color: #6B7280; margin-top: 8px;\">Belum ada voucher yang tersedia untuk tier Anda saat ini</p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($freeShipping)): ?>
                <div class=\"section-title\">
                    <span>🚚</span> Free Shipping Vouchers
                    <div class=\"section-subtitle\"><?= count($freeShipping) ?> tersedia</div>
                </div>
                <div class=\"voucher-grid\">
                    <?php foreach ($freeShipping as $v): ?>
                        <div class=\"voucher-card\" onclick=\"copyVoucherCode('<?= htmlspecialchars($v['code']) ?>')\">\n                            <div class=\"voucher-header free-shipping\">
                                <?php if ($v['image']): ?>
                                    <img src=\"/uploads/vouchers/<?= htmlspecialchars($v['image']) ?>\" class=\"voucher-icon\" alt=\"Icon\">
                                <?php endif; ?>
                                <div class=\"voucher-code\"><?= htmlspecialchars($v['code']) ?></div>
                                <div class=\"voucher-name\"><?= htmlspecialchars($v['name']) ?></div>
                            </div>
                            
                            <div class=\"voucher-body\">
                                <div class=\"voucher-value\">
                                    FREE SHIPPING
                                    <?php if ($v['discount_value']): ?>
                                        <span style=\"font-size: 16px; color: #6B7280;\">(Max: Rp <?= number_format($v['discount_value'], 0, ',', '.') ?>)</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($v['description']): ?>
                                    <div class=\"voucher-description\"><?= nl2br(htmlspecialchars($v['description'])) ?></div>
                                <?php endif; ?>
                                
                                <div class=\"voucher-conditions\">
                                    <?php if ($v['min_purchase']): ?>
                                        <div class=\"condition-item\">
                                            <span class=\"condition-icon\">📦</span>
                                            Min. belanja <strong>Rp <?= number_format($v['min_purchase'], 0, ',', '.') ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class=\"condition-item\">
                                        <span class=\"condition-icon\">🔢</span>
                                        Bisa dipakai <strong><?= $v['max_usage_per_user'] ?>x</strong> (sudah dipakai: <?= $v['usage_count'] ?>x)
                                    </div>
                                    <div class=\"condition-item\">
                                        <span class=\"condition-icon\">📅</span>
                                        Valid hingga <strong><?= date('d M Y', strtotime($v['valid_until'])) ?></strong>
                                    </div>
                                </div>
                                
                                <?php if ($v['terms_conditions']): ?>
                                    <span class=\"terms-toggle\" onclick=\"event.stopPropagation(); toggleTerms(this)\">Lihat S&K</span>
                                    <div class=\"terms-content\"><?= nl2br(htmlspecialchars($v['terms_conditions'])) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class=\"voucher-footer\">
                                <button class=\"btn-use\" onclick=\"event.stopPropagation(); useVoucher('<?= htmlspecialchars($v['code']) ?>')\">
                                    Gunakan Sekarang
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($discount)): ?>
                <div class=\"section-title\">
                    <span>💰</span> Discount Vouchers
                    <div class=\"section-subtitle\"><?= count($discount) ?> tersedia</div>
                </div>
                <div class=\"voucher-grid\">
                    <?php foreach ($discount as $v): ?>
                        <div class=\"voucher-card\" onclick=\"copyVoucherCode('<?= htmlspecialchars($v['code']) ?>')\">\n                            <div class=\"voucher-header\">
                                <?php if ($v['image']): ?>
                                    <img src=\"/uploads/vouchers/<?= htmlspecialchars($v['image']) ?>\" class=\"voucher-icon\" alt=\"Icon\">
                                <?php endif; ?>
                                <div class=\"voucher-code\"><?= htmlspecialchars($v['code']) ?></div>
                                <div class=\"voucher-name\"><?= htmlspecialchars($v['name']) ?></div>
                            </div>
                            
                            <div class=\"voucher-body\">
                                <div class=\"voucher-value\">
                                    <?php if ($v['discount_type'] === 'percentage'): ?>
                                        <?= number_format($v['discount_value'], 0) ?>% OFF
                                        <?php if ($v['max_discount']): ?>
                                            <span style=\"font-size: 16px; color: #6B7280;\">(Max: Rp <?= number_format($v['max_discount'], 0, ',', '.') ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Rp <?= number_format($v['discount_value'], 0, ',', '.') ?> OFF
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($v['description']): ?>
                                    <div class=\"voucher-description\"><?= nl2br(htmlspecialchars($v['description'])) ?></div>
                                <?php endif; ?>
                                
                                <div class=\"voucher-conditions\">
                                    <?php if ($v['min_purchase']): ?>
                                        <div class=\"condition-item\">
                                            <span class=\"condition-icon\">📦</span>
                                            Min. belanja <strong>Rp <?= number_format($v['min_purchase'], 0, ',', '.') ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class=\"condition-item\">
                                        <span class=\"condition-icon\">🔢</span>
                                        Bisa dipakai <strong><?= $v['max_usage_per_user'] ?>x</strong> (sudah dipakai: <?= $v['usage_count'] ?>x)
                                    </div>
                                    <div class=\"condition-item\">
                                        <span class=\"condition-icon\">📅</span>
                                        Valid hingga <strong><?= date('d M Y', strtotime($v['valid_until'])) ?></strong>
                                    </div>
                                </div>
                                
                                <?php if ($v['terms_conditions']): ?>
                                    <span class=\"terms-toggle\" onclick=\"event.stopPropagation(); toggleTerms(this)\">Lihat S&K</span>
                                    <div class=\"terms-content\"><?= nl2br(htmlspecialchars($v['terms_conditions'])) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class=\"voucher-footer\">
                                <button class=\"btn-use\" onclick=\"event.stopPropagation(); useVoucher('<?= htmlspecialchars($v['code']) ?>')\">
                                    Gunakan Sekarang
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <script>
    function toggleTerms(element) {
        const content = element.nextElementSibling;
        content.classList.toggle('show');
        element.textContent = content.classList.contains('show') ? 'Sembunyikan S&K' : 'Lihat S&K';
    }

    function copyVoucherCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('✅ Kode voucher \"' + code + '\" berhasil disalin!\\nPaste di checkout untuk menggunakan.');
        });
    }

    function useVoucher(code) {
        // Redirect to shop or save voucher code to session
        sessionStorage.setItem('pending_voucher', code);
        alert('Voucher \"' + code + '\" siap digunakan!\\nLanjut ke checkout untuk apply voucher.');
        window.location.href = '/shop.php';
    }
    </script>
</body>
</html>
