Despliegue a Hostinger (audios.iglesiapalma.org)

1) Requisitos
- PHP 8.2+ con extensiones: mbstring, openssl, pdo_mysql, bcmath, fileinfo, tokenizer, xml, ctype, json, curl.
- Composer 2.x. Node.js solo si compilas assets en el servidor.
- Subdominio: audios.iglesiapalma.org apuntando el docroot a `public/` del proyecto.

2) Primer despliegue (SSH)
```
cd ~
git clone https://github.com/carlosgilavilez/audios_app.git
cd audios_app
cp .env.production.example .env  # edita credenciales y APP_URL
bash scripts/deploy-first.sh
```
Si no hay Node en el servidor, compila en local y sube `public/build`.

3) Actualizaciones
```
cd ~/audios_app
bash scripts/deploy-update.sh
```

4) Límite de subida (Hostinger)
El script crea `public/.user.ini` con: upload_max_filesize=100M, post_max_size=100M, memory_limit=256M.

5) Backups
- Linux/macOS: `bash scripts/backup.sh`
- Windows (PowerShell): `./scripts/backup.ps1`
Genera en `storage/backups/`:
  - Copia de `.env` con timestamp
  - Dump MySQL (si `mysqldump` está disponible)
  - ZIP/TAR de `storage/app/public`

6) CORS para WordPress
Permite orígenes: `https://iglesiapalma.org, https://audios.iglesiapalma.org` (configurable por env: `CORS_ALLOWED_ORIGINS`).

7) Optimización
`php artisan config:cache && route:cache && view:cache` ya se ejecutan en los scripts.

8) Rollback simple
```
cd ~/audios_app
git log --oneline
git reset --hard <SHA_SEGURO>
composer install --no-dev --optimize-autoloader
php artisan config:cache route:cache view:cache
```

