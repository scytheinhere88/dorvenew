# 🗺️ Setup Google Maps API untuk Address Book

Fitur Address Book sudah dibuat dan siap digunakan, namun membutuhkan **Google Maps API Key** untuk dapat berfungsi penuh.

## 📋 Langkah-langkah Setup

### 1️⃣ Dapatkan Google Maps API Key

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Enable **Maps JavaScript API** dan **Geocoding API**:
   - Navigation menu → APIs & Services → Library
   - Cari "Maps JavaScript API" → Enable
   - Cari "Geocoding API" → Enable
4. Buat API Key:
   - Navigation menu → APIs & Services → Credentials
   - Click "Create Credentials" → API Key
   - Copy API Key yang dihasilkan

### 2️⃣ Konfigurasi API Key (Recommended)

Untuk keamanan, batasi API Key Anda:

1. Click pada API Key yang baru dibuat
2. Pilih "Application restrictions":
   - Select "HTTP referrers (web sites)"
   - Add: `https://dorve.id/*` dan `https://*.dorve.id/*`
3. Pilih "API restrictions":
   - Select "Restrict key"
   - Pilih: Maps JavaScript API, Geocoding API

### 3️⃣ Update File PHP

Edit file `/app/member/address-book.php`:

Cari baris ini (sekitar line 311):
```javascript
const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY'; // GANTI INI!
```

Ganti dengan API Key Anda:
```javascript
const GOOGLE_MAPS_API_KEY = 'AIzaSyABC123...'; // API Key Anda
```

### 4️⃣ Test Fitur

1. Login sebagai member
2. Buka halaman "Address Book"
3. Click "Add New Address"
4. Map seharusnya muncul dan bisa diklik untuk memilih lokasi

## 🆓 Gratis atau Bayar?

Google Maps API **GRATIS** untuk:
- 28,000 map loads per bulan
- 40,000 Geocoding requests per bulan

Untuk website e-commerce kecil-menengah, ini lebih dari cukup!

## 🔥 Alternatif (Jika Tidak Mau Setup Maps)

Fitur Address Book tetap berfungsi **tanpa** Google Maps:
- User bisa input alamat secara manual
- Koordinat latitude/longitude akan NULL
- Semua fitur lain tetap jalan normal

Map hanya mempermudah user untuk memilih lokasi dengan presisi.

## ❓ Troubleshooting

**Error: "This page can't load Google Maps correctly"**
- Check API Key sudah benar
- Check API sudah di-enable (Maps JavaScript API & Geocoding API)
- Check billing account sudah disetup (walaupun free tier)

**Map tidak muncul:**
- Open Browser Console (F12)
- Check error message
- Pastikan tidak ada typo di API Key

## 📞 Need Help?

Jika ada kesulitan setup, hubungi developer atau check dokumentasi resmi:
- [Google Maps Platform Documentation](https://developers.google.com/maps/documentation)
- [Pricing Calculator](https://mapsplatform.google.com/pricing/)
