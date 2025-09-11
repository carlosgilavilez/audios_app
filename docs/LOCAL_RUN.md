# LOCAL_RUN.md
## Cómo arrancar el sistema en local

### 1. Iniciar Laravel
En la terminal, dentro del directorio del proyecto:
```bash
php artisan serve
```

Esto levantará Laravel en:
👉 http://localhost:8000

### 2. Iniciar Vite

En otra terminal (también en la carpeta del proyecto):
```bash
npm install
Arranque local
 - `npm run dev`: inicia Vite en desarrollo.

Publicar cambios y desplegar a producción
 - `npm run deploy -- "mensaje opcional"`
   - Hace `git add -A`, `git pull --rebase`, crea commit si hay cambios y hace `git push`.
   - Si estás en `main`, se dispara el workflow de GitHub (`.github/workflows/deploy.yml`) que construye, empaqueta y despliega al servidor.
   - Si no estás en `main`, solo empuja tu rama y muestra un aviso.

Notas
 - Requiere que el remoto `origin` esté configurado y que tengas permisos para empujar.
 - El workflow usa los secretos `SSH_HOST`, `SSH_PORT`, `SSH_USER`, `SSH_KEY` o `SSH_PASSWORD`, y `TARGET_DIR` en GitHub.
```

Esto instalará las dependencias de Node y levantará Vite, que compila los estilos y scripts en caliente.

✅ Con ambos comandos corriendo (Laravel + Vite), el sistema estará listo para usarse en el navegador en:
👉 http://localhost:8000


---

## 📝 Mensaje para Gemini cuando abras el directorio

Cuando abras el proyecto en VS Code o en tu terminal, solo dile a Gemini algo así:

> **"Gemini, ejecuta los pasos de `docs/LOCAL_RUN.md` para arrancar el sistema en local."**

Y él sabrá:
1. Abrir una terminal y correr `php artisan serve`.  
2. Abrir otra terminal y correr `npm run dev`.  

---

📌 Con esto:  
- Tú no te complicas.  
- Gemini sabe qué hacer cada vez.  
- Todo queda documentado dentro del proyecto.  

---

## ✅ Checklist Rápido (para ti)

1.  **Terminal 1**: `php artisan serve`
2.  **Terminal 2**: `npm install` y luego `npm run dev`
3.  **Navegador**: Abrir `http://localhost:8000`
