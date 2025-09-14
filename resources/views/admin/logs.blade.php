@extends('layouts.dashboard')

@section('title', 'Registros de Actividad')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Registros de Actividad</h1>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <table class="min-w-full divide-y divide-border text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripci&oacute;n</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse ($activityLogs as $log)
                            @php
                                $colorClass = '';
                                $actionWord = '';
                                switch ($log->action) {
                                    case 'created':
                                        $colorClass = 'text-green-600';
                                        $actionWord = 'creó';
                                        break;
                                    case 'updated':
                                        $colorClass = 'text-yellow-600';
                                        $actionWord = 'actualizó';
                                        break;
                                    case 'deleted':
                                        $colorClass = 'text-red-600';
                                        $actionWord = 'eliminó';
                                        break;
                                }

                                // Calcular el resto de la descripción quitando la palabra de acción + espacio
                                $description = $log->description;
                                $prefix = $actionWord . ' ';
                                if ($actionWord !== '' && str_starts_with($description, $prefix)) {
                                    $restDescription = substr($description, strlen($prefix));
                                } else {
                                    $restDescription = $description; // Respaldo por si cambia el formato
                                }
                            @endphp
                            <tr class="hover:bg-muted/50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $log->user->name ?? 'Sistema' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($actionWord)
                                        <span class="{{ $colorClass }}">{{ $actionWord }}</span>
                                        {{ ' ' . $restDescription }}
                                    @else
                                        {{ $log->description }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-muted-foreground">No hay actividad para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 pt-0">
                {{ $activityLogs->links() }}
            </div>
        </div>
    </div>
@endsection
