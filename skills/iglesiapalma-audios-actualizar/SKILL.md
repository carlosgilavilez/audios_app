---
name: iglesiapalma-audios-actualizar
description: Actualizar el contexto persistente y las skills del proyecto de audios de Iglesia Palma con nuevas instrucciones, correcciones, resultados y próximos pasos para futuros agentes.
---

# Actualizar la continuidad del proyecto

Localizar y leer audios_app/docs/PROJECT_STATE.md y el contexto operativo privado indicado allí. Aplicar iglesiapalma-audios-proyecto si hace falta.

El usuario pidió mantener las tres skills precisas con cada nueva instrucción o corrección y automatizar lo repetible cuando facilite una tarea real. Incorporar lo confirmado al estado canónico, sustituir pendientes obsoletos y registrar lo que todavía falta. No convertir una hipótesis o sugerencia en decisión del usuario.

El16/11/2026 añadió la instrucción permanente de enviar siempre a GitHub todos los cambios del proyecto al cerrar cada tarea. Incluir las fuentes de las skills, PROJECT_STATE.md, documentación, helpers, pruebas y versión, además del código. Revisar secretos/exclusiones y cambios previos; comprobar la publicación remota, sin confundir commit local con push. Si el estado se actualiza después del despliegue, publicar un commit documental de cierre y distinguirlo del SHA de aplicación realmente desplegado. Registrar su SHA final en el informe privado cuando incluirlo en el propio commit produciría una referencia circular.

Conservar la preferencia explícita de usar el navegador integrado de Codex (Browser/iab) para el proyecto, mediante el runtime oficial de Node REPL validado. No reemplazarlo por el Chrome del usuario, Windows Computer Use o cua_repl al retomar. Registrar la conexión y documentación oficial recuperadas sin guardar cookies, puertos de sesión, tokens ni handles caducados como si fueran accesos reutilizables.

Actualizar en la misma intervención:
1. Fecha/hora America/La_Paz, objetivo y alcance/autorización actuales.
2. Estado y archivos relevantes; distinguir local, publicado en GitHub, desplegado y verificado. Registrar SHA, ejecución y respaldo solo si se observaron.
3. Pruebas/evidencias con origen y límites. Un HTTP500 seguido de200 no es archivo roto confirmado; una vista local no es producción.
4. Inventario con fecha, conjunto de originales, estados y auditoría de archivos cuando exista; no dejar hipótesis de huérfanos pendiente si ya se comprobó.
5. Accesos disponibles, respuestas recibidas, bloqueos concretos y siguiente acción ejecutable. No volver a pedir credenciales o aprobaciones ya aportadas.
6. Archivos aún sin commit y cambios previos del usuario preservados, para que otro agente no los pierda ni asuma que existen en otro clon.

Mantener las cifras/avances en docs/PROJECT_STATE.md. Las skills contienen procedimientos reutilizables y enlaces, sin copiar historiales cambiantes. Si cambia un proceso, actualizar su fuente skills/iglesiapalma-audios-*/SKILL.md y referencias; reutilizar o mejorar un helper para una operación repetible en lugar de dejar órdenes improvisadas como único conocimiento.

En una entrega de interfaz, registrar la preferencia visual final, versión SemVer en package.json/package-lock.json/CHANGELOG.md, guía pública, commit realmente desplegado y capturas de producción. Si el usuario corrige el diseño mientras se trabaja, incorporar esa corrección al resultado y sustituir la preferencia anterior en el contexto vigente; conservar las capturas anteriores como histórico identificado.

En cambios del reproductor táctil, distinguir pruebas nativas de puntero/teclado del navegador y pruebas físicas de teléfono. Registrar posición real y estado de pausa/reproducción, sincronización compacto/ampliado y cualquier limitación de automatización del iframe con la alternativa comprobada; conservar el procedimiento en la referencia de interfaz de mantenimiento.

En cargas, registrar por separado transferencia privada, hashes, plan, inserción en catálogo y publicación observada. Conservar las preguntas pendientes y la procedencia de títulos/autores/fechas; una respuesta sobre un archivo no establece automáticamente una política para todos. Si cambia la herramienta de navegador o esta detiene la sesión, documentar el motivo y el punto de reanudación, sin reutilizar identificadores de otra sesión ni declarar completada una operación pendiente.

Cuando llegue una confirmación y se aplique, sustituir el pendiente correspondiente por el resultado verificado y las cifras actuales. Mantener la evidencia inicial como histórica y enlazar el manifiesto de corrección; no dejar instrucciones antiguas de volver a preguntar o volver a publicar IDs ya resueltos. Actualizar también el CSV entregado preservando su versión inicial.

Comprobar la interfaz y sintaxis de helpers modificados y una ejecución relevante cuando proceda. Mantener copias de preparación palma-* coherentes con sus fuentes o marcarlas como históricas. No hace falta repetir toda la batería del proyecto para una actualización documental.

Usar `scripts/sync-project-skills.ps1` desde la raíz local del repositorio. Sin opciones valida las tres fuentes con quick_validate.py; con `-Sync` valida primero y copia **solo esas tres skills** al directorio de skills de Codex del usuario, comprobando hashes e incluyendo agents/openai.yaml y referencias. No borra archivos ni toca otras skills. Ejecutar dentro de los permisos disponibles; los cambios fuera del workspace pueden requerir escalación de la herramienta.

El helper acepta -RepoRoot, -CodexSkillsRoot, -PythonExecutable y -YamlModuleRoot. Reutiliza Python/PyYAML existentes, no instala dependencias. El entorno sandbox puede usar otro TEMP: si falta yaml, usar el directorio de PyYAML preparado en el entorno nativo según PROJECT_STATE.md. No instalar otro Python por ello.

Nunca guardar valores de .env, contraseñas, tokens, cookies, códigos MFA ni claves en contexto, skills o Git. Registrar nombres de variables y ubicación basta. Documentar un rechazo automático y la alternativa aceptada sin intentar publicar después el alcance rechazado por otra vía. Actualizar documentación no autoriza desplegar, editar catálogo ni cargar medios.


El repositorio es público: conservar rutas de infraestructura, datos de cuenta compartida, usuarios operativos y respaldos detallados en revision-audios/PROJECT_STATE_PRIVATE.md, fuera de Git. El estado y las skills públicos describen el proceso con variables de entorno; actualizar ambos contextos y no volver a enviar el documento privado completo.
