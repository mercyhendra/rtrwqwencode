# 📦 Backup & Restore Guide
## RT/RW Digital Management System

---

## 🔐 Cara Membuat Backup

### **Opsi 1: Via Browser (Termudah - Recommended)**

1. **Pastikan XAMPP/Laragon sudah running**
   - Apache: ✅ Running
   - MariaDB/MySQL: ✅ Running

2. **Buka browser dan akses:**
   ```
   http://localhost/rt-rw-system/backup.php
   ```

3. **Tunggu proses backup selesai**
   - Script akan otomatis membuat ZIP file
   - Database akan di-export otomatis
   - Download akan otomatis dimulai

4. **Simpan file backup di tempat aman**
   - Filename: `rt-rw-backup_YYYY-MM-DD_HHMMSS.zip`
   - Ukuran: Sekitar 1-5 MB
   - Lokasi download: Folder `backups/`

---

### **Opsi 2: Manual (Tanpa Script)**

#### **A. Backup File Project**

1. **Copy folder project:**
   ```
   C:\QwenCode\rt-rw-system\
   ```

2. **Paste ke tempat aman:**
   - External hard drive
   - Google Drive
   - Dropbox
   - USB flash drive

#### **B. Backup Database**

1. **Buka phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Pilih database `wargavbr`**

3. **Klik tab "Export"**

4. **Pilih "Quick" export method**

5. **Format: SQL**

6. **Klik "Go"**

7. **Simpan file SQL** dengan nama:
   ```
   wargavbr_backup_YYYY-MM-DD.sql
   ```

---

## 📥 Cara Restore Backup

### **Opsi 1: Restore dari ZIP Backup**

1. **Extract file ZIP** ke folder tujuan:
   ```
   C:\QwenCode\rt-rw-system\
   ```

2. **Buka phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

3. **Drop database lama (jika ada):**
   ```sql
   DROP DATABASE IF EXISTS wargavbr;
   ```

4. **Buat database baru:**
   ```sql
   CREATE DATABASE wargavbr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Pilih database `wargavbr`**

6. **Klik tab "Import"**

7. **Choose File:**
   - Pilih file `database_backup.sql` dari dalam ZIP

8. **Klik "Go"**

9. **Done!** Database sudah di-restore

---

### **Opsi 2: Restore Manual**

1. **Copy folder project** ke lokasi semula:
   ```
   C:\QwenCode\rt-rw-system\
   ```

2. **Restore database:**
   - Buka phpMyAdmin
   - Pilih database `wargavbr`
   - Import file SQL backup

---

## 📁 Struktur Backup

```
rt-rw-backup_2025-02-20_143022.zip
│
├── index.html
├── login.html
├── register.html
├── dashboard.html
├── warga.html
├── kk.html
├── keluarga-saya.html
├── ... (semua file HTML)
│
├── css/
│   └── style.css
│
├── js/
│   ├── database.js
│   ├── auth.js
│   └── main.js
│
├── api/
│   ├── config.php
│   ├── users.php
│   └── anggota_keluarga.php
│
├── database/
│   ├── schema.sql
│   ├── updates/
│   └── fixes/
│
└── database_backup.sql  ← Database export
```

---

## 🗓️ Jadwal Backup yang Disarankan

| Jenis Backup | Frekuensi | Kapan |
|--------------|-----------|-------|
| **Full Backup** (File + DB) | Setiap minggu | Setiap Senin pagi |
| **Database Only** | Setiap hari | Setiap malam (auto) |
| **Before Update** | Setiap kali | Sebelum update besar |

---

## 💾 Tips Penyimpanan Backup

### **Lokasi Penyimpanan:**

1. **Local Storage:**
   ```
   D:\Backup\RT-RW\
   ```

2. **Cloud Storage:**
   - Google Drive
   - Dropbox
   - OneDrive

3. **External Storage:**
   - USB Flash Drive
   - External Hard Drive

### **Naming Convention:**

Gunakan format:
```
rt-rw-backup_YYYY-MM-DD_description.zip
```

Contoh:
```
rt-rw-backup_2025-02-20_initial-release.zip
rt-rw-backup_2025-02-27_before-update.zip
rt-rw-backup_2025-03-05_weekly-backup.zip
```

---

## 🔍 Troubleshooting

### **Error: "Failed to create ZIP file"**

**Solusi:**
1. Cek permission folder `backups/`
2. Pastikan ada space cukup di disk
3. Close aplikasi yang sedang akses file

### **Error: "Database export failed"**

**Solusi:**
1. Pastikan MariaDB/MySQL running
2. Cek kredensial di `backup.php`
3. Export manual via phpMyAdmin

### **File ZIP Corrupt**

**Solusi:**
1. Download ulang
2. Cek ukuran file (jangan 0 bytes)
3. Gunakan backup sebelumnya

---

## 📊 Checklist Backup

Sebelum menutup project, pastikan:

- [ ] Backup via browser sudah dibuat
- [ ] File ZIP sudah di-download
- [ ] Backup disimpan di minimal 2 tempat (local + cloud)
- [ ] File backup bisa dibuka (test extract)
- [ ] Database bisa di-restore (test restore)

---

## 🆘 Emergency Contact

Jika ada masalah dengan backup/restore:

1. **Cek log file** di `backups/` folder
2. **Test restore** di environment berbeda
3. **Gunakan backup sebelumnya** jika yang terbaru bermasalah

---

## 📞 Quick Commands

### **Backup Database via Command Line:**
```bash
mysqldump -u root wargavbr > backup_manual.sql
```

### **Restore Database via Command Line:**
```bash
mysql -u root wargavbr < backup_manual.sql
```

---

**Last Updated:** 2025-02-20  
**Version:** 1.0

---

**💡 Remember:** Backup adalah teman terbaik Anda! Selalu backup sebelum melakukan perubahan besar.
