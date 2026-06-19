# Panduan Deploy ke cPanel

## Persyaratan Server
- PHP >= 8.2 (direkomendasikan PHP 8.3)
- Extension PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- MySQL >= 5.7 / MariaDB >= 10.3
- Composer
- mod_rewrite aktif

---

## Langkah Deploy

### 1. Upload File ke cPanel

**Opsi A — via Git (direkomendasikan):**
```bash
# Di SSH Terminal cPanel:
cd ~/
git clone https://github.com/akmaldthree-ux/marketing.git dsm-marketing
cd dsm-marketing
git checkout claude/cool-brahmagupta-yee82z
```

**Opsi B — via File Manager:**
- Download ZIP dari GitHub branch `claude/cool-brahmagupta-yee82z`
- Extract ke folder di server (misal: `~/dsm-marketing/`)

---

### 2. Buat Database MySQL di cPanel
1. Buka **cPanel → MySQL Databases**
2. Buat database baru: `namauser_dsmmarketing`
3. Buat user MySQL baru dengan password kuat
4. Tambahkan user ke database dengan privilege **ALL PRIVILEGES**

---

### 3. Konfigurasi Domain/Subdomain
1. Buka **cPanel → Addon Domains** atau **Subdomains**
2. Set **Document Root** ke: `~/dsm-marketing/public`
   - Contoh: `/home/namauser/dsm-marketing/public`
3. Simpan

---

### 4. Setting .env
```bash
cd ~/dsm-marketing
cp .env.example .env
nano .env   # atau edit via File Manager
```

Edit nilai berikut:
```env
APP_URL=https://yourdomain.com
APP_KEY=          # akan diisi otomatis saat deploy.sh dijalankan

DB_DATABASE=namauser_dsmmarketing
DB_USERNAME=namauser_dbuser
DB_PASSWORD=passwordmu
```

---

### 5. Jalankan Deploy Script
```bash
cd ~/dsm-marketing
bash deploy.sh
```

Script ini akan otomatis:
- Install PHP dependencies (`composer install`)
- Generate `APP_KEY`
- Jalankan migrasi database
- Seed data awal (user, toko, target, dll)
- Optimize & cache konfigurasi

---

### 6. Verifikasi
Buka browser → `https://yourdomain.com`

Login dengan:
| Role  | Email               | Password |
|-------|---------------------|----------|
| Admin | admin@dsm.co.id     | password |
| PIC   | sinta@dsm.co.id     | password |
| Viewer| viewer@dsm.co.id    | password |

---

## Troubleshooting

**Error 500:**
```bash
php artisan config:clear
php artisan cache:clear
chmod -R 775 storage bootstrap/cache
```

**Halaman kosong / CSS tidak muncul:**
- Pastikan `APP_URL` di `.env` sesuai dengan domain
- Pastikan Document Root mengarah ke folder `public/`

**Database error:**
- Cek kredensial DB di `.env`
- Pastikan user DB punya privilege ALL PRIVILEGES

**Composer tidak tersedia:**
- cPanel biasanya menyediakan Composer di `/usr/local/bin/composer`
- Atau gunakan: `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php`

---

## Struktur Folder di Server
```
~/dsm-marketing/          ← root project
├── app/
├── bootstrap/
├── config/
├── database/
├── public/               ← Document Root (arahkan domain ke sini)
│   └── index.php
├── resources/
├── routes/
├── storage/
├── vendor/               ← dibuat saat composer install
├── .env                  ← konfigurasi environment
├── .htaccess             ← redirect root ke /public
└── deploy.sh             ← script deploy otomatis
```
