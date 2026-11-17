# Sistema de audios de Iglesia Palma

Actualizado: **16/11/2026, 20:30, America/La_Paz**. Contexto público del proyecto. Los accesos, rutas de infraestructura, respaldos y evidencias operativas se conservan en el contexto privado del workspace, fuera de Git.

## Estado actual

La versión **v0.2.6** está preparada y pendiente de terminar su publicación y despliegue. Base de aplicación verificada: **db326d8a05267d835cf72f0c0d69755735e1d836 (v0.2.5)**. Actualizar esta sección con el resultado real al cerrar; no confundir preparación local, publicación en GitHub y despliegue.

El usuario aprobó la apariencia y pidió evitar que el reproductor quedase detenido al adelantar. También indicó **enviar siempre a GitHub todos los cambios del proyecto** al cerrar cada tarea, incluidos código, pruebas, helpers, documentación, versión, estado y fuentes de skills. Se revisa el conjunto completo antes de publicar. Credenciales, medios, datos de cuenta e infraestructura y evidencias privadas permanecen excluidos. Publicar un helper o una migración no significa ejecutarlos en producción.

## Reproductor v0.2.6

En la prueba anterior, el compacto recuperó reproducción alrededor de1.2s después de saltar fuera del búfer; el ampliado quedó fijo al menos4.9s mientras recibía fragmentos intermedios del arrastre. Las tres barras ahora previsualizan durante input y solicitan una sola posición al soltar/change/blur, con respaldo de pointerup/touchend y cancelación de gestos. No se sobrescribe el value del control activo.

resources/js/audio-playback.js precarga solo la pista elegida, informa de la carga y controla un único reintento tras12s sin avance. Restaura la posición al recibir metadatos. Si no se recupera, se detiene y ofrece Reproducir; no genera un bucle. Pausar o cambiar de pista cancela el temporizador. Se respetan pausas del sistema y rechazos de permisos de reproducción.

La precarga usa auto después de seleccionar una pista; conserva metadata si el navegador comunica ahorro de datos o red2G. Puede consumir más transferencia de esa pista, incluso en pausa. Un salto fuera del búfer sigue dependiendo de la conexión y el reintento puede repetir algunos bytes. No se descargan otras pistas ni se usa un blob completo, HLS o recodificación de originales. Estos efectos se comunicaron antes de implementar.

La ruta /play-audio mantiene HTTP206/Content-Range comprobados. La URL /storage no está disponible en el hosting actual: no cambiar a esa ruta ni crear enlaces públicos sin una revisión específica. El aspecto normal, catálogo, metadatos y WordPress se mantienen.

## Validación observada

- 12 pruebas Node para carga, pausa, recuperación limitada, cambio de pista, permisos y posiciones válidas.
- 20 pruebas PHP/119 aserciones de filtros y metadatos, con SQLite en memoria y sin tocar la base real.
- Build y comprobaciones de sintaxis correctos. Assets preparados: app-KXBpIFKR.css y player-BcoFn6iV.js; se conservan los fingerprint anteriores.
- En la vista local aislada, compacto pausado65.1%=58.59s; ampliado30% avanzó27.007193→27.715228s; escritorio10% avanzó9→9.411926s.360px sin overflow, área táctil44px y aviso oculto al recuperarse.
- La prueba real del navegador detectó un problema de receptor del temporizador; se corrigió con funciones envolventes y se recompiló antes de publicar.
- Son pruebas nativas del navegador integrado, sin acreditar un teléfono físico. La validación de producción de v0.2.6 se anotará al terminar.

## Preferencias y funciones vigentes

Biblioteca Laravel incrustada en WordPress/Divi: [biblioteca directa](https://audios.iglesiapalma.org/audios?view=table&embed=1) y [página pública](https://iglesiapalma.org/audios-predicaciones/). Repositorio: [audios_app](https://github.com/carlosgilavilez/audios_app).

Usar Codex Browser/iab mediante el runtime oficial de Node REPL por instrucción del usuario. No sustituirlo por Chrome del usuario, Windows Computer Use ni cua_repl.

Mantener el degradado gris azulado, barras finas, Serie después de Buscar y Limpiar con contraste alto. En móvil, cabecera transparente con texto legible, Buscar fijo/compacto y tarjetas con solo título, autor y cita si existe. El reproductor conserva sus metadatos completos, progreso6px, tirador y área táctil44px. Carga50 audios por defecto y respeta otra selección explícita.

El buscador captura criterios inequívocos sin sustituir selecciones explícitas; conserva selección y chips al navegar. Serie está disponible y Desde/Hasta se retiraron; Año permanece. Las variantes históricas de autor no se fusionan automáticamente.

## Inventario y continuidad

La importación autorizada y la resolución de las tres fechas confirmadas ya se completaron. Los322 archivos nuevos están publicados y no queda ningún original del archivo local proporcionado por incorporar. No repetir importación ni resolución de fechas. Los informes detallados y datos administrativos se consultan en el contexto privado.

Las mejoras de revisión del catálogo histórico requieren una tarea separada; no alterar estados, fechas o autores por el mero hecho de actualizar documentación.

La revisión automática rechazó publicar la primera copia del estado porque incluía infraestructura y cuenta privadas. Se conservó íntegra fuera de Git y se preparó esta versión pública. Los helpers utilizan variables de entorno para los directorios operativos; sus valores reales nunca se versionan.

## Procedimientos y cierre

Leer las tres skills en skills/ y sus referencias. El contexto operativo íntegro se localiza junto al repositorio en revision-audios/PROJECT_STATE_PRIVATE.md, únicamente en el workspace privado autorizado. No incluirlo en Git ni copiar sus valores al estado público. Si no está disponible en otro clon, solicitar el contexto operativo privado antes de desplegar.

Los helpers requieren configuración explícita de PALMA_LARAVEL_ROOT y directorios privados según el modo; scripts/PalmaEnvironment.php valida sus límites. No ejecutar deploy.sh, push.js o los workflows históricos a ciegas. Usar [skip ci] en los commits de este flujo y el helper revisado con base/destino explícitos, sin migraciones, dependencias ni importación.

La nueva instrucción de GitHub incorpora también helpers, pruebas y adaptaciones SQLite acumuladas; revisar sus cambios y ejecutar validaciones pertinentes. La sincronización global de skills no reemplaza su publicación en el repositorio. Se admite un commit documental posterior al despliegue: registrar por separado el SHA de aplicación y el cierre de documentación, y comprobar GitHub remoto.

Pendiente inmediato de v0.2.6: publicar el conjunto depurado, desplegar, verificar que currentTime sigue avanzando tras buscar fuera del búfer en MP3 real/iframe, detener la vista temporal y retirar solo su WAV propio, actualizar el resultado y publicar el cierre documental. Mantener el historial privado y los originales intactos.

La apertura de búsqueda se etiqueta «Buscar». En móvil y escritorio el panel usa «Encuentra el mensaje que buscas» como título principal de22px, sin un encabezado «Filtros» separado. Conservar el estado compacto accesible al desplazarse.
