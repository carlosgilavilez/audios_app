# Changelog

## v0.1.1 — UI tweaks + nullsafe fixes

- Reproductor: invertidas las flechas de Anterior/Siguiente para que apunten hacia afuera del botón central.
- Tema: suavizado del modo claro y del modo oscuro (menos contraste extremo).
- Layout: ajustado el header (toggle de tema + botón Salir) para evitar overflow horizontal.
- Tablas: añadida barra de desplazamiento horizontal (`overflow-x-auto`) en listados de audios (editor), autores (admin) y series (admin) para mejor responsividad.
- Vistas audios: uso de operador nullsafe (`?->`) en admin/público/editor para evitar errores de “Attempt to read property ... on null”.
- Body: `overflow-x-hidden` para prevenir la barra horizontal por desbordes mínimos.

Commits relevantes: 451e68f, be1e813

