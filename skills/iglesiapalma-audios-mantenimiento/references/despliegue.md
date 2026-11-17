# Despliegue e integración WordPress

Leer primero docs/PROJECT_STATE.md y el contexto operativo privado indicado allí. Las rutas reales, cuenta compartida, usuario operativo y respaldos detallados se conservan fuera del repositorio público. El proceso no autoriza una publicación por sí solo.

## Navegador y acceso

El usuario exige Codex Browser/iab mediante el runtime oficial de Node REPL. Localizar el plugin Browser instalado y su scripts/browser-client.mjs según la documentación disponible. setupBrowserRuntime devuelve Agent: conservarlo, listar navegadores, elegir el backend iab y leer browser.documentation(). No asumir variables globales ni reutilizar handles caducados. No sustituirlo por Chrome, Windows Computer Use o cua_repl; un intento anterior con cua_repl fue rechazado.

Acceder a hPanel con las credenciales privadas ya aportadas y gestionar el sitio de audios desde la cuenta compartida identificada en el contexto privado. El usuario completa MFA cuando aparezca. No crear accesos, claves ni permisos adicionales. Para un iframe cruzado, si falla locator.press por la raíz de foco, comprobar activeElement y usar teclado AX nativo solo si el control previsto está enfocado; no simular eventos con JavaScript.

El terminal de File Manager tiene aria-label Terminal input y salida .xterm-rows. Comprobar una orden corta después de inactividad y esperar resultado/prompt antes de pegar una operación larga. La primera orden al reconectar puede perderse. No usar APIs privadas de Hostinger ni transportes de sesión alternativos.

## GitHub y configuración

Publicar todos los cambios del proyecto al cerrar una tarea por instrucción expresa del usuario: código, helpers, pruebas, documentación, estado y fuentes de skills. Revisar secretos, archivos privados y el diff completo. Verificar SHA remoto; un commit local no basta. Separar si procede aplicación y cierre documental y registrar cuál se desplegó. No publicar datos de infraestructura ni el contexto privado para cumplir esta instrucción.

Los workflows históricos pueden migrar/sembrar o limpiar por FTP. Usar [skip ci] en este flujo revisado; no ejecutar push.js, deploy.sh ni FTP a ciegas. Compilar localmente e incluir manifest y todos los assets nuevos, conservando fingerprint anteriores.

El hosting acepta fetch HTTPS público con autenticación limpiada para esa orden. No crear claves por un fallo de origin SSH. Usar FETCH_HEAD si fetch por URL dejó origin/main anterior. Revisar el destino si main cambió mientras se preparaba.

Los helpers públicos requieren valores privados suministrados en el entorno de ejecución:

- PALMA_LARAVEL_ROOT: raíz de la aplicación verificada.
- PALMA_DEPLOY_BACKUPS: directorio existente de respaldos fuera del web root.
- PALMA_DEPLOY_INBOX: directorio privado de preparación.
- PALMA_IMPORT_WORKDIR: directorio privado exacto del lote, solo para importación/verificación.

No guardar esos valores en Git. scripts/PalmaEnvironment.php valida límites y debe acompañar a los helpers PHP que lo requieren, además de AudioImportMetadata.php cuando corresponda. Las copias operativas históricas pueden tener otra configuración: comprobar fuente/hash y consultar el contexto privado antes de reemplazarlas.

## Desplegar Laravel

Comparar HEAD real, GitHub main, destino y base revisados. El helper scripts/deploy-public-library.sh exige TARGET_SHA, modo --check|apply y BASE_SHA, además de las variables de entorno privadas. No asumir una base histórica.

Ejecutar primero --check. Solo con validación correcta y autorización vigente, ejecutar apply. El helper valida árbol limpio, raíz, main y ancestro; respalda código/build, huella de .env y totales; hace avance directo en mantenimiento breve, conserva assets y limpia vistas. No ejecuta migraciones, seeders, Composer ni npm. El trap restaura servicio e intenta volver al SHA anterior ante error si el árbol sigue limpio.

Verificar salida final, HEAD, .env sin cambios, mantenimiento desactivado y catálogo sin alteraciones. Guardar rutas de respaldo/log y cifras administrativas en el contexto privado; el estado público registra solo el resultado y SHA de aplicación. Un exit0 sin comprobación pública no basta.

## Iframe Divi

Usar iframe-embed.html y scripts/update-wordpress-audio-embed.php con WP-CLI, el sitio esperado y un administrador existente identificado por la configuración privada. Confirmar página/slug/estado. Crear un respaldo fresco de post_content fuera del web root, proporcionar PALMA_EMBED_BACKUP y validar sin escribir. PALMA_EMBED_SOURCE permite una fuente dentro del inbox; PALMA_EMBED_APPLY=1 aplica solo cuando esté autorizado.

El helper reemplaza únicamente el token JSON del código en la estructura Divi esperada, sin reserializar todo el árbol. Si la estructura o el contenido cambió, detenerse e inspeccionar. Si el snippet ya coincide, no escribe. Comprobar página y metadatos al terminar. Para revertir, restaurar solo post_content desde una copia privada revisada con el administrador existente.

## Verificar y cerrar

Comprobar directamente y dentro del WordPress real filtros y tamaños pertinentes. En reproducción, cargar un MP3 real, buscar fuera del búfer y verificar que currentTime sigue aumentando; dejarlo pausado. Mantener los datos de infraestructura fuera de capturas públicas. Cerrar pestañas de QA, restablecer viewport y actualizar/sincronizar las skills. Publicar en GitHub el cierre documental y comprobar su SHA remoto; las evidencias detalladas permanecen privadas.
