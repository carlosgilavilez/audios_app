# Biblioteca pública de audios - v0.2.1

Código de versión: **v0.2.1**, registrado también en package.json, package-lock.json y CHANGELOG.md.

## Uso y parámetros

La biblioteca se sirve en /audios y /embed. En WordPress se utiliza /audios?view=table&embed=1.

- q: título, autor, referencia bíblica, serie, categoría o año. Un criterio inequívoco se captura en su campo; el texto ambiguo permanece como búsqueda libre.
- serie_id, categoria_id, autor_id, libro_id y anio: criterios explícitos combinables. Se conservan en campos, chips, paginación y enlaces.
- view=table|cards, per_page=10|25|50|100 y embed=1: presentación y tamaño de página.
- dark=1|0: modo oscuro o claro. El botón de tema conserva la preferencia.
- Desde/Hasta se retiraron por decisión del proyecto; sus parámetros antiguos se limpian al entrar.

Orden del filtro: **Buscar, Serie, Categoría, Autor, Año y Libro de la Biblia**. Serie aparece cuando hay series con audios publicados. Buscar aplica la selección; Limpiar conserva la presentación y retira los criterios.

## Diseño y reproductor

La interfaz utiliza verde para la acción principal y botones Limpiar de contraste alto. El modo oscuro recupera el degradado gris azulado anterior, con paneles y reproductor más luminosos; el usuario prefirió este acabado al fondo casi negro de v0.2.0. Las barras de desplazamiento son finas y conservan la navegación por teclado y táctil. La inspiración en Spotify se limita al estilo visual; no se incorporan sus marcas ni contenidos.

En escritorio, el reproductor organiza metadatos, controles, progreso y volumen. En móvil tiene una presentación compacta y otra ampliada con progreso y controles táctiles. Los controles de reproducir/pausar actualizan su nombre accesible y estado; avance/retroceso saltan diez segundos. El progreso compacto y los deslizadores se actualizan con el audio.

La hoja resources/css/public-library.css está limitada a html.audio-library, aplicado a las rutas públicas desde layouts/preview.blade.php. Se carga desde public/audios.blade.php después de los estilos compartidos. Los paneles internos mantienen su tema existente. Ajustar el degradado y las transparencias en esta hoja; conservar el texto limpio, sin el logotipo detrás.

## Compilación y comprobación

Ejecutar npm run build para actualizar manifest y assets. Conservar los archivos fingerprint anteriores para páginas cacheadas. Documentar la versión en CHANGELOG.md y mantener coherentes las versiones de package.json y package-lock.json.

Comprobar modo claro/oscuro, anchos de360,390,820y1280px, títulos largos, orden/selección de filtros, Buscar/Limpiar y navegación. Probar reproducción/pausa, pulsación del icono SVG, progreso, volumen, salto de diez segundos y reproductor móvil ampliado. Respetar movimiento reducido y foco visible.

La prueba aislada se hace con SQLite en memoria o una copia de prueba; RefreshDatabase nunca debe apuntar al MySQL del usuario o producción.

## Integración WordPress

El snippet canónico es iframe-embed.html. Además de la altura bajo la cabecera Divi y los ajustes del anfitrión, v0.2.0 estiliza la barra externa solo en la página205. La hoja Laravel no puede modificar esa barra porque el iframe es de otro origen.

Actualizar el módulo Divi con respaldo fresco y el helper update-wordpress-audio-embed.php. Comprobar la página WordPress real, sus widgets, extremos del iframe y reproductor; una simulación local no acredita ese resultado. Las cabeceras permiten la incrustación solo en los dominios de la iglesia configurados por el proyecto.
