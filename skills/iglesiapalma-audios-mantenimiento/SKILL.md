---
name: iglesiapalma-audios-mantenimiento
description: Mantener el buscador, el diseño responsive, el reproductor y el inventario del sistema Laravel de audios de Iglesia Palma, y desplegar sus cambios en Hostinger cuando se solicite.
---

# Mantenimiento del sistema de audios

Leer primero audios_app/docs/PROJECT_STATE.md; aplicar iglesiapalma-audios-proyecto si hace falta localizarlo. Al invocarla, ejecutar el trabajo pedido con los helpers existentes y verificar el resultado. No crear una tarea recurrente ni repetir cambios ya desplegados.

Usar el navegador integrado de Codex (Browser/iab), por instrucción expresa del usuario, con el runtime oficial de Node REPL. No usar Chrome del usuario, Windows Computer Use ni cua_repl para este proyecto. Las interacciones de hPanel se hacen en ese navegador con el API oficial del plugin; recuperar la conexión documentada si cambia el entorno de herramientas.

## Buscador y móvil

Para renovar la interfaz o el reproductor, leer [references/interfaz.md](references/interfaz.md): preferencias visuales vigentes, CSS público, barras del iframe y comprobación de versiones.

- Reproducir en https://audios.iglesiapalma.org/audios?view=table&embed=1 y https://iglesiapalma.org/audios-predicaciones/.
- Relacionar PublicAudioController, formulario compartido, lista, public-audios.js y parámetros GET. Comprobar autor, libro, categoría, serie y año separados y combinados; selección y chips deben persistir al buscar/paginar/cambiar vista. Desde/Hasta se retiraron: comprobar también la limpieza de URLs antiguas con fecha_desde/fecha_hasta. Al retirar un criterio, conservar los demás; al limpiar, conservar embed/view/per_page.
- La biblioteca pública y /embed cargan50 por defecto; conservar las elecciones explícitas de per_page y su valor en los formularios. La regresión correspondiente usa más de50 registros para comprobar el límite real, además de la selección del control.
- Texto inequívoco puede convertirse en el campo correspondiente; no convertir ambiguos ni reemplazar una selección explícita distinta. Las variantes de autor son datos a revisar, no una autorización para fusionarlos.
- Usar PublicAudioFiltersTest y añadir regresiones cuando cambie comportamiento que lo justifique. Ejecutar con SQLite :memory:, APP_ENV=testing, CACHE_STORE=array y SESSION_DRIVER=array, nunca con RefreshDatabase contra el MySQL local del usuario o producción. Las dos correcciones históricas de migraciones para SQLite siguen locales: no incluirlas automáticamente en un despliegue de filtros.
- Revisar tamaños pertinentes (360, 390/414, 768/820, 1280), scroll del panel, resultados largos, paginación y reproductor. Comprobar el iframe real y los widgets del anfitrión por separado; una cabecera simulada no acredita el resultado en Divi.
- El progreso del reproductor móvil encogido debe poder tocarse y arrastrarse, conservando el estado de pausa/reproducción y la sincronización con el ampliado. La referencia de interfaz describe su área táctil y la verificación nativa en el iframe.
- Compilar assets si cambian fuentes. Incluir CSS/JS nuevos y manifest; conservar los fingerprint anteriores para páginas cacheadas. No publicar un manifest con archivos omitidos ni añadir eliminaciones por costumbre.

## Inventario

Leer [references/inventario.md](references/inventario.md). Reutilizar los scripts de originales, HEAD, comparación y auditoría de almacenamiento.

- Confirmar catálogo completo y estados; el total público incluye solo Publicado.
- Comparar originales sin alterarlos y exigir correspondencias unívocas. No calcular pendientes restando simplemente totales.
- Si hay acceso autorizado al hosting, ejecutar audit-audio-storage.php: separa registros sin archivo, archivos sin registro y referencias duplicadas. Guardar el informe fuera de public_html.
- Distinguir pendientes de carga, subidos pendientes de publicación, dudas de metadatos e incidencias transitorias. Entregar cifras con fecha, alcance y evidencia; no cargar ni publicar por el mero hecho de inventariar.

Para cargas expresamente solicitadas, leer [references/importacion.md](references/importacion.md). Reutilizar el flujo privado plan/check/apply y preservar los pendientes anteriores. La autorización vigente y el punto exacto de reanudación están en PROJECT_STATE.md.

Si el usuario confirma fechas de audios retenidos de un lote ya importado, usar el procedimiento de resolución posterior de esa referencia: respaldo, cambios limitados a los IDs confirmados, publicación y verificación final. No volver a aplicar el importador ni sobrescribir su evidencia inicial.

## Despliegue e iframe

Al cerrar una tarea, publicar en GitHub **todos los cambios del proyecto** por instrucción expresa del usuario, incluidos helpers/pruebas/docs/estado/skills. Revisar la lista completa y las exclusiones antes del commit; verificar el SHA remoto y conservar .env, medios e informes privados fuera de Git. Se pueden usar commits separados para aplicación y cierre documental. Registrar cuál está desplegado y cuál es el último del repositorio; no dejar las skills solo en la copia global. La publicación no implica ejecutar helpers o migraciones en producción.

Leer [references/despliegue.md](references/despliegue.md) para el acceso confirmado, fetch HTTPS, helper de Git y actualización de la página Divi por WP-CLI.

Usar el alcance ya autorizado en la conversación; no pedir de nuevo una aprobación recibida. Revisar archivos/workflows y preparar un cambio concreto antes de cualquier aprobación que realmente falte. Una nueva tarea de revisión no implica por sí sola desplegar.

No ejecutar a ciegas scripts/push.js (git add -A), deploy.sh (migraciones/stash) ni deploy-ftp.yml (dangerous-clean-slate:true). El flujo de biblioteca permite desplegar sin migraciones, dependencias ni cambios del catálogo. Los helpers se suben a una ubicación privada; verificar que existan localmente, porque no todos están en el commit público.

Para el iframe, usar iframe-embed.html y update-wordpress-audio-embed.php con backup fresco. Conservar el bloque Divi existente; el helper reemplaza solo el token JSON del código y se detiene si cambia la estructura. Validación por defecto, escritura explícita e idempotencia. No reserializar todo el árbol de Divi.

Al terminar o encontrar un bloqueo, aplicar iglesiapalma-audios-actualizar: registrar resultados observados, SHA/respaldo, archivos locales y publicación real, evidencias y siguiente acción.

