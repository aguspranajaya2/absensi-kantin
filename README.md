# Absensi Kantin

Aplikasi Laravel untuk absensi kantin dengan QR Code, panel admin, SQLite, riwayat, rekap bulanan, dan ekspor CSV.

## Menjalankan secara lokal

Gunakan PHP 8.3 atau lebih baru dan Composer. Dari PowerShell pada folder proyek:

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
php artisan migrate --seed
php artisan app:create-admin
php artisan serve
```

Buka `http://127.0.0.1:8000`. Database lokal berada di `database/database.sqlite`. Aset CSS dan JavaScript dilayani langsung dari `public/assets`, jadi Node.js tidak diperlukan untuk menjalankan versi ini. Kamera browser memerlukan izin kamera dan `localhost` atau HTTPS.

Tidak ada akun admin bawaan atau pendaftaran publik. Buat akun admin dari terminal dengan `php artisan app:create-admin`; kata sandi diminta secara tersembunyi dan harus minimal 12 karakter.

## Fitur

- Pencatatan masuk dan pulang melalui kamera, scanner USB, atau input ID.
- Profil pengguna dengan ID unik otomatis, QR Code, status aktif, dan fitur cetak.
- Panel admin berautentikasi untuk mengelola pengguna.
- Riwayat dengan filter tanggal/jenis, rekap bulanan, dan unduhan CSV.
- Database relasional; catatan absensi menyimpan snapshot nama dan ID agar riwayat tetap terbaca setelah profil dihapus atau diubah.
- Impor sekali klik untuk data `localStorage` lama pada browser/perangkat yang sama. Masuk sebagai admin dan pilih **Impor data lama** jika pemberitahuan muncul.

## Keamanan

Admin tidak dapat didaftarkan dari halaman publik. Rute admin mewajibkan sesi login dan peran admin; login dibatasi lima percobaan per menit per email/IP. Form memakai perlindungan CSRF dan validasi server. Database menegakkan ID pengguna unik serta maksimal satu catatan Masuk dan Pulang per pengguna per hari. Input ditampilkan dengan escaping Blade, header keamanan/CSP dipasang, dan ekspor CSV melindungi nilai yang dapat dianggap formula spreadsheet.

Halaman absensi tetap terbuka untuk perangkat kantin agar staf dapat memindai tanpa akun. Permintaan absensi dibatasi 12 kali per menit per IP dan duplikasi ditolak database. Untuk pemakaian lewat internet atau jaringan bersama, batasi halaman ini ke jaringan/VPN kantin atau tambahkan verifikasi identitas staf; ID QR Code saja tidak membuktikan siapa yang memindai.

Untuk produksi, layani hanya direktori `public`, gunakan HTTPS, atur `APP_DEBUG=false`, simpan `.env` dan `APP_KEY` dengan aman, set `SESSION_SECURE_COOKIE=true`, dan buat cadangan rutin file SQLite atau database yang dipakai.
