# Changelog

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

