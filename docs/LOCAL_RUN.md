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
npm run dev
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