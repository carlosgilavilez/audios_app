Sistema de Gestión de Audios IBRPM

Fecha: 28-08-2025

📌 Resumen

Este documento define los roles de usuario en el sistema de gestión de audios (IBRPM) y las diferencias de permisos entre Administrador y Editor.
El objetivo es garantizar un control claro de quién puede realizar cada acción y quién tiene acceso al registro de actividad (auditoría).

🔹 Roles Definidos
👑 Administrador

Tiene acceso completo a todo el sistema.

Puede crear, editar y eliminar autores, series y audios.

Además, tiene acceso al registro de actividad (logs) que muestra quién creó, editó o eliminó cada entidad.

Es el rol con privilegios máximos en el sistema.

✍️ Editor

Tiene acceso completo a CRUD de autores, series y audios.

Puede subir audios, asignar autores y series, y gestionar los datos básicos.

❌ No tiene acceso al registro de actividad.

Su trabajo se centra en la gestión de contenido, no en la auditoría.

🔹 Cuadro Comparativo
Funcionalidad	Admin ✅	Editor ✅
CRUD Autores	Sí	Sí
CRUD Series	Sí	Sí
CRUD Audios	Sí	Sí
Ver estadísticas	Sí	Sí
Ver actividad (registro/logs)	Sí	❌
🔹 Registro de Actividad (solo Admin)

El registro de actividad permite a los administradores:

Auditar acciones realizadas por cualquier usuario (admin o editor).

Ver qué usuario realizó la acción, en qué entidad, y en qué fecha/hora.

Consultar acciones de tipo:

created

updated

deleted

Este módulo es exclusivo de administradores y se integra en el Dashboard Admin.

🔹 Conclusión

Editores → gestionan contenido (autores, series, audios).

Administradores → gestionan contenido + supervisan mediante el registro de actividad.

Este esquema asegura que el flujo de trabajo sea productivo para editores, mientras los administradores mantienen control total y visibilidad sobre todos los cambios.
