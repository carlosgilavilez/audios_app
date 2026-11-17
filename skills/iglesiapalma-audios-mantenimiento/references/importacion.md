# Cargar originales ausentes con metadatos

Leer PROJECT_STATE.md para el alcance autorizado, carpeta privada, evidencias y etapa real de la carga. La orden de subir los ausentes y reflejarlos en la tabla pública autoriza publicarlos; conservar los pendientes anteriores. Un inventario aislado no autoriza esa carga.

## Preparación y correspondencias

1. `scripts/inventory-audio-originals.php ORIGINALES SALIDA.json --sha256` lee ID3 ampliado y calcula SHA-256 sin modificar medios.
2. `scripts/hash-audio-payloads.php INVENTARIO.json SALIDA.json` añade el hash del MPEG sin ID3 y confirma la integridad del archivo completo. Permite reconocer copias cuyos tags fueron retocados.
3. Revalidar contra el catálogo/almacenamiento actual. No restar simplemente los totales. El helper remoto resuelve por SHA completo, después por MPEG; registra el método. El fallback de metadatos/tamaño solo usa correspondencias ya verificadas y exige identidad de título/ID/tamaño.

`AudioImportMetadata.php` genera título, autor, serie, categoría, libro/cita, fecha, turno, duración y descripción factual. No fusiona perfiles existentes. La procedencia documental o de transcripción queda en overrides/plan. Las discrepancias irresueltas de fecha se retienen con fecha nula; dos campos ID3 iguales no son evidencia independiente frente al nombre.

Si el usuario confirma expresamente una política general de fechas del nombre, se puede configurar `"_settings":{"datePolicy":"filename"}` en overrides.json. Sin esa respuesta, conservar las retenciones. Los overrides por archivo tienen prioridad.

Para contenido sin título, `transcribe-audio-content.py` realiza ASR local con los requisitos fijados, sin transmitir los audios. Un título descriptivo derivado del fragmento se registra como tal; no implica haber escuchado la grabación completa. Nunca atribuir identidad mediante comparación de voces. Usar créditos escritos oficiales o una identificación verbal inequívoca, conservando la fuente.

## Transferencia privada y ejecución

hPanel File Manager → **All hosting files** permite acceder al home de la cuenta, fuera de public_html. Navegar a la carpeta privada del lote y staging; subir archivos en tandas con el selector oficial. Continue in background permite usar el terminal sin detener la cola. La carga del formulario Laravel limita archivos a50MB, insuficiente para algunos originales. No duplicar la transferencia ya completada.

Copiar al directorio privado `import-local-audios.php`, `AudioImportMetadata.php`, JSON fuente y overrides.json; verificar SHA de los helpers/JSON. Usar las herramientas disponibles y documentadas; el terminal web Linux de hPanel es la vía ya confirmada. Si la herramienta detiene Computer Use, detener los inputs y registrar el bloqueo. No trasladar handles/índices entre runtimes.

El helper requiere PALMA_LARAVEL_ROOT y PALMA_IMPORT_WORKDIR configurados en privado y scripts/PalmaEnvironment.php junto a AudioImportMetadata.php. Valida que el directorio sea exactamente el autorizado y esté fuera del web root. Las copias operativas anteriores se consultan en el contexto privado. Sus modos son:

```bash
php WORK/import-local-audios.php plan WORK/audio-import-source-2026-11-15.json WORK
php WORK/import-local-audios.php check WORK/audio-import-source-2026-11-15.json WORK
php WORK/import-local-audios.php apply WORK/audio-import-source-2026-11-15.json WORK ACTOR_ID
```

Sustituir WORK y ACTOR_ID por valores verificados del estado; no ejecutar esos literales. Primero revisar plan.json y retenciones. check exige todos los archivos y SHA correctos; apply repite guardas. Cualquier cambio de helpers, fuente, overrides o catálogo obliga a regenerar plan. Cachés privados de hashes usan tamaño/mtime/ctime/inodo y SHA esperado; no borrar por costumbre ni confundir un caché con un resultado de apply.

apply exige un administrador existente, guarda before-database.json, usa bloqueo de lote y transacción, impide sobreescrituras y duplicados publicados título/autor/fecha. Crea solo los ausentes y logs de actividad; mueve sus archivos verificados al disco público con permisos644. Ante fallo revierte la transacción y devuelve sus medios a staging. Si hay colisión, investigar; no desactivar la guarda.

La carpeta de destino del helper actual es `audios/import-2026-11-15`: revisar y adaptar explícitamente para un lote futuro. No reutilizar un apply cuyo plan ya se consumió. result.json acredita IDs creados, estados, cifras y huella de los registros anteriores. El backup contiene datos privados: nunca publicarlo en Git.

Después, verificar result.json, originales sin cambios, auditoría de almacenamiento, enlaces servidos y tabla interna/pública. Comprobar Serie y filtros en la página WordPress real, móvil y reproducción/pausa. Guardar evidencias locales y actualizar PROJECT_STATE.md; distinguir lo pendiente de carga, de metadatos y de publicación.

El plan consumido y result.json son evidencia del apply: **no volver a ejecutar apply sobre ese lote**. Para retomar, consultar primero result.json y el estado real. Los archivos que se movieron a su destino ya no estarán en staging; eso no es una transferencia incompleta.

Reutilizar `scripts/verify-local-audio-import.php WORK` después de consolidar los HEAD en head-final.json. Comprueba todos los campos y relaciones guardados, estado/ruta/tamaño, respuesta y tamaño servidos, huella de registros previos y logs de creación. Solo lee producción y genera post-import-verification.json e imported-audios.csv privados; termina con código2 si hay discrepancias. Sus fuentes y copias privadas deben coincidir.

Para HEAD en este hosting preferir `check-audio-files.php INPUT OUTPUT --sequential-delay-ms=1000`. Si una tanda falla por conexión a MySQL, conservar el diagnóstico y repetir solo esos IDs de forma pausada antes de declarar archivos rotos. El resultado final debe mantener una fila por cada ID comprobado y su historial de intentos.

Los títulos pueden coincidir aunque sean grabaciones distintas: conservar las distinciones explícitas de las etiquetas, como una conferencia y su «Debate». Si el título se toma solo del primer segmento separado por barras, revisar duplicados título/autor/fecha antes de apply, además de los hashes. No quitar la guarda de duplicados para superar una colisión.

## Resolver fechas confirmadas después de importar

Para el lote actual existe `scripts/resolve-import-dates.php check|apply WORK ACTOR_ID`. Lee confirmed-import-dates.json, con reason e items (id,sourcePath,date), generado únicamente de una confirmación explícita del usuario. Su guarda actual exige exactamente tres registros: no usarlo como editor genérico ni ampliar esa lista sin una nueva instrucción.

El modo check verifica la pertenencia al lote aplicado, estado Pendiente/fechaNULL, ausencia de cambios en título/descripción/ruta, SHA completo del MP3 y fecha igual al nombre confirmado; también descarta duplicados publicados. apply repite esas guardas, respalda todos los audios, usa transacción y modifica solo fecha/descripción/estado de los tres IDs, además del updated_at habitual y sus logs. Conserva los demás registros y limpia la caché.

Guarda date-resolution-result.json y un before-date-resolution-*.json privado; si el resultado ya existe, se detiene para impedir aplicar dos veces. El plan y result.json iniciales se mantienen intactos. Se preservan también initial-post-import-verification.json e initial-imported-audios.csv antes de regenerar los informes actuales.

El verificador actualizado reconoce ese resultado separado y comprueba su importSourceHash y confirmationHash antes de ajustar las expectativas de los IDs resueltos. Reejecutarlo para validar los322 registros del lote, repetir HEAD solo en los tres enlaces afectados con pausa1000ms y comprobar tabla interna/pública. Descargar los informes finales, conservar el CSV inicial y actualizar el CSV entregado. Registrar en PROJECT_STATE.md el alcance exacto de la confirmación, fecha, respaldo, cifras y que ya no queda esa pregunta pendiente.
