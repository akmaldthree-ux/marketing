# Panduan Deploy — DSM Marketing Intelligence Platform
## Target: marketing.dthree.co.id

---

## Informasi Server

| Item | Detail |
|------|--------|
| Subdomain | `marketing.dthree.co.id` |
| Folder project | `/home/dthreeco/marketing.dthree.co.id/` |
| Document Root | `/home/dthreeco/marketing.dthree.co.id/public/` |
| cPanel user | `dthreeco` |
| Database prefix | `dthreeco_` |

---

## Persyaratan Server

Pastikan hosting sudah mendukung:
- PHP **8.2** atau **8.3** (wajib)
- Extension PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `intl`
- MySQL **5.7+** atau MariaDB **10.3+**
- **Composer** tersedia via SSH
- **mod_rewrite** aktif (biasanya sudah aktif di cPanel)
- SSH Terminal aktif (cPanel → Terminal)

---

## LANGKAH 1 — Buat Subdomain di cPanel

1. Login ke **cPanel** → cari menu **Subdomains**
2. Isi form:
   - **Subdomain:** `marketing`
   - **Domain:** `dthree.co.id`
   - **Document Root:** hapus isian otomatis, ganti dengan:
     ```
     /home/dthreeco/marketing.dthree.co.id/public
     ```
3. Klik **Create**

> ⚠️ **Penting:** Document Root harus mengarah ke folder `/public`, bukan ke root project.

---

## LANGKAH 2 — Buat Database MySQL di cPanel

1. Buka **cPanel → MySQL Databases**

2. **Buat Database Baru:**
   - Nama database: `marketing` → klik **Create Database**
   - Nama lengkap otomatis: `dthreeco_marketing`

3. **Buat User Database** (atau gunakan user yang sudah ada):
   - Jika belum ada user `dthreeco_root`, buat di bagian **MySQL Users**
   - Gunakan password yang kuat dan **simpan baik-baik**

4. **Tambahkan User ke Database:**
   - Scroll ke **Add User to Database**
   - Pilih user: `dthreeco_root`
   - Pilih database: `dthreeco_marketing`
   - Klik **Add** → centang **ALL PRIVILEGES** → **Make Changes**

Informasi database yang akan digunakan:
```
DB_DATABASE = dthreeco_marketing
DB_USERNAME = dthreeco_root
DB_PASSWORD = (password database kamu)
```

---

## LANGKAH 3 — Upload File Project ke Server

Buka **cPanel → Terminal** (SSH Terminal), lalu jalankan perintah berikut:

### 3a. Masuk ke direktori home
```bash
cd /home/dthreeco
```

### 3b. Hapus folder subdomain yang otomatis dibuat cPanel (biasanya kosong)
```bash
rm -rf marketing.dthree.co.id
```

### 3c. Clone repository dari GitHub
```bash
git clone https://github.com/akmaldthree-ux/marketing.git marketing.dthree.co.id
```

### 3d. Masuk ke folder project dan pindah ke branch yang benar
```bash
cd marketing.dthree.co.id
git checkout claude/cool-brahmagupta-yee82z
```

### 3e. Verifikasi file sudah ada
```bash
ls -la
```
Pastikan terlihat folder: `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`

---

## LANGKAH 4 — Konfigurasi File .env

### 4a. Salin file konfigurasi
```bash
cp .env.example .env
```

### 4b. Edit file .env
```bash
nano .env
```

Ubah isian berikut sesuai data server kamu:

```env
APP_NAME="DSM Marketing Intelligence Platform"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://marketing.dthree.co.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dthreeco_marketing
DB_USERNAME=dthreeco_root
DB_PASSWORD=isi_password_database_disini

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Simpan: tekan `Ctrl+X` → `Y` → `Enter`

---

## LANGKAH 5 — Jalankan Deploy Script

```bash
bash deploy.sh
```

Script ini akan otomatis menjalankan:
1. `composer install` — install semua dependency PHP
2. `php artisan key:generate` — generate APP_KEY
3. `php artisan migrate` — buat semua tabel di database
4. `php artisan db:seed` — isi data awal (user, toko, target, dll)
5. `php artisan config:cache` + `route:cache` + `view:cache` — optimasi
6. `chmod 775 storage bootstrap/cache` — set permission folder

Output yang diharapkan di akhir:
```
=========================================
  Deploy selesai!
  Akun login default:
  Admin    : admin@dsm.co.id / password
  PIC      : sinta@dsm.co.id / password
  Viewer   : viewer@dsm.co.id / password
=========================================
```

---

## LANGKAH 6 — Set PHP Version (jika perlu)

Jika server menggunakan PHP versi lama secara default:

1. Buka **cPanel → MultiPHP Manager**
2. Cari domain `marketing.dthree.co.id`
3. Pilih **PHP 8.2** atau **PHP 8.3**
4. Klik **Apply**

Verifikasi versi PHP aktif:
```bash
php -v
```

---

## LANGKAH 7 — Verifikasi

Buka browser → `https://marketing.dthree.co.id`

Harus muncul halaman login DSM Intelligence.

Login dengan akun default:

| Role  | Email               | Password |
|-------|---------------------|----------|
| Admin | admin@dsm.co.id     | password |
| PIC   | sinta@dsm.co.id     | password |
| Viewer| viewer@dsm.co.id    | password |

> ⚠️ **Segera ganti password** setelah login pertama melalui menu **Settings**.

---

## Struktur Folder di Server

```
/home/dthreeco/
└── marketing.dthree.co.id/        ← root project (git clone ke sini)
    ├── app/
    │   ├── Http/Controllers/
    │   └── Models/
    ├── bootstrap/
    ├── config/
    │   └── database.php
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    ├── public/                     ← Document Root subdomain
    │   ├── index.php
    │   └── .htaccess
    ├── resources/
    │   └── views/
    ├── routes/
    │   └── web.php
    ├── storage/                    ← harus writable (chmod 775)
    ├── vendor/                     ← dibuat oleh composer install
    ├── .env                        ← konfigurasi (jangan di-commit!)
    ├── .htaccess                   ← redirect root ke /public
    └── deploy.sh                   ← script deploy otomatis
```

---

## Troubleshooting

### Error 500 — Internal Server Error
```bash
cd /home/dthreeco/marketing.dthree.co.id
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
```
Cek log error:
```bash
tail -50 storage/logs/laravel.log
```

### Halaman muncul tapi CSS/tampilan rusak
- Pastikan `APP_URL` di `.env` menggunakan `https://marketing.dthree.co.id` (tanpa trailing slash)
- Pastikan Document Root di cPanel mengarah ke `.../public` bukan ke root project

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
php artisan config:cache
```

### Error: Database connection refused
- Pastikan `DB_HOST=127.0.0.1` (bukan `localhost` — kadang berbeda di cPanel)
- Cek ulang nama database, username, dan password
- Pastikan user sudah diberi ALL PRIVILEGES pada database

### Error: Composer not found
```bash
# Coba jalur alternatif di cPanel:
/usr/local/bin/composer install --no-dev --optimize-autoloader
```
Atau unduh Composer manual:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install --no-dev --optimize-autoloader
```

### Error: Permission denied pada storage/
```bash
chmod -R 775 /home/dthreeco/marketing.dthree.co.id/storage
chmod -R 775 /home/dthreeco/marketing.dthree.co.id/bootstrap/cache
```

### SSL / HTTPS tidak aktif
- Buka **cPanel → SSL/TLS** → aktifkan **AutoSSL** untuk domain `marketing.dthree.co.id`
- Tunggu 5–10 menit lalu coba akses kembali

---

## Update Aplikasi (jika ada perubahan kode)

```bash
cd /home/dthreeco/marketing.dthree.co.id

# Pull perubahan terbaru dari GitHub
git pull origin claude/cool-brahmagupta-yee82z

# Jalankan migrasi baru (jika ada)
php artisan migrate --force

# Refresh cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Ringkasan Perintah (Cheat Sheet)

```bash
# 1. Masuk ke folder project
cd /home/dthreeco/marketing.dthree.co.id

# 2. Clone (hanya pertama kali)
git clone https://github.com/akmaldthree-ux/marketing.git /home/dthreeco/marketing.dthree.co.id
git checkout claude/cool-brahmagupta-yee82z

# 3. Setup .env
cp .env.example .env
nano .env

# 4. Deploy
bash deploy.sh

# 5. Cek log jika ada error
tail -f storage/logs/laravel.log
```
