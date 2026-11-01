# PANDUAN DEPLOYMENT MULTI-ENVIRONMENT
## Event Sales Management System (Stand-PKTOS)

Aplikasi ini dibangun dengan arsitektur **Server-Agnostic** (Twelve-Factor App Configuration). Tidak ada hardcoding IP address, hostname, domain, maupun direktori Windows pada logika bisnis. Konfigurasi sepenuhnya diatur melalui file `.env`.

---

## DAFTAR ISI
1. [Spesifikasi & Kebutuhan Sistem](#1-spesifikasi--kebutuhan-sistem)
2. [Environment A: Windows + Laragon (Laptop Stand)](#2-environment-a-windows--laragon-laptop-stand)
   - [Akses Smartphone Kasir via Wi-Fi Lokal](#akses-smartphone-kasir-via-wi-fi-lokal)
   - [Akses Smartphone Kasir via Cloudflare Tunnel (Internet Publik Bebas Kuota Lokal)](#akses-smartphone-kasir-via-cloudflare-tunnel-internet-publik)
3. [Environment B: Linux VPS (Ubuntu / Debian + Nginx)](#3-environment-b-linux-vps-ubuntu--debian--nginx)
4. [Environment C: Shared Hosting (cPanel)](#4-environment-c-shared-hosting-cpanel)
5. [Environment D: Laptop Pinjaman / Pemulihan Darurat (Disaster Recovery)](#5-environment-d-laptop-pinjaman--pemulihan-darurat-disaster-recovery)
6. [Daftar Akun Pengujian Default](#6-daftar-akun-pengujian-default)

---

## 1. Spesifikasi & Kebutuhan Sistem

- **PHP Version**: 8.2, 8.3, atau 8.4 (Direkomendasikan PHP 8.4).
- **PHP Extensions**:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `bcmath`
  - `zip`
- **Database**: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+.
- **Node.js & NPM**: Node 18+ / 20+ / 22+ (untuk build Tailwind CSS & Vite).
- **Composer**: Composer 2.x.

---

## 2. Environment A: Windows + Laragon (Laptop Stand)

Lingkungan ini paling umum digunakan pada stand offline menggunakan laptop koordinator.

### Langkah Instalasi:
1. Pastikan **Laragon** telah terinstal dan service **Apache** & **MySQL** telah aktif (Start All).
2. Buka Terminal Laragon atau PowerShell di folder proyek `Stand-PKTOS`:
   ```powershell
   composer install
   npm install
   npm run build
   ```
3. Salin file environment:
   ```powershell
   copy .env.example .env
   php artisan key:generate
   ```
4. Konfigurasikan `.env`:
   ```env
   APP_NAME="Stand-PKTOS"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=stand_pktos
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Buat database `stand_pktos` dan jalankan migrasi beserta data awal:
   ```powershell
   php artisan migrate --seed
   ```
6. Jalankan server Laravel:
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```

---

### Akses Smartphone Kasir via Wi-Fi Lokal
Agar petugas kasir dapat membuka aplikasi di HP masing-masing melalui satu jaringan Wi-Fi atau Hotspot HP yang sama:

1. Buka PowerShell di laptop dan cek IP lokal:
   ```powershell
   ipconfig
   ```
   Cari baris `IPv4 Address`, misalnya `192.168.1.15` (atau `192.168.43.x` jika memakai Tethering HP).
2. Pastikan Laravel dijalankan dengan opsi `--host=0.0.0.0`:
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```
3. Pada browser HP petugas (Chrome/Safari), buka alamat:
   ```text
   http://192.168.1.15:8000
   ```
4. Kasir langsung dapat login dengan akun masing-masing (misal `budi`, `siti`) dan memproses transaksi secara bersamaan!

---

### Akses Smartphone Kasir via Cloudflare Tunnel (Internet Publik)
Jika Wi-Fi stand memiliki isolasi klien (AP isolation) yang menghalangi koneksi antar perangkat lokal, gunakan **Cloudflare Tunnel (gratis & instan)**:

1. Unduh binary `cloudflared` untuk Windows: [https://github.com/cloudflare/cloudflared/releases](https://github.com/cloudflare/cloudflared/releases).
2. Buka terminal di laptop dan jalankan:
   ```powershell
   cloudflared tunnel --url http://localhost:8000
   ```
3. Terminal akan menghasilkan URL publik HTTPS instan, contoh:
   ```text
   https://random-stand-name.trycloudflare.com
   ```
4. Bagikan link tersebut ke seluruh kasir di stand. Semua HP kasir dapat mengakses sistem dari jaringan internet mana pun (paket data seluler masing-masing) dengan koneksi HTTPS yang aman dan terenkripsi!

---

## 3. Environment B: Linux VPS (Ubuntu / Debian + Nginx)

Untuk deployment online terpusat di server cloud (DigitalOcean, Linode, AWS, dsb).

### 1. Install Dependensi Server
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.4-fpm php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-bcmath php8.4-curl php8.4-zip git unzip
```

### 2. Setup Proyek & Permission
```bash
cd /var/www
git clone <repository_url> stand-pktos
cd stand-pktos

composer install --no-dev --optimize-autoloader
npm install
npm run build

cp .env.example .env
nano .env # Sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD, dan APP_URL
php artisan key:generate
php artisan migrate --seed --force

# Atur permission storage dan cache
sudo chown -R www-data:www-data /var/www/stand-pktos/storage /var/www/stand-pktos/bootstrap/cache
sudo chmod -R 775 /var/www/stand-pktos/storage /var/www/stand-pktos/bootstrap/cache
```

### 3. Konfigurasi Nginx Server Block
Buat file `/etc/nginx/sites-available/stand-pktos`:
```nginx
server {
    listen 80;
    server_name stand.domainanda.com;
    root /var/www/stand-pktos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi dan pasang SSL gratis Let's Encrypt:
```bash
sudo ln -s /etc/nginx/sites-available/stand-pktos /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d stand.domainanda.com
```

### 4. Cache Optimasi Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Environment C: Shared Hosting (cPanel)

Jika menggunakan shared hosting cPanel yang mendukung PHP 8.2+:

1. **Persyaratan Minimum**: Pastikan cPanel mendukung PHP 8.2 atau 8.4 dan ekstensi `pdo_mysql`, `bcmath`, `mbstring`, `zip`.
2. **Build Aset di Komputer Lokal Terlebih Dahulu**:
   Jalankan `npm run build` dan `composer install --no-dev` di laptop sebelum mengunggah.
3. **Struktur Direktori**:
   - Unggah seluruh folder proyek di luar folder `public_html` (misal di `/home/username/stand_app`).
   - Salin isi dari folder `public/` ke dalam folder `public_html/`.
   - Buka file `public_html/index.php` dan sesuaikan path autoload:
     ```php
     require __DIR__.'/../stand_app/vendor/autoload.php';
     $app = require_once __DIR__.'/../stand_app/bootstrap/app.php';
     ```
4. **Setup Database**:
   - Buat MySQL Database dan MySQL User di cPanel MySQL Databases Wizard.
   - Edit file `.env` di `/home/username/stand_app/.env` dengan kredensial database tersebut.
   - Jalankan migration via Terminal cPanel: `php artisan migrate --seed --force` (atau import file sql melalui phpMyAdmin).

---

## 5. Environment D: Laptop Pinjaman / Pemulihan Darurat (Disaster Recovery)

Jika laptop utama rusak atau mati tiba-tiba saat stand sedang beroperasi:

### Langkah Pemulihan Cepat (< 5 Menit):
1. **Ekspor Database Terkini dari Laptop Lama** (jika masih bisa diakses):
   ```powershell
   mysqldump -u root stand_pktos > backup_stand.sql
   ```
2. **Salin Folder Proyek** ke Flashdisk atau Google Drive lalu pindahkan ke laptop pinjaman.
3. **Di Laptop Pinjaman**:
   - Pastikan Laragon / PHP & MySQL terpasang.
   - Buka terminal di folder proyek:
     ```powershell
     # Buat database kosong
     mysql -u root -e "CREATE DATABASE stand_pktos CHARACTER SET utf8mb4;"
     # Restore data transaksi
     mysql -u root stand_pktos < backup_stand.sql
     ```
   - Jalankan server:
     ```powershell
     php artisan serve --host=0.0.0.0 --port=8000
     ```
4. Semua data penjualan, snapshot invoice, dan audit log **tetap utuh 100% tanpa ada data yang hilang**. Kasir langsung dapat melanjutkan transaksi.

---

## 6. Daftar Akun Pengujian Default

Setelah menjalankan `php artisan migrate --seed`, akun berikut siap digunakan:

| No | Peran / Role | Nama | Username | Email | Password Default |
|---|---|---|---|---|---|
| 1 | **Admin Koordinator** | Koordinator Stand (Admin) | `admin` | `admin@stand-pktos.local` | `password123` |
| 2 | **Petugas 1 (Shift 1)** | Budi Santoso | `budi` | `budi@stand-pktos.local` | `password123` |
| 3 | **Petugas 2 (Shift 1)** | Siti Rahma | `siti` | `siti@stand-pktos.local` | `password123` |
| 4 | **Petugas 3 (Shift 2)** | Andi Pratama | `andi` | `andi@stand-pktos.local` | `password123` |
| 5 | **Petugas 4 (Shift 2)** | Dewi Lestari | `dewi` | `dewi@stand-pktos.local` | `password123` |

*Catatan: Tombol "Akses Cepat Pengujian" tersedia pada halaman login untuk mengisi username dan password secara otomatis dengan sekali klik.*

