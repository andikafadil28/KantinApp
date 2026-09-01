# SUMMARY — Ringkasan Cepat

> **Single source of truth = `AGENTS.md`** (TODO detail, notes teknis, behavior rules, changelog).
> File ini sengaja tipis buat hemat token tiap sesi — jangan nambah-nambah detail di sini, taruh di AGENTS.md.

# CURRENT

Fokus aktif: **Deployment Ubuntu + stabilisasi POS kantin**. Konfigurasi Docker sedang disiapkan dan belum di-commit. Redirect logout sudah diarahkan ke `/kantinsakina`, sedangkan koneksi database sudah mendukung environment variable dengan fallback Laragon. Detail lengkap: AGENTS.md.

Deployment Ubuntu sedang direncanakan dan belum dieksekusi. Server disiapkan sebagai host multi-application untuk KantinApp (PHP native) dan aplikasi Carwash (Laravel). Setup dilakukan bertahap melalui SSH, dengan verifikasi pada setiap step sebelum lanjut.

# Deployment Ubuntu

## Target

| Area | Konfigurasi |
|------|-------------|
| Repository | `https://github.com/andikafadil28/KantinApp.git` branch `main` |
| Folder checkout Ubuntu | `/srv/kantinsakina` |
| URL pengujian | `http://192.168.0.214:85/kantinsakina` |
| URL produksi | `http://192.168.0.215:85/kantinsakina` |
| Port aplikasi | Host Ubuntu `85` → Apache container `80` |
| Database aktif | MySQL/MariaDB dalam container Docker di Ubuntu |
| Migrasi database | Export/import SQL dari MySQL Windows |
| Foto menu | Transfer `assets/img` ke storage persisten Ubuntu |
| Remote management | SSH melalui Tailscale sebagai jalur utama/cadangan |
| Backup | NAS SMB pukul `12:00` dan `23:00` WIB |
| Retensi backup | Semua snapshot selama 30 hari |
| NAS | `\\192.168.0.76\master it\kantin\backupDatabase` |
| Aplikasi tambahan | Carwash berbasis Laravel, belum dibuat, dalam stack Docker terpisah |

Database tidak dijalankan dari NAS dan port `3306` tidak dibuka ke LAN. NAS hanya menjadi tujuan backup. Container aplikasi mengakses database melalui network internal Docker.

Ubuntu menjadi host Docker untuk beberapa aplikasi, tetapi setiap aplikasi memakai Compose project, network, container, database/user, environment, volume, dan lifecycle deployment yang terpisah. KantinApp tidak boleh digabung ke container Laravel. Carwash belum memiliki repository, memakai database baru, dan URL/port-nya belum ditentukan. Stack Carwash dibuat nanti setelah versi PHP/Laravel, scheduler, queue, Redis, dan kebutuhan storage sudah nyata.

## Checklist

- [x] Buat runbook command SSH di `DEPLOYMENT-UBUNTU.md`
- [x] Ubah `Database/connect.php` agar memakai environment variable
- [x] Ubah redirect logout menjadi `/kantinsakina/login`
- [x] Tambahkan Dockerfile dan Docker Compose
- [x] Tambahkan konfigurasi Apache, PHP, dan health check
- [x] Tambahkan `.env.example` dan ignore seluruh secret production
- [x] Review dan push konfigurasi deployment ke GitHub (`c0ceac4`)
- [x] Jalankan preflight Ubuntu melalui SSH
- [x] Install Tailscale langsung pada host Ubuntu
- [x] Verifikasi SSH melalui Tailscale sebelum mengubah jaringan
- [x] Install Docker Engine dan Docker Compose Plugin
- [x] Verifikasi kapasitas CPU, RAM, dan disk untuk dua aplikasi
- [x] Clone GitHub ke `/srv/kantinsakina`
- [ ] Jalankan stack pengujian pada `192.168.0.214:85`
- [ ] Pastikan aplikasi tersedia di `/kantinsakina`
- [x] Cek versi MySQL/MariaDB pada server Windows
- [ ] Export/import database rehearsal ke Docker Ubuntu
- [ ] Transfer dan verifikasi seluruh `assets/img`
- [ ] Mount SMB NAS `//192.168.0.76/master it`
- [ ] Pasang backup otomatis pukul `12:00` dan `23:00` WIB
- [ ] Terapkan retensi backup 30 hari
- [ ] Jalankan backup dan restore test
- [ ] Smoke test login, home, menu, order, bayar, laporan, dan rekap
- [ ] Aktifkan auto-start Docker, container, Tailscale, mount NAS, dan backup timer
- [ ] Aktifkan `Restore on AC Power Loss` pada BIOS/UEFI
- [ ] Uji reboot dan pastikan seluruh service kembali aktif
- [ ] Hentikan transaksi Windows saat final cutover tengah malam
- [ ] Export/import database final dan sinkronkan foto terakhir
- [ ] Pindahkan IP Ubuntu dari `.214` ke `.215` melalui SSH Tailscale
- [ ] Verifikasi URL produksi `http://192.168.0.215:85/kantinsakina`
- [ ] Buat backup NAS pertama setelah cutover
- [ ] Simpan server Windows sebagai rollback sementara
- [ ] Inventarisasi repository dan requirement aplikasi Laravel Carwash
- [ ] Tentukan URL/port aplikasi Carwash tanpa mengubah port `85` KantinApp
- [ ] Buat Compose project Carwash yang terisolasi dari KantinApp
- [ ] Siapkan Laravel `APP_KEY`, environment, storage, database, scheduler, dan queue jika dipakai
- [ ] Tambahkan data persisten Carwash ke backup NAS dan restore test

## Aturan Eksekusi

- Semua pekerjaan server dilakukan lewat SSH, satu step per checkpoint.
- User menjalankan command; output diverifikasi sebelum lanjut ke step berikutnya.
- Password SSH, database, NAS, Restic, dan token Tailscale tidak boleh masuk Git atau chat.
- Perubahan IP hanya dilakukan setelah SSH melalui Tailscale terbukti bekerja.
- Jenis dan versi image database harus kompatibel dengan hasil `SELECT VERSION()` dari server Windows.
- Source diperbarui dengan `git pull --ff-only`, lalu image aplikasi di-build ulang secara terkontrol.
- Database dan foto menu wajib ikut restore test; file SQL saja belum mencakup `assets/img`.
- KantinApp dan Carwash harus memakai Compose project terpisah agar update, restart, dan dependency tidak saling mengganggu.
- Port host `85` tetap khusus untuk KantinApp; endpoint Carwash ditentukan terpisah.

# Status Fitur

| Area | Status |
|------|--------|
| Dashboard home (hero + chart terlaris + stat cards) | ✅ SELESAI |
| Filter kantin di order | ✅ SELESAI (`6f8355d`) |
| Rekap Keuangan (detail + per menu) | ✅ SELESAI v1.4.4 |
| Export Excel (PhpSpreadsheet, 8 file) | ✅ SELESAI |
| Auth session + level (1=admin / 3=kasir) | ✅ AKTIF |
| Hardening SQLi/XSS + cleanup backup | ⏳ PRIORITAS |
| Commit `validate_logout.php` | ⏳ PENDING |

# Teknologi Singkat

- **PHP native procedural** (Laragon) — tanpa framework/autoload; MySQL DB `sakinakantin` (user `root`, no pass) via `Database/connect.php`
- Routing mini `.htaccess`: `/halaman` → `index.php?x=halaman`, halaman map di switch `index.php`
- Auth session manual, `password_verify()`/`password_hash`; role **level 1 = admin**, **level 3 = kasir/toko** (auto-filter kios sendiri)
- Template SB Admin 2 + Bootstrap 5.3 CDN; composer cuma `phpoffice/phpspreadsheet ^4.5` buat export Excel
- Gak ada testing framework / migration — verifikasi manual + `php -l`

# Changelog Terbaru

- **25 Apr 2026** `6f8355d` — nambah filter kantin
- **09 Mar 2026** — ubah chart (2x) + ubah template warna
- **13 Feb 2026** `3fbf3b9` — Update v1.4.4 laporan keuangan
- Riwayat lengkap: `git log` (20 commit) / AGENTS.md

# Catatan Cepat

- Model harga keuangan (JANGAN diubah tanpa konfirmasi): `harga` = untung toko, `pajak` = untung RS, PPN 11% → harga pembeli. Rumus di `Database/Query/Rekap_keuangan_*`
- Ada file backup/dead code (`* copy.php`, `*.html` SB Admin 2, `sign-in/`) + bug kecil (double `head.php` di main, `proses_input_user.php` rusak) — detail di AGENTS.md
