# Sistema Audios IBRPM – Progreso hasta 27-08-2025

## 📌 Contexto General
- Proyecto: **Sistema de gestión de audios IBRPM**.  
- Backend en **Laravel** (subdominio `audios.iglesiapalma.org`).  
- Parte pública en **WordPress**, con consumo de la API vía **plugin/shortcode**.  
- Objetivo: subir, administrar y mostrar audios con autores, series, categorías, turnos, libros, etiquetas.  

---

## 🚀 Instalación Local

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd audios_app
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**
   - Copia `.env.example` a `.env` y configura tu base de datos.
   - Genera la clave de la aplicación:
     ```bash
     php artisan key:generate
     ```

4. **Migrar y sembrar la base de datos**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Compilar assets y correr el servidor**
   ```bash
   npm run dev
   ```
   En otra terminal:
   ```bash
   php artisan serve
   ```

6. **Acceder a la aplicación**
   - **App**: [http://localhost:8000](http://localhost:8000)
   - **Login**: `/login` (user: `test@example.com`, pass: `password`)
   - **Admin**: `/admin`

---

## ✅ Estado del Backend (hasta v1.0.6)
- API implementada y probada (CRUD de **Autores** y **Series**, con campo `comentario`).  
- Regla de negocio: al eliminar un autor o serie, los audios asociados cambian a estado = **Pendiente** (no se borran).  
- Migraciones corregidas: `autor_id` y `serie_id` ahora son `nullable()` con `nullOnDelete()`.  
- Controladores API y tests de feature completados.  
- Autenticación protegida con `auth:web`.  

---

## ✅ Estado del Frontend (Laravel)
- **Laravel Breeze** instalado con Blade + Alpine + Tailwind + Vite.  
- Login, registro y dashboard funcionando.  
- Panel admin creado en `/admin`:  
  - Controladores web: `AutorAdminController`, `SerieAdminController` y `AudioAdminController` implementados con todos los métodos del resource.
  - Vistas Blade (`index`, `create`, `edit`) para autores, series y audios completadas y consistentes.
  - Formularios de creación/edición de audios incluyen todos los campos y relaciones (autores, series).
  - Tablas en las vistas `index` muestran los datos de la base de datos y ahora soportan **búsqueda y filtrado dinámico**.
  - **Funcionalidad de subida de audios mejorada** en `admin.audios.create` con drag & drop y barra de progreso.
  - Mensajes flash de éxito/error implementados.  

---

## Admin Routes
- `/admin/autores`
- `/admin/series`
- `/admin/audios`

---

## ⚠️ Pendiente
- QA manual de CRUD Autores/Series/Audios en navegador.  
- Implementación de la **apariencia/UX** según checklist (tooltips, reproductor sticky, filtros, badges de categorías, etc.).  
- Integración en WordPress con shortcode.  

---

## ▶️ Próximos Pasos
1. Terminar panel admin con tablas completas y confirmación de QA.  
2. Implementar capa de apariencia (checklist UX).  
3. Crear plugin/shortcode WP que consuma la API.  
4. Desplegar en Hostinger (subdominio `audios.iglesiapalma.org`).  

---

📌 **Nota para Gemini**:  
Este archivo sirve como punto de referencia del proyecto. Retoma a partir de aquí.

  
