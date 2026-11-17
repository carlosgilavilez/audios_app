# Sistema de audios de Iglesia Palma

Actualizado: **16/11/2026, 20:45, America/La_Paz**. Contexto público. Los accesos, infraestructura, respaldos detallados y evidencias operativas se conservan en el workspace privado, fuera de Git.

## Estado vigente: v0.2.6 publicada, desplegada y verificada

Commit de aplicación: **8a7ffa52c64c04b032805c1f0d9cfd5c1c19b3f6**, base **db326d8a05267d835cf72f0c0d69755735e1d836**. GitHub recibió48 cambios revisados: reproductor, Buscar/título, versión, assets, helpers, pruebas, estado y fuentes de las tres skills, incluyendo el trabajo acumulado antes local. El cierre de este documento se publica en un commit posterior exclusivamente documental; su SHA remoto se verifica y registra en el informe privado, separado del commit de aplicación desplegado.

Despliegue por Browser/iab y terminal hPanel: preflight y apply correctos, DEPLOY_EXIT=0, entorno sin cambios y catálogo sin alteraciones. No se ejecutaron migraciones, importadores, Composer ni npm en el servidor, ni se editó WordPress. Los directorios y el respaldo concreto se consultan en el contexto privado. No quedan acciones pendientes de estas solicitudes.

## Resultado del reproductor

El usuario aprobó el aspecto y pidió evitar la detención al adelantar. Antes del cambio, el compacto recuperó reproducción alrededor de1.2s tras buscar fuera del búfer; el ampliado quedó fijo al menos4.9s mientras recibía puntos intermedios abandonados. Las tres barras ahora previsualizan con input y solicitan una sola posición al soltar/change/blur, con respaldo pointerup/touchend/blur de ventana y cancelación de gestos. No se sobrescribe el value del control activo.

resources/js/audio-playback.js precarga solo la pista elegida, muestra un aviso durante la carga y permite un único reintento tras12s sin avance. Restaura la posición al recibir metadatos. Si tras el reintento no se recupera, se detiene y ofrece Reproducir, sin un bucle de solicitudes. Pausa y cambio de pista cancelan el temporizador. Se respetan la pausa del sistema y los rechazos de permisos de reproducción.

La precarga usa auto después de elegir una pista; conserva metadata si el navegador comunica ahorro de datos o red2G. Puede consumir más transferencia de esa pista incluso en pausa. Buscar fuera del búfer depende de la conexión y un reintento puede repetir algunos bytes. No se descargan otras pistas ni se usa un blob completo, HLS o recodificación. Estos efectos se comunicaron antes de implementar; los tiempos observados no son una garantía para todas las redes.

La ruta /play-audio conserva HTTP206/Content-Range comprobados. /storage no está disponible en el hosting actual: no cambiar a esa ruta ni crear enlaces públicos por rutina. El catálogo, metadatos y aspecto normal se conservan.

## Buscar y preferencias vigentes

El botón de apertura dice **Buscar**. Tanto el panel móvil como la barra lateral usan **Encuentra el mensaje que buscas** como título principal de22px; se retiró el encabezado separado Filtros. El estado compacto al desplazarse mantiene aria-label Buscar y área44px.

Mantener el degradado gris azulado, barras finas, Serie después de Buscar y Limpiar con contraste alto. En móvil, cabecera transparente con texto legible, Buscar fijo/compacto y tarjetas con solo título, autor y cita si existe. El reproductor conserva sus metadatos completos, progreso6px, tirador y área táctil44px. Carga50 audios por defecto y respeta otra selección explícita.

El buscador captura criterios inequívocos sin sustituir selecciones explícitas; conserva selección/chips al navegar. Serie está disponible; Desde/Hasta se retiraron y Año permanece. Las variantes históricas de autor no se fusionan automáticamente.

## Validación observada

- 12 pruebas Node de carga, pausa, recuperación limitada, cambio de pista, permisos y posiciones válidas.
- 20 pruebas PHP/119 aserciones de filtros/metadatos con SQLite en memoria, más4 pruebas/4 aserciones de límites de configuración privada. No se tocó la base real.
- Build y sintaxis PHP/Python/shell correctos. Assets finales: app-KXBpIFKR.css, player-BcoFn6iV.js y public-library-DvaOcQh8.css; manifest actualizado y fingerprint anteriores conservados.
- En la vista local aislada, compacto pausado65.1%=58.59s; ampliado30% avanzó27.007193→27.715228s; escritorio10% avanzó9→9.411926s. Se corrigió el receptor del temporizador y se comprobó el navegador antes de publicar.
- MP3 de producción de37:38: antes del salto solo había búfer al principio. Compacto84.9% recuperó readyState4 y avanzó1917.469541→1917.990092s en1266ms desde la acción. Ampliado40% avanzó903.401433→904.089786s en1488ms, con mensaje de carga retirado y metadatos completos.
- En WordPress real, salto55%:1776.967706→1777.585254s en1375ms, readyState4, reproduciendo; terminó pausado. La comprobación acredita avance real, no solo que cambió el deslizador.
- Buscar, título22px y ausencia del encabezado Filtros comprobados en móvil/escritorio.50 tarjetas;360/390/1280 sin overflow horizontal. Son pruebas nativas del navegador integrado; no una prueba física en teléfono.
- Servidor temporal detenido y WAV sintético propio retirado. Originales intactos; medios de producción pausados. Tres skills validadas y sincronizadas:10 archivos con hashes idénticos.

## Proyecto, inventario y continuidad

Laravel incrustado en WordPress/Divi: [biblioteca directa](https://audios.iglesiapalma.org/audios?view=table&embed=1), [página pública](https://iglesiapalma.org/audios-predicaciones/) y [repositorio](https://github.com/carlosgilavilez/audios_app).

Usar Codex Browser/iab mediante su runtime oficial de Node REPL, por instrucción del usuario. No sustituirlo por Chrome del usuario, Windows Computer Use ni cua_repl. Las pruebas locales utilizan SQLite aislado; nunca RefreshDatabase contra la base del usuario o producción.

La importación y las tres fechas confirmadas ya se completaron. Los322 archivos nuevos están publicados y no queda ningún original del archivo local proporcionado por incorporar. No repetir importación ni resolución de fechas. Los datos administrativos e informes detallados se consultan en el contexto privado. Revisar el catálogo histórico requiere una tarea separada; no alterar estados, fechas o autores al actualizar documentación.

## GitHub, skills y privacidad

Instrucción permanente del usuario: **enviar siempre a GitHub todos los cambios del proyecto al cerrar cada tarea**, incluidos código, pruebas, helpers, documentación, versión, estado y fuentes de skills. Revisar el diff completo y comprobar el SHA remoto; un commit local o sincronizar skills globales no completa este paso. Distinguir el commit de aplicación del cierre documental posterior.

La revisión automática rechazó publicar la primera copia del estado íntegro por contener infraestructura/cuenta privadas. Se conservó fuera de Git y se publicó esta documentación depurada. Las credenciales, rutas reales de operación y evidencias privadas permanecen excluidas por .gitignore y por la revisión del conjunto.

Leer las tres skills en skills/ y sus referencias. El contexto operativo íntegro se localiza junto al repositorio en revision-audios/PROJECT_STATE_PRIVATE.md, solo en el workspace privado autorizado. No copiar sus valores al estado público. Si no está disponible en otro clon, recuperar ese contexto privado por una vía autorizada antes de desplegar.

Los helpers requieren PALMA_LARAVEL_ROOT y directorios privados según su modo; scripts/PalmaEnvironment.php valida sus límites. Publicarlos no autoriza ejecutarlos. Las copias operativas anteriores pueden tener otra configuración y se identifican en el contexto privado. No ejecutar deploy.sh, push.js o workflows históricos a ciegas. Usar [skip ci] y el helper revisado con base/destino explícitos; no ejecutar migraciones/importación como parte de una entrega de interfaz.
