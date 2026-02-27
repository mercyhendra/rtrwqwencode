# Setup Database MariaDB - VILLA BINTARO REGENCY RT/RW Digital

## 📋 Prerequisites

1. **XAMPP** atau **MariaDB Server** terinstall
2. **Apache** dan **MariaDB** sudah berjalan
3. Browser modern (Chrome, Firefox, Edge)

## 🚀 Langkah Setup

### 1. Setup Database

1. Buka **phpMyAdmin** di `http://localhost/phpmyadmin`
2. Login dengan user: `root` (tanpa password)
3. Klik tab **SQL**
4. Copy-paste isi file `database/schema.sql`
5. Klik **Go** untuk execute

Atau via command line:
```bash
cd C:\QwenCode\rt-rw-system\database
mysql -u root < schema.sql
```

### 2. Konfigurasi API

File konfigurasi sudah ada di `api/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wargavbr');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Jika database Anda berbeda, edit file ini sesuai konfigurasi.

### 3. Test API

Buka browser dan akses:
```
http://localhost/rt-rw-system/api/users.php?action=get_all
```

Jika berhasil, akan muncul JSON:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "email": "admin@rtrw.com",
            "role": "admin",
            "nama": "Administrator",
            ...
        }
    ]
}
```

### 4. Jalankan Aplikasi

1. Pastikan Apache dan MariaDB berjalan di XAMPP
2. Buka browser
3. Akses: `http://localhost/rt-rw-system/`
4. Login dengan akun default:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@rtrw.com | admin123 |
| RT | rt@rtrw.com | rt123 |
| Warga | warga@rtrw.com | warga123 |

## 📁 Struktur Database

### Tabel Utama

1. **users** - Data pengguna (warga, admin, RT, RW)
2. **anggota_keluarga** - Anggota keluarga per KK
3. **pengumuman** - Pengumuman RT/RW
4. **iuran** - Data iuran warga
5. **layanan_surat** - Permohonan surat
6. **kegiatan** - Kegiatan lingkungan
7. **notifikasi** - Notifikasi untuk warga

### Default Users

Setelah import schema.sql, akan ada 3 user default:

```sql
-- Admin
Email: admin@rtrw.com
Password: admin123
Role: admin

-- Ketua RT
Email: rt@rtrw.com
Password: rt123
Role: rt

-- Warga Demo
Email: warga@rtrw.com
Password: warga123
Role: warga
```

## 🔧 Troubleshooting

### Error: "Database connection failed"

**Solusi:**
1. Pastikan MariaDB/MySQL sudah running di XAMPP
2. Cek database `wargavbr` sudah dibuat
3. Cek user `root` tanpa password

### Error: "Table doesn't exist"

**Solusi:**
1. Jalankan ulang file `schema.sql`
2. Pastikan tidak ada error saat import

### API tidak merespon

**Solusi:**
1. Pastikan Apache sudah running
2. Cek file `api/config.php` sudah benar
3. Cek error log Apache di `xampp/apache/logs/error.log`

### CORS Error di Browser

**Solusi:**
Aplikasi sudah mendukung CORS. Jika masih ada error:
1. Pastikan mengakses via `http://localhost/`
2. Jangan buka file HTML langsung (file://)

## 📝 Catatan Penting

1. **Password Hashing**: Password default menggunakan hash bcrypt
2. **UUID**: Setiap user memiliki UUID unik untuk security
3. **Timestamps**: created_at dan updated_at otomatis terupdate
4. **Foreign Keys**: Relasi antar tabel menggunakan foreign key constraints

## 🔐 Security Notes

Untuk production:
- [ ] Ganti password root database
- [ ] Gunakan HTTPS
- [ ] Tambahkan authentication middleware
- [ ] Enable prepared statements (sudah ada di code)
- [ ] Set environment variables untuk config database

## 📊 Testing

Test registrasi warga baru:
1. Buka `http://localhost/rt-rw-system/register.html`
2. Isi form dengan data baru
3. Submit
4. Cek database di phpMyAdmin - user baru akan muncul
5. Login dengan user baru di `login.html`

Test admin panel:
1. Login sebagai admin
2. Buka Data Warga
3. Data warga yang baru register akan muncul otomatis dari database!

---

**© 2025 VILLA BINTARO REGENCY RT/RW Digital**
