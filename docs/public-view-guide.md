# Public Audio Library Guide

## URL parameters
- `view=table|cards` selecciona entre la tabla responsive y las tarjetas (persiste en `localStorage`).
- `theme=neutral|verde|azul|vino` define la paleta aplicada a la UI y al reproductor sticky.
- `dark=1|0` fuerza modo oscuro o claro sin importar la preferencia del sistema.
- `wp_preview=1` activa el modo Vista WordPress; combinalo con `wp_width=360|414|768|780|1024|1366` para fijar el ancho simulado.
- Los filtros (`search`, `categoria_id`, `year`, `per_page`) se conservan cuando compartes la URL.

## Add a new color theme
1. Agrega el slug al arreglo `themes` exportado por `ThemeManager` en `resources/js/app.js`.
2. Declara las variables CSS claras en `resources/css/app.css` dentro de `:root[data-theme="tu-tema"]`.
3. Duplica el bloque dentro de `.dark[data-theme="tu-tema"]` para la version oscura manteniendo contraste AA.
4. Anade la opcion al picker en `resources/views/public/audios.blade.php`.
5. Ejecuta `npm run build` (o `npm run dev`) para regenerar los assets y validar el reproductor.

## WordPress preview mode controls
- Activalo con el boton "Vista WordPress" o agrega `?wp_preview=1` a la URL.
- La barra superior permite alternar anchos predefinidos y muestra el valor vigente.
- El modo oculta la cromatica externa y restringe el layout al ancho elegido para simular un post embebido.

## CSP y CORS para una futura integracion en iframe
- Configura `frame-ancestors` permitiendo el dominio de WordPress que embebara la vista.
- Sirve los audios con `Access-Control-Allow-Origin` apuntando al host remoto si se reproduce cross-origin.
- Manten `script-src` y `style-src` alineados con las rutas de Vite o el CDN empleado.
- Evita dependencias de cookies o autenticacion: expon unicamente rutas publicas dentro del iframe.
