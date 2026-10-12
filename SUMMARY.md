# SUMMARY — Ringkasan Cepat

> **Single source of truth = `AGENTS.md`** (TODO detail, notes teknis, behavior rules, changelog).
> File ini sengaja tipis buat hemat token tiap sesi — jangan nambah detail di sini, taruh di AGENTS.md.

# CURRENT

Fokus aktif: **Final cutover Ubuntu tengah malam**. Versi `7146dcf` ter-deploy di Ubuntu uji `192.168.0.214`. Dashboard, transaksi sampai pembayaran, laporan/export, backup NAS, restore test, reboot test lolos. Tidak ada blocker. Detail + urutan cutover: AGENTS.md.

`main` sudah lebih baru (`deafa71`, `0dbae55` = fitur menu bulk/import/delete, 12 Okt 2026), belum masuk server uji sampai `git pull` + rebuild image.

## Resume Cutover

- URL uji: `http://192.168.0.214:85/kantinsakina`
- Tailscale: host `kantin-server`, IP `100.92.230.124`, key expiry disabled
- Container `app` + `db` healthy; port `85` ter-publish
- MySQL rehearsal setelah smoke: 16 tabel, 9 kios, 305 menu, 5.418 order, 10.188 item, 5.417 pembayaran
- `ONLY_FULL_GROUP_BY` dimatikan (kompat query legacy)
- Root cause Home = permission `menu_telaris.php` `600`; fix `644`
- Foto `missing=0`; NAS automount, Restic, direct SQL, retensi 30 hari, timer 12:00/23:00, restore test lolos
- Next: verifikasi backup 23:00 → write freeze Windows → dump/import final → sinkron foto → smoke `.214` → pindah `.215`

## Handoff Komputer Lain

1. Pull `main`, baca `SUMMARY.md` + AGENTS.md bagian `Cutover Final Tengah Malam - Urutan Wajib`.
2. Akses `ssh kantin@100.92.230.124`; satu checkpoint + verifikasi output sebelum lanjut.
3. Jangan masukkan password/token/`.env`/credential ke Git/chat.

## TODO Cutover Tengah Malam

- [ ] Verifikasi backup 23:00, snapshot Restic, direct SQL terbaru di NAS.
- [ ] Pastikan SSH Tailscale aktif; pertahankan code freeze `7146dcf`.
- [ ] Write freeze Windows + catat count/transaksi terakhir (baseline).
- [ ] Stop container app Ubuntu, buat + validasi dump final Windows.
- [ ] Transfer dump, cocokkan SHA-256, sinkron delta foto.
- [ ] Backup rehearsal terakhir, drop/recreate DB tujuan, import final.
- [ ] Cocokkan 16 tabel, count utama, transaksi terakhir, item, pembayaran.
- [ ] Start app + smoke test lengkap di `.214`.
- [ ] Shutdown/pindahkan IP Windows, ubah Ubuntu `.214` → `.215` via Tailscale.
- [ ] Verifikasi URL kasir, transaksi smoke final, log, DB.
- [ ] Backup manual (Restic + `sakinakantin-latest.sql` isi data final).
- [ ] Simpan Windows sebagai rollback (tanpa transaksi, tanpa `.215`).

# Deployment Ubuntu

## Target

| Area | Konfigurasi |
|------|-------------|
| Repository | `https://github.com/andikafadil28/KantinApp.git` branch `main` |
| Folder checkout | `/srv/kantinsakina` |
| URL uji | `http://192.168.0.214:85/kantinsakina` |
| URL produksi | `http://192.168.0.215:85/kantinsakina` |
| Port | host Ubuntu `85` → Apache container `80` |
| Database | MySQL `8.0.46` dalam Docker Ubuntu |
| Migrasi DB | export/import SQL dari MySQL Windows |
| Foto menu | `assets/img` ke storage persisten Ubuntu |
| Remote | SSH via Tailscale (utama/cadangan) |
| Backup | NAS SMB `12:00` + `23:00` WIB |
| Retensi | snapshot 30 hari |
| NAS | `\\192.168.0.76\master it\kantin\backupDatabase` |
| Aplikasi lain | Carwash (Laravel), belum dibuat, Compose terpisah |

DB tidak dari NAS; port `3306` tak dibuka ke LAN. NAS hanya tujuan backup; app akses DB via network internal Docker. Ubuntu host multi-app: tiap app punya Compose project/network/container/DB/user/env/volume terpisah. KantinApp tak digabung ke container Laravel. Carwash dibuat nanti setelah kebutuhan (PHP/Laravel, scheduler, queue, Redis, storage) nyata.

## Checklist

- [x] Runbook SSH di `DEPLOYMENT-UBUNTU.md`
- [x] `Database/connect.php` pakai env var
- [x] Redirect logout ke `/kantinsakina/login`
- [x] Dockerfile + Docker Compose
- [x] Config Apache/PHP + health check
- [x] `.env.example` + ignore secret production
- [x] Push config deployment (`c0ceac4`)
- [x] Preflight Ubuntu via SSH
- [x] Install Tailscale di host
- [x] Verifikasi SSH Tailscale sebelum ubah jaringan
- [x] Install Docker Engine + Compose Plugin
- [x] Verifikasi CPU/RAM/disk untuk 2 app
- [x] Clone GitHub ke `/srv/kantinsakina`
- [x] Stack uji di `192.168.0.214:85`
- [x] App tersedia di `/kantinsakina`
- [x] Cek versi MySQL/MariaDB Windows
- [x] Export/import DB rehearsal ke Docker Ubuntu
- [x] Transfer + verifikasi `assets/img` (`tb_menu.foto` missing `0`)
- [x] Mount SMB NAS dengan systemd automount
- [x] Backup otomatis `12:00` + `23:00` WIB
- [x] Retensi 30 hari (Restic + direct SQL)
- [x] Backup, full integrity check, restore/import test
- [x] Smoke test login/home/menu/order/bayar/laporan/rekap/export
- [x] Auto-start Docker, container, Tailscale, mount NAS, backup timer
- [ ] Aktifkan `Restore on AC Power Loss` BIOS/UEFI
- [x] Uji reboot — semua service kembali aktif
- [ ] Hentikan transaksi Windows saat final cutover
- [ ] Export/import DB final + sinkron foto terakhir
- [ ] Pindahkan IP Ubuntu `.214` → `.215` via Tailscale
- [ ] Verifikasi URL produksi
- [ ] Backup NAS pertama setelah cutover
- [ ] Simpan Windows sebagai rollback sementara
- [ ] Inventarisasi repo + requirement Laravel Carwash
- [ ] Tentukan URL/port Carwash tanpa ganggu port `85`
- [ ] Compose project Carwash terisolasi
- [ ] Siapkan `APP_KEY`, env, storage, DB, scheduler, queue Carwash
- [ ] Data persisten Carwash masuk backup NAS + restore test

## Aturan Eksekusi

- Semua kerja server via SSH, satu step per checkpoint.
- User jalanin command; output diverifikasi sebelum lanjut.
- Password SSH/DB/NAS/Restic/token tak boleh ke Git/chat.
- Ubah IP hanya setelah SSH Tailscale terbukti jalan.
- Versi image DB harus kompatibel `SELECT VERSION()` Windows.
- Source update: `git pull --ff-only` lalu rebuild image terkontrol.
- DB + foto wajib ikut restore test (SQL saja belum cukup).
- KantinApp & Carwash Compose project terpisah.
- Port host `85` khusus KantinApp.

# Status Fitur

| Area | Status |
|------|--------|
| Dashboard home (hero + chart terlaris + stat cards) | ✅ SELESAI |
| Filter kantin di order | ✅ SELESAI (`6f8355d`) |
| Rekap Keuangan (detail + per menu) | ✅ SELESAI v1.4.4 |
| Export Excel (PhpSpreadsheet, 8 file) | ✅ SELESAI |
| Auth session + level (1=admin / 3=kasir) | ✅ AKTIF |
| Menu bulk add + kios global | ✅ SELESAI (`deafa71`) |
| Import menu CSV/Excel + template | ✅ SELESAI (`deafa71`) |
| Konfirmasi duplikat `/menuduplikat` | ✅ SELESAI (`0dbae55`) |
| Hapus menu massal | ✅ SELESAI (`0dbae55`) |
| Duplikat menu = `(nama, kios)` | ✅ AKTIF |
| Hardening SQLi/XSS + cleanup backup | ⏳ PRIORITAS |
| Commit `validate_logout.php` | ✅ SELESAI (`c0ceac4`) |

# Teknologi Singkat

- **PHP native procedural** — tanpa framework/autoload; MySQL via env var di Docker + fallback Laragon
- Routing mini `.htaccess`: `/halaman` → `index.php?x=halaman`, map di switch `index.php`
- Auth session manual, `password_verify()`/`password_hash`; level 1=admin, 3=kasir/toko (auto-filter kios)
- Template SB Admin 2 + Bootstrap 5.3 CDN; composer cuma `phpoffice/phpspreadsheet ^4.5`
- Tak ada testing framework/migration — verifikasi manual + `php -l`

# Changelog Terbaru

- **12 Okt 2026** `0dbae55` — konfirmasi duplikat menu + hapus menu massal
- **12 Okt 2026** `deafa71` — input menu bulk + import CSV/Excel
- **21 Sep 2026** `7eeb6db` — perbaiki penghapusan order dan sidebar
- **02 Sep 2026** `7146dcf` — fix warning alur pembayaran
- **02 Sep 2026** `c0c9103`, `b458c95` — handoff + kesiapan cutover
- **01 Sep 2026** `9891566`, `99a719b`, `b10d40f`, `6b81f6b`, `c0ceac4` — fix deployment Docker
- **25 Apr 2026** `6f8355d` — nambah filter kantin
- **09 Mar 2026** — ubah chart + template warna
- Riwayat lengkap: `git log` / AGENTS.md

# Catatan Cepat

- Model harga (JANGAN ubah tanpa konfirmasi): `harga` = untung toko, `pajak` = untung RS, PPN 11% → harga pembeli. Rumus di `Database/Query/Rekap_keuangan_*`
- Dead code (`* copy.php`, `*.html` SB Admin 2, `sign-in/`) + bug (`main.php` double `head.php`, `proses_input_user.php` rusak, `validate_menu_edit.php:54` dead query) — detail di AGENTS.md
- Duplikat menu = komposit `(nama, kios)`; nama sama beda kios boleh
