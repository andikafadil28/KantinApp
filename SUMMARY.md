# SUMMARY — Ringkasan Cepat

> **Single source of truth = `AGENTS.md`** (TODO detail, notes teknis, behavior rules, changelog).
> File ini sengaja tipis buat hemat token tiap sesi — jangan nambah-nambah detail di sini, taruh di AGENTS.md.

# CURRENT

Fokus aktif: **Final cutover Ubuntu tengah malam**. Versi `7146dcf` sudah ter-deploy pada Ubuntu uji `192.168.0.214`. Dashboard, transaksi sampai pembayaran, laporan/export, backup NAS, restore test, dan reboot test sudah lolos. Tidak ada blocker aplikasi aktif. Detail dan urutan cutover lengkap: AGENTS.md.

Server disiapkan sebagai host multi-application untuk KantinApp (PHP native) dan aplikasi Carwash (Laravel). Setup dilakukan bertahap melalui SSH, dengan verifikasi pada setiap step sebelum lanjut.

## Resume Cutover

- URL uji aktif: `http://192.168.0.214:85/kantinsakina`
- Tailscale aktif: hostname `kantin-server`, IP `100.92.230.124`, key expiry disabled
- Container `app` dan `db` healthy; port host `85` ter-publish
- MySQL rehearsal setelah smoke test: 16 tabel, 9 kios, 305 menu, 5.418 order, 10.188 item, 5.417 pembayaran
- Login berhasil dan transaksi tidak hilang; `ONLY_FULL_GROUP_BY` sudah dinonaktifkan untuk kompatibilitas query legacy
- Root cause Home adalah permission `menu_telaris.php` mode `600`; sudah diperbaiki menjadi `644` dan diverifikasi setelah rebuild/reboot
- Foto tervalidasi `missing=0`; NAS automount, Restic, direct SQL, retensi 30 hari, timer 12:00/23:00, dan restore test sudah lolos
- Next: verifikasi backup 23:00, write freeze Windows, dump/import final, sinkronisasi foto, smoke `.214`, lalu pindah ke `.215`

## Handoff Komputer Lain

1. Pull branch `main`, lalu baca `SUMMARY.md` dan `AGENTS.md` bagian `Cutover Final Tengah Malam - Urutan Wajib`.
2. Akses Ubuntu melalui `ssh kantin@100.92.230.124`; jalankan satu checkpoint dan verifikasi output sebelum lanjut.
3. Jangan masukkan password, token, `.env`, atau isi credential file ke Git/chat.

## TODO Cutover Tengah Malam

- [ ] Verifikasi backup timer pukul 23:00, snapshot Restic, dan direct SQL terbaru di NAS.
- [ ] Pastikan SSH Tailscale aktif; pertahankan code freeze aplikasi `7146dcf`.
- [ ] Terapkan write freeze Windows dan catat count serta transaksi terakhir sebagai baseline.
- [ ] Stop container app Ubuntu, lalu buat dan validasi dump final Windows.
- [ ] Transfer dump, cocokkan SHA-256, dan sinkronkan delta foto terakhir.
- [ ] Backup rehearsal Ubuntu terakhir, drop/recreate database tujuan, lalu import final.
- [ ] Cocokkan 16 tabel, count utama, transaksi terakhir, item, dan pembayaran.
- [ ] Start app dan smoke test lengkap di `.214`.
- [ ] Shutdown/pindahkan IP Windows, lalu ubah Ubuntu `.214` menjadi `.215` lewat Tailscale.
- [ ] Verifikasi URL kasir, transaksi smoke final, log, dan database.
- [ ] Jalankan backup manual agar Restic dan `sakinakantin-latest.sql` berisi data final.
- [ ] Simpan Windows sebagai rollback tanpa menerima transaksi dan tanpa memakai `.215`.

# Deployment Ubuntu

## Target

| Area | Konfigurasi |
|------|-------------|
| Repository | `https://github.com/andikafadil28/KantinApp.git` branch `main` |
| Folder checkout Ubuntu | `/srv/kantinsakina` |
| URL pengujian | `http://192.168.0.214:85/kantinsakina` |
| URL produksi | `http://192.168.0.215:85/kantinsakina` |
| Port aplikasi | Host Ubuntu `85` → Apache container `80` |
| Database aktif | MySQL `8.0.46` dalam container Docker di Ubuntu |
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
- [x] Jalankan stack pengujian pada `192.168.0.214:85`
- [x] Pastikan aplikasi tersedia di `/kantinsakina`
- [x] Cek versi MySQL/MariaDB pada server Windows
- [x] Export/import database rehearsal ke Docker Ubuntu
- [x] Transfer dan verifikasi seluruh `assets/img` (`tb_menu.foto` missing `0`)
- [x] Mount SMB NAS `//192.168.0.76/master it` dengan systemd automount
- [x] Pasang backup otomatis pukul `12:00` dan `23:00` WIB
- [x] Terapkan retensi backup 30 hari untuk Restic dan direct SQL
- [x] Jalankan backup, full integrity check, dan restore/import test
- [x] Smoke test login, home, menu, order, bayar, laporan, rekap, dan export
- [x] Aktifkan auto-start Docker, container, Tailscale, mount NAS, dan backup timer
- [ ] Aktifkan `Restore on AC Power Loss` pada BIOS/UEFI
- [x] Uji reboot dan pastikan seluruh service kembali aktif
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
| Commit `validate_logout.php` | ✅ SELESAI (`c0ceac4`) |

# Teknologi Singkat

- **PHP native procedural** — tanpa framework/autoload; koneksi MySQL via environment variable di Docker dengan fallback Laragon lokal
- Routing mini `.htaccess`: `/halaman` → `index.php?x=halaman`, halaman map di switch `index.php`
- Auth session manual, `password_verify()`/`password_hash`; role **level 1 = admin**, **level 3 = kasir/toko** (auto-filter kios sendiri)
- Template SB Admin 2 + Bootstrap 5.3 CDN; composer cuma `phpoffice/phpspreadsheet ^4.5` buat export Excel
- Gak ada testing framework / migration — verifikasi manual + `php -l`

# Changelog Terbaru

- **02 Sep 2026** `7146dcf` — fix warning alur pembayaran
- **02 Sep 2026** `83cbd77` — catat progres deployment Ubuntu
- **01 Sep 2026** `9891566` — samakan SQL mode aplikasi legacy
- **01 Sep 2026** `99a719b` — fix query dashboard MySQL strict
- **01 Sep 2026** `b10d40f` — fix publish port aplikasi Docker
- **01 Sep 2026** `6b81f6b` — fix build extension PHP Docker
- **01 Sep 2026** `c0ceac4` — siapkan deployment Docker Ubuntu
- **25 Apr 2026** `6f8355d` — nambah filter kantin
- **09 Mar 2026** — ubah chart (2x) + ubah template warna
- **13 Feb 2026** `3fbf3b9` — Update v1.4.4 laporan keuangan
- Riwayat lengkap: `git log` (20 commit) / AGENTS.md

# Catatan Cepat

- Model harga keuangan (JANGAN diubah tanpa konfirmasi): `harga` = untung toko, `pajak` = untung RS, PPN 11% → harga pembeli. Rumus di `Database/Query/Rekap_keuangan_*`
- Ada file backup/dead code (`* copy.php`, `*.html` SB Admin 2, `sign-in/`) + bug kecil (double `head.php` di main, `proses_input_user.php` rusak) — detail di AGENTS.md
