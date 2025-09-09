Despliegue a GitHub y Producción

Requisitos previos
- Acceso SSH al servidor `audios.iglesiapalma.org` con un usuario con permisos sobre el proyecto.
- Clave SSH configurada en GitHub (para `git pull`) y en tu equipo local (para `ssh/scp`).
- Ruta del proyecto en producción (ejemplo): `/var/www/audios_app`.
- PHP 8.2, Composer y (opcional) Node.js en el servidor. Si no hay Node, copia `public/build` desde local.

1) Publicar cambios a GitHub
```
# En tu máquina local (PowerShell/Git Bash) en la carpeta del repo
git status
git add -A
git commit -m "Ajustes de layout, tabla y scroll audios"

# Configura el remoto si falta
git remote -v
git remote add origin git@github.com:USUARIO/REPO.git   # si aún no existe

git push -u origin main   # o la rama que uses
```

2) Actualizar código en producción vía Git
```
ssh usuario@audios.iglesiapalma.org
cd /var/www/audios_app
git fetch origin
git checkout main
git pull --ff-only origin main

# Dependencias PHP
composer install --no-dev --prefer-dist --optimize-autoloader

# (Opción A) Construir assets en el servidor
npm ci && npm run build

# (Opción B) Si no hay Node, copia el build desde local (ver paso 3)

# Migraciones y caches (cuidado en horario de bajo tráfico)
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear

# Enlace de almacenamiento público (si no existe)
php artisan storage:link

# Reiniciar PHP-FPM (según distro)
sudo systemctl reload php8.2-fpm || sudo systemctl reload php-fpm
```

3) Enviar contenidos (audios) al servidor
Ruta local de audios (por defecto Laravel): `storage/app/public/audios`

- Con `rsync` (recomendado; si usas Git Bash/WSL):
```
rsync -avz --progress storage/app/public/audios/ \
  usuario@audios.iglesiapalma.org:/var/www/audios_app/storage/app/public/audios/
```

- Con `scp` (PowerShell con OpenSSH):
```
scp -r storage/app/public/audios \
  usuario@audios.iglesiapalma.org:/var/www/audios_app/storage/app/public/
```

Después de copiar, en el servidor ajusta permisos:
```
ssh usuario@audios.iglesiapalma.org "sudo chown -R www-data:www-data /var/www/audios_app/storage && sudo find /var/www/audios_app/storage -type d -exec chmod 775 {} \; && sudo find /var/www/audios_app/storage -type f -exec chmod 664 {} \;"
```

4) (Opcional) Copiar también `public/build` desde local si no construyes en el servidor
```
rsync -avz --delete public/build/ \
  usuario@audios.iglesiapalma.org:/var/www/audios_app/public/build/
```

5) Verificación rápida
- Abre `https://audios.iglesiapalma.org/admin/audios` y comprueba que:
  - No hay barra horizontal si no hay overflow.
  - La columna Nombre muestra grupos de 3 palabras por línea.
  - Reproduce un audio y verifica que se sirve desde `/storage/audios/...`.

6) Automatización (sugerido)
- GitHub Actions que, al hacer push en `main`:
  - Instala PHP deps + `npm ci && npm run build`.
  - Sube `public/build` vía `rsync` y ejecuta `ssh` remoto para `git pull`, `composer install`, `php artisan migrate --force` y caches.
  - (Opcional) Sincroniza `storage/app/public/audios` si lo gestionas desde GitHub (normalmente se hace manual o con un job programado por tamaño).

Notas
- Asegúrate de que `.env` en producción apunta a la BD correcta y que `APP_ENV=production`, `APP_DEBUG=false`.
- Si usas colas/supervisor, reinicia los workers tras el despliegue.
