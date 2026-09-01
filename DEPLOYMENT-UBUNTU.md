# Deployment Ubuntu

Dokumen ini adalah runbook deployment KantinApp melalui SSH. Jalankan satu checkpoint, verifikasi hasilnya, lalu lanjut ke checkpoint berikutnya.

## Target

- Checkout: `/srv/kantinsakina`
- Data foto: `/srv/kantinsakina-data/images`
- Import SQL: `/srv/kantinsakina-data/imports`
- URL uji: `http://192.168.0.214:85/kantinsakina`
- URL produksi: `http://192.168.0.215:85/kantinsakina`
- Database: MySQL 8.0 dalam Docker, tidak diekspos ke LAN
- Backup NAS: pukul 12:00 dan 23:00 WIB, retensi 30 hari

MySQL mempertahankan strict mode, tetapi `ONLY_FULL_GROUP_BY` dinonaktifkan untuk kompatibilitas query legacy aplikasi. Jangan mengubah SQL mode atau rumus laporan keuangan tanpa smoke test lengkap.

## Update Source

Clone pertama kali:

```bash
sudo install -d -o kantin -g kantin /srv/kantinsakina
git clone https://github.com/andikafadil28/KantinApp.git /srv/kantinsakina
```

Update berikutnya:

```bash
cd /srv/kantinsakina
git pull --ff-only origin main
docker compose build app
docker compose up -d
```

## Secret Environment

Secret hanya dibuat di Ubuntu dan tidak boleh di-commit.

```bash
cd /srv/kantinsakina
umask 077
cp .env.example .env
```

Ganti `DB_PASSWORD` dan `DB_ROOT_PASSWORD` dengan password hex acak yang berbeda. Pastikan file hanya bisa dibaca owner:

```bash
chmod 600 /srv/kantinsakina/.env
```

Compose akan menolak konfigurasi jika salah satu password masih kosong. Gunakan karakter hex agar aman dari interpolasi shell dan jangan kirim nilainya ke chat atau Git.

## Storage Foto

Volume foto harus dibuat dan diisi sebelum container pertama dijalankan agar bind mount tidak menutupi foto bawaan repository.

```bash
sudo install -d -o 33 -g 33 /srv/kantinsakina-data/images
sudo cp -a /srv/kantinsakina/assets/img/. /srv/kantinsakina-data/images/
sudo chown -R 33:33 /srv/kantinsakina-data/images
sudo install -d -o kantin -g kantin /srv/kantinsakina-data/imports
```

## Menjalankan Stack

Validasi Compose sebelum membuat container:

```bash
cd /srv/kantinsakina
docker compose config --quiet
```

Build dan jalankan:

```bash
docker compose build app
docker compose up -d
docker compose ps
```

Log diagnosis:

```bash
docker compose logs --tail=100 app db
```

## Import Database

File SQL hasil export Windows ditempatkan di `/srv/kantinsakina-data/imports`. Import dilakukan dengan root database container tanpa membuka port MySQL ke LAN. Aplikasi dihentikan dan schema dibuat ulang agar data rehearsal tidak bercampur dengan dump final.

```bash
cd /srv/kantinsakina
docker compose stop app
docker compose exec -T db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS \`$MYSQL_DATABASE\`; CREATE DATABASE \`$MYSQL_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"'
docker compose exec -T db sh -c 'exec mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < /srv/kantinsakina-data/imports/sakinakantin.sql
docker compose start app
```

Verifikasi tabel:

```bash
docker compose exec -T db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e "SHOW TABLES;"'
```

Perintah import harus selesai dengan exit code `0`. Jangan lanjut cutover jika ada error collation, duplicate key, atau tabel yang hilang.

## Backup NAS

Backup database dan foto ke NAS belum dipasang pada tahap menjalankan stack. Script dump konsisten, mount SMB, timer pukul 12:00/23:00 WIB, retensi 30 hari, dan restore test dipasang pada checkpoint backup sebelum cutover produksi.

## Auto-Start

Docker dijalankan oleh systemd dan container memakai `restart: unless-stopped`.

```bash
systemctl is-enabled docker
systemctl is-active docker
docker compose ps
```

## Cutover

1. Hentikan transaksi pada Windows.
2. Export SQL final dan salin perubahan terakhir `assets/img`.
3. Import dump final ke Ubuntu.
4. Jalankan smoke test pada IP `.214`.
5. Pastikan SSH Tailscale aktif.
6. Lepaskan IP `.215` dari Windows.
7. Ubah Ubuntu dari `.214` ke `.215` melalui sesi SSH Tailscale.
8. Verifikasi `http://192.168.0.215:85/kantinsakina`.
9. Buat backup NAS pertama dan pertahankan Windows sebagai rollback sementara.

## Laravel Carwash

Carwash harus memakai Compose project, network, database, volume, environment, dan port yang terpisah. Jangan memasukkan aplikasi Laravel ke container KantinApp. Detail stack dibuat setelah repository dan requirement Laravel tersedia.
