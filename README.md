# Aplikasi Web Sistem Absensi Sekolah Berbasis QR Code

[![Continuous Integration](https://github.com/ikhsan3adi/absensi-sekolah-qr-code/actions/workflows/ci.yml/badge.svg)](https://github.com/ikhsan3adi/absensi-sekolah-qr-code/actions/workflows/ci.yml)
![GitHub Repo stars](https://img.shields.io/github/stars/ikhsan3adi/absensi-sekolah-qr-code?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/ikhsan3adi/absensi-sekolah-qr-code?style=social)
![GitHub forks](https://img.shields.io/github/forks/ikhsan3adi/absensi-sekolah-qr-code?style=social)

![Preview](./screenshots/hero.png)

Aplikasi Web Sistem Absensi Sekolah Berbasis QR Code adalah sebuah proyek yang bertujuan untuk mengotomatisasi proses absensi di lingkungan sekolah menggunakan teknologi QR code. Aplikasi ini dikembangkan dengan menggunakan framework CodeIgniter 4 dan didesain untuk mempermudah pengelolaan dan pencatatan kehadiran siswa dan guru.

> [Instalasi & Cara Penggunaan](#cara-penggunaan)

## Fitur Utama

- **QR Code scanner.** Setiap siswa/guru menunjukkan qr code kepada perangkat yang dilengkapi dengan kamera. Aplikasi akan memvalidasi QR code dan mencatat kehadiran siswa ke dalam database.
- **Notifikasi Presensi via WhatsApp**. Setelah berhasil scan dan presensi, pemberitahuan dikirim ke nomor hp siswa melalui whatsapp.
- **Login petugas.**
- **Dashboard petugas.** Petugas sekolah dapat dengan mudah memantau kehadiran siswa dalam periode waktu tertentu melalui tampilan yang disediakan.
- **QR Code generator & downloader.** Petugas yang sudah login akan men-generate dan/atau mendownload qr code setiap siswa/guru. Setiap siswa akan diberikan QR code unik yang terkait dengan identitas siswa. QR code ini akan digunakan saat proses absensi.
- **Ubah data absen siswa/guru.** Petugas dapat mengubah data absensi setiap siswa/guru. Misalnya mengubah data kehadiran dari `tanpa keterangan` menjadi `sakit` atau `izin`.
- **Tambah, Ubah, Hapus(CRUD) data siswa/guru.**
- **Tambah, Ubah, Hapus(CRUD) data kelas.**
- **Lihat, Tambah, Ubah, Hapus(CRUD) data petugas.** (khusus petugas yang login sebagai **`superadmin`**).
- **Generate Laporan.** Generate laporan dalam bentuk pdf.
- **Import Banyak Siswa.** Menggunakan CSV delimiter koma (,), Contoh: [CSV](https://github.com/ikhsan3adi/absensi-sekolah-qr-code/blob/141ef728f01b14b89b43aee2c0c38680b0b60528/public/assets/file/csv_siswa_example.csv).

> [!NOTE]
>
> ## Framework dan Library Yang Digunakan
>
> - [CodeIgniter 4](https://github.com/codeigniter4/CodeIgniter4)
> - [Material Dashboard Bootstrap 4](https://www.creative-tim.com/product/material-dashboard-bs4)
> - [Myth Auth Library](https://github.com/lonnieezell/myth-auth)
> - [Endroid QR Code Generator](https://github.com/endroid/qr-code)
> - [ZXing JS QR Code Scanner](https://github.com/zxing-js/library)
>
> ---
>
> - [Fonnte](https://fonnte.com/) - WhatsApp API untuk mengirim pesan notifikasi

## Screenshots

### Tampilan Halaman QR Scanner

![QR Scanner view](./screenshots/qr-scanner.jpeg)

### Tampilan Absen Masuk dan Pulang

![QR Scanner absen](./screenshots/absen.jpg)

> #### Notifikasi via WhatsApp
>
> ![Notifikasi WA](./screenshots/notif-wa.png)

### Tampilan Login Petugas

![Login](./screenshots/login.jpeg)

### Tampilan Dashboard Petugas

![Dashboard](./screenshots/dashboard.png)

### Tampilan CRUD Data Absen

| Siswa (Dengan Data Kelas)                          |                       Guru                       |
| -------------------------------------------------- | :----------------------------------------------: |
| ![CRUD Absen Siswa](./screenshots/absen-siswa.png) | ![CRUD Absen Guru](./screenshots/absen-guru.png) |

### Tampilan Ubah Data Kehadiran

<p align="center">
  <img src="./screenshots/ubah-kehadiran.jpeg" height="320px" style="object-fit:cover" alt="Ubah Data Kehadiran" title="Ubah Data Kehadiran">
</p>

### Tampilan CRUD Data Siswa & Guru

| Siswa                                            |                      Guru                      |
| ------------------------------------------------ | :--------------------------------------------: |
| ![CRUD Data Siswa](./screenshots/data-siswa.png) | ![CRUD Data Guru](./screenshots/data-guru.png) |

### Tampilan CRUD Data Kelas & Jurusan

![CRUD Data Siswa](./screenshots/kelas-jurusan.png)

### Tampilan Generate QR Code dan Generate Laporan

| Generate QR                                   |                Generate Laporan                |
| --------------------------------------------- | :--------------------------------------------: |
| ![Generate QR](./screenshots/generate-qr.png) | ![Generate Laporan](./screenshots/laporan.png) |

## Cara Penggunaan

### Persyaratan

- [Composer](https://getcomposer.org/).
- PHP 8.1+ dan MySQL/MariaDB atau [XAMPP](https://www.apachefriends.org/download.html) versi 8.1+ dengan mengaktifkan extension `intl` dan `gd`.
- Pastikan perangkat memiliki kamera/webcam untuk menjalankan qr scanner. Bisa juga menggunakan kamera HP dengan bantuan software DroidCam.

### Instalasi

- Clone/Download source code proyek ini.

- Install dependencies yang diperlukan dengan cara menjalankan perintah berikut di terminal:

  ```shell
  composer install
  ```

- Jika belum terdapat file `.env`, rename file `.env.example` menjadi `.env`

- Buat database `db_absensi`(sesuaikan dengan yang terdapat di `.env`) di phpMyAdmin / mysql

- Jalankan migrasi database untuk membuat struktur tabel yang diperlukan. Ketikkan perintah berikut di terminal:

  ```shell
  php spark migrate --all
  ```

- Jalankan web server (contoh Apache, XAMPP, etc)
- Atau gunakan `php spark serve` (atur baseURL di `.env` menjadi `http://localhost:8080/` terlebih dahulu).
- Lalu jalankan aplikasi di browser.
- Login menggunakan krendensial superadmin:

  ```txt
  username : superadmin
  password : superadmin
  ```

- Izinkan akses kamera.

### Konfigurasi

> [!IMPORTANT]
>
> - Konfigurasi file `.env` untuk mengatur base url(terutama jika melakukan hosting), koneksi database dan pengaturan lainnya sesuai dengan lingkungan pengembangan Anda.
>
> - Untuk mengaktifkan **notifikasi WhatsApp**, pertama-tama ubah variabel `.env` berikut dari `false` menjadi `true`.
>
>   ```sh
>   # .env
>   # WA_NOTIFICATION=false # sebelum
>   WA_NOTIFICATION=true
>   ```
>
>   Lalu masukkan token WhatsApp API.
>
>   ```sh
>   # .env
>   WA_NOTIFICATION=true
>   WHATSAPP_PROVIDER=Fonnte
>   WHATSAPP_TOKEN=XXXXXXXXXXXXXXXXX # ganti dengan token anda
>   ```
>
>   _**Untuk mendapatkan token, daftar di website [fonnte](https://md.fonnte.com/new/register.php) terlebih dahulu. Lalu daftarkan device anda dan [dapatkan token Fonnte Whatsapp API](https://docs.fonnte.com/token-api-key/)**_
>
> - Untuk mengubah konfigurasi nama sekolah, tahun ajaran logo sekolah dll sudah disediakan pengaturan (khusus untuk superadmin).
>
> - Logo Sekolah Rekomendasi 100x100px atau 1:1 dan berformat PNG/JPG.
>
> - Jika ingin mengubah email, username & password dari superadmin, buka file `app\Database\Migrations\2023-08-18-000004_AddSuperadmin.php` lalu ubah & sesuaikan kode berikut:
>
>   ```php
>   // INSERT INITIAL SUPERADMIN
>   $email = 'adminsuper@gmail.com';
>   $username = 'superadmin';
>   $password = 'superadmin';
>   ```

## Deploy ke Shared Hosting

Panduan ini untuk hosting cPanel/shared hosting umum (Niagahoster, Rumahweb, Hostinger, dll) yang biasanya **document root**-nya tidak bisa diarahkan langsung ke folder `public/` proyek CodeIgniter. Ada 2 cara: dengan SSH (composer & spark tersedia) atau tanpa SSH (upload manual + import SQL).

### Persyaratan Hosting

- PHP 8.1+ dengan extension `intl`, `mbstring`, `mysqli`, dan `gd` aktif (aktifkan lewat menu **Select PHP Version** di cPanel).
- Database MySQL/MariaDB (buat lewat menu **MySQL Databases**).
- Akses **File Manager**/FTP, dan idealnya akses **SSH** + **Composer** (banyak cPanel modern sudah menyediakan menu **Terminal** & **Setup Node.js/PHP App**).

### A. Dengan akses SSH (direkomendasikan)

1. Upload/clone seluruh source code ke server. Lokasinya tergantung apakah document root hosting Anda bisa diubah (lihat langkah 5):
   - Bisa diubah → upload ke folder **di luar** `public_html`, misalnya `~/siabdi`.
   - Tidak bisa diubah (harus tetap `public_html`) → upload **langsung ke dalam** `public_html`.
2. Masuk ke folder proyek lalu install dependency production:

   ```shell
   cd ~/siabdi   # atau cd ~/public_html jika upload langsung ke public_html
   composer install --no-dev --optimize-autoloader
   ```

3. Salin `.env.example` menjadi `.env`, lalu atur:

   ```sh
   CI_ENVIRONMENT = production
   app.baseURL = 'https://namadomainanda.com/'

   database.default.hostname = localhost
   database.default.database = nama_database_cpanel
   database.default.username = user_database_cpanel
   database.default.password = password_database_cpanel
   ```

4. Jalankan migrasi database:

   ```shell
   php spark migrate --all
   ```

   Atau jika lebih mudah, import langsung file [`database/siabdi.sql`](database/siabdi.sql) lewat phpMyAdmin (lihat bagian [Import Database](#import-database) di bawah).

5. Arahkan folder web-accessible ke aplikasi:

   - **Jika hosting mengizinkan mengubah document root** — arahkan document root domain/subdomain ke folder `~/siabdi/public` lewat menu **Domains**/**Addon Domains** di cPanel (kolom "Document Root"). Ini cara paling bersih karena tidak perlu mengubah file apa pun.

   - **Jika harus tetap pakai `public_html`** (tidak bisa ubah document root) — pastikan seluruh proyek (folder `app/`, `public/`, `vendor/`, `writable/`, file `.htaccess`, `.env`, dll — sesuai langkah 1) memang berada langsung di dalam `public_html/`. Proyek ini sudah menyertakan file [`.htaccess`](.htaccess) di root yang otomatis meneruskan semua request ke folder `public/`, jadi tidak perlu mengedit `index.php` atau memindahkan file apa pun — cukup pastikan `.htaccess` ini ikut ter-upload. File sensitif (`.env`, folder `app/`, `writable/`, `vendor/`) tetap **tidak bisa diakses langsung** lewat URL karena semua request selalu diteruskan ke dalam `public/` terlebih dahulu.

6. Set permission folder `writable/` menjadi `755` (atau `775` jika masih gagal menulis):

   ```shell
   chmod -R 755 ~/siabdi/writable
   ```

7. Buka domain Anda di browser, lalu login dengan akun superadmin default (`superadmin` / `superadmin`) dan **segera ganti password**-nya.

### B. Tanpa akses SSH (upload manual)

1. Jalankan `composer install --no-dev --optimize-autoloader` **di komputer lokal** terlebih dahulu (supaya folder `vendor/` sudah lengkap), lalu compress seluruh proyek (termasuk folder `vendor/`) menjadi `.zip`.
2. Upload & extract `.zip` tersebut lewat **File Manager** cPanel — ke folder di luar `public_html` (misal `~/siabdi`) jika document root bisa diubah, atau **langsung ke dalam** `public_html` jika tidak bisa (lihat penjelasan langkah 5 pada bagian [A](#a-dengan-akses-ssh-direkomendasikan)).
3. Atur `.env` (langkah 3 di bagian A), lalu set permission folder `writable/` lewat File Manager: klik kanan folder → **Permissions** → `755`.
4. Untuk membuat struktur tabel & data awal (karena `php spark migrate` butuh terminal), import database lewat phpMyAdmin — lihat langkah di bawah.

### Import Database

Karena banyak shared hosting tidak menyediakan SSH, disediakan file SQL siap import di [`database/siabdi.sql`](database/siabdi.sql) sebagai alternatif dari `php spark migrate --all`. File ini berisi seluruh struktur tabel (termasuk tabel otentikasi Myth\Auth) **dan** data seeding awal: status kehadiran, daftar jurusan (OTKP/BDP/AKL/RPL), daftar kelas (X/XI/XII), pengaturan umum default, serta 1 akun superadmin.

1. Buat database kosong lewat menu **MySQL Databases** di cPanel, lalu buat user database dan berikan **All Privileges** ke database tersebut.
2. Buka **phpMyAdmin**, pilih database yang baru dibuat, buka tab **Import**.
3. Pilih file `database/siabdi.sql`, lalu klik **Go**/**Kirim**.
4. Setelah import selesai, sesuaikan kredensial database tersebut ke file `.env` (lihat langkah 3 di atas).
5. Login dengan akun superadmin default:

   ```txt
   username : superadmin
   password : superadmin
   ```

   **Segera ganti password ini setelah login pertama kali.**

> [!NOTE]
> File `database/siabdi.sql` **tidak** berisi data dummy siswa/guru — hanya data referensi/master yang dibutuhkan aplikasi agar bisa langsung dipakai. Tambahkan data siswa, guru, kelas tambahan, dll melalui panel admin setelah login.

## Kesimpulan

Dengan aplikasi web sistem absensi sekolah berbasis QR code ini, diharapkan proses absensi di sekolah menjadi lebih efisien dan terotomatisasi. Proyek ini dapat diadaptasi dan dikembangkan lebih lanjut sesuai dengan kebutuhan dan persyaratan sekolah Anda.

Jangan lupa beri star ya...⭐⭐⭐

## Contributing 🤝

Kami menerima kontribusi dari komunitas terbuka untuk meningkatkan aplikasi ini. Jika Anda menemukan masalah, bug, atau memiliki saran untuk peningkatan, silakan buat issue baru dalam repositori ini atau ajukan pull request.

## Donasi ❤

[![Donate saweria](https://img.shields.io/badge/Donate-Saweria-red?style=for-the-badge&link=https%3A%2F%2Fsaweria.co%2Fxiboxann)](https://saweria.co/xiboxann)

## Star History

<a href="https://www.star-history.com/#ikhsan3adi/absensi-sekolah-qr-code&Date">
 <picture>
   <source media="(prefers-color-scheme: dark)" srcset="https://api.star-history.com/svg?repos=ikhsan3adi/absensi-sekolah-qr-code&type=Date&theme=dark" />
   <source media="(prefers-color-scheme: light)" srcset="https://api.star-history.com/svg?repos=ikhsan3adi/absensi-sekolah-qr-code&type=Date" />
   <img alt="Star History Chart" src="https://api.star-history.com/svg?repos=ikhsan3adi/absensi-sekolah-qr-code&type=Date" />
 </picture>
</a>

## Kontributor 🛠️

- [@ikhsan3adi](https://www.github.com/ikhsan3adi)
- [@reactmore](https://www.github.com/reactmore)
- [@janglapuk](https://www.github.com/janglapuk)
- [@nanda443](https://www.github.com/nanda443)
- [@kevindoni](https://www.github.com/kevindoni)
- [@pandigresik](https://github.com/pandigresik)
