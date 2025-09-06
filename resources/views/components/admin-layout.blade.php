><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <header class="bg-white shadow p-4">
            <h1 class="text-xl font-bold">Panel de Administración</h1>
        </header>
        <main class="p-6">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
