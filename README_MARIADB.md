# VILLA BINTARO REGENCY RT/RW Digital Management System

Sistem Informasi Manajemen RT/RW Digital dengan database MariaDB untuk pengelolaan lingkungan yang lebih efisien dan transparan.

## 🆕 Update Terbaru

✅ **Backend MariaDB** - Sekarang menggunakan database MariaDB untuk penyimpanan data yang lebih handal
✅ **API RESTful** - Komunikasi frontend-backend menggunakan API
✅ **Real-time Sync** - Data warga baru langsung muncul di admin panel

## 🚀 Quick Start

### 1. Setup Database

```bash
# Buka phpMyAdmin di http://localhost/phpmyadmin
# Import file database/schema.sql
# Atau via command line:
mysql -u root < database/schema.sql
```

### 2. Jalankan Aplikasi

1. Pastikan **XAMPP** (Apache + MariaDB) sudah running
2. Buka browser
3. Akses: `http://localhost/rt-rw-system/`

### 3. Login

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Admin** | admin@rtrw.com | admin123 | Full Access |
| **RT** | rt@rtrw.com | rt123 | Full Access |
| **Warga** | warga@rtrw.com | warga123 | Limited Access |

## 📁 Struktur File

```
rt-rw-system/
├── api/                        # Backend API (PHP)
│   ├── config.php             # Database configuration
│   └── users.php              # Users API endpoint
├── database/                   # Database files
│   └── schema.sql             # Database schema
├── css/
│   └── style.css              # Main stylesheet
├── js/
│   ├── database.js            # Database API client
│   ├── auth.js                # Legacy auth (localStorage)
│   └── main.js                # Main JavaScript
├── index.html                  # Landing page
├── login.html                  # Login page
├── register.html               # Register page
├── dashboard.html              # Dashboard admin
├── warga.html                  # Data warga (admin only)
├── warga-saya.html             # Data saya (warga)
├── kk.html                     # Kartu keluarga
├── pengumuman.html             # Pengumuman
├── notifikasi.html             # Notifikasi
├── layanan.html                # Layanan surat
├── iuran.html                  # Kas & iuran (admin)
├── iuran-saya.html             # Iuran saya (warga)
├── kegiatan.html               # Kegiatan
├── laporan.html                # Laporan (admin)
├── keluarga-saya.html          # Keluarga saya (warga)
├── README.md                   # This file
└── DATABASE_SETUP.md          # Detailed DB setup guide
```

## 🔧 Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MariaDB/MySQL** - Database
- **PDO** - Database abstraction layer
- **Password Hashing** - Secure password storage

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling dengan CSS Variables
- **JavaScript (ES6+)** - Client-side logic
- **Fetch API** - HTTP requests to backend

## 🔐 Sistem Role (Hak Akses)

| Role | Lihat Data | Edit Data | Hapus Data |
|------|-----------|-----------|------------|
| **Admin** | ✅ Semua | ✅ Semua | ✅ Semua |
| **RT** | ✅ Semua | ✅ Semua | ✅ Semua |
| **RW** | ✅ Semua | ✅ Semua | ✅ Semua |
| **Warga** | ❌ Hanya sendiri | ✅ Data sendiri | ❌ Tidak bisa |

## 📝 API Endpoints

### Users API (`api/users.php`)

```
GET  /api/users.php?action=get_all          - Get all users
GET  /api/users.php?action=get_one&id=1     - Get user by ID
POST /api/users.php?action=register         - Register new user
POST /api/users.php?action=login            - Login user
PUT  /api/users.php?action=update           - Update user
DELETE /api/users.php?action=delete&id=1    - Delete user
```

## 🗄️ Database Schema

Database `wargavbr` dengan tabel:
- **users** - Data pengguna (warga, admin, RT, RW)
- **anggota_keluarga** - Anggota keluarga per KK
- **pengumuman** - Pengumuman RT/RW
- **iuran** - Data iuran warga
- **layanan_surat** - Permohonan surat
- **kegiatan** - Kegiatan lingkungan
- **notifikasi** - Notifikasi untuk warga

## 🧪 Testing

### Test Registrasi
1. Buka `register.html`
2. Isi form dengan data baru (gunakan KK yang belum terdaftar)
3. Submit
4. ✅ User tersimpan di database MariaDB

### Test Admin Panel
1. Login sebagai admin (`admin@rtrw.com` / `admin123`)
2. Buka `warga.html` (Data Warga)
3. ✅ Data warga yang baru register langsung muncul!

### Test Edit/Delete
1. Di halaman Data Warga, klik ✏️ Edit
2. Update data dan save
3. Klik 🗑️ Delete untuk menghapus
4. ✅ Perubahan tersimpan di database

## ⚠️ Troubleshooting

### Data tidak muncul di admin panel
- Pastikan MariaDB sudah running
- Cek console log (F12) untuk error
- Refresh halaman dengan tombol 🔄

### Error database connection
- Cek XAMPP > MariaDB sudah running
- Cek database `wargavbr` sudah dibuat
- Cek file `api/config.php`

### API tidak merespon
- Pastikan Apache sudah running
- Akses via `http://localhost/` (bukan file://)
- Cek error log Apache

## 📚 Documentation

- **DATABASE_SETUP.md** - Panduan lengkap setup database
- **api/config.php** - Konfigurasi database
- **database/schema.sql** - Database schema lengkap

## 🔒 Security Best Practices

Untuk production:
- [ ] Ganti password default
- [ ] Gunakan HTTPS
- [ ] Set password untuk user root database
- [ ] Enable CSRF protection
- [ ] Add rate limiting untuk API
- [ ] Sanitize semua input user
- [ ] Use environment variables untuk config

## 📞 Support

Untuk pertanyaan atau bantuan:
1. Cek file `DATABASE_SETUP.md` untuk setup database
2. Cek console log (F12) untuk error messages
3. Pastikan XAMPP (Apache + MariaDB) sudah running

---

**© 2025 VILLA BINTARO REGENCY RT/RW Digital**  
Powered by MariaDB & PHP
