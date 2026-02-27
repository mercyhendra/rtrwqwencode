# 🏘️ VILLA BINTARO REGENCY - RT/RW Digital System

Sistem manajemen RT/RW digital untuk Villa Bintaro Regency.

**URL:** https://vbr.fakechefnats.site/

---

## ✨ Fitur Utama

### 👥 Manajemen Warga
- Data warga terpusat
- Kartu keluarga digital
- Request anggota keluarga online

### 📢 Komunikasi
- Pengumuman real-time
- Notifikasi otomatis
- Layanan surat online

### 💰 Keuangan
- Iuran warga digital
- Tracking pembayaran
- Laporan kas real-time

### 📅 Kegiatan
- Jadwal kegiatan
- Pendaftaran online
- Tracking peserta

---

## 🚀 Quick Start

### 1. Login

**Admin/Staff:**
- Email: `admin@rtrw.com` | Password: `admin123`
- Email: `rt@rtrw.com` | Password: `rt123`

**Warga:**
- Email: `warga@rtrw.com` | Password: `warga123`

### 2. Insert Sample Data (Jika Belum Ada)

```
https://vbr.fakechefnats.site/insert_sample_data.php
```

### 3. Test Semua Halaman

```
https://vbr.fakechefnats.site/test_pages.php
```

---

## 📄 Daftar Halaman

### Admin/Staff Pages
| Halaman | URL |
|---------|-----|
| Dashboard | [/dashboard.php](dashboard.php) |
| Data Warga | [/warga.php](warga.php) |
| Kartu Keluarga | [/kk.php](kk.php) |
| Request Anggota | [/requests.php](requests.php) |
| Pengumuman | [/pengumuman.php](pengumuman.php) |
| Notifikasi | [/notifikasi.php](notifikasi.php) |
| Layanan Surat | [/layanan.php](layanan.php) |
| Iuran | [/iuran.php](iuran.php) |
| Kegiatan | [/kegiatan.php](kegiatan.php) |
| Laporan | [/laporan.php](laporan.php) |

### User Pages
| Halaman | URL |
|---------|-----|
| Profil Saya | [/warga-saya.php](warga-saya.php) |
| Keluarga Saya | [/keluarga-saya.php](keluarga-saya.php) |
| Iuran Saya | [/iuran-saya.php](iuran-saya.php) |

---

## 🗄️ Database

**Database:** `wargavbr`  
**User:** `root`  
**Password:** (kosong)

### Tabel Utama
- `users` - Data warga
- `pengumuman` - Pengumuman
- `kegiatan` - Kegiatan
- `notifikasi` - Notifikasi
- `layanan_surat` - Layanan surat
- `iuran` - Iuran warga
- `anggota_keluarga` - Anggota keluarga
- `anggota_keluarga_request` - Request anggota

---

## 🔌 API Endpoints

### Pengumuman
```
GET    /api/pengumuman.php       - List pengumuman
POST   /api/pengumuman.php       - Tambah pengumuman
PUT    /api/pengumuman.php       - Update pengumuman
DELETE /api/pengumuman.php       - Hapus pengumuman
```

### Kegiatan
```
GET    /api/kegiatan.php         - List kegiatan
POST   /api/kegiatan.php         - Tambah/join kegiatan
PUT    /api/kegiatan.php         - Update kegiatan
```

### Notifikasi
```
GET    /api/notifikasi.php       - List notifikasi
PUT    /api/notifikasi.php       - Mark as read
```

### Layanan Surat
```
GET    /api/layanan.php          - List surat
POST   /api/layanan.php          - Buat surat
PUT    /api/layanan.php          - Update status
DELETE /api/layanan.php          - Hapus surat
```

### Iuran
```
GET    /api/iuran.php            - List iuran
POST   /api/iuran.php            - Catat iuran
PUT    /api/iuran.php            - Update/bayar iuran
```

### Warga
```
GET    /api/warga.php            - List warga
POST   /api/warga.php            - Tambah warga
PUT    /api/warga.php            - Update warga
DELETE /api/warga.php            - Hapus warga
```

### Requests
```
GET    /api/requests.php         - List requests
POST   /api/requests.php         - Buat request
PUT    /api/requests.php         - Approve/reject
DELETE /api/requests.php         - Hapus request
```

---

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Design:** Custom Premium CSS
- **Auth:** Session-based authentication

---

## 📁 Struktur Folder

```
rt-rw-system/
├── api/                    # API endpoints
│   ├── pengumuman.php
│   ├── kegiatan.php
│   ├── notifikasi.php
│   ├── layanan.php
│   ├── iuran.php
│   ├── warga.php
│   └── requests.php
├── includes/               # Includes & helpers
│   └── auth.php
├── database/               # Database scripts
│   ├── schema.sql
│   └── sample_data_lengkap.sql
├── css/                    # Stylesheets
│   └── style-premium.css
├── js/                     # JavaScript files
│   ├── main.js
│   └── auth.js
├── *.php                   # Halaman utama
├── insert_sample_data.php  # Script insert sample data
└── test_pages.php          # Test semua halaman
```

---

## 🔐 Security

- Password hashing dengan `password_hash()`
- CSRF protection
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- Role-based access control

---

## 📊 Sample Data

Sample data sudah include:
- 3 users (admin, RT, warga)
- 4 pengumuman
- 6 kegiatan
- 5 layanan surat
- 6 iuran
- 5 notifikasi
- 3 request anggota keluarga

---

## 🐛 Troubleshooting

### Database connection failed
```php
// Cek file api/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wargavbr');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Tabel tidak ada
```bash
# Import schema
mysql -u root wargavbr < database/schema.sql
```

### Data kosong
```
# Akses script insert sample data
https://vbr.fakechefnats.site/insert_sample_data.php
```

---

## 📞 Support

Untuk bantuan teknis, hubungi administrator sistem.

---

## 📝 License

Proprietary - VILLA BINTARO REGENCY

---

## 🎉 Credits

Developed for VILLA BINTARO REGENCY RT/RW Digital System

**Version:** 1.0.0  
**Last Updated:** February 2026
