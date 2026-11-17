# Repetir el inventario

Ejecutar desde la raíz de `audios_app`. Guardar las exportaciones en una carpeta local fuera de Git; la revisión inicial usó `..\revision-audios`.

## Originales

```powershell
php scripts/inventory-audio-originals.php "..\Audios tratados CGIL" "..\revision-audios\originales-locales.json"
```

Lee nombres, tamaños, fechas del nombre y etiquetas/duración con getID3, sin cambiar los originales. Añadir `--sha256` como último argumento para calcular el hash completo. Incluye además subtitle, genre, comment, conductor y encodedBy. Para comparar el MPEG sin diferencias de ID3, usar `scripts/hash-audio-payloads.php`; consultar [importacion.md](importacion.md) si se solicita una carga.

## Catálogo de producción

Usar el panel autorizado y recorrer la paginación completa, con 100 registros por página si está disponible. Extraer datos del DOM de la tabla visible mediante las herramientas de navegador; no cambiar los selectores de estado ni seleccionar acciones masivas. No es necesario abrir Editar, que puede adquirir un bloqueo de edición.

Guardar un JSON con `source`, `consultedAt`, `pages` (URL y resumen de cada página) y `rows`. Cada fila debe tener:

| Campo | Contenido |
|---|---|
| `id` | ID numérico único del audio |
| `title` | Título completo, uniendo sus fragmentos de línea con espacios |
| `author` | Autor visible |
| `category` | Categoría visible |
| `series` | Serie visible |
| `date` | Fecha `DD/MM/YYYY`, o cadena vacía si falta |
| `citation` | Cita bíblica visible |
| `turn` | Turno visible |
| `status` | Estado real del registro; en el select usar `data-last-value` o la opción seleccionada, no todos los textos de opciones |
| `duration` | `MM:SS` o `HH:MM:SS` |
| `editUrl` | URL de edición observada, solo como referencia |

Confirmar que el total y los IDs únicos coinciden con el resumen del panel. El endpoint público `/api/audios` del código inicial filtra el estado antiguo `Normal`; **no usarlo como sustituto del inventario de todos los estados** sin corregir y verificar antes su contrato.

## Archivos servidos y cruce

```powershell
php scripts/check-audio-files.php "..\revision-audios\catalogo-produccion.json" "..\revision-audios\archivos-servidor-head.json"
python scripts/compare-audio-inventory.py "..\revision-audios\originales-locales.json" "..\revision-audios\catalogo-produccion.json" "..\revision-audios" --file-headers "..\revision-audios\archivos-servidor-head.json"
```

Si el alias `python` no funciona en Windows, localizar el Python empaquetado mediante `load_workspace_dependencies`; no instalar otro Python por ese motivo.

El comprobador usa HEAD contra `audios.iglesiapalma.org/play-audio/{id}`: no descarga audios ni cambia datos; por defecto admite cuatro peticiones simultáneas y repite una vez los fallos 5xx/de red. En Hostinger usar `--sequential-delay-ms=1000` como último argumento para una petición cada vez y una pausa entre ellas: una tanda concurrente produjo errores temporales de conexión MySQL que desaparecieron al repetir los afectados con ese ritmo. No se midió un límite exacto del hosting. Distinguir una incidencia transitoria de un fallo persistente y conservar ambos informes, sin cambiar configuración de MySQL por una hipótesis.

El cruce requiere coincidencia unívoca entre originales y registros. Primero usa título+fecha+duración, después título+duración, y por último fecha+duración. Tolera dos segundos de redondeo. Si se aporta HEAD, añade la comparación de tamaño exacto. Produce `comparacion-inventario.json` y `originales-sin-coincidencia.csv`.

Revisar coincidencias sin título o con fechas distintas. No corregir fechas automáticamente: el nombre, ID3 y catálogo pueden discrepar. No convertir una comparación de tamaño en una afirmación de igualdad de hash.

## Auditoría del almacenamiento en Hostinger

Cuando el hosting sea accesible dentro del alcance autorizado, usar `scripts/audit-audio-storage.php`. En la revisión inicial se confirmó que no había audios huérfanos: consultar PROJECT_STATE.md para fecha y cifras, no dejar esa comprobación como pendiente.

El helper solo funciona por CLI, comprueba que Laravel está en producción, lee id/archivo/estado, lista el disco público y compara rutas. No modifica registros ni medios. Enumera extensiones de audio habituales, cuenta otros archivos y genera detalle de ausentes, no registrados, duplicados y tamaños. No calcula hashes binarios.

Requiere PALMA_LARAVEL_ROOT con la raíz verificada y scripts/PalmaEnvironment.php junto al helper. Rechaza informes dentro del web root. Los valores de directorios se consultan en el contexto privado; publicar las fuentes no autoriza ejecutarlas.

Ejemplo con un directorio privado ya creado:

```bash
php "$PALMA_DEPLOY_INBOX/palma-audit-audio-storage.php" "$PALMA_DEPLOY_BACKUPS/DIRECTORIO_REVISADO/storage-audit.json"
```

Imprime solo el resumen y guarda el detalle con permisos600. Leer el resumen y el resultado de la orden; conservar el JSON privado y su ubicación como evidencia. Si hay archivos no registrados, cruzarlos por nombre/tamaño/metadatos con los originales antes de llamar a todos los no coincidentes «pendientes de subir». Si no hay huérfanos, registrar expresamente esa conclusión y su disco/alcance.
