Deploy en producción (pull + build)

Requisitos servidor
- Git y PHP 8.2+
- Composer instalado en PATH
- Opcional: Node.js 18+ si quieres compilar assets en el servidor

Pasos rápidos (SSH)
1) Ir al directorio del proyecto (donde está .git)
   cd /ruta/a/tu/proyecto

2) Ejecutar el script de deploy (usa main por defecto)
   chmod +x scripts/deploy.sh
   ./scripts/deploy.sh

Qué hace el script
- git pull --rebase origin main (o el branch que pases como 1er arg)
- composer install --no-dev --optimize-autoloader
- php artisan migrate --force
- php artisan optimize:clear && cache de config/routes/views
- npm ci && npm run build si hay Node; si no, deja los assets como están
- Ajusta permisos en storage y bootstrap/cache

Variables .env a revisar en producción
- BROADCAST_CONNECTION=pusher y claves PUSHER_* de producción
- MAIL_* con SMTP real de producción
- APP_ENV=production, APP_DEBUG=false (recomendado)

Problemas comunes
- Conflictos en public/build: vuelve a compilar en servidor (npm run build) o sube public/build desde local.
- Cambios locales sin commit: el script hace git stash -u antes del pull.

