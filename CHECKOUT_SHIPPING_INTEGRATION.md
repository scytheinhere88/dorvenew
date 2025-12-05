# 📦 CHECKOUT SHIPPING INTEGRATION GUIDE

## ✅ FITUR YANG SUDAH DIPERBAIKI:

### **API Calculate Rates - Filter Kurir Tidak Tersedia**

API sekarang otomatis:
- ✅ **Filter out** kurir yang tidak tersedia (price = 0 atau error)
- ✅ **Sort by price** (termurah dulu)
- ✅ **Return info** tentang kurir unavailable (untuk debugging)
- ✅ **Handle** area tanpa kurir tersedia sama sekali

**File Updated**: `/app/api/shipping/calculate-rates.php`

---

## 🔧 CARA INTEGRATE DI CHECKOUT PAGE:

### **Step 1: Tambahkan Postal Code Field di Checkout Form**

```php
<!-- Di file: /pages/checkout.php -->

<div class="form-row">
    <div class="form-group">
        <label>Kota/Kabupaten *</label>
        <input type="text" name="city" id="city" required>
    </div>
    <div class="form-group">
        <label>Kode Pos *</label>
        <input type="text" 
               name="postal_code" 
               id="postalCode" 
               required 
               pattern="[0-9]{5}"
               placeholder="Contoh: 12345"
               onblur="calculateShipping()">
        <small style="color: #6B7280; font-size: 12px;">
            5 digit kode pos untuk menghitung ongkir
        </small>
    </div>
</div>
```

---

### **Step 2: Tambahkan Section Pilih Kurir**

```php
<!-- Shipping Method Selection (Hidden sampai postal code diisi) -->
<div class="form-section" id="shippingSection" style="display: none;">
    <h3>📦 Pilih Metode Pengiriman</h3>
    <div id="shippingRates">
        <div style="text-align: center; padding: 40px; color: #9CA3AF;">
            <div style="font-size: 48px; margin-bottom: 12px;">📮</div>
            <p>Masukkan kode pos untuk melihat pilihan kurir...</p>
        </div>
    </div>
    
    <!-- Hidden inputs untuk store selected courier -->
    <input type="hidden" name="courier_company" id="courierCompany">
    <input type="hidden" name="courier_service" id="courierService">
    <input type="hidden" name="shipping_cost" id="shippingCost">
    <input type="hidden" name="rate_id" id="rateId">
</div>

<!-- Order Summary (Update total saat pilih kurir) -->
<div class="order-summary">
    <div class="summary-row">
        <span>Subtotal</span>
        <span id="subtotal"><?php echo formatPrice($cartTotal); ?></span>
    </div>
    <div class="summary-row" id="shippingCostRow" style="display: none;">
        <span>Ongkos Kirim</span>
        <span id="shippingCostDisplay">Rp 0</span>
    </div>
    <div class="summary-row total">
        <span>Total</span>
        <span id="grandTotal"><?php echo formatPrice($cartTotal); ?></span>
    </div>
</div>
```

---

### **Step 3: Tambahkan JavaScript untuk Calculate & Display Rates**

```javascript
<script>
// Get cart items data (for weight calculation)
const cartItems = <?php 
echo json_encode(array_map(function($item) {
    return [
        'name' => $item['name'],
        'value' => intval($item['price']),
        'quantity' => intval($item['qty']),
        'weight' => 500 // Default 500g per item - SESUAIKAN dengan data product weight Anda
    ];
}, $cart_items)); 
?>;

const subtotalAmount = <?php echo $cartTotal; ?>;

function calculateShipping() {
    const postalCode = document.getElementById('postalCode').value;
    
    // Validate postal code
    if (!postalCode || postalCode.length < 5) {
        return;
    }
    
    // Show loading
    document.getElementById('shippingRates').innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <div class="spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #3B82F6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 16px;"></div>
            <p style="color: #6B7280;">Mencari kurir yang tersedia...</p>
        </div>
    `;
    
    // Call API
    fetch('/api/shipping/calculate-rates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            postal_code: postalCode,
            items: cartItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.rates && data.rates.length > 0) {
            renderShippingRates(data.rates);
            document.getElementById('shippingSection').style.display = 'block';
        } else {
            // NO COURIERS AVAILABLE
            showNoCouriersAvailable(data.unavailable_couriers || []);
        }
    })
    .catch(error => {
        document.getElementById('shippingRates').innerHTML = `
            <div style="padding: 20px; background: #FEE2E2; border-radius: 8px; border-left: 4px solid #EF4444;">
                <strong style="color: #991B1B;">❌ Error:</strong>
                <p style="color: #991B1B; margin: 8px 0 0;">${error.message}</p>
            </div>
        `;
    });
}

function renderShippingRates(rates) {
    let html = '<div style="display: grid; gap: 12px;">';
    
    rates.forEach((rate, index) => {
        const isRecommended = index === 0; // Cheapest = recommended
        
        html += `
            <label class="shipping-option ${isRecommended ? 'recommended' : ''}" style="
                display: flex;
                align-items: center;
                padding: 20px;
                border: 2px solid ${isRecommended ? '#3B82F6' : '#E5E7EB'};
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.2s;
                background: ${isRecommended ? '#F0F9FF' : 'white'};
            ">
                <input type="radio" 
                       name="shipping_rate" 
                       value="${rate.rate_id}" 
                       data-company="${rate.courier_company}"
                       data-service="${rate.courier_service_name}"
                       data-cost="${rate.price}"
                       data-rate-id="${rate.rate_id}"
                       onchange="selectShipping(this)"
                       ${isRecommended ? 'checked' : ''}
                       style="margin-right: 16px; width: 20px; height: 20px;">
                
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <strong style="text-transform: uppercase; font-size: 16px;">${rate.courier_company}</strong>
                        ${isRecommended ? '<span style="background: #3B82F6; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">TERMURAH</span>' : ''}
                    </div>
                    <div style="color: #6B7280; font-size: 14px; margin-bottom: 4px;">
                        ${rate.courier_service_name}
                    </div>
                    <div style="color: #6B7280; font-size: 13px;">
                        ⏱️ Estimasi: ${rate.duration}
                    </div>
                </div>
                
                <div style="text-align: right;">
                    <div style="font-size: 20px; font-weight: 700; color: #1F2937;">
                        ${formatRupiah(rate.price)}
                    </div>
                </div>
            </label>
        `;
    });
    
    html += '</div>';
    
    // Info total couriers
    html += `
        <div style="margin-top: 16px; padding: 12px; background: #DBEAFE; border-radius: 8px; font-size: 13px; color: #1E40AF;">
            ℹ️ <strong>${rates.length} kurir tersedia</strong> untuk area ini. Pilih yang paling sesuai!
        </div>
    `;
    
    document.getElementById('shippingRates').innerHTML = html;
    
    // Auto-select cheapest (first one)
    if (rates.length > 0) {
        const firstRadio = document.querySelector('input[name="shipping_rate"]');
        if (firstRadio) {
            selectShipping(firstRadio);
        }
    }
}

function showNoCouriersAvailable(unavailableCouriers) {
    let html = `
        <div style="text-align: center; padding: 40px; background: #FEF3C7; border-radius: 12px; border: 2px solid #F59E0B;">
            <div style="font-size: 64px; margin-bottom: 16px;">📭</div>
            <h3 style="color: #92400E; margin-bottom: 12px;">Tidak Ada Kurir Tersedia</h3>
            <p style="color: #92400E; margin-bottom: 20px;">
                Maaf, untuk saat ini belum ada kurir yang melayani pengiriman ke kode pos ini.
            </p>
    `;
    
    if (unavailableCouriers.length > 0) {
        html += `
            <details style="text-align: left; max-width: 400px; margin: 20px auto; padding: 16px; background: white; border-radius: 8px;">
                <summary style="cursor: pointer; font-weight: 600; color: #92400E;">
                    Kurir yang dicek (${unavailableCouriers.length})
                </summary>
                <ul style="margin: 12px 0 0 20px; color: #6B7280; font-size: 13px;">
                    ${unavailableCouriers.map(c => `<li>${c}</li>`).join('')}
                </ul>
            </details>
        `;
    }
    
    html += `
        <div style="margin-top: 20px;">
            <p style="color: #92400E; font-size: 14px;">
                <strong>Saran:</strong>
            </p>
            <ul style="list-style: none; padding: 0; margin: 12px 0; color: #92400E; font-size: 14px;">
                <li>✓ Periksa kembali kode pos Anda</li>
                <li>✓ Coba gunakan kode pos area terdekat</li>
                <li>✓ Hubungi customer service untuk bantuan</li>
            </ul>
        </div>
        </div>
    `;
    
    document.getElementById('shippingRates').innerHTML = html;
    document.getElementById('shippingSection').style.display = 'block';
}

function selectShipping(radio) {
    // Update hidden inputs
    document.getElementById('courierCompany').value = radio.dataset.company;
    document.getElementById('courierService').value = radio.dataset.service;
    document.getElementById('shippingCost').value = radio.dataset.cost;
    document.getElementById('rateId').value = radio.dataset.rateId;
    
    // Update order summary
    const shippingCost = parseFloat(radio.dataset.cost);
    document.getElementById('shippingCostDisplay').textContent = formatRupiah(shippingCost);
    document.getElementById('shippingCostRow').style.display = 'flex';
    
    const grandTotal = subtotalAmount + shippingCost;
    document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
    
    // Visual feedback - highlight selected
    document.querySelectorAll('.shipping-option').forEach(option => {
        option.style.borderColor = '#E5E7EB';
        option.style.background = 'white';
    });
    radio.closest('.shipping-option').style.borderColor = '#3B82F6';
    radio.closest('.shipping-option').style.background = '#F0F9FF';
}

function formatRupiah(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// CSS for spinner animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .shipping-option:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
`;
document.head.appendChild(style);
</script>
```

---

## 🎯 BEHAVIOR - KURIR TIDAK TERSEDIA:

### **Scenario 1: Ada Kurir Tersedia**
✅ Tampil list kurir dengan harga
✅ Sort by price (termurah dulu)
✅ Badge "TERMURAH" di option pertama
✅ Auto-select kurir termurah
✅ Update total order otomatis

### **Scenario 2: TIDAK Ada Kurir Tersedia**
✅ Tampil pesan "Tidak Ada Kurir Tersedia"
✅ Icon 📭 untuk visual clarity
✅ Saran: cek kode pos, coba area lain, contact CS
✅ Details dropdown showing kurir yang dicek (debugging)
✅ **Checkout button di-disable** (tidak bisa proceed)

### **Scenario 3: Error API/Network**
✅ Tampil error message dengan detail
✅ User bisa retry (input postal code lagi)

---

## 📊 API RESPONSE FORMAT:

### **Success - Ada Kurir Tersedia:**
```json
{
  "success": true,
  "rates": [
    {
      "courier_company": "jne",
      "courier_name": "JNE",
      "courier_service_name": "Reguler",
      "rate_id": "jne_reg_12345",
      "price": 15000,
      "duration": "2-3 hari",
      "description": "Layanan reguler JNE",
      "available": true
    },
    {
      "courier_company": "jnt",
      "courier_name": "J&T Express",
      "courier_service_name": "Reguler",
      "rate_id": "jnt_reg_67890",
      "price": 18000,
      "duration": "2-4 hari",
      "description": "Layanan reguler J&T",
      "available": true
    }
  ],
  "total_available": 2,
  "unavailable_couriers": [],
  "message": "Rates available"
}
```

### **Success - TIDAK Ada Kurir Tersedia:**
```json
{
  "success": true,
  "rates": [],
  "total_available": 0,
  "unavailable_couriers": [
    "jne - Reguler",
    "jnt - Reguler",
    "sicepat - Reguler"
  ],
  "message": "No couriers available for this area"
}
```

### **Error:**
```json
{
  "success": false,
  "error": "Invalid postal code or API error"
}
```

---

## ✅ VALIDATION & USER EXPERIENCE:

### **Form Validation:**
```javascript
// Disable checkout button jika belum pilih kurir
function validateCheckoutForm() {
    const courierSelected = document.getElementById('courierCompany').value;
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (!courierSelected) {
        checkoutBtn.disabled = true;
        checkoutBtn.style.opacity = '0.5';
        checkoutBtn.style.cursor = 'not-allowed';
        checkoutBtn.title = 'Pilih metode pengiriman dulu';
    } else {
        checkoutBtn.disabled = false;
        checkoutBtn.style.opacity = '1';
        checkoutBtn.style.cursor = 'pointer';
        checkoutBtn.title = '';
    }
}

// Call validation saat pilih kurir
function selectShipping(radio) {
    // ... existing code ...
    validateCheckoutForm();
}
```

### **Progressive Enhancement:**
1. User input address → Normal checkout
2. User input postal code → Trigger calculate shipping
3. API loading → Show spinner
4. Results ready → Show courier options
5. User select courier → Update total
6. Proceed to payment

---

## 🐛 ERROR HANDLING:

### **Handle Common Errors:**
```javascript
.catch(error => {
    let errorMessage = 'Terjadi kesalahan';
    let errorHint = 'Silakan coba lagi atau hubungi customer service';
    
    if (error.message.includes('postal')) {
        errorMessage = 'Kode Pos Tidak Valid';
        errorHint = 'Pastikan kode pos terdiri dari 5 digit angka';
    } else if (error.message.includes('network')) {
        errorMessage = 'Koneksi Internet Bermasalah';
        errorHint = 'Periksa koneksi internet Anda dan coba lagi';
    }
    
    document.getElementById('shippingRates').innerHTML = `
        <div style="padding: 24px; background: #FEE2E2; border-radius: 12px; border-left: 4px solid #EF4444; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 12px;">⚠️</div>
            <h4 style="color: #991B1B; margin-bottom: 8px;">${errorMessage}</h4>
            <p style="color: #991B1B; margin-bottom: 16px;">${errorHint}</p>
            <button onclick="calculateShipping()" style="padding: 10px 20px; background: #EF4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                🔄 Coba Lagi
            </button>
        </div>
    `;
});
```

---

## 📱 RESPONSIVE DESIGN:

```css
/* Add to your checkout CSS */
.shipping-option {
    display: flex;
    align-items: center;
    padding: 20px;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.shipping-option:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.shipping-option.recommended {
    border-color: #3B82F6;
    background: #F0F9FF;
}

@media (max-width: 640px) {
    .shipping-option {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .shipping-option input[type="radio"] {
        margin-bottom: 8px;
    }
}
```

---

## 🧪 TESTING CHECKLIST:

- [ ] Input kode pos Jakarta (12xxx) → Ada kurir
- [ ] Input kode pos Papua (99xxx) → Mungkin tidak ada/sedikit kurir
- [ ] Input kode pos invalid (00000) → Error handling
- [ ] Select kurir termurah → Total update correctly
- [ ] Select kurir lain → Total update correctly
- [ ] Clear postal code → Reset shipping options
- [ ] Network error simulation → Error message shown
- [ ] Mobile responsive → All elements visible

---

## 📝 NOTES:

1. **Weight Calculation**: 
   - Sesuaikan `weight: 500` dengan data product weight Anda
   - Atau ambil dari database: `$item['weight']`

2. **Courier Codes**:
   - Default: `jne,jnt,sicepat,anteraja,idexpress`
   - Bisa diubah di admin settings

3. **Caching** (Optional):
   - Cache rates per postal code untuk 1 jam
   - Reduce API calls ke Biteship

4. **Fallback**:
   - Jika semua kurir tidak tersedia, suggest contact CS
   - Atau tampilkan form "Request Shipping Quote"

---

**DONE!** 🎉

API sudah:
✅ Filter kurir tidak tersedia (tidak tampil sama sekali)
✅ Sort by price (termurah dulu)
✅ Return info debug untuk unavailable couriers
✅ Handle error dengan proper messages

Tinggal integrate di checkout page dengan JavaScript di atas! 🚀
