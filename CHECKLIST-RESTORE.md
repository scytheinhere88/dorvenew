# ✅ Database Restoration Checklist

## 🎯 Pre-Restoration

- [ ] Backup data lama (jika ada yang penting)
- [ ] Verify database credentials di `config.php`
- [ ] Check MySQL version (≥ 5.7 atau MariaDB ≥ 10.2)
- [ ] Ensure database `dorve_dorve` exists
- [ ] Test database connection

---

## 🚀 Restoration Process

### Option A: Auto-Import (Recommended)
- [ ] Open browser
- [ ] Navigate to: `https://dorve.co/restore-database.php`
- [ ] Review database information
- [ ] Click "🚀 Start Database Restoration"
- [ ] Confirm warning message
- [ ] Wait for completion (30-60 seconds)
- [ ] Verify success message

### Option B: Manual Import
- [ ] Login to phpMyAdmin
- [ ] Select database: `dorve_dorve`
- [ ] Click "Import" tab
- [ ] Choose file: `COMPLETE-DATABASE-RESTORE.sql`
- [ ] Click "Go"
- [ ] Wait for completion
- [ ] Check for error messages

---

## ✅ Post-Restoration Verification

### 1. Database Structure
- [ ] Verify 20 tables created
- [ ] Check stored procedures exist (`update_user_tier`, `process_referral_reward`)
- [ ] Verify triggers exist (3 triggers)
- [ ] Check views created (2 views)

**Quick SQL Check:**
```sql
SHOW TABLES;
SHOW PROCEDURE STATUS WHERE Db = 'dorve_dorve';
SHOW TRIGGERS;
```

### 2. Default Data
- [ ] Verify 2 admin users exist
- [ ] Check 8 categories created
- [ ] Verify 4 shipping methods
- [ ] Check 8 vouchers created
- [ ] Verify system settings populated
- [ ] Check 5 CMS pages exist

**Quick SQL Check:**
```sql
SELECT COUNT(*) FROM users WHERE role = 'admin';
SELECT COUNT(*) FROM categories;
SELECT COUNT(*) FROM vouchers;
```

### 3. Admin Access
- [ ] Navigate to `/admin/login.php`
- [ ] Login with: `admin1@dorve.co` / `Dorve889`
- [ ] Verify dashboard loads
- [ ] Check all menu items accessible
- [ ] Test admin2 login: `admin2@dorve.co` / `Admin889`

---

## 🎨 Initial Configuration

### 1. System Settings (`/admin/settings/`)
- [ ] Update store name (if needed)
- [ ] Set store email
- [ ] Configure phone number
- [ ] Update store address
- [ ] Set WhatsApp number
- [ ] Configure marquee text
- [ ] Enable/disable promotion banner

### 2. Shipping Methods (`/admin/shipping/`)
- [ ] Review default shipping methods
- [ ] Update costs if needed
- [ ] Set delivery times
- [ ] Enable/disable methods
- [ ] Test shipping calculation

### 3. Vouchers (`/admin/vouchers/`)
- [ ] Review default vouchers
- [ ] Adjust values if needed
- [ ] Set validity periods
- [ ] Create custom vouchers
- [ ] Test voucher application

### 4. Referral Settings (`/admin/referrals/settings.php`)
- [ ] Review commission percentage (default 5%)
- [ ] Set minimum topup amount
- [ ] Configure referral code prefix
- [ ] Enable/disable referral system

---

## 🛍️ Add Initial Content

### 1. Categories (`/admin/categories/`)
- [ ] Review default categories
- [ ] Add category images
- [ ] Update descriptions
- [ ] Set sort order
- [ ] Add subcategories (optional)

### 2. Products (`/admin/products/`)
- [ ] Add first product
- [ ] Upload product images
- [ ] Set price & discount
- [ ] Add product variants (size, color)
- [ ] Set stock quantities
- [ ] Add SEO meta tags
- [ ] Test product display on frontend

### 3. CMS Pages (`/admin/pages/`)
- [ ] Update "About Us" content
- [ ] Customize "Privacy Policy"
- [ ] Edit "Terms & Conditions"
- [ ] Update "Shipping Policy"
- [ ] Customize "FAQ"

---

## 🧪 Feature Testing

### 1. Frontend Testing
- [ ] Homepage loads correctly
- [ ] All Products page displays
- [ ] New Collection page works
- [ ] Product detail page shows correctly
- [ ] Search functionality works
- [ ] Category filtering works
- [ ] Language switch works (ID/EN)

### 2. User Registration & Login
- [ ] Register new customer account
- [ ] Verify email (if enabled)
- [ ] Login with customer account
- [ ] Check referral code generated
- [ ] Verify tier = Bronze

### 3. Shopping Flow
- [ ] Add product to cart
- [ ] Update cart quantities
- [ ] Apply voucher code
- [ ] Test free shipping voucher
- [ ] Proceed to checkout
- [ ] Complete order (use test wallet balance)

### 4. Wallet System
- [ ] Request wallet topup
- [ ] Admin approve topup
- [ ] Verify balance credited
- [ ] Check wallet transaction history
- [ ] Test payment with wallet

### 5. Referral System
- [ ] Customer A shares referral code
- [ ] Customer B registers with code
- [ ] Verify referral_rewards = pending
- [ ] Customer B completes first topup
- [ ] Verify commission paid to Customer A
- [ ] Check referral dashboard

### 6. Tier System
- [ ] Customer topup Rp 1,000,000
- [ ] Verify tier upgraded to Silver
- [ ] Check tier upgrade history
- [ ] Test tier-specific vouchers
- [ ] Verify VVIP benefits at Rp 20M+

### 7. Order Management
- [ ] Admin view pending orders
- [ ] Update order status
- [ ] Add tracking number
- [ ] Test order timeline
- [ ] Customer track order
- [ ] Test order cancellation

### 8. Admin Functions
- [ ] View dashboard statistics
- [ ] Generate sales reports
- [ ] Manage user accounts
- [ ] View referral stats
- [ ] Approve pending topups
- [ ] Update product stock
- [ ] Manage vouchers

---

## 🔧 Performance & Security

### 1. Performance Check
- [ ] Test page load speed
- [ ] Check database query performance
- [ ] Verify image optimization
- [ ] Test on mobile devices
- [ ] Check browser compatibility

### 2. Security Check
- [ ] Verify admin panel access restricted
- [ ] Test SQL injection prevention
- [ ] Check XSS protection
- [ ] Verify password hashing
- [ ] Test session management
- [ ] Check file upload security

---

## 📱 Mobile Testing

- [ ] Homepage responsive
- [ ] Product pages mobile-friendly
- [ ] Cart works on mobile
- [ ] Checkout process smooth
- [ ] Admin panel usable on tablet
- [ ] Touch interactions work

---

## 🚨 Common Issues Check

### Database Issues
- [ ] No foreign key errors
- [ ] All triggers working
- [ ] Stored procedures executable
- [ ] Views accessible
- [ ] Auto-increment working

### Application Issues
- [ ] No PHP errors
- [ ] Session working
- [ ] File uploads working
- [ ] Email sending (if configured)
- [ ] Payment processing

---

## 📊 Final Verification

### Database Health
```sql
-- Check table counts
SELECT 
  (SELECT COUNT(*) FROM users) as users,
  (SELECT COUNT(*) FROM products) as products,
  (SELECT COUNT(*) FROM categories) as categories,
  (SELECT COUNT(*) FROM vouchers) as vouchers,
  (SELECT COUNT(*) FROM orders) as orders;

-- Check for errors
SHOW ENGINE INNODB STATUS;
```

### Application Health
- [ ] Error logs empty
- [ ] All pages load without warnings
- [ ] Database connection stable
- [ ] Sessions persisting
- [ ] Cache working (if enabled)

---

## 🎉 Launch Checklist

### Pre-Launch
- [ ] All features tested
- [ ] Sample data removed/updated
- [ ] Real products added
- [ ] Payment gateway configured
- [ ] Email notifications working
- [ ] Backup system in place

### Launch Day
- [ ] Final database backup
- [ ] Clear cache
- [ ] Test critical paths
- [ ] Monitor error logs
- [ ] Watch server resources
- [ ] Customer support ready

### Post-Launch
- [ ] Monitor user registrations
- [ ] Track order flow
- [ ] Check for errors
- [ ] Gather user feedback
- [ ] Plan improvements

---

## 📞 Support Resources

**Documentation:**
- `DATABASE-RESTORATION-GUIDE.md` - Complete guide
- `ADMIN-QUICK-REFERENCE.md` - Admin tutorial
- `README-DATABASE-RESTORATION.txt` - Quick overview

**Database Files:**
- `COMPLETE-DATABASE-RESTORE.sql` - SQL file
- `restore-database.php` - Auto-import script

**Access URLs:**
- Admin: `/admin/login.php`
- Homepage: `/index.php`
- Products: `/pages/all-products.php`
- Restore: `/restore-database.php`

---

## ✅ Completion Status

- [ ] Database restored successfully
- [ ] Admin access verified
- [ ] Initial configuration done
- [ ] Content added
- [ ] Features tested
- [ ] Security checked
- [ ] Mobile tested
- [ ] Performance optimized
- [ ] Ready for launch! 🚀

---

**Date Completed:** _______________
**Tested By:** _______________
**Notes:** _______________

---

🎊 **Congratulations! Dorve House is ready to go!** 🎊
