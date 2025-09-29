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

---

## Integración con WordPress

Para mostrar la biblioteca de audios dentro de un sitio de WordPress (ej. `iglesiapalma.org`), el método recomendado es usar un `<iframe>`.

### Pasos de Implementación

1.  **URL Pública:** Asegúrese de que esta aplicación de audios esté desplegada y accesible en una URL pública (ej. `https://audios.iglesiapalma.org`).
2.  **Página de WordPress:** En el panel de WordPress, edite o cree la página donde se mostrará la biblioteca.
3.  **Bloque HTML:** Inserte un bloque de "HTML Personalizado".
4.  **Código iFrame:** Pegue el siguiente código, reemplazando la URL de ejemplo por la real.

    ```html
    <iframe
      id="audio-library-iframe"
      src="https://audios.iglesiapalma.org"
      style="border:0; width:100%; height:100vh;"
      title="Biblioteca de Audios">
    </iframe>
    ```

### Checklist Técnico de Revisión

El programador debe revisar los siguientes puntos en el servidor donde se aloja la aplicación de audios para asegurar una correcta integración:

1.  **Cabeceras de Seguridad (HTTP Headers):**
    *   La cabecera `X-Frame-Options` debe ser eliminada o modificada. No puede ser `DENY` o `SAMEORIGIN`.
    *   En su lugar, se debe usar la cabecera `Content-Security-Policy` con la directiva `frame-ancestors`.
    *   **Importante:** Esta directiva debe especificar *únicamente* los dominios del sitio de WordPress para asegurar que la aplicación no pueda ser incrustada en otros sitios no autorizados.
    *   **Ejemplo:** `Content-Security-Policy: frame-ancestors https://iglesiapalma.org https://www.iglesiapalma.org;`

2.  **HTTPS Obligatorio:**
    *   Toda la aplicación de audios debe servirse bajo HTTPS para evitar problemas de contenido mixto en el navegador.

3.  **Modo Embed:**
    *   La aplicación debe ser capaz de detectar un modo "embed" (ej. a través de un parámetro en la URL como `?embed=1`) para ocultar elementos de navegación propios (headers, footers, etc.) que no son necesarios dentro del iframe.

4.  **Diseño Responsivo y Scroll:**
    *   Verificar que el contenido dentro del iFrame se adapta correctamente y no produce dobles barras de scroll (una del iFrame y otra de la página principal).
    *   Asegurarse de que el reproductor de audio no quede oculto por otros elementos de la página de WordPress, como footers o barras de cookies.

5.  **CORS y Assets:**
    *   Comprobar que no haya errores de Cross-Origin Resource Sharing (CORS) en la consola del navegador.
    *   Todos los assets (fuentes, imágenes, etc.) deben cargarse correctamente desde el dominio de WordPress sin ser bloqueados.
