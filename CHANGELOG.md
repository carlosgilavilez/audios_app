# Changelog

## v0.2.4 - Lista móvil mínima y progreso más visible (2026-11-16)

- Las tarjetas móviles muestran solo título, autor y cita bíblica cuando existe, con menos espacio vertical. Fecha, serie y duración dejan de aparecer en la lista móvil.
- Los metadatos y controles del reproductor se conservan completos. La línea de progreso del reproductor móvil encogido pasa de 4 a 6 px.
- Se mantienen el filtro fijo y compacto, el contraste del encabezado y los 50 audios por defecto.

## v0.2.3 - Filtro fijo y tarjetas móviles compactas (2026-11-16)

- Texto claro del encabezado móvil sobre superficies redondeadas de contraste alto; se mantiene el fondo general transparente.
- Filtros permanece visible al desplazarse y se reduce a un botón de icono de 44 px, conservando su nombre accesible y la apertura del panel.
- Autor y fecha comparten fila debajo del título; cita y duración comparten la fila inferior para reducir la altura de cada tarjeta.
- La biblioteca y el iframe cargan 50 audios por defecto. Las elecciones explícitas de tamaño de página se respetan.

## v0.2.2 - Cabecera y tarjetas móviles más ligeras (2026-11-16)

- La franja móvil de Mostrar/Filtros se integra en el fondo sin el bloque rectangular de color; los controles tienen más espacio lateral y el botón Filtros conserva 44 px de altura táctil.
- Se retira la línea de categoría de las tarjetas móviles para reducir su altura. El filtro por categoría, los metadatos del reproductor y la tabla de escritorio conservan esa información.
- Se mantiene el degradado gris azulado y el estilo de v0.2.1.

## v0.2.1 - Degradado oscuro recuperado (2026-11-16)

- Se recupera el degradado gris azulado de la interfaz anterior por preferencia del cliente, con superficies más luminosas y texto legible.
- Filtros, tarjetas y reproductor acompañan el degradado, conservando las barras finas, el orden de Serie, Limpiar con alto contraste y los ajustes responsive de v0.2.0.
- El reproductor móvil ampliado mantiene el ancho completo, sin reservar una franja blanca al ocultar el desplazamiento.

## v0.2.0 - Biblioteca y reproductor renovados (2026-11-16)

Código de versión: `v0.2.0` (SemVer, mejora de interfaz sin cambios de base de datos).

- Barras de desplazamiento finas y discretas en biblioteca, filtros y página WordPress, sin ocultar el desplazamiento por teclado o táctil.
- En la página de audios, WhatsApp pasa después del pie también en escritorio para mantener libres los controles del reproductor.
- Serie inmediatamente después de Buscar en escritorio y móvil; filtros y chips conservan su comportamiento.
- Limpiar y Limpiar todo con contraste alto, áreas de pulsación amplias y foco visible.
- Superficies claras/oscuras más uniformes, tipografía y filas más legibles, con inspiración visual en Spotify y sin el fondo de logotipo sobre los textos.
- Reproductor de escritorio organizado en metadatos, controles, progreso y volumen; versión móvil compacta y ampliada.
- Progreso y volumen más finos; progreso del reproductor compacto y ampliado actualizado y controles de reproducir/pausar con etiquetas accesibles sincronizadas.
- Corregida la doble activación al pulsar los iconos SVG del botón de reproducción.
- Compatibilidad con movimiento reducido, contraste forzado y diferentes anchos de pantalla.
- Se conservan audios, metadatos, estados de publicación y assets anteriores para páginas cacheadas.

## v0.1.1 — UI tweaks + nullsafe fixes

- Reproductor: invertidas las flechas de Anterior/Siguiente para que apunten hacia afuera del botón central.
- Tema: suavizado del modo claro y del modo oscuro (menos contraste extremo).
- Layout: ajustado el header (toggle de tema + botón Salir) para evitar overflow horizontal.
- Tablas: añadida barra de desplazamiento horizontal (`overflow-x-auto`) en listados de audios (editor), autores (admin) y series (admin) para mejor responsividad.
- Vistas audios: uso de operador nullsafe (`?->`) en admin/público/editor para evitar errores de “Attempt to read property ... on null”.
- Body: `overflow-x-hidden` para prevenir la barra horizontal por desbordes mínimos.

Commits relevantes: 451e68f, be1e813

