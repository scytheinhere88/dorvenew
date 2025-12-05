# 📝 DETAIL PERUBAHAN - BITESHIP INTEGRATION

## 🆕 FILE BARU YANG DIBUAT

### 1. Migration & Setup (2 files)
| File | Deskripsi | Cara Pakai |
|------|-----------|------------|
| `/app/database-migration.sql` | SQL script manual (backup option) | Manual import via phpMyAdmin |
| `/app/INSTALL_MIGRATION.php` | **Auto migration installer** ⭐ | Buka di browser: `https://dorve.id/INSTALL_MIGRATION.php` |

### 2. Admin - Order Management (5 files)
| File | Deskripsi | Fungsi |
|------|-----------|--------|
| `/app/admin/orders/print-batch.php` | Batch print labels | Bulk print shipping labels dengan auto status update |
| `/app/admin/orders/templates/label-a6.php` | Template label A6 | Design label pengiriman professional |
| `/app/admin/assets/label-a6.css` | Stylesheet label | CSS untuk print-optimized labels |
| `/app/admin/orders/update-status.php` | API update status | Bulk update order status (AJAX endpoint) |
| `/app/admin/orders/picking-list.php` | Picking list warehouse | Checklist items untuk dipick per order |

### 3. Admin - Integration Testing (2 files)
| File | Deskripsi | Fungsi |
|------|-----------|--------|
| `/app/admin/integration/test-biteship-api.php` | Test API connection | Verify Biteship API key working |
| `/app/admin/integration/test-webhook.php` | Test webhook endpoint | Verify webhook URL accessible |

### 4. API Endpoints (2 files)
| File | Deskripsi | Fungsi |
|------|-----------|--------|
| `/app/api/shipping/calculate-rates.php` | Calculate shipping rates | Untuk checkout - show courier options |
| `/app/api/orders/create-from-payment.php` | Create Biteship order | **PENTING!** Call ini setelah payment success |

### 5. Documentation (2 files)
| File | Deskripsi |
|------|-----------|
| `/app/BITESHIP_INTEGRATION_GUIDE.md` | Complete setup guide dengan API docs |
| `/app/CHANGELOG_DETAIL.md` | Dokumen ini |

**TOTAL: 14 FILE BARU**

---

## ✏️ FILE YANG DIUPDATE/DIPERBAIKI

### 1. Admin Settings (1 file)
| File | Yang Diubah | Alasan |
|------|-------------|--------|
| `/app/admin/settings/api-settings.php` | Fixed `$valueColumn` undefined error di line 55 | Error saat save Biteship settings |

**Detail perubahan:**
```php
// BEFORE (Error):
foreach ($settings as $key => $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, $valueColumn) VALUES (?, ?)
                          ON DUPLICATE KEY UPDATE $valueColumn = ?");
    // $valueColumn tidak didefinisikan di scope ini!
}

// AFTER (Fixed):
// Check which column name is used
$checkStmt = $pdo->query("DESCRIBE settings");
$columns = array_column($checkStmt->fetchAll(), 'Field');
$valueColumn = in_array('setting_value', $columns) ? 'setting_value' : 'value';

foreach ($settings as $key => $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, $valueColumn) VALUES (?, ?)
                          ON DUPLICATE KEY UPDATE $valueColumn = ?");
}
```

---

## 📊 DATABASE CHANGES

### Tables Modified:
| Table | Changes |
|-------|---------|
| `settings` | Column rename: `value` → `setting_value` (normalization) |
| `orders` | Added 6 columns: `fulfillment_status`, `shipping_courier`, `shipping_service`, `shipping_cost`, `tracking_number`, `notes` |
| `orders` | Added 2 indexes: `idx_tracking`, `idx_fulfillment` |

### New Tables Created (4):

#### 1. `order_addresses`
```sql
- id (Primary Key)
- order_id (Foreign Key to orders)
- type (ENUM: billing/shipping)
- name, phone, address_line, district, city, province, postal_code, country
- created_at
```
**Purpose:** Store billing & shipping addresses per order

#### 2. `biteship_shipments`
```sql
- id (Primary Key)
- order_id (Foreign Key to orders)
- biteship_order_id (Unique - from Biteship API)
- courier_company, courier_name, courier_service_name, courier_service_code
- rate_id, shipping_cost, insurance_cost
- status (pending/confirmed/in_transit/delivered)
- waybill_id (tracking number)
- label_print_batch_id (for batch printing)
- pickup_code, delivery_date, pickup_time
- destination & origin details (province, city, postal_code)
- weight_kg, raw_response (JSON from API)
- created_at, updated_at
```
**Purpose:** Store Biteship shipment data per order

#### 3. `biteship_webhook_logs`
```sql
- id (Primary Key)
- event (type of webhook event)
- biteship_order_id
- payload (JSON data from webhook)
- processed (boolean - sudah diproses atau belum)
- error_message
- created_at
```
**Purpose:** Log all incoming webhooks from Biteship for debugging

#### 4. `print_batches`
```sql
- id (Primary Key)
- batch_code (Unique - generated per batch)
- printed_by_admin_id (which admin printed)
- printed_at (timestamp)
- total_orders (count)
- notes
```
**Purpose:** Track batch printing sessions

### New Settings Inserted:
```
biteship_enabled = 1
biteship_api_key = biteship_live.eyJhbGc...
biteship_environment = production
biteship_webhook_secret = (empty - optional)
biteship_default_couriers = jne,jnt,sicepat,anteraja,idexpress
store_name = Dorve.id Official Store
store_phone = +62-813-7737-8859
store_address = Jakarta, Indonesia
store_city = Jakarta Selatan
store_province = DKI Jakarta
store_postal_code = 12345
store_country = ID
```

---

## 🚀 CARA INSTALL/UPDATE DI SERVER

### **OPTION 1: Pakai Auto Installer (RECOMMENDED)** ⭐

#### Step 1: Upload File
Upload file `INSTALL_MIGRATION.php` ke root directory website Anda (sejajar dengan index.php)

#### Step 2: Backup Database
```bash
# Via SSH
mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql

# Atau via phpMyAdmin: Export → SQL
```

#### Step 3: Jalankan Migration
1. Buka browser
2. Login sebagai admin dulu di website
3. Akses: `https://dorve.id/INSTALL_MIGRATION.php`
4. Centang "Saya sudah backup database"
5. Klik "Jalankan Migration Sekarang"
6. Tunggu sampai selesai (akan show progress & hasil)

#### Step 4: Upload File-File Baru
Upload semua file baru ini ke server (via FTP/cPanel File Manager):
```
/app/admin/orders/print-batch.php
/app/admin/orders/templates/label-a6.php
/app/admin/orders/update-status.php
/app/admin/orders/picking-list.php
/app/admin/assets/label-a6.css
/app/admin/integration/test-biteship-api.php
/app/admin/integration/test-webhook.php
/app/api/shipping/calculate-rates.php
/app/api/orders/create-from-payment.php
```

#### Step 5: Update File yang Diubah
Replace file ini dengan versi baru:
```
/app/admin/settings/api-settings.php
```

#### Step 6: Hapus File Migration
⚠️ **PENTING!** Hapus `INSTALL_MIGRATION.php` dari server setelah selesai!

---

### **OPTION 2: Manual SQL Import**

#### Step 1: Backup Database
```bash
mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql
```

#### Step 2: Import SQL
Via phpMyAdmin:
1. Login phpMyAdmin
2. Pilih database `dorve_dorve`
3. Tab "Import"
4. Upload file `database-migration.sql`
5. Click "Go"

#### Step 3-6: Sama seperti Option 1 (Step 4-6)

---

## ✅ VERIFICATION CHECKLIST

Setelah install, verify hal-hal ini:

### Database:
- [ ] Table `biteship_shipments` ada
- [ ] Table `order_addresses` ada
- [ ] Table `biteship_webhook_logs` ada
- [ ] Table `print_batches` ada
- [ ] Table `orders` punya kolom `fulfillment_status`
- [ ] Table `settings` pakai kolom `setting_value` (bukan `value`)
- [ ] Settings `biteship_api_key` terisi

### Admin Panel:
- [ ] Login admin berhasil
- [ ] Go to Settings → API Settings
- [ ] Section "Biteship Configuration" muncul
- [ ] API Key terisi otomatis
- [ ] Klik "Test API Key" → Success
- [ ] Webhook URL terlihat: `https://dorve.id/api/biteship/webhook.php`

### Files:
- [ ] File `/admin/orders/print-batch.php` ada
- [ ] File `/admin/orders/templates/label-a6.php` ada
- [ ] File `/admin/assets/label-a6.css` ada
- [ ] File `/api/shipping/calculate-rates.php` ada
- [ ] File `/api/orders/create-from-payment.php` ada

### Biteship Dashboard:
- [ ] Login ke https://business.biteship.com/
- [ ] Go to Settings → Webhooks
- [ ] Add webhook URL: `https://dorve.id/api/biteship/webhook.php`
- [ ] Subscribe events: `order.status`, `order.waybill_id`
- [ ] Save

---

## 🧪 TESTING

### Test 1: API Connection
1. Admin → Settings → API Settings
2. Scroll ke Biteship section
3. Klik "🧪 Test API Key"
4. Harus muncul: "✅ Biteship API Connection Successful!"

### Test 2: Webhook
1. Di API Settings page
2. Klik "📡 Test Webhook"
3. Harus muncul: "✅ Webhook Endpoint Accessible"

### Test 3: Calculate Rates (Manual)
Buka browser console (F12) dan test API:
```javascript
fetch('/api/shipping/calculate-rates.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    postal_code: '12345',
    items: [{
      name: 'Test Product',
      value: 100000,
      quantity: 1,
      weight: 500
    }]
  })
})
.then(r => r.json())
.then(d => console.log(d))
```
Harus return list of courier rates

### Test 4: End-to-End (After Orders Exist)
1. Create test order di website
2. Complete payment → status jadi "paid"
3. Call API create-from-payment (via Postman atau curl):
```bash
curl -X POST https://dorve.id/api/orders/create-from-payment.php \
  -H "Content-Type: application/json" \
  -d '{"order_id": 123}'
```
4. Check admin orders page → status harus "waiting_print"
5. Check table `biteship_shipments` → ada data baru
6. Select order → Klik "Print Labels"
7. Window baru terbuka dengan label
8. Print → Status auto update ke "waiting_pickup"

---

## 📱 FITUR YANG BERFUNGSI SETELAH UPDATE

### 1. Order Management System
✅ Tab filtering by status (New, Waiting Print, In Transit, dll)
✅ Search by order number / tracking / customer name
✅ Bulk selection dengan checkboxes
✅ Professional UI dengan status badges

### 2. Batch Print Labels
✅ Select multiple orders
✅ Print all labels at once (A6 format)
✅ Auto update status ke "waiting_pickup"
✅ Batch tracking (tersimpan di database)
✅ Professional label design dengan branding Dorve.id

### 3. Shipping Integration
✅ Calculate shipping rates at checkout
✅ Multiple courier support (JNE, JNT, Sicepat, dll)
✅ Auto-create Biteship order after payment
✅ Webhook untuk status updates

### 4. Warehouse Features
✅ Picking list untuk warehouse staff
✅ Item checklist per order
✅ Print-friendly format

### 5. Admin Tools
✅ Test API connection
✅ Test webhook endpoint
✅ Bulk status update
✅ Order tracking view

---

## ⚠️ YANG BELUM DIKERJAKAN

Sesuai handoff summary, masih ada tasks ini:

### Priority 1 (P1):
- [ ] **Business Growth Dashboard**
  - Daily/monthly statistics
  - New members, transactions, deposits chart
  - 6-month data retention

- [ ] **Email Notifications**
  - Professional HTML templates
  - Order confirmation email
  - Shipping status update email
  - Waybill generated notification

### Priority 2 (P2):
- [ ] **Product Detail Enhancements**
  - "You Might Also Like" section (related products)
  - Customer Reviews system (rating & comments)

- [ ] **User Management**
  - Admin can add/subtract user wallet balance
  - Verify all user fields editable by admin

- [ ] **Password Reset**
  - Verify "Forgot Password" flow working

### Priority 3 (P3):
- [ ] **Fix Admin Pages**
  - payment-settings page
  - bank-accounts page

- [ ] **Referral System**
  - Percentage-based (configurable)
  - Admin panel for configuration

---

## 🐛 TROUBLESHOOTING

### Error: "Table already exists"
**Cause:** Migration sudah pernah dijalankan
**Fix:** Jangan run lagi. Cek table `biteship_shipments` - kalau ada, berarti sudah jalan.

### Error: "API Connection Failed"
**Cause:** API key salah atau network issue
**Fix:** 
1. Check API key di admin settings
2. Test dengan curl manual
3. Check Biteship Dashboard - API key valid?

### Error: "Webhook test failed"
**Cause:** URL tidak accessible dari luar
**Fix:**
1. Check file `/api/biteship/webhook.php` ada
2. Test akses langsung via browser
3. Check .htaccess tidak block

### Error: "Print batch tidak update status"
**Cause:** JavaScript error atau database error
**Fix:**
1. Check browser console (F12)
2. Check PHP error log
3. Verify table `print_batches` exists

### Error: "Cannot save Biteship settings"
**Cause:** Column name mismatch (value vs setting_value)
**Fix:** Run migration lagi - ini sudah dihandle di migration script

---

## 📞 NEXT STEPS AFTER INSTALL

1. **Verify Installation**
   - Run all items in Verification Checklist
   - Test API connection
   - Test webhook

2. **Configure Biteship**
   - Setup webhook di Biteship Dashboard
   - Test dengan dummy order

3. **Update Checkout Page** (Anda yang handle)
   - Integrate `/api/shipping/calculate-rates.php` di checkout
   - Show courier options ke customer
   - Save selected courier to order

4. **Integrate Payment Callback** (Anda yang handle)
   - After payment success, call `/api/orders/create-from-payment.php`
   - Pass order_id
   - Handle response

5. **Train Admin Staff**
   - Cara pakai order management page
   - Cara bulk print labels
   - Cara pakai picking list

---

**DONE! Semua file sudah siap deploy!** 🚀

Kalau ada pertanyaan atau error, kasih tau error message lengkapnya ya bro.
