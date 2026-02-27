# PHP System Documentation
## VILLA BINTARO REGENCY RT/RW Digital

## 📁 Struktur File PHP

```
rt-rw-system/
├── includes/
│   └── auth.php              # Authentication & session helper
├── api/
│   ├── config.php            # Database configuration
│   ├── users.php             # Users API endpoint
│   └── anggota_keluarga.php  # Family members API endpoint
├── index.php                 # Landing page
├── login.php                 # Login page
├── register.php              # Registration page
├── logout.php                # Logout handler
├── unauthorized.php          # Access denied page
├── error.php                 # Error page (404, 500, etc.)
├── reset_password.php        # Password reset utility
│
├── dashboard.php             # Admin dashboard
├── warga.php                 # Data warga management
├── kk.php                    # Kartu keluarga management
├── requests.php              # Request anggota management
├── pengumuman.php            # Pengumuman management
├── notifikasi.php            # Notifikasi management
├── layanan.php               # Layanan surat
├── iuran.php                 # Kas & iuran management
├── kegiatan.php              # Kegiatan management
├── laporan.php               # Laporan management
│
├── warga-saya.php            # User profile (warga)
├── keluarga-saya.php         # User family (warga)
└── iuran-saya.php            # User payments (warga)
```

## 🔐 Sistem Autentikasi

### Role-Based Access Control (RBAC)

| Role | Akses |
|------|-------|
| **admin** | Full access ke semua fitur |
| **rt** | Full access (seperti admin) |
| **rw** | Full access (seperti admin) |
| **warga** | Limited access (data sendiri saja) |

### Fungsi Authentication Helper

```php
// Cek apakah user sudah login
isLoggedIn()

// Dapatkan data user saat ini
getCurrentUser()

// Dapatkan role user saat ini
getCurrentUserRole()

// Cek apakah user memiliki role tertentu
hasRole(['admin', 'rt'])

// Require authentication dengan role tertentu
requireAuth([ROLE_ADMIN, ROLE_RT])

// Require authentication (role apapun)
requireLogin()

// Cek apakah user adalah staff (admin/rt/rw)
isStaff()

// Logout user
logout()
```

### Konstanta Role

```php
ROLE_ADMIN  = 'admin'
ROLE_RT     = 'rt'
ROLE_RW     = 'rw'
ROLE_WARGA  = 'warga'
```

## 🗄️ Database Configuration

File: `api/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wargavbr');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### Fungsi Database Helper

```php
// Dapatkan koneksi database
getDbConnection()

// Kirim response JSON
jsonResponse($data, $statusCode)

// Generate UUID
generateUUID()

// Hash password
hashPassword($password)

// Verify password
verifyPassword($password, $hash)
```

## 📝 Fungsi Utility

### Formatting

```php
// Format Rupiah
formatRupiah(1000000)  // Output: Rp 1.000.000

// Format tanggal
formatTanggal('2025-02-21')  // Output: 21/02/2025

// Format waktu
formatWaktu('2025-02-21 10:30:00')  // Output: 21/02/2025 10:30

// Time ago
timeAgo('2025-02-21 08:30:00')  // Output: 2 jam yang lalu
```

### Security

```php
// Sanitize input
sanitize($data)

// Generate CSRF token
generateCSRFToken()

// Verify CSRF token
verifyCSRFToken($token)
```

### Flash Messages

```php
// Set flash message
setFlash('success', 'Data berhasil disimpan')

// Get dan clear flash message
$flash = getFlash()
```

## 🚀 Cara Menggunakan

### 1. Setup Database

```bash
# Pastikan MariaDB/MySQL sudah running
# Import database schema dari folder database/
```

### 2. Konfigurasi Database

Edit file `api/config.php` sesuai dengan konfigurasi database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wargavbr');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 3. Akses Aplikasi

```
http://localhost/rt-rw-system/index.php
```

### 4. Login dengan Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@rtrw.com | admin123 |
| RT | rt@rtrw.com | rt123 |
| Warga | warga@rtrw.com | warga123 |

## 🔧 Error Handling

### Development Mode

File `includes/auth.php` sudah dikonfigurasi untuk development:

```php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Errors logged, not displayed
```

### Production Mode

Untuk production, ubah menjadi:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

### Custom Error Pages

- `error.php` - Handle 404, 500 errors
- `unauthorized.php` - Handle 403 errors

## 📊 Database Tables

Sistem ini menggunakan tabel-tabel berikut:

- `users` - User accounts
- `warga` - Citizen data
- `kartu_keluarga` - Family cards
- `pengumuman` - Announcements
- `notifikasi` - Notifications
- `layanan_surat` - Document requests
- `iuran` - Payments
- `kegiatan` - Activities
- `requests` - Family member requests

## 🔒 Security Best Practices

1. **Password Hashing**: Menggunakan `password_hash()` dengan `PASSWORD_DEFAULT`
2. **Session Management**: PHP sessions dengan timeout
3. **CSRF Protection**: Token-based CSRF protection
4. **Input Sanitization**: `htmlspecialchars()` untuk mencegah XSS
5. **Prepared Statements**: PDO prepared statements untuk mencegah SQL Injection
6. **Role-Based Access**: RBAC untuk membatasi akses fitur

## 🐛 Troubleshooting

### Error: "Database connection failed"

1. Pastikan MariaDB/MySQL running
2. Cek kredensial di `api/config.php`
3. Pastikan database `wargavbr` sudah dibuat

### Error: "Configuration file not found"

1. Pastikan file `api/config.php` ada
2. Cek path relative dari file yang memanggil

### Session tidak bekerja

1. Pastikan `session_start()` dipanggil sebelum output HTML
2. Cek konfigurasi session di php.ini
3. Pastikan cookies diaktifkan di browser

### Redirect loop

1. Clear browser cookies/cache
2. Cek logic di `requireAuth()` dan `isLoggedIn()`

## 📞 Support

Untuk pertanyaan atau bantuan, hubungi administrator sistem.

---

**© 2025 VILLA BINTARO REGENCY RT/RW Digital**
